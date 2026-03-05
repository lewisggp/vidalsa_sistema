<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Consumible extends Model
{
    protected $table      = 'consumibles';
    protected $primaryKey = 'ID_CONSUMIBLE';

    protected $fillable = [
        // Bloque 1 — del Excel
        'FECHA',
        'IDENTIFICADOR',
        'RESP_NOMBRE',
        'RESP_CI',
        'CANTIDAD',
        'RAW_ORIGEN',
        // Bloque 2 — del formulario
        'TIPO_CONSUMIBLE',
        'ESPECIFICACION',   // Aceites: viscosidad (15W-40, SAE90). Caucho: medida (11R22.5)
        'UNIDAD',
        'ID_FRENTE',
        // Bloque 3 — resueltos después
        'ID_EQUIPO',
        'ID_SUMINISTRO',
        'ESTADO_EQUIPO',
        'NOTAS',
    ];

    protected $casts = [
        'FECHA'    => 'date',
        'CANTIDAD' => 'decimal:2',
    ];

    // ── Relaciones ───────────────────────────────────────────────

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'ID_EQUIPO', 'ID_EQUIPO');
    }

    public function frente()
    {
        return $this->belongsTo(FrenteTrabajo::class, 'ID_FRENTE', 'ID_FRENTE');
    }

    public function suministro()
    {
        return $this->belongsTo(SuministroOrigen::class, 'ID_SUMINISTRO', 'ID_SUMINISTRO');
    }

    // ── Scopes para filtros rápidos ──────────────────────────────

    /**
     * Solo registros con equipo confirmado (para gráficos oficiales).
     */
    public function scopeConfirmados(Builder $query): Builder
    {
        return $query->where('ESTADO_EQUIPO', 'CONFIRMADO');
    }

    /**
     * Registros pendientes de identificar el equipo.
     */
    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('ESTADO_EQUIPO', 'PENDIENTE');
    }

    /**
     * Filtrar por rango de fechas.
     */
    public function scopePeriodo(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereBetween('FECHA', [$desde, $hasta]);
    }

    /**
     * Filtrar por tipo de consumible.
     */
    public function scopeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('TIPO_CONSUMIBLE', $tipo);
    }

    /**
     * Filtrar por frente.
     */
    public function scopeFrente(Builder $query, int $idFrente): Builder
    {
        return $query->where('ID_FRENTE', $idFrente);
    }

    // ── Labels útiles para vistas ────────────────────────────────

    public static function tiposLabel(): array
    {
        return [
            'GASOIL'       => 'Gasoil',
            'GASOLINA'     => 'Gasolina',
            'ACEITE'       => 'Aceite',
            'CAUCHO'       => 'Caucho',
            'REFRIGERANTE' => 'Refrigerante',
            'OTRO'         => 'Otro',
        ];
    }

    public static function unidadesPorTipo(): array
    {
        return [
            'GASOIL'       => 'LITROS',
            'GASOLINA'     => 'LITROS',
            'ACEITE'       => 'LITROS',
            'CAUCHO'       => 'UNIDADES',
            'REFRIGERANTE' => 'LITROS',
            'OTRO'         => 'LITROS',
        ];
    }

    public function getTipoLabelAttribute(): string
    {
        return self::tiposLabel()[$this->TIPO_CONSUMIBLE] ?? $this->TIPO_CONSUMIBLE;
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->ESTADO_EQUIPO) {
            'CONFIRMADO' => 'Confirmado',
            'PENDIENTE'  => 'Pendiente',
            'SIN_MATCH'  => 'Sin coincidencia',
            default      => $this->ESTADO_EQUIPO,
        };
    }
}
