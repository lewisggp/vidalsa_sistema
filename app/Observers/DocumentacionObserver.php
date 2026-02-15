<?php

namespace App\Observers;

use App\Models\Documentacion;
use App\Models\Equipo;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DocumentacionObserver
{
    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    public $afterCommit = true;

    /**
     * Handle the Documentacion "created" event.
     */
    public function created(Documentacion $documentacion): void
    {
        $this->refreshCache();
    }

    /**
     * Handle the Documentacion "updated" event.
     */
    public function updated(Documentacion $documentacion): void
    {
        $this->refreshCache();
    }

    /**
     * Handle the Documentacion "deleted" event.
     */
    public function deleted(Documentacion $documentacion): void
    {
        $this->refreshCache();
    }

    /**
     * Force refresh the dashboard cache for expired documents.
     * Regenerates the cache immediately instead of just invalidating.
     */
    private function refreshCache(): void
    {
        $now = Carbon::now();
        $in30Days = $now->copy()->addDays(30);
        
        // 1. Recalculate total alerts count (Expired + Warning within 30 days)
        $stats = Documentacion::selectRaw("
            COUNT(CASE WHEN FECHA_VENC_POLIZA < ? THEN 1 END) as poliza,
            COUNT(CASE WHEN FECHA_ROTC < ? THEN 1 END) as rotc,
            COUNT(CASE WHEN FECHA_RACDA < ? THEN 1 END) as racda
        ", [$in30Days, $in30Days, $in30Days])->first();
        
        $totalAlerts = ($stats->poliza ?? 0) + ($stats->rotc ?? 0) + ($stats->racda ?? 0);
        Cache::put('dashboard_total_alerts', $totalAlerts, $now->copy()->endOfDay());
        
        // 2. Regenerate alerts list using shared controller logic
        $controller = app(\App\Http\Controllers\DashboardController::class);
        $expiredList = $controller->generateAlertsList();
        
        Cache::put('dashboard_expired_list_v3', $expiredList, Carbon::now()->endOfDay());
    }
}
