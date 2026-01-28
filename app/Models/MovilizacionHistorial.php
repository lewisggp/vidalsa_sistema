<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovilizacionHistorial extends Model
{
    protected $table = 'movilizacion_historial';
    protected $primaryKey = 'ID_MOVILIZACION';

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'ID_EQUIPO', 'ID_EQUIPO');
    }

    public function frenteOrigen()
    {
        return $this->belongsTo(FrenteTrabajo::class, 'ID_FRENTE_ORIGEN', 'ID_FRENTE');
    }

    public function frenteDestino()
    {
        return $this->belongsTo(FrenteTrabajo::class, 'ID_FRENTE_DESTINO', 'ID_FRENTE');
    }
}
