<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suministros_origen', function (Blueprint $table) {
            $table->id('ID_SUMINISTRO');

            $table->enum('TIPO_COMBUSTIBLE', [
                'GASOIL', 'GASOLINA',
                'ACEITE_MOTOR', 'ACEITE_CAJA', 'ACEITE_HIDR',
                'CAUCHO', 'REFRIGERANTE', 'OTRO'
            ])->comment('Tipo de consumible que llegó al proyecto');

            $table->decimal('CANTIDAD_TOTAL', 10, 2)->comment('Litros / Unidades totales que llegaron');
            $table->enum('UNIDAD', ['LITROS', 'GALONES', 'UNIDADES', 'KG'])->default('LITROS');

            $table->date('FECHA_LLEGADA')->comment('Fecha en que llegó la carga al proyecto');

            $table->unsignedBigInteger('ID_FRENTE')->nullable()->comment('FK → frentes_trabajo');
            $table->foreign('ID_FRENTE')->references('ID_FRENTE')->on('frentes_trabajo')->nullOnDelete();

            $table->string('PROVEEDOR', 200)->nullable()->comment('Empresa que trajo el suministro');
            $table->string('NRO_GUIA', 100)->nullable()->comment('Número de guía de despacho');
            $table->string('NRO_CISTERNA', 100)->nullable()->comment('Placa del camión cisterna');

            $table->text('NOTAS')->nullable();

            $table->timestamps();

            $table->index('TIPO_COMBUSTIBLE', 'idx_so_tipo');
            $table->index('ID_FRENTE',        'idx_so_frente');
            $table->index('FECHA_LLEGADA',     'idx_so_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suministros_origen');
    }
};
