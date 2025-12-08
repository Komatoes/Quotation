# Dashboard Notification Cards - Visual Guide

## Dashboard Layout

Your dashboard now has the following structure:

```
┌─────────────────────────────────────────────────────────────────────┐
│                         TOP NAVIGATION BAR                          │
│              🔔 (Bell Icon with Unread Count Badge)  👤             │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                     DASHBOARD - MAIN CONTENT                         │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────────────────────┐  ┌──────────────────────────────┐ │
│  │  📬 UNREAD MESSAGES          │  │  ✅ CUSTOMER APPROVALS       │ │
│  ├──────────────────────────────┤  ├──────────────────────────────┤ │
│  │                              │  │                              │ │
│  │  Purple Gradient Background  │  │  Pink/Red Gradient Background│ │
│  │                              │  │                              │ │
│  │  Count: 5                    │  │  Count: 12                   │ │
│  │  "Click to view"             │  │  "Approved by customers"     │ │
│  │                              │  │                              │ │
│  │  Click → Opens Bell Dropdown │  │  Click → Reports Page        │ │
│  │                              │  │                              │ │
│  └──────────────────────────────┘  └──────────────────────────────┘ │
│                                                                      │
├─────────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────────────┐│
│  │             QUOTATION STATISTICS (Main Card)                    ││
│  ├─────────────────────────────────────────────────────────────────┤│
│  │                                                                  ││
│  │  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐                         ││
│  │  │Draft │  │Approv│  │Reject│  │Ongoig│                         ││
│  │  │  5   │  │  12  │  │  2   │  │  10  │                         ││
│  │  └──────┘  └──────┘  └──────┘  └──────┘                         ││
│  │                                                                  ││
│  └─────────────────────────────────────────────────────────────────┘│
│                                                                      │
├─────────────────────────────────────────────────────────────────────┤
│  Materials Section                                                   │
│  Current Projects Section                                            │
│  Draft Quotations Section                                            │
│  Archived Projects Section                                           │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Card Details

### Card 1: Unread Messages 📬

```
┌─────────────────────────────────────────┐
│                                          │
│  🔔 UNREAD MESSAGES                      │ ← Small label (uppercase)
│  5                                       │ ← Large bold count number
│  Click to view                           │ ← Hint text
│                                  🔔      │ ← Large icon on right
│                                          │
└─────────────────────────────────────────┘
```

**Styling**:
- Background: Purple gradient (#667eea → #764ba2)
- Text color: White
- Icon: fa-bell (bell icon), large and white
- Hover effect: Shadow increases, card lifts up
- Border: None, rounded corners
- Transition: Smooth animations on hover

**Click Behavior**:
```javascript
onclick="document.querySelector('.notification-toggle').click()"
// Opens the bell icon dropdown in navbar
```

---

### Card 2: Customer Approvals ✅

```
┌─────────────────────────────────────────┐
│                                          │
│  ✓ CUSTOMER APPROVALS                    │ ← Small label (uppercase)
│  12                                      │ ← Large bold count number
│  Approved by customers                   │ ← Hint text
│                              👍          │ ← Large icon on right
│                                          │
└─────────────────────────────────────────┘
```

**Styling**:
- Background: Pink/Red gradient (#f093fb → #f5576c)
- Text color: White
- Icon: fa-thumbs-up (thumbs up icon), large and white
- Hover effect: Shadow increases, card lifts up
- Border: None, rounded corners
- Transition: Smooth animations on hover

**Click Behavior**:
```javascript
onclick="window.location.href='{{ route('quotation.reports') }}'"
// Navigates to detailed quotation reports page
```

---

## Interaction Examples

### Example 1: User Has Unread Notifications

```
Dashboard Loads
    ↓
viewHome() counts unread notifications
    ↓
$unreadNotifications = 3
    ↓
Card displays: 3
    ↓
User sees card with "3" in purple
    ↓
User clicks card
    ↓
Bell dropdown opens automatically
    ↓
User sees 3 unread notifications listed
    ↓
User clicks one notification
    ↓
Marked as read, navigates to quotation
    ↓
Dashboard refreshes → Count becomes 2
```

---

### Example 2: Customer Approves Quotation

```
Customer opens public quotation link
    ↓
Customer clicks "Approve" button
    ↓
Backend: customerApprove() processes
    ↓
Updates:
  • customer_approved = true
  • approved_by_customer_at = now()
  • Creates notification
    ↓
Admin dashboard refreshes
    ↓
"Customer Approvals" card increments
    ↓
"Unread Messages" card shows new notification
    ↓
Bell icon shows badge with count
    ↓
Admin can click either card to view
```

---

## Visual States

### Unread Messages Card - States

**State 1: No unread messages**
```
┌─────────────────────────────────────────┐
│  🔔 UNREAD MESSAGES                      │
│  0                                       │
│  Click to view                           │
│                                  🔔      │
└─────────────────────────────────────────┘
```

**State 2: Some unread messages**
```
┌─────────────────────────────────────────┐
│  🔔 UNREAD MESSAGES                      │
│  5                                       │ ← Eye-catching
│  Click to view                           │
│                                  🔔      │
└─────────────────────────────────────────┘
```

**State 3: Many unread messages**
```
┌─────────────────────────────────────────┐
│  🔔 UNREAD MESSAGES                      │
│  25                                      │ ← Urgent
│  Click to view                           │
│                                  🔔      │
└─────────────────────────────────────────┘
```

---

### Customer Approvals Card - States

**State 1: No approvals**
```
┌─────────────────────────────────────────┐
│  👍 CUSTOMER APPROVALS                   │
│  0                                       │
│  Approved by customers                   │
│                              👍          │
└─────────────────────────────────────────┘
```

**State 2: Some approvals**
```
┌─────────────────────────────────────────┐
│  👍 CUSTOMER APPROVALS                   │
│  12                                      │ ← Good engagement
│  Approved by customers                   │
│                              👍          │
└─────────────────────────────────────────┘
```

**State 3: Many approvals**
```
┌─────────────────────────────────────────┐
│  👍 CUSTOMER APPROVALS                   │
│  45                                      │ ← Great engagement
│  Approved by customers                   │
│                              👍          │
└─────────────────────────────────────────┘
```

---

## Hover Effects

### Before Hover (Default State)
```
┌─────────────────────────────────────────┐
│  Box Shadow: Small (0.125rem 0.25rem)   │
│  Transform: None                         │
│  Cursor: Pointer                         │
└─────────────────────────────────────────┘
```

### After Hover (Mouse Over)
```
┌─────────────────────────────────────────┐
│  Box Shadow: Large (1rem 3rem)          │ ← Stronger shadow
│  Transform: translateY(-2px)            │ ← Lifts up 2px
│  Cursor: Pointer                         │
└─────────────────────────────────────────┘
```

---

## Responsive Design

### Desktop (1200px+)
```
┌─────────────────────────────────────────────────────────────┐
│  ┌─────────────────┐         ┌─────────────────┐           │
│  │  Col-lg-6       │         │  Col-lg-6       │           │
│  │ Card 1          │         │ Card 2          │           │
│  └─────────────────┘         └─────────────────┘           │
└─────────────────────────────────────────────────────────────┘
```

### Tablet (768px - 1199px)
```
┌─────────────────────────────────────────────────────────────┐
│  ┌─────────────────┐         ┌─────────────────┐           │
│  │  Col-md-6       │         │  Col-md-6       │           │
│  │ Card 1          │         │ Card 2          │           │
│  └─────────────────┘         └─────────────────┘           │
└─────────────────────────────────────────────────────────────┘
```

### Mobile (< 768px)
```
┌──────────────────────────────┐
│  ┌──────────────────────────┐│
│  │  Col-md-6 (Full Width)   ││
│  │ Card 1                   ││
│  └──────────────────────────┘│
│  ┌──────────────────────────┐│
│  │  Col-md-6 (Full Width)   ││
│  │ Card 2                   ││
│  └──────────────────────────┘│
└──────────────────────────────┘
```

---

## Data Flow

### Unread Notifications

```
User Action: Creates notification
    ↓
Database: notifications table
    ↓
Query: count where user_id=X AND read=FALSE
    ↓
Controller: viewHome() method
    ↓
Variable: $unreadNotifications = 5
    ↓
View: dashboard.blade.php
    ↓
Blade: {{ $unreadNotifications }}
    ↓
Display: Card shows "5"
```

---

### Customer Approvals

```
User Action: Customer approves quotation
    ↓
Database: quotations table
    ↓
Update:
  • approved_by_customer_at = now()
  • customer_approved = true
    ↓
Query: count where status=2 AND approved_by_customer_at IS NOT NULL
    ↓
Controller: viewHome() method
    ↓
Variable: $customerApprovedQuotations = 12
    ↓
View: dashboard.blade.php
    ↓
Blade: {{ $customerApprovedQuotations }}
    ↓
Display: Card shows "12"
```

---

## Color Scheme

### Card 1 - Unread Messages
- **Primary Color**: #667eea (Blue-purple)
- **Secondary Color**: #764ba2 (Deep purple)
- **Gradient**: Linear 135deg from #667eea to #764ba2
- **Text Color**: White (rgba(255,255,255,1))
- **Hint Text Color**: rgba(255,255,255,0.7)

### Card 2 - Customer Approvals
- **Primary Color**: #f093fb (Light pink)
- **Secondary Color**: #f5576c (Red)
- **Gradient**: Linear 135deg from #f093fb to #f5576c
- **Text Color**: White (rgba(255,255,255,1))
- **Hint Text Color**: rgba(255,255,255,0.7)

---

## Summary

The dashboard now provides:

✅ **Quick visibility** of unread notifications
✅ **Quick visibility** of customer approvals
✅ **One-click access** to notifications or reports
✅ **Professional gradient styling** with hover effects
✅ **Responsive design** for all devices
✅ **Real-time updates** as data changes
✅ **Clear visual hierarchy** with icons and colors

Both cards are fully integrated with your notification system and quotation tracking!
