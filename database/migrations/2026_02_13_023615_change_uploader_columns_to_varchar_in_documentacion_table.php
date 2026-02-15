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
            $table->string('PROPIEDAD_SUBIDO_POR')->nullable()->change();
            $table->string('POLIZA_SUBIDO_POR')->nullable()->change();
            $table->string('ROTC_SUBIDO_POR')->nullable()->change();
            $table->string('RACDA_SUBIDO_POR')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentacion', function (Blueprint $table) {
            $table->integer('PROPIEDAD_SUBIDO_POR')->nullable()->change();
            $table->integer('POLIZA_SUBIDO_POR')->nullable()->change();
            $table->integer('ROTC_SUBIDO_POR')->nullable()->change();
            $table->integer('RACDA_SUBIDO_POR')->nullable()->change();
        });
    }
};
