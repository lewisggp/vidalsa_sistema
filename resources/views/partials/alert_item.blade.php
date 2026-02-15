@php
    $isExpired = ($alert->status ?? 'expired') === 'expired';
    $iconColor = $isExpired ? '#ef4444' : '#f59e0b';
    $icon = $isExpired ? 'error' : 'schedule';
    
    $gestionadoPor = $alert->gestionado_por ?? null;
    $diasGestion = null;
    if (isset($alert->fecha_gestion) && $alert->fecha_gestion) {
        $diasGestion = (int) \Carbon\Carbon::parse($alert->fecha_gestion)->diffInDays(now());
    }
@endphp
<div class="alert-card" 
     data-equipo-id="{{ $alert->equipo->ID_EQUIPO }}" 
     data-doc-type="{{ $alert->type_key }}" 
     data-placa="{{ optional($alert->equipo->documentacion)->PLACA ?? '' }}"
     data-chasis="{{ $alert->equipo->SERIAL_CHASIS ?? '' }}"
     data-motor-serial="{{ $alert->equipo->SERIAL_DE_MOTOR ?? '' }}"
     data-marca="{{ $alert->equipo->MARCA ?? '' }}"
     data-modelo="{{ $alert->equipo->MODELO ?? '' }}"
     data-tipo="{{ $alert->equipo->tipo->nombre ?? 'Equipo' }}"
     style="padding: 12px; border-bottom: 1px solid #e5e7eb; background: white; transition: background 0.2s; cursor: default !important;">
    <div style="display: flex; align-items: flex-start; gap: 8px;">
        {{-- Icon --}}
        <div style="flex-shrink: 0;">
            <i class="material-icons" style="font-size: 24px; color: {{ $iconColor }};">{{ $icon }}</i>
        </div>
        
        {{-- Content --}}
        <div style="flex: 1; min-width: 0;">
            {{-- Equipment Name --}}
            <div style="font-size: 14px; font-weight: 600; color: #1f2937; margin-bottom: 4px;">
                {{ $alert->equipo->tipo->nombre ?? 'Equipo' }} {{ $alert->equipo->MARCA }} {{ $alert->equipo->MODELO }}
            </div>
            
            {{-- Status Line --}}
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 6px;">
                <span style="font-size: 13px; color: {{ $iconColor }}; font-weight: 500;">
                    {{ $alert->label }}: {{ ucfirst(\Carbon\Carbon::parse($alert->fecha)->locale('es')->diffForHumans(null, true)) }}
                </span>

                {{-- Eye Button --}}
                <button type="button" 
                    data-equipo-id="{{ $alert->equipo->ID_EQUIPO }}"
                    data-codigo="{{ $alert->equipo->CODIGO_PATIO }}"
                    data-marca="{{ $alert->equipo->MARCA }}"
                    data-modelo="{{ $alert->equipo->MODELO }}"
                    data-anio="{{ $alert->equipo->ANIO }}"
                    data-tipo="{{ $alert->equipo->tipo->nombre ?? 'N/A' }}"
                    data-categoria="{{ $alert->equipo->CATEGORIA_FLOTA }}"
                    data-ubicacion="{{ $alert->equipo->frenteActual->NOMBRE_FRENTE ?? 'Sin Asignar' }}"
                    data-motor-serial="{{ $alert->equipo->SERIAL_DE_MOTOR }}"
                    data-chasis="{{ $alert->equipo->SERIAL_CHASIS }}"
                    data-combustible="{{ $alert->equipo->especificaciones->COMBUSTIBLE ?? 'N/A' }}"
                    data-consumo="{{ $alert->equipo->especificaciones->CONSUMO_PROMEDIO ?? 'N/A' }}"
                    data-placa="{{ $alert->equipo->documentacion->PLACA ?? 'N/A' }}"
                    data-titular="{{ $alert->equipo->documentacion->NOMBRE_DEL_TITULAR ?? 'N/A' }}"
                    data-nro-doc="{{ $alert->equipo->documentacion->NRO_DE_DOCUMENTO ?? 'N/A' }}"
                    data-venc-seguro="{{ $alert->equipo->documentacion->FECHA_VENC_POLIZA ?? 'N/A' }}"
                    data-seguro="{{ $alert->equipo->documentacion->seguro->NOMBRE_ASEGURADORA ?? 'N/A' }}"
                    data-link-propiedad="{{ $alert->equipo->documentacion->LINK_DOC_PROPIEDAD ?? '' }}"
                    data-link-seguro="{{ $alert->equipo->documentacion->LINK_POLIZA_SEGURO ?? '' }}"
                    data-link-rotc="{{ $alert->equipo->documentacion->LINK_ROTC ?? '' }}"
                    data-fecha-rotc="{{ $alert->equipo->documentacion->FECHA_ROTC ?? '' }}"
                    data-link-racda="{{ $alert->equipo->documentacion->LINK_RACDA ?? '' }}"
                    data-fecha-racda="{{ $alert->equipo->documentacion->FECHA_RACDA ?? '' }}"
                    data-link-adicional="{{ $alert->equipo->documentacion->LINK_DOC_ADICIONAL ?? '' }}"
                    data-link-gps="{{ $alert->equipo->LINK_GPS ?? '' }}"
                    onclick="showDetailsImproved(this, event)" 
                    style="flex-shrink: 0; background: transparent; border: none; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: default; transition: all 0.2s; color: #9ca3af;"
                    onmouseover="this.style.background='rgba(0,0,0,0.05)'; this.style.color='#4b5563'"
                    onmouseout="this.style.background='transparent'; this.style.color='#9ca3af'"
                    title="Ver detalles">
                    <i class="material-icons" style="font-size: 20px;">visibility</i>
                </button>
            </div>
            
            {{-- Management Badge or Action --}}
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                @if($gestionadoPor)
                    <span style="display: inline-flex; align-items: center; gap: 4px; background: #e0f2fe; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; color: #0369a1; border: 1px solid #bae6fd;">
                        <i class="material-icons" style="font-size: 14px;">engineering</i>
                        En gestión: {{ $gestionadoPor }} @if($diasGestion !== null)({{ $diasGestion }} {{ $diasGestion == 1 ? 'día' : 'días' }})@endif
                    </span>
                @else
                    <button 
                        onclick="iniciarGestion('{{ $alert->equipo->ID_EQUIPO }}', '{{ $alert->type_key }}')"
                        style="background: transparent; border: 1px solid #d1d5db; padding: 2px 8px; border-radius: 6px; color: #4b5563; font-size: 11px; font-weight: 600; cursor: default; transition: all 0.2s; display: inline-flex; align-items: center; gap: 4px;"
                        onmouseover="this.style.borderColor='#3b82f6'; this.style.color='#2563eb'; this.style.background='#eff6ff'"
                        onmouseout="this.style.borderColor='#d1d5db'; this.style.color='#4b5563'; this.style.background='transparent'">
                        <span>Gestionar</span>
                        <i class="material-icons" style="font-size: 12px;">arrow_forward</i>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
