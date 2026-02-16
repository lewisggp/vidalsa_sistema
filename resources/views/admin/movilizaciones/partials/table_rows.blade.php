@forelse($movilizaciones as $mov)
    <tr>
        <!-- 1. Equipo -->
        <td style="padding: 2px 8px; text-align: left; border-right: 1px solid #e2e8f0; border-left: 1px solid #cbd5e0; border-top: 1px solid #cbd5e0; border-bottom: 1px solid #cbd5e0; border-radius: 12px 0 0 12px; width: 240px;">
            <div style="display: flex; align-items: center; justify-content: flex-start; gap: 10px;">
                @php $equipoFoto = $mov->equipo->especificaciones->FOTO_REFERENCIAL ?? null; @endphp
                @if($equipoFoto)
                    <div style="width: 50px; height: 35px; border-radius: 4px; overflow: hidden; flex-shrink: 0; background: #f8fafc;" onclick="enlargeImage('{{ asset($equipoFoto) }}')" >
                        <img src="{{ asset($equipoFoto) }}" alt="Foto" style="width: 100%; height: 100%; object-fit: contain;">
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

        <!-- 2. Trayecto (Origen → Destino) -->
        <td style="padding: 2px 8px; text-align: center; border-right: 1px solid #e2e8f0; border-top: 1px solid #cbd5e0; border-bottom: 1px solid #cbd5e0; width: 390px;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 12px;">
                <div style="display: flex; flex-direction: column; align-items: center; max-width: 160px;">
                    <span style="font-size: 12px; color: #2563eb; font-weight: 800; text-transform: uppercase;">Origen</span>
                    <span style="font-weight: 600; color: #4a5568; font-size: 14px; line-height: 1.2; display: block;">
                        {{ $mov->frenteOrigen->NOMBRE_FRENTE ?? 'Sin Origen' }}
                    </span>
                </div>
                <i class="material-icons" style="font-size: 18px; color: #cbd5e0; flex-shrink: 0;">east</i>
                <div style="display: flex; flex-direction: column; align-items: center; max-width: 160px;">
                    <span style="font-size: 12px; color: #10b981; font-weight: 800; text-transform: uppercase;">Destino</span>
                    <span style="font-weight: 700; color: var(--maquinaria-dark-blue); font-size: 14px; line-height: 1.2; display: block;">
                        {{ $mov->frenteDestino->NOMBRE_FRENTE ?? 'Sin Destino' }}
                    </span>
                </div>
            </div>
        </td>

        <!-- 3. Fechas (Salida — Llegada) -->
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

        <!-- 4. N° Operación (Control + Usuario) -->
        <td style="padding: 2px 8px; text-align: center; border-right: 1px solid #e2e8f0; border-top: 1px solid #cbd5e0; border-bottom: 1px solid #cbd5e0; width: 140px;">
            <div style="display: flex; flex-direction: column; align-items: center; line-height: 1.2;">
                <span style="font-weight: 800; color: #1e293b; font-size: 14px;">{{ $mov->formatted_codigo_control }}</span>
                <div style="display: flex; align-items: center; justify-content: center; gap: 4px; color: #64748b; font-size: 14px; font-weight: 600;">
                    <i class="material-icons" style="font-size: 16px;">person</i>
                    {{ $mov->usuario->NOMBRE_COMPLETO ?? $mov->USUARIO_REGISTRO }}
                </div>
            </div>
        </td>

        <!-- 5. Estado -->
        <td style="padding: 2px 8px; text-align: center; border-right: 1px solid #cbd5e0; border-top: 1px solid #cbd5e0; border-bottom: 1px solid #cbd5e0; border-radius: 0 12px 12px 0; width: 70px;">
            <div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                
                @if($mov->ESTADO_MVO == 'TRANSITO')
                    {{-- Estado TRANSITO: Mostrar texto + botones --}}
                    @php
                        $statusColor = '#ef4444'; // Rojo para TRANSITO
                    @endphp
                    <span style="color: {{ $statusColor }}; font-size: 14px; font-weight: 800; text-transform: uppercase;">
                        {{ $mov->ESTADO_MVO }}
                    </span>
                    
                    @php
                        // Obtener el frente actual del usuario autenticado
                        $usuario = auth()->user();
                        $usuarioFrenteId = $usuario->ID_FRENTE_ASIGNADO;
                        $esGlobal = ($usuario->NIVEL_ACCESO == 1);
                        
                        // Determinar la acción disponible
                        $esDestinatario = ($usuarioFrenteId == $mov->ID_FRENTE_DESTINO);
                        $esOrigen = ($usuarioFrenteId == $mov->ID_FRENTE_ORIGEN);
                        
                        // Usuarios GLOBAL pueden hacer ambas acciones
                        $puedeRecibir = $esDestinatario || $esGlobal;
                        $puedeRetornar = $esOrigen || $esGlobal;
                    @endphp

                    <div style="display: flex; flex-direction: column; gap: 4px; width: 100%;">
                        @if($puedeRecibir)
                            {{-- Usuario tiene permiso para RECIBIR --}}
                            <button type="button" class="btn-details-mini" 
                                onclick='iniciarRecepcion({{ $mov->ID_MOVILIZACION }}, "{{ $mov->frenteDestino->NOMBRE_FRENTE }}", "{{ $mov->frenteDestino->SUBDIVISIONES ?? "" }}")'
                                style="background: #10b981; color: white; border: none; padding: 6px 10px; min-height: 32px; width: 100%; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 5px; font-size: 12px; font-weight: 700; cursor: default; transition: all 0.2s;" 
                                title="Confirmar recepción en {{ $mov->frenteDestino->NOMBRE_FRENTE }}"
                                onmouseover="this.style.background='#059669'; this.style.transform='scale(1.05)'"
                                onmouseout="this.style.background='#10b981'; this.style.transform='scale(1)'">
                                <i class="material-icons" style="font-size: 16px;">check_circle</i>
                                <span>RECIBIR</span>
                            </button>
                            
                        @else
                            {{-- Usuario NO tiene permisos en este trayecto --}}
                            <div style="background: #f1f5f9; color: #94a3b8; border: 1px dashed #cbd5e0; padding: 6px 6px; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 3px; font-size: 10px; font-weight: 600; min-height: 32px;" 
                                title="No tienes permisos para recibir en {{ $mov->frenteDestino->NOMBRE_FRENTE }}">
                                <i class="material-icons" style="font-size: 14px;">block</i>
                                <span>Sin Acceso</span>
                            </div>
                        @endif
                    </div>
                    
                @elseif($mov->ESTADO_MVO == 'RECIBIDO')
                    {{-- Estado final: RECIBIDO en destino --}}
                    <div style="display: flex; flex-direction: column; width: 100%; gap: 2px;">
                        <div style="background: #d1fae5; color: #065f46; border: 1px solid #10b981; padding: 4px 6px; border-radius: 6px; display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 11px; font-weight: 700;">
                            <i class="material-icons" style="font-size: 14px;">done_all</i>
                            <span>COMPLETADO</span>
                        </div>
                        
                        {{-- Mostrar detalle de ubicación si existe --}}
                        @if($mov->DETALLE_UBICACION)
                            <div style="font-size: 10px; color: #047857; text-align: center; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 2px;">
                                <i class="material-icons" style="font-size: 10px;">place</i>
                                {{ $mov->DETALLE_UBICACION }}
                            </div>
                        @endif
                    </div>
                    
                @elseif($mov->ESTADO_MVO == 'RETORNADO')
                    {{-- Estado final: RETORNADO al origen --}}
                    <div style="background: #fef3c7; color: #92400e; border: 1px solid #f59e0b; padding: 6px 8px; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 11px; font-weight: 700; min-height: 36px;">
                        <i class="material-icons" style="font-size: 16px;">assignment_return</i>
                        <span>RETORNADO</span>
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
