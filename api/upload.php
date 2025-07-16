<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/FileManager.php';

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

// CSRF Protection
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
    echo json_encode(['success' => false, 'message' => 'No files selected']);
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

$fileManager = new FileManager($db, $admin_settings['github_token'], $admin_settings['github_username']);
$user_id = getUserId();
$repo_name = getGithubRepo();

$uploaded_files = [];
$failed_files = [];

// Process multiple files
$files = $_FILES['files'];
$file_count = count($files['name']);

for ($i = 0; $i < $file_count; $i++) {
    $file = [
        'name' => $files['name'][$i],
        'type' => $files['type'][$i],
        'tmp_name' => $files['tmp_name'][$i],
        'error' => $files['error'][$i],
        'size' => $files['size'][$i]
    ];

    $result = $fileManager->uploadFile($user_id, $repo_name, $file);
    
    if ($result['success']) {
        $uploaded_files[] = $file['name'];
    } else {
        $failed_files[] = $file['name'] . ' (' . $result['message'] . ')';
    }
}

$message = '';
if (!empty($uploaded_files)) {
    $message .= count($uploaded_files) . ' file(s) uploaded successfully';
}
if (!empty($failed_files)) {
    if (!empty($uploaded_files)) $message .= '. ';
    $message .= count($failed_files) . ' file(s) failed to upload';
}

echo json_encode([
    'success' => !empty($uploaded_files),
    'message' => $message,
    'uploaded' => $uploaded_files,
    'failed' => $failed_files
]);
?>