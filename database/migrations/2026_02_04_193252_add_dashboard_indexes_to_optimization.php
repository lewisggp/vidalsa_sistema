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
        // Add indexes safely — skip if already exist (compatible with MySQL, SQLite, PostgreSQL)
        try {
            Schema::table('documentacion', function (Blueprint $table) {
                $table->index('FECHA_VENC_POLIZA');
            });
        } catch (\Throwable $e) { /* Index already exists — skip */ }

        try {
            Schema::table('documentacion', function (Blueprint $table) {
                $table->index('FECHA_ROTC');
            });
        } catch (\Throwable $e) { /* Index already exists — skip */ }

        try {
            Schema::table('documentacion', function (Blueprint $table) {
                $table->index('FECHA_RACDA');
            });
        } catch (\Throwable $e) { /* Index already exists — skip */ }

        try {
            Schema::table('equipos', function (Blueprint $table) {
                $table->index('id_tipo_equipo');
            });
        } catch (\Throwable $e) { /* Index already exists — skip */ }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes safely — skip if they don't exist (compatible with MySQL, SQLite, PostgreSQL)
        try {
            Schema::table('documentacion', function (Blueprint $table) {
                $table->dropIndex(['FECHA_VENC_POLIZA']);
            });
        } catch (\Throwable $e) { /* Index doesn't exist — skip */ }

        try {
            Schema::table('documentacion', function (Blueprint $table) {
                $table->dropIndex(['FECHA_ROTC']);
            });
        } catch (\Throwable $e) { /* Index doesn't exist — skip */ }

        try {
            Schema::table('documentacion', function (Blueprint $table) {
                $table->dropIndex(['FECHA_RACDA']);
            });
        } catch (\Throwable $e) { /* Index doesn't exist — skip */ }

        try {
            Schema::table('equipos', function (Blueprint $table) {
                $table->dropIndex(['id_tipo_equipo']);
            });
        } catch (\Throwable $e) { /* Index doesn't exist — skip */ }
    }
};
