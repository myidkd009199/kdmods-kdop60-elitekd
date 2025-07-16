<?php
// Installation script for FileManager Pro
require_once 'config/database.php';

// Check if already installed
if (file_exists('.installed')) {
    echo "<h2>FileManager Pro is already installed!</h2>";
    echo "<p><a href='index.php'>Go to Login Page</a></p>";
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            throw new Exception("Database connection failed");
        }
        
        // Read and execute SQL schema
        $sql = file_get_contents('database/schema.sql');
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $db->exec($statement);
            }
        }
        
        // Create uploads directory
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        
        // Create .htaccess for uploads directory
        file_put_contents('uploads/.htaccess', "Options -Indexes\nDeny from all");
        
        // Mark as installed
        file_put_contents('.installed', date('Y-m-d H:i:s'));
        
        $success = "Installation completed successfully!";
        
    } catch (Exception $e) {
        $error = "Installation failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FileManager Pro - Installation</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .install-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .btn-install {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
        }
        .btn-install:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="install-container">
            <div class="text-center mb-4">
                <h1><i class="fas fa-cloud"></i> FileManager Pro</h1>
                <p class="text-muted">Installation Wizard</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
                <div class="text-center">
                    <a href="index.php" class="btn btn-install">
                        <i class="fas fa-arrow-right"></i> Go to Login Page
                    </a>
                </div>
            <?php else: ?>
                <div class="mb-4">
                    <h4>Pre-Installation Checklist</h4>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            PHP Version (7.4+)
                            <span class="badge bg-<?php echo version_compare(PHP_VERSION, '7.4.0') >= 0 ? 'success' : 'danger'; ?>">
                                <?php echo PHP_VERSION; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            MySQL/PDO Extension
                            <span class="badge bg-<?php echo extension_loaded('pdo_mysql') ? 'success' : 'danger'; ?>">
                                <?php echo extension_loaded('pdo_mysql') ? 'Available' : 'Missing'; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            cURL Extension
                            <span class="badge bg-<?php echo extension_loaded('curl') ? 'success' : 'danger'; ?>">
                                <?php echo extension_loaded('curl') ? 'Available' : 'Missing'; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Write Permissions
                            <span class="badge bg-<?php echo is_writable('.') ? 'success' : 'danger'; ?>">
                                <?php echo is_writable('.') ? 'OK' : 'No Write Access'; ?>
                            </span>
                        </li>
                    </ul>
                </div>
                
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Before Installation</h5>
                    <ol>
                        <li>Create a MySQL database for FileManager Pro</li>
                        <li>Update database credentials in <code>config/database.php</code></li>
                        <li>Get your GitHub Personal Access Token</li>
                        <li>Make sure all requirements above are met</li>
                    </ol>
                </div>
                
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle"></i> Default Admin Account</h5>
                    <p><strong>Username:</strong> admin<br>
                    <strong>Password:</strong> admin123<br>
                    <em>Please change this password after first login!</em></p>
                </div>
                
                <form method="POST" class="text-center">
                    <button type="submit" class="btn btn-install">
                        <i class="fas fa-download"></i> Install FileManager Pro
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>