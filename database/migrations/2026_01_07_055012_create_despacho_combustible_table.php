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
        Schema::create('despacho_combustible', function (Blueprint $table) {
            $table->id('ID_DESPACHO');
            $table->unsignedBigInteger('ID_EQUIPO');
            $table->unsignedBigInteger('ID_FRENTE');
            $table->date('FECHA');
            $table->decimal('CANTIDAD_LITROS', 10, 2);
            $table->timestamps();

            $table->foreign('ID_EQUIPO')->references('ID_EQUIPO')->on('equipos')->onDelete('cascade');
            $table->foreign('ID_FRENTE')->references('ID_FRENTE')->on('frentes_trabajo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despacho_combustible');
    }
};
