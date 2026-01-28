<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update statuses to match the strictly required ENUM values
        DB::table('movilizacion_historial')
            ->where('ESTADO_MVO', 'COMPLETADA')
            ->update(['ESTADO_MVO' => 'RECIBIDO']);

        DB::table('movilizacion_historial')
            ->where('ESTADO_MVO', 'CANCELADO')
            ->update(['ESTADO_MVO' => 'RETORNADO']);
            
        // Optional: If there are any other weird statuses, default them to TRANSITO?
        // For now, let's stick to the user's specific request.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No logical reverse for data correction
    }
};
