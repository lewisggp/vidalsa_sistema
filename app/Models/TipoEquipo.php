<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEquipo extends Model
{
    protected $fillable = ['nombre', 'ROL_ANCLAJE'];

    /**
     * Helper para saber si este tipo de equipo puede remolcar otros.
     */
    public function esRemolcador()
    {
        return $this->ROL_ANCLAJE === 'REMOLCADOR';
    }

    /**
     * Helper para saber si este tipo de equipo debe ser remolcado.
     */
    public function esRemolcable()
    {
        return $this->ROL_ANCLAJE === 'REMOLCABLE';
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_tipo_equipo');
    }
}
