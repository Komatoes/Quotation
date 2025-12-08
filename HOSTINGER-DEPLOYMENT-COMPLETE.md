# 🌐 Hostinger Deployment Guide - Quotation System

**Complete Setup & Installation Instructions**  
**Version**: 1.0  
**Last Updated**: November 30, 2025  
**Target**: Hostinger Shared Hosting (Linux)

---

## 📋 Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [SSH Connection & File Upload](#ssh-connection--file-upload)
3. [PHP & Server Requirements](#php--server-requirements)
4. [Composer Installation](#composer-installation)
5. [Database Setup](#database-setup)
6. [Environment Configuration](#environment-configuration)
7. [Backup & Restore Setup](#backup--restore-setup)
8. [Final Configuration](#final-configuration)
9. [Verification & Testing](#verification--testing)
10. [Troubleshooting](#troubleshooting)

---

## Pre-Deployment Checklist

Before you start, ensure you have:

- [ ] Hostinger account with active hosting plan
- [ ] SSH access enabled on Hostinger
- [ ] Database created in Hostinger (MySQL 5.7+)
- [ ] PHP 8.0.2+ installed on Hostinger
- [ ] Composer available on Hostinger (usually pre-installed)
- [ ] `USER_CREDENTIALS.md` file (saved locally with passwords)
- [ ] Latest code from your Git repository (or ZIP file)
- [ ] Database dump from local machine (backup of `quotation` database)
- [ ] Google Drive credentials (if using Google Drive backup)

---

## SSH Connection & File Upload

### Step 1: Connect via SSH

```bash
# Replace username and domain with your Hostinger details
ssh username@your-domain.com

# Or use Hostinger's IP address
ssh username@xxx.xxx.xxx.xxx
```

### Step 2: Navigate to Public Directory

```bash
# Go to your public_html or www directory
cd public_html
# or
cd www

# Check current directory
pwd
```

### Step 3: Upload Your Project Files

**Option A: Using Git (Recommended)**
```bash
# Clone your repository
git clone https://github.com/your-username/Quotation.git .

# Checkout your main branch
git checkout main  # or your main branch name
```

**Option B: Using SFTP (File Manager)**
- Connect via FileZilla or Hostinger's file manager
- Upload all files from your local project to `public_html/`
- Ensure directory structure is maintained

**Option C: Using ZIP Upload**
```bash
# Upload ZIP file via FileZilla or Hostinger panel
unzip quotation.zip
rm quotation.zip  # Remove after extraction
```

---

## PHP & Server Requirements

### Step 1: Check PHP Version

```bash
# Verify PHP version (should be 8.0.2 or higher)
php -v

# Output example:
# PHP 8.1.0 (cli) (built: Nov 15 2025 12:34:56) ( NTS )
```

### Step 2: Check Required PHP Extensions

```bash
# Check installed extensions
php -m | grep -E "json|mysql|pdo|curl|gd|zip"

# Required extensions for Quotation:
# - PDO (Database)
# - PDO MySQL
# - JSON
# - cURL
# - ZIP
# - GD (for image processing)
# - Fileinfo
```

### Step 3: Enable Additional Extensions (if needed)

Contact Hostinger support if extensions are missing. Most are pre-installed.

### Step 4: Check PHP Configuration

```bash
# Display PHP configuration
php -i | grep -E "memory_limit|max_execution_time|upload_max_filesize"

# Recommended values:
# - memory_limit: 256M minimum (512M recommended)
# - max_execution_time: 300 seconds
# - upload_max_filesize: 100M+
```

Contact Hostinger support to increase limits if needed.

---

## Composer Installation

### Step 1: Verify Composer is Installed

```bash
# Check Composer version
composer --version

# Output example:
# Composer version 2.5.0 2023-11-02 15:59:32
```

### Step 2: Install Project Dependencies

```bash
# Navigate to project root
cd /home/username/public_html/  # Adjust path

# Install all dependencies from composer.json
composer install

# This will install:
# ✓ Laravel Framework 9.19+
# ✓ Laravel Sanctum 3.0+
# ✓ Spatie Laravel Backup (for backup/restore)
# ✓ Spatie Laravel Permission (for roles)
# ✓ PHPOffice PHPWord (for DOCX exports)
# ✓ guzzlehttp/guzzle (for Google Drive API)
# ✓ All other dependencies
```

**Expected Output:**
```
Installing dependencies from lock file
Verifying lock file contents can be installed on this platform
...
39 packages installed
```

### Step 3: Verify Installation

```bash
# List installed packages
composer list

# Check vendor directory exists
ls -la vendor/ | head -20

# Should see many folders: laravel/, spatie/, phpoffice/, etc.
```

---

## Database Setup

### Step 1: Create Database on Hostinger

**Via Hostinger Control Panel:**
1. Log in to Hostinger
2. Go to Databases → MySQL
3. Click "Create Database"
4. Name: `quotation` (or your preferred name)
5. Username: Create new or use existing
6. Password: Use strong password
7. Click Create

**Via SSH:**
```bash
# Connect to MySQL
mysql -u root -p
# or with specific user
mysql -u username -p

# Enter your database password when prompted

# Create database
CREATE DATABASE quotation;

# Show databases
SHOW DATABASES;

# Exit MySQL
exit;
```

### Step 2: Import Your Local Database

**Option A: Using mysqldump (Recommended)**
```bash
# On your LOCAL machine, export your current database:
mysqldump -u root -p quotation > quotation_backup.sql

# Upload quotation_backup.sql to Hostinger via SFTP/FileZilla
```

**Then on Hostinger (via SSH):**
```bash
# Import the database
mysql -u username -p quotation < quotation_backup.sql

# Verify import
mysql -u username -p -e "SELECT COUNT(*) FROM quotation.quotations LIMIT 1;"
```

**Option B: Using phpMyAdmin**
1. Access Hostinger's phpMyAdmin
2. Create database `quotation`
3. Go to Import tab
4. Select your local `quotation_backup.sql`
5. Click Import

### Step 3: Verify Database Connection

```bash
# Test database connection
mysql -u username -p -e "SHOW TABLES;" quotation

# Should display all your tables:
# clients, quotations, materials, users, roles, permissions, etc.
```

---

## Environment Configuration

### Step 1: Create .env File on Hostinger

```bash
# Create .env file
touch .env

# Edit the file
nano .env
```

### Step 2: Add Configuration

Copy the following and replace with YOUR Hostinger details:

```env
# Application Settings
APP_NAME=Quotation
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE  # We'll generate this in Step 4
APP_DEBUG=false
APP_URL=https://your-domain.com  # Your Hostinger domain

# Logging
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Database Configuration (from Hostinger Control Panel)
DB_CONNECTION=mysql
DB_HOST=localhost  # or your database host
DB_PORT=3306
DB_DATABASE=quotation
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# Broadcast & Cache
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Redis (optional, usually not needed on Hostinger)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-domain.com
MAIL_PORT=587
MAIL_USERNAME=your-email@your-domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Backup Database Dump Command (Linux Paths for Hostinger)
DUMP_COMMAND_PATH=/usr/bin  # Linux command path
MYSQL_DUMP_COMMAND_PATH=/usr/bin

# Google Drive Backup (Optional - for 3-2-1 strategy)
# See GOOGLE-DRIVE-HOSTINGER-SETUP.md for details
GOOGLE_DRIVE_CLIENT_EMAIL=
GOOGLE_DRIVE_PRIVATE_KEY=
GOOGLE_DRIVE_FOLDER_ID=

# AWS S3 (Optional - for full 3-2-1 strategy)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

# Sanctum API (for authentication)
SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

**Important:** Replace `YOUR_DETAILS` with actual values!

### Step 3: Save the File

```bash
# In nano editor: Ctrl+O, Enter, Ctrl+X

# Or using cat method:
cat > .env << 'EOF'
APP_NAME=Quotation
APP_ENV=production
# ... paste full config above ...
EOF
```

### Step 4: Generate Application Key

```bash
# Generate Laravel APP_KEY
php artisan key:generate

# Output example:
# Application key [base64:***...***] set successfully.

# Verify it's in .env
grep APP_KEY .env
```

### Step 5: Set File Permissions

```bash
# Make storage directory writable
chmod -R 775 storage/

# Make bootstrap cache writable
chmod -R 775 bootstrap/cache/

# Make public directory writable (for uploads)
chmod -R 775 public/

# Verify permissions
ls -la storage/
ls -la bootstrap/cache/
```

---

## Backup & Restore Setup

### Step 1: Create Required Directories

```bash
# Create backup directory
mkdir -p storage/app/Laravel

# Create safety backup directory
mkdir -p storage/app/safety-backups

# Create Google Drive backup directory (for future use)
mkdir -p storage/app/google-backups

# Set permissions
chmod -R 755 storage/app/Laravel
chmod -R 755 storage/app/safety-backups
chmod -R 755 storage/app/google-backups
```

### Step 2: Verify Backup Configuration

```bash
# Check backup config
cat config/backup.php | grep -A 10 "name\|database"

# Expected output should show:
# - backup name: quotation
# - databases: quotation
# - compression: zip
```

### Step 3: Create Initial Backup

```bash
# Run first backup manually
php artisan backup:run

# Check if backup was created
ls -lah storage/app/Laravel/

# Should show: quotation-*.zip (around 10-100MB depending on data)
```

### Step 4: Schedule Backups (via Cron)

**On Hostinger Control Panel:**
1. Go to Cron Jobs
2. Add new cron job
3. Command: `/usr/bin/php /home/username/public_html/artisan schedule:run >> /dev/null 2>&1`
4. Frequency: Every 1 minute (Laravel scheduler will handle daily runs)
5. Save

**Or via SSH:**
```bash
# Edit crontab
crontab -e

# Add this line:
* * * * * cd /home/username/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1

# Save: Ctrl+O, Enter, Ctrl+X

# Verify cron
crontab -l
```

**Result:** Backups will run daily at:
- 02:00 AM (main backup)
- 03:00 AM (if first failed)

### Step 5: Test Backup Access via Dashboard

1. Log in to your application
2. Go to Admin → Backup & Restore
3. Click "Create Backup" button
4. Wait for completion
5. Verify backup appears in list
6. Try Download button
7. Verify local backups display in 3-2-1 status

---

## Final Configuration

### Step 1: Clear Cache

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Verify cache is cleared
ls -la storage/framework/cache/
```

### Step 2: Migrate & Seed Database

```bash
# Run migrations (if needed)
php artisan migrate

# Seed initial data (roles, permissions, users)
php artisan db:seed --class=DatabaseSeeder

# Verify users were created
php artisan tinker
> User::all()->pluck('username', 'name');
# Should show: Jomilo (admin), Redcrislan (staff)
> exit
```

### Step 3: Publish Assets

```bash
# Publish Laravel assets
php artisan vendor:publish --tag=laravel-assets --force

# Verify public directory
ls -la public/
# Should include: css/, js/, storage/, Image/, favicon.ico
```

### Step 4: Set Application to Production

```bash
# Update .env
nano .env

# Change these values:
# APP_DEBUG=false
# APP_ENV=production
```

### Step 5: Verify File Structure

```bash
# Check all critical directories exist
test -d storage && echo "✓ storage/" || echo "✗ storage missing"
test -d bootstrap/cache && echo "✓ bootstrap/cache" || echo "✗ bootstrap/cache missing"
test -d public && echo "✓ public/" || echo "✗ public missing"
test -f .env && echo "✓ .env exists" || echo "✗ .env missing"
test -f artisan && echo "✓ artisan exists" || echo "✗ artisan missing"

# All should show ✓
```

---

## Verification & Testing

### Step 1: Check Website Status

```bash
# Visit your Hostinger domain
https://your-domain.com

# Should display login page (no errors)
```

### Step 2: Test Login

**Admin Account:**
- Username: `jomilo`
- Password: `SecurePass@2025!Qtn` (from USER_CREDENTIALS.md)
- Should access admin dashboard

**Staff Account:**
- Username: `redcrislan`
- Password: `SecurePass@2025!Qtn` (from USER_CREDENTIALS.md)
- Should see limited staff features

### Step 3: Test Core Features

```
Quotations:
□ Create quotation
□ Edit quotation
□ Export PDF
□ Export DOCX
□ View draft list

Projects:
□ View projects
□ View archives

Materials:
□ View material list

Backup & Restore (Admin Only):
□ Create backup
□ Download backup
□ View backup list
□ Delete backup
□ Restore from backup (test on staging first!)
```

### Step 4: Test Backup & Restore

```bash
# SSH into Hostinger

# Create test backup
php artisan backup:run

# List backups
ls -lah storage/app/Laravel/

# Via Dashboard:
# 1. Login as admin (Jomilo)
# 2. Go to Backup & Restore
# 3. Click "Create Backup"
# 4. Wait for completion
# 5. Verify in list
# 6. Test Download
```

### Step 5: Check Error Logs

```bash
# Check Laravel error logs
tail -50 storage/logs/laravel.log

# Should show minimal errors in production mode
# Common expected: "No errors" or info messages only

# Check what errors exist
grep -i "error\|exception\|fatal" storage/logs/laravel.log | head -20
```

### Step 6: Test Database Connection

```bash
# SSH to Hostinger

# Test MySQL connection
mysql -u username -p -e "SELECT COUNT(*) as total_users FROM quotation.users;"

# Should output:
# +----+
# | total_users |
# +----+
# | 2 |  (or number of users you have)
```

---

## Post-Deployment Tasks

### Step 1: Change User Passwords

**First login for each user:**
1. Log in with temporary password: `SecurePass@2025!Qtn`
2. Navigate to Profile/Settings
3. Change password to something only they know
4. Log out and verify new password works

### Step 2: Set Up SSL Certificate

```bash
# Hostinger usually has free SSL via AutoSSL
# In Hostinger Control Panel:
1. Go to Domains
2. Select your domain
3. Go to SSL Certificates
4. Ensure AutoSSL is enabled
5. Verify green lock appears on website
```

### Step 3: Configure Email Notifications (Optional)

If you want email alerts for backups:

```env
# In .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com  # or your email provider
MAIL_PORT=587
MAIL_USERNAME=your-email@your-domain.com
MAIL_PASSWORD=your-email-password
MAIL_FROM_ADDRESS=noreply@your-domain.com

# Test email
php artisan tinker
> Mail::raw('Test email', fn($msg) => $msg->to('your-email@your-domain.com'));
> exit
```

### Step 4: Set Up Monitoring (Optional)

Consider setting up:
- ✓ Error tracking (Sentry, Rollbar)
- ✓ Uptime monitoring (UptimeRobot)
- ✓ Daily backup verification
- ✓ Log rotation

### Step 5: Update GitHub with Deployment Info

```bash
# Add deployment notes to your repository
git pull origin main
git add .
git commit -m "Deployment configuration for Hostinger"
git push origin main
```

---

## Troubleshooting

### Problem: "Composer command not found"

**Solution:**
```bash
# Check if composer is installed
which composer

# If not, install composer
cd ~
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### Problem: "Permission denied" on storage/

**Solution:**
```bash
# Fix permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# If still issues, set owner
sudo chown -R username:username storage/
sudo chown -R username:username bootstrap/cache/
```

### Problem: "mysql command not found"

**Solution:**
```bash
# MySQL client may not be in PATH
# Hostinger usually provides: /usr/bin/mysql

# Test
/usr/bin/mysql -u username -p

# Create alias
echo "alias mysql='/usr/bin/mysql'" >> ~/.bashrc
source ~/.bashrc
```

### Problem: "Connection refused" on database

**Solution:**
```bash
# Verify .env has correct database credentials
cat .env | grep DB_

# Check MySQL is running
ps aux | grep mysql

# If connection still fails:
# 1. Verify database created in Hostinger Control Panel
# 2. Check username/password in Control Panel matches .env
# 3. Contact Hostinger support - might be firewall issue
```

### Problem: "Backup creation failed"

**Solution:**
```bash
# Check MySQL dump command path
which mysqldump
# Should return: /usr/bin/mysqldump

# Verify in .env:
grep DUMP_COMMAND_PATH .env
# Should be: DUMP_COMMAND_PATH=/usr/bin

# Check storage permissions
ls -la storage/app/Laravel/

# Test backup manually
php artisan backup:run --verbose

# Check logs
tail -50 storage/logs/laravel.log
```

### Problem: "Class 'PDO' not found"

**Solution:**
```bash
# PHP PDO extension missing
# Contact Hostinger support to enable PDO for MySQL

# Verify extension is loaded
php -m | grep pdo_mysql

# Should show: pdo_mysql
```

### Problem: "Vendor directory is empty"

**Solution:**
```bash
# Composer install failed
# Re-run composer
composer install --no-dev

# Or with verbose output
composer install -vvv

# Check for errors and fix them
```

### Problem: "APP_KEY not set"

**Solution:**
```bash
# Generate APP_KEY
php artisan key:generate

# Verify it's in .env
grep APP_KEY .env

# Clear cache
php artisan cache:clear
```

---

## Deployment Checklist

Before considering deployment complete:

- [ ] SSH access works
- [ ] Files uploaded successfully
- [ ] PHP version is 8.0.2+
- [ ] All required PHP extensions present
- [ ] Composer installed and dependencies installed
- [ ] Database created and imported
- [ ] .env file created with correct credentials
- [ ] APP_KEY generated
- [ ] Permissions set (storage/, bootstrap/cache/, public/)
- [ ] Cache cleared
- [ ] Migrations run
- [ ] Database seeded
- [ ] Website loads without errors
- [ ] Login works with both admin and staff accounts
- [ ] Backup creation works
- [ ] Initial backup created and stored
- [ ] Cron job scheduled for daily backups
- [ ] SSL certificate active (green lock)
- [ ] Error logs checked and clean
- [ ] Database connection verified
- [ ] All core features tested

---

## Quick Reference Commands

```bash
# Check status
php artisan --version
composer --version
php -v
mysql --version

# Cache management
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Database
php artisan migrate
php artisan db:seed

# Backup
php artisan backup:run
ls -lah storage/app/Laravel/

# Logs
tail -50 storage/logs/laravel.log

# Permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# SSH connection
ssh username@your-domain.com
ssh username@xxx.xxx.xxx.xxx
```

---

## Support & Documentation

For additional help:

- **Laravel Docs**: https://laravel.com/docs/9.x
- **Spatie Backup**: https://spatie.be/docs/laravel-backup
- **Spatie Permission**: https://spatie.be/docs/laravel-permission
- **Hostinger Support**: https://www.hostinger.com/support
- **Your Guides**:
  - `USER_CREDENTIALS.md` - Login credentials
  - `GOOGLE-DRIVE-HOSTINGER-SETUP.md` - Google Drive backup
  - `docs/HOSTINGER-GDRIVE-SETUP.md` - Detailed backup setup
  - `README.md` - Project overview

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | Nov 30, 2025 | Initial Hostinger deployment guide |

---

**Created By**: GitHub Copilot  
**Project**: Quotation System  
**Status**: ✅ Ready for Production

⚠️ **IMPORTANT**: This guide is for production deployment. Test thoroughly on a staging environment first if possible. Always backup before making major changes.

