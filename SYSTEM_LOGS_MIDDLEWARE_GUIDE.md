# System Logs - Automatic Action Tracking

## Overview

Your quotation system now has **automatic action tracking via middleware and model observers**. Every significant action in your system is automatically logged without requiring manual logging calls.

## What Gets Logged Automatically

### ✅ HTTP Actions (via Middleware)
All non-GET requests (POST, PUT, PATCH, DELETE) are automatically logged:
- User who performed the action
- HTTP method and endpoint
- Request data (sensitive fields excluded)
- Response status code
- IP address and user agent
- Related model and record ID

**Excluded from logging:**
- GET requests (view-only operations)
- Notification routes
- Profile/password routes
- Admin logs viewing itself
- Unauthenticated requests

### ✅ Database Changes (via Observers)
Create, Update, Delete operations are automatically logged for:
- **Quotations** - Every quotation change is tracked
- **Projects** - Every project modification is logged
- **Materials** - Every material change is recorded
- **Clients** - Every client update is captured

**What gets captured:**
- Before and after values (for updates)
- Exact timestamp
- User who made the change
- Changes stored as JSON for detailed comparison

## Architecture

### 1. Middleware: `LogSystemActions`
**Location:** `app/Http/Middleware/LogSystemActions.php`

Captures all HTTP requests after they're processed and logs them automatically.

**How it works:**
```php
// Applied to all web routes in app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \App\Http\Middleware\LogSystemActions::class,
    ],
];
```

**Smart filtering:**
- Only logs authenticated users
- Skips GET requests (view operations)
- Excludes sensitive routes
- Safely sanitizes sensitive data (passwords, tokens, etc.)

### 2. Model Observers
**Location:** `app/Observers/`

Four observers watch for database changes:
- `QuotationObserver.php`
- `ProjectObserver.php`
- `MaterialObserver.php`
- `ClientObserver.php`

**Registered in:** `app/Providers/AppServiceProvider.php`

**How it works:**
```php
// In AppServiceProvider::boot()
Quotation::observe(QuotationObserver::class);
Project::observe(ProjectObserver::class);
Material::observe(MaterialObserver::class);
Client::observe(ClientObserver::class);
```

**Events monitored:**
- `created` - When a record is first created
- `updated` - When a record is modified
- `deleted` - When a record is deleted
- `restored` - When a soft-deleted record is restored
- `forceDeleted` - When a record is permanently deleted

### 3. Helper: `SystemLogHelper`
**Location:** `app/Helpers/SystemLogHelper.php`

Static helper methods for manual logging when needed.

**Available methods:**
```php
// Generic logging
SystemLogHelper::log($action, $description, $model, $modelId, $changes);

// Specific entity logging
SystemLogHelper::logQuotation($action, $description, $quotationId, $changes);
SystemLogHelper::logProject($action, $description, $projectId, $changes);
SystemLogHelper::logComment($action, $description, $commentId, $changes);

// Specific action logging
SystemLogHelper::logApproval($description, $model, $modelId);
SystemLogHelper::logRejection($description, $modelId, $reason);
SystemLogHelper::logDownload($description, $model, $modelId);
```

## Example Scenarios

### Scenario 1: Creating a Quotation
```
User submits form to create a new quotation:

1. HTTP Request (POST /quotations)
   └─ Middleware logs: "POST /quotations (Status: 201) - Success"
   
2. Quotation Model Created
   └─ Observer logs: "Quotation created: QT-001-2025"
   └─ Captures: created_at timestamp, user_id
   
System Logs Table now has 2 new entries:
├─ HTTP action log (by middleware)
└─ Create event log (by observer)
```

### Scenario 2: Updating Material Price
```
User changes material cost:

1. HTTP Request (PUT /materials/5)
   └─ Middleware logs: "PUT /materials/5 (Status: 200) - Success"
   
2. Material Model Updated
   └─ Observer logs: "Material updated: Steel Sheet"
   └─ Captures: before & after values
   
System Logs Table now shows:
├─ HTTP action log (by middleware)
└─ Update event log (by observer)
   └─ Changes: {"before": {"price": 100}, "after": {"price": 150}}
```

### Scenario 3: Deleting a Project
```
User soft-deletes a project:

1. HTTP Request (DELETE /projects/3)
   └─ Middleware logs: "DELETE /projects/3 (Status: 200) - Success"
   
2. Project Model Deleted
   └─ Observer logs: "Project deleted: Office Renovation"
   
System Logs Table entries created:
├─ HTTP action log (by middleware)
└─ Delete event log (by observer)
```

## Data Structure

Each log entry captures:
```
{
  "id": 1,
  "user_id": 5,
  "action": "created",
  "description": "Quotation created: QT-001-2025",
  "model": "App\Models\Quotation",
  "model_id": 42,
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "changes": {
    "created_at": "2025-12-08 14:35:22"
  },
  "created_at": "2025-12-08 14:35:22",
  "updated_at": "2025-12-08 14:35:22"
}
```

## Accessing Logs

### Via Admin Dashboard
Navigate to: **http://localhost:8000/admin/logs**

Features:
- View all system logs with full details
- Filter by action type (created, updated, deleted, etc.)
- Filter by model type (Quotation, Project, Material, Client)
- Search by description
- Filter by date range
- Export logs to CSV
- Clear old logs (90+ days)

### Via Database
Query directly:
```php
// Get all quotation logs
$logs = SystemLog::where('model', 'App\Models\Quotation')->get();

// Get specific action logs
$logs = SystemLog::where('action', 'created')->get();

// Get user's actions
$logs = SystemLog::where('user_id', 5)->get();

// Get recent logs
$logs = SystemLog::recent()->limit(100)->get();

// Get logs for specific model
$logs = SystemLog::byModel('App\Models\Quotation')->get();
```

### Via Helper Methods
```php
use App\Helpers\SystemLogHelper;

// Get recent logs
$logs = SystemLogHelper::getRecentLogs(50);

// Get user's logs
$logs = SystemLogHelper::getUserLogs(5);

// Get quotation-specific logs
$logs = SystemLogHelper::getQuotationLogs(42);
```

## Configuration

### Changing Logged Routes
Edit `app/Http/Middleware/LogSystemActions.php`:

```php
private function shouldSkipLogging(Request $request): bool
{
    $skipRoutes = [
        'notifications.',      // Skip these
        'profile.',           // Skip these
        // Add more as needed
    ];
    // ...
}
```

### Changing Logged Models
Edit `app/Providers/AppServiceProvider.php`:

```php
public function boot()
{
    // Currently observing:
    Quotation::observe(QuotationObserver::class);
    Project::observe(ProjectObserver::class);
    Material::observe(MaterialObserver::class);
    Client::observe(ClientObserver::class);
    
    // To add more:
    // YourModel::observe(YourObserver::class);
}
```

### Creating Observers for Additional Models
```bash
# Create an observer (in terminal)
php artisan make:observer YourModelObserver --model=YourModel
```

Then register in `AppServiceProvider::boot()`:
```php
YourModel::observe(YourModelObserver::class);
```

## Performance Considerations

### What We Did to Keep It Fast
- ✅ Logging happens **after** response (non-blocking)
- ✅ Only logs authenticated requests
- ✅ Skips GET requests (view operations)
- ✅ Database indexes on frequently queried columns
- ✅ Automatic cleanup of logs older than 90 days

### Database Indexes
The `system_logs` table has strategic indexes:
- `user_id` - Fast user filtering
- `action` - Fast action type filtering
- `model` - Fast model type filtering
- `created_at` - Fast date range filtering
- `(model, model_id)` - Fast record lookups

## Maintenance

### Automatic Cleanup
The system automatically deletes logs older than 90 days.

**Manual cleanup (if needed):**
```bash
php artisan logs:clear
```

**Clear old logs via admin UI:**
1. Go to **Admin → System Logs**
2. Click "Clear Old Logs" button
3. Confirm deletion

### Monitoring Log Growth
```bash
# Check database size
SELECT COUNT(*) FROM system_logs;

# Check logs by model
SELECT model, COUNT(*) as count FROM system_logs GROUP BY model;

# Check logs by action
SELECT action, COUNT(*) as count FROM system_logs GROUP BY action;
```

## Troubleshooting

### Logs Not Appearing
1. **Check middleware is registered:**
   ```bash
   php artisan route:list
   ```
   Should show LogSystemActions in web middleware group

2. **Check observers are registered:**
   Look for observer bindings in `AppServiceProvider.php`

3. **Clear caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Check database:**
   ```bash
   # Does table exist?
   php artisan migrate:status
   
   # Are there logs?
   php artisan tinker
   > App\Models\SystemLog::count()
   ```

### Logs Appearing for Unexpected Actions
Review the `shouldSkipLogging()` method in `LogSystemActions.php` middleware and adjust the `$skipRoutes` array.

### Performance Issues
1. Check database indexes exist
2. Archive old logs (clear logs older than 90 days)
3. Consider reducing log retention period
4. Check if middleware is running on too many routes

## Integration Examples

### Log Custom Actions
```php
// In your controller:
use App\Helpers\SystemLogHelper;

public function approveQuotation($quotationId)
{
    $quotation = Quotation::findOrFail($quotationId);
    
    // Do the approval
    $quotation->update(['status' => 'approved']);
    
    // Log the action
    SystemLogHelper::logApproval(
        description: "Quotation approved by " . auth()->user()->name,
        model: 'App\Models\Quotation',
        modelId: $quotationId
    );
    
    return redirect()->back()->with('success', 'Quotation approved');
}
```

### Get Audit Trail for Record
```php
// Get all changes to a specific quotation
$changes = SystemLog::where('model', 'App\Models\Quotation')
    ->where('model_id', 42)
    ->orderBy('created_at', 'asc')
    ->get();

// Display timeline
foreach ($changes as $log) {
    echo "{$log->user->name} {$log->action} at {$log->created_at}";
}
```

## Next Steps

✅ **System logs are now fully operational!**

Your system is tracking:
- **All HTTP actions** (POST, PUT, PATCH, DELETE)
- **All database changes** (Quotation, Project, Material, Client)
- **User who performed action**
- **When it happened**
- **What changed** (before/after values)
- **Request details** (IP, user agent)

### Optional Enhancements
- Add logging to more models (Comment, User, etc.)
- Create dashboard widget showing recent activity
- Set up alerts for critical actions
- Export logs for compliance/audit purposes
- Create user activity reports

**View your logs at:** http://localhost:8000/admin/logs
