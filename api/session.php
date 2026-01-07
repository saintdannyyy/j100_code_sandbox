<?php
// =============================================================================
// api/session.php - Session Management for J100 Coding Sandbox
// =============================================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORS headers for API access
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Redirect URL for unauthorized users
define('REDIRECT_URL', 'https://j100coders.org/coder/codelab.php');

/**
 * Initialize or validate user session
 * Call this at the start of index.php
 */
function initSession($userId = null)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // If user ID is provided (from URL), store it in session
    if ($userId !== null && !empty($userId) && $userId !== 'anonymous') {
        $_SESSION['user_id'] = $userId;
        $_SESSION['session_start'] = time();
        $_SESSION['last_activity'] = time();
        return true;
    }

    // Check if session already has a valid user
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = time();
        return true;
    }

    return false;
}

/**
 * Get current user ID from session
 */
function getCurrentUserId()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['user_id'] ?? null;
}

/**
 * Check if user is authenticated
 */
function isAuthenticated()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * End user session
 */
function endSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Clear session data
    $_SESSION = array();

    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy session
    session_destroy();

    return true;
}

/**
 * Get session info
 */
function getSessionInfo()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isAuthenticated()) {
        return null;
    }

    return [
        'user_id' => $_SESSION['user_id'],
        'session_start' => $_SESSION['session_start'] ?? null,
        'last_activity' => $_SESSION['last_activity'] ?? null,
        'session_duration' => isset($_SESSION['session_start'])
            ? time() - $_SESSION['session_start']
            : null
    ];
}

// =============================================================================
// API Endpoint Handling (when called directly)
// =============================================================================

if (basename($_SERVER['PHP_SELF']) === 'session.php') {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Get session status
            if (isAuthenticated()) {
                echo json_encode([
                    'authenticated' => true,
                    'session' => getSessionInfo()
                ]);
            } else {
                echo json_encode([
                    'authenticated' => false,
                    'redirect' => REDIRECT_URL
                ]);
            }
            break;

        case 'POST':
            // Initialize session with user ID
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = $data['user_id'] ?? null;

            if ($userId && initSession($userId)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Session initialized',
                    'session' => getSessionInfo()
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid user ID',
                    'redirect' => REDIRECT_URL
                ]);
            }
            break;

        case 'DELETE':
            // End session
            endSession();
            echo json_encode([
                'success' => true,
                'message' => 'Session ended',
                'redirect' => REDIRECT_URL
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}
