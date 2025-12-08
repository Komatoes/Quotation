# Running Migrations on Hostinger

## Step 1: Push Changes to Hostinger
First, push the migration fixes to your repository:

```bash
cd c:\xampp\htdocs\Quotation
git push origin AFTERTHESISREVS
```

## Step 2: SSH into Hostinger

```bash
ssh u620524563@id-dci-web1986.nxcli.io
# Or use your Hostinger SSH connection details
```

## Step 3: Navigate to Project

```bash
cd /home/u620524563/public_html/Quotation
# Or wherever your project is hosted
```

## Step 4: Pull Latest Changes

```bash
git pull origin AFTERTHESISREVS
```

## Step 5: Run Migrations

### Option A: Fresh Migration (⚠️ Deletes all data - for initial setup)
```bash
php artisan migrate:fresh
```

### Option B: Regular Migration (Recommended)
```bash
php artisan migrate --force
```

The `--force` flag is required because you're in production environment.

## Step 6: Verify Success

```bash
# Check migration status
php artisan migrate:status

# Should show all migrations as [Batch X] Ran
```

## Troubleshooting

### If migrations still fail:
```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit()

# Verify database exists
php artisan db:show

# Check current migrations in database
php artisan migrate:status
```

### If you need to rollback:
```bash
# Rollback last batch
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:rollback --step=999
```

### Manual cleanup (if needed):
```bash
# SSH into Hostinger and run MySQL
mysql -u u620524563_Joms_DB25 -p u620524563_Joms_DB25

# Check migrations table
SELECT * FROM migrations;

# If needed, clear all and start fresh
DELETE FROM migrations;
```

Then run: `php artisan migrate:fresh`

## After Migration Success

1. ✅ Verify all tables exist
2. ✅ Create test admin account if needed: `php artisan admin:create`
3. ✅ Verify permissions: `php artisan roles:show`
4. ✅ Check logs: `tail -f storage/logs/laravel.log`

## Important Notes

⚠️ **DO NOT use `migrate:fresh` on production with existing data!**
- It will delete all tables and data
- Only use for initial setup or when you want to reset everything

✅ Use `php artisan migrate --force` for regular production migrations

## Contact Support
If you encounter issues:
1. Check storage/logs/laravel.log
2. Verify database credentials in .env
3. Ensure all dependencies are installed: `composer install`
