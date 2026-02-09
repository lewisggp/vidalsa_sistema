<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentacion', function (Blueprint $table) {
            $table->text('LINK_DOC_ADICIONAL')->nullable()->after('LINK_RACDA');
        });
    }

    public function down(): void
    {
        Schema::table('documentacion', function (Blueprint $table) {
            $table->dropColumn(['LINK_DOC_ADICIONAL']);
        });
    }
};
