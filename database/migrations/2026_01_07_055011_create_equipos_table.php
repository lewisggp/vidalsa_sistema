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
        Schema::create('equipos', function (Blueprint $table) {
            $table->id('ID_EQUIPO');
            $table->string('TIPO_EQUIPO', 50);
            $table->string('CATEGORIA_FLOTA', 20)->nullable();
            $table->string('CODIGO_PATIO', 20)->unique();
            $table->string('MARCA', 50);
            $table->string('MODELO', 50);
            $table->smallInteger('ANIO');
            $table->unsignedBigInteger('ID_ESPEC')->nullable();
            $table->string('SERIAL_CHASIS', 100)->unique();
            $table->string('SERIAL_DE_MOTOR', 100)->nullable();
            $table->string('LINK_GPS', 500)->nullable();
            $table->string('FOTO_EQUIPO', 500)->nullable();
            $table->unsignedBigInteger('ID_FRENTE_ACTUAL')->nullable();
            $table->boolean('CONFIRMADO_EN_SITIO')->default(0);
            $table->string('ESTADO_OPERATIVO', 20)->default('OPERATIVO');
            $table->unsignedBigInteger('ID_ANCLAJE')->nullable();
            $table->timestamps();

            // $table->foreign('ID_ESPEC')->references('ID_ESPEC')->on('caracteristicas_modelo')->onDelete('set null')->onUpdate('cascade');
            // $table->foreign('ID_FRENTE_ACTUAL')->references('ID_FRENTE')->on('frentes_trabajo')->onDelete('set null')->onUpdate('cascade');
            // $table->foreign('ID_ANCLAJE')->references('ID_EQUIPO')->on('equipos')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
