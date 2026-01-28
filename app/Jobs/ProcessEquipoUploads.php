<?php

namespace App\Jobs;

use App\Models\Equipo;
use App\Models\Documentacion;
use App\Models\CaracteristicaModelo;
use App\Services\GoogleDriveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessEquipoUploads implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 30;

    protected $equipoId;
    protected $filesToUpload; 

    /**
     * Create a new job instance.
     *
     * @param int $equipoId
     * @param array $filesToUpload
     */
    public function __construct($equipoId, array $filesToUpload)
    {
        $this->equipoId = $equipoId;
        $this->filesToUpload = $filesToUpload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $equipo = Equipo::with('documentacion')->find($this->equipoId);
        if (!$equipo) {
            Log::error("ProcessEquipoUploads: Equipo ID {$this->equipoId} not found.");
            return;
        }

        $driveService = GoogleDriveService::getInstance();
        $rootFolderId = $driveService->getRootFolderId();

        // Folders Configuration
        $folders = [
            'foto_equipo' => '1Pmm9WI6YSi6Wb6-2_L0D5wk5whHs-mCf',
            'foto_referencial' => '1KWEYWqnPjmJxz1XpR8U-Jto8KQT9RSsy',
            
            // Map Docs to Root (or Default) explicitly
            'doc_propiedad' => $rootFolderId,
            'poliza_seguro' => $rootFolderId,
            'doc_rotc' => $rootFolderId,
            'doc_racda' => $rootFolderId,
            
            'default' => $rootFolderId
        ];

        foreach ($this->filesToUpload as $fileData) {
            try {
                $type = $fileData['type'];
                $localPath = $fileData['path']; 
                
                // Use Storage::disk('local') to match Controller force-store
                if (!Storage::disk('local')->exists($localPath)) {
                     Log::warning("ProcessEquipoUploads: File missing from LOCAL storage: {$localPath}");
                     continue;
                }
                $fullLocalPath = Storage::disk('local')->path($localPath);

                // Determine Folder
                $targetFolderId = $folders[$type] ?? $folders['default'];

                // Prepare Upload Object (Acting as UploadedFile)
                $fileObject = new \Illuminate\Http\File($fullLocalPath);
                
                // Upload
                $driveFile = $driveService->uploadFile(
                    $targetFolderId, 
                    $fileObject, 
                    $fileData['originalName'], 
                    $fileData['mime']
                );

                if ($driveFile && isset($driveFile->id)) {
                    $publicUrl = '/storage/google/' . $driveFile->id;
                    $this->updateRecord($equipo, $type, $publicUrl);
                }

                // Cleanup Local (Using Storage facade is safer)
                Storage::disk('local')->delete($localPath);

            } catch (\Exception $e) {
                Log::error("ProcessEquipoUploads Error ({$type}): " . $e->getMessage());
                // Rethrow to trigger retry logic if it's a transient error?
                // For now, we log and continue to next file to avoid blocking others.
                // If we want retry per file, we'd need job per file.
                // But we want to retry the WHOLE job if a network error occurs.
                throw $e; 
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("ProcessEquipoUploads FAILED completely for Equipo ID {$this->equipoId}: " . $exception->getMessage());
        // Here we could notify the user via DB Notification
    }

    protected function updateRecord($equipo, $type, $url)
    {
        switch ($type) {
            case 'foto_equipo':
                $equipo->update(['FOTO_EQUIPO' => $url]);
                break;
                
            case 'foto_referencial':
                if ($equipo->ID_ESPEC) {
                    $espec = CaracteristicaModelo::find($equipo->ID_ESPEC);
                    if ($espec) $espec->update(['FOTO_REFERENCIAL' => $url]);
                }
                break;
                
            case 'doc_propiedad':
                $this->updateDoc($equipo, 'LINK_DOC_PROPIEDAD', $url);
                break;
            case 'poliza_seguro':
                $this->updateDoc($equipo, 'LINK_POLIZA_SEGURO', $url);
                break;
            case 'doc_rotc':
                $this->updateDoc($equipo, 'LINK_ROTC', $url);
                break;
            case 'doc_racda':
                $this->updateDoc($equipo, 'LINK_RACDA', $url);
                break;
        }
    }

    protected function updateDoc($equipo, $col, $url)
    {
        if ($equipo->documentacion) {
            $equipo->documentacion->update([$col => $url]);
        } else {
            // Should exist from Controller, but just in case
            Documentacion::create([
                'ID_EQUIPO' => $equipo->ID_EQUIPO,
                $col => $url
            ]);
        }
    }
}
