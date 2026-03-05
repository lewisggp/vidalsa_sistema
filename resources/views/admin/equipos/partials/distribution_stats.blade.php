@if(request('id_tipo'))
    <!-- Distribución por FRENTE (contexto del tipo seleccionado) -->
    <h4 style="margin: 0 0 12px 0; font-size: 12px; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
        <i class="material-icons" style="font-size: 18px; color: #10b981;">map</i>
        Ubicación por Frente
    </h4>
    <ul style="list-style: none; padding: 0; margin: 0; max-height: 500px; overflow-y: auto; overflow-x: hidden; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px;" class="custom-scrollbar">
        @if($hasFilter ?? request('search_query') || request('id_frente') || request('id_tipo'))
            @php $totalFrentes = $frentesStats->sum('total'); @endphp
            @foreach($frentesStats as $stat)
                @php $percentage = $totalFrentes > 0 ? ($stat->total / $totalFrentes) * 100 : 0; @endphp
                <li onclick="selectOption('frenteFilterSelect', '{{ $stat->ID_FRENTE_ACTUAL }}', '{{ $stat->NOMBRE_FRENTE }}'); loadEquipos();" style="padding-bottom: 8px; border-bottom: 1px dashed #f1f5f9; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        {{-- Nombre con tooltip personalizado --}}
                        <div class="tooltip-wrapper" style="position: relative; max-width: 90px; overflow: hidden;">
                            <span style="color: #334155; font-size: 12px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">
                                {{ $stat->NOMBRE_FRENTE ?? 'Sin Asignar' }}
                            </span>
                            {{-- Burbuja tooltip estilo igual al de la tabla --}}
                            <div class="tooltip-bubble" style="
                                pointer-events: none;
                                opacity: 0;
                                visibility: hidden;
                                position: absolute;
                                bottom: 100%;
                                left: 50%;
                                transform: translateX(-50%) translateY(5px);
                                background-color: #1e293b;
                                color: #fff;
                                padding: 5px 9px;
                                border-radius: 6px;
                                font-size: 11px;
                                font-weight: 500;
                                white-space: nowrap;
                                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.15);
                                transition: all 0.2s ease-in-out;
                                z-index: 100;
                                margin-bottom: 4px;
                            ">
                                {{ $stat->NOMBRE_FRENTE ?? 'Sin Asignar' }}
                                <div style="position: absolute; top: 100%; left: 50%; margin-left: -4px; border-width: 4px; border-style: solid; border-color: #1e293b transparent transparent transparent;"></div>
                            </div>
                        </div>
                        <span style="font-weight: 700; color: #1e293b; font-size: 11px; background: #f1f5f9; padding: 1px 5px; border-radius: 4px; flex-shrink: 0;">
                            {{ $stat->total }}
                        </span>
                    </div>
                    <div style="width: 100%; height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                        <div style="width:{{ $percentage }}%; height: 100%; background: linear-gradient(90deg, #10b981 0%, #059669 100%); border-radius: 3px;"></div>
                    </div>
                </li>
            @endforeach
        @endif
    </ul>
@else
    <!-- Distribución por TIPO (Default) -->
    <h4 style="margin: 0 0 12px 0; font-size: 12px; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
        <i class="material-icons" style="font-size: 18px; color: #3b82f6;">pie_chart</i>
        Distribución
    </h4>

    <ul style="list-style: none; padding: 0; margin: 0; max-height: 500px; overflow-y: auto; overflow-x: hidden; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px;" class="custom-scrollbar">
        @if($hasFilter ?? request('search_query') || request('id_frente') || request('id_tipo'))
            @php $totalStats = $tiposStats->sum('total'); @endphp
            @foreach($tiposStats as $stat)
                @php $percentage = $totalStats > 0 ? ($stat->total / $totalStats) * 100 : 0; @endphp
                <li onclick="selectOption('tipoFilterSelect', '{{ $stat->id_tipo_equipo }}', '{{ $stat->nombre }}'); loadEquipos();" style="padding-bottom: 8px; border-bottom: 1px dashed #f1f5f9; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        {{-- Nombre con tooltip personalizado --}}
                        <div class="tooltip-wrapper" style="position: relative; max-width: 90px; overflow: hidden;">
                            <span style="color: #334155; font-size: 12px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">
                                {{ $stat->nombre ?? 'Desconocido' }}
                            </span>
                            {{-- Burbuja tooltip estilo igual al de la tabla --}}
                            <div class="tooltip-bubble" style="
                                pointer-events: none;
                                opacity: 0;
                                visibility: hidden;
                                position: absolute;
                                bottom: 100%;
                                left: 50%;
                                transform: translateX(-50%) translateY(5px);
                                background-color: #1e293b;
                                color: #fff;
                                padding: 5px 9px;
                                border-radius: 6px;
                                font-size: 11px;
                                font-weight: 500;
                                white-space: nowrap;
                                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.15);
                                transition: all 0.2s ease-in-out;
                                z-index: 100;
                                margin-bottom: 4px;
                            ">
                                {{ $stat->nombre ?? 'Desconocido' }}
                                <div style="position: absolute; top: 100%; left: 50%; margin-left: -4px; border-width: 4px; border-style: solid; border-color: #1e293b transparent transparent transparent;"></div>
                            </div>
                        </div>
                        <span style="font-weight: 700; color: #1e293b; font-size: 11px; background: #f1f5f9; padding: 1px 5px; border-radius: 4px; flex-shrink: 0;">
                            {{ $stat->total }}
                        </span>
                    </div>
                    <div style="width: 100%; height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                        <div style="width: {{ $percentage }}%; height: 100%; background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%); border-radius: 3px;"></div>
                    </div>
                </li>
            @endforeach
        @endif
    </ul>
@endif
