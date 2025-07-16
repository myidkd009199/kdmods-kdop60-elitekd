# 🔧 Troubleshooting Guide - FileManager Pro

## 🚨 Login/Register Error: "An error occurred. Please try again."

I've fixed the main path issues that were causing authentication errors. Here's what was fixed and how to resolve any remaining issues:

### ✅ **What Was Fixed:**

1. **File Path Issues**: Updated all `require_once` statements to use `__DIR__` for absolute paths
2. **Admin Password Hash**: Fixed the admin password hash in the database
3. **Include Paths**: Corrected relative path issues in all auth, API, and admin files

### 🛠️ **Step-by-Step Resolution:**

#### **Step 1: Test the System**
Visit these test pages to diagnose issues:

1. **`/test_auth.php`** - Tests database connection, admin user, sessions, and file includes
2. **`/reset_admin_password.php`** - Resets admin password if needed
3. **`/debug_login.php`** - Detailed login debugging (temporary)

#### **Step 2: Check Database Setup**
```sql
-- Verify database exists and admin user is present
USE filemanager_db;
SELECT * FROM users WHERE username = 'admin';
```

#### **Step 3: Verify Configuration**
Check `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'filemanager_db';  // Your actual database name
private $username = 'your_db_user';   // Your database username
private $password = 'your_db_pass';   // Your database password
```

#### **Step 4: Test Admin Login**
- **Username:** `admin`
- **Password:** `admin123`

If this fails, use the password reset script.

---

## 🐛 **Common Issues & Solutions**

### **Database Connection Failed**
```
❌ Error: "Connection error: SQLSTATE[HY000] [1045] Access denied"
```
**Solution:**
1. Check database credentials in `config/database.php`
2. Ensure MySQL is running
3. Verify database user has proper permissions

### **CSRF Token Issues**
```
❌ Error: "Invalid CSRF token"
```
**Solution:**
1. Clear browser cache and cookies
2. Ensure sessions are working (check `test_auth.php`)
3. Verify server has session write permissions

### **File Not Found Errors**
```
❌ Error: "require_once failed opening required file"
```
**Solution:**
All path issues should now be fixed. If you still see this:
1. Check file permissions (755 for directories, 644 for files)
2. Verify all files exist in correct locations

### **Admin User Not Found**
```
❌ Error: "Invalid username or password"
```
**Solution:**
1. Run `test_auth.php` to check if admin user exists
2. Use `reset_admin_password.php` to recreate admin user
3. Check if installation completed properly

### **GitHub API Issues**
```
❌ Error: "GitHub settings not configured"
```
**Solution:**
1. Login as admin
2. Go to Admin Panel → GitHub Settings
3. Enter valid GitHub token and username

---

## 🚀 **Quick Fix Commands**

### **Reset Everything:**
```bash
# 1. Drop and recreate database (if needed)
mysql -u root -p
DROP DATABASE filemanager_db;
CREATE DATABASE filemanager_db;

# 2. Run installation again
# Visit: http://yoursite.com/install.php
```

### **Fix File Permissions:**
```bash
chmod 755 /path/to/filemanager-pro/
chmod -R 644 /path/to/filemanager-pro/*
chmod -R 755 /path/to/filemanager-pro/*/
chmod 777 /path/to/filemanager-pro/uploads/
```

---

## 🔍 **Debug Steps**

### **1. Enable Error Reporting**
Add to top of `index.php` temporarily:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### **2. Check Browser Console**
Open Browser Developer Tools → Console tab
Look for JavaScript errors when clicking Login/Register

### **3. Check Server Logs**
- Apache: `/var/log/apache2/error.log`
- Nginx: `/var/log/nginx/error.log`
- PHP: Check `php_error.log`

### **4. Test with Debug Login**
Use `debug_login.php` for detailed error information:
```javascript
// Change in index.php temporarily:
fetch('debug_login.php', {  // Instead of 'auth/login.php'
    method: 'POST',
    body: formData
})
```

---

## ✅ **Verification Checklist**

After fixes, verify these work:

- [ ] Database connection test passes
- [ ] Admin user exists and password works
- [ ] CSRF tokens are generated
- [ ] File includes work without errors
- [ ] Login form submits without JavaScript errors
- [ ] Admin can access admin panel
- [ ] Regular users can register

---

## 🆘 **Still Having Issues?**

### **If login still fails:**
1. Use `reset_admin_password.php` to set a new admin password
2. Clear all browser data (cache, cookies, sessions)
3. Try a different browser or incognito mode
4. Check if server meets requirements (PHP 7.4+, MySQL, extensions)

### **If register fails:**
1. Ensure GitHub settings are configured in admin panel
2. Check database permissions for INSERT operations
3. Verify email format validation

### **For new installations:**
1. Ensure you've run `install.php` successfully
2. Check if `.installed` file exists
3. Verify all database tables were created

---

## 📞 **Emergency Reset**

If nothing works, delete these files and start fresh:
- `.installed` (to allow reinstallation)
- Database tables (to recreate from scratch)
- `config/config.php` (reconfigure settings)

Then visit `/install.php` again.

---

**The authentication system should now work properly. If you're still experiencing issues, run the test scripts first to identify the specific problem.**