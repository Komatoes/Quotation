# Backup GUI Quick Start Guide

## 🎯 Quick Access

**URL**: `http://localhost/admin/backup/`  
**Access**: Admin users only  
**Menu**: Sidebar → Administration → Backup & Restore

## 📊 Dashboard Overview

The backup management dashboard displays:

### 1. **3-2-1 Strategy Status Cards**
```
┌─────────────────────────────────────────────────────────────┐
│  🖥️ Local Storage (Server)    🔵 Google Drive    ☁️ S3      │
│         3 backups                  0 backups      0 backups │
│                                                              │
│  ✅ 3-2-1 Strategy Compliant                               │
│  You have adequate backups across multiple locations.      │
└─────────────────────────────────────────────────────────────┘
```

### 2. **Storage Usage Summary**
- Total size of all local backups
- Google Drive connection status

### 3. **Quick Actions**
- **Create Backup Now** - Triggers immediate backup with progress
- **Refresh** - Reload latest backup list

### 4. **Backup Files Table**
| Filename | Size | Created | Actions |
|----------|------|---------|---------|
| backup-2024-01-15.zip | 89.13 MB | 2024-01-15 15:45 | Download / Delete |

## 🚀 Common Tasks

### Create a Backup
```
1. Click "Create Backup Now" button
2. Progress modal appears
3. Wait for completion message
4. Dashboard auto-refreshes with new backup
```

### Download a Backup
```
1. Find backup in table
2. Click "Download" button
3. Browser downloads .zip file to your computer
4. Extract with your preferred archive tool (7-Zip, WinRAR, etc.)
```

### Delete a Backup
```
1. Find backup in table
2. Click "Delete" button
3. Confirm in dialog
4. Backup removed from server
```

### Monitor 3-2-1 Status
```
1. Check top status cards
2. Green ✅ = Strategy compliant (all 3 copies present)
3. Yellow ⚠️  = Need more backups or offsite copies
```

## 📋 Backup File Structure

Each backup `.zip` contains:
- **Database dump** (MySQL database as `.sql`)
- **Application files** (all files from project root)
- **Created**: Automatic filename includes date/time

Location on server: `storage/app/laravel-backup/`

## 🔒 Security Notes

- ✅ Only admin users can access this dashboard
- ✅ All operations require authentication
- ✅ File paths are sanitized (prevent directory traversal)
- ⚠️ Backups contain sensitive data (database, configuration)
- ⚠️ Store downloaded backups securely

## 🔧 Configuration

### Enable Google Drive Auto-Upload
1. Create Google OAuth credentials
2. Add to `.env`:
   ```
   GOOGLE_DRIVE_ENABLED=true
   GOOGLE_DRIVE_ACCESS_TOKEN=your_token
   GOOGLE_DRIVE_FOLDER_ID=your_folder_id
   ```
3. After next backup, files auto-upload to Google Drive

### Enable AWS S3 Backups
1. Add AWS credentials to `.env`
2. Update backup config for S3 destination
3. Backups will sync to S3 bucket

## 📞 Troubleshooting

### "403 Unauthorized" Error
- **Cause**: You're not logged in as an admin
- **Fix**: Log in with admin account
- **Check**: Your user should have `role = 'admin'` or `hasRole('admin')`

### Backup Creation Fails
- **Cause**: PHP artisan command error
- **Fix**: Check server logs: `storage/logs/laravel.log`
- **Try**: Test manually: `php artisan backup:run --disable-notifications`

### Can't Download Backup
- **Cause**: File was deleted or corrupted
- **Fix**: Create new backup and try again
- **Try**: Check file exists: `ls storage/app/laravel-backup/`

### 3-2-1 Status Shows "Not Compliant"
- **Cause**: Not enough backup copies across locations
- **Fix**: 
  - Create another local backup (click Create Backup Now)
  - OR setup Google Drive integration
  - OR setup S3 integration

## 📈 Backup Schedule

Automatic backups run daily:
- **Database Only**: 02:00 AM
- **Full Backup**: 02:00 AM (combined with above)
- **Cleanup**: 03:00 AM (removes backups older than 7 days)

Check `app/Console/Kernel.php` to modify schedule.

## 🆘 Need Help?

- Review: `docs/BACKUP-README.md` for overview
- Quick Steps: `docs/BACKUP-QUICKSTART.md`
- Technical Details: `docs/backup-restore.md`
- Reference: `docs/BACKUP-QUICK-REF.md`
- Checklist: `docs/BACKUP-CHECKLIST.md`

## ✅ Testing Checklist

When setting up, verify:
- [ ] Can access dashboard as admin
- [ ] Cannot access dashboard as non-admin
- [ ] Create Backup button works
- [ ] Backup file appears in table
- [ ] Download button downloads file
- [ ] Delete button removes backup
- [ ] 3-2-1 cards show correct counts
- [ ] Status badge shows correct compliance
- [ ] Mobile view is readable
- [ ] Google Drive integration (if enabled)

