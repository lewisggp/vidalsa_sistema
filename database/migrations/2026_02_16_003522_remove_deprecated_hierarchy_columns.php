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
        // 1. Limpiar FrentesTrabajo
        Schema::table('frentes_trabajo', function (Blueprint $table) {
            // Drop foreign keys first (try catch block is not possible in migration, but we assume generated names)
            // Convention: table_column_foreign
            
            // Check if column exists before trying to drop foreign key or column
            if (Schema::hasColumn('frentes_trabajo', 'ID_FRENTE_PADRE')) {
                // Try dropping foreign key first (might fail if not exists, but usually safe to assume if column exists)
                // We use dropForeign with array syntax which automatically builds the name
                try {
                    $table->dropForeign(['ID_FRENTE_PADRE']);
                } catch (\Exception $e) {
                    // Ignore if foreign key doesn't exist
                }
                $table->dropColumn('ID_FRENTE_PADRE');
            }
        });

        // 2. Limpiar MovilizacionHistorial
        Schema::table('movilizacion_historial', function (Blueprint $table) {
            if (Schema::hasColumn('movilizacion_historial', 'ID_FRENTE_RECEPCION')) {
                try {
                    $table->dropForeign(['ID_FRENTE_RECEPCION']);
                } catch (\Exception $e) {
                    // Ignore
                }
                $table->dropColumn('ID_FRENTE_RECEPCION');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frentes_trabajo', function (Blueprint $table) {
            $table->unsignedBigInteger('ID_FRENTE_PADRE')->nullable();
            $table->foreign('ID_FRENTE_PADRE')->references('ID_FRENTE')->on('frentes_trabajo');
        });

        Schema::table('movilizacion_historial', function (Blueprint $table) {
            $table->unsignedBigInteger('ID_FRENTE_RECEPCION')->nullable();
            $table->foreign('ID_FRENTE_RECEPCION')->references('ID_FRENTE')->on('frentes_trabajo');
        });
    }
};
