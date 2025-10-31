<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $tokensCount = DB::table('quotations')->whereNotNull('public_token')->count();
    echo "tokens_count: {$tokensCount}\n";

    $tokens = DB::table('quotations')->select('id','public_token')->whereNotNull('public_token')->limit(5)->get();
    echo "sample_tokens:\n";
    foreach ($tokens as $t) {
        echo "  id={$t->id} token={$t->public_token}\n";
    }

    $clients = DB::table('clients')->select('id')->limit(5)->get();
    echo "clients_sample:\n";
    foreach ($clients as $c) echo "  client_id={$c->id}\n";

    $users = DB::table('users')->select('id')->limit(5)->get();
    echo "users_sample:\n";
    foreach ($users as $u) echo "  user_id={$u->id}\n";

    $status = DB::table('quotation_status')->select('id,status_name')->limit(10)->get();
    echo "status_sample:\n";
    foreach ($status as $s) echo "  id={$s->id} name={$s->status_name}\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
