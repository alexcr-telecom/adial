-- Fix admin password for existing installations
-- Run this if you already installed and admin/admin login doesn't work
--
-- Usage: mysql -u root -p adialer < fix_admin_password.sql

-- Update the admin user password to the correct bcrypt hash
UPDATE users
SET password = '$2y$10$oR5Dcs4NJMByGLwuxHwF3uoLxBspqSfjm1E0zDJwxGRmbtXojWM96'
WHERE username = 'admin';

-- Verify the update
SELECT id, username, email, role, is_active
FROM users
WHERE username = 'admin';

-- The password is now correctly set to 'admin'
