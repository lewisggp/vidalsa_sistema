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
        Schema::table('equipos', function (Blueprint $table) {
            // Add indexes for autocomplete performance
            $table->index('MODELO', 'idx_equipos_modelo');
            $table->index('ANIO', 'idx_equipos_anio');
            $table->index(['MODELO', 'ANIO'], 'idx_equipos_modelo_anio');
        });

        Schema::table('caracteristicas_modelo', function (Blueprint $table) {
            // Add indexes for catalog search performance
            $table->index('MODELO', 'idx_caracteristicas_modelo');
            $table->index('ANIO_ESPEC', 'idx_caracteristicas_anio');
            $table->index(['MODELO', 'ANIO_ESPEC'], 'idx_caracteristicas_modelo_anio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropIndex('idx_equipos_modelo');
            $table->dropIndex('idx_equipos_anio');
            $table->dropIndex('idx_equipos_modelo_anio');
        });

        Schema::table('caracteristicas_modelo', function (Blueprint $table) {
            $table->dropIndex('idx_caracteristicas_modelo');
            $table->dropIndex('idx_caracteristicas_anio');
            $table->dropIndex('idx_caracteristicas_modelo_anio');
        });
    }
};
