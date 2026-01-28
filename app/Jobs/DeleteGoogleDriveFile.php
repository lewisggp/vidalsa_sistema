<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteGoogleDriveFile implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $fileId;

    /**
     * Create a new job instance.
     *
     * @param string $fileId
     * @return void
     */
    public function __construct($fileId)
    {
        $this->fileId = $fileId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($this->fileId)) {
            return;
        }

        try {
            $driveService = \App\Services\GoogleDriveService::getInstance();
            $driveService->deleteFile($this->fileId);
            \Illuminate\Support\Facades\Log::info("Background Job: Deleted old Drive file {$this->fileId}");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Background Job Failed: Could not delete Drive file {$this->fileId}. Error: " . $e->getMessage());
        }
    }
}
