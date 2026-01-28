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
        Schema::create('responsable', function (Blueprint $table) {
            $table->id('ID_ASIGNACION');
            $table->unsignedBigInteger('ID_EQUIPO');
            $table->string('CEDULA_RESPONSABLE', 20);
            $table->string('PERSONA_ASIGNADA', 150);
            $table->date('FECHA_ASIGNACION');
            $table->timestamps();

            $table->foreign('ID_EQUIPO')->references('ID_EQUIPO')->on('equipos')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responsable');
    }
};
