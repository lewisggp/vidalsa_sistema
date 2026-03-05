<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Migrar registros existentes con subtipos → ACEITE
        DB::statement("
            UPDATE consumibles
            SET TIPO_CONSUMIBLE = 'ACEITE'
            WHERE TIPO_CONSUMIBLE IN ('ACEITE_MOTOR','ACEITE_CAJA','ACEITE_HIDR')
        ");

        // 2. Reemplazar el ENUM — un solo tipo ACEITE
        DB::statement("
            ALTER TABLE consumibles
            MODIFY COLUMN TIPO_CONSUMIBLE
            ENUM('GASOIL','GASOLINA','ACEITE','CAUCHO','REFRIGERANTE','OTRO')
            NOT NULL
        ");
    }

    public function down(): void
    {
        // Restaurar enum original con los 3 subtipos
        DB::statement("
            ALTER TABLE consumibles
            MODIFY COLUMN TIPO_CONSUMIBLE
            ENUM('GASOIL','GASOLINA','ACEITE_MOTOR','ACEITE_CAJA','ACEITE_HIDR','ACEITE','CAUCHO','REFRIGERANTE','OTRO')
            NOT NULL
        ");
    }
};
