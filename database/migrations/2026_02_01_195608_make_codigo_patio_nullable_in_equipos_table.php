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
            $table->string('CODIGO_PATIO')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            // We cannot easily revert to non-nullable if there are nulls, 
            // but we can try making it nullable(false) if needed.
            // For now, it's safer to leave as is or basic revert.
            $table->string('CODIGO_PATIO')->nullable(false)->change();
        });
    }
};
