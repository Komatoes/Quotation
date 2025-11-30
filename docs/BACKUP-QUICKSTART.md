# Quick Start: Backup & Restore Step-by-Step

## 📦 CREATE A BACKUP

### Step 1: Open PowerShell (Windows)
Navigate to your project folder:

```powershell
cd C:\xampp\htdocs\Quotation
```

### Step 2: Run the Backup Command
```powershell
php artisan backup:run
```

Expected output:
```
Starting backup...
Dumping database quotation...
Determining files to backup...
Zipping 1967 files and directories...
Created zip containing 1967 files and directories. Size is 89.13 MB
Copying zip to disk named local...
Successfully copied zip to disk named local.
Backup completed!
```

### Step 3: Verify Backup Was Created
```powershell
php artisan backup:list
```

You'll see a table showing:
- **Name**: "quotation" (or your APP_NAME)
- **# of backups**: Number of backup files
- **Newest backup**: When the last backup was created
- **Used storage**: Total size of all backups

**Backup files are stored in**: `C:\xampp\htdocs\Quotation\storage\app\laravel-backup`

---

## 🔄 RESTORE FROM A BACKUP

### Step 1: List Available Backups
```powershell
cd C:\xampp\htdocs\Quotation
php artisan backup:list
```

Note the backup filename (e.g., `backup_2025-11-30-140530.zip`).

### Step 2: Locate and Extract the Backup
Navigate to the backups folder:

```powershell
cd C:\xampp\htdocs\Quotation\storage\app\laravel-backup
```

You'll see `.zip` files. Pick the one you want to restore from. Extract it to a temporary folder:

```powershell
Expand-Archive -Path backup_2025-11-30-140530.zip -DestinationPath C:\temp\restore
```

Inside `C:\temp\restore`, you'll see:
```
backup_2025-11-30-140530/
├── database/
│   └── quotation.sql
├── files/
│   ├── app/
│   ├── config/
│   ├── resources/
│   └── ... (all project files)
└── manifest.json
```

### Step 3: Backup Your Current Database (IMPORTANT - Safety First!)

Before restoring, back up your current database:

```powershell
"C:\xampp\mysql\bin\mysqldump.exe" -u root quotation > C:\temp\quotation_backup_current.sql
```

This saves your current database in case you need to rollback.

### Step 4: Reset the Database
Drop the current database and create a fresh one:

```powershell
"C:\xampp\mysql\bin\mysql.exe" -u root -e "DROP DATABASE IF EXISTS quotation; CREATE DATABASE quotation;"
```

### Step 5: Restore the Database from Backup
```powershell
"C:\xampp\mysql\bin\mysql.exe" -u root quotation < C:\temp\restore\database\quotation.sql
```

You should see no errors. The database is now restored.

### Step 6: Restore Project Files (Optional but Recommended)
Copy the backup files back to the project:

```powershell
# Copy everything from backup/files back to project root
Copy-Item -Path C:\temp\restore\files\* -Destination C:\xampp\htdocs\Quotation -Recurse -Force
```

⚠️ **Warning**: This will overwrite your current files. Backup first!

### Step 7: Clear Laravel Caches
```powershell
cd C:\xampp\htdocs\Quotation
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 8: Verify the Restore
1. Open your application in the browser: `http://localhost/Quotation`
2. Log in with your credentials
3. Check that data matches the backup date
4. Verify files are accessible (quotations, materials, etc.)

---

## ⏰ AUTOMATIC BACKUPS (Optional - Setup Once)

To have backups run automatically every day at 02:00 and 03:00:

### On Windows with Task Scheduler:

1. **Open Task Scheduler**:
   - Press `Win + R`, type `taskschd.msc`, press Enter

2. **Create a new task**:
   - Right-click "Task Scheduler Library" → "Create Task..."

3. **General tab**:
   - Name: `Laravel Backup Scheduler`
   - Check: "Run whether user is logged in or not"

4. **Triggers tab**:
   - New → Daily at 02:00 (or every 1 minute to let Laravel handle it)

5. **Actions tab**:
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `C:\xampp\htdocs\Quotation\artisan schedule:run`
   - Start in: `C:\xampp\htdocs\Quotation`

6. **Conditions tab**:
   - Uncheck "Stop the task if it runs longer than"

7. **Click OK** and provide admin password when prompted.

Now backups will run automatically!

---

## 📋 QUICK REFERENCE

| Task | Command |
|------|---------|
| Create backup | `php artisan backup:run` |
| List backups | `php artisan backup:list` |
| Backup database only | `php artisan backup:run --only-db` |
| Backup files only | `php artisan backup:run --only-files` |
| Check backup health | `php artisan backup:monitor` |

---

## ✅ TROUBLESHOOTING

**Q: "mysqldump is not recognized"**
- A: Ensure you're using the full path: `"C:\xampp\mysql\bin\mysqldump.exe"`

**Q: Backup file is too large**
- A: Try `php artisan backup:run --only-db` for database only, or adjust excludes in `config/backup.php`

**Q: Database won't restore ("access denied")**
- A: Ensure MySQL is running. Test with: `"C:\xampp\mysql\bin\mysql.exe" -u root -e "SHOW DATABASES;"`

**Q: Files restored but pages are broken**
- A: Run `php artisan cache:clear` and `php artisan migrate` to refresh application state

**Q: No backups appear in list**
- A: Check folder exists: `C:\xampp\htdocs\Quotation\storage\app\laravel-backup`
- A: Check permissions on `storage` folder (should be writable)

---

## 🎯 SUMMARY

- **Backup**: `php artisan backup:run` (takes 1-2 minutes)
- **List**: `php artisan backup:list` (shows all backups)
- **Restore Database**: Extract `.sql` file → `mysql -u root quotation < file.sql`
- **Restore Files**: Extract zip → Copy files back to project
- **Automate**: Set up Windows Task Scheduler to run `php artisan schedule:run` every minute

That's it! You're covered for backup and restore. 🎉
