@extends('layouts.estructura_base')

@section('title', 'Movilizaciones de Equipos y Maquinarias')

@section('content')
<section class="page-title-card" style="text-align: left; margin: 0 0 10px 0;">
    <h1 class="page-title">
        <span class="page-title-line2" style="color: #000;">Control de Recepción</span>
    </h1>
</section>

<style>

    @keyframes pulse-alert {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    @keyframes slideIn {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @media (max-width: 1024px) {
        .mv-mobile-hidden {
            display: none !important;
        }
    }
</style>

<div class="movilizaciones-layout" style="align-items: start; width: 100%;">
    
    <!-- Left Column: Table & Filters -->
    <div class="admin-card movilizaciones-main-card" style="margin: 0; width: 100%;">

        <!-- Stats compactas visibles solo en mobile -->
        <div class="movilizaciones-mobile-stats">
            <div class="stat-pill">
                <i class="material-icons">local_shipping</i>
                <span id="mobileTransitoCount">{{ $totalTransito }}</span> En Tránsito
            </div>
        </div>

        <!-- Filter Toolbar -->
        <div class="movilizaciones-filter-bar filter-toolbar-container" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; margin-bottom: 5px;">
            <!-- Frente Filter -->
            <div class="mv-filter-item" style="flex: 2; min-width: 170px;">
                <div class="custom-dropdown" id="frenteFilterSelect" data-filter-type="id_frente" data-default-label="Filtrar Frente...">
                    <input type="hidden" name="id_frente" data-filter-value value="{{ request('id_frente') }}" form="search-form">
                    
                    @php 
                        $currentFrente = $frentes->firstWhere('ID_FRENTE', request('id_frente'));
                    @endphp

                    <div class="dropdown-trigger {{ request('id_frente') && request('id_frente') != 'all' ? 'filter-active' : '' }}" style="padding: 0; display: flex; align-items: center; background: #fbfcfd; overflow: hidden; border: 1px solid #cbd5e0; border-radius: 12px; height: 45px;">
                        <div style="padding: 0 10px; display: flex; align-items: center; color: var(--maquinaria-gray-text);">
                            <i class="material-icons" style="font-size: 18px;">search</i>
                        </div>
                        <input type="text" name="filter_search_dropdown" data-filter-search
                            placeholder="{{ $currentFrente ? $currentFrente->NOMBRE_FRENTE : 'Filtrar Frente...' }}" 
                             aria-label="Filtrar Frente"
                            style="width: 100%; border: none; background: transparent; padding: 10px 5px; font-size: 14px; outline: none;"
                            oninput="window.filterDropdownOptions(this)"
                            autocomplete="off">
                        <i class="material-icons" data-clear-btn style="padding: 0 5px; color: var(--maquinaria-gray-text); font-size: 18px; display: {{ request('id_frente') && request('id_frente') != 'all' ? 'block' : 'none' }};" onclick="event.stopPropagation(); clearDropdownFilter('frenteFilterSelect'); loadMovilizaciones();">close</i>
                    </div>

                    <div class="dropdown-content" style="padding: 5px; max-height: none; overflow: visible;">
                        <div class="dropdown-item-list" style="max-height: 250px; overflow-y: auto;">
                            <div class="dropdown-item {{ !request('id_frente') || request('id_frente') == 'all' ? 'selected' : '' }}" data-value="all" onclick="selectOption('frenteFilterSelect', 'all', 'TODOS LOS FRENTES'); loadMovilizaciones();">
                                TODOS LOS FRENTES
                            </div>
                            @foreach($frentes as $frente)
                                <div class="dropdown-item {{ request('id_frente') == $frente->ID_FRENTE ? 'selected' : '' }}" data-value="{{ $frente->ID_FRENTE }}" onclick="selectOption('frenteFilterSelect', '{{ $frente->ID_FRENTE }}', '{{ $frente->NOMBRE_FRENTE }}'); loadMovilizaciones();">
                                    {{ $frente->NOMBRE_FRENTE }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tipo Filter -->
            <div class="mv-filter-item" style="flex: 2; min-width: 170px;">
                <div class="custom-dropdown" id="tipoFilterSelect" data-filter-type="id_tipo" data-default-label="Filtrar Tipo...">
                    <input type="hidden" name="id_tipo" data-filter-value value="{{ request('id_tipo') }}" form="search-form">
                    
                    @php 
                        $currentTipo = $allTipos->firstWhere('id', request('id_tipo'));
                    @endphp

                    <div class="dropdown-trigger {{ request('id_tipo') ? 'filter-active' : '' }}" style="padding: 0; display: flex; align-items: center; background: #fbfcfd; overflow: hidden; border: 1px solid #cbd5e0; border-radius: 12px; height: 45px;">
                        <div style="padding: 0 10px; display: flex; align-items: center; color: var(--maquinaria-gray-text);">
                            <i class="material-icons" style="font-size: 18px;">search</i>
                        </div>
                        <input type="text" name="filter_search_dropdown" data-filter-search
                            placeholder="{{ $currentTipo ? $currentTipo->nombre : 'Filtrar Tipo...' }}" 
                             aria-label="Filtrar Tipo"
                            style="width: 100%; border: none; background: transparent; padding: 10px 5px; font-size: 14px; outline: none;"
                            oninput="window.filterDropdownOptions(this)"
                            autocomplete="off">
                        <i class="material-icons" data-clear-btn style="padding: 0 5px; color: var(--maquinaria-gray-text); font-size: 18px; display: {{ request('id_tipo') ? 'block' : 'none' }};" onclick="event.stopPropagation(); clearDropdownFilter('tipoFilterSelect'); loadMovilizaciones();">close</i>
                    </div>

                    <div class="dropdown-content" style="padding: 5px; max-height: none; overflow: visible;">
                        <div class="dropdown-item-list" style="max-height: 250px; overflow-y: auto;">
                            <div class="dropdown-item {{ !request('id_tipo') || request('id_tipo') == 'all' ? 'selected' : '' }}" data-value="all" onclick="selectOption('tipoFilterSelect', 'all', 'TODOS LOS TIPOS'); loadMovilizaciones();">
                                TODOS LOS TIPOS
                            </div>
                            @foreach($allTipos as $tipo)
                                <div class="dropdown-item {{ request('id_tipo') == $tipo->id ? 'selected' : '' }}" data-value="{{ $tipo->id }}" onclick="selectOption('tipoFilterSelect', '{{ $tipo->id }}', '{{ $tipo->nombre }}'); loadMovilizaciones();">
                                    {{ $tipo->nombre }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila: Búsqueda + Botón Filtro Avanzado (siempre juntos) -->
            <div class="mv-search-adv-row">

                <!-- Search Filter -->
                <div class="mv-filter-item mv-search-item" style="flex: 1.5; min-width: 140px;">
                    <form action="{{ route('movilizaciones.index') }}" method="GET" id="search-form" onsubmit="event.preventDefault(); loadMovilizaciones();" style="margin: 0;">
                        <div class="search-wrapper" style="width: 100%; border-color: {{ request('search') ? '#0067b1' : '#cbd5e0' }}; background: {{ request('search') ? '#e1effa' : '#fff' }};">
                            <i class="material-icons search-icon">search</i>
                            <input type="text" id="searchInput" name="search" value="{{ request('search') }}"
                                placeholder="Buscar Control o Equipo"
                                class="search-input-field"
                                autocomplete="off"
                                onkeyup="if(this.value.length >= 4 || this.value.length == 0) { /* Debounce handled in script */ }">
                            <i id="btn_clear_search" class="material-icons clear-icon" style="display: {{ request('search') ? 'block' : 'none' }};" onclick="selectMovilizacionFilter('search', '');">close</i>
                        </div>
                    </form>
                </div>

                <!-- Botón Filtro Avanzado (Fechas) -->
                <div class="mv-adv-filter-wrap" style="position: relative; flex-shrink: 0;">
                    <button type="button" id="btnAdvancedFilter" class="btn-primary-maquinaria"
                        style="height: 45px; width: 45px; padding: 0; display: flex; align-items: center; justify-content: center; background: {{ request('fecha_desde') || request('fecha_hasta') ? '#e1effa' : 'white' }}; border: 1px solid {{ request('fecha_desde') || request('fecha_hasta') ? '#0067b1' : '#cbd5e0' }}; color: {{ request('fecha_desde') || request('fecha_hasta') ? '#0067b1' : '#64748b' }}; box-shadow: none; border-radius: 12px; cursor: pointer; transition: all 0.2s;"
                        onclick="toggleAdvancedFilter(event)">
                        <i class="material-icons">filter_list</i>
                    </button>

                    <!-- Panel Flotante de Filtros -->
                    <div id="advancedFilterPanel" style="display: none; position: absolute; top: 100%; right: 0; width: 280px; background: #e2e8f0; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15); border: 1px solid #cbd5e1; z-index: 100; margin-top: 10px; padding: 15px;">
                        <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 700; color: #334155; display: flex; justify-content: space-between; align-items: center;">
                            Filtros Avanzados
                            <span style="font-size: 11px; color: #64748b; font-weight: 400; text-decoration: underline; cursor: default;" onclick="clearDateFilters()">Limpiar Todo</span>
                        </h4>

                        <!-- Fecha Desde -->
                        <div style="margin-bottom: 15px;">
                            <span style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px;">Fecha Desde</span>
                            <input type="date" id="filterFechaDesde"
                                value="{{ request('fecha_desde') }}"
                                style="width: 100%; height: 32px; padding: 0 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 12px; background: {{ request('fecha_desde') ? '#e1effa' : 'white' }}; outline: none; color: #0f172a; box-sizing: border-box;"
                                onfocus="this.style.borderColor='#0067b1'"
                                onblur="this.style.borderColor='#e2e8f0'"
                                onchange="loadMovilizaciones()">
                        </div>

                        <!-- Fecha Hasta -->
                        <div>
                            <span style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px;">Fecha Hasta</span>
                            <input type="date" id="filterFechaHasta"
                                value="{{ request('fecha_hasta') }}"
                                style="width: 100%; height: 32px; padding: 0 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 12px; background: {{ request('fecha_hasta') ? '#e1effa' : 'white' }}; outline: none; color: #0f172a; box-sizing: border-box;"
                                onfocus="this.style.borderColor='#0067b1'"
                                onblur="this.style.borderColor='#e2e8f0'"
                                onchange="loadMovilizaciones()">
                        </div>
                    </div>
                </div>

            </div>{{-- /mv-search-adv-row --}}


            <!-- Botón Recepción Directa -->
            <div class="mv-filter-btn-row">
                <button type="button" id="btnRecepcionDirecta"
                    style="height: 45px; padding: 0 16px; display: flex; align-items: center; gap: 6px; background: #0067b1; border: none; color: white; border-radius: 12px; font-weight: 700; font-size: 13px; box-shadow: 0 4px 6px -1px rgba(0, 103, 177, 0.3); transition: all 0.2s;"
                    onmouseover="this.style.background='#005a9e'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 8px -1px rgba(0, 103, 177, 0.4)'"
                    onmouseout="this.style.background='#0067b1'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 103, 177, 0.3)'"
                    onclick="abrirRecepcionDirecta()">
                    <i class="material-icons" style="font-size: 18px;">input</i>
                    Recepción Directa
                </button>
            </div>
        </div>


        <!-- Table Container -->
        <div class="custom-scrollbar-container movilizaciones-table-wrap" style="margin-top: 5px;">
            <table class="admin-table" id="movilizacionesTable">
                <thead>
                    <tr class="table-row-header">
                        <th class="table-header-custom">Equipo</th>
                        <th class="table-header-custom" style="text-align: center !important;">Trayecto (Origen → Destino)</th>
                        <th class="table-header-custom mv-mobile-hidden" style="text-align: center !important;">Fechas</th>
                        <th class="table-header-custom mv-col-op mv-mobile-hidden" style="text-align: center !important;">N° OPERACIÓN</th>
                        <th class="table-header-custom" style="text-align: center !important;">Estado</th>
                    </tr>
                </thead>
                <tbody id="movilizacionesTableBody">
                    @include('admin.movilizaciones.partials.table_rows')
                </tbody>
            </table>
        </div>
        
         <!-- Pagination -->
        <div id="movilizacionesPagination" style="margin-top: 25px;">
            {{ $movilizaciones->links() }}
        </div>

    </div>

    <!-- Right Sidebar -->
    <div class="counter-sidebar movilizaciones-sidebar" style="position: sticky; top: 20px; display: flex; flex-direction: column; gap: 20px;">
        
        <!-- Total Card -->
        <div style="background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 100%); border-radius: 12px; padding: 15px; color: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); position: relative; overflow: hidden;">
            <i class="material-icons" style="position: absolute; right: -15px; bottom: -15px; font-size: 80px; opacity: 0.1; transform: rotate(-15deg);">agriculture</i>
            <div style="position: relative; z-index: 2;">
                <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.9; margin-bottom: 5px;">Equipos en Tránsito</div>
                <div style="display: flex; align-items: baseline; gap: 5px;">
                    <span id="totalTransitoCount" style="font-size: 32px; font-weight: 800; line-height: 1; letter-spacing: -1px;">
                        {{ $totalTransito }}
                    </span>
                    <span style="font-size: 12px; opacity: 0.8; font-weight: 500;">registros</span>
                </div>
            </div>
        </div>

        <!-- Status Stats -->
        <div id="statusStatsContainer" style="background: white; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
             <h4 style="margin: 0 0 15px 0; font-size: 13px; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="material-icons" style="font-size: 18px; color: #8b5cf6;">local_shipping</i>
                En Tránsito por Frente
            </h4>
             <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
                @forelse($transitoPorFrente as $stat)
                    <li style="padding: 10px; background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12px; color: #64748b; font-weight: 600;">{{ $stat->NOMBRE_FRENTE }}</span>
                        <span style="background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: 700;">{{ $stat->total }}</span>
                    </li>
                @empty
                    <li style="padding: 15px; text-align: center; color: #94a3b8; font-style: italic; font-size: 13px;">No hay equipos en tránsito</li>
                @endforelse
            </ul>
        </div>


    </div>

</div>

<!-- Image Overlay Modal -->
<div id="imageOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center; cursor: default;" onclick="this.style.display='none'">
    <img id="enlargedImg" style="max-width: 90%; max-height: 90%; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); transition: transform 0.3s ease;">
</div>

<!-- Modal de Recepción con Sub-Ubicación (TODOS los frentes) -->
<div id="recepcionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
    <div style="background: white; width: 90%; max-width: 450px; border-radius: 16px; padding: 25px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); animation: slideIn 0.3s ease-out;">
        
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="width: 50px; height: 50px; background: #e0eff8; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                <i class="material-icons" style="font-size: 30px; color: #0067b1;">check_circle</i>
            </div>
            <h3 style="font-size: 18px; font-weight: 800; color: #1e293b; margin: 0;">Confirmar Recepción</h3>
            <p style="font-size: 14px; color: #64748b; margin-top: 5px;">
                El equipo ha llegado a <strong id="modalFrenteNombre" style="color: #0f172a;"></strong>
            </p>
        </div>

        <form id="formRecepcion" action="" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="RECIBIDO">
            

            {{-- Input de ubicación con sugerencias de subdivisiones --}}
            <div style="margin-bottom: 20px; position: relative;">
                <label for="input_ubicacion_recepcion" style="display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px;">
                    <i class="material-icons" style="font-size: 14px; vertical-align: middle;">place</i>
                    Ubicación / Sección <span style="font-weight: 400; color: #94a3b8; font-size: 12px;">(Opcional)</span>
                </label>
                <input type="text" id="input_ubicacion_recepcion" name="DETALLE_UBICACION"
                    placeholder=""
                    style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e0; border-radius: 10px; font-size: 14px; background: #f8fafc; outline: none; transition: border 0.2s; box-sizing: border-box;"
                    onfocus="this.style.borderColor='#0067b1'; showUbicacionSuggestions('ubicacion-suggestions')"
                    onblur="this.style.borderColor='#cbd5e0'; setTimeout(()=>hideUbicacionSuggestions('ubicacion-suggestions'), 200)"
                    oninput="filterUbicacionSuggestions(this, 'ubicacion-suggestions')">
                <!-- Sugerencias -->
                <div id="ubicacion-suggestions" style="display:none; position:absolute; top:100%; left:0; right:0; background:white; border:1px solid #cbd5e0; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); z-index:500; max-height:160px; overflow-y:auto; margin-top:4px;"></div>
            </div>


            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="document.getElementById('recepcionModal').style.display='none'" 
                    style="flex: 1; padding: 10px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; font-weight: 600; color: #64748b; transition: all 0.2s;"
                    onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">
                    Cancelar
                </button>
                <button type="submit" id="btnConfirmarRecepcion"
                    style="flex: 1; padding: 10px; background: #0067b1; border: none; border-radius: 8px; font-weight: 700; color: white; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(0, 103, 177, 0.2);"
                    onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 8px -1px rgba(0, 103, 177, 0.3)'" 
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 103, 177, 0.2)'">
                    Confirmar Recepción
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL DE RECEPCIÓN DIRECTA                     -->
<!-- ============================================== -->
<div id="recepcionDirectaModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10000; justify-content: center; align-items: center;">
    <div style="background: white; width: 95%; max-width: 450px; max-height: 90vh; border-radius: 16px; padding: 0; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); animation: slideIn 0.3s ease-out; display: flex; flex-direction: column; overflow: hidden;">
        
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
                    <!-- populated by JS -->
                </div>
            </div>



            {{-- Frente receptor: hidden, siempre el del usuario --}}
            <input type="hidden" id="rdFrenteInput" value="{{ auth()->user()->ID_FRENTE_ASIGNADO }}">

            {{-- PASO 2: Ubicación específica (opcional) --}}
            <div style="margin-bottom: 15px;">
                <label for="rdUbicacionInput" style="display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px;">
                    <span style="background: #0067b1; color: white; padding: 2px 8px; border-radius: 50%; font-size: 11px; font-weight: 800; margin-right: 6px;">2</span>
                    UBICACIÓN DETALLADA EN: <span style="color: #0f172a; font-weight: 900; text-transform: uppercase;">
                        {{ optional(\App\Models\FrenteTrabajo::find(auth()->user()->ID_FRENTE_ASIGNADO))->NOMBRE_FRENTE ?? 'SIN ASIGNAR' }}
                    </span>
                </label>
                {{-- Input con sugerencias --}}
                <div style="position: relative;">
                    <input type="text" id="rdUbicacionInput"
                        placeholder=""
                        style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e0; border-radius: 10px; font-size: 14px; background: #f8fafc; outline: none; box-sizing: border-box;"
                        onfocus="this.style.borderColor='#0067b1'; showUbicacionSuggestions('rd-ubicacion-suggestions')"
                        onblur="this.style.borderColor='#cbd5e0'; setTimeout(()=>hideUbicacionSuggestions('rd-ubicacion-suggestions'), 200)"
                        oninput="filterUbicacionSuggestions(this, 'rd-ubicacion-suggestions')">
                    <!-- Sugerencias -->
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
                onmouseover="this.style.background='#005a9e'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 8px -1px rgba(0, 103, 177, 0.4)'"
                onmouseout="this.style.background='#0067b1'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 103, 177, 0.3)'">
                <i class="material-icons" style="font-size: 16px;">check_circle</i>
                Confirmar
            </button>
        </div>
    </div>
</div>

@endsection
