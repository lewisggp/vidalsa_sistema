<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('menu');
    }
    return view('auth.inicio_sesion');
})->name('login');

Route::get('/login', function () {
    return redirect()->route('login');
});

// Lightweight route to refresh CSRF token (Handshake)
Route::get('/refresh-csrf', function () {
    return csrf_token();
});

Route::post('/', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login.post');
Route::redirect('/home', '/menu');

Route::middleware(['auth'])->group(function () {
    // Password Change Routes (Excluded from password check loop)
    Route::get('/admin/cambiar-clave', [App\Http\Controllers\Auth\ChangePasswordController::class, 'show'])->name('password.change');
    Route::post('/admin/cambiar-clave', [App\Http\Controllers\Auth\ChangePasswordController::class, 'update'])->name('password.update');

    Route::middleware(['password.change.check'])->group(function () {
        Route::get('/menu', [App\Http\Controllers\DashboardController::class, 'index'])->name('menu');

        Route::prefix('admin')->group(function () {
            Route::resource('usuarios', App\Http\Controllers\UserController::class)->except(['show']);
            Route::get('frentes/buscar', [App\Http\Controllers\FrenteTrabajoController::class, 'search'])->name('frentes.search');
            Route::resource('frentes', App\Http\Controllers\FrenteTrabajoController::class)->except(['show']);
            Route::patch('equipos/{id}/status', [App\Http\Controllers\EquipoController::class, 'changeStatus'])->name('equipos.changeStatus');
            Route::post('equipos/{id}/upload-doc', [App\Http\Controllers\EquipoController::class, 'uploadDoc'])->name('equipos.uploadDoc');
            Route::delete('equipos/{id}/delete-doc', [App\Http\Controllers\EquipoController::class, 'deleteDoc'])->name('equipos.deleteDoc');
            Route::get('equipos/export', [App\Http\Controllers\EquipoController::class, 'export'])->name('equipos.export');
            Route::get('equipos/search-field', [App\Http\Controllers\EquipoController::class, 'searchField'])->name('equipos.searchField');
            Route::get('equipos/search-specs', [App\Http\Controllers\EquipoController::class, 'searchSpecs'])->name('equipos.searchSpecs');
            Route::get('equipos/check-unique', [App\Http\Controllers\EquipoController::class, 'checkUniqueness'])->name('equipos.checkUnique');
            Route::get('equipos/{id}/metadata', [App\Http\Controllers\EquipoController::class, 'metadata'])->name('equipos.metadata');
            Route::post('equipos/{id}/update-metadata', [App\Http\Controllers\EquipoController::class, 'updateMetadata'])->name('equipos.updateMetadata');
            Route::post('equipos/bulk-mobilize', [App\Http\Controllers\MovilizacionController::class, 'bulkStore'])->name('equipos.bulkMobilize');
            Route::resource('equipos', App\Http\Controllers\EquipoController::class);
            Route::resource('movilizaciones', App\Http\Controllers\MovilizacionController::class);
            Route::patch('movilizaciones/{id}/status', [App\Http\Controllers\MovilizacionController::class, 'updateStatus'])->name('movilizaciones.updateStatus');
            Route::resource('catalogo', App\Http\Controllers\CaracteristicaModeloController::class);
        });
    });
});

// Route replaced by root POST
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Google Drive File Proxy (Extreme Optimization with Full Range Support)
Route::middleware(['auth'])->get('storage/google/{path}', function ($path) {
    try {
        $fileId = $path;
        $cachePath = 'google_cache/' . $fileId;

        // 1. CHECK LOCAL CACHE (Fastest, Offline-capable)
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($cachePath)) {
            $path = \Illuminate\Support\Facades\Storage::disk('local')->path($cachePath);
            $mime = mime_content_type($path);
            
            return response()->file($path, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=2592000',
                'Pragma' => 'public',
                'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 2592000),
            ]);
        }

        // 2. FETCH FROM GOOGLE (If not in cache)
        $driveService = \App\Services\GoogleDriveService::getInstance();
        
        // Metadata (Cached for 1 day)
        $metadata = \Illuminate\Support\Facades\Cache::remember('gdrive_meta_' . $fileId, 86400, function() use ($driveService, $fileId) {
            $drive = $driveService->getDrive();
            $file = $drive->files->get($fileId, [
                'fields' => 'mimeType,size,modifiedTime',
                'supportsAllDrives' => true
            ]);
            return [
                'mime' => $file->getMimeType(),
                'size' => $file->getSize() ?: 0,
            ];
        });

        // 3. DOWNLOAD & SAVE TO LOCAL CACHE
        $stream = $driveService->getStreamById($fileId);
        \Illuminate\Support\Facades\Storage::disk('local')->put($cachePath, $stream);
        
        // 4. SERVE FROM LOCAL CACHE
        $localPath = \Illuminate\Support\Facades\Storage::disk('local')->path($cachePath);
        
        return response()->file($localPath, [
            'Content-Type' => $metadata['mime'],
            'Cache-Control' => 'public, max-age=2592000',
            'Pragma' => 'public',
            'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 2592000),
        ]);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Google Drive fetch error: ' . $e->getMessage());
        
        // If local cache failed or GDrive failed, return 404 image placeholder logic could go here
        abort(404, 'Imagen no disponible');
    }
})->where('path', '.*')->name('drive.file');
