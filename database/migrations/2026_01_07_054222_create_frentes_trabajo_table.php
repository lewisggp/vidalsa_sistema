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
        Schema::create('frentes_trabajo', function (Blueprint $table) {
            $table->id('ID_FRENTE');
            $table->string('NOMBRE_FRENTE', 150)->unique()->comment('Ej: PATIO EL LECHON o PROYECTO BOLIVAR');
            $table->string('UBICACION', 100)->nullable();
            $table->enum('TIPO_FRENTE', ['RESGUARDO', 'OPERACION'])->default('OPERACION')->comment('Clave para reportes de disponibilidad');
            $table->enum('ESTATUS_FRENTE', ['ACTIVO', 'FINALIZADO'])->default('ACTIVO');
            $table->string('RESP_1_NOM', 60)->nullable();
            $table->string('RESP_1_CAR', 40)->nullable();
            $table->string('RESP_2_NOM', 60)->nullable();
            $table->string('RESP_2_CAR', 40)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frentes_trabajo');
    }
};
