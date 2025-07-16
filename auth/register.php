<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/GitHubAPI.php';

header('Content-Type: application/json');

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

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$user->username = trim($_POST['username']);
$user->email = trim($_POST['email']);
$user->password = $_POST['password'];

// Validation
if (empty($user->username) || empty($user->email) || empty($user->password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

if (strlen($user->password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit();
}

if (!preg_match('/^[a-zA-Z0-9_-]+$/', $user->username)) {
    echo json_encode(['success' => false, 'message' => 'Username can only contain letters, numbers, underscore and dash']);
    exit();
}

// Check if username or email already exists
if ($user->usernameExists()) {
    echo json_encode(['success' => false, 'message' => 'Username already exists']);
    exit();
}

if ($user->emailExists()) {
    echo json_encode(['success' => false, 'message' => 'Email already exists']);
    exit();
}

// Register user
if ($user->register()) {
    // Get admin GitHub settings
    $query = "SELECT github_token, github_username FROM admin_settings ORDER BY id DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $admin_settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin_settings) {
        // Create GitHub repository
        $github = new GitHubAPI($admin_settings['github_token'], $admin_settings['github_username']);
        $repo_name = $user->github_repo;
        
        $repo_result = $github->createRepository($repo_name, 'File Manager Repository for ' . $user->username);
        
        if ($repo_result) {
            // Save repo info to database
            $query = "INSERT INTO github_repos (user_id, repo_name, repo_url, release_url) 
                     VALUES (:user_id, :repo_name, :repo_url, :release_url)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user->id);
            $stmt->bindParam(':repo_name', $repo_name);
            $stmt->bindParam(':repo_url', $repo_result['html_url']);
            $stmt->bindParam(':release_url', $repo_result['html_url'] . '/releases');
            $stmt->execute();
        }
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful! GitHub repository created: ' . $user->github_repo
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed']);
}
?>