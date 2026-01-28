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
        Schema::create('caracteristicas_modelo', function (Blueprint $table) {
            $table->id('ID_ESPEC');
            $table->string('MODELO', 50)->comment('Modelo del Equipo (Ej: D6T)');
            $table->smallInteger('ANIO_ESPEC');
            $table->string('MOTOR', 150)->nullable();
            // $table->string('TRANSMISION', 100)->nullable(); // Removed
            $table->string('CAPACIDAD', 50)->nullable();
            // $table->string('VERSION_MOTOR', 100)->nullable(); // Removed
            $table->string('COMBUSTIBLE', 100)->nullable();
            $table->string('CONSUMO_PROMEDIO', 50)->nullable();
            $table->string('ACEITE_MOTOR', 100)->nullable();
            $table->string('ACEITE_CAJA', 100)->nullable();
            $table->string('LIGA_FRENO', 50)->nullable();
            $table->string('REFRIGERANTE', 100)->nullable();
            $table->string('TIPO_BATERIA', 100)->nullable();
            $table->string('FOTO_REFERENCIAL', 255)->nullable(); // Added
            $table->timestamps();

            $table->unique(['MODELO', 'ANIO_ESPEC', 'ACEITE_MOTOR'], 'uk_especificacion_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caracteristicas_modelo');
    }
};
