<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $seeder = new \Database\Seeders\ExternalProductSeeder();
    $seeder->run();
    echo "Done";
} catch (\Exception $e) {
    echo "TOP LEVEL ERROR:";
    dd($e->getMessage());
}
