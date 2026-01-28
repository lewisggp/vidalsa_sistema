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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('ID_USUARIO');
            $table->string('NOMBRE_COMPLETO', 150);
            $table->string('CORREO_ELECTRONICO', 150)->unique();
            $table->string('PASSWORD_HASH', 255)->comment('Contraseña encriptada');
            $table->unsignedBigInteger('ID_ROL')->nullable();
            $table->string('SESSION_TOKEN', 500)->nullable()->comment('Token para validación de sesión activa');
            $table->integer('NIVEL_ACCESO')->default(2);
            $table->unsignedBigInteger('ID_FRENTE_ASIGNADO')->nullable();
            $table->enum('ESTATUS', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->string('PERMISOS', 255)->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('ID_ROL')->references('ID_ROL')->on('roles')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('ID_FRENTE_ASIGNADO')->references('ID_FRENTE')->on('frentes_trabajo')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
