<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DespachoCombustible extends Model
{
    protected $table = 'despacho_combustible';
    protected $primaryKey = 'ID_DESPACHO';

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'ID_EQUIPO', 'ID_EQUIPO');
    }

    public function frente()
    {
        return $this->belongsTo(FrenteTrabajo::class, 'ID_FRENTE', 'ID_FRENTE');
    }
}
