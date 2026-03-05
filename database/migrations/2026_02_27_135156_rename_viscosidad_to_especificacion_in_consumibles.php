<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Renombrar VISCOSIDAD → ESPECIFICACION
     *
     * Este campo almacena información específica del consumible según su tipo:
     *   - ACEITE_MOTOR / ACEITE_CAJA / ACEITE_HIDR → Viscosidad/grado (15W-40, SAE 90...)
     *   - CAUCHO                                    → Medida del caucho (11R22.5, 295/80R22.5...)
     *   - Otros tipos                               → NULL
     */
    public function up(): void
    {
        Schema::table('consumibles', function (Blueprint $table) {
            $table->renameColumn('VISCOSIDAD', 'ESPECIFICACION');
        });
    }

    public function down(): void
    {
        Schema::table('consumibles', function (Blueprint $table) {
            $table->renameColumn('ESPECIFICACION', 'VISCOSIDAD');
        });
    }
};
