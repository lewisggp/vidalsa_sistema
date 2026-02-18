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

// Function to start management (replacing tomarResponsabilidad)
window.iniciarGestion = function (equipoId, docType) {
    // Check if modal system exists
    if (typeof showModal === 'function') {
        showModal({
            type: 'info',
            title: 'Confirmación de Seguridad',
            message: 'Para iniciar la gestión, ingrese su contraseña:<br><input type="password" id="auth_password_confirm" style="width: 100%; margin-top: 10px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">',
            confirmText: 'Confirmar',
            cancelText: 'Cancelar',
            onConfirm: async () => {
                const password = document.getElementById('auth_password_confirm').value;
                if (!password) {
                    alert('Debe ingresar su contraseña');
                    return;
                }
                await ejecutarIniciarGestion(equipoId, docType, password);
            }
        });
    } else {
        const password = prompt('Por seguridad, ingrese su contraseña para confirmar:');
        if (password) {
            ejecutarIniciarGestion(equipoId, docType, password);
        }
    }
};

async function ejecutarIniciarGestion(equipoId, docType, password) {
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
                doc_type: docType,
                password: password
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



