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
        Schema::create('bloqueo_ip', function (Blueprint $table) {
            $table->id('ID_BLOQUEO');
            $table->string('DIRECCION_IP', 45)->unique()->comment('IP del equipo atacante');
            $table->integer('CANTIDAD_INTENTOS')->default(0)->comment('Contador de fallos');
            $table->timestamp('ULTIMO_INTENTO')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->boolean('BLOQUEO_PERMANENTE')->default(0)->comment('0: Acceso permitido, 1: Bloqueado permanentemente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloqueo_ip');
    }
};
