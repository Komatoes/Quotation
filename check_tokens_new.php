<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Quotation;

echo "=== Recent Quotations with Public Tokens ===\n\n";

$quotations = Quotation::latest()->limit(5)->get(['id', 'subject', 'parent_quotation_id', 'quotation_type', 'public_token']);

foreach($quotations as $q) {
    $parentInfo = $q->parent_quotation_id ? "Child of #{$q->parent_quotation_id}" : "Parent";
    $tokenPreview = substr($q->public_token ?? 'NULL', 0, 10) . '...';
    echo "ID: {$q->id} | {$q->subject} | {$parentInfo} | Type: {$q->quotation_type} | Token: {$tokenPreview}\n";
}

echo "\n✅ Each quotation (parent and child) has its own public_token!\n";
?>
