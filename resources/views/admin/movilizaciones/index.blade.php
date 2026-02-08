@extends('layouts.estructura_base')

@section('title', 'Movilizaciones de Equipos y Maquinarias')

@section('content')
<section class="page-title-card" style="text-align: left; margin: 0 0 10px 0;">
    <h1 class="page-title">
        <span class="page-title-line2" style="color: #000;">Historial de Movilización</span>
    </h1>
</section>

<div class="page-layout-grid" style="display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 40px; align-items: start; width: 100%;">
    
    <!-- Left Column: Table & Filters -->
    <div class="admin-card" style="margin: 0; min-height: 80vh; min-width: 0; width: 100%;">
        
        <!-- Filter Toolbar -->
        <div class="filter-toolbar-container" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; margin-bottom: 5px;">
            <!-- Frente Filter -->
            <div class="filter-item" style="flex: 1; min-width: 150px; max-width: 260px;">
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
                            style="width: 100%; border: none; background: transparent; padding: 10px 5px; font-size: 14px; outline: none;"
                            onkeyup="filterDropdownOptions(this)"
                            onfocus="this.closest('.custom-dropdown').classList.add('active')"
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
            <div class="filter-item" style="flex: 1; min-width: 150px; max-width: 260px;">
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
                            style="width: 100%; border: none; background: transparent; padding: 10px 5px; font-size: 14px; outline: none;"
                            onkeyup="filterDropdownOptions(this)"
                            onfocus="this.closest('.custom-dropdown').classList.add('active')"
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

            <!-- Search Filter -->
            <div style="flex: 1; min-width: 150px; max-width: 300px;">
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
        </div>

        <!-- Table Container -->
        <div class="custom-scrollbar-container" style="margin-top: 5px;">
            <table class="admin-table" style="width: 100%; border-collapse: separate; border-spacing: 0 8px; min-width: 910px;">
                <thead>
                    <tr class="table-row-header">
                        <th class="table-header-custom" style="width: 240px;">Equipo</th>
                        <th class="table-header-custom" style="width: 390px; text-align: center !important;">Trayecto (Origen → Destino)</th>
                        <th class="table-header-custom" style="width: 90px; text-align: center !important;">Fechas</th>
                        <th class="table-header-custom" style="width: 140px; text-align: center !important;">N° OPERACIÓN</th>
                        <th class="table-header-custom" style="width: 70px; text-align: center !important;">Estado</th>
                    </tr>
                </thead>
                <tbody id="movilizacionesTableBody" style="font-size: 14px;">
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
    <div class="counter-sidebar" style="position: sticky; top: 20px; display: flex; flex-direction: column; gap: 20px;">
        
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
@endsection

<!-- Image Overlay Modal -->
<div id="imageOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center; cursor: default;" onclick="this.style.display='none'">
    <img id="enlargedImg" style="max-width: 90%; max-height: 90%; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); transition: transform 0.3s ease;">
</div>


