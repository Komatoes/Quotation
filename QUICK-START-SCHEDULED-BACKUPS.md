# ⚡ Scheduled Backups & Google Drive - Quick Start

## TL;DR Setup (5 Steps)

### 1. Create Google Cloud Project & Service Account
```
Go to console.cloud.google.com
→ New Project: "Quotation-Backups"
→ Search "Google Drive API" → Enable
→ APIs & Services → Credentials → New Service Account
→ Name: quotation-backup-agent
→ Keys → Add Key → JSON (save file)
```

### 2. Create Google Drive Folder & Share
```
Google Drive → New Folder: "Quotation-Backups"
→ Share → paste service account email → Editor access
→ Copy folder ID from URL
```

### 3. Update `.env` (Local & Hostinger)

Extract from downloaded JSON and add to `.env`:
```env
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_PROJECT_ID=your-project-id
GOOGLE_DRIVE_PRIVATE_KEY_ID=key-id-from-json
GOOGLE_DRIVE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nKEY-CONTENT\n-----END PRIVATE KEY-----\n"
GOOGLE_DRIVE_CLIENT_EMAIL=quotation-backup-agent@project-id.iam.gserviceaccount.com
GOOGLE_DRIVE_CLIENT_ID=client-id-from-json
GOOGLE_DRIVE_FOLDER_ID=folder-id-from-drive-url
```

### 4. Push Code & Deploy

**Local:**
```bash
git add app/Http/Controllers/BackupManagementController.php config/services.php
git commit -m "Add scheduled backups and Google Drive"
git push origin BACKUPANDRESTORE
```

**Hostinger (SSH):**
```bash
ssh your-user@jomsconstruction.com
cd /path/to/app
git pull origin BACKUPANDRESTORE
nano .env  # Add Google Drive variables
php artisan config:clear
```

### 5. Add Cron Job (Hostinger only)

```bash
ssh your-user@jomsconstruction.com
crontab -e

# Add this line:
* * * * * cd /home/u620524563/domains/jomsconstruction.com/Quotation && php artisan schedule:run >> /dev/null 2>&1

# Save: Ctrl+O → Enter → Ctrl+X
```

## Verify It Works

**Check logs after 02:00 AM:**
```bash
ssh your-user@jomsconstruction.com
cd /path/to/app
grep -i "backup\|google" storage/logs/laravel.log | tail -20
```

**Should see:**
```
Restore process started for backup: 2025-11-30-02-00-00.zip
Successfully uploaded backup to Google Drive: 2025-11-30-02-00-00.zip
```

**Check Google Drive:**
- Open [Google Drive](https://drive.google.com) → Quotation-Backups
- Should see new .zip files appearing daily

## Schedule

- **02:00 AM daily:** Automatic backup runs & uploads to Google Drive
- **03:00 AM daily:** Old backups cleaned up (keeps last 5)
- **Anytime:** Can manually restore from admin dashboard

## Troubleshooting

| Issue | Fix |
|-------|-----|
| Backups not running | Check `crontab -l` shows the cron job |
| Google Drive upload fails | Verify all 7 GOOGLE_DRIVE_* vars in .env |
| Private key error | Ensure private key has literal `\n` characters |
| Cron blocked | Contact Hostinger to enable cron on your account |

## Files Modified

- `app/Http/Controllers/BackupManagementController.php` - Added Google Drive upload methods
- `config/services.php` - Added google.drive config section
- `.env` (local & Hostinger) - Added 7 Google Drive credentials

## Full Documentation

See `SCHEDULED-BACKUPS-GOOGLE-DRIVE-SETUP.md` for detailed step-by-step guide with screenshots, troubleshooting, and examples.

---

**Status:** ✅ Complete  
**Time to setup:** ~30 minutes
