// movilizaciones_index.js - Movilizaciones Module Logic
// Version: 10.0 - Mobile card layout + filter fixes
console.log('[MOVILIZACIONES] Script v10.0 cargado');

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

    // Date range filters
    const fechaDesde = document.getElementById('filterFechaDesde');
    const fechaHasta = document.getElementById('filterFechaHasta');
    if (fechaDesde?.value) params.append('fecha_desde', fechaDesde.value);
    if (fechaHasta?.value) params.append('fecha_hasta', fechaHasta.value);

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
    const inputUbicacion = document.getElementById('input_ubicacion_recepcion');

    if (!modal || !form || !labelFrente) return;

    // Configurar form
    form.action = `/admin/movilizaciones/${idMovilizacion}/status`;
    labelFrente.textContent = nombreFrente;
    inputUbicacion.value = '';

    // Configurar subdivisiones como sugerencias en el input de ubicación
    const allSubs = (subdivisiones && subdivisiones.trim() !== '') ? subdivisiones.split(',').map(s => s.trim()).filter(Boolean) : [];
    loadUbicacionSuggestions('ubicacion-suggestions', allSubs);

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
    document.getElementById('rdUbicacionInput').value = '';

    // Cargar sugerencias del frente fijo del usuario automáticamente
    const idFrente = document.getElementById('rdFrenteInput').value;
    if (idFrente) {
        fetch(`/admin/movilizaciones/subdivisiones/${idFrente}`)
            .then(r => r.json())
            .then(data => {
                const subs = data.tiene_subdivisiones ? (data.subdivisiones || []) : [];
                loadUbicacionSuggestions('rd-ubicacion-suggestions', subs);
            })
            .catch(() => loadUbicacionSuggestions('rd-ubicacion-suggestions', []));
    } else {
        loadUbicacionSuggestions('rd-ubicacion-suggestions', []);
    }

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


            list.innerHTML = '';
            data.forEach(eq => {
                const isSelected = rdEquiposSeleccionados.some(s => s.ID_EQUIPO === eq.ID_EQUIPO);
                const item = document.createElement('div');
                item.id = `card-equipo-${eq.ID_EQUIPO}`; // ID único para manipular DOM directo
                item.className = 'dropdown-item';

                // Estilo dinámico según selección
                const baseStyle = `
                    display:flex; align-items:stretch; gap:0;
                    background:${isSelected ? '#f0f9ff' : 'white'};
                    transition: all 0.2s ease;
                    cursor: default;
                    position: relative;
                    overflow: hidden;
                    min-height: 110px;
                `;

                // Borde y márgenes especiales para seleccionados
                if (isSelected) {
                    item.style.cssText = baseStyle + `
                        border: 2px solid #0067b1; 
                        border-radius: 12px; 
                        transform: scale(0.99);
                        margin-bottom: 8px;
                        box-shadow: 0 4px 6px -1px rgba(0, 103, 177, 0.1);
                    `;
                } else {
                    item.style.cssText = baseStyle + `
                        border-bottom: 1px solid #f1f5f9;
                    `;
                }

                // ── Foto ──
                let fotoHtml = '';
                const radiusStyle = isSelected ? 'border-top-left-radius:10px; border-bottom-left-radius:10px;' : '';

                if (eq.FOTO) {
                    const driveId = eq.FOTO.replace('/storage/google/', '').split('?')[0];
                    fotoHtml = `
                        <div style="width:85px; min-width:85px; align-self:stretch; position:relative; overflow:hidden; background:#f1f5f9; ${radiusStyle}">
                            <img src="/storage/google/${driveId}" alt="" loading="lazy" 
                                style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover;"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display:none; width:100%; height:100%; align-items:center; justify-content:center;">
                                <span class="material-icons" style="font-size:32px; color:#cbd5e0;">directions_car</span>
                            </div>
                        </div>`;
                } else {
                    fotoHtml = `
                        <div style="width:85px; min-width:85px; align-self:stretch; background:#f1f5f9; display:flex; align-items:center; justify-content:center; ${radiusStyle}">
                            <span class="material-icons" style="font-size:32px; color:#cbd5e0;">directions_car</span>
                        </div>`;
                }

                const tipo = eq.TIPO ?? '';
                const marca = eq.MARCA ?? '';
                const model = eq.MODELO ?? '';
                const anio = eq.ANIO ? String(eq.ANIO) : '';
                const serial = eq.SERIAL_CHASIS || null;
                const placa = eq.PLACA && eq.PLACA !== 'S/P' ? eq.PLACA : null;

                const warningFinalizado = eq.FRENTE_ACTUAL_ESTATUS === 'FINALIZADO'
                    ? `<span style="background:#fef2f2;color:#dc2626;padding:1px 6px;border-radius:10px;font-size:9px;font-weight:700;margin-left:4px;">FRENTE CERRADO</span>`
                    : '';

                // CONTENIDO
                item.innerHTML = `
                    <div style="display:flex; flex-direction:row; align-items:stretch; width:100%;">
                        ${fotoHtml}
                        
                        <div style="flex:1; padding:12px 14px; display:flex; flex-direction:column; justify-content:center; gap:3px; min-width:0;">
                            <!-- TIPO -->
                            <div style="font-weight:900; font-size:14px; color:#000000; line-height:1.2; text-transform:uppercase;">
                                ${tipo}${warningFinalizado}
                            </div>
                            <!-- MARCA · MODELO · AÑO -->
                            <div style="font-size:13px; color:#000000; font-weight:700; margin-bottom:4px;">
                                ${[marca, model, anio].filter(Boolean).join(' · ') || '<span style="color:#94a3b8; font-weight:400; font-style:italic;">Sin detalles</span>'}
                            </div>
                            <!-- DATOS -->
                            ${serial ? `<div style="font-size:12px; color:#000000; font-weight:600;">${serial}</div>` : ''}
                            ${placa ? `<div style="font-size:12px; color:#000000; font-weight:600;">P: ${placa}</div>` : ''}
                            <!-- UBICACIÓN -->
                            <div style="font-size:11px; color:#94a3b8; margin-top:4px; display:flex; align-items:center; gap:4px;">
                                <i class="material-icons" style="font-size:12px;">place</i>
                                ${eq.FRENTE_ACTUAL}
                            </div>
                        </div>

                        <!-- INDICADOR CHECK -->
                        ${isSelected ? `
                        <div class="rd-check-indicator" style="display:flex; align-items:center; padding-right:15px;">
                            <div style="width:28px; height:28px; background:#0067b1; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                                <i class="material-icons" style="color:white; font-size:18px;">check</i>
                            </div>
                        </div>` : ''}
                    </div>
                `;

                // LOGICA CLICK TARJETA COMPLETA (Actualización Instantánea)
                item.onclick = function () {
                    const currentlySelected = rdEquiposSeleccionados.some(s => s.ID_EQUIPO === eq.ID_EQUIPO);

                    if (currentlySelected) {
                        // Deseleccionar
                        rdRemoverEquipo(eq.ID_EQUIPO);
                    } else {
                        // Seleccionar
                        rdAgregarEquipo(eq);
                    }
                    // Actualizar visualmente ESTE elemento sin recargar todo
                    rdToggleVisual(document.getElementById(`card-equipo-${eq.ID_EQUIPO}`), !currentlySelected);
                };

                // Hover Effects
                if (!isSelected) {
                    item.onmouseover = () => { item.style.background = '#f8fafc'; };
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
    // No action needed UI-wise (handled by toggleVisual)
}

function rdRemoverEquipo(id) {
    rdEquiposSeleccionados = rdEquiposSeleccionados.filter(s => s.ID_EQUIPO !== id);

    // Si la acción vino de lógica externa (no toggle directo), aseguramos update visual
    const card = document.getElementById(`card-equipo-${id}`);
    if (card) rdToggleVisual(card, false);
}

// Función para actualizar estilos visuales sin re-renderizar
function rdToggleVisual(card, isSelected) {
    if (!card) return;

    // Check mark container
    let checkDiv = card.querySelector('.rd-check-indicator');

    if (isSelected) {
        // APLICAR ESTILO SELECCIONADO
        card.style.background = '#f0f9ff';
        card.style.border = '2px solid #0067b1';
        card.style.borderRadius = '12px';
        card.style.transform = 'scale(0.99)';
        card.style.marginBottom = '8px';
        card.style.boxShadow = '0 4px 6px -1px rgba(0, 103, 177, 0.1)';

        // Bordes foto
        const fotoDiv = card.querySelector('div[style*="min-width:85px"]');
        if (fotoDiv) {
            fotoDiv.style.borderTopLeftRadius = '10px';
            fotoDiv.style.borderBottomLeftRadius = '10px';
        }

        // Mostrar Check
        if (!checkDiv) {
            checkDiv = document.createElement('div');
            checkDiv.className = 'rd-check-indicator';
            checkDiv.style.cssText = 'display:flex; align-items:center; padding-right:15px;';
            checkDiv.innerHTML = `
                <div style="width:28px; height:28px; background:#0067b1; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                    <i class="material-icons" style="color:white; font-size:18px;">check</i>
                </div>`;
            // Insertar al final del contenedor flex principal (hijo directo del card)
            card.firstElementChild.appendChild(checkDiv);
        }

        // Desactivar hover fx
        card.onmouseover = null;
        card.onmouseout = null;

    } else {
        // QUITAR ESTILO SELECCIONADO (Volver a normal)
        card.style.background = 'white';
        card.style.border = ''; // Reset
        card.style.borderBottom = '1px solid #f1f5f9';
        card.style.borderRadius = '';
        card.style.transform = '';
        card.style.marginBottom = '';
        card.style.boxShadow = '';

        // Bordes foto reset
        const fotoDiv = card.querySelector('div[style*="min-width:85px"]');
        if (fotoDiv) {
            fotoDiv.style.borderTopLeftRadius = '0';
            fotoDiv.style.borderBottomLeftRadius = '0';
        }

        // Quitar Check
        if (checkDiv) checkDiv.remove();

        // Restaurar Hover
        card.onmouseover = () => { card.style.background = '#f8fafc'; };
        card.onmouseout = () => { card.style.background = 'white'; };
    }
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
    const ubicacionInput = document.getElementById('rdUbicacionInput');

    input.value = id;
    label.textContent = nombre;
    label.style.color = '#1e293b';

    // Cerrar dropdown
    document.getElementById('rdFrenteSelect').classList.remove('active');

    // Fetch subdivisiones del frente para sugerencias
    fetch(`/admin/movilizaciones/subdivisiones/${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.tiene_subdivisiones) {
                const subsList = data.subdivisiones || [];
                loadUbicacionSuggestions('rd-ubicacion-suggestions', subsList);
            } else {
                loadUbicacionSuggestions('rd-ubicacion-suggestions', []);
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

// ─── Date Filter Toggle ─────────────────────────────────────────────────────
window.advancedFilterOpen = false;

window.toggleAdvancedFilter = function (e) {
    if (e) e.stopPropagation(); // Evita que el click burbujee al document listener
    const panel = document.getElementById('advancedFilterPanel');
    const btn = document.getElementById('btnAdvancedFilter');
    if (!panel) return;

    window.advancedFilterOpen = !window.advancedFilterOpen;

    if (window.advancedFilterOpen) {
        panel.style.display = 'block';
        btn.style.background = '#e1effa';
        btn.style.borderColor = '#0067b1';
        btn.style.color = '#0067b1';
    } else {
        panel.style.display = 'none';
        btn.style.background = 'white';
        btn.style.borderColor = '#cbd5e0';
        btn.style.color = '#64748b';
    }
};

// Cerrar el panel al hacer click fuera
document.addEventListener('click', function (e) {
    const panel = document.getElementById('advancedFilterPanel');
    const btn = document.getElementById('btnAdvancedFilter');
    if (!panel || !window.advancedFilterOpen) return;
    if (!panel.contains(e.target) && !btn.contains(e.target)) {
        panel.style.display = 'none';
        btn.style.background = 'white';
        btn.style.borderColor = '#cbd5e0';
        btn.style.color = '#64748b';
        window.advancedFilterOpen = false;
    }
});


window.clearDateFilters = function () {
    const desde = document.getElementById('filterFechaDesde');
    const hasta = document.getElementById('filterFechaHasta');
    if (desde) desde.value = '';
    if (hasta) hasta.value = '';
    window.loadMovilizaciones();
};

// ─── Sugerencias de Ubicación ────────────────────────────────────────────────

// Llena el dropdown de sugerencias con un array de strings
window.loadUbicacionSuggestions = function (containerId, items) {
    const box = document.getElementById(containerId);
    if (!box) return;
    box.innerHTML = '';
    box._allItems = items || [];
    _renderSuggestions(box, items);
};

function _renderSuggestions(box, items) {
    box.innerHTML = '';
    if (!items || items.length === 0) { box.style.display = 'none'; return; }

    // Busca el input de texto dentro del mismo contenedor padre
    const input = box.closest('div').querySelector('input[type="text"]');

    items.forEach(item => {
        const d = document.createElement('div');
        d.textContent = item;
        d.style.cssText = 'padding: 9px 14px; font-size: 13px; color: #1e293b; cursor: default; border-bottom: 1px solid #f1f5f9; transition: background 0.15s;';
        d.onmouseover = () => d.style.background = '#f0f9ff';
        d.onmouseout = () => d.style.background = 'white';
        d.onmousedown = (e) => {
            e.preventDefault(); // Evita que onblur oculte antes del click
            if (input) input.value = item;
            box.style.display = 'none';
        };
        box.appendChild(d);
    });
}

window.showUbicacionSuggestions = function (containerId) {
    const box = document.getElementById(containerId);
    if (!box || !box._allItems || box._allItems.length === 0) return;
    const input = box.parentElement.querySelector('input[type="text"]');
    const typed = input ? input.value.trim().toUpperCase() : '';
    const filtered = typed ? box._allItems.filter(i => i.toUpperCase().includes(typed)) : box._allItems;
    if (filtered.length === 0) { box.style.display = 'none'; return; }
    _renderSuggestions(box, filtered);
    box.style.display = 'block';
};

window.hideUbicacionSuggestions = function (containerId) {
    const box = document.getElementById(containerId);
    if (box) box.style.display = 'none';
};

window.filterUbicacionSuggestions = function (input, containerId) {
    const box = document.getElementById(containerId);
    if (!box || !box._allItems) return;
    const typed = input.value.trim().toUpperCase();
    const filtered = typed ? box._allItems.filter(i => i.toUpperCase().includes(typed)) : box._allItems;
    if (filtered.length === 0) { box.style.display = 'none'; return; }
    _renderSuggestions(box, filtered);
    box.style.display = 'block';
};
