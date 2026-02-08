<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documentacion extends Model
{
    protected $table = 'documentacion';
    protected $primaryKey = 'ID_EQUIPO';
    public $incrementing = false;

    protected $fillable = [
        'ID_EQUIPO',
        'NRO_DE_DOCUMENTO',
        'PLACA',
        'NOMBRE_DEL_TITULAR',
        'LINK_DOC_PROPIEDAD',
        'ID_SEGURO',
        'ESTADO_POLIZA',
        'FECHA_VENC_POLIZA',
        'LINK_POLIZA_SEGURO',
        'FECHA_ROTC',
        'LINK_ROTC',
        'FECHA_RACDA',
        'LINK_RACDA',
        'LINK_DOC_ADICIONAL'
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'ID_EQUIPO', 'ID_EQUIPO');
    }

    public function seguro()
    {
        return $this->belongsTo(CatalogoSeguro::class, 'ID_SEGURO', 'ID_SEGURO');
    }
}
