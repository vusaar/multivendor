<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$host = config('database.connections.pgsql.host');
$db = config('database.connections.pgsql.database');
$user = config('database.connections.pgsql.username');
$port = config('database.connections.pgsql.port');

echo "Testing connection to {$host}:{$port}...\n";

$start = microtime(true);
try {
    // Force a new connection
    $pdo = DB::connection()->getPdo();
    $connectTime = microtime(true) - $start;
    echo "SUCCESS: Connected in " . number_format($connectTime, 4) . " seconds.\n";
} catch (\Exception $e) {
    echo "ERROR: Could not connect: " . $e->getMessage() . "\n";
    exit;
}

$start = microtime(true);
$pdo->query("SELECT 1");
$queryTime = microtime(true) - $start;
echo "SUCCESS: 'SELECT 1' executed in " . number_format($queryTime, 4) . " seconds.\n";

if ($connectTime > 1) {
    echo "\n[DIAGNOSIS]: The connection itself is slow (" . number_format($connectTime, 2) . "s).\n";
    echo "This indicates a NETWORK or SERVER CONFIGURATION issue, not a code issue.\n";
    echo "Detailed causes:\n";
    echo "1. The remote server might be trying to reverse-lookup your IP (" . gethostname() . ") and failing (DNS timeout).\n";
    echo "2. A firewall is dropping packets before allowing the connection.\n";
    echo "3. High latency/ping to the remote server.\n";
} else {
    echo "\n[DIAGNOSIS]: Connection is fast. The slowness might be in the query itself or app overhead.\n";
}
