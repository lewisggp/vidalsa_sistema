<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$frentes = \App\Models\FrenteTrabajo::get();
echo json_encode($frentes, JSON_PRETTY_PRINT);
