# 📅 Scheduled Backups + Google Drive Integration Setup

Complete guide to enable automatic daily backups and sync them to Google Drive.

---

## Overview

**What will happen:**
- ✅ Every day at 02:00 AM: automatic backup runs
- ✅ Every day at 03:00 AM: old backups are cleaned up
- ✅ After each backup: latest backup is uploaded to Google Drive
- ✅ You can restore anytime from the admin dashboard

**Components:**
1. Laravel Task Scheduler (`kernel.php` + cron job on server)
2. Spatie Laravel Backup (`backup:run` and `backup:clean` commands)
3. Google Drive API (OAuth service account)
4. BackupManagementController (handles upload)

---

## Part 1: Local Setup (Windows XAMPP)

### Step 1.1: Configure Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. **New Project** → Name: `Quotation-Backups` → Create

### Step 1.2: Enable Google Drive API

1. In the top search bar type: `Google Drive API`
2. Click the result → **Enable**

### Step 1.3: Create Service Account

1. Left sidebar → **APIs & Services** → **Credentials**
2. **Create Credentials** → **Service Account**
3. Fill in:
   - Service account name: `quotation-backup-agent`
   - Click **Create and Continue**
4. On next screen, click **Continue** (skip optional steps)
5. On final screen, click **Done**

### Step 1.4: Create JSON Key

1. In Credentials page, click your service account name (quotation-backup-agent)
2. Go to **Keys** tab
3. **Add Key** → **Create new key** → Choose **JSON** → **Create**
4. A JSON file downloads — **keep it safe**

### Step 1.5: Create Google Drive Folder

1. Go to [Google Drive](https://drive.google.com)
2. **New** → **Folder** → Name: `Quotation-Backups`
3. Right-click the folder → **Share**
4. **From the JSON file** copy this email: `quotation-backup-agent@YOUR-PROJECT-ID.iam.gserviceaccount.com`
5. Paste it in Share dialog → Give **Editor** access → **Share**

### Step 1.6: Get Folder ID

1. Open the `Quotation-Backups` folder in Drive
2. The URL is: `https://drive.google.com/drive/folders/FOLDER-ID-HERE`
3. Copy the folder ID (the long string after `/folders/`)

### Step 1.7: Update `.env` with Google Drive Credentials

Open `.env` in your project root and add these lines:

```env
# === GOOGLE DRIVE BACKUP CONFIG ===
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_PROJECT_ID=YOUR-PROJECT-ID
GOOGLE_DRIVE_PRIVATE_KEY_ID=FROM-JSON-FILE
GOOGLE_DRIVE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nYOUR-KEY-HERE\n-----END PRIVATE KEY-----\n"
GOOGLE_DRIVE_CLIENT_EMAIL=quotation-backup-agent@YOUR-PROJECT-ID.iam.gserviceaccount.com
GOOGLE_DRIVE_CLIENT_ID=YOUR-CLIENT-ID
GOOGLE_DRIVE_FOLDER_ID=FOLDER-ID-YOU-COPIED
```

**How to extract from JSON file:**

Open the downloaded JSON file in a text editor:

```json
{
  "type": "service_account",
  "project_id": "YOUR-PROJECT-ID",        ← GOOGLE_DRIVE_PROJECT_ID
  "private_key_id": "abc123...",           ← GOOGLE_DRIVE_PRIVATE_KEY_ID
  "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",  ← GOOGLE_DRIVE_PRIVATE_KEY
  "client_email": "quotation-backup-agent@...",  ← GOOGLE_DRIVE_CLIENT_EMAIL
  "client_id": "123456789",                ← GOOGLE_DRIVE_CLIENT_ID
  ...
}
```

**Important:** When copying `private_key`, include the newlines (`\n`) exactly as shown.

### Step 1.8: Test Locally

```bash
cd c:\xampp\htdocs\Quotation

# Clear cache
php artisan config:clear

# Manually trigger a backup
php artisan backup:run

# Check logs
type storage/logs/laravel.log | findstr /R "Google|backup"

# You should see logs like:
# "Google Drive backup upload initiated"
# "Successfully uploaded backup to Google Drive"
```

---

## Part 2: Deploy to Production (Hostinger)

### Step 2.1: Push Code Changes

```bash
cd c:\xampp\htdocs\Quotation

# Commit controller and config changes
git add app/Http/Controllers/BackupManagementController.php config/services.php
git commit -m "Add scheduled backups and Google Drive integration"
git push origin BACKUPANDRESTORE
```

### Step 2.2: SSH to Hostinger and Pull Changes

```bash
ssh your-user@jomsconstruction.com
cd /home/u620524563/domains/jomsconstruction.com/Quotation

# Pull latest code
git pull origin BACKUPANDRESTORE

# Clear caches
php artisan config:clear
php artisan cache:clear
```

### Step 2.3: Add `.env` Variables on Hostinger

Edit the `.env` file on the server and add the same Google Drive credentials:

```bash
# On the server, edit your .env
nano .env

# Add these lines at the end:
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_PROJECT_ID=YOUR-PROJECT-ID
GOOGLE_DRIVE_PRIVATE_KEY_ID=abc123...
GOOGLE_DRIVE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nYOUR-KEY\n-----END PRIVATE KEY-----\n"
GOOGLE_DRIVE_CLIENT_EMAIL=quotation-backup-agent@...
GOOGLE_DRIVE_CLIENT_ID=123456789
GOOGLE_DRIVE_FOLDER_ID=YOUR-FOLDER-ID

# Save: Ctrl+O → Enter → Ctrl+X
```

### Step 2.4: Set Up Cron Job on Hostinger

The Laravel scheduler runs via a single cron job that checks every minute. Add it:

```bash
# Still SSH'd into Hostinger
crontab -e

# Add this single line:
* * * * * cd /home/u620524563/domains/jomsconstruction.com/Quotation && php artisan schedule:run >> /dev/null 2>&1

# Save: Ctrl+O → Enter → Ctrl+X
```

**What this does:**
- Runs every minute (`* * * * *`)
- Changes to your app directory
- Executes `php artisan schedule:run`
- Silently discards output (`>> /dev/null 2>&1`)

**The Laravel scheduler then checks if any tasks are due (02:00 AM backup, 03:00 AM cleanup, etc.)**

### Step 2.5: Verify Cron Is Set

```bash
# List your cron jobs (still SSH'd)
crontab -l

# You should see your new line
```

---

## Part 3: Test & Monitor

### Manual Test (Force Backup Now)

```bash
# SSH to server
ssh your-user@jomsconstruction.com
cd /home/u620524563/domains/jomsconstruction.com/Quotation

# Manually run backup (don't wait for 02:00 AM)
php artisan backup:run

# Check logs
tail -100 storage/logs/laravel.log

# Look for:
# "Restore process started" or "backup running"
# "Successfully uploaded backup to Google Drive"
```

### Monitor Scheduled Runs

Check logs after 02:00 AM your server timezone:

```bash
# SSH to server
ssh your-user@jomsconstruction.com
cd /home/u620524563/domains/jomsconstruction.com/Quotation

# Check if backup ran
grep -i "backup\|google" storage/logs/laravel.log | tail -50

# Expected output:
# "Restore process started for backup: 2025-11-30-02-00-00.zip"
# "Successfully uploaded backup to Google Drive"
```

### Check Google Drive

1. Open [Google Drive](https://drive.google.com)
2. Go to `Quotation-Backups` folder
3. You should see backup ZIP files appearing daily

---

## Part 4: Troubleshooting

### Issue: Backups not running at 02:00 AM

**Check 1: Is cron running?**
```bash
# SSH to server
ssh your-user@jomsconstruction.com

# List cron jobs
crontab -l

# Should show: * * * * * cd /home/u620524563/... php artisan schedule:run
```

**Check 2: Does Laravel scheduler know about the task?**
```bash
# SSH to server
cd /path/to/app
php artisan schedule:list

# Should show:
# backup:run     Every day at 2:00 AM ....

# If not, verify app/Console/Kernel.php has:
# $schedule->command('backup:run')->dailyAt('02:00')->withoutOverlapping();
```

**Check 3: Check logs for errors**
```bash
tail -200 storage/logs/laravel.log | grep -i error
```

### Issue: Google Drive upload failing

**Check 1: Credentials in .env?**
```bash
grep GOOGLE_DRIVE .env
# All 7 variables should be present
```

**Check 2: Private key formatted correctly?**
```bash
# In .env, PRIVATE_KEY should have literal \n characters, like:
GOOGLE_DRIVE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nMIIEvgIA...\n-----END PRIVATE KEY-----\n"

# NOT actual newlines (that would break the .env file)
```

**Check 3: Service account has Drive permissions?**
```bash
# Go to Google Drive → Quotation-Backups folder → Share
# Verify quotation-backup-agent@project-id.iam.gserviceaccount.com has Editor access
```

**Check 4: Test credentials manually**
```bash
ssh your-user@jomsconstruction.com
cd /path/to/app
php artisan backup:run

# Watch logs:
tail -f storage/logs/laravel.log

# You should see detailed Google Drive upload logs
```

### Issue: Cron job not executing

**Check 1: Hostinger cron logs**
```bash
ssh your-user@jomsconstruction.com

# Try to find cron execution logs (location varies by host)
grep CRON /var/log/syslog 2>/dev/null | tail -20
# or
journalctl -u cron 2>/dev/null | tail -20
```

**Check 2: Add a simple test cron job**
```bash
crontab -e

# Add a test line that writes to a file:
* * * * * echo "Cron working" >> /home/u620524563/test-cron.txt

# Wait 2 minutes, then check:
cat /home/u620524563/test-cron.txt

# If file doesn't exist or isn't updated, cron is blocked
# Contact Hostinger support to enable cron
```

### Issue: Backup runs but upload is slow

**Normal behavior:**
- Small DB (< 100 MB): 2-5 seconds
- Medium DB (100 MB - 1 GB): 10-30 seconds
- Large DB (> 1 GB): May take 1-2 minutes

**If upload times out (500 error):**
- Increase PHP timeout in cron job:
  ```bash
  crontab -e
  # Change to:
  * * * * * cd /path/to/app && timeout 600 php artisan schedule:run >> /dev/null 2>&1
  ```

---

## Part 5: Configuration Summary

### `.env` Variables Needed

```env
# Required for scheduled backups
DB_DATABASE=quotation_db
DB_USERNAME=quotation_user
DB_PASSWORD=your_password
DB_HOST=localhost

# Required for Google Drive upload
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_PROJECT_ID=your-project-id
GOOGLE_DRIVE_PRIVATE_KEY_ID=key-id
GOOGLE_DRIVE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n"
GOOGLE_DRIVE_CLIENT_EMAIL=quotation-backup-agent@project-id.iam.gserviceaccount.com
GOOGLE_DRIVE_CLIENT_ID=client-id
GOOGLE_DRIVE_FOLDER_ID=folder-id-from-drive
```

### Backup Schedule (in `app/Console/Kernel.php`)

```php
protected function schedule(Schedule $schedule)
{
    // Daily backup at 02:00 AM
    $schedule->command('backup:run')->dailyAt('02:00')->withoutOverlapping();

    // Daily cleanup at 03:00 AM
    $schedule->command('backup:clean')->dailyAt('03:00')->withoutOverlapping();
}
```

### Cron Job (on Hostinger server)

```bash
* * * * * cd /home/u620524563/domains/jomsconstruction.com/Quotation && php artisan schedule:run >> /dev/null 2>&1
```

---

## Quick Checklist

- [ ] Google Cloud Project created
- [ ] Google Drive API enabled
- [ ] Service account created with JSON key
- [ ] Google Drive folder shared with service account
- [ ] `.env` updated with 7 Google Drive variables (local machine)
- [ ] Code pushed to repository
- [ ] Code pulled on Hostinger
- [ ] `.env` updated with Google Drive variables (Hostinger)
- [ ] Cron job added on Hostinger (`crontab -e`)
- [ ] Cron job verified (`crontab -l`)
- [ ] Manual backup tested (`php artisan backup:run`)
- [ ] Logs show successful Google Drive upload
- [ ] Backup ZIP files appearing in Google Drive folder

---

## 24-Hour Timeline (Example)

**Day 1:**
- 02:00 AM: Backup runs → uploads to Google Drive
- 03:00 AM: Old backups cleaned up (keeps last 5)

**Day 2:**
- 02:00 AM: Another backup → uploads to Google Drive
- ... repeat daily

**To restore:**
- Log in as admin → Backup Management
- Choose a backup → Click "Restore"
- Select a backup from Google Drive (future feature)

---

## Support

If you encounter issues:

1. **Check logs first:**
   ```bash
   ssh your-user@jomsconstruction.com
   cd /path/to/app
   tail -200 storage/logs/laravel.log | grep -i -E "backup|google|error"
   ```

2. **Verify cron is installed:**
   ```bash
   crontab -l
   ```

3. **Test backup manually:**
   ```bash
   php artisan backup:run
   tail -50 storage/logs/laravel.log
   ```

4. **Check Google Drive folder for files:**
   - Drive → Quotation-Backups → should see .zip files

---

**Status:** ✅ All components implemented  
**Version:** 1.0  
**Last Updated:** November 30, 2025
