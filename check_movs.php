<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$movs = \App\Models\Movilizacion::orderBy('created_at', 'desc')->take(5)->get();
echo json_encode($movs, JSON_PRETTY_PRINT);
