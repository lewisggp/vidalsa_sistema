<?php

namespace App\Http\Controllers;

use App\Models\Movilizacion;
use App\Models\FrenteTrabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovilizacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Permiso para MOVER equipos (Crear movilizaciones o registrar recepción directa sin despacho previo)
        $this->middleware('can:equipos.assign')->only(['create', 'store', 'bulkStore', 'recepcionDirecta']);
    }

    public function index(Request $request)
    {
        // Frente filter logic: LOCAL users always see their frente (locked).
        // GLOBAL users get their frente as default ONLY on the initial page load (non-AJAX).
        // When a GLOBAL user clears the filter (AJAX request), we respect the empty value.
        $user = auth()->user();
        $isLocalUser = $user && $user->NIVEL_ACCESO == 2;

        if ($isLocalUser && $user->ID_FRENTE_ASIGNADO) {
            // LOCAL: Always force their assigned frente, ignore any request parameter
            $request->merge(['id_frente' => $user->ID_FRENTE_ASIGNADO]);
        } elseif (!$isLocalUser && $user && $user->ID_FRENTE_ASIGNADO) {
            // GLOBAL: Only set default on initial page load (not AJAX / not when user explicitly cleared it)
            if (!$request->wantsJson() && !$request->exists('id_frente')) {
                $request->merge(['id_frente' => $user->ID_FRENTE_ASIGNADO]);
            }
        }

        $query = Movilizacion::with(['equipo.tipo', 'equipo.especificaciones', 'equipo.documentacion', 'frenteOrigen', 'frenteDestino', 'usuario']);

        // Smart Search Filters (Pattern-Based Optimization)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $searchUpper = strtoupper($search);

            $query->where(function ($q) use ($search, $searchUpper) {
                // Pattern 1: MV-XXXXX or MVXXXXX → Search CODIGO_CONTROL
                if (preg_match('/^MV-?\d+/i', $search)) {
                    $cleanSearch = ltrim(str_replace(['MV-', 'MV'], '', $searchUpper), '0');
                    $q->where('CODIGO_CONTROL', 'like', "%{$searchUpper}%")
                        ->orWhere('CODIGO_CONTROL', 'like', "%{$cleanSearch}%");
                }
                // Pattern 2: DD-MM-YYYY format → Search CODIGO_PATIO
                elseif (preg_match('/\d{2}-\d{2}-\d{4}/', $search)) {
                    $q->whereHas('equipo', function ($qEq) use ($search) {
                        $qEq->where('CODIGO_PATIO', 'like', "%{$search}%");
                    });
                }
                // Pattern 3: #NUMBER → Search NUMERO_ETIQUETA
                elseif (strpos($search, '#') === 0) {
                    $numeroEtiqueta = ltrim($search, '#');
                    $q->whereHas('equipo', function ($qEq) use ($numeroEtiqueta) {
                        $qEq->where('NUMERO_ETIQUETA', 'like', "%{$numeroEtiqueta}%");
                    });
                }
                // Pattern 4: Contains hyphen → Search CODIGO_PATIO
                elseif (strpos($search, '-') !== false) {
                    $q->whereHas('equipo', function ($qEq) use ($search) {
                        $qEq->where('CODIGO_PATIO', 'like', "%{$search}%");
                    });
                }
                // Pattern 5: Default → Search PLACA and SERIAL_CHASIS
                else {
                    $q->whereHas('equipo', function ($qEq) use ($search) {
                        $qEq->where('SERIAL_CHASIS', 'like', "%{$search}%")
                            ->orWhereHas('documentacion', function ($qDoc) use ($search) {
                                $qDoc->where('PLACA', 'like', "%{$search}%");
                            });
                    });
                }
            });
        }

        if ($request->filled('id_frente') && $request->id_frente !== 'all') {
            $direccion = $request->input('direccion_frente'); // 'entrada', 'salida', o vacío

            if ($direccion === 'entrada') {
                // Solo registros donde el frente seleccionado es el DESTINO (equipos que ENTRARON)
                $query->where('ID_FRENTE_DESTINO', $request->id_frente);
            } elseif ($direccion === 'salida') {
                // Solo registros donde el frente seleccionado es el ORIGEN (equipos que SALIERON)
                $query->where('ID_FRENTE_ORIGEN', $request->id_frente);
            } else {
                // Sin filtro de dirección: mostrar ambos (comportamiento original)
                $query->where(function ($q) use ($request) {
                    $q->where('ID_FRENTE_DESTINO', $request->id_frente)
                        ->orWhere('ID_FRENTE_ORIGEN', $request->id_frente);
                });
            }
        }

        if ($request->filled('id_tipo') && $request->id_tipo !== 'all') {
            $query->whereHas('equipo', function ($q) use ($request) {
                $q->where('id_tipo_equipo', $request->id_tipo);
            });
        }

        // Filtro por rango de fechas (FECHA_DESPACHO)
        if ($request->filled('fecha_desde')) {
            $query->whereDate('FECHA_DESPACHO', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('FECHA_DESPACHO', '<=', $request->fecha_hasta);
        }

        $movilizaciones = $query->orderBy('FECHA_DESPACHO', 'desc')->paginate(12);

        // Stats: Total In Transit — filtrado con los mismos criterios activos
        $statsQuery = Movilizacion::where('ESTADO_MVO', 'TRANSITO');

        // Aplicar filtro de frente a las stats (mismo criterio que la tabla)
        if ($request->filled('id_frente') && $request->id_frente !== 'all') {
            $direccion = $request->input('direccion_frente');
            if ($direccion === 'entrada') {
                $statsQuery->where('ID_FRENTE_DESTINO', $request->id_frente);
            } elseif ($direccion === 'salida') {
                $statsQuery->where('ID_FRENTE_ORIGEN', $request->id_frente);
            } else {
                $statsQuery->where(function ($q) use ($request) {
                    $q->where('ID_FRENTE_DESTINO', $request->id_frente)
                      ->orWhere('ID_FRENTE_ORIGEN', $request->id_frente);
                });
            }
        }

        // Aplicar filtro de tipo a las stats
        if ($request->filled('id_tipo') && $request->id_tipo !== 'all') {
            $statsQuery->whereHas('equipo', function ($q) use ($request) {
                $q->where('id_tipo_equipo', $request->id_tipo);
            });
        }

        // Aplicar filtro de fechas a las stats
        if ($request->filled('fecha_desde')) {
            $statsQuery->whereDate('FECHA_DESPACHO', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $statsQuery->whereDate('FECHA_DESPACHO', '<=', $request->fecha_hasta);
        }

        $totalTransito = (clone $statsQuery)->count();

        $transitoPorFrente = (clone $statsQuery)
            ->join('frentes_trabajo', 'movilizacion_historial.ID_FRENTE_DESTINO', '=', 'frentes_trabajo.ID_FRENTE')
            ->select('frentes_trabajo.NOMBRE_FRENTE', DB::raw('count(*) as total'))
            ->groupBy('frentes_trabajo.NOMBRE_FRENTE')
            ->orderByDesc('total')
            ->get();

        $frentes = FrenteTrabajo::where('ESTATUS_FRENTE', 'ACTIVO')->orderBy('NOMBRE_FRENTE')->get();
        $allTipos = \App\Models\TipoEquipo::orderBy('nombre')->get();

        // Check if JSON specifically requested (for filters)
        if ($request->wantsJson()) {
            $tableHtml = view('admin.movilizaciones.partials.table_rows', compact('movilizaciones'))->render();
            $paginationHtml = $movilizaciones->appends($request->all())->links()->toHtml();

            $statsHtml = '<h4 style="margin: 0 0 15px 0; font-size: 13px; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="material-icons" style="font-size: 18px; color: #8b5cf6;">local_shipping</i>
                En Tránsito por Frente
            </h4>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">';

            if ($transitoPorFrente->isNotEmpty()) {
                foreach ($transitoPorFrente as $stat) {
                    $statsHtml .= '<li style="padding: 10px; background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12px; color: #64748b; font-weight: 600;">' . $stat->NOMBRE_FRENTE . '</span>
                            <span style="background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: 700;">' . $stat->total . '</span>
                        </li>';
                }
            } else {
                $statsHtml .= '<li style="padding: 15px; text-align: center; color: #94a3b8; font-style: italic; font-size: 13px;">No hay equipos en tránsito</li>';
            }
            $statsHtml .= '</ul>';

            return response()->json([
                'html' => $tableHtml,
                'pagination' => $paginationHtml,
                'statsHtml' => $statsHtml,
                'totalTransito' => $totalTransito
            ]);
        }

        return view('admin.movilizaciones.index', compact('movilizaciones', 'totalTransito', 'transitoPorFrente', 'frentes', 'allTipos'));
    }

    public function create()
    {
        $equipos = \App\Models\Equipo::with(['tipo', 'frenteActual'])
            ->where('ESTADO_OPERATIVO', 'OPERATIVO')
            ->orderBy('CODIGO_PATIO')
            ->get();

        $frentes = FrenteTrabajo::where('ESTATUS_FRENTE', 'ACTIVO')->orderBy('NOMBRE_FRENTE')->get();

        return view('admin.movilizaciones.create', compact('equipos', 'frentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ID_EQUIPO' => 'required|exists:equipos,ID_EQUIPO',
            'ID_FRENTE_DESTINO' => 'required|exists:frentes_trabajo,ID_FRENTE',
        ]);

        $equipo = \App\Models\Equipo::findOrFail($request->ID_EQUIPO);

        // Lock para evitar race condition si dos usuarios despachan al mismo tiempo
        $lastLog = Movilizacion::latest('ID_MOVILIZACION')->lockForUpdate()->first();
        $nextId = $lastLog ? ($lastLog->ID_MOVILIZACION + 1) : 1;

        $origen = $equipo->ID_FRENTE_ACTUAL ?? 1;

        Movilizacion::create([
            'CODIGO_CONTROL' => $nextId,
            'ID_EQUIPO' => $request->ID_EQUIPO,
            'ID_FRENTE_ORIGEN' => $origen,
            'ID_FRENTE_DESTINO' => $request->ID_FRENTE_DESTINO,
            'FECHA_DESPACHO' => now(),
            'ESTADO_MVO' => 'TRANSITO',
            'TIPO_MOVIMIENTO' => 'DESPACHO',
            'USUARIO_REGISTRO' => auth()->user()->CORREO_ELECTRONICO ?? 'SISTEMA',
        ]);

        return redirect()->route('movilizaciones.index')->with('success', 'Movilización registrada correctamente.');
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:equipos,ID_EQUIPO',
            'destination' => 'required|string|max:255',
            'generar_pdf' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $frente = FrenteTrabajo::firstOrCreate(
                ['NOMBRE_FRENTE' => strtoupper($request->destination)],
                ['ESTATUS_FRENTE' => 'ACTIVO']
            );

            $user = auth()->user()->CORREO_ELECTRONICO ?? 'SISTEMA';
            $now = now();
            $generarPdf = $request->input('generar_pdf', true);

            $lastLog = Movilizacion::latest('ID_MOVILIZACION')->lockForUpdate()->first();
            $nextId = null;
            if ($generarPdf) {
                // Generar CODIGO_CONTROL solo si se requiere acta de traslado
                $nextId = $lastLog && $lastLog->CODIGO_CONTROL ? ((int)$lastLog->CODIGO_CONTROL + 1) : ($lastLog ? ($lastLog->ID_MOVILIZACION + 1) : 1);
            }

            $equipos = \App\Models\Equipo::whereIn('ID_EQUIPO', $request->ids)->get(['ID_EQUIPO', 'ID_FRENTE_ACTUAL']);

            $insertData = [];
            foreach ($equipos as $equipo) {
                if ($generarPdf) {
                    $insertData[] = [
                        'CODIGO_CONTROL' => $nextId,
                        'ID_EQUIPO' => $equipo->ID_EQUIPO,
                        'ID_FRENTE_ORIGEN' => $equipo->ID_FRENTE_ACTUAL ?? 1,
                        'ID_FRENTE_DESTINO' => $frente->ID_FRENTE,
                        'FECHA_DESPACHO' => $now,
                        'ESTADO_MVO' => 'TRANSITO',
                        'TIPO_MOVIMIENTO' => 'DESPACHO',
                        'USUARIO_REGISTRO' => $user,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                } else {
                    $insertData[] = [
                        'CODIGO_CONTROL' => null,
                        'ID_EQUIPO' => $equipo->ID_EQUIPO,
                        'ID_FRENTE_ORIGEN' => $equipo->ID_FRENTE_ACTUAL ?? 1,
                        'ID_FRENTE_DESTINO' => $frente->ID_FRENTE,
                        'FECHA_DESPACHO' => null,
                        'FECHA_RECEPCION' => $now,
                        'ESTADO_MVO' => 'RECIBIDO',
                        'TIPO_MOVIMIENTO' => 'ACT.',
                        'USUARIO_REGISTRO' => $user,
                        'USUARIO_RECEPCION' => $user,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            $movilizacionIds = [];
            if (!empty($insertData)) {
                Movilizacion::insert($insertData);

                if ($nextId !== null) {
                    $movilizacionIds = Movilizacion::where('CODIGO_CONTROL', $nextId)
                        ->pluck('ID_MOVILIZACION')
                        ->toArray();
                } else {
                    // Si no hay CODIGO_CONTROL igual trataremos de retornar los ids 
                    // aunque no se va a generar acta
                    $movilizacionIds = Movilizacion::whereIn('ID_EQUIPO', $request->ids)
                        ->where('ID_FRENTE_DESTINO', $frente->ID_FRENTE)
                        ->where('ESTADO_MVO', 'RECIBIDO')
                        ->where('FECHA_RECEPCION', $now)
                        ->pluck('ID_MOVILIZACION')
                        ->toArray();
                }
            }

            \App\Models\Equipo::whereIn('ID_EQUIPO', $request->ids)->update([
                'ID_FRENTE_ACTUAL' => $frente->ID_FRENTE,
                'CONFIRMADO_EN_SITIO' => $generarPdf ? 0 : 1
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'movilizacion_ids' => $movilizacionIds,
                'count' => count($movilizacionIds),
                'generar_pdf' => $generarPdf
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Confirmar recepción de una movilización en tránsito (RECIBIR)
     */
    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $mov = Movilizacion::with('equipo', 'frenteOrigen', 'frenteDestino')->findOrFail($id);
            $usuario = auth()->user();

            $request->validate([
                'status' => 'required|in:RECIBIDO,RECHAZADO,RETORNADO',
                'DETALLE_UBICACION' => 'nullable|string|max:150'
            ]);

            // Validar autorización
            $esGlobal = ($usuario->NIVEL_ACCESO == 1);
            $usuarioFrente = $usuario->ID_FRENTE_ASIGNADO;

            if (!$esGlobal) {
                if ($request->status == 'RECIBIDO' && $usuarioFrente != $mov->ID_FRENTE_DESTINO) {
                    $errorMsg = 'Solo el frente destino puede confirmar la recepción';
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'error' => $errorMsg], 403);
                    }
                    abort(403, $errorMsg);
                }
            }

            // Validar que esté en tránsito
            if ($mov->ESTADO_MVO != 'TRANSITO') {
                $errorMsg = 'Esta movilización ya fue procesada';
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'error' => $errorMsg], 422);
                }
                return back()->withErrors(['error' => $errorMsg]);
            }

            // 1. Actualizar movilización
            $mov->update([
                'ESTADO_MVO' => 'RECIBIDO',
                'FECHA_RECEPCION' => now(),
                'DETALLE_UBICACION' => $request->DETALLE_UBICACION,
                'USUARIO_RECEPCION' => $usuario->CORREO_ELECTRONICO ?? 'SISTEMA',
            ]);

            // 2. Actualizar equipo: confirmar en el frente destino
            if ($mov->equipo) {
                $mov->equipo->update([
                    'ID_FRENTE_ACTUAL' => $mov->ID_FRENTE_DESTINO,
                    'DETALLE_UBICACION_ACTUAL' => $request->DETALLE_UBICACION,
                    'CONFIRMADO_EN_SITIO' => 1
                ]);
            }

            DB::commit();

            $message = 'Equipo recibido exitosamente en ' . ($mov->frenteDestino->NOMBRE_FRENTE ?? 'Destino');

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en updateStatus: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Error al actualizar: ' . $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    /**
     * RECEPCIÓN DIRECTA: Registrar equipos que llegan sin movilización previa
     */
    public function recepcionDirecta(Request $request)
    {
        // El frente destino SIEMPRE es el frente del usuario que gestiona la recepción
        $request->merge(['ID_FRENTE_DESTINO' => auth()->user()->ID_FRENTE_ASIGNADO]);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:equipos,ID_EQUIPO',
            'ID_FRENTE_DESTINO' => 'required|exists:frentes_trabajo,ID_FRENTE',
            'DETALLE_UBICACION' => 'nullable|string|max:150',
        ]);

        DB::beginTransaction();
        try {
            $usuario = auth()->user();
            $now = now();
            $frenteDestino = FrenteTrabajo::findOrFail($request->ID_FRENTE_DESTINO);

            $equipos = \App\Models\Equipo::with('frenteActual')
                ->whereIn('ID_EQUIPO', $request->ids)
                ->get();

            $insertData = [];
            foreach ($equipos as $equipo) {
                $insertData[] = [
                    'CODIGO_CONTROL' => null, // Recepciones directas no tienen código de control
                    'ID_EQUIPO' => $equipo->ID_EQUIPO,
                    'ID_FRENTE_ORIGEN' => $equipo->ID_FRENTE_ACTUAL ?? $request->ID_FRENTE_DESTINO,
                    'ID_FRENTE_DESTINO' => $request->ID_FRENTE_DESTINO,
                    'DETALLE_UBICACION' => $request->DETALLE_UBICACION,
                    'FECHA_DESPACHO' => null, // No hubo despacho
                    'FECHA_RECEPCION' => $now,
                    'ESTADO_MVO' => 'RECIBIDO',
                    'TIPO_MOVIMIENTO' => 'RECEPCION_DIRECTA',
                    'USUARIO_REGISTRO' => $usuario->CORREO_ELECTRONICO ?? 'SISTEMA',
                    'USUARIO_RECEPCION' => $usuario->CORREO_ELECTRONICO ?? 'SISTEMA',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($insertData)) {
                Movilizacion::insert($insertData);
            }

            // Actualizar equipos
            \App\Models\Equipo::whereIn('ID_EQUIPO', $request->ids)->update([
                'ID_FRENTE_ACTUAL' => $request->ID_FRENTE_DESTINO,
                'DETALLE_UBICACION_ACTUAL' => $request->DETALLE_UBICACION,
                'CONFIRMADO_EN_SITIO' => 1,
            ]);

            DB::commit();

            $ubicacionTexto = $frenteDestino->NOMBRE_FRENTE;
            if ($request->filled('DETALLE_UBICACION')) {
                $ubicacionTexto .= ' → ' . $request->DETALLE_UBICACION;
            }

            return response()->json([
                'success' => true,
                'message' => count($request->ids) . ' equipo(s) recibido(s) directamente en ' . $ubicacionTexto,
                'count' => count($request->ids),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en recepcionDirecta: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Buscar equipos para recepción directa
     */
    public function buscarEquiposParaRecepcion(Request $request)
    {
        $query = \App\Models\Equipo::with(['tipo', 'frenteActual', 'documentacion', 'especificaciones:ID_ESPEC,FOTO_REFERENCIAL']);

        if ($request->filled('search')) {
            $search = $request->search;
            $searchUpper = strtoupper(trim($search));

            if (strpos($searchUpper, '#') !== false) {
                // Mode: Tag Number Search
                $tagSearch = str_replace('#', '', $searchUpper);
                $query->where('NUMERO_ETIQUETA', 'like', "%{$tagSearch}%");

            } elseif (strpos($searchUpper, '-') !== false) {
                // Mode: Yard Code Search
                $query->where('CODIGO_PATIO', 'like', "%{$searchUpper}%");

            } else {
                // Standard search — O/0 ambiguity applied ONLY to PLACA
                $placaVariants = collect([
                    $searchUpper,
                    str_replace('O', '0', $searchUpper),
                    str_replace('0', 'O', $searchUpper),
                    str_replace(['O', '0'], ['0', 'O'], $searchUpper),
                ])->unique()->values()->all();

                $query->where(function ($q) use ($searchUpper, $placaVariants) {
                    $q->where('SERIAL_CHASIS', 'like', "%{$searchUpper}%")
                      ->orWhere('SERIAL_DE_MOTOR', 'like', "%{$searchUpper}%")
                      ->orWhere('CODIGO_PATIO', 'like', "%{$searchUpper}%")
                      ->orWhere('NUMERO_ETIQUETA', 'like', "%{$searchUpper}%")
                      ->orWhereHas('documentacion', function ($d) use ($placaVariants) {
                          $d->where(function ($pq) use ($placaVariants) {
                              foreach ($placaVariants as $variant) {
                                  $pq->orWhere('PLACA', 'like', "%{$variant}%");
                              }
                          });
                      });
                });
            }
        }

        $equipos = $query->orderBy('CODIGO_PATIO')->limit(20)->get();

        return response()->json($equipos->map(function ($eq) {
            // Determinar la mejor foto disponible
            $foto = null;
            if ($eq->FOTO_EQUIPO) {
                $foto = $eq->FOTO_EQUIPO;
            } elseif ($eq->especificaciones && $eq->especificaciones->FOTO_REFERENCIAL) {
                $foto = $eq->especificaciones->FOTO_REFERENCIAL;
            }

            return [
                'ID_EQUIPO' => $eq->ID_EQUIPO,
                'TIPO' => $eq->tipo->nombre ?? 'N/A',
                'CODIGO_PATIO' => $eq->CODIGO_PATIO,
                'SERIAL_CHASIS' => $eq->SERIAL_CHASIS,
                'PLACA' => $eq->documentacion->PLACA ?? 'S/P',
                'MARCA' => $eq->MARCA,
                'MODELO' => $eq->MODELO,
                'ANIO' => $eq->ANIO,
                'FRENTE_ACTUAL' => $eq->frenteActual->NOMBRE_FRENTE ?? 'Sin Asignar',
                'FRENTE_ACTUAL_ESTATUS' => $eq->frenteActual->ESTATUS_FRENTE ?? null,
                'CONFIRMADO' => $eq->CONFIRMADO_EN_SITIO,
                'DETALLE_UBICACION' => $eq->DETALLE_UBICACION_ACTUAL,
                'FOTO' => $foto, // URL de foto del equipo o referencial
            ];
        }));
    }

    /**
     * API: Obtener subdivisiones de un frente
     */
    public function getSubdivisiones($id)
    {
        $frente = FrenteTrabajo::findOrFail($id);
        $subdivisiones = [];
        if ($frente->SUBDIVISIONES && trim($frente->SUBDIVISIONES) !== '') {
            $subdivisiones = array_filter(array_map('trim', explode(',', $frente->SUBDIVISIONES)));
        }
        return response()->json([
            'nombre' => $frente->NOMBRE_FRENTE,
            'subdivisiones' => array_values($subdivisiones),
            'tiene_subdivisiones' => count($subdivisiones) > 0,
        ]);
    }

    /**
     * Generar PDF del Acta de Traslado (Agrupado por CODIGO_CONTROL)
     */
    public function generarActaTraslado($id)
    {
        try {
            $baseMov = Movilizacion::findOrFail($id);

            $movilizaciones = Movilizacion::with([
                'equipo.tipo',
                'equipo.documentacion',
                'equipo.especificaciones',
                'frenteOrigen',
                'frenteDestino',
                'usuario'
            ])
                ->where('CODIGO_CONTROL', $baseMov->CODIGO_CONTROL)
                ->get();

            if ($movilizaciones->isEmpty()) {
                return back()->withErrors(['error' => 'No se encontraron registros para esta movilización.']);
            }

            $movilizacion = $movilizaciones->first();

            $frenteOrigen = FrenteTrabajo::find($movilizacion->ID_FRENTE_ORIGEN);
            $frenteDestino = FrenteTrabajo::find($movilizacion->ID_FRENTE_DESTINO);

            if (!$frenteDestino) {
                return back()->withErrors(['error' => 'No se encontró el frente de destino']);
            }

            $pdf = new ActaTrasladoPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $pdf->frenteOrigen = $frenteOrigen->NOMBRE_FRENTE ?? 'OFICINA PRINCIPAL';
            $pdf->setPrintHeader(true);
            $pdf->setPrintFooter(true);
            $pdf->SetMargins(15, 42, 15);  // top=42 para dejar espacio al header nativo
            $pdf->SetHeaderMargin(8);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 10);

            $equipos = $movilizaciones->map(function ($mov) {
                return $mov->equipo;
            });

            $html = view('admin.movilizaciones.acta_traslado_pdf', compact('movilizaciones', 'equipos', 'movilizacion', 'frenteOrigen', 'frenteDestino'))->render();

            $html = str_replace("this.closest('div[style*='position: fixed']').remove();", "", $html);

            $pdf->writeHTML($html, true, false, true, false, '');

            $filename = 'Acta_Traslado_' . $movilizacion->CODIGO_CONTROL . '.pdf';

            return $pdf->Output($filename, 'D');

        } catch (\Exception $e) {
            \Log::error('Error generando Acta de Traslado: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al generar el acta: ' . $e->getMessage()]);
        }
    }
}

// Clase personalizada para el PDF
class ActaTrasladoPDF extends \TCPDF
{
    public $frenteOrigen = '';

    public function Header()
    {
        $image_file = public_path('img/imagen_uno.jpg');
        if (file_exists($image_file)) {
            $this->Image($image_file, 15, 8, 0, 25, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        $this->SetFont('helvetica', '', 8.5);
        $frente = strtoupper($this->frenteOrigen ?: 'OFICINA PRINCIPAL');
        $html = '<div style="text-align: right; line-height: 1.8;"><strong>FECHA DE EMISI&Oacute;N:</strong> ' . \Carbon\Carbon::now()->format('d/m/Y') . '<br><strong>FRENTE DE ORIGEN:</strong> ' . $frente . '<br>EMITIDO POR SISTEMA DE GESTI&Oacute;N DE FLOTA</div>';
        $this->writeHTMLCell(0, 0, 15, 20, $html, 0, 1, 0, true, 'R', true);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}
