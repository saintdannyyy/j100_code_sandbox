<?php
// =============================================================================
// api/snippets.php - Main API Endpoint for CodeBin
// =============================================================================

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

$database = new Database();
$db = $database->getConnection();

// Check database connection
if ($db === null) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$request_method = $_SERVER["REQUEST_METHOD"];

// Get snippet ID from multiple possible sources
$snippet_id = null;

// 1. Check query parameter: ?id=abc123
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $snippet_id = $_GET['id'];
}

// 2. Check URL path: /api/snippets/abc123
if ($snippet_id === null) {
    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH);

    // Match /snippets/{id} or /api/snippets/{id}
    if (preg_match('/\/snippets\/([a-zA-Z0-9]+)/', $path, $matches)) {
        $snippet_id = $matches[1];
    }
}

// Route handling
if ($request_method === 'POST') {
    createSnippet($db);
} elseif ($request_method === 'GET') {
    if ($snippet_id !== null && !empty($snippet_id)) {
        getSnippet($db, $snippet_id);
    } else {
        listSnippets($db);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}

// =============================================================================
// CREATE SNIPPET
// =============================================================================
function createSnippet($db)
{
    $data = json_decode(file_get_contents("php://input"));

    // Validation
    if (empty($data->title) || empty($data->code) || empty($data->language)) {
        http_response_code(400);
        echo json_encode(["error" => "Title, code, and language are required"]);
        return;
    }

    // Generate unique ID
    $snippet_id = generateUniqueId($db);

    // Get current timestamp
    $created_at = date('Y-m-d H:i:s');

    // Default values
    $description = isset($data->description) ? $data->description : '';
    $permissions = isset($data->permissions) ? $data->permissions : 'public';
    $author_id = isset($data->author_id) ? $data->author_id : 'anonymous';

    // Validate permissions
    if (!in_array($permissions, ['public', 'unlisted', 'private'])) {
        $permissions = 'public';
    }

    try {
        $query = "INSERT INTO code_snippets 
                  (id, title, description, language, code, permissions, author_id, created_at, updated_at) 
                  VALUES 
                  (:id, :title, :description, :language, :code, :permissions, :author_id, :created_at, :updated_at)";

        $stmt = $db->prepare($query);

        $stmt->bindParam(":id", $snippet_id);
        $stmt->bindParam(":title", $data->title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":language", $data->language);
        $stmt->bindParam(":code", $data->code);
        $stmt->bindParam(":permissions", $permissions);
        $stmt->bindParam(":author_id", $author_id);
        $stmt->bindParam(":created_at", $created_at);
        $stmt->bindParam(":updated_at", $created_at);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode([
                "message" => "Snippet created successfully",
                "id" => $snippet_id,
                "title" => $data->title,
                "language" => $data->language,
                "permissions" => $permissions,
                "created_at" => $created_at
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Unable to create snippet"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}

// =============================================================================
// GET SNIPPET BY ID
// =============================================================================
function getSnippet($db, $snippet_id)
{
    // Sanitize input - only allow alphanumeric
    $snippet_id = preg_replace('/[^a-zA-Z0-9]/', '', $snippet_id);

    try {
        $query = "SELECT * FROM code_snippets WHERE id = :id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $snippet_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Increment view count
            incrementViews($db, $snippet_id);

            http_response_code(200);
            echo json_encode([
                "id" => $row['id'],
                "title" => $row['title'],
                "description" => $row['description'],
                "language" => $row['language'],
                "code" => $row['code'],
                "permissions" => $row['permissions'],
                "author_id" => $row['author_id'],
                "created_at" => $row['created_at'],
                "updated_at" => $row['updated_at'],
                "views" => (int)$row['views'] + 1
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Snippet not found"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}

// =============================================================================
// LIST SNIPPETS (optional - for browse page)
// =============================================================================
function listSnippets($db)
{
    try {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $offset = ($page - 1) * $limit;

        // Only show public snippets in listing
        $query = "SELECT id, title, language, author_id, created_at, views 
                  FROM code_snippets 
                  WHERE permissions = 'public' 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        $snippets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM code_snippets WHERE permissions = 'public'";
        $countStmt = $db->prepare($countQuery);
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        http_response_code(200);
        echo json_encode([
            "snippets" => $snippets,
            "total" => $total,
            "page" => $page,
            "limit" => $limit,
            "total_pages" => ceil($total / $limit)
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================
function generateUniqueId($db)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $id_length = 8;

    do {
        $id = '';
        for ($i = 0; $i < $id_length; $i++) {
            $id .= $characters[random_int(0, strlen($characters) - 1)];
        }

        // Check if ID exists
        $query = "SELECT id FROM code_snippets WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
    } while ($stmt->rowCount() > 0);

    return $id;
}

function incrementViews($db, $snippet_id)
{
    try {
        $query = "UPDATE code_snippets SET views = views + 1 WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $snippet_id);
        $stmt->execute();
    } catch (PDOException $e) {
        // Silently fail - don't break the main request
        error_log("Error incrementing views: " . $e->getMessage());
    }
}
