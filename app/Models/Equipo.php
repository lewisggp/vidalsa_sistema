<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    protected $table = 'equipos';
    protected $primaryKey = 'ID_EQUIPO';

    protected $fillable = [
        'id_tipo_equipo', // Renamed from TIPO_EQUIPO
        'NUMERO_ETIQUETA',
        'CATEGORIA_FLOTA',
        'CODIGO_PATIO',
        'MARCA',
        'MODELO',
        'ANIO',
        'ID_ESPEC',
        'SERIAL_CHASIS',
        'SERIAL_DE_MOTOR',
        'LINK_GPS',
        'FOTO_EQUIPO',
        'ID_FRENTE_ACTUAL',
        'CONFIRMADO_EN_SITIO',
        'ESTADO_OPERATIVO',
        'ID_ANCLAJE'
    ];

    /**
     * Get the best available photo for the equipment.
     * Prioritizes the specific unit photo, falls back to the model catalog photo.
     */
    public function getFotoAttribute()
    {
        // Prioritize model catalog photo (requested look)
        if ($this->especificaciones && $this->especificaciones->FOTO_REFERENCIAL) {
            return asset($this->especificaciones->FOTO_REFERENCIAL);
        }

        // Fallback to specific unit photo
        if ($this->FOTO_EQUIPO) return asset($this->FOTO_EQUIPO);
        
        return null;
    }

    public function tipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo');
    }

    public function especificaciones()
    {
        return $this->belongsTo(CaracteristicaModelo::class, 'ID_ESPEC', 'ID_ESPEC');
    }
    public function frenteActual()
    {
        return $this->belongsTo(FrenteTrabajo::class, 'ID_FRENTE_ACTUAL', 'ID_FRENTE');
    }

    public function anclaje()
    {
        return $this->belongsTo(Equipo::class, 'ID_ANCLAJE', 'ID_EQUIPO');
    }

    public function equiposAnclados()
    {
        return $this->hasMany(Equipo::class, 'ID_ANCLAJE', 'ID_EQUIPO');
    }

    public function documentacion()
    {
        return $this->hasOne(Documentacion::class, 'ID_EQUIPO', 'ID_EQUIPO');
    }

    public function responsables()
    {
        return $this->hasMany(Responsable::class, 'ID_EQUIPO', 'ID_EQUIPO');
    }

    public function despachosCombustible()
    {
        return $this->hasMany(DespachoCombustible::class, 'ID_EQUIPO', 'ID_EQUIPO');
    }

    public function movilizaciones()
    {
        return $this->hasMany(MovilizacionHistorial::class, 'ID_EQUIPO', 'ID_EQUIPO');
    }

    public function solicitudesMantenimiento()
    {
        return $this->hasMany(SolicitudMantenimiento::class, 'ID_EQUIPO', 'ID_EQUIPO');
    }
}
