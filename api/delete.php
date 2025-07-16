<?php
require_once '../config/config.php';
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../classes/FileManager.php';

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

if (!isset($input['file_id'])) {
    echo json_encode(['success' => false, 'message' => 'File ID required']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get admin GitHub settings
$query = "SELECT github_token, github_username FROM admin_settings ORDER BY id DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$admin_settings = $stmt->fetch(PDO::FETCH_ASSOC);

$fileManager = new FileManager($db, $admin_settings['github_token'], $admin_settings['github_username']);
$result = $fileManager->deleteFile($input['file_id'], getUserId(), getGithubRepo());

echo json_encode($result);
?>