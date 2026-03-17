# 🚀 INSTALLATION GUIDE - BTE Result System

## ❌ Issue: Directory Listing Instead of Website

If you're seeing a file list instead of the website, follow these steps:

---

## ✅ SOLUTION 1: Move Files to Correct Location

### Step 1: Create Proper Folder Structure

1. Open your XAMPP installation folder (usually `C:\xampp\htdocs\`)

2. Create a new folder called `bte_system`:
   ```
   C:\xampp\htdocs\bte_system\
   ```

3. Copy ALL the downloaded files into this folder

### Step 2: Verify Folder Structure

Your folder should look like this:
```
C:\xampp\htdocs\bte_system\
├── .htaccess
├── index.php
├── login.php
├── register.php
├── about.php
├── institutes.php
├── logout.php
├── database_updates.sql
├── README.md
├── ajax/
│   ├── get_programs.php
│   ├── get_students.php
│   └── ...
├── assets/
│   └── css/
│       └── style.css
├── config/
│   └── database.php
├── faculty/
│   ├── dashboard.php
│   ├── marks.php
│   └── attendance.php
├── student/
│   └── dashboard.php
└── includes/
    ├── header.php
    └── footer.php
```

---

## ✅ SOLUTION 2: Configure Database

### Step 1: Import Database

1. Open phpMyAdmin: `http://localhost:3307/phpmyadmin` or `http://localhost/phpmyadmin`

2. Click "Import" tab

3. Choose your `SQL_Database_Backup.sql` file

4. Click "Go" to import

5. After successful import, select the `bte_result_system` database

6. Click "SQL" tab

7. Copy and paste contents of `database_updates.sql`

8. Click "Go" to execute

### Step 2: Verify Database Credentials

The file `config/database.php` is already configured with:
- **Host**: localhost
- **User**: root
- **Password**: root123
- **Database**: bte_result_system
- **Port**: 3307

If your setup is different, edit this file.

---

## ✅ SOLUTION 3: Fix Apache Configuration

### Option A: Use .htaccess (Easiest)

The `.htaccess` file is already created. Just make sure:

1. Open XAMPP Control Panel
2. Click "Config" next to Apache
3. Select "httpd.conf"
4. Find this line:
   ```
   #LoadModule rewrite_module modules/mod_rewrite.so
   ```
5. Remove the `#` to make it:
   ```
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
6. Find this section:
   ```
   <Directory "C:/xampp/htdocs">
       AllowOverride None
   ```
7. Change `None` to `All`:
   ```
   <Directory "C:/xampp/htdocs">
       AllowOverride All
   ```
8. Save and restart Apache

### Option B: Direct httpd.conf Configuration

1. Open XAMPP Control Panel
2. Click "Config" next to Apache  
3. Select "httpd.conf"
4. Find:
   ```
   DirectoryIndex index.html index.php
   ```
5. Change to:
   ```
   DirectoryIndex index.php index.html
   ```
6. Find:
   ```
   Options Indexes FollowSymLinks
   ```
7. Remove `Indexes`:
   ```
   Options FollowSymLinks
   ```
8. Save and restart Apache

---

## ✅ SOLUTION 4: Access the Website

After completing the above steps:

1. Start Apache and MySQL from XAMPP Control Panel

2. Open browser and go to:
   ```
   http://localhost/bte_system/
   ```

You should now see the proper home page!

---

## 📱 Testing the System

### 1. Test Home Page
- URL: `http://localhost/bte_system/`
- Should show: Hero banner with "Har Ghar Tiranga"

### 2. Test Registration
- Click "Register Now"
- URL: `http://localhost/bte_system/register.php`
- Fill form and submit

### 3. Test Login
- Click "Login"
- URL: `http://localhost/bte_system/login.php`
- Enter credentials

### 4. Test Institutes Page
- Click "INSTITUTES" in navigation
- URL: `http://localhost/bte_system/institutes.php`
- Should show institute information forms

---

## 🐛 Common Issues & Solutions

### Issue: "Connection failed" Error
**Solution**: Check database credentials in `config/database.php`

### Issue: Still seeing directory listing
**Solution**: 
1. Clear browser cache (Ctrl + Shift + Delete)
2. Try: `http://localhost/bte_system/index.php` (with index.php explicitly)
3. Restart Apache

### Issue: "Page not found" errors
**Solution**: Check that all folders (ajax, assets, config, faculty, student, includes) exist

### Issue: CSS not loading
**Solution**: Make sure `assets/css/style.css` exists

### Issue: Login not working
**Solution**: 
1. Make sure you ran `database_updates.sql`
2. Check that `users` table exists in database
3. Register a new account first

---

## 🔐 Creating First Admin Account

After installation, create an admin account:

### Method 1: Via Register Page (then update in database)
1. Register as "Faculty" 
2. Go to phpMyAdmin
3. Find `users` table
4. Change `Role` to 'admin' and `Status` to 'active'

### Method 2: Direct SQL (Recommended)
1. Open phpMyAdmin
2. Select `bte_result_system` database
3. Click "SQL" tab
4. Run this query:
```sql
INSERT INTO users (Email, Password, Role, Status) 
VALUES ('admin@bte.gov.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');
```
Password is: `password`

5. Login with:
   - Email: admin@bte.gov.in
   - Password: password

---

## 📞 Need Help?

If you're still having issues:

1. Check XAMPP error logs:
   - `C:\xampp\apache\logs\error.log`

2. Enable PHP errors (already done in .htaccess)

3. Make sure:
   - ✅ Apache is running
   - ✅ MySQL is running
   - ✅ Port 80 is not blocked
   - ✅ Port 3307 is correct for MySQL

---

## ✨ Features Available

After successful setup, you can use:

### For Students:
- ✅ Register and login
- ✅ View results
- ✅ Check attendance
- ✅ See enrolled subjects

### For Faculty:
- ✅ Register and login
- ✅ Select institution, department, year, class
- ✅ View student lists
- ✅ Mark attendance
- ✅ Enter marks (internal/external)
- ✅ View all assigned subjects

### Public Pages:
- ✅ View institutes
- ✅ View courses by institute
- ✅ View principal and contact info

---

## 🎯 Quick Start Checklist

- [ ] XAMPP installed
- [ ] Files in `htdocs/bte_system/`
- [ ] Database imported
- [ ] database_updates.sql executed
- [ ] config/database.php configured
- [ ] Apache restarted
- [ ] Accessed http://localhost/bte_system/
- [ ] Home page loads correctly
- [ ] Registration works
- [ ] Login works

**All checked? You're ready to go! 🚀**
