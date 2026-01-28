// equipos_index.js - Equipos Module Logic
// Version: 2.2 - Global Selection & CSP Fixes

// Use window to ensure persistent state across SPA reloads if the script is re-executed
window.selectedEquipos = window.selectedEquipos || {};

// Global Status Dropdown Logic
window.toggleStatusDropdown = function (trigger) {
    if (!trigger) return;
    document.querySelectorAll('.status-dropdown-menu').forEach(menu => {
        if (menu.previousElementSibling !== trigger) {
            menu.style.display = 'none';
        }
    });

    const menu = trigger.nextElementSibling;
    if (menu) {
        const isHidden = menu.style.display === 'none' || menu.style.display === '';
        menu.style.display = isHidden ? 'block' : 'none';
    }
};

// Selection UI Update Tracker
function updateSelectionUI() {
    const ids = Object.keys(window.selectedEquipos);
    const count = ids.length;
    const bar = document.getElementById('bulkFloatingBar');
    const text = document.getElementById('bulkCountText');

    if (bar && text) {
        if (count > 0) {
            text.innerText = `${count} equipo${count > 1 ? 's' : ''} seleccionado${count > 1 ? 's' : ''}`;
            bar.classList.add('active');
        } else {
            bar.classList.remove('active');
        }
    }
}

// Global Selection Action
window.clearSelection = function (event) {
    // Prevent event bubbling to avoid conflicts
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }

    // Defensive check to prevent re-execution
    if (!window.selectedEquipos || Object.keys(window.selectedEquipos).length === 0) {
        return;
    }

    window.selectedEquipos = {};
    document.querySelectorAll('.selected-row-maquinaria').forEach(row => {
        row.classList.remove('selected-row-maquinaria');
    });
    updateSelectionUI();
};

// Row Double Click Logic (Delegated)
function handleRowDblClick(e) {
    // Look for target row in the equipos table
    const row = e.target.closest('#equiposTableBody tr');
    if (!row) return;

    // Ignore if clicking interactive elements
    if (e.target.closest('button') || e.target.closest('.custom-dropdown') || e.target.closest('.material-icons') || e.target.closest('a') || e.target.closest('input')) return;

    const btnDetails = row.querySelector('.btn-details-mini');
    if (!btnDetails) return;

    const id = btnDetails.dataset.equipoId;
    const code = btnDetails.dataset.codigo;

    if (window.selectedEquipos[id]) {
        delete window.selectedEquipos[id];
        row.classList.remove('selected-row-maquinaria');
    } else {
        window.selectedEquipos[id] = code;
        row.classList.add('selected-row-maquinaria');
    }

    updateSelectionUI();
}

// Attach Global Events Once
if (!window.equiposListenersAttached) {
    document.addEventListener('dblclick', handleRowDblClick);

    document.addEventListener('click', function (e) {
        // Close status dropdowns when clicking outside
        if (!e.target.closest('.custom-dropdown')) {
            document.querySelectorAll('.status-dropdown-menu').forEach(menu => {
                menu.style.display = 'none';
            });
        }

        // 1. Advanced Filter Selection (Model, Year)
        const filterItem = e.target.closest('[data-advanced-filter]');
        if (filterItem) {
            e.preventDefault();
            e.stopPropagation();
            window.selectAdvancedFilter(filterItem.dataset.key, filterItem.dataset.value);
            return;
        }

        // 2. Clear Advanced Filters Button
        const clearBtn = e.target.closest('[data-action="clear-advanced-filters"]');
        if (clearBtn) {
            e.preventDefault();
            e.stopPropagation();
            window.clearAdvancedFilters();
            return;
        }

        // 3. Clear Specific Filter (Generic)
        const clearSpecific = e.target.closest('[data-clear-target]');
        if (clearSpecific) {
            e.preventDefault();
            e.stopPropagation();
            const target = clearSpecific.dataset.clearTarget; // 'id_frente' or 'modelo' etc

            // All filters now use selectAdvancedFilter
            window.selectAdvancedFilter(target, '');
        }
    });

    window.equiposListenersAttached = true;
}

window.enlargeImage = function (src) {
    const overlay = document.getElementById('imageOverlay');
    const img = document.getElementById('enlargedImg');
    if (!overlay || !img) return;
    img.src = src;
    overlay.style.display = 'flex';
};

window.toggleDocFilter = function (type) {
    window.loadEquipos();
};

window.loadEquipos = function (url = null, silent = false) {
    const tableBody = document.getElementById('equiposTableBody');
    if (!tableBody) return;

    let baseUrl = url || window.location.pathname;
    const searchInput = document.getElementById('searchInput');
    const frenteInput = document.getElementById('input_frente_filter');
    const tipoInput = document.getElementById('input_tipo_filter');
    const modeloInput = document.getElementById('searchModelInput');
    const anioInput = document.getElementById('input_anio_filter');

    // Unified Filter Object (Single Source of Truth)
    const filters = {
        search_query: searchInput?.value,
        id_frente: (frenteInput?.value !== '') ? frenteInput?.value : null,
        id_tipo: (tipoInput?.value !== '') ? tipoInput?.value : null,
        modelo: (modeloInput?.value !== '') ? modeloInput?.value : null,
        marca: (document.getElementById('searchMarcaInput')?.value !== '') ? document.getElementById('searchMarcaInput')?.value : null,
        anio: (anioInput?.value !== '') ? anioInput?.value : null,
        filter_propiedad: document.getElementById('chk_propiedad')?.checked ? 'true' : null,
        filter_poliza: document.getElementById('chk_poliza')?.checked ? 'true' : null,
        filter_rotc: document.getElementById('chk_rotc')?.checked ? 'true' : null,
        filter_racda: document.getElementById('chk_racda')?.checked ? 'true' : null
    };

    const params = new URLSearchParams();

    // Cleanly append only valid filter values (non-null, non-empty)
    Object.entries(filters).forEach(([key, value]) => {
        if (value && typeof value === 'string' && value.trim() !== '') {
            params.append(key, value.trim());
        } else if (value && typeof value !== 'string') {
            params.append(key, value);
        }
    });

    console.log('Loading Equipos with standardized params:', params.toString());

    const finalUrl = baseUrl + (baseUrl.includes('?') ? '&' : '?') + params.toString();
    tableBody.style.opacity = '0.5';

    if (!silent && window.showPreloader) window.showPreloader();

    fetch(finalUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (response.status === 419 || response.status === 401) {
                window.location.reload();
                return;
            }
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (!data) return;

            tableBody.innerHTML = data.html;
            tableBody.style.opacity = '1';

            // Re-apply selection highlighting
            tableBody.querySelectorAll('tr').forEach(row => {
                const btn = row.querySelector('.btn-details-mini');
                if (btn && window.selectedEquipos[btn.dataset.equipoId]) {
                    row.classList.add('selected-row-maquinaria');
                }
            });

            const paginationContainer = document.getElementById('equiposPagination');
            if (paginationContainer) paginationContainer.innerHTML = '';

            const statsTotal = document.getElementById('stats_total');
            const statsInactivos = document.getElementById('stats_inactivos');
            const statsMantenimiento = document.getElementById('stats_mantenimiento');
            if (statsTotal) statsTotal.textContent = data.stats.total;
            if (statsInactivos) statsInactivos.textContent = data.stats.inactivos;
            if (statsMantenimiento) statsMantenimiento.textContent = data.stats.mantenimiento;

            const distroContainer = document.getElementById('distributionStatsContainer');
            if (distroContainer) distroContainer.innerHTML = data.distribution;

            window.history.pushState(null, '', finalUrl);
        })
        .catch(error => {
            console.error('Error loading equipos:', error);
            tableBody.style.opacity = '1';
        })
        .finally(() => {
            if (window.hidePreloader) window.hidePreloader();
        });
};

window.selectAdvancedFilter = function (key, value) {
    if (window.searchTimeout) clearTimeout(window.searchTimeout);

    if (key === 'modelo') {
        const input = document.getElementById('searchModelInput');
        if (input) input.value = value;
        const list = document.getElementById('modelList');
        if (list) list.style.display = 'none';
        const btn = document.getElementById('btn_clear_modelo');
        if (btn) btn.style.display = value ? 'block' : 'none';
    }

    if (key === 'anio') {
        const input = document.getElementById('input_anio_filter');
        if (input) input.value = value;
        const labelSpan = document.querySelector('#yearList')?.previousElementSibling?.querySelector('span');
        if (labelSpan) labelSpan.innerText = value || 'Seleccionar Año';
        const list = document.getElementById('yearList');
        if (list) list.style.display = 'none';
        const btn = document.getElementById('btn_clear_anio');
        if (btn) btn.style.display = value ? 'block' : 'none';
    }

    if (key === 'frente') {
        const input = document.getElementById('input_frente_filter');
        if (input) input.value = value;
        const searchInput = document.getElementById('filterSearchInput');
        if (searchInput) searchInput.placeholder = value ? value : 'Filtrar Frente...';
        const btn = document.getElementById('btn_clear_frente');
        if (btn) btn.style.display = value ? 'block' : 'none';
        const dropdown = document.getElementById('frenteFilterSelect');
        if (dropdown) dropdown.classList.remove('active');
    }

    if (key === 'tipo') {
        const input = document.getElementById('input_tipo_filter');
        if (input) input.value = value;
        const searchInput = document.getElementById('filterTipoSearchInput');
        if (searchInput) searchInput.placeholder = value ? value : 'Filtrar Tipo...';
        const btn = document.getElementById('btn_clear_tipo');
        if (btn) btn.style.display = value ? 'block' : 'none';
        const dropdown = document.getElementById('tipoFilterSelect');
        if (dropdown) dropdown.classList.remove('active');
    }

    if (key === 'search') {
        const input = document.getElementById('searchInput');
        if (input) input.value = value;
        const btn = document.getElementById('btn_clear_search');
        if (btn) btn.style.display = value ? 'block' : 'none';
    }

    if (!value) {
        // If clearing a filter, reload to show data based on other remaining filters
        window.loadEquipos();
        return;
    }

    window.loadEquipos();
};

window.clearAdvancedFilters = function () {
    if (window.searchTimeout) clearTimeout(window.searchTimeout);

    const modelInput = document.getElementById('searchModelInput');
    if (modelInput) modelInput.value = '';
    const modelBtn = document.getElementById('btn_clear_modelo');
    if (modelBtn) modelBtn.style.display = 'none';

    const anioInput = document.getElementById('input_anio_filter');
    if (anioInput) anioInput.value = '';
    const anioLabel = document.querySelector('#yearList')?.previousElementSibling?.querySelector('span');
    if (anioLabel) anioLabel.innerText = 'Seleccionar Año';
    const anioBtn = document.getElementById('btn_clear_anio');
    if (anioBtn) anioBtn.style.display = 'none';

    // Clear Doc Filters
    ['chk_propiedad', 'chk_poliza', 'chk_rotc', 'chk_racda'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.checked = false;
    });

    // Must reload to reflect the cleared filters
    window.loadEquipos();
};

window.filterList = function (inputId, listId) {
    const input = document.getElementById(inputId);
    const list = document.getElementById(listId);
    if (!input || !list) return;

    const filter = input.value.toUpperCase();
    const items = list.querySelectorAll('.filter-option-item');

    items.forEach(item => {
        const txt = item.textContent || item.innerText;
        item.style.display = (txt.toUpperCase().indexOf(filter) > -1) ? "" : "none";
    });

    list.style.display = 'block';
};

window.changeStatus = function (id, newStatus, url, element) {
    if (!element) return;
    const dropdown = element.closest('.custom-dropdown');
    if (!dropdown) return;

    const oldStatus = dropdown.getAttribute('data-current-status');
    if (oldStatus === newStatus) {
        window.toggleStatusDropdown(dropdown.querySelector('.status-trigger'));
        return;
    }

    const trigger = dropdown.querySelector('.status-trigger');
    const menu = dropdown.querySelector('.status-dropdown-menu');

    const statusConfig = {
        'OPERATIVO': { color: '#16a34a', icon: 'check_circle', label: 'Operativo' },
        'INOPERATIVO': { color: '#dc2626', icon: 'cancel', label: 'Inoperativo' },
        'EN MANTENIMIENTO': { color: '#d97706', icon: 'engineering', label: 'Mantenimiento' },
        'DESINCORPORADO': { color: '#475569', icon: 'archive', label: 'Desincorp.' }
    };

    const config = statusConfig[newStatus] || statusConfig['DESINCORPORADO'];
    if (trigger) {
        trigger.innerHTML = `
            <div style="display: flex; align-items: center; gap: 6px; color: ${config.color};">
                <i class="material-icons" style="font-size: 16px;">${config.icon}</i>
                <span style="color: #334155;">${config.label}</span>
            </div>
            <i class="material-icons" style="font-size: 16px; color: #94a3b8;">expand_more</i>
        `;
    }
    if (menu) menu.style.display = 'none';

    window.updateLocalStats(oldStatus, newStatus);
    dropdown.setAttribute('data-current-status', newStatus);

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-HTTP-Method-Override': 'PATCH'
        },
        body: JSON.stringify({ status: newStatus })
    })
        .then(response => {
            if (response.status === 419 || response.status === 401) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Update failed:', error);
            window.loadEquipos();
        });
};

window.openBulkModal = function (event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }

    // 1. Validation
    if (!window.selectedEquipos || Object.keys(window.selectedEquipos).length === 0) {
        alert('Por favor seleccione equipos primero.');
        return;
    }

    // 2. Nuclear Cleanup: Remove any existing dynamic modals
    const oldModals = document.querySelectorAll('.dynamic-bulk-modal');
    oldModals.forEach(el => el.remove());

    // 3. Create Overlay (Safe, Isolated Context)
    const overlay = document.createElement('div');
    overlay.className = 'dynamic-bulk-modal';
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100vw';
    overlay.style.height = '100vh';
    overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
    overlay.style.zIndex = '2500'; // Corrected Z-Index (Below Standard Modal 3000, Above Header 1000)
    overlay.style.display = 'flex';
    overlay.style.justifyContent = 'center';
    overlay.style.alignItems = 'center';
    overlay.style.backdropFilter = 'blur(2px)';

    // 4. Create Content Box
    const content = document.createElement('div');
    content.style.backgroundColor = 'white';
    content.style.borderRadius = '16px';
    content.style.width = '90%';
    content.style.maxWidth = '500px';
    content.style.overflow = 'hidden';
    content.style.boxShadow = '0 25px 50px -12px rgba(0,0,0,0.25)';
    content.style.animation = 'slideDown 0.2s ease-out'; // Defined in CSS

    // 5. Header
    const header = document.createElement('div');
    header.style.background = '#1e293b';
    header.style.padding = '20px';
    header.style.color = 'white';
    header.style.display = 'flex';
    header.style.justifyContent = 'center';
    header.style.alignItems = 'center';
    header.style.position = 'relative';
    header.innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="material-icons" style="color: #60a5fa;">local_shipping</i>
            <h2 style="margin: 0; font-size: 18px; font-weight: 700;">Movilización</h2>
        </div>
        <button type="button" id="btnCloseDynamic" style="position: absolute; right: 20px; background: transparent; border: none; color: white; cursor: pointer;">
            <i class="material-icons">close</i>
        </button>
    `;

    // 6. Body & Form Construction
    const body = document.createElement('div');
    body.style.padding = '25px';

    // Generate Equipments List
    let listHtml = '';
    Object.values(window.selectedEquipos).forEach(code => {
        listHtml += `<span style="background: #e2e8f0; padding: 2px 8px; border-radius: 4px; margin-right: 5px; display: inline-block; margin-bottom: 5px; font-size: 12px; color: #64748b;">${code}</span>`;
    });

    // Clone Datalist Options safely from main DOM (extract only <option> elements)
    let optionsHtml = '';
    const existingDatalist = document.querySelector('#frentesList');
    if (existingDatalist) {
        const options = existingDatalist.querySelectorAll('option');
        options.forEach(opt => {
            const value = opt.getAttribute('value') || '';
            const dataId = opt.getAttribute('data-id') || '';
            optionsHtml += `<option value="${value}" data-id="${dataId}"></option>`;
        });
    }

    body.innerHTML = `
        <form id="dynamicBulkForm">

            <div style="margin-bottom: 25px;">
                <label for="dynamicDestInput" style="display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px;">Frente de Destino</label>
                <div style="position: relative;">
                    <i class="material-icons" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #94a3b8;">place</i>
                    <input type="text" id="dynamicDestInput" list="dynamicFrentesList"
                        placeholder="Escriba o busque destino..."
                        autocomplete="off"
                        style="width: 100%; padding: 12px 12px 12px 40px; border: 2px solid #e2e8f0; border-radius: 10px; outline: none; box-sizing: border-box; font-size: 14px;">
                     <datalist id="dynamicFrentesList">
                        ${optionsHtml}
                     </datalist>
                </div>
            </div>

            <button type="submit" style="width: 100%; height: 48px; border-radius: 10px; font-weight: 700; font-size: 15px; background: #0067b1; color: white; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px;">
                <i class="material-icons" style="font-size: 18px;">save</i> Confirmar
            </button>
        </form>
    `;

    // 7. Assemble
    content.appendChild(header);
    content.appendChild(body);
    overlay.appendChild(content);
    document.body.appendChild(overlay);

    // 8. Attach Event Listeners (Directly to new elements)

    // Close Button
    const closeBtn = overlay.querySelector('#btnCloseDynamic');
    closeBtn.onclick = function () { overlay.remove(); };

    // Close on Overlay Click (Optional)
    overlay.onclick = function (e) {
        if (e.target === overlay) overlay.remove();
    };

    // Form Submit
    const form = body.querySelector('#dynamicBulkForm');
    form.onsubmit = function (e) {
        e.preventDefault();

        const destInput = body.querySelector('#dynamicDestInput');
        const dest = destInput ? destInput.value.trim() : '';

        if (!dest) {
            showModal({
                type: 'warning',
                title: 'Campo Requerido',
                message: 'Por favor ingrese un frente de destino.',
                confirmText: 'Entendido',
                hideCancel: true
            });
            return;
        }

        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;

        // Show visual loading state in button
        btn.innerHTML = '<i class="material-icons" style="font-size: 18px; animation: spin 1s linear infinite;">sync</i> Procesando...';
        btn.disabled = true;
        btn.style.opacity = '0.7';
        btn.style.cursor = 'wait';

        const ids = Object.keys(window.selectedEquipos);

        // Show global preloader (may be behind modal)
        if (window.showPreloader) window.showPreloader();

        fetch('/admin/equipos/bulk-mobilize', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids, destination: dest })
        })
            .then(function (res) {
                if (res.status === 419) {
                    if (window.hidePreloader) window.hidePreloader();
                    showModal({
                        type: 'error',
                        title: 'Sesión Expirada',
                        message: 'Su sesión ha expirado. La página se recargará.',
                        confirmText: 'Recargar',
                        hideCancel: true,
                        onConfirm: () => window.location.reload()
                    });
                    return;
                }
                if (!res.ok) throw new Error('Error en la respuesta');
                return res.json();
            })
            .then(function (data) {
                if (!data) return; // Session expired case

                // Hide preloader
                if (window.hidePreloader) window.hidePreloader();

                overlay.remove();
                window.clearSelection();
                window.loadEquipos();

                showModal({
                    type: 'success',
                    title: '¡Operación Exitosa!',
                    message: 'Movilización masiva completada con éxito.',
                    confirmText: 'Aceptar',
                    hideCancel: true
                });
            })
            .catch(function (err) {
                console.error(err);

                // Hide preloader
                if (window.hidePreloader) window.hidePreloader();

                // Restore button state
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';

                showModal({
                    type: 'error',
                    title: 'Error',
                    message: 'Hubo un error al procesar la movilización. Por favor intente nuevamente.',
                    confirmText: 'Entendido',
                    hideCancel: true
                });
            });
    };
};


window.updateLocalStats = function (oldStatus, newStatus) {
    const elOper = document.getElementById('stats_activos');
    const elInop = document.getElementById('stats_inactivos');
    const elMant = document.getElementById('stats_mantenimiento');

    const adjust = (el, amount) => {
        if (el) {
            let val = parseInt(el.textContent.replace(/\D/g, '')) || 0;
            val += amount;
            el.textContent = val < 0 ? 0 : val;
        }
    };

    if (oldStatus === 'OPERATIVO') adjust(elOper, -1);
    if (oldStatus === 'INOPERATIVO' || oldStatus === 'DESINCORPORADO') adjust(elInop, -1);
    if (oldStatus === 'EN MANTENIMIENTO') adjust(elMant, -1);

    if (newStatus === 'OPERATIVO') adjust(elOper, 1);
    if (newStatus === 'INOPERATIVO' || newStatus === 'DESINCORPORADO') adjust(elInop, 1);
    if (newStatus === 'EN MANTENIMIENTO') adjust(elMant, 1);
};

window.exportEquipos = function () {
    const searchInput = document.getElementById('searchInput');
    const frenteInput = document.getElementById('input_frente_filter');
    const tipoInput = document.getElementById('input_tipo_filter');
    const modeloInput = document.getElementById('searchModelInput');
    const anioInput = document.getElementById('input_anio_filter');

    const params = new URLSearchParams();
    if (searchInput?.value) params.append('search_query', searchInput.value);
    if (frenteInput?.value) params.append('id_frente', frenteInput.value);
    if (tipoInput?.value) params.append('id_tipo', tipoInput.value);
    if (modeloInput?.value) params.append('modelo', modeloInput.value);
    if (anioInput?.value) params.append('anio', anioInput.value);

    window.location.href = '/admin/equipos/export?' + params.toString();
};

function initEquipos() {
    if (!document.getElementById('equiposTableBody')) return;

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const val = this.value;
            const clearBtn = document.getElementById('btn_clear_search');
            if (clearBtn) clearBtn.style.display = (val.length > 0) ? 'block' : 'none';

            clearTimeout(window.searchTimeout);
            if (val.length >= 4 || val.length === 0) {
                window.searchTimeout = setTimeout(() => window.loadEquipos(), 600);
            }
        });
    }

    const form = document.getElementById('search-form');
    if (form) {
        form.onsubmit = function (e) {
            e.preventDefault();
            window.loadEquipos();
            return false;
        };
    }

    updateSelectionUI();
}

// Listen for SPA navigation to clear selections when leaving equipos page
window.addEventListener('spa:contentLoaded', function () {
    // Check if we're NOT on the equipos page anymore
    const isOnEquiposPage = document.getElementById('equiposTableBody') !== null;

    if (!isOnEquiposPage && window.selectedEquipos && Object.keys(window.selectedEquipos).length > 0) {
        // Clear selections when navigating away
        window.selectedEquipos = {};
        updateSelectionUI();
    }
});

// Register with Module Manager for SPA compatibility
ModuleManager.register('equipos',
    () => document.getElementById('equiposTableBody') !== null,
    initEquipos
);
