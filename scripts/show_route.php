<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$quot = DB::table('quotations')->whereNotNull('public_token')->first();
if (!$quot) {
    echo "No quotation with public_token found\n";
    exit(1);
}

// Generate route
$url = route('quotation.public', ['token' => $quot->public_token]);
echo "Generated URL: $url\n";
