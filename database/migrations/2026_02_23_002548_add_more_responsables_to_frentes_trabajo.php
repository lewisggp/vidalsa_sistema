<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('frentes_trabajo', function (Blueprint $table) {
            $table->string('RESP_3_NOM')->nullable()->after('RESP_2_CAR');
            $table->string('RESP_3_CAR')->nullable()->after('RESP_3_NOM');
            $table->string('RESP_4_NOM')->nullable()->after('RESP_3_CAR');
            $table->string('RESP_4_CAR')->nullable()->after('RESP_4_NOM');

            $table->string('RESP_1_EQU')->nullable()->after('RESP_4_CAR');
            $table->string('RESP_2_EQU')->nullable()->after('RESP_1_EQU');
            $table->string('RESP_3_EQU')->nullable()->after('RESP_2_EQU');
            $table->string('RESP_4_EQU')->nullable()->after('RESP_3_EQU');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frentes_trabajo', function (Blueprint $table) {
            $table->dropColumn([
                'RESP_3_NOM',
                'RESP_3_CAR',
                'RESP_4_NOM',
                'RESP_4_CAR',
                'RESP_1_EQU',
                'RESP_2_EQU',
                'RESP_3_EQU',
                'RESP_4_EQU'
            ]);
        });
    }
};
