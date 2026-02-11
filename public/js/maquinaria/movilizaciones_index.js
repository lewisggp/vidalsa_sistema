// movilizaciones_index.js - Movilizaciones Module Logic
// Version: 3.0 - Robust Equipos-Style Filtering

// Global Filter Handler (Isolated from Equipos)
window.selectMovilizacionFilter = function (type, value) {
    // 1. Update Input Values
    if (type === 'frente') {
        const input = document.getElementById('input_frente_filter');
        if (input) input.value = value;

        // Update Placeholder & Input Text
        const searchInput = document.getElementById('filterSearchInput');
        if (searchInput) {
            if (value === 'all' || value === '') {
                searchInput.placeholder = 'Todos los Frentes';
                searchInput.value = ''; // Clear search term on selection
            } else {
                // Try to find the name from the list
                const item = document.querySelector(`#frenteItemsList .dropdown-item[onclick*="'${value}'"]`);
                if (item) searchInput.placeholder = item.innerText.trim();
                searchInput.value = '';
            }
        }

        // Toggle Clear Button
        const btn = document.getElementById('btn_clear_frente');
        if (btn) btn.style.display = (value && value !== 'all') ? 'block' : 'none';

        // Close Dropdown
        const dropdown = document.getElementById('frenteFilterSelect');
        if (dropdown) {
            dropdown.classList.remove('active');
            const content = dropdown.querySelector('.dropdown-content');
            if (content) content.style.display = 'none';
        }
    }

    if (type === 'tipo') {
        const input = document.getElementById('input_tipo_filter');
        if (input) input.value = value;

        const searchInput = document.getElementById('filterTipoSearchInput');
        if (searchInput) {
            if (value === '') {
                searchInput.placeholder = 'Todos los Tipos';
                searchInput.value = '';
            } else {
                const item = document.querySelector(`#tipoItemsList .dropdown-item[onclick*="'${value}'"]`);
                if (item) searchInput.placeholder = item.innerText.trim();
                searchInput.value = '';
            }
        }

        const btn = document.getElementById('btn_clear_tipo');
        if (btn) btn.style.display = value ? 'block' : 'none';

        const dropdown = document.getElementById('tipoFilterSelect');
        if (dropdown) {
            dropdown.classList.remove('active');
            const content = dropdown.querySelector('.dropdown-content');
            if (content) content.style.display = 'none';
        }
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

// Global Event Listeners (SPA Safe)
function initMovilizacionesListeners() {
    // NOTE: Dropdown toggles now handled by global delegation in uicomponents.js
    // No need to manually attach onclick to .dropdown-trigger elements

    // Search Input Auto-Search (module-specific logic)
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

// Initialize on page load
function initMovilizaciones() {
    if (!document.getElementById('movilizacionesTableBody')) return;
    initMovilizacionesListeners();
}

// Register with Module Manager for SPA compatibility
if (typeof ModuleManager !== 'undefined') {
    ModuleManager.register('movilizaciones',
        () => document.getElementById('movilizacionesTableBody') !== null,
        initMovilizaciones
    );
}

// Listen for SPA navigation to reinitialize module
window.addEventListener('spa:contentLoaded', function () {
    if (document.getElementById('movilizacionesTableBody')) {
        initMovilizaciones();
    }
});
