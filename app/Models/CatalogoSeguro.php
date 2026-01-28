<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoSeguro extends Model
{
    protected $table = 'catalogo_seguros';
    protected $primaryKey = 'ID_SEGURO';
    
    protected $fillable = ['NOMBRE_ASEGURADORA'];

    public function documentaciones()
    {
        return $this->hasMany(Documentacion::class, 'ID_SEGURO', 'ID_SEGURO');
    }
}
