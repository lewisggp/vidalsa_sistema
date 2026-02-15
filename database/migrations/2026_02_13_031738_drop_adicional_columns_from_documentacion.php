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
            $table->dropColumn(['ADICIONAL_SUBIDO_POR', 'ADICIONAL_FECHA_SUBIDA']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentacion', function (Blueprint $table) {
            $table->string('ADICIONAL_SUBIDO_POR')->nullable()->after('LINK_DOC_ADICIONAL');
            $table->timestamp('ADICIONAL_FECHA_SUBIDA')->nullable()->after('ADICIONAL_SUBIDO_POR');
        });
    }
};
