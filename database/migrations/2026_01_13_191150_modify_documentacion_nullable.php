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
            $table->string('NRO_DE_DOCUMENTO', 100)->nullable()->change();
            $table->string('NOMBRE_DEL_TITULAR', 150)->nullable()->change();
            $table->unsignedBigInteger('ID_SEGURO')->nullable()->change();
            $table->string('ESTADO_POLIZA', 50)->nullable()->change();
            $table->date('FECHA_VENC_POLIZA')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentacion', function (Blueprint $table) {
            $table->string('NRO_DE_DOCUMENTO', 100)->nullable(false)->change();
            $table->string('NOMBRE_DEL_TITULAR', 150)->nullable(false)->change();
            $table->unsignedBigInteger('ID_SEGURO')->nullable(false)->change();
            $table->string('ESTADO_POLIZA', 50)->nullable(false)->change();
            $table->date('FECHA_VENC_POLIZA')->nullable(false)->change();
        });
    }
};
