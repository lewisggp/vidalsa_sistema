@extends('layouts.estructura_base')

@section('title', 'Gestión de Equipos')

@section('content')

<section class="page-title-card" style="text-align: left; margin: 0 0 10px 0;">
    <h1 class="page-title">
        <span class="page-title-line2" style="color: #000;">Gestión de Equipos y Maquinaria</span>
    </h1>
</section>

<div class="page-layout-grid" style="display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 40px; align-items: start; width: 100%;">
    
    <!-- Left Column: Table & Filters -->
    <div class="admin-card" data-page="equipos" style="margin: 0; min-height: 80vh; min-width: 0; width: 100%;">
    <div class="filter-toolbar-container" style="margin-bottom: 5px;">
        <!-- Frente Filter -->
        <div class="filter-item aligned-filter">
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
                    <i class="material-icons" data-clear-btn style="padding: 0 5px; color: var(--maquinaria-gray-text); font-size: 18px; display: {{ request('id_frente') && request('id_frente') != 'all' ? 'block' : 'none' }};" onclick="event.stopPropagation(); clearDropdownFilter('frenteFilterSelect'); loadEquipos();">close</i>
                </div>

                <div class="dropdown-content" style="padding: 5px; max-height: none; overflow: visible; z-index: 1000;">
                    <div class="dropdown-item-list" style="max-height: 250px; overflow-y: auto;">
                            <div class="dropdown-item {{ !request('id_frente') || request('id_frente') == 'all' ? 'selected' : '' }}" data-value="all" onclick="selectOption('frenteFilterSelect', 'all', 'TODOS LOS FRENTES'); loadEquipos();">
                                TODOS LOS FRENTES
                            </div>
                            @foreach($frentes as $frente)
                                <div class="dropdown-item {{ request('id_frente') == $frente->ID_FRENTE ? 'selected' : '' }}" data-value="{{ $frente->ID_FRENTE }}" onclick="selectOption('frenteFilterSelect', '{{ $frente->ID_FRENTE }}', '{{ $frente->NOMBRE_FRENTE }}'); loadEquipos();">
                                    {{ $frente->NOMBRE_FRENTE }}
                                </div>
                            @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Tipo Filter -->
        <div class="filter-item aligned-filter" style="flex: 1.5;">
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
                    <i class="material-icons" data-clear-btn
                       style="padding: 0 5px; color: var(--maquinaria-gray-text); font-size: 18px; display: {{ request('id_tipo') ? 'block' : 'none' }};"
                       onclick="event.preventDefault(); event.stopPropagation(); clearDropdownFilter('tipoFilterSelect'); loadEquipos();">close</i>
                </div>

                <div class="dropdown-content" style="padding: 5px; max-height: none; overflow: visible; z-index: 1000;">
                    <div class="dropdown-item-list" style="max-height: 250px; overflow-y: auto;">
                        <div class="dropdown-item {{ !request('id_tipo') ? 'selected' : '' }}" data-value="" onclick="selectOption('tipoFilterSelect', '', 'Filtrar Tipo...'); loadEquipos();">
                            TODOS LOS TIPOS
                        </div>
                        @foreach($allTipos as $tipo)
                            <div class="dropdown-item {{ request('id_tipo') == $tipo->id ? 'selected' : '' }}" data-value="{{ $tipo->id }}" onclick="selectOption('tipoFilterSelect', '{{ $tipo->id }}', '{{ $tipo->nombre }}'); loadEquipos();">
                                {{ $tipo->nombre }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Filter / Seriales -->
        <!-- Search Filter / Seriales + Advanced Filter Button -->
        <div class="filter-item aligned-filter" style="display: flex; gap: 10px;">
            <form action="{{ route('equipos.index') }}" method="GET" id="search-form" style="flex: 1; margin: 0;">
                
                <div class="search-wrapper" style="width: 100%; border-color: {{ request('search_query') ? '#0067b1' : '#cbd5e0' }}; background: {{ request('search_query') ? '#e1effa' : '#fff' }};">
                    <i class="material-icons search-icon">search</i>
                    <input type="text" id="searchInput" name="search_query" value="{{ request('search_query') }}" 
                        placeholder="Buscar Seriales" 
                        aria-label="Search Serials"
                        class="search-input-field"
                        autocomplete="off"
                        onkeyup="if(this.value.length >= 4 || this.value.length == 0) { /* Debounce handled in script */ }">
                    <i id="btn_clear_search" class="material-icons clear-icon" 
                       style="display: {{ request('search_query') ? 'block' : 'none' }};" 
                       onclick="event.preventDefault(); event.stopPropagation(); selectAdvancedFilter('search', ''); document.getElementById('searchInput').value='';">close</i>
                </div>
            </form>

            <!-- Advanced Filter Trigger -->
            <div style="position: relative;">
                <button type="button" id="btnAdvancedFilter" class="btn-primary-maquinaria" style="height: 45px; width: 45px; padding: 0; display: flex; align-items: center; justify-content: center; background: {{ request('modelo') || request('anio') ? '#e1effa' : 'white' }}; border: 1px solid {{ request('modelo') || request('anio') ? '#0067b1' : '#cbd5e0' }}; color: {{ request('modelo') || request('anio') ? '#0067b1' : '#64748b' }}; box-shadow: none;">
                    <i class="material-icons">filter_list</i>
                </button>
                
                <!-- Dynamic Filter Panel -->
                <div id="advancedFilterPanel" style="display: none; position: absolute; top: 100%; right: 0; width: 300px; background: #e2e8f0; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15); border: 1px solid #cbd5e1; z-index: 100; margin-top: 10px; padding: 15px;">
                    <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 700; color: #334155; display: flex; justify-content: space-between; align-items: center;">
                        Filtros Avanzados
                        <span style="font-size: 11px; color: #64748b; font-weight: 400; text-decoration: underline;" onclick="clearAdvancedFilters()">Limpiar Todo</span>
                    </h4>

                    <!-- Modelo Filter (Rebuilt like Tipo) -->
                    <div style="margin-bottom: 15px;">
                        <span style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px;">Modelo</span>
                        <div class="custom-dropdown" id="modeloAdvFilter" data-filter-type="modelo" data-default-label="Seleccionar Modelo..." style="font-size: 12px;">
                            <input type="hidden" name="modelo" data-filter-value value="{{ request('modelo') }}">
                            
                            <div class="dropdown-trigger" style="padding: 0; display: flex; align-items: center; background: {{ request('modelo') ? '#e1effa' : 'white' }}; border: 1px solid #e2e8f0; border-radius: 6px; height: 32px;">
                                <div style="padding: 0 8px; display: flex; align-items: center; color: #94a3b8;">
                                    <i class="material-icons" style="font-size: 16px;">search</i>
                                </div>
                                <input type="text" name="filter_search_dropdown" data-filter-search 
                                    placeholder="{{ request('modelo') ?: 'Seleccionar Modelo...' }}" 
                                    aria-label="Filtrar Modelo"
                                    style="width: 100%; border: none; background: transparent; padding: 6px 5px; font-size: 12px; outline: none;"
                                    oninput="window.filterDropdownOptions(this)"
                                    autocomplete="off">
                                <i class="material-icons" data-clear-btn style="padding: 0 5px; color: #94a3b8; font-size: 16px; display: {{ request('modelo') ? 'block' : 'none' }};" 
                                   onclick="event.stopPropagation(); clearDropdownFilter('modeloAdvFilter'); loadEquipos();">close</i>
                            </div>

                            <div class="dropdown-content" style="padding: 5px; max-height: none; overflow: visible; z-index: 1000;">
                                <div class="dropdown-item-list" style="max-height: 150px; overflow-y: auto;">
                                    @if(isset($availableModelos))
                                        @foreach($availableModelos as $mod)
                                            @if(trim($mod) !== '')
                                                <div class="dropdown-item {{ request('modelo') == $mod ? 'selected' : '' }}" data-value="{{ $mod }}" onclick="selectOption('modeloAdvFilter', '{{ $mod }}', '{{ $mod }}'); loadEquipos();">{{ $mod }}</div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Marca Filter (Rebuilt like Tipo) -->
                    <div style="margin-bottom: 15px;">
                        <span style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px;">Marca</span>
                        <div class="custom-dropdown" id="marcaAdvFilter" data-filter-type="marca" data-default-label="Seleccionar Marca..." style="font-size: 12px;">
                            <input type="hidden" name="marca" data-filter-value value="{{ request('marca') }}">
                            
                            <div class="dropdown-trigger" style="padding: 0; display: flex; align-items: center; background: {{ request('marca') ? '#e1effa' : 'white' }}; border: 1px solid #e2e8f0; border-radius: 6px; height: 32px;">
                                <div style="padding: 0 8px; display: flex; align-items: center; color: #94a3b8;">
                                    <i class="material-icons" style="font-size: 16px;">search</i>
                                </div>
                                <input type="text" name="filter_search_dropdown" data-filter-search 
                                    placeholder="{{ request('marca') ?: 'Seleccionar Marca...' }}" 
                                    aria-label="Filtrar Marca"
                                    style="width: 100%; border: none; background: transparent; padding: 6px 5px; font-size: 12px; outline: none;"
                                    oninput="window.filterDropdownOptions(this)"
                                    autocomplete="off">
                                <i class="material-icons" data-clear-btn style="padding: 0 5px; color: #94a3b8; font-size: 16px; display: {{ request('marca') ? 'block' : 'none' }};" 
                                   onclick="event.stopPropagation(); clearDropdownFilter('marcaAdvFilter'); loadEquipos();">close</i>
                            </div>

                            <div class="dropdown-content" style="padding: 5px; max-height: none; overflow: visible; z-index: 1000;">
                                <div class="dropdown-item-list" style="max-height: 150px; overflow-y: auto;">
                                    @if(isset($availableMarcas))
                                        @foreach($availableMarcas as $marca)
                                            @if(trim($marca) !== '')
                                                <div class="dropdown-item {{ request('marca') == $marca ? 'selected' : '' }}" data-value="{{ $marca }}" onclick="selectOption('marcaAdvFilter', '{{ $marca }}', '{{ $marca }}'); loadEquipos();">{{ $marca }}</div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Año Filter (Rebuilt like Tipo) -->
                    <div>
                        <span style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px;">Año</span>
                        <div class="custom-dropdown" id="anioAdvFilter" data-filter-type="anio" data-default-label="Seleccionar Año..." style="font-size: 12px;">
                            <input type="hidden" name="anio" data-filter-value value="{{ request('anio') }}">
                            
                            <div class="dropdown-trigger" style="padding: 0; display: flex; align-items: center; background: {{ request('anio') ? '#e1effa' : 'white' }}; border: 1px solid #e2e8f0; border-radius: 6px; height: 32px;">
                                <div style="padding: 0 8px; display: flex; align-items: center; color: #94a3b8;">
                                    <i class="material-icons" style="font-size: 16px;">search</i>
                                </div>
                                <input type="text" name="filter_search_dropdown" data-filter-search 
                                    placeholder="{{ request('anio') ?: 'Seleccionar Año...' }}" 
                                    aria-label="Filtrar Año"
                                    style="width: 100%; border: none; background: transparent; padding: 6px 5px; font-size: 12px; outline: none;"
                                    oninput="window.filterDropdownOptions(this)"
                                    autocomplete="off">
                                <i class="material-icons" data-clear-btn style="padding: 0 5px; color: #94a3b8; font-size: 16px; display: {{ request('anio') ? 'block' : 'none' }};" 
                                   onclick="event.stopPropagation(); clearDropdownFilter('anioAdvFilter'); loadEquipos();">close</i>
                            </div>

                            <div class="dropdown-content" style="padding: 5px; max-height: none; overflow: visible; z-index: 1000;">
                                <div class="dropdown-item-list" style="max-height: 120px; overflow-y: auto;">
                                    @if(isset($availableAnios))
                                        @foreach($availableAnios as $anio)
                                            @if(trim($anio) !== '')
                                                <div class="dropdown-item {{ request('anio') == $anio ? 'selected' : '' }}" data-value="{{ $anio }}" onclick="selectOption('anioAdvFilter', '{{ $anio }}', '{{ $anio }}'); loadEquipos();">{{ $anio }}</div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categoría Flota Filter -->
                    <div style="margin-top: 15px;">
                        <span style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px;">Categoría Flota</span>
                        <div class="custom-dropdown" id="categoriaAdvFilter" data-filter-type="categoria" data-default-label="Seleccionar Categoría..." style="font-size: 12px;">
                            <input type="hidden" name="categoria" data-filter-value value="{{ request('categoria') }}">
                            
                            <div class="dropdown-trigger" style="padding: 0; display: flex; align-items: center; background: {{ request('categoria') ? '#e1effa' : 'white' }}; border: 1px solid #e2e8f0; border-radius: 6px; height: 32px;">
                                <div style="padding: 0 8px; display: flex; align-items: center; color: #94a3b8;">
                                    <i class="material-icons" style="font-size: 16px;">local_shipping</i>
                                </div>
                                <input type="text" readonly
                                    id="filter_display_categoria"
                                    name="filter_display_categoria"
                                    placeholder="{{ request('categoria') ?: 'Seleccionar Categoría...' }}" 
                                    aria-label="Filtrar Categoría"
                                    style="width: 100%; border: none; background: transparent; padding: 6px 5px; font-size: 12px; outline: none;"
                                    onclick="this.closest('.custom-dropdown').classList.toggle('active')">
                                <i class="material-icons" data-clear-btn style="padding: 0 5px; color: #94a3b8; font-size: 16px; display: {{ request('categoria') ? 'block' : 'none' }};" 
                                   onclick="event.stopPropagation(); clearDropdownFilter('categoriaAdvFilter'); loadEquipos();">close</i>
                            </div>

                            <div class="dropdown-content" style="padding: 5px; max-height: none; overflow: visible; z-index: 1000;">
                                <div class="dropdown-item-list">
                                    <div class="dropdown-item {{ request('categoria') == 'FLOTA LIVIANA' ? 'selected' : '' }}" data-value="FLOTA LIVIANA" onclick="selectOption('categoriaAdvFilter', 'FLOTA LIVIANA', 'FLOTA LIVIANA'); loadEquipos();">FLOTA LIVIANA</div>
                                    <div class="dropdown-item {{ request('categoria') == 'FLOTA PESADA' ? 'selected' : '' }}" data-value="FLOTA PESADA" onclick="selectOption('categoriaAdvFilter', 'FLOTA PESADA', 'FLOTA PESADA'); loadEquipos();">FLOTA PESADA</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estado Operativo Filter -->
                    <div style="margin-top: 15px;">
                        <span style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px;">Estado Operativo</span>
                        <div class="custom-dropdown" id="estadoAdvFilter" data-filter-type="estado" data-default-label="Seleccionar Estado..." style="font-size: 12px;">
                            <input type="hidden" name="estado" data-filter-value value="{{ request('estado') }}">
                            
                            <div class="dropdown-trigger" style="padding: 0; display: flex; align-items: center; background: {{ request('estado') ? '#e1effa' : 'white' }}; border: 1px solid #e2e8f0; border-radius: 6px; height: 32px;">
                                <div style="padding: 0 8px; display: flex; align-items: center; color: #94a3b8;">
                                    <i class="material-icons" style="font-size: 16px;">info</i>
                                </div>
                                <input type="text" readonly
                                    id="filter_display_estado"
                                    name="filter_display_estado"
                                    placeholder="{{ request('estado') ?: 'Seleccionar Estado...' }}" 
                                    aria-label="Filtrar Estado Operativo"
                                    style="width: 100%; border: none; background: transparent; padding: 6px 5px; font-size: 12px; outline: none;"
                                    onclick="this.closest('.custom-dropdown').classList.toggle('active')">
                                <i class="material-icons" data-clear-btn style="padding: 0 5px; color: #94a3b8; font-size: 16px; display: {{ request('estado') ? 'block' : 'none' }};" 
                                   onclick="event.stopPropagation(); clearDropdownFilter('estadoAdvFilter'); loadEquipos();">close</i>
                            </div>

                            <div class="dropdown-content" style="padding: 5px; max-height: none; overflow: visible; z-index: 1000;">
                                <div class="dropdown-item-list">
                                    <div class="dropdown-item {{ request('estado') == 'OPERATIVO' ? 'selected' : '' }}" data-value="OPERATIVO" onclick="selectOption('estadoAdvFilter', 'OPERATIVO', 'OPERATIVO'); loadEquipos();">OPERATIVO</div>
                                    <div class="dropdown-item {{ request('estado') == 'INOPERATIVO' ? 'selected' : '' }}" data-value="INOPERATIVO" onclick="selectOption('estadoAdvFilter', 'INOPERATIVO', 'INOPERATIVO'); loadEquipos();">INOPERATIVO</div>
                                    <div class="dropdown-item {{ request('estado') == 'EN MANTENIMIENTO' ? 'selected' : '' }}" data-value="EN MANTENIMIENTO" onclick="selectOption('estadoAdvFilter', 'EN MANTENIMIENTO', 'EN MANTENIMIENTO'); loadEquipos();">EN MANTENIMIENTO</div>
                                    <div class="dropdown-item {{ request('estado') == 'DESINCORPORADO' ? 'selected' : '' }}" data-value="DESINCORPORADO" onclick="selectOption('estadoAdvFilter', 'DESINCORPORADO', 'DESINCORPORADO'); loadEquipos();">DESINCORPORADO</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documentation Filters (New) -->
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #cbd5e1;">
                        <span style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;">Documentación Cargada</span>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                            <label style="display: flex; align-items: center; font-size: 13px; color: #334155;">
                                <input type="checkbox" id="chk_propiedad" onchange="toggleDocFilter('propiedad')" {{ request('filter_propiedad') == 'true' ? 'checked' : '' }} style="margin-right: 8px; accent-color: var(--maquinaria-blue);">
                                Propiedad
                            </label>

                            <label style="display: flex; align-items: center; font-size: 13px; color: #334155;">
                                <input type="checkbox" id="chk_poliza" onchange="toggleDocFilter('poliza')" {{ request('filter_poliza') == 'true' ? 'checked' : '' }} style="margin-right: 8px; accent-color: var(--maquinaria-blue);">
                                Póliza
                            </label>

                            <label style="display: flex; align-items: center; font-size: 13px; color: #334155;">
                                <input type="checkbox" id="chk_rotc" onchange="toggleDocFilter('rotc')" {{ request('filter_rotc') == 'true' ? 'checked' : '' }} style="margin-right: 8px; accent-color: var(--maquinaria-blue);">
                                ROTC
                            </label>

                            <label style="display: flex; align-items: center; font-size: 13px; color: #334155;">
                                <input type="checkbox" id="chk_racda" onchange="toggleDocFilter('racda')" {{ request('filter_racda') == 'true' ? 'checked' : '' }} style="margin-right: 8px; accent-color: var(--maquinaria-blue);">
                                RACDA
                            </label>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- New Button -->
        <!-- Dropdown Menu Button (Acciones: Nuevo, Exportar, Movilización) -->
        <div class="filter-item aligned-filter" style="position: relative; width: auto; flex: 0 0 auto; margin-left: auto;">
            
            <!-- Main Trigger Button -->
            <button type="button" id="btnAcciones" class="btn-primary-maquinaria" style="padding: 0 15px; height: 45px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <i class="material-icons">settings</i>
                <span>Acciones</span>
                <i class="material-icons" style="font-size: 18px; margin-left: 2px;">expand_more</i>
            </button>

            <!-- Dropdown Menu -->
            <div id="splitDropdownMenu" style="display: none; position: absolute; top: 100%; right: 0; width: 220px; background: #e2e8f0; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; z-index: 50; margin-top: 5px; overflow: hidden; animation: slideDown 0.2s ease-out;">
                
                <!-- Movilización -->
                <a href="{{ route('movilizaciones.index') }}" class="dropdown-item-custom" style="display: flex; align-items: center; gap: 10px; padding: 12px 15px; color: #475569; text-decoration: none; transition: all 0.2s; border-bottom: 1px solid #f1f5f9;">
                    <div style="background: #f0fdf4; padding: 6px; border-radius: 6px; display: flex;">
                        <i class="material-icons" style="font-size: 18px; color: #16a34a;">local_shipping</i>
                    </div>
                    <span style="font-size: 14px; font-weight: 500;">Movilización</span>
                </a>

                <!-- Exportar -->
                <a href="#" onclick="exportEquipos(); return false;" class="dropdown-item-custom" style="display: flex; align-items: center; gap: 10px; padding: 12px 15px; color: #475569; text-decoration: none; transition: all 0.2s; border-bottom: 1px solid #f1f5f9;">
                    <div style="background: #f1f5f9; padding: 6px; border-radius: 6px; display: flex;">
                        <i class="material-icons" style="font-size: 18px; color: #64748b;">download</i>
                    </div>
                    <span style="font-size: 14px; font-weight: 500;">Exportación de Data</span>
                </a>

                <!-- Nuevo -->
                <a href="{{ route('equipos.create') }}" class="dropdown-item-custom" style="display: flex; align-items: center; gap: 10px; padding: 12px 15px; color: #475569; text-decoration: none; transition: all 0.2s;">
                    <div style="background: #e0f2fe; padding: 6px; border-radius: 6px; display: flex;">
                        <i class="material-icons" style="font-size: 18px; color: #0284c7;">add_circle</i>
                    </div>
                    <span style="font-size: 14px; font-weight: 500;">Nuevo Equipo</span>
                </a>
            </div>
        </div>

        <!-- Year filter hidden input moved inside the dropdown container -->

        <!-- Advanced Filter Logic migrated to equipos_index.js -->
    </div>
    <div class="custom-scrollbar-container" style="margin-top: 5px;">
        <table class="admin-table table-equipos-mobile" style="width: 100%; border-collapse: separate; border-spacing: 0 10px;">
            <thead>
                <tr class="table-row-header">
                    <th class="table-header-custom" style="width: 150px;"></th> <!-- Foto Fixed -->
                    <th class="table-header-custom" style="width: 22%;">Tipo</th> <!-- Fluid (Wide) -->
                    <th class="table-header-custom" style="width: 15%;">Marca / Modelo</th> <!-- Fluid (Narrower) -->
                    <th class="table-header-custom" style="width: 25%;">Serials / Placa / ID</th> <!-- Fluid (Wide) -->
                    <th class="table-header-custom" style="width: 110px;">Estatus</th> <!-- Fixed -->
                    <th class="table-cell-center" style="width: 50px;"></th> <!-- Fixed -->
                </tr>
            </thead>
            <tbody id="equiposTableBody" style="font-size: 15px;">
                @include('admin.equipos.partials.table_rows')

            </tbody>
        </table>
        
        <form id="delete-form-global" action="" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>



    <!-- Pagination removed as requested (Single list on filter) -->
    <div id="equiposPagination" style="margin-top: 25px;"></div>
</div> <!-- End admin-card -->

<!-- Right Column: Simple Counter -->
<div class="counter-sidebar" style="position: sticky; top: 20px; display: flex; flex-direction: column; gap: 15px;">
    @php $hasFilter = request('search_query') || request('id_frente') || request('id_tipo'); @endphp
    
    <!-- Main Total Card -->
    <div style="background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%); border-radius: 12px; padding: 15px; color: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); position: relative; overflow: hidden;">
        <!-- Decorative Icon -->
        <i class="material-icons" style="position: absolute; right: -15px; bottom: -15px; font-size: 80px; opacity: 0.1; transform: rotate(-15deg);">agriculture</i>
        
        <div style="position: relative; z-index: 2;">
            <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.8; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                <i class="material-icons" style="font-size: 14px;">pie_chart</i>
                Consolidado de Equipos
            </div>
            
            <div style="display: flex; align-items: center; gap: 8px;">
                <!-- Main Total -->
                <div style="display: flex; flex-direction: column; align-items: center; background: rgba(255,255,255,0.15); padding: 8px 6px; border-radius: 10px; min-width: 65px;">
                    <span id="stats_total" style="font-size: 36px; font-weight: 800; line-height: 1;">
                        {{ $hasFilter ? $stats['total'] : '--' }}
                    </span>
                    <span style="font-size: 13px; opacity: 0.8; font-weight: 700; margin-top: 2px;">TOTAL</span>
                </div>
                
                <!-- Detailed Stats Row -->
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 4px; flex: 1;">
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(239, 68, 68, 0.15); padding: 6px 2px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.25);">
                        <i class="material-icons" style="font-size: 20px; color: #ef4444; margin-bottom: 2px;">cancel</i>
                        <strong id="stats_inactivos" style="font-weight: 800; font-size: 20px;">{{ $hasFilter ? $stats['inactivos'] : '--' }}</strong>
                        <span style="font-size: 11px; opacity: 0.8; font-weight: 700; text-transform: uppercase;">Inoperativos</span>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(245, 158, 11, 0.15); padding: 6px 2px; border-radius: 8px; border: 1px solid rgba(245, 158, 11, 0.25);">
                        <i class="material-icons" style="font-size: 20px; color: #f59e0b; margin-bottom: 2px;">engineering</i>
                        <strong id="stats_mantenimiento" style="font-weight: 800; font-size: 20px;">{{ $hasFilter ? $stats['mantenimiento'] : '--' }}</strong>
                        <span style="font-size: 11px; opacity: 0.8; font-weight: 700;">MANTENIMIENTO</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breakdown by Type or Front (Dynamic) -->
    <div style="background: white; border-radius: 12px; padding: 15px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden;">
        <div id="distributionStatsContainer">
            @include('admin.equipos.partials.distribution_stats')
        </div>
    </div>
</div>

</div> <!-- End Page Layout Grid -->






<!-- Image Overlay Modal -->
<div id="imageOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center; cursor: default;" onclick="this.style.display='none'">
    <img id="enlargedImg" style="max-width: 90%; max-height: 90%; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); transition: transform 0.3s ease;">
</div>

<!-- Floating Action Bar -->
<div id="bulkFloatingBar" class="selection-floating-bar">
    <div class="selection-counter">
        <div style="background: rgba(255,255,255,0.1); padding: 5px; border-radius: 50%; display: flex;">
            <i class="material-icons" style="font-size: 18px; color: #60a5fa;">inventory_2</i>
        </div>
        <span id="bulkCountText">0 seleccionados</span>
    </div>
    <div style="width: 1px; height: 24px; background: rgba(255,255,255,0.2);"></div>
    <div style="display: flex; gap: 10px;">
        <button type="button" onclick="clearSelection(event)" style="background: transparent; border: none; color: #94a3b8; font-size: 13px; font-weight: 600;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#94a3b8'">
            Limpiar
        </button>
        <button type="button" onclick="openBulkModal(event)" class="btn-bulk-action">
            <i class="material-icons" style="font-size: 18px;">local_shipping</i>
            Asignar
        </button>
    </div>
</div>

<!-- Hidden Datalist for Dynamic Modal (Autocomplete Source) -->
<datalist id="frentesList" style="display: none;">
    @foreach($frentes as $f)
        <option value="{{ $f->NOMBRE_FRENTE }}" data-id="{{ $f->ID_FRENTE }}"></option>
    @endforeach
</datalist>

    @include('admin.equipos.partials.equipment_details_modal')
@endsection
@section('extra_js')
    {{-- Replaced by Global Load in Layout --}}
@endsection
