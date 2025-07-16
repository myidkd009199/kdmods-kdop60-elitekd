<?php
require_once 'config/config.php';
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'classes/FileManager.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

// Get admin GitHub settings
$query = "SELECT github_token, github_username FROM admin_settings ORDER BY id DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$admin_settings = $stmt->fetch(PDO::FETCH_ASSOC);

$fileManager = new FileManager($db, $admin_settings['github_token'], $admin_settings['github_username']);
$files = $fileManager->getUserFiles(getUserId());
$stats = $fileManager->getFileStats(getUserId());
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .upload-area {
            border: 3px dashed #dee2e6;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            background: #fff;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .upload-area:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        .upload-area.dragover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        .file-item {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }
        .file-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-gradient:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .progress {
            height: 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-cloud"></i> <?php echo APP_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo getUsername(); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#deleteRepoModal">
                            <i class="fas fa-trash"></i> Delete Repository
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="auth/logout.php">
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
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-files fa-2x mb-2"></i>
                        <h3><?php echo $stats['total_files'] ?? 0; ?></h3>
                        <p class="mb-0">Total Files</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-hdd fa-2x mb-2"></i>
                        <h3><?php echo number_format(($stats['total_size'] ?? 0) / 1024 / 1024, 2); ?> MB</h3>
                        <p class="mb-0">Storage Used</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fab fa-github fa-2x mb-2"></i>
                        <h6 class="text-truncate"><?php echo getGithubRepo(); ?></h6>
                        <p class="mb-0">GitHub Repository</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Area -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cloud-upload-alt"></i> Upload Files</h5>
            </div>
            <div class="card-body">
                <div class="upload-area" id="uploadArea">
                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                    <h5>Drag & drop files here or click to browse</h5>
                    <p class="text-muted">Maximum file size: <?php echo MAX_FILE_SIZE / 1024 / 1024; ?>MB</p>
                    <input type="file" id="fileInput" multiple class="d-none">
                </div>
                <div id="uploadProgress" class="mt-3" style="display: none;">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Files List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-folder-open"></i> My Files</h5>
                <button class="btn btn-gradient btn-sm" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($files)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No files uploaded yet</h5>
                        <p class="text-muted">Upload your first file to get started</p>
                    </div>
                <?php else: ?>
                    <div id="filesList">
                        <?php foreach ($files as $file): ?>
                            <div class="file-item" data-file-id="<?php echo $file['id']; ?>">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <i class="fas fa-file fa-lg text-primary me-2"></i>
                                        <strong><?php echo htmlspecialchars($file['original_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo number_format($file['file_size'] / 1024, 2); ?> KB • 
                                            <?php echo date('M j, Y g:i A', strtotime($file['uploaded_at'])); ?>
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <button class="btn btn-outline-primary btn-sm me-2" 
                                                onclick="downloadFile(<?php echo $file['id']; ?>)">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" 
                                                onclick="deleteFile(<?php echo $file['id']; ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Repository Modal -->
    <div class="modal fade" id="deleteRepoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Repository</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning!</strong> This will permanently delete your GitHub repository 
                        <code><?php echo getGithubRepo(); ?></code> and all files.
                    </div>
                    <p>Type your username <strong><?php echo getUsername(); ?></strong> to confirm:</p>
                    <input type="text" class="form-control" id="confirmUsername" placeholder="Enter username">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="deleteRepository()">
                        <i class="fas fa-trash"></i> Delete Repository
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload handling
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const uploadProgress = document.getElementById('uploadProgress');
        const progressBar = uploadProgress.querySelector('.progress-bar');

        uploadArea.addEventListener('click', () => fileInput.click());

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            if (files.length === 0) return;

            const formData = new FormData();
            for (let file of files) {
                formData.append('files[]', file);
            }
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

            uploadProgress.style.display = 'block';
            progressBar.style.width = '0%';

            fetch('api/upload.php', {
                method: 'POST',
                body: formData
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
                showAlert('Upload failed. Please try again.', 'danger');
            })
            .finally(() => {
                uploadProgress.style.display = 'none';
                fileInput.value = '';
            });
        }

        function downloadFile(fileId) {
            window.open(`api/download.php?id=${fileId}`, '_blank');
        }

        function deleteFile(fileId) {
            if (!confirm('Are you sure you want to delete this file?')) return;

            fetch('api/delete.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    file_id: fileId,
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-file-id="${fileId}"]`).remove();
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Delete failed. Please try again.', 'danger');
            });
        }

        function deleteRepository() {
            const username = document.getElementById('confirmUsername').value;
            if (username !== '<?php echo getUsername(); ?>') {
                showAlert('Username confirmation does not match.', 'danger');
                return;
            }

            fetch('api/delete_repo.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => window.location.href = 'auth/logout.php', 2000);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Delete failed. Please try again.', 'danger');
            });
        }

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