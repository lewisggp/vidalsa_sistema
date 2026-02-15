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
            // Poliza tracking
            $table->unsignedBigInteger('POLIZA_SUBIDO_POR')->nullable();
            $table->timestamp('POLIZA_FECHA_SUBIDA')->nullable();
            
            // ROTC tracking
            $table->unsignedBigInteger('ROTC_SUBIDO_POR')->nullable();
            $table->timestamp('ROTC_FECHA_SUBIDA')->nullable();
            
            // RACDA tracking
            $table->unsignedBigInteger('RACDA_SUBIDO_POR')->nullable();
            $table->timestamp('RACDA_FECHA_SUBIDA')->nullable();
            
            // Propiedad tracking
            $table->unsignedBigInteger('PROPIEDAD_SUBIDO_POR')->nullable();
            $table->timestamp('PROPIEDAD_FECHA_SUBIDA')->nullable();
            
            // Adicional tracking  
            $table->unsignedBigInteger('ADICIONAL_SUBIDO_POR')->nullable();
            $table->timestamp('ADICIONAL_FECHA_SUBIDA')->nullable();
        });
        
        // Add foreign keys in separate statement to avoid conflicts
        Schema::table('documentacion', function (Blueprint $table) {
            $table->foreign('POLIZA_SUBIDO_POR')->references('ID_USUARIO')->on('usuarios')->onDelete('set null');
            $table->foreign('ROTC_SUBIDO_POR')->references('ID_USUARIO')->on('usuarios')->onDelete('set null');
            $table->foreign('RACDA_SUBIDO_POR')->references('ID_USUARIO')->on('usuarios')->onDelete('set null');
            $table->foreign('PROPIEDAD_SUBIDO_POR')->references('ID_USUARIO')->on('usuarios')->onDelete('set null');
            $table->foreign('ADICIONAL_SUBIDO_POR')->references('ID_USUARIO')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentacion', function (Blueprint $table) {
            $table->dropForeign(['POLIZA_SUBIDO_POR']);
            $table->dropForeign(['ROTC_SUBIDO_POR']);
            $table->dropForeign(['RACDA_SUBIDO_POR']);
            $table->dropForeign(['PROPIEDAD_SUBIDO_POR']);
            $table->dropForeign(['ADICIONAL_SUBIDO_POR']);
            
            $table->dropColumn([
                'POLIZA_SUBIDO_POR',
                'POLIZA_FECHA_SUBIDA',
                'ROTC_SUBIDO_POR',
                'ROTC_FECHA_SUBIDA',
                'RACDA_SUBIDO_POR',
                'RACDA_FECHA_SUBIDA',
                'PROPIEDAD_SUBIDO_POR',
                'PROPIEDAD_FECHA_SUBIDA',
                'ADICIONAL_SUBIDO_POR',
                'ADICIONAL_FECHA_SUBIDA'
            ]);
        });
    }
};
