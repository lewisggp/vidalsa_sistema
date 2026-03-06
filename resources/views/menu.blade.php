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
                        <div style="padding: 8px 12px; border-bottom: 1px solid #e5e7eb; background: white; display: flex; gap: 6px; align-items: center;">
                            <input type="text" id="pendingMovSearch" placeholder="Buscar..." 
                                   style="flex: 1; min-width: 0; box-sizing: border-box; padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.815rem; outline: none; transition: border 0.2s;"
                                   onfocus="this.style.borderColor='#3b82f6'"
                                   onblur="this.style.borderColor='#d1d5db'"
                                   onkeyup="filterPendingMovs()"
                                   autocomplete="off">
                            <button type="button"
                               onclick="abrirRecepcionDirecta()"
                               class="btn-recibir-dashboard"
                               title="Recepción Directa (sin movilización previa)"
                               style="background: rgb(0, 103, 177); border: none; color: white; padding: 0 10px; height: 36px; border-radius: 6px; font-weight: 700; display: flex; align-items: center; gap: 4px; text-decoration: none; flex-shrink: 0; cursor: default;">
                                <i class="material-icons" style="font-size: 18px;">input</i>
                                <span class="desktop-only" style="font-size: 10px; font-weight: 800;">RECEP. DIRECTA</span>
                            </button>
                        </div>
                        <div class="activity-list" id="pendingMovsList">
                            @include('partials.pending_movs_list', ['recentActivity' => $recentActivity])
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
                        <div style="padding: 8px 12px; border-bottom: 1px solid #e5e7eb; background: white; display: flex; align-items: center; gap: 6px;">
                            <input type="text" id="alertSearch" placeholder="Buscar..." 
                                   style="flex: 1; box-sizing: border-box; padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.815rem; outline: none; transition: border 0.2s;"
                                   onfocus="this.style.borderColor='#3b82f6'"
                                   onblur="this.style.borderColor='#d1d5db'"
                                   onkeyup="filterDashboardAlerts()"
                                   autocomplete="off">
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
    <div class="machinery-fixed-bottom">
        <div class="machinery-wrapper" style="width: 100%; height: auto;">
            <img src="{{ asset('images/maquinaria_login_new.webp') }}" alt="Maquinaria Vidalsa" style="width: 100%; height: auto; display: block; filter: drop-shadow(-10px -10px 20px rgba(0, 0, 0, 0.15));">
        </div>
    </div>

    <!-- Partial Modal for Equipment Details (Used by Alerts) -->
    @include('admin.equipos.partials.equipment_details_modal')

    {{-- ============================================================ --}}
    {{-- MODAL DE RECEPCIÓN DIRECTA (Abierto desde el menú) --}}
    {{-- ============================================================ --}}
    @php
        $menuUser        = auth()->user();
        $menuFrenteAsig  = $menuUser ? $menuUser->ID_FRENTE_ASIGNADO : null;
        $menuFrenteObj   = $menuFrenteAsig ? $frentes->firstWhere('ID_FRENTE', $menuFrenteAsig) : null;
    @endphp

    {{-- El modal reutiliza exactamente la misma lógica JS de movilizaciones_index.js --}}
    <div id="recepcionDirectaModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10000; justify-content: center; align-items: center;">
        <div style="background: white; width: 95%; max-width: 450px; max-height: 90vh; border-radius: 16px; padding: 0; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); animation: slideDown 0.3s ease-out; display: flex; flex-direction: column; overflow: hidden;">

            {{-- Header --}}
            <div style="background: linear-gradient(135deg, #0067b1, #004e8c); padding: 14px 18px; color: white; flex-shrink: 0;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="material-icons" style="font-size: 22px;">input</i>
                        <div>
                            <h3 style="margin: 0; font-size: 15px; font-weight: 800;">Recepción Directa</h3>
                            <p style="margin: 0; font-size: 11px; opacity: 0.85;">Sin movilización previa</p>
                        </div>
                    </div>
                    <button type="button" onclick="cerrarRecepcionDirecta()" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="material-icons" style="font-size: 18px;">close</i>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div style="padding: 20px 25px; overflow-y: auto; flex: 1;">

                {{-- PASO 1: Buscar equipos --}}
                <div style="margin-bottom: 20px;">
                    <label for="rdSearchInput" style="display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px;">
                        <span style="background: #0067b1; color: white; padding: 2px 8px; border-radius: 50%; font-size: 11px; font-weight: 800; margin-right: 6px;">1</span>
                        Buscar Equipo (Serial, Placa o Código)
                    </label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="rdSearchInput"
                            placeholder="Buscar por serial, placa o código..."
                            style="flex: 1; padding: 10px 14px; border: 1px solid #cbd5e0; border-radius: 10px; font-size: 14px; background: #f8fafc; outline: none;"
                            autocomplete="off"
                            onfocus="this.style.borderColor='#0067b1'" onblur="this.style.borderColor='#cbd5e0'"
                            onkeyup="if(event.key==='Enter') buscarEquiposRD()">
                        <button type="button" onclick="buscarEquiposRD()"
                            style="padding: 10px 16px; background: #0067b1; border: none; border-radius: 10px; color: white; font-weight: 700; display: flex; align-items: center; gap: 4px; transition: background 0.2s;"
                            onmouseover="this.style.background='#005a9e'" onmouseout="this.style.background='#0067b1'">
                            <i class="material-icons" style="font-size: 18px;">search</i>
                        </button>
                    </div>
                </div>

                {{-- Resultados de búsqueda --}}
                <div id="rdResultados" style="margin-bottom: 20px; display: none;">
                    <p style="font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px; margin-top: 0; text-transform: uppercase;">Resultados</p>
                    <div id="rdResultadosList" style="min-height: 100px; max-height: 400px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 10px; background: #fafbfc;">
                        {{-- populated by JS --}}
                    </div>
                </div>

                {{-- Frente receptor: hidden, siempre el frente asignado al usuario --}}
                <input type="hidden" id="rdFrenteInput" value="{{ $menuFrenteAsig }}">

                {{-- PASO 2: Ubicación específica --}}
                <div style="margin-bottom: 15px;">
                    <label for="rdUbicacionInput" style="display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px;">
                        <span style="background: #0067b1; color: white; padding: 2px 8px; border-radius: 50%; font-size: 11px; font-weight: 800; margin-right: 6px;">2</span>
                        UBICACIÓN DETALLADA EN: <span style="color: #0f172a; font-weight: 900; text-transform: uppercase;">
                            {{ $menuFrenteObj ? $menuFrenteObj->NOMBRE_FRENTE : 'SIN ASIGNAR' }}
                        </span>
                    </label>
                    <div style="position: relative;">
                        <input type="text" id="rdUbicacionInput"
                            placeholder=""
                            style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e0; border-radius: 10px; font-size: 14px; background: #f8fafc; outline: none; box-sizing: border-box;"
                            onfocus="this.style.borderColor='#0067b1'; showUbicacionSuggestions('rd-ubicacion-suggestions')"
                            onblur="this.style.borderColor='#cbd5e0'; setTimeout(()=>hideUbicacionSuggestions('rd-ubicacion-suggestions'), 200)"
                            oninput="filterUbicacionSuggestions(this, 'rd-ubicacion-suggestions')">
                        <div id="rd-ubicacion-suggestions" style="display:none; position:absolute; top:100%; left:0; right:0; background:white; border:1px solid #cbd5e0; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); z-index:500; max-height:160px; overflow-y:auto; margin-top:4px;"></div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div style="padding: 15px 25px; border-top: 1px solid #e2e8f0; display: flex; gap: 10px; flex-shrink: 0; background: #fafbfc;">
                <button type="button" onclick="cerrarRecepcionDirecta()"
                    style="flex: 1; padding: 12px; background: white; border: 1px solid #e2e8f0; border-radius: 10px; font-weight: 600; color: #64748b;">
                    Cancelar
                </button>
                <button type="button" id="btnConfirmarRD" onclick="confirmarRecepcionDirecta()"
                    style="flex: 1; padding: 12px; background: #0067b1; border: none; border-radius: 10px; font-weight: 700; color: white; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(0, 103, 177, 0.3);"
                    onmouseover="this.style.background='#005a9e'; this.style.transform='translateY(-1px)'"
                    onmouseout="this.style.background='#0067b1'; this.style.transform='translateY(0)'">
                    <i class="material-icons" style="font-size: 16px;">check_circle</i>
                    Confirmar
                </button>
            </div>
        </div>
    </div>

@endsection

@section('extra_js')
    <script>
        // Animación del modal de recepción directa
        const _styleRD = document.createElement('style');
        _styleRD.textContent = '@keyframes slideDown { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }';
        document.head.appendChild(_styleRD);

        // En el MENÚ no hay tabla de movilizaciones, así que sobreescribimos
        // loadMovilizaciones para que refresque solo la lista pendiente (sin recargar página).
        window.loadMovilizaciones = function () {
            if (typeof window.refreshPendingMovs === 'function') {
                window.refreshPendingMovs();
            }
        };

    </script>
@endsection
