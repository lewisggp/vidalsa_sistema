<?php

namespace App\Http\Controllers;

use App\Models\Movilizacion;
use App\Models\FrenteTrabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovilizacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Movilizacion::with(['equipo.tipo', 'equipo.especificaciones', 'equipo.documentacion', 'frenteOrigen', 'frenteDestino', 'usuario']);

        // Smart Search Filters (Pattern-Based Optimization)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $searchUpper = strtoupper($search);
            
            $query->where(function($q) use ($search, $searchUpper) {
                // Pattern 1: MV-XXXXX or MVXXXXX → Search CODIGO_CONTROL
                if (preg_match('/^MV-?\d+/i', $search)) {
                    $cleanSearch = ltrim(str_replace(['MV-', 'MV'], '', $searchUpper), '0');
                    $q->where('CODIGO_CONTROL', 'like', "%{$searchUpper}%")
                      ->orWhere('CODIGO_CONTROL', 'like', "%{$cleanSearch}%");
                }
                // Pattern 2: DD-MM-YYYY format → Search CODIGO_PATIO
                elseif (preg_match('/\d{2}-\d{2}-\d{4}/', $search)) {
                    $q->whereHas('equipo', function($qEq) use ($search) {
                        $qEq->where('CODIGO_PATIO', 'like', "%{$search}%");
                    });
                }
                // Pattern 3: #NUMBER → Search NUMERO_ETIQUETA
                elseif (strpos($search, '#') === 0) {
                    $numeroEtiqueta = ltrim($search, '#');
                    $q->whereHas('equipo', function($qEq) use ($numeroEtiqueta) {
                        $qEq->where('NUMERO_ETIQUETA', 'like', "%{$numeroEtiqueta}%");
                    });
                }
                // Pattern 4: Contains hyphen → Search CODIGO_PATIO
                elseif (strpos($search, '-') !== false) {
                    $q->whereHas('equipo', function($qEq) use ($search) {
                        $qEq->where('CODIGO_PATIO', 'like', "%{$search}%");
                    });
                }
                // Pattern 5: Default → Search PLACA and SERIAL_CHASIS
                else {
                    $q->whereHas('equipo', function($qEq) use ($search) {
                        $qEq->where('SERIAL_CHASIS', 'like', "%{$search}%")
                            ->orWhereHas('documentacion', function($qDoc) use ($search) {
                                $qDoc->where('PLACA', 'like', "%{$search}%");
                            });
                    });
                }
            });
        }

        if ($request->filled('id_frente') && $request->id_frente !== 'all') {
            $query->where(function($q) use ($request) {
                $q->where('ID_FRENTE_DESTINO', $request->id_frente)
                  ->orWhere('ID_FRENTE_ORIGEN', $request->id_frente);
            });
        }

        if ($request->filled('id_tipo') && $request->id_tipo !== 'all') {
            $query->whereHas('equipo', function($q) use ($request) {
                $q->where('id_tipo_equipo', $request->id_tipo);
            });
        }

        if ($request->filled('id_frente_origen')) {
            $query->where('ID_FRENTE_ORIGEN', $request->id_frente_origen);
        }

        $movilizaciones = $query->orderBy('FECHA_DESPACHO', 'desc')->paginate(12);

        // Stats: Total In Transit & In Transit by Destination Front
        // These are GLOBAL stats (not filtered by search/table filters) as per dashboard requirements
        $totalTransito = Movilizacion::where('ESTADO_MVO', 'TRANSITO')->count();
        
        $transitoPorFrente = Movilizacion::where('ESTADO_MVO', 'TRANSITO')
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

            // Rebuild Stats HTML for AJAX updates (although these are global, keeping them dynamic is good practice)
            $statsHtml = '<h4 style="margin: 0 0 15px 0; font-size: 13px; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="material-icons" style="font-size: 18px; color: #8b5cf6;">local_shipping</i>
                En Tránsito por Frente
            </h4>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">';
            
            if ($transitoPorFrente->isNotEmpty()) {
                foreach($transitoPorFrente as $stat) {
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
                'totalTransito' => $totalTransito // Send this for frontend update if needed (will need JS update)
            ]);
        }

        return view('admin.movilizaciones.index', compact('movilizaciones', 'totalTransito', 'transitoPorFrente', 'frentes', 'allTipos'));
    }

    public function create()
    {
        // Only load available equipments (active and confirmed on site, or simply not in transit?)
        // Assuming we want to move any equipment that is supposedly on site.
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
        
        // Prevent moving if already in transit? (Optional safety check)
        if ($equipo->CONFIRMADO_EN_SITIO == 0) {
             // return back()->withErrors(['msg' => 'El equipo ya está en tránsito.']);
        }

        // Generate ID (Simulating Max + 1 logic for CODIGO_CONTROL if it is numeric)
        $lastLog = Movilizacion::latest('ID_MOVILIZACION')->first();
        $nextId = $lastLog ? ($lastLog->ID_MOVILIZACION + 1) : 1; 
        
        // Ensure ID_FRENTE_ORIGEN is valid. If null, maybe default to a generic one or error.
        $origen = $equipo->ID_FRENTE_ACTUAL ?? 1; // Fallback to 1 if null

        Movilizacion::create([
            'CODIGO_CONTROL' => $nextId, // Storing strict number
            'ID_EQUIPO' => $request->ID_EQUIPO,
            'ID_FRENTE_ORIGEN' => $origen,
            'ID_FRENTE_DESTINO' => $request->ID_FRENTE_DESTINO,
            'FECHA_DESPACHO' => now(),
            'ESTADO_MVO' => 'TRANSITO',
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
        ]);

        try {
            DB::beginTransaction();

            // 1. Find or Create the destination front
            // Use shared lock if high concurrency expected, but firstOrCreate is atomic enough for this.
            $frente = FrenteTrabajo::firstOrCreate(
                ['NOMBRE_FRENTE' => strtoupper($request->destination)],
                ['ESTATUS_FRENTE' => 'ACTIVO']
            );

            $user = auth()->user()->CORREO_ELECTRONICO ?? 'SISTEMA';
            $now = now();
            
            // 2. Get Next Control ID (Robustness: Locking to prevent race conditions in high concurrency)
            // We lock the last record to ensure we get a unique sequence for this batch
            $lastLog = Movilizacion::latest('ID_MOVILIZACION')->lockForUpdate()->first();
            $nextId = $lastLog ? ($lastLog->ID_MOVILIZACION + 1) : 1;
            
            // 3. Prepare Batch Insert Data
            // We fetch the 'origin' for each equipment efficiently. 
            // If all come from different origins, we must fetch them.
            // Optimization: Fetch only necessary columns.
            $equipos = \App\Models\Equipo::whereIn('ID_EQUIPO', $request->ids)->get(['ID_EQUIPO', 'ID_FRENTE_ACTUAL']);
            
            $insertData = [];
            foreach ($equipos as $equipo) {
                $insertData[] = [
                    'CODIGO_CONTROL' => $nextId,
                    'ID_EQUIPO' => $equipo->ID_EQUIPO,
                    'ID_FRENTE_ORIGEN' => $equipo->ID_FRENTE_ACTUAL ?? 1,
                    'ID_FRENTE_DESTINO' => $frente->ID_FRENTE,
                    'FECHA_DESPACHO' => $now,
                    'ESTADO_MVO' => 'TRANSITO',
                    'USUARIO_REGISTRO' => $user,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // 4. Batch Insert (One Query)
            $movilizacionIds = [];
            if (!empty($insertData)) {
                Movilizacion::insert($insertData);
                
                // Obtener los IDs de las movilizaciones recién creadas
                $movilizacionIds = Movilizacion::where('CODIGO_CONTROL', $nextId)
                    ->pluck('ID_MOVILIZACION')
                    ->toArray();
            }

            // 5. Batch Update Equipment Status (One Query)
            \App\Models\Equipo::whereIn('ID_EQUIPO', $request->ids)->update([
                'ID_FRENTE_ACTUAL' => $frente->ID_FRENTE,
                'CONFIRMADO_EN_SITIO' => 0
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'movilizacion_ids' => $movilizacionIds,
                'count' => count($movilizacionIds)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $mov = Movilizacion::with('equipo', 'frenteOrigen', 'frenteDestino')->findOrFail($id);
            $usuario = auth()->user();
            
            $request->validate([
                'status' => 'required|in:RECIBIDO,RETORNADO',
                'DETALLE_UBICACION' => 'nullable|string|max:150'
            ]);
            
            // Validar autorización
            $esGlobal = ($usuario->NIVEL_ACCESO == 1);
            $usuarioFrente = $usuario->ID_FRENTE_ASIGNADO;
            
            if (!$esGlobal) {
                // Usuarios LOCAL deben estar en el frente correcto
                if ($request->status == 'RECIBIDO' && $usuarioFrente != $mov->ID_FRENTE_DESTINO) {
                    abort(403, 'Solo el frente destino puede confirmar la recepción');
                }
                
                if ($request->status == 'RETORNADO' && $usuarioFrente != $mov->ID_FRENTE_ORIGEN) {
                    abort(403, 'Solo el frente origen puede confirmar el retorno');
                }
            }
            
            // Validar que esté en tránsito
            if ($mov->ESTADO_MVO != 'TRANSITO') {
                return back()->withErrors(['error' => 'Esta movilización ya fue procesada']);
            }
            
            // 1. Actualizar movilización
            $mov->update([
                'ESTADO_MVO' => $request->status,
                'FECHA_RECEPCION' => now(),
                'DETALLE_UBICACION' => $request->DETALLE_UBICACION // Guardar nombre del patio (Ej: Patio 1)
            ]);

            // 2. Actualizar tabla equipos según el estado
            if ($request->status == 'RECIBIDO') {
                // RECIBIDO en DESTINO
                // El equipo pasa a estar en el FRENTE DESTINO (Padre)
                // Y actualizamos su detalle de ubicación
                
                $mov->equipo->update([
                    'ID_FRENTE_ACTUAL' => $mov->ID_FRENTE_DESTINO,
                    'DETALLE_UBICACION_ACTUAL' => $request->DETALLE_UBICACION,
                    'CONFIRMADO_EN_SITIO' => 1
                ]);

                // Mensaje personalizado
                $nombreUbicacion = $mov->frenteDestino->NOMBRE_FRENTE;
                if ($request->filled('DETALLE_UBICACION')) {
                    $nombreUbicacion .= ' (' . $request->DETALLE_UBICACION . ')';
                }

                $message = 'Equipo recibido exitosamente en ' . $nombreUbicacion;
            } 
            elseif ($request->status == 'RETORNADO') {
                // RETORNADO → Volver al frente ORIGEN
                // Limpiamos el detalle porque vuelve al origen general (o se podría pedir detalle de retorno, pero por ahora general)
                $mov->equipo->update([
                    'ID_FRENTE_ACTUAL' => $mov->ID_FRENTE_ORIGEN,
                    'DETALLE_UBICACION_ACTUAL' => null, 
                    'CONFIRMADO_EN_SITIO' => 1
                ]);
                $message = 'Equipo retornado exitosamente a ' . $mov->frenteOrigen->NOMBRE_FRENTE;
            }

            DB::commit();
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en updateStatus: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    /**
     * Generar PDF del Acta de Traslado
     */
    /**
     * Generar PDF del Acta de Traslado (Agrupado por CODIGO_CONTROL)
     */
    public function generarActaTraslado($id)
    {
        try {
            // 1. Cargar la movilización base para obtener la REFERENCIA (CODIGO_CONTROL)
            $baseMov = Movilizacion::findOrFail($id);

            // 2. Buscar TODAS las movilizaciones que comparten ese código de control (el mismo lote de traslado)
            $movilizaciones = Movilizacion::with([
                'equipo.tipo',
                'equipo.documentacion',
                'equipo.especificaciones', // Para la marca si es necesaria
                'frenteOrigen',
                'frenteDestino',
                'usuario'
            ])
            ->where('CODIGO_CONTROL', $baseMov->CODIGO_CONTROL)
            ->get();

            if ($movilizaciones->isEmpty()) {
                return back()->withErrors(['error' => 'No se encontraron registros para esta movilización.']);
            }

            // Tomamos la primera para datos generales
            $movilizacion = $movilizaciones->first();

            // Obtener el frente de ORIGEN con sus responsables (Quienes firman la salida)
            $frenteOrigen = FrenteTrabajo::find($movilizacion->ID_FRENTE_ORIGEN);
            
            // Obtener el frente de DESTINO (Para indicar hacia donde va)
            $frenteDestino = FrenteTrabajo::find($movilizacion->ID_FRENTE_DESTINO);

            if (!$frenteDestino) {
                return back()->withErrors(['error' => 'No se encontró el frente de destino']);
            }

            // Generar PDF usando nuestra clase personalizada con Header
            $pdf = new ActaTrasladoPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Asignar el código de control para que aparezca en el Header
            $pdf->codigoControl = $movilizacion->CODIGO_CONTROL;
            
            // Configuración del documento
            $pdf->SetTitle('Acta de Traslado - ' . $movilizacion->CODIGO_CONTROL);
            
            // Márgenes: Arriba 35mm para dejar espacio al Header gráfico, Abajo 15mm
            $pdf->SetMargins(15, 35, 15); 
            $pdf->SetHeaderMargin(10);
            $pdf->SetAutoPageBreak(true, 15);
            
            // Habilitar Header y Footer personalizados
            $pdf->setPrintHeader(true);
            $pdf->setPrintFooter(true);
            
            $pdf->AddPage();

            // Renderizar vista pasando la COLECCIÓN de movilizaciones y los frentes
            $html = view('admin.movilizaciones.acta_traslado_pdf', compact('movilizaciones', 'movilizacion', 'frenteOrigen', 'frenteDestino'))->render();
            
            // CLEANUP: Eliminar script inyectado accidentalmente (posiblemente por herramientas de dev o extensiones) que aparece impreso
            $html = str_replace("this.closest('div[style*='position: fixed']').remove();", "", $html);

            $pdf->writeHTML($html, true, false, true, false, '');

            // Nombre del archivo
            $filename = 'Acta_Traslado_' . $movilizacion->CODIGO_CONTROL . '.pdf';

            // Descargar PDF
            return $pdf->Output($filename, 'D');

        } catch (\Exception $e) {
            \Log::error('Error generando Acta de Traslado: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al generar el acta: ' . $e->getMessage()]);
        }
    }
}

// Clase personalizada para el PDF con Header Gráfico Corporativo
class ActaTrasladoPDF extends \TCPDF {
    public $codigoControl = null;
    
    public function Header() {
        // Imagen 'imagen_uno.jpg' corporativa (Usada en reporte de alertas)
        $image_file = public_path('img/imagen_uno.jpg');
        
        // Logo a la izquierda (Coordenadas: X=15, Y=10, H=25)
        if (file_exists($image_file)) {
            $this->Image($image_file, 15, 10, 0, 25, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
             // Fallback si no existe la imagen_uno, buscamos logo.png
             $logo_bkp = public_path('images/maquinaria/logo.png');
             if(file_exists($logo_bkp)) {
                 $this->Image($logo_bkp, 15, 10, 0, 20, 'PNG');
             }
        }
        
        // Título centrado: ACTA DE TRASLADO
        $this->SetY(15);
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 5, 'ACTA DE TRASLADO', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        
        // Bloque Informativo Derecha: N° Operación + Fecha + Emisor
        $this->SetY(15);
        $this->SetX(15);
        $this->SetFont('helvetica', '', 9);
        
        $numeroOp = $this->codigoControl ? str_pad($this->codigoControl, 6, '0', STR_PAD_LEFT) : '---';
        
        $html = '<div style="text-align: right;">
            <strong>N° OPERACIÓN: ' . $numeroOp . '</strong><br>
            <strong>FECHA DE EMISIÓN:</strong> ' . \Carbon\Carbon::now()->format('d/m/Y') . '<br>
            EMITIDO POR SISTEMA DE GESTIÓN DE FLOTA
        </div>';
        
        $this->writeHTMLCell(0, 0, 15, 15, $html, 0, 0, 0, true, 'R', true);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'C');
    }
}
