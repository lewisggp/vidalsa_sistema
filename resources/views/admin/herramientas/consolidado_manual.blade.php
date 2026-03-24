@extends('layouts.estructura_base')
@section('title', 'Consolidado Manual de Equipos')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<style>
    body, .main-viewport { background: #f1f5f9 !important; }
    .cm-page { font-family: 'Inter', sans-serif; max-width: 1300px; margin: 0 auto; padding: 10px 0 40px; }
    .cm-title { font-size: 22px; font-weight: 800; color: #1e293b; display:flex; align-items:center; gap:8px; margin-bottom: 18px; }
    .cm-grid { display: grid; grid-template-columns: 1fr 360px; gap: 20px; align-items: start; }
    @media(max-width:900px){ .cm-grid { grid-template-columns: 1fr; } }

    /* ── Tabla Editable ───────────────────────────────────────── */
    .cm-card { background: #fff; border-radius: 16px; padding: 22px; box-shadow: 0 4px 20px rgba(0,0,0,.06); border: 1px solid #e2e8f0; }
    .cm-card-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing:1.2px; color:#64748b; margin-bottom: 14px; display:flex; align-items:center; gap:6px; }
    .cm-hints { font-size: 12px; color: #94a3b8; margin-bottom: 12px; padding: 8px 12px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e0; }
    .cm-hints b { color: #475569; }

    #editableTable { width: 100%; border-collapse: collapse; font-size: 14px; }
    #editableTable thead th { background: #1e3a5f; color: #fff; padding: 10px 12px; text-align: left; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: .8px; }
    #editableTable thead th:first-child { border-radius: 8px 0 0 8px; }
    #editableTable thead th:last-child  { border-radius: 0 8px 8px 0; text-align:center; }
    #editableTable tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .15s; }
    #editableTable tbody tr:hover { background: #f8fafc; }
    #editableTable td { padding: 6px 4px; vertical-align: middle; }
    #editableTable td input {
        width: 100%; border: 1px solid transparent; border-radius: 6px;
        padding: 6px 10px; font-size: 14px; font-family: 'Inter', sans-serif;
        background: transparent; outline: none; transition: all .2s; color: #1e293b;
    }
    #editableTable td input:focus { border-color: #0067b1; background: #eff8ff; }
    #editableTable td input.num-col { text-align: center; font-weight: 700; color: #0067b1; }
    #editableTable td.td-del { text-align: center; }

    .btn-add-row { margin-top: 12px; background: #f1f5f9; border: 1.5px dashed #94a3b8; color: #475569; border-radius: 8px; padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .2s; display: flex; align-items:center; gap:5px; }
    .btn-add-row:hover { background: #e0f2fe; border-color: #0067b1; color: #0067b1; }
    .btn-del-row { background: none; border: none; cursor: pointer; color: #cbd5e0; font-size: 18px; line-height:1; transition: color .2s; }
    .btn-del-row:hover { color: #ef4444; }

    .cm-actions { display: flex; gap: 10px; margin-top: 16px; flex-wrap: wrap; }
    .btn-generate { background: linear-gradient(135deg, #1a365d, #2c5282); color: #fff; border: none; border-radius: 10px; padding: 10px 22px; font-size: 14px; font-weight: 700; cursor: pointer; display:flex; align-items:center; gap:6px; transition: opacity .2s; }
    .btn-generate:hover { opacity: .9; }
    .btn-clear { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 18px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all .2s; }
    .btn-clear:hover { background: #fee2e2; color: #dc2626; border-color: #fca5a5; }
    .btn-paste-hint { font-size: 12px; color: #0067b1; background: #eff8ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 7px 12px; display:flex; align-items:center; gap:5px; }

    /* ── Panel Consolidado ────────────────────────────────────── */
    .cm-consolidado { background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%); border-radius: 16px; padding: 20px; color: white; box-shadow: 0 8px 30px rgba(26,54,93,.35); }
    .cm-cons-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing:1.5px; opacity:.8; margin-bottom: 14px; display:flex; align-items:center; gap:5px; }
    .cm-stats-row { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
    .cm-stat-main { display: flex; flex-direction: column; align-items: center; background: rgba(255,255,255,.15); padding: 10px 14px; border-radius: 12px; min-width: 80px; }
    .cm-stat-main span:first-child { font-size: 42px; font-weight: 800; line-height:1; }
    .cm-stat-main span:last-child { font-size: 12px; opacity:.8; font-weight:700; margin-top:2px; }
    .cm-stats-detail { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; flex:1; }
    .cm-det-pill { display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 8px 4px; border-radius:10px; }
    .cm-det-pill i { font-size:22px; margin-bottom:3px; }
    .cm-det-pill strong { font-size:22px; font-weight:800; }
    .cm-det-pill span { font-size:10px; font-weight:700; text-transform:uppercase; opacity:.85; }

    /* Donut */
    #donutWrap { position: relative; width: 180px; height: 180px; margin: 0 auto 16px; }
    #donutCenter { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; pointer-events:none; }
    #donutCenter .total-num { font-size:36px; font-weight:800; color:#fff; line-height:1; }
    #donutCenter .total-lbl { font-size:11px; color:rgba(255,255,255,.7); font-weight:600; }

    /* Barras tipo */
    #tiposListWrap { max-height: 260px; overflow-y: auto; padding-right: 4px; }
    #tiposListWrap::-webkit-scrollbar { width: 4px; }
    #tiposListWrap::-webkit-scrollbar-thumb { background: rgba(255,255,255,.25); border-radius:4px; }
    .tipo-bar-item { margin-bottom: 10px; }
    .tipo-bar-header { display:flex; justify-content:space-between; align-items:center; margin-bottom: 4px; }
    .tipo-bar-name { font-size: 12px; font-weight: 600; color: rgba(255,255,255,.9); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width: 210px; }
    .tipo-bar-count { font-size: 12px; font-weight: 800; color: #fff; }
    .tipo-bar-track { background: rgba(255,255,255,.15); border-radius:4px; height:6px; }
    .tipo-bar-fill { height:6px; border-radius:4px; background: linear-gradient(90deg, #60a5fa, #93c5fd); transition: width .6s ease; }

    /* Botón descarga */
    .btn-download { background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.3); color:#fff; border-radius: 10px; padding: 10px 16px; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px; width:100%; justify-content:center; margin-top:16px; transition: all .2s; }
    .btn-download:hover { background: rgba(255,255,255,.25); }
    #downloadSpinner { display:none; }
</style>

<div class="cm-page">
    <div class="cm-title">
        <i class="material-icons" style="color:#0067b1; font-size:28px;">pie_chart</i>
        Consolidado Manual de Equipos
    </div>

    <div class="cm-grid">

        {{-- ════ TABLA EDITABLE ════ --}}
        <div class="cm-card">
            <div class="cm-card-title">
                <i class="material-icons" style="font-size:16px; color:#0067b1;">table_chart</i>
                Ingresa tus datos
            </div>

            <div class="cm-hints">
                💡 <b>Pega desde Excel:</b> Selecciona tus celdas en Excel → <kbd>Ctrl+C</kbd> → haz clic en la primera celda de la columna <b>"Tipo de Equipo"</b> → <kbd>Ctrl+V</kbd>. Los datos se distribuirán automáticamente en las filas. Columnas: <b>Tipo | Operativos | Inoperativos | Mantenimiento</b>
            </div>

            <table id="editableTable">
                <thead>
                    <tr>
                        <th style="width:38%">Tipo de Equipo</th>
                        <th style="width:16%; text-align:center">Operativos</th>
                        <th style="width:16%; text-align:center">Inoperativos</th>
                        <th style="width:16%; text-align:center">Mantenim.</th>
                        <th style="width:14%">Total</th>
                        <th style="width:5%"></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    {{-- Rows inserted by JS --}}
                </tbody>
            </table>

            <button class="btn-add-row" onclick="addRow()">
                <i class="material-icons" style="font-size:16px;">add</i> Agregar fila
            </button>

            <div class="cm-actions">
                <button class="btn-generate" onclick="generateChart()">
                    <i class="material-icons" style="font-size:18px;">auto_graph</i>
                    Generar Consolidado
                </button>
                <button class="btn-clear" onclick="clearTable()">
                    <i class="material-icons" style="font-size:16px;">clear_all</i>
                    Limpiar
                </button>
            </div>
        </div>

        {{-- ════ PANEL CONSOLIDADO ════ --}}
        <div>
            <div class="cm-consolidado" id="consolidadoPanel">
                <div class="cm-cons-title">
                    <i class="material-icons" style="font-size:14px;">pie_chart</i>
                    Consolidado de Equipos
                </div>

                {{-- Totales --}}
                <div class="cm-stats-row">
                    <div class="cm-stat-main">
                        <span id="c_total">0</span>
                        <span>TOTAL</span>
                    </div>
                    <div class="cm-stats-detail">
                        <div class="cm-det-pill" style="background:rgba(239,68,68,.15); border:1px solid rgba(239,68,68,.25);">
                            <i class="material-icons" style="color:#f87171;">cancel</i>
                            <strong id="c_inop">0</strong>
                            <span>Inoperativos</span>
                        </div>
                        <div class="cm-det-pill" style="background:rgba(245,158,11,.15); border:1px solid rgba(245,158,11,.25);">
                            <i class="material-icons" style="color:#fbbf24;">engineering</i>
                            <strong id="c_mant">0</strong>
                            <span>Mantenimiento</span>
                        </div>
                    </div>
                </div>

                {{-- Distribución Donut --}}
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; opacity:.7; margin-bottom:10px; display:flex; align-items:center; gap:5px;">
                    <i class="material-icons" style="font-size:13px;">donut_large</i> Distribución
                </div>
                <div id="donutWrap">
                    <canvas id="donutChart"></canvas>
                    <div id="donutCenter">
                        <div class="total-num" id="donut_total">0</div>
                        <div class="total-lbl">TOTAL</div>
                    </div>
                </div>

                {{-- Barras por tipo --}}
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; opacity:.7; margin-bottom:10px; display:flex; align-items:center; gap:5px;">
                    <i class="material-icons" style="font-size:13px;">bar_chart</i> Por Tipo
                </div>
                <div id="tiposListWrap">
                    <p id="tiposEmpty" style="color:rgba(255,255,255,.5); font-size:13px; text-align:center; padding:20px 0;">
                        Ingresa datos y presiona<br><b>"Generar Consolidado"</b>
                    </p>
                </div>

                <button class="btn-download" onclick="downloadImage()">
                    <span id="downloadSpinner"><i class="material-icons" style="font-size:16px; animation:spin 1s linear infinite;">refresh</i></span>
                    <i class="material-icons" style="font-size:16px;" id="downloadIcon">download</i>
                    Descargar Imagen
                </button>
            </div>
        </div>

    </div>
</div>

<style>
@keyframes spin { from{ transform:rotate(0deg); } to{ transform:rotate(360deg); } }
</style>

<script>
// ─── Estado ───────────────────────────────────────────────────
window._cmDonutInstance = window._cmDonutInstance || null;
const COLORS = [
    '#60a5fa','#34d399','#f472b6','#fbbf24','#a78bfa',
    '#fb923c','#22d3ee','#4ade80','#f87171','#818cf8',
    '#e879f9','#facc15','#2dd4bf','#f97316','#c084fc'
];

// ─── Tabla Editable ───────────────────────────────────────────
function createRow(tipo='', op='', inop='', mant='') {
    const tr = document.createElement('tr');
    const total = (parseInt(op)||0) + (parseInt(inop)||0) + (parseInt(mant)||0);
    tr.innerHTML = `
        <td><input type="text"   class="tipo-col"  placeholder="Ej: Ambulancia Ford F-350" value="${tipo}" onchange="recalcRow(this)"></td>
        <td><input type="number" class="num-col"   placeholder="0" min="0" value="${op}"   onchange="recalcRow(this)"></td>
        <td><input type="number" class="num-col"   placeholder="0" min="0" value="${inop}" onchange="recalcRow(this)"></td>
        <td><input type="number" class="num-col"   placeholder="0" min="0" value="${mant}" onchange="recalcRow(this)"></td>
        <td><input type="number" class="num-col"   readonly value="${total||''}" style="color:#1e293b; background:#f8fafc; cursor:default;" tabindex="-1"></td>
        <td class="td-del"><button class="btn-del-row" title="Eliminar fila" onclick="this.closest('tr').remove()"><i class="material-icons">close</i></button></td>
    `;
    // Paste handler dentro de la celda Tipo
    tr.querySelector('.tipo-col').addEventListener('paste', handleExcelPaste);
    return tr;
}

function addRow(tipo='', op='', inop='', mant='') {
    document.getElementById('tableBody').appendChild(createRow(tipo, op, inop, mant));
}

function recalcRow(input) {
    const tr = input.closest('tr');
    const nums = tr.querySelectorAll('input.num-col:not([readonly])');
    let sum = 0;
    nums.forEach(n => sum += parseInt(n.value)||0);
    tr.querySelector('input[readonly]').value = sum || '';
}

function clearTable() {
    document.getElementById('tableBody').innerHTML = '';
    for(let i=0;i<5;i++) addRow();
    resetConsolidado();
}

// ─── PEGAR DESDE EXCEL ────────────────────────────────────────
function handleExcelPaste(e) {
    const pasteData = (e.clipboardData || window.clipboardData).getData('text');
    if (!pasteData || (!pasteData.includes('\n') && !pasteData.includes('\t'))) return;

    e.preventDefault();
    const lines = pasteData.trim().split('\n');
    const tbody = document.getElementById('tableBody');

    // Posición de la fila actual
    const currentRow = e.target.closest('tr');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    let startIdx = rows.indexOf(currentRow);
    if (startIdx === -1) startIdx = rows.length;

    lines.forEach((line, i) => {
        const cols = line.split('\t').map(c => c.trim().replace(/\r/g,''));
        const tipo = cols[0] || '';
        const op   = parseInt(cols[1]) || '';
        const inop = parseInt(cols[2]) || '';
        const mant = parseInt(cols[3]) || '';

        let targetRow = rows[startIdx + i];
        if (!targetRow) {
            targetRow = createRow(tipo, op, inop, mant);
            tbody.appendChild(targetRow);
        } else {
            const inputs = targetRow.querySelectorAll('input');
            inputs[0].value = tipo;
            inputs[1].value = op;
            inputs[2].value = inop;
            inputs[3].value = mant;
            recalcRow(inputs[1]);
        }
    });
}

// ─── Generar Gráfico ──────────────────────────────────────────
function generateChart() {
    const rows = document.querySelectorAll('#tableBody tr');
    const tipos = [];
    let totalOp=0, totalInop=0, totalMant=0;

    rows.forEach(tr => {
        const inputs = tr.querySelectorAll('input');
        const tipo = inputs[0].value.trim();
        const op   = parseInt(inputs[1].value)||0;
        const inop = parseInt(inputs[2].value)||0;
        const mant = parseInt(inputs[3].value)||0;
        const total = op + inop + mant;
        if (tipo && total > 0) {
            tipos.push({ nombre: tipo, total, op, inop, mant });
            totalOp   += op;
            totalInop += inop;
            totalMant += mant;
        }
    });

    const grand = totalOp + totalInop + totalMant;

    // Actualizar números
    document.getElementById('c_total').textContent   = grand;
    document.getElementById('c_inop').textContent    = totalInop;
    document.getElementById('c_mant').textContent    = totalMant;
    document.getElementById('donut_total').textContent= grand;

    // Donut
    renderDonut(totalOp, totalInop, totalMant);

    // Barras por tipo
    renderBars(tipos);
}

function renderDonut(op, inop, mant) {
    const ctx = document.getElementById('donutChart').getContext('2d');
    if (window._cmDonutInstance) window._cmDonutInstance.destroy();
    window._cmDonutInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Operativos', 'Inoperativos', 'Mantenimiento'],
            datasets: [{
                data: [op, inop, mant],
                backgroundColor: ['#34d399','#f87171','#fbbf24'],
                borderColor: 'rgba(26,54,93,.5)',
                borderWidth: 2,
                hoverOffset: 6
            }]
        },
        options: {
            cutout: '68%',
            responsive: true,
            plugins: { legend: { display:false }, tooltip: { enabled: true } }
        }
    });
}

function renderBars(tipos) {
    const wrap = document.getElementById('tiposListWrap');
    if (!tipos.length) {
        wrap.innerHTML = '<p id="tiposEmpty" style="color:rgba(255,255,255,.5);font-size:13px;text-align:center;padding:20px 0;">Sin datos válidos para mostrar.</p>';
        return;
    }
    const maxVal = Math.max(...tipos.map(t => t.total));
    wrap.innerHTML = tipos.sort((a,b)=>b.total-a.total).map((t, i) => `
        <div class="tipo-bar-item">
            <div class="tipo-bar-header">
                <span class="tipo-bar-name" title="${t.nombre}">${t.nombre}</span>
                <span class="tipo-bar-count">${t.total}</span>
            </div>
            <div class="tipo-bar-track">
                <div class="tipo-bar-fill" style="width:${(t.total/maxVal*100).toFixed(1)}%; background:${COLORS[i%COLORS.length]};"></div>
            </div>
        </div>
    `).join('');
}

function resetConsolidado() {
    ['c_total','c_inop','c_mant','donut_total'].forEach(id => document.getElementById(id).textContent = '0');
    if (window._cmDonutInstance) { window._cmDonutInstance.destroy(); window._cmDonutInstance = null; }
    document.getElementById('tiposListWrap').innerHTML = '<p id="tiposEmpty" style="color:rgba(255,255,255,.5);font-size:13px;text-align:center;padding:20px 0;">Ingresa datos y presiona<br><b>"Generar Consolidado"</b></p>';
}

// ─── Descarga ─────────────────────────────────────────────────
async function downloadImage() {
    const panel = document.getElementById('consolidadoPanel');
    const btnIcon = document.getElementById('downloadIcon');
    const spinner = document.getElementById('downloadSpinner');
    btnIcon.style.display = 'none';
    spinner.style.display = 'inline-block';
    try {
        const canvas = await html2canvas(panel, { scale: 2, backgroundColor: null, useCORS: true });
        const link = document.createElement('a');
        link.download = `Consolidado_Equipos_${new Date().toISOString().slice(0,10)}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    } finally {
        btnIcon.style.display = '';
        spinner.style.display = 'none';
    }
}

// ─── Init ─────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    for(let i=0;i<6;i++) addRow();
});
window.addEventListener('spa:contentLoaded', function() {
    if (!document.getElementById('editableTable')) return;
    if (!document.getElementById('tableBody').children.length) {
        for(let i=0;i<6;i++) addRow();
    }
});
</script>
@endsection
