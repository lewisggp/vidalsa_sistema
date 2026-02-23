<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipo;
use App\Models\Movilizacion;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Alerts (Poliza + ROTC + RACDA) - Optimized with Daily Cache
        // Cache until the end of the current day (midnight)
        $totalAlerts = \Illuminate\Support\Facades\Cache::remember('dashboard_total_alerts', Carbon::now()->endOfDay(), function() {
            $now = Carbon::now();
            $in30Days = $now->copy()->addDays(30);
            
            $stats = \App\Models\Documentacion::selectRaw("
                COUNT(CASE WHEN FECHA_VENC_POLIZA < ? THEN 1 END) as poliza,
                COUNT(CASE WHEN FECHA_ROTC < ? THEN 1 END) as rotc,
                COUNT(CASE WHEN FECHA_RACDA < ? THEN 1 END) as racda
            ", [$in30Days, $in30Days, $in30Days])->first();

            return ($stats->poliza ?? 0) + ($stats->rotc ?? 0) + ($stats->racda ?? 0);
        });

        // 2. Mobilizations Today - Cached by MovilizacionObserver
        $movilizacionesHoy = \Illuminate\Support\Facades\Cache::rememberForever('dashboard_movilizaciones_hoy', function() {
            return Movilizacion::whereDate('FECHA_DESPACHO', Carbon::today())->count();
        });

        // 3. Pending Mobilizations - Optimized
        $pendientes = \Illuminate\Support\Facades\Cache::rememberForever('dashboard_pendientes', function () {
            return Movilizacion::where('ESTADO_MVO', 'TRANSITO')->count(); 
        }); 

        // 4. Alerts List (Cached Daily) - Expired AND Expiring Soon (30 days)
        // UPDATED TO v2 for consistency with AJAX endpoint and Observer
        $expiredList = \Illuminate\Support\Facades\Cache::remember('dashboard_expired_list_v3', Carbon::now()->endOfDay(), function() {
            return $this->generateAlertsList();
        });

        // 5. Recent Activity - Cached by MovilizacionObserver (ALL pending, not just 5)
        $recentActivity = \Illuminate\Support\Facades\Cache::rememberForever('dashboard_recent_activity', function() {
            return Movilizacion::with(['equipo.tipo', 'equipo.documentacion', 'frenteDestino'])
                ->where('ESTADO_MVO', 'TRANSITO')
                ->orderBy('created_at', 'desc')
                ->get();
        });

        return view('menu', compact(
            'totalAlerts', 
            'movilizacionesHoy', 
            'pendientes', 
            'recentActivity',
            'expiredList'
        ));
    }

    public function resetCache()
    {
        try {
            // 1. Clear Application Cache
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');

            // 2. Specific Google Drive Circuit Breaker (Redundant but safe)
            \Illuminate\Support\Facades\Cache::forget('google_drive_connection_error');

            // 3. Clear Internal Dashboard Caches
            \Illuminate\Support\Facades\Cache::forget('dashboard_total_alerts');
            \Illuminate\Support\Facades\Cache::forget('dashboard_movilizaciones_hoy');
            \Illuminate\Support\Facades\Cache::forget('dashboard_pendientes');
            \Illuminate\Support\Facades\Cache::forget('dashboard_expired_list_v3'); // Corrected key
            \Illuminate\Support\Facades\Cache::forget('dashboard_recent_activity');

            return back()->with('success', 'Sistema reiniciado correctamente. Las conexiones han sido restablecidas.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Reset Cache Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al reiniciar el sistema: ' . $e->getMessage()]);
        }
    }

    public function getAlertsHtml()
    {
        // NO CACHE - Direct fetch to ensure real-time updates for "Take Responsibility"
        $expiredList = $this->generateAlertsList();
        $totalAlerts = $expiredList->count();
        
        return response()->json([
            'html' => view('partials.dashboard_alerts', compact('expiredList'))->render(),
            'totalAlerts' => $totalAlerts
        ]);
    }

    /**
     * Generate alerts list for expired and expiring documents
     * Shared logic used by index(), getAlertsHtml(), and Observer
     */
    public function generateAlertsList()
    {
        $now = \Carbon\Carbon::now();
        $in30Days = $now->copy()->addDays(30);
        
        $equipos = Equipo::whereHas('documentacion', function($q) use ($in30Days) {
            $q->where('FECHA_VENC_POLIZA', '<', $in30Days)
              ->orWhere('FECHA_ROTC', '<', $in30Days)
              ->orWhere('FECHA_RACDA', '<', $in30Days);
        })
        ->with([
            'documentacion.frenteGestionPoliza',
            'documentacion.frenteGestionRotc',
            'documentacion.frenteGestionRacda',
            'tipo',
            'frenteActual'
        ])
        ->get();

        $alerts = collect();

        foreach ($equipos as $equipo) {
            $doc = $equipo->documentacion;
            
            // Poliza
            if ($doc->FECHA_VENC_POLIZA) {
                $fechaPoliza = \Carbon\Carbon::parse($doc->FECHA_VENC_POLIZA);
                
                // Determine Status
                $status = $fechaPoliza->lt($now) ? 'expired' : ($fechaPoliza->lt($in30Days) ? 'warning' : 'valid');
                
                if ($status !== 'valid') {
                    $alerts->push((object)[
                        'equipo' => $equipo,
                        'type_key' => 'poliza',
                        'label' => $status === 'expired' ? 'Póliza Vencida' : 'Póliza Por Vencer',
                        'fecha' => $doc->FECHA_VENC_POLIZA,
                        'current_link' => $doc->LINK_POLIZA_SEGURO,
                        'status' => $status,
                        'gestionado_por' => $doc->frenteGestionPoliza ? $doc->frenteGestionPoliza->NOMBRE_FRENTE : null,
                        'fecha_gestion' => $doc->poliza_gestion_fecha
                    ]);
                }
            }

            // ROTC
            if ($doc->FECHA_ROTC) {
                $fechaRotc = \Carbon\Carbon::parse($doc->FECHA_ROTC);
                $status = $fechaRotc->lt($now) ? 'expired' : ($fechaRotc->lt($in30Days) ? 'warning' : 'valid');
                
                if ($status !== 'valid') {
                    $alerts->push((object)[
                        'equipo' => $equipo,
                        'type_key' => 'rotc',
                        'label' => $status === 'expired' ? 'ROTC Vencido' : 'ROTC Por Vencer',
                        'fecha' => $doc->FECHA_ROTC,
                        'current_link' => $doc->LINK_ROTC,
                        'status' => $status,
                        'gestionado_por' => $doc->frenteGestionRotc ? $doc->frenteGestionRotc->NOMBRE_FRENTE : null,
                        'fecha_gestion' => $doc->rotc_gestion_fecha
                    ]);
                }
            }

            // RACDA
            if ($doc->FECHA_RACDA) {
                $fechaRacda = \Carbon\Carbon::parse($doc->FECHA_RACDA);
                $status = $fechaRacda->lt($now) ? 'expired' : ($fechaRacda->lt($in30Days) ? 'warning' : 'valid');
                
                if ($status !== 'valid') {
                    $alerts->push((object)[
                        'equipo' => $equipo,
                        'type_key' => 'racda',
                        'label' => $status === 'expired' ? 'RACDA Vencido' : 'RACDA Por Vencer',
                        'fecha' => $doc->FECHA_RACDA,
                        'current_link' => $doc->LINK_RACDA,
                        'status' => $status,
                        'gestionado_por' => $doc->frenteGestionRacda ? $doc->frenteGestionRacda->NOMBRE_FRENTE : null,
                        'fecha_gestion' => $doc->racda_gestion_fecha
                    ]);
                }
            }
        }

        // Separate expired from warnings
        $expired = $alerts->where('status', 'expired')->sortBy('fecha')->values();
        $warnings = $alerts->where('status', 'warning')->sortBy('fecha')->values();
        
        // Combine: warnings first (upcoming), then expired
        return $warnings->concat($expired)->values();
    }

    /**
     * Start management of a document
     */
    public function iniciarGestion(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,ID_EQUIPO',
            'doc_type' => 'required|in:poliza,rotc,racda'
        ]);

        $user = auth()->user();
        
        // CHECK PERMISSIONS (Matching CAN_UPDATE_INFO logic)
        if (!$user->can('super.admin') && 
            !$user->can('equipos.edit') && 
            !$user->can('user.edit') && 
            !$user->can('Actualizar Información')) 
        {
            return response()->json(['success' => false, 'message' => 'No tiene permisos para realizar esta acción.'], 403);
        }

        if (!$user->ID_FRENTE_ASIGNADO) {
            return response()->json(['success' => false, 'message' => 'Debe pertenecer a un frente para iniciar gestión'], 403);
        }

        $doc = \App\Models\Documentacion::where('ID_EQUIPO', $request->equipo_id)->first();
        if (!$doc) return response()->json(['success' => false, 'message' => 'Documentación no encontrada'], 404);

        $frenteField = $request->doc_type . '_gestion_frente_id';
        $fechaField = $request->doc_type . '_gestion_fecha';

        $doc->$frenteField = $user->ID_FRENTE_ASIGNADO;
        $doc->$fechaField = now();
        $doc->save();

        // Clear Cache
        \Illuminate\Support\Facades\Cache::forget('dashboard_total_alerts');
        \Illuminate\Support\Facades\Cache::forget('dashboard_expired_list_v3');

        return response()->json(['success' => true]);
    }
    
    /**
     * Generate PDF Report of Expired & Expiring Documents
     */
    public function exportDocumentsPDF()
    {
        try {
            // Get current user info
            $user = auth()->user();
            $nombreUsuario = $user->NOMBRE_USUARIO ?? 'Sistema';
            $nombreFrente = $user->frenteAsignado ? $user->frenteAsignado->NOMBRE_FRENTE : 'Sin Frente Asignado';
            $fechaEmision = \Carbon\Carbon::now()->locale('es')->isoFormat('DD [de] MMMM [de] YYYY - HH:mm');

            // Get alerts list
            $alertsList = $this->generateAlertsList();
            
            // Separate by status and Sort by Equipment Type for grouping
            $vencidos = $alertsList->filter(function($alert) {
                return $alert->status === 'expired';
            })->sortBy('equipo.tipo.nombre')->values();
            
            $proximos = $alertsList->filter(function($alert) {
                return $alert->status === 'warning';
            })->sortBy('equipo.tipo.nombre')->values();

            // Calculate totals
            $totalVencidos = $vencidos->count();
            $totalProximos = $proximos->count();
            
            // Get unique equipment count
            $totalEquipos = $alertsList->pluck('equipo.ID_EQUIPO')->unique()->count();

            // MANUAL LOADING OF TCPDF (Emergency Mode)
            // If the package is physically present but not autoloaded yet
            if (!class_exists('TCPDF')) {
                $tcpdfPath = base_path('vendor/tecnickcom/tcpdf/tcpdf.php');
                if (file_exists($tcpdfPath)) {
                    require_once($tcpdfPath);
                }
            }

            // Render View to HTML
            $html = view('reports.documentos_vencidos_pdf', compact(
                'vencidos',
                'proximos',
                'nombreUsuario',
                'nombreFrente',
                'fechaEmision',
                'totalVencidos',
                'totalProximos',
                'totalEquipos'
            ))->render();

            if (class_exists('TCPDF')) {
                $pdf = new ReportePDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                
                // Set document information
                $pdf->SetCreator('Sistema de Gestión');
                $pdf->SetAuthor($nombreUsuario);
                $pdf->SetTitle('Reporte de Documentos Vencidos');

                // Configuración de Márgenes (2cm)
                // Configuración de Márgenes
                $pdf->setPrintHeader(true); 
                $pdf->setPrintFooter(true); 
                $pdf->SetMargins(25, 40, 25); // Margen superior 4cm para bajar el contenido y no chocar con el header grande
                $pdf->SetHeaderMargin(10); // Header a 1cm del borde
                $pdf->SetAutoPageBreak(TRUE, 25);
                
                // Add a page
                $pdf->AddPage();
                
                // Write HTML
                $pdf->writeHTML($html, true, false, true, false, '');
                
                // Download
                $filename = 'Reporte_Documentos_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.pdf';
                
                return response($pdf->Output($filename, 'S')) // S = Return as string
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            } else {
                 throw new \Exception('La librería TCPDF no se encuentra instalada correctamente.');
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PDF Export Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al generar PDF: ' . $e->getMessage()]);
        }
    }
}

// Clase para personalizar el pie de página del PDF
if (class_exists('TCPDF') && !class_exists('ReportePDF')) {
    class ReportePDF extends \TCPDF {
        public function Header() {
            // Imagen a 1cm (10mm) del borde superior y 2.5cm (25mm) del izquierdo
            $image_file = public_path('img/imagen_uno.jpg');
            // Image(file, x, y, w, h) -> h=25mm (Más grande)
            if (file_exists($image_file)) {
                $this->Image($image_file, 25, 10, 0, 25, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            }

            // Texto a la derecha, alineado con la base de la foto (Y=27 aprox para base en 35)
            $this->SetFont('helvetica', '', 8.5);
            $html = '<div style="text-align: right;"><strong>FECHA DE EMISIÓN:</strong> ' . \Carbon\Carbon::now()->format('d/m/Y') . '<br>EMITIDO POR SISTEMA DE GESTIÓN DE FLOTA</div>';
            
            // Renderizar HTML Cell
            $this->writeHTMLCell(0, 0, 25, 27, $html, 0, 1, 0, true, 'R', true);
        }

        public function Footer() {
            $this->SetY(-15);
            $this->SetFont('helvetica', '', 8.5); // Sin cursiva y tamaño 8.5
            $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
        }
    }
}
