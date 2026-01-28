<?php

namespace App\Observers;

use App\Models\Documentacion;
use Illuminate\Support\Facades\Cache;

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
     */
    private function refreshCache(): void
    {
        // Invalidate both caches - they will be regenerated on next dashboard load
        Cache::forget('dashboard_total_alerts');
        Cache::forget('dashboard_expired_list_all');
    }
}
