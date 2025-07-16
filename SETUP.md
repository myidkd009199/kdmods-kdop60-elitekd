# Quick Setup Guide - FileManager Pro

## 🚀 Quick Start (5 minutes)

### 1. Database Setup
```sql
-- Create database
CREATE DATABASE filemanager_db;

-- Update config/database.php with your credentials
```

### 2. GitHub Token Setup
1. Go to GitHub → Settings → Developer settings → Personal access tokens
2. Generate new token with `repo` permissions
3. Copy the token

### 3. Configuration
Update `config/config.php`:
```php
define('GITHUB_TOKEN', 'your_actual_github_token_here');
define('GITHUB_USERNAME', 'your_github_username');
```

### 4. Installation
1. Visit: `http://yoursite.com/install.php`
2. Click "Install FileManager Pro"
3. Done! 🎉

### 5. Default Login
- **Admin Username:** `admin`
- **Password:** `admin123`
- **⚠️ Change password immediately!**

---

## 📁 Project Structure

```
filemanager-pro/
├── 🏠 index.php              # Login/Register page
├── 📊 dashboard.php          # User dashboard
├── ⚙️ install.php            # Installation wizard
├── 🔧 config/               # Configuration files
├── 👥 classes/              # Core PHP classes
├── 🔐 auth/                 # Authentication handlers
├── 🌐 api/                  # API endpoints
├── 👑 admin/                # Admin panel
├── 📤 uploads/              # File storage
└── 🗄️ database/            # SQL schema
```

---

## ✨ Features Included

### 👤 User Features
- ✅ Register/Login system
- ✅ Auto GitHub repo creation (`filemanager-{username}-loader`)
- ✅ Drag & drop file upload
- ✅ File download/delete
- ✅ Repository deletion
- ✅ Mobile-responsive UI

### 👑 Admin Features
- ✅ User management (ban/unban/delete)
- ✅ GitHub settings update
- ✅ System statistics
- ✅ Complete user control

### 🔒 Security Features
- ✅ CSRF protection
- ✅ SQL injection prevention
- ✅ Secure file handling
- ✅ Session management
- ✅ Password hashing

---

## 🛠️ Requirements Check

Before installation, ensure you have:
- ✅ PHP 7.4+
- ✅ MySQL 5.7+
- ✅ cURL extension
- ✅ PDO MySQL extension
- ✅ Write permissions
- ✅ GitHub Personal Access Token

---

## 🎯 Post-Installation

### Update Admin Password
1. Login as admin
2. Go to admin settings
3. Change password immediately

### Configure GitHub Settings
1. Admin Panel → GitHub Settings
2. Enter your GitHub token
3. Enter your GitHub username
4. Save settings

### Test User Registration
1. Register a new user
2. Check if GitHub repo is created
3. Test file upload functionality

---

## 🔥 Key URLs

- **Main Site:** `/index.php`
- **User Dashboard:** `/dashboard.php`
- **Admin Panel:** `/admin/dashboard.php`
- **Installation:** `/install.php`

---

## 💡 Tips

1. **Security:** Change default admin password immediately
2. **GitHub:** Ensure token has proper repository permissions
3. **Storage:** Monitor disk space for uploads
4. **Backup:** Regular database backups recommended
5. **Updates:** Keep GitHub token refreshed

---

## 🆘 Troubleshooting

### Database Connection Failed
- Check `config/database.php` credentials
- Verify MySQL is running
- Ensure database exists

### GitHub API Errors
- Verify token permissions
- Check token expiration
- Confirm username is correct

### File Upload Issues
- Check `uploads/` directory permissions (777)
- Verify PHP upload limits
- Check disk space

---

## 📞 Support

If you encounter issues:
1. Check this guide first
2. Verify all requirements
3. Check server error logs
4. Ensure proper configuration

---

**🎉 Enjoy your new FileManager Pro system!**