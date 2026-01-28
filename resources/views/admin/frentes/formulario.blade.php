@extends('layouts.estructura_base')

@section('title', 'Gestión de Frentes')

@section('content')
<section class="page-title-card" style="text-align: center; margin: 0 auto 10px auto;">
    <h1 class="page-title">
        <span class="page-title-line2" style="color: #000; font-size: 28px;" id="formTitle">{{ isset($frente) ? 'Edición de Frente de Trabajo' : 'Registro de Frente de Trabajo' }}</span>
    </h1>
</section>






    <!-- Form -->
    <div class="admin-card" style="max-width: 800px; margin: 0 auto;">
    
    <!-- Top Search Bar (Standardized Smart Dropdown) -->
    <div style="margin-bottom: 30px; display: flex; justify-content: center;">
        <div style="width: 100%; max-width: 500px; position: relative;">
            
            <div class="custom-dropdown" id="frenteSearchDropdown" style="width: 100%;">
                <div class="dropdown-trigger" onclick="toggleDropdown('frenteSearchDropdown')" style="background: #fff; border: 1px solid #cbd5e0; border-radius: 12px; height: 45px; display: flex; align-items: center; justify-content: space-between; padding: 0; width: 100%; overflow: hidden; cursor: pointer;">
                    
                    <div style="padding: 0 10px; display: flex; align-items: center; color: var(--maquinaria-gray-text);">
                        <i class="material-icons" style="font-size: 18px;">search</i>
                    </div>

                    <input type="text" id="filterSearchInput" 
                        placeholder="Buscar frente para editar..." 
                        style="width: 100%; border: none; background: transparent; padding: 10px 5px; font-size: 14px; outline: none; color: #4a5568;"
                        autocomplete="off"
                        oninput="window.filterFrentesDropdown(this)">

                    <div style="display: flex; align-items: center; padding-right: 10px;">
                        <i id="btn_clear_search_frente" class="material-icons" 
                           style="font-size: 18px; color: #a0aec0; margin-right: 5px; cursor: pointer; display: none;" 
                           onclick="event.stopPropagation(); window.clearFrentesSearchSPA();">close</i>
                        <i class="material-icons" style="color: #a0aec0; cursor: pointer;">expand_more</i>
                    </div>
                </div>

                <div class="dropdown-content" style="padding: 5px; max-height: none; overflow: visible;">
                    <div class="dropdown-item-list" id="frenteItemsList" style="max-height: 250px; overflow-y: auto;">
                        @if(isset($allFrentes))
                            @foreach($allFrentes as $f)
                                <div class="dropdown-item search-result-item" 
                                     data-name="{{ $f->NOMBRE_FRENTE }}"
                                     onclick="selectFrenteSPA('{{ $f->ID_FRENTE }}')">
                                    {{ $f->NOMBRE_FRENTE }}
                                </div>
                            @endforeach
                        @endif
                        <div id="no-results-msg" style="display: none; padding: 10px 15px; color: #94a3b8; font-size: 14px;">No se encontraron resultados</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <form action="{{ isset($frente) ? route('frentes.update', $frente->ID_FRENTE) : route('frentes.store') }}" 
          method="POST" 
          id="frenteForm"
          onsubmit="if(window.showPreloader) window.showPreloader();">
        @csrf
        @if(isset($frente)) @method('PUT') @endif
        <input type="hidden" id="ID_FRENTE" name="ID_FRENTE" value="{{ $frente->ID_FRENTE ?? '' }}">

        <div class="form-grid">
            <!-- Row 1 -->
            <div>
                <label for="NOMBRE_FRENTE" class="form-label">Nombre del Frente <span style="color: red;">*</span></label>
                <input type="text" id="NOMBRE_FRENTE" name="NOMBRE_FRENTE" class="form-input-custom" style="background: white;" placeholder="Ej: MINA 1" value="{{ old('NOMBRE_FRENTE', $frente->NOMBRE_FRENTE ?? '') }}" required autocomplete="off">
            </div>

            <div>
                <div class="form-label">Tipo de Frente <span style="color: red;">*</span></div>
                <div class="custom-dropdown" id="tipoSelect">
                    <input type="hidden" name="TIPO_FRENTE" id="input_tipo" value="{{ old('TIPO_FRENTE', $frente->TIPO_FRENTE ?? '') }}">
                    <div class="dropdown-trigger" onclick="toggleDropdown('tipoSelect')" style="background: white;">
                        <span id="label_tipo">{{ old('TIPO_FRENTE', $frente->TIPO_FRENTE ?? 'Seleccione Tipo...') }}</span>
                        <i class="material-icons">expand_more</i>
                    </div>
                    <div class="dropdown-content">
                        <div class="dropdown-item" onclick="selectOption('tipoSelect', 'OPERACION', 'OPERACION', 'tipo')">OPERACION</div>
                        <div class="dropdown-item" onclick="selectOption('tipoSelect', 'RESGUARDO', 'RESGUARDO', 'tipo')">RESGUARDO</div>
                    </div>
                </div>
            </div>

            <!-- Row 2 -->
            <div>
                <label for="UBICACION" class="form-label">Ubicación / Geografía <span style="color: red;">*</span></label>
                <input type="text" id="UBICACION" name="UBICACION" class="form-input-custom" style="background: white;" placeholder="Ej: Sector 4" value="{{ old('UBICACION', $frente->UBICACION ?? '') }}" required autocomplete="off">
            </div>

            <div>
                <div class="form-label">Estatus del Proyecto <span style="color: red;">*</span></div>
                <div class="custom-dropdown" id="statusSelect">
                    <input type="hidden" name="ESTATUS_FRENTE" id="input_estatus" value="{{ old('ESTATUS_FRENTE', $frente->ESTATUS_FRENTE ?? '') }}">
                    <div class="dropdown-trigger" onclick="toggleDropdown('statusSelect')" style="background: white;">
                        <span id="label_estatus">{{ old('ESTATUS_FRENTE', $frente->ESTATUS_FRENTE ?? 'Seleccione Estatus...') }}</span>
                        <i class="material-icons">expand_more</i>
                    </div>
                    <div class="dropdown-content">
                        <div class="dropdown-item" onclick="selectOption('statusSelect', 'ACTIVO', 'ACTIVO', 'estatus')">ACTIVO</div>
                        <div class="dropdown-item" onclick="selectOption('statusSelect', 'FINALIZADO', 'FINALIZADO', 'estatus')">FINALIZADO</div>
                    </div>
                </div>
            </div>

            <!-- Row 3 -->
            <div>
                <label for="RESP_1_NOM" class="form-label">Responsable Principal <span style="color: red;">*</span></label>
                <input type="text" id="RESP_1_NOM" name="RESP_1_NOM" class="form-input-custom" style="background: white;" placeholder="Nombre completo" value="{{ old('RESP_1_NOM', $frente->RESP_1_NOM ?? '') }}" required autocomplete="off">
            </div>

            <div>
                <label for="RESP_1_CAR" class="form-label">Cargo <span style="color: red;">*</span></label>
                <input type="text" id="RESP_1_CAR" name="RESP_1_CAR" class="form-input-custom" style="background: white;" placeholder="Ej: Gerente" value="{{ old('RESP_1_CAR', $frente->RESP_1_CAR ?? '') }}" required autocomplete="off">
            </div>

            <!-- Row 4 -->
            <div>
                <label for="RESP_2_NOM" class="form-label">Segundo Responsable</label>
                <input type="text" id="RESP_2_NOM" name="RESP_2_NOM" class="form-input-custom" style="background: white;" placeholder="Opcional" value="{{ old('RESP_2_NOM', $frente->RESP_2_NOM ?? '') }}" autocomplete="off">
            </div>

            <div>
                <label for="RESP_2_CAR" class="form-label">Cargo Segundo Responsable</label>
                <input type="text" id="RESP_2_CAR" name="RESP_2_CAR" class="form-input-custom" style="background: white;" placeholder="Opcional" value="{{ old('RESP_2_CAR', $frente->RESP_2_CAR ?? '') }}" autocomplete="off">
            </div>
        </div>

        <div style="margin-top: 40px; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; align-items: center;">
            <a href="{{ route('menu') }}" class="btn-primary-maquinaria btn-secondary">
                Cancelar
            </a>
            


            <button type="submit" class="btn-primary-maquinaria" style="padding: 12px 30px; font-size: 14px; height: auto;">
                <i class="material-icons" style="font-size: 20px;" id="submitBtnIcon">{{ isset($frente) ? 'save' : 'add_circle' }}</i>
                <span id="submitBtnText">{{ isset($frente) ? 'Guardar Cambios' : 'Registrar' }}</span>
            </button>
        </div>
    </form>
    
    @if(isset($frente) && $frente->exists)

    @endif
</div>
@endsection

<!-- Custom Delete Modal -->

