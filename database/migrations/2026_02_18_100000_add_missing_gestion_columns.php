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
            // POLIZA Gestion
            if (!Schema::hasColumn('documentacion', 'poliza_gestion_frente_id')) {
                $table->unsignedBigInteger('poliza_gestion_frente_id')->nullable();
                $table->timestamp('poliza_gestion_fecha')->nullable();
            }
            if (!Schema::hasColumn('documentacion', 'poliza_status')) {
                $table->enum('poliza_status', ['vigente', 'en_proceso', 'vencido'])->default('vigente');
            }
            if (!Schema::hasColumn('documentacion', 'poliza_frente_gestionando')) {
                $table->unsignedBigInteger('poliza_frente_gestionando')->nullable();
                $table->timestamp('poliza_fecha_inicio_gestion')->nullable();
            }

            // ROTC Gestion
            if (!Schema::hasColumn('documentacion', 'rotc_gestion_frente_id')) {
                $table->unsignedBigInteger('rotc_gestion_frente_id')->nullable();
                $table->timestamp('rotc_gestion_fecha')->nullable();
            }
            if (!Schema::hasColumn('documentacion', 'rotc_status')) {
                $table->enum('rotc_status', ['vigente', 'en_proceso', 'vencido'])->default('vigente');
            }
            if (!Schema::hasColumn('documentacion', 'rotc_frente_gestionando')) {
                $table->unsignedBigInteger('rotc_frente_gestionando')->nullable();
                $table->timestamp('rotc_fecha_inicio_gestion')->nullable();
            }

            // RACDA Gestion
             if (!Schema::hasColumn('documentacion', 'racda_gestion_frente_id')) {
                $table->unsignedBigInteger('racda_gestion_frente_id')->nullable();
                $table->timestamp('racda_gestion_fecha')->nullable();
            }
            if (!Schema::hasColumn('documentacion', 'racda_status')) {
                $table->enum('racda_status', ['vigente', 'en_proceso', 'vencido'])->default('vigente');
            }
            if (!Schema::hasColumn('documentacion', 'racda_frente_gestionando')) {
                $table->unsignedBigInteger('racda_frente_gestionando')->nullable();
                $table->timestamp('racda_fecha_inicio_gestion')->nullable();
            }

            // Foreign Keys (Opcional, si existen las tablas)
             // $table->foreign('poliza_gestion_frente_id')->references('id')->on('frentes_trabajo')->nullOnDelete();
             // $table->foreign('poliza_frente_gestionando')->references('id')->on('frentes_trabajo')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentacion', function (Blueprint $table) {
            $table->dropColumn([
                'poliza_gestion_frente_id', 'poliza_gestion_fecha', 'poliza_status', 'poliza_frente_gestionando', 'poliza_fecha_inicio_gestion',
                'rotc_gestion_frente_id', 'rotc_gestion_fecha', 'rotc_status', 'rotc_frente_gestionando', 'rotc_fecha_inicio_gestion',
                'racda_gestion_frente_id', 'racda_gestion_fecha', 'racda_status', 'racda_frente_gestionando', 'racda_fecha_inicio_gestion',
            ]);
        });
    }
};
