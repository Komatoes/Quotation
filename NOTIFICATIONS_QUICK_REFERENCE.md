# Notifications System - Quick Reference

## 🔔 What Triggers Notifications?

### 1. **When Customer Adds a Comment**
```
✅ Customer views quotation link → Adds comment
→ Notification created for all admin/staff
→ Type: "comment"
→ Icon: 💬 (info blue)
→ Message: "Customer added a comment on quotation: ABC-001"
```

### 2. **When Admin Approves Quotation**
```
✅ Admin clicks "Approve" button
→ Validates contract fields (subject, dates)
→ Notification created for all admin/staff
→ Type: "approval"
→ Icon: ✓ (success green)
→ Message: "Quotation ABC-001 has been approved by John Doe"
```

### 3. **When Admin Rejects Quotation**
```
✅ Admin clicks "Reject" button → Selects reason
→ Notification created for all admin/staff
→ Type: "rejection"
→ Icon: ✕ (danger red)
→ Message: "Quotation ABC-001 has been rejected"
```

## 📍 Where to Find Notifications

### Navbar Bell Icon
```
Dashboard → Top Right Corner → 🔔 Bell Icon
```

**Features**:
- **Red Badge**: Shows count of unread notifications
- **Click to Open**: Dropdown with up to 5 latest unread notifications
- **Auto-Refresh**: Updates every 30 seconds

### Dropdown Menu
```
Bell Icon ↓
├── Notification Item 1
├── Notification Item 2
├── Notification Item 3
├── [Mark All As Read] button
├── [Clear All] button
└── [View All Notifications] link
```

## 🎯 Actions You Can Take

### Mark Notification as Read
```
Single: Click on notification → Auto-marked as read → Navigates to quotation
All: Click "Mark All As Read" button → All notifications marked
```

### Delete Notification
```
Single: Click delete icon (🗑️) on notification → Confirm → Removed
All: Click "Clear All" button → Confirm → All deleted
```

### View Related Quotation
```
Click notification → Auto-marked as read → Navigates to /quotations/{id}
```

## 💾 Notification Structure

```javascript
{
  id: 1,
  user_id: 5,              // Admin user ID
  type: 'comment',         // comment, approval, rejection, status_change, project_update, new_quotation
  title: 'New Comment',
  message: 'Customer added comment on quotation ABC-001',
  related_model: 'Quotation',  // Link to specific quotation
  related_id: 123,             // Quotation ID
  read: false,             // true if marked as read
  read_at: null,           // Timestamp when marked read
  created_at: '2024-12-08 14:30:00',
  updated_at: '2024-12-08 14:30:00'
}
```

## 🎨 Notification Types & Styling

| Type | Icon | Color | When | Message Example |
|------|------|-------|------|-----------------|
| **comment** | fa-comment | Blue | Customer comments | "Customer added a comment on quotation ABC-001" |
| **approval** | fa-check-circle | Green | Admin approves | "Quotation ABC-001 has been approved by John" |
| **rejection** | fa-times-circle | Red | Admin rejects | "Quotation ABC-001 has been rejected" |
| **status_change** | fa-sync-alt | Orange | Status updated | "Project XYZ status changed from Draft to Active" |
| **project_update** | fa-chart-line | Purple | Progress updated | "Project XYZ progress updated to 50%" |
| **new_quotation** | fa-file-invoice | Gray | New quotation | "New quotation ABC-001 created by admin" |

## 🔄 Real-Time Updates

### How It Works
```
Page Load → JavaScript starts
  ↓
Every 30 seconds:
  1. Fetch unread notification count
  2. Update red badge number
  3. Fetch latest 5 unread notifications
  4. Update dropdown list

User clicks notification:
  1. Mark as read (AJAX)
  2. Navigate to related quotation
  3. Badge count decreases

User clicks "Mark All As Read":
  1. Bulk mark all as read (AJAX)
  2. Badge disappears
  3. All notifications marked as read

User clicks delete:
  1. Delete notification (AJAX)
  2. Remove from dropdown
  3. Count updates
```

## 📱 Mobile Support

- ✅ Bell icon responsive on mobile
- ✅ Dropdown repositions for mobile screens
- ✅ Touch-friendly buttons
- ✅ Readable text on small screens

## 🚀 Performance

- **Database**: Indexed queries for fast lookups
- **Caching**: 30-second refresh reduces server load
- **Pagination**: Limits data transfer
- **AJAX**: Non-blocking real-time updates

## 💡 Pro Tips

1. **Unread Count**: Red badge shows only unread notifications
2. **Auto-Navigate**: Click notification to go directly to quotation
3. **Bulk Actions**: Use "Mark All" or "Clear All" for quick cleanup
4. **No Spam**: Notifications only created for actual events
5. **Time Format**: Shows relative time (e.g., "5m ago", "2h ago")

## 🔍 API Endpoints (for developers)

```
GET  /notifications/count              - Get unread count
GET  /notifications                    - Get all notifications (paginated)
GET  /notifications/unread             - Get unread only (max 5)
POST /notifications/{id}/read          - Mark single as read
POST /notifications/mark-all-read      - Bulk mark all as read
DELETE /notifications/{id}             - Delete single
DELETE /notifications                  - Delete all
```

**Authentication**: All endpoints require login (middleware:auth)

**Response Format**: JSON
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { /* ... */ }
}
```

## 📞 Common Issues

### Red Badge Not Showing
- Solution: Refresh page (browser cache)
- Check: Is user logged in?

### Notifications Not Appearing
- Solution: Check user has admin/staff role
- Check: Is the action (comment, approval) actually happening?
- Check: User ID is correctly stored in notification

### Notification Count Not Updating
- Solution: Wait 30 seconds for auto-refresh
- Manual: Refresh page with F5

## 🎓 How to Use in Your Code

### For Developers Creating Notifications

```php
use App\Helpers\NotificationHelper;

// Method 1: Direct creation
NotificationHelper::notify(
    userId: 1,
    type: 'comment',
    title: 'New Comment',
    message: 'Customer added a comment',
    relatedModel: 'Quotation',
    relatedId: 123
);

// Method 2: Helper method
NotificationHelper::notifyCommentAdded($comment, $quotation);
NotificationHelper::notifyQuotationApproved($quotation);
NotificationHelper::notifyQuotationRejected($quotation);
```

### For Querying Notifications

```php
// Get user's unread notifications
$unread = auth()->user()->notifications()->unread()->recent()->get();

// Get specific type
$comments = auth()->user()->notifications()->byType('comment')->get();

// Mark as read
$notification->markAsRead();

// Get 5 most recent
$recent = auth()->user()->notifications()->recent()->limit(5)->get();
```

## 📊 Notification Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                      CUSTOMER ACTION                         │
│              (Comment, Approval, Rejection)                  │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│            TRIGGER NOTIFICATION CREATION                     │
│   QuotationCommentController::storePublicComment()          │
│   QuotationController::updateStatus()                        │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│          NOTIFICATION HELPER CREATES RECORD                  │
│      NotificationHelper::notifyCommentAdded()                │
│      NotificationHelper::notifyQuotationApproved()           │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│        INSERT INTO NOTIFICATIONS TABLE                       │
│   user_id, type, title, message, related_model, read=false  │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│         ADMIN/STAFF DASHBOARD FRONT-END                      │
│  JavaScript fetches notifications every 30 seconds           │
│  Bell icon updates → Dropdown refreshes                      │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│              USER INTERACTION                                │
│  Click → Navigate + Mark as Read                             │
│  Mark All → Bulk read  →  Delete → Remove                    │
└─────────────────────────────────────────────────────────────┘
```

## ✅ Complete Feature Checklist

- ✅ Real-time notifications in navbar
- ✅ Comment triggers notification
- ✅ Approval triggers notification
- ✅ Rejection triggers notification
- ✅ Mark as read functionality
- ✅ Delete single notification
- ✅ Clear all notifications
- ✅ Unread count badge
- ✅ Auto-navigate to quotation
- ✅ Professional Font Awesome icons
- ✅ Responsive mobile design
- ✅ 30-second auto-refresh
- ✅ AJAX non-blocking updates
- ✅ Database indexes for performance
- ✅ User relationship configured

---

**System Status**: 🟢 **PRODUCTION READY**

All notification features are implemented and ready for use!
