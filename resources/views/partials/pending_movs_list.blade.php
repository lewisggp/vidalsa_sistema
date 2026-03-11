{{--
    Partial: Lista de Movilizaciones Pendientes (Por Confirmar Recepción)
    Variables requeridas: $recentActivity (Collection de Movilizacion con relaciones)
--}}
@php
    $authUser      = auth()->user();
    $authFrenteIds = $authUser ? $authUser->getFrentesIds() : [];
    $authEsGlobal  = ($authUser && $authUser->NIVEL_ACCESO == 1);
@endphp

@forelse($recentActivity as $activity)
    @php
        $esDestinatario = $authEsGlobal || in_array((string)$activity->ID_FRENTE_DESTINO, array_map('strval', $authFrenteIds));
        $placa          = $activity->equipo->documentacion->PLACA ?? null;
        $serial         = $activity->equipo->SERIAL_CHASIS ?? null;
        $primaryId      = ($placa && strtoupper($placa) !== 'N/A') ? $placa : $serial;
        $nombreFrente   = $activity->frenteDestino->NOMBRE_FRENTE ?? 'Sin Frente';
    @endphp

    <div class="activity-item"
         data-chasis="{{ $activity->equipo->SERIAL_CHASIS ?? '' }}"
         data-placa="{{ $activity->equipo->documentacion->PLACA ?? '' }}"
         data-etiqueta="{{ $activity->equipo->NUMERO_ETIQUETA ?? '' }}"
         style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; border-bottom: 1px solid #f1f5f9;">
        <div class="activity-icon">
            <i class="material-icons">local_shipping</i>
        </div>
        <div class="activity-info" style="flex: 1; min-width: 0;">
            <div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                <strong style="font-size: 13px; color: #1e293b;">{{ $activity->equipo->tipo->nombre ?? 'Equipo' }}</strong>
                @if(!empty($activity->equipo->NUMERO_ETIQUETA))
                    <span style="color: #0067b1; font-weight: 800; font-size: 12px;">#{{ $activity->equipo->NUMERO_ETIQUETA }}</span>
                @endif
            </div>
            <p class="activity-text" style="margin: 1px 0; font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">
                {{ $primaryId }}
            </p>
            {{-- FRENTE DE DESTINO --}}
            <div style="display: flex; align-items: center; gap: 3px; margin-top: 3px;">
                <span style="
                    display: inline-flex; align-items: center; gap: 3px;
                    background: {{ $esDestinatario ? '#e0f2fe' : '#f1f5f9' }};
                    color: {{ $esDestinatario ? '#0369a1' : '#64748b' }};
                    border: 1px solid {{ $esDestinatario ? '#bae6fd' : '#e2e8f0' }};
                    border-radius: 20px; padding: 1px 7px;
                    font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;">
                    <i class="material-icons" style="font-size: 10px;">place</i>
                    {{ $nombreFrente }}
                </span>
            </div>
            <div style="font-size: 10px; color: #94a3b8; display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                <i class="material-icons" style="font-size: 12px;">schedule</i>
                {{ $activity->created_at ? $activity->created_at->locale('es')->diffForHumans() : 'Fecha Desconocida' }}
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="activity-actions" style="display: flex; gap: 6px; flex-shrink: 0; align-items: center;">
            @if($esDestinatario)
                <button type="button"
                    onclick="iniciarRecepcionDesdeDashboard({{ $activity->ID_MOVILIZACION }}, '{{ addslashes($activity->frenteDestino->NOMBRE_FRENTE ?? '') }}', '{{ addslashes($activity->frenteDestino->SUBDIVISIONES ?? '') }}', {{ $activity->ID_FRENTE_DESTINO }})"
                    class="btn-recibir-dashboard"
                    title="Confirmar recepción en {{ $nombreFrente }}"
                    style="background: #1e293b; border: none; color: white; padding: 4px 8px; height: 32px; border-radius: 8px; font-weight: 800; display: flex; align-items: center; gap: 5px; cursor: default;">
                    <i class="material-icons" style="font-size: 16px;">check_circle</i>
                    <span style="font-size: 10px;">RECIBIR</span>
                </button>
            @else
                <div class="btn-sin-acceso-dashboard"
                    title="No tienes permisos para recibir este equipo"
                    style="height: 32px; padding: 5px 12px; display: flex; align-items: center; gap: 4px;">
                    <i class="material-icons" style="font-size: 18px;">block</i>
                    <span style="font-size: 11px; font-weight: 800;">Sin Acceso</span>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="empty-state">
        <i class="material-icons">inbox</i>
        <p>No hay movilizaciones por confirmar.</p>
    </div>
@endforelse
