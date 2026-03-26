@extends('layouts.estructura_base')

@section('title', 'Movilizaciones de Equipos y Maquinarias')

@section('content')
<section class="page-title-card" style="text-align: left; margin: 0 0 10px 0;">
    <h1 class="page-title">
        <span class="page-title-line2" style="color: #000;">Control de Recepción</span>
    </h1>
</section>


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
        @php
            $authUser       = auth()->user();
            $isLocalUser    = $authUser && $authUser->NIVEL_ACCESO == 2;
            $dashFrenteIds  = $authUser ? $authUser->getFrentesIds() : [];
            $hasMultiple    = count($dashFrenteIds) > 1;
            $userFrenteObj  = count($dashFrenteIds) === 1 ? $frentes->firstWhere('ID_FRENTE', $dashFrenteIds[0]) : null;
        @endphp

        {{-- =====================================================================
             FILTRO FRENTE: LOCAL = bloqueado | GLOBAL = dropdown con default real
             ===================================================================== --}}
        <div class="mv-filter-item" style="flex: 2; min-width: 170px;">
            @php
                $currentFrenteId = request('id_frente');
                // Si es un global validando por primera vez y tiene frentes default, podemos usarlo:
                if (!$isLocalUser && !$currentFrenteId && isset($userFrenteAsig) && $userFrenteAsig) {
                    $currentFrenteId = $userFrenteAsig;
                }
                $currentFrente = $currentFrenteId ? $frentes->firstWhere('ID_FRENTE', $currentFrenteId) : null;
                $frentesDropdown = $isLocalUser ? $frentes->whereIn('ID_FRENTE', $dashFrenteIds) : $frentes;
                $placeholderText = $currentFrente ? $currentFrente->NOMBRE_FRENTE : ($isLocalUser ? 'Todos Mis Frentes' : 'Filtrar Frente...');
            @endphp
            <div class="custom-dropdown" id="frenteFilterSelect" data-filter-type="id_frente" data-default-label="{{ $isLocalUser ? 'Todos Mis Frentes' : 'Filtrar Frente...' }}">
                <input type="hidden" name="id_frente" data-filter-value value="{{ $currentFrenteId }}" form="search-form">

                <div class="dropdown-trigger {{ $currentFrenteId && $currentFrenteId != 'all' ? 'filter-active' : '' }}" style="padding:0; display:flex; align-items:center; background:#fbfcfd; overflow:hidden; border:1px solid #cbd5e0; border-radius:12px; height:45px;">
                    <div style="padding:0 10px; display:flex; align-items:center; color:var(--maquinaria-gray-text);">
                        <i class="material-icons" style="font-size:18px;">search</i>
                    </div>
                    <input type="text" name="filter_search_dropdown" data-filter-search
                        placeholder="{{ $placeholderText }}"
                        aria-label="Filtrar Frente"
                        style="flex:1; border:none; background:transparent; padding:10px 5px; font-size:14px; outline:none; min-width:0;"
                        oninput="window.filterDropdownOptions(this)"
                        autocomplete="off">
                    <i class="material-icons" data-clear-btn
                       style="padding:0 5px; color:var(--maquinaria-gray-text); font-size:18px; display:{{ $currentFrenteId && $currentFrenteId != 'all' ? 'block' : 'none' }}; cursor:pointer;"
                       onclick="event.stopPropagation(); clearDropdownFilter('frenteFilterSelect'); setTimeout(function(){ window.loadMovilizaciones(); }, 100);">close</i>
                </div>

                <div class="dropdown-content" style="padding:5px; max-height:none; overflow:visible;">
                    <div class="dropdown-item-list" style="max-height:250px; overflow-y:auto;">
                        <div class="dropdown-item {{ !$currentFrenteId || $currentFrenteId == 'all' ? 'selected' : '' }}"
                             data-value="all"
                             onclick="selectOption('frenteFilterSelect', 'all', '{{ $isLocalUser ? 'Todos Mis Frentes' : 'TODOS LOS FRENTES' }}'); setTimeout(function(){ window.loadMovilizaciones(); }, 100);">
                            {{ $isLocalUser ? 'TODOS MIS FRENTES' : 'TODOS LOS FRENTES' }}
                        </div>
                        @foreach($frentesDropdown as $frente)
                            <div class="dropdown-item {{ $currentFrenteId == $frente->ID_FRENTE ? 'selected' : '' }}"
                                 data-value="{{ $frente->ID_FRENTE }}"
                                 onclick="selectOption('frenteFilterSelect', '{{ $frente->ID_FRENTE }}', '{{ addslashes($frente->NOMBRE_FRENTE) }}'); setTimeout(function(){ window.loadMovilizaciones(); }, 100);">
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
                            style="flex: 1; border: none; background: transparent; padding: 10px 5px; font-size: 14px; outline: none; min-width: 0;"
                            oninput="window.filterDropdownOptions(this)"
                            autocomplete="off">
                        <i class="material-icons" data-clear-btn style="padding: 0 5px; color: var(--maquinaria-gray-text); font-size: 18px; display: {{ request('id_tipo') ? 'block' : 'none' }}; cursor:pointer;" onclick="event.stopPropagation(); clearDropdownFilter('tipoFilterSelect'); setTimeout(function(){ window.loadMovilizaciones(); }, 100);">close</i>
                    </div>

                    <div class="dropdown-content" style="padding: 5px; max-height: none; overflow: visible;">
                        <div class="dropdown-item-list" style="max-height: 250px; overflow-y: auto;">
                            <div class="dropdown-item {{ !request('id_tipo') || request('id_tipo') == 'all' ? 'selected' : '' }}" data-value="all" onclick="selectOption('tipoFilterSelect', 'all', 'TODOS LOS TIPOS'); setTimeout(function(){ window.loadMovilizaciones(); }, 100);">
                                TODOS LOS TIPOS
                            </div>
                            @foreach($allTipos as $tipo)
                                <div class="dropdown-item {{ request('id_tipo') == $tipo->id ? 'selected' : '' }}" data-value="{{ $tipo->id }}" onclick="selectOption('tipoFilterSelect', '{{ $tipo->id }}', '{{ addslashes($tipo->nombre) }}'); setTimeout(function(){ window.loadMovilizaciones(); }, 100);">
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
                                onkeydown="if(event.key === 'Enter'){ event.preventDefault(); window.loadMovilizaciones(); }"
                                oninput="clearTimeout(window._searchTimerMv); window._searchTimerMv = setTimeout(() => { const btn = document.getElementById('btn_clear_search'); if(btn) btn.style.display = this.value.length > 0 ? 'block' : 'none'; window.loadMovilizaciones(); }, 600);">
                            <i id="btn_clear_search" class="material-icons clear-icon" style="display: {{ request('search') ? 'block' : 'none' }};" onclick="selectMovilizacionFilter('search', '');">close</i>
                        </div>
                    </form>
                </div>

                <!-- Botón Filtro Avanzado (Fechas) -->
                <div class="mv-adv-filter-wrap" style="position: relative; flex-shrink: 0;">
                    <button type="button" id="btnAdvancedFilter" class="btn-primary-maquinaria"
                        style="height: 45px; width: 45px; padding: 0; display: flex; align-items: center; justify-content: center; background: {{ request('fecha_desde') || request('fecha_hasta') || request('direccion_frente') ? '#e1effa' : 'white' }}; border: 1px solid {{ request('fecha_desde') || request('fecha_hasta') || request('direccion_frente') ? '#0067b1' : '#cbd5e0' }}; color: {{ request('fecha_desde') || request('fecha_hasta') || request('direccion_frente') ? '#0067b1' : '#64748b' }}; box-shadow: none; border-radius: 12px; cursor: default; transition: all 0.2s;"
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
                                onchange="loadMovilizaciones()"
                                onclick="try{this.showPicker()}catch(e){}">
                        </div>

                        <!-- Fecha Hasta -->
                        <div style="margin-bottom: 15px;">
                            <span style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px;">Fecha Hasta</span>
                            <input type="date" id="filterFechaHasta"
                                value="{{ request('fecha_hasta') }}"
                                style="width: 100%; height: 32px; padding: 0 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 12px; background: {{ request('fecha_hasta') ? '#e1effa' : 'white' }}; outline: none; color: #0f172a; box-sizing: border-box;"
                                onfocus="this.style.borderColor='#0067b1'"
                                onblur="this.style.borderColor='#e2e8f0'"
                                onchange="loadMovilizaciones()"
                                onclick="try{this.showPicker()}catch(e){}">
                        </div>

                        <!-- Dirección del Frente (Entrada / Salida) -->
                        <div>
                            <span style="display:flex; align-items:center; gap:5px; font-size:12px; font-weight:600; color:#64748b; margin-bottom:6px;">
                                <i class="material-icons" style="font-size:13px;">swap_horiz</i>
                                Dirección del Frente
                            </span>
                            <div style="display: flex; gap: 6px;">
                                <button type="button" id="filterDireccionTodas"
                                    onclick="setDireccionFilter('')"
                                    style="flex:1; height:32px; border-radius:8px; border:1px solid {{ !request('direccion_frente') ? '#0067b1' : '#e2e8f0' }}; background:{{ !request('direccion_frente') ? '#e1effa' : 'white' }}; color:{{ !request('direccion_frente') ? '#0067b1' : '#64748b' }}; font-size:11px; font-weight:600; cursor:default; transition:all 0.2s;">
                                    Todas
                                </button>
                                <button type="button" id="filterDireccionEntrada"
                                    onclick="setDireccionFilter('entrada')"
                                    style="flex:1; height:32px; border-radius:8px; border:1px solid {{ request('direccion_frente') == 'entrada' ? '#16a34a' : '#e2e8f0' }}; background:{{ request('direccion_frente') == 'entrada' ? '#dcfce7' : 'white' }}; color:{{ request('direccion_frente') == 'entrada' ? '#16a34a' : '#64748b' }}; font-size:11px; font-weight:600; cursor:default; transition:all 0.2s;">
                                    <i class="material-icons" style="font-size:13px; vertical-align:middle;">arrow_downward</i>
                                    Entrada
                                </button>
                                <button type="button" id="filterDireccionSalida"
                                    onclick="setDireccionFilter('salida')"
                                    style="flex:1; height:32px; border-radius:8px; border:1px solid {{ request('direccion_frente') == 'salida' ? '#dc2626' : '#e2e8f0' }}; background:{{ request('direccion_frente') == 'salida' ? '#fee2e2' : 'white' }}; color:{{ request('direccion_frente') == 'salida' ? '#dc2626' : '#64748b' }}; font-size:11px; font-weight:600; cursor:default; transition:all 0.2s;">
                                    <i class="material-icons" style="font-size:13px; vertical-align:middle;">arrow_upward</i>
                                    Salida
                                </button>
                            </div>
                            <input type="hidden" id="filterDireccionFrente" value="{{ request('direccion_frente') }}">
                        </div>
                    </div>
                </div>

            </div>{{-- /mv-search-adv-row --}}


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
        <div id="movilizacionesPagination" style="margin-top: 25px; overflow-x: auto; max-width: 100%;">
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



@endsection

