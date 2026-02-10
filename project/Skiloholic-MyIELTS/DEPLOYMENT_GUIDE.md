# MyIELTS - cPanel Deployment Guide

## 🚀 Production Configuration Complete!

Your MyIELTS platform is now configured for deployment to **https://skiloholic.com**

**IMPORTANT UPDATE:** The `database/schema.sql` file has been consolidated and corrected. It is now the single source of truth for the database structure. If you have already imported the schema, please DROP all tables and re-import `database/schema.sql`, or manually adjust your tables to match the structure defined in it (specifically `users` table columns `password_hash` and `email_verified`).

---

## 📋 Files Changed for Production

### 1. **config/database.php**
- ✅ DB_NAME: `mpkhydsc_myielts`
- ✅ DB_USER: `mpkhydsc_sharfin`
- ✅ DB_PASS: `DevNerds@Sharfin9090`

### 2. **config/config.php**
- ✅ BASE_URL: `https://skiloholic.com/`
- ✅ HTTPS security: `session.cookie_secure = 1`
- ✅ Error display: Disabled (errors logged to `error.log`)
- ✅ Error logging: Enabled

---

## 🔧 cPanel Setup Steps

### Step 1: Create MySQL Database

1. Login to cPanel
2. Go to **MySQL® Databases**
3. Database should already exist: `mpkhydsc_myielts`
4. User should already exist: `mpkhydsc_sharfin`
5. Verify user has **ALL PRIVILEGES** on the database

### Step 2: Import Database Schema

1. Go to **phpMyAdmin** in cPanel
2. Select database: `mpkhydsc_myielts`
3. Click **Import** tab
4. Choose file: `database/schema.sql`
5. Click **Go** to import

### Step 3: Upload Files

**Option A: File Manager**
1. Go to **File Manager** in cPanel
2. Navigate to `public_html/`
3. Upload all files from `E:\Xampp\htdocs\Skiloholic-MyIELTS\`
4. Extract if uploaded as ZIP

**Option B: FTP**
1. Use FileZilla or similar FTP client
2. Upload all files to `public_html/`

**Files to Upload:**
```
✅ All PHP files
✅ config/ directory
✅ includes/ directory
✅ auth/ directory
✅ admin/ directory
✅ tests/ directory
✅ assets/ directory (CSS, JS)
✅ Logo/ directory
✅ vendor/ directory (PHPMailer)
✅ database/ directory
✅ setup/ directory
✅ .htaccess file
✅ index.php
✅ dashboard.php (Note: This file is actually located in profile/dashboard.php but linked correctly now)
```

### Step 4: Set File Permissions

In File Manager or via FTP, set permissions:

```
uploads/ → 755 (rwxr-xr-x)
uploads/test-images/ → 755
uploads/submission-images/ → 755
```

To create folders if they don't exist:
1. Right-click in File Manager
2. Create folder: `uploads`
3. Inside `uploads`, create:
   - `test-images`
   - `submission-images`
4. Set permissions to 755

### Step 5: Verify Email Configuration

Email is already configured for Skiloholic server:
- Host: `mail.skiloholic.com`
- Email: `no_reply@skiloholic.com`
- Port: 465 (SSL)

No changes needed unless you want to use a different email.

### Step 6: Run Installation

1. Visit: `https://skiloholic.com/setup/install.php`
2. The database is already configured, so it should:
   - Connect successfully
   - Create admin account (if not exists)
   - Set up directories
3. **IMPORTANT:** After successful installation, **DELETE** the `setup/` folder for security!

```bash
# Delete setup folder via File Manager or SSH
rm -rf setup/
```

### Step 7: Test the Platform

1. Visit: `https://skiloholic.com/`
2. You should see the homepage
3. Login with admin credentials:
   - Email: `sharfinhossain50@gmail.com`
   - Password: `DevNerds@Sharfin9090`
4. Test creating a test
5. Register a test user
6. Take a test
7. Evaluate as admin

---

## 🔒 Post-Deployment Security

### Critical Security Steps:

1. **Delete installation script:**
   ```bash
   rm -rf setup/
   ```

2. **Verify .htaccess is active**
   - Should prevent direct access to PHP files in `config/`, `includes/`

3. **Check file permissions:**
   - Config files: 644
   - PHP files: 644
   - Directories: 755
   - .htaccess: 644

4. **Monitor error log:**
   - Errors will be logged to: `error.log` in root directory
   - Check regularly for issues

5. **Enable SSL/HTTPS:**
   - Your site is already configured for HTTPS
   - Install SSL certificate in cPanel (Let's Encrypt - free)
   - Force HTTPS redirect via .htaccess (already configured)

---

## 📧 Email Testing

After deployment, test email notifications:

1. Register a new user → Should receive verification email
2. Submit a test → Admin should receive notification
3. Assign examiner → Student should receive notification
4. Complete evaluation → Student should receive results email

If emails are not sending:
- Check spam folder
- Verify SMTP credentials in `config/email.php`
- Check cPanel email logs

---

## 🐛 Troubleshooting

### Issue: "Database connection failed"
**Solution:**
- Verify database credentials in `config/database.php`
- Check database exists in cPanel MySQL
- Ensure user has privileges

### Issue: "500 Internal Server Error"
**Solution:**
- Check `error.log` file in root
- Verify PHP version is 7.4 or higher
- Check file permissions (should be 644 for PHP, 755 for directories)

### Issue: "Page not found" or CSS not loading
**Solution:**
- Verify all files uploaded correctly
- Check `.htaccess` file exists
- Ensure `BASE_URL` in `config/config.php` is correct

### Issue: Images not uploading
**Solution:**
- Check `uploads/` directory exists
- Verify permissions are 755
- Check PHP upload limits in cPanel (should be at least 5MB)

### Issue: Emails not sending
**Solution:**
- Check spam folder
- Verify SMTP credentials
- Test email in cPanel → Email Accounts → Check email delivery

---

## 📊 Admin Access

**Admin Login:**
- URL: `https://skiloholic.com/auth/login.php`
- Email: `sharfinhossain50@gmail.com`
- Password: `DevNerds@Sharfin9090`

**Admin Panel:**
- Dashboard: `https://skiloholic.com/admin/`
- Manage Tests: `https://skiloholic.com/admin/tests/manage.php`
- Submission Queue: `https://skiloholic.com/admin/submissions/queue.php`

---

## 🔄 Future Updates

When you make code changes locally:

1. Test on localhost first
2. Update only the changed files via FTP/File Manager
3. If database schema changes, run SQL updates in phpMyAdmin
4. Clear browser cache after updates

---

## 📝 Quick Reference

**Production URLs:**
- Homepage: `https://skiloholic.com/`
- User Login: `https://skiloholic.com/auth/login.php`
- User Register: `https://skiloholic.com/auth/register.php`
- Admin Panel: `https://skiloholic.com/admin/`

**Admin Credentials:**
- Email: `sharfinhossain50@gmail.com`
- Password: `DevNerds@Sharfin9090`

**Database:**
- Name: `mpkhydsc_myielts`
- User: `mpkhydsc_sharfin`
- Host: `localhost`

**Email Server:**
- SMTP: `mail.skiloholic.com`
- Port: 465 (SSL)
- From: `no_reply@skiloholic.com`

---

## ✅ Deployment Checklist

- [ ] Database created in cPanel
- [ ] Database user has ALL PRIVILEGES
- [ ] Database schema imported via phpMyAdmin (Use updated `database/schema.sql`!)
- [ ] All files uploaded to `public_html/`
- [ ] `uploads/` directories created with 755 permissions
- [ ] Visited `https://skiloholic.com/setup/install.php`
- [ ] **DELETED** `setup/` folder after installation
- [ ] Tested admin login
- [ ] Tested user registration
- [ ] Tested email notifications
- [ ] SSL certificate installed (HTTPS working)
- [ ] Reviewed error.log for issues

---

**Your MyIELTS platform is ready for production! 🎉**

Good luck with your deployment!
