# 📦 Installation & Dependencies Reference

**Complete list of what gets installed via Composer on Hostinger**

---

## System Requirements

### Required on Hostinger

```
PHP Version:        8.0.2 or higher (8.1+ recommended)
MySQL:              5.7 or higher
Web Server:         Apache with mod_rewrite enabled
Disk Space:         500MB+ (for code + backups)
RAM:                256MB minimum (512MB+ recommended)
Extensions:         PDO, JSON, cURL, ZIP, GD, Fileinfo
Composer:           2.0+ (usually pre-installed)
```

### Verify on Hostinger (via SSH)

```bash
# PHP version
php -v

# Check extensions
php -m | grep -E "json|pdo|curl|zip|gd|fileinfo"

# MySQL
mysql --version

# Composer
composer --version

# All should show version numbers (no errors)
```

---

## Dependencies from composer.json

### Production Dependencies

These are REQUIRED for the system to work:

#### Framework & Core
```
laravel/framework:       ^9.19   (Core Laravel)
laravel/sanctum:         ^3.0    (API authentication)
laravel/tinker:          ^2.7    (REPL - optional)
```

#### Backup System
```
spatie/laravel-backup:   *       (Backup/restore feature)
                                 - Handles zip compression
                                 - MySQL dumps
                                 - Automatic scheduling
                                 - Storage management
```

#### Access Control
```
spatie/laravel-permission: ^6.23 (Roles & permissions)
                                 - Admin role
                                 - Staff role
                                 - Permission management
                                 - Role-based access control
```

#### Export Features
```
phpoffice/phpword:       ^1.3    (DOCX export)
                                 - Word document generation
                                 - Quotation exports
                                 - Formatting support
```

#### HTTP Client
```
guzzlehttp/guzzle:       ^7.2    (HTTP requests)
                                 - Google Drive API calls
                                 - External API integration
                                 - Backup uploads
```

### Development Dependencies (Not needed on Hostinger)

```
fakerphp/faker:          ^1.9.1  (Test data generation)
laravel/pint:            ^1.0    (Code formatting)
laravel/sail:            ^1.0.1  (Docker setup)
mockery/mockery:         ^1.4.4  (Mocking for tests)
nunomaduro/collision:    ^6.1    (Error display)
phpunit/phpunit:         ^9.5.10 (Testing framework)
spatie/laravel-ignition: ^1.0    (Error debugging)
```

**Note:** Development dependencies are NOT installed on Hostinger with `composer install --no-dev`

---

## Package Installation Details

### What Happens When You Run `composer install`

```bash
composer install
```

**Output Timeline:**
1. ✅ Reads `composer.json` (all dependencies)
2. ✅ Reads `composer.lock` (exact versions)
3. ✅ Downloads all packages from Packagist
4. ✅ Runs post-install scripts
5. ✅ Generates autoloader

**Result:**
- ~39 packages installed
- ~50,000+ PHP files created
- `vendor/` directory created (~100MB)
- Time: 5-10 minutes

### Package Directory Structure

```
vendor/
├── laravel/
│   ├── framework/              (Core Laravel)
│   ├── sanctum/                (Authentication)
│   └── tinker/                 (REPL)
├── spatie/
│   ├── laravel-backup/         (Backup system)
│   ├── laravel-permission/     (Roles & access)
│   ├── backup-server/          (Backup tools)
│   └── ...
├── phpoffice/
│   └── phpword/                (Word document generation)
├── guzzlehttp/
│   ├── guzzle/                 (HTTP client)
│   ├── psr7/                   (HTTP standards)
│   └── promises/               (Async handling)
├── symfony/
│   ├── console/                (CLI commands)
│   ├── http-foundation/        (HTTP handling)
│   └── ... (20+ packages)
├── psr/
│   └── (PSR standards packages)
└── ... (30+ more packages)
```

---

## Composer Lock File

### What is composer.lock?

- **Exact version lock** for reproducible installations
- **Location**: Root directory: `composer.lock`
- **Size**: ~50KB
- **Purpose**: Ensures same versions everywhere
- **Never edit manually** - let Composer manage it

### Why It's Important

```
Local Machine (XAMPP):
composer install → Uses composer.lock → Version X.Y.Z

Hostinger Server:
composer install → Uses SAME composer.lock → Version X.Y.Z

Same Versions Everywhere = No Conflicts! ✅
```

### How to Update Packages

```bash
# Update packages (respects composer.lock)
composer install

# Force update (creates new lock versions)
composer update

# Update specific package
composer update laravel/framework

# Note: Usually just use 'composer install'
```

---

## Installation Size Reference

### On Hostinger After composer install

```
vendor/                    ~100 MB  (All packages)
bootstrap/cache/           ~1 MB    (Generated cache)
storage/logs/             ~5 MB    (Log files)
storage/app/Laravel/      ~50-200 MB (Backups - grows over time)
public/                   ~20 MB   (Assets & uploads)
config/                   ~1 MB    (Configuration)
app/                      ~5 MB    (Application code)
resources/                ~10 MB   (Views & assets)
routes/                   ~50 KB   (Route definitions)
database/                 ~5 MB    (Migrations & seeders)

TOTAL: ~250-400 MB (without backups)
```

### Storage Growth

```
After 1 week:    +100 MB (7 daily backups)
After 1 month:   +400 MB (30 daily backups)
After 1 year:    +4.8 GB (365 daily backups)

PRO TIP: Set backup cleanup policy to keep only 30 days
See: config/backup.php for cleanup strategies
```

---

## Hostinger Compatibility Check

### PHP Extensions Required

```bash
# Check on Hostinger
php -m

# Must include:
✓ PDO               (Database access)
✓ pdo_mysql         (MySQL driver)
✓ json              (JSON encoding)
✓ cURL              (HTTP requests)
✓ zip               (Backup compression)
✓ GD                (Image processing)
✓ fileinfo          (File type detection)

# Optional but recommended:
✓ opcache           (Performance)
✓ redis             (Caching - if available)
```

### Hostinger PHP Configuration

**What Hostinger Usually Provides:**
- ✅ PHP 8.1+ available
- ✅ All required extensions enabled
- ✅ Composer pre-installed
- ✅ SSH access available
- ✅ MySQL 5.7+ available
- ✅ Apache with mod_rewrite
- ✅ 300+ second max execution time
- ✅ 256MB+ memory limit

**What to Verify:**
```bash
# Check memory limit
php -i | grep "memory_limit"
# Should show: 256M or higher

# Check max execution time
php -i | grep "max_execution_time"
# Should show: 300 or higher

# Check MySQL
mysql --version
# Should show: 5.7+
```

### What Usually Causes Issues

```
❌ PHP < 8.0.2          → Laravel 9+ won't work
❌ Missing PDO           → Database won't connect
❌ Missing JSON ext     → JSON errors
❌ Missing ZIP ext      → Backup creation fails
❌ Disabled cURL        → Google Drive won't work
❌ Memory < 128M        → Composer fails
❌ No mod_rewrite       → URL rewriting breaks
```

**Contact Hostinger support if any of above missing!**

---

## Installation on Hostinger Step-by-Step

### Step 1: SSH into Hostinger

```bash
ssh username@your-domain.com
cd public_html
```

### Step 2: Verify PHP & Composer

```bash
php -v          # Should show 8.0.2+
composer -v     # Should show 2.0+
```

### Step 3: Upload composer.json & composer.lock

If using Git:
```bash
git clone https://github.com/your-repo/Quotation.git .
cd your-project-root
```

If using SFTP:
- Upload `composer.json`
- Upload `composer.lock`
- Upload all other files

### Step 4: Install All Dependencies

```bash
# Install all packages from composer.lock
composer install

# Expected output:
# Installing dependencies from lock file
# Verifying lock file contents can be installed on this platform
# Installing 39 packages
# ... (listing all packages)
# Dependency successfully installed!
```

### Step 5: Verify Installation

```bash
# Check vendor directory exists
ls vendor/ | head -20

# Should show many directories: laravel/, spatie/, symfony/, etc.

# Check key files exist
ls app/Models/User.php
ls config/backup.php
ls routes/web.php

# All should exist
```

### Step 6: Generate Application Key

```bash
# This creates APP_KEY in .env
php artisan key:generate

# Output:
# Application key [base64:***...***] set successfully.
```

---

## Troubleshooting Installation

### Problem: "Composer not found"

```bash
# Composer usually pre-installed on Hostinger
# If not, contact support or try:

which composer
# If returns nothing, install:

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
```

### Problem: "Memory exhausted" during composer install

```bash
# Increase memory limit
COMPOSER_MEMORY_LIMIT=-1 composer install

# Or edit php.ini (if accessible)
# memory_limit = 512M
```

### Problem: "Could not authenticate with username/password"

```bash
# If accessing private repos, use GitHub token:
composer config --auth github-oauth.github.com YOUR_GITHUB_TOKEN

# Then retry:
composer install
```

### Problem: "Package not found"

```bash
# Clear cache and retry
composer cache:clear
composer install

# If still fails, verify composer.lock is correct:
cat composer.lock | grep -A5 "laravel/framework"
```

### Problem: "PHP extension required: ext-json"

```bash
# JSON extension missing
# Contact Hostinger support to enable:
# - json extension
# - Must be enabled in php.ini

# Verify:
php -m | grep json
# Should show: json
```

---

## Production Installation Best Practices

### On Hostinger, Always:

```bash
# 1. Use --no-dev (don't install dev dependencies)
composer install --no-dev

# 2. Optimize autoloader
composer install --optimize-autoloader

# 3. Clear cache after install
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 4. Set permissions
chmod -R 755 vendor/
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### Recommended: Production Install

```bash
# All best practices in one command
composer install \
  --no-dev \
  --optimize-autoloader \
  --prefer-dist
```

### After Installation: Verify

```bash
# Check all critical packages installed
php artisan tinker

# In tinker:
> \App\Models\User::count()
# Should show: 2 (admin & staff)

> \Spatie\Permission\Models\Role::pluck('name')
# Should show: admin, staff

> config('backup.backup.name')
# Should show: quotation

> exit
```

---

## Specific Packages Used

### Laravel Backup (spatie/laravel-backup)

**What it does:**
- Creates compressed ZIP backups
- Includes database via mysqldump
- Excludes vendor & node_modules
- Scheduled automatic backups
- Cleanup of old backups
- Backup notifications

**Config:** `config/backup.php`

```php
// Backup runs daily at 02:00 AM
'backup' => [
    'name' => 'quotation',
    'source' => ['files' => ['include' => [base_path()]]],
    'databases' => ['mysql' => ['quotation']],
    'compression' => 'gzip',
]
```

### Laravel Permission (spatie/laravel-permission)

**What it does:**
- Role-based access control
- Define admin & staff roles
- Assign permissions to roles
- Check user permissions
- Gate-based authorization

**Usage:**
```php
auth()->user()->hasRole('admin')
auth()->user()->hasPermission('create.quotation')
```

### PHPOffice (phpoffice/phpword)

**What it does:**
- Generate Word (.docx) documents
- Format text, tables, sections
- Export quotations as DOCX
- Create reports

**Usage:**
```php
$phpWord = new PhpWord();
$section = $phpWord->addSection();
$section->addText('Hello World!');
```

### Guzzle (guzzlehttp/guzzle)

**What it does:**
- Make HTTP requests
- Connect to external APIs
- Upload to Google Drive
- Handle authentication tokens

**Usage:**
```php
$client = new Client();
$response = $client->get('https://api.example.com/data');
```

---

## Package Security

### Security Updates

```bash
# Check for outdated packages
composer outdated

# Update only patch versions (safe)
composer update

# Update minor versions (usually safe)
composer update --minor-only

# Check for security vulnerabilities
composer audit
```

### On Hostinger: Security Practices

```
✅ Keep vendor/ in project (needed for production)
✅ Update packages monthly
✅ Monitor security advisories
✅ Never commit .env to Git
✅ Use strong database passwords
✅ Enable HTTPS/SSL
✅ Regular backups
✅ Monitor error logs
```

---

## Performance Optimization

### After Installation

```bash
# 1. Optimize autoloader (production only)
composer install --optimize-autoloader --no-dev

# 2. Enable opcache (PHP performance)
# Contact Hostinger to enable

# 3. Configure Laravel cache
# See: config/cache.php

# 4. Set up Redis (if available)
# Optional, but faster than file cache
```

### Resulting Optimization

```
Without optimization:  ~500ms per request
With optimization:     ~100ms per request
  - Faster loading
  - Lower server load
  - Better user experience
```

---

## Reference Files

| File | Purpose |
|------|---------|
| `composer.json` | Defines all dependencies |
| `composer.lock` | Locks exact versions |
| `config/backup.php` | Backup configuration |
| `config/permission.php` | Permission configuration |
| `.env` | Environment variables |
| `artisan` | Command-line interface |

---

## Quick Reference Commands

```bash
# Installation
composer install
composer install --no-dev

# Verification
php -v
composer -v
php -m | grep json
mysql --version

# Application
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan backup:run

# Cache
php artisan cache:clear
php artisan config:clear

# Permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

---

## Need Help?

**Hostinger-specific:**
- Check Hostinger knowledge base
- Contact Hostinger support
- Verify PHP extensions in Control Panel

**Laravel/Spatie-specific:**
- Laravel Docs: https://laravel.com/docs/9.x
- Spatie Backup: https://spatie.be/docs/laravel-backup
- Spatie Permission: https://spatie.be/docs/laravel-permission

**This System:**
- `HOSTINGER-DEPLOYMENT-COMPLETE.md` - Full deployment guide
- `HOSTINGER-QUICK-START.md` - Quick reference
- `USER_CREDENTIALS.md` - Login info

---

**Version**: 1.0  
**Created**: November 30, 2025  
**Status**: ✅ Reference Complete

