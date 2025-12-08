# Notifications System - Architecture & Flow Diagrams

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         QUOTATION MANAGEMENT SYSTEM                      │
│                      + NOTIFICATIONS SUBSYSTEM                           │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                            FRONTEND LAYER                                │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌─────────────────┐     ┌──────────────────┐    ┌─────────────────┐  │
│  │  Dashboard      │     │  Bell Icon       │    │  User Dropdown  │  │
│  │  (View Page)    │     │  (Navbar)        │    │  (Logout)       │  │
│  └─────────────────┘     └────────┬─────────┘    └─────────────────┘  │
│                                   │                                      │
│                      ┌────────────┴──────────────┐                      │
│                      │                           │                      │
│                  ┌───▼──────────────┐  ┌────────▼───┐                  │
│                  │  Notification    │  │ Dropdown   │                  │
│                  │  Bell Component  │  │ Menu       │                  │
│                  │  notifications.  │  │ (5 items)  │                  │
│                  │  blade.php       │  └────────────┘                  │
│                  └────────┬─────────┘                                   │
│                           │                                              │
│                  ┌────────▼──────────────┐                              │
│                  │   Real-Time JS        │                              │
│                  │ - loadNotificationCount    │                              │
│                  │ - loadUnreadNotifications  │                              │
│                  │ - handleNotificationClick  │                              │
│                  │ - markAllAsRead           │                              │
│                  │ - deleteNotification      │                              │
│                  │ - clearAll                │                              │
│                  └────────┬──────────────┘                              │
│                           │                                              │
└───────────────────────────┼──────────────────────────────────────────────┘
                            │
                 (AJAX every 30 seconds)
                            │
┌───────────────────────────┼──────────────────────────────────────────────┐
│                    BACKEND API LAYER                                     │
├───────────────────────────┼──────────────────────────────────────────────┤
│                           │                                              │
│        ┌──────────────────▼──────────────────┐                          │
│        │  NotificationController (7 methods)  │                          │
│        │                                      │                          │
│        │  • getUnreadCount()                  │                          │
│        │  • getNotifications()                │                          │
│        │  • getUnreadNotifications()          │                          │
│        │  • markAsRead()                      │                          │
│        │  • markAllAsRead()                   │                          │
│        │  • delete()                          │                          │
│        │  • clearAll()                        │                          │
│        └────────────────┬─────────────────────┘                          │
│                         │                                                │
│        ┌────────────────▼──────────────────┐                            │
│        │  Notification Model (Eloquent)     │                            │
│        │                                    │                            │
│        │  • Relationships: belongsTo(User)  │                            │
│        │  • Scopes: unread, read, byType    │                            │
│        │  • Methods: markAsRead, getIcon    │                            │
│        │  • recent, getColor                │                            │
│        └────────────────┬─────────────────────┘                          │
│                         │                                                │
└─────────────────────────┼──────────────────────────────────────────────┘
                          │
┌─────────────────────────┼──────────────────────────────────────────────┐
│              NOTIFICATION CREATION TRIGGER LAYER                        │
├─────────────────────────┼──────────────────────────────────────────────┤
│                         │                                               │
│    ┌────────────────────┴──────────────────────┐                       │
│    │                                           │                       │
│  ┌─▼──────────────────────┐   ┌───────────────▼──┐                    │
│  │ QuotationComment      │   │ Quotation        │                    │
│  │ Controller            │   │ Controller       │                    │
│  │                       │   │                  │                    │
│  │ storePublicComment()  │   │ updateStatus()   │                    │
│  │ storeAdminComment()   │   │ (status_id=2,3) │                    │
│  └────────┬──────────────┘   └────────┬─────────┘                    │
│           │                           │                               │
│  ┌────────▼───────────────────────────▼───────┐                      │
│  │  NotificationHelper (6 static methods)      │                      │
│  │                                              │                      │
│  │  • notify($userId, $type, $title, ...)    │                      │
│  │  • notifyCommentAdded($comment, $quote)    │                      │
│  │  • notifyQuotationApproved($quote)         │                      │
│  │  • notifyQuotationRejected($quote)         │                      │
│  │  • notifyProjectStatusChange($proj, ...)   │                      │
│  │  • notifyProgressUpdate($proj, %)          │                      │
│  └────────┬───────────────────────────────────┘                      │
│           │                                                           │
└───────────┼───────────────────────────────────────────────────────────┘
            │
┌───────────▼───────────────────────────────────────────────────────────┐
│                        DATABASE LAYER                                 │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌────────────────────────────────────────────────┐                   │
│  │  notifications Table                           │                   │
│  │  ├─ id (PK)                                   │                   │
│  │  ├─ user_id (FK → users)                      │                   │
│  │  ├─ type (VARCHAR)                            │                   │
│  │  ├─ title (VARCHAR)                           │                   │
│  │  ├─ message (TEXT)                            │                   │
│  │  ├─ related_model (VARCHAR)                   │                   │
│  │  ├─ related_id (BIGINT)                       │                   │
│  │  ├─ read (BOOLEAN)                            │                   │
│  │  ├─ read_at (TIMESTAMP)                       │                   │
│  │  ├─ created_at (TIMESTAMP)                    │                   │
│  │  └─ updated_at (TIMESTAMP)                    │                   │
│  │                                                │                   │
│  │  Indexes:                                      │                   │
│  │  ├─ [user_id, read] - For unread queries     │                   │
│  │  └─ [created_at] - For ordering              │                   │
│  └────────────────────────────────────────────────┘                   │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

## Event Flow Diagram

### 1. Comment Notification Flow

```
┌──────────────────────────────────────────────────────────────────────┐
│                     CUSTOMER ACTION FLOW                             │
├──────────────────────────────────────────────────────────────────────┤

1. Customer opens quotation link → Views quotation details page
2. Writes comment in textarea → Clicks "Add Comment"
3. JavaScript sends AJAX POST to /quotation-comments/store
4. Request validated and saved to quotation_comments table

┌─────────────────────────────────────────────────────────────────┐
│ storePublicComment() in QuotationCommentController             │
└──────────────────┬──────────────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────────┐
│ NotificationHelper::notifyCommentAdded()                    │
│                                                             │
│ 1. Query all users with 'admin' or 'staff' role           │
│ 2. Loop through each admin/staff user                      │
│ 3. Call Notification::create() for each user               │
│                                                             │
│    Fields created:                                          │
│    • user_id = admin/staff ID                              │
│    • type = 'comment'                                      │
│    • title = 'New Comment'                                 │
│    • message = "Customer added comment on quotation XYZ"   │
│    • related_model = 'Quotation'                           │
│    • related_id = quotation->id                            │
│    • read = false                                          │
│    • created_at = now()                                    │
└──────────────────┬──────────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────────┐
│ INSERT INTO notifications (...)                             │
│ VALUES (admin_id, 'comment', 'New Comment', ..., false)    │
│                                                             │
│ [Repeat for each admin/staff user]                         │
└──────────────────┬──────────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────────┐
│ Admin Dashboard (Next 30 seconds auto-refresh)             │
│                                                             │
│ JavaScript polls: GET /notifications/count                 │
│ Response: { count: 1 }                                    │
│                                                             │
│ Bell icon updates:                                          │
│ • Red badge now shows "1"                                 │
│ • Icon animates to indicate new notification              │
└──────────────────┬──────────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────────┐
│ Admin clicks bell icon → Dropdown opens                   │
│                                                             │
│ JavaScript calls: GET /notifications/unread?limit=5        │
│ Response: { data: [notification_object] }                  │
│                                                             │
│ Notification displays:                                      │
│ • Icon: fa-comment (blue)                                 │
│ • Title: "New Comment"                                    │
│ • Message: "Customer added comment on quotation ABC-001"  │
│ • Time: "Just now"                                        │
│ • Delete button: 🗑️                                        │
└──────────────────┬──────────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────────┐
│ Admin clicks notification                                   │
│                                                             │
│ JavaScript:                                                 │
│ 1. POST /notifications/{id}/read                           │
│ 2. { read: true, read_at: now() }                          │
│ 3. Navigates to /quotations/{id}                          │
│                                                             │
│ Result:                                                     │
│ • Notification marked as read                              │
│ • Admin navigates to quotation details                    │
│ • Sees customer's comment                                  │
│ • Can reply if needed                                      │
└──────────────────────────────────────────────────────────────┘
```

### 2. Approval Notification Flow

```
┌──────────────────────────────────────────────────────────────────────┐
│                     ADMIN APPROVAL FLOW                              │
├──────────────────────────────────────────────────────────────────────┤

1. Admin clicks "Approve" button on quotation details page
2. Modal validates: Contract checkbox, subject, start date, end date
3. Admin submits form with confirmation
4. Request sent to QuotationController::updateStatus(status_id=2)

┌─────────────────────────────────────────────────────────────────┐
│ QuotationController::updateStatus()                            │
│ (status_id = 2, meaning "Approved")                            │
└──────────────────┬──────────────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────────┐
│ 1. Validate all required fields                            │
│ 2. Check authorization (admin/manager only)                │
│ 3. Update quotation: status_id = 2, contract fields...     │
│ 4. CALL: NotificationHelper::notifyQuotationApproved()    │
└──────────────────┬──────────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────────┐
│ NotificationHelper::notifyQuotationApproved($quotation)    │
│                                                             │
│ 1. Get all admin/staff users                              │
│ 2. For each admin/staff:                                  │
│    - Create notification with:                             │
│      • type = 'approval'                                   │
│      • title = 'Quotation Approved'                        │
│      • message = "Quotation ABC-001 approved by John"     │
│      • related_model = 'Quotation'                         │
│      • related_id = quotation->id                          │
│      • icon = fa-check-circle (green)                      │
└──────────────────┬──────────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────────┐
│ Response sent to client:                                    │
│ { success: true, message: "Quotation approved!" }          │
└──────────────────┬──────────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────────┐
│ All admin/staff see approval notification                  │
│ • Bell badge increments                                   │
│ • Dropdown shows green approval notification              │
│ • Click to navigate to quotation                          │
└──────────────────────────────────────────────────────────────┘
```

### 3. Rejection Notification Flow

```
┌──────────────────────────────────────────────────────────────────────┐
│                     ADMIN REJECTION FLOW                             │
├──────────────────────────────────────────────────────────────────────┤

1. Admin clicks "Reject" button on quotation
2. Modal opens with textarea and quick-select reason buttons
3. Admin selects reason (e.g., "Budget exceeded", "Timeline issue", custom)
4. Admin clicks "Reject" with confirmation
5. Request sent with status_id=3 and rejection_reason

┌─────────────────────────────────────────────────────────────────┐
│ QuotationController::updateStatus()                            │
│ (status_id = 3, meaning "Rejected")                            │
└──────────────────┬──────────────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────────┐
│ 1. Validate rejection_reason is provided                   │
│ 2. Check authorization                                     │
│ 3. Update quotation:                                       │
│    • status_id = 3                                         │
│    • rejection_reason = "Budget exceeded"                  │
│ 4. CALL: NotificationHelper::notifyQuotationRejected()    │
└──────────────────┬──────────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────────┐
│ NotificationHelper::notifyQuotationRejected($quotation)    │
│                                                             │
│ 1. Get all admin/staff users                              │
│ 2. For each admin/staff:                                  │
│    - Create notification with:                             │
│      • type = 'rejection'                                  │
│      • title = 'Quotation Rejected'                        │
│      • message = "Quotation ABC-001 has been rejected"    │
│      • icon = fa-times-circle (red)                        │
└──────────────────┬──────────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────────┐
│ All admin/staff see rejection notification                 │
│ • Red rejection notification in dropdown                   │
│ • Can click to view rejection reason                       │
│ • Can respond or archive quotation                         │
└──────────────────────────────────────────────────────────────┘
```

## Database Query Flow

```
┌─────────────────────────────────────────────────────┐
│         GET /notifications/unread?limit=5           │
└────────────────┬──────────────────────────────────┘
                 │
         ┌───────▼────────┐
         │  Check Auth    │
         │  (middleware)  │
         └───────┬────────┘
                 │
    ┌────────────▼──────────────────┐
    │  getUnreadNotifications()      │
    │  in NotificationController    │
    └────────────┬───────────────────┘
                 │
    ┌────────────▼──────────────────────────────────┐
    │  SELECT * FROM notifications                 │
    │  WHERE user_id = auth()->id()                │
    │  AND read = 0                                │
    │  ORDER BY created_at DESC                    │
    │  LIMIT 5                                      │
    │                                              │
    │  Index used: [user_id, read]                 │
    │  Performance: O(1) index lookup              │
    └────────────┬──────────────────────────────────┘
                 │
    ┌────────────▼──────────────────┐
    │  Return JSON response         │
    │                              │
    │  {                            │
    │    "data": [                  │
    │      {                        │
    │        "id": 1,               │
    │        "type": "comment",     │
    │        "title": "New Comment",│
    │        "message": "...",      │
    │        "created_at": "..."    │
    │      }                        │
    │    ],                         │
    │    "count": 1                 │
    │  }                            │
    └────────────┬───────────────────┘
                 │
    ┌────────────▼──────────────────┐
    │  Browser receives JSON        │
    │  JavaScript renders           │
    │  Dropdown updates             │
    │  Bell badge updates           │
    └───────────────────────────────┘
```

## Real-Time Update Cycle

```
┌─────────────────────────────────────────────────────────────┐
│          BROWSER NOTIFICATION UPDATE CYCLE                 │
├─────────────────────────────────────────────────────────────┤

┌──────────┐
│  Page    │
│  Load    │
└────┬─────┘
     │
     ├─► setInterval(loadNotificationCount, 30000)
     │   [Every 30 seconds]
     │
     ├─► setInterval(loadUnreadNotifications, 30000)
     │   [Every 30 seconds]
     │
     └─► Add click listeners for user actions

T=0s ┌─────────────────────────────────────┐
     │  Initial Load                       │
     │  • Count = 0 (no notifications)     │
     │  • Bell badge empty                 │
     └─────────────────────────────────────┘

T=30s ┌────────────────────────────────────────────┐
      │  Auto-refresh cycle #1                      │
      │  GET /notifications/count                   │
      │  Response: { count: 0 }                     │
      │  • No changes, bell stays empty             │
      └────────────────────────────────────────────┘

T=32s ┌────────────────────────────────────────────┐
      │  User Action: Customer adds comment         │
      │  → Comment stored to database               │
      │  → Notification created for all admins      │
      │  → Notification.read = false                │
      └────────────────────────────────────────────┘

T=60s ┌────────────────────────────────────────────┐
      │  Auto-refresh cycle #2                      │
      │  GET /notifications/count                   │
      │  Response: { count: 1 }                     │
      │  • Bell badge updates to "1"                │
      │  • Gets unread notifications                │
      │  • Renders in dropdown                      │
      │  • Admin sees notification                  │
      └────────────────────────────────────────────┘

T=65s ┌────────────────────────────────────────────┐
      │  User Action: Admin clicks notification     │
      │  • POST /notifications/{id}/read            │
      │  • Update: read = true, read_at = now()     │
      │  • Navigate to /quotations/{id}             │
      │  • Admin sees quotation with comment        │
      └────────────────────────────────────────────┘

T=90s ┌────────────────────────────────────────────┐
      │  Auto-refresh cycle #3                      │
      │  GET /notifications/count                   │
      │  Response: { count: 0 } (was marked read)   │
      │  • Bell badge becomes empty                 │
      │  • Dropdown shows empty state               │
      └────────────────────────────────────────────┘
```

## Component Interaction Diagram

```
┌────────────────────────────────────────────────────────────────┐
│                    notifications.blade.php                     │
│                      (Bell Component)                          │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  HTML Structure                                      │    │
│  │                                                      │    │
│  │  <div class="notification-container">               │    │
│  │    <button class="notification-toggle">             │    │
│  │      <i class="fa-solid fa-bell"></i>              │    │
│  │      <span class="badge" id="notif-count">0</span> │    │
│  │    </button>                                         │    │
│  │                                                      │    │
│  │    <div class="notification-dropdown" hidden>       │    │
│  │      <div class="dropdown-header">                  │    │
│  │        <h5>Notifications</h5>                       │    │
│  │        <div class="header-buttons">                 │    │
│  │          <button>Mark All as Read</button>          │    │
│  │          <button>Clear All</button>                 │    │
│  │        </div>                                        │    │
│  │      </div>                                          │    │
│  │                                                      │    │
│  │      <div class="notification-list" id="notif-list">    │
│  │        <!-- Notifications rendered here -->          │    │
│  │      </div>                                          │    │
│  │                                                      │    │
│  │      <div class="dropdown-footer">                  │    │
│  │        <a href="/notifications/all">                │    │
│  │          View All Notifications                     │    │
│  │        </a>                                          │    │
│  │      </div>                                          │    │
│  │    </div>                                            │    │
│  │  </div>                                              │    │
│  │                                                      │    │
│  └──────────────────────────────────────────────────────┘    │
│                                                                │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  JavaScript Functions                               │    │
│  │                                                      │    │
│  │  1. loadNotificationCount()                         │    │
│  │     • AJAX GET /notifications/count                │    │
│  │     • Update badge with count                      │    │
│  │     • Called every 30 seconds                       │    │
│  │                                                      │    │
│  │  2. loadUnreadNotifications()                       │    │
│  │     • AJAX GET /notifications/unread               │    │
│  │     • Render HTML for each notification            │    │
│  │     • Create click handlers                         │    │
│  │                                                      │    │
│  │  3. toggleNotificationsDropdown()                   │    │
│  │     • Show/hide dropdown on toggle                 │    │
│  │     • Close on click outside                        │    │
│  │                                                      │    │
│  │  4. handleNotificationClick(id)                     │    │
│  │     • AJAX POST /notifications/{id}/read           │    │
│  │     • Navigate to related quotation                │    │
│  │                                                      │    │
│  │  5. deleteNotification(id)                          │    │
│  │     • AJAX DELETE /notifications/{id}              │    │
│  │     • Remove from dropdown                          │    │
│  │                                                      │    │
│  │  6. markAllAsRead()                                 │    │
│  │     • AJAX POST /notifications/mark-all-read       │    │
│  │     • Clear all notifications                       │    │
│  │                                                      │    │
│  │  7. clearAllNotifications()                         │    │
│  │     • AJAX DELETE /notifications                   │    │
│  │     • Remove all notifications                      │    │
│  │                                                      │    │
│  └──────────────────────────────────────────────────────┘    │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

## User Interaction Flow

```
ADMIN/STAFF PERSPECTIVE
───────────────────────

┌─ Dashboard Page ──────────────────────────────────────────┐
│                                                            │
│  [Navbar: Home | Quotations | Projects | ... | 🔔(1) | ▼]│
│                                                            │
└────────────────────────────────────────────────────────────┘
                          ▲
                          │
                    User clicks bell

┌─ Notification Dropdown Opens ─────────────────────────────┐
│                                                            │
│  ┌─ Header ──────────────────────────────────────────┐   │
│  │ Notifications    [Mark All] [Clear All]           │   │
│  ├──────────────────────────────────────────────────┤   │
│  │                                                  │   │
│  │ 💬 New Comment                    [Delete]       │   │
│  │ Customer added comment on ABC-001                │   │
│  │ Just now                                         │   │
│  │                                                  │   │
│  │ ✓ Quotation Approved               [Delete]      │   │
│  │ Quotation ABC-001 approved by John               │   │
│  │ 5 minutes ago                                    │   │
│  │                                                  │   │
│  ├──────────────────────────────────────────────────┤   │
│  │ [View All Notifications]                         │   │
│  └──────────────────────────────────────────────────┘   │
│                                                            │
└────────────────────────────────────────────────────────────┘
                          ▲
                          │
                   User clicks notification
                          │
                          ▼
┌─ Navigates to Quotation Page ─────────────────────────────┐
│                                                            │
│ Quotation ABC-001 Details                                │
│ Client: Acme Corp                                         │
│ Status: Approved                                          │
│ Created: 2024-12-01                                       │
│                                                            │
│ [Comments Section]                                        │
│ ┌────────────────────────────────────────────────────┐  │
│ │ 💬 Customer Comment                                │  │
│ │ "This looks good, please proceed"                  │  │
│ │ 10 minutes ago                                     │  │
│ │ [Reply]                                            │  │
│ └────────────────────────────────────────────────────┘  │
│                                                            │
└────────────────────────────────────────────────────────────┘
                          ▲
                          │
                   Notification auto-marked
                   as read, Bell badge --1
```

---

## Summary

The notifications system provides:
- ✅ **Real-time updates** every 30 seconds
- ✅ **Automatic triggers** on key events
- ✅ **Professional UI** with Font Awesome icons
- ✅ **Quick navigation** to related quotations
- ✅ **Easy management** (mark read, delete, clear all)
- ✅ **Database optimization** with proper indexes
- ✅ **Seamless integration** with existing system

**System Ready**: 🟢 **PRODUCTION READY**
