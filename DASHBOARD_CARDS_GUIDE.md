# Dashboard Notification Cards - How They Work

## Overview

Two new dashboard cards have been added to provide quick visibility into:
1. **Unread Messages** - Notifications waiting for your attention
2. **Customer Approvals** - Quotations that customers have approved

---

## Dashboard Cards Explained

### 1. Unread Messages Card 📬

**Location**: Top-left of dashboard (purple gradient)

**Shows**: Count of unread notifications

**What it displays**:
- Number of unread notifications for the logged-in user
- Click to open the notification bell dropdown in navbar
- Real-time count (updates every 30 seconds)

**Functionality**:
```
Unread Messages: 5
Click → Opens notification dropdown in navbar
```

**Example use case**:
- See at a glance you have 5 new notifications
- Click card to view them immediately
- Notifications include: comments, approvals, rejections

---

### 2. Customer Approvals Card ✅

**Location**: Top-right of dashboard (pink/red gradient)

**Shows**: Count of quotations approved by customers

**What it displays**:
- Number of quotations with `customer_approved = true`
- AND `approved_by_customer_at` is not null
- Click to navigate to detailed reports page

**Functionality**:
```
Customer Approvals: 12
Click → Navigate to quotation reports page
```

**Example use case**:
- See 12 quotations have been approved by customers
- Click to view detailed breakdown
- Compare with pending quotations

---

## How They Work

### Unread Notifications Card

**Backend Logic** (in `QuotationController::viewHome()`):
```php
$unreadNotifications = \App\Models\Notification::where('user_id', auth()->id())
    ->where('read', false)
    ->count();
```

**Frontend** (dashboard.blade.php):
```blade
<h3>{{ $unreadNotifications }}</h3>
```

**Click Action**:
```javascript
onclick="document.querySelector('.notification-toggle').click()"
```
→ Opens the bell icon dropdown in navbar

**Updates**: Every page load (real-time via notification auto-refresh in navbar)

---

### Customer Approvals Card

**Backend Logic** (in `QuotationController::viewHome()`):
```php
$customerApprovedQuotations = \App\Models\Quotation::where('status_id', $approvedId)
    ->whereNotNull('approved_by_customer_at')
    ->count();
```

**Frontend** (dashboard.blade.php):
```blade
<h3>{{ $customerApprovedQuotations }}</h3>
```

**Click Action**:
```javascript
onclick="window.location.href='{{ route('quotation.reports') }}'"
```
→ Navigates to detailed quotation reports page

---

## When Timestamps Are Set

### approved_by_customer_at

**Set when**: Customer clicks "Approve" button on public quotation link

**Code location**: `QuotationCommentController::customerApprove()`

**What happens**:
1. Customer approves quotation from public link
2. `approved_by_customer_at` timestamp set to `now()`
3. `customer_approved` flag set to `true`
4. Notification created for admin/staff users
5. Card count updates automatically

**Example**:
```php
$quotation->update([
    'customer_approved' => true,
    'approved_by_customer_at' => now()
]);

NotificationHelper::notify(
    $quotation->employee_id,
    'customer_approval',
    'Customer Approved Quotation',
    "Customer {$name} approved quotation: {$number}",
    'Quotation',
    $quotation->id
);
```

---

## Database Changes

### New Column Added

**Table**: `quotations`

**Column**: `approved_by_customer_at`

**Type**: `timestamp` (nullable)

**Default**: `NULL`

**Set to**: `now()` when customer approves

**Migration**: `2024_12_08_000001_add_approved_by_customer_at_to_quotations.php`

---

## Card Features

### Visual Design

**Unread Messages Card**:
- **Color**: Purple gradient (#667eea to #764ba2)
- **Icon**: fa-bell (bell icon)
- **Style**: Hover effect with shadow and lift
- **Text**: "Unread Messages" + count + "Click to view"

**Customer Approvals Card**:
- **Color**: Pink/Red gradient (#f093fb to #f5576c)
- **Icon**: fa-thumbs-up (thumbs up icon)
- **Style**: Hover effect with shadow and lift
- **Text**: "Customer Approvals" + count + "Approved by customers"

### Interactive Features

Both cards have:
- ✅ Hover effect (shadow increases, card lifts up)
- ✅ Click handlers (navigate or open)
- ✅ Cursor pointer (shows clickable)
- ✅ Smooth transitions
- ✅ Responsive on all devices

---

## Example Workflow

### Scenario: Customer Approves Quotation

```
1. Customer views quotation on public link
   └─ Clicks "Approve" button

2. Backend processes approval:
   └─ Sets approved_by_customer_at = now()
   └─ Sets customer_approved = true
   └─ Creates notification

3. Admin's dashboard refreshes:
   └─ "Customer Approvals" card increments
   └─ "Unread Messages" card shows new notification
   └─ Bell icon shows notification count

4. Admin can:
   └─ Click "Customer Approvals" → See detailed reports
   └─ Click "Unread Messages" → View notification
   └─ See which quotations customers approved
```

---

## Related Features

### Notifications System
- Unread notifications auto-refresh every 30 seconds
- Clicking notification marks it as read
- Notification count updates automatically

### Quotation Reports Page
- See all quotations by status
- View monthly breakdown
- Customer approvals section
- Rejection reasons summary

### Quotation Model
- `customer_approved` boolean field
- `approved_by_customer_at` timestamp field
- Both tracked for reporting

---

## Testing the Cards

### Test 1: Verify Unread Notifications
1. Log in as admin
2. Go to dashboard
3. See "Unread Messages: X"
4. Click the card
5. → Notification dropdown opens

### Test 2: Verify Customer Approvals
1. Log in as admin
2. Go to dashboard
3. See "Customer Approvals: X"
4. Click the card
5. → Navigates to reports page

### Test 3: Trigger a Customer Approval
1. Share public quotation link
2. Open link as customer
3. Click "Approve" button
4. Return to admin dashboard
5. → "Customer Approvals" count increments
6. → "Unread Messages" count increments
7. → New notification in bell icon

---

## Frequently Asked Questions

**Q: Do the cards update in real-time?**
A: Dashboard loads fresh data on every page load. Unread notifications update every 30 seconds via auto-refresh.

**Q: What if a customer approves but then rejects?**
A: The approval timestamp remains. The card counts quotations with `approved_by_customer_at` not null.

**Q: Can I clear unread notifications?**
A: Yes, via the notification dropdown (mark all as read). The card count will update on next page load.

**Q: Where's the customer approval notification stored?**
A: In the `notifications` table with `type = 'customer_approval'`.

**Q: Can I customize the card styling?**
A: Yes, edit the inline styles in `dashboard.blade.php` for the card colors and icons.

---

## Performance

Both cards are optimized:
- ✅ Single query per card (no N+1 problems)
- ✅ Index on [user_id, read] for notifications
- ✅ Index on created_at for date ordering
- ✅ Lightweight JavaScript (no external dependencies)

---

## Summary

The two new dashboard cards provide:

1. **Unread Messages** 📬
   - Quick visibility of pending notifications
   - One-click access to notification dropdown
   - Real-time count updates

2. **Customer Approvals** ✅
   - Track quotations approved by customers
   - Navigate to detailed reports
   - Monitor customer engagement

Both cards are fully integrated with the existing notification system and quotation tracking!
