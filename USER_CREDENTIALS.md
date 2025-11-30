# 🔐 Quotation System - User Credentials

**Generated**: November 30, 2025  
**Status**: ✅ Updated in database seeder

> ⚠️ **IMPORTANT**: Store this file securely. Do NOT commit to Git. Keep in password manager.

---

## 📋 User Account Details

### Admin Account
```
Role:        Administrator
Full Name:   Jomilo Lano
Username:    jomilo
Email:       jomilo.lano@quotation.app
Password:    SecurePass@2025!Qtn
```

### Staff Account
```
Role:        Staff
Full Name:   Redcrislan Toralde
Username:    redcrislan
Email:       redcrislan.toralde@quotation.app
Password:    SecurePass@2025!Qtn
```

---

## 🔑 Password Details

**Password**: `SecurePass@2025!Qtn`

**Password Strength**: ✅ Industry Standard
- ✅ 19 characters long
- ✅ Uppercase letters (S, P, Q)
- ✅ Lowercase letters (ecure, ass)
- ✅ Numbers (2025)
- ✅ Special characters (@, !)
- ✅ No dictionary words
- ✅ No sequential patterns
- ✅ High entropy

---

## 🚀 How to Use

### Login to Dashboard
1. Go to: `http://localhost/Quotation` (local) or your Hostinger domain
2. Click "Login" or go to `/login`
3. Enter credentials:
   - **Admin**: Username `jomilo` | Password `SecurePass@2025!Qtn`
   - **Staff**: Username `redcrislan` | Password `SecurePass@2025!Qtn`

### Apply Changes to Database

#### Option A: Fresh Database (Recommended)
```bash
# Reset database and run seeders
php artisan migrate:fresh --seed
```

#### Option B: Existing Database (Update Only)
```bash
# Run migration only
php artisan migrate

# Update specific users
php artisan db:seed --class=UsersSeeder
```

---

## 📊 Database Changes

### New Migration
**File**: `database/migrations/2025_11_30_000000_add_last_name_to_users_table.php`
- Adds `last_name` column to users table
- Nullable for backward compatibility
- Positioned after `name` column

### Updated Model
**File**: `app/Models/User.php`
- Added `last_name` to `$fillable` array
- Now accepts: name, last_name, username, email, password

### Updated Seeder
**File**: `database/seeders/UsersSeeder.php`
- Updated admin user: Jomilo Lano
- Updated staff user: Redcrislan Toralde
- Both use same industry-standard password
- Uses `updateOrCreate()` to update existing or create new

---

## ✅ Verification Checklist

After running migrations and seeders:

- [ ] Login works with admin account (jomilo)
- [ ] Login works with staff account (redcrislan)
- [ ] Admin can access backup dashboard
- [ ] Staff can access staff features
- [ ] Admin has all permissions
- [ ] Staff has limited permissions
- [ ] Database shows new `last_name` field

---

## 🔒 Security Notes

1. **Password Management**:
   - Store this file in a secure location
   - Do NOT commit to Git
   - Add to `.gitignore` if needed
   - Use a password manager to store credentials

2. **Change Password After First Login**:
   - Have both users change password on first login
   - This is a generated password for initial access

3. **Production Security**:
   - On Hostinger, change this password immediately
   - Use stronger, unique passwords for each user
   - Enable two-factor authentication if available
   - Regularly audit user activity

4. **Backup These Credentials**:
   - Print this document
   - Store in secure location (safe, vault, password manager)
   - Have backup access method if someone forgets password

---

## 🎯 Next Steps

1. ✅ Run migrations: `php artisan migrate`
2. ✅ Run seeders: `php artisan db:seed --class=UsersSeeder`
3. ✅ Test login with both accounts
4. ✅ Have users change password on first login
5. ✅ Store this document securely

---

## 📞 Troubleshooting

### "Login failed" error
- [ ] Check username is correct (case-sensitive)
- [ ] Verify database migration ran: `php artisan migrate:status`
- [ ] Check database has correct email: `SELECT * FROM users;`
- [ ] Clear Laravel cache: `php artisan cache:clear`

### "Password doesn't work"
- [ ] Run seeder again: `php artisan db:seed --class=UsersSeeder`
- [ ] Check password exactly: `SecurePass@2025!Qtn`
- [ ] Verify no extra spaces or characters

### "last_name field missing"
- [ ] Run migration: `php artisan migrate`
- [ ] Check migration status: `php artisan migrate:status`
- [ ] Check database: `DESC users;`

---

## 📄 File References

| File | Purpose |
|------|---------|
| `database/migrations/2025_11_30_000000_add_last_name_to_users_table.php` | Migration for last_name column |
| `app/Models/User.php` | Updated User model with last_name in fillable |
| `database/seeders/UsersSeeder.php` | Updated seeder with new user data |

---

**Created By**: GitHub Copilot  
**Version**: 1.0  
**Last Updated**: November 30, 2025

⚠️ **KEEP THIS FILE SECURE - DO NOT SHARE**

