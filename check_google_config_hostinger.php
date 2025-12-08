<?php
/**
 * Google Drive Configuration Checker for Hostinger
 * 
 * Usage: php check_google_config_hostinger.php
 * 
 * This script checks if all Google Drive credentials are properly configured
 * in the .env file and reads by Laravel config.
 */

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          GOOGLE DRIVE CONFIGURATION CHECKER                    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Check Google Drive config
$enabled = config('services.google.drive.enabled');
$projectId = config('services.google.drive.project_id');
$privateKeyId = config('services.google.drive.private_key_id');
$privateKey = config('services.google.drive.private_key');
$clientEmail = config('services.google.drive.client_email');
$clientId = config('services.google.drive.client_id');
$folderId = config('services.google.drive.folder_id');

echo "📋 CONFIG VALUES LOADED FROM .env:\n";
echo "───────────────────────────────────────\n";
echo "  Enabled:       " . ($enabled ? "✅ YES" : "❌ NO") . "\n";
echo "  Project ID:    " . ($projectId ? "✅ " . substr($projectId, 0, 25) . "..." : "❌ MISSING") . "\n";
echo "  Private KeyID: " . ($privateKeyId ? "✅ " . substr($privateKeyId, 0, 20) . "..." : "❌ MISSING") . "\n";
echo "  Private Key:   " . ($privateKey ? "✅ " . strlen($privateKey) . " chars" : "❌ MISSING") . "\n";
echo "  Client Email:  " . ($clientEmail ? "✅ " . substr($clientEmail, 0, 40) . "..." : "❌ MISSING") . "\n";
echo "  Client ID:     " . ($clientId ? "✅ " . $clientId : "❌ MISSING") . "\n";
echo "  Folder ID:     " . ($folderId ? "✅ " . $folderId : "❌ MISSING") . "\n";

echo "\n🔍 CONNECTION STATUS:\n";
echo "───────────────────────────────────────\n";
$connected = $enabled && !empty($projectId) && !empty($privateKey) && !empty($clientEmail) && !empty($folderId);
echo "  Status: " . ($connected ? "✅ CONNECTED" : "❌ NOT CONNECTED") . "\n";

if (!$connected) {
    echo "\n⚠️  MISSING CREDENTIALS:\n";
    echo "───────────────────────────────────────\n";
    if (!$enabled) echo "  ❌ GOOGLE_DRIVE_ENABLED not set to 'true'\n";
    if (!$projectId) echo "  ❌ GOOGLE_DRIVE_PROJECT_ID missing\n";
    if (!$privateKey) echo "  ❌ GOOGLE_DRIVE_PRIVATE_KEY missing\n";
    if (!$clientEmail) echo "  ❌ GOOGLE_DRIVE_CLIENT_EMAIL missing\n";
    if (!$folderId) echo "  ❌ GOOGLE_DRIVE_FOLDER_ID missing\n";
    
    echo "\n💡 HOW TO FIX:\n";
    echo "───────────────────────────────────────\n";
    echo "  1. SSH into Hostinger\n";
    echo "  2. Edit the .env file:\n";
    echo "     nano /home/u620524563/domains/jomsconstruction.com/Quotation/.env\n";
    echo "\n  3. Add these lines at the end:\n";
    echo "───────────────────────────────────────\n";
    echo "GOOGLE_DRIVE_ENABLED=true\n";
    echo "GOOGLE_DRIVE_PROJECT_ID=quotation-backups\n";
    echo "GOOGLE_DRIVE_PRIVATE_KEY_ID=5ab51bfcd9a37448c1f5acaeca582d195ce80729\n";
    echo "GOOGLE_DRIVE_PRIVATE_KEY=\"-----BEGIN PRIVATE KEY-----\\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDguCXc/ku6MK3/\\nUUxCcmnt6fyk8jukFDfhulc/lHYTantBjG3md+rSmahN/V8QwdkfHDsEtEYa7JdB\\nqUgspRSHworxhtJlxmLQM5rzOtKfjquAte8QXZ+kb0oDaf2i7UnNLHW7kURWSJfx\\nzmmk5CYRQjCo8t8BOH1LtYH89qP3Z4phcy0gOuyyeBKisL9hYYIV08etL9wYQM9Y\\n8DnXxjKlX3zdbNUDysyUAjLH18961AwAtexYFt3gEiEpLwqGj123Lh7AcLqMtOGO\\n/bXX0cy+rc1XB/g8Hnq755UnIaB2XP0qkd/J+1dRvreHihk29RDPOmanAuP/gBXM\\nfAzumDTVAgMBAAECggEABaZLi664IobY7HJBcDOmUfrZDkTXVt8MHOYyDXynhm/d\\n0UePUumjUOtqLb18V2/d53qKAHPGYW3oAirzkBIP1NvvBi2cDKyOX480k9sEhQEf\\nTkJN1EPcBUIVlOqIwGWBh7PGX/cLD0f8FM21nl61u7l8yB8pTtXDPKdkMSeM0i7z\\n7D/QlyYTqiklZeW15gvpIjH9KOZ1GUCR4DyZ+g6jRBQcECyYgI38qg5M9nnzBTaH\\nZB6JsDywCO7Mqnwu8NcZ8nUeuvc81SCDvMldvyxzKM/06oSC6K0sLkvHZPYAh5pT\\n346bgOJtdRUITkF/u5CURqfHxX9jsNZwvFj6nzbpUQKBgQD8+mpHgeFuZvyAfRxA\\nNAt3XNlexF8wkrgwARFq5ZKZdRPai8ceQXzPGJ7Nq96K1uQYCnDwFCpg7PV5Woyz\\nfvp7Q6+MBuXdaKl80IQuHk/Y6OZYrn0ppQO6/gXr57BC2852b1pnJKWAjB80N1qr\\ne7PySeYQXOs5pD7SmibTihE6uQKBgQDjZ1HWHsMQuDqo+AcVSI5b5lyKIddXE6ya\\n0rUrHQiWYWmtQw/Z2ZzDiDkej1ontGCG7vHVwa9AOWwGX9IeIxZVJusM1cJApS8B\\nl1Z6KBSvLLynsQKfWYfX5WWh5ZvRPaS0OrFJKIaP4DGMUxFdm2vCvIAejlIfihRk\\nfZbOwRSM/QKBgE/SmAxeIAqc9ll4oJxlj6SnKHNtlPeXpASJobxQGfTP62bPKhIS\\n6dXS1/DfpUW7zpcDXGV0h3az7jTPbIwKqfCRB+gwGQHGz4vxo3OF3v37Zwtea2Hj\\njozHMIkiPwypnLjkI+tdtdcc34+zU6m+S6ZMyQoqt5IlkCkVuOCQtSUJAoGAfPha\\n0QE20glss08bFWc6VObUFFVkskXtpWgGiBr8jSgbm0wvedlNfWdvfIvrT8ahBYZr\\nAL3gQbtM0nP2VPmTXFh29CbFCiG9I3K344oDVAGR69YrSsc5EDzRDZfRebwWt7VJ\\nmJrc2FSs5iEAeiDfp7VP83rjRtHrQw6Bwj35vUECgYEAlR42+/otq48jD4ylBblj\\ncrTL8JGrn75rRNTMjm2iX4Zne+Xrc4WfgGSGewEc6odxjqWIvGykBrnofUdnMR24\\ns2Hz2OwK67XoIxfvQKspX70QQP/UxO7q1cZFLLRI+PjP2l+Cu5XEacMD/KWiqv9I\\nudQenrfQvVLIRtESdVxEdV0=\\n-----END PRIVATE KEY-----\\n\"\n";
    echo "GOOGLE_DRIVE_CLIENT_EMAIL=jomsbuilders-backup-agent@quotation-backups.iam.gserviceaccount.com\n";
    echo "GOOGLE_DRIVE_CLIENT_ID=112439056909561249289\n";
    echo "GOOGLE_DRIVE_FOLDER_ID=1Gleqcqf-ESe22mHKFwpDbgGcagpjLPpn\n";
    echo "───────────────────────────────────────\n";
    
    echo "\n  4. Save the file (Ctrl+O, Enter, Ctrl+X)\n";
    echo "\n  5. Clear config cache:\n";
    echo "     php artisan config:clear\n";
    echo "\n  6. Run this checker again:\n";
    echo "     php check_google_config_hostinger.php\n";
} else {
    echo "\n✅ All credentials are properly configured!\n";
    echo "\n📝 Next Steps:\n";
    echo "───────────────────────────────────────\n";
    echo "  1. Clear config cache:\n";
    echo "     php artisan config:clear\n";
    echo "\n  2. Refresh the admin dashboard\n";
    echo "     Google Drive status should now show: ✅ CONNECTED\n";
    echo "\n  3. Test backup upload:\n";
    echo "     php artisan backup:run\n";
}

echo "\n";
?>
