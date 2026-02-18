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
        Schema::table('equipos', function (Blueprint $table) {
            if (!Schema::hasColumn('equipos', 'NUMERO_ETIQUETA')) {
                $table->string('NUMERO_ETIQUETA', 50)->nullable()->after('id_tipo_equipo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn('NUMERO_ETIQUETA');
        });
    }
};
