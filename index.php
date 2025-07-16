<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .auth-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .logo p {
            color: #666;
            margin: 0;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .nav-tabs {
            border-bottom: none;
            margin-bottom: 1.5rem;
        }
        .nav-tabs .nav-link {
            border: none;
            border-radius: 10px;
            margin-right: 10px;
            color: #666;
            font-weight: 600;
        }
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .github-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .github-info small {
            color: #666;
        }
    </style>
</head>
<body>
    <?php
    require_once 'config/config.php';
    require_once 'includes/auth_check.php';
    
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit();
    }
    ?>
    
    <div class="container">
        <div class="auth-container">
            <div class="logo">
                <h1><i class="fas fa-cloud"></i> <?php echo APP_NAME; ?></h1>
                <p>Secure File Management with GitHub Integration</p>
            </div>
            
            <ul class="nav nav-tabs justify-content-center" id="authTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="authTabsContent">
                <!-- Login Form -->
                <div class="tab-pane fade show active" id="login" role="tabpanel">
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="loginUsername" class="form-label">
                                <i class="fas fa-user"></i> Username
                            </label>
                            <input type="text" class="form-control" id="loginUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <input type="password" class="form-control" id="loginPassword" name="password" required>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>
                </div>
                
                <!-- Register Form -->
                <div class="tab-pane fade" id="register" role="tabpanel">
                    <form id="registerForm">
                        <div class="mb-3">
                            <label for="registerUsername" class="form-label">
                                <i class="fas fa-user"></i> Username
                            </label>
                            <input type="text" class="form-control" id="registerUsername" name="username" 
                                   pattern="[a-zA-Z0-9_-]+" title="Only letters, numbers, underscore and dash allowed" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control" id="registerEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <input type="password" class="form-control" id="registerPassword" name="password" 
                                   minlength="6" required>
                        </div>
                        <div class="github-info">
                            <small>
                                <i class="fab fa-github"></i> 
                                <strong>GitHub Integration:</strong> A repository named 
                                <code>filemanager-{username}-loader</code> will be automatically created for you 
                                with an initial release.
                            </small>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </form>
                </div>
            </div>
            
            <div id="alertContainer" class="mt-3"></div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }

        // Login Form
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            submitBtn.disabled = true;
            
            fetch('auth/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    showAlert(data.message);
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Register Form
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
            submitBtn.disabled = true;
            
            fetch('auth/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        document.getElementById('login-tab').click();
                        this.reset();
                    }, 2000);
                } else {
                    showAlert(data.message);
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Update GitHub repo preview
        document.getElementById('registerUsername').addEventListener('input', function() {
            const username = this.value;
            const preview = document.querySelector('.github-info code');
            if (preview) {
                preview.textContent = `filemanager-${username}-loader`;
            }
        });
    </script>
</body>
</html>