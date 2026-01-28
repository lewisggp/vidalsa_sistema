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
        Schema::create('movilizacion_historial', function (Blueprint $table) {
            $table->id('ID_MOVILIZACION');
            $table->string('CODIGO_CONTROL', 100);
            $table->unsignedBigInteger('ID_EQUIPO');
            $table->unsignedBigInteger('ID_FRENTE_ORIGEN');
            $table->unsignedBigInteger('ID_FRENTE_DESTINO');
            $table->dateTime('FECHA_DESPACHO');
            $table->dateTime('FECHA_RECEPCION')->nullable();
            $table->string('ESTADO_MVO', 20)->default('TRANSITO');
            $table->string('USUARIO_REGISTRO', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movilizacion_historial');
    }
};
