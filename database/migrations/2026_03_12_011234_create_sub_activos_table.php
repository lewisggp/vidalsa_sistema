<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_activos', function (Blueprint $table) {
            $table->id();

            // Tipo de sub-activo (extensible a futuro)
            $table->enum('tipo', [
                'MAQUINA_SOLDADURA',
                'PLANTA_ELECTRICA',
                'CONTENEDOR',
                'COMPRESOR',
                'OTRO',
            ])->default('OTRO');

            // Identificación
            $table->string('serial', 100)->nullable();
            $table->string('marca', 80)->nullable();
            $table->string('modelo', 80)->nullable();
            $table->smallInteger('anio')->nullable();

            // Ubicación: frente donde está físicamente (cuando está suelto)
            $table->unsignedBigInteger('ID_FRENTE')->nullable();

            // Vínculo opcional al vehículo que lo porta (camión de soldadura, etc.)
            // NULL = está suelto / independiente
            $table->unsignedBigInteger('ID_EQUIPO_HOST')->nullable();

            // Estado operativo
            $table->enum('estado', ['OPERATIVO', 'INOPERATIVO', 'EN_ALMACEN'])
                  ->default('OPERATIVO');

            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('ID_FRENTE')
                  ->references('ID_FRENTE')->on('frentes_trabajo')
                  ->onDelete('set null');

            $table->foreign('ID_EQUIPO_HOST')
                  ->references('ID_EQUIPO')->on('equipos')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_activos');
    }
};
