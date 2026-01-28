// usuarios_index.js - Usuarios Module Logic with Filters
// Version: 4.0 - Equipos-Style Filter Architecture

// Window-level functions for dropdown interaction
window.clearUsuariosFilter = function (filterName) {
    if (filterName === 'id_frente' || filterName === 'frente_filter') {
        const input = document.getElementById('filterSearchInput');
        if (input) {
            input.value = '';
            input.placeholder = 'Filtrar Frente...';
        }
        const hiddenInput = document.getElementById('input_frente_filter');
        if (hiddenInput) hiddenInput.value = '';

        const clearBtn = document.getElementById('btn_clear_frente');
        if (clearBtn) clearBtn.style.display = 'none';

        document.querySelectorAll('#frenteFilterSelect .dropdown-item').forEach(el => {
            el.classList.remove('selected');
        });
        document.querySelector('#frenteFilterSelect .dropdown-item')?.classList.add('selected');
    } else if (filterName === 'search') {
        const input = document.getElementById('searchInput');
        if (input) input.value = '';
        const clearBtn = document.getElementById('btn_clear_search');
        if (clearBtn) clearBtn.style.display = 'none';
    }

    // Reload usuarios after clearing filter
    window.loadUsuarios();
};

// Main load function - Equipos-style architecture
window.loadUsuarios = function (url = null) {
    const tableBody = document.getElementById('usuariosTableBody');
    if (!tableBody) return;

    let baseUrl = url || window.location.pathname;
    const searchInput = document.getElementById('searchInput');
    const frenteInput = document.getElementById('input_frente_filter');

    // Unified Filter Object (Single Source of Truth)
    const filters = {
        search: searchInput?.value,
        id_frente: (frenteInput?.value !== '') ? frenteInput?.value : null
    };

    const params = new URLSearchParams();

    // Cleanly append only valid filter values (non-null, non-empty)
    Object.entries(filters).forEach(([key, value]) => {
        if (value && typeof value === 'string' && value.trim() !== '') {
            params.append(key, value.trim());
        }
    });

    // Handle pagination URL
    if (url && url.includes('page=')) {
        try {
            const urlObj = new URL(url, window.location.origin);
            const page = urlObj.searchParams.get('page');
            if (page) params.append('page', page);
            baseUrl = urlObj.pathname;
        } catch (e) {
            console.error('URL parsing error:', e);
        }
    }

    const finalUrl = baseUrl + '?' + params.toString();
    tableBody.style.opacity = '0.5';
    if (window.showPreloader) window.showPreloader();

    fetch(finalUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            tableBody.innerHTML = data.html;
            tableBody.style.opacity = '1';

            const paginationContainer = document.getElementById('usuariosPagination');
            if (paginationContainer && data.pagination !== undefined) {
                paginationContainer.innerHTML = data.pagination;
            }

            if (data.count !== undefined) {
                const badgeText = document.getElementById('user-count-text');
                if (badgeText) {
                    badgeText.innerText = data.count;
                } else {
                    const badge = document.getElementById('user-count-badge');
                    if (badge) badge.innerText = data.count;
                }
            }

            window.history.pushState(null, '', finalUrl);
            if (window.hidePreloader) window.hidePreloader();
        })
        .catch(error => {
            console.error('Error loading usuarios:', error);
            tableBody.style.opacity = '1';
            if (window.hidePreloader) window.hidePreloader();
        });
};

// Event Listener for Dropdown Selection (Decoupled architecture)
window.addEventListener('dropdown-selection', function (e) {
    if (e.detail.type === 'frente_filter' && document.getElementById('usuariosTableBody')) {
        const clearBtn = document.getElementById('btn_clear_frente');
        if (clearBtn) {
            clearBtn.style.display = e.detail.value ? 'block' : 'none';
        }
        window.loadUsuarios();
    }
});

// Pagination click handler
document.addEventListener('click', function (e) {
    const link = e.target.closest('#usuariosPagination a');
    if (link) {
        e.preventDefault();
        window.loadUsuarios(link.getAttribute('href'));
    }
});

// Initialize on page load
function initUsuarios() {
    if (!document.getElementById('usuariosTableBody')) return;

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const val = this.value;
            const clearBtn = document.getElementById('btn_clear_search');
            if (clearBtn) clearBtn.style.display = (val.length > 0) ? 'block' : 'none';

            clearTimeout(window.searchTimeout);
            if (val.length >= 4 || val.length === 0) {
                window.searchTimeout = setTimeout(() => window.loadUsuarios(), 500);
            }
        });
    }

    const form = document.getElementById('search-form');
    if (form) {
        form.onsubmit = function (e) {
            e.preventDefault();
            window.loadUsuarios();
            return false;
        };
    }
}

// Register with Module Manager for SPA compatibility
if (typeof ModuleManager !== 'undefined') {
    ModuleManager.register('usuarios',
        () => document.getElementById('usuariosTableBody') !== null,
        initUsuarios
    );
}

// Custom Delete Modal Logic
window.confirmDelete = function (userId, userName) {
    const modal = document.getElementById('deleteModal');
    const nameSpan = document.getElementById('deleteModalUserName');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const form = document.getElementById('delete-form-global');

    if (modal && nameSpan && confirmBtn && form) {
        nameSpan.innerText = userName;
        form.action = `/admin/usuarios/${userId}`;

        confirmBtn.onclick = function () {
            window.closeDeleteModal();
            if (window.showPreloader) {
                window.showPreloader();
            }
            form.submit();
        };

        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            modal.classList.add('active');
            modal.style.opacity = '1';
        });
    } else {
        console.error('Modal elements not found for delete confirmation');
    }
};

window.closeDeleteModal = function () {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
};
