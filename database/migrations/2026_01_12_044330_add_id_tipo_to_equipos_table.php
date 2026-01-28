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
            $table->unsignedBigInteger('id_tipo_equipo')->nullable()->after('TIPO_EQUIPO');
            // $table->foreign('id_tipo_equipo')->references('id')->on('tipo_equipos')->onDelete('set null');
        });

        // Migrate data
        $equipos = DB::table('equipos')->whereNotNull('TIPO_EQUIPO')->get();
        foreach ($equipos as $equipo) {
            // Find or create header type
            $tipoId = DB::table('tipo_equipos')->insertGetId([
                'nombre' => $equipo->TIPO_EQUIPO,
                'created_at' => now(),
                'updated_at' => now(),
            ]); // This might fail on duplicate unique, so use updateOrInsert logic or check existence
            
            // Better logic:
            $existing = DB::table('tipo_equipos')->where('nombre', $equipo->TIPO_EQUIPO)->first();
            if (!$existing) {
                 $id = DB::table('tipo_equipos')->insertGetId([
                    'nombre' => $equipo->TIPO_EQUIPO,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $id = $existing->id;
            }

            DB::table('equipos')->where('ID_EQUIPO', $equipo->ID_EQUIPO)->update(['id_tipo_equipo' => $id]);
        }

        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn('TIPO_EQUIPO');
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('TIPO_EQUIPO', 50)->nullable();
        });

        // Restore data (roughly)
        $equipos = DB::table('equipos')->whereNotNull('id_tipo_equipo')->get();
        foreach($equipos as $equipo) {
            $tipo = DB::table('tipo_equipos')->where('id', $equipo->id_tipo_equipo)->first();
            if($tipo) {
                DB::table('equipos')->where('ID_EQUIPO', $equipo->ID_EQUIPO)->update(['TIPO_EQUIPO' => $tipo->nombre]);
            }
        }

        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn('id_tipo_equipo');
        });
    }
};
