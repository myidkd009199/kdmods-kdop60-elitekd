<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/GitHubAPI.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

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

// Get admin GitHub settings
$query = "SELECT github_token, github_username FROM admin_settings ORDER BY id DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$admin_settings = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin_settings) {
    echo json_encode(['success' => false, 'message' => 'GitHub settings not configured']);
    exit();
}

$github = new GitHubAPI($admin_settings['github_token'], $admin_settings['github_username']);
$repo_name = getGithubRepo();
$user_id = getUserId();

// Delete GitHub repository
$github_result = $github->deleteRepository($repo_name);

if ($github_result['code'] === 204) {
    // Delete local files
    $upload_dir = UPLOAD_DIR . $user_id . '/';
    if (is_dir($upload_dir)) {
        $files = glob($upload_dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($upload_dir);
    }
    
    // Delete database records
    $query = "DELETE FROM files WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $query = "DELETE FROM github_repos WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Repository and all files deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete GitHub repository']);
}
?>