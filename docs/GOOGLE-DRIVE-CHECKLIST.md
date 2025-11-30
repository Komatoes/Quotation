# ✅ Google Drive Setup Checklist

**Print this out!** Check off as you complete each step.

---

## 🔵 PHASE 1: Google Cloud Console Setup (10 minutes)

### Create Project
- [ ] Go to: https://console.cloud.google.com/
- [ ] Click: "Select a Project" dropdown
- [ ] Click: "NEW PROJECT"
- [ ] Name: "Quotation Backups"
- [ ] Click: "CREATE"
- [ ] Wait 2-3 minutes for creation

### Enable Google Drive API
- [ ] Left sidebar: "APIs & Services"
- [ ] Button: "+ ENABLE APIS AND SERVICES"
- [ ] Search: "Google Drive API"
- [ ] Click: "Google Drive API" result
- [ ] Button: "ENABLE"
- [ ] Wait 30 seconds

### Create Service Account
- [ ] Left sidebar: "APIs & Services" → "Credentials"
- [ ] Button: "+ CREATE CREDENTIALS"
- [ ] Select: "Service Account"
- [ ] Service account name: "quotation-backups"
- [ ] Click: "CREATE AND CONTINUE"
- [ ] Grant roles: Search "drive" → Select "Editor"
- [ ] Click: "CONTINUE" → "DONE"

### Create JSON Key
- [ ] Find service account: "quotation-backups"
- [ ] Click on service account name
- [ ] Tab: "KEYS"
- [ ] Button: "+ ADD KEY" → "Create new key"
- [ ] Type: "JSON"
- [ ] Button: "CREATE"
- [ ] ✅ **File downloads** - SAVE IT!

**SAVE THIS FILE PATH**: `_____________________`

---

## 🔵 PHASE 2: Get Your Credentials (5 minutes)

### Extract Info from JSON
- [ ] Open the downloaded JSON file
- [ ] Find line: `"client_email": "..."`
- [ ] **COPY**: `_____________________`
- [ ] Find line: `"type": "service_account"`
- [ ] Verify it's there

### Create Google Drive Folder
- [ ] Go to: https://drive.google.com/
- [ ] Button: "+ New" → "Folder"
- [ ] Name: "Quotation Backups"
- [ ] Create it
- [ ] Right-click folder → "Share"
- [ ] Paste: The client_email
- [ ] Permission: "Editor"
- [ ] Click: "Share"

### Get Folder ID
- [ ] Open "Quotation Backups" folder
- [ ] Look at URL bar
- [ ] Copy folder ID from URL
- [ ] Folder ID: `_____________________`

---

## 🔵 PHASE 3: Local Setup (10 minutes)

### Install Package
- [ ] Terminal: `cd C:\xampp\htdocs\Quotation`
- [ ] Terminal: `composer require nao-pon/google-drive-laravel`
- [ ] Wait for installation (~2 minutes)

### Edit .env File
- [ ] Open: `C:\xampp\htdocs\Quotation\.env`
- [ ] Go to bottom
- [ ] Add these lines:
```
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_FOLDER_ID=YOUR_FOLDER_ID_HERE
GOOGLE_DRIVE_CREDENTIALS_PATH=storage/app/google-credentials.json
```
- [ ] Replace `YOUR_FOLDER_ID_HERE` with your folder ID
- [ ] Save file

### Copy JSON Credentials
- [ ] Take JSON file from Phase 1
- [ ] Copy to: `C:\xampp\htdocs\Quotation\storage\app\google-credentials.json`
- [ ] Verify file exists in that location

### Test Local Backup
- [ ] Terminal: `cd C:\xampp\htdocs\Quotation`
- [ ] Terminal: `php artisan backup:run`
- [ ] Wait 2-5 minutes
- [ ] Terminal: Check no errors shown
- [ ] Go to: https://drive.google.com/
- [ ] Open: "Quotation Backups" folder
- [ ] ✅ **New ZIP file should be there!**

---

## 🟢 PHASE 4: Deploy to Hostinger (15 minutes)

### Push Code
- [ ] Terminal: `git add .`
- [ ] Terminal: `git commit -m "Add Google Drive integration"`
- [ ] Terminal: `git push origin BACKUPANDRESTORE`
- [ ] Wait for push to complete

**OR if using FTP:**
- [ ] Upload all files to Hostinger: `public_html/quotation/`

### SSH into Hostinger
- [ ] Terminal: `ssh user@hostinger-server.com`
- [ ] Terminal: `cd public_html/quotation`
- [ ] Terminal: `pwd` (verify correct location)

### Install Dependencies
- [ ] Terminal: `composer install`
- [ ] Wait 2-3 minutes for completion
- [ ] Verify no errors

### Configure Hostinger .env
- [ ] Terminal: `nano .env`
- [ ] Go to bottom
- [ ] Add same lines as local:
```
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_FOLDER_ID=YOUR_FOLDER_ID_HERE
GOOGLE_DRIVE_CREDENTIALS_PATH=storage/app/google-credentials.json
```
- [ ] Save: `Ctrl+X` → `Y` → `Enter`

### Copy JSON to Hostinger
- [ ] **Local machine** Terminal: 
```
scp storage/app/google-credentials.json user@hostinger-server.com:public_html/quotation/storage/app/
```
- [ ] OR: Upload via FTP to `public_html/quotation/storage/app/google-credentials.json`

### Fix Permissions
- [ ] Terminal (on Hostinger): `chmod -R 755 storage/`
- [ ] Terminal: `chmod 600 storage/app/google-credentials.json`
- [ ] Terminal: `chmod -R 755 bootstrap/cache/`

### Test on Hostinger
- [ ] Terminal: `php artisan backup:run`
- [ ] Wait 2-5 minutes
- [ ] Terminal: No errors?
- [ ] Terminal: `tail storage/logs/laravel.log | grep "Google Drive"`
- [ ] Should show: "Backup uploaded to Google Drive"

### Verify File Uploaded
- [ ] Go to: https://drive.google.com/
- [ ] Open: "Quotation Backups" folder
- [ ] ✅ **New ZIP file from Hostinger should be there!**

---

## 🟢 PHASE 5: Final Verification (5 minutes)

### Check Dashboard Locally
- [ ] Browser: http://localhost/admin/backup/
- [ ] Card: "Google Drive: X" (should be > 0)
- [ ] If 0, GDrive upload not working

### Check Dashboard on Hostinger
- [ ] Browser: https://yoursite.com/admin/backup/
- [ ] Card: "Google Drive: X" (should be > 0)
- [ ] If 0, check logs

### Check Logs
- [ ] Local: `storage/logs/laravel.log`
- [ ] Hostinger: SSH → `tail -f storage/logs/laravel.log`
- [ ] Look for: "Backup uploaded to Google Drive: "
- [ ] No errors?

### Daily Automatic Test
- [ ] Wait until: Tomorrow 02:00 AM (or manually test)
- [ ] Check Google Drive folder
- [ ] Should see new backup
- [ ] Check dashboard: GDrive count increased?

---

## 🧪 TROUBLESHOOTING

### If upload fails:

**Check 1: Is Google Drive enabled?**
- [ ] `.env` has `GOOGLE_DRIVE_ENABLED=true`
- [ ] If not: Add it and save

**Check 2: Is folder ID correct?**
- [ ] Go to Google Drive folder
- [ ] Copy ID from URL again
- [ ] Update `.env` GOOGLE_DRIVE_FOLDER_ID

**Check 3: Is JSON file in right place?**
- [ ] File exists: `storage/app/google-credentials.json`
- [ ] If not: Copy it again

**Check 4: Is service account authorized?**
- [ ] Go to Google Drive folder
- [ ] Right-click → Share
- [ ] Is service account email listed as Editor?
- [ ] If not: Re-share it

**Check 5: What do logs say?**
- [ ] `tail storage/logs/laravel.log | grep -i error`
- [ ] Look for error messages
- [ ] Search error message on Google

---

## ✅ SUCCESS SIGNS

When everything works, you'll see:

- [ ] ✅ Backup created locally at 02:00 AM
- [ ] ✅ Backup uploaded to Google Drive automatically
- [ ] ✅ Dashboard shows GDrive count > 0
- [ ] ✅ ZIP file visible in Google Drive folder
- [ ] ✅ Logs show: "Backup uploaded to Google Drive"
- [ ] ✅ No error messages in logs
- [ ] ✅ Both local AND GDrive backups visible in dashboard

---

## 📊 Expected Result After Setup

```
BEFORE Setup:
┌──────────────────┐
│ Local: 4         │
│ GDrive: 0 ❌     │
│ S3: 0            │
└──────────────────┘

AFTER Setup:
┌──────────────────┐
│ Local: 4         │
│ GDrive: 1+ ✅    │
│ S3: 0            │
└──────────────────┘
```

---

## 🎉 All Done!

Once all checkmarks are filled:
- ✅ You have Google Drive backups
- ✅ Automatic daily uploads working
- ✅ Your data is redundant (2 copies)
- ✅ You're compliant with 3-2-1 strategy (partially)

**Next Step (Optional)**: Add AWS S3 for full 3-2-1 compliance

---

**Date Completed**: `_________________`

**Completed By**: `_________________`

**Notes**: 
```
_________________________________
_________________________________
_________________________________
```

