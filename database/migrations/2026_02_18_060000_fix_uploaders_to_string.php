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
        Schema::table('documentacion', function (Blueprint $table) {
            // 1. Eliminar Foreign Keys si existen (para permitir guardar texto libre como emails)
            // Se usa array syntax para dropForeign que busca el nombre convencional 'documentacion_columna_foreign'
            try {
                $table->dropForeign(['POLIZA_SUBIDO_POR']);
            } catch (\Exception $e) {}
            try {
                $table->dropForeign(['ROTC_SUBIDO_POR']);
            } catch (\Exception $e) {}
            try {
                $table->dropForeign(['RACDA_SUBIDO_POR']);
            } catch (\Exception $e) {}
            try {
                $table->dropForeign(['PROPIEDAD_SUBIDO_POR']);
            } catch (\Exception $e) {}
            try {
                $table->dropForeign(['ADICIONAL_SUBIDO_POR']);
            } catch (\Exception $e) {}

            // 2. Cambiar columnas a String (VARCHAR) para guardar emails
            $table->string('POLIZA_SUBIDO_POR')->nullable()->change();
            $table->string('ROTC_SUBIDO_POR')->nullable()->change();
            $table->string('RACDA_SUBIDO_POR')->nullable()->change();
            $table->string('PROPIEDAD_SUBIDO_POR')->nullable()->change();
            $table->string('ADICIONAL_SUBIDO_POR')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se puede revertir fácilmente a INT si ya hay emails guardados, 
        // pero definimos la estructura inversa por formalidad.
        Schema::table('documentacion', function (Blueprint $table) {
             // Esto fallará si hay datos no numéricos, es solo referencial
            // $table->unsignedBigInteger('POLIZA_SUBIDO_POR')->nullable()->change();
            // ... re-add foreign keys
        });
    }
};
