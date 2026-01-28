<?php

namespace App\Http\Controllers;

use App\Models\CaracteristicaModelo;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CaracteristicaModeloController extends Controller
{
    public function index(Request $request)
    {
        $query = CaracteristicaModelo::query();

        // 1. Filter by Model
        if ($request->filled('modelo') && trim($request->modelo) !== '') {
            $query->where('MODELO', 'like', "%{$request->modelo}%");
        }

        // 2. Filter by Year
        if ($request->filled('anio') && trim($request->anio) !== '') {
            $query->where('ANIO_ESPEC', $request->anio);
        }

        // Stats Calculation (Based on current filters)
        $statsQuery = $query->clone();
        $totalCount = $statsQuery->count();

        // Group by Model (for Sidebar Stats)
        $modelCounts = $query->clone()
            ->select('MODELO', DB::raw('count(*) as count'))
            ->groupBy('MODELO')
            ->orderBy('count', 'desc')
            ->get();

        // Pagination
        $catalogos = $query->orderBy('MODELO', 'asc')->paginate(10);

        // --- Standardized Lists (Not Context-Aware to avoid confusion) ---
        // This matches Equipo logic: Load all available options regardless of current filter
        $availableModelos = CaracteristicaModelo::select('MODELO')->distinct()->orderBy('MODELO')->pluck('MODELO');
        $availableAnios = CaracteristicaModelo::select('ANIO_ESPEC')->distinct()->orderBy('ANIO_ESPEC', 'desc')->pluck('ANIO_ESPEC');

        // JSON Response for AJAX
        if ($request->wantsJson() && $request->has('ajax_load')) {
            $tableHtml = view('admin.catalogo.partials.table_rows', compact('catalogos'))->render();
            $paginationHtml = $catalogos->appends($request->all())->links()->toHtml();
            $statsHtml = view('admin.catalogo.partials.stats_sidebar', compact('totalCount', 'modelCounts'))->render();

            return response()->json([
                'html' => $tableHtml,
                'pagination' => $paginationHtml,
                'stats' => $statsHtml,
            ]);
        }

        return view('admin.catalogo.index', compact('catalogos', 'availableModelos', 'availableAnios', 'totalCount', 'modelCounts'));
    }

    public function create()
    {
        $distinctModelos = CaracteristicaModelo::select('MODELO')->distinct()->orderBy('MODELO')->pluck('MODELO');
        return view('admin.catalogo.create', compact('distinctModelos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'MODELO' => 'required|max:50',
            'ANIO_ESPEC' => 'required|integer',
            'MOTOR' => 'nullable|max:150',
            'CAPACIDAD' => 'nullable|max:50',
            'COMBUSTIBLE' => 'nullable|max:100',
            'CONSUMO_PROMEDIO' => 'nullable|max:50',
            'ACEITE_MOTOR' => 'nullable|max:100',
            'ACEITE_CAJA' => 'nullable|max:100',
            'LIGA_FRENO' => 'nullable|max:50',
            'REFRIGERANTE' => 'nullable|max:100',
            'TIPO_BATERIA' => 'nullable|max:100',
            'foto_referencial' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], $this->validationMessages(), $this->validationAttributes());

        try {
            DB::transaction(function () use ($request, $validated) {
                // Force Uppercase on all string fields
                $fieldsToUpper = ['MODELO', 'MOTOR', 'CAPACIDAD', 'COMBUSTIBLE', 'CONSUMO_PROMEDIO', 
                                  'ACEITE_MOTOR', 'ACEITE_CAJA', 'LIGA_FRENO', 'REFRIGERANTE', 'TIPO_BATERIA'];
                
                foreach ($fieldsToUpper as $field) {
                    if (isset($validated[$field]) && is_string($validated[$field])) {
                        $validated[$field] = strtoupper($validated[$field]);
                    }
                }
                $catalogo = CaracteristicaModelo::create($validated);

                if ($request->hasFile('foto_referencial')) {
                    $driveService = GoogleDriveService::getInstance();
                    $folderId = config('filesystems.disks.google.catalog_folder'); // Specific folder for model photos
                    $file = $request->file('foto_referencial');
                    $filename = 'catalog_' . time() . '_' . $catalogo->ID_ESPEC . '.' . $file->getClientOriginalExtension();
                    
                    $driveFile = $driveService->uploadFile($folderId, $file, $filename, $file->getMimeType());
                    
                    if ($driveFile && isset($driveFile->id)) {
                        $catalogo->update(['FOTO_REFERENCIAL' => '/storage/google/' . $driveFile->id]);
                    }
                }
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Modelo registrado correctamente en el catálogo.'
                ], 200);
            }

            return redirect()->route('catalogo.index')->with('success', 'Equipo registrado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error registrando modelo en catálogo: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar el modelo: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Error al registrar el modelo: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $catalogo = CaracteristicaModelo::findOrFail($id);
        return view('admin.catalogo.edit', compact('catalogo'));
    }

    public function update(Request $request, $id)
    {
        $catalogo = CaracteristicaModelo::findOrFail($id);

        $validated = $request->validate([
            'MODELO' => 'required|max:50',
            'ANIO_ESPEC' => 'required|integer',
            'MOTOR' => 'nullable|max:150',
            'CAPACIDAD' => 'nullable|max:50',
            'COMBUSTIBLE' => 'nullable|max:100',
            'CONSUMO_PROMEDIO' => 'nullable|max:50',
            'ACEITE_MOTOR' => 'nullable|max:100',
            'ACEITE_CAJA' => 'nullable|max:100',
            'LIGA_FRENO' => 'nullable|max:50',
            'REFRIGERANTE' => 'nullable|max:100',
            'TIPO_BATERIA' => 'nullable|max:100',
            'foto_referencial' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], $this->validationMessages(), $this->validationAttributes());

        try {
            DB::transaction(function () use ($request, $validated, $catalogo) {
                // Force Uppercase on all string fields
                $fieldsToUpper = ['MODELO', 'MOTOR', 'CAPACIDAD', 'COMBUSTIBLE', 'CONSUMO_PROMEDIO', 
                                  'ACEITE_MOTOR', 'ACEITE_CAJA', 'LIGA_FRENO', 'REFRIGERANTE', 'TIPO_BATERIA'];
                
                foreach ($fieldsToUpper as $field) {
                    if (isset($validated[$field]) && is_string($validated[$field])) {
                        $validated[$field] = strtoupper($validated[$field]);
                    }
                }
                $catalogo->update($validated);

                if ($request->hasFile('foto_referencial')) {
                    // Store old file ID for cleanup AFTER successful upload
                    $oldFileId = null;
                    if ($catalogo->FOTO_REFERENCIAL) {
                        $oldFileId = str_replace('/storage/google/', '', $catalogo->FOTO_REFERENCIAL);
                    }

                    // 1. UPLOAD NEW PHOTO TO GOOGLE DRIVE
                    $driveService = GoogleDriveService::getInstance();
                    $folderId = config('filesystems.disks.google.catalog_folder');
                    $file = $request->file('foto_referencial');
                    $filename = 'catalog_' . time() . '_' . $catalogo->ID_ESPEC . '.' . $file->getClientOriginalExtension();
                    
                    $driveFile = $driveService->uploadFile($folderId, $file, $filename, $file->getMimeType());
                    
                    if ($driveFile && isset($driveFile->id)) {
                        // 2. UPDATE DATABASE WITH NEW FILE ID
                        $catalogo->update(['FOTO_REFERENCIAL' => '/storage/google/' . $driveFile->id]);
                        
                        // 3. DELETE OLD FILE FROM GOOGLE DRIVE (Only after successful upload & DB update)
                        if ($oldFileId) {
                            try {
                                $driveService->deleteFile($oldFileId);
                                // Also invalidate local cache
                                \Illuminate\Support\Facades\Storage::disk('local')->delete('google_cache/' . $oldFileId);
                                \Illuminate\Support\Facades\Cache::forget('gdrive_meta_' . $oldFileId);
                            } catch (\Exception $e) {
                                // Log error but don't fail the entire operation
                                Log::warning('Failed to delete old Google Drive file: ' . $oldFileId . ' - ' . $e->getMessage());
                            }
                        }
                    }
                }
            });

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Modelo actualizado exitosamente', 'redirect' => route('catalogo.index')]);
            }

            return redirect()->route('catalogo.index')->with('success', 'Modelo actualizado exitosamente');
        } catch (\Exception $e) {
            Log::error('Error actualizando modelo en catálogo: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Error al actualizar el modelo: ' . $e->getMessage()], 500);
            }

            return back()->withInput()->with('error', 'Error al actualizar el modelo: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $catalogo = CaracteristicaModelo::findOrFail($id);
            
            // DELETE FROM GOOGLE DRIVE & INVALIDATE CACHE
            if ($catalogo->FOTO_REFERENCIAL) {
                $fileId = str_replace('/storage/google/', '', $catalogo->FOTO_REFERENCIAL);
                
                try {
                    // Delete from Google Drive
                    $driveService = GoogleDriveService::getInstance();
                    $driveService->deleteFile($fileId);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete Google Drive file during catalog deletion: ' . $fileId . ' - ' . $e->getMessage());
                }
                
                // Clean up local cache
                \Illuminate\Support\Facades\Storage::disk('local')->delete('google_cache/' . $fileId);
                \Illuminate\Support\Facades\Cache::forget('gdrive_meta_' . $fileId);
            }
            
            $catalogo->delete();

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Modelo eliminado del catálogo', 'redirect' => route('catalogo.index')]);
            }

            return redirect()->route('catalogo.index')->with('success', 'Modelo eliminado del catálogo');
        } catch (\Exception $e) {
            Log::error('Error eliminando modelo del catálogo: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json(['message' => 'No se puede eliminar porque está vinculado a otros registros.'], 500);
            }

            return back()->with('error', 'No se puede eliminar el modelo porque está vinculado a uno o más equipos.');
        }
    }

    private function validationMessages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'max' => 'El campo :attribute no debe exceder los :max caracteres o kilobytes.',
            'image' => 'El campo :attribute debe ser una imagen.',
            'mimes' => 'El campo :attribute debe ser de tipo: :values.',
        ];
    }

    private function validationAttributes()
    {
        return [
            'MODELO' => 'Modelo',
            'ANIO_ESPEC' => 'Año de Ficha',
            'MOTOR' => 'Motor',
            'CAPACIDAD' => 'Capacidad',
            'COMBUSTIBLE' => 'Combustible',
            'CONSUMO_PROMEDIO' => 'Consumo Promedio',
            'ACEITE_MOTOR' => 'Aceite de Motor',
            'ACEITE_CAJA' => 'Aceite de Caja',
            'LIGA_FRENO' => 'Liga de Freno',
            'REFRIGERANTE' => 'Refrigerante',
            'TIPO_BATERIA' => 'Tipo de Batería',
            'foto_referencial' => 'Foto PNG del Catálogo',
        ];
    }
}
