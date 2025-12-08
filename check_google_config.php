<?php
// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check Google Drive config
echo "=== GOOGLE DRIVE CONFIG CHECK ===\n\n";

$enabled = config('services.google.drive.enabled');
$projectId = config('services.google.drive.project_id');
$privateKeyId = config('services.google.drive.private_key_id');
$privateKey = config('services.google.drive.private_key');
$clientEmail = config('services.google.drive.client_email');
$clientId = config('services.google.drive.client_id');
$folderId = config('services.google.drive.folder_id');

echo "Enabled: " . ($enabled ? 'YES' : 'NO') . "\n";
echo "Project ID: " . ($projectId ? "✓ " . substr($projectId, 0, 20) . "..." : "✗ MISSING") . "\n";
echo "Private Key ID: " . ($privateKeyId ? "✓ " . $privateKeyId : "✗ MISSING") . "\n";
echo "Private Key: " . ($privateKey ? "✓ " . strlen($privateKey) . " chars" : "✗ MISSING") . "\n";
echo "Client Email: " . ($clientEmail ? "✓ " . $clientEmail : "✗ MISSING") . "\n";
echo "Client ID: " . ($clientId ? "✓ " . $clientId : "✗ MISSING") . "\n";
echo "Folder ID: " . ($folderId ? "✓ " . $folderId : "✗ MISSING") . "\n";

echo "\n=== CONNECTION STATUS ===\n";
$connected = $enabled && !empty($projectId) && !empty($privateKey) && !empty($clientEmail) && !empty($folderId);
echo "Connected: " . ($connected ? "✓ YES" : "✗ NO") . "\n";

if (!$connected) {
    echo "\nMissing:\n";
    if (!$enabled) echo "  - Google Drive not ENABLED\n";
    if (!$projectId) echo "  - PROJECT_ID\n";
    if (!$privateKey) echo "  - PRIVATE_KEY\n";
    if (!$clientEmail) echo "  - CLIENT_EMAIL\n";
    if (!$folderId) echo "  - FOLDER_ID\n";
}

echo "\n=== RAW ENV VALUES ===\n";
echo "GOOGLE_DRIVE_ENABLED: " . (env('GOOGLE_DRIVE_ENABLED') ? env('GOOGLE_DRIVE_ENABLED') : "NOT SET") . "\n";
echo "GOOGLE_DRIVE_PROJECT_ID: " . (env('GOOGLE_DRIVE_PROJECT_ID') ? env('GOOGLE_DRIVE_PROJECT_ID') : "NOT SET") . "\n";
echo "GOOGLE_DRIVE_FOLDER_ID: " . (env('GOOGLE_DRIVE_FOLDER_ID') ? env('GOOGLE_DRIVE_FOLDER_ID') : "NOT SET") . "\n";
?>
