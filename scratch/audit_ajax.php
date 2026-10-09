<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

echo "=== AUDITING FRONTEND AJAX & API ENDPOINTS ===\n";

$registeredUris = [];
foreach (Route::getRoutes()->getRoutes() as $r) {
    $registeredUris[] = '/' . ltrim($r->uri(), '/');
    if ($r->getName()) {
        $registeredUris[] = $r->getName();
    }
}

$bladeFiles = File::allFiles(resource_path('views'));
$ajaxDetails = [];

foreach ($bladeFiles as $file) {
    $content = file_get_contents($file->getRealPath());
    $relativePath = str_replace(base_path() . '/', '', $file->getRealPath());

    // Detect fetch('url'), $.ajax({url: '...'}), axios.post('url'), etc.
    if (preg_match_all('/(?:fetch|\$\.ajax|axios\.(?:get|post|put|delete))\s*\(\s*[\'"`]([^\'"`]+)[\'"`]/i', $content, $m)) {
        foreach ($m[1] as $endpoint) {
            // Ignore absolute external URLs or variable interpolations
            if (strpos($endpoint, 'http://') === 0 || strpos($endpoint, 'https://') === 0 || strpos($endpoint, '${') !== false) {
                continue;
            }
            $cleanUri = '/' . ltrim($endpoint, '/');
            $ajaxDetails[] = [
                'file' => $relativePath,
                'endpoint' => $endpoint,
                'clean_uri' => $cleanUri
            ];
        }
    }
}

echo "Total AJAX Endpoints Found in Views: " . count($ajaxDetails) . "\n";
foreach ($ajaxDetails as $ajax) {
    echo " - [{$ajax['file']}] Endpoint: {$ajax['endpoint']}\n";
}
