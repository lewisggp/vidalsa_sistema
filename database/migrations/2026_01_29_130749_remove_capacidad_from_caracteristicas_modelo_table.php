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
        Schema::table('caracteristicas_modelo', function (Blueprint $table) {
            $table->dropColumn('CAPACIDAD');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caracteristicas_modelo', function (Blueprint $table) {
            $table->string('CAPACIDAD', 50)->nullable();
        });
    }
};
