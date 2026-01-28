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
        Schema::create('catalogo_seguros', function (Blueprint $table) {
            $table->id('ID_SEGURO')->comment('Clave Primaria');
            $table->string('NOMBRE_ASEGURADORA', 150)->unique()->comment('Nombre de la compañía de seguros');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogo_seguros');
    }
};
