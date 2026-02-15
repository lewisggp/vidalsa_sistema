<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tipo_equipos', function (Blueprint $table) {
            $table->enum('ROL_ANCLAJE', ['NEUTRO', 'REMOLCADOR', 'REMOLCABLE'])
                  ->default('NEUTRO')
                  ->after('nombre') // Place it after the name for better structure
                  ->comment('Define si el tipo de equipo remolca, es remolcado o es independiente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_equipos', function (Blueprint $table) {
            $table->dropColumn('ROL_ANCLAJE');
        });
    }
};
