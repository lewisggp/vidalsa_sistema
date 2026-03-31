<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('frentes_trabajo', function (Blueprint $table) {
            $table->string('RESP_1_CED', 20)->nullable()->after('RESP_1_CAR');
            $table->string('RESP_2_CED', 20)->nullable()->after('RESP_2_CAR');
            $table->string('RESP_3_CED', 20)->nullable()->after('RESP_3_CAR');
            $table->string('RESP_4_CED', 20)->nullable()->after('RESP_4_CAR');
        });
    }

    public function down(): void
    {
        Schema::table('frentes_trabajo', function (Blueprint $table) {
            $table->dropColumn(['RESP_1_CED', 'RESP_2_CED', 'RESP_3_CED', 'RESP_4_CED']);
        });
    }
};
