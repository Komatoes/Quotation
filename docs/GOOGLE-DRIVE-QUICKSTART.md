# 🚀 Google Drive Integration - Quick Start (5 Minutes)

**Goal**: Get Google Drive backups working on Hostinger  
**Time**: 30 minutes setup + 5 minutes testing  
**Complexity**: Medium

---

## ⚡ 30-Second Overview

Your backups will automatically upload to Google Drive every day at 02:00 AM alongside local backups.

**Flow**:
```
Daily Backup (02:00 AM)
    ↓
✅ Local backup created
    ↓
✅ Automatically uploaded to Google Drive
    ↓
✅ Both visible in dashboard
    ↓
🎉 Your data is redundant!
```

---

## 📋 What You Need

Before starting, gather these:
- [ ] Gmail/Google account
- [ ] Access to Google Cloud Console
- [ ] SSH access to Hostinger (for testing)

---

## 🎯 TL;DR - 3 Main Steps

### Step 1: Google Cloud Setup (10 min)
→ Get credentials from Google Cloud Console

### Step 2: Local Configuration (10 min)
→ Add credentials to your `.env` file

### Step 3: Deploy & Test (10 min)
→ Push to Hostinger and verify

---

## 📖 DETAILED STEPS

### STEP 1: Create Google Cloud Project

**Location**: https://console.cloud.google.com/

1. Click: **Select a Project** (top dropdown)
2. Click: **NEW PROJECT**
3. Name: `Quotation Backups`
4. Click: **CREATE**
5. Wait 2 minutes...

---

### STEP 2: Enable Google Drive API

**In Google Cloud Console**:

1. Left sidebar → **APIs & Services**
2. Button: **+ ENABLE APIS AND SERVICES**
3. Search: `Google Drive API`
4. Click result
5. Click: **ENABLE**

✅ Done!

---

### STEP 3: Create Service Account

**Why service account?** It's perfect for automatic server uploads (not OAuth).

1. Left sidebar → **APIs & Services** → **Credentials**
2. Button: **+ CREATE CREDENTIALS**
3. Select: **Service Account**
4. Name: `quotation-backups`
5. Click: **CREATE AND CONTINUE**
6. Role: Select dropdown → Search `drive` → Select **`Editor`**
7. Click: **CONTINUE**
8. Click: **DONE**

✅ Service account created!

---

### STEP 4: Create JSON Key

1. Find your service account in **Credentials** page
2. Click the service account name
3. Go to: **KEYS** tab
4. Button: **+ ADD KEY** → **Create new key**
5. Type: **JSON**
6. Click: **CREATE**

**A file downloads automatically** - this is your credentials! 
**SAVE IT SOMEWHERE SAFE** - you'll need it!

✅ Credentials downloaded!

---

### STEP 5: Get Service Account Email

1. Open the JSON file you just downloaded
2. Find this line:
   ```json
   "client_email": "quotation-backups@xxxxx.iam.gserviceaccount.com"
   ```
3. **COPY THIS EMAIL** (you'll use it next)

---

### STEP 6: Create & Share Google Drive Folder

1. Go to: https://drive.google.com/
2. Button: **+ New** → **Folder**
3. Name: `Quotation Backups`
4. Create it
5. Right-click folder → **Share**
6. Paste: The service account email (from step 5)
7. Permission: **Editor**
8. Click: **Share**

✅ Folder shared with service account!

---

### STEP 7: Get Folder ID

1. Open the **Quotation Backups** folder
2. Look at URL:
   ```
   https://drive.google.com/drive/folders/1ABC2DEF3GHI4JKL5MNOPQRST
                                         ↑ This is folder ID
   ```
3. **COPY THIS ID** (you'll need it)

---

## 💾 Local Setup (Your Machine)

### STEP 8: Install Package

```bash
cd C:\xampp\htdocs\Quotation
composer require nao-pon/google-drive-laravel
```

Wait for installation...

---

### STEP 9: Add to .env File

Open: `C:\xampp\htdocs\Quotation\.env`

Add at the bottom:

```env
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_FOLDER_ID=1ABC2DEF3GHI4JKL5MNOPQRST
GOOGLE_DRIVE_CREDENTIALS_PATH=storage/app/google-credentials.json
```

Replace `1ABC2DEF3GHI4JKL5MNOPQRST` with your actual folder ID (from step 7).

---

### STEP 10: Copy Credentials JSON

1. Take the JSON file from step 4
2. Copy to: `C:\xampp\htdocs\Quotation\storage\app\google-credentials.json`
3. Done!

---

### STEP 11: Test Locally

```bash
cd C:\xampp\htdocs\Quotation

# Create a backup
php artisan backup:run

# Check if uploaded to Google Drive
# Go to Google Drive and look in "Quotation Backups" folder
# Should see a new ZIP file!
```

✅ If you see the file in Google Drive, it works!

---

## 🌐 Deploy to Hostinger

### STEP 12: Push Code to Hostinger

```bash
# If using Git
git add .
git commit -m "Add Google Drive integration"
git push origin BACKUPANDRESTORE

# OR use FTP to upload files
# Copy all files to public_html/quotation
```

---

### STEP 13: SSH into Hostinger

```bash
ssh user@hostinger-server.com
cd public_html/quotation
```

---

### STEP 14: Install Dependencies

```bash
composer install
```

---

### STEP 15: Add .env Variables

On Hostinger server:

```bash
# Edit .env
nano .env

# Add same Google Drive variables:
# GOOGLE_DRIVE_ENABLED=true
# GOOGLE_DRIVE_FOLDER_ID=1ABC2DEF3GHI4JKL5MNOPQRST
# GOOGLE_DRIVE_CREDENTIALS_PATH=storage/app/google-credentials.json

# Save: Ctrl+X, Y, Enter
```

---

### STEP 16: Copy Credentials JSON

```bash
# On your local machine
scp storage/app/google-credentials.json user@hostinger-server.com:public_html/quotation/storage/app/

# Or manually upload via FTP
```

---

### STEP 17: Fix Permissions

```bash
# SSH into Hostinger, then:
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod 600 storage/app/google-credentials.json
```

---

### STEP 18: Test on Hostinger

```bash
php artisan backup:run

# Watch logs
tail -f storage/logs/laravel.log | grep "Google Drive"

# Should see: "Backup uploaded to Google Drive"
```

---

### STEP 19: Verify in Google Drive

1. Go to: https://drive.google.com/
2. Open: **Quotation Backups** folder
3. Should see: New ZIP file!

✅ **SUCCESS!**

---

## 🔍 Verify it Works

### Check 1: Dashboard
```
http://localhost/admin/backup/
(or on Hostinger: https://yoursite.com/admin/backup/)

Should show:
- Local: 4 (or more)
- GDrive: 1 (or more)
```

### Check 2: Google Drive
```
https://drive.google.com/
→ Quotation Backups folder
Should see ZIP files being added
```

### Check 3: Logs
```
storage/logs/laravel.log

Look for:
✅ "Backup uploaded to Google Drive"
❌ No error messages
```

---

## 🧪 Full Test Workflow

1. **Create backup locally** via GUI
   - Click [💾 Create Backup Now]
   - Wait for success
   
2. **Check Google Drive**
   - Should see new file in folder
   
3. **Check dashboard**
   - GDrive count should increase
   
4. **On Hostinger, same test**
   - SSH in, run `php artisan backup:run`
   - Check Google Drive
   - Check dashboard

---

## 📊 What Happens Daily (Automatic)

```
Every day at 02:00 AM:

1. Backup created locally ✅
2. Uploaded to Google Drive ✅
3. Backup cleaned up (old ones deleted)
4. GDrive backups cleaned up
5. All logged in storage/logs/laravel.log

You see nothing - it just works! 🚀
```

---

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| **"Credentials not found"** | Check path: `storage/app/google-credentials.json` exists? |
| **"Invalid folder ID"** | Get from URL: `https://drive.google.com/drive/folders/FOLDER_ID` |
| **Upload fails silently** | Check logs: `tail storage/logs/laravel.log` |
| **Permission error on Hostinger** | Run: `chmod 755 storage/app/` |
| **"Service account not authorized"** | Re-share Google Drive folder with service account email |
| **No backups appearing in GDrive** | Check if `GOOGLE_DRIVE_ENABLED=true` in `.env` |

---

## ✅ Verification Checklist

- [ ] Google Cloud project created
- [ ] Google Drive API enabled
- [ ] Service account created
- [ ] JSON credentials downloaded
- [ ] Google Drive folder created
- [ ] Folder shared with service account
- [ ] Folder ID retrieved
- [ ] Package installed locally
- [ ] `.env` updated with variables
- [ ] JSON copied to `storage/app/`
- [ ] Backup created locally and uploaded to GDrive
- [ ] Files deployed to Hostinger
- [ ] Credentials copied to Hostinger
- [ ] Permissions fixed on Hostinger
- [ ] Test backup created on Hostinger
- [ ] File appears in Google Drive
- [ ] Dashboard shows GDrive count

---

## 🎉 You're Done!

Your backup system now has:
- ✅ Local backups (daily at 02:00 AM)
- ✅ Google Drive backups (automatic upload)
- ✅ Redundancy (2 copies: local + cloud)
- ✅ Partial 3-2-1 compliance

**Next Optional Step**: Add AWS S3 for full 3-2-1 (offsite + backup2)

---

**Questions?** See full guide: `docs/GOOGLE-DRIVE-SETUP.md`

