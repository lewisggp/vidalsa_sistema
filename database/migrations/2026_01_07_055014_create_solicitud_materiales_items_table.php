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
        Schema::create('solicitud_materiales_items', function (Blueprint $table) {
            $table->id('ID_ITEM');
            $table->unsignedBigInteger('ID_SOLICITUD');
            $table->string('DESCRIPCION_MATERIAL', 255);
            $table->decimal('CANT_SOLICITADA', 10, 2);
            $table->string('UNIDAD', 50)->default('UNIDADES');
            $table->decimal('CANT_DISPONIBLE_FRENTE', 10, 2)->nullable();
            $table->decimal('CANT_APORTADA_PATIO', 10, 2)->default(0.00);
            // CANT_PROC se calculará en el modelo PHP por compatibilidad MySQL/SQLite
            $table->timestamps();

            $table->foreign('ID_SOLICITUD')->references('ID_SOLICITUD')->on('solicitudes_mantenimiento')->onDelete('cascade');
        });

        /*
        DB::unprepared('
            CREATE TRIGGER `TR_ACTUALIZAR_ESTADO_LOGISTICO_VIDALSA` AFTER UPDATE ON `solicitud_materiales_items` FOR EACH ROW BEGIN
                DECLARE TOTAL_ITEMS INT;
                DECLARE ITEMS_COMPLETOS INT;

                SELECT COUNT(*) INTO TOTAL_ITEMS FROM solicitud_materiales_items WHERE ID_SOLICITUD = NEW.ID_SOLICITUD;
                
                SELECT COUNT(*) INTO ITEMS_COMPLETOS FROM solicitud_materiales_items 
                WHERE ID_SOLICITUD = NEW.ID_SOLICITUD AND CANT_DISPONIBLE_FRENTE >= CANT_SOLICITADA;

                IF ITEMS_COMPLETOS = 0 THEN
                    UPDATE solicitudes_mantenimiento SET ESTADO_SOLIC = "PENDIENTE" WHERE ID_SOLICITUD = NEW.ID_SOLICITUD;
                ELSEIF ITEMS_COMPLETOS < TOTAL_ITEMS THEN
                    UPDATE solicitudes_mantenimiento SET ESTADO_SOLIC = "PARCIAL" WHERE ID_SOLICITUD = NEW.ID_SOLICITUD;
                ELSE
                    UPDATE solicitudes_mantenimiento SET ESTADO_SOLIC = "COMPLETADO" WHERE ID_SOLICITUD = NEW.ID_SOLICITUD;
                END IF;
            END
        ');
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_materiales_items');
    }
};
