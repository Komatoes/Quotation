# System Logs Implementation Guide

## Overview

The System Logs feature is an **admin-only audit trail** that records all important system activities. This helps track user actions, quotation changes, approvals, rejections, and more.

---

## Features

✅ **Comprehensive Logging**
- User actions (who did what, when, from where)
- Quotation approvals and rejections
- Comment creation and modifications
- Project updates
- Quotation downloads

✅ **Admin Dashboard**
- View all system logs with timestamps
- Filter by action, user, model, date range
- Search functionality
- Export to CSV
- Pagination (25 logs per page)

✅ **Data Tracking**
- User ID and email
- Action type with color-coded badges
- IP address and user agent
- Before/after changes stored as JSON
- Timestamps with human-readable format ("2m ago", "5h ago")

✅ **Maintenance Tools**
- Clear logs older than 90 days
- Export logs for external analysis
- Detailed log views

---

## Installation & Setup

### 1. Database Setup ✅
The migration has been executed successfully:
- Table: `system_logs`
- Columns: id, user_id, action, description, model, model_id, ip_address, user_agent, changes, timestamps
- Indexes: user_id, action, model, created_at, [model, model_id]

### 2. Model
**File**: `app/Models/SystemLog.php`

**Features**:
- Relationship to User model
- 7 query scopes: byAction(), byUser(), byModel(), byModelId(), recent(), today(), thisWeek(), thisMonth()
- Methods: getActionBadgeColor(), getActionIcon()

**Example Usage**:
```php
// Get recent logs
$logs = SystemLog::recent()->limit(10)->get();

// Get logs for a specific user
$userLogs = SystemLog::byUser(auth()->id())->get();

// Get approval actions
$approvals = SystemLog::byAction('approved')->get();

// Get logs from today
$todayLogs = SystemLog::today()->get();
```

### 3. Controller
**File**: `app/Http/Controllers/Admin/AdminLogController.php`

**Methods**:
- `index()` - Display logs with filters and pagination
- `show()` - Show detailed log view
- `export()` - Export filtered logs to CSV
- `clearOldLogs()` - Delete logs older than 90 days

### 4. Views
**File**: `resources/views/admin/logs/index.blade.php`

Features:
- Advanced filtering (action, model, date range, search)
- Beautiful table with user info, timestamps
- Color-coded action badges
- Responsive design
- Export and clear buttons

### 5. Routes
**File**: `routes/web.php`

```php
Route::middleware(['auth'])->prefix('admin/logs')->group(function () {
    Route::get('/', [AdminLogController::class, 'index'])->name('admin.logs.index');
    Route::get('/show/{log}', [AdminLogController::class, 'show'])->name('admin.logs.show');
    Route::get('/export', [AdminLogController::class, 'export'])->name('admin.logs.export');
    Route::post('/clear', [AdminLogController::class, 'clearOldLogs'])->name('admin.logs.clear');
});
```

### 6. Sidebar Integration
**File**: `resources/views/layouts/sidebar.blade.php`

Added "System Logs" button in Administration section (admin-only):
```blade
<li class="menu-item">
    <a href="{{ route('admin.logs.index') }}" class="menu-link">
        <i class="fa-solid fa-file-lines menu-icon"></i>
        <div>System Logs</div>
    </a>
</li>
```

---

## Helper Methods

**File**: `app/Helpers/SystemLogHelper.php`

Easy-to-use static helper methods for logging throughout your application:

### Basic Logging
```php
use App\Helpers\SystemLogHelper;

// Log any action
SystemLogHelper::log(
    'created',
    'New quotation created',
    'App\Models\Quotation',
    $quotationId
);

// Log with changes
SystemLogHelper::log(
    'updated',
    'Quotation status changed',
    'App\Models\Quotation',
    $quotationId,
    ['status' => ['draft', 'approved']]
);
```

### Specific Action Helpers
```php
// Log quotation action
SystemLogHelper::logQuotation(
    'created',
    'Quotation #123 created for John Doe',
    $quotationId
);

// Log approval
SystemLogHelper::logApproval(
    'QT-2024-001',
    'John Doe'
);

// Log rejection
SystemLogHelper::logRejection(
    'QT-2024-001',
    'John Doe',
    'Price is too high'
);

// Log download
SystemLogHelper::logDownload('Quotation', 'QT-2024-001.pdf', $quotationId);

// Log comment
SystemLogHelper::logComment(
    'created',
    'Comment added to quotation QT-2024-001',
    $commentId
);

// Log project action
SystemLogHelper::logProject(
    'updated',
    'Project status changed to ongoing',
    $projectId
);
```

### Query Helpers
```php
// Get recent logs (10 max)
$recent = SystemLogHelper::getRecentLogs(10);

// Get user's logs
$userLogs = SystemLogHelper::getUserLogs(auth()->id(), 20);

// Get quotation history
$history = SystemLogHelper::getQuotationLogs($quotationId);
```

---

## How to Use in Your Application

### Example 1: Log When Creating a Quotation

In `QuotationController@store()`:

```php
public function store(Request $request)
{
    $quotation = Quotation::create($request->validated());
    
    // Log the action
    SystemLogHelper::logQuotation(
        'created',
        "New quotation created: {$quotation->number}",
        $quotation->id
    );
    
    return redirect()->back()->with('success', 'Quotation created!');
}
```

### Example 2: Log When Approving a Quotation

In `QuotationController@approve()`:

```php
public function approve($id)
{
    $quotation = Quotation::findOrFail($id);
    $quotation->update(['status_id' => 2]); // Approved
    
    // Log the action
    SystemLogHelper::logApproval(
        $quotation->number,
        auth()->user()->name
    );
    
    return back()->with('success', 'Quotation approved!');
}
```

### Example 3: Log When Adding a Comment

In `QuotationCommentController@store()`:

```php
public function store(Request $request)
{
    $comment = QuotationComment::create($request->validated());
    
    // Log the action
    SystemLogHelper::logComment(
        'created',
        "Comment added to quotation #{$comment->quotation_id}",
        $comment->id
    );
    
    return back();
}
```

---

## Available Action Types

The system recognizes these action types:

| Action | Badge Color | Icon | Use Case |
|--------|------------|------|----------|
| `created` | Green | fa-plus-circle | New record created |
| `updated` | Cyan | fa-edit | Record modified |
| `deleted` | Red | fa-trash | Record deleted |
| `approved` | Teal | fa-check-circle | Quotation approved |
| `rejected` | Orange | fa-times-circle | Quotation rejected |
| `commented` | Blue | fa-comment | Comment added |
| `viewed` | Gray | fa-eye | Item viewed |
| `downloaded` | Purple | fa-download | File downloaded |

---

## Admin Dashboard Features

### Access
- URL: `/admin/logs`
- Sidebar: Administration → System Logs
- Permission: Admin only

### Filter Options
- **Search**: Search in description and action type
- **Action**: Filter by specific action (created, updated, deleted, etc.)
- **Model**: Filter by model type (Quotation, Project, Comment, etc.)
- **Start Date**: Filter logs from this date onwards
- **End Date**: Filter logs up to this date
- **Clear Filters**: Reset all filters

### Display
Each log shows:
- ✅ Log ID (badge)
- ✅ User (name + email)
- ✅ Action (color-coded badge with icon)
- ✅ Description (truncated, hover for full text)
- ✅ Related Model (type + ID)
- ✅ IP Address
- ✅ Date & Time (format + "2h ago" style)

### Actions
- **Export CSV**: Download filtered logs as CSV file
- **Clear Old Logs**: Delete logs older than 90 days
- **Pagination**: 25 logs per page

---

## Data Structure

### Database Schema

```sql
CREATE TABLE system_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NULLABLE (FK to users),
    action VARCHAR(255) NOT NULL,
    description TEXT NULLABLE,
    model VARCHAR(255) NULLABLE,
    model_id BIGINT NULLABLE,
    ip_address VARCHAR(45) NULLABLE,
    user_agent TEXT NULLABLE,
    changes JSON NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_model (model),
    INDEX idx_created_at (created_at),
    INDEX idx_model_model_id (model, model_id)
);
```

### Example Log Entry

```json
{
    "id": 1,
    "user_id": 5,
    "action": "approved",
    "description": "Customer John Doe approved quotation: QT-2024-001",
    "model": "App\Models\Quotation",
    "model_id": 42,
    "ip_address": "192.168.1.100",
    "user_agent": "Mozilla/5.0...",
    "changes": {
        "status": ["draft", "approved"],
        "approved_at": [null, "2024-12-08 10:30:00"]
    },
    "created_at": "2024-12-08 10:30:00",
    "updated_at": "2024-12-08 10:30:00"
}
```

---

## Integration Examples

### In NotificationHelper
```php
// When creating a customer approval notification
NotificationHelper::notify(
    $quotation->employee_id,
    'customer_approval',
    'Customer Approved Quotation',
    "Customer {$client_name} approved quotation: {$number}",
    'Quotation',
    $quotation->id
);

// Also log it
SystemLogHelper::logApproval($number, $client_name);
```

### In QuotationController
```php
// Log quotation creation
SystemLogHelper::logQuotation(
    'created',
    "New quotation created: {$quotation->number}",
    $quotation->id
);

// Log quotation update
SystemLogHelper::logQuotation(
    'updated',
    "Quotation {$quotation->number} status changed",
    $quotation->id,
    ['status_id' => [$old_status, $new_status]]
);
```

---

## Best Practices

### ✅ Do's
- Log important actions (create, update, delete, approve, reject)
- Include quotation/project numbers in descriptions
- Include client/user names for context
- Use provided helper methods (cleaner code)
- Store before/after changes when available

### ❌ Don'ts
- Don't log every page view (creates too many entries)
- Don't expose sensitive information (passwords, tokens)
- Don't use unclear descriptions
- Don't forget to log critical actions like approvals/rejections

### Example Good Log Description
```
"Quotation QT-2024-001 for John Doe approved by Admin User"
```

### Example Bad Log Description
```
"Something happened"
```

---

## Performance Considerations

### Indexes
The `system_logs` table has strategic indexes:
- `user_id` - Fast filtering by user
- `action` - Fast filtering by action type
- `model` - Fast filtering by model
- `created_at` - Fast filtering by date
- `(model, model_id)` - Fast lookups for specific records

### Cleanup
The system provides a tool to clear logs older than 90 days:
- URL: POST `/admin/logs/clear`
- Admin only
- Recommended to run monthly

### Pagination
The dashboard shows 25 logs per page to prevent performance issues.

---

## CSV Export Format

When you export logs, you get a CSV file with these columns:
```
ID,User,Action,Description,Model,Model ID,IP Address,Date Time
1,John Doe,approved,"Customer approved quotation",App\Models\Quotation,42,192.168.1.100,2024-12-08 10:30:00
```

---

## Troubleshooting

### Q: Logs not appearing?
**A**: Make sure to call `SystemLogHelper::log()` or related methods in your controller/model.

### Q: Can I see logs for a specific quotation?
**A**: Yes, use: `SystemLogHelper::getQuotationLogs($quotationId)`

### Q: How to delete old logs?
**A**: Use the "Clear Old Logs" button in admin, or run in code:
```php
SystemLog::where('created_at', '<', now()->subDays(90))->delete();
```

### Q: Can non-admins see logs?
**A**: No, the route is protected and the sidebar button only shows to admins.

---

## Files Changed/Created

```
✅ Created:
- database/migrations/2024_12_08_000002_create_system_logs_table.php
- app/Models/SystemLog.php
- app/Http/Controllers/Admin/AdminLogController.php
- app/Helpers/SystemLogHelper.php
- resources/views/admin/logs/index.blade.php
- SYSTEM_LOGS_GUIDE.md (this file)

✅ Modified:
- routes/web.php (added system logs routes)
- resources/views/layouts/sidebar.blade.php (added sidebar button)
```

---

## Summary

System Logs is a complete audit trail system that:
- 📊 Tracks all important system activities
- 🔍 Provides advanced filtering and search
- 📈 Helps with compliance and troubleshooting
- 📁 Exports data for external analysis
- ⚙️ Automatically manages old records

All admin-only, secure, and performant! 🚀
