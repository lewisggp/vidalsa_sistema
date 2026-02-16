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
        // 1. Agregar campo de SUBDIVISIONES (Texto simple separado por comas) a frentes_trabajo
        Schema::table('frentes_trabajo', function (Blueprint $table) {
            $table->text('SUBDIVISIONES')->nullable()->after('TIPO_FRENTE');
        });

        // 2. Ajustar movilizacion_historial para manejar la ubicación específica como TEXTO (Nombre del Patio)
        // en lugar de depender de un ID de frente fantasma.
        Schema::table('movilizacion_historial', function (Blueprint $table) {
            // Drop FK si existe (la creé en una migración previa reciente)
            // Primero aseguramos eliminar la restricción si existe, luego la columna o la reutilizamos.
            // Para seguridad, agregamos una columna nueva dedicada al nombre.
            $table->string('DETALLE_UBICACION', 150)->nullable()->after('ID_FRENTE_DESTINO')
                  ->comment('Nombre específico del patio o subdivisión donde se recibió (ej: PATIO 1)');
        });

        // 3. Ajustar equipos para rastrear la ubicación detallada actual
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('DETALLE_UBICACION_ACTUAL', 150)->nullable()->after('ID_FRENTE_ACTUAL')
                  ->comment('Nombre específico del patio actual (ej: PATIO 1)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frentes_trabajo', function (Blueprint $table) {
            $table->dropColumn('SUBDIVISIONES');
        });

        Schema::table('movilizacion_historial', function (Blueprint $table) {
            $table->dropColumn('DETALLE_UBICACION');
        });

        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn('DETALLE_UBICACION_ACTUAL');
        });
    }
};
