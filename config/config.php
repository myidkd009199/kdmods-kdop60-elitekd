<?php
// GitHub Configuration
define('GITHUB_TOKEN', 'your_github_token_here');
define('GITHUB_USERNAME', 'your_github_username_here');
define('GITHUB_API_URL', 'https://api.github.com');

// Application Configuration
define('APP_NAME', 'FileManager Pro');
define('APP_URL', 'http://localhost');
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB

// Security
define('JWT_SECRET', 'your_secret_key_here');
define('PASSWORD_SALT', 'your_salt_here');

// Session Configuration
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>