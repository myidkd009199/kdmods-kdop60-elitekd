<?php
require_once '../config/config.php';
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../classes/FileManager.php';

if (!isLoggedIn()) {
    http_response_code(401);
    exit('Unauthorized');
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('File ID required');
}

$database = new Database();
$db = $database->getConnection();

// Get admin GitHub settings
$query = "SELECT github_token, github_username FROM admin_settings ORDER BY id DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$admin_settings = $stmt->fetch(PDO::FETCH_ASSOC);

$fileManager = new FileManager($db, $admin_settings['github_token'], $admin_settings['github_username']);
$file = $fileManager->downloadFile($_GET['id'], getUserId());

if (!$file) {
    http_response_code(404);
    exit('File not found');
}

// Set headers for download
header('Content-Description: File Transfer');
header('Content-Type: ' . $file['mime_type']);
header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
header('Content-Length: ' . $file['file_size']);
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Clear output buffer
if (ob_get_level()) {
    ob_end_clean();
}

// Output file content
readfile($file['file_path']);
exit();
?>