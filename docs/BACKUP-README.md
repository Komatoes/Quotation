# Backup & Restore — Complete Documentation

## 📁 Documents in This Folder

You now have **three backup/restore guides**:

1. **`BACKUP-QUICKSTART.md`** ← **START HERE**
   - Simple step-by-step instructions
   - How to create a backup (2 minutes)
   - How to restore a backup (5 minutes)
   - Automatic scheduling setup

2. **`BACKUP-VISUAL-GUIDE.md`**
   - What you'll see when running commands
   - Where files are stored
   - Common scenarios (database corrupted, undo mistakes, etc.)
   - Configuration reference
   - Troubleshooting

3. **`backup-restore.md`**
   - Detailed technical documentation
   - Configuration options
   - Advanced customization
   - S3/cloud storage setup

---

## 🎯 Quick Start (5 Minutes)

### Create a Backup
```powershell
cd C:\xampp\htdocs\Quotation
php artisan backup:run
```

### See All Backups
```powershell
php artisan backup:list
```

### Restore (Basic)
```powershell
# 1. Extract backup
Expand-Archive -Path C:\xampp\htdocs\Quotation\storage\app\laravel-backup\backup_2025-11-30-145530.zip `
               -DestinationPath C:\temp\restore

# 2. Reset database
"C:\xampp\mysql\bin\mysql.exe" -u root -e "DROP DATABASE IF EXISTS quotation; CREATE DATABASE quotation;"

# 3. Restore database
"C:\xampp\mysql\bin\mysql.exe" -u root quotation < C:\temp\restore\database\quotation.sql

# 4. Clear caches
php artisan cache:clear
```

---

## 🔄 The Backup System at a Glance

```
┌─────────────────────────────────────────────────────────────┐
│                   Your Application                          │
│  Database (quotation) + Files (app/, config/, etc.)        │
└────────────────┬────────────────────────────────────────────┘
                 │
                 │ php artisan backup:run
                 ↓
┌─────────────────────────────────────────────────────────────┐
│              Spatie Laravel Backup                          │
│  • Dumps MySQL database to SQL file                        │
│  • Zips database + project files                           │
│  • Saves as backup_YYYY-MM-DD-HHMMSS.zip                 │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│         storage/app/laravel-backup/                         │
│  • backup_2025-11-30-145530.zip (89 MB)                   │
│  • backup_2025-11-29-021445.zip (88 MB)                   │
│  • backup_2025-11-28-021330.zip (87 MB)                   │
└─────────────────────────────────────────────────────────────┘

Daily at 03:00 → Cleanup removes old backups per retention policy
```

---

## 📋 What's Included in Each Backup?

✅ **Included**:
- MySQL database (`quotation` table data + schema)
- All application code (Laravel files)
- Configuration files
- Public assets (images, CSS, JS)
- Logs and sessions

❌ **NOT Included** (automatically excluded):
- `vendor/` folder (rebuilt with `composer install`)
- `node_modules/` folder (rebuilt with `npm install`)
- Temporary cache files
- Old backup files

---

## ⏱️ How Long Does It Take?

| Operation | Time |
|-----------|------|
| Create backup | 1-2 minutes |
| List backups | < 5 seconds |
| Extract backup zip | 30 seconds |
| Restore database | 30 seconds |
| Clear caches | 5 seconds |
| **Total restore time** | **~2-3 minutes** |

---

## 🗂️ Where Backups Are Stored

**Primary location**: `C:\xampp\htdocs\Quotation\storage\app\laravel-backup\`

**Each backup file**:
- Filename: `backup_YYYY-MM-DD-HHMMSS.zip`
- Size: 80-100 MB (typical)
- Contains: database + files + metadata

**Backup retention**:
- Keep: Last 7 days (all), then 1 per day for 16 days, then 1 per week, etc.
- Auto-cleanup: Runs daily at 03:00
- Manual cleanup: `php artisan backup:clean`

---

## 🛠️ System Setup (What's Already Done)

✅ **Installed**: `spatie/laravel-backup` package
✅ **Configured**: `config/backup.php` with XAMPP paths
✅ **Scheduled**: `app/Console/Kernel.php` with daily backup/cleanup
✅ **Fixed**: Windows MySQL path (uses `C:\xampp\mysql\bin`)
✅ **Documented**: This guide + BACKUP-QUICKSTART.md

---

## 🚀 Automatic Backups (Optional Setup)

The backups are **already scheduled** in `app/Console/Kernel.php` to run at:
- **02:00** - Full backup
- **03:00** - Cleanup old backups

To **enable automatic execution**, set up a task scheduler:

### Windows Task Scheduler (One-time setup)

1. Open Task Scheduler: `Win + R` → `taskschd.msc` → Enter
2. Right-click "Task Scheduler Library" → "Create Basic Task"
3. Name: "Laravel Backup Scheduler"
4. Trigger: Daily at 02:00 (or every 1 minute)
5. Action: Run program
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `C:\xampp\htdocs\Quotation\artisan schedule:run`
   - Start in: `C:\xampp\htdocs\Quotation`
6. Click OK → Done

Now backups run automatically! ✅

### Linux/Mac (One-time setup)

Add to crontab (`crontab -e`):

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## ❓ FAQs

**Q: How often should I backup?**
- A: Daily (already scheduled). More if you make frequent changes.

**Q: How long do backups keep?**
- A: 7 days (all backups), then monthly/yearly. Configurable in `config/backup.php`.

**Q: Can I backup just the database?**
- A: Yes: `php artisan backup:run --only-db`

**Q: Can I restore to a different server?**
- A: Yes, extract the backup and copy files/database to new server.

**Q: What if backup fails?**
- A: Check logs: `storage/logs/laravel.log`
- Check MySQL is running: `"C:\xampp\mysql\bin\mysql.exe" -u root -e "SHOW DATABASES;"`

**Q: Can I backup to cloud (S3)?**
- A: Yes (advanced). Edit `config/backup.php` and add S3 disk config.

---

## 📚 File References

| File | Purpose |
|------|---------|
| `config/backup.php` | Backup settings (what to include, retention policy) |
| `app/Console/Kernel.php` | Scheduled tasks (when backups run) |
| `app/Providers/AppServiceProvider.php` | Service setup (MySQL path for Windows) |
| `.env` | Environment variables (backup disk, notification email) |
| `storage/app/laravel-backup/` | Where backups are stored |

---

## ✨ Key Takeaways

1. **Backups run daily** at 02:00 (already set up)
2. **Manual backup anytime**: `php artisan backup:run`
3. **Restore** by extracting zip, restoring SQL, clearing caches
4. **Location**: `storage/app/laravel-backup/` (80-100 MB each)
5. **Retention**: 7 days all backups, then monthly/yearly archive

---

## 🎓 Next Steps

1. **Test a backup now**:
   ```powershell
   php artisan backup:run
   php artisan backup:list
   ```

2. **Read `BACKUP-QUICKSTART.md`** for detailed step-by-step restore instructions

3. **Enable automatic scheduling** (Task Scheduler on Windows or cron on Linux)

4. **Test a restore** once to verify it works (practice run!)

5. **(Optional) Setup cloud storage** for off-site backups

---

## 📞 Support

For more details, see:
- **Quick steps**: `BACKUP-QUICKSTART.md`
- **Visual examples**: `BACKUP-VISUAL-GUIDE.md`
- **Technical reference**: `backup-restore.md`

Or check the official docs: https://github.com/spatie/laravel-backup

---

**Status**: ✅ Backup system is **installed, configured, and ready to use!**

Start with: `php artisan backup:run`
