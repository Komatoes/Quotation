# HOSTINGER DEPLOYMENT CHECKLIST - Google Drive Integration

## Current Status
- ✅ **Localhost**: Google Drive configured and connected
- ❌ **Hostinger (jomsconstruction.com)**: Google Drive showing "Not Connected - Setup needed"

## Root Cause
The `.env` file on Hostinger is **missing the Google Drive credentials** that are present on your local XAMPP installation.

## What Needs to Be Done on Hostinger

### Option 1: Direct SSH Update (Recommended - Fast)

```bash
# SSH into Hostinger
ssh user@jomsconstruction.com

# Navigate to app directory
cd /home/u620524563/domains/jomsconstruction.com/Quotation

# Backup current .env
cp .env .env.backup

# Add Google Drive credentials to .env using echo
echo "" >> .env
echo "# Google Drive Configuration" >> .env
echo "GOOGLE_DRIVE_ENABLED=true" >> .env
echo "GOOGLE_DRIVE_PROJECT_ID=quotation-backups" >> .env
echo 'GOOGLE_DRIVE_PRIVATE_KEY_ID=5ab51bfcd9a37448c1f5acaeca582d195ce80729' >> .env
echo 'GOOGLE_DRIVE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDguCXc/ku6MK3/\nUUxCcmnt6fyk8jukFDfhulc/lHYTantBjG3md+rSmahN/V8QwdkfHDsEtEYa7JdB\nqUgspRSHworxhtJlxmLQM5rzOtKfjquAte8QXZ+kb0oDaf2i7UnNLHW7kURWSJfx\nzmmk5CYRQjCo8t8BOH1LtYH89qP3Z4phcy0gOuyyeBKisL9hYYIV08etL9wYQM9Y\n8DnXxjKlX3zdbNUDysyUAjLH18961AwAtexYFt3gEiEpLwqGj123Lh7AcLqMtOGO\n/bXX0cy+rc1XB/g8Hnq755UnIaB2XP0qkd/J+1dRvreHihk29RDPOmanAuP/gBXM\nfAzumDTVAgMBAAECggEABaZLi664IobY7HJBcDOmUfrZDkTXVt8MHOYyDXynhm/d\n0UePUumjUOtqLb18V2/d53qKAHPGYW3oAirzkBIP1NvvBi2cDKyOX480k9sEhQEf\nTkJN1EPcBUIVlOqIwGWBh7PGX/cLD0f8FM21nl61u7l8yB8pTtXDPKdkMSeM0i7z\n7D/QlyYTqiklZeW15gvpIjH9KOZ1GUCR4DyZ+g6jRBQcECyYgI38qg5M9nnzBTaH\nZB6JsDywCO7Mqnwu8NcZ8nUeuvc81SCDvMldvyxzKM/06oSC6K0sLkvHZPYAh5pT\n346bgOJtdRUITkF/u5CURqfHxX9jsNZwvFj6nzbpUQKBgQD8+mpHgeFuZvyAfRxA\nNAt3XNlexF8wkrgwARFq5ZKZdRPai8ceQXzPGJ7Nq96K1uQYCnDwFCpg7PV5Woyz\nfvp7Q6+MBuXdaKl80IQuHk/Y6OZYrn0ppQO6/gXr57BC2852b1pnJKWAjB80N1qr\ne7PySeYQXOs5pD7SmibTihE6uQKBgQDjZ1HWHsMQuDqo+AcVSI5b5lyKIddXE6ya\n0rUrHQiWYWmtQw/Z2ZzDiDkej1ontGCG7vHVwa9AOWwGX9IeIxZVJusM1cJApS8B\nl1Z6KBSvLLynsQKfWYfX5WWh5ZvRPaS0OrFJKIaP4DGMUxFdm2vCvIAejlIfihRk\nfZbOwRSM/QKBgE/SmAxeIAqc9ll4oJxlj6SnKHNtlPeXpASJobxQGfTP62bPKhIS\n6dXS1/DfpUW7zpcDXGV0h3az7jTPbIwKqfCRB+gwGQHGz4vxo3OF3v37Zwtea2Hj\njozHMIkiPwypnLjkI+tdtdcc34+zU6m+S6ZMyQoqt5IlkCkVuOCQtSUJAoGAfPha\n0QE20glss08bFWc6VObUFFVkskXtpWgGiBr8jSgbm0wvedlNfWdvfIvrT8ahBYZr\nAL3gQbtM0nP2VPmTXFh29CbFCiG9I3K344oDVAGR69YrSsc5EDzRDZfRebwWt7VJ\nmJrc2FSs5iEAeiDfp7VP83rjRtHrQw6Bwj35vUECgYEAlR42+/otq48jD4ylBblj\ncrTL8JGrn75rRNTMjm2iX4Zne+Xrc4WfgGSGewEc6odxjqWIvGykBrnofUdnMR24\ns2Hz2OwK67XoIxfvQKspX70QQP/UxO7q1cZFLLRI+PjP2l+Cu5XEacMD/KWiqv9I\nudQenrfQvVLIRtESdVxEdV0=\n-----END PRIVATE KEY-----\n"' >> .env
echo "GOOGLE_DRIVE_CLIENT_EMAIL=jomsbuilders-backup-agent@quotation-backups.iam.gserviceaccount.com" >> .env
echo "GOOGLE_DRIVE_CLIENT_ID=112439056909561249289" >> .env
echo "GOOGLE_DRIVE_FOLDER_ID=1Gleqcqf-ESe22mHKFwpDbgGcagpjLPpn" >> .env

# Verify the settings were added
tail -20 .env

# Clear config cache
php artisan config:clear

# Verify connection works
php check_google_config.php
```

### Option 2: Manual File Edit (If SSH Doesn't Work)

If you prefer using the Hostinger File Manager:
1. Login to Hostinger → File Manager
2. Navigate to `/home/u620524563/domains/jomsconstruction.com/Quotation`
3. Edit `.env` file
4. Scroll to the bottom
5. Add all the Google Drive credentials from above
6. Save the file

### What Each Credential Does

| Variable | Value | Purpose |
|----------|-------|---------|
| `GOOGLE_DRIVE_ENABLED` | `true` | Enable/disable Google Drive backups |
| `GOOGLE_DRIVE_PROJECT_ID` | `quotation-backups` | Google Cloud project ID |
| `GOOGLE_DRIVE_PRIVATE_KEY_ID` | `5ab51...` | Key ID for JWT signing |
| `GOOGLE_DRIVE_PRIVATE_KEY` | `-----BEGIN...` | Private key for authentication |
| `GOOGLE_DRIVE_CLIENT_EMAIL` | `jomsbuilders-...@quotation-backups.iam.gserviceaccount.com` | Service account email |
| `GOOGLE_DRIVE_CLIENT_ID` | `112439...` | Service account client ID |
| `GOOGLE_DRIVE_FOLDER_ID` | `1Gleqcqf-...` | Google Drive folder ID where backups are stored |

## Verification Steps After Adding Credentials

### Step 1: Clear Cache
```bash
php artisan config:clear
```

### Step 2: Verify Config Loads
```bash
php check_google_config.php
```

Expected output:
```
Connected: ✓ YES
```

### Step 3: Test Backup with Google Drive Upload
```bash
php artisan backup:run
```

### Step 4: Check Logs
```bash
tail -100 storage/logs/laravel.log | grep -i "google\|drive"
```

Expected logs:
```
Google Drive backup upload initiated for: Laravel-2025-11-30-...
JWT token created successfully
Access token obtained successfully
File uploaded successfully to Google Drive. File ID: ...
```

### Step 5: Verify in Dashboard
1. Go to admin dashboard
2. Check Backup Management page
3. Should show: ✅ **Google Drive Status: Connected**

## If Still Not Connected After Adding Credentials

Common issues:

1. **Private Key Has Extra Spaces**
   - The `\n` characters should be literal in the .env file
   - Don't replace them with actual newlines

2. **Folder ID is Wrong**
   - Make sure it's just the ID: `1Gleqcqf-ESe22mHKFwpDbgGcagpjLPpn`
   - Not the full URL

3. **Config Cache Not Cleared**
   - Run: `php artisan config:clear`

4. **File Permissions**
   - Check that `.env` file is readable by PHP process

5. **Google Drive API Not Enabled**
   - Verify in Google Cloud Console that "Google Drive API" is enabled for your project

## Next Actions

1. **SSH into Hostinger** (or use File Manager)
2. **Add the Google Drive credentials to .env**
3. **Run:** `php artisan config:clear`
4. **Verify:** `php check_google_config.php`
5. **Refresh** admin dashboard to see "✅ Connected"
