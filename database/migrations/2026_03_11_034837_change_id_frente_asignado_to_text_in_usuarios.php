<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Eliminar la FK constraint primero para poder cambiar el tipo
        DB::statement("ALTER TABLE usuarios DROP FOREIGN KEY usuarios_id_frente_asignado_foreign");

        // 2. Cambiar la columna de bigint FK a varchar para almacenar múltiples IDs separados por coma
        DB::statement("ALTER TABLE usuarios MODIFY ID_FRENTE_ASIGNADO VARCHAR(500) NULL");
    }

    public function down(): void
    {
        // Revertir a bigint sin FK (la FK original se perdió al migrar)
        Schema::table('usuarios', function (Blueprint $table) {
            $table->unsignedBigInteger('ID_FRENTE_ASIGNADO')->nullable()->change();
        });
    }
};
