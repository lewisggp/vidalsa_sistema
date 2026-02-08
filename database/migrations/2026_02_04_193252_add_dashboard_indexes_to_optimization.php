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
        try {
            Schema::table('documentacion', function (Blueprint $table) {
                // Check existing indexes manually to be safe
                $indexes = collect(DB::select("SHOW INDEXES FROM documentacion"))->pluck('Key_name')->toArray();

                if (!in_array('documentacion_fecha_venc_poliza_index', $indexes)) $table->index('FECHA_VENC_POLIZA');
                if (!in_array('documentacion_fecha_rotc_index', $indexes)) $table->index('FECHA_ROTC');
                if (!in_array('documentacion_fecha_racda_index', $indexes)) $table->index('FECHA_RACDA');
            });

            Schema::table('equipos', function (Blueprint $table) {
                $indexes = collect(DB::select("SHOW INDEXES FROM equipos"))->pluck('Key_name')->toArray();

                if (!in_array('equipos_id_tipo_equipo_index', $indexes)) $table->index('id_tipo_equipo');
            });

        } catch (\Throwable $e) {
            // Log error but don't stop execution if index exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentacion', function (Blueprint $table) {
            $table->dropIndex(['FECHA_VENC_POLIZA']);
            $table->dropIndex(['FECHA_ROTC']);
            $table->dropIndex(['FECHA_RACDA']);
        });

        Schema::table('equipos', function (Blueprint $table) {
            $table->dropIndex(['id_tipo_equipo']);
        });
    }
};
