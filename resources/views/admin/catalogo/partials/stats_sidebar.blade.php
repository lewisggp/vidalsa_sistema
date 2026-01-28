@php
    $hasFilter = request('modelo') || request('anio');
@endphp

<style>
    .stat-item {
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        padding: 10px 12px; 
        border-radius: 8px; 
        transition: all 0.2s ease;
        cursor: pointer;
        border: 1px solid transparent;
        margin-bottom: 6px;
    }
    .stat-item:hover {
        background-color: #f8fafc;
        border-color: #e2e8f0;
        transform: translateX(3px);
    }
    .stat-item.active {
        background-color: #eff6ff;
        border-color: #bfdbfe;
    }
    .stat-item.active .stat-count {
        background-color: #3b82f6;
        color: white;
    }
    .stat-name {
        font-size: 13px;
        color: #475569;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 180px;
    }
    .stat-item.active .stat-name {
        color: #1e40af;
        font-weight: 700;
    }
    .stat-count {
        background: #f1f5f9; 
        color: #64748b; 
        font-weight: 700; 
        font-size: 11px; 
        padding: 2px 8px; 
        border-radius: 12px;
        transition: all 0.2s;
    }
    .stat-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .stat-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9; 
    }
    .stat-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e0; 
        border-radius: 2px;
    }
    .stat-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #a0aec0; 
    }
</style>

<!-- Main Total Card -->
<div style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border-radius: 12px; padding: 15px; color: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); position: relative; overflow: hidden; margin-bottom: 8px;">
    <!-- Abstract Shapes for "Dynamic" look -->
    <div style="position: absolute; top: -10px; right: -10px; width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    <div style="position: absolute; bottom: 10px; left: -10px; width: 40px; height: 40px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
    
    <div style="position: relative; z-index: 2;">
        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.8; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; color: #cbd5e0;">
            <i class="material-icons" style="font-size: 14px;">dataset</i>
            Total Registros
        </div>
        
        <div style="display: flex; align-items: baseline; gap: 4px;">
            <span style="font-size: 42px; font-weight: 800; line-height: 1; letter-spacing: -1px;">
                {{ $totalCount }}
            </span>
            <span style="font-size: 14px; opacity: 0.8; font-weight: 500;">Modelos</span>
        </div>
    </div>
</div>

<!-- Breakdown Lists Container -->
<div style="background: white; border-radius: 12px; padding: 12px 15px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column;">
    
    <!-- Count by Model -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
        <h4 style="margin: 0; font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
            <i class="material-icons" style="font-size: 16px; color: #3b82f6;">category</i>
            Por Modelo
        </h4>
        @if(request('modelo'))
            <span onclick="selectAdvancedOption('modelo', '');" style="font-size: 10px; color: #ef4444; cursor: pointer; font-weight: 600; background: #fee2e2; padding: 2px 6px; border-radius: 4px;">
                Borrar Filtro
            </span>
        @endif
    </div>

    <div class="stat-scrollbar" style="max-height: 350px; overflow-y: auto; padding-right: 5px;">
        @foreach($modelCounts as $modelStat)
            @php 
                $isActive = request('modelo') == $modelStat->MODELO;
            @endphp
            <div class="stat-item {{ $isActive ? 'active' : '' }}" 
                 onclick="selectAdvancedOption('modelo', '{{ $modelStat->MODELO }}')">
                
                <div style="display: flex; align-items: center; gap: 10px;">
                    <!-- Color dot indicator -->
                    <div style="width: 6px; height: 6px; border-radius: 50%; background: {{ $isActive ? '#3b82f6' : '#cbd5e0' }};"></div>
                    <span class="stat-name" title="{{ $modelStat->MODELO }}">{{ $modelStat->MODELO }}</span>
                </div>

                <span class="stat-count">
                    {{ $modelStat->count }}
                </span>
            </div>
        @endforeach
    </div>

</div>
