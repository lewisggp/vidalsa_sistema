@extends('layouts.estructura_base')

@section('titulo', 'Configuración de Flota')

@section('content')
<div style="height: calc(100vh - 80px); display: flex; flex-direction: column; gap: 20px;">
    
    <!-- Navbar / Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="{{ route('equipos.index') }}" style="color: #64748b; text-decoration: none; display: flex; align-items: center; gap: 5px; font-weight: 600; font-size: 14px;">
                <i class="material-icons" style="font-size: 18px;">arrow_back</i> Volver
            </a>
            <div style="width: 1px; height: 24px; background: #e2e8f0;"></div>
            <h1 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="material-icons" style="color: #0f766e;">link</i> 
                Configurador de Flota
            </h1>
        </div>

        <!-- FRENTE SELECTOR -->
        <div style="display: flex; align-items: center; gap: 10px;">
            <label style="font-size: 13px; font-weight: 600; color: #64748b;">Frente Operativo:</label>
            <form action="{{ route('equipos.configuracionFlota') }}" method="GET">
                <select name="id_frente" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px; color: #334155; font-weight: 600; min-width: 200px; cursor: pointer;">
                    <option value="">-- Todos los Frentes --</option>
                    @foreach($frentes as $frente)
                        <option value="{{ $frente->ID_FRENTE }}" {{ request('id_frente') == $frente->ID_FRENTE ? 'selected' : '' }}>
                            {{ $frente->NOMBRE_FRENTE }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- MAIN WORKSPACE -->
    <div style="flex: 1; display: grid; grid-template-columns: 350px 1fr 300px; gap: 20px; min-height: 0;">

        <!-- COLUMN 1: HIJOS DISPONIBLES (Remolcables) -->
        <div class="panel-section" style="background: white; border-radius: 12px; display: flex; flex-direction: column; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            <div style="padding: 15px; border-bottom: 2px solid #f1f5f9; background: #f8fafc; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #64748b; display: flex; align-items: center; justify-content: space-between;">
                    COMPONENTES SUELTOS
                    <span class="badge-counter" style="background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 99px; font-size: 11px;">{{ count($remolcablesLibres) }}</span>
                </h3>
                <input type="text" placeholder="Buscar Placa o Serial..." class="search-input-small" onkeyup="filterList(this, 'listHijosLibres')">
            </div>
            <div class="scroll-container" style="flex: 1; overflow-y: auto; padding: 10px; background: #f8fafc;">
                <div id="listHijosLibres" class="drop-zone" data-type="hijo-libre" style="min-height: 100%; display: flex; flex-direction: column; gap: 8px;">
                     @forelse($remolcablesLibres as $hijo)
                        @php
                            $identificador = $hijo->documentacion->PLACA ?? ($hijo->SERIAL_CHASIS ?? $hijo->CODIGO_PATIO);
                        @endphp
                        <div class="card-item draggable-hijo" draggable="true" data-id="{{ $hijo->ID_EQUIPO }}" ondragstart="drag(event)">
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <div style="background: #e0f2fe; color: #0369a1; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 8px; flex-shrink: 0;">
                                    <i class="material-icons" style="font-size: 20px;">local_shipping</i>
                                </div>
                                <div style="flex: 1; overflow: hidden;">
                                    <div style="font-weight: 800; color: #1e293b; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $identificador }}
                                    </div>
                                    <div style="font-size: 11px; color: #64748b; display: flex; justify-content: space-between;">
                                        <span>{{ $hijo->tipo->nombre ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <button onclick="selectHijo(this, '{{ $hijo->ID_EQUIPO }}')" class="btn-action-small">
                                    <i class="material-icons">arrow_forward</i>
                                </button>
                            </div>
                        </div>
                     @empty
                        <div class="empty-state">No hay componentes libres en este frente</div>
                     @endforelse
                </div>
            </div>
        </div>

        <!-- COLUMN 2: TABLERO DE RELACIONES (Active Pairs) -->
        <div class="panel-section" style="background: white; border-radius: 12px; display: flex; flex-direction: column; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 2px solid #e2e8f0;">
             <div style="padding: 15px; border-bottom: 2px solid #f1f5f9; background: #fff;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #0f172a; text-align: center;">
                    CONFIGURACIÓN ACTUAL (CONVOYS)
                </h3>
            </div>
            
            <div class="scroll-container" style="flex: 1; overflow-y: auto; padding: 20px;">
                <div id="convoysContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">
                    <!-- Loop de Padres con sus hijos -->
                    @foreach($remolcadores as $padre)
                        @php
                            $identificadorPadre = $padre->documentacion->PLACA ?? ($padre->SERIAL_CHASIS ?? $padre->CODIGO_PATIO);
                        @endphp
                        <div class="convoy-card" data-padre-id="{{ $padre->ID_EQUIPO }}" ondrop="drop(event)" ondragover="allowDrop(event)">
                            <!-- HEADER DEL PADRE -->
                            <div class="convoy-header">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="icon-box-padre">
                                        <i class="material-icons">local_shipping</i>
                                    </div>
                                    <div>
                                        <div class="convoy-title">{{ $identificadorPadre }}</div>
                                        <div class="convoy-subtitle">{{ $padre->MARCA }} {{ $padre->MODELO }}</div>
                                    </div>
                                </div>
                                <div class="status-indicator {{ count($padre->equiposAnclados) > 0 ? 'active' : 'idle' }}">
                                    {{ count($padre->equiposAnclados) > 0 ? 'Con Carga' : 'Libre' }}
                                </div>
                            </div>

                            <!-- ZONA DE HIJOS (DROP ZONE) -->
                            <div class="convoy-body">
                                @if(count($padre->equiposAnclados) > 0)
                                    @foreach($padre->equiposAnclados as $hijo)
                                        @php
                                            $identificadorHijo = $hijo->documentacion->PLACA ?? ($hijo->SERIAL_CHASIS ?? $hijo->CODIGO_PATIO);
                                        @endphp
                                        <div class="hijo-anchored-item">
                                            <div style="display: flex; gap: 10px; align-items: center;">
                                                <i class="material-icons" style="font-size: 16px; color: #64748b;">link</i>
                                                <div>
                                                    <span style="font-weight: 700; color: #334155;">{{ $identificadorHijo }}</span>
                                                    <span style="font-size: 11px; color: #64748b; margin-left: 5px;">{{ $hijo->tipo->nombre ?? '' }}</span>
                                                </div>
                                            </div>
                                            <button onclick="desvincular('{{ $hijo->ID_EQUIPO }}')" class="btn-unlink" title="Desvincular">
                                                <i class="material-icons">link_off</i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="drop-placeholder">
                                        <i class="material-icons" style="font-size: 20px; color: #cbd5e1;">add_link</i>
                                        <span>Arrastre un componente aquí</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- COLUMN 3: FILTROS O INFO EXTRA -->
        <div class="panel-section" style="background: white; border-radius: 12px; display: flex; flex-direction: column; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
             <div style="padding: 15px; border-bottom: 2px solid #f1f5f9; background: #f8fafc; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #64748b;">
                    AYUDA RÁPIDA
                </h3>
            </div>
            <div style="padding: 20px;">
                <p style="font-size: 13px; color: #64748b; line-height: 1.5;">
                    Seleccione un <strong>Frente Operativo</strong> arriba para filtrar los equipos.
                </p>
                <p style="font-size: 13px; color: #64748b; line-height: 1.5;">
                    Arrastre los componentes desde la izquierda hacia los remolcadores disponibles.
                </p>
                
                <div style="margin-top: 20px; padding: 15px; background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;">
                    <div style="font-weight: 700; color: #166534; font-size: 13px; margin-bottom: 5px;">Nota Importante</div>
                    <div style="font-size: 12px; color: #15803d;">
                        Solo se muestran los equipos en estado OPERATIVO o EN MANTENIMIENTO. Los equipos Inoperativos no pueden ser configurados.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    /* Estilos Específicos para este Módulo */
    .search-input-small {
        width: 100%;
        margin-top: 10px;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        outline: none;
    }
    .search-input-small:focus { border-color: #0f766e; }

    .card-item {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px;
        cursor: grab;
        transition: all 0.2s;
    }
    .card-item:hover {
        border-color: #94a3b8;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -2px rgba(0,0,0,0.05);
    }
    .card-item:active { cursor: grabbing; }

    .btn-action-small {
        background: transparent; border: none; color: #cbd5e1; cursor: pointer; padding: 5px; border-radius: 4px;
    }
    .btn-action-small:hover { background: #f1f5f9; color: #0f766e; }

    .empty-state {
        text-align: center; color: #94a3b8; font-size: 12px; padding: 20px; font-style: italic;
    }

    /* Convoy Card Styling */
    .convoy-card {
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.2s;
    }
    .convoy-card.drag-over {
        border-color: #0f766e;
        background: #f0fdfa;
        transform: scale(1.01);
    }

    .convoy-header {
        padding: 12px 15px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .icon-box-padre {
        width: 36px; height: 36px; background: #0f172a; color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center;
    }
    
    .convoy-title { font-weight: 700; font-size: 14px; color: #0f172a; }
    .convoy-subtitle { font-size: 11px; color: #64748b; }

    .status-indicator {
        font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 99px; text-transform: uppercase;
    }
    .status-indicator.idle { background: #e2e8f0; color: #64748b; }
    .status-indicator.active { background: #dcfce7; color: #166534; }

    .convoy-body {
        padding: 10px;
        min-height: 50px;
        background: white;
    }

    .drop-placeholder {
        border: 2px dashed #e2e8f0;
        border-radius: 8px;
        padding: 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 12px;
        gap: 5px;
    }

    .hijo-anchored-item {
        background: #f1f5f9;
        border-radius: 6px;
        padding: 8px 12px;
        margin-bottom: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-left: 3px solid #0f766e;
    }
    
    .btn-unlink {
        border: none; background: transparent; color: #ef4444; cursor: pointer; padding: 4px; border-radius: 4px; opacity: 0.6;
    }
    .btn-unlink:hover { opacity: 1; background: #fee2e2; }

</style>

<script>
    // --- DRAG AND DROP LOGIC ---
    let draggedItemId = null;

    function drag(ev) {
        draggedItemId = ev.target.getAttribute('data-id');
        ev.dataTransfer.setData("text", draggedItemId);
        ev.target.style.opacity = '0.5';
    }

    function allowDrop(ev) {
        ev.preventDefault();
        // Visual feedback
        const card = ev.currentTarget;
        card.classList.add('drag-over');
    }

    // Leave event to remove visual feedback
    document.addEventListener('dragleave', function(event) {
        if (event.target.classList && event.target.classList.contains('convoy-card')) {
             event.target.classList.remove('drag-over');
        }
    });

    function drop(ev) {
        ev.preventDefault();
        
        // Find the Card Element (Target)
        let targetCard = ev.target.closest('.convoy-card');
        if (!targetCard) return;

        targetCard.classList.remove('drag-over');
        const padreId = targetCard.getAttribute('data-padre-id');
        const hijoId = draggedItemId;

        if (!hijoId || !padreId) return;

        // AJAX CALL TO VINCULAR
        fetch("{{ route('equipos.vincular') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ padre_id: padreId, hijo_id: hijoId })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                location.reload(); // Simple reload for now to reflect state
            } else {
                alert('Error: ' + (data.message || 'No se pudo vincular'));
            }
        })
        .catch(err => console.error(err));
        
        // Reset Opacity
        const draggedEl = document.querySelector(`.draggable-hijo[data-id="${hijoId}"]`);
        if(draggedEl) draggedEl.style.opacity = '1';
    }

    function desvincular(hijoId) {
        if(!confirm('¿Desea desvincular este componente?')) return;

        fetch("{{ route('equipos.desvincular') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ hijo_id: hijoId })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo desvincular'));
            }
        })
        .catch(err => console.error(err));
    }

    // --- Search Filter ---
    function filterList(input, listId) {
        const filter = input.value.toUpperCase();
        const list = document.getElementById(listId);
        const items = list.getElementsByClassName('card-item');

        for (let i = 0; i < items.length; i++) {
            const txtValue = items[i].textContent || items[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                items[i].style.display = "";
            } else {
                items[i].style.display = "none";
            }
        }
    }
    
    // Fallback for button click instead of drag
    function selectHijo(btn, id) {
        alert('Por favor arrastre este componente hacia el equipo remolcador deseado en la columna central.');
    }
</script>
@endsection
