<!-- Modelo, Año y Foto -->
<style>
    .grid-model-layout {
        display: grid;
        grid-template-columns: 2fr 0.8fr 1.2fr;
        gap: 15px;
        margin-bottom: 20px;
    }
    .grid-specs-layout {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }
    @media (max-width: 900px) {
        .grid-model-layout {
            grid-template-columns: 1fr 1fr;
        }
        .grid-model-layout > div:first-child {
            grid-column: span 2;
        }
        .grid-specs-layout {
            grid-template-columns: 1fr;
        }
    }
</style>
<div class="grid-model-layout">
    <!-- Modelo -->
    <div>
        <label for="MODELO" style="display: block; font-weight: 700; margin-bottom: 4px; color: var(--maquinaria-dark-blue); font-size: 13px;">Modelo</label>
        <input type="text" id="MODELO" name="MODELO" list="modelos_existentes" class="form-input-custom @error('MODELO') is-invalid @enderror" value="{{ old('MODELO', $catalogo->MODELO ?? '') }}" placeholder="Ej: Caterpillar D6T" required oninput="this.value = this.value.toUpperCase()" autocomplete="off">
        <datalist id="modelos_existentes">
            @if(isset($distinctModelos))
                @foreach($distinctModelos as $mod)
                    <option value="{{ $mod }}">
                @endforeach
            @endif
        </datalist>
        @error('MODELO') <span class="error-message-inline">{{ $message }}</span> @enderror
    </div>

    <!-- Año de Ficha -->
    <div>
        <label for="ANIO_ESPEC" style="display: block; font-weight: 700; margin-bottom: 4px; color: var(--maquinaria-dark-blue); font-size: 13px;">Año</label>
        <input type="number" id="ANIO_ESPEC" name="ANIO_ESPEC" class="form-input-custom no-spinner @error('ANIO_ESPEC') is-invalid @enderror" value="{{ old('ANIO_ESPEC', $catalogo->ANIO_ESPEC ?? '') }}" placeholder="Ej: {{ date('Y') }}" required style="-moz-appearance: textfield;" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        <style>
            /* Chrome, Safari, Edge, Opera */
            input::-webkit-outer-spin-button,
            input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
            }
        </style>
        @error('ANIO_ESPEC') <span class="error-message-inline">{{ $message }}</span> @enderror
    </div>

    <!-- Foto del Modelo -->
    <div>
        <div style="display: block; font-weight: 700; margin-bottom: 4px; color: var(--maquinaria-dark-blue); font-size: 13px;">Foto del Modelo</div>
        <div style="display: flex; gap: 8px; align-items: center; height: 38px;">
            <label for="foto_referencial" id="preview_referencial" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: white; border-radius: 8px; border: 1px solid #cbd5e0; flex-shrink: 0; cursor: pointer; transition: all 0.2s;" title="Foto del Modelo" onmouseover="this.style.borderColor='var(--maquinaria-blue)';" onmouseout="this.style.borderColor='#cbd5e0';">
                @if(isset($catalogo) && $catalogo->FOTO_REFERENCIAL)
                    <img src="{{ route('drive.file', ['path' => str_replace('/storage/google/', '', $catalogo->FOTO_REFERENCIAL)]) }}" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 4px;">
                @else
                    <i class="material-icons" style="font-size: 16px; color: #cbd5e0;">photo_camera</i>
                @endif
            </label>
            <input type="file" id="foto_referencial" name="foto_referencial" accept="image/*" style="display: none;">
            <div style="font-size: 10px; color: #718096; line-height: 1.1;">Click para {{ isset($catalogo) ? 'cambiar' : 'cargar' }}</div>
        </div>
    </div>
</div>

<h4 style="color: var(--maquinaria-blue); font-size: 13px; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; margin: 15px 0 10px 0;">ESPECIFICACIONES TÉCNICAS</h4>

<div class="grid-specs-layout">
    <!-- Motor -->
    <div>
        <label for="MOTOR" style="display: block; font-weight: 700; margin-bottom: 4px; color: var(--maquinaria-dark-blue); font-size: 13px;">Motor</label>
        <input type="text" id="MOTOR" name="MOTOR" class="form-input-custom" value="{{ old('MOTOR', $catalogo->MOTOR ?? '') }}" placeholder="Ej: Cat C9.3" autocomplete="off" oninput="this.value = this.value.toUpperCase()">
        @error('MOTOR') <span class="error-message-inline">{{ $message }}</span> @enderror
    </div>

    <!-- Capacidad -->
    <div>
        <label for="CAPACIDAD" style="display: block; font-weight: 700; margin-bottom: 4px; color: var(--maquinaria-dark-blue); font-size: 13px;">Capacidad / Volumen</label>
        <input type="text" id="CAPACIDAD" name="CAPACIDAD" class="form-input-custom" value="{{ old('CAPACIDAD', $catalogo->CAPACIDAD ?? '') }}" placeholder="Ej: 20 Ton / 15m³ (Incluir Unidad)" autocomplete="off" oninput="this.value = this.value.toUpperCase()">
        <p style="font-size: 11px; color: var(--maquinaria-red); margin-top: 4px; font-weight: 700;"><i class="material-icons" style="font-size: 11px; vertical-align: middle;">info</i> Indicar unidad (Kg, Ton, m³, Lts)</p>
    </div>

    <!-- Tipo de Combustible -->
    <div>
        <label for="COMBUSTIBLE" style="display: block; font-weight: 700; margin-bottom: 4px; color: var(--maquinaria-dark-blue); font-size: 13px;">Combustible</label>
        <input type="text" id="COMBUSTIBLE" name="COMBUSTIBLE" list="combustibles_list" class="form-input-custom" value="{{ old('COMBUSTIBLE', $catalogo->COMBUSTIBLE ?? '') }}" placeholder="Seleccione..." autocomplete="off" oninput="this.value = this.value.toUpperCase()">
        <datalist id="combustibles_list">
            <option value="GASOLINA">
            <option value="DIESEL">
            <option value="GASOIL">
            <option value="GAS">
            <option value="ELÉCTRICO">
        </datalist>
    </div>

    <!-- Consumo -->
    <div>
        <label for="CONSUMO_PROMEDIO" style="display: block; font-weight: 700; margin-bottom: 4px; color: var(--maquinaria-dark-blue); font-size: 13px;">Consumo (L/Día)</label>
        <input type="text" id="CONSUMO_PROMEDIO" name="CONSUMO_PROMEDIO" class="form-input-custom" value="{{ old('CONSUMO_PROMEDIO', $catalogo->CONSUMO_PROMEDIO ?? '') }}" placeholder="Ej: 120" autocomplete="off" oninput="this.value = this.value.toUpperCase()">
    </div>
</div>

<h4 style="color: var(--maquinaria-blue); font-size: 13px; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; margin: 15px 0 10px 0;">MANTENIMIENTO</h4>

<div class="grid-responsive-3" style="gap: 10px;">
    <div>
        <label for="ACEITE_MOTOR" style="display: block; font-weight: 700; margin-bottom: 4px; color: var(--maquinaria-dark-blue); font-size: 13px;">Aceite Motor:</label>
        <input type="text" id="ACEITE_MOTOR" name="ACEITE_MOTOR" class="form-input-custom" value="{{ old('ACEITE_MOTOR', $catalogo->ACEITE_MOTOR ?? '') }}" placeholder="Ej: 15W-40" autocomplete="off" oninput="this.value = this.value.toUpperCase()">
    </div>
    <div>
        <label for="ACEITE_CAJA" style="display: block; font-weight: 700; margin-bottom: 4px; color: var(--maquinaria-dark-blue); font-size: 13px;">Aceite Caja:</label>
        <input type="text" id="ACEITE_CAJA" name="ACEITE_CAJA" class="form-input-custom" value="{{ old('ACEITE_CAJA', $catalogo->ACEITE_CAJA ?? '') }}" placeholder="Ej: SAE 30" autocomplete="off" oninput="this.value = this.value.toUpperCase()">
    </div>
    <div>
        <label for="LIGA_FRENO" style="display: block; font-weight: 700; margin-bottom: 4px; color: var(--maquinaria-dark-blue); font-size: 13px;">Liga Freno:</label>
        <input type="text" id="LIGA_FRENO" name="LIGA_FRENO" class="form-input-custom" value="{{ old('LIGA_FRENO', $catalogo->LIGA_FRENO ?? '') }}" placeholder="Ej: DOT 4" autocomplete="off" oninput="this.value = this.value.toUpperCase()">
    </div>
    <div>
        <label for="REFRIGERANTE" style="display: block; font-weight: 700; margin-bottom: 4px; color: var(--maquinaria-dark-blue); font-size: 13px;">Refrigerante:</label>
        <input type="text" id="REFRIGERANTE" name="REFRIGERANTE" class="form-input-custom" value="{{ old('REFRIGERANTE', $catalogo->REFRIGERANTE ?? '') }}" placeholder="Ej: ELC (Rojo)" autocomplete="off" oninput="this.value = this.value.toUpperCase()">
    </div>
    <div>
        <label for="TIPO_BATERIA" style="display: block; font-weight: 700; margin-bottom: 4px; color: var(--maquinaria-dark-blue); font-size: 13px;">Batería:</label>
        <input type="text" id="TIPO_BATERIA" name="TIPO_BATERIA" class="form-input-custom" value="{{ old('TIPO_BATERIA', $catalogo->TIPO_BATERIA ?? '') }}" placeholder="Ej: 12V 1000CCA" oninput="this.value = this.value.toUpperCase()">
    </div>
</div>
