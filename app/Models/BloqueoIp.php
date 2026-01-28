<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloqueoIp extends Model
{
    use HasFactory;

    protected $table = 'bloqueo_ip';
    protected $primaryKey = 'ID_BLOQUEO';

    protected $fillable = [
        'DIRECCION_IP',
        'CANTIDAD_INTENTOS',
        'ULTIMO_INTENTO',
        'BLOQUEO_PERMANENTE',
    ];

    protected $casts = [
        'ULTIMO_INTENTO' => 'datetime',
        'BLOQUEO_PERMANENTE' => 'boolean',
    ];
}
