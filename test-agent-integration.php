<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\SearchAgentService;

$query = "shirts under 50";
echo "Testing SearchAgentService with query: '{$query}'\n";

$service = new SearchAgentService();
$results = $service->search($query);

if ($results === null) {
    echo "ERROR: SearchAgentService returned NULL. Check Laravel logs or if Node.js agent is running on port 3001.\n";
} elseif (empty($results)) {
    echo "SUCCESS: Connection established, but no results found for this query.\n";
} else {
    echo "SUCCESS: Found " . count($results) . " product IDs:\n";
    print_r($results);
}
