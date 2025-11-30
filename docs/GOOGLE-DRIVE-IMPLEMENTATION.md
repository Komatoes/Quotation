# 🔧 Google Drive Code Implementation

**Note**: This is manual implementation. The actual code needs to be added to your controller.

**Status**: Ready to implement  
**Complexity**: Intermediate  
**Files to Modify**: 1

---

## 📋 Overview

After you complete the Google Drive setup (Phase 1 & 2), you need to add code to:
1. Upload backups to Google Drive automatically
2. Download backups from Google Drive
3. Show status on dashboard

---

## 📁 Files to Modify

```
app/Http/Controllers/BackupManagementController.php
  ├─ Add: uploadBackupToGoogleDrive() method
  ├─ Add: getGoogleDriveBackups() method
  ├─ Add: getGoogleDriveCredentials() method
  ├─ Modify: create() method (add auto-upload)
  └─ Modify: getGoogleDriveStatus() method
```

---

## 🔌 Install Package First

```bash
cd C:\xampp\htdocs\Quotation
composer require google/apiclient:"^2.12"
```

---

## 📝 Code to Add

### Step 1: Update `create()` Method

Find this method in `BackupManagementController.php`:

**FIND:**
```php
public function create(Request $request)
{
    if (!$this->isAdmin()) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    try {
        // Run backup
        Artisan::call('backup:run', ['--disable-notifications' => true]);
        
        $output = Artisan::output();
        
        // Upload to Google Drive if connected
        if (config('services.google.drive.enabled')) {
            $this->uploadLatestBackupToGoogleDrive();
        }

        return response()->json([
            'success' => true,
            'message' => 'Backup created successfully!',
            'output' => $output,
            'backups' => $this->getBackupsList(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Backup failed: ' . $e->getMessage(),
        ], 500);
    }
}
```

**REPLACE WITH:**
```php
public function create(Request $request)
{
    if (!$this->isAdmin()) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    try {
        // Run backup
        Artisan::call('backup:run', ['--disable-notifications' => true]);
        
        $output = Artisan::output();
        
        // Upload to Google Drive if enabled
        if (env('GOOGLE_DRIVE_ENABLED', false)) {
            $backupName = 'quotation-backup-' . date('Y-m-d-H-i-s') . '.zip';
            $latestBackup = $this->getBackupsList()[0] ?? null;
            
            if ($latestBackup) {
                $backupPath = storage_path('app/Laravel/' . $latestBackup['name']);
                $uploadResult = $this->uploadBackupToGoogleDrive($backupPath, $backupName);
                
                if ($uploadResult) {
                    Log::info('Backup auto-uploaded to Google Drive: ' . $backupName);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Backup created successfully!',
            'output' => $output,
            'backups' => $this->getBackupsList(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Backup failed: ' . $e->getMessage(),
        ], 500);
    }
}
```

---

### Step 2: Add New Methods

Add these new methods at the end of the class (before the closing `}`):

```php
    /**
     * Upload backup to Google Drive
     */
    private function uploadBackupToGoogleDrive($backupPath, $backupName)
    {
        if (!env('GOOGLE_DRIVE_ENABLED', false)) {
            Log::info('Google Drive upload skipped (not enabled)');
            return false;
        }

        try {
            $credentialsPath = storage_path(env('GOOGLE_DRIVE_CREDENTIALS_PATH', 'app/google-credentials.json'));
            
            if (!file_exists($credentialsPath)) {
                throw new \Exception('Google Drive credentials file not found: ' . $credentialsPath);
            }

            $client = new \Google_Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope(\Google_Service_Drive::DRIVE);
            
            $driveService = new \Google_Service_Drive($client);
            
            $file = new \Google_Service_Drive_DriveFile();
            $file->setName($backupName);
            $file->setParents([env('GOOGLE_DRIVE_FOLDER_ID')]);
            
            $result = $driveService->files->create($file, [
                'data' => file_get_contents($backupPath),
                'mimeType' => 'application/zip',
                'uploadType' => 'media',
            ]);
            
            Log::info("Backup uploaded to Google Drive: " . $result->getId());
            return true;
        } catch (\Exception $e) {
            Log::error("Google Drive upload failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Google Drive backups list
     */
    private function getGoogleDriveBackups()
    {
        if (!env('GOOGLE_DRIVE_ENABLED', false)) {
            return [];
        }

        try {
            $credentialsPath = storage_path(env('GOOGLE_DRIVE_CREDENTIALS_PATH', 'app/google-credentials.json'));
            
            if (!file_exists($credentialsPath)) {
                return [];
            }

            $client = new \Google_Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope(\Google_Service_Drive::DRIVE);
            
            $driveService = new \Google_Service_Drive($client);
            
            $query = "'" . env('GOOGLE_DRIVE_FOLDER_ID') . "' in parents and mimeType = 'application/zip'";
            $results = $driveService->files->listFiles([
                'q' => $query,
                'pageSize' => 10,
                'fields' => 'files(id, name, size, createdTime, webViewLink)',
                'orderBy' => 'createdTime desc',
            ]);
            
            return $results->getFiles() ?? [];
        } catch (\Exception $e) {
            Log::error("Failed to fetch Google Drive backups: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Google Drive status
     */
    private function getGoogleDriveStatus()
    {
        if (!env('GOOGLE_DRIVE_ENABLED', false)) {
            return [
                'connected' => false,
                'folder_id' => null,
                'backup_count' => 0,
            ];
        }

        try {
            $gdBackups = $this->getGoogleDriveBackups();
            return [
                'connected' => true,
                'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
                'backup_count' => count($gdBackups),
                'backups' => $gdBackups,
            ];
        } catch (\Exception $e) {
            Log::error('Google Drive status check failed: ' . $e->getMessage());
            return [
                'connected' => false,
                'folder_id' => null,
                'backup_count' => 0,
            ];
        }
    }
```

---

### Step 3: Add to .env File

Open: `C:\xampp\htdocs\Quotation\.env`

Add at the bottom:

```env
# Google Drive Backup Configuration
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_FOLDER_ID=YOUR_FOLDER_ID_HERE
GOOGLE_DRIVE_CREDENTIALS_PATH=app/google-credentials.json
```

Replace `YOUR_FOLDER_ID_HERE` with your actual Google Drive folder ID.

---

### Step 4: Add Credentials File

1. Download JSON from Google Cloud Console
2. Save to: `C:\xampp\htdocs\Quotation\storage\app\google-credentials.json`

---

## ✅ Verify Installation

### Test the code:

```bash
cd C:\xampp\htdocs\Quotation

# Check for syntax errors
php -l app/Http/Controllers/BackupManagementController.php

# Should output: "No syntax errors detected"
```

### Test backup with upload:

```bash
php artisan backup:run

# Check logs
tail storage/logs/laravel.log | grep "Google Drive"

# Should show: "Backup uploaded to Google Drive"
```

### Verify in Google Drive:

1. Go to: https://drive.google.com/
2. Open: "Quotation Backups" folder
3. Should see new ZIP file

---

## 🔍 What Each Method Does

### `uploadBackupToGoogleDrive()`
- Takes backup file path
- Connects to Google Drive using service account credentials
- Creates file in shared folder
- Returns true/false for success

### `getGoogleDriveBackups()`
- Connects to Google Drive
- Lists all ZIP files in shared folder
- Returns array of files with metadata
- Used by dashboard to show GDrive backups

### `getGoogleDriveStatus()`
- Checks if Google Drive is connected
- Gets count of backups in GDrive
- Returns connection status
- Used by dashboard cards

---

## 🐛 Troubleshooting

### "Class not found: Google_Client"
**Solution**: Run `composer require google/apiclient:"^2.12"`

### "Credentials file not found"
**Solution**: Verify path:
```bash
ls -la storage/app/google-credentials.json
# Should show file exists
```

### "Invalid folder ID"
**Solution**: Get from URL:
```
https://drive.google.com/drive/folders/1ABC2DEF3GHI4JKL5
                                     ↑ This is folder ID
```

### "Permission denied"
**Solution**: Check service account is shared:
1. Go to Google Drive folder
2. Right-click → Share
3. Should see service account as Editor

### No backups appear in GDrive
**Solution**: Check logs:
```bash
tail storage/logs/laravel.log | grep -i "google drive"
```

---

## 📊 After Implementation

When complete, your system will:

```
Daily Backup (02:00 AM)
    ↓
✅ Local backup created: storage/app/Laravel/
✅ Auto-uploaded to GDrive
✅ Both shown in dashboard
✅ Restore available from either
```

---

## 🚀 Next Steps

1. **Test locally** - Create backup, verify GDrive upload
2. **Deploy to Hostinger** - Push code, copy credentials
3. **Test on Hostinger** - Create backup via SSH, verify upload
4. **Monitor** - Check daily backups appear in Google Drive

---

## 📖 Full Guides

- **Quick setup**: `docs/GOOGLE-DRIVE-QUICKSTART.md`
- **Full details**: `docs/GOOGLE-DRIVE-SETUP.md`
- **Checklist**: `docs/GOOGLE-DRIVE-CHECKLIST.md`

