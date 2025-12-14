# Rush Project Approval - Complete UI Flow

## Standard Project Approval

```
APPROVE QUOTATION MODAL
═══════════════════════════════════════════════════════════

📝 Contract Subject:
   [Quotation Subject Auto-filled]      ✅ REQUIRED
   (Can edit if needed)

⚠️ ☐ This is a rush project             Unchecked by default

📅 Project Start Date:
   [Dec 15, 2025] or earlier            ✅ REQUIRED (if not rush)

📅 Project End Date:
   [Dec 25, 2025]                       ✅ REQUIRED (if not rush)

☑ I confirm this quotation is backed
  by a valid contract                   ✅ REQUIRED (if not rush)

[Cancel] [✓ Approve]
```

---

## Rush Project Approval (Minimal!)

```
APPROVE QUOTATION MODAL
═══════════════════════════════════════════════════════════

⚠️ ☑ This is a rush project    ← Check this
     (Skip all contract details)

(Everything else hidden!)

[Cancel] [✓ Approve]  ← Just click approve!
```

---

## Before vs After Comparison

### BEFORE (OLD BEHAVIOR)
```
Standard:  Contract Subject + Dates + Confirmation ✅
Rush:      Contract Subject + Dates + Confirmation ❌ Wrong!
```

### AFTER (NEW BEHAVIOR)
```
Standard:  Contract Subject + Dates + Confirmation ✅
Rush:      (Nothing - all hidden!)                ✅ Perfect!
```

---

## Approval Checklist

### For Standard Projects:
```
✓ Fill Contract Subject
✓ Fill Project Start Date (today or earlier - backtrack allowed!)
✓ Fill Project End Date
✓ Check "I confirm with contract"
→ APPROVED
```

### For Rush Projects:
```
✓ Check "This is a rush project"
(All fields disappear)
→ APPROVED  ← One checkbox is all you need!
```

---

## What Hidden Means

When you check "Rush Project":
```
Before:
┌────────────────────────────┐
│ Contract Subject: [filled] │
│ ☑ This is a rush project   │
│ Start Date: [filled]       │
│ End Date: [filled]         │
│ ☑ With Contract            │
└────────────────────────────┘

After:
┌────────────────────────────┐
│ ☑ This is a rush project   │
│                            │
│ (All other fields hidden)  │
└────────────────────────────┘
```

---

## Data Sent to Server

### Standard Project Data:
```json
{
    "status_id": 2,
    "contract_subject": "Project X Phase 1",
    "project_start_date": "2025-12-10",
    "project_end_date": "2025-12-25",
    "is_rush_project": 0,
    "with_contract": 1
}
```

### Rush Project Data:
```json
{
    "status_id": 2,
    "contract_subject": null,      ← Not provided
    "project_start_date": null,    ← Not provided
    "project_end_date": null,      ← Not provided
    "is_rush_project": 1,          ← This is the key
    "with_contract": 1             ← Auto-set
}
```

---

## Field Requirements Summary

| Field | Standard | Rush |
|-------|----------|------|
| Contract Subject | ✅ Show & Require | ❌ Hide |
| Dates | ✅ Show & Require | ❌ Hide |
| With Contract | ✅ Show & Require | ❌ Hide (auto-checked) |
| Approval | ✅ Needs all fields | ✅ One checkbox = done |

---

## The Logic (Updated)

```
User clicks "Rush Project" checkbox

JavaScript:
  ├─ Hide: Contract Subject field
  ├─ Hide: Date fields
  ├─ Hide: Contract confirmation
  ├─ Set: with_contract = true (auto)
  └─ Validation: Skip all field checks

User clicks "Approve":
  ├─ Client validation: Only check rush flag
  ├─ Send to server: All nulls for contract data
  └─ Server: Store is_rush_project=true

Result: ✅ Approved as rush project!
```

---

## Perfect! No More Irony 🎉

```
❌ OLD: Rush project with contract subject requirement = ironic
✅ NEW: Rush project with just one checkbox = makes sense!
```

You can now approve rush projects with literally one checkbox click. Everything else is hidden and auto-handled. Perfect for fast-moving projects!
