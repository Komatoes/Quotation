# Backup & Restore — Quick Reference Card

## 🎯 BACKUP (Create)

```
QUICK: Open PowerShell, type this:

cd C:\xampp\htdocs\Quotation
php artisan backup:run
```

**Takes**: 1-2 minutes
**Output**: "Backup completed!"
**Stored**: `C:\xampp\htdocs\Quotation\storage\app\laravel-backup\`

---

## 📋 LIST BACKUPS

```
php artisan backup:list
```

Shows:
- How many backups exist
- Size of each backup
- When the newest one was created
- Health status (✅ or ❌)

---

## 🔄 RESTORE (Full Steps)

### Step 1: Extract Backup
```
cd C:\xampp\htdocs\Quotation\storage\app\laravel-backup
Expand-Archive -Path backup_2025-11-30-145530.zip -DestinationPath C:\temp\restore
```

### Step 2: Backup Current Database (Safety)
```
"C:\xampp\mysql\bin\mysqldump.exe" -u root quotation > C:\temp\quotation_current.sql
```

### Step 3: Reset Database
```
"C:\xampp\mysql\bin\mysql.exe" -u root -e "DROP DATABASE IF EXISTS quotation; CREATE DATABASE quotation;"
```

### Step 4: Restore Database
```
"C:\xampp\mysql\bin\mysql.exe" -u root quotation < C:\temp\restore\database\quotation.sql
```

### Step 5: Clear Caches
```
cd C:\xampp\htdocs\Quotation
php artisan cache:clear
```

**Takes**: ~3 minutes total

---

## ⏰ AUTOMATIC BACKUPS (Windows)

### Setup One-Time:
1. Open: `Win + R` → `taskschd.msc` → Enter
2. Right-click "Task Scheduler Library"
3. "Create Basic Task"
4. Name: "Laravel Backup"
5. Trigger: Daily at 02:00
6. Action → Run Program:
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `C:\xampp\htdocs\Quotation\artisan schedule:run`
   - Start in: `C:\xampp\htdocs\Quotation`
7. OK → Done

**Now**: Backups run automatically daily at 02:00 ✅

---

## 📚 WHERE TO FIND WHAT YOU NEED

| Want | Read |
|------|------|
| Overview | `docs/BACKUP-README.md` |
| How-to | `docs/BACKUP-QUICKSTART.md` |
| Examples | `docs/BACKUP-VISUAL-GUIDE.md` |
| Technical | `docs/backup-restore.md` |
| Checklist | `docs/BACKUP-CHECKLIST.md` |

---

## 🆘 COMMON ISSUES

| Problem | Fix |
|---------|-----|
| "mysqldump not found" | Use full path: `C:\xampp\mysql\bin\mysqldump.exe` |
| Backup too large | Run: `php artisan backup:run --only-db` |
| Can't restore | Check MySQL is running in XAMPP Control Panel |
| No backups found | Check folder: `C:\xampp\htdocs\Quotation\storage\app\laravel-backup\` |

---

## 📊 WHAT'S INCLUDED?

✅ **In Backup**:
- Database (all data)
- Application code
- Config files
- Logs & sessions

❌ **NOT in Backup**:
- vendor/ (can rebuild)
- node_modules/ (can rebuild)

---

## ✨ KEY COMMANDS

```
# Create
php artisan backup:run

# List
php artisan backup:list

# Monitor
php artisan backup:monitor

# Database only
php artisan backup:run --only-db

# Files only
php artisan backup:run --only-files

# Cleanup old backups
php artisan backup:clean
```

---

## 📍 BACKUP STORAGE

**Location**: `storage/app/laravel-backup/`

**Filename format**: `backup_YYYY-MM-DD-HHMMSS.zip`

**Size**: ~80-100 MB each

**Retention**: 7 days (all), then 1 per day for 16 days, then 1 per week, etc.

---

## ⏱️ TIMING

| Task | Time |
|------|------|
| Create backup | 1-2 min |
| List backups | < 5 sec |
| Extract zip | 30 sec |
| Restore DB | 30 sec |
| Clear caches | 5 sec |
| **Full restore** | **~3 min** |

---

## ✅ STATUS

- ✅ Backup system installed
- ✅ Configuration done
- ✅ Schedule set up
- ✅ Documentation complete
- ✅ Ready to use

**Try now**: `php artisan backup:run`

---

## 📞 NEED HELP?

1. Check: `docs/BACKUP-CHECKLIST.md`
2. Read: `docs/BACKUP-VISUAL-GUIDE.md` (Troubleshooting section)
3. Ask: Your DevOps person with error message

---

## 🎓 QUICK START (5 MINUTES)

```powershell
# 1. Backup now
cd C:\xampp\htdocs\Quotation
php artisan backup:run

# 2. See backups
php artisan backup:list

# 3. Done! ✅
```

**Next**: Read `docs/BACKUP-QUICKSTART.md` for restore steps

---

**Print this page or save as reference!** 📋
