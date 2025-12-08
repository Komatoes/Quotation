# Backup & Restore — Visual Examples & Reference

## 📊 What You'll See When Running Commands

### Example 1: Creating a Backup

```
PS C:\xampp\htdocs\Quotation> php artisan backup:run
Starting backup...
Dumping database quotation...
Determining files to backup...
Zipping 1967 files and directories...
Created zip containing 1967 files and directories. Size is 89.13 MB
Copying zip to disk named local...
Successfully copied zip to disk named local.
Backup completed!
```

✅ **What it means**: Your database + all project files are now in a single `.zip` file in `storage/app/laravel-backup/`.

---

### Example 2: Listing Backups

```
PS C:\xampp\htdocs\Quotation> php artisan backup:list

+-----------+-------+-----------+---------+--------------+-----------------------+--------------+
| Name      | Disk  | Reachable | Healthy | # of backups | Newest backup         | Used storage |
+-----------+-------+-----------+---------+--------------+-----------------------+--------------+
| quotation | local | ✅         | ✅       |            3 | 1.00 (2 hours ago)    |    267.39 MB |
+-----------+-------+-----------+---------+--------------+-----------------------+--------------+
```

✅ **What it means**: You have 3 backups, the newest is 2 hours old, and they use 267 MB total storage.

---

### Example 3: Backup File Structure (After Extraction)

When you extract a backup zip, you'll see:

```
backup_2025-11-30-145530/
│
├── database/
│   └── quotation.sql                    ← Database dump (your data)
│
├── files/
│   ├── app/                              ← Laravel app files
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── public/
│   ├── storage/
│   ├── .env                              ← Configuration
│   ├── artisan
│   └── ... (all project files except vendor/ and node_modules/)
│
└── manifest.json                         ← Metadata about the backup
```

---

## 🔍 Where Are My Backups?

**Location**: `C:\xampp\htdocs\Quotation\storage\app\laravel-backup\`

**Filenames** follow this pattern: `backup_YYYY-MM-DD-HHMMSS.zip`

Examples:
- `backup_2025-11-30-021530.zip` (Nov 30, 2025 at 02:15:30)
- `backup_2025-11-29-021445.zip` (Nov 29, 2025 at 02:14:45)
- `backup_2025-11-28-021330.zip` (Nov 28, 2025 at 02:13:30)

---

## 🚀 Common Scenarios & How to Handle Them

### Scenario 1: "I Made a Mistake and Need to Restore Yesterday's Data"

1. **Find yesterday's backup**:
   ```powershell
   cd C:\xampp\htdocs\Quotation\storage\app\laravel-backup
   dir  # Lists all backup files
   ```

2. **Extract it**:
   ```powershell
   Expand-Archive -Path backup_2025-11-29-021445.zip -DestinationPath C:\temp\restore_nov29
   ```

3. **Restore the database**:
   ```powershell
   # First, backup current database (safety)
   "C:\xampp\mysql\bin\mysqldump.exe" -u root quotation > C:\temp\quotation_current.sql
   
   # Reset database
   "C:\xampp\mysql\bin\mysql.exe" -u root -e "DROP DATABASE IF EXISTS quotation; CREATE DATABASE quotation;"
   
   # Restore from backup
   "C:\xampp\mysql\bin\mysql.exe" -u root quotation < C:\temp\restore_nov29\database\quotation.sql
   ```

4. **Clear caches**:
   ```powershell
   cd C:\xampp\htdocs\Quotation
   php artisan cache:clear
   ```

5. **Done!** Your data is now from November 29.

---

### Scenario 2: "Database is Corrupted, Need Fresh Start"

1. **Get the latest backup**:
   ```powershell
   php artisan backup:list  # See which is newest
   ```

2. **Extract and restore** (same as Scenario 1)

3. **Run migrations** (if needed for any pending schema changes):
   ```powershell
   php artisan migrate
   ```

---

### Scenario 3: "I Want to Backup Only the Database (No Files)"

```powershell
php artisan backup:run --only-db
```

This creates a much smaller backup file (just the SQL dump + metadata).

---

### Scenario 4: "I Need to Move Backups to an External Drive"

1. **Copy all backups to external drive**:
   ```powershell
   Copy-Item -Path C:\xampp\htdocs\Quotation\storage\app\laravel-backup\* `
             -Destination D:\Backups\Quotation -Recurse
   ```

2. **Restore from external drive**:
   ```powershell
   Expand-Archive -Path D:\Backups\Quotation\backup_2025-11-30-145530.zip `
                  -DestinationPath C:\temp\restore
   
   # Then follow restore steps above
   ```

---

## 📅 Retention Policy (Auto-Cleanup)

The system automatically keeps backups based on this schedule:

| Time Period | Keep | Reason |
|-------------|------|--------|
| Last 7 days | ALL backups | Full granularity for recent mistakes |
| Days 8-23 | ONE per day | Saves space but keeps daily snapshots |
| Days 24-183 | ONE per week | Monthly review capability |
| Months 7-12 | ONE per month | Annual auditing |
| Year 2+ | ONE per year | Long-term archival |

**Auto-cleanup runs daily at 03:00** (after 02:00 backup completes).

You can disable/customize in `config/backup.php`:
```php
'cleanup' => [
    'default_strategy' => [
        'keep_all_backups_for_days' => 7,          // Change this number
        'keep_daily_backups_for_days' => 16,
        // ... more options
    ],
],
```

---

## ⚙️ Configuration Reference

### Backup Schedule
**File**: `app/Console/Kernel.php`

```php
// Runs daily at 02:00
$schedule->command('backup:run')->dailyAt('02:00')->withoutOverlapping();

// Cleanup runs daily at 03:00
$schedule->command('backup:clean')->dailyAt('03:00')->withoutOverlapping();
```

To change times, edit the times in `dailyAt('HH:MM')`.

### What Gets Backed Up
**File**: `config/backup.php`

```php
'include' => [
    base_path(),  // Entire project
],

'exclude' => [
    base_path('vendor'),       // Not included (rebuilt from composer)
    base_path('node_modules'), // Not included (rebuilt from npm)
],

'databases' => [
    'mysql',  // Backup MySQL database
],
```

---

## 🆘 Common Issues & Fixes

| Issue | Fix |
|-------|-----|
| **"mysqldump.exe not found"** | Use full path: `C:\xampp\mysql\bin\mysqldump.exe` |
| **Backup too large (> 500 MB)** | Use `backup:run --only-db` or exclude more folders in config |
| **Permission denied on storage folder** | Right-click folder → Properties → Security → Edit → Full Control for user |
| **Database won't restore** | Check MySQL is running: `"C:\xampp\mysql\bin\mysql.exe" -u root -e "SHOW DATABASES;"` |
| **Backup stuck/taking forever** | Stop with `Ctrl+C`, check if MySQL is responsive, increase timeout |
| **No backups in list** | Check: `storage/app/laravel-backup/` exists and is writable |

---

## ✨ Pro Tips

💡 **Tip 1**: Always backup your current database before restoring:
```powershell
"C:\xampp\mysql\bin\mysqldump.exe" -u root quotation > C:\temp\quotation_before_restore.sql
```

💡 **Tip 2**: Test restores on a copy of the database first:
```powershell
"C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE quotation_test;"
"C:\xampp\mysql\bin\mysql.exe" -u root quotation_test < C:\path\to\backup.sql
```

💡 **Tip 3**: Keep backups on multiple disks (local + external/cloud):
```powershell
# Copy to external drive weekly
Copy-Item -Path C:\xampp\htdocs\Quotation\storage\app\laravel-backup\* `
          -Destination E:\BackupArchive -Force
```

💡 **Tip 4**: Monitor backup size and clean up old ones:
```powershell
php artisan backup:clean  # Manually trigger cleanup
```

---

## 📞 Quick Command Reference

```powershell
# View all available backup commands
php artisan list backup

# Manual backup (files + database)
php artisan backup:run

# Database only
php artisan backup:run --only-db

# Files only
php artisan backup:run --only-files

# List all backups with status
php artisan backup:list

# Check backup health
php artisan backup:monitor

# Manual cleanup (prune old backups per policy)
php artisan backup:clean
```

---

That's everything you need! Start with creating a backup (`php artisan backup:run`) and you're ready. 🎉
