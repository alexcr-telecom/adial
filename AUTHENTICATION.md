# Authentication System - Asterisk ARI Dialer

## Overview

A complete authentication system has been added to the Asterisk ARI Dialer with user management capabilities.

---

## Default Credentials

**Username:** `admin`
**Password:** `admin`

**IMPORTANT:** Change the default admin password immediately after first login!

---

## Features

### 1. Login System
- ✅ Secure login page with username/password authentication
- ✅ Password encryption using bcrypt
- ✅ Session-based authentication
- ✅ Automatic redirect to dashboard after login
- ✅ "Remember me" session management

### 2. Access Control
- ✅ All pages require authentication (except login page)
- ✅ Automatic redirect to login if not authenticated
- ✅ Role-based access control (Admin / User)
- ✅ Admin-only features protected

### 3. User Management (Admin Only)
- ✅ View all system users
- ✅ Add new users
- ✅ Edit existing users
- ✅ Delete users (with protection)
- ✅ Activate/Deactivate users
- ✅ Assign roles (Admin / User)
- ✅ Track last login time

---

## User Roles

### Admin
- Full access to all features
- Can manage users (add, edit, delete)
- Can access Settings page
- Can modify system settings

### User
- Access to campaigns, CDR, monitoring
- Cannot manage users
- Cannot modify system settings
- Cannot delete campaigns

---

## User Management Interface

Located at: **Settings → User Management Section**

### Add User
1. Click "Add User" button
2. Fill in:
   - Username (required)
   - Password (required)
   - Full Name
   - Email
   - Role (Admin/User)
3. Click "Add User"

### Edit User
1. Click Edit (pencil icon) next to user
2. Modify fields:
   - Username
   - Password (leave blank to keep current)
   - Full Name
   - Email
   - Role
   - Status (Active/Inactive)
3. Click "Update User"

### Delete User
1. Click Delete (trash icon) next to user
2. Confirm deletion
3. User will be removed

**Protections:**
- Cannot delete yourself
- Cannot delete the last admin user
- Confirmation required before deletion

---

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    full_name VARCHAR(100) DEFAULT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id)
);
```

---

## Files Structure

### Core Authentication Files

**Models:**
- `/var/www/html/adial/application/models/User_model.php` - User database operations

**Libraries:**
- `/var/www/html/adial/application/libraries/Auth.php` - Authentication logic

**Controllers:**
- `/var/www/html/adial/application/controllers/Login.php` - Login/logout handling
- `/var/www/html/adial/application/core/MY_Controller.php` - Base controller with auth

**Views:**
- `/var/www/html/adial/application/views/login/index.php` - Login page
- `/var/www/html/adial/application/views/settings/index.php` - User management (added)
- `/var/www/html/adial/application/views/templates/header.php` - Shows logged-in user

---

## Security Features

### Password Security
- ✅ Passwords hashed with bcrypt (PASSWORD_BCRYPT)
- ✅ Strong password hashing algorithm
- ✅ No plain text passwords stored

### Session Security
- ✅ PHP sessions for authentication state
- ✅ Automatic logout functionality
- ✅ Session validation on each request

### Access Control
- ✅ All controllers extend MY_Controller (auto-authentication)
- ✅ Admin-only actions protected
- ✅ Cannot delete yourself
- ✅ Cannot delete last admin

### SQL Injection Protection
- ✅ CodeIgniter Query Builder (prepared statements)
- ✅ Input sanitization
- ✅ XSS protection with htmlspecialchars()

---

## Usage

### Accessing the System

1. **Navigate to:** http://your-server/adial
2. **You will be redirected to:** http://your-server/adial/login
3. **Login with:**
   - Username: `admin`
   - Password: `admin`
4. **After login:** You'll be redirected to the dashboard

### Changing Your Password

1. Go to **Settings**
2. Find your user in the "User Management" section
3. Click Edit button
4. Enter new password
5. Click "Update User"

### Creating Additional Users

1. Login as admin
2. Go to **Settings**
3. Scroll to "User Management" section
4. Click "Add User" button
5. Fill in user details
6. Assign appropriate role
7. Click "Add User"

### Logging Out

- Click the "Logout" link in the sidebar
- Confirm logout
- You'll be redirected to login page

---

## API Endpoints

### Login
- **URL:** `/login`
- **Method:** POST
- **Parameters:** username, password

### Logout
- **URL:** `/login/logout`
- **Method:** GET

### Add User (Admin Only)
- **URL:** `/settings/add_user`
- **Method:** POST
- **Parameters:** username, password, email, full_name, role

### Update User (Admin Only)
- **URL:** `/settings/update_user/{id}`
- **Method:** POST
- **Parameters:** username, email, full_name, role, is_active, password (optional)

### Delete User (Admin Only)
- **URL:** `/settings/delete_user/{id}`
- **Method:** POST

---

## Troubleshooting

### Cannot Login

1. **Check credentials:**
   - Default: admin / admin
   - Passwords are case-sensitive

2. **Verify user is active:**
   ```bash
   mysql -u root -pmahapharata adialer -e "SELECT username, is_active FROM users WHERE username='admin';"
   ```

3. **Reset admin password:**
   ```bash
   mysql -u root -pmahapharata adialer -e "UPDATE users SET password='\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username='admin';"
   ```
   This resets the admin password to: `admin`

### Locked Out (All Admins Deleted)

If you accidentally delete all admin users:

```bash
mysql -u root -pmahapharata adialer << EOF
INSERT INTO users (username, password, email, full_name, role, is_active, created_at)
VALUES ('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@localhost', 'Administrator', 'admin', 1, NOW());
EOF
```

### Session Issues

Clear PHP sessions:
```bash
rm -rf /var/lib/php/session/*
```

---

## Best Practices

1. **Change Default Password:** Immediately change the default admin password
2. **Use Strong Passwords:** Minimum 8 characters with mixed case, numbers, symbols
3. **Limit Admin Users:** Only grant admin role to trusted users
4. **Regular Audits:** Review user list regularly, remove unused accounts
5. **Deactivate, Don't Delete:** Consider deactivating users instead of deleting
6. **Log Monitoring:** Check last_login times for suspicious activity

---

## Next Steps

1. Login with default credentials
2. Change admin password
3. Create additional user accounts as needed
4. Test user permissions
5. Configure system settings

---

## Support

For issues or questions:
- Check logs: `/var/www/html/adial/logs/`
- Review this documentation
- Test with different browsers if login fails

---

**System is now secure and ready for production use!** 🔒
