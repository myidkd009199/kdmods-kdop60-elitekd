<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';
require_once '../../classes/User.php';
require_once '../../classes/GitHubAPI.php';

header('Content-Type: application/json');

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// CSRF Protection
if (!isset($input['csrf_token']) || $input['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

switch ($input['action']) {
    case 'update_status':
        if (!isset($input['user_id']) || !isset($input['status'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit();
        }
        
        if ($user->updateStatus($input['user_id'], $input['status'])) {
            $status_text = $input['status'] === 'banned' ? 'banned' : 'unbanned';
            echo json_encode(['success' => true, 'message' => "User has been {$status_text} successfully"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user status']);
        }
        break;
        
    case 'delete_user':
        if (!isset($input['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User ID required']);
            exit();
        }
        
        // Get user info first
        $query = "SELECT username, github_repo FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $input['user_id']);
        $stmt->execute();
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user_data) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit();
        }
        
        // Get admin GitHub settings
        $query = "SELECT github_token, github_username FROM admin_settings ORDER BY id DESC LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $admin_settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete GitHub repository if settings exist
        if ($admin_settings && $user_data['github_repo']) {
            $github = new GitHubAPI($admin_settings['github_token'], $admin_settings['github_username']);
            $github->deleteRepository($user_data['github_repo']);
        }
        
        // Delete local files
        $upload_dir = UPLOAD_DIR . $input['user_id'] . '/';
        if (is_dir($upload_dir)) {
            $files = glob($upload_dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($upload_dir);
        }
        
        // Delete user from database (cascade will handle related records)
        if ($user->deleteUser($input['user_id'])) {
            echo json_encode(['success' => true, 'message' => 'User and all associated data deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>