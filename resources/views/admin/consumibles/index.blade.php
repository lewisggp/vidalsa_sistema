@extends('layouts.estructura_base')
@section('title', 'Consumibles')

@section('content')
<div id="consumiblesAppRoot" data-route-match="{{ route('consumibles.matchAutomatico') }}" data-frentes='@json($frentes->map(fn($f) => ['id' => $f->ID_FRENTE, 'nombre' => $f->NOMBRE_FRENTE]))'>
<style>
    .badge-pen  { background:#fef3c7; color:#92400e; border-radius:20px; padding:2px 10px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:4px; }
    .badge-ok   { background:#d1fae5; color:#065f46; border-radius:20px; padding:2px 10px; font-size:12px; font-weight:700; }
    .badge-err  { background:#fee2e2; color:#991b1b; border-radius:20px; padding:2px 10px; font-size:12px; font-weight:700; }
    .badge-none { background:#f1f5f9; color:#475569; border-radius:20px; padding:2px 10px; font-size:12px; font-weight:700; }
    /* Chips para Tipos de consumible */
    .tipo-chip { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:2px 10px; font-size:11px; font-weight:700; }
    .tipo-gasoil   { background:#fef3c7; color:#92400e; }
    .tipo-aceite   { background:#e0f2fe; color:#0369a1; }
    .tipo-caucho   { background:#f3e8ff; color:#6b21a8; }
    .tipo-otro     { background:#f1f5f9; color:#475569; }

    /* Panel match automático */
    .match-panel { background:linear-gradient(135deg,#1e293b,#0f172a); border-radius:14px; padding:20px 24px; margin-bottom:20px; color:#fff; }
    .match-panel h3 { margin:0 0 4px 0; font-size:15px; font-weight:700; display:flex; align-items:center; gap:8px; }
    .match-panel p  { margin:0 0 16px 0; color:#94a3b8; font-size:13px; }
    .match-counters { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
    .match-counter  { background:rgba(255,255,255,0.07); border-radius:10px; padding:10px 18px; text-align:center; min-width:90px; }
    .match-counter strong { display:block; font-size:26px; font-weight:800; }
    .match-counter span   { font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; }
    .c-pen { color:#fbbf24; } .c-ok { color:#34d399; } .c-err { color:#f87171; }
    .btn-match { background:linear-gradient(135deg,#0067b1,#0052cc); color:#fff; border:none; padding:11px 24px; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all .2s; }
    .btn-match:hover { opacity:.9; transform:translateY(-1px); }
    .btn-match:disabled { opacity:.5; cursor:not-allowed; transform:none; }
    .match-progress { display:none; margin-top:16px; }
    .match-bar-wrap { background:rgba(255,255,255,0.1); border-radius:20px; height:8px; overflow:hidden; margin-top:8px; }
    .match-bar { height:100%; background:linear-gradient(90deg,#34d399,#059669); border-radius:20px; width:0%; transition:width .6s; }
    .match-results { display:none; margin-top:16px; background:rgba(255,255,255,0.05); border-radius:10px; padding:14px; max-height:220px; overflow-y:auto; }
    .match-result-row { display:flex; justify-content:space-between; align-items:center; padding:5px 0; border-bottom:1px solid rgba(255,255,255,0.06); font-size:12px; }
    .match-result-row:last-child { border:none; }
    .mr-id    { font-family:monospace; color:#cbd5e1; font-weight:600; min-width:130px; }
    .mr-match { color:#34d399; }
    .mr-none  { color:#f87171; font-style:italic; }
</style>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 class="page-title" style="margin-bottom:4px;">
            <span class="page-title-line2" style="color:#000;">Gestión de Consumibles</span>
        </h1>
        <p style="margin:0; color:#64748b; font-size:13px;">Gasoil · Aceite · Cauchos por frente y equipo</p>
    </div>
</div>

{{-- ══ PANEL MATCH AUTOMÁTICO ══ --}}
@if($pendientes > 0 || $sinMatch > 0)
<div class="match-panel">
    <h3>
        <i class="material-icons" style="font-size:20px; color:#fbbf24;">auto_fix_high</i>
        Match Automático de Equipos
    </h3>
    <p>El sistema compara el <strong>IDENTIFICADOR</strong> de cada registro contra <strong>placa</strong> y <strong>serial de chasis</strong> de tus equipos. Lo que no encuentre queda como Sin Match.</p>

    <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap; justify-content: space-between;">
        <div class="match-counters" style="margin-bottom: 0;">
            {{-- Pendientes → filtra tabla a PENDIENTE --}}
            <a class="match-counter" href="{{ route('consumibles.index', ['estado' => 'PENDIENTE']) }}" style="text-decoration:none;" title="Ver pendientes en la tabla">
                <strong class="c-pen" id="cnt-pendientes">{{ $pendientes }}</strong>
                <span>Pendientes</span>
                @if($pendientes > 0)<span style="font-size:10px; color:#fbbf24; margin-top:2px;">→ ver →</span>@endif
            </a>
            <div class="match-counter">
                <strong class="c-ok" id="cnt-confirmados">{{ $confirmados }}</strong>
                <span>Confirmados</span>
            </div>
            {{-- Sin Match → filtra tabla a SIN_MATCH --}}
            <a class="match-counter" href="{{ route('consumibles.index', ['estado' => 'SIN_MATCH']) }}" style="text-decoration:none;" title="Ver sin match en la tabla">
                <strong class="c-err" id="cnt-sinmatch">{{ $sinMatch }}</strong>
                <span>Sin Match</span>
                @if($sinMatch > 0)<span style="font-size:10px; color:#f87171; margin-top:2px;">→ ver →</span>@endif
            </a>
        </div>

        {{-- Botón Acciones al lado de los contadores --}}
        <div style="position: relative; z-index: 1000; flex-shrink: 0;">
            <button type="button" id="btnAcciones" onclick="toggleAccionesMenu(event)" class="btn-primary-maquinaria" style="padding: 0 15px; height: 45px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <i class="material-icons">settings</i>
                <span>Acciones</span>
                <i class="material-icons" style="font-size: 18px; margin-left: 2px;">expand_more</i>
            </button>
            <div id="splitDropdownMenu" style="display: none; position: absolute; top: 100%; right: 0; width: 260px; background: #e2e8f0; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; z-index: 1050; margin-top: 5px; overflow: hidden;">
                <a href="{{ route('consumibles.graficos') }}" class="dropdown-item-custom" style="display: flex; align-items: center; gap: 10px; padding: 12px 15px; color: #475569; text-decoration: none; border-bottom: 1px solid #f1f5f9; background: transparent; transition: all 0.2s;">
                    <div style="background: #eff6ff; padding: 6px; border-radius: 6px; display: flex;"><i class="material-icons" style="font-size:18px; color:#3b82f6;">bar_chart</i></div>
                    <span style="font-size:14px; font-weight:500;">Gráficos y Reportes</span>
                </a>
                <a href="{{ route('consumibles.cargar') }}" class="dropdown-item-custom" style="display: flex; align-items: center; gap: 10px; padding: 12px 15px; color: #475569; text-decoration: none; border-bottom: 1px solid #f1f5f9; background: transparent; transition: all 0.2s;">
                    <div style="background: #f1f5f9; padding: 6px; border-radius: 6px; display: flex;"><i class="material-icons" style="font-size:18px; color:#64748b;">upload</i></div>
                    <span style="font-size:14px; font-weight:500;">Cargar Lote de Consumibles</span>
                </a>
                <button type="button" id="btnMatch" onclick="document.getElementById('splitDropdownMenu').style.display='none'; ejecutarMatch()" class="dropdown-item-custom" style="width:100%; display:flex; align-items:center; gap:10px; padding:12px 15px; color:#475569; border:none; background:transparent; text-align:left; cursor:pointer; transition:all 0.2s;" {{ ($pendientes == 0 && $sinMatch == 0) ? 'disabled' : '' }}>
                    <div style="background:#fee2e2; padding:6px; border-radius:6px; display:flex;"><i class="material-icons" style="font-size:18px; color:#dc2626;">bolt</i></div>
                    <span style="font-size:14px; font-weight:500;">Ejecutar Match ({{ $pendientes }} Pend. / {{ $sinMatch }} S/M)</span>
                </button>
            </div>
            <script>
                function toggleAccionesMenu(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    var menu = document.getElementById('splitDropdownMenu');
                    menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
                }
                document.addEventListener('click', function(e) {
                    var btn = document.getElementById('btnAcciones');
                    var menu = document.getElementById('splitDropdownMenu');
                    if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
                        menu.style.display = 'none';
                    }
                });
            </script>
        </div>
    </div>

    <div class="match-progress" id="matchProgress">
        <p style="margin:0 0 6px 0; font-size:13px; color:#94a3b8;">Procesando registros...</p>
        <div class="match-bar-wrap"><div class="match-bar" id="matchBar"></div></div>
    </div>

    <div class="match-results" id="matchResults">
        <p style="margin:0 0 8px 0; font-size:11px; color:#94a3b8; font-weight:700; text-transform:uppercase; letter-spacing:.5px;">Detalle del proceso — ✅ coincidió · ✗ no encontrado</p>
        <div id="matchResultsBody"></div>
    </div>
</div>
@endif

<div class="admin-card" style="box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px;">
    {{-- Filtros --}}
    <form method="GET" action="{{ route('consumibles.index') }}" id="filtrosForm">
    <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center; margin-bottom: 20px;">
        <div style="flex: 2; min-width: 200px;">
            @php
                $currentFrenteId = request('id_frente');
                $currentFrente = $currentFrenteId ? $frentes->firstWhere('ID_FRENTE', $currentFrenteId) : null;
            @endphp
            <div class="custom-dropdown" id="frenteFilterSelect" data-filter-type="id_frente" data-default-label="Todos los frentes">
                <input type="hidden" name="id_frente" data-filter-value value="{{ $currentFrenteId }}" form="filtrosForm">

                <div class="dropdown-trigger {{ $currentFrenteId ? 'filter-active' : '' }}" style="padding:0; display:flex; align-items:center; background:#fbfcfd; overflow:hidden; border:1px solid #cbd5e0; border-radius:10px; height:42px;">
                    <div style="padding:0 10px; display:flex; align-items:center; color:var(--maquinaria-gray-text, #94a3b8);">
                        <i class="material-icons" style="font-size:18px;">search</i>
                    </div>
                    <input type="text" data-filter-search
                        placeholder="{{ $currentFrente ? $currentFrente->NOMBRE_FRENTE : 'Todos los frentes' }}"
                        style="width:100%; border:none; background:transparent; padding:0 5px; font-size:13px; outline:none; height:100%;"
                        oninput="window.filterDropdownOptions(this)"
                        autocomplete="off">
                    <i class="material-icons" data-clear-btn
                       style="padding:0 5px; color:var(--maquinaria-gray-text, #94a3b8); font-size:18px; display:{{ $currentFrenteId ? 'block' : 'none' }}; cursor:pointer;"
                       onclick="event.stopPropagation(); window.clearDropdownFilter('frenteFilterSelect'); document.getElementById('filtrosForm').submit();">close</i>
                </div>

                <div class="dropdown-content" style="padding:5px; max-height:none; overflow:visible; z-index:1000;">
                    <div class="dropdown-item-list" style="max-height:250px; overflow-y:auto;">
                        <div class="dropdown-item {{ !$currentFrenteId ? 'selected' : '' }}"
                             data-value=""
                             onclick="window.selectOption('frenteFilterSelect', '', 'Todos los frentes'); setTimeout(()=>document.getElementById('filtrosForm').submit(), 50);">
                            Todos los frentes
                        </div>
                        @foreach($frentes as $f)
                            <div class="dropdown-item {{ $currentFrenteId == $f->ID_FRENTE ? 'selected' : '' }}"
                                 data-value="{{ $f->ID_FRENTE }}"
                                 onclick="window.selectOption('frenteFilterSelect', '{{ $f->ID_FRENTE }}', '{{ $f->NOMBRE_FRENTE }}'); setTimeout(()=>document.getElementById('filtrosForm').submit(), 50);">
                                {{ $f->NOMBRE_FRENTE }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div style="flex: 1.5; min-width: 140px;">
            @php
                $currentTipo = request('tipo');
                $tiposList = \App\Models\Consumible::tiposLabel();
            @endphp
            <div class="custom-dropdown" id="tipoFilterSelect" data-filter-type="tipo" data-default-label="Todos los tipos">
                <input type="hidden" name="tipo" data-filter-value value="{{ $currentTipo }}" form="filtrosForm">
                <div class="dropdown-trigger {{ $currentTipo ? 'filter-active' : '' }}" style="padding:0; display:flex; align-items:center; background:#fbfcfd; overflow:hidden; border:1px solid #cbd5e0; border-radius:10px; height:42px;">
                    <div style="padding:0 10px; display:flex; align-items:center; color:var(--maquinaria-gray-text, #94a3b8);">
                        <i class="material-icons" style="font-size:18px;">category</i>
                    </div>
                    <input type="text" data-filter-search
                        placeholder="{{ $currentTipo ? $tiposList[$currentTipo] : 'Todos los tipos' }}"
                        style="width:100%; border:none; background:transparent; padding:0 5px; font-size:13px; outline:none; height:100%;"
                        oninput="window.filterDropdownOptions(this)"
                        autocomplete="off">
                    <i class="material-icons" data-clear-btn
                       style="padding:0 5px; color:var(--maquinaria-gray-text, #94a3b8); font-size:18px; display:{{ $currentTipo ? 'block' : 'none' }}; cursor:pointer;"
                       onclick="event.stopPropagation(); window.clearDropdownFilter('tipoFilterSelect'); document.getElementById('filtrosForm').submit();">close</i>
                </div>
                <div class="dropdown-content" style="padding:5px; max-height:none; overflow:visible; z-index:1000;">
                    <div class="dropdown-item-list" style="max-height:250px; overflow-y:auto;">
                        <div class="dropdown-item {{ !$currentTipo ? 'selected' : '' }}" data-value="" onclick="window.selectOption('tipoFilterSelect', '', 'Todos los tipos'); setTimeout(()=>document.getElementById('filtrosForm').submit(), 50);">Todos los tipos</div>
                        @foreach($tiposList as $val => $label)
                            <div class="dropdown-item {{ $currentTipo == $val ? 'selected' : '' }}" data-value="{{ $val }}" onclick="window.selectOption('tipoFilterSelect', '{{ $val }}', '{{ $label }}'); setTimeout(()=>document.getElementById('filtrosForm').submit(), 50);">{{ $label }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div style="flex: 1.5; min-width: 140px;">
            @php
                $currentEstado = request('estado');
                $estadosList = ['CONFIRMADO'=>'Confirmado', 'PENDIENTE'=>'Pendiente', 'SIN_MATCH'=>'Sin Match'];
            @endphp
            <div class="custom-dropdown" id="estadoFilterSelect" data-filter-type="estado" data-default-label="Todos los estados">
                <input type="hidden" name="estado" data-filter-value value="{{ $currentEstado }}" form="filtrosForm">
                <div class="dropdown-trigger {{ $currentEstado ? 'filter-active' : '' }}" style="padding:0; display:flex; align-items:center; background:#fbfcfd; overflow:hidden; border:1px solid #cbd5e0; border-radius:10px; height:42px;">
                    <div style="padding:0 10px; display:flex; align-items:center; color:var(--maquinaria-gray-text, #94a3b8);">
                        <i class="material-icons" style="font-size:18px;">flag</i>
                    </div>
                    <input type="text" data-filter-search
                        placeholder="{{ $currentEstado ? $estadosList[$currentEstado] : 'Todos los estados' }}"
                        style="width:100%; border:none; background:transparent; padding:0 5px; font-size:13px; outline:none; height:100%;"
                        oninput="window.filterDropdownOptions(this)"
                        autocomplete="off">
                    <i class="material-icons" data-clear-btn
                       style="padding:0 5px; color:var(--maquinaria-gray-text, #94a3b8); font-size:18px; display:{{ $currentEstado ? 'block' : 'none' }}; cursor:pointer;"
                       onclick="event.stopPropagation(); window.clearDropdownFilter('estadoFilterSelect'); document.getElementById('filtrosForm').submit();">close</i>
                </div>
                <div class="dropdown-content" style="padding:5px; max-height:none; overflow:visible; z-index:1000;">
                    <div class="dropdown-item-list" style="max-height:250px; overflow-y:auto;">
                        <div class="dropdown-item {{ !$currentEstado ? 'selected' : '' }}" data-value="" onclick="window.selectOption('estadoFilterSelect', '', 'Todos los estados'); setTimeout(()=>document.getElementById('filtrosForm').submit(), 50);">Todos los estados</div>
                        @foreach($estadosList as $val => $label)
                            <div class="dropdown-item {{ $currentEstado == $val ? 'selected' : '' }}" data-value="{{ $val }}" onclick="window.selectOption('estadoFilterSelect', '{{ $val }}', '{{ $label }}'); setTimeout(()=>document.getElementById('filtrosForm').submit(), 50);">{{ $label }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div style="flex: 1.5; min-width: 140px;">
            @php
                $currentTipoEquipoId = request('id_tipo_equipo');
                $currentTipoEquipo = $currentTipoEquipoId ? $tipos_equipo->firstWhere('id', $currentTipoEquipoId) : null;
            @endphp
            <div class="custom-dropdown" id="tipoEquipoFilterSelect" data-filter-type="id_tipo_equipo" data-default-label="Equipo Resuelto">
                <input type="hidden" name="id_tipo_equipo" data-filter-value value="{{ $currentTipoEquipoId }}" form="filtrosForm">
                <div class="dropdown-trigger {{ $currentTipoEquipoId ? 'filter-active' : '' }}" style="padding:0; display:flex; align-items:center; background:#fbfcfd; overflow:hidden; border:1px solid #cbd5e0; border-radius:10px; height:42px;">
                    <div style="padding:0 10px; display:flex; align-items:center; color:var(--maquinaria-gray-text, #94a3b8);">
                        <i class="material-icons" style="font-size:18px;">local_shipping</i>
                    </div>
                    <input type="text" data-filter-search
                        placeholder="{{ $currentTipoEquipo ? $currentTipoEquipo->nombre : 'Equipo Resuelto' }}"
                        style="width:100%; border:none; background:transparent; padding:0 5px; font-size:13px; outline:none; height:100%;"
                        oninput="window.filterDropdownOptions(this)"
                        autocomplete="off">
                    <i class="material-icons" data-clear-btn
                       style="padding:0 5px; color:var(--maquinaria-gray-text, #94a3b8); font-size:18px; display:{{ $currentTipoEquipoId ? 'block' : 'none' }}; cursor:pointer;"
                       onclick="event.stopPropagation(); window.clearDropdownFilter('tipoEquipoFilterSelect'); document.getElementById('filtrosForm').submit();">close</i>
                </div>
                <div class="dropdown-content" style="padding:5px; max-height:none; overflow:visible; z-index:1000;">
                    <div class="dropdown-item-list" style="max-height:250px; overflow-y:auto;">
                        <div class="dropdown-item {{ !$currentTipoEquipoId ? 'selected' : '' }}" data-value="" onclick="window.selectOption('tipoEquipoFilterSelect', '', 'Equipo Resuelto'); setTimeout(()=>document.getElementById('filtrosForm').submit(), 50);">Equipo Resuelto (Todos)</div>
                        @foreach($tipos_equipo as $tEq)
                            <div class="dropdown-item {{ $currentTipoEquipoId == $tEq->id ? 'selected' : '' }}" data-value="{{ $tEq->id }}" onclick="window.selectOption('tipoEquipoFilterSelect', '{{ $tEq->id }}', '{{ $tEq->nombre }}'); setTimeout(()=>document.getElementById('filtrosForm').submit(), 50);">{{ $tEq->nombre }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 8px;">
            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" onchange="this.form.submit()" onclick="this.showPicker()" title="Desde" style="width: 100%; height: 42px; border-radius: 10px; border: 1px solid #cbd5e0; background: #fbfcfd; outline: none; padding: 0 12px; font-size:13px; color: #1e293b; cursor: pointer;">
            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" onchange="this.form.submit()" onclick="this.showPicker()" title="Hasta" style="width: 100%; height: 42px; border-radius: 10px; border: 1px solid #cbd5e0; background: #fbfcfd; outline: none; padding: 0 12px; font-size:13px; color: #1e293b; cursor: pointer;">
        </div>

        <div style="flex: 1.5; min-width: 160px;">
            <div style="width: 100%; height: 42px; border-radius: 10px; display: flex; align-items: center; border: 1px solid {{ request('identificador') ? '#0067b1' : '#cbd5e0' }}; background: {{ request('identificador') ? '#e1effa' : '#fbfcfd' }}; overflow: hidden;">
                <i class="material-icons" style="padding: 0 8px; color: #94a3b8; font-size: 18px; flex-shrink: 0;">search</i>
                <input type="text" name="identificador" value="{{ request('identificador') }}" placeholder="Buscar placa/serial..." style="flex: 1; border: none; background: transparent; padding: 0; outline: none; font-size: 13px; color:#1e293b; min-width: 0;" onkeydown="if(event.key==='Enter'){this.form.submit();}" oninput="clearTimeout(window._idTimer); window._idTimer=setTimeout(()=>this.form.submit(),600)">
                @if(request('identificador'))
                    <a href="{{ route('consumibles.index', array_merge(request()->except('identificador'))) }}" style="padding: 0 8px; color: #94a3b8; display: flex; align-items: center; flex-shrink: 0;"><i class="material-icons" style="font-size: 18px;">close</i></a>
                @endif
            </div>
        </div>

        @if(request()->hasAny(['id_frente','tipo','estado','id_tipo_equipo','fecha_desde','fecha_hasta','identificador']))
            <div style="flex: 0 0 auto;">
                <a href="{{ route('consumibles.index') }}" class="btn-secondary" style="height: 42px; display: flex; align-items: center; padding: 0 15px; border-radius: 10px;">
                    <i class="material-icons" style="font-size:17px;">close</i>
                </a>
            </div>
        @endif
    </div>
    </form>

    {{-- Resumen surtido por frente --}}
    @if($resumenFrente->isNotEmpty())
    @php $maxFrente = $resumenFrente->max('total'); @endphp
    <div style="margin: 18px 0 20px 0;">
        <p style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin:0 0 10px 0; display:flex; align-items:center; gap:6px;">
            <i class="material-icons" style="font-size:15px; color:#0067b1;">bar_chart</i>
            Surtido por frente
            <span style="font-weight:400; color:#94a3b8; text-transform:none; letter-spacing:0;">— con los filtros aplicados</span>
        </p>
        <div style="display:flex; flex-direction:column; gap:6px;">
            @foreach($resumenFrente as $rf)
            @php
                $pct = $maxFrente > 0 ? round($rf->total / $maxFrente * 100, 1) : 0;
                $isTop = $loop->first;
                $barColor = $isTop
                    ? 'linear-gradient(90deg,#7f1d1d,#b91c1c)'
                    : 'linear-gradient(90deg,#003a70,#0067b1)';
            @endphp
            <div style="display:grid; grid-template-columns:180px 1fr 130px; align-items:center; gap:10px;">
                <span style="font-size:12px; font-weight:700; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $rf->NOMBRE_FRENTE }}">
                    {{ $rf->NOMBRE_FRENTE }}
                </span>
                <div style="background:#f1f5f9; border-radius:20px; height:9px; overflow:hidden;">
                    <div style="width:{{ $pct }}%; background:{{ $barColor }}; height:100%; border-radius:20px; transition:width .5s;"></div>
                </div>
                <span style="font-size:12px; font-weight:800; color:#003a70; white-space:nowrap; text-align:right;">
                    {{ number_format($rf->total, 0, ',', '.') }} {{ $rf->unidad }}
                    <span style="display:block; font-size:10px; font-weight:600; color:#64748b;">⛽ {{ $rf->despachos }} despacho{{ $rf->despachos != 1 ? 's' : '' }}</span>
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Tabla --}}
    <div style="overflow-x:auto;">

    <table class="admin-table">
        <thead>
            <tr>
                <th style="width:38px; text-align:center; color:#94a3b8;">#</th>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Frente</th>
                <th>Identificador</th>
                <th>Equipo Resuelto</th>
                <th>Responsable</th>
                <th>Estado</th>
                <th style="width:40px;"></th>
            </tr>
        </thead>
        <tbody>
        @forelse($consumibles as $c)
            @php
                $tipoClass = match(true) {
                    str_starts_with($c->TIPO_CONSUMIBLE, 'GASOIL') || str_starts_with($c->TIPO_CONSUMIBLE, 'GASOLINA') => 'tipo-gasoil',
                    str_starts_with($c->TIPO_CONSUMIBLE, 'ACEITE') => 'tipo-aceite',
                    $c->TIPO_CONSUMIBLE === 'CAUCHO' => 'tipo-caucho',
                    default => 'tipo-otro'
                };
            @endphp
            <tr>
                <td style="text-align:center; font-size:11px; font-weight:700; color:#cbd5e0;
                           background:#f8fafc; border-right:1px solid #e2e8f0; padding:6px 4px;">
                    {{ ($consumibles->currentPage() - 1) * $consumibles->perPage() + $loop->iteration }}
                </td>
                <td style="white-space:nowrap; font-weight:600;">
                    {{ \Carbon\Carbon::parse($c->FECHA)->format('d/m/Y') }}
                </td>
                <td>
                    <span class="tipo-chip {{ $tipoClass }}">
                        {{ $c->tipo_label }}
                    </span>
                    @if($c->ESPECIFICACION)
                        <span style="display:block; font-size:10px; color:#64748b; font-weight:600;
                                     margin-top:2px; font-family:monospace;">{{ $c->ESPECIFICACION }}</span>
                    @endif
                </td>
                <td style="font-weight:700; text-align:right; white-space:nowrap;">
                    {{ number_format($c->CANTIDAD, 1) }}
                    <span style="color:#94a3b8; font-size:11px;">{{ $c->UNIDAD }}</span>
                </td>
                <td style="font-size:12px;" id="frente-cell-{{ $c->ID_CONSUMIBLE }}">
                    <div style="display:flex; align-items:center; gap:4px;">
                        <span id="frente-txt-{{ $c->ID_CONSUMIBLE }}" style="flex:1;">
                            {{ $c->frente?->NOMBRE_FRENTE ?? '—' }}
                        </span>
                        <button onclick="editarFrente({{ $c->ID_CONSUMIBLE }}, {{ $c->ID_FRENTE ?? 'null' }})"
                                style="background:none;border:none;cursor:pointer;padding:2px;color:#94a3b8;"
                                title="Cambiar frente">
                            <i class="material-icons" style="font-size:14px;">edit</i>
                        </button>
                    </div>
                    @php
                        $frenteConsumible = $c->ID_FRENTE;
                        $frenteEquipo     = $c->equipo?->ID_FRENTE_ACTUAL;
                        $discrepancia     = $c->equipo && $frenteEquipo && $frenteConsumible != $frenteEquipo;
                    @endphp
                    @if($discrepancia)
                        <span title="El equipo está registrado en otro frente: {{ $c->equipo->frenteActual?->NOMBRE_FRENTE ?? 'N/A' }}"
                              style="display:inline-flex; align-items:center; gap:3px; background:#fef3c7; color:#92400e;
                                     border-radius:6px; padding:1px 6px; font-size:10px; font-weight:700;
                                     cursor:help; margin-left:4px; white-space:nowrap;">
                            ⚠ {{ $c->equipo->frenteActual?->NOMBRE_FRENTE ?? '?' }}
                        </span>
                    @endif
                </td>
                <td style="font-family:monospace; font-size:12px; color:#1e293b;" id="id-cell-{{ $c->ID_CONSUMIBLE }}">
                    <div style="display:flex; align-items:center; gap:5px;">
                        <span id="id-txt-{{ $c->ID_CONSUMIBLE }}" style="flex:1;">{{ $c->IDENTIFICADOR ?? '—' }}</span>
                        <button onclick="editarId({{ $c->ID_CONSUMIBLE }}, '{{ addslashes($c->IDENTIFICADOR ?? '') }}')"
                                style="background:none;border:none;cursor:pointer;padding:2px;color:#94a3b8;"
                                title="Editar identificador">
                            <i class="material-icons" style="font-size:14px;">edit</i>
                        </button>
                    </div>
                </td>
                <td style="font-size:12px; line-height:1.5;">
                    @if($c->equipo)
                        @php
                            // ¿El identificador buscado es exactamente la placa o el serial?
                            $idBuscado  = strtoupper(trim($c->IDENTIFICADOR ?? ''));
                            $placa      = strtoupper(trim($c->equipo->CODIGO_PATIO ?? ''));
                            $serial     = strtoupper(trim($c->equipo->SERIAL_CHASIS ?? ''));
                            $esExacto   = ($idBuscado === $placa || $idBuscado === $serial);
                            // Coincidencia real: mostrar serial si el buscado no es exacto
                            $coincidencia = (!$esExacto && $idBuscado)
                                ? ($serial ?: $placa)
                                : null;
                        @endphp

                        {{-- Tipo --}}
                        <span style="font-size:10px; font-weight:700; color:#0067b1; text-transform:uppercase; letter-spacing:.3px;">
                            {{ $c->equipo->tipo?->nombre ?? 'S/T' }}
                        </span>

                        {{-- Marca --}}
                        <span style="display:block; color:#1e293b; font-weight:700; font-size:13px;">
                            {{ $c->equipo->MARCA }}
                        </span>

                        {{-- Frente actual del equipo en la BD --}}
                        <span style="display:block; font-size:11px; color:#475569; margin-top:1px;">
                            <i class="material-icons" style="font-size:11px; vertical-align:middle;">location_on</i>
                            {{ $c->equipo->frenteActual?->NOMBRE_FRENTE ?? 'Sin frente asignado' }}
                        </span>

                        {{-- Identificador buscado --}}
                        <span style="display:block; font-family:monospace; font-size:11px; color:#64748b; margin-top:2px;">
                            {{ $c->IDENTIFICADOR ?? '—' }}
                        </span>

                        {{-- Coincidencia completa (si el buscado era parcial) --}}
                        @if($coincidencia)
                            <span style="display:block; font-family:monospace; font-size:10px;
                                         color:#059669; margin-top:1px; font-weight:700;"
                                  title="Serial/placa completo encontrado en la BD">
                                → {{ $coincidencia }}
                            </span>
                        @endif

                    @else
                        <span style="color:#94a3b8; font-style:italic; font-size:12px;">Sin resolver</span>
                        @if($c->IDENTIFICADOR)
                            <span style="display:block; font-family:monospace; font-size:11px; color:#f87171; margin-top:2px;">
                                {{ $c->IDENTIFICADOR }}
                            </span>
                        @endif
                    @endif
                </td>
                <td style="font-size:12px;">
                    {{ $c->RESP_NOMBRE ?? '—' }}
                    @if($c->RESP_CI)
                        <span style="color:#94a3b8;"> · {{ $c->RESP_CI }}</span>
                    @endif
                </td>
                <td>
                    @if($c->ESTADO_EQUIPO === 'CONFIRMADO')
                        <span class="badge-ok">✓ Confirmado</span>
                    @elseif($c->ESTADO_EQUIPO === 'PENDIENTE')
                        <span class="badge-pen">⏳ Pendiente</span>
                    @else
                        <span class="badge-err">✗ Sin match</span>
                    @endif
                </td>
                <td>
                    <button type="button"
                        onclick="eliminarConsumible({{ $c->ID_CONSUMIBLE }}, '{{ route('consumibles.destroy', $c->ID_CONSUMIBLE) }}')"
                        style="background:none; border:none; color:#ef4444; cursor:pointer; padding:4px;"
                        title="Eliminar">
                        <i class="material-icons" style="font-size:17px;">delete</i>
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" style="text-align:center; padding:40px; color:#94a3b8;">
                    <i class="material-icons" style="font-size:40px; display:block; margin-bottom:8px;">inbox</i>
                    No hay registros de consumibles todavía.
                    <a href="{{ route('consumibles.cargar') }}" style="color:#0067b1; font-weight:600;">Cargar el primer lote →</a>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <div style="margin-top:20px;">
        {{ $consumibles->links('pagination::bootstrap-4') }}
    </div>
</div>

</div>
@endsection

<script>
function eliminarConsumible(id, url) {
    if (!confirm('¿Eliminar este registro?')) return;

    const btn = document.querySelector(`button[onclick*="eliminarConsumible(${id},"]`);
    if (btn) { btn.disabled = true; btn.style.opacity = '0.4'; }

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept':       'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            const row = btn ? btn.closest('tr') : null;
            if (row) {
                row.style.transition = 'opacity .25s';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 250);
            }
        } else {
            alert('No se pudo eliminar el registro.');
            if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
        }
    })
    .catch(() => {
        alert('Error de red al intentar eliminar.');
        if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
    });
}
</script>



