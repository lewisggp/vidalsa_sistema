<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FrenteTrabajo;
use App\Models\Equipo;
use App\Models\CaracteristicaModelo;
use Illuminate\Support\Facades\DB;

class RegistroEquipoSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            // 1. Ensure Frente exists
            $frente = FrenteTrabajo::firstOrCreate(
                ['NOMBRE_FRENTE' => 'ANACO'],
                [
                    'UBICACION' => 'Anzoátegui, Anaco',
                    'RESP_1_NOM' => 'No Asignado',
                    'RESP_1_CAR' => 'Supervisor',
                    'ESTATUS_FRENTE' => 'ACTIVO'
                ]
            );

            // 2. Create Equipment
            $equipo = Equipo::updateOrCreate(
                ['SERIAL_CHASIS' => 'LZZ1ELSF1SJ413129'], // Unique key
                [
                    'CODIGO_PATIO' => 'VS-BH-STK-02',
                    'TIPO_EQUIPO' => 'VOLTEO', // Using the verbatim type from user. 
                    'MARCA' => 'SINOTRUK',
                    'MODELO' => 'ZZ3257V464JB1',
                    'ANIO' => 2025,
                    'SERIAL_DE_MOTOR' => '1425F022978',
                    'ESTADO_OPERATIVO' => 'OPERATIVO',
                    'ID_FRENTE_ACTUAL' => $frente->ID_FRENTE,
                    'CATEGORIA_FLOTA' => 'MAQUINARIA_PESADA', // Defaulting or inferring
                    'CONFIRMADO_EN_SITIO' => true
                ]
            );

            // 3. Create Specs (Optional but good to store model info)
            // Checking if specs exist for this model to avoid duplicates if possible, or just link if I had specs logic fully disjoint.
            // For now, I won't create a separate spec record unless I need to ID_ESPEC. 
            // The user didn't give technical specs like engine capacity etc, so I will skip creating CaracteristicaModelo 
            // and just rely on the Equipo table fields I just filled.
            
            $this->command->info("Equipo VS-BH-STK-02 registrado en frente ANACO.");
        });
    }
}
