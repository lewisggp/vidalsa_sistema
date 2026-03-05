@extends('layouts.estructura_base')
@section('title', 'Gráficos de Consumibles')

@section('content')
<style>

    .g-grid-2  { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; }
    .g-grid-1  { margin-bottom:20px; }
    .g-card    { background:#fff; border-radius:16px; padding:25px;
                 box-shadow:0 2px 8px rgba(0,0,0,.06), 0 8px 24px rgba(0,0,0,.06); }
    .g-title   { font-size:14px; font-weight:700; color:#1e293b; margin:0 0 16px 0; display:flex; align-items:center; gap:8px; }
    .g-title i { font-size:18px; color:#0067b1; }
    .g-subtitle{ font-size:11px; color:#94a3b8; font-weight:400; margin-left:4px; }

    .btn-green     { background:linear-gradient(135deg,#059669,#047857); }

    /* Tarjetas resumen */
    .resumen-grid { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:20px; }
    .resumen-card { flex:1; min-width:150px; background:linear-gradient(135deg,#1e293b,#0f172a);
                    border-radius:14px; padding:16px 18px; color:#fff; }
    /* Tarjetas resumen — estilos internos manejados por JS */

    /* Ranking frentes — grid de 4 columnas, valor auto-ancho para alinear */
    .frow { display:grid;
            grid-template-columns: 24px 190px 1fr auto;
            align-items:center;
            gap:8px;
            padding:7px 0;
            border-bottom:1px solid #f1f5f9; }
    .frow:last-child { border:none; }
    .frow-num  { font-size:11px; color:#94a3b8; font-weight:700;
                 text-align:center; }
    .frow-name { font-weight:700; font-size:12px; color:#1e293b;
                 word-break:break-word; line-height:1.3; }
    .frow-bar-wrap { background:#f1f5f9; border-radius:20px;
                     height:10px; overflow:hidden; }
    .frow-bar  { height:100%; border-radius:20px; transition:width .6s; }
    .frow-val  { text-align:left; font-weight:800; font-size:13px;
                 color:#003a70; white-space:nowrap; padding-left:4px; }
    .frow-dep  { display:block; font-size:11px; font-weight:700;
                 color:#0077cc; margin-top:1px; }

    /* Grid de tarjetas de equipos */
    .eq-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(190px, 1fr)); gap:10px; }
    .eq-card { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;
               padding:12px 14px; transition:box-shadow .2s; }
    .eq-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.08); }
    .eq-tipo { font-size:10px; font-weight:700; color:#0067b1; text-transform:uppercase;
               letter-spacing:.4px; margin-bottom:2px; }
    .eq-modelo { font-size:12px; font-weight:700; color:#1e293b; margin-bottom:8px;
                 white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .eq-total { font-size:20px; font-weight:800; color:#003a70; line-height:1; }
    .eq-unidad{ font-size:10px; color:#94a3b8; }
    .eq-desp  { font-size:12px; font-weight:700; color:#0077cc; margin-top:5px; }
    .eq-bar   { margin-top:8px; background:#e2e8f0; border-radius:20px; height:4px; overflow:hidden; }
    .eq-bar-fill { height:100%; border-radius:20px; transition:width .6s; }
    .eq-frente { font-size:11px; color:#475569; font-weight:600; margin-bottom:6px;
                 white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

    .loading-overlay { display:flex; align-items:center; justify-content:center; height:180px;
                       color:#94a3b8; font-size:13px; gap:8px; }
    @keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }

    /* Tabla de todos los equipos */
    .eq-desp-badge { display:inline-flex; align-items:center; gap:4px; background:#003a70;
                     color:#fff; border-radius:20px; padding:2px 10px; font-size:12px; font-weight:700; }
    .eq-search { width:100%; padding:9px 12px; border:1px solid #cbd5e0; border-radius:10px;
                 font-size:13px; margin-bottom:14px; outline:none; }
    .eq-search:focus { border-color:#0067b1; }
</style>

{{-- HEADER --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 class="page-title" style="margin-bottom:4px;">
            <span class="page-title-line2" style="color:#000;">Análisis de Consumibles</span>
        </h1>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a href="{{ route('consumibles.index') }}" class="btn-secondary">
            <i class="material-icons" style="font-size:17px;">list</i> Registros
        </a>
        <button class="btn-primary-maquinaria btn-green" onclick="descargarCsv()">
            <i class="material-icons" style="font-size:17px;">download</i> Exportar CSV
        </button>
    </div>
</div>

<div class="admin-card" style="box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 20px 25px; margin-bottom: 20px;">
{{-- FILTROS --}}
<div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
    <div style="flex: 2; min-width: 200px;">
        <div class="custom-dropdown" id="frenteFilterSelect" data-filter-type="frente" data-default-label="Todos los frentes">
            <input type="hidden" id="fFrente" data-filter-value value="">

            <div class="dropdown-trigger" style="padding:0; display:flex; align-items:center; background:#fbfcfd; overflow:hidden; border:1px solid #cbd5e0; border-radius:10px; height:42px;">
                <div style="padding:0 10px; display:flex; align-items:center; color:var(--maquinaria-gray-text, #94a3b8);">
                    <i class="material-icons" style="font-size:18px;">search</i>
                </div>
                <input type="text" data-filter-search
                    placeholder="Todos los frentes"
                    style="width:100%; border:none; background:transparent; padding:0 5px; font-size:13px; outline:none; height:100%;"
                    oninput="window.filterDropdownOptions(this)"
                    autocomplete="off">
                <i class="material-icons" data-clear-btn
                   style="padding:0 5px; color:var(--maquinaria-gray-text, #94a3b8); font-size:18px; display:none; cursor:pointer;"
                   onclick="event.stopPropagation(); window.clearDropdownFilter('frenteFilterSelect'); setTimeout(cargarDatos, 50);">close</i>
            </div>

            <div class="dropdown-content" style="padding:5px; max-height:none; overflow:visible; z-index:1000;">
                <div class="dropdown-item-list" style="max-height:250px; overflow-y:auto;">
                    <div class="dropdown-item selected"
                         data-value=""
                         onclick="window.selectOption('frenteFilterSelect', '', 'Todos los frentes'); cargarDatos();">
                        Todos los frentes
                    </div>
                    @foreach($frentes as $f)
                        <div class="dropdown-item"
                             data-value="{{ $f->ID_FRENTE }}"
                             onclick="window.selectOption('frenteFilterSelect', '{{ $f->ID_FRENTE }}', '{{ $f->NOMBRE_FRENTE }}'); cargarDatos();">
                            {{ $f->NOMBRE_FRENTE }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div style="flex: 1.5; min-width: 140px;">
        <select id="fTipo" onchange="cargarDatos()" style="width:100%; height:42px; border-radius:10px; border:1px solid #cbd5e0; background:#fbfcfd; outline:none; padding:0 12px; font-size:13px; color:#1e293b;">
            <option value="">Todos los tipos</option>
            @foreach(\App\Models\Consumible::tiposLabel() as $v => $l)
                <option value="{{ $v }}">{{ $l }}</option>
            @endforeach
        </select>
    </div>

    <div style="display:flex; gap:8px;">
        <input type="date" id="fDesde" title="Desde" style="width: 100%; height:42px; border-radius:10px; border:1px solid #cbd5e0; background:#fbfcfd; outline:none; padding:0 12px; font-size:13px; color:#1e293b;">
        <input type="date" id="fHasta" title="Hasta" style="width: 100%; height:42px; border-radius:10px; border:1px solid #cbd5e0; background:#fbfcfd; outline:none; padding:0 12px; font-size:13px; color:#1e293b;">
    </div>

    <div style="flex: 0 0 auto;">
        <button class="btn-primary-maquinaria" onclick="cargarDatos()" style="height:42px; display:flex; align-items:center; padding:0 20px; border-radius:10px;">
            <i class="material-icons" style="font-size:17px; margin-right:5px;">refresh</i> Aplicar
        </button>
    </div>
</div>
</div>

{{-- TARJETAS RESUMEN --}}
<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:12px;">
    <span style="font-size:14px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px;">
        <i class="material-icons" style="color:#0067b1; font-size:18px;">analytics</i>
        Resumen General
    </span>
    <button onclick="descargarPanelResumen('resumen_general')" title="Descargar imagen" style="border:none;background:transparent;cursor:pointer;color:#94a3b8;display:flex;align-items:center;padding:4px 8px;border-radius:8px;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
        <i class="material-icons" style="font-size:17px;">photo_camera</i>
    </button>
</div>
<div class="resumen-grid" id="resumenGrid">
    <div class="loading-overlay" style="width:100%;">
        <i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i> Cargando...
    </div>
</div>

{{-- TOTAL POR FRENTE --}}
<div class="g-grid-1">
    <div class="g-card" id="panelTotalFrente">
        <p class="g-title" style="justify-content:space-between;">
            <span style="display:flex;align-items:center;gap:8px;">
                <i class="material-icons">bar_chart</i>
                <span id="tituloTotalFrente">Total de Consumo por Frente</span>
            </span>
            <button onclick="descargarPanelHtml('panelTotalFrente','consumo_por_frente')" title="Descargar imagen" style="border:none;background:transparent;cursor:pointer;color:#94a3b8;display:flex;align-items:center;padding:4px 8px;border-radius:8px;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                <i class="material-icons" style="font-size:17px;">photo_camera</i>
            </button>
        </p>
        <div id="loadingTotalFrente" class="loading-overlay">
            <i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i>
        </div>
        <div id="totalFrenteBody" style="display:none;"></div>
    </div>
</div>

{{-- EQUIPOS ASIGNADOS POR FRENTE --}}
<div class="g-grid-1">
    <div class="g-card" id="panelEqAsig">
        <p class="g-title" style="justify-content:space-between;">
            <span style="display:flex;align-items:center;gap:8px;">
                <i class="material-icons" style="color:#64748b;">directions_bus</i>
                <span>Equipos Asignados por Frente <span class="g-subtitle">— flota actual en cada frente</span></span>
            </span>
            <button onclick="descargarPanelEquipos('equipos_asignados')" title="Descargar imagen" style="border:none;background:transparent;cursor:pointer;color:#94a3b8;display:flex;align-items:center;padding:4px 8px;border-radius:8px;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                <i class="material-icons" style="font-size:17px;">photo_camera</i>
            </button>
        </p>
        <div id="loadingEqAsig" class="loading-overlay">
            <i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i>
        </div>
        <div id="eqAsigBody" style="display:none;"></div>
    </div>
</div>

{{-- BARRAS POR TIPO --}}
<div class="g-grid-1">
    <div class="g-card">
        <p class="g-title" style="justify-content:space-between;">
            <span style="display:flex;align-items:center;gap:8px;">
                <i class="material-icons">stacked_bar_chart</i>
                Por Frente Desglosado por Tipo
            </span>
            <button onclick="descargarGrafico('chartFrente','consumo_por_frente')" title="Descargar imagen" style="border:none;background:transparent;cursor:pointer;color:#94a3b8;display:flex;align-items:center;padding:4px 8px;border-radius:8px;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                <i class="material-icons" style="font-size:17px;">photo_camera</i>
            </button>
        </p>
        <div id="loadingFrente" class="loading-overlay">
            <i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i>
        </div>
        <canvas id="chartFrente" style="display:none; max-height:300px;"></canvas>
    </div>
</div>


{{-- CONSUMO POR TIPO DE EQUIPO × FRENTE --}}
<div class="g-grid-1">
    <div class="g-card">
        <p class="g-title" style="justify-content:space-between;">
            <span style="display:flex;align-items:center;gap:8px;">
                <i class="material-icons">directions_car</i>
                Consumo por Tipo de Equipo y Frente
                <span class="g-subtitle">— una barra por tipo · solo equipos identificados</span>
            </span>
            <button onclick="descargarGrafico('chartTipoEq','consumo_tipo_equipo')" title="Descargar imagen" style="border:none;background:transparent;cursor:pointer;color:#94a3b8;display:flex;align-items:center;padding:4px 8px;border-radius:8px;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                <i class="material-icons" style="font-size:17px;">photo_camera</i>
            </button>
        </p>
        <div id="loadingTipoEq" class="loading-overlay">
            <i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i>
        </div>
        <canvas id="chartTipoEq" style="display:none; max-height:320px;"></canvas>
    </div>
</div>

{{-- EQUIPOS QUE SURTIERON POR FRENTE (solo con frente seleccionado) --}}
<div class="g-grid-1" id="secEqFrente" style="display:none;">
    <div class="g-card">
        <p class="g-title" style="justify-content:space-between;">
            <span style="display:flex;align-items:center;gap:8px;">
                <i class="material-icons">agriculture</i>
                Equipos que Surtieron
                <span class="g-subtitle" id="subtitleEqFrente">— selecciona un frente para ver los equipos</span>
            </span>
            <button onclick="descargarGrafico('chartEqFrente','equipos_surtieron')" title="Descargar imagen" style="border:none;background:transparent;cursor:pointer;color:#94a3b8;display:flex;align-items:center;padding:4px 8px;border-radius:8px;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                <i class="material-icons" style="font-size:17px;">photo_camera</i>
            </button>
        </p>
        <div id="loadingEqFrente" class="loading-overlay">
            <i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i>
        </div>
        <canvas id="chartEqFrente" style="display:none;"></canvas>
    </div>
</div>

{{-- TOP EQUIPOS — GRID DE TARJETAS --}}
<div class="g-grid-1">
    <div class="g-card">
        <p class="g-title" style="justify-content:space-between;">
            <span style="display:flex;align-items:center;gap:8px;">
                <i class="material-icons">emoji_events</i>
                Top 15 Equipos Mayor Consumo
                <span class="g-subtitle">— total · nº de despachos</span>
            </span>
            <button onclick="descargarRanking()" title="Descargar imagen" style="border:none;background:transparent;cursor:pointer;color:#94a3b8;display:flex;align-items:center;padding:4px 8px;border-radius:8px;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                <i class="material-icons" style="font-size:17px;">photo_camera</i>
            </button>
        </p>
        <div id="loadingRanking" class="loading-overlay">
            <i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i>
        </div>
        <div id="rankingBody" style="display:none;"></div>
    </div>
</div>

{{-- EQUIPOS INOPERATIVOS EN FRENTE --}}
<div class="g-grid-1" id="secInoperativos" style="display:none;">
    <div class="g-card">
        <p class="g-title" style="justify-content:space-between;">
            <span style="display:flex;align-items:center;gap:8px;">
                <i class="material-icons" style="color:#ef4444;">build_circle</i>
                Equipos Inoperativos — Frente Seleccionado
                <span class="g-subtitle">— registra la causa de inoperatividad</span>
            </span>
            <button onclick="descargarPanelInoperativos()" title="Descargar imagen" style="border:none;background:transparent;cursor:pointer;color:#94a3b8;display:flex;align-items:center;padding:4px 8px;border-radius:8px;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                <i class="material-icons" style="font-size:17px;">photo_camera</i>
            </button>
        </p>
        <div id="loadingInoperativos" class="loading-overlay">
            <i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i>
        </div>
        <div id="inoperativosBody" style="display:none;"></div>
    </div>
</div>

{{-- TOTAL ABSOLUTO POR ESPECIFICACION (ACEITE/CAUCHO) --}}
<div class="g-grid-1" id="secTotalEspec" style="display:none; margin-bottom: 24px;">
    <div class="g-card">
        <p class="g-title" id="titleTotalEspec">
            <i class="material-icons" id="iconTotalEspec" style="color:#0067b1;">pie_chart</i>
            <span id="txtTotalEspec">Consumo Total por Especificación</span>
            <span class="g-subtitle">— global acumulado</span>
        </p>
        <div id="loadingTotalEspec" class="loading-overlay">
            <i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i>
        </div>
        <div id="totalEspecBody" style="display:none; flex-wrap:wrap; align-items:center; padding: 10px 0;"></div>
    </div>
</div>

{{-- ESPECIFICACION POR FRENTE --}}
<div class="g-grid-1" id="secEspecFrente" style="display:none;">
    <div class="g-card">
        <p class="g-title" id="titleEspecFrente" style="justify-content:space-between;">
            <span style="display:flex;align-items:center;gap:8px;">
                <i class="material-icons" id="iconEspecFrente" style="color:#0067b1;">opacity</i>
                <span><span id="txtEspecFrente">Consumo por Frente — Desglose por Especificación</span> <span class="g-subtitle">— todos los registros</span></span>
            </span>
            <button onclick="descargarPanelEspecFrente('desglose_especificacion')" title="Descargar imagen" style="border:none;background:transparent;cursor:pointer;color:#94a3b8;display:flex;align-items:center;padding:4px 8px;border-radius:8px;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                <i class="material-icons" style="font-size:17px;">photo_camera</i>
            </button>
        </p>
        <div id="loadingAceiteFrente" class="loading-overlay">
            <i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i>
        </div>
        <div id="aceiteFrente-body" style="display:none;"></div>
    </div>
</div>

{{-- ESPECIFICACION POR EQUIPO --}}
<div class="g-grid-1" id="secEspecEquipo" style="display:none;">
    <div class="g-card">
        <p class="g-title" id="titleEspecEquipo">
            <i class="material-icons" id="iconEspecEquipo" style="color:#0067b1;">settings</i>
            <span id="txtEspecEquipo">Consumo por Equipo — Desglose por Especificación</span>
            <span class="g-subtitle">— solo equipos identificados</span>
        </p>
        <div id="loadingAceiteEquipo" class="loading-overlay">
            <i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i>
        </div>
        <div id="aceiteEquipoBody" style="display:none; overflow-x:auto;"></div>
    </div>
</div>

{{-- CAUCHOS POR TIPO DE EQUIPO Y MEDIDA --}}
<div class="g-grid-1" id="secCauchoModelo">
    <div class="g-card">
        <p class="g-title" style="justify-content:space-between;">
            <span style="display:flex;align-items:center;gap:8px;">
                <i class="material-icons" style="color:#1b5e20;">tire_repair</i>
                Cauchos por Tipo de Equipo y Medida
                <span class="g-subtitle">— unidades reemplazadas · solo equipos identificados</span>
            </span>
            <button onclick="descargarGrafico('chartCauchoModelo','cauchos_por_modelo')" title="Descargar imagen" style="border:none;background:transparent;cursor:pointer;color:#94a3b8;display:flex;align-items:center;padding:4px 8px;border-radius:8px;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                <i class="material-icons" style="font-size:17px;">photo_camera</i>
            </button>
        </p>
        <div id="loadingCauchoModelo" class="loading-overlay">
            <i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i>
        </div>
        <canvas id="chartCauchoModelo" style="display:none; max-height:340px;"></canvas>
    </div>
</div>

{{-- TODOS LOS EQUIPOS — TABLA COMPLETA --}}
<div class="g-grid-1">
    <div class="g-card">
        <p class="g-title">
            <i class="material-icons">table_chart</i>
            Despachos por Equipo — Todos
            <span class="g-subtitle" id="subtotalEquipos"></span>
        </p>
        <input class="eq-search" id="eqSearch" type="text" placeholder="Buscar placa, marca, modelo, tipo..." oninput="filtrarTablaEquipos(this.value)">
        <div id="loadingTodosEq" class="loading-overlay">
            <i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i>
        </div>
        <div style="overflow-x:auto; display:none;" id="wrapTodosEq">
            <table class="admin-table" id="tablaEquipos">
                <thead>
                    <tr>
                        <th class="sortable" onclick="sortTabla(0)">Tipo ▵</th>
                        <th class="sortable" onclick="sortTabla(1)">Identificador(es)</th>
                        <th class="sortable" onclick="sortTabla(2)">Marca / Modelo</th>
                        <th class="sortable" onclick="sortTabla(3)" style="text-align:right;">Despachos ▾</th>
                        <th class="sortable" onclick="sortTabla(4)" style="text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody id="bodyTodosEq"></tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
Chart.register(ChartDataLabels);
// Paleta corporativa: variada y profunda
const COLORES = {
    'GASOIL':       '#003a70',   // azul marino
    'GASOLINA':     '#c41c00',   // rojo intenso
    'ACEITE':       '#0077cc',   // azul eléctrico
    'CAUCHO':       '#1b5e20',   // verde oscuro
    'REFRIGERANTE': '#00838f',   // teal
    'OTRO':         '#546e7a',   // gris azulado
};
const TIPO_LABEL = {
    'GASOIL':'Gasoil','GASOLINA':'Gasolina','ACEITE':'Aceite',
    'CAUCHO':'Caucho','REFRIGERANTE':'Refrigerante','OTRO':'Otro'
};

let chartFrente = null, chartTipoEq = null, chartEqFrente = null, chartCauchoModelo = null;

function getParams() {
    const p = new URLSearchParams();
    const frente = document.getElementById('fFrente').value;
    const tipo   = document.getElementById('fTipo').value;
    const desde  = document.getElementById('fDesde').value;
    const hasta  = document.getElementById('fHasta').value;
    if (frente) p.set('id_frente', frente);
    if (tipo)   p.set('tipo', tipo);
    if (desde)  p.set('desde', desde);
    if (hasta)  p.set('hasta', hasta);
    return p;
}

function show(id)   { document.getElementById(id).style.display = ''; }
function hide(id)   { document.getElementById(id).style.display = 'none'; }
function canvas(id) { document.getElementById(id).style.display = 'block'; }

function cargarDatos() {
    const params = getParams();

    // ── Loading: mostrar spinners de las secciones siempre visibles ──
    ['loadingTotalFrente','loadingEqAsig','loadingFrente','loadingTipoEq',
     'loadingRanking','loadingTodosEq','loadingCauchoModelo', 'loadingInoperativos'].forEach(show);

    // ── Ocultar contenido previo (prev carga) ────────────────────────
    ['chartFrente','chartTipoEq','totalFrenteBody','eqAsigBody',
     'rankingBody','wrapTodosEq','chartCauchoModelo', 'inoperativosBody'].forEach(hide);

    // ── Secciones de especificación: ocultar antes de cada carga ─────
    // Evita que queden datos viejos visibles durante la nueva carga.
    hide('secTotalEspec');
    hide('secEspecFrente');
    hide('secEspecEquipo');
    hide('secInoperativos');
    hide('totalEspecBody');
    hide('aceiteFrente-body');
    hide('aceiteEquipoBody');

    document.getElementById('resumenGrid').innerHTML =
        '<div class="loading-overlay" style="width:100%;"><i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i> Cargando...</div>';

    fetch(`{{ route('consumibles.graficosData') }}?${params}`)
        .then(r => r.json())
        .then(data => {
            const frenteSeleccionado = document.getElementById('fFrente').value;
            renderResumen(data.resumen);
            renderTotalFrente(data.por_frente);
            // ── Orden de frentes según gráfico de consumo (mayor a menor) ────────
            // 'let' porque luego insertamos AMBIENTE en la posición que le corresponde.
            let ordenFrente = (data.por_frente || [])
                .map(d => d.NOMBRE_FRENTE)
                .filter((v, i, a) => a.indexOf(v) === i);  // únicos, en orden consumo

            // ── AMBIENTE en posición #3, excepto cuando se filtra por CAUCHO o ACEITE ──
            const tipoFiltro = document.getElementById('fTipo').value;
            const tiposSinAmbiente = ['CAUCHO', 'ACEITE'];
            if (data.equipos_asignados && data.equipos_asignados['AMBIENTE']
                && !tiposSinAmbiente.includes(tipoFiltro)) {
                ordenFrente.splice(2, 0, 'AMBIENTE');
            }

            window.renderEquiposAsignados(data.equipos_asignados || {}, ordenFrente);
            renderFrente(data.por_frente);
            renderTipoEquipo(data.por_tipo_equipo);
            // Solo mostrar equipos×frente cuando hay un frente específico seleccionado
            if (frenteSeleccionado) {
                document.getElementById('secEqFrente').style.display = '';
                renderEquiposPorFrente(data.equipos_por_frente, frenteSeleccionado);
            } else {
                document.getElementById('secEqFrente').style.display = 'none';
            }
            if (frenteSeleccionado) {
                document.getElementById('secInoperativos').style.display = '';
                const fname = (data.inoperativos && data.inoperativos.length > 0) 
                              ? data.inoperativos[0].frente_nombre 
                              : document.querySelector('#frenteFilterSelect [data-filter-search]')?.placeholder;
                renderInoperativos(data.inoperativos || [], fname || 'Frente Seleccionado');
            } else {
                document.getElementById('secInoperativos').style.display = 'none';
            }
            renderRanking(data.top_equipos);
            renderCauchosPorModelo(data.cauchos_por_modelo);
            renderTodosEquipos(data.todos_equipos);
            renderTotalEspec(data.espec_frente, data.tipo_activo);
            renderEspecFrente(data.espec_frente,  data.tipo_activo);
            renderEspecEquipo(data.espec_equipo, data.tipo_activo);
        })
        .catch(err => {
            console.error('Error cargando datos de gráficos:', err);
            ['loadingTotalFrente','loadingEqAsig','loadingFrente','loadingTipoEq',
             'loadingRanking','loadingTodosEq','loadingCauchoModelo', 'loadingInoperativos'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = '<span style="color:#ef4444;">Error al cargar datos</span>';
            });
        });
}

// ── Equipos asignados por frente — cajitas numeradas en orden de consumo ───
window.renderEquiposAsignados = function(eqAsig, ordenFrente) {
    hide('loadingEqAsig');
    const body = document.getElementById('eqAsigBody');
    body.style.display = 'block';

    // Filtrar solo los frentes que tienen equipos asignados, manteniendo el orden de consumo.
    // AMBIENTE ya viene inserado en la pos correcta por cargarDatos().
    const lista = (ordenFrente || [])
        .filter(frente => eqAsig[frente])          // solo frentes con equipos asignados
        .map(frente => ({
            frente,
            total: parseInt(eqAsig[frente]?.total_asignados || 0),
        }));

    if (!lista.length) {
        body.innerHTML = '<p style="color:#94a3b8;font-size:13px;text-align:center;padding:20px;">Sin datos de equipos asignados.</p>';
        return;
    }

    const GRIS_FIJO = '#475569';

    // i+1 sobre la lista YA FILTRADA → siempre secuencial sin saltos
    body.innerHTML = `<div style="display:flex;flex-wrap:wrap;gap:10px;">${
        lista.map((row, i) => `
            <div style="
                background:${GRIS_FIJO};
                color:#fff;
                border-radius:12px;
                padding:12px 16px;
                min-width:200px;
                flex:1;
                display:flex;
                flex-direction:column;
                align-items:flex-start;
                justify-content:center;
                gap:8px;
                box-shadow:0 2px 8px rgba(0,0,0,.15);
            ">
                <div style="display:flex; align-items:center; gap:8px; width:100%;">
                    <span style="font-size:13px;font-weight:700;color:#94a3b8;">#${i + 1}</span>
                    <span style="font-size:12px;font-weight:700;line-height:1.2;word-break:break-word;" title="${row.frente}">${row.frente}</span>
                </div>
                <div style="display:flex;align-items:baseline;gap:5px;">
                    <span style="font-size:26px;font-weight:900;line-height:1;">${row.total}</span>
                    <span style="font-size:13px;font-weight:600;opacity:.9;">equipo${row.total!==1?'s':''}</span>
                </div>
            </div>`
        ).join('')
    }</div>`;
};

// Exponer cargarDatos globalmente para el ModuleManager SPA
window.cargarDatos = cargarDatos;

// ── Tarjetas resumen ───────────────────────────────────────────────
function renderResumen(datos) {
    const grid = document.getElementById('resumenGrid');
    if (!datos || datos.length === 0) {
        grid.innerHTML = '<p style="color:#94a3b8;font-size:13px;padding:10px;">Sin datos con los filtros aplicados.</p>';
        return;
    }
    grid.innerHTML = datos.map(d => {
        return `
        <div class="resumen-card">
            <div style="display:flex; align-items:baseline; gap:8px; flex-wrap:wrap; line-height:1.2;">
                <span style="font-size:28px; font-weight:800; color:#fff;">
                    ${parseFloat(d.total).toLocaleString('es-VE',{minimumFractionDigits:0,maximumFractionDigits:1})}
                </span>
                <span style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px;">
                    ${d.unidad} · ${TIPO_LABEL[d.TIPO_CONSUMIBLE] || d.TIPO_CONSUMIBLE}
                </span>
            </div>
            <div style="font-size:11px; color:#64748b; margin-top:6px;">
                ${d.registros} despachos · ${d.equipos_distintos} eq
            </div>
        </div>`;
    }).join('');
}

// ── Total por frente (barras horizontales) ───────────────────────────────
function renderTotalFrente(datos) {
    const searchInput = document.querySelector('#frenteFilterSelect [data-filter-search]');
    const nombreFrente = searchInput ? searchInput.placeholder : '';
    const hayFrenteSeleccionado = !!document.getElementById('fFrente').value;
    const tituloEl = document.getElementById('tituloTotalFrente');
    if (tituloEl) {
        tituloEl.textContent = hayFrenteSeleccionado
            ? `Consumo — ${nombreFrente}`
            : 'Total de Consumo por Frente';
    }
    hide('loadingTotalFrente');
    const body = document.getElementById('totalFrenteBody');
    body.style.display = 'block';
    if (!datos || datos.length === 0) {
        body.innerHTML = '<p style="color:#94a3b8;font-size:13px;text-align:center;padding:20px;">Sin datos.</p>';
        return;
    }


    const mapaTotal     = {};
    const mapaDespachos = {};
    const mapaUnidad    = {};
    datos.forEach(d => {
        mapaTotal[d.NOMBRE_FRENTE]     = (mapaTotal[d.NOMBRE_FRENTE]     || 0) + parseFloat(d.total);
        mapaDespachos[d.NOMBRE_FRENTE] = (mapaDespachos[d.NOMBRE_FRENTE] || 0) + parseInt(d.despachos || 0);
        if (!mapaUnidad[d.NOMBRE_FRENTE]) mapaUnidad[d.NOMBRE_FRENTE] = d.unidad || '';
    });
    const ordenado = Object.entries(mapaTotal).sort((a, b) => b[1] - a[1]);
    const maxVal   = ordenado[0]?.[1] || 1;
    const n = ordenado.length;

    body.innerHTML = ordenado.map(([frente, total], i) => {
        const pct    = (total / maxVal * 100).toFixed(1);
        const dep    = mapaDespachos[frente] || 0;
        const unidad = mapaUnidad[frente] || '';
        let color;
        if (i === 0) {
            color = 'linear-gradient(90deg,#7f1d1d,#b91c1c)';
        } else if (i === 1) {
            color = 'linear-gradient(90deg,#b91c1c,#ef4444)';
        } else {
            const blueIdx = i - 2;
            const blueN   = Math.max(n - 2, 1);
            const lightA  = Math.round(22 + (28 * blueIdx / blueN));
            const lightB  = Math.round(32 + (22 * blueIdx / blueN));
            const satA    = Math.round(82 - (18 * blueIdx / blueN));
            color = `linear-gradient(90deg,hsl(213,${satA}%,${lightA}%),hsl(213,${satA-5}%,${lightB}%))`;
        }
        return `
        <div class="frow">
            <span class="frow-num">#${i+1}</span>
            <span class="frow-name" title="${frente}">${frente}</span>
            <div class="frow-bar-wrap">
                <div class="frow-bar" style="width:${pct}%; background:${color};"></div>
            </div>
            <span class="frow-val">
                ${total.toLocaleString('es-VE',{minimumFractionDigits:0,maximumFractionDigits:1})} ${unidad}
                <span class="frow-dep">&#9981; ${dep} llenado${dep!==1?'s':''}</span>
            </span>
        </div>`;
    }).join('');
}




// ── Barras por tipo por frente ─────────────────────────────────────
function renderFrente(datos) {
    if (!datos || datos.length === 0) {
        const el = document.getElementById('loadingFrente');
        el.innerHTML = '<span style="color:#94a3b8;font-size:13px;">Sin datos para mostrar.</span>';
        el.style.display = 'flex';
        return;
    }
    hide('loadingFrente');
    canvas('chartFrente');
    const frentes = [...new Set(datos.map(d => d.NOMBRE_FRENTE))];
    const tipos   = [...new Set(datos.map(d => d.TIPO_CONSUMIBLE))];
    const datasets = tipos.map(tipo => ({
        label: TIPO_LABEL[tipo] || tipo,
        data: frentes.map(f => {
            const row = datos.find(d => d.NOMBRE_FRENTE === f && d.TIPO_CONSUMIBLE === tipo);
            return row ? parseFloat(row.total) : 0;
        }),
        backgroundColor: COLORES[tipo] || '#94a3b8',
        borderRadius: 5, borderSkipped: false,
    }));
    if (chartFrente) chartFrente.destroy();
    chartFrente = new Chart(document.getElementById('chartFrente'), {
        type: 'bar',
        data: { labels: frentes, datasets },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                datalabels: {
                    anchor:  'center',
                    align:   'center',
                    color:   '#fff',
                    font:    { size: 11, weight: '700' },
                    // Mostrar valor solo si la barra es suficientemente alta
                    formatter: v => v > 0 ? v.toLocaleString('es-VE', {maximumFractionDigits:0}) : '',
                    display: ctx => ctx.dataset.data[ctx.dataIndex] > 0,
                },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const tipo = tipos[ctx.datasetIndex];
                            const row  = datos.find(d => d.NOMBRE_FRENTE === frentes[ctx.dataIndex] && d.TIPO_CONSUMIBLE === tipo);
                            const u    = row?.unidad || '';
                            const dep  = row?.despachos || 0;
                            return [
                                ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('es-VE')} ${u}`,
                                ` ⛽ ${dep} llenado${dep !== 1 ? 's' : ''} de tanque`,
                            ];
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display:false } },
                y: { beginAtZero:true, grid: { color:'#f1f5f9' } }
            }
        }
    });
}


// ── Consumo por tipo de equipo (barra por tipo, total sumado) ───────
function renderTipoEquipo(datos) {
    const loadEl = document.getElementById('loadingTipoEq');
    if (!datos || datos.length === 0) {
        loadEl.innerHTML = '<span style="color:#94a3b8;font-size:13px;">Sin equipos identificados para mostrar.</span>';
        loadEl.style.display = 'flex';
        return;
    }
    loadEl.style.display = 'none';
    // Agrupar por tipo_equipo sumando totales de todos los frentes
    const mapaTotal    = {};
    const mapaDespachos = {};
    const mapaUnidad   = {};
    datos.forEach(d => {
        mapaTotal[d.tipo_equipo]     = (mapaTotal[d.tipo_equipo]     || 0) + parseFloat(d.total);
        mapaDespachos[d.tipo_equipo] = (mapaDespachos[d.tipo_equipo] || 0) + parseInt(d.despachos || 0);
        mapaUnidad[d.tipo_equipo]    = d.unidad;
    });
    // Ordenar de mayor a menor
    const ordenado = Object.entries(mapaTotal).sort((a, b) => b[1] - a[1]);
    const PALETA_EQ = ['#003a70','#c41c00','#0077cc','#7b1fa2','#e65100','#1b5e20','#00838f','#546e7a','#f57f17','#4a148c'];

    document.getElementById('chartTipoEq').style.display = 'block';
    if (chartTipoEq) chartTipoEq.destroy();
    chartTipoEq = new Chart(document.getElementById('chartTipoEq'), {
        type: 'bar',
        data: {
            labels:   ordenado.map(([t]) => t),
            datasets: [{
                label: 'Consumo total',
                data:  ordenado.map(([, v]) => v),
                backgroundColor: ordenado.map((_, i) => PALETA_EQ[i % PALETA_EQ.length]),
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            layout: { padding: { top: 22 } },
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    color: '#1e293b',
                    font: { size: 10, weight: '700' },
                    formatter: v => v > 0 ? v.toLocaleString('es-VE', {maximumFractionDigits:0}) : '',
                    clip: false,
                },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const tipo = ordenado[ctx.dataIndex][0];
                            const dep  = mapaDespachos[tipo] || 0;
                            const u    = mapaUnidad[tipo] || '';
                            return [
                                ` ${ctx.parsed.y.toLocaleString('es-VE')} ${u}`,
                                ` ⛽ ${dep} despacho${dep !== 1 ? 's' : ''}`,
                            ];
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } }
            }
        }
    });
}

// ── Cauchos por Tipo de Equipo y Medida ─────────────────────────────────────
function renderCauchosPorModelo(datos) {
    const loadEl = document.getElementById('loadingCauchoModelo');
    const canvEl = document.getElementById('chartCauchoModelo');
    const secEl  = document.getElementById('secCauchoModelo');

    if (!datos || datos.length === 0) {
        secEl.style.display = 'none';   // oculta toda la sección si no hay cauchos
        return;
    }
    secEl.style.display = '';
    loadEl.style.display = 'none';
    canvEl.style.display = 'block';

    // Tipos de equipo únicos (eje X)
    const tipos   = [...new Set(datos.map(d => d.tipo_equipo))];
    // Medidas únicas (un dataset/color por medida)
    const medidas = [...new Set(datos.map(d => d.medida))];

    const PALETA_CAUCHO = [
        '#1b5e20','#2e7d32','#388e3c','#43a047','#66bb6a',
        '#a5d6a7','#004d40','#00695c','#00796b','#00897b',
    ];

    const datasets = medidas.map((medida, mi) => ({
        label: medida,
        data: tipos.map(tipo => {
            const row = datos.find(d => d.tipo_equipo === tipo && d.medida === medida);
            return row ? parseFloat(row.total) : 0;
        }),
        backgroundColor: PALETA_CAUCHO[mi % PALETA_CAUCHO.length],
        borderRadius: 4,
        borderSkipped: false,
    }));

    const mapaInfo = {};
    datos.forEach(d => { mapaInfo[`${d.tipo_equipo}||${d.medida}`] = d; });

    if (chartCauchoModelo) chartCauchoModelo.destroy();
    chartCauchoModelo = new Chart(canvEl, {
        type: 'bar',
        data: { labels: tipos, datasets },
        options: {
            responsive: true,
            layout: { padding: { top: 10 } },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { size: 11 }, boxWidth: 14, padding: 12 }
                },
                datalabels: {
                    anchor: 'center',
                    align: 'center',
                    color: '#fff',
                    font: { size: 10, weight: '700' },
                    formatter: v => v > 0 ? v.toLocaleString('es-VE', {maximumFractionDigits: 0}) : '',
                    display: ctx => ctx.dataset.data[ctx.dataIndex] > 0,
                },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const medida = medidas[ctx.datasetIndex];
                            const tipo   = tipos[ctx.dataIndex];
                            const info   = mapaInfo[`${tipo}||${medida}`];
                            const dep    = info?.despachos || 0;
                            const u      = info?.unidad    || 'Un';
                            return [
                                ` Medida: ${medida}`,
                                ` ${ctx.parsed.y.toLocaleString('es-VE')} ${u}`,
                                ` 🔧 ${dep} reemplazo${dep !== 1 ? 's' : ''}`,
                            ];
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { display: false },
                    ticks: { font: { size: 11, weight: '600' } }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    title: { display: true, text: 'Unidades', font: { size: 11 } }
                }
            }
        }
    });
}

// ── Equipos individuales que surtieron en el frente seleccionado ────
function renderEquiposPorFrente(datos, frenteId) {
    const loadEl  = document.getElementById('loadingEqFrente');
    const canvEl  = document.getElementById('chartEqFrente');

    // Actualizar subtítulo
    const searchInput = document.querySelector('#frenteFilterSelect [data-filter-search]');
    const nombre = searchInput ? searchInput.placeholder : 'Frente';
    document.getElementById('subtitleEqFrente').textContent = `— equipos en: ${nombre}`;

    // Siempre reset: mostrar spinner, ocultar canvas
    loadEl.innerHTML = '<i class="material-icons" style="animation:spin 1s linear infinite;">refresh</i>';
    loadEl.style.display = 'flex';
    canvEl.style.display = 'none';
    if (chartEqFrente) { chartEqFrente.destroy(); chartEqFrente = null; }

    if (!datos || datos.length === 0) {
        loadEl.innerHTML = '<span style="color:#94a3b8;font-size:13px;">Sin equipos identificados en este frente.</span>';
        return;
    }

    const PALETA_EQ = ['#003a70','#c41c00','#0077cc','#7b1fa2','#e65100','#1b5e20','#00838f','#546e7a','#f57f17','#4a148c'];

    // Agrupar por equipo individual — key: ID_EQUIPO para no mezclar equipos del mismo tipo
    const equiposMap = new Map();
    datos.forEach(d => {
        // Identificador para el tooltip: placa preferida, sino serial
        const idTooltip = (d.CODIGO_PATIO  && d.CODIGO_PATIO.trim())
                        ? d.CODIGO_PATIO.trim()
                        : (d.SERIAL_CHASIS && d.SERIAL_CHASIS.trim())
                        ? d.SERIAL_CHASIS.trim()
                        : '—';
        const key = d.ID_EQUIPO || idTooltip;
        if (!equiposMap.has(key)) {
            equiposMap.set(key, {
                tipo:      d.tipo_equipo || 'S/T',   // ← label en el eje X
                idLabel:   idTooltip,                 // ← placa o serial (tooltip)
                modelo:    d.MODELO || '',            // ← modelo (tooltip)
                total:     0,
                desp:      0,
                unidad:    d.unidad,
            });
        }
        const eq = equiposMap.get(key);
        eq.total += parseFloat(d.total);
        eq.desp  += parseInt(d.despachos || 0);
    });

    const equipos = [...equiposMap.values()];
    // Ordenar de mayor a menor consumo
    equipos.sort((a, b) => b.total - a.total);

    // Color consistente por tipo de equipo
    const tiposUnicos  = [...new Set(equipos.map(e => e.tipo))];
    const colorPorTipo = {};
    tiposUnicos.forEach((t, i) => { colorPorTipo[t] = PALETA_EQ[i % PALETA_EQ.length]; });

    // Si hay tipos repetidos, añadir sufijo ordinal (Camión #1, Camión #2…)
    const contadorIdx = {};
    const labelsFinal = equipos.map(e => {
        const total_tipo = equipos.filter(x => x.tipo === e.tipo).length;
        if (total_tipo === 1) return e.tipo;
        contadorIdx[e.tipo] = (contadorIdx[e.tipo] || 0) + 1;
        return `${e.tipo} #${contadorIdx[e.tipo]}`;
    });

    const values = equipos.map(e => e.total);
    const colors = equipos.map(e => colorPorTipo[e.tipo] || '#94a3b8');

    // Mostrar canvas y ocultar spinner
    loadEl.style.display = 'none';
    canvEl.style.removeProperty('height');
    canvEl.style.display = 'block';

    chartEqFrente = new Chart(canvEl, {
        type: 'bar',
        data: {
            labels: labelsFinal,
            datasets: [{
                label: nombre,
                data:  values,
                backgroundColor: colors,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            layout: { padding: { top: 22 } },
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'end', align: 'end',
                    color:  '#1e293b',
                    font:   { size: 10, weight: '700' },
                    formatter: v => v > 0 ? v.toLocaleString('es-VE', {maximumFractionDigits:1}) : '',
                    clip: false,
                },
                tooltip: {
                    callbacks: {
                        title: ctx => {
                            const eq = equipos[ctx[0].dataIndex];
                            return `🎦 ${eq.idLabel}`;
                        },
                        label: ctx => {
                            const eq = equipos[ctx.dataIndex];
                            return ` ⛽ ${eq.desp} despacho${eq.desp !== 1 ? 's' : ''}`;
                        },
                        afterLabel: () => undefined,
                    }
                }
            },
            scales: {
                x: {
                    grid:  { display: false },
                    ticks: { font: { size: 11, weight: '600' } }
                },
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } }
            }
        }
    });
}

// ── Equipos Inoperativos ────────────────────────────────────────────────
window.renderInoperativos = function(datos, frente) {
    const loadEl = document.getElementById('loadingInoperativos');
    if (loadEl) loadEl.style.display = 'none';

    const box = document.getElementById('inoperativosBody');
    box.style.display = 'block';

    if (!datos || datos.length === 0) {
        box.innerHTML = '<p style="color:#94a3b8;font-size:13px;text-align:center;">No hay equipos inoperativos registrados en este frente.</p>';
        return;
    }

    box.innerHTML = `<div class="eq-grid">${
        datos.map(d => {
            const placa = (d.PLACA && d.PLACA !== 'S/P' && d.PLACA !== '') ? d.PLACA : d.SERIAL_CHASIS;
            const fotoRow = d.FOTO_REFERENCIAL || d.FOTO_EQUIPO;
            let fotoHtml = '';
            if (fotoRow) {
                const driveId = fotoRow.replace(/^.*\/storage\/google\//, '').split('?')[0];
                fotoHtml = `<img src="/storage/google/${driveId}" style="width:100%;height:100%;object-fit:cover;">`;
            } else {
                fotoHtml = `<i class="material-icons" style="color:#cbd5e0;font-size:32px;">directions_car</i>`;
            }

            const total = d.total ? parseFloat(d.total).toLocaleString('es-VE') : '0';
            const despachos = d.despachos || 0;
            const unidad = d.unidad || 'LITROS';

            return `
            <div class="eq-card" style="border-left:4px solid #ef4444; background-color:#f2f2f2; display:flex; flex-direction:column; gap:6px; padding:10px 12px;">
                <div style="display:flex; gap:10px; align-items:flex-start;">
                    <div style="width:40px; height:40px; border-radius:6px; background:#e2e8f0; overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        ${fotoHtml}
                    </div>
                    <div style="overflow:hidden; flex:1; min-width:0;">
                        <div class="eq-tipo" style="font-size:11px;">${d.tipo}</div>
                        <div class="eq-modelo" style="font-size:12px;">${placa}</div>
                        <div style="font-size:10px; color:#475569; font-weight:600; margin-top:2px; display:flex; align-items:flex-start; gap:2px;">
                            <i class="material-icons" style="font-size:11px; flex-shrink:0; margin-top:1px;">place</i>
                            <span style="word-break:break-word; line-height:1.3;">${d.frente_nombre || frente}</span>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top:2px; border-top:1px dashed #cbd5e0; padding-top:6px; text-align:left;">
                    <div style="font-size:10px; color:#94a3b8; font-weight:700;">CAUSA DE INOPERATIVIDAD:</div>
                    <div style="width:100%; height:20px; border-bottom:1px solid #94a3b8;"></div>
                    <div style="width:100%; height:20px; border-bottom:1px solid #94a3b8;"></div>
                </div>
            </div>`;
        }).join('')
    }</div>`;
};

window.descargarPanelInoperativos = function() {
    capturaPanelHtml('secInoperativos', 'equipos_inoperativos');
};


// ── TOP EQUIPOS — Grid de tarjetas compactas ───────────────────────
let _rankingData = [];
function renderRanking(datos) {
    hide('loadingRanking');
    _rankingData = datos || [];
    const body = document.getElementById('rankingBody');
    body.style.display = 'block';
    if (!_rankingData.length) {
        body.innerHTML = '<p style="color:#94a3b8;font-size:13px;text-align:center;padding:20px;">Sin datos.</p>';
        return;
    }
    const maxVal = Math.max(..._rankingData.map(d => parseFloat(d.total)));
    body.innerHTML = `<div class="eq-grid">${
        _rankingData.map((d, i) => {
            const total = parseFloat(d.total);
            const pct   = maxVal > 0 ? (total / maxVal * 100).toFixed(1) : 0;
            const desp  = d.despachos || 0;
            const barColor = i === 0 ? '#003a70'
                           : i <= 2  ? '#0077cc'
                           : i <= 5  ? '#546e7a'
                           : '#9ca3af';

            // Identificador: Placa primero, sino Serial, sino Codigo, sino Modelo
            const idTexto = (d.PLACA && d.PLACA.trim()) ? d.PLACA :
                            (d.SERIAL_CHASIS && d.SERIAL_CHASIS.trim()) ? d.SERIAL_CHASIS :
                            (d.CODIGO_PATIO && d.CODIGO_PATIO.trim()) ? d.CODIGO_PATIO :
                            (d.MODELO || 'S/ID');

            const frenteTxt = d.frente ? `<div class="eq-frente">📍 ${d.frente}</div>` : '';

            return `
            <div class="eq-card">
                <div class="eq-tipo">${d.tipo || 'S/T'}</div>
                <div class="eq-modelo" title="${idTexto}" style="font-family:monospace; font-size:13px; letter-spacing:0.5px; color:#1e293b;">${idTexto}</div>
                ${frenteTxt}
                <div class="eq-total">
                    ${total.toLocaleString('es-VE',{minimumFractionDigits:0,maximumFractionDigits:1})}
                    <span class="eq-unidad">${d.unidad}</span>
                </div>
                <div class="eq-desp">⛽ ${desp} despacho${desp !== 1 ? 's' : ''}</div>
                <div class="eq-bar">
                    <div class="eq-bar-fill" style="width:${pct}%; background:${barColor};"></div>
                </div>
            </div>`;
        }).join('')
    }</div>`;
}

// ── DESCARGA el panel Top 15 como imagen PNG ──────────────────────────────
function descargarRanking() {
    if (!_rankingData || !_rankingData.length) {
        alert('No hay datos para descargar. Carga los gráficos primero.');
        return;
    }

    const DPR    = 2;
    const PAD    = 20;
    const COLS   = 3;
    const CARD_W = 240;
    const CARD_H = 122;
    const GAP    = 12;
    const TITLE_H = 50;
    const ROWS   = Math.ceil(_rankingData.length / COLS);
    const W      = PAD * 2 + COLS * CARD_W + (COLS - 1) * GAP;
    const H      = TITLE_H + ROWS * (CARD_H + GAP) + PAD;

    const cvs = document.createElement('canvas');
    cvs.width  = W * DPR;
    cvs.height = H * DPR;
    const ctx = cvs.getContext('2d');
    ctx.scale(DPR, DPR);

    // Fondo blanco
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, W, H);

    // Título
    ctx.fillStyle = '#1e293b';
    ctx.font = 'bold 14px system-ui, sans-serif';
    ctx.fillText('🏆 Top 15 Equipos Mayor Consumo', PAD, PAD + 16);
    ctx.fillStyle = '#94a3b8';
    ctx.font = '11px system-ui, sans-serif';
    ctx.fillText('total · despachos · frente asignado', PAD, PAD + 32);

    const maxVal = Math.max(..._rankingData.map(d => parseFloat(d.total)));

    _rankingData.forEach((d, i) => {
        const col = i % COLS;
        const row = Math.floor(i / COLS);
        const cx  = PAD + col * (CARD_W + GAP);
        const cy  = TITLE_H + row * (CARD_H + GAP);

        // Fondo tarjeta
        ctx.fillStyle = '#f8fafc';
        ctx.strokeStyle = '#e2e8f0';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.roundRect(cx, cy, CARD_W, CARD_H, 8);
        ctx.fill();
        ctx.stroke();

        const barColor = i === 0 ? '#003a70' : i <= 2 ? '#0077cc' : i <= 5 ? '#546e7a' : '#9ca3af';

        const idTexto = (d.PLACA && d.PLACA.trim()) ? d.PLACA
                      : (d.SERIAL_CHASIS && d.SERIAL_CHASIS.trim()) ? d.SERIAL_CHASIS
                      : (d.CODIGO_PATIO && d.CODIGO_PATIO.trim()) ? d.CODIGO_PATIO
                      : (d.MODELO || 'S/ID');

        // Tipo equipo
        ctx.fillStyle = '#0067b1';
        ctx.font = 'bold 9px system-ui, sans-serif';
        ctx.fillText((d.tipo || 'S/T').toUpperCase(), cx + 10, cy + 16);

        // Identificador
        ctx.fillStyle = '#1e293b';
        ctx.font = 'bold 12px monospace';
        ctx.fillText(idTexto.slice(0, 22), cx + 10, cy + 32);

        // Frente asignado
        ctx.fillStyle = '#475569';
        ctx.font = '10px system-ui, sans-serif';
        ctx.fillText('📍 ' + (d.frente ? d.frente.slice(0, 30) : 'Sin frente'), cx + 10, cy + 46);

        // Total (número grande)
        const total = parseFloat(d.total);
        const totalStr = total.toLocaleString('es-VE', {maximumFractionDigits: 0});
        ctx.fillStyle = '#003a70';
        ctx.font = 'bold 18px system-ui, sans-serif';
        ctx.fillText(totalStr, cx + 10, cy + 70);
        ctx.fillStyle = '#94a3b8';
        ctx.font = '10px system-ui, sans-serif';
        ctx.fillText(d.unidad || '', cx + 14 + ctx.measureText(totalStr).width, cy + 70);

        // Despachos
        ctx.fillStyle = '#0077cc';
        ctx.font = 'bold 11px system-ui, sans-serif';
        ctx.fillText(`⛽ ${d.despachos || 0} despacho${d.despachos !== 1 ? 's' : ''}`, cx + 10, cy + 88);

        // Barra de progreso
        const barW = CARD_W - 20;
        const pct  = maxVal > 0 ? total / maxVal : 0;
        ctx.fillStyle = '#e2e8f0';
        ctx.beginPath();
        ctx.roundRect(cx + 10, cy + CARD_H - 14, barW, 4, 2);
        ctx.fill();
        ctx.fillStyle = barColor;
        ctx.beginPath();
        ctx.roundRect(cx + 10, cy + CARD_H - 14, barW * pct, 4, 2);
        ctx.fill();
    });

    const fecha = new Date().toISOString().slice(0, 10);
    const link  = document.createElement('a');
    link.download = `top_equipos_${fecha}.png`;
    link.href     = cvs.toDataURL('image/png');
    link.click();
}

// ── TODOS LOS EQUIPOS — Tabla completa con buscador ─────────────━
let _todosData = [];

function renderTodosEquipos(datos) {
    hide('loadingTodosEq');
    document.getElementById('wrapTodosEq').style.display = 'block';
    _todosData = datos || [];
    document.getElementById('subtotalEquipos').textContent =
        `— ${_todosData.length} equipo${_todosData.length !== 1 ? 's' : ''} registrados`;
    llenarTablaEquipos(_todosData);
}

function llenarTablaEquipos(datos) {
    const body = document.getElementById('bodyTodosEq');
    if (!datos || datos.length === 0) {
        body.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:30px;color:#94a3b8;">Sin datos disponibles.</td></tr>`;
        return;
    }
    body.innerHTML = datos.map((d, i) => {
        const total = parseFloat(d.total);
        const ids   = (d.identificadores || d.CODIGO_PATIO || '—');
        return `<tr>
            <td style="font-size:11px;font-weight:700;color:#0067b1;text-transform:uppercase;">${d.tipo}</td>
            <td>
                <span style="font-family:monospace;font-weight:700;color:#1e293b;font-size:12px;">${ids}</span>
                <span style="display:block;font-size:10px;color:#94a3b8;margin-top:1px;">${d.CODIGO_PATIO}</span>
            </td>
            <td style="font-size:12px;">${d.MARCA} ${d.MODELO}</td>
            <td style="text-align:right;"><span class="eq-desp-badge">⛽ ${d.despachos}</span></td>
            <td style="text-align:right;font-weight:800;color:#0067b1;">
                ${total.toLocaleString('es-VE',{minimumFractionDigits:0,maximumFractionDigits:1})}
                <span style="font-size:10px;color:#94a3b8;font-weight:400;">${d.unidad}</span>
            </td>
        </tr>`;
    }).join('');
}

function filtrarTablaEquipos(q) {
    if (!q) { llenarTablaEquipos(_todosData); return; }
    const lq = q.toLowerCase();
    llenarTablaEquipos(_todosData.filter(d =>
        (d.identificadores||'').toLowerCase().includes(lq) ||
        (d.CODIGO_PATIO||'').toLowerCase().includes(lq)    ||
        (d.MARCA||'').toLowerCase().includes(lq)           ||
        (d.MODELO||'').toLowerCase().includes(lq)          ||
        (d.tipo||'').toLowerCase().includes(lq)
    ));
}

let _sortDir = -1; // -1=desc, 1=asc
function sortTabla(col) {
    _sortDir *= -1;
    const keys = ['tipo', 'identificadores', 'MARCA', 'despachos', 'total'];
    const key  = keys[col];
    const sorted = [..._todosData].sort((a, b) => {
        const av = isNaN(a[key]) ? (a[key]||'') : parseFloat(a[key]);
        const bv = isNaN(b[key]) ? (b[key]||'') : parseFloat(b[key]);
        return av > bv ? _sortDir : av < bv ? -_sortDir : 0;
    });
    llenarTablaEquipos(sorted);
}

// ── Total absoluto por Especificación — panel oculto (info ya visible en los gráficos de barras)
function renderTotalEspec(datos, tipoActivo) {
    const sec = document.getElementById('secTotalEspec');
    if (sec) sec.style.display = 'none';
}

// ── Especificación por frente (Aceite × Viscosidad ó Caucho × Modelo) ──────
function renderEspecFrente(datos, tipoActivo) {
    const sec  = document.getElementById('secEspecFrente');
    const load = document.getElementById('loadingAceiteFrente');
    const body = document.getElementById('aceiteFrente-body');
    if (!datos || datos.length === 0) { sec.style.display = 'none'; return; }

    // Configuración por tipo
    const esCaucho = tipoActivo === 'CAUCHO';
    const COLOR_BASE = esCaucho ? '#059669' : '#0067b1';
    const UNIDAD     = esCaucho ? 'Un' : 'L';
    const LABEL_ESPEC = esCaucho ? 'MODELOS' : 'VISCOSIDADES';
    const TITULO = esCaucho
        ? 'Caucho por Frente — Desglose por Modelo'
        : 'Aceite por Frente — Desglose por Viscosidad';

    // Actualizar título dinámicamente
    document.getElementById('txtEspecFrente').textContent = TITULO;
    document.getElementById('iconEspecFrente').style.color = COLOR_BASE;

    sec.style.display  = '';
    load.style.display = 'none';
    body.style.display = 'block';
    body.innerHTML     = '';

    const mapaFrente = {};
    const mapEspec = {};
    datos.forEach(d => {
        const val = parseFloat(d.total) || 0;
        mapaFrente[d.NOMBRE_FRENTE]  = (mapaFrente[d.NOMBRE_FRENTE] || 0) + val;
        mapEspec[d.ESPECIFICACION] = (mapEspec[d.ESPECIFICACION] || 0) + val;
    });
    const frentes  = Object.keys(mapaFrente).sort((a, b) => mapaFrente[b] - mapaFrente[a]);
    // Ordenar especificaciones por volumen de mayor a menor global
    const especs   = Object.keys(mapEspec).sort((a,b) => mapEspec[b] - mapEspec[a]);
    const maxTotal = Math.max(...Object.values(mapaFrente));

    // Paleta solicitada: 1° Rojo mayor consumo, 2° Azul intermedio, 3° Verde o Gris distinto, resto Grises
    // (Aplica al grafico "Aceite por Frente" y "Caucho por Frente")
    const PALETA = [
        '#b91c1c', // [0] Rojo intenso (mayor consumo global)
        '#0067b1', // [1] Azul corporativo (2do mayor consumo)
        '#059669', // [2] Verde oscuro (3er mayor consumo)
        '#475569', // [3] Gris slate oscuro
        '#64748b', // [4] Gris slate medio
        '#94a3b8', // [5] Gris slate claro
        '#cbd5e1', // [6] Gris slate más claro
        '#e2e8f0'  // [7] Gris base
    ];

    body.innerHTML = frentes.map((frente, i) => {
        const filas = datos.filter(d => d.NOMBRE_FRENTE === frente);
        const tot   = mapaFrente[frente];
        const barSegs = filas.sort((a, b) => parseFloat(b.total) - parseFloat(a.total)).map(f => {
            const pct   = parseFloat(f.total) / tot * 100;
            const color = PALETA[especs.indexOf(f.ESPECIFICACION) % PALETA.length];
            return `<div title="${f.ESPECIFICACION}: ${parseFloat(f.total).toFixed(0)} ${UNIDAD} (${pct.toFixed(0)}%)"
                         style="width:${pct}%;background:${color};height:100%;"></div>`;
        }).join('');
        const chips = filas.sort((a, b) => parseFloat(b.total) - parseFloat(a.total)).map(f => {
            const color = PALETA[especs.indexOf(f.ESPECIFICACION) % PALETA.length];
            return `<span style="background:${color}18;border:1px solid ${color}55;color:${color};
                                border-radius:20px;padding:1px 8px;font-weight:700;font-size:11px;
                                margin:1px;display:inline-block;">
                ${f.ESPECIFICACION} — ${parseFloat(f.total).toLocaleString('es-VE',{maximumFractionDigits:0})} ${UNIDAD}
            </span>`;
        }).join('');
        const wb = Math.round(tot / maxTotal * 100);
        return `<div class="frow">
            <span class="frow-num">#${i+1}</span>
            <span class="frow-name">${frente}</span>
            <span class="frow-bar-wrap"><div style="display:flex;height:100%;width:${wb}%;">${barSegs}</div></span>
            <span class="frow-val">${tot.toLocaleString('es-VE',{maximumFractionDigits:0})} ${UNIDAD}</span>
        </div>
        <div style="padding:2px 0 8px 32px;border-bottom:1px solid #f1f5f9;">${chips}</div>`;
    }).join('');

    // Leyenda
    const leg = especs.map((e, i) => {
        const c = PALETA[i % PALETA.length];
        const val = mapEspec[e].toLocaleString('es-VE',{maximumFractionDigits:0});
        return `
        <span style="display:inline-flex;align-items:center;gap:6px;margin:4px 6px;padding:4px 10px;background:${c}15;border:1px solid ${c}40;border-radius:20px;font-size:12px;font-weight:700;color:${c};">
            <span style="width:10px;height:10px;border-radius:50%;background:${c};display:inline-block;"></span>
            ${e} <span style="opacity:0.6;font-weight:600;margin:0 2px;">—</span> <span style="font-weight:900;">${val} ${UNIDAD}</span>
        </span>`;
    }).join('');
    body.insertAdjacentHTML('beforeend',
        `<div style="margin-top:12px;padding-top:10px;border-top:1px solid #f1f5f9;display:flex;flex-wrap:wrap;align-items:center;">
            <span style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.4px;margin-right:6px;">${LABEL_ESPEC}:</span>${leg}
        </div>`);
}

// ── Especificación por equipo (tabla) ────────────────────────────────
function renderEspecEquipo(datos, tipoActivo) {
    const sec  = document.getElementById('secEspecEquipo');
    const load = document.getElementById('loadingAceiteEquipo');
    const body = document.getElementById('aceiteEquipoBody');
    if (!datos || datos.length === 0) { sec.style.display = 'none'; return; }

    const esCaucho   = tipoActivo === 'CAUCHO';
    const COLOR_BASE = esCaucho ? '#059669' : '#0067b1';
    const UNIDAD     = esCaucho ? 'Un' : 'L';
    const TITULO     = esCaucho
        ? 'Caucho por Equipo — Desglose por Modelo'
        : 'Aceite por Equipo — Desglose por Viscosidad';

    document.getElementById('txtEspecEquipo').textContent = TITULO;
    document.getElementById('iconEspecEquipo').style.color = COLOR_BASE;

    sec.style.display  = '';
    load.style.display = 'none';
    body.style.display = 'block';

    // Calcular volumen total por especificación de nuevo (misma lógica)
    const mapEspec = {};
    datos.forEach(d => {
        mapEspec[d.ESPECIFICACION] = (mapEspec[d.ESPECIFICACION] || 0) + (parseFloat(d.total) || 0);
    });
    const especs = Object.keys(mapEspec).sort((a,b) => mapEspec[b] - mapEspec[a]);

    const PALETA = [
        '#b91c1c', // [0] Rojo intenso
        '#0067b1', // [1] Azul corporativo
        '#059669', // [2] Verde oscuro
        '#475569', // [3] Gris slate oscuro
        '#64748b', // [4] Gris slate medio
        '#94a3b8', // [5] Gris slate claro
        '#cbd5e1', // [6] Gris slate más claro
        '#e2e8f0'  // [7] Gris base
    ];

    const eq = {};
    datos.forEach(d => {
        // Prioridad: PLACA → SERIAL_CHASIS → CODIGO_PATIO → IDENTIFICADOR (código de referencia)
        const idTexto = (d.PLACA && d.PLACA.trim())          ? d.PLACA.trim()
                      : (d.SERIAL_CHASIS && d.SERIAL_CHASIS.trim()) ? d.SERIAL_CHASIS.trim()
                      : (d.CODIGO_PATIO && d.CODIGO_PATIO.trim())   ? d.CODIGO_PATIO.trim()
                      : (d.identificador || 'S/ID');

        // Agrupar por ID_EQUIPO para no mezclar equipos distintos con el mismo modelo
        const k = d.ID_EQUIPO || idTexto;
        if (!eq[k]) eq[k] = { idTexto, modelo: d.MODELO, tipo: d.tipo_equipo, especs: {} };
        eq[k].especs[d.ESPECIFICACION] = (eq[k].especs[d.ESPECIFICACION] || 0) + parseFloat(d.total);
    });
    const filas = Object.values(eq).sort((a, b) =>
        Object.values(b.especs).reduce((s, v) => s + v, 0) -
        Object.values(a.especs).reduce((s, v) => s + v, 0)
    );

    const thead = `<tr>
        <th>Equipo / Código</th><th>Tipo</th>
        ${especs.map((e, i) => `<th style="text-align:right;color:${PALETA[i % PALETA.length]};">${e}</th>`).join('')}
        <th style="text-align:right;">Total</th>
    </tr>`;
    const tbody = filas.map(f => {
        const tot = Object.values(f.especs).reduce((s, v) => s + v, 0);
        const cells = especs.map((e, i) => {
            const v = f.especs[e];
            return v
                ? `<td style="text-align:right;font-weight:700;color:${PALETA[i % PALETA.length]};">${v.toLocaleString('es-VE',{maximumFractionDigits:0})}</td>`
                : `<td style="text-align:right;color:#cbd5e0;">—</td>`;
        }).join('');
        return `<tr>
            <td style="font-weight:700;font-family:monospace;font-size:12px;">${f.idTexto}
                <span style="display:block;color:#64748b;font-weight:400;font-size:10px;font-family:sans-serif;">${f.modelo || ''}</span>
            </td>
            <td style="font-size:11px;color:#64748b;">${f.tipo}</td>
            ${cells}
            <td style="text-align:right;font-weight:800;color:${COLOR_BASE};">${tot.toLocaleString('es-VE',{maximumFractionDigits:0})} ${UNIDAD}</td>
        </tr>`;
    }).join('');
    body.innerHTML = `<table class="admin-table"><thead>${thead}</thead><tbody>${tbody}</tbody></table>`;
}

// ── CSV ────────────────────────────────────────────────────────────
function descargarCsv() {
    window.location.href = `{{ route('consumibles.exportarCsv') }}?${getParams()}`;
}

// ── Descargar gráfico como imagen PNG ─────────────────────────────
function descargarGrafico(canvasId, nombre) {
    const canvas = document.getElementById(canvasId);
    if (!canvas || canvas.style.display === 'none') {
        alert('El gráfico no está visible aún. Aplica los filtros primero.');
        return;
    }
    const fecha = new Date().toISOString().slice(0,10);
    const link  = document.createElement('a');
    link.download = `${nombre}_${fecha}.png`;
    link.href     = canvas.toDataURL('image/png');
    link.click();
}

// ── Helper: capturar cualquier panel del DOM tal como se ve en pantalla ──────
function capturaPanelHtml(panelId, nombre, callbackClone) {
    const el = document.getElementById(panelId);
    if (!el || el.style.display === 'none') {
        alert('El panel no está visible. Aplica los filtros primero.'); return;
    }
    if (typeof html2canvas === 'undefined') {
        alert('La librería de captura aún está cargando. Inténtalo en unos segundos.'); return;
    }
    const fecha = new Date().toISOString().slice(0, 10);
    html2canvas(el, {
        scale: 2,
        useCORS: true,
        backgroundColor: '#ffffff',
        logging: false,
        onclone: function(clonedDoc) {
            if (callbackClone) callbackClone(clonedDoc);
        }
    }).then(canvas => {
        const link = document.createElement('a');
        link.download = nombre + '_' + fecha + '.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    });
}

// ── Descargar "Total por Frente" — captura del DOM real ───────────────────────
function descargarPanelHtml(panelId, nombre) {
    const body = document.getElementById('totalFrenteBody');
    if (!body || !body.querySelector('.frow')) {
        alert('No hay datos para descargar.'); return;
    }
    // Captura el panel completo (g-card padre)
    const card = body.closest('.g-card') || body.parentElement;
    capturaPanelHtml(card?.id || 'panelTotalFrente', nombre);
    if (!card?.id) {
        // Si no tiene id, asignar temporalmente
        card.id = '__tmp_panel_frente';
        capturaPanelHtml('__tmp_panel_frente', nombre);
        setTimeout(() => { if (card) card.removeAttribute('id'); }, 2000);
    }
}

// ── Descargar "Equipos Asignados" — captura del DOM real en 1 COLUMNA ──────────
function descargarPanelEquipos(nombre) {
    const body = document.getElementById('eqAsigBody');
    if (!body || !body.firstElementChild?.children?.length) {
        alert('No hay datos para descargar.'); return;
    }
    const card = body.closest('.g-card') || body.parentElement;
    
    // Función para forzar que el panel se dibuje como lista de 1 columna solo para la foto
    const forceOneColumn = (clonedDoc) => {
        const clonedBody = clonedDoc.getElementById('eqAsigBody');
        if (clonedBody && clonedBody.firstElementChild) {
            const container = clonedBody.firstElementChild;
            container.style.flexDirection = 'column';
            container.style.flexWrap = 'nowrap';
        }
        // Reducimos el ancho de la tarjeta para que la lista no quede alargada a todo el ancho de la pantalla original
        const clonedCardId = card?.id || '__tmp_panel_equipos';
        const clonedCard = clonedDoc.getElementById(clonedCardId);
        if (clonedCard) {
            clonedCard.style.width = '350px';
            clonedCard.style.margin = '0 auto';
        }
    };

    if (card && !card.id) {
        card.id = '__tmp_panel_equipos';
        capturaPanelHtml('__tmp_panel_equipos', nombre, forceOneColumn);
        setTimeout(() => { if (card) card.removeAttribute('id'); }, 2000);
    } else if (card) {
        capturaPanelHtml(card.id, nombre, forceOneColumn);
    }
}




// ── Descargar "Tarjetas Resumen" como PNG (Canvas 2D puro) ─────────────────
function descargarPanelResumen(nombre) {
    const grid = document.getElementById('resumenGrid');
    if (!grid || grid.children.length === 0 || grid.querySelector('.loading-overlay')) {
        alert('No hay datos para descargar.'); return; 
    }
    
    // Extraer datos
    const datos = [...grid.children].map(caja => {
        const topDiv = caja.firstElementChild;
        const botDiv = caja.lastElementChild;
        const numTxt  = topDiv?.querySelector('span:first-child')?.textContent?.trim() || '';
        const tipoTxt = topDiv?.querySelector('span:last-child')?.textContent?.trim() || '';
        const botTxt  = botDiv?.textContent?.replace(/\s+/g, ' ').trim() || '';
        return { numTxt, tipoTxt, botTxt };
    });

    const DPR = 2;
    // Si hay menos de 4, usamos 200 de ancho, si no, intentamos distribuir bien
    const cols = Math.min(datos.length, 4); 
    const rows = Math.ceil(datos.length / cols);

    const BOX_W = 260, BOX_H = 75; 
    const GAP = 14, PAD_X = 25, PAD_Y = 25, TITLE_H = 50;
    
    const W = PAD_X * 2 + cols * BOX_W + Math.max(0, cols - 1) * GAP;
    const H = TITLE_H + PAD_Y * 2 + rows * BOX_H + Math.max(0, rows - 1) * GAP;

    const canvas = document.createElement('canvas');
    canvas.width  = W * DPR;
    canvas.height = H * DPR;
    const ctx = canvas.getContext('2d');
    ctx.scale(DPR, DPR);

    function rr(x, y, w, h, r) {
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + w - r, y);
        ctx.arcTo(x + w, y, x + w, y + r, r);
        ctx.lineTo(x + w, y + h - r);
        ctx.arcTo(x + w, y + h, x + w - r, y + h, r);
        ctx.lineTo(x + r, y + h);
        ctx.arcTo(x, y + h, x, y + h - r, r);
        ctx.lineTo(x, y + r);
        ctx.arcTo(x, y, x + r, y, r);
        ctx.closePath();
    }

    // Fondo blanco del panel general
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, W, H);

    // Título
    ctx.fillStyle = '#1e293b';
    ctx.font = 'bold 15px system-ui,sans-serif';
    ctx.textAlign = 'left';
    ctx.fillText('Resumen General - Análisis de Consumo', PAD_X, 30);
    
    // Línea separadora
    ctx.strokeStyle = '#e2e8f0'; ctx.lineWidth = 1;
    ctx.beginPath(); ctx.moveTo(PAD_X, TITLE_H); ctx.lineTo(W - PAD_X, TITLE_H); ctx.stroke();

    // Dibujar tarjetas
    datos.forEach((d, i) => {
        const c = i % cols;
        const r = Math.floor(i / cols);
        const x = PAD_X + c * (BOX_W + GAP);
        const y = TITLE_H + PAD_Y + r * (BOX_H + GAP);

        // Fondo degrade azul oscuro (similar al "admin-card")
        const grad = ctx.createLinearGradient(x, y, x + BOX_W, y + BOX_H);
        grad.addColorStop(0, '#1e293b');
        grad.addColorStop(1, '#0f172a');
        ctx.fillStyle = grad;
        rr(x, y, BOX_W, BOX_H, 14);
        ctx.fill();

        ctx.textAlign = 'left';
        
        // Número gigante
        ctx.fillStyle = '#ffffff';
        ctx.font = '800 28px system-ui,sans-serif';
        const numW = ctx.measureText(d.numTxt).width;
        ctx.fillText(d.numTxt, x + 16, y + 42);
        
        // Tipo de consumible y unidad (se asegura de no pasarse usando truncamiento visual o redimension)
        ctx.fillStyle = '#94a3b8';
        ctx.font = '700 12px system-ui,sans-serif';
        let subTxt = d.tipoTxt;
        let maxW = BOX_W - numW - 16 - 12; // Ancho máximo permitido (Caja - Num - PaddingIzq - PaddingDer)
        while(ctx.measureText(subTxt).width > maxW && subTxt.length > 5) {
            subTxt = subTxt.slice(0, -1);
        }
        if(subTxt !== d.tipoTxt) subTxt += '…';
        ctx.fillText(subTxt, x + 16 + numW + 8, y + 36);

        // Despachos y eq
        ctx.fillStyle = '#64748b';
        ctx.font = '11px system-ui,sans-serif';
        ctx.fillText(d.botTxt, x + 16, y + 60);
    });

    const fecha = new Date().toISOString().slice(0, 10);
    const link  = document.createElement('a');
    link.download = nombre + '_' + fecha + '.png';
    link.href     = canvas.toDataURL('image/png');
    link.click();
}

// ── Descargar "Desglose por Especificación" como PNG (Canvas 2D puro) ──────
function descargarPanelEspecFrente(nombre) {
    const body   = document.getElementById('aceiteFrente-body');
    const titulo = document.getElementById('txtEspecFrente')?.textContent || 'Desglose por Especificación';
    if (!body || body.children.length === 0) { alert('No hay datos para descargar.'); return; }

    const rowsData = [];
    const frows = body.querySelectorAll('.frow');
    if (!frows.length) { alert('No hay datos para descargar.'); return; }

    frows.forEach(row => {
        const name = row.querySelector('.frow-name')?.textContent?.trim() || '';
        const valTxt = row.querySelector('.frow-val')?.textContent?.trim() || '';
        
        // Segmentos de la barra
        const barWrap = row.querySelector('.frow-bar-wrap > div');
        const segs = [];
        if (barWrap) {
            [...barWrap.children].forEach(seg => {
                segs.push({
                    w: parseFloat(seg.style.width) || 0, // %
                    c: seg.style.background || '#ccc'
                });
            });
            // normalizar % si el wrap mismo no es 100%
            const wrapW = parseFloat(barWrap.style.width) || 100;
            segs.forEach(s => s.w = s.w * wrapW / 100); 
        }

        // Chips
        const chipsDiv = row.nextElementSibling;
        const chips = [];
        if (chipsDiv && chipsDiv.tagName === 'DIV' && chipsDiv.style.padding.includes('32px')) {
            [...chipsDiv.children].forEach(chip => {
                 chips.push({
                     txt: chip.textContent.replace(/\s+/g, ' ').trim(),
                     c: chip.style.color || '#333',
                     bg: chip.style.backgroundColor || '#eee',
                     bc: chip.style.borderColor || '#ccc'
                 });
            });
        }
        
        rowsData.push({ name, valTxt, segs, chips });
    });

    const legendDiv = body.lastElementChild;
    const legends = [];
    if (legendDiv && legendDiv.style.marginTop === '12px') {
        const spans = legendDiv.querySelectorAll('span[style*="align-items:center"]');
        spans.forEach(s => {
            const circle = s.querySelector('span');
            const color = circle ? circle.style.background : '#ccc';
            const txt = s.textContent.replace(/\s+/g, ' ').trim();
            legends.push({ txt, c: color });
        });
    }

    const DPR = 2, W = 820, PAD = 24, TITLE_H = 52;
    const NUM_W = 30, NAME_W = 185, VAL_W = 130;
    const BAR_X = PAD + NUM_W + 8 + NAME_W + 8;
    const BAR_W = W - BAR_X - VAL_W - PAD;

    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    // Pre-calcular la altura dinámica basada en los chips y barras
    ctx.font = 'bold 11px system-ui,sans-serif';
    let currentYOffset = TITLE_H + PAD;
    rowsData.forEach(d => {
        d.rowY = currentYOffset;
        currentYOffset += 28; // row main height
        
        let chipX = PAD + 32;
        let chipY = currentYOffset + 10;
        let maxChipH = 0;
        
        d.chips.forEach(chip => {
             const chipTxtW = ctx.measureText(chip.txt).width + 16;
             if (chipX + chipTxtW > W - PAD) {
                 chipX = PAD + 32;
                 chipY += 22;
             }
             chip.x = chipX;
             chip.y = chipY;
             chip.w = chipTxtW;
             chipX += chipTxtW + 4;
             maxChipH = Math.max(maxChipH, chipY + 20 - currentYOffset);
        });
        
        currentYOffset += maxChipH + 12; // padding bottom
    });
    
    // Leyenda
    let legY = currentYOffset + 24;
    let legX = PAD;
    legends.forEach(l => {
        const w = ctx.measureText(l.txt).width + 20;
        if(legX + w > W - PAD) { legX = PAD; legY += 20; }
        l.x = legX; l.y = legY;
        legX += w + 8;
    });

    const H = legY + 30;

    canvas.width  = W * DPR;
    canvas.height = H * DPR;
    ctx.scale(DPR, DPR);

    function rr(x, y, w, h, r) {
        r = Math.min(r, w / 2, h / 2);
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + w - r, y);
        ctx.arcTo(x + w, y, x + w, y + r, r);
        ctx.lineTo(x + w, y + h - r);
        ctx.arcTo(x + w, y + h, x + w - r, y + h, r);
        ctx.lineTo(x + r, y + h);
        ctx.arcTo(x, y + h, x, y + h - r, r);
        ctx.lineTo(x, y + r);
        ctx.arcTo(x, y, x + r, y, r);
        ctx.closePath();
    }

    // Dibujado fondo
    ctx.fillStyle = '#ffffff';
    rr(0, 0, W, H, 16);
    ctx.fill();
    ctx.save();
    rr(0, 0, W, H, 16);
    ctx.clip();

    // Titulo
    ctx.fillStyle = '#1e293b';
    ctx.font = 'bold 15px system-ui,sans-serif';
    ctx.textAlign = 'left';
    ctx.fillText(titulo, PAD + 4, 34);
    ctx.strokeStyle = '#e2e8f0'; ctx.lineWidth = 1;
    ctx.beginPath(); ctx.moveTo(PAD, TITLE_H); ctx.lineTo(W - PAD, TITLE_H); ctx.stroke();

    // Dibujar datos
    rowsData.forEach((d, i) => {
        if (i > 0) {
            ctx.strokeStyle = '#f8fafc'; ctx.lineWidth = 1;
            ctx.beginPath(); ctx.moveTo(PAD, d.rowY - 8); ctx.lineTo(W - PAD, d.rowY - 8); ctx.stroke();
        }

        const midY = d.rowY + 14;

        // Número # Numeral
        ctx.fillStyle = '#94a3b8';
        ctx.font = 'bold 11px system-ui,sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('#' + (i+1), PAD + NUM_W / 2, midY + 4);

        // Nombre
        ctx.fillStyle = '#1e293b';
        ctx.font = 'bold 12px system-ui,sans-serif';
        ctx.textAlign = 'left';
        let name = d.name;
        while (ctx.measureText(name).width > NAME_W - 4 && name.length > 4) name = name.slice(0, -1);
        if (name !== d.name) name += '…';
        ctx.fillText(name, PAD + NUM_W + 8, midY + 4);

        // Barra wrap
        ctx.fillStyle = '#f1f5f9';
        rr(BAR_X, midY - 5, BAR_W, 10, 5); ctx.fill();

        // Recortar dibujo de segmentos a las esquinas redondeadas
        ctx.save();
        rr(BAR_X, midY - 5, BAR_W, 10, 5);
        ctx.clip();
        
        let currentSegX = BAR_X;
        d.segs.forEach(s => {
            const sw = (s.w / 100) * BAR_W;
            if (sw > 0) {
                ctx.fillStyle = s.c;
                ctx.fillRect(currentSegX, midY - 5, sw, 10);
                currentSegX += sw;
            }
        });
        
        ctx.restore();

        // Valor total
        ctx.fillStyle = '#0f172a';
        ctx.font = '900 13px system-ui,sans-serif';
        ctx.textAlign = 'right';
        ctx.fillText(d.valTxt, W - PAD, midY + 4);

        // Chips
        d.chips.forEach(chip => {
            // Shadow / bg
            ctx.fillStyle = chip.bg;
            rr(chip.x, chip.y - 12, chip.w, 18, 9);
            ctx.fill();
            // border
            ctx.lineWidth = 1;
            ctx.strokeStyle = chip.bc;
            ctx.stroke();
            
            ctx.fillStyle = chip.c;
            ctx.font = 'bold 10px system-ui,sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(chip.txt, chip.x + chip.w/2, chip.y + 1);
        });
    });

    // Leyenda
    ctx.strokeStyle = '#f1f5f9'; ctx.lineWidth = 1;
    ctx.beginPath(); ctx.moveTo(PAD, legY - 18); ctx.lineTo(W - PAD, legY - 18); ctx.stroke();
    ctx.fillStyle = '#94a3b8';
    ctx.font = 'bold 10px system-ui,sans-serif';
    ctx.textAlign = 'left';
    ctx.fillText('LEYENDA:', PAD, legY);
    
    legends.forEach(l => {
        ctx.fillStyle = l.c;
        rr(l.x + 55, legY - 7, 10, 10, 3);
        ctx.fill();
        
        ctx.fillStyle = l.c;
        ctx.font = 'bold 11px system-ui,sans-serif';
        ctx.fillText(l.txt, l.x + 70, legY + 1);
    });

    const fecha = new Date().toISOString().slice(0, 10);
    const link  = document.createElement('a');
    link.download = nombre + '_' + fecha + '.png';
    link.href     = canvas.toDataURL('image/png');
    link.click();
}

</script>
<script src="{{ asset('js/maquinaria/consumibles_graficos.js') }}?v=2.0"></script>
<script>
    // Carga inicial de datos — se ejecuta tras cargar todos los scripts
    if (typeof cargarDatos === 'function') cargarDatos();
</script>
@endsection
