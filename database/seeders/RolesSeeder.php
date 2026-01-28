<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['NOMBRE_ROL' => 'SUPER ADMIN'],
            ['NOMBRE_ROL' => 'ADMINISTRADOR DE CONTRATOS'],
            ['NOMBRE_ROL' => 'COORDINADOR LOGÍSTICO'],
            ['NOMBRE_ROL' => 'PLANIFICADOR'],
            ['NOMBRE_ROL' => 'SUP. MECÁNICA PESADA'],
            ['NOMBRE_ROL' => 'SUP. MECÁNICA LIVIANA'],
            ['NOMBRE_ROL' => 'SUP. DE TRANSPORTE'],
            ['NOMBRE_ROL' => 'INGENIERO RESIDENTE'],
            ['NOMBRE_ROL' => 'COORDINADOR MECÁNICO'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'NOMBRE_ROL' => $role['NOMBRE_ROL'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
