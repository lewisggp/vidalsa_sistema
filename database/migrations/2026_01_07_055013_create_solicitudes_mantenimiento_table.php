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
        Schema::create('solicitudes_mantenimiento', function (Blueprint $table) {
            $table->id('ID_SOLICITUD');
            $table->unsignedBigInteger('ID_EQUIPO');
            $table->unsignedBigInteger('ID_FRENTE_ORIGEN');
            $table->unsignedBigInteger('ID_USUARIO_SOLICITA');
            $table->string('TIPO_MANTENIMIENTO', 20);
            $table->timestamp('FECHA_SOLICITUD')->useCurrent();
            $table->text('DESCRIPCION_MOTIVO');
            $table->string('ESTADO_SOLIC', 20)->default('PENDIENTE');
            $table->string('FOTO_INFORME_FIRMADO', 500)->nullable();
            $table->boolean('REVISADO_LOCAL')->default(0);
            $table->boolean('REVISADO_PATIO')->default(0);
            $table->timestamps();

            $table->foreign('ID_EQUIPO')->references('ID_EQUIPO')->on('equipos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('ID_FRENTE_ORIGEN')->references('ID_FRENTE')->on('frentes_trabajo');
            $table->foreign('ID_USUARIO_SOLICITA')->references('ID_USUARIO')->on('usuarios');
        });

        /*
        DB::unprepared('
            CREATE TRIGGER `tr_equipo_inoperativo_por_falla` AFTER INSERT ON `solicitudes_mantenimiento` FOR EACH ROW BEGIN
                UPDATE equipos 
                SET ESTADO_OPERATIVO = "INOPERATIVO" 
                WHERE ID_EQUIPO = NEW.ID_EQUIPO;
            END
        ');
        */

        /*
        DB::unprepared("
            CREATE TRIGGER `tr_habilitar_equipo_final` AFTER UPDATE ON `solicitudes_mantenimiento` FOR EACH ROW BEGIN
                IF NEW.ESTADO_SOLIC = 'COMPLETADO' THEN
                    UPDATE equipos SET ESTADO_OPERATIVO = 'OPERATIVO' WHERE ID_EQUIPO = NEW.ID_EQUIPO;
                END IF;
            END
        ");
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_mantenimiento');
    }
};
