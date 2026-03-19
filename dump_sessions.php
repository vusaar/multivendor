<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$sessions = DB::table('whatsapp_sessions')->get();
$data = [];
foreach ($sessions as $s) {
    $data[$s->phone_number] = json_decode($s->data);
}

file_put_contents('sessions_dump.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Dumped " . count($data) . " sessions to sessions_dump.json\n";
