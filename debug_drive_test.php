<?php

use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Log;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Google Drive Service...\n";

try {
    $service = GoogleDriveService::getInstance();
    $drive = $service->getDrive(); // This triggers token check/refresh
    echo "Service initialized successfully.\n";
    
    $about = $drive->about->get(['fields' => 'user']);
    echo "Connected as: " . $about->user->emailAddress . "\n";
    
    echo "Attempting to list files in root folder...\n";
    $files = $drive->files->listFiles(['pageSize' => 5]);
    echo "Found " . count($files->getFiles()) . " files.\n";
    
    echo "Test PASSED.\n";
} catch (\Exception $e) {
    echo "Test FAILED: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
