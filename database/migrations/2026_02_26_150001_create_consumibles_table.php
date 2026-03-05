<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumibles', function (Blueprint $table) {
            $table->id('ID_CONSUMIBLE');

            // ══════════════════════════════════════════════════════
            // BLOQUE 1: LO QUE SE COPIA DIRECTO DEL EXCEL
            // (después de limpiarlo con IA)
            // ══════════════════════════════════════════════════════

            $table->date('FECHA')->comment('Fecha del despacho — ya reconstruida del Excel');

            $table->string('IDENTIFICADOR', 100)->nullable()
                  ->comment('Placa, serial de chasis o nº etiqueta — ya extraído limpio del Excel');

            $table->string('RESP_NOMBRE', 150)->nullable()
                  ->comment('Nombre del responsable que retiró el consumible');

            $table->string('RESP_CI', 20)->nullable()
                  ->comment('Cédula del responsable');

            $table->decimal('CANTIDAD', 10, 2)
                  ->comment('Litros para gasoil/aceite, Unidades para cauchos');

            $table->string('RAW_ORIGEN', 300)->nullable()
                  ->comment('Texto del origen del suministro cuando el Excel lo incluye');

            // ══════════════════════════════════════════════════════
            // BLOQUE 2: LO QUE SE DEFINE UNA VEZ EN EL FORMULARIO
            // (igual para todo el lote que se pega)
            // ══════════════════════════════════════════════════════

            $table->enum('TIPO_CONSUMIBLE', [
                'GASOIL', 'GASOLINA',
                'ACEITE_MOTOR', 'ACEITE_CAJA', 'ACEITE_HIDR',
                'CAUCHO', 'REFRIGERANTE', 'OTRO'
            ])->comment('Definido en el formulario antes de pegar las filas');

            $table->enum('UNIDAD', ['LITROS', 'GALONES', 'UNIDADES', 'KG'])
                  ->default('LITROS')
                  ->comment('Auto-sugerido según tipo, ajustable en el formulario');

            $table->unsignedBigInteger('ID_FRENTE')->nullable()
                  ->comment('Frente seleccionado por nombre en el formulario antes de pegar');
            $table->foreign('ID_FRENTE')->references('ID_FRENTE')->on('frentes_trabajo')->nullOnDelete();

            // ══════════════════════════════════════════════════════
            // BLOQUE 3: LO QUE SE LLENA DESPUÉS CON SQL
            // ══════════════════════════════════════════════════════

            $table->unsignedBigInteger('ID_EQUIPO')->nullable()
                  ->comment('FK → equipos — se resuelve con SQL usando IDENTIFICADOR');
            $table->foreign('ID_EQUIPO')->references('ID_EQUIPO')->on('equipos')->nullOnDelete();

            $table->unsignedBigInteger('ID_SUMINISTRO')->nullable()
                  ->comment('FK → suministros_origen — se asigna al vincular con la cisterna');
            $table->foreign('ID_SUMINISTRO')->references('ID_SUMINISTRO')->on('suministros_origen')->nullOnDelete();

            $table->enum('ESTADO_EQUIPO', ['PENDIENTE', 'CONFIRMADO', 'SIN_MATCH'])
                  ->default('PENDIENTE')
                  ->comment('PENDIENTE hasta que ID_EQUIPO quede resuelto por SQL');

            $table->text('NOTAS')->nullable();

            $table->timestamps();

            // Índices para que los gráficos sean rápidos
            $table->index('FECHA',           'idx_con_fecha');
            $table->index('TIPO_CONSUMIBLE', 'idx_con_tipo');
            $table->index('ID_FRENTE',       'idx_con_frente');
            $table->index('ID_EQUIPO',       'idx_con_equipo');
            $table->index('ESTADO_EQUIPO',   'idx_con_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumibles');
    }
};
