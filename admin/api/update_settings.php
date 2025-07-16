<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_POST['github_token']) || !isset($_POST['github_username'])) {
    echo json_encode(['success' => false, 'message' => 'GitHub token and username are required']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$github_token = trim($_POST['github_token']);
$github_username = trim($_POST['github_username']);

// Validate inputs
if (empty($github_username)) {
    echo json_encode(['success' => false, 'message' => 'GitHub username cannot be empty']);
    exit();
}

// If token is empty, keep the existing token
if (empty($github_token)) {
    $query = "SELECT github_token FROM admin_settings ORDER BY id DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    $github_token = $existing['github_token'] ?? '';
    
    if (empty($github_token)) {
        echo json_encode(['success' => false, 'message' => 'GitHub token is required for first-time setup']);
        exit();
    }
}

try {
    // Check if settings exist
    $query = "SELECT id FROM admin_settings LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exists) {
        // Update existing settings
        $query = "UPDATE admin_settings SET github_token = :token, github_username = :username WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':token', $github_token);
        $stmt->bindParam(':username', $github_username);
        $stmt->bindParam(':id', $exists['id']);
    } else {
        // Insert new settings
        $query = "INSERT INTO admin_settings (github_token, github_username) VALUES (:token, :username)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':token', $github_token);
        $stmt->bindParam(':username', $github_username);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'GitHub settings updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update settings']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>