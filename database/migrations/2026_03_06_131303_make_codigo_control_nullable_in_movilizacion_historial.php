<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CODIGO_CONTROL must be nullable so that recepciones directas
     * (which have no prior dispatch) can be stored without a control number.
     * Also make FECHA_DESPACHO nullable for the same reason.
     */
    public function up(): void
    {
        Schema::table('movilizacion_historial', function (Blueprint $table) {
            $table->string('CODIGO_CONTROL', 100)->nullable()->change();
            $table->dateTime('FECHA_DESPACHO')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('movilizacion_historial', function (Blueprint $table) {
            $table->string('CODIGO_CONTROL', 100)->nullable(false)->change();
            $table->dateTime('FECHA_DESPACHO')->nullable(false)->change();
        });
    }
};
