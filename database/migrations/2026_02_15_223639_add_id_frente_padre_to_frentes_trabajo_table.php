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
        Schema::table('frentes_trabajo', function (Blueprint $table) {
            if (!Schema::hasColumn('frentes_trabajo', 'ID_FRENTE_PADRE')) {
                $table->unsignedBigInteger('ID_FRENTE_PADRE')->nullable()->after('ESTATUS_FRENTE');
                $table->foreign('ID_FRENTE_PADRE')->references('ID_FRENTE')->on('frentes_trabajo')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frentes_trabajo', function (Blueprint $table) {
            $table->dropForeign(['ID_FRENTE_PADRE']);
            $table->dropColumn('ID_FRENTE_PADRE');
        });
    }
};
