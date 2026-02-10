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
        $expiredList = \Illuminate\Support\Facades\Cache::remember('dashboard_expired_list_v2', Carbon::now()->endOfDay(), function() {
            return $this->generateAlertsList();
        });

        // 5. Recent Activity - Cached by MovilizacionObserver (ALL pending, not just 5)
        $recentActivity = \Illuminate\Support\Facades\Cache::rememberForever('dashboard_recent_activity', function() {
            return Movilizacion::with(['equipo.tipo', 'frenteDestino'])
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
            \Illuminate\Support\Facades\Cache::forget('dashboard_expired_list_v2'); // Corrected key
            \Illuminate\Support\Facades\Cache::forget('dashboard_recent_activity');

            return back()->with('success', 'Sistema reiniciado correctamente. Las conexiones han sido restablecidas.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Reset Cache Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al reiniciar el sistema: ' . $e->getMessage()]);
        }
    }

    public function getAlertsHtml()
    {
        // 5. Alerts List (Reuse existing cache logic)
        // Since DocumentacionObserver updates this cache on change, we just need to fetch it.
        // KEY UPDATED TO v2 TO FORCE REFRESH AFTER STRUCTURE CHANGE
        $expiredList = \Illuminate\Support\Facades\Cache::remember('dashboard_expired_list_v2', \Carbon\Carbon::now()->endOfDay(), function() {
            return $this->generateAlertsList();
        });

        // Recalculate total count as well for sidebar badge if needed
        $now = \Carbon\Carbon::now();
        $in30Days = $now->copy()->addDays(30);
        $totalAlerts = \Illuminate\Support\Facades\Cache::remember('dashboard_total_alerts', $now->copy()->endOfDay(), function() use ($now, $in30Days) {
             $stats = \App\Models\Documentacion::selectRaw("
                COUNT(CASE WHEN FECHA_VENC_POLIZA < ? THEN 1 END) as poliza,
                COUNT(CASE WHEN FECHA_ROTC < ? THEN 1 END) as rotc,
                COUNT(CASE WHEN FECHA_RACDA < ? THEN 1 END) as racda
            ", [$in30Days, $in30Days, $in30Days])->first();
            return ($stats->poliza ?? 0) + ($stats->rotc ?? 0) + ($stats->racda ?? 0);
        });

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
        ->with(['documentacion', 'tipo'])
        ->get();

        $alerts = collect();

        foreach ($equipos as $equipo) {
            $doc = $equipo->documentacion;
            
            // Poliza
            if ($doc->FECHA_VENC_POLIZA) {
                $fechaPoliza = \Carbon\Carbon::parse($doc->FECHA_VENC_POLIZA);
                if ($fechaPoliza->lt($now)) {
                    $alerts->push((object)[
                        'equipo' => $equipo,
                        'type_key' => 'poliza',
                        'label' => 'Póliza Vencida',
                        'fecha' => $doc->FECHA_VENC_POLIZA,
                        'current_link' => $doc->LINK_POLIZA_SEGURO,
                        'status' => 'expired'
                    ]);
                } elseif ($fechaPoliza->lt($in30Days)) {
                    $alerts->push((object)[
                        'equipo' => $equipo,
                        'type_key' => 'poliza',
                        'label' => 'Póliza Por Vencer',
                        'fecha' => $doc->FECHA_VENC_POLIZA,
                        'current_link' => $doc->LINK_POLIZA_SEGURO,
                        'status' => 'warning'
                    ]);
                }
            }

            // ROTC
            if ($doc->FECHA_ROTC) {
                $fechaRotc = \Carbon\Carbon::parse($doc->FECHA_ROTC);
                if ($fechaRotc->lt($now)) {
                    $alerts->push((object)[
                        'equipo' => $equipo,
                        'type_key' => 'rotc',
                        'label' => 'ROTC Vencido',
                        'fecha' => $doc->FECHA_ROTC,
                        'current_link' => $doc->LINK_ROTC,
                        'status' => 'expired'
                    ]);
                } elseif ($fechaRotc->lt($in30Days)) {
                    $alerts->push((object)[
                        'equipo' => $equipo,
                        'type_key' => 'rotc',
                        'label' => 'ROTC Por Vencer',
                        'fecha' => $doc->FECHA_ROTC,
                        'current_link' => $doc->LINK_ROTC,
                        'status' => 'warning'
                    ]);
                }
            }

            // RACDA
            if ($doc->FECHA_RACDA) {
                $fechaRacda = \Carbon\Carbon::parse($doc->FECHA_RACDA);
                if ($fechaRacda->lt($now)) {
                    $alerts->push((object)[
                        'equipo' => $equipo,
                        'type_key' => 'racda',
                        'label' => 'RACDA Vencido',
                        'fecha' => $doc->FECHA_RACDA,
                        'current_link' => $doc->LINK_RACDA,
                        'status' => 'expired'
                    ]);
                } elseif ($fechaRacda->lt($in30Days)) {
                    $alerts->push((object)[
                        'equipo' => $equipo,
                        'type_key' => 'racda',
                        'label' => 'RACDA Por Vencer',
                        'fecha' => $doc->FECHA_RACDA,
                        'current_link' => $doc->LINK_RACDA,
                        'status' => 'warning'
                    ]);
                }
            }
        }

        // Separate expired from warnings
        $expired = $alerts->where('status', 'expired')->sortBy('fecha')->values()->take(20);
        $warnings = $alerts->where('status', 'warning')->sortBy('fecha')->values()->take(20);
        
        // Combine: warnings first (upcoming), then expired
        return $warnings->concat($expired)->values();
    }
}
