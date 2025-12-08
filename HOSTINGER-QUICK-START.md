# 🚀 Hostinger Deployment - Quick Start Summary

**TL;DR Version** - Follow this if you're in a hurry!

---

## 5-Minute Overview

### What You Need on Hostinger
- ✅ PHP 8.0.2+ 
- ✅ MySQL 5.7+
- ✅ SSH access enabled
- ✅ Composer installed (usually pre-installed)

### Deploy Steps (In Order)

```bash
# 1. Connect to Hostinger
ssh username@your-domain.com

# 2. Go to public directory
cd public_html

# 3. Upload your code (Git clone recommended)
git clone https://github.com/your-repo/Quotation.git .

# 4. Install dependencies
composer install

# 5. Create .env file
nano .env
# Paste your .env config (see full guide for details)

# 6. Generate key
php artisan key:generate

# 7. Set permissions
chmod -R 775 storage/ bootstrap/cache/ public/

# 8. Create database on Hostinger Control Panel first!
# Then import your database:
mysql -u username -p quotation < backup.sql

# 9. Run migrations
php artisan migrate

# 10. Seed database (creates admin & staff users)
php artisan db:seed

# 11. Clear cache
php artisan cache:clear

# 12. Create first backup
php artisan backup:run

# 13. Set up cron for daily backups
crontab -e
# Add: * * * * * cd /home/username/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1

# Done! ✅
```

---

## Critical Things to Change

| Item | Local Value | Hostinger Value | Where |
|------|-------------|-----------------|-------|
| APP_ENV | local | production | .env |
| APP_DEBUG | true | false | .env |
| APP_URL | http://localhost/ | https://your-domain.com | .env |
| DB_HOST | 127.0.0.1 | localhost (usually) | .env |
| DB_USERNAME | root | from Control Panel | .env |
| DB_PASSWORD | blank | from Control Panel | .env |
| DUMP_COMMAND_PATH | C:\xampp\mysql\bin | /usr/bin | .env |

---

## .env Template for Hostinger

```env
APP_NAME=Quotation
APP_ENV=production
APP_KEY=base64:GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=quotation
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

DUMP_COMMAND_PATH=/usr/bin
MYSQL_DUMP_COMMAND_PATH=/usr/bin

# (rest from full guide)
```

---

## Login Credentials

After deployment, login with:

**Admin Account**
- Username: `jomilo`
- Password: `SecurePass@2025!Qtn`

**Staff Account**
- Username: `redcrislan`
- Password: `SecurePass@2025!Qtn`

⚠️ **Have them change passwords after first login!**

---

## Verify It Works

1. Visit: https://your-domain.com
2. Should see login page (no errors)
3. Log in with jomilo/staff credentials
4. Test: Create quotation, create backup
5. Check: Admin → Backup & Restore shows "Local" backups

---

## Troubleshooting Quick Fixes

| Problem | Fix |
|---------|-----|
| "Permission denied" | `chmod -R 775 storage/ bootstrap/cache/` |
| "Database connection error" | Verify .env has correct DB credentials |
| "PDO not found" | Contact Hostinger - enable PDO extension |
| "Composer not found" | Usually pre-installed, ask Hostinger support |
| "Backup failed" | Check MySQL dump path: should be `/usr/bin` |

---

## Full Guide Location

**For complete details**, see: `HOSTINGER-DEPLOYMENT-COMPLETE.md`

Covers:
- ✅ Step-by-step with screenshots
- ✅ Database import procedures
- ✅ All configuration options
- ✅ Extensive troubleshooting
- ✅ Testing procedures
- ✅ Security setup (SSL, permissions)
- ✅ Backup & restore verification
- ✅ Cron job setup
- ✅ Post-deployment tasks

---

## What Gets Installed

From `composer.json` - automatically installed:

```json
✓ Laravel Framework 9.19+
✓ Laravel Sanctum (API authentication)
✓ Spatie Laravel Backup (backup/restore)
✓ Spatie Laravel Permission (roles & access control)
✓ PHPOffice PHPWord (DOCX export)
✓ guzzlehttp/guzzle (HTTP client, Google Drive)
✓ 30+ other packages
```

All dependencies from `composer.lock` - exact versions guaranteed.

---

## Backup & Restore Features Deployed

✅ **Automatic Daily Backups**
- 02:00 AM: Main backup
- 03:00 AM: Fallback if first failed
- Runs automatically via cron

✅ **3-2-1 Strategy**
- 1 copy: Hostinger local storage
- 2 media: Disk storage
- Ready for Google Drive (see docs)
- Ready for AWS S3 (optional)

✅ **Admin Dashboard**
- View all backups
- Create manual backup
- Download backup to computer
- Delete old backups
- Restore from backup

✅ **Database Safety**
- Pre-restore safety backup
- Automatic rollback on error
- Maintenance mode during restore
- Detailed logging

---

## Security Notes

⚠️ **On Hostinger, ALWAYS:**

1. **Change the temporary passwords** after first login
2. **Enable SSL** (usually automatic via AutoSSL)
3. **Set file permissions** (already included in guide)
4. **Update .env** with production values
5. **Keep .env out of Git** (already in .gitignore)
6. **Monitor error logs** regularly
7. **Test backups** occasionally (restore to test)
8. **Update Laravel** regularly for security patches

---

## Next Steps After Deployment

1. ✅ Log in and test all features
2. ✅ Create test quotation
3. ✅ Create test backup (via Dashboard)
4. ✅ Download backup file
5. ✅ Wait 24 hours for automatic backup
6. ✅ Verify automatic backup created
7. ✅ Optional: Set up Google Drive backups (see docs)
8. ✅ Optional: Set up monitoring/alerts

---

## Files to Keep Safe

Store these locally in a secure location:

- `USER_CREDENTIALS.md` - Login credentials & passwords
- `HOSTINGER-DEPLOYMENT-COMPLETE.md` - Full deployment guide
- `GOOGLE-DRIVE-HOSTINGER-SETUP.md` - Backup setup (if using)
- `.env` file - Database credentials (never share!)
- Database dump - `quotation_backup.sql`

---

## Support Files in Your Repo

| File | Purpose |
|------|---------|
| `HOSTINGER-DEPLOYMENT-COMPLETE.md` | Complete step-by-step guide |
| `USER_CREDENTIALS.md` | Login info, passwords, how to apply |
| `composer.json` | All dependencies required |
| `config/backup.php` | Backup configuration |
| `.env` | Example environment variables |
| `docs/HOSTINGER-GDRIVE-SETUP.md` | Google Drive backup setup |

---

## Estimated Time

- SSH access: 5 min
- Upload files: 10-30 min (depends on file size)
- Composer install: 5-10 min
- Database setup: 5 min
- Configuration: 5 min
- Testing: 10 min

**Total: 40-65 minutes**

---

## Success Indicators ✅

After deployment, you should see:

- [ ] Website loads at your domain
- [ ] Login page appears
- [ ] Can login with jomilo/redcrislan
- [ ] Dashboard shows quotations/projects/materials
- [ ] Can create a quotation
- [ ] Admin panel shows Backup & Restore option
- [ ] Can create a backup from Dashboard
- [ ] Backup appears in list
- [ ] Can download the backup file

If all above: **Deployment Successful! 🎉**

---

## Need More Help?

**See**: `HOSTINGER-DEPLOYMENT-COMPLETE.md` for:
- Detailed step-by-step instructions
- Screenshots & examples
- Troubleshooting for every issue
- Database import procedures
- SSL setup instructions
- Email configuration
- Monitoring setup
- And much more...

---

**Version**: 1.0  
**Created**: November 30, 2025  
**Status**: ✅ Ready to Deploy

