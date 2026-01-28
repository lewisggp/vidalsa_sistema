<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Responsable extends Model
{
    protected $table = 'responsable';
    protected $primaryKey = 'ID_ASIGNACION';

    protected $fillable = [
        'ID_EQUIPO',
        'CEDULA_RESPONSABLE',
        'PERSONA_ASIGNADA',
        'FECHA_ASIGNACION'
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'ID_EQUIPO', 'ID_EQUIPO');
    }
}
