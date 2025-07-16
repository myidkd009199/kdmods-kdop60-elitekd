<?php
// Test script to verify authentication setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Authentication Test</h2>";

// Test 1: Database connection
echo "<h3>1. Database Connection Test</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    if ($db) {
        echo "✅ Database connection successful<br>";
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 2: Admin user exists
echo "<h3>2. Admin User Test</h3>";
try {
    $query = "SELECT username, password FROM users WHERE username = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✅ Admin user found<br>";
        echo "Username: " . $admin['username'] . "<br>";
        
        // Test password verification
        $test_password = 'admin123';
        if (password_verify($test_password, $admin['password'])) {
            echo "✅ Password verification works<br>";
        } else {
            echo "❌ Password verification failed<br>";
            // Create correct hash
            $correct_hash = password_hash($test_password, PASSWORD_DEFAULT);
            echo "Correct hash should be: " . $correct_hash . "<br>";
        }
    } else {
        echo "❌ Admin user not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Query error: " . $e->getMessage() . "<br>";
}

// Test 3: Session functionality
echo "<h3>3. Session Test</h3>";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Sessions working<br>";
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    echo "CSRF Token: " . $_SESSION['csrf_token'] . "<br>";
} else {
    echo "❌ Sessions not working<br>";
}

// Test 4: File includes
echo "<h3>4. File Include Test</h3>";
try {
    require_once 'classes/User.php';
    echo "✅ User class loaded<br>";
    
    require_once 'includes/auth_check.php';
    echo "✅ Auth check functions loaded<br>";
    
} catch (Exception $e) {
    echo "❌ Include error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>If all tests pass, try logging in again. If admin password fails, check the database.</strong></p>";
echo "<p><a href='index.php'>← Back to Login</a></p>";
?>