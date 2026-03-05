@extends('layouts.estructura_base')
@section('title', 'Cargar Consumibles')

@section('content')
<style>
    .con-label   { font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; display:block; }
    .con-select, .con-input { width:100%; padding:10px 14px; border:1px solid #cbd5e0; border-radius:10px; font-size:13px; color:#1e293b; background:#fbfcfd; outline:none; transition:border .2s; }
    .con-select:focus, .con-input:focus { border-color:#0067b1; background:#fff; }
    .btn-green   { background:linear-gradient(135deg,#059669,#047857); }

    /* ── ZONA DE PEGADO ── */
    .paste-zone { border:3px dashed #bfdbfe; border-radius:12px; background:#f0f9ff;
                  padding:20px; text-align:center; cursor:pointer; transition:all .2s;
                  margin-bottom:16px; display:flex; align-items:center; justify-content:center; gap:10px; }
    .paste-zone:hover, .paste-zone.drag-over { border-color:#0067b1; background:#dbeafe; }
    .paste-zone i { font-size:28px; color:#0067b1; }
    .paste-zone p { margin:0; font-size:14px; font-weight:700; color:#1d4ed8; }
    .paste-zone small { display:block; font-size:12px; color:#64748b; margin-top:3px; font-weight:400; }

    /* ── TABLA TIPO EXCEL ── */
    .tbl-wrap { overflow-x:auto; border-radius:10px; border:1px solid #e2e8f0; }
    #tablaFilas { width:100%; border-collapse:collapse; font-size:13px; min-width:700px; }
    #tablaFilas thead th {
        background:#1e293b; color:#e2e8f0; font-weight:700; padding:9px 8px;
        text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.4px;
        border-right:1px solid #334155; white-space:nowrap; }
    #tablaFilas thead th:first-child { width:36px; text-align:center; }
    #tablaFilas thead th:last-child  { width:36px; border-right:none; }
    #tablaFilas tbody tr:nth-child(even) { background:#f8fafc; }
    #tablaFilas tbody tr:hover { background:#eff6ff; }
    #tablaFilas td { padding:0; border-bottom:1px solid #e2e8f0; border-right:1px solid #f1f5f9;
                     vertical-align:middle; }
    #tablaFilas td:first-child { text-align:center; color:#94a3b8; font-size:11px; font-weight:700;
                                  padding:6px 4px; background:#f8fafc; border-right:2px solid #e2e8f0; }
    #tablaFilas td:last-child { border-right:none; text-align:center; background:#fafafa; }

    /* Celdas editables */
    #tablaFilas td input[type=text],
    #tablaFilas td input[type=date],
    #tablaFilas td input[type=number] {
        width:100%; border:none; background:transparent; outline:none;
        padding:8px 8px; font-size:13px; color:#1e293b; font-family:inherit; }
    #tablaFilas td input:focus {
        background:#eff6ff; outline:2px solid #0067b1; outline-offset:-2px; border-radius:4px; }
    #tablaFilas .btn-del { background:none; border:none; color:#cbd5e0; padding:6px;
                           cursor:pointer; border-radius:6px; transition:all .2s; }
    #tablaFilas .btn-del:hover { background:#fee2e2; color:#ef4444; }

    .badge-count { display:inline-block; background:#0067b1; color:#fff; border-radius:20px;
                   padding:1px 10px; font-size:12px; font-weight:700; margin-left:8px; }
</style>

{{-- CABECERA --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 class="page-title" style="margin-bottom:4px;">
            <span class="page-title-line2" style="color:#000;">Cargar Consumibles</span>
        </h1>
    </div>
</div>

<form method="POST" action="{{ route('consumibles.guardarLote') }}" id="formLote">
@csrf

{{-- ═══ CONFIGURACIÓN DEL LOTE ═══ --}}
<div class="admin-card" style="box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 20px;">

    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:16px; align-items:end;">
        {{-- TIPO CONSUMIBLE --}}
        <div>
            <label class="con-label">Tipo de Consumible *</label>
            <select name="tipo_consumible" id="tipoSelect" class="con-select" onchange="actualizarUnidad()" required>
                <option value="">— Seleccionar —</option>
                @foreach($tipos as $val => $label)
                    <option value="{{ $val }}" {{ old('tipo_consumible') == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- UNIDAD --}}
        <div>
            <label class="con-label">Unidad *</label>
            <select name="unidad" id="unidadSelect" class="con-select" required>
                @foreach($unidades as $val => $label)
                    <option value="{{ $val }}" {{ old('unidad', 'LITROS') == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- FRENTE --}}
        <div>
            <label class="con-label">Frente de Trabajo *</label>
            <div style="position:relative;">
                <input type="text" id="frenteSearch" placeholder="Click para ver todos los frentes..."
                    class="con-input" autocomplete="off"
                    oninput="filtrarFrentes(this.value)"
                    onfocus="mostrarTodosLosFrente()">
                <input type="hidden" name="id_frente" id="idFrenteHidden" value="{{ old('id_frente') }}">
                <div id="frenteDropdown"
                     style="display:none; position:absolute; top:100%; left:0; right:0; background:#fff;
                            border:1px solid #cbd5e0; border-radius:10px; box-shadow:0 8px 20px rgba(0,0,0,.12);
                            z-index:200; max-height:240px; overflow-y:auto; margin-top:4px;"></div>
            </div>
            <span id="frenteSeleccionado" style="font-size:12px; color:#059669; font-weight:600; margin-top:4px; display:none;"></span>
        </div>
    </div>
</div>

{{-- Datalists para autocompletado en columna Espec --}}
<datalist id="listAceite">
    <option value="15W-40"><option value="10W-30"><option value="10W-40">
    <option value="5W-30"><option value="5W-40"><option value="SAE 30">
    <option value="SAE 40"><option value="SAE 90"><option value="SAE 140">
    <option value="80W-90"><option value="85W-140"><option value="ATF Dexron">
    <option value="ISO 46"><option value="ISO 68">
</datalist>
<datalist id="listCaucho">
    <option value="11R22.5"><option value="295/80R22.5"><option value="315/80R22.5">
    <option value="385/65R22.5"><option value="275/70R22.5"><option value="10.00R20">
    <option value="12.00R20"><option value="900R20"><option value="1200R24">
    <option value="265/65R17"><option value="245/70R16">
</datalist>

{{-- ═══ TABLA / ZONA DE PEGADO ═══ --}}
<div class="admin-card" style="box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 20px;">
    {{-- Cabecera con contador y botones --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">

        <div style="display:flex; gap:8px;">
            <button type="button" class="btn-secondary" style="padding:8px 14px; font-size:13px;" onclick="limpiarTabla()">
                <i class="material-icons" style="font-size:16px;">delete_sweep</i> Limpiar
            </button>
            <button type="button" class="btn-secondary" style="padding:8px 14px; font-size:13px;" onclick="agregarFila()">
                <i class="material-icons" style="font-size:16px;">add</i> Agregar fila
            </button>
        </div>
    </div>



    {{-- Tabla --}}
    <div class="tbl-wrap">
        <table id="tablaFilas">
            <thead>
                <tr>
                    <th>#</th>
                    <th>FECHA *</th>
                    <th>IDENTIFICADOR <span style="font-weight:400;opacity:.7;">(placa / serial)</span></th>
                    <th>RESP. NOMBRE</th>
                    <th>RESP. C.I.</th>
                    <th>CANTIDAD *</th>
                    <th id="thEspec" style="display:none; color:#0067b1;">VISCOSIDAD</th>
                    <th>ORIGEN SUMINISTRO</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla"></tbody>
        </table>
    </div>

    <button type="button"
            style="background:none; border:2px dashed #cbd5e0; color:#64748b; width:100%; padding:10px;
                   border-radius:10px; font-size:13px; font-weight:600; cursor:pointer; display:flex;
                   align-items:center; justify-content:center; gap:6px; transition:all .2s; margin-top:10px;"
            onmouseover="this.style.borderColor='#0067b1'; this.style.color='#0067b1';"
            onmouseout="this.style.borderColor='#cbd5e0'; this.style.color='#64748b';"
            onclick="agregarFila()">
        <i class="material-icons" style="font-size:18px;">add</i> Agregar Fila
    </button>
</div>

{{-- ACCIONES --}}
<div style="display:flex; justify-content:flex-end; gap:12px; padding-bottom:40px;">
    <a href="{{ route('consumibles.index') }}" class="btn-secondary">Cancelar</a>
    <button type="submit" class="btn-primary-maquinaria btn-green" style="padding: 10px 22px; font-size:14px;">
        <i class="material-icons" style="font-size:18px;">save</i>
        Guardar Lote
    </button>
</div>

</form>

{{-- ═══ JAVASCRIPT ═══ --}}
<script>
const FRENTES = @json($frentes->map(fn($f) => ['id' => $f->ID_FRENTE, 'nombre' => $f->NOMBRE_FRENTE]));
let filaCount = 0;

// ── Tipo helper ──────────────────────────────────────────────────────
const TIPOS_ACEITE = ['ACEITE'];
const TIPOS_CAUCHO = ['CAUCHO'];
function tipoActual()     { return document.getElementById('tipoSelect').value; }
function necesitaEspec()  { const t = tipoActual(); return TIPOS_ACEITE.includes(t) || TIPOS_CAUCHO.includes(t); }
function datalistIdActual(){ return TIPOS_ACEITE.includes(tipoActual()) ? 'listAceite' : 'listCaucho'; }

// ── Unidad automática + mostrar/ocultar columna VISCOSIDAD/MEDIDA ────
function actualizarUnidad() {
    const tipo = tipoActual();

    // Unidad automática
    const mapa = { 'GASOIL':'LITROS','GASOLINA':'LITROS','ACEITE':'LITROS',
                   'CAUCHO':'UNIDADES','REFRIGERANTE':'LITROS','OTRO':'LITROS' };
    document.getElementById('unidadSelect').value = mapa[tipo] || 'LITROS';

    const espec = necesitaEspec();
    const th    = document.getElementById('thEspec');

    // Cabecera de columna
    if (espec) {
        th.style.display = '';
        if (TIPOS_ACEITE.includes(tipo)) {
            th.textContent = '⬡ VISCOSIDAD';
            th.style.color = '#0067b1';
        } else {
            th.textContent = '⬡ MODELO CAUCHO';
            th.style.color = '#059669';
        }
    } else {
        th.style.display = 'none';
    }

    // Actualizar celdas existentes en la tabla
    const dlId = espec ? datalistIdActual() : '';
    document.querySelectorAll('[data-espec-cell]').forEach(td => {
        td.style.display = espec ? '' : 'none';
        const inp = td.querySelector('input');
        if (inp) {
            inp.setAttribute('list', dlId);
            if (!espec) inp.value = '';
        }
    });
}


// ── Frente dropdown ───────────────────────────────────────────────
function renderDropdownFrente(lista) {
    const dd = document.getElementById('frenteDropdown');
    if (!dd) return;
    dd.innerHTML = lista.length === 0
        ? '<div style="padding:10px 14px; color:#94a3b8; font-size:13px;">Sin resultados</div>'
        : lista.map(f =>
            `<div onclick="seleccionarFrente(${f.id},'${f.nombre.replace(/'/g,"\\'")}')"
                  style="padding:10px 14px; cursor:pointer; font-size:13px; color:#1e293b; transition:background .15s;"
                  onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background=''">
                ${f.nombre}
             </div>`
          ).join('');
    dd.style.display = 'block';
}

function mostrarTodosLosFrente() { renderDropdownFrente(FRENTES); }

function filtrarFrentes(q) {
    if (!q || q.length < 1) { renderDropdownFrente(FRENTES); return; }
    renderDropdownFrente(FRENTES.filter(f => f.nombre.toLowerCase().includes(q.toLowerCase())));
}

function seleccionarFrente(id, nombre) {
    document.getElementById('idFrenteHidden').value  = id;
    document.getElementById('frenteSearch').value    = nombre;
    document.getElementById('frenteDropdown').style.display = 'none';
    const badge = document.getElementById('frenteSeleccionado');
    badge.textContent  = '✓ ' + nombre;
    badge.style.display = 'block';
}

document.addEventListener('click', e => {
    const dd = document.getElementById('frenteDropdown');
    if (!dd) return;
    if (!e.target.closest('#frenteSearch') && !e.target.closest('#frenteDropdown'))
        dd.style.display = 'none';
});

// ── Gestión de filas ──────────────────────────────────────────────
function agregarFila(data = {}, idx = null) {
    filaCount++;
    const n     = filaCount;
    const espec = necesitaEspec();
    const dlId  = espec ? datalistIdActual() : '';
    const tr    = document.createElement('tr');
    tr.id = `fila_${n}`;
    tr.innerHTML = `
        <td>${n}</td>
        <td><input type="date"   name="filas[${n}][fecha]"         value="${limpiar(data.fecha)}"    tabindex="${n*10+1}"></td>
        <td><input type="text"   name="filas[${n}][identificador]" value="${limpiar(data.id)}"       tabindex="${n*10+2}" placeholder="placa / serial"></td>
        <td><input type="text"   name="filas[${n}][resp_nombre]"   value="${limpiar(data.nombre)}"   tabindex="${n*10+3}" placeholder="Nombre responsable"></td>
        <td><input type="text"   name="filas[${n}][resp_ci]"       value="${limpiar(data.ci)}"       tabindex="${n*10+4}" placeholder="C.I."></td>
        <td><input type="number" name="filas[${n}][cantidad]"      value="${limpiar(data.cantidad)}" tabindex="${n*10+5}" placeholder="0" step="0.01" min="0" style="max-width:100px;"></td>
        <td data-espec-cell style="display:${espec?'':'none'}">
            <input type="text" name="filas[${n}][especificacion]" value="${limpiar(data.espec)}"
                   data-espec-input list="${dlId}" tabindex="${n*10+6}"
                   placeholder="${dlId==='listAceite'?'15W-40, SAE 90...':'Ej: Goodyear G177, Bridgestone M854...'}"
                   style="min-width:90px;">
        </td>
        <td><input type="text"   name="filas[${n}][raw_origen]"    value="${limpiar(data.origen)}"   tabindex="${n*10+7}" placeholder="Cisterna, guía... (opcional)"></td>
        <td><button type="button" class="btn-del" onclick="eliminarFila(${n})" title="Eliminar">
                <i class="material-icons" style="font-size:16px;">close</i>
            </button></td>`;
    document.getElementById('cuerpoTabla').appendChild(tr);
    actualizarContador();
    if (filaCount === 1) { const f = tr.querySelector('input'); if (f) f.id = 'primerInput'; }
    return tr;
}

function limpiar(v) {
    if (v === undefined || v === null) return '';
    return String(v).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function eliminarFila(n) {
    const el = document.getElementById(`fila_${n}`);
    if (el) el.remove();
    actualizarContador();
}

function limpiarTabla() {
    if (!confirm('¿Limpiar todas las filas?')) return;
    document.getElementById('cuerpoTabla').innerHTML = '';
    filaCount = 0;
    actualizarContador();
}

function actualizarContador() {
    const contador = document.getElementById('contadorFilas');
    if (contador) {
        const n = document.getElementById('cuerpoTabla').children.length;
        contador.textContent = n + (n === 1 ? ' fila' : ' filas');
    }
}

// ── PEGAR DESDE EXCEL ─────────────────────────────────────────────
// Funciona en TODA la página (no solo dentro de la tabla)
document.addEventListener('paste', function(e) {
    // Si el foco está en un input del lote de configuración → no interceptar
    const active = document.activeElement;
    if (active && (active.id === 'frenteSearch' || active.id === 'tipoSelect' || active.id === 'unidadSelect')) return;

    const text = (e.clipboardData || window.clipboardData).getData('text');
    if (!text || (!text.includes('\t') && !text.includes('\n'))) return;

    e.preventDefault();

    // Limpiar tabla antes de pegar (opcional: solo si hay filas vacías)
    const filasSinDatos = [...document.getElementById('cuerpoTabla').querySelectorAll('tr')]
        .every(tr => [...tr.querySelectorAll('input')].every(i => !i.value.trim()));
    if (filasSinDatos) {
        document.getElementById('cuerpoTabla').innerHTML = '';
        filaCount = 0;
    }

    // Parsear líneas
    const lineas = text.trim().split('\n');
    let insertadas = 0;

    lineas.forEach(linea => {
        const cols = linea.split('\t').map(c => c.trim().replace(/\r/g, ''));
        // Saltar si la línea está completamente vacía
        if (cols.every(c => !c)) return;

        // Autodetectar si la primera línea es encabezado (texto, no fecha ni número)
        if (insertadas === 0 && isNaN(Date.parse(cols[0])) && isNaN(parseFloat(cols[0])) && cols[0] && !/^\d{1,2}\/\d{1,2}/.test(cols[0])) {
            return; // saltar encabezado
        }

        // ── Parsear fecha ─────────────────────────────────────────
        let fecha = cols[0] || '';

        // Formato Excel venezolano: DD-MM-YY  →  19-02-26 = 19 feb 2026
        if (/^\d{1,2}-\d{1,2}-\d{2}$/.test(fecha)) {
            const p = fecha.split('-');
            const yy = parseInt(p[2]);
            const yyyy = yy <= 50 ? 2000 + yy : 1900 + yy;  // 26→2026, 99→1999
            fecha = `${yyyy}-${p[1].padStart(2,'0')}-${p[0].padStart(2,'0')}`;
        }
        // Formato DD-MM-YYYY con 4 dígitos de año
        else if (/^\d{1,2}-\d{1,2}-\d{4}$/.test(fecha)) {
            const p = fecha.split('-');
            fecha = `${p[2]}-${p[1].padStart(2,'0')}-${p[0].padStart(2,'0')}`;
        }
        // Formato DD/MM/YYYY o MM/DD/YYYY con barras
        else if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(fecha)) {
            const p = fecha.split('/');
            if (parseInt(p[0]) > 12) {
                // DD/MM/YYYY
                fecha = `${p[2]}-${p[1].padStart(2,'0')}-${p[0].padStart(2,'0')}`;
            } else {
                // MM/DD/YYYY
                fecha = `${p[2]}-${p[0].padStart(2,'0')}-${p[1].padStart(2,'0')}`;
            }
        }
        // YYYY-MM-DD ya está en formato correcto → no tocar

        agregarFila({
            fecha:    fecha,
            id:       cols[1] || '',
            nombre:   cols[2] || '',
            ci:       cols[3] || '',
            cantidad: (cols[4] || '').replace(',', '.'),
            espec:    necesitaEspec() ? (cols[5] || '') : '',
            origen:   necesitaEspec() ? (cols[6] || '') : (cols[5] || ''),
        });
        insertadas++;
    });

    if (insertadas > 0) {
        const pz = document.getElementById('pasteZone');
        if (pz) {
            pz.style.borderColor = '#059669';
            setTimeout(() => { pz.style.borderColor = ''; }, 1000);
        }
    }
});

// ── Validación antes de enviar ────────────────────────────────────
document.getElementById('formLote').addEventListener('submit', function(e) {
    const frente = document.getElementById('idFrenteHidden').value;
    const tipo   = document.getElementById('tipoSelect').value;
    const filas  = document.getElementById('cuerpoTabla').children.length;
    if (!frente) { e.preventDefault(); alert('Selecciona un frente de trabajo.'); return; }
    if (!tipo)   { e.preventDefault(); alert('Selecciona el tipo de consumible.'); return; }
    if (!filas)  { e.preventDefault(); alert('Agrega al menos una fila.'); return; }
});

// ── Inicio: 5 filas vacías ────────────────────────────────────────
window.addEventListener('DOMContentLoaded', () => {
    for (let i = 0; i < 5; i++) agregarFila();
});
</script>
@endsection
