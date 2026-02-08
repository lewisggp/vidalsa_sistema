@extends('layouts.estructura_base')

@section('title', 'Gestión de Usuarios')

@section('content')
<div>
<section class="page-title-card" style="text-align: left; width: 95%; max-width: 1600px; margin: 0 auto 10px auto;">
    <h1 class="page-title" style="display: flex; align-items: center; gap: 12px; font-size: 24px;">
        <span class="page-title-line2" style="color: #000; margin: 0;">Gestión de Usuarios</span>
        <span id="user-count-badge" style="background: rgba(0, 103, 177, 0.08); color: #0067b1; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: 700; border: 1px solid rgba(0, 103, 177, 0.15); display: inline-flex; align-items: center; justify-content: center; min-width: 30px; height: 26px; gap: 6px;">
            <i class="material-icons" style="font-size: 16px;">people</i>
            <span id="user-count-text">{{ $users->total() }}</span>
        </span>
    </h1>
</section>



<div class="admin-card" style="width: 95%; max-width: 1600px; margin: 0 auto;">
    <div class="filter-toolbar-container" style="margin-bottom: 5px;">
        <!-- Search Filter -->
        <div class="filter-item aligned-filter responsive-filter-item">
            <form id="search-form" style="width: 100%;" onsubmit="event.preventDefault();">
                <div class="search-wrapper" style="width: 100%; border-color: {{ request('search') ? '#0067b1' : '#cbd5e0' }}; background: {{ request('search') ? '#e1effa' : '#fbfcfd' }}; height: 45px;">
                    <i class="material-icons search-icon">search</i>
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" 
                        placeholder="Buscar por nombre o correo..." 
                        class="search-input-field"
                        style="height: 100%;"
                        autocomplete="off">
                    <i id="btn_clear_search" class="material-icons clear-icon" style="display: {{ request('search') ? 'block' : 'none' }};" onclick="clearUsuariosFilter('search');">close</i>
                </div>
            </form>
        </div>

        <!-- Frente Filter -->
        <div class="filter-item aligned-filter responsive-filter-item">
            <div class="custom-dropdown" id="frenteFilterSelect" data-filter-type="frente_filter" data-default-label="Filtrar Frente..." style="width: 100%;">
                <input type="hidden" name="id_frente" data-filter-value value="{{ request('id_frente') }}">
                
                @php 
                    $currentFrente = $frentes->firstWhere('ID_FRENTE', request('id_frente'));
                @endphp

                <div class="dropdown-trigger {{ request('id_frente') ? 'filter-active' : '' }}" style="background: #fbfcfd; border: 1px solid #cbd5e0; border-radius: 12px; height: 45px; display: flex; align-items: center; justify-content: space-between; padding: 0; width: 100%; overflow: hidden;">
                    
                    <div style="padding: 0 10px; display: flex; align-items: center; color: var(--maquinaria-gray-text);">
                        <i class="material-icons" style="font-size: 18px;">search</i>
                    </div>

                    <input type="text" name="filter_search_dropdown" data-filter-search
                        placeholder="{{ $currentFrente ? $currentFrente->NOMBRE_FRENTE : 'Filtrar Frente...' }}" 
                        style="width: 100%; border: none; background: transparent; padding: 10px 5px; font-size: 14px; outline: none; color: #4a5568;"
                        onkeyup="window.filterDropdownOptions(this)"
                        onfocus="this.closest('.custom-dropdown').classList.add('active')"
                        autocomplete="off">

                    <div style="display: flex; align-items: center; padding-right: 10px;">
                        <i class="material-icons" data-clear-btn
                           style="font-size: 18px; color: #a0aec0; margin-right: 5px; display: {{ request('id_frente') ? 'block' : 'none' }};" 
                           onclick="event.stopPropagation(); clearDropdownFilter('frenteFilterSelect'); loadUsuarios();"
                           title="Limpiar filtro">close</i>
                    </div>
                </div>

                <div class="dropdown-content" style="padding: 5px; max-height: none; overflow: visible;">
                    <div class="dropdown-item-list" style="max-height: 250px; overflow-y: auto;">
                        <div class="dropdown-item {{ !request('id_frente') || request('id_frente') == 'all' ? 'selected' : '' }}" data-value="all" onclick="selectOption('frenteFilterSelect', 'all', 'TODOS LOS FRENTES');">
                            TODOS LOS FRENTES
                        </div>
                        @foreach($frentes as $frente)
                            <div class="dropdown-item {{ request('id_frente') == $frente->ID_FRENTE ? 'selected' : '' }}" data-value="{{ $frente->ID_FRENTE }}" onclick="selectOption('frenteFilterSelect', '{{ $frente->ID_FRENTE }}', '{{ $frente->NOMBRE_FRENTE }}');">
                                {{ $frente->NOMBRE_FRENTE }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- New User Button -->
        <div class="filter-item aligned-filter responsive-btn-item">
            <a href="{{ route('usuarios.create') }}" class="btn-primary-maquinaria btn-nuevo-usuario">
                <i class="material-icons">person_add</i>
                Nuevo
            </a>
        </div>
    </div>

    <!-- Unified Responsive Table -->
    <div class="custom-scrollbar-container">
        <table class="admin-table" style="width: 100% !important;">
            <thead>
                <tr style="background: #cbd5e0; text-align: left; color: var(--maquinaria-dark-blue); font-size: 14px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; border-bottom: 2px solid #a0aec0;">
                    <th class="table-cell-bordered" style="padding: 10px 15px; text-align: left; min-width: 200px;">Nombre y Apellido</th>
                    <th class="table-cell-bordered" style="padding: 10px 15px; text-align: left; min-width: 180px;">Correo</th>
                    <th class="table-cell-bordered" style="padding: 10px 15px; text-align: left; min-width: 150px;">Rol</th>
                    <th class="table-cell-bordered" style="padding: 10px 15px; text-align: left; min-width: 120px;">Acceso</th>
                    <th class="table-cell-bordered" style="padding: 10px 15px; text-align: left; min-width: 180px;">Frente de Trabajo</th>
                    <th class="table-cell-bordered" style="padding: 10px 15px; text-align: left; min-width: 100px;">Estado</th>
                    <th style="padding: 10px 15px; text-align: left; width: 100px;">Acciones</th>
                </tr>
            </thead>
            <tbody id="usuariosTableBody" style="font-size: 14px;">
                @include('admin.usuarios.partials.table_rows', ['users' => $users])
            </tbody>
        </table>
        {{-- Single Delete Form for Optimization --}}
        <form id="delete-form-global" action="" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>

    <!-- Pagination -->

    <div id="usuariosPagination" style="margin-top: 25px;">
        {{ $users->links() }}
    </div>

    <!-- Custom Delete Modal -->
    <div id="deleteModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999;">
        <div class="modal-card" style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <i class="material-icons modal-icon modal-icon-warning" style="font-size: 48px; color: #e53e3e; margin-bottom: 15px; display: block;">warning</i>
            <h3 class="modal-title" style="margin: 0 0 10px 0; font-size: 20px; font-weight: 700; color: #2d3748;">¿Eliminar registro?</h3>
            <p class="modal-message" style="margin-bottom: 25px; color: #718096; line-height: 1.5;">
                ¿Estás seguro de que deseas eliminar a "<strong id="deleteModalUserName" style="color: #2d3748;"></strong>"? Esta acción no se puede deshacer.
            </p>
            <div class="modal-footer" style="display: flex; gap: 10px; justify-content: center;">
                <button onclick="closeDeleteModal()" class="modal-btn modal-btn-cancel" style="padding: 10px 20px; border-radius: 6px; border: 1px solid #cbd5e0; background: white; color: #4a5568; font-weight: 600;">Cancelar</button>
                <button id="confirmDeleteBtn" class="modal-btn modal-btn-confirm" style="padding: 10px 20px; border-radius: 6px; border: none; background: #e53e3e; color: white; font-weight: 600;">Eliminar</button>
            </div>
        </div>
    </div>
</div>
</div>


@endsection

@section('extra_js')
<script src="{{ asset('js/maquinaria/usuarios_index.js') }}?v={{ time() }}"></script>
@endsection
