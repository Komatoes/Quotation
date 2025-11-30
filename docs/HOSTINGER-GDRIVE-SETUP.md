# 🌐 Hostinger Deployment Guide - Google Drive Integration

**Status**: Step-by-step for Hostinger  
**Complexity**: Beginner-Intermediate  
**Time**: 20 minutes

---

## 📍 About Hostinger

Hostinger is where you'll host your production application. This guide explains how to set up Google Drive backups specifically for Hostinger deployment.

---

## 🎯 Your Hostinger Setup

Typical Hostinger setup:
```
Your Domain: yoursite.com
Root Folder: public_html/
App Folder: public_html/quotation/
SSH: ✅ Available
Database: ✅ MySQL
PHP: ✅ 8.0+
Composer: ✅ Available
```

---

## 📋 Pre-Setup Checklist

Before you start, have these ready:

- [ ] Google Cloud credentials (from local setup)
- [ ] JSON file: `google-credentials.json`
- [ ] Google Drive folder ID
- [ ] Hostinger SSH access details
- [ ] Local app deployed to Hostinger (code uploaded)

---

## 🚀 Hostinger Setup Steps

### Step 1: SSH into Hostinger

```bash
# Command:
ssh user@hostinger-server.com

# Then navigate to app:
cd public_html/quotation

# Verify you're in right place:
pwd
# Should output: /home/user/public_html/quotation
```

### Step 2: Install Composer Dependencies

```bash
composer install

# Wait 2-3 minutes...
# Should complete with no errors
```

### Step 3: Configure .env File

Edit your `.env` file on Hostinger:

```bash
nano .env
```

Navigate to the bottom and add:

```env
# Google Drive Backup Configuration
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_FOLDER_ID=YOUR_FOLDER_ID_HERE
GOOGLE_DRIVE_CREDENTIALS_PATH=app/google-credentials.json
```

Replace:
- `YOUR_FOLDER_ID_HERE` with your actual Google Drive folder ID

Save file:
- Press: `Ctrl+X`
- Press: `Y`
- Press: `Enter`

### Step 4: Upload JSON Credentials

**From your local machine**, copy credentials to Hostinger:

**Option A: Using SCP (Recommended)**
```bash
# On your local machine terminal:
scp storage/app/google-credentials.json user@hostinger-server.com:public_html/quotation/storage/app/

# You'll be prompted for password
```

**Option B: Using FTP**
1. Open your FTP client
2. Connect to Hostinger
3. Navigate to: `public_html/quotation/storage/app/`
4. Upload: `google-credentials.json`

**Option C: Create file directly on server**
```bash
# SSH into Hostinger, then:
cd public_html/quotation/storage/app/

nano google-credentials.json

# Paste entire JSON content from your local file
# Save: Ctrl+X, Y, Enter
```

### Step 5: Fix File Permissions

```bash
# SSH into Hostinger, in app directory:

# Make storage writable
chmod -R 755 storage/

# Make credentials file secure
chmod 600 storage/app/google-credentials.json

# Make bootstrap cache writable
chmod -R 755 bootstrap/cache/

# Verify permissions
ls -la storage/app/google-credentials.json
# Should show: -rw------- (600 permissions)
```

### Step 6: Clear Cache (Important!)

```bash
php artisan config:cache
php artisan config:clear
php artisan cache:clear
```

---

## 🧪 Test on Hostinger

### Test 1: Create a Backup

```bash
# SSH into Hostinger
cd public_html/quotation

# Create backup
php artisan backup:run

# Wait 2-5 minutes...
```

### Test 2: Check Logs

```bash
# See last 20 lines of logs
tail -20 storage/logs/laravel.log

# Look for line containing "Google Drive"
# Should see: "Backup uploaded to Google Drive"
```

### Test 3: Verify in Google Drive

1. Go to: https://drive.google.com/
2. Open folder: "Quotation Backups"
3. You should see new ZIP file from Hostinger!

### Test 4: Check Dashboard

1. Go to: `https://yoursite.com/admin/backup/`
2. Card should show: "GDrive: X" (where X > 0)
3. If shows 0, something's wrong (check logs)

---

## 🔍 Troubleshooting on Hostinger

### Issue: "Credentials file not found"

**Solution 1**: Verify file exists
```bash
ls -la storage/app/google-credentials.json
# Should show file exists
```

**Solution 2**: Check path in `.env`
```bash
# SSH, then:
grep GOOGLE_DRIVE_CREDENTIALS_PATH .env
# Should show: app/google-credentials.json
```

**Solution 3**: Verify full path
```bash
# Should work:
cat storage/app/google-credentials.json
# Should show JSON content (not error)
```

### Issue: "Permission denied"

**Solution**: Fix permissions
```bash
chmod -R 755 storage/
chmod 600 storage/app/google-credentials.json
chmod -R 755 bootstrap/cache/
```

### Issue: No backup uploaded

**Solution 1**: Check if enabled
```bash
grep GOOGLE_DRIVE_ENABLED .env
# Should show: true
```

**Solution 2**: Check logs for errors
```bash
tail -50 storage/logs/laravel.log | grep -i "google\|error"
# Look for actual error message
```

**Solution 3**: Verify folder ID
```bash
grep GOOGLE_DRIVE_FOLDER_ID .env
# Should show your folder ID (not blank)
```

### Issue: "Invalid folder ID"

**Solution**: Get correct ID from Google Drive
```
https://drive.google.com/drive/folders/1ABC2DEF3GHI4JKL5MNOPQRST
                                     ↑↑↑ Copy this part
```

Then update:
```bash
nano .env
# Update: GOOGLE_DRIVE_FOLDER_ID=1ABC2DEF3GHI4JKL5MNOPQRST
# Save: Ctrl+X, Y, Enter
```

### Issue: Service account not authorized

**Solution**: Re-share folder in Google Drive
1. Go to: https://drive.google.com/
2. Open folder: "Quotation Backups"
3. Right-click → Share
4. Add service account email as Editor
5. Click Share

---

## ✅ Verify Everything Works

### Checklist:

- [ ] SSH into Hostinger successfully
- [ ] `cd public_html/quotation` works
- [ ] `composer install` completed
- [ ] `.env` updated with Google Drive variables
- [ ] JSON file copied to `storage/app/`
- [ ] Permissions set correctly (600 for JSON)
- [ ] `php artisan backup:run` completes
- [ ] Logs show "Backup uploaded to Google Drive"
- [ ] File appears in Google Drive folder
- [ ] Dashboard shows GDrive count > 0

All checked? ✅ **You're done!**

---

## 📊 Daily Automatic Backups

After setup, your backups will work like this:

```
Every Day at 02:00 AM (UTC):

1. Laravel Scheduler triggers
2. Backup created locally
3. Uploaded to Google Drive
4. Old backups cleaned up (local & GDrive)
5. All logged in storage/logs/laravel.log

You do nothing - it just works! 🚀
```

---

## 🔐 Security on Hostinger

### Best Practices:

1. **Protect `.env`**
   ```bash
   chmod 600 .env
   ```

2. **Protect credentials JSON**
   ```bash
   chmod 600 storage/app/google-credentials.json
   ```

3. **Don't commit credentials** to Git
   ```
   In .gitignore:
   .env
   storage/app/google-credentials.json
   ```

4. **Use strong database passwords**
   - Already in .env, keep them secret

5. **Restrict backup folder permissions**
   ```bash
   chmod 755 storage/app/Laravel/
   chmod 600 storage/app/google-credentials.json
   ```

---

## 📈 Monitoring

### Check backup status daily:

```bash
# SSH into Hostinger
cd public_html/quotation

# See latest backups
php artisan backup:list

# Check for errors
tail -20 storage/logs/laravel.log | grep -i error

# Monitor logs in real-time
tail -f storage/logs/laravel.log
# Press Ctrl+C to exit
```

### Set up alerts (Optional):

1. Go to: https://drive.google.com/
2. Right-click "Quotation Backups" folder
3. Add notification if preferred

---

## 🎯 Next Steps

### After Hostinger Setup:

1. ✅ Monitor daily backups (02:00 AM)
2. ✅ Verify files in Google Drive folder
3. ✅ Check dashboard occasionally
4. ✅ Test restore monthly
5. ⏳ (Optional) Add AWS S3 for full 3-2-1

---

## 📖 Related Guides

- **Setup Overview**: `GOOGLE-DRIVE-SETUP.md`
- **Quick Start**: `GOOGLE-DRIVE-QUICKSTART.md`
- **Checklist**: `GOOGLE-DRIVE-CHECKLIST.md`
- **Code Details**: `GOOGLE-DRIVE-IMPLEMENTATION.md`

---

## 🆘 Still Need Help?

### Common Issues:

| Problem | Guide Section |
|---------|---------------|
| "File not found" | [Troubleshooting](#-troubleshooting-on-hostinger) |
| "Permission denied" | [Fix Permissions](#step-5-fix-file-permissions) |
| "Upload fails" | [Test on Hostinger](#-test-on-hostinger) |
| "No backups appear" | [Troubleshooting](#issue-no-backup-uploaded) |

### Get help:

1. Check troubleshooting section above
2. Review logs: `tail storage/logs/laravel.log`
3. Verify all steps completed
4. Ask in comments with specific error message

---

## ✨ You're All Set!

Your Hostinger backup system with Google Drive is ready!

**Verify once more**:
- [ ] Dashboard shows GDrive backups
- [ ] Logs show successful uploads
- [ ] Files appear in Google Drive
- [ ] Daily backups scheduled

**🎉 Success!**

---

**Version**: 1.0  
**Created**: November 30, 2025  
**Status**: Ready for Hostinger Deployment

