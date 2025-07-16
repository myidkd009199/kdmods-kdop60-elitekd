<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';

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
$user->password = $_POST['password'];

// Validation
if (empty($user->username) || empty($user->password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit();
}

// Attempt login
if ($user->login()) {
    // Set session variables
    $_SESSION['user_id'] = $user->id;
    $_SESSION['username'] = $user->username;
    $_SESSION['email'] = $user->email;
    $_SESSION['github_repo'] = $user->github_repo;
    $_SESSION['role'] = $user->role;
    $_SESSION['logged_in'] = true;
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => $user->role === 'admin' ? 'admin/dashboard.php' : 'dashboard.php'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
}
?>