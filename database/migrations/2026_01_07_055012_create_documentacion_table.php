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
        Schema::create('documentacion', function (Blueprint $table) {
            $table->unsignedBigInteger('ID_EQUIPO');
            $table->string('NRO_DE_DOCUMENTO', 100);
            $table->string('PLACA', 20)->unique()->nullable();
            $table->string('NOMBRE_DEL_TITULAR', 150);
            $table->string('LINK_DOC_PROPIEDAD', 500)->nullable();
            $table->unsignedBigInteger('ID_SEGURO');
            $table->string('ESTADO_POLIZA', 50);
            $table->date('FECHA_VENC_POLIZA');
            $table->string('LINK_POLIZA_SEGURO', 500)->nullable();
            $table->date('FECHA_ROTC')->nullable();
            $table->string('LINK_ROTC', 500)->nullable();
            $table->date('FECHA_RACDA')->nullable();
            $table->string('LINK_RACDA', 500)->nullable();
            $table->timestamps();

            $table->primary('ID_EQUIPO');
            $table->foreign('ID_EQUIPO')->references('ID_EQUIPO')->on('equipos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('ID_SEGURO')->references('ID_SEGURO')->on('catalogo_seguros')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentacion');
    }
};
