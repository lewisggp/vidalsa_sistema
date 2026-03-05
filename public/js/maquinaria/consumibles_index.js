/**
 * consumibles_index.js - JS Logic for Consumibles (SPA Compatible)
 */
if (typeof window.ModuleManager !== 'undefined') {
    ModuleManager.register('consumibles_index',
        () => document.getElementById('consumiblesAppRoot') !== null,
        () => {
            // console.log('Initializing Consumibles Module');
            const appRoot = document.getElementById('consumiblesAppRoot');
            if (!appRoot) return;

            // 1. Initialize Tom Select for all searchable selects
            const selects = document.querySelectorAll('select.searchable-select');
            selects.forEach(selectEl => {
                if (selectEl.tomselect) {
                    selectEl.tomselect.destroy();
                }
                new TomSelect(selectEl, {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    },
                    placeholder: "Seleccionar/Buscar...",
                    maxOptions: null
                });
            });

            // Expose logic to global scope so inline onclicks work
            window.CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            // Rebind logic...
            window.ejecutarMatch = function () {
                const btn = document.getElementById('btnMatch');
                if (!btn) return;
                const progress = document.getElementById('matchProgress');
                const bar = document.getElementById('matchBar');
                const results = document.getElementById('matchResults');
                const body = document.getElementById('matchResultsBody');
                const routeMatch = appRoot.dataset.routeMatch;

                btn.disabled = true;
                btn.innerHTML = '<i class="material-icons" style="font-size:20px; animation:spin 1s linear infinite;">refresh</i> Procesando...';
                if (progress) progress.style.display = 'block';
                if (results) results.style.display = 'none';

                let pct = 0;
                const ticker = setInterval(() => {
                    pct = Math.min(pct + 3, 85);
                    if (bar) bar.style.width = pct + '%';
                }, 100);

                fetch(routeMatch, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.CSRF,
                        'Accept': 'application/json',
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        clearInterval(ticker);
                        if (bar) bar.style.width = '100%';
                        const p = document.getElementById('cnt-pendientes'); if (p) p.textContent = 0;
                        const c = document.getElementById('cnt-confirmados'); if (c) c.textContent = data.confirmados;
                        const sm = document.getElementById('cnt-sinmatch'); if (sm) sm.textContent = data.sin_match;
                        if (body) {
                            body.innerHTML = data.detalle.map(r => {
                                const modoLabel = {
                                    'placa': '🔵 placa exacta (doc.)',
                                    'placa_parcial': '🟣 placa parcial (doc.)',
                                    'codigo_patio': '🔷 código patio',
                                    'serial_exacto': '🟢 serial exacto',
                                    'serial_parcial': '🟡 serial parcial',
                                }[r.modo] || (r.modo ? r.modo : 'sin identificador');
                                return `<div class="match-result-row">
                                <span class="mr-id">${r.identificador}</span>
                                ${r.estado === 'CONFIRMADO'
                                        ? `<span class="mr-match">✓ ${r.match} <span style="opacity:.6;font-size:11px;">(${modoLabel})</span></span>`
                                        : `<span class="mr-none">✗ Sin coincidencia <span style="opacity:.6;font-size:11px;">(buscado como: ${modoLabel})</span></span>`
                                    }
                            </div>`;
                            }).join('');
                        }
                        if (results) results.style.display = 'block';
                        btn.innerHTML = `<i class="material-icons" style="font-size:18px;">check_circle</i> Listo — ${data.confirmados} confirmados, ${data.sin_match} sin match`;
                        btn.style.background = 'linear-gradient(135deg,#059669,#047857)';
                        setTimeout(() => location.reload(), 2500);
                    })
                    .catch(err => {
                        clearInterval(ticker);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="material-icons" style="font-size:20px;">bolt</i> Reintentar Match';
                        btn.style.background = 'linear-gradient(135deg,#dc2626,#b91c1c)';
                        console.error('Error match:', err);
                    });
            };

            window.editarId = function (id, valorActual) {
                const celda = document.getElementById('id-cell-' + id);
                if (!celda) return;
                celda.innerHTML = `
                    <div style="display:flex;align-items:center;gap:4px;">
                        <input id="inp-id-${id}" type="text" value="${valorActual}"
                               style="font-family:monospace;font-size:12px;padding:3px 6px;border:1px solid #0067b1;
                                      border-radius:6px;flex:1;min-width:0;outline:none;"
                               onkeydown="if(event.key==='Enter')guardarId(${id});if(event.key==='Escape')cancelarId(${id},'${valorActual.replace(/'/g, "\\\\'")}')">
                        <button onclick="guardarId(${id})"
                                style="background:#0067b1;color:#fff;border:none;border-radius:6px;padding:3px 7px;cursor:pointer;font-size:11px;font-weight:700;">
                            ✓
                        </button>
                        <button onclick="cancelarId(${id},'${valorActual.replace(/'/g, "\\\\'")}',${id})"
                                style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e0;border-radius:6px;padding:3px 7px;cursor:pointer;font-size:11px;">
                            ✕
                        </button>
                    </div>`;
                document.getElementById('inp-id-' + id).focus();
            };

            window.guardarId = function (id) {
                const inp = document.getElementById('inp-id-' + id);
                if (!inp) return;
                const nuevo = inp.value.trim().toUpperCase();
                if (!nuevo) { inp.style.border = '1px solid #ef4444'; return; }

                inp.disabled = true;

                fetch(`/admin/consumibles/${id}/identificador`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF },
                    body: JSON.stringify({ identificador: nuevo }),
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.ok) {
                            const celda = document.getElementById('id-cell-' + id);
                            if (!celda) return;
                            celda.innerHTML = `
                            <div style="display:flex;align-items:center;gap:5px;">
                                <span id="id-txt-${id}" style="flex:1;color:#059669;font-weight:700;">${nuevo}</span>
                                <button onclick="editarId(${id},'${nuevo}')"
                                        style="background:none;border:none;cursor:pointer;padding:2px;color:#94a3b8;" title="Editar identificador">
                                    <i class="material-icons" style="font-size:14px;">edit</i>
                                </button>
                            </div>`;
                            setTimeout(() => {
                                const txt = document.getElementById('id-txt-' + id);
                                if (txt) txt.style.color = '#1e293b';
                            }, 2000);
                        }
                    })
                    .catch(() => {
                        const inp2 = document.getElementById('inp-id-' + id);
                        if (inp2) { inp2.disabled = false; inp2.style.border = '1px solid #ef4444'; }
                    });
            };

            window.cancelarId = function (id, valorOriginal) {
                const celda = document.getElementById('id-cell-' + id);
                if (!celda) return;
                const v = (valorOriginal || '').replace(/'/g, "\\\\'");
                celda.innerHTML = `
                    <div style="display:flex;align-items:center;gap:5px;">
                        <span id="id-txt-${id}" style="flex:1;">${valorOriginal || '—'}</span>
                        <button onclick="editarId(${id},'${v}')"
                                style="background:none;border:none;cursor:pointer;padding:2px;color:#94a3b8;" title="Editar identificador">
                            <i class="material-icons" style="font-size:14px;">edit</i>
                        </button>
                    </div>`;
            };

            window.editarFrente = function (id, idActual) {
                const celda = document.getElementById('frente-cell-' + id);
                if (!celda) return;

                let frentesList = [];
                try {
                    frentesList = JSON.parse(appRoot.dataset.frentes || '[]');
                } catch (e) { }

                const opcionesHtml = frentesList.map(f =>
                    `<option value="${f.id}" ${f.id == idActual ? 'selected' : ''}>${f.nombre}</option>`
                ).join('');

                celda.innerHTML = `
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <select id="sel-frente-${id}"
                                style="font-size:12px;padding:4px 6px;border:1px solid #0067b1;border-radius:6px;width:100%;">
                            ${opcionesHtml}
                        </select>
                        <div style="display:flex;gap:4px;">
                            <button onclick="guardarFrente(${id})"
                                    style="flex:1;background:#0067b1;color:#fff;border:none;border-radius:6px;padding:4px;cursor:pointer;font-size:11px;font-weight:700;">
                                ✓ Guardar
                            </button>
                            <button onclick="cancelarFrente(${id})"
                                    style="flex:1;background:#f1f5f9;color:#475569;border:1px solid #cbd5e0;border-radius:6px;padding:4px;cursor:pointer;font-size:11px;">
                                ✕
                            </button>
                        </div>
                    </div>`;
            };

            window.guardarFrente = function (id) {
                const sel = document.getElementById('sel-frente-' + id);
                if (!sel) return;
                const idFrente = sel.value;
                const nombre = sel.options[sel.selectedIndex].text;
                sel.disabled = true;

                fetch(`/admin/consumibles/${id}/frente`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF },
                    body: JSON.stringify({ id_frente: idFrente }),
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.ok) {
                            const celda = document.getElementById('frente-cell-' + id);
                            if (!celda) return;
                            celda.innerHTML = `
                            <div style="display:flex;align-items:center;gap:4px;">
                                <span id="frente-txt-${id}" style="flex:1;color:#059669;font-weight:700;">${data.nombre_frente}</span>
                                <button onclick="editarFrente(${id}, ${idFrente})"
                                        style="background:none;border:none;cursor:pointer;padding:2px;color:#94a3b8;" title="Cambiar frente">
                                    <i class="material-icons" style="font-size:14px;">edit</i>
                                </button>
                            </div>`;
                            setTimeout(() => {
                                const txt = document.getElementById('frente-txt-' + id);
                                if (txt) txt.style.color = '#1e293b';
                            }, 2000);
                        }
                    })
                    .catch(() => { if (sel) { sel.disabled = false; sel.style.border = '1px solid #ef4444'; } });
            };

            window.cancelarFrente = function (id) {
                location.reload();
            };
        }
    );
}
