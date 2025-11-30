# Backup & Restore — Spatie Laravel Backup (Implemented)

## Overview
This project uses **Spatie Laravel Backup** to perform full backups of files and database. Backups are automatically scheduled daily and are compressed and stored locally in `storage/app/laravel-backup`.

## What's Already Configured

1. **Spatie Package**: `spatie/laravel-backup` is installed.
2. **Service Provider Binding**: Fixed container binding for `CleanupStrategy` in `AppServiceProvider.php`.
3. **Windows Path Fix**: Configured to use XAMPP's `mysqldump` on Windows (`C:\xampp\mysql\bin`).
4. **Config File**: `config/backup.php` with sensible defaults (7-day retention).
5. **Scheduled Tasks**: Daily `backup:run` at 02:00 and daily `backup:clean` at 03:00 (in `app/Console/Kernel.php`).
6. **Backup Storage**: Backups are stored in `storage/app/laravel-backup` as `.zip` files.

## Verify Backups Work

Run a manual backup to verify the setup:

```powershell
cd C:\xampp\htdocs\Quotation
php artisan backup:run
```

Expected output:
```
Starting backup...
Dumping database quotation...
Determining files to backup...
Zipping <N> files and directories...
Created zip containing <N> files and directories. Size is <SIZE> MB
Copying zip to disk named local...
Successfully copied zip to disk named local.
Backup completed!
```

List backups:

```powershell
php artisan backup:list
```

Sample output:
```
+---------+-------+-----------+---------+--------------+-----------------------+--------------+
| Name    | Disk  | Reachable | Healthy | # of backups | Newest backup         | Used storage |
+---------+-------+-----------+---------+--------------+-----------------------+--------------+
| Laravel | local | ✅         | ✅       |            3 | 0.00 (53 seconds ago) |    178.25 MB |
+---------+-------+-----------+---------+--------------+-----------------------+--------------+
```

## Enable Scheduled Backups

### On Linux (Cron)
Add this line to your crontab (`crontab -e`) to run Laravel scheduler every minute:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

This will trigger `backup:run` daily at 02:00 and `backup:clean` daily at 03:00.

### On Windows (XAMPP with Task Scheduler)
1. Open **Task Scheduler** on Windows.
2. Create a new task with:
   - **Trigger**: Repeat daily at 02:00 (or run every 1 minute to let Laravel scheduler handle the timing).
   - **Action**: Run program
     - Program: `C:\xampp\php\php.exe`
     - Arguments: `"C:\xampp\htdocs\Quotation\artisan" schedule:run`
     - Start in: `C:\xampp\htdocs\Quotation`
3. Save and enable the task.

Alternatively, run the backup manually once per day:

```powershell
php artisan backup:run
```

## Restore from Backup

### Manual Restore Process

1. **Locate backup file**:
   - Backups are stored in `storage/app/laravel-backup/` as `.zip` files.
   - Download or access the backup file.

2. **Extract backup**:
   - Unzip the backup file to a temporary folder.
   - The structure will typically include:
     ```
     backup-<timestamp>.zip
     ├── database/
     │   └── quotation.sql
     ├── files/
     │   └── (project files and directories)
     └── manifest.json
     ```

3. **Restore database**:
   ```powershell
   "C:\xampp\mysql\bin\mysql.exe" -u root -p quotation < "C:\path\to\backup\database\quotation.sql"
   ```
   - Prompt for MySQL password (empty by default on XAMPP).
   - If the database exists, this will overwrite it; create a fresh database if needed first:
     ```sql
     DROP DATABASE IF EXISTS quotation;
     CREATE DATABASE quotation;
     ```

4. **Restore files** (optional):
   - Copy files from `backup/files/` back to the project root, preserving folder structure.
   - Be careful with `node_modules`, `vendor`, and `.git` — you may want to rebuild these from source files instead.

5. **Clear caches**:
   ```powershell
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

6. **Verify**:
   - Log in to the application and verify data is present.
   - Check logs in `storage/logs/laravel.log` for any errors.

## Customization

### Change Backup Retention Policy
Edit `config/backup.php` and adjust:

```php
'cleanup' => [
    'default_strategy' => [
        'keep_all_backups_for_days' => 7,          // Keep all backups for 7 days
        'keep_daily_backups_for_days' => 16,       // Then keep one daily for 16 days
        'keep_weekly_backups_for_weeks' => 8,      // Then keep one weekly for 8 weeks
        'keep_monthly_backups_for_months' => 4,    // Then keep one monthly for 4 months
        'keep_yearly_backups_for_years' => 2,      // Then keep one yearly for 2 years
    ],
],
```

### Exclude Directories
Edit `config/backup.php` and add directories to the `exclude` list:

```php
'exclude' => [
    base_path('vendor'),
    base_path('node_modules'),
    storage_path('logs'),
    storage_path('framework/cache'),
    // Add more directories to exclude
],
```

### Enable Notifications
To send notifications on backup success/failure, enable in `.env`:

```bash
BACKUP_ENABLE_NOTIFICATIONS=true
BACKUP_NOTIFICATION_EMAIL=your-email@example.com
MAIL_MAILER=smtp
# Configure SMTP settings...
```

Then add notification classes to `config/backup.php` `'notifications'` array.

## Troubleshooting

### "mysqldump is not recognized"
- Ensure XAMPP's MySQL bin directory is in the PATH or configured.
- The project already configures this in `AppServiceProvider::boot()` to use `C:\xampp\mysql\bin`.

### Backup cleanup fails with notification errors
- Notifications are disabled by default. If you enable them, ensure your Mail configuration works.
- Check `MAIL_*` env vars in `.env` are correct.

### Large backups taking too long
- Consider excluding `vendor` and `node_modules` — these can be rebuilt from `composer.json` and `package.json`.
- Run `backup:run --only-db` for database-only backups if files are not critical.

### Backups not appearing
- Check `storage/app/laravel-backup/` directory exists and has write permissions.
- Run `php artisan storage:link` if needed.
- Check Laravel logs: `storage/logs/laravel.log`.

## Summary

✅ **Backups are set up and working.**
- Run `php artisan backup:run` to verify.
- Schedules are in place; enable via cron (Linux) or Task Scheduler (Windows).
- Manual restore is documented above.
- Retention policy cleans old backups automatically.

**Next Steps:**
- Enable the scheduler (cron/Task Scheduler) to run `php artisan schedule:run` every minute.
- Test a restore on a test environment to confirm backups are valid.
- Optionally: configure S3 or another remote disk for off-site storage, enable notifications, or add encryption.