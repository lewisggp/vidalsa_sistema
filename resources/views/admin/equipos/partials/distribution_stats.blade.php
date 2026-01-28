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
                <li style="padding-bottom: 8px; border-bottom: 1px dashed #f1f5f9;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <span style="color: #334155; font-size: 12px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px;" title="{{ $stat->NOMBRE_FRENTE ?? 'Sin Asignar' }}">
                            {{ $stat->NOMBRE_FRENTE ?? 'Sin Asignar' }}
                        </span>
                        <span style="font-weight: 700; color: #1e293b; font-size: 11px; background: #f1f5f9; padding: 1px 5px; border-radius: 4px;">
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
                <li style="padding-bottom: 8px; border-bottom: 1px dashed #f1f5f9;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <span style="color: #334155; font-size: 12px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px;" title="{{ $stat->nombre ?? 'Desconocido' }}">
                            {{ $stat->nombre ?? 'Desconocido' }}
                        </span>
                        <span style="font-weight: 700; color: #1e293b; font-size: 11px; background: #f1f5f9; padding: 1px 5px; border-radius: 4px;">
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
