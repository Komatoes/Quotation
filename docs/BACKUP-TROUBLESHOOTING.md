# 🆘 Backup GUI Troubleshooting Guide

## Issue: Backups Not Appearing in Dashboard

### ✅ FIXED: Wrong File Path (Nov 30, 2025)
**Status**: This issue has been resolved!

**What Was Happening**:
- Backup appeared successful in GUI
- But files weren't showing in dashboard
- Creating backups multiple times made "ghost" backups

**Root Cause**:
- Controller looked in: `storage/app/laravel-backup/`
- Spatie saved to: `storage/app/Laravel/`
- Mismatch = files couldn't be found

**Solution Applied**:
- Updated `BackupManagementController.php` (3 methods)
- Fixed paths in: `getBackupsList()`, `download()`, `delete()`
- Dashboard now shows all existing backups

**Verification**:
```bash
# Check backups exist
Get-ChildItem c:\xampp\htdocs\Quotation\storage\app\Laravel -Filter "*.zip"

# Expected: 5+ backup files visible
```

---

## Common Issues & Solutions

### Issue 1: "No backups found" After Creating Backup

**Problem**: GUI shows success but table stays empty

**Solution**:
1. Hard refresh dashboard: `Ctrl+Shift+R` (or `Cmd+Shift+R` on Mac)
2. Check backup directory exists:
   ```bash
   Test-Path c:\xampp\htdocs\Quotation\storage\app\Laravel
   ```
3. If directory missing, run:
   ```bash
   php artisan backup:run --disable-notifications
   ```

### Issue 2: "Backup created successfully!" But File Not Saved

**Problem**: Success message appears but no file created

**Possible Causes**:

| Cause | Check | Fix |
|-------|-------|-----|
| Storage directory permissions | `ls -la storage/app/` | Set write permissions |
| mysqldump missing | `which mysqldump` | Add to PATH |
| Disk space full | Check `df` output | Free up space |
| Spatie not installed | `composer show spatie/laravel-backup` | Run `composer install` |

**Debug Steps**:
```bash
# 1. Check logs
tail -f storage/logs/laravel.log

# 2. Test backup manually
php artisan backup:run --disable-notifications

# 3. Check file creation
Get-ChildItem storage/app/Laravel -Filter "*.zip" -Recurse
```

### Issue 3: Download Button Doesn't Work

**Problem**: Clicking download button doesn't download file

**Solution**:
1. Check file exists:
   ```bash
   Test-Path "c:\xampp\htdocs\Quotation\storage\app\Laravel\[filename].zip"
   ```
2. Check file is readable:
   - Right-click file → Properties → verify readable
3. Check browser allows downloads:
   - Allow downloads in browser settings
4. Check browser console for errors:
   - F12 → Console tab → Look for red errors

### Issue 4: Delete Button Shows "Failed to Delete"

**Problem**: Getting error message when trying to delete

**Solution**:
1. Check file permissions:
   ```bash
   icacls c:\xampp\htdocs\Quotation\storage\app\Laravel
   ```
2. Close any programs using the file:
   - File might be locked by antivirus or other process
3. Try manual delete:
   ```bash
   Remove-Item "c:\xampp\htdocs\Quotation\storage\app\Laravel\[filename].zip"
   ```

### Issue 5: 3-2-1 Status Shows 0 for All Values

**Problem**: Status cards show 0 local, 0 GDrive, 0 S3

**Solution**:
1. Verify backups exist:
   ```bash
   Get-ChildItem c:\xampp\htdocs\Quotation\storage\app\Laravel -Filter "*.zip"
   ```
2. Check `getBackupsList()` is finding files:
   - Add temp debug to controller
   - Or manually check directory
3. Clear browser cache:
   - Ctrl+Shift+Delete → Clear cached images/files

### Issue 6: "Unauthorized" Error on Dashboard

**Problem**: Seeing 403 Forbidden when accessing `/admin/backup/`

**Solution**:
1. Verify logged in:
   - Not logged in = redirected to login
2. Verify admin role:
   - Check user table: `role` or `role_name` field = 'admin'
   - Test with known admin account
3. Check role detection in controller:
   - Try different user account
   - Check `isAdmin()` method logic

### Issue 7: Browser Shows "Page Not Found" for `/admin/backup/`

**Problem**: 404 error when accessing dashboard

**Solution**:
1. Verify routes are loaded:
   ```bash
   php artisan route:list | grep backup
   ```
2. Expected output:
   ```
   admin/backup ....................GET|HEAD  admin.backup.index
   admin/backup/create .............POST      admin.backup.create
   admin/backup/download/{filename}.GET|HEAD  admin.backup.download
   admin/backup/delete .............POST      admin.backup.delete
   ```
3. If routes missing:
   - Check `routes/web.php` has backup routes
   - Run: `php artisan cache:clear`
   - Run: `php artisan route:cache`

### Issue 8: Progress Modal Hangs Forever

**Problem**: "Creating Backup..." modal never completes

**Solution**:
1. Check browser console:
   - F12 → Console → Look for JavaScript errors
2. Check Laravel logs:
   - `tail storage/logs/laravel.log`
3. Check backup process:
   - Open separate terminal: `php artisan backup:run`
   - See if it completes
4. Increase timeout if large backup:
   - Edit modal timeout in `backup-management.blade.php`

---

## Diagnostic Commands

### Check All Backup Files
```bash
Get-ChildItem -Path "c:\xampp\htdocs\Quotation\storage\app\Laravel" -Recurse -Filter "*.zip" | Select-Object FullName, Length, LastWriteTime
```

### Count Backup Files
```bash
(Get-ChildItem -Path "c:\xampp\htdocs\Quotation\storage\app\Laravel" -Filter "*.zip" | Measure-Object).Count
```

### Check Total Backup Size
```bash
Get-ChildItem -Path "c:\xampp\htdocs\Quotation\storage\app\Laravel" -Filter "*.zip" | Measure-Object -Sum -Property Length
```

### List Recent Backups
```bash
Get-ChildItem -Path "c:\xampp\htdocs\Quotation\storage\app\Laravel" -Filter "*.zip" | Sort-Object LastWriteTime -Descending | Select-Object -First 5
```

### Test Backup Command
```bash
cd c:\xampp\htdocs\Quotation
php artisan backup:run --disable-notifications
```

### Check Laravel Logs
```bash
# Last 20 lines
Get-Content -Path "c:\xampp\htdocs\Quotation\storage\logs\laravel.log" -Tail 20

# Search for errors
Select-String -Path "c:\xampp\htdocs\Quotation\storage\logs\laravel.log" -Pattern "error|Error|ERROR|exception|Exception" | Select-Object -Last 10
```

### Verify Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo()
# Should return PDO object, not error
```

---

## Performance Issues

### Issue: Dashboard Takes Long Time to Load

**Problem**: Lots of backup files (100+) makes dashboard slow

**Solution**:
1. Delete old backups to reduce file count
2. Use pagination (future feature)
3. Implement lazy loading (future feature)

### Issue: Create Backup Takes Too Long

**Problem**: Backup process takes 10+ minutes

**Solution**:
1. Check database size:
   ```bash
   # In Laravel Tinker
   DB::select("SELECT SUM(data_length + index_length) / 1024 / 1024 as MB FROM information_schema.tables WHERE table_schema = 'quotation'")
   ```
2. Exclude large files from backup:
   - Edit `config/backup.php` exclusions
3. Use scheduled backup instead (runs at off-peak time)

---

## Advanced Debugging

### Enable Spatie Debug Mode
```php
// In BackupManagementController.php
Artisan::call('backup:run', [
    '--disable-notifications' => true,
    '--only-db' => false,  // Backup everything
    '--timeout' => 600,     // 10 minute timeout
]);

// Then check output
$output = Artisan::output();
Log::info('Backup output: ' . $output);
```

### Check Spatie Configuration
```bash
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider" --force
```

### Validate Backup File
```bash
# Verify backup file is valid ZIP
jar -tf "storage/app/Laravel/backup-2025-11-30-00-56-44.zip" | head -20

# Or in PowerShell
[System.IO.Compression.ZipFile]::OpenRead("c:\xampp\htdocs\Quotation\storage\app\Laravel\backup-2025-11-30-00-56-44.zip").Entries | Select-Object -First 20
```

---

## Performance Monitoring

### Monitor Backup Process in Real-Time
```bash
# Terminal 1: Start backup
php artisan backup:run

# Terminal 2: Watch file growth
while($true) { 
    Get-ChildItem c:\xampp\htdocs\Quotation\storage\app\Laravel -Filter "*.zip" | Select-Object Name, @{N='Size (MB)';E={[math]::Round($_.Length/1MB, 2)}}
    Start-Sleep -Seconds 5
}
```

### Check Memory Usage During Backup
```bash
# Windows Resource Monitor
Get-Process php | Select-Object ProcessName, @{N='Memory (MB)';E={[math]::Round($_.WorkingSet/1MB, 2)}}
```

---

## Support Resources

| Resource | Location |
|----------|----------|
| Backup Path Fix | `docs/BACKUP-PATH-FIX.md` |
| Quick Start Guide | `docs/BACKUP-GUI-QUICKSTART.md` |
| Technical Details | `docs/BACKUP-GUI-IMPLEMENTATION.md` |
| Architecture Diagrams | `docs/BACKUP-GUI-ARCHITECTURE.md` |
| Complete Checklist | `docs/BACKUP-GUI-CHECKLIST.md` |
| Spatie Docs | https://spatie.be/docs/laravel-backup |
| Laravel Docs | https://laravel.com |

---

## Getting Help

1. **Check logs**: `storage/logs/laravel.log`
2. **Review docs**: `docs/` folder
3. **Test manually**: `php artisan backup:run`
4. **Check browser**: F12 Console tab
5. **Verify setup**: Routes, permissions, database connection

---

**Last Updated**: November 30, 2025  
**Version**: 1.0  
**Status**: Ready for production

