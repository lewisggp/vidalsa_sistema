<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubActivo extends Model
{
    protected $table = 'sub_activos';

    protected $fillable = [
        'tipo',
        'serial',
        'marca',
        'modelo',
        'capacidad',
        'anio',
        'ID_FRENTE',
        'ID_EQUIPO_HOST',
        'estado',
        'observaciones',
    ];

    /** Labels legibles por tipo */
    public static function tiposLabel(): array
    {
        return [
            'MAQUINA_SOLDADURA' => 'Máquina de Soldadura',
            'PLANTA_ELECTRICA'  => 'Planta Eléctrica',
            'CONTENEDOR'        => 'Contenedor',
            'COMPRESOR'         => 'Compresor',
            'OTRO'              => 'Otro',
        ];
    }

    /** Icono material por tipo */
    public static function tiposIcono(): array
    {
        return [
            'MAQUINA_SOLDADURA' => 'construction',
            'PLANTA_ELECTRICA'  => 'bolt',
            'CONTENEDOR'        => 'inventory_2',
            'COMPRESOR'         => 'air',
            'OTRO'              => 'handyman',
        ];
    }

    public function getTipoLabelAttribute(): string
    {
        return self::tiposLabel()[$this->tipo] ?? $this->tipo;
    }

    public function getTipoIconoAttribute(): string
    {
        return self::tiposIcono()[$this->tipo] ?? 'handyman';
    }

    // ── Relaciones ────────────────────────────────────────────

    /** Frente donde está físicamente (para los sueltos) */
    public function frente()
    {
        return $this->belongsTo(FrenteTrabajo::class, 'ID_FRENTE', 'ID_FRENTE');
    }

    /** Vehículo que lo porta (nullable) */
    public function equipoHost()
    {
        return $this->belongsTo(Equipo::class, 'ID_EQUIPO_HOST', 'ID_EQUIPO');
    }
}
