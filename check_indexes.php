<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['equipos', 'movilizacion_historial'];

foreach ($tables as $table) {
    echo "Indexes for table '$table':\n";
    $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($table);
    foreach ($indexes as $index) {
        echo "- " . $index->getName() . " (" . implode(', ', $index->getColumns()) . ")\n";
    }
    echo "\n";
}
