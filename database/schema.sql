-- Create database
CREATE DATABASE IF NOT EXISTS filemanager_db;
USE filemanager_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    github_repo VARCHAR(100),
    status ENUM('active', 'banned') DEFAULT 'active',
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Files table
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    mime_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- GitHub repos table
CREATE TABLE github_repos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    repo_name VARCHAR(100) NOT NULL,
    repo_url VARCHAR(255),
    release_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Admin settings table
CREATE TABLE admin_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    github_token VARCHAR(255),
    github_username VARCHAR(100),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@filemanager.com', '$2y$10$K8mEG.8QNh5kF5z1F5z1F5z1F5z1F5z1F5z1F5z1F5z1F5z1F5z1Fux2', 'admin');

-- Insert default admin settings
INSERT INTO admin_settings (github_token, github_username) VALUES 
('your_github_token_here', 'your_github_username_here');