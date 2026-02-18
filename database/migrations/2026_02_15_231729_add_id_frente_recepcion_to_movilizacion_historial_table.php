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
        Schema::table('movilizacion_historial', function (Blueprint $table) {
            if (!Schema::hasColumn('movilizacion_historial', 'ID_FRENTE_RECEPCION')) {
                $table->unsignedBigInteger('ID_FRENTE_RECEPCION')->nullable()->after('ID_FRENTE_DESTINO');
                $table->foreign('ID_FRENTE_RECEPCION')->references('ID_FRENTE')->on('frentes_trabajo')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movilizacion_historial', function (Blueprint $table) {
            $table->dropForeign(['ID_FRENTE_RECEPCION']);
            $table->dropColumn('ID_FRENTE_RECEPCION');
        });
    }
};
