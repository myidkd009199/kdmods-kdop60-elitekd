<?php
// GitHub API Test Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>GitHub API Test</h2>";

// Test 1: Check GitHub settings
echo "<h3>1. GitHub Settings Test</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT github_token, github_username FROM admin_settings ORDER BY id DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $admin_settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin_settings) {
        echo "✅ GitHub settings found<br>";
        echo "Username: " . htmlspecialchars($admin_settings['github_username']) . "<br>";
        echo "Token: " . (strlen($admin_settings['github_token']) > 0 ? 'Present (' . strlen($admin_settings['github_token']) . ' chars)' : 'Missing') . "<br>";
        
        if (empty($admin_settings['github_token'])) {
            echo "❌ <strong>GitHub token is empty! Please configure in admin panel.</strong><br>";
            echo "<a href='admin/dashboard.php'>Go to Admin Panel</a><br>";
        }
        if (empty($admin_settings['github_username'])) {
            echo "❌ <strong>GitHub username is empty! Please configure in admin panel.</strong><br>";
        }
    } else {
        echo "❌ No GitHub settings found<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

if (!$admin_settings || empty($admin_settings['github_token']) || empty($admin_settings['github_username'])) {
    echo "<hr><p><strong>Cannot proceed with API tests - GitHub settings not configured.</strong></p>";
    echo "<p><a href='index.php'>← Back to Login</a></p>";
    exit();
}

// Test 2: Test GitHub API Authentication
echo "<h3>2. GitHub API Authentication Test</h3>";
try {
    require_once 'classes/GitHubAPI.php';
    $github = new GitHubAPI($admin_settings['github_token'], $admin_settings['github_username']);
    
    // Test API access by getting user info
    $user_url = 'https://api.github.com/user';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $user_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $admin_settings['github_token'],
        'User-Agent: FileManager-App/1.0',
        'Accept: application/vnd.github+json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $userData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($userData['login'])) {
        echo "✅ GitHub API authentication successful<br>";
        echo "Authenticated as: " . htmlspecialchars($userData['login']) . "<br>";
        echo "Account type: " . htmlspecialchars($userData['type'] ?? 'Unknown') . "<br>";
        
        if (isset($userData['permissions'])) {
            echo "Repository permissions: " . json_encode($userData['permissions']) . "<br>";
        }
    } else {
        echo "❌ GitHub API authentication failed<br>";
        echo "HTTP Code: " . $httpCode . "<br>";
        echo "Response: " . htmlspecialchars($response) . "<br>";
        
        if ($httpCode === 401) {
            echo "<strong>Token is invalid or expired. Please generate a new token with 'repo' permissions.</strong><br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 3: Test Repository Creation
echo "<h3>3. Repository Creation Test</h3>";
if ($httpCode === 200) {
    $test_repo_name = 'filemanager-test-' . date('Y-m-d-H-i-s');
    echo "Testing repository creation: <strong>" . $test_repo_name . "</strong><br>";
    
    try {
        $repo_result = $github->createRepository($test_repo_name, 'Test repository for FileManager Pro');
        
        echo "API Response:<br>";
        echo "<pre>" . json_encode($repo_result, JSON_PRETTY_PRINT) . "</pre>";
        
        if (isset($repo_result['error']) && $repo_result['error']) {
            echo "❌ Repository creation failed<br>";
            echo "Error: " . htmlspecialchars($repo_result['message']) . "<br>";
            echo "HTTP Code: " . $repo_result['code'] . "<br>";
            
            if (isset($repo_result['data']['errors'])) {
                echo "Detailed errors:<br>";
                foreach ($repo_result['data']['errors'] as $error) {
                    echo "- " . htmlspecialchars($error['message'] ?? 'Unknown error') . "<br>";
                }
            }
        } elseif (isset($repo_result['html_url'])) {
            echo "✅ Repository created successfully!<br>";
            echo "Repository URL: <a href='" . htmlspecialchars($repo_result['html_url']) . "' target='_blank'>" . htmlspecialchars($repo_result['html_url']) . "</a><br>";
            echo "Clone URL: " . htmlspecialchars($repo_result['clone_url']) . "<br>";
            
            // Test cleanup - delete the test repository
            echo "<br><strong>Cleaning up test repository...</strong><br>";
            $delete_result = $github->deleteRepository($test_repo_name);
            if ($delete_result['code'] === 204) {
                echo "✅ Test repository deleted successfully<br>";
            } else {
                echo "⚠️ Could not delete test repository (you may need to delete it manually)<br>";
            }
        } else {
            echo "❌ Unexpected response format<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "<br>";
    }
} else {
    echo "Skipping repository test due to authentication failure.<br>";
}

// Test 4: Token Permissions Check
echo "<h3>4. Token Permissions Analysis</h3>";
echo "<p>For repository creation, your GitHub token needs these scopes:</p>";
echo "<ul>";
echo "<li><strong>repo</strong> - Full control of private repositories</li>";
echo "<li><strong>public_repo</strong> - Access to public repositories (minimum)</li>";
echo "</ul>";

echo "<h4>How to create a proper GitHub token:</h4>";
echo "<ol>";
echo "<li>Go to GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)</li>";
echo "<li>Click 'Generate new token (classic)'</li>";
echo "<li>Select scopes: <strong>repo</strong> (this includes public_repo)</li>";
echo "<li>Copy the token and update it in the admin panel</li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>If authentication passes but repository creation fails, check the error details above.</strong></p>";
echo "<p><a href='admin/dashboard.php'>Update GitHub Settings</a> | <a href='index.php'>← Back to Login</a></p>";
?>