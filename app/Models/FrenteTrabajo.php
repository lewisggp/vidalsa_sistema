<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrenteTrabajo extends Model
{
    protected $table = 'frentes_trabajo';
    protected $primaryKey = 'ID_FRENTE';

    protected $fillable = [
        'NOMBRE_FRENTE',
        'UBICACION',
        'TIPO_FRENTE',
        'ESTATUS_FRENTE',
        'RESP_1_NOM',
        'RESP_1_CAR',
        'RESP_2_NOM',
        'RESP_2_CAR'
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'ID_FRENTE_ASIGNADO', 'ID_FRENTE');
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'ID_FRENTE_ACTUAL', 'ID_FRENTE');
    }

    public function despachoCombustible()
    {
        return $this->hasMany(DespachoCombustible::class, 'ID_FRENTE', 'ID_FRENTE');
    }

    public function movilizacionesOrigen()
    {
        return $this->hasMany(MovilizacionHistorial::class, 'ID_FRENTE_ORIGEN', 'ID_FRENTE');
    }

    public function movilizacionesDestino()
    {
        return $this->hasMany(MovilizacionHistorial::class, 'ID_FRENTE_DESTINO', 'ID_FRENTE');
    }

    public function solicitudesMantenimiento()
    {
        return $this->hasMany(SolicitudMantenimiento::class, 'ID_FRENTE_ORIGEN', 'ID_FRENTE');
    }
}
