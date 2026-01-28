<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudMaterialesItem extends Model
{
    protected $table = 'solicitud_materiales_items';
    protected $primaryKey = 'ID_ITEM';

    public function solicitud()
    {
        return $this->belongsTo(SolicitudMantenimiento::class, 'ID_SOLICITUD', 'ID_SOLICITUD');
    }
}
