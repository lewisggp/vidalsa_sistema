<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'ID_USUARIO';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'NOMBRE_COMPLETO',
        'CORREO_ELECTRONICO',
        'PASSWORD_HASH',
        'ID_ROL',
        'SESSION_TOKEN',
        'NIVEL_ACCESO',
        'ID_FRENTE_ASIGNADO',
        'ESTATUS',
        'PERMISOS',
        'REQUIERE_CAMBIO_CLAVE',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'PASSWORD_HASH',
        'SESSION_TOKEN',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'PERMISOS' => 'array', // Comentado para usar accessor/mutator para columna SET
    ];

    /**
     * Get the permissions as an array.
     */
    public function getPermisosAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    /**
     * Set the permissions from an array to a comma-separated string.
     */
    public function setPermisosAttribute($value)
    {
        $this->attributes['PERMISOS'] = is_array($value) ? implode(',', $value) : $value;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->PASSWORD_HASH;
    }

    public function rol()
    {
        return $this->belongsTo(Role::class, 'ID_ROL', 'ID_ROL');
    }

    public function frenteAsignado()
    {
        return $this->belongsTo(FrenteTrabajo::class, 'ID_FRENTE_ASIGNADO', 'ID_FRENTE');
    }

    public function solicitudesMantenimiento()
    {
        return $this->hasMany(SolicitudMantenimiento::class, 'ID_USUARIO_SOLICITA', 'ID_USUARIO');
    }

    /**
     * Get the access level as descriptive text.
     *
     * @return string
     */
    public function getNivelAccesoTextoAttribute()
    {
        $niveles = [
            1 => 'GLOBAL',
            2 => 'LOCAL'
        ];
        
        return $niveles[$this->NIVEL_ACCESO] ?? 'Desconocido';
    }
    /**
     * Determine if the entity has the given abilities.
     *
     * @param  iterable|string  $abilities
     * @param  array|mixed  $arguments
     * @return bool
     */
    public function can($abilities, $arguments = []): bool
    {
        // 1. Si es un permiso de nuestro sistema (string), verificar en columna PERMISOS
        if (is_string($abilities)) {
            $permisos = $this->PERMISOS; // Esto ya usa el accessor getPermisosAttribute (array)
            
            // Si tiene el permiso exacto
            if (in_array($abilities, $permisos)) {
                return true;
            }
            
            // Si es Super Administrador (opcional, pero común)
            // if ($this->ID_ROL === 1) return true;
        }

        // 2. Delegar al comportamiento estándar de Laravel (Gates/Policies)
        return parent::can($abilities, $arguments);
    }
}
