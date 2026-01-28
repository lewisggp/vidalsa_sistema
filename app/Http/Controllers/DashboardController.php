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
        // 2. Expired Policies (Alerts) - Optimized with Daily Cache

        // Check documentacion table via relationship
        // 2. Total Alerts (Poliza + ROTC + RACDA) - Optimized with Daily Cache
        // Cache until the end of the current day (midnight)
        $totalAlerts = \Illuminate\Support\Facades\Cache::remember('dashboard_total_alerts', Carbon::now()->endOfDay(), function() {
            $now = Carbon::now();
            
            $stats = \App\Models\Documentacion::selectRaw("
                COUNT(CASE WHEN FECHA_VENC_POLIZA < ? THEN 1 END) as poliza,
                COUNT(CASE WHEN FECHA_ROTC < ? THEN 1 END) as rotc,
                COUNT(CASE WHEN FECHA_RACDA < ? THEN 1 END) as racda
            ", [$now, $now, $now])->first();

            return ($stats->poliza ?? 0) + ($stats->rotc ?? 0) + ($stats->racda ?? 0);
        });

        // 3. Mobilizations Today - Cached by MovilizacionObserver
        $movilizacionesHoy = \Illuminate\Support\Facades\Cache::rememberForever('dashboard_movilizaciones_hoy', function() {
            return Movilizacion::whereDate('FECHA_DESPACHO', Carbon::today())->count();
        });

        // 4. Pending Mobilizations - Optimized
        $pendientes = \Illuminate\Support\Facades\Cache::rememberForever('dashboard_pendientes', function () {
            return Movilizacion::where('ESTADO_MVO', 'TRANSITO')->count(); 
        }); 

        // 5. Expired Policies List (Cached Daily) - Expanded for ROTC/RACDA
        $expiredList = \Illuminate\Support\Facades\Cache::remember('dashboard_expired_list_all', Carbon::now()->endOfDay(), function() {
            $now = Carbon::now();
            $equipos = Equipo::whereHas('documentacion', function($q) use ($now) {
                $q->where('FECHA_VENC_POLIZA', '<', $now)
                  ->orWhere('FECHA_ROTC', '<', $now)
                  ->orWhere('FECHA_RACDA', '<', $now);
            })
            ->with(['documentacion', 'tipo'])
            ->get();

            $alerts = collect();

            foreach ($equipos as $equipo) {
                $doc = $equipo->documentacion;
                
                // Poliza
                if ($doc->FECHA_VENC_POLIZA && Carbon::parse($doc->FECHA_VENC_POLIZA)->lt($now)) {
                    $alerts->push((object)[
                        'equipo' => $equipo,
                        'type_key' => 'poliza',
                        'label' => 'Póliza Vencida',
                        'fecha' => $doc->FECHA_VENC_POLIZA,
                        'current_link' => $doc->LINK_POLIZA_SEGURO
                    ]);
                }

                // ROTC
                if ($doc->FECHA_ROTC && Carbon::parse($doc->FECHA_ROTC)->lt($now)) {
                    $alerts->push((object)[
                        'equipo' => $equipo,
                        'type_key' => 'rotc',
                        'label' => 'ROTC Vencido',
                        'fecha' => $doc->FECHA_ROTC,
                        'current_link' => $doc->LINK_ROTC
                    ]);
                }

                // RACDA
                if ($doc->FECHA_RACDA && Carbon::parse($doc->FECHA_RACDA)->lt($now)) {
                    $alerts->push((object)[
                        'equipo' => $equipo,
                        'type_key' => 'racda',
                        'label' => 'RACDA Vencido',
                        'fecha' => $doc->FECHA_RACDA,
                        'current_link' => $doc->LINK_RACDA
                    ]);
                }
            }

            return $alerts->sortBy('fecha')->values()->take(20);
        });

        // 6. Recent Activity - Cached by MovilizacionObserver
        $recentActivity = \Illuminate\Support\Facades\Cache::rememberForever('dashboard_recent_activity', function() {
            return Movilizacion::with(['equipo', 'frenteDestino'])
                ->where('ESTADO_MVO', 'TRANSITO')
                ->orderBy('created_at', 'desc')
                ->take(5)
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
}
