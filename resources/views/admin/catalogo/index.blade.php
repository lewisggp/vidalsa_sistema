@extends('layouts.estructura_base')

@section('title', 'Catálogo de Modelos')

@section('content')
<section class="page-title-card" style="text-align: left; margin: 0 0 10px 0;">
    <h1 class="page-title">
        <span class="page-title-line2" style="color: #000;">Catálogo por Modelo</span>
    </h1>
</section>

<div class="page-layout-grid" style="display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 40px; align-items: start; width: 100%;">
    <div class="admin-card" style="margin: 0; min-height: 80vh; min-width: 0; width: fit-content; padding: 6px;">

    <div class="filter-toolbar-container" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; margin-bottom: 5px;">
    <form id="catalogoFiltersForm" onsubmit="event.preventDefault(); loadCatalogo();" style="display: contents;">
            
            <!-- Modelo Filter -->
            <div class="filter-item" style="flex: 0 1 300px; min-width: 100px;">
                <div class="custom-dropdown" id="modeloFilterSelect" data-filter-type="modelo" data-default-label="Buscar Modelo...">
                    <input type="hidden" name="modelo" data-filter-value value="{{ request('modelo') }}">
                    <div class="dropdown-trigger {{ request('modelo') ? 'filter-active' : '' }}" style="padding: 0; display: flex; align-items: center; background: #fbfcfd; border: 1px solid #cbd5e0; border-radius: 12px; height: 45px; position: relative; overflow: hidden;">
                        <div style="padding: 0 10px; display: flex; align-items: center; color: var(--maquinaria-gray-text);">
                            <i class="material-icons" style="font-size: 18px;">search</i>
                        </div>
                        <input type="text" placeholder="{{ request('modelo') ?: 'Buscar Modelo...' }}" 
                            name="filter_search_dropdown"
                            data-filter-search
                            style="width: 100%; border: none; background: transparent; padding: 10px 5px; font-size: 14px; outline: none;"
                            onkeyup="filterDropdownOptions(this);"
                            onfocus="this.closest('.custom-dropdown').classList.add('active')"
                            autocomplete="off">
                        <i class="material-icons" data-clear-btn style="padding: 0 5px; color: var(--maquinaria-gray-text); font-size: 18px; display: {{ request('modelo') ? 'block' : 'none' }};" onclick="event.stopPropagation(); clearDropdownFilter('modeloFilterSelect'); loadCatalogo();">close</i>
                    </div>
                    <div class="dropdown-content" style="padding: 5px; max-height: none; overflow: visible;">
                        <div class="dropdown-item-list" style="max-height: 250px; overflow-y: auto;">
                            <div class="dropdown-item {{ !request('modelo') || request('modelo') == 'all' ? 'selected' : '' }}" data-value="all" onclick="selectOption('modeloFilterSelect', 'all', 'TODOS LOS MODELOS'); loadCatalogo();">
                                TODOS LOS MODELOS
                            </div>
                            @foreach($availableModelos as $mod)
                                <div class="dropdown-item {{ request('modelo') == $mod ? 'selected' : '' }}" data-value="{{ $mod }}" onclick="selectOption('modeloFilterSelect', '{{ $mod }}', '{{ $mod }}'); loadCatalogo();">
                                    {{ $mod }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Año Filter -->
            <div class="filter-item" style="flex: 0 0 190px;">
                <div class="custom-dropdown" id="anioFilterSelect" data-filter-type="anio" data-default-label="Buscar Año...">
                    <input type="hidden" name="anio" data-filter-value value="{{ request('anio') }}">
                    <div class="dropdown-trigger {{ request('anio') ? 'filter-active' : '' }}" style="padding: 0; display: flex; align-items: center; background: #fbfcfd; border: 1px solid #cbd5e0; border-radius: 12px; height: 45px; position: relative; overflow: hidden;">
                        <div style="padding: 0 10px; display: flex; align-items: center; color: var(--maquinaria-gray-text);">
                            <i class="material-icons" style="font-size: 18px;">calendar_today</i>
                        </div>
                        <input type="text" placeholder="{{ request('anio') ?: 'Buscar Año...' }}" 
                            name="filter_search_dropdown"
                            data-filter-search
                            style="width: 100%; border: none; background: transparent; padding: 10px 5px; font-size: 14px; outline: none;"
                            onkeyup="filterDropdownOptions(this);"
                            onfocus="this.closest('.custom-dropdown').classList.add('active')"
                            autocomplete="off">
                        <i class="material-icons" data-clear-btn style="padding: 0 5px; color: var(--maquinaria-gray-text); font-size: 18px; display: {{ request('anio') ? 'block' : 'none' }};" onclick="event.stopPropagation(); clearDropdownFilter('anioFilterSelect'); loadCatalogo();">close</i>
                    </div>
                    <div class="dropdown-content" style="padding: 5px; max-height: none; overflow: visible;">
                        <div class="dropdown-item-list" style="max-height: 250px; overflow-y: auto;">
                            <div class="dropdown-item {{ !request('anio') || request('anio') == 'all' ? 'selected' : '' }}" data-value="all" onclick="selectOption('anioFilterSelect', 'all', 'TODOS LOS AÑOS'); loadCatalogo();">
                                TODOS LOS AÑOS
                            </div>
                            @foreach($availableAnios as $a)
                                <div class="dropdown-item {{ request('anio') == $a ? 'selected' : '' }}" data-value="{{ $a }}" onclick="selectOption('anioFilterSelect', '{{ $a }}', '{{ $a }}'); loadCatalogo();">
                                    {{ $a }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nuevo Button -->
            <div class="filter-item">
                <a href="{{ route('catalogo.create') }}" class="btn-primary-maquinaria" style="height: 45px; display: flex; align-items: center; padding: 0 15px; text-decoration: none; gap: 8px;">
                    <i class="material-icons" style="font-size: 18px;">add_circle</i>
                    Nuevo
                </a>
            </div>

    </form>
    </div>

    <div class="custom-scrollbar-container" style="width: 100%; overflow-x: auto; margin-top: 5px;">
        <!-- Added catalog-specific-table class for CSS isolation -->
        <table class="admin-table catalog-specific-table" style="width: 1040px; min-width: 60%; margin: 0; max-width: 100%;">
            <thead>
                <tr class="table-row-header">
                    <th class="table-header-custom table-cell-bordered" style="width: 160px;"></th> 
                    <th class="table-header-custom table-cell-bordered" style="width: 160px; text-align: center;">Modelo / Año</th>
                    <th class="table-header-custom table-cell-bordered" style="width: 250px; text-align: center;">Motor / Energía / Consumo</th>
                    <th class="table-header-custom table-cell-bordered" style="width: 290px; text-align: center;">Lubricantes y Fluidos</th>
                    <th class="table-header-custom" style="width: 25px; text-align: center !important; padding-left: 0; padding-right: 0;">Acciones</th>
                </tr>
            </thead>
            <tbody id="catalogoTableBody" style="font-size: 15px;">
                @include('admin.catalogo.partials.table_rows')
            </tbody>
        </table>
    </div>

    <div style="margin-top: 25px;" id="catalogoPagination">
        {{ $catalogos->links() }}
    </div>
</div> <!-- End admin-card -->

<!-- Right Sidebar: Stats -->
<div class="counter-sidebar" id="statsSidebarContainer" style="position: sticky; top: 20px; display: flex; flex-direction: column; gap: 15px;">
    @include('admin.catalogo.partials.stats_sidebar')
</div>

</div> <!-- End page-layout-grid -->



    <!-- Custom Delete Modal -->
    <div id="deleteModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999;">
        <div class="modal-card" style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <i class="material-icons modal-icon modal-icon-warning" style="font-size: 48px; color: #e53e3e; margin-bottom: 15px; display: block;">warning</i>
            <h3 class="modal-title" style="margin: 0 0 10px 0; font-size: 20px; font-weight: 700; color: #2d3748;">¿Eliminar registro?</h3>
            <p class="modal-message" style="margin-bottom: 25px; color: #718096; line-height: 1.5;">
                ¿Estás seguro de que deseas eliminar el modelo "<strong id="deleteModalUserName" style="color: #2d3748;"></strong>"? Esta acción no se puede deshacer.
            </p>
            <div class="modal-footer" style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" onclick="closeDeleteModal()" class="modal-btn modal-btn-cancel" style="padding: 10px 20px; border-radius: 6px; border: 1px solid #cbd5e0; background: white; color: #4a5568; font-weight: 600;">Cancelar</button>
                <button id="confirmDeleteBtn" type="button" class="modal-btn modal-btn-confirm" style="padding: 10px 20px; border-radius: 6px; border: none; background: #e53e3e; color: white; font-weight: 600;">Eliminar</button>
            </div>
        </div>
    </div>

@endsection
