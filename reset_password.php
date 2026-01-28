<?php
// reset_password.php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = \App\Models\Usuario::find(1);
if($u) {
    echo "Found user: " . $u->CORREO_ELECTRONICO . "\n";
    $u->PASSWORD_HASH = \Illuminate\Support\Facades\Hash::make('2302');
    $u->ESTATUS = 'ACTIVO';
    $u->save();
    echo "Password RESET to '2302' and Status ACTIVO successfully.\n";
} else {
    echo "User ID 1 NOT FOUND.\n";
}
