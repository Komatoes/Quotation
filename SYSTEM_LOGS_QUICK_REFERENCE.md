# System Logs - Quick Reference

## Access System Logs

**URL**: `http://localhost/admin/logs`

**Sidebar**: Administration → System Logs (admin-only)

---

## Quick Usage Examples

### Log a Quotation Approval
```php
use App\Helpers\SystemLogHelper;

SystemLogHelper::logApproval('QT-2024-001', 'John Doe');
```

### Log a Quotation Rejection
```php
SystemLogHelper::logRejection('QT-2024-001', 'John Doe', 'Price is too high');
```

### Log a Custom Action
```php
SystemLogHelper::logQuotation(
    'updated',
    'Quotation status changed to ongoing',
    $quotationId
);
```

### Log a Download
```php
SystemLogHelper::logDownload('Quotation PDF', 'QT-2024-001.pdf', $quotationId);
```

### Log a Comment
```php
SystemLogHelper::logComment('created', 'Comment added', $commentId);
```

---

## Features at a Glance

| Feature | How to Use |
|---------|-----------|
| **View Logs** | Go to Admin → System Logs |
| **Filter by Action** | Use "Action" dropdown |
| **Filter by Model** | Use "Model" dropdown |
| **Search** | Enter keywords in search box |
| **Date Range** | Use "Start Date" and "End Date" |
| **Export to CSV** | Click "Export CSV" button |
| **Clear Old Logs** | Click "Clear Old Logs" button (deletes 90+ days old) |

---

## What Gets Logged

- ✅ Quotation creation, updates, approvals, rejections
- ✅ Comment creation and modifications
- ✅ Project updates
- ✅ File downloads
- ✅ Custom actions (anything you define)

---

## Log Entry Details

Each log shows:
- **ID**: Unique log identifier
- **User**: Who performed the action
- **Action**: What action was performed (with color badge)
- **Description**: Detailed information
- **Model**: Related object (Quotation, Project, etc.)
- **IP Address**: Where the action came from
- **Date & Time**: When it happened (e.g., "2h ago")

---

## Admin Dashboard

### Filters
```
Search field    → Search description
Action dropdown → Filter by action type
Model dropdown  → Filter by model type
Start Date      → From date
End Date        → To date
```

### Actions
```
Filter button       → Apply selected filters
Clear Filters link  → Reset all filters
Export CSV          → Download logs as CSV
Clear Old Logs      → Delete logs older than 90 days
```

### Display
- 25 logs per page
- Color-coded badges for action types
- Timestamps with "ago" format (e.g., "5m ago", "2h ago")
- User info (name + email)

---

## Helper Methods Cheat Sheet

```php
use App\Helpers\SystemLogHelper;

// Generic log
SystemLogHelper::log(action, description, model, modelId, changes);

// Quotation
SystemLogHelper::logQuotation(action, description, quotationId, changes);

// Project
SystemLogHelper::logProject(action, description, projectId, changes);

// Comment
SystemLogHelper::logComment(action, description, commentId, changes);

// Approval
SystemLogHelper::logApproval(quotationNumber, clientName, changes);

// Rejection
SystemLogHelper::logRejection(quotationNumber, clientName, reason, changes);

// Download
SystemLogHelper::logDownload(type, name, id);

// Get recent logs
SystemLogHelper::getRecentLogs(limit);

// Get user logs
SystemLogHelper::getUserLogs(userId, limit);

// Get quotation logs
SystemLogHelper::getQuotationLogs(quotationId);
```

---

## Action Types & Colors

| Action | Color | Icon | Usage |
|--------|-------|------|-------|
| created | Green | ➕ | New record |
| updated | Cyan | ✏️ | Modified record |
| deleted | Red | 🗑️ | Deleted record |
| approved | Teal | ✓ | Approval |
| rejected | Orange | ✗ | Rejection |
| commented | Blue | 💬 | Comment added |
| viewed | Gray | 👁️ | Item viewed |
| downloaded | Purple | ⬇️ | File download |

---

## File Locations

```
app/Models/SystemLog.php
app/Http/Controllers/Admin/AdminLogController.php
app/Helpers/SystemLogHelper.php
resources/views/admin/logs/index.blade.php
database/migrations/2024_12_08_000002_create_system_logs_table.php
routes/web.php (lines with 'admin/logs')
resources/views/layouts/sidebar.blade.php (System Logs button)
```

---

## Security Notes

✅ **Admin-Only Access**
- Only users with admin role can view logs
- Routes are protected with auth middleware

✅ **No Sensitive Data**
- Passwords and tokens are never logged
- Only action summaries are stored

✅ **Performance Optimized**
- Indexed columns for fast queries
- Pagination to prevent loading too much data
- Cleanup tool to remove old logs

---

## Common Scenarios

### Scenario 1: Customer approves quotation
```php
// In your approval handler:
$quotation->update(['status_id' => 2]); // approved
SystemLogHelper::logApproval($quotation->number, $client->name);
```

### Scenario 2: Admin rejects quotation
```php
// In your rejection handler:
$quotation->update(['status_id' => 3]); // rejected
SystemLogHelper::logRejection(
    $quotation->number,
    auth()->user()->name,
    $rejectionReason
);
```

### Scenario 3: Track quotation changes
```php
// In your update handler:
$changes = [
    'price' => [$oldPrice, $newPrice],
    'status' => [$oldStatus, $newStatus],
];
SystemLogHelper::logQuotation('updated', 'Quotation updated', $quotationId, $changes);
```

---

## Tips

💡 **Pro Tip 1**: Always include quotation/project numbers in descriptions for easy reference.

💡 **Pro Tip 2**: Use the export feature to analyze logs in Excel/Google Sheets.

💡 **Pro Tip 3**: Set up a monthly task to clear old logs (click "Clear Old Logs").

💡 **Pro Tip 4**: Check logs regularly to track user activity and system health.

---

## CSV Export Format

When you export logs, you get a file like:

```
ID,User,Action,Description,Model,Model ID,IP Address,Date Time
1,John Doe,approved,Customer John Doe approved quotation: QT-2024-001,App\Models\Quotation,42,192.168.1.100,2024-12-08 10:30:00
2,Jane Smith,created,New quotation created: QT-2024-002,App\Models\Quotation,43,192.168.1.105,2024-12-08 11:15:00
```

---

## Troubleshooting

**Q: Why don't I see the System Logs button?**
A: You must be logged in as an admin user.

**Q: How often should I clear old logs?**
A: Monthly cleanup is recommended. Logs older than 90 days are automatically removed.

**Q: Can I see logs for a specific quotation?**
A: Yes, filter by Model "Quotation" and search for the quotation number.

**Q: Are logs deleted automatically?**
A: No, only when you click "Clear Old Logs" or run the cleanup command.

---

## Next Steps

1. ✅ Access System Logs via sidebar
2. ✅ Explore the dashboard and filters
3. ✅ Export a CSV to see the data format
4. ✅ Start adding logging calls in your controllers
5. ✅ Review logs periodically for insights

---

**System Logs are now live! 🚀**
