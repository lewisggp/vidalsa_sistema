<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movilizacion_historial', function (Blueprint $table) {
            // Tipo de movimiento: DESPACHO (normal) o RECEPCION_DIRECTA (sin despacho previo)
            if (!Schema::hasColumn('movilizacion_historial', 'TIPO_MOVIMIENTO')) {
                $table->string('TIPO_MOVIMIENTO', 30)->default('DESPACHO')->after('ESTADO_MVO');
            }

            // Quién confirmó la recepción (auditoría)
            if (!Schema::hasColumn('movilizacion_historial', 'USUARIO_RECEPCION')) {
                $table->string('USUARIO_RECEPCION', 100)->nullable()->after('USUARIO_REGISTRO');
            }
        });

        // Hacer FECHA_DESPACHO nullable (recepciones directas no tienen fecha de despacho)
        Schema::table('movilizacion_historial', function (Blueprint $table) {
            $table->dateTime('FECHA_DESPACHO')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('movilizacion_historial', function (Blueprint $table) {
            if (Schema::hasColumn('movilizacion_historial', 'TIPO_MOVIMIENTO')) {
                $table->dropColumn('TIPO_MOVIMIENTO');
            }
            if (Schema::hasColumn('movilizacion_historial', 'USUARIO_RECEPCION')) {
                $table->dropColumn('USUARIO_RECEPCION');
            }
        });
    }
};
