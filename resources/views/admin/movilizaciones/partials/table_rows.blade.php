@forelse($movilizaciones as $mov)
    <tr>
        {{-- 1. Equipo --}}
        <td style="padding: 2px 8px; text-align: left; border-right: 1px solid #e2e8f0; border-left: 1px solid #cbd5e0; border-top: 1px solid #cbd5e0; border-bottom: 1px solid #cbd5e0; border-radius: 12px 0 0 12px; width: 240px;">
            <div style="display: flex; align-items: center; justify-content: flex-start; gap: 10px;">
                @php $equipoFoto = $mov->equipo->especificaciones->FOTO_REFERENCIAL ?? null; @endphp
                @if($equipoFoto)
                    <div style="width: 50px; height: 35px; border-radius: 4px; overflow: hidden; flex-shrink: 0; background: #f8fafc;" onclick="enlargeImage('{{ route('drive.file', ['path' => str_replace('/storage/google/', '', $equipoFoto)]) }}')" >
                        <img src="{{ route('drive.file', ['path' => str_replace('/storage/google/', '', $equipoFoto)]) }}" alt="Foto" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                @else
                    <div style="width: 50px; height: 35px; border-radius: 4px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #cbd5e0; flex-shrink: 0; border: 1px dashed #e2e8f0;">
                        <i class="material-icons" style="font-size: 20px;">image_not_supported</i>
                    </div>
                @endif
                <div style="display: flex; flex-direction: column; flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 13px; color: #718096; font-weight: 700; text-transform: uppercase;">{{ $mov->equipo->tipo->nombre ?? 'N/A' }}:</span>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 0;">
                        <div style="color: #4a5568; font-size: 14px;"><strong>S:</strong> {{ $mov->equipo->SERIAL_CHASIS ?? 'S/S' }}</div>
                        <div style="color: var(--maquinaria-blue); font-size: 14px; margin-top: 1px;"><strong>P:</strong> {{ $mov->equipo->documentacion->PLACA ?? 'S/P' }}</div>
                        <div style="color: #2d3748; font-size: 14px; margin-top: 1px; font-weight: 600;"><strong>ID:</strong> {{ $mov->equipo->CODIGO_PATIO ?? 'N/D' }}</div>
                    </div>
                </div>
            </div>
        </td>

        {{-- 2. Trayecto (Origen → Destino) --}}
        <td style="padding: 2px 8px; text-align: center; border-right: 1px solid #e2e8f0; border-top: 1px solid #cbd5e0; border-bottom: 1px solid #cbd5e0; width: 390px;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 12px;">
                <div style="display: flex; flex-direction: column; align-items: center; max-width: 160px;">
                    <span style="font-size: 12px; color: #64748b; font-weight: 800; text-transform: uppercase;">Origen</span>
                    <span style="font-weight: 600; color: #4a5568; font-size: 14px; line-height: 1.2; display: block;">
                        {{ $mov->frenteOrigen->NOMBRE_FRENTE ?? 'Sin Origen' }}
                    </span>
                </div>
                <i class="material-icons" style="font-size: 18px; color: #cbd5e0; flex-shrink: 0;">east</i>
                <div style="display: flex; flex-direction: column; align-items: center; max-width: 160px;">
                    <span style="font-size: 12px; color: #0067b1; font-weight: 800; text-transform: uppercase;">Destino</span>
                    <span style="font-weight: 700; color: var(--maquinaria-dark-blue); font-size: 14px; line-height: 1.2; display: block;">
                        {{ $mov->frenteDestino->NOMBRE_FRENTE ?? 'Sin Destino' }}
                    </span>
                </div>
            </div>
            {{-- Badge de tipo de movimiento --}}
            @if($mov->TIPO_MOVIMIENTO == 'RECEPCION_DIRECTA')
                <div style="margin-top: 4px; display: flex; justify-content: center;">
                    <span style="background: #e0e7ff; color: #3730a3; padding: 1px 8px; border-radius: 10px; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;">
                        <i class="material-icons" style="font-size: 11px;">input</i>
                        RECEPCIÓN DIRECTA
                    </span>
                </div>
            @endif
        </td>

        {{-- 3. Fechas (Salida — Llegada) --}}
        <td style="padding: 2px 8px; text-align: center; border-right: 1px solid #e2e8f0; border-top: 1px solid #cbd5e0; border-bottom: 1px solid #cbd5e0; width: 90px;">
            <div style="display: flex; flex-direction: column; align-items: center; line-height: 1.2;">
                <div style="display: flex; align-items: center; gap: 4px;">
                    <i class="material-icons" style="font-size: 14px; color: #ef4444;">logout</i>
                    <span style="font-size: 14px; color: #334155; font-weight: 700;">{{ $mov->FECHA_DESPACHO ? $mov->FECHA_DESPACHO->format('d/m/Y') : '--' }}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 4px;">
                    <i class="material-icons" style="font-size: 14px; color: #10b981;">login</i>
                    <span style="font-size: 14px; color: #334155; font-weight: 700;">{{ $mov->FECHA_RECEPCION ? $mov->FECHA_RECEPCION->format('d/m/Y') : '--' }}</span>
                </div>
            </div>
        </td>

        {{-- 4. N° Operación (Control + Usuario) --}}
        <td style="padding: 2px 8px; text-align: center; border-right: 1px solid #e2e8f0; border-top: 1px solid #cbd5e0; border-bottom: 1px solid #cbd5e0; width: 140px;">
            <div style="display: flex; flex-direction: column; align-items: center; line-height: 1.2;">
                <span style="font-weight: 800; color: #1e293b; font-size: 14px;">{{ $mov->formatted_codigo_control }}</span>
                <div style="display: flex; align-items: center; justify-content: center; gap: 4px; color: #64748b; font-size: 14px; font-weight: 600;">
                    <i class="material-icons" style="font-size: 16px;">person</i>
                    {{ $mov->usuario->NOMBRE_COMPLETO ?? $mov->USUARIO_REGISTRO }}
                </div>
            </div>
        </td>

        {{-- 5. Estado --}}
        <td style="padding: 2px 8px; text-align: center; border-right: 1px solid #cbd5e0; border-top: 1px solid #cbd5e0; border-bottom: 1px solid #cbd5e0; border-radius: 0 12px 12px 0; width: 70px;">
            <div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                
                @if($mov->ESTADO_MVO == 'TRANSITO')
                    {{-- Estado TRANSITO: Mostrar texto + botón RECIBIR --}}
                    <span style="color: #ef4444; font-size: 14px; font-weight: 800; text-transform: uppercase;">
                        {{ $mov->ESTADO_MVO }}
                    </span>
                    
                    @php
                        $usuario = auth()->user();
                        $usuarioFrenteId = $usuario->ID_FRENTE_ASIGNADO;
                        $esGlobal = ($usuario->NIVEL_ACCESO == 1);
                        $esDestinatario = ($usuarioFrenteId == $mov->ID_FRENTE_DESTINO);
                        $puedeRecibir = $esDestinatario || $esGlobal;
                    @endphp

                    <div style="display: flex; flex-direction: column; gap: 4px; width: 100%;">
                        @if($puedeRecibir)
                            <button type="button" class="btn-details-mini" 
                                onclick='iniciarRecepcion({{ $mov->ID_MOVILIZACION }}, "{{ $mov->frenteDestino->NOMBRE_FRENTE }}", "{{ $mov->frenteDestino->SUBDIVISIONES ?? "" }}", {{ $mov->frenteDestino->ID_FRENTE }})'
                                style="background: #0067b1; color: white; border: none; padding: 6px 10px; min-height: 32px; width: 100%; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 5px; font-size: 12px; font-weight: 700; transition: all 0.2;" 
                                title="Confirmar recepción en {{ $mov->frenteDestino->NOMBRE_FRENTE }}"
                                onmouseover="this.style.background='#005a9e'; this.style.transform='scale(1.05)'"
                                onmouseout="this.style.background='#0067b1'; this.style.transform='scale(1)'">
                                <i class="material-icons" style="font-size: 16px;">check_circle</i>
                                <span>RECIBIR</span>
                            </button>
                            
                        @else
                            <div style="background: #f1f5f9; color: #94a3b8; border: 1px dashed #cbd5e0; padding: 6px 6px; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 3px; font-size: 10px; font-weight: 600; min-height: 32px;" 
                                title="No tienes permisos para recibir en {{ $mov->frenteDestino->NOMBRE_FRENTE }}">
                                <i class="material-icons" style="font-size: 14px;">block</i>
                                <span>Sin Acceso</span>
                            </div>
                        @endif
                    </div>
                    
                @elseif($mov->ESTADO_MVO == 'RECIBIDO')
                    {{-- Estado final: RECIBIDO --}}
                    <div style="background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; padding: 4px 6px; border-radius: 6px; display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 11px; font-weight: 700;">
                        <i class="material-icons" style="font-size: 14px;">done_all</i>
                        <span>COMPLETADO</span>
                    </div>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" style="text-align: center; padding: 60px; color: #94a3b8; border: 1px dashed #cbd5e0; border-radius: 12px;">
            <i class="material-icons" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;">local_shipping</i>
            <p style="font-weight: 600;">No se encontraron movilizaciones registradas.</p>
        </td>
    </tr>
@endforelse
