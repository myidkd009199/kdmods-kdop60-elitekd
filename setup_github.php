<?php
// Quick GitHub Settings Setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>GitHub Settings Setup</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $github_token = trim($_POST['github_token'] ?? '');
    $github_username = trim($_POST['github_username'] ?? '');
    
    if (empty($github_username)) {
        echo "<div style='color: red;'>❌ GitHub username is required</div>";
    } else {
        // If token is empty, keep the existing token
        if (empty($github_token)) {
            try {
                $query = "SELECT github_token FROM admin_settings ORDER BY id DESC LIMIT 1";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                $github_token = $existing['github_token'] ?? '';
                
                if (empty($github_token)) {
                    echo "<div style='color: red;'>❌ GitHub token is required for first-time setup</div>";
                    echo "<p>Please enter a GitHub token.</p>";
                } else {
                    echo "<div style='color: blue;'>ℹ️ Using existing GitHub token</div>";
                }
            } catch (Exception $e) {
                echo "<div style='color: red;'>❌ Error checking existing token: " . $e->getMessage() . "</div>";
            }
        }
        
        if (!empty($github_token)) {
        try {
            require_once 'config/database.php';
            $database = new Database();
            $db = $database->getConnection();
            
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
                echo "<div style='color: green; font-weight: bold;'>✅ GitHub settings updated successfully!</div>";
                echo "<p>Token: " . substr($github_token, 0, 8) . "... (" . strlen($github_token) . " characters)</p>";
                echo "<p>Username: " . htmlspecialchars($github_username) . "</p>";
                echo "<hr>";
                echo "<p><a href='test_github.php'>Test GitHub API</a> | <a href='index.php'>← Back to Login</a></p>";
            } else {
                echo "<div style='color: red;'>❌ Failed to update GitHub settings</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ Database error: " . $e->getMessage() . "</div>";
        }
        }
    }
} else {
    // Show current settings
    try {
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT github_token, github_username FROM admin_settings ORDER BY id DESC LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $current_settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($current_settings) {
            echo "<p><strong>Current Settings:</strong></p>";
            echo "<p>Username: " . htmlspecialchars($current_settings['github_username']) . "</p>";
            echo "<p>Token: " . (strlen($current_settings['github_token']) > 0 ? 'Present (' . strlen($current_settings['github_token']) . ' chars)' : 'Not set') . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: orange;'>Could not load current settings: " . $e->getMessage() . "</p>";
    }
?>

<div style="background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
    <h3>How to get GitHub Token:</h3>
    <ol>
        <li>Go to <a href="https://github.com/settings/tokens" target="_blank">GitHub Personal Access Tokens</a></li>
        <li>Click "Generate new token (classic)"</li>
        <li>Name it "FileManager Pro"</li>
        <li>Select scope: <strong>repo</strong> (full control of repositories)</li>
        <li>Click "Generate token"</li>
        <li>Copy the token and paste it below</li>
    </ol>
</div>

<form method="POST" style="max-width: 500px;">
    <div style="margin-bottom: 15px;">
        <label for="github_username" style="display: block; font-weight: bold;">GitHub Username:</label>
        <input type="text" id="github_username" name="github_username" 
               placeholder="your-github-username" 
               value="<?php echo htmlspecialchars($current_settings['github_username'] ?? ''); ?>"
               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="github_token" style="display: block; font-weight: bold;">GitHub Token:</label>
        <input type="password" id="github_token" name="github_token" 
               placeholder="ghp_xxxxxxxxxxxxxxxxxxxx"
               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        <small style="color: #666;">Leave blank to keep current token</small>
    </div>
    
    <button type="submit" style="background: #0066cc; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
        Update GitHub Settings
    </button>
</form>

<hr>
<p><a href="test_github.php">Test GitHub API</a> | <a href="admin/dashboard.php">Admin Panel</a> | <a href="index.php">← Back to Login</a></p>

<?php
}
?>