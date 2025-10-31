<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$rows = DB::table('quotations')
    ->leftJoin('quotation_status', 'quotations.status_id', '=', 'quotation_status.id')
    ->select('quotations.id', 'quotations.public_token', 'quotation_status.status_name')
    ->get();

foreach ($rows as $r) {
    echo "id={$r->id} token={$r->public_token} status={$r->status_name}\n";
}
