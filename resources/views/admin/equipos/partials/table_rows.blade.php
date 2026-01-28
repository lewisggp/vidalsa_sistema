@forelse($equipos as $equipo)
    <tr>
        <!-- 1. Foto -->
        <td class="table-cell-custom table-cell-center">
            <!-- Frente Info -->
            <div style="font-size: 13px; color: #000000; margin-bottom: 5px; line-height: 1.1; font-weight: 600; text-align: center; width: 100%; word-wrap: break-word;" title="{{ $equipo->frenteActual->NOMBRE_FRENTE ?? 'Sin Asignar' }}">
                {{ $equipo->frenteActual->NOMBRE_FRENTE ?? 'Sin Asignar' }}
            </div>

            @if($equipo->especificaciones && $equipo->especificaciones->FOTO_REFERENCIAL)
                <div class="table-image-wrapper" style="cursor: default;" onclick="enlargeImage('{{ route('drive.file', ['path' => str_replace('/storage/google/', '', $equipo->especificaciones->FOTO_REFERENCIAL)]) }}')">
                    <img src="{{ route('drive.file', ['path' => str_replace('/storage/google/', '', $equipo->especificaciones->FOTO_REFERENCIAL)]) }}" 
                         alt="Foto Modelo" 
                         loading="lazy"
                         onload="this.style.opacity='1'">
                </div>
            @else
                <div class="table-image-wrapper placeholder">
                    <span class="material-icons">image_not_supported</span>
                </div>
            @endif
        </td>
        <!-- 2. Tipo -->
        <td class="table-cell-custom" style="font-weight: 600; max-width: 170px; font-size: 14px; color: #000;">
            {{ $equipo->tipo->nombre ?? 'N/A' }}
            @if($equipo->NUMERO_ETIQUETA)
                <span style="margin-left: 8px; font-weight: 700; color: #0067b1;">#{{ $equipo->NUMERO_ETIQUETA }}</span>
            @endif
        </td>
        <!-- 3. Marca / Modelo -->
        <td class="table-cell-custom" style="max-width: 110px; word-wrap: break-word; overflow-wrap: break-word;">
            <div style="font-weight: 700; font-size: 14px; color: #000;">{{ $equipo->MARCA }}</div>
            <div style="font-size: 14px; color: #718096;">{{ $equipo->MODELO }}</div>
        </td>
        <!-- 4. Serials / Placa / ID -->
        <td class="table-cell-custom" style="max-width: 160px; word-wrap: break-word; overflow-wrap: break-word; font-size: 14px;">
            <div style="color: #4a5568;"><strong>S:</strong> {{ $equipo->SERIAL_CHASIS }}</div>
            @if($equipo->documentacion && $equipo->documentacion->PLACA)
                <div style="color: var(--maquinaria-blue); margin-top: 1px;"><strong>P:</strong> {{ $equipo->documentacion->PLACA }}</div>
            @else
                <div style="color: #a0aec0; margin-top: 1px; font-style: italic;">Sin Placa</div>
            @endif
            <div style="color: #2d3748; margin-top: 1px; font-weight: 600;"><strong>ID:</strong> {{ $equipo->CODIGO_PATIO }}</div>
        </td>
        <!-- 6. Estatus -->
        <td class="table-cell-custom" style="padding: 12px 2px; width: 140px;">
            <div class="custom-dropdown" style="width: 100%; position: relative;" data-current-status="{{ $equipo->ESTADO_OPERATIVO }}">
                 @php
                    $statusConfig = [
                        'OPERATIVO' => ['color' => '#16a34a', 'bg' => '#f0fdf4', 'icon' => 'check_circle', 'label' => 'Operativo'],
                        'INOPERATIVO' => ['color' => '#dc2626', 'bg' => '#fef2f2', 'icon' => 'cancel', 'label' => 'Inoperativo'],
                        'EN MANTENIMIENTO' => ['color' => '#d97706', 'bg' => '#fffbeb', 'icon' => 'engineering', 'label' => 'Mantenimiento'],
                        'DESINCORPORADO' => ['color' => '#475569', 'bg' => '#f1f5f9', 'icon' => 'archive', 'label' => 'Desincorp.']
                    ];
                    $currentConfig = $statusConfig[$equipo->ESTADO_OPERATIVO] ?? $statusConfig['DESINCORPORADO'];
                @endphp

                <!-- Trigger -->
                <div onclick="event.stopPropagation(); toggleStatusDropdown(this)" 
                     class="status-trigger" 
                     style="cursor: pointer; padding: 6px 10px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; gap: 5px; font-size: 13px; font-weight: 600; background: white; border: 1px solid #e2e8f0; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    
                    <div style="display: flex; align-items: center; gap: 6px; color: {{ $currentConfig['color'] }};">
                        <i class="material-icons" style="font-size: 16px;">{{ $currentConfig['icon'] }}</i>
                        <span style="color: #334155;">{{ $currentConfig['label'] }}</span>
                    </div>
                    <i class="material-icons" style="font-size: 16px; color: #94a3b8;">expand_more</i>
                </div>

                <!-- Dropdown -->
                <div class="status-dropdown-menu" style="display: none; position: absolute; top: calc(100% + 5px); left: 0; min-width: 180px; background: white; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; z-index: 50; overflow: hidden;">
                    @foreach($statusConfig as $key => $config)
                        <div onclick="changeStatus('{{ $equipo->ID_EQUIPO }}', '{{ $key }}', '{{ route('equipos.changeStatus', $equipo->ID_EQUIPO) }}', this)" 
                             style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; cursor: pointer; transition: background 0.1s; border-bottom: 1px solid #f8fafc;" 
                             onmouseover="this.style.background='#f8fafc'" 
                             onmouseout="this.style.background='white'">
                            
                            <div style="background: {{ $config['bg'] }}; padding: 4px; border-radius: 4px; display: flex;">
                                <i class="material-icons" style="font-size: 16px; color: {{ $config['color'] }};">{{ $config['icon'] }}</i>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: #334155;">{{ $config['label'] }}</span>
                            @if($equipo->ESTADO_OPERATIVO == $key)
                                <i class="material-icons" style="font-size: 14px; color: {{ $config['color'] }}; margin-left: auto;">check</i>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </td>
        <td class="table-cell-center" style="padding: 12px 5px; width: 20px;">
            <div style="display: flex; gap: 8px; justify-content: center;">
                <button type="button" 
                    data-equipo-id="{{ $equipo->ID_EQUIPO }}"
                    data-codigo="{{ $equipo->CODIGO_PATIO }}"
                    data-marca="{{ $equipo->MARCA }}"
                    data-modelo="{{ $equipo->MODELO }}"
                    data-anio="{{ $equipo->ANIO }}"
                    data-tipo="{{ $equipo->tipo->nombre ?? 'N/A' }}"
                    data-categoria="{{ $equipo->CATEGORIA_FLOTA }}"
                    data-ubicacion="{{ $equipo->frenteActual->NOMBRE_FRENTE ?? 'Sin Asignar' }}"
                    data-motor-serial="{{ $equipo->SERIAL_DE_MOTOR }}"
                    data-chasis="{{ $equipo->SERIAL_CHASIS }}"
                    
                    {{-- Tech Specs --}}
                    data-motor-tech="{{ $equipo->especificaciones->MOTOR ?? 'N/A' }}"
                    data-capacidad="{{ $equipo->especificaciones->CAPACIDAD ?? 'N/A' }}"
                    data-combustible="{{ $equipo->especificaciones->COMBUSTIBLE ?? 'N/A' }}"
                    data-consumo="{{ $equipo->especificaciones->CONSUMO_PROMEDIO ?? 'N/A' }}"
                    data-aceite-m="{{ $equipo->especificaciones->ACEITE_MOTOR ?? 'N/A' }}"
                    data-aceite-c="{{ $equipo->especificaciones->ACEITE_CAJA ?? 'N/A' }}"
                    data-liga="{{ $equipo->especificaciones->LIGA_FRENO ?? 'N/A' }}"
                    data-refrigerante="{{ $equipo->especificaciones->REFRIGERANTE ?? 'N/A' }}"
                    data-bateria="{{ $equipo->especificaciones->TIPO_BATERIA ?? 'N/A' }}"

                    {{-- Documentation --}}
                    data-placa="{{ $equipo->documentacion->PLACA ?? 'N/A' }}"
                    data-titular="{{ $equipo->documentacion->NOMBRE_DEL_TITULAR ?? 'N/A' }}"
                    data-nro-doc="{{ $equipo->documentacion->NRO_DE_DOCUMENTO ?? 'N/A' }}"
                    data-venc-seguro="{{ $equipo->documentacion->FECHA_VENC_POLIZA ?? 'N/A' }}"
                    data-seguro="{{ $equipo->documentacion->seguro->NOMBRE_ASEGURADORA ?? 'N/A' }}"
                    data-link-propiedad="{{ $equipo->documentacion->LINK_DOC_PROPIEDAD ?? '' }}"
                    data-link-seguro="{{ $equipo->documentacion->LINK_POLIZA_SEGURO ?? '' }}"
                    data-link-rotc="{{ $equipo->documentacion->LINK_ROTC ?? '' }}"
                    data-fecha-rotc="{{ $equipo->documentacion->FECHA_ROTC ?? '' }}"
                    data-link-racda="{{ $equipo->documentacion->LINK_RACDA ?? '' }}"
                    data-fecha-racda="{{ $equipo->documentacion->FECHA_RACDA ?? '' }}"
                    data-link-gps="{{ $equipo->LINK_GPS ?? '' }}"

                    onclick="showDetailsImproved(this, event)" 
                    class="btn-details-mini" 
                    title="Ver Detalles">
                    <i class="material-icons">visibility</i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="table-empty-state">
            @if(request('search_query') || request('id_frente') || request('id_tipo'))
                <i class="material-icons" style="font-size: 48px; display: block; margin-bottom: 10px; color: #cbd5e0;">search_off</i>
                No se encontraron equipos con los filtros aplicados.
            @else
                <i class="material-icons" style="font-size: 48px; display: block; margin-bottom: 10px; color: #cbd5e0;">filter_alt</i>
                Seleccione un filtro para ver los equipos.
            @endif
        </td>
    </tr>
@endforelse
