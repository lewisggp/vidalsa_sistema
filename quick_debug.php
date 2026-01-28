<?php
// quick_debug.php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\Usuario::all();
foreach($users as $u) {
    echo "ID: " . $u->ID_USUARIO . " | EMAIL: " . $u->CORREO_ELECTRONICO . " | STATUS: " . $u->ESTATUS . "\n";
}
