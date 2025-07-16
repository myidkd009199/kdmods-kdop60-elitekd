<?php
// Debug login script with detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed', 'debug' => 'Only POST requests allowed']);
    exit();
}

try {
    // Test includes
    echo json_encode(['debug' => 'Starting includes...']);
    
    require_once __DIR__ . '/config/config.php';
    echo json_encode(['debug' => 'Config loaded']);
    
    require_once __DIR__ . '/config/database.php';
    echo json_encode(['debug' => 'Database class loaded']);
    
    require_once __DIR__ . '/classes/User.php';
    echo json_encode(['debug' => 'User class loaded']);
    
    // Test database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed', 'debug' => 'PDO connection is null']);
        exit();
    }
    
    // Check if session started
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'CSRF token missing', 'debug' => [
            'POST_token' => isset($_POST['csrf_token']) ? 'present' : 'missing',
            'SESSION_token' => isset($_SESSION['csrf_token']) ? 'present' : 'missing'
        ]]);
        exit();
    }
    
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token', 'debug' => [
            'POST_token' => $_POST['csrf_token'],
            'SESSION_token' => $_SESSION['csrf_token']
        ]]);
        exit();
    }
    
    $user = new User($db);
    $user->username = trim($_POST['username'] ?? '');
    $user->password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($user->username) || empty($user->password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required', 'debug' => [
            'username' => empty($user->username) ? 'empty' : 'provided',
            'password' => empty($user->password) ? 'empty' : 'provided'
        ]]);
        exit();
    }
    
    // Check if user exists
    $query = "SELECT * FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $user->username);
    $stmt->execute();
    $found_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$found_user) {
        echo json_encode(['success' => false, 'message' => 'User not found', 'debug' => 'Username does not exist in database']);
        exit();
    }
    
    // Check password
    if (!password_verify($user->password, $found_user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid password', 'debug' => [
            'provided_password' => $user->password,
            'stored_hash' => $found_user['password'],
            'verify_result' => 'false'
        ]]);
        exit();
    }
    
    // Check if user is active
    if ($found_user['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Account is not active', 'debug' => 'User status: ' . $found_user['status']]);
        exit();
    }
    
    // Success - set session variables
    $_SESSION['user_id'] = $found_user['id'];
    $_SESSION['username'] = $found_user['username'];
    $_SESSION['email'] = $found_user['email'];
    $_SESSION['github_repo'] = $found_user['github_repo'];
    $_SESSION['role'] = $found_user['role'];
    $_SESSION['logged_in'] = true;
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => $found_user['role'] === 'admin' ? 'admin/dashboard.php' : 'dashboard.php',
        'debug' => 'All checks passed, session variables set'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage(), 'debug' => [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]]);
}
?>