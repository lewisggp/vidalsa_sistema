// movilizaciones_index.js - Movilizaciones Module Logic
// Version: 7.3 - Fixed duplicate searchUp variable
console.log('[MOVILIZACIONES] Script v7.3 cargado');

// Global Filter Handler (Isolated from Equipos)
window.selectMovilizacionFilter = function (type, value) {
    // 1. Update Input Values
    if (type === 'frente') {
        const input = document.querySelector('input[name="id_frente"]');
        if (input) input.value = value;
    }

    if (type === 'tipo') {
        const input = document.querySelector('input[name="id_tipo"]');
        if (input) input.value = value;
    }

    if (type === 'search') {
        const input = document.getElementById('searchInput');
        if (input) input.value = value;
        const btn = document.getElementById('btn_clear_search');
        if (btn) btn.style.display = value ? 'block' : 'none';
    }

    // 2. Trigger Reload
    window.loadMovilizaciones();
};


window.loadMovilizaciones = function (url = null) {
    const tableBody = document.getElementById('movilizacionesTableBody');
    if (!tableBody) return;

    let baseUrl = url || window.location.pathname;
    const searchInput = document.getElementById('searchInput');
    const frenteInput = document.querySelector('input[name="id_frente"]');
    const tipoInput = document.querySelector('input[name="id_tipo"]');

    // URL Construction
    const params = new URLSearchParams();
    if (searchInput?.value) params.append('search', searchInput.value);

    // Filter values
    if (frenteInput?.value && frenteInput.value !== 'all') {
        params.append('id_frente', frenteInput.value);
    }
    if (tipoInput?.value && tipoInput.value !== 'all') {
        params.append('id_tipo', tipoInput.value);
    }

    // Maintain pagination if just switching pages via URL click
    if (url && url.includes('page=')) {
        try {
            const urlObj = new URL(url, window.location.origin);
            const page = urlObj.searchParams.get('page');
            if (page) params.set('page', page);
            baseUrl = urlObj.pathname;
        } catch (e) { console.error(e); }
    }

    const finalUrl = baseUrl + (baseUrl.includes('?') ? '&' : '?') + params.toString();
    tableBody.style.opacity = '0.5';
    if (window.showPreloader) window.showPreloader();

    fetch(finalUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            tableBody.innerHTML = data.html;
            tableBody.style.opacity = '1';

            const paginationContainer = document.getElementById('movilizacionesPagination');
            if (paginationContainer) paginationContainer.innerHTML = data.pagination;

            const statsContainer = document.getElementById('statusStatsContainer');
            if (statsContainer && data.statsHtml) {
                statsContainer.innerHTML = data.statsHtml;
            }

            // Update Global Transit Count (Purple Card)
            const totalTransitoEl = document.getElementById('totalTransitoCount');
            if (totalTransitoEl && data.totalTransito !== undefined) {
                totalTransitoEl.innerText = data.totalTransito;
            }

            window.history.pushState(null, '', finalUrl);
            if (window.hidePreloader) window.hidePreloader();
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.style.opacity = '1';
            if (window.hidePreloader) window.hidePreloader();
        });
};

// ===========================================
// RECEPCIÓN DE MOVILIZACIONES (Flujo Normal)
// ===========================================

/**
 * Inicia el proceso de recepción de un equipo en tránsito
 */
window.iniciarRecepcion = function (idMovilizacion, nombreFrente, subdivisiones, idFrenteDestino) {
    const modal = document.getElementById('recepcionModal');
    const form = document.getElementById('formRecepcion');
    const labelFrente = document.getElementById('modalFrenteNombre');
    const seccionSub = document.getElementById('seccionSubdivisiones');
    const patioList = document.getElementById('patioList');
    const inputPatio = document.getElementById('input_patio');
    const labelPatio = document.getElementById('label_patio');
    const inputUbicacion = document.getElementById('input_ubicacion_recepcion');

    if (!modal || !form || !labelFrente) return;

    // Configurar form
    form.action = `/admin/movilizaciones/${idMovilizacion}/status`;
    labelFrente.textContent = nombreFrente;
    inputUbicacion.value = '';

    // Configurar subdivisiones si existen
    if (subdivisiones && subdivisiones.trim() !== '') {
        seccionSub.style.display = 'block';
        patioList.innerHTML = '';
        inputPatio.value = '';
        labelPatio.textContent = 'Seleccione Subdivisión...';
        labelPatio.style.color = '#94a3b8';

        const listaPatios = subdivisiones.split(',');
        listaPatios.forEach(patio => {
            patio = patio.trim();
            if (patio.length > 0) {
                const item = document.createElement('div');
                item.className = 'dropdown-item';
                item.textContent = patio;
                item.onclick = function () {
                    inputPatio.value = patio;
                    labelPatio.textContent = patio;
                    labelPatio.style.color = '#1e293b';
                    // También sugerir en el input libre
                    inputUbicacion.value = patio;
                    // Cerrar dropdown
                    const dd = document.getElementById('patioSelect');
                    if (dd) dd.classList.remove('active');
                };
                patioList.appendChild(item);
            }
        });
    } else {
        seccionSub.style.display = 'none';
        inputPatio.value = '';
    }

    form.onsubmit = function (e) {
        e.preventDefault();
        const btn = document.getElementById('btnConfirmarRecepcion');
        if (btn.disabled) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="material-icons spin">sync</i> Procesando...';

        const formData = new FormData(this);
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modal.style.display = 'none';
                    window.loadMovilizaciones();
                } else {
                    // Mostrar error real del servidor al usuario
                    alert('Error: ' + (data.error || 'No se pudo procesar la recepción'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de comunicación con el servidor. Intente de nuevo.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'Confirmar Recepción';
            });
    };

    modal.style.display = 'flex';
};

// ===========================================
// RECEPCIÓN DIRECTA (NUEVO)
// ===========================================

let rdEquiposSeleccionados = [];

window.abrirRecepcionDirecta = function () {
    const modal = document.getElementById('recepcionDirectaModal');
    if (!modal) return;

    // Reset del formulario
    rdEquiposSeleccionados = [];
    document.getElementById('rdSearchInput').value = '';
    document.getElementById('rdResultados').style.display = 'none';
    document.getElementById('rdSeleccionados').style.display = 'none';
    document.getElementById('rdSubdivisionesContainer').style.display = 'none';
    document.getElementById('rdUbicacionInput').value = '';
    // rdFrenteInput NO se resetea: su valor es el frente del usuario, fijo desde el HTML

    modal.style.display = 'flex';
};

window.cerrarRecepcionDirecta = function () {
    document.getElementById('recepcionDirectaModal').style.display = 'none';
};

window.buscarEquiposRD = function () {
    const search = document.getElementById('rdSearchInput').value.trim();
    if (search.length < 3) return alert('Ingrese al menos 3 caracteres');

    const list = document.getElementById('rdResultadosList');
    const container = document.getElementById('rdResultados');
    list.innerHTML = '<div style="padding: 15px; text-align: center; color: #94a3b8;"><i class="material-icons spin">sync</i> Buscando...</div>';
    container.style.display = 'block';

    fetch(`/admin/movilizaciones/buscar-equipos-recepcion?search=${encodeURIComponent(search)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(r => r.json())
        .then(data => {
            if (data.length === 0) {
                list.innerHTML = '<div style="padding: 15px; text-align: center; color: #94a3b8;">No se encontraron equipos</div>';
                return;
            }

            const searchUp = search.toUpperCase();

            list.innerHTML = '';
            data.forEach(eq => {
                const isSelected = rdEquiposSeleccionados.some(s => s.ID_EQUIPO === eq.ID_EQUIPO);
                const item = document.createElement('div');
                item.className = 'dropdown-item';
                item.style.cssText = `
                    display:flex; align-items:stretch; gap:0;
                    border-bottom:2px solid #f0f4f8;
                    background:${isSelected ? '#f8fafc' : 'white'};
                    opacity:${isSelected ? '0.65' : '1'};
                    transition: background 0.15s;
                `;

                // ── Campo coincidente ──
                const placa = eq.PLACA && eq.PLACA !== 'S/P' ? eq.PLACA : null;
                const serial = eq.SERIAL_CHASIS || null;
                const codigoPatio = eq.CODIGO_PATIO || null;

                let matchBadge = '';
                if (placa && placa.toUpperCase().includes(searchUp)) {
                    matchBadge = `<span style="background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;">🪪 ${placa}</span>`;
                } else if (serial && serial.toUpperCase().includes(searchUp)) {
                    matchBadge = `<span style="background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;">🔩 ${serial}</span>`;
                } else if (codigoPatio && codigoPatio.toUpperCase().includes(searchUp)) {
                    matchBadge = `<span style="background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;">🏷 ${codigoPatio}</span>`;
                }

                const tipo = eq.TIPO ?? '';
                const marca = eq.MARCA ?? '';
                const model = eq.MODELO ?? '';
                const anio = eq.ANIO ? String(eq.ANIO) : '';

                const warningFinalizado = eq.FRENTE_ACTUAL_ESTATUS === 'FINALIZADO'
                    ? `<span style="background:#fef2f2;color:#dc2626;padding:1px 6px;border-radius:10px;font-size:9px;font-weight:700;margin-left:4px;">FRENTE CERRADO</span>`
                    : '';

                // ── Foto ──
                let fotoHtml = '';
                if (eq.FOTO) {
                    // La URL viene en formato /storage/google/ID?v=...
                    // La ruta Drive necesita pasar por la ruta drive.file
                    const driveId = eq.FOTO.replace('/storage/google/', '').split('?')[0];
                    fotoHtml = `
                        <div style="width:72px;min-width:72px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;border-right:1px solid #e2e8f0;overflow:hidden;border-radius:0;">
                            <img src="/storage/google/${driveId}" alt="" loading="lazy"
                                 style="width:72px;height:100%;object-fit:cover;"
                                 onerror="this.parentElement.innerHTML='<span class=\'material-icons\' style=\'font-size:28px;color:#cbd5e0;\'>directions_car</span>';">
                        </div>`;
                } else {
                    fotoHtml = `
                        <div style="width:72px;min-width:72px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;border-right:1px solid #e2e8f0;">
                            <span class="material-icons" style="font-size:28px;color:#cbd5e0;">directions_car</span>
                        </div>`;
                }

                // ── Chips de identificadores ──
                let chips = '';
                if (codigoPatio) chips += `<span style="background:#f1f5f9;color:#475569;padding:2px 7px;border-radius:10px;font-size:10px;font-weight:600;">ID: ${codigoPatio}</span> `;
                if (placa) chips += `<span style="background:#f1f5f9;color:#475569;padding:2px 7px;border-radius:10px;font-size:10px;font-weight:600;">P: ${placa}</span> `;

                item.innerHTML = `
                    ${fotoHtml}
                    <div style="flex:1;min-width:0;padding:12px 14px;display:flex;flex-direction:column;gap:5px;">
                        <div style="font-weight:800;font-size:13px;color:#1e293b;line-height:1.3;word-break:break-word;">
                            ${tipo}${warningFinalizado}
                        </div>
                        <div style="font-size:12px;color:#64748b;font-weight:500;">
                            ${[marca, model, anio].filter(Boolean).join(' · ')}
                        </div>
                        ${matchBadge ? `<div style="margin-top:2px;">${matchBadge}</div>` : ''}
                        <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:2px;">
                            ${chips}
                        </div>
                        <div style="font-size:10px;color:#94a3b8;display:flex;align-items:center;gap:3px;margin-top:1px;">
                            <i class="material-icons" style="font-size:11px;">place</i>
                            ${eq.FRENTE_ACTUAL}
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;padding:12px 12px 12px 0;flex-shrink:0;">
                        <button type="button"
                            style="padding:8px 14px;font-size:11px;background:${isSelected ? '#94a3b8' : '#0067b1'};border:none;border-radius:8px;color:white;font-weight:700;white-space:nowrap;"
                            ${isSelected ? 'disabled' : ''}>
                            ${isSelected ? '✓ Agregado' : '+ Agregar'}
                        </button>
                    </div>
                `;

                if (!isSelected) {
                    item.onclick = () => rdAgregarEquipo(eq);
                    item.onmouseover = () => { if (!isSelected) item.style.background = '#f8faff'; };
                    item.onmouseout = () => { item.style.background = 'white'; };
                }
                list.appendChild(item);
            });
        })
        .catch(e => {
            console.error(e);
            list.innerHTML = '<div style="padding: 15px; text-align: center; color: #dc2626;">Error al buscar</div>';
        });
};

function rdAgregarEquipo(eq) {
    if (rdEquiposSeleccionados.some(s => s.ID_EQUIPO === eq.ID_EQUIPO)) return;

    rdEquiposSeleccionados.push(eq);
    rdActualizarSeleccionados();
    window.buscarEquiposRD(); // Re-render results to disable selected
}

function rdRemoverEquipo(id) {
    rdEquiposSeleccionados = rdEquiposSeleccionados.filter(s => s.ID_EQUIPO !== id);
    rdActualizarSeleccionados();
    window.buscarEquiposRD();
}

function rdActualizarSeleccionados() {
    const container = document.getElementById('rdSeleccionados');
    const list = document.getElementById('rdSeleccionadosList');
    const contador = document.getElementById('rdContador');

    if (rdEquiposSeleccionados.length === 0) {
        container.style.display = 'none';
        return;
    }

    container.style.display = 'block';
    contador.textContent = rdEquiposSeleccionados.length;
    list.innerHTML = '';

    rdEquiposSeleccionados.forEach(eq => {
        const tipo = eq.TIPO ?? '';
        const marca = eq.MARCA ? eq.MARCA : '';
        const label = [tipo, marca].filter(Boolean).join(' ');
        const span = document.createElement('span');
        span.style.cssText = 'background:#e0e7ff; color:#3730a3; padding:4px 10px; border-radius:15px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:5px; border:1px solid #c7d2fe;';
        span.innerHTML = `${label || eq.CODIGO_PATIO || 'Equipo'} <i class="material-icons" style="font-size:14px;" onclick="rdRemoverEquipo(${eq.ID_EQUIPO})">cancel</i>`;
        list.appendChild(span);
    });
}

window.filtrarFrentesRD = function (search) {
    search = search.toUpperCase();
    const items = document.querySelectorAll('#rdFrenteList .dropdown-item');
    items.forEach(it => {
        const text = it.textContent.toUpperCase();
        it.style.display = text.includes(search) ? 'block' : 'none';
    });
};

window.seleccionarFrenteRD = function (id, nombre) {
    const input = document.getElementById('rdFrenteInput');
    const label = document.getElementById('rdFrenteLabel');
    const subContainer = document.getElementById('rdSubdivisionesContainer');
    const patioList = document.getElementById('rdPatioList');
    const patioInput = document.getElementById('rdPatioInput');
    const patioLabel = document.getElementById('rdPatioLabel');
    const ubicacionInput = document.getElementById('rdUbicacionInput');

    input.value = id;
    label.textContent = nombre;
    label.style.color = '#1e293b';

    // Cerrar dropdown
    document.getElementById('rdFrenteSelect').classList.remove('active');

    // Fetch subdivisiones
    fetch(`/admin/movilizaciones/subdivisiones/${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.tiene_subdivisiones) {
                subContainer.style.display = 'block';
                patioInput.value = '';
                patioLabel.textContent = 'Seleccionar subdivisión...';
                patioLabel.style.color = '#94a3b8';

                patioList.innerHTML = '';
                data.subdivisiones.forEach(sub => {
                    const item = document.createElement('div');
                    item.className = 'dropdown-item';
                    item.textContent = sub;
                    item.onclick = function () {
                        patioInput.value = sub;
                        patioLabel.textContent = sub;
                        patioLabel.style.color = '#1e293b';
                        ubicacionInput.value = sub;
                        document.getElementById('rdPatioSelect').classList.remove('active');
                    };
                    patioList.appendChild(item);
                });
            } else {
                subContainer.style.display = 'none';
                patioInput.value = '';
            }
        });
};

window.confirmarRecepcionDirecta = function () {
    const ids = rdEquiposSeleccionados.map(s => s.ID_EQUIPO);
    const idFrente = document.getElementById('rdFrenteInput').value;
    const ubicacion = document.getElementById('rdUbicacionInput').value;

    if (ids.length === 0) return alert('Seleccione al menos un equipo');


    const btn = document.getElementById('btnConfirmarRD');
    if (btn.disabled) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="material-icons spin">sync</i> Procesando...';

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    fetch('/admin/movilizaciones/recepcion-directa', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            ids: ids,
            ID_FRENTE_DESTINO: idFrente,
            DETALLE_UBICACION: ubicacion
        })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.cerrarRecepcionDirecta();
                window.loadMovilizaciones();
                alert(data.message);
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(e => {
            console.error(e);
            alert('Error inesperado al procesar');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="material-icons">check_circle</i> Confirmar Recepción';
        });
};

// ===========================================
// UTILS & INITIALIZATION
// ===========================================

function initMovilizacionesListeners() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const val = this.value;
            const clearBtn = document.getElementById('btn_clear_search');
            if (clearBtn) clearBtn.style.display = (val.length > 0) ? 'block' : 'none';

            clearTimeout(window.searchTimeout);
            if (val.length >= 4 || val.length === 0) {
                window.searchTimeout = setTimeout(() => window.loadMovilizaciones(), 1000);
            }
        });
    }
}

function initMovilizaciones() {
    if (!document.getElementById('movilizacionesTableBody')) return;
    initMovilizacionesListeners();
}

// Global toggle for custom dropdowns
window.toggleDropdown = function (id, event) {
    if (event) event.stopPropagation();
    const el = document.getElementById(id);
    if (!el) return;

    // Close other dropdowns
    document.querySelectorAll('.custom-dropdown').forEach(d => {
        if (d.id !== id) d.classList.remove('active');
    });

    el.classList.toggle('active');
};

// Global click outside to close dropdowns
document.addEventListener('click', () => {
    document.querySelectorAll('.custom-dropdown').forEach(d => d.classList.remove('active'));
});

// Register module
if (typeof ModuleManager !== 'undefined') {
    ModuleManager.register('movilizaciones',
        () => document.getElementById('movilizacionesTableBody') !== null,
        initMovilizaciones
    );
}

window.addEventListener('spa:contentLoaded', function () {
    if (document.getElementById('movilizacionesTableBody')) {
        initMovilizaciones();
    }
});
