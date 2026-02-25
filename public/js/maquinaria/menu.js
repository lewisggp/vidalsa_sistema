// menu.js - Dashboard Interaction Logic
// Handlers for "Movilizaciones Hoy" and "Alertas Documentos" cards

// Assign directly to window to ensure global availability across SPA navigation
// No IIFE wrapper to prevent any scoping issues

window.toggleExpiredDocs = function () {
    const expiredContainer = document.getElementById('expiredDocsContainer');
    const pendingContainer = document.getElementById('pendingMovsContainer');

    if (!expiredContainer) return;

    if (expiredContainer.style.display === 'none') {
        expiredContainer.style.display = 'flex';
        // Close the other list if it exists
        if (pendingContainer) pendingContainer.style.display = 'none';
    } else {
        expiredContainer.style.display = 'none';
    }
};

window.togglePendingMovs = function () {
    const pendingContainer = document.getElementById('pendingMovsContainer');
    const expiredContainer = document.getElementById('expiredDocsContainer');

    if (!pendingContainer) return;

    if (pendingContainer.style.display === 'none') {
        pendingContainer.style.display = 'flex';
        // Close the other list if it exists
        if (expiredContainer) expiredContainer.style.display = 'none';
    } else {
        pendingContainer.style.display = 'none';
    }
};

console.log('✅ Menu Dashboard Functions Loaded (Global Scope)');

// Function to refresh alerts list via AJAX without page reload
window.refreshDashboardAlerts = async function () {
    const listContainer = document.getElementById('dashboardAlertsList');
    if (!listContainer) return;

    try {
        // Add timestamp to prevent browser caching
        const response = await fetch(`/dashboard/alerts-html?t=${Date.now()}`);
        if (!response.ok) throw new Error('Network response was not ok');

        const data = await response.json();

        // Update List HTML
        if (data.html) {
            listContainer.innerHTML = data.html;
            // Re-apply fade-in effect if desired
            listContainer.style.opacity = '0';
            setTimeout(() => {
                listContainer.style.transition = 'opacity 0.3s ease';
                listContainer.style.opacity = '1';
            }, 50);
        }

        // Update Total Badge (if exists)
        const totalBadge = document.querySelector('.card-yellow .card-value');
        if (totalBadge && data.totalAlerts !== undefined) {
            totalBadge.innerText = data.totalAlerts;
        }

    } catch (error) {
        console.error('Failed to refresh dashboard alerts:', error);
    }
};

// Function to filter dashboard alerts by search input
window.filterDashboardAlerts = function () {
    const input = document.getElementById('alertSearch');
    if (!input) return;

    const normalizeStr = str => str ? str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase() : '';
    const filter = normalizeStr(input.value);

    const list = document.getElementById('dashboardAlertsList');
    if (!list) return;

    const items = list.querySelectorAll('.alert-card');
    let hasVisibleItems = false;

    items.forEach(item => {
        const textToSearch = [
            item.getAttribute('data-placa'),
            item.getAttribute('data-chasis'),
            item.getAttribute('data-motor-serial'),
            item.getAttribute('data-marca'),
            item.getAttribute('data-modelo'),
            item.innerText
        ].map(normalizeStr).join(' ');

        if (textToSearch.indexOf(filter) > -1) {
            item.style.display = "";
            hasVisibleItems = true;
        } else {
            item.style.display = "none";
        }
    });

    let emptyState = document.getElementById('search-empty-state');
    if (!hasVisibleItems && filter.length > 0) {
        if (!emptyState) {
            emptyState = document.createElement('div');
            emptyState.id = 'search-empty-state';
            emptyState.style.padding = '20px';
            emptyState.style.textAlign = 'center';
            emptyState.style.color = '#64748b';
            emptyState.innerHTML = `<p>No se encontraron resultados.</p>`;
            list.appendChild(emptyState);
        } else {
            emptyState.style.display = 'block';
        }
    } else if (emptyState) {
        emptyState.style.display = 'none';
    }
};

window.filterPendingMovs = function () {
    const input = document.getElementById('pendingMovSearch');
    if (!input) return;

    const normalizeStr = str => str ? str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase() : '';
    let val = input.value.trim();
    const isTagSearch = val.startsWith('#');
    const filter = normalizeStr(isTagSearch ? val.substring(1) : val);

    const container = document.getElementById('pendingMovsContainer');
    if (!container) return;

    const items = container.querySelectorAll('.activity-item');
    let hasVisibleItems = false;

    items.forEach(item => {
        const placa = normalizeStr(item.getAttribute('data-placa'));
        const chasis = normalizeStr(item.getAttribute('data-chasis'));
        const etiqueta = normalizeStr(item.getAttribute('data-etiqueta'));
        const fullText = normalizeStr(item.innerText);

        let match = false;
        if (isTagSearch) {
            match = etiqueta.indexOf(filter) > -1;
        } else {
            match = placa.indexOf(filter) > -1 || chasis.indexOf(filter) > -1 || fullText.indexOf(filter) > -1 || etiqueta.indexOf(filter) > -1;
        }

        if (match) {
            item.style.display = "flex";
            hasVisibleItems = true;
        } else {
            item.style.display = "none";
        }
    });

    let emptyState = document.getElementById('movs-search-empty-state');
    const list = container.querySelector('.activity-list');

    if (!hasVisibleItems && val.length > 0) {
        if (!emptyState) {
            emptyState = document.createElement('div');
            emptyState.id = 'movs-search-empty-state';
            emptyState.style.padding = '20px';
            emptyState.style.textAlign = 'center';
            emptyState.style.color = '#64748b';
            emptyState.innerHTML = `<p>No se encontraron equipos pendientes con ese criterio.</p>`;
            list.appendChild(emptyState);
        } else {
            emptyState.style.display = 'block';
        }
    } else if (emptyState) {
        emptyState.style.display = 'none';
    }
};

// Function to start management (replacing tomarResponsabilidad)
window.iniciarGestion = function (equipoId, docType) {
    // CHECK PERMISSION FIRST
    if (typeof window.CAN_UPDATE_INFO !== 'undefined' && window.CAN_UPDATE_INFO === false) {
        if (typeof showModal === 'function') {
            showModal({
                type: 'error',
                title: 'Acceso Denegado',
                message: 'No tienes permisos para realizar esta acción (Actualizar Información).',
                confirmText: 'Entendido',
                hideCancel: true
            });
        } else {
            alert('Acceso Denegado: No tienes permisos para actualizar información.');
        }
        return;
    }

    // Check if modal system exists
    if (typeof showModal === 'function') {
        showModal({
            type: 'info',
            title: 'Iniciar Gestión',
            message: '¿Confirma que su frente comenzará a gestionar este documento? <br><small>Se registrará su frente como responsable de la renovación.</small>',
            confirmText: 'Aceptar',
            cancelText: 'Cancelar',
            onConfirm: async () => {
                await ejecutarIniciarGestion(equipoId, docType);
            }
        });
    } else {
        if (confirm('¿Confirma que comenzará a gestionar este documento?')) {
            ejecutarIniciarGestion(equipoId, docType);
        }
    }
};

async function ejecutarIniciarGestion(equipoId, docType) {
    // Show global preloader
    const preloader = document.getElementById('preloader');
    if (preloader) preloader.style.display = 'flex';

    try {
        const response = await fetch('/dashboard/iniciar-gestion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                equipo_id: equipoId,
                doc_type: docType
            })
        });

        const data = await response.json();
        if (data.success) {
            await refreshDashboardAlerts();
            // Hide preloader after refresh
            if (preloader) preloader.style.display = 'none';
        } else {
            if (preloader) preloader.style.display = 'none';
            throw new Error(data.message || 'Error al iniciar gestión');
        }
    } catch (error) {
        if (preloader) preloader.style.display = 'none';
        console.error('Error:', error);
        if (typeof showModal === 'function') {
            showModal({ type: 'error', title: 'Error', message: error.message });
        } else {
            alert(error.message);
        }
    }
};

// ─────────────────────────────────────────────────────────
// RECIBIR MOVILIZACIÓN — AJAX (sin recarga de página)
// ─────────────────────────────────────────────────────────

/**
 * Refresca la lista de movilizaciones pendientes vía AJAX
 * y actualiza los contadores de las cards.
 */
window.refreshPendingMovs = async function () {
    const listContainer = document.getElementById('pendingMovsList');
    if (!listContainer) return;

    try {
        const response = await fetch(`/dashboard/pending-movs-html?t=${Date.now()}`);
        if (!response.ok) throw new Error('Network error');

        const data = await response.json();

        // Actualizar lista
        if (data.html !== undefined) {
            listContainer.style.opacity = '0';
            setTimeout(() => {
                listContainer.innerHTML = data.html;
                listContainer.style.transition = 'opacity 0.3s ease';
                listContainer.style.opacity = '1';
            }, 100);
        }

        // Actualizar contador "Por Confirmar" (x|N Por Confirmar — card-subtext-inline)
        if (data.pendientes !== undefined) {
            const subtextEl = document.querySelector('.card-blue .card-subtext-inline');
            if (subtextEl) subtextEl.innerText = `| ${data.pendientes} Por Confirmar`;
        }

        // Actualizar contador "Movilizaciones Hoy" (card-value dentro de card-blue)
        if (data.movilizacionesHoy !== undefined) {
            const hoyEl = document.querySelector('.card-blue .card-value');
            if (hoyEl) hoyEl.innerText = data.movilizacionesHoy;
        }

    } catch (error) {
        console.error('refreshPendingMovs error:', error);
    }
};

/**
 * Confirmación y ejecución AJAX de recepción de una movilización.
 * @param {number} movId  - ID de la movilización
 * @param {HTMLElement} btn - referencia al botón clickeado (para feedback visual)
 */
window.recibirMovilizacion = function (movId, btn) {
    if (typeof showModal === 'function') {
        showModal({
            type: 'info',
            title: 'Confirmar Recepción',
            message: '¿Confirmas la recepción de este equipo en tu frente?',
            confirmText: 'Sí, Recibir',
            cancelText: 'Cancelar',
            onConfirm: () => _ejecutarRecepcion(movId, btn)
        });
    } else {
        if (confirm('¿Confirmas la recepción de este equipo?')) {
            _ejecutarRecepcion(movId, btn);
        }
    }
};

async function _ejecutarRecepcion(movId, btn) {
    // Feedback visual inmediato en el botón
    const originalHTML = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="material-icons" style="font-size:16px;animation:spin 0.8s linear infinite;">hourglass_empty</i>';
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch(`/admin/movilizaciones/${movId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: 'RECIBIDO' })
        });

        const data = await response.json();

        if (data.success) {
            // Refrescar solo la lista sin recargar la página
            await refreshPendingMovs();

            if (typeof showModal === 'function') {
                showModal({
                    type: 'success',
                    title: '¡Recepción Confirmada!',
                    message: data.message || 'El equipo fue recibido exitosamente.',
                    confirmText: 'Entendido',
                    hideCancel: true
                });
            }
        } else {
            throw new Error(data.error || data.message || 'Error al confirmar recepción');
        }

    } catch (error) {
        // Restaurar botón en caso de error
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
        console.error('_ejecutarRecepcion error:', error);
        if (typeof showModal === 'function') {
            showModal({ type: 'error', title: 'Error', message: error.message });
        } else {
            alert(error.message);
        }
    }
}
