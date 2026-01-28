<?php
// =============================================================================
// api/sql-execute.php - Secure SQLite Execution Engine for CodeBin
// =============================================================================

require_once 'session.php';
require_once 'config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}

// =============================================================================
// CONFIGURATION
// =============================================================================
define('DB_FOLDER', __DIR__ . '/../databases/');
define('MAX_DB_SIZE', 5 * 1024 * 1024);      // 5MB per database
define('MAX_ROWS_RETURNED', 1000);            // Max rows in result
define('QUERY_TIMEOUT', 5);                   // Seconds
define('MAX_STATEMENTS', 50);                 // Max statements per execution

// =============================================================================
// SECURITY: SQL BLACKLIST
// =============================================================================
$SQL_BLACKLIST = [
    'ATTACH',           // No external DB access
    'DETACH',           // No DB detachment
    'LOAD_EXTENSION',   // No extensions
    'VACUUM INTO',      // No file export
    '.import',          // No file import
    '.output',          // No file output
    '.read',            // No file read
    '.shell',           // No shell access
    '.system',          // No system commands
    'file:',            // No file URIs
    'readfile',         // No file reading
    'writefile',        // No file writing
];

// Safe PRAGMA commands (whitelist)
$SAFE_PRAGMAS = [
    'table_info',
    'table_list',
    'index_list',
    'index_info',
    'foreign_key_list',
    'database_list',
    'encoding',
    'max_page_count',
    'page_count',
    'page_size',
    'schema_version',
    'user_version',
];

// =============================================================================
// MAIN EXECUTION
// =============================================================================
$data = json_decode(file_get_contents("php://input"));

if (empty($data->sql)) {
    http_response_code(400);
    echo json_encode(["error" => "SQL query is required"]);
    exit();
}

$sql = $data->sql;
$snippetId = isset($data->snippet_id) ? $data->snippet_id : null;
$action = isset($data->action) ? $data->action : 'execute';
$currentUserId = getCurrentUserId();

// Handle different actions
switch ($action) {
    case 'execute':
        executeSql($sql, $snippetId, $currentUserId);
        break;
    case 'reset':
        resetDatabase($snippetId, $currentUserId);
        break;
    case 'schema':
        getSchema($snippetId, $currentUserId);
        break;
    default:
        http_response_code(400);
        echo json_encode(["error" => "Invalid action"]);
}

// =============================================================================
// EXECUTE SQL
// =============================================================================
function executeSql($sql, $snippetId, $userId)
{
    global $SQL_BLACKLIST, $SAFE_PRAGMAS;

    // Security check
    $securityResult = checkSqlSecurity($sql, $SQL_BLACKLIST, $SAFE_PRAGMAS);
    if ($securityResult !== true) {
        http_response_code(400);
        echo json_encode([
            "error" => "Security violation",
            "details" => $securityResult
        ]);
        return;
    }

    // Get or create database
    $dbPath = getDbPath($snippetId, $userId);

    if (!$dbPath) {
        http_response_code(400);
        echo json_encode(["error" => "Could not create/access database"]);
        return;
    }

    try {
        $db = new SQLite3($dbPath);
        $db->busyTimeout(QUERY_TIMEOUT * 1000);

        // Set safety pragmas
        $db->exec('PRAGMA foreign_keys = ON');
        $db->exec('PRAGMA max_page_count = 1280'); // ~5MB limit

        // Split into statements
        $statements = splitSqlStatements($sql);

        if (count($statements) > MAX_STATEMENTS) {
            echo json_encode([
                "error" => "Too many statements",
                "details" => "Maximum " . MAX_STATEMENTS . " statements allowed per execution"
            ]);
            $db->close();
            return;
        }

        $results = [];
        $totalRowsAffected = 0;
        $startTime = microtime(true);

        foreach ($statements as $index => $stmt) {
            $stmt = trim($stmt);
            if (empty($stmt)) continue;

            $stmtResult = executeStatement($db, $stmt, $index + 1);
            $results[] = $stmtResult;

            if (isset($stmtResult['error'])) {
                // Stop on first error
                break;
            }

            if (isset($stmtResult['rows_affected'])) {
                $totalRowsAffected += $stmtResult['rows_affected'];
            }
        }

        $executionTime = round(microtime(true) - $startTime, 4);
        $dbSize = filesize($dbPath);

        $db->close();

        echo json_encode([
            "success" => true,
            "results" => $results,
            "total_rows_affected" => $totalRowsAffected,
            "execution_time" => $executionTime,
            "db_size" => $dbSize,
            "db_size_formatted" => formatBytes($dbSize)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "error" => "Database error",
            "details" => $e->getMessage()
        ]);
    }
}

// =============================================================================
// EXECUTE SINGLE STATEMENT
// =============================================================================
function executeStatement($db, $sql, $statementNum)
{
    $sqlUpper = strtoupper(trim($sql));
    $isSelect = strpos($sqlUpper, 'SELECT') === 0;
    $isPragma = strpos($sqlUpper, 'PRAGMA') === 0;
    $isShow = strpos($sqlUpper, 'SHOW') === 0 || strpos($sqlUpper, '.TABLES') === 0;

    try {
        if ($isSelect || $isPragma) {
            // Query that returns data
            $result = @$db->query($sql);

            if ($result === false) {
                return [
                    "statement" => $statementNum,
                    "error" => $db->lastErrorMsg(),
                    "sql" => truncateSql($sql)
                ];
            }

            $columns = [];
            $rows = [];
            $rowCount = 0;

            // Get column names
            $numColumns = $result->numColumns();
            for ($i = 0; $i < $numColumns; $i++) {
                $columns[] = $result->columnName($i);
            }

            // Fetch rows
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if ($rowCount >= MAX_ROWS_RETURNED) {
                    break;
                }
                $rows[] = $row;
                $rowCount++;
            }

            $result->finalize();

            return [
                "statement" => $statementNum,
                "type" => "query",
                "columns" => $columns,
                "rows" => $rows,
                "row_count" => count($rows),
                "truncated" => $rowCount >= MAX_ROWS_RETURNED,
                "sql" => truncateSql($sql)
            ];
        } else {
            // Non-query statement (INSERT, UPDATE, DELETE, CREATE, etc.)
            $success = @$db->exec($sql);

            if ($success === false) {
                return [
                    "statement" => $statementNum,
                    "error" => $db->lastErrorMsg(),
                    "sql" => truncateSql($sql)
                ];
            }

            $changes = $db->changes();
            $lastId = $db->lastInsertRowID();

            // Determine statement type
            $type = 'unknown';
            if (strpos($sqlUpper, 'INSERT') === 0) $type = 'INSERT';
            elseif (strpos($sqlUpper, 'UPDATE') === 0) $type = 'UPDATE';
            elseif (strpos($sqlUpper, 'DELETE') === 0) $type = 'DELETE';
            elseif (strpos($sqlUpper, 'CREATE') === 0) $type = 'CREATE';
            elseif (strpos($sqlUpper, 'DROP') === 0) $type = 'DROP';
            elseif (strpos($sqlUpper, 'ALTER') === 0) $type = 'ALTER';

            return [
                "statement" => $statementNum,
                "type" => $type,
                "rows_affected" => $changes,
                "last_insert_id" => ($type === 'INSERT' && $lastId > 0) ? $lastId : null,
                "sql" => truncateSql($sql)
            ];
        }
    } catch (Exception $e) {
        return [
            "statement" => $statementNum,
            "error" => $e->getMessage(),
            "sql" => truncateSql($sql)
        ];
    }
}

// =============================================================================
// SECURITY CHECK
// =============================================================================
function checkSqlSecurity($sql, $blacklist, $safePragmas)
{
    $sqlUpper = strtoupper($sql);

    // Check blacklist
    foreach ($blacklist as $forbidden) {
        if (stripos($sql, $forbidden) !== false) {
            return "Forbidden command detected: " . $forbidden;
        }
    }

    // Check PRAGMA commands
    if (preg_match_all('/PRAGMA\s+(\w+)/i', $sql, $matches)) {
        foreach ($matches[1] as $pragma) {
            $pragmaLower = strtolower($pragma);
            if (!in_array($pragmaLower, $safePragmas)) {
                return "Forbidden PRAGMA: " . $pragma;
            }
        }
    }

    return true;
}

// =============================================================================
// GET/CREATE DATABASE PATH
// =============================================================================
function getDbPath($snippetId, $userId)
{
    // Ensure databases folder exists
    if (!file_exists(DB_FOLDER)) {
        mkdir(DB_FOLDER, 0755, true);
    }

    if ($snippetId) {
        // Permanent database for saved snippet
        $dbPath = DB_FOLDER . 'snippet_' . preg_replace('/[^a-zA-Z0-9]/', '', $snippetId) . '.db';

        // Verify ownership if snippet exists
        if (file_exists($dbPath)) {
            // Check snippet ownership in MySQL
            $database = new Database();
            $db = $database->getConnection();

            if ($db) {
                $stmt = $db->prepare("SELECT author_id FROM code_snippets WHERE id = :id");
                $stmt->bindParam(':id', $snippetId);
                $stmt->execute();
                $snippet = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($snippet && $snippet['author_id'] !== $userId) {
                    // Read-only for non-owners - create temp copy
                    return createReadOnlyDb($dbPath, $userId);
                }
            }
        }

        return $dbPath;
    } else {
        // Temporary database for unsaved work (session-based)
        if (!$userId) {
            $userId = session_id();
        }
        $tempDbPath = DB_FOLDER . 'temp_' . preg_replace('/[^a-zA-Z0-9]/', '', $userId) . '.db';
        return $tempDbPath;
    }
}

// =============================================================================
// CREATE READ-ONLY COPY
// =============================================================================
function createReadOnlyDb($originalPath, $userId)
{
    $readOnlyPath = DB_FOLDER . 'readonly_' . preg_replace('/[^a-zA-Z0-9]/', '', $userId) . '_' . md5($originalPath) . '.db';

    // Copy the original DB for read-only access
    if (!file_exists($readOnlyPath) || filemtime($originalPath) > filemtime($readOnlyPath)) {
        copy($originalPath, $readOnlyPath);
    }

    return $readOnlyPath;
}

// =============================================================================
// RESET DATABASE
// =============================================================================
function resetDatabase($snippetId, $userId)
{
    $dbPath = getDbPath($snippetId, $userId);

    if (!$dbPath) {
        http_response_code(400);
        echo json_encode(["error" => "Could not access database"]);
        return;
    }

    // Check ownership for snippet databases
    if ($snippetId) {
        $database = new Database();
        $db = $database->getConnection();

        if ($db) {
            $stmt = $db->prepare("SELECT author_id FROM code_snippets WHERE id = :id");
            $stmt->bindParam(':id', $snippetId);
            $stmt->execute();
            $snippet = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($snippet && $snippet['author_id'] !== $userId) {
                http_response_code(403);
                echo json_encode(["error" => "You can only reset your own database"]);
                return;
            }
        }
    }

    // Delete the database file
    if (file_exists($dbPath)) {
        unlink($dbPath);
    }

    echo json_encode([
        "success" => true,
        "message" => "Database reset successfully. Start fresh with CREATE TABLE!"
    ]);
}

// =============================================================================
// GET SCHEMA
// =============================================================================
function getSchema($snippetId, $userId)
{
    $dbPath = getDbPath($snippetId, $userId);

    if (!$dbPath || !file_exists($dbPath)) {
        echo json_encode([
            "success" => true,
            "tables" => [],
            "message" => "No database yet. Run CREATE TABLE to start!"
        ]);
        return;
    }

    try {
        $db = new SQLite3($dbPath);

        // Get all tables
        $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");

        $tables = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $tableName = $row['name'];

            // Get columns for each table
            $colResult = $db->query("PRAGMA table_info($tableName)");
            $columns = [];
            while ($col = $colResult->fetchArray(SQLITE3_ASSOC)) {
                $columns[] = [
                    "name" => $col['name'],
                    "type" => $col['type'],
                    "nullable" => !$col['notnull'],
                    "pk" => (bool)$col['pk'],
                    "default" => $col['dflt_value']
                ];
            }

            // Get row count
            $countResult = $db->querySingle("SELECT COUNT(*) FROM $tableName");

            $tables[] = [
                "name" => $tableName,
                "columns" => $columns,
                "row_count" => $countResult
            ];
        }

        $db->close();

        echo json_encode([
            "success" => true,
            "tables" => $tables,
            "db_size" => filesize($dbPath),
            "db_size_formatted" => formatBytes(filesize($dbPath))
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "error" => "Failed to get schema",
            "details" => $e->getMessage()
        ]);
    }
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================
function splitSqlStatements($sql)
{
    // Split by semicolon, but respect quotes
    $statements = [];
    $current = '';
    $inString = false;
    $stringChar = '';

    for ($i = 0; $i < strlen($sql); $i++) {
        $char = $sql[$i];

        if (!$inString && ($char === '"' || $char === "'")) {
            $inString = true;
            $stringChar = $char;
        } elseif ($inString && $char === $stringChar) {
            // Check for escaped quote
            if ($i + 1 < strlen($sql) && $sql[$i + 1] === $stringChar) {
                $current .= $char;
                $i++;
            } else {
                $inString = false;
            }
        }

        if ($char === ';' && !$inString) {
            $statements[] = trim($current);
            $current = '';
        } else {
            $current .= $char;
        }
    }

    if (!empty(trim($current))) {
        $statements[] = trim($current);
    }

    return $statements;
}

function truncateSql($sql, $maxLen = 100)
{
    $sql = preg_replace('/\s+/', ' ', trim($sql));
    if (strlen($sql) > $maxLen) {
        return substr($sql, 0, $maxLen) . '...';
    }
    return $sql;
}

function formatBytes($bytes)
{
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' bytes';
}

// =============================================================================
// CLEANUP OLD TEMP DATABASES (called periodically)
// =============================================================================
function cleanupTempDatabases($maxAge = 86400) // 24 hours
{
    $files = glob(DB_FOLDER . 'temp_*.db');
    $now = time();

    foreach ($files as $file) {
        if (($now - filemtime($file)) > $maxAge) {
            unlink($file);
        }
    }

    // Also clean old read-only copies
    $readonlyFiles = glob(DB_FOLDER . 'readonly_*.db');
    foreach ($readonlyFiles as $file) {
        if (($now - filemtime($file)) > 3600) { // 1 hour
            unlink($file);
        }
    }
}

// Run cleanup 1% of the time
if (rand(1, 100) === 1) {
    cleanupTempDatabases();
}
