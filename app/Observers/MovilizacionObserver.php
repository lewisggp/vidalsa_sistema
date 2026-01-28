<?php

namespace App\Observers;

use App\Models\Movilizacion;
use Illuminate\Support\Facades\Cache;

class MovilizacionObserver
{
    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    public $afterCommit = true;

    /**
     * Handle the Movilizacion "created" event.
     */
    public function created(Movilizacion $movilizacion): void
    {
        $this->refreshCache();
    }

    /**
     * Handle the Movilizacion "updated" event.
     */
    public function updated(Movilizacion $movilizacion): void
    {
        $this->refreshCache();
    }

    /**
     * Handle the Movilizacion "deleted" event.
     */
    public function deleted(Movilizacion $movilizacion): void
    {
        $this->refreshCache();
    }

    /**
     * Force refresh the dashboard cache.
     */
    private function refreshCache(): void
    {
        // ESTADO_MVO = 'TRANSITO' is what we count as "Pendientes" or "En Progreso" for the dashboard
        Cache::forever('dashboard_pendientes', Movilizacion::where('ESTADO_MVO', 'TRANSITO')->count());
        
        // Cache movilizaciones today (date-based, will auto-refresh when day changes)
        Cache::forever('dashboard_movilizaciones_hoy', Movilizacion::whereDate('FECHA_DESPACHO', now()->today())->count());
        
        // Cache recent activity (last 5 pending mobilizations)
        $recentActivity = Movilizacion::with(['equipo', 'frenteDestino'])
            ->where('ESTADO_MVO', 'TRANSITO')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        Cache::forever('dashboard_recent_activity', $recentActivity);
    }
}
