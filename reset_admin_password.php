<?php
// Script to reset admin password
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Reset Admin Password</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'] ?? 'admin123';
    
    try {
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update admin password
        $query = "UPDATE users SET password = :password WHERE username = 'admin'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':password', $hashed_password);
        
        if ($stmt->execute()) {
            echo "<div style='color: green; font-weight: bold;'>✅ Admin password updated successfully!</div>";
            echo "<p>New password: <strong>" . htmlspecialchars($new_password) . "</strong></p>";
            echo "<p><a href='index.php'>← Go to Login</a></p>";
        } else {
            echo "<div style='color: red;'>❌ Failed to update password</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>";
    }
} else {
?>

<form method="POST">
    <p>Enter new admin password (leave blank for default 'admin123'):</p>
    <input type="text" name="password" placeholder="admin123" style="padding: 10px; width: 200px;">
    <br><br>
    <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px;">
        Reset Password
    </button>
</form>

<hr>
<p><a href="test_auth.php">Run Authentication Test</a></p>
<p><a href="index.php">← Back to Login</a></p>

<?php
}
?>