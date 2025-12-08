# 🌱 SEEDING ADMIN ACCOUNTS ON HOSTINGER

After you run `php artisan migrate:fresh`, you need to seed the admin accounts back in.

## Quick Steps on Hostinger:

```bash
cd /home/u620524563/public_html/Quotation

# 1. Pull latest (includes AdminSeeder)
git pull origin AFTERTHESISREVS

# 2. Run migrations fresh (creates all tables)
php artisan migrate:fresh

# 3. SEED THE DATA (creates all admin accounts)
php artisan db:seed

# OR seed only admins:
# php artisan db:seed --class=AdminSeeder
```

## Complete Migration + Seed Command:

```bash
php artisan migrate:fresh --seed
```

This will:
1. ✅ Drop all tables
2. ✅ Run all migrations
3. ✅ Run all seeders (RolesAndPermissionsSeeder → UsersSeeder → AdminSeeder)

## Admin Accounts Created:

```
Username: ADMIN          | Email: laronvogn@gmail.com              | Password: ADMIN123
Username: nemo           | Email: blankgajes@gmail.com             | Password: Admin@123456
Username: ange           | Email: angelikamaslang@gmail.com        | Password: Admin@123456
Username: mark           | Email: markandrebayo234@gmail.com       | Password: Admin@123456
Username: jomilo         | Email: jomilo.lano@quotation.app        | Password: Jomilo@123456
```

## Verify Success:

```bash
# Check all admins were created
php artisan tinker
>>> User::where('role', 'admin')->get();

# Should show 5 admin users

# Check admin roles (Spatie)
>>> php artisan roles:show

# Should show all admins with 24 permissions each

# Exit
>>> exit()
```

## What's New:

Created: `database/seeders/AdminSeeder.php`
- Seeds all 5 admin accounts
- Verifies emails (email_verified_at = now)
- Assigns Spatie admin role automatically
- Uses `firstOrCreate()` so running seed multiple times is safe

Updated: `database/seeders/DatabaseSeeder.php`
- Now calls AdminSeeder after RolesAndPermissionsSeeder and UsersSeeder
- Ensures roles exist before assigning them to admins

## Testing Locally:

```bash
php artisan migrate:fresh --seed
php artisan roles:show
```

You should see all 5 admins with proper roles and permissions!
