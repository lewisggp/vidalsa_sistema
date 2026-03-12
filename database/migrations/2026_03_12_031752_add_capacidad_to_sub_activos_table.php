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
        Schema::table('sub_activos', function (Blueprint $table) {
            $table->string('capacidad', 80)->nullable()->after('modelo');
        });
    }

    public function down(): void
    {
        Schema::table('sub_activos', function (Blueprint $table) {
            $table->dropColumn('capacidad');
        });
    }
};
