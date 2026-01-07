<?php
// =============================================================================
// api/snippets.php - Main API Endpoint for CodeBin
// =============================================================================

// Include session management
require_once 'session.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS");
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
} elseif ($request_method === 'DELETE') {
    if ($snippet_id !== null && !empty($snippet_id)) {
        deleteSnippet($db, $snippet_id);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Snippet ID required to take action"]);
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

    if (empty($data->title) || empty($data->code)) {
        http_response_code(400);
        echo json_encode(["error" => "Title and code are required"]);
        return;
    }

    // Get user ID from session first, then fall back to request data
    $author_id = getCurrentUserId();
    if (!$author_id && isset($data->author_id)) {
        $author_id = $data->author_id;
    }

    if (!$author_id || $author_id === 'anonymous') {
        http_response_code(401);
        echo json_encode([
            "error" => "Authentication required",
            "redirect" => REDIRECT_URL
        ]);
        return;
    }

    $id = generateUniqueId($db);

    $query = "INSERT INTO code_snippets (id, title, description, language, code, permissions, author_id) 
              VALUES (:id, :title, :description, :language, :code, :permissions, :author_id)";

    $stmt = $db->prepare($query);

    $title = htmlspecialchars(strip_tags($data->title));
    $description = isset($data->description) ? htmlspecialchars(strip_tags($data->description)) : '';
    $language = isset($data->language) ? $data->language : 'plaintext';
    $code = $data->code;
    $permissions = isset($data->permissions) ? $data->permissions : 'public';

    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':language', $language);
    $stmt->bindParam(':code', $code);
    $stmt->bindParam(':permissions', $permissions);
    $stmt->bindParam(':author_id', $author_id);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            "message" => "Snippet created successfully",
            "id" => $id,
            "author_id" => $author_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to create snippet"]);
    }
}

// =============================================================================
// GET SNIPPET BY ID
// =============================================================================
function getSnippet($db, $snippet_id)
{
    $query = "SELECT * FROM code_snippets WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $snippet_id);
    $stmt->execute();

    $snippet = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($snippet) {
        // Check permissions
        $currentUserId = getCurrentUserId();

        if ($snippet['permissions'] === 'private' && $snippet['author_id'] !== $currentUserId) {
            http_response_code(403);
            echo json_encode(["error" => "Access denied - private snippet"]);
            return;
        }

        // Increment views
        incrementViews($db, $snippet_id);

        echo json_encode($snippet);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Snippet not found"]);
    }
}

// =============================================================================
// LIST SNIPPETS (optional - for browse page)
// =============================================================================
function listSnippets($db)
{
    // Get user ID from session first, then query param
    $author_id = getCurrentUserId();
    if (!$author_id && isset($_GET['author'])) {
        $author_id = $_GET['author'];
    }

    if ($author_id) {
        // List user's snippets
        $query = "SELECT id, title, description, language, permissions, created_at, views 
                  FROM code_snippets 
                  WHERE author_id = :author_id 
                  ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':author_id', $author_id);
    } else {
        // List public snippets only
        $query = "SELECT id, title, description, language, author_id, created_at, views 
                  FROM code_snippets 
                  WHERE permissions = 'public' 
                  ORDER BY created_at DESC 
                  LIMIT 50";
        $stmt = $db->prepare($query);
    }

    $stmt->execute();
    $snippets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "snippets" => $snippets,
        "count" => count($snippets),
        "author_id" => $author_id
    ]);
}

// =============================================================================
// DELETE SNIPPET
// =============================================================================
function deleteSnippet($db, $snippet_id)
{
    // Verify ownership
    $currentUserId = getCurrentUserId();

    if (!$currentUserId) {
        http_response_code(401);
        echo json_encode([
            "error" => "Authentication required",
            "redirect" => REDIRECT_URL
        ]);
        return;
    }

    // Check if snippet exists and belongs to user
    $checkQuery = "SELECT author_id FROM code_snippets WHERE id = :id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':id', $snippet_id);
    $checkStmt->execute();
    $snippet = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$snippet) {
        http_response_code(404);
        echo json_encode(["error" => "Snippet not found"]);
        return;
    }

    if ($snippet['author_id'] !== $currentUserId) {
        http_response_code(403);
        echo json_encode(["error" => "You can only delete your own snippets"]);
        return;
    }

    $query = "DELETE FROM code_snippets WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $snippet_id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Snippet deleted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to delete snippet"]);
    }
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================
function generateUniqueId($db)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $length = 8;

    do {
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $characters[random_int(0, strlen($characters) - 1)];
        }

        $query = "SELECT id FROM code_snippets WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    } while ($stmt->fetch());

    return $id;
}

function incrementViews($db, $snippet_id)
{
    $query = "UPDATE code_snippets SET views = views + 1 WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $snippet_id);
    $stmt->execute();
}
