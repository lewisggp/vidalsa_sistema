<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumibles', function (Blueprint $table) {
            // Solo aplica a ACEITE_MOTOR, ACEITE_CAJA, ACEITE_HIDR
            // Ejemplos: 15W-40, 5W-30, SAE 90, SAE 30, 10W-30
            $table->string('VISCOSIDAD', 30)
                  ->nullable()
                  ->after('TIPO_CONSUMIBLE')
                  ->comment('Viscosidad/grado del aceite. Ej: 15W-40, SAE 90. Solo para tipos ACEITE_*');
        });
    }

    public function down(): void
    {
        Schema::table('consumibles', function (Blueprint $table) {
            $table->dropColumn('VISCOSIDAD');
        });
    }
};
