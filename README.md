# FileManager Pro

A secure, modern file management system with GitHub integration built in PHP and MySQL. Features user registration/login, automatic GitHub repository creation, file upload/download/delete functionality, user isolation, admin panel, and mobile-responsive UI.

## Features

### User Features
- **User Registration & Login**: Secure authentication system
- **Automatic GitHub Integration**: Creates `filemanager-{username}-loader` repository on signup
- **File Management**: Upload, download, delete files with drag & drop support
- **User Isolation**: Users can only access their own files and repositories
- **Repository Management**: Users can delete their entire repository and data
- **Mobile-Friendly UI**: Responsive design that works on all devices

### Admin Features
- **User Management**: View all users, ban/unban accounts, delete users
- **GitHub Settings**: Update GitHub token and username for repository creation
- **Statistics Dashboard**: Monitor total users, files, and storage usage
- **Complete Control**: Admin can manage all user accounts and data

### Technical Features
- **Security**: CSRF protection, SQL injection prevention, secure file handling
- **GitHub API Integration**: Automatic repository creation, file uploads to GitHub
- **Modern UI**: Bootstrap 5, FontAwesome icons, gradient designs
- **File Validation**: Size limits, type checking, error handling
- **Session Management**: Secure session handling and authentication

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- cURL extension
- PDO MySQL extension
- Write permissions for uploads directory
- GitHub Personal Access Token

## Installation

### 1. Download and Setup

```bash
# Clone or download the project
git clone <repository-url>
cd filemanager-pro

# Set proper permissions
chmod 755 .
chmod 777 uploads/
```

### 2. Database Configuration

1. Create a MySQL database for the application
2. Update database credentials in `config/database.php`:

```php
private $host = 'localhost';
private $db_name = 'your_database_name';
private $username = 'your_db_username';
private $password = 'your_db_password';
```

### 3. GitHub Configuration

1. Create a GitHub Personal Access Token with repository permissions
2. Update GitHub settings in `config/config.php` or through admin panel:

```php
define('GITHUB_TOKEN', 'your_github_token_here');
define('GITHUB_USERNAME', 'your_github_username_here');
```

### 4. Run Installation

1. Navigate to `http://yoursite.com/install.php`
2. Follow the installation wizard
3. The installer will:
   - Create database tables
   - Set up upload directories
   - Create default admin account

### 5. Default Admin Account

- **Username:** `admin`
- **Password:** `admin123`
- **⚠️ Change this password immediately after first login!**

## Configuration

### Security Settings

Update these in `config/config.php`:

```php
define('JWT_SECRET', 'your_secret_key_here');
define('PASSWORD_SALT', 'your_salt_here');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
```

### GitHub Integration

The system uses GitHub API to:
- Create repositories for new users
- Upload files to GitHub repositories
- Create initial releases
- Delete repositories when requested

### File Upload Settings

- Default max file size: 50MB
- Supported: All file types (configurable)
- Storage: Local + GitHub repository
- Security: File validation and sanitization

## Usage

### For Users

1. **Register**: Create account with username/email/password
2. **Auto Repository**: System creates `filemanager-{username}-loader` repo
3. **Upload Files**: Drag & drop or click to upload files
4. **Manage Files**: Download, delete, or view file details
5. **Repository Control**: Delete entire repository if needed

### For Admins

1. **Access Admin Panel**: Login with admin account
2. **User Management**: View, ban/unban, or delete users
3. **GitHub Settings**: Update token and username
4. **Monitor System**: View statistics and usage data

## API Endpoints

### Authentication
- `POST /auth/login.php` - User login
- `POST /auth/register.php` - User registration
- `GET /auth/logout.php` - User logout

### File Management
- `POST /api/upload.php` - Upload files
- `GET /api/download.php?id={file_id}` - Download file
- `POST /api/delete.php` - Delete file
- `POST /api/delete_repo.php` - Delete repository

### Admin (Requires admin role)
- `POST /admin/api/user_management.php` - Manage users
- `POST /admin/api/update_settings.php` - Update GitHub settings

## Security Features

- **CSRF Protection**: All forms include CSRF tokens
- **SQL Injection Prevention**: Prepared statements throughout
- **Authentication**: Session-based authentication system
- **Authorization**: Role-based access control
- **File Security**: Validation, sanitization, and secure storage
- **Password Security**: Hashed passwords with salt

## Directory Structure

```
filemanager-pro/
├── config/
│   ├── config.php          # Main configuration
│   └── database.php        # Database connection
├── classes/
│   ├── User.php           # User management
│   ├── GitHubAPI.php      # GitHub integration
│   └── FileManager.php    # File operations
├── auth/
│   ├── login.php          # Login handler
│   ├── register.php       # Registration handler
│   └── logout.php         # Logout handler
├── api/
│   ├── upload.php         # File upload API
│   ├── download.php       # File download API
│   ├── delete.php         # File deletion API
│   └── delete_repo.php    # Repository deletion API
├── admin/
│   ├── dashboard.php      # Admin dashboard
│   └── api/               # Admin API endpoints
├── includes/
│   └── auth_check.php     # Authentication helpers
├── uploads/               # Local file storage
├── database/
│   └── schema.sql         # Database schema
├── index.php              # Main login page
├── dashboard.php          # User dashboard
├── install.php            # Installation wizard
└── README.md              # This file
```

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `config/database.php`
   - Ensure MySQL server is running
   - Verify database exists

2. **GitHub API Errors**
   - Verify GitHub token has correct permissions
   - Check token hasn't expired
   - Ensure username is correct

3. **File Upload Issues**
   - Check `uploads/` directory permissions (777)
   - Verify PHP file size limits
   - Check disk space availability

4. **Permission Denied**
   - Set proper file permissions
   - Check web server user permissions
   - Verify .htaccess rules

### Support

For issues or questions:
1. Check this README for common solutions
2. Verify all requirements are met
3. Check server error logs
4. Ensure proper configuration

## License

This project is licensed under the MIT License. See LICENSE file for details.

## Contributing

1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## Changelog

### Version 1.0.0
- Initial release
- User registration/login system
- GitHub integration
- File management
- Admin panel
- Mobile-responsive UI
