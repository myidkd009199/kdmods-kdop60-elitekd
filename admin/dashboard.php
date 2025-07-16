<?php
require_once '../config/config.php';
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../classes/User.php';

requireAdmin();

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$users = $user->getAllUsers();

// Get admin settings
$query = "SELECT * FROM admin_settings ORDER BY id DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$admin_settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Get statistics
$query = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
    SUM(CASE WHEN status = 'banned' THEN 1 ELSE 0 END) as banned_users
    FROM users WHERE role = 'user'";
$stmt = $db->prepare($query);
$stmt->execute();
$user_stats = $stmt->fetch(PDO::FETCH_ASSOC);

$query = "SELECT COUNT(*) as total_files, SUM(file_size) as total_size FROM files";
$stmt = $db->prepare($query);
$stmt->execute();
$file_stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stats-card {
            background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
            color: white;
        }
        .user-row {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-left: 4px solid #dc3545;
        }
        .btn-admin {
            background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
            border: none;
            color: white;
        }
        .btn-admin:hover {
            background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
            color: white;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-banned {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-shield-alt"></i> <?php echo APP_NAME; ?> Admin
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-shield"></i> <?php echo getUsername(); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#settingsModal">
                            <i class="fas fa-cog"></i> GitHub Settings
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../dashboard.php">
                            <i class="fas fa-user"></i> User Dashboard
                        </a></li>
                        <li><a class="dropdown-item" href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h3><?php echo $user_stats['total_users']; ?></h3>
                        <p class="mb-0">Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-user-check fa-2x mb-2"></i>
                        <h3><?php echo $user_stats['active_users']; ?></h3>
                        <p class="mb-0">Active Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-files fa-2x mb-2"></i>
                        <h3><?php echo $file_stats['total_files'] ?? 0; ?></h3>
                        <p class="mb-0">Total Files</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-hdd fa-2x mb-2"></i>
                        <h3><?php echo number_format(($file_stats['total_size'] ?? 0) / 1024 / 1024, 2); ?> MB</h3>
                        <p class="mb-0">Storage Used</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Management -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users-cog"></i> User Management</h5>
                <button class="btn btn-admin btn-sm" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No users found</h5>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>GitHub Repo</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user_item): ?>
                                    <?php if ($user_item['role'] === 'user'): ?>
                                        <tr data-user-id="<?php echo $user_item['id']; ?>">
                                            <td>
                                                <strong><?php echo htmlspecialchars($user_item['username']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($user_item['email']); ?></td>
                                            <td>
                                                <code><?php echo htmlspecialchars($user_item['github_repo']); ?></code>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $user_item['status']; ?>">
                                                    <?php echo ucfirst($user_item['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($user_item['created_at'])); ?></td>
                                            <td>
                                                <?php if ($user_item['status'] === 'active'): ?>
                                                    <button class="btn btn-warning btn-sm me-2" 
                                                            onclick="updateUserStatus(<?php echo $user_item['id']; ?>, 'banned')">
                                                        <i class="fas fa-ban"></i> Ban
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-success btn-sm me-2" 
                                                            onclick="updateUserStatus(<?php echo $user_item['id']; ?>, 'active')">
                                                        <i class="fas fa-check"></i> Unban
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-danger btn-sm" 
                                                        onclick="deleteUser(<?php echo $user_item['id']; ?>)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- GitHub Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">GitHub Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="settingsForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="githubToken" class="form-label">GitHub Token</label>
                            <input type="password" class="form-control" id="githubToken" name="github_token" 
                                   value="<?php echo $admin_settings['github_token'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="githubUsername" class="form-label">GitHub Username</label>
                            <input type="text" class="form-control" id="githubUsername" name="github_username" 
                                   value="<?php echo $admin_settings['github_username'] ?? ''; ?>" required>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            These settings are used to create repositories for new users and manage file uploads.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-admin">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateUserStatus(userId, status) {
            const action = status === 'banned' ? 'ban' : 'unban';
            if (!confirm(`Are you sure you want to ${action} this user?`)) return;

            fetch('api/user_management.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'update_status',
                    user_id: userId,
                    status: status,
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Operation failed. Please try again.', 'danger');
            });
        }

        function deleteUser(userId) {
            if (!confirm('Are you sure you want to delete this user? This will also delete their repository and all files.')) return;

            fetch('api/user_management.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'delete_user',
                    user_id: userId,
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-user-id="${userId}"]`).remove();
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Delete failed. Please try again.', 'danger');
            });
        }

        // Settings form
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
            
            fetch('api/update_settings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('settingsModal')).hide();
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Update failed. Please try again.', 'danger');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.insertBefore(alertDiv, document.body.firstChild);
        }
    </script>
</body>
</html>