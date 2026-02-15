@extends('layouts.estructura_base')

@section('title', 'Tablero de Control')

@section('content')

<style>
    /* Forzar fondo blanco solo en el dashboard */
    body, .main-viewport {
        background-color: #ffffff !important;
    }
</style>



<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none;">
    <svg viewBox="0 0 1440 900" preserveAspectRatio="xMinYMin slice" xmlns="http://www.w3.org/2000/svg" style="width: 100%; height: 100%;">
        <path d="M0 900 V 400 Q 150 750 600 850 T 1440 900 Z" fill="#00004d" />
        <path d="M1440 0 V 400 Q 1300 350 1200 0 Z" fill="#00004d" />
        <path d="M1440 900 V 500 Q 1350 650 1440 800 Z" fill="#00004d" opacity="0.9" />
    </svg>
</div>

<div class="dashboard-container" style="padding: 10px 20px; position: relative; z-index: 1;">
    
    <!-- Header -->
    <section class="page-title-card" style="text-align: left; margin: 0 0 10px 0;">
        <h1 class="page-title">
            <span class="page-title-line2" style="color: #000;">Sistema de Gestión de Equipos Operacionales</span>
        </h1>
    </section>

    <!-- Main Grid -->
    <div class="dashboard-grid">
        
        <!-- Column 1: Resumen Rápido (Cards) -->
        <div class="card-section" style="grid-column: span 12;">
            <div class="cards-wrapper">
                
                <!-- MOVILIZACIONES SECTION -->
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <!-- Card 3: Movilizaciones -->
                    <div class="dashboard-card card-blue" onclick="togglePendingMovs()">
                        <div class="icon-wrapper">
                            <i class="material-icons">local_shipping</i>
                        </div>
                        <div class="card-content">
                            <span class="card-label">Movilizaciones Hoy</span>
                            <div class="card-value-row">
                                <span class="card-value">{{ $movilizacionesHoy }}</span>
                                <span class="card-subtext-inline">| {{ $pendientes }} Por Confirmar</span>
                            </div>
                        </div>
                    </div>

                    <!-- Movilizaciones Pendientes List -->
                    <div class="content-card activity-card" id="pendingMovsContainer" style="display: none;">
                        <h3 class="card-title">Equipos Por Confirmar Recepción</h3>
                        <div class="activity-list">
                            @forelse($recentActivity as $activity)
                            @php
                                // Obtener permisos del usuario actual
                                $usuario = auth()->user();
                                $usuarioFrenteId = $usuario->ID_FRENTE_ASIGNADO;
                                $esGlobal = ($usuario->NIVEL_ACCESO == 1);
                                
                                // Determinar si puede recibir (destinatario o global)
                                $esDestinatario = ($usuarioFrenteId == $activity->ID_FRENTE_DESTINO);
                                $puedeRecibir = $esDestinatario || $esGlobal;
                            @endphp
                            
                            <div class="activity-item" style="display: flex; align-items: center; gap: 12px; padding: 12px; border-bottom: 1px solid #f1f5f9;">
                                <div class="activity-icon">
                                    <i class="material-icons">local_shipping</i>
                                </div>
                                <div class="activity-info" style="flex: 1; min-width: 0;">
                                    <p class="activity-text" style="margin: 0 0 4px 0;">
                                        <strong>{{ $activity->equipo->tipo->nombre ?? 'Equipo' }}</strong> - {{ $activity->equipo->SERIAL_CHASIS ?? 'N/A' }} → {{ $activity->frenteDestino->NOMBRE_FRENTE ?? 'Destino' }}
                                    </p>
                                    <span class="activity-time">{{ $activity->created_at->locale('es')->diffForHumans() }}</span>
                                </div>
                                
                                <!-- Botón de Recibir -->
                                <div class="activity-actions" style="display: flex; gap: 6px; flex-shrink: 0;">
                                    @if($puedeRecibir)
                                        <form action="{{ route('movilizaciones.updateStatus', $activity->ID_MOVILIZACION) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="RECIBIDO">
                                            <button type="submit" 
                                                class="btn-recibir-dashboard"
                                                title="Confirmar recepción en {{ $activity->frenteDestino->NOMBRE_FRENTE }}">
                                                <i class="material-icons">check_circle</i>
                                                <span>RECIBIR</span>
                                            </button>
                                        </form>
                                    @else
                                        {{-- Sin permisos para recibir --}}
                                        <div class="btn-sin-acceso-dashboard"
                                            title="No tienes permisos para recibir este equipo">
                                            <i class="material-icons">block</i>
                                            <span>Sin Acceso</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="empty-state">
                                <i class="material-icons">inbox</i>
                                <p>No hay movilizaciones por confirmar.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- ALERTAS SECTION -->
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <!-- Card 4: Alertas -->
                    <div class="dashboard-card card-yellow" onclick="toggleExpiredDocs()">
                        <div class="icon-wrapper">
                            <i class="material-icons {{ $totalAlerts > 0 ? 'bell-shake' : '' }}">notifications</i>
                        </div>
                        <div class="card-content">
                            <span class="card-label">Alertas Documentos</span>
                            <div class="card-value-row">
                                <span class="card-value">{{ $totalAlerts }}</span>
                                <span class="card-subtext-inline" style="font-weight: 800; color: #000000;">| Por Renovar</span>
                            </div>
                        </div>
                    </div>

                    <!-- Documentos Vencidos y Por Vencer List -->
                    <div class="content-card policies-card" id="expiredDocsContainer" style="display: none;">
                        <h3 class="card-title" style="color: #000;">Alertas de Documentos</h3>
                        <div style="padding: 10px 15px; border-bottom: 1px solid #e5e7eb; background: white; display: flex; align-items: center; gap: 10px;">
                            <input type="text" id="alertSearch" placeholder="Buscar por placa, serial, chasis..." 
                                   style="flex: 1; box-sizing: border-box; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; outline: none; transition: border 0.2s;"
                                   onfocus="this.style.borderColor='#3b82f6'"
                                   onblur="this.style.borderColor='#d1d5db'"
                                   onkeyup="filterDashboardAlerts()">
                            <a href="{{ route('dashboard.exportDocumentsPDF') }}"
                               data-no-spa="true"
                               class="btn-export-pdf"
                               title="Descargar Reporte PDF"
                               style="display: inline-flex; align-items: center; justify-content: center; padding: 8px; background: transparent; color: #94a3b8; border: none; text-decoration: none; transition: all 0.2s; cursor: default;"
                               onmouseover="this.style.color='#ef4444'"
                               onmouseout="this.style.color='#94a3b8'">
                                <i class="material-icons" style="font-size: 20px;">file_download</i>
                            </a>
                        </div>
                        <div class="activity-list" style="max-height: 400px; overflow-y: auto;">
                            <div id="dashboardAlertsList">
                                @include('partials.dashboard_alerts')
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    </div>
    
    
    <!-- User Floating Panel (Bottom Left) -->
    <style>
        @media (max-width: 768px) {
            #userFloatingPanel {
                display: none !important;
            }
        }
    </style>
    <div id="userFloatingPanel" style="position: fixed; bottom: 20px; left: 20px; z-index: 0; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); padding: 10px 20px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.5); box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 12px; transition: transform 0.3s ease;">
        <div style="width: 35px; height: 35px; background: var(--maquinaria-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
            {{ substr(auth()->user()->NOMBRE_COMPLETO ?? 'U', 0, 1) }}
        </div>
        <div style="display: flex; flex-direction: column;">
            <span style="font-size: 14px; color: #1e293b; font-weight: 700;">{{ auth()->user()->NOMBRE_COMPLETO ?? 'Usuario' }}</span>
            <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">{{ auth()->user()->rol->NOMBRE_ROL ?? 'Sin Rol' }}</span>
        </div>
    </div>
    
    <!-- Feature Cards (Above Machinery) -->
    <div class="features-floating-wrapper">
        <div class="features-container">
            <div class="feature-card">
                <i class="material-icons feature-card-icon">description</i>
                <span class="feature-text">Acceso a Documentación</span>
            </div>
            <div class="feature-card">
                <i class="material-icons feature-card-icon">location_on</i>
                <span class="feature-text">Estado y Ubicación</span>
            </div>
            <div class="feature-card">
                <i class="material-icons feature-card-icon">engineering</i>
                <span class="feature-text">Control de Mantenimiento</span>
            </div>
        </div>
    </div>
    
    <!-- Machinery Image (Reused from Login - Big & Impactful) -->
    <div class="machinery-fixed-bottom">
        <div class="machinery-wrapper" style="width: 100%; height: auto;">
            <img src="{{ asset('images/maquinaria_login_new.webp') }}" alt="Maquinaria Vidalsa" style="width: 100%; height: auto; display: block; filter: drop-shadow(-10px -10px 20px rgba(0, 0, 0, 0.15));">
        </div>
    </div>
</div>





    <!-- Partial Modal for Equipment Details (Used by Alerts) -->
    @include('admin.equipos.partials.equipment_details_modal')

@section('scripts')
    <!-- Inject Scripts for Equipment Modal Logic on Dashboard -->
    <script src="{{ asset('js/maquinaria/uicomponents.js') }}"></script>
    <script src="{{ asset('js/maquinaria/equipos_index.js') }}"></script>
    <script>
        // Ensure functions are available globally if needed, though uicomponents.js should handle it
        console.log('Dashboard scripts loaded for alerts.');

        document.addEventListener('DOMContentLoaded', function() {
            // Intercept all forms with the receive button
            const receiveForms = document.querySelectorAll('form'); 
            
            receiveForms.forEach(form => {
                if(form.querySelector('.btn-recibir-dashboard')) {
                    form.addEventListener('submit', function(e) {
                        const btn = this.querySelector('.btn-recibir-dashboard');
                         // Prevent multiple clicks if already disabled
                        if (btn.disabled) return;

                        // Show Global Preloader
                        const preloader = document.getElementById('preloader');
                        if (preloader) {
                            preloader.style.display = 'flex';
                        }
                        
                        // Disable button to prevent double submit
                        btn.disabled = true;
                    });
                }
            });
        });
    </script>
@endsection
@endsection
