<?php

namespace App\Observers;

use App\Models\Equipo;
use Illuminate\Support\Facades\Cache;

class EquipoObserver
{
    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    public $afterCommit = true;

    /**
     * Handle the Equipo "created" event.
     */
    public function created(Equipo $equipo): void
    {
        $this->refreshCache();
    }

    /**
     * Handle the Equipo "updated" event.
     */
    public function updated(Equipo $equipo): void
    {
        $this->refreshCache();
    }

    /**
     * Handle the Equipo "deleted" event.
     */
    public function deleted(Equipo $equipo): void
    {
        $this->refreshCache();
    }

    /**
     * Handle the Equipo "restored" event.
     */
    public function restored(Equipo $equipo): void
    {
        $this->refreshCache();
    }

    /**
     * Force refresh the dashboard cache.
     */
    private function refreshCache(): void
    {
        // When an equipment changes, invalidate expired documents caches
        // They will be regenerated on next dashboard load
        Cache::forget('dashboard_total_alerts');
        Cache::forget('dashboard_expired_list_all');
    }
}
