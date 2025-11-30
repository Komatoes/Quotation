# ⚡ 60-Second Quick Start

## GO RIGHT NOW ➡️

### Step 1: Open Dashboard (10 seconds)
```
Browser: http://localhost/admin/backup/
You see: Dashboard with backup table
```

### Step 2: Create Backup (5 minutes)
```
Click: [💾 Create Backup Now]
Wait: Progress modal shows progress
See: Success modal
Result: Backup appears in table ✅
```

### Step 3: Restore Backup (5 minutes)
```
Click: [⟲ Restore] on any backup
Read: Yellow warning modal carefully
Click: [Yes, Restore This Backup]
Wait: Progress modal shows steps
See: Success modal
Result: Database restored ✅
```

---

## That's It! 🎉

You now have:
- ✅ Automatic daily backups (02:00 AM)
- ✅ Backup GUI dashboard
- ✅ One-click restore capability
- ✅ Automatic safety backups
- ✅ Full error recovery

---

## Need Help?

| Question | Answer |
|----------|--------|
| Can't find GUI? | Visit: http://localhost/admin/backup/ |
| How long does backup take? | 2-5 minutes |
| How long does restore take? | 2-15 minutes |
| What if I'm not an admin? | Ask your admin for access |
| Something broke? | Read: docs/BACKUP-TROUBLESHOOTING.md |
| Want full docs? | Read: docs/START-HERE.md |

---

## CLI Alternative

```powershell
cd C:\xampp\htdocs\Quotation

# Backup now
php artisan backup:run

# List backups
php artisan backup:list

# Restore (manual - see full docs)
# ...complicated, use GUI instead
```

---

**Go backup your system now!** 🚀

→ http://localhost/admin/backup/

