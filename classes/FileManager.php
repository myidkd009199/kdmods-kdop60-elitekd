<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/GitHubAPI.php';

class FileManager {
    private $conn;
    private $github;
    private $table_name = "files";

    public function __construct($db, $github_token, $github_username) {
        $this->conn = $db;
        $this->github = new GitHubAPI($github_token, $github_username);
    }

    public function uploadFile($user_id, $repo_name, $file) {
        // Validate file
        if (!$this->validateFile($file)) {
            return ['success' => false, 'message' => 'Invalid file'];
        }

        // Create uploads directory if not exists
        $upload_dir = UPLOAD_DIR . $user_id . '/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Upload to GitHub
            $file_content = file_get_contents($file_path);
            $github_response = $this->github->uploadFile($repo_name, $file['name'], $file_content);

            if ($github_response['code'] === 201) {
                // Save to database
                $query = "INSERT INTO " . $this->table_name . " 
                         SET user_id=:user_id, filename=:filename, original_name=:original_name, 
                             file_path=:file_path, file_size=:file_size, mime_type=:mime_type";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->bindParam(":filename", $filename);
                $stmt->bindParam(":original_name", $file['name']);
                $stmt->bindParam(":file_path", $file_path);
                $stmt->bindParam(":file_size", $file['size']);
                $stmt->bindParam(":mime_type", $file['type']);

                if ($stmt->execute()) {
                    return ['success' => true, 'message' => 'File uploaded successfully'];
                }
            }
        }

        return ['success' => false, 'message' => 'Upload failed'];
    }

    public function getUserFiles($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id ORDER BY uploaded_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteFile($file_id, $user_id, $repo_name) {
        // Get file info
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $file_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$file) {
            return ['success' => false, 'message' => 'File not found'];
        }

        // Get file SHA from GitHub for deletion
        $github_file = $this->github->getFileContent($repo_name, $file['original_name']);
        if ($github_file['code'] === 200 && isset($github_file['data']['sha'])) {
            // Delete from GitHub
            $this->github->deleteFile($repo_name, $file['original_name'], $github_file['data']['sha']);
        }

        // Delete local file
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }

        // Delete from database
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $file_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'File deleted successfully'];
        }

        return ['success' => false, 'message' => 'Delete failed'];
    }

    public function downloadFile($file_id, $user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $file_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($file && file_exists($file['file_path'])) {
            return $file;
        }
        return false;
    }

    private function validateFile($file) {
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            return false;
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Add more validation as needed
        return true;
    }

    public function getFileStats($user_id) {
        $query = "SELECT COUNT(*) as total_files, SUM(file_size) as total_size 
                 FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>