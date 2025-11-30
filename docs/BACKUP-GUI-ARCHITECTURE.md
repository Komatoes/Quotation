# Backup GUI Architecture & Flow Diagrams

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                          WEB BROWSER                                 │
│  [Admin User] → /admin/backup/ → [Backup Management Dashboard]     │
└─────────────────────────────────────────────────────────────────────┘
                                 ↓
┌─────────────────────────────────────────────────────────────────────┐
│                      LARAVEL APPLICATION                             │
│                                                                       │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ Router: routes/web.php                                      │   │
│  │  ├─ GET  /admin/backup/          → index                   │   │
│  │  ├─ POST /admin/backup/create     → create                 │   │
│  │  ├─ GET  /admin/backup/download/{filename} → download      │   │
│  │  └─ POST /admin/backup/delete     → delete                 │   │
│  │  All routes protected with: auth middleware                │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                                 ↓                                    │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ Controller: BackupManagementController                      │   │
│  │                                                              │   │
│  │  • isAdmin()                     [Auth Check]              │   │
│  │  • index()                       [Show Dashboard]           │   │
│  │  • create()                      [Trigger Backup]          │   │
│  │  • download($filename)           [Get File]               │   │
│  │  • delete()                      [Remove Backup]          │   │
│  │  • getBackupsList()              [Scan Storage]           │   │
│  │  • calculateBackupStats()        [3-2-1 Stats]            │   │
│  │  • getGoogleDriveStatus()        [Check Connection]       │   │
│  │                                                              │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                                 ↓                                    │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ Spatie Backup: php artisan backup:run                       │   │
│  │                                                              │   │
│  │  ├─ Database Export (mysqldump)                            │   │
│  │  │   └─ MySQL → backup.sql                                │   │
│  │  │                                                          │   │
│  │  ├─ File Collection (entire project)                       │   │
│  │  │   └─ app/ config/ routes/ ... → files                 │   │
│  │  │                                                          │   │
│  │  └─ Compression (zip)                                      │   │
│  │      └─ backup-YYYY-MM-DD-HHMMSS.zip                     │   │
│  │                                                              │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                                 ↓                                    │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ Storage Destinations                                        │   │
│  │                                                              │   │
│  │  ✅ Local Disk                                              │   │
│  │     storage/app/laravel-backup/backup-*.zip               │   │
│  │                                                              │   │
│  │  🔄 Google Drive (Integration Ready)                        │   │
│  │     [stub] uploadLatestBackupToGoogleDrive()               │   │
│  │                                                              │   │
│  │  ☁️  AWS S3 (Integration Ready)                             │   │
│  │     [stub] countS3Backups()                                │   │
│  │                                                              │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

## Dashboard Data Flow

```
┌─────────────────────────────────────────────────────────────────┐
│ User Visits: /admin/backup/                                     │
└─────────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────────┐
│ BackupManagementController::index()                             │
│  1. Check: isAdmin() → true?                                    │
│  2. Get: getBackupsList()                                       │
│  3. Calculate: calculateBackupStats()                           │
│  4. Check: getGoogleDriveStatus()                               │
│  5. Pass data to view                                           │
└─────────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────────┐
│ View: resources/views/admin/backup-management.blade.php         │
│                                                                   │
│ Displays:                                                        │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 3-2-1 Status Cards                                       │  │
│  │  • Local: {{ $backupStats['local_count'] }}             │  │
│  │  • GDrive: {{ $backupStats['google_drive_count'] }}     │  │
│  │  • S3: {{ $backupStats['s3_count'] }}                   │  │
│  │  • Compliance: {{ $backupStats['strategy_compliant'] }} │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Backup Files Table                                       │  │
│  │  @foreach($backups as $backup)                           │  │
│  │    • {{ $backup['name'] }} | {{ $backup['size'] }}       │  │
│  │      [Download] [Delete]                                 │  │
│  │  @endforeach                                              │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ AJAX Event Listeners                                     │  │
│  │  • #backupBtn.onclick → createBackup()                   │  │
│  │  • Download links → Direct download                      │  │
│  │  • Delete buttons → deleteBackup(filename)               │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

## Create Backup Operation

```
User clicks "Create Backup Now"
           ↓
┌──────────────────────────────┐
│ Modal: "Creating Backup..."  │
│ Status: "Preparing backup..." │
│ Progress spinner (animated)   │
└──────────────────────────────┘
           ↓ AJAX POST
┌──────────────────────────────────────────┐
│ BackupManagementController::create()     │
│                                          │
│ 1. Check: isAdmin()                      │
│ 2. Run: Artisan::call('backup:run',      │
│    ['--disable-notifications' => true])  │
│ 3. If Google Drive enabled:              │
│    uploadLatestBackupToGoogleDrive()     │
│ 4. Return JSON response                  │
└──────────────────────────────────────────┘
           ↓
Spatie Backup Process:
  1. Create temporary directory
  2. Export MySQL database → dump.sql
  3. Collect all project files
  4. Create zip: backup-2024-01-15-153045.zip
  5. Save to: storage/app/laravel-backup/
  6. Return success
           ↓
┌──────────────────────────────────────────┐
│ Response JSON:                           │
│ {                                        │
│   "success": true,                       │
│   "message": "Backup created!",          │
│   "backups": [...]                       │
│ }                                        │
└──────────────────────────────────────────┘
           ↓ JavaScript handler
┌──────────────────────────────────────────┐
│ Modal: "✅ Backup completed!"            │
│ Auto-refresh after 2 seconds             │
│ → Page reloads                           │
│ → New backup visible in table            │
└──────────────────────────────────────────┘
```

## Delete Backup Operation

```
User clicks "Delete" for backup-2024-01-15.zip
           ↓
┌────────────────────────────────────────────┐
│ SweetAlert2 Confirmation Dialog:           │
│ "Are you sure? backup-2024-01-15.zip"      │
│ [Cancel] [OK]                              │
└────────────────────────────────────────────┘
           ↓ User confirms
           ↓ AJAX POST
┌────────────────────────────────────────────┐
│ BackupManagementController::delete()       │
│                                            │
│ 1. Check: isAdmin()                        │
│ 2. Validate: file exists?                  │
│ 3. Execute: unlink(backupPath)             │
│ 4. Return JSON: success                    │
└────────────────────────────────────────────┘
           ↓
┌────────────────────────────────────────────┐
│ File removed from:                         │
│ storage/app/laravel-backup/               │
└────────────────────────────────────────────┘
           ↓ JavaScript handler
┌────────────────────────────────────────────┐
│ SweetAlert2: "Deleted successfully!"       │
│ → Page reloads                             │
│ → Backup removed from table                │
└────────────────────────────────────────────┘
```

## 3-2-1 Strategy Compliance Logic

```
┌─────────────────────────────────────────────────────────────┐
│ calculateBackupStats($backups)                              │
│                                                              │
│ Input: Array of local backup files                          │
│                                                              │
│ Process:                                                    │
│ ┌──────────────────────────────────────────────────────┐   │
│ │ 1. Count LOCAL backups                              │   │
│ │    local_count = count($backups) ✓                  │   │
│ │                                                      │   │
│ │ 2. Count GOOGLE DRIVE backups                       │   │
│ │    gdrive_count = countGoogleDriveBackups() → API   │   │
│ │                                                      │   │
│ │ 3. Count S3 backups                                 │   │
│ │    s3_count = countS3Backups() → API                │   │
│ │                                                      │   │
│ │ 4. Calculate total size                             │   │
│ │    total_size = sum(all backup sizes)               │   │
│ │                                                      │   │
│ │ 5. Check 3-2-1 compliance                          │   │
│ │    3-COPY: local_count ≥ 3? ✓                      │   │
│ │    2-MEDIA: (gdrive_count > 0 OR s3_count > 0)? ✓  │   │
│ │    1-OFFSITE: (gdrive_count > 0 OR s3_count > 0)? ✓│   │
│ │                                                      │   │
│ │    strategy_compliant =                             │   │
│ │      (3-COPY AND 2-MEDIA AND 1-OFFSITE)            │   │
│ │                                                      │   │
│ └──────────────────────────────────────────────────────┘   │
│                                                              │
│ Output: Array of stats                                      │
│ {                                                           │
│   'local_count': 3,                                         │
│   'google_drive_count': 0,                                  │
│   's3_count': 0,                                            │
│   'total_size': '267.39 MB',                               │
│   'strategy_compliant': false  ← Need more offsite         │
│ }                                                           │
│                                                              │
│ Display Logic in View:                                      │
│ if strategy_compliant:                                      │
│   ✅ 3-2-1 Strategy Compliant                              │
│ else:                                                       │
│   ⚠️ Strategy Not Compliant → Add offsite copies           │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

## Authorization Flow

```
User Request → /admin/backup/
        ↓
┌───────────────────────────────┐
│ Middleware: auth              │
│ Verify: User logged in?       │
│ No → Redirect to login        │
│ Yes → Continue               │
└───────────────────────────────┘
        ↓
┌───────────────────────────────┐
│ Controller Method             │
│ Call: $this->isAdmin()        │
└───────────────────────────────┘
        ↓
┌─────────────────────────────────────────────────────┐
│ isAdmin() checks (in order):                        │
│                                                     │
│ 1. $user->hasRole('admin')      ← Try first        │
│    if (method_exists && true) → return true        │
│                                                     │
│ 2. $user->role === 'admin'      ← Fallback #1      │
│    if (attribute exists && equals) → return true   │
│                                                     │
│ 3. $user->role_name === 'admin' ← Fallback #2      │
│    if (attribute exists && equals) → return true   │
│                                                     │
│ All checks false → return false                    │
│                                                     │
└─────────────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────┐
│ If admin:                                │
│ ✓ Proceed with operation                 │
│ ✓ Return view/JSON with data            │
│                                          │
│ If not admin:                            │
│ ✗ abort(403, 'Unauthorized')            │
│ ✗ Return error response                  │
└──────────────────────────────────────────┘
```

## Component Interaction Diagram

```
┌──────────────────────────────────────────────────────────────────┐
│                    BACKUP GUI COMPONENTS                         │
│                                                                   │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ NAVBAR & BREADCRUMB                                        │  │
│  │ Home > Backup Management                                   │  │
│  └────────────────────────────────────────────────────────────┘  │
│                          ↓                                        │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ HEADER                                                     │  │
│  │ 🗄️  Backup & Restore Management                            │  │
│  │ Admin-only backup control panel with 3-2-1 strategy       │  │
│  └────────────────────────────────────────────────────────────┘  │
│                          ↓                                        │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ 3-2-1 STRATEGY STATUS                                      │  │
│  │ ┌──────────────────────────────────────────────────────┐   │  │
│  │ │ 🖥️ Local  │ 🔵 GDrive │ ☁️ S3  │ Compliance Badge   │   │  │
│  │ │ 3 backups │ 0 backups │ 0 bkps│ ✅ or ⚠️            │   │  │
│  │ └──────────────────────────────────────────────────────┘   │  │
│  └────────────────────────────────────────────────────────────┘  │
│                          ↓                                        │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ SUMMARY & STATUS                                           │  │
│  │ ┌──────────────────────┐ ┌──────────────────────────────┐  │  │
│  │ │ Storage: 267.39 MB   │ │ GDrive: 🔗 Connected / 🚫 No │  │  │
│  │ └──────────────────────┘ └──────────────────────────────┘  │  │
│  └────────────────────────────────────────────────────────────┘  │
│                          ↓                                        │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ QUICK ACTIONS                                              │  │
│  │ [💾 Create Backup Now] [🔄 Refresh]                         │  │
│  └────────────────────────────────────────────────────────────┘  │
│                          ↓                                        │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ BACKUP FILES TABLE                                         │  │
│  │ ┌─────────────────────────────────────────────────────┐    │  │
│  │ │ Filename │ Size │ Created │ Download │ Delete       │    │  │
│  │ ├─────────────────────────────────────────────────────┤    │  │
│  │ │ backup-1 │ 89MB │ 2024... │    📥    │    🗑️       │    │  │
│  │ │ backup-2 │ 87MB │ 2024... │    📥    │    🗑️       │    │  │
│  │ │ backup-3 │ 91MB │ 2024... │    📥    │    🗑️       │    │  │
│  │ └─────────────────────────────────────────────────────┘    │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘

    MODALS (Hidden until triggered):
    ┌────────────────────────────────┐
    │ Creating Backup...             │
    │ ░░░░░░░░░░░░░░░░░░░░░░░░░░░░  │ (progress)
    │ Preparing backup...            │
    └────────────────────────────────┘
```

## File Structure

```
📦 Quotation
├── 📂 app/Http/Controllers/
│   └── BackupManagementController.php ✅ (262 lines)
│       ├── isAdmin()
│       ├── index()
│       ├── create()
│       ├── download()
│       ├── delete()
│       ├── getBackupsList()
│       ├── calculateBackupStats()
│       ├── formatBytes()
│       ├── getGoogleDriveStatus()
│       ├── countGoogleDriveBackups()
│       ├── countS3Backups()
│       └── uploadLatestBackupToGoogleDrive()
│
├── 📂 resources/views/
│   ├── 📂 admin/
│   │   └── backup-management.blade.php ✅ (302 lines)
│   │       ├── Breadcrumb navigation
│   │       ├── 3-2-1 status cards
│   │       ├── Storage summary
│   │       ├── GDrive status
│   │       ├── Quick actions
│   │       ├── Backup files table
│   │       ├── Progress modal
│   │       └── AJAX handlers (JS)
│   │
│   └── 📂 layouts/
│       ├── app.blade.php ✓ (extended by backup view)
│       └── sidebar.blade.php ✅ (updated)
│           └── Added admin "Backup & Restore" menu item
│
├── 📂 routes/
│   └── web.php ✅ (updated)
│       ├── POST   /admin/backup/create
│       ├── GET    /admin/backup/
│       ├── GET    /admin/backup/download/{filename}
│       └── POST   /admin/backup/delete
│
├── 📂 docs/
│   ├── BACKUP-GUI-IMPLEMENTATION.md ✅ (NEW)
│   ├── BACKUP-GUI-QUICKSTART.md ✅ (NEW)
│   └── BACKUP-GUI-STATUS.md ✅ (NEW)
│
├── 📂 storage/app/
│   └── 📂 laravel-backup/
│       ├── backup-2024-01-15-150245.zip
│       ├── backup-2024-01-14-021000.zip
│       └── backup-2024-01-13-021000.zip
│
└── config/
    └── backup.php ✓ (Spatie config, existing)
```

## Security Model

```
Request comes in
        ↓
┌────────────────────────────────────────────────────┐
│ Layer 1: HTTP Middleware (auth)                    │
│ - Check: User is authenticated?                    │
│ - No → Redirect to login                           │
│ - Yes → Continue                                   │
└────────────────────────────────────────────────────┘
        ↓
┌────────────────────────────────────────────────────┐
│ Layer 2: Controller Method (isAdmin())             │
│ - Check: User has admin role?                      │
│ - No → abort(403)                                  │
│ - Yes → Allow operation                           │
└────────────────────────────────────────────────────┘
        ↓
┌────────────────────────────────────────────────────┐
│ Layer 3: Input Validation                          │
│ - Sanitize: basename() on filenames                │
│ - Validate: File exists in correct location        │
│ - Check: No directory traversal                    │
└────────────────────────────────────────────────────┘
        ↓
┌────────────────────────────────────────────────────┐
│ Layer 4: Operation Execution                       │
│ - Execute: unlink(), artisan, etc.                 │
│ - Handle: Exceptions                              │
│ - Log: Success/failure                            │
└────────────────────────────────────────────────────┘
        ↓
Response sent to user
```

