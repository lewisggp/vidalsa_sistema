<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudMantenimiento extends Model
{
    protected $table = 'solicitudes_mantenimiento';
    protected $primaryKey = 'ID_SOLICITUD';

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'ID_EQUIPO', 'ID_EQUIPO');
    }

    public function frente()
    {
        return $this->belongsTo(FrenteTrabajo::class, 'ID_FRENTE_ORIGEN', 'ID_FRENTE');
    }

    public function usuarioSolicita()
    {
        return $this->belongsTo(Usuario::class, 'ID_USUARIO_SOLICITA', 'ID_USUARIO');
    }

    public function items()
    {
        return $this->hasMany(SolicitudMaterialesItem::class, 'ID_SOLICITUD', 'ID_SOLICITUD');
    }
}
