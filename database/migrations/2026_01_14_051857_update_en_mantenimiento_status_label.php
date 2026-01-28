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
        DB::table('equipos')
            ->where('ESTADO_OPERATIVO', 'EN_MANTENIMIENTO')
            ->update(['ESTADO_OPERATIVO' => 'EN MANTENIMIENTO']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('equipos')
            ->where('ESTADO_OPERATIVO', 'EN MANTENIMIENTO')
            ->update(['ESTADO_OPERATIVO' => 'EN_MANTENIMIENTO']);
    }
};
