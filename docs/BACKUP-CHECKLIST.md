# Backup & Restore Checklist

## ✅ System Setup (Already Completed)

- [x] `spatie/laravel-backup` package installed
- [x] `config/backup.php` configured for XAMPP
- [x] `app/Console/Kernel.php` scheduled daily backups
- [x] `AppServiceProvider.php` fixed Windows MySQL path
- [x] `.env` updated with backup settings
- [x] Backup storage folder created: `storage/app/laravel-backup/`
- [x] Documentation completed

**Status**: ✅ **Ready to use!**

---

## 🎯 Quick Start (Right Now - 5 Minutes)

### Step 1: Create Your First Backup
```powershell
cd C:\xampp\htdocs\Quotation
php artisan backup:run
```

**Expected**: Takes 1-2 minutes, shows "Backup completed!"

- [ ] ✅ Backup created successfully

### Step 2: Verify Backup Was Saved
```powershell
php artisan backup:list
```

**Expected**: Shows a table with 1 backup in the list

- [ ] ✅ Backup appears in list
- [ ] ✅ File size is 80+ MB
- [ ] ✅ "Healthy" column shows ✅

### Step 3: Check Backup File Physically Exists
```powershell
cd C:\xampp\htdocs\Quotation\storage\app\laravel-backup
dir
```

**Expected**: Shows `backup_2025-11-30-XXXXXX.zip` file

- [ ] ✅ Backup zip file exists
- [ ] ✅ File size is reasonable (80-100 MB)

---

## 🔄 Restore Test (Practice - 10 Minutes)

Do this once to verify restore works before you need it:

### Step 1: Extract Backup
```powershell
cd C:\xampp\htdocs\Quotation\storage\app\laravel-backup
# Pick the newest backup file
$backupFile = (Get-ChildItem -Name | Sort-Object -Descending | Select-Object -First 1)
Expand-Archive -Path $backupFile -DestinationPath C:\temp\restore_test
```

- [ ] ✅ Extraction succeeded without errors

### Step 2: Safety Backup (Just in Case)
```powershell
"C:\xampp\mysql\bin\mysqldump.exe" -u root quotation > C:\temp\quotation_backup_current.sql
```

- [ ] ✅ Current database backed up to C:\temp\quotation_backup_current.sql

### Step 3: Restore Database from Test Backup
```powershell
# Create test database
"C:\xampp\mysql\bin\mysql.exe" -u root -e "DROP DATABASE IF EXISTS quotation_test; CREATE DATABASE quotation_test;"

# Restore to test database
"C:\xampp\mysql\bin\mysql.exe" -u root quotation_test < C:\temp\restore_test\database\quotation.sql
```

- [ ] ✅ Test restore completed without errors

### Step 4: Verify Test Database Has Data
```powershell
"C:\xampp\mysql\bin\mysql.exe" -u root quotation_test -e "SELECT COUNT(*) AS TableCount FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'quotation_test';"
```

**Expected**: Shows number > 0

- [ ] ✅ Test database has tables
- [ ] ✅ Restore works correctly

### Step 5: Clean Up Test Database
```powershell
"C:\xampp\mysql\bin\mysql.exe" -u root -e "DROP DATABASE quotation_test;"
```

- [ ] ✅ Test database deleted

---

## ⏰ Automatic Scheduling (Optional but Recommended)

### Windows Task Scheduler Setup (One-time)

- [ ] Open Task Scheduler (`Win + R` → `taskschd.msc`)
- [ ] Create Basic Task → "Laravel Backup Scheduler"
- [ ] Set Trigger: Daily at 02:00
- [ ] Set Action:
  - Program: `C:\xampp\php\php.exe`
  - Arguments: `C:\xampp\htdocs\Quotation\artisan schedule:run`
  - Start in: `C:\xampp\htdocs\Quotation`
- [ ] Enable the task
- [ ] ✅ Automatic daily backups enabled

### Linux/Mac Cron Setup (One-time)

- [ ] Open terminal and run: `crontab -e`
- [ ] Add this line:
  ```
  * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
  ```
- [ ] Save and exit
- [ ] ✅ Automatic daily backups enabled

---

## 📋 Ongoing Maintenance

### Weekly Tasks

- [ ] Check backup is created (every Monday):
  ```powershell
  php artisan backup:list
  ```

- [ ] Verify latest backup is recent and "Healthy" ✅

### Monthly Tasks

- [ ] Review backup storage size:
  ```powershell
  (Get-ChildItem -Path C:\xampp\htdocs\Quotation\storage\app\laravel-backup\ | Measure-Object -Property Length -Sum).Sum / 1MB
  ```
  Should be 250-500 MB (3-5 backups)

- [ ] Manual cleanup if storage > 500 MB:
  ```powershell
  php artisan backup:clean
  ```

### Quarterly Tasks

- [ ] Test a full restore on a backup from 3 months ago
- [ ] Verify all data matches expectations
- [ ] Document any restore issues

---

## 🆘 Troubleshooting Quick Fixes

### Issue: "No backups found"

- [ ] Check folder exists: `C:\xampp\htdocs\Quotation\storage\app\laravel-backup\`
- [ ] Check folder permissions (should be writable)
- [ ] Try manual backup: `php artisan backup:run`

### Issue: "mysqldump not found"

- [ ] Verify MySQL is running (XAMPP Control Panel)
- [ ] Use full path in commands: `C:\xampp\mysql\bin\mysqldump.exe`

### Issue: Database won't restore

- [ ] Check MySQL is running
- [ ] Test connection: `"C:\xampp\mysql\bin\mysql.exe" -u root -e "SHOW DATABASES;"`
- [ ] Ensure database exists: `"C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE quotation;"`

### Issue: Backup file too large (> 200 MB)

- [ ] Backup only database: `php artisan backup:run --only-db`
- [ ] Or exclude more folders in `config/backup.php`

---

## 📚 Documentation Files

| File | For What |
|------|----------|
| **`BACKUP-README.md`** | Overview & system info |
| **`BACKUP-QUICKSTART.md`** | Step-by-step how-to guide |
| **`BACKUP-VISUAL-GUIDE.md`** | Examples & common scenarios |
| **`backup-restore.md`** | Technical reference |

**Read Order**: README → QUICKSTART → VISUAL-GUIDE → Technical

---

## 🎯 Remember

✅ **System is ready** — backups can be created immediately
✅ **Test restore** — do this once to practice
✅ **Enable scheduler** — set up Task Scheduler or cron for automatic backups
✅ **Monitor weekly** — quick check that backups are running

---

## 📞 Quick Reference Commands

```powershell
# Backup operations
php artisan backup:run                    # Create backup now
php artisan backup:run --only-db          # Database only
php artisan backup:run --only-files       # Files only
php artisan backup:list                   # See all backups
php artisan backup:monitor                # Check backup health
php artisan backup:clean                  # Clean old backups manually

# MySQL operations
"C:\xampp\mysql\bin\mysqldump.exe" -u root quotation > backup.sql       # Export DB
"C:\xampp\mysql\bin\mysql.exe" -u root quotation < backup.sql           # Import DB

# Clear caches after restore
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## ✨ Success Criteria

Once you've completed this checklist, you have:

- ✅ **Working backups** (can create them manually)
- ✅ **Verified restores** (tested restore process)
- ✅ **Automatic scheduling** (optional but recommended)
- ✅ **Documentation** (know where to find help)
- ✅ **Peace of mind** (data is protected!)

**Congratulations! Your backup system is complete.** 🎉

---

**Date Completed**: _______________

**Last Backup Created**: _______________

**Last Restore Test**: _______________
