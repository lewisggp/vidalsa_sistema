<!-- General Info -->
<h3 style="color: var(--maquinaria-blue); font-size: 16px; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 20px;">Información General</h3>

<div class="grid-responsive-5">
    <!-- Código de Patio -->
    <div>
        <label for="codigo_patio" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Código de Patio</label>
        <input type="text" id="codigo_patio" name="CODIGO_PATIO" class="form-input-custom @error('CODIGO_PATIO') is-invalid @enderror" value="{{ old('CODIGO_PATIO', $equipo->CODIGO_PATIO ?? '') }}" placeholder="Ej: V-01" autocomplete="off">
        @error('CODIGO_PATIO') <span class="error-message-inline">{{ $message }}</span> @enderror
    </div>

    <!-- Tipo de Equipo -->
    <div>
        <label for="tipo_equipo" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Tipo de Equipo</label>
        <input type="text" id="tipo_equipo" name="TIPO_EQUIPO" list="tipos_list" class="form-input-custom @error('TIPO_EQUIPO') is-invalid @enderror" value="{{ old('TIPO_EQUIPO', $equipo->TIPO_EQUIPO ?? '') }}" placeholder="Seleccione o escriba..." required maxlength="35" autocomplete="off">
        @error('TIPO_EQUIPO') <span class="error-message-inline">{{ $message }}</span> @enderror
        <datalist id="tipos_list">
            @foreach($tipos_equipo as $tipo)
                <option value="{{ $tipo }}">
            @endforeach
        </datalist>
    </div>

    <!-- Marca -->
    <div>
        <label for="marca" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Marca</label>
        <input type="text" id="marca" name="MARCA" list="marcas_list" class="form-input-custom @error('MARCA') is-invalid @enderror" value="{{ old('MARCA', $equipo->MARCA ?? '') }}" placeholder="Seleccione o escriba..." required autocomplete="off">
        @error('MARCA') <span class="error-message-inline">{{ $message }}</span> @enderror
        <datalist id="marcas_list"></datalist>
    </div>

    <!-- Modelo -->
    <div>
        <label for="modelo" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Modelo</label>
        <input type="text" id="modelo" name="MODELO" list="modelos_list" class="form-input-custom @error('MODELO') is-invalid @enderror" value="{{ old('MODELO', $equipo->MODELO ?? '') }}" placeholder="Seleccione o escriba..." required autocomplete="off">
        @error('MODELO') <span class="error-message-inline">{{ $message }}</span> @enderror
        <datalist id="modelos_list"></datalist>
    </div>

    <!-- Año -->
    <div>
        <label for="anio" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Año</label>
        <input type="text" id="anio" name="ANIO" class="form-input-custom @error('ANIO') is-invalid @enderror" value="{{ old('ANIO', $equipo->ANIO ?? '') }}" placeholder="Ej: 2025" required maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '');" autocomplete="off">
        @error('ANIO') <span class="error-message-inline">{{ $message }}</span> @enderror
    </div>

    <!-- Serial Chasis -->
    <div>
        <label for="serial_chasis" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Serial de Chasis</label>
        <input type="text" id="serial_chasis" name="SERIAL_CHASIS" class="form-input-custom @error('SERIAL_CHASIS') is-invalid @enderror" value="{{ old('SERIAL_CHASIS', $equipo->SERIAL_CHASIS ?? '') }}" placeholder="Serial único" required autocomplete="off">
        @error('SERIAL_CHASIS') <span class="error-message-inline">{{ $message }}</span> @enderror
    </div>

    <!-- Serial Motor -->
    <div>
        <label for="serial_motor" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Serial de Motor</label>
        <input type="text" id="serial_motor" name="SERIAL_DE_MOTOR" class="form-input-custom @error('SERIAL_DE_MOTOR') is-invalid @enderror" value="{{ old('SERIAL_DE_MOTOR', $equipo->SERIAL_DE_MOTOR ?? '') }}" placeholder="Opcional" autocomplete="off">
        @error('SERIAL_DE_MOTOR') <span class="error-message-inline">{{ $message }}</span> @enderror
    </div>

    <!-- Categoría de Flota -->
    <div>
        <span id="lbl_categoria_title" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Categoría de Flota</span>
        <div class="custom-dropdown @error('CATEGORIA_FLOTA') is-invalid @enderror" id="categoriaSelect">
            <input type="hidden" name="CATEGORIA_FLOTA" id="input_categoria" value="{{ old('CATEGORIA_FLOTA', $equipo->CATEGORIA_FLOTA ?? '') }}" aria-label="Categoría de Flota">
            <div class="dropdown-trigger" id="trigger_categoria" onclick="toggleDropdown('categoriaSelect')" tabindex="0" role="button" aria-haspopup="listbox" aria-labelledby="lbl_categoria_title label_categoria">
                <span id="label_categoria">{{ old('CATEGORIA_FLOTA', $equipo->CATEGORIA_FLOTA ?? '') ?: 'SELECCIONE' }}</span>
                <i class="material-icons">expand_more</i>
            </div>
            <div class="dropdown-content">
                @foreach($categorias as $cat)
                    <div class="dropdown-item" onclick="selectOption('categoriaSelect', '{{ $cat }}', '{{ $cat }}', 'categoria')">{{ $cat }}</div>
                @endforeach
            </div>
        </div>
        @error('CATEGORIA_FLOTA') <span class="error-message-inline">{{ $message }}</span> @enderror
    </div>

    <!-- Frente de Trabajo -->
    <div>
        <span id="lbl_frente_title" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Frente de Trabajo</span>
        <div class="custom-dropdown @error('ID_FRENTE_ACTUAL') is-invalid @enderror" id="frenteSelect">
            <input type="hidden" name="ID_FRENTE_ACTUAL" id="input_frente" value="{{ old('ID_FRENTE_ACTUAL', $equipo->ID_FRENTE_ACTUAL ?? '') }}" aria-label="Frente de Trabajo">
            <div class="dropdown-trigger" id="trigger_frente" onclick="toggleDropdown('frenteSelect')" tabindex="0" role="button" aria-haspopup="listbox" aria-labelledby="lbl_frente_title label_frente">
                <span id="label_frente">{{ $frentes[old('ID_FRENTE_ACTUAL', $equipo->ID_FRENTE_ACTUAL ?? '')] ?? 'SELECCIONE' }}</span>
                <i class="material-icons">expand_more</i>
            </div>
            <div class="dropdown-content">
                <div class="dropdown-item" onclick="selectOption('frenteSelect', '', 'Sin Asignar', 'frente')">Sin Asignar</div>
                @foreach($frentes as $id => $nombre)
                    <div class="dropdown-item" onclick="selectOption('frenteSelect', '{{ $id }}', '{{ $nombre }}', 'frente')">{{ $nombre }}</div>
                @endforeach
            </div>
        </div>
        @error('ID_FRENTE_ACTUAL') <span class="error-message-inline">{{ $message }}</span> @enderror
    </div>

    <!-- Estatus -->
    <div>
        <span id="lbl_estado_title" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Estatus</span>
        <div class="custom-dropdown" id="estadoSelect">
            <input type="hidden" name="ESTADO_OPERATIVO" id="input_estado" value="{{ old('ESTADO_OPERATIVO', $equipo->ESTADO_OPERATIVO ?? 'OPERATIVO') }}" aria-label="Estatus Operativo">
            <div class="dropdown-trigger" id="trigger_estado" onclick="toggleDropdown('estadoSelect')" tabindex="0" role="button" aria-haspopup="listbox" aria-labelledby="lbl_estado_title label_estado">
                <span id="label_estado">{{ old('ESTADO_OPERATIVO', $equipo->ESTADO_OPERATIVO ?? 'OPERATIVO') == 'EN MANTENIMIENTO' ? 'MANTENIMIENTO' : old('ESTADO_OPERATIVO', $equipo->ESTADO_OPERATIVO ?? 'OPERATIVO') }}</span>
                <i class="material-icons">expand_more</i>
            </div>
            <div class="dropdown-content">
                @foreach(['OPERATIVO', 'INOPERATIVO', 'EN MANTENIMIENTO' => 'MANTENIMIENTO', 'DESINCORPORADO'] as $key => $val)
                    @php 
                        $val_display = is_numeric($key) ? $val : $val;
                        $val_value = is_numeric($key) ? $val : $key;
                    @endphp
                    <div class="dropdown-item" onclick="selectOption('estadoSelect', '{{ $val_value }}', '{{ $val_display }}', 'estado')">{{ $val_display }}</div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Documentation -->
<h3 style="color: var(--maquinaria-blue); font-size: 16px; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 20px; margin-top: 30px;">Documentación Legal</h3>
<div class="grid-responsive-5">
    @php
        $hasProp = isset($equipo->documentacion) && $equipo->documentacion->LINK_DOC_PROPIEDAD;
        $hasPoliza = isset($equipo->documentacion) && $equipo->documentacion->LINK_POLIZA_SEGURO;
        $hasRotc = isset($equipo->documentacion) && $equipo->documentacion->LINK_ROTC;
        $hasRacda = isset($equipo->documentacion) && $equipo->documentacion->LINK_RACDA;
    @endphp
    <!-- Placa -->
    <div>
        <label for="placa" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Placa</label>
        <input type="text" id="placa" name="documentacion[PLACA]" class="form-input-custom @error('documentacion.PLACA') is-invalid @enderror" value="{{ old('documentacion.PLACA', $equipo->documentacion->PLACA ?? '') }}" placeholder="Ej: A00BC12">
        @error('documentacion.PLACA') <span class="error-message-inline">{{ $message }}</span> @enderror
    </div>

    <!-- Titular -->
    <div>
        <label for="titular" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Titular del Registro</label>
        <input type="text" id="titular" name="documentacion[NOMBRE_DEL_TITULAR]" class="form-input-custom" value="{{ old('documentacion.NOMBRE_DEL_TITULAR', $equipo->documentacion->NOMBRE_DEL_TITULAR ?? '') }}" placeholder="Nombre propietario" autocomplete="off">
    </div>

    <div style="position: relative;">
        <label for="nro_doc_propiedad" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Nro Título</label>
        <div style="display: flex; align-items: center; gap: 8px;">
            <input type="text" id="nro_doc_propiedad" name="documentacion[NRO_DE_DOCUMENTO]" class="form-input-custom doc-meta" data-file-target="doc_propiedad" data-has-existing="{{ $hasProp ? 'true' : 'false' }}" value="{{ old('documentacion.NRO_DE_DOCUMENTO', $equipo->documentacion->NRO_DE_DOCUMENTO ?? '') }}" style="flex: 1;" autocomplete="off">

            <!-- Button Wrapper -->
            <div id="wrapper_propiedad" class="{{ $hasProp ? 'pdf-btn-container' : 'upload-placeholder-mini' }}" style="{{ $hasProp ? 'width: auto; height: auto;' : 'border-radius: 50%;' }}">
                 @if($hasProp)
                    <a href="{{ $equipo->documentacion->LINK_DOC_PROPIEDAD }}" target="_blank" class="btn-preview-pdf" style="text-decoration: none;">
                        <i class="material-icons">visibility</i> Ver PDF
                    </a>
                    <label for="doc_propiedad" title="Reemplazar PDF" style="cursor: pointer; margin-left: 5px; color: var(--maquinaria-blue); display: flex; align-items: center;">
                        <i class="material-icons" style="font-size: 20px;">edit</i>
                    </label>
                 @else
                    <label for="doc_propiedad" title="Cargar PDF de Propiedad" style="border-radius: 50%; border-style: solid; border-width: 2px;">
                        <i class="material-icons" style="font-size: 18px;">add</i>
                    </label>
                 @endif
            </div>
            <input type="file" id="doc_propiedad" name="doc_propiedad" class="doc-file" data-meta-target="nro_doc_propiedad" accept=".pdf" style="display: none;">

        </div>
        <small id="error_meta_nro_doc_propiedad" style="display:none; color: #e53e3e; font-size: 11px; margin-top: 2px;">Escriba el Nro. de Documento</small>
        <small id="file_prop" style="color: #718096; font-size: 10px; display: {{ $hasProp ? 'block' : 'none' }};">{{ $hasProp ? '📄 Documento cargado' : '' }}</small>
        @error('doc_propiedad') <div style="color: var(--maquinaria-red); font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror

    </div>

    <!-- Póliza -->
    <div>
        <label for="seguro" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Póliza</label>
        <input type="text" id="seguro" name="documentacion[NOMBRE_SEGURO]" list="seguros_list" class="form-input-custom" value="{{ old('documentacion.NOMBRE_SEGURO', $equipo->documentacion->seguro->NOMBRE_ASEGURADORA ?? '') }}" placeholder="Seleccione o escriba aseguradora..." autocomplete="off">
        <datalist id="seguros_list">
            @foreach($seguros as $nombre)
                <option value="{{ $nombre }}">
            @endforeach
        </datalist>
    </div>

    <div style="position: relative;">
        <label for="venc_poliza" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Vencimiento Póliza</label>
        <div style="display: flex; align-items: center; gap: 8px;">
            <input type="date" id="venc_poliza" name="documentacion[FECHA_VENC_POLIZA]" class="form-input-custom doc-meta" data-file-target="poliza_seguro" data-has-existing="{{ $hasPoliza ? 'true' : 'false' }}" value="{{ old('documentacion.FECHA_VENC_POLIZA', $equipo->documentacion->FECHA_VENC_POLIZA ?? '') }}" style="flex: 1;">

            <div id="wrapper_poliza" class="{{ $hasPoliza ? 'pdf-btn-container' : 'upload-placeholder-mini' }}" style="{{ $hasPoliza ? 'width: auto; height: auto;' : 'border-radius: 50%;' }}">
                 @if($hasPoliza)
                    <a href="{{ $equipo->documentacion->LINK_POLIZA_SEGURO }}" target="_blank" class="btn-preview-pdf" style="text-decoration: none;">
                        <i class="material-icons">visibility</i> Ver PDF
                    </a>
                    <label for="poliza_seguro" title="Reemplazar PDF" style="cursor: pointer; margin-left: 5px; color: var(--maquinaria-blue); display: flex; align-items: center;">
                        <i class="material-icons" style="font-size: 20px;">edit</i>
                    </label>
                 @else
                    <label for="poliza_seguro" title="Cargar PDF de Póliza" style="border-radius: 50%; border-style: solid; border-width: 2px;">
                        <i class="material-icons" style="font-size: 18px;">add</i>
                    </label>
                 @endif
            </div>
            <input type="file" id="poliza_seguro" name="poliza_seguro" class="doc-file" data-meta-target="venc_poliza" accept=".pdf" style="display: none;">

        </div>
        <small id="error_meta_venc_poliza" style="display:none; color: #e53e3e; font-size: 11px; margin-top: 2px;">Seleccione la Fecha de Vencimiento de Póliza</small>
        <small id="file_poliza" style="color: #718096; font-size: 10px; display: {{ $hasPoliza ? 'block' : 'none' }};">{{ $hasPoliza ? '📄 Póliza cargada' : '' }}</small>
        @error('poliza_seguro') <div style="color: var(--maquinaria-red); font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror

        <input type="hidden" name="documentacion[ESTADO_POLIZA]" value="VIGENTE">
    </div>

    <div style="position: relative;">
        <label for="fecha_rotc" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Fecha ROTC</label>
        <div style="display: flex; align-items: center; gap: 8px;">
            <input type="date" id="fecha_rotc" name="documentacion[FECHA_ROTC]" class="form-input-custom doc-meta" data-file-target="doc_rotc" data-has-existing="{{ $hasRotc ? 'true' : 'false' }}" value="{{ old('documentacion.FECHA_ROTC', $equipo->documentacion->FECHA_ROTC ?? '') }}" style="flex: 1;">

            <div id="wrapper_rotc" class="{{ $hasRotc ? 'pdf-btn-container' : 'upload-placeholder-mini' }}" style="{{ $hasRotc ? 'width: auto; height: auto;' : 'border-radius: 50%;' }}">
                 @if($hasRotc)
                    <a href="{{ $equipo->documentacion->LINK_ROTC }}" target="_blank" class="btn-preview-pdf" style="text-decoration: none;">
                        <i class="material-icons">visibility</i> Ver PDF
                    </a>
                    <label for="doc_rotc" title="Reemplazar PDF" style="cursor: pointer; margin-left: 5px; color: var(--maquinaria-blue); display: flex; align-items: center;">
                        <i class="material-icons" style="font-size: 20px;">edit</i>
                    </label>
                 @else
                     <label for="doc_rotc" title="Cargar PDF ROTC" style="border-radius: 50%; border-style: solid; border-width: 2px;">
                        <i class="material-icons" style="font-size: 18px;">add</i>
                    </label>
                 @endif
            </div>
            <input type="file" id="doc_rotc" name="doc_rotc" class="doc-file" data-meta-target="fecha_rotc" accept=".pdf" style="display: none;">

        </div>
        <small id="error_meta_fecha_rotc" style="display:none; color: #e53e3e; font-size: 11px; margin-top: 2px;">Seleccione la Fecha ROTC</small>
        <small id="file_rotc" style="color: #718096; font-size: 10px; display: {{ $hasRotc ? 'block' : 'none' }};">{{ $hasRotc ? '📄 ROTC cargado' : '' }}</small>
        @error('doc_rotc') <div style="color: var(--maquinaria-red); font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror

    </div>

    <div style="position: relative;">
        <label for="fecha_racda" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Fecha RACDA</label>
        <div style="display: flex; align-items: center; gap: 8px;">
            <input type="date" id="fecha_racda" name="documentacion[FECHA_RACDA]" class="form-input-custom doc-meta" data-file-target="doc_racda" data-has-existing="{{ $hasRacda ? 'true' : 'false' }}" value="{{ old('documentacion.FECHA_RACDA', $equipo->documentacion->FECHA_RACDA ?? '') }}" style="flex: 1;">

           <div id="wrapper_racda" class="{{ $hasRacda ? 'pdf-btn-container' : 'upload-placeholder-mini' }}" style="{{ $hasRacda ? 'width: auto; height: auto;' : 'border-radius: 50%;' }}">
                 @if($hasRacda)
                    <a href="{{ $equipo->documentacion->LINK_RACDA }}" target="_blank" class="btn-preview-pdf" style="text-decoration: none;">
                        <i class="material-icons">visibility</i> Ver PDF
                    </a>
                    <label for="doc_racda" title="Reemplazar PDF" style="cursor: pointer; margin-left: 5px; color: var(--maquinaria-blue); display: flex; align-items: center;">
                        <i class="material-icons" style="font-size: 20px;">edit</i>
                    </label>
                 @else
                     <label for="doc_racda" title="Cargar PDF RACDA" style="border-radius: 50%; border-style: solid; border-width: 2px;">
                        <i class="material-icons" style="font-size: 18px;">add</i>
                    </label>
                 @endif
            </div>
            <input type="file" id="doc_racda" name="doc_racda" class="doc-file" data-meta-target="fecha_racda" accept=".pdf" style="display: none;">

        </div>
        <small id="error_meta_fecha_racda" style="display:none; color: #e53e3e; font-size: 11px; margin-top: 2px;">Seleccione la Fecha RACDA</small>
        <small id="file_racda" style="color: #718096; font-size: 10px; display: {{ $hasRacda ? 'block' : 'none' }};">{{ $hasRacda ? '📄 RACDA cargado' : '' }}</small>
    </div>

    <!-- Ficha Técnica -->
    <div>
        <label for="ficha_tecnica" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Ficha Técnica</label>
        <input type="text" id="ficha_tecnica" name="FICHA_TECNICA_MODELO" list="specs_list" class="form-input-custom" value="{{ old('FICHA_TECNICA_MODELO', $equipo->especificaciones->MODELO ?? '') }}" placeholder="Seleccione o escriba..." autocomplete="off">
        <datalist id="specs_list">
            @foreach($modelos_specs as $spec)
                <option value="{{ $spec->MODELO }}">
            @endforeach
        </datalist>
        <input type="hidden" name="ID_ESPEC" value="{{ old('ID_ESPEC', $equipo->ID_ESPEC ?? '') }}">
    </div>

    <!-- Link GPS -->
    <div>
        <label for="link_gps" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Link GPS</label>
        <div style="display: flex; align-items: center; gap: 8px;">
            <input type="url" id="link_gps" name="LINK_GPS" class="form-input-custom" value="{{ old('LINK_GPS', $equipo->LINK_GPS ?? '') }}" placeholder="https://..." style="flex: 1;">
            <span style="color: #10b981; display: flex; align-items: center;"><i class="material-icons" style="font-size: 20px;">gps_fixed</i></span>
        </div>
    </div>

    <!-- Foto -->
    <div>
        <label for="foto_equipo" style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--maquinaria-dark-blue);">Foto del Equipo</label>
        <div style="display: flex; gap: 10px; align-items: center; height: 38px;">
            <label for="foto_equipo" id="preview_equipo" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: white; border-radius: 8px; border: 1px solid #cbd5e0; flex-shrink: 0; cursor: pointer; transition: all 0.2s;" title="Foto del Equipo" onmouseover="this.style.borderColor='var(--maquinaria-blue)';" onmouseout="this.style.borderColor='#cbd5e0';">
                @if(isset($equipo) && $equipo->FOTO_EQUIPO)
                    <img src="{{ asset($equipo->FOTO_EQUIPO) }}" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 4px;">
                @else
                    <i class="material-icons" style="font-size: 16px; color: #cbd5e0;">photo_camera</i>
                @endif
            </label>
            <input type="file" id="foto_equipo" name="foto_equipo" accept="image/*" style="display: none;">
            <div style="font-size: 10px; color: #718096; line-height: 1.2;">Click para {{ isset($equipo) ? 'cambiar' : 'cargar' }}</div>
        </div>
    </div>
</div>

<!-- Logic moved to public/js/maquinaria/form_logic.js for CSP compliance -->
