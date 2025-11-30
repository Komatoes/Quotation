# 🔗 Google Drive Integration Guide for Hostinger Deployment

**Status**: Ready to Implement  
**Complexity**: Intermediate  
**Time to Setup**: 30-45 minutes  
**Hostinger Ready**: ✅ Yes

---

## 📋 Overview

This guide explains how to integrate Google Drive with your backup system once deployed to Hostinger. Your backups will auto-upload to Google Drive after each backup runs (02:00 AM daily).

### What You'll Get
- ✅ Automatic backup uploads to Google Drive
- ✅ Restore from Google Drive backups
- ✅ Backup redundancy (local + cloud)
- ✅ 3-2-1 strategy compliance
- ✅ Easy file recovery from web interface

---

## 🎯 Setup Phases

### Phase 1: Google Cloud Project Setup (15 minutes) ← START HERE
- Create Google Cloud project
- Enable Google Drive API
- Create OAuth 2.0 credentials
- Get credentials JSON file

### Phase 2: Laravel Configuration (10 minutes)
- Add credentials to `.env`
- Create config file for Google Drive
- Install Laravel Google Drive package

### Phase 3: Implement Upload Logic (15 minutes)
- Add upload method to controller
- Schedule auto-upload after backup
- Add to restore UI

### Phase 4: Test & Deploy (10 minutes)
- Test locally
- Deploy to Hostinger
- Verify auto-upload works

---

## 📖 Phase 1: Google Cloud Project Setup

### Step 1: Create Google Cloud Project

1. Go to: **https://console.cloud.google.com/**
2. Click: **"Select a Project"** (top-left dropdown)
3. Click: **"NEW PROJECT"**
4. **Project name**: `Quotation App Backups`
5. Click: **"CREATE"**
6. Wait: 1-2 minutes for project creation

### Step 2: Enable Google Drive API

1. In Google Cloud Console, click: **"APIs & Services"**
2. Click: **"Enable APIs and Services"** button
3. Search for: **"Google Drive API"**
4. Click on result: **"Google Drive API"**
5. Click: **"ENABLE"**
6. Wait: API enables (takes 30 seconds)

### Step 3: Create OAuth 2.0 Credentials

1. Click: **"Credentials"** (left sidebar)
2. Click: **"+ CREATE CREDENTIALS"** button
3. Select: **"OAuth 2.0 Client ID"**
4. Choose: **"Web application"**
5. **Name**: `Quotation App Backups`
6. **Authorized JavaScript origins**: 
   - Local: `http://localhost`
   - Hostinger: `https://yourdomainname.com`
7. **Authorized redirect URIs**:
   - Local: `http://localhost/admin/backup/callback`
   - Hostinger: `https://yourdomainname.com/admin/backup/callback`
8. Click: **"CREATE"**
9. **IMPORTANT**: Copy **Client ID** and **Client Secret** (save these!)

### Step 4: Create a Service Account (For Server-Side Upload)

This is better than OAuth for automatic uploads on a server.

1. Go to: **Google Cloud Console → Credentials**
2. Click: **"+ CREATE CREDENTIALS"**
3. Select: **"Service Account"**
4. **Service account name**: `quotation-backups`
5. Click: **"CREATE AND CONTINUE"**
6. **Role**: `Editor` (or custom: `roles/drive.admin`)
7. Click: **"CONTINUE"** → **"DONE"**
8. Find the service account in the list
9. Click on: **"quotation-backups"**
10. Go to: **"KEYS"** tab
11. Click: **"ADD KEY"** → **"Create new key"**
12. Select: **"JSON"**
13. Click: **"CREATE"**
14. **SAVE THIS FILE** - it auto-downloads

### Step 5: Share Google Drive Folder with Service Account

1. Create folder in your Google Drive: **"Quotation Backups"**
2. Open the service account JSON file you downloaded
3. Find and copy: **`client_email`** (looks like: `xxx@xxx.iam.gserviceaccount.com`)
4. Right-click the **"Quotation Backups"** folder
5. Click: **"Share"**
6. Paste: The service account email
7. Give: **"Editor"** permission
8. Click: **"Share"**

### ✅ Phase 1 Complete

You now have:
- ✅ Google Cloud project created
- ✅ Google Drive API enabled
- ✅ OAuth 2.0 credentials (Client ID + Secret)
- ✅ Service account created
- ✅ JSON credentials file
- ✅ Shared Google Drive folder

---

## 🛠️ Phase 2: Laravel Configuration

### Step 1: Install Google Drive Package

```bash
cd C:\xampp\htdocs\Quotation
composer require nao-pon/google-drive-laravel
```

### Step 2: Add to `.env` File

Open: `C:\xampp\htdocs\Quotation\.env`

Add these lines at the bottom:

```env
# Google Drive Configuration
GOOGLE_DRIVE_CLIENT_ID=YOUR_CLIENT_ID_HERE
GOOGLE_DRIVE_CLIENT_SECRET=YOUR_CLIENT_SECRET_HERE
GOOGLE_DRIVE_FOLDER_ID=YOUR_FOLDER_ID_HERE
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_CREDENTIALS_PATH=storage/app/google-credentials.json
```

**Where to get these values:**
- `GOOGLE_DRIVE_CLIENT_ID` - From Google Cloud Console (OAuth credentials)
- `GOOGLE_DRIVE_CLIENT_SECRET` - From Google Cloud Console (OAuth credentials)
- `GOOGLE_DRIVE_FOLDER_ID` - From Google Drive folder URL: `https://drive.google.com/drive/folders/FOLDER_ID_HERE`

### Step 3: Add Service Account JSON

1. Download the JSON file you created in Phase 1
2. Copy it to: `C:\xampp\htdocs\Quotation\storage\app\google-credentials.json`
3. This file should NOT be committed to Git (add to `.gitignore`)

### Step 4: Create Config File

Create: `C:\xampp\htdocs\Quotation\config\google-drive.php`

```php
<?php

return [
    'enabled' => env('GOOGLE_DRIVE_ENABLED', false),
    
    'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
    'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
    
    'credentials_path' => env('GOOGLE_DRIVE_CREDENTIALS_PATH', 'storage/app/google-credentials.json'),
    
    'upload_on_backup' => env('GOOGLE_DRIVE_UPLOAD_ON_BACKUP', true),
];
```

### ✅ Phase 2 Complete

You now have:
- ✅ Google Drive package installed
- ✅ Environment variables configured
- ✅ Credentials stored securely
- ✅ Config file created

---

## 💻 Phase 3: Implementation Code

### Step 1: Add Upload Method to Controller

Open: `C:\xampp\htdocs\Quotation\app\Http\Controllers\BackupManagementController.php`

Add this new method after the `restoreFromSafetyBackup()` method:

```php
    /**
     * Upload backup to Google Drive
     */
    private function uploadBackupToGoogleDrive($backupPath, $backupName)
    {
        if (!config('google-drive.enabled')) {
            Log::info('Google Drive upload skipped (not enabled)');
            return false;
        }

        try {
            $credentials = $this->getGoogleDriveCredentials();
            $client = new \Google_Client();
            $client->setAuthConfig($credentials);
            $client->addScope(\Google_Service_Drive::DRIVE);
            
            $driveService = new \Google_Service_Drive($client);
            
            $file = new \Google_Service_Drive_DriveFile();
            $file->setName($backupName);
            $file->setParents([config('google-drive.folder_id')]);
            
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
     * Get Google Drive credentials
     */
    private function getGoogleDriveCredentials()
    {
        $credentialsPath = storage_path(config('google-drive.credentials_path'));
        
        if (!file_exists($credentialsPath)) {
            throw new \Exception('Google Drive credentials file not found: ' . $credentialsPath);
        }
        
        return json_decode(file_get_contents($credentialsPath), true);
    }

    /**
     * Get Google Drive backups list
     */
    private function getGoogleDriveBackups()
    {
        if (!config('google-drive.enabled')) {
            return [];
        }

        try {
            $credentials = $this->getGoogleDriveCredentials();
            $client = new \Google_Client();
            $client->setAuthConfig($credentials);
            $client->addScope(\Google_Service_Drive::DRIVE);
            
            $driveService = new \Google_Service_Drive($client);
            
            $query = "'" . config('google-drive.folder_id') . "' in parents and mimeType = 'application/zip'";
            $results = $driveService->files->listFiles([
                'q' => $query,
                'pageSize' => 10,
                'fields' => 'files(id, name, size, createdTime, webViewLink)',
                'orderBy' => 'createdTime desc',
            ]);
            
            return $results->getFiles();
        } catch (\Exception $e) {
            Log::error("Failed to fetch Google Drive backups: " . $e->getMessage());
            return [];
        }
    }
```

### Step 2: Auto-Upload After Backup

In the `create()` method, update the upload code:

Find this:
```php
// Upload to Google Drive if connected
if (config('services.google.drive.enabled')) {
    $this->uploadLatestBackupToGoogleDrive();
}
```

Replace with:
```php
// Upload to Google Drive if enabled
if (config('google-drive.upload_on_backup')) {
    $backupName = 'quotation-backup-' . date('Y-m-d-H-i-s') . '.zip';
    $latestBackup = $this->getBackupsList()[0] ?? null;
    
    if ($latestBackup) {
        $backupPath = storage_path('app/Laravel/' . $latestBackup['name']);
        $this->uploadBackupToGoogleDrive($backupPath, $backupName);
    }
}
```

### Step 3: Update Dashboard to Show Google Drive Backups

Update the `getGoogleDriveStatus()` method:

```php
    private function getGoogleDriveStatus()
    {
        if (!config('google-drive.enabled')) {
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
                'folder_id' => config('google-drive.folder_id'),
                'backup_count' => count($gdBackups),
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

### ✅ Phase 3 Complete

You now have:
- ✅ Upload method implemented
- ✅ Auto-upload after backup
- ✅ Google Drive status checking
- ✅ Error handling and logging

---

## 🧪 Phase 4: Test & Deploy

### Test Locally

```bash
cd C:\xampp\htdocs\Quotation

# Create a backup
php artisan backup:run

# Check logs for upload status
tail -f storage/logs/laravel.log
```

You should see:
```
[2025-11-30] ... INFO: Backup uploaded to Google Drive: file-id-xxxxx
```

### Deploy to Hostinger

1. **Upload your files** to Hostinger (use Git or FTP)
2. **Copy `.env` file** with Google Drive credentials
3. **Copy credentials JSON** to `storage/app/google-credentials.json`
4. **Run Composer**: 
   ```bash
   composer install
   ```
5. **Verify permissions**:
   ```bash
   chmod -R 755 storage/
   chmod -R 755 bootstrap/cache/
   ```

### Verify on Hostinger

1. SSH into Hostinger:
   ```bash
   ssh user@hostinger-server
   cd public_html/quotation
   ```

2. Test backup:
   ```bash
   php artisan backup:run
   ```

3. Check logs:
   ```bash
   tail -f storage/logs/laravel.log | grep "Google Drive"
   ```

4. Verify in Google Drive - you should see new backup file!

---

## 🔄 Restore from Google Drive

To restore from a Google Drive backup, users can:

1. **Download from Google Drive**
2. **Upload to local backups folder**
3. **Restore via GUI**

Or, you can add a restore option in the GUI:

Update: `resources/views/admin/backup-management.blade.php`

Add this to the dashboard:

```blade
<div class="tab-pane fade" id="gdrive" role="tabpanel">
    <h5>Google Drive Backups</h5>
    @if($googleDriveConnected)
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="gdrive-backups">
                <!-- Populated via AJAX -->
            </tbody>
        </table>
    @else
        <div class="alert alert-info">
            Google Drive not connected. Configure in .env to enable.
        </div>
    @endif
</div>
```

---

## 📊 3-2-1 Strategy Status (After Setup)

```
┌─────────────────────┐
│ 3-2-1 STRATEGY      │
├─────────────────────┤
│                     │
│ 3 Copies:           │
│  ✅ Local (Daily)   │
│  ✅ GDrive (Daily)  │
│  ⏳ S3 (Optional)   │
│                     │
│ 2 Media:            │
│  ✅ Disk (Local)    │
│  ✅ Cloud (GDrive)  │
│                     │
│ 1 Offsite:          │
│  ✅ Google Drive    │
│     (Off-site)      │
│                     │
│ STATUS: ✅ COMPLIANT│
│                     │
└─────────────────────┘
```

---

## 🔐 Security Considerations

### Protect Your Credentials

1. **Never commit `.env`** to Git
2. **Never commit JSON** credentials file to Git
3. **Use `.gitignore`**:
   ```
   .env
   .env.local
   storage/app/google-credentials.json
   ```

4. **Set file permissions** on Hostinger:
   ```bash
   chmod 600 .env
   chmod 600 storage/app/google-credentials.json
   ```

### Recommended Setup

```
Local Development
├─ .env (with test credentials)
├─ storage/app/google-credentials.json (test account)
└─ Test Google Drive folder

Hostinger Production
├─ .env (with production credentials)
├─ storage/app/google-credentials.json (production account)
└─ Production Google Drive folder
```

---

## 🚀 Quick Reference: Steps to Connect Google Drive

### Before Hosting
1. ✅ Create Google Cloud project
2. ✅ Enable Google Drive API
3. ✅ Create service account
4. ✅ Download JSON credentials
5. ✅ Create shared Google Drive folder

### On Your Local Machine
1. ✅ Install package: `composer require nao-pon/google-drive-laravel`
2. ✅ Add `.env` variables
3. ✅ Create `config/google-drive.php`
4. ✅ Copy JSON to `storage/app/google-credentials.json`
5. ✅ Test backup and upload locally

### On Hostinger
1. ✅ Deploy code with Google Drive package
2. ✅ Copy `.env` with credentials
3. ✅ Copy JSON credentials file
4. ✅ Run `composer install`
5. ✅ Test backup via SSH
6. ✅ Verify upload in Google Drive

---

## 📋 Troubleshooting

### Q: "Credentials file not found"
**A**: Check path is correct:
```bash
ls -la storage/app/google-credentials.json
```

### Q: "Invalid folder ID"
**A**: Get correct folder ID from URL:
```
https://drive.google.com/drive/folders/1ABC2DEF3GHI4JKL5
                                     ↑ This is folder ID
```

### Q: Upload fails silently
**A**: Check logs:
```bash
tail storage/logs/laravel.log | grep "Google Drive"
```

### Q: Permission denied on Hostinger
**A**: Fix permissions:
```bash
chmod -R 755 storage/app/
chmod 600 storage/app/google-credentials.json
```

---

## ✅ Success Checklist

- [ ] Google Cloud project created
- [ ] Google Drive API enabled
- [ ] Service account created and JSON downloaded
- [ ] Google Drive folder created and shared
- [ ] Composer package installed locally
- [ ] `.env` variables added
- [ ] `config/google-drive.php` created
- [ ] JSON credentials copied to `storage/app/`
- [ ] Code methods added to controller
- [ ] Test backup locally and verify upload
- [ ] Files deployed to Hostinger
- [ ] `.env` and credentials copied to Hostinger
- [ ] Test backup on Hostinger
- [ ] Verify file appears in Google Drive
- [ ] Dashboard shows Google Drive backups

---

## 🎉 You're Ready!

Once setup complete, your backup system will:
- ✅ Create local backups daily (02:00 AM)
- ✅ Auto-upload to Google Drive
- ✅ Restore from either location
- ✅ Show 3-2-1 compliance status
- ✅ Provide redundancy and disaster recovery

**Next Step**: Start with Phase 1 (Google Cloud setup)!

---

**Questions?** Check the troubleshooting section above or create a new issue.

