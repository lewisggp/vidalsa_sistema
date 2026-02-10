@php
    $isExpired = ($alert->status ?? 'expired') === 'expired';
    $iconBg = $isExpired ? '#fee2e2' : '#fef3c7';
    $iconColor = $isExpired ? '#991b1b' : '#92400e';
    $textColor = $isExpired ? '#ef4444' : '#f59e0b';
    $icon = $isExpired ? 'error' : 'schedule';

    // Button Dynamic Styles based on Status
    $btnBg = $isExpired ? '#fef2f2' : '#fffbeb';
    $btnBorder = $isExpired ? '#fecaca' : '#fde68a';
    $btnHover = $isExpired ? '#fee2e2' : '#fef3c7';
    $btnIconColor = $isExpired ? '#dc2626' : '#d97706';
@endphp
<div class="activity-item" style="display: flex; align-items: center; gap: 12px; padding: 12px; border-bottom: 1px solid #f1f5f9;">
    <div class="activity-icon" style="background: {{ $iconBg }}; color: {{ $iconColor }};">
        <i class="material-icons">{{ $icon }}</i>
    </div>
    <div class="activity-info" style="flex: 1; min-width: 0;">
        <p class="activity-text">
            <strong>{{ $alert->equipo->tipo->nombre ?? 'Equipo' }} {{ $alert->equipo->MARCA }} {{ $alert->equipo->MODELO }}</strong>
            <br>
            <span style="font-size: 0.9em; font-weight: normal; color: #64748b;">
                {{ $alert->equipo->SERIAL_CHASIS ?? $alert->equipo->PLACA ?? '' }}
            </span>
        </p>
        <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px;">
            <span class="activity-time" style="color: {{ $textColor }}; font-weight: 600;">
                {{ $alert->label }}: {{ ucfirst(\Carbon\Carbon::parse($alert->fecha)->locale('es')->diffForHumans(null, true)) }}
            </span>
        </div>
    </div>
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
        
        {{-- Tech Specs --}}
        data-combustible="{{ $alert->equipo->especificaciones->COMBUSTIBLE ?? 'N/A' }}"
        data-consumo="{{ $alert->equipo->especificaciones->CONSUMO_PROMEDIO ?? 'N/A' }}"

        {{-- Documentation --}}
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
        style="background: {{ $btnBg }}; border: 1px solid {{ $btnBorder }}; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0;"
        onmouseover="this.style.background='{{ $btnHover }}'" 
        onmouseout="this.style.background='{{ $btnBg }}'"
        title="Ver detalles y gestionar documento">
        <i class="material-icons" style="font-size: 20px; color: {{ $btnIconColor }};">visibility</i>
    </button>
</div>
