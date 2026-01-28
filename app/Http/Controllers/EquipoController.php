<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\FrenteTrabajo;
use App\Models\CatalogoSeguro;
use App\Models\CaracteristicaModelo;
use App\Models\Documentacion;
use App\Models\Responsable;
use Illuminate\Http\Request;
use App\Models\TipoEquipo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EquipoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search_query');
        $equipos = Equipo::query();

        if ($request->filled('id_frente') && trim($request->id_frente) !== '' && $request->id_frente !== 'all') {
            $equipos->where('ID_FRENTE_ACTUAL', $request->id_frente);
        }

        if ($request->filled('id_tipo') && trim($request->id_tipo) !== '' && $request->id_tipo !== 'all') {
            $equipos->where('id_tipo_equipo', $request->id_tipo);
        }

        if ($request->filled('modelo') && trim($request->modelo) !== '') {
            $equipos->where('MODELO', $request->modelo);
        }

        if ($request->filled('marca') && trim($request->marca) !== '') {
            $equipos->where('MARCA', $request->marca);
        }

        if ($request->filled('anio') && trim($request->anio) !== '') {
            $equipos->where('ANIO', $request->anio);
        }

        if ($search) {
            // Smart Search: If hyphen detected, search only in CODIGO_PATIO (faster)
            if (strpos($search, '-') !== false) {
                $equipos->where('CODIGO_PATIO', 'like', "%{$search}%");
            } else {
                // Standard search across all columns
                $equipos->where(function ($q) use ($search) {
                    $q->where('SERIAL_CHASIS', 'like', "%{$search}%")
                      ->orWhereHas('documentacion', function ($d) use ($search) {
                          $d->where('PLACA', 'like', "%{$search}%");
                      })
                      ->orWhere('SERIAL_DE_MOTOR', 'like', "%{$search}%")
                      ->orWhere('CODIGO_PATIO', 'like', "%{$search}%");
                });
            }
        }

        // --- Documentation Filters ---
        if ($request->filled('filter_propiedad') && $request->filter_propiedad === 'true') {
            $equipos->whereHas('documentacion', function($q) {
                $q->whereNotNull('NRO_DOCUMENTO_PROPIEDAD');
            });
        }

        if ($request->filled('filter_poliza') && $request->filter_poliza === 'true') {
            $equipos->whereHas('documentacion', function($q) {
                $q->whereNotNull('SEGURO_ID')
                  ->whereNotNull('PATH_POLIZA');
            });
        }

        if ($request->filled('filter_rotc') && $request->filter_rotc === 'true') {
            $equipos->whereHas('documentacion', function($q) {
                $q->whereNotNull('PATH_ROTC');
            });
        }

        if ($request->filled('filter_racda') && $request->filter_racda === 'true') {
            $equipos->whereHas('documentacion', function($q) {
                $q->whereNotNull('PATH_RACDA');
            });
        }

        $equipos->select('equipos.*')
                ->leftJoin('tipo_equipos', 'equipos.id_tipo_equipo', '=', 'tipo_equipos.id')
                ->with(['documentacion.seguro', 'especificaciones', 'tipo', 'frenteActual'])
                ->orderBy('tipo_equipos.nombre', 'asc')
                ->orderBy('equipos.CODIGO_PATIO', 'asc');

        // Check if any filter is applied (with non-empty values)
        $hasFilter = $request->filled('id_frente') || $request->filled('id_tipo') || $request->filled('search_query') || $request->filled('modelo') || $request->filled('marca') || $request->filled('anio') || $request->filled('filter_propiedad') || $request->filled('filter_poliza') || $request->filled('filter_rotc') || $request->filled('filter_racda');

        if ($hasFilter) {
            $equipos = $equipos->get();
        } else {
             // Return empty collection to open the interface without showing any records initially
             $equipos = collect([]); 
        }
        
        $frentes = FrenteTrabajo::where('ESTATUS_FRENTE', 'ACTIVO')->orderBy('NOMBRE_FRENTE', 'asc')->get();
        $allTipos = TipoEquipo::orderBy('nombre', 'asc')->get();
        
        // Advanced Filter Lists (Optimized: Only needed for initial page load, not AJAX)
        $availableModelos = [];
        $availableMarcas = [];
        $availableAnios = [];
        if (!$request->wantsJson()) {
            $availableModelos = Equipo::distinct()->whereNotNull('MODELO')->orderBy('MODELO', 'asc')->pluck('MODELO');
            $availableMarcas = Equipo::distinct()->whereNotNull('MARCA')->orderBy('MARCA', 'asc')->pluck('MARCA');
            $availableAnios = Equipo::distinct()->whereNotNull('ANIO')->orderBy('ANIO', 'desc')->pluck('ANIO');
        }
        
        $stats = ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'mantenimiento' => 0];
        $tiposStats = collect([]);
        $frentesStats = []; // Ensure array or collection

        // Only calculate stats if filters are active and we have data
        if ($hasFilter) {
            // OPTIMIZATION: Calculate stats from the already loaded collection instead of hitting DB again
            // This reduces DB queries from ~5 to 1.
            
            $stats['total'] = $equipos->count();
            $stats['activos'] = $equipos->where('ESTADO_OPERATIVO', 'OPERATIVO')->count();
            $stats['inactivos'] = $equipos->whereIn('ESTADO_OPERATIVO', ['INOPERATIVO', 'DESINCORPORADO'])->count();
            $stats['mantenimiento'] = $equipos->where('ESTADO_OPERATIVO', 'EN MANTENIMIENTO')->count();

            // Calculate Tipos Stats from Collection
            $tiposStats = $equipos->groupBy('id_tipo_equipo')->map(function ($group) {
                // Get the first item to access relation (assuming eager loaded)
                $first = $group->first();
                return (object) [
                    'id_tipo_equipo' => $first->id_tipo_equipo,
                    'nombre' => $first->tipo->nombre ?? 'Sin Tipo', // Access relation
                    'total' => $group->count()
                ];
            })->sortBy('nombre')->values();

            // Calculate Frentes Stats from Collection (if needed for drilldown)
            if ($request->filled('id_tipo')) {
                $frentesStats = $equipos->whereNotNull('ID_FRENTE_ACTUAL')->groupBy('ID_FRENTE_ACTUAL')->map(function ($group) {
                    $first = $group->first();
                    return (object) [
                        'ID_FRENTE_ACTUAL' => $first->ID_FRENTE_ACTUAL,
                        'NOMBRE_FRENTE' => $first->frenteActual->NOMBRE_FRENTE ?? 'Sin Frente',
                        'total' => $group->count()
                    ];
                })->sortBy('NOMBRE_FRENTE')->values();
            }
        }

        if ($request->wantsJson()) {
            // When no filter is active, we want the UI to show '--' to indicate "waiting for filter"
            // specifically for the counter cards.
            $responseStats = $stats;
            if (!$hasFilter) {
                $responseStats = [
                    'total' => '--',
                    'activos' => '--',
                    'inactivos' => '--',
                    'mantenimiento' => '--'
                ];
            }

            return response()->json([
                'html' => view('admin.equipos.partials.table_rows', compact('equipos'))->render(),
                'pagination' => '',
                'stats' => $responseStats,
                'distribution' => view('admin.equipos.partials.distribution_stats', compact('frentesStats', 'tiposStats', 'hasFilter'))->render(), // Pass hasFilter explicitly
            ]);
        }

        return view('admin.equipos.index', compact('equipos', 'stats', 'frentes', 'allTipos', 'tiposStats', 'frentesStats', 'availableModelos', 'availableMarcas', 'availableAnios'));
    }

    public function export(Request $request)
    {
        $fileName = 'equipos_export_' . date('Y-m-d_H-i') . '.xls';

        $equipos = Equipo::query();

        // Apply same filters
        if ($request->filled('id_frente') && $request->id_frente != 'all') {
            $equipos->where('ID_FRENTE_ACTUAL', $request->id_frente);
        }
        if ($request->filled('id_tipo')) {
            $equipos->where('id_tipo_equipo', $request->id_tipo);
        }
        if ($request->filled('modelo')) {
            $equipos->where('MODELO', $request->modelo);
        }
        if ($request->filled('anio')) {
            $equipos->where('ANIO', $request->anio);
        }

        $search = $request->input('search_query');
        if ($search) {
            $equipos->where(function ($q) use ($search) {
                $q->where('SERIAL_CHASIS', 'like', "%{$search}%")
                  ->orWhereHas('documentacion', function ($d) use ($search) {
                      $d->where('PLACA', 'like', "%{$search}%");
                  })
                  ->orWhere('SERIAL_DE_MOTOR', 'like', "%{$search}%")
                  ->orWhere('CODIGO_PATIO', 'like', "%{$search}%");
            });
        }

        $equipos->with(['frenteActual', 'tipo', 'documentacion', 'especificaciones']);

        return response()->streamDownload(function () use ($equipos) {
            $handle = fopen('php://output', 'w');
            
            // Start HTML for Excel
            fwrite($handle, '<html xmlns:x="urn:schemas-microsoft-com:office:excel">');
            fwrite($handle, '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>');
            fwrite($handle, '<body>');
            fwrite($handle, '<table style="border-collapse: collapse;">');

            // Exact columns requested by user in order (DB Keys)
            $headers = ['FRENTE', 'TIPO', 'CATEGORIA_FLOTA', 'MARCA', 'MODELO', 'ANIO', 'CODIGO_PATIO', 'SERIAL_CHASIS', 'SERIAL_DE_MOTOR', 'ESTADO_OPERATIVO', 'PLACA', 'NRO_DE_DOCUMENTO', 'NOMBRE_DEL_TITULAR', 'ESTADO_POLIZA', 'FECHA_VENC_POLIZA'];
            
            // Display Labels (Mapped 1:1)
            $labels = ['FRENTE', 'TIPO', 'CATEGORÍA', 'MARCA', 'MODELO', 'AÑO', 'CÓDIGO', 'SERIAL CHASIS', 'SERIAL MOTOR', 'ESTATUS', 'PLACA', 'NRO DOCUMENTO', 'TITULAR', 'ESTADO PÓLIZA', 'VENCIMIENTO PÓLIZA'];
            
            // --- MAIN TITLE ROW ---
            $currentDate = date('d/m/Y');
            fwrite($handle, '<thead>');
            fwrite($handle, '<tr>');
            // Colspan 15 for 15 columns
            fwrite($handle, '<th colspan="15" style="text-align: center; font-weight: bold; font-size: 22px; height: 60px; vertical-align: middle; border: thin solid #000000; background-color: #003366; color: #ffffff;">REPORTE DE ASIGNACIÓN DE EQUIPOS Y MAQUINARIA PARA LA FECHA ' . $currentDate . '</th>');
            fwrite($handle, '</tr>');

            // Render Header Row with Styles
            fwrite($handle, '<tr style="height: 30px;">');
            foreach ($labels as $hdr) {
                // Style: Blue bg, White text, Bold, Black Border (thin)
                fwrite($handle, '<th style="background-color: #003366; color: #ffffff; font-weight: bold; border: thin solid #000000; padding: 5px;">' . $hdr . '</th>');
            }
            fwrite($handle, '</tr></thead>');

            fwrite($handle, '<tbody>');
            
            $equipos->chunk(200, function ($chunk) use ($handle, $headers) {
                foreach ($chunk as $equipo) {
                    fwrite($handle, '<tr>');
                    foreach ($headers as $col) {
                        $val = '';
                        // Map headers to data
                        switch ($col) {
                            case 'FRENTE':
                                $val = $equipo->frenteActual ? $equipo->frenteActual->NOMBRE_FRENTE : '';
                                break;
                            case 'TIPO':
                                $val = $equipo->tipo ? $equipo->tipo->nombre : '';
                                break;
                            case 'PLACA':
                                $val = $equipo->documentacion ? $equipo->documentacion->PLACA : '';
                                break;
                            case 'NRO_DE_DOCUMENTO':
                                $val = $equipo->documentacion ? $equipo->documentacion->NRO_DE_DOCUMENTO : '';
                                break;
                            case 'NOMBRE_DEL_TITULAR':
                                $val = $equipo->documentacion ? $equipo->documentacion->NOMBRE_DEL_TITULAR : '';
                                break;
                            case 'ESTADO_POLIZA':
                                $val = $equipo->documentacion ? $equipo->documentacion->ESTADO_POLIZA : '';
                                break;
                            case 'FECHA_VENC_POLIZA':
                                $val = $equipo->documentacion ? $equipo->documentacion->FECHA_VENC_POLIZA : '';
                                break;
                            default:
                                $val = $equipo->$col ?? '';
                                break;
                        }

                        // Style: Black Border (thin)
                        fwrite($handle, '<td style="border: thin solid #000000;">' . $val . '</td>');
                    }
                    fwrite($handle, '</tr>');
                }
            });

            fwrite($handle, '</tbody>');
            fwrite($handle, '</table></body></html>');
            fclose($handle);

        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function searchSpecs(Request $request)
    {
        $query = $request->input('query');
        if (!$query) return response()->json([]);

        $results = CaracteristicaModelo::select('ID_ESPEC', 'MODELO')
            ->where('MODELO', 'LIKE', "%{$query}%")
            ->orderBy('MODELO', 'asc')
            ->limit(20)
            ->get();

        return response()->json($results);
    }

    public function searchField(Request $request)
    {
        $field = $request->input('field');
        $query = $request->input('query');
        
        if (!$field || !$query) {
            return response()->json([]);
        }

        // Map frontend field names to database columns
        $fieldMap = [
            'MARCA' => 'MARCA',
            'MODELO' => 'MODELO'
        ];

        if (!isset($fieldMap[$field])) {
            return response()->json([]);
        }

        $column = $fieldMap[$field];
        
        $results = Equipo::select($column)
            ->distinct()
            ->whereNotNull($column)
            ->where($column, 'LIKE', "%{$query}%")
            ->orderBy($column, 'asc')
            ->limit(15)
            ->pluck($column);

        return response()->json($results);
    }

    public function create()
    {
        // Cache dropdown lists for 1 hour to avoid repeated DB queries
        $frentes = \Illuminate\Support\Facades\Cache::remember('frentes_activos_form', 3600, function() {
            return FrenteTrabajo::where('ESTATUS_FRENTE', 'ACTIVO')
                ->orderBy('NOMBRE_FRENTE', 'asc')
                ->pluck('NOMBRE_FRENTE', 'ID_FRENTE');
        });

        $seguros = \Illuminate\Support\Facades\Cache::remember('seguros_list_form', 3600, function() {
            return CatalogoSeguro::orderBy('NOMBRE_ASEGURADORA', 'asc')
                ->pluck('NOMBRE_ASEGURADORA');
        });

        $tipos_equipo = \Illuminate\Support\Facades\Cache::remember('tipos_equipo_list_form', 3600, function() {
            return TipoEquipo::orderBy('nombre', 'asc')
                ->pluck('nombre');
        });
        
        // Optimización: No cargar listados completos, usar autocompletado AJAX
        $marcas = []; 
        $modelos = [];
        $modelos_specs = collect([]); // Carga vacía para optimizar velocidad
        
        $categorias = ['FLOTA LIVIANA', 'FLOTA PESADA'];
        
        $equipo = new Equipo(); // Empty instance for form partial
        return view('admin.equipos.create', compact('frentes', 'seguros', 'modelos_specs', 'tipos_equipo', 'marcas', 'modelos', 'categorias', 'equipo'));
    }

    public function store(Request $request)
    {
        set_time_limit(600); 
        ini_set('memory_limit', '512M');

        // Normalize inputs to uppercase before validation to avoid case-sensitivity issues with unique constraints
        $request->merge([
            'CODIGO_PATIO' => (trim($request->CODIGO_PATIO ?? '') === '') ? null : strtoupper($request->CODIGO_PATIO),
            'SERIAL_CHASIS' => strtoupper($request->SERIAL_CHASIS),
            'SERIAL_DE_MOTOR' => (trim($request->SERIAL_DE_MOTOR ?? '') === '') ? null : strtoupper(trim($request->SERIAL_DE_MOTOR)),
        ]);
        
        if ($request->has('documentacion.PLACA')) {
            $doc = $request->documentacion;
            $placa = trim($doc['PLACA'] ?? '');
            $doc['PLACA'] = ($placa === '') ? null : strtoupper($placa);
            $request->merge(['documentacion' => $doc]);
        }

        $validated = $request->validate([
            'CODIGO_PATIO' => 'nullable|unique:equipos,CODIGO_PATIO',
            'TIPO_EQUIPO' => 'required',
            'CATEGORIA_FLOTA' => 'required|in:FLOTA LIVIANA,FLOTA PESADA',
            'MARCA' => 'required',
            'MODELO' => 'required',
            'ANIO' => 'required|integer',
            'SERIAL_CHASIS' => 'required|unique:equipos,SERIAL_CHASIS',
            'SERIAL_DE_MOTOR' => 'nullable|unique:equipos,SERIAL_DE_MOTOR',
            'documentacion.PLACA' => 'nullable|unique:documentacion,PLACA',
            'ESTADO_OPERATIVO' => 'required',
            'doc_propiedad' => 'nullable|file|mimes:pdf|max:5120|required_with:documentacion.NRO_DE_DOCUMENTO',
            'documentacion.NRO_DE_DOCUMENTO' => 'nullable|required_with:doc_propiedad',
            'poliza_seguro' => 'nullable|file|mimes:pdf|max:5120|required_with:documentacion.FECHA_VENC_POLIZA',
            'documentacion.FECHA_VENC_POLIZA' => 'nullable|required_with:poliza_seguro',
            'doc_rotc' => 'nullable|file|mimes:pdf|max:5120|required_with:documentacion.FECHA_ROTC',
            'documentacion.FECHA_ROTC' => 'nullable|required_with:doc_rotc',
            'doc_racda' => 'nullable|file|mimes:pdf|max:5120|required_with:documentacion.FECHA_RACDA',
            'documentacion.FECHA_RACDA' => 'nullable|required_with:doc_racda',
            'foto_equipo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'foto_referencial' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], $this->validationMessages(), $this->validationAttributes());

        DB::transaction(function () use ($request) {
            $tipoName = strtoupper($request->input('TIPO_EQUIPO'));
            $tipo = TipoEquipo::firstOrCreate(['nombre' => $tipoName]);
            $data = $request->except(['specs', 'responsable', 'documentacion', 'TIPO_EQUIPO', 'doc_propiedad', 'poliza_seguro', 'doc_rotc', 'doc_racda', 'foto_equipo', 'foto_referencial']);
            $data['id_tipo_equipo'] = $tipo->id;
            $data['TIPO_EQUIPO'] = $tipoName;
            $data['CODIGO_PATIO'] = strtoupper($data['CODIGO_PATIO'] ?? '');
            $data['MARCA'] = strtoupper($data['MARCA'] ?? '');
            $data['MODELO'] = strtoupper($data['MODELO'] ?? '');
            $data['SERIAL_CHASIS'] = strtoupper($data['SERIAL_CHASIS'] ?? '');
            $data['SERIAL_DE_MOTOR'] = (trim($data['SERIAL_DE_MOTOR'] ?? '') === '') ? null : strtoupper(trim($data['SERIAL_DE_MOTOR']));

            $equipo = Equipo::create($data);

            // Files to process async
            $filesToProcess = [];

            if ($request->filled('ID_ESPEC')) {
                $equipo->ID_ESPEC = $request->input('ID_ESPEC');
                $equipo->save();
                if ($request->hasFile('foto_referencial')) {
                    $file = $request->file('foto_referencial');
                    $filename = 'catalog_ref_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('temp_staging', $filename, 'local'); // Local Storage
                    $filesToProcess[] = [
                        'type' => 'foto_referencial',
                        'path' => $path,
                        'mime' => $file->getMimeType(),
                        'originalName' => $filename
                    ];
                }
            }

            if ($request->hasFile('foto_equipo')) {
                $file = $request->file('foto_equipo');
                $filename = 'foto_unidad_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('temp_staging', $filename, 'local');
                $filesToProcess[] = [
                    'type' => 'foto_equipo',
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'originalName' => $filename
                ];
            }

            if ($request->has('documentacion')) {
                $reqDoc = $request->input('documentacion');
                $reqDoc['ID_EQUIPO'] = $equipo->ID_EQUIPO;
                if (!empty($reqDoc['NOMBRE_SEGURO'])) {
                    $seguro = CatalogoSeguro::firstOrCreate(['NOMBRE_ASEGURADORA' => strtoupper($reqDoc['NOMBRE_SEGURO'])]);
                    $reqDoc['ID_SEGURO'] = $seguro->ID_SEGURO;
                }
                unset($reqDoc['NOMBRE_SEGURO']);
                $reqDoc = array_filter($reqDoc, function($value) { return !is_null($value) && $value !== ''; });

                $docTypes = ['doc_propiedad', 'poliza_seguro', 'doc_rotc', 'doc_racda'];
                foreach ($docTypes as $fileKey) {
                    if ($request->hasFile($fileKey)) {
                        $file = $request->file($fileKey);
                        $filename = $fileKey . '_' . time() . '.pdf';
                        $path = $file->storeAs('temp_staging', $filename, 'local');
                        $filesToProcess[] = [
                            'type' => $fileKey,
                            'path' => $path,
                            'mime' => 'application/pdf',
                            'originalName' => $filename
                        ];
                    }
                }

                if (isset($reqDoc['PLACA'])) {
                    $placaVal = trim($reqDoc['PLACA']);
                    $reqDoc['PLACA'] = ($placaVal === '') ? null : strtoupper($placaVal);
                }
                if (isset($reqDoc['NOMBRE_DEL_TITULAR'])) $reqDoc['NOMBRE_DEL_TITULAR'] = strtoupper($reqDoc['NOMBRE_DEL_TITULAR']);
                if (isset($reqDoc['NRO_DE_DOCUMENTO'])) $reqDoc['NRO_DE_DOCUMENTO'] = strtoupper($reqDoc['NRO_DE_DOCUMENTO']);
                Documentacion::create($reqDoc);
            }

            if ($request->has('responsable')) {
                $reqResp = $request->input('responsable');
                if (!empty($reqResp['NOMBRE_RESPONSABLE'])) {
                    $reqResp['ID_EQUIPO'] = $equipo->ID_EQUIPO;
                    $reqResp['FECHA_ASIGNACION'] = now();
                    Responsable::create($reqResp);
                }
            }

            // Dispatch Async Job
            if (count($filesToProcess) > 0) {
                \App\Jobs\ProcessEquipoUploads::dispatch($equipo->ID_EQUIPO, $filesToProcess);
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Equipo registrado correctamente.', 'redirect' => route('equipos.index')]);
        }

        return redirect()->route('equipos.index')->with('success', 'Equipo registrado correctamente.');
    }

    public function show($id)
    {
        $equipo = Equipo::with('frenteActual', 'especificaciones', 'documentacion.seguro', 'responsables')->findOrFail($id);
        return view('admin.equipos.show', compact('equipo'));
    }

    public function edit($id)
    {
        $equipo = Equipo::with('frenteActual', 'especificaciones', 'documentacion', 'responsables')->findOrFail($id);
        $frentes = FrenteTrabajo::where('ESTATUS_FRENTE', 'ACTIVO')->orderBy('NOMBRE_FRENTE', 'asc')->pluck('NOMBRE_FRENTE', 'ID_FRENTE');
        $seguros = CatalogoSeguro::orderBy('NOMBRE_ASEGURADORA', 'asc')->pluck('NOMBRE_ASEGURADORA');
        $modelos_specs = collect([]); // Optimization
        $tipos_equipo = TipoEquipo::orderBy('nombre', 'asc')->pluck('nombre');
        
        // Optimización
        $marcas = []; 
        $modelos = [];
        
        $categorias = ['FLOTA LIVIANA', 'FLOTA PESADA'];
        return view('admin.equipos.edit', compact('equipo', 'frentes', 'seguros', 'modelos_specs', 'categorias', 'tipos_equipo', 'marcas', 'modelos'));
    }

    public function update(Request $request, $id)
    {
        set_time_limit(300);
        $equipo = Equipo::findOrFail($id);

        // Normalize inputs to uppercase before validation to avoid case-sensitivity issues with unique constraints
        $request->merge([
            'CODIGO_PATIO' => strtoupper($request->CODIGO_PATIO),
            'SERIAL_CHASIS' => strtoupper($request->SERIAL_CHASIS),
            'SERIAL_DE_MOTOR' => (trim($request->SERIAL_DE_MOTOR ?? '') === '') ? null : strtoupper(trim($request->SERIAL_DE_MOTOR)),
        ]);
        
        if ($request->has('documentacion.PLACA')) {
            $doc = $request->documentacion;
            $placa = trim($doc['PLACA'] ?? '');
            $doc['PLACA'] = ($placa === '') ? null : strtoupper($placa);
            $request->merge(['documentacion' => $doc]);
        }

        $validated = $request->validate([
            'CODIGO_PATIO' => 'required|unique:equipos,CODIGO_PATIO,' . $id . ',ID_EQUIPO',
            'TIPO_EQUIPO' => 'required',
            'CATEGORIA_FLOTA' => 'required|in:FLOTA LIVIANA,FLOTA PESADA',
            'MARCA' => 'required',
            'MODELO' => 'required',
            'ANIO' => 'required|integer',
            'SERIAL_CHASIS' => 'required|unique:equipos,SERIAL_CHASIS,' . $id . ',ID_EQUIPO',
            'SERIAL_DE_MOTOR' => 'nullable|unique:equipos,SERIAL_DE_MOTOR,' . $id . ',ID_EQUIPO',
            'documentacion.PLACA' => 'nullable|unique:documentacion,PLACA,' . ($equipo->documentacion ? $equipo->documentacion->ID_EQUIPO : 'NULL') . ',ID_EQUIPO',
            'ESTADO_OPERATIVO' => 'required',
            'foto_equipo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'foto_referencial' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'doc_propiedad' => 'nullable|file|mimes:pdf|max:5120',
            'poliza_seguro' => 'nullable|file|mimes:pdf|max:5120',
            'doc_rotc' => 'nullable|file|mimes:pdf|max:5120',
            'doc_racda' => 'nullable|file|mimes:pdf|max:5120',
        ], $this->validationMessages(), $this->validationAttributes());

         $validator = \Illuminate\Support\Facades\Validator::make($request->all(), []); 
         // Custom validation for update: Check if file exists or is being uploaded if meta is present
         $validator->after(function ($validator) use ($request, $equipo) {
             // Propiedad
             if ($request->filled('documentacion.NRO_DE_DOCUMENTO')) {
                 $hasFile = $request->hasFile('doc_propiedad');
                 $hasExisting = $equipo->documentacion && $equipo->documentacion->LINK_DOC_PROPIEDAD;
                 if (!$hasFile && !$hasExisting) {
                     $validator->errors()->add('doc_propiedad', 'El documento de propiedad es obligatorio si se indica el número.');
                 }
             }
             if ($request->hasFile('doc_propiedad') && !$request->filled('documentacion.NRO_DE_DOCUMENTO')) {
                 if (!($equipo->documentacion && $equipo->documentacion->NRO_DE_DOCUMENTO)) {
                      $validator->errors()->add('documentacion.NRO_DE_DOCUMENTO', 'El número de documento es obligatorio al cargar el archivo.');
                 }
             }

             // Poliza
             if ($request->filled('documentacion.FECHA_VENC_POLIZA')) {
                 $hasFile = $request->hasFile('poliza_seguro');
                 $hasExisting = $equipo->documentacion && $equipo->documentacion->LINK_POLIZA_SEGURO;
                 if (!$hasFile && !$hasExisting) {
                     $validator->errors()->add('poliza_seguro', 'La póliza es obligatoria si se indica el vencimiento.');
                 }
             }
              if ($request->hasFile('poliza_seguro') && !$request->filled('documentacion.FECHA_VENC_POLIZA')) {
                 if (!($equipo->documentacion && $equipo->documentacion->FECHA_VENC_POLIZA)) {
                      $validator->errors()->add('documentacion.FECHA_VENC_POLIZA', 'La fecha de vencimiento es obligatoria al cargar la póliza.');
                 }
             }

             // ROTC
             if ($request->filled('documentacion.FECHA_ROTC')) {
                 $hasFile = $request->hasFile('doc_rotc');
                 $hasExisting = $equipo->documentacion && $equipo->documentacion->LINK_ROTC;
                 if (!$hasFile && !$hasExisting) {
                     $validator->errors()->add('doc_rotc', 'El documento ROTC es obligatorio si se indica la fecha.');
                 }
             }
             if ($request->hasFile('doc_rotc') && !$request->filled('documentacion.FECHA_ROTC')) {
                 if (!($equipo->documentacion && $equipo->documentacion->FECHA_ROTC)) {
                      $validator->errors()->add('documentacion.FECHA_ROTC', 'La fecha ROTC es obligatoria al cargar el archivo.');
                 }
             }

             // RACDA
             if ($request->filled('documentacion.FECHA_RACDA')) {
                 $hasFile = $request->hasFile('doc_racda');
                 $hasExisting = $equipo->documentacion && $equipo->documentacion->LINK_RACDA;
                 if (!$hasFile && !$hasExisting) {
                     $validator->errors()->add('doc_racda', 'El documento RACDA es obligatorio si se indica la fecha.');
                 }
             }
              if ($request->hasFile('doc_racda') && !$request->filled('documentacion.FECHA_RACDA')) {
                 if (!($equipo->documentacion && $equipo->documentacion->FECHA_RACDA)) {
                      $validator->errors()->add('documentacion.FECHA_RACDA', 'La fecha RACDA es obligatoria al cargar el archivo.');
                 }
             }
         });
         $validator->validate();

        DB::transaction(function () use ($request, $equipo) {
            $tipoName = strtoupper($request->input('TIPO_EQUIPO'));
            $tipo = TipoEquipo::firstOrCreate(['nombre' => $tipoName]);
            $data = $request->except(['specs', 'responsable', 'documentacion', 'TIPO_EQUIPO', 'doc_propiedad', 'poliza_seguro', 'doc_rotc', 'doc_racda', 'foto_equipo', 'foto_referencial']);
            $data['id_tipo_equipo'] = $tipo->id;
            $data['TIPO_EQUIPO'] = $tipoName;
            $data['CODIGO_PATIO'] = strtoupper(trim($data['CODIGO_PATIO'] ?? ''));
            $data['MARCA'] = strtoupper(trim($data['MARCA'] ?? ''));
            $data['MODELO'] = strtoupper(trim($data['MODELO'] ?? ''));
            $data['SERIAL_CHASIS'] = strtoupper(trim($data['SERIAL_CHASIS'] ?? ''));
            $data['SERIAL_DE_MOTOR'] = (trim($data['SERIAL_DE_MOTOR'] ?? '') === '') ? null : strtoupper(trim($data['SERIAL_DE_MOTOR']));
            $equipo->update($data);

            if ($request->filled('ID_ESPEC')) {
                $equipo->ID_ESPEC = $request->input('ID_ESPEC');
                $equipo->save();
                if ($request->hasFile('foto_referencial')) {
                    $espec = CaracteristicaModelo::find($equipo->ID_ESPEC);
                    if ($espec) {
                        $catalogFolderId = config('filesystems.disks.google.catalog_folder'); // Specific folder for model photos
                        $driveService = \App\Services\GoogleDriveService::getInstance();
                        $file = $request->file('foto_referencial');
                        $filename = 'catalog_ref_' . time() . '.' . $file->getClientOriginalExtension();
                        $driveFile = $driveService->uploadFile($catalogFolderId, $file, $filename, $file->getMimeType());
                        if ($driveFile && isset($driveFile->id)) {
                            $espec->update(['FOTO_REFERENCIAL' => '/storage/google/' . $driveFile->id]);
                        }
                    }
                }
            }

            $driveService = \App\Services\GoogleDriveService::getInstance();
            $folderId = $driveService->getRootFolderId();

            if ($request->hasFile('foto_equipo')) {
                $file = $request->file('foto_equipo');
                $photoFolderId = config('filesystems.disks.google.equipment_folder'); // Specific folder for equipment photos
                $driveFile = $driveService->uploadFile($photoFolderId, $file, 'foto_unidad_' . time() . '.' . $file->getClientOriginalExtension(), $file->getMimeType());
                if ($driveFile && isset($driveFile->id)) $equipo->update(['FOTO_EQUIPO' => '/storage/google/' . $driveFile->id]); 
            }

            if ($request->has('documentacion')) {
                $docData = $request->input('documentacion');
                if (!empty($docData['NOMBRE_SEGURO'])) {
                    $seguro = CatalogoSeguro::firstOrCreate(['NOMBRE_ASEGURADORA' => strtoupper($docData['NOMBRE_SEGURO'])]);
                    $docData['ID_SEGURO'] = $seguro->ID_SEGURO;
                }
                unset($docData['NOMBRE_SEGURO']);
                $docData = array_filter($docData, function($value) { return !is_null($value) && $value !== ''; });

                $docTypes = ['doc_propiedad' => 'LINK_DOC_PROPIEDAD', 'poliza_seguro' => 'LINK_POLIZA_SEGURO', 'doc_rotc' => 'LINK_ROTC', 'doc_racda' => 'LINK_RACDA'];
                foreach ($docTypes as $fileKey => $dbCol) {
                    if ($request->hasFile($fileKey)) {
                        $file = $request->file($fileKey);
                        
                        // Check for old file and delete it (Correctly using DB relation)
                        if ($equipo->documentacion && $equipo->documentacion->$dbCol && str_starts_with($equipo->documentacion->$dbCol, '/storage/google/')) {
                             $oldFileId = str_replace('/storage/google/', '', $equipo->documentacion->$dbCol);
                             try {
                                 $driveService->deleteFile($oldFileId);
                             } catch (\Exception $e) {
                                 Log::error("Failed to delete old Drive file: $oldFileId");
                             }
                        }

                        $driveFile = $driveService->uploadFile($folderId, $file, $fileKey . '_' . time() . '.pdf', 'application/pdf');
                        if ($driveFile && isset($driveFile->id)) $docData[$dbCol] = '/storage/google/' . $driveFile->id;
                    }
                }

                if (isset($docData['PLACA'])) {
                    $placaVal = trim($docData['PLACA']);
                    $docData['PLACA'] = ($placaVal === '') ? null : strtoupper($placaVal);
                }
                if (isset($docData['NOMBRE_DEL_TITULAR'])) $docData['NOMBRE_DEL_TITULAR'] = strtoupper($docData['NOMBRE_DEL_TITULAR']);
                if (isset($docData['NRO_DE_DOCUMENTO'])) $docData['NRO_DE_DOCUMENTO'] = strtoupper($docData['NRO_DE_DOCUMENTO']);

                if ($equipo->documentacion) $equipo->documentacion->update($docData);
                else {
                    $docData['ID_EQUIPO'] = $equipo->ID_EQUIPO;
                    Documentacion::create($docData);
                }
            }
        });

        return redirect()->route('equipos.index')->with('success', 'Equipo actualizado.');
    }

    public function destroy($id)
    {
        $equipo = Equipo::findOrFail($id);
        $equipo->delete();
        return redirect()->route('equipos.index')->with('success', 'Equipo eliminado.');
    }

    public function changeStatus(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);
        $equipo->ESTADO_OPERATIVO = $request->input('status');
        $equipo->save();
        return back()->with('success', 'Estatus actualizado.');
    }


    public function uploadDoc(Request $request, $id)
    {
        set_time_limit(600);
        ini_set('memory_limit', '512M');
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:51200',
            'doc_type' => 'required|in:propiedad,poliza,rotc,racda',
            'expiration_date' => 'nullable|date'
        ]);

        $equipo = Equipo::findOrFail($id);
        $type = $request->input('doc_type');
        $file = $request->file('file');
        
        $dbColumn = '';
        $dateColumn = '';
        $filenamePrefix = '';
        switch ($type) {
            case 'propiedad': 
                $dbColumn = 'LINK_DOC_PROPIEDAD'; 
                $filenamePrefix = 'doc_propiedad_'; 
                break;
            case 'poliza': 
                $dbColumn = 'LINK_POLIZA_SEGURO'; 
                $dateColumn = 'FECHA_VENC_POLIZA'; 
                $filenamePrefix = 'poliza_seguro_'; 
                break;
            case 'rotc': 
                $dbColumn = 'LINK_ROTC'; 
                $dateColumn = 'FECHA_ROTC'; 
                $filenamePrefix = 'rotc_'; 
                break;
            case 'racda': 
                $dbColumn = 'LINK_RACDA'; 
                $dateColumn = 'FECHA_RACDA'; 
                $filenamePrefix = 'racda_'; 
                break;
        }

        try {
            $driveService = \App\Services\GoogleDriveService::getInstance();
            
            // 1. CAPTURE OLD FILE ID (Don't delete yet - Safety First)
            $oldFileIdToDelete = null;
            if ($equipo->documentacion && $equipo->documentacion->$dbColumn && str_starts_with($equipo->documentacion->$dbColumn, '/storage/google/')) {
                 $oldFileIdToDelete = str_replace('/storage/google/', '', $equipo->documentacion->$dbColumn);
            }

            // 2. UPLOAD NEW FILE
            $folderId = $driveService->getRootFolderId();
            $filename = $filenamePrefix . time() . '.pdf';
            $driveFile = $driveService->uploadFile($folderId, $file, $filename, $file->getMimeType());
            
            if (!$driveFile || !isset($driveFile->id)) throw new \Exception("La subida a Google Drive no retornó un ID válido");

            $fullUrl = '/storage/google/' . $driveFile->id;

            // 3. UPDATE DATABASE
            $updateData = [$dbColumn => $fullUrl];
            if ($dateColumn && $request->filled('expiration_date')) {
                $updateData[$dateColumn] = $request->input('expiration_date');
            }

            if ($equipo->documentacion) $equipo->documentacion->update($updateData);
            else {
                $updateData['ID_EQUIPO'] = $equipo->ID_EQUIPO;
                Documentacion::create($updateData);
            }

            // 4. DELETE OLD FILE (Only after success)
            if ($oldFileIdToDelete) {
                \App\Jobs\DeleteGoogleDriveFile::dispatch($oldFileIdToDelete);
            }

            // Clear Dashboard Cache to update alerts immediately
            \Illuminate\Support\Facades\Cache::forget('dashboard_total_alerts');
            \Illuminate\Support\Facades\Cache::forget('dashboard_expired_list_all');

            if (ob_get_length()) ob_end_clean();
            return response()->json(['success' => true, 'link' => $fullUrl, 'message' => 'Documento actualizado correctamente']);
        } catch (\Exception $e) {
            Log::error('Error subiendo archivo a Google Drive: ' . $e->getMessage());
            if (ob_get_length()) ob_end_clean();
            return response()->json(['success' => false, 'message' => 'Error al subir archivo: ' . $e->getMessage()], 500);
        }
    }

    private function validationMessages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'unique' => 'El :attribute ya ha sido registrado.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
            'max' => 'El campo :attribute no debe pesar más de :max kilobytes.',
            'image' => 'El campo :attribute debe ser una imagen.',
            'in' => 'El valor seleccionado para :attribute es inválido.',
            'required_with' => 'El campo :attribute es obligatorio cuando :values está presente.',
        ];
    }

    private function validationAttributes()
    {
        return [
            'CODIGO_PATIO' => 'Código de Patio',
            'TIPO_EQUIPO' => 'Tipo de Equipo',
            'CATEGORIA_FLOTA' => 'Categoría de Flota',
            'MARCA' => 'Marca',
            'MODELO' => 'Modelo',
            'ANIO' => 'Año',
            'SERIAL_CHASIS' => 'Serial de Chasis',
            'SERIAL_DE_MOTOR' => 'Serial de Motor',
            'documentacion.PLACA' => 'Placa',
            'ESTADO_OPERATIVO' => 'Estatus',
            'doc_propiedad' => 'Documento de Propiedad',
            'documentacion.NRO_DE_DOCUMENTO' => 'Nro. de Documento',
            'poliza_seguro' => 'Póliza de Seguro',
            'documentacion.FECHA_VENC_POLIZA' => 'Fecha de Vencimiento de Póliza',
            'doc_rotc' => 'Documento ROTC',
            'documentacion.FECHA_ROTC' => 'Fecha ROTC',
            'doc_racda' => 'Documento RACDA',
            'documentacion.FECHA_RACDA' => 'Fecha RACDA',
            'foto_equipo' => 'Foto del Equipo',
            'foto_referencial' => 'Foto Referencial',
        ];
    }
    public function checkUniqueness(Request $request)
    {
        $field = $request->input('field');
        $value = $request->input('value');
        $id = $request->input('id'); // For update exclusion

        $allowedFields = ['SERIAL_CHASIS', 'SERIAL_DE_MOTOR', 'CODIGO_PATIO', 'PLACA'];
        if (!in_array($field, $allowedFields)) {
            return response()->json(['error' => 'Invalid field'], 400);
        }

        if ($field === 'PLACA') {
            $query = Documentacion::where('PLACA', strtoupper($value));
            if ($id) {
                // If updating, we need to exclude the documentation belonging to this equipment
                $query->where('ID_EQUIPO', '!=', $id);
            }
            return response()->json(['exists' => $query->exists()]);
        }

        $query = Equipo::where($field, strtoupper($value));
        if ($id) {
            $query->where('ID_EQUIPO', '!=', $id);
        }

        return response()->json(['exists' => $query->exists()]);
    }

    /**
     * Get metadata for a specific document type
     */
    public function metadata(Request $request, $id)
    {
        $equipo = Equipo::with('documentacion.seguro')->findOrFail($id);
        $type = $request->input('type');
        
        $data = [];
        
        switch ($type) {
            case 'propiedad':
                $data = [
                    'nro_documento' => $equipo->documentacion->NRO_DE_DOCUMENTO ?? '',
                    'titular' => $equipo->documentacion->NOMBRE_DEL_TITULAR ?? '',
                    'placa' => $equipo->documentacion->PLACA ?? '',
                    'serial_chasis' => $equipo->SERIAL_CHASIS ?? '',
                    'serial_motor' => $equipo->SERIAL_DE_MOTOR ?? '',
                ];
                break;
                
            case 'poliza':
                $data = [
                    'fecha_vencimiento' => $equipo->documentacion->FECHA_VENC_POLIZA ?? '',
                    'id_seguro' => $equipo->documentacion->ID_SEGURO ?? null,
                    'insurers' => CatalogoSeguro::orderBy('NOMBRE_ASEGURADORA', 'asc')->get(),
                ];
                break;
                
            case 'rotc':
                $data = [
                    'fecha_vencimiento' => $equipo->documentacion->FECHA_ROTC ?? '',
                ];
                break;
                
            case 'racda':
                $data = [
                    'fecha_vencimiento' => $equipo->documentacion->FECHA_RACDA ?? '',
                ];
                break;
        }
        
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Update metadata for a specific document type
     */
    public function updateMetadata(Request $request, $id)
    {
        $equipo = Equipo::with('documentacion')->findOrFail($id);
        $type = $request->input('doc_type');
        
        if (!$equipo->documentacion) {
            return response()->json(['success' => false, 'message' => 'No existe documentación para este equipo'], 400);
        }
        
        $updateData = [];
        
        switch ($type) {
            case 'propiedad':
                $updateData = [
                    'NRO_DE_DOCUMENTO' => strtoupper($request->input('nro_documento', '')),
                    'NOMBRE_DEL_TITULAR' => strtoupper($request->input('titular', '')),
                    'PLACA' => strtoupper($request->input('placa', '')),
                ];

                // Update Equipment Serials directly
                $equipo->update([
                    'SERIAL_CHASIS' => strtoupper($request->input('serial_chasis', '')),
                    'SERIAL_DE_MOTOR' => (trim($request->input('serial_motor', '') ?? '') === '') ? null : strtoupper(trim($request->input('serial_motor', ''))),
                ]);
                break;
                
            case 'poliza':
                $updateData = [
                    'FECHA_VENC_POLIZA' => $request->input('fecha_vencimiento'),
                ];
                
                // Handle insurance name (create if new)
                if ($request->filled('nombre_aseguradora')) {
                    $seguro = CatalogoSeguro::firstOrCreate([
                        'NOMBRE_ASEGURADORA' => strtoupper($request->input('nombre_aseguradora'))
                    ]);
                    $updateData['ID_SEGURO'] = $seguro->ID_SEGURO;
                }
                break;
                
            case 'rotc':
                $updateData = [
                    'FECHA_ROTC' => $request->input('fecha_vencimiento'),
                ];
                break;
                
            case 'racda':
                $updateData = [
                    'FECHA_RACDA' => $request->input('fecha_vencimiento'),
                ];
                break;
        }
        
        // Filter empty values
        $updateData = array_filter($updateData, function($value) {
            return !is_null($value) && $value !== '';
        });
        
        $equipo->documentacion->update($updateData);
        
        // Clear Dashboard Cache to update alerts immediately
        \Illuminate\Support\Facades\Cache::forget('dashboard_total_alerts');
        \Illuminate\Support\Facades\Cache::forget('dashboard_expired_list_all');
        
        return response()->json(['success' => true, 'message' => 'Datos actualizados correctamente']);
    }
}
