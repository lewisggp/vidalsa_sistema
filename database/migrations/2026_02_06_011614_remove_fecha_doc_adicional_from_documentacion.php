<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentacion', function (Blueprint $table) {
            $table->dropColumn('FECHA_DOC_ADICIONAL');
        });
    }

    public function down(): void
    {
        Schema::table('documentacion', function (Blueprint $table) {
            $table->date('FECHA_DOC_ADICIONAL')->nullable()->after('LINK_DOC_ADICIONAL');
        });
    }
};
