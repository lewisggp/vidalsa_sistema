/**
 * uicomponents.js - Shared UI Components
 * Version: 2.0 - Clean Architecture with Event Delegation
 */

// Global click handler for dropdowns (event delegation)
// Helper to close all dropdowns except the one passed
window.closeAllDropdowns = function (exceptElement) {
    // Close standard .custom-dropdown components
    document.querySelectorAll('.custom-dropdown, .custom-multiselect').forEach(el => {
        if (el !== exceptElement) el.classList.remove('active');
    });

    // Close special filters (Equipos Index) if they are not the exception
    // We check if the exceptElement *contains* the special list to avoid closing it if we are interacting with it?
    // Actually, 'exceptElement' is usually the container being opened.

    // yearList
    const yearList = document.getElementById('yearList');
    if (yearList && yearList !== exceptElement && !exceptElement?.contains(yearList)) {
        yearList.style.display = 'none';
    }

    // modelList
    const modelList = document.getElementById('modelList');
    if (modelList && modelList !== exceptElement && !exceptElement?.contains(modelList)) {
        modelList.style.display = 'none';
    }

    // marcaList
    const marcaList = document.getElementById('marcaList');
    if (marcaList && marcaList !== exceptElement && !exceptElement?.contains(marcaList)) {
        marcaList.style.display = 'none';
    }
}

// Global click handler for dropdowns (event delegation) - ROBUST VERSION
document.addEventListener('click', function (e) {
    // 1. Identify if click is inside a dropdown trigger
    const trigger = e.target.closest('.dropdown-trigger, .multiselect-trigger');

    // If not clicking a trigger, check if clicking INSIDE an already open dropdown
    if (!trigger) {
        const isClickInside = e.target.closest('.custom-dropdown, .custom-multiselect') ||
            e.target.closest('#yearList') ||
            e.target.closest('#modelList') ||
            e.target.closest('#marcaList') ||
            e.target.id === 'searchModelInput' ||
            e.target.id === 'searchMarcaInput';

        if (!isClickInside) {
            closeAllDropdowns(null); // Close everything
        }
        return;
    }

    // 2. Resolve the parent dropdown component
    const parent = trigger.closest('.custom-dropdown, .custom-multiselect');
    if (!parent) return; // If clicking a trigger without parent (like Año inline), return early (it handles itself)

    // 3. Logic: Always Toggle, but be smart about Inputs
    const isInput = e.target.tagName === 'INPUT';
    const isOpen = parent.classList.contains('active');

    // GUARD: If clicking an input that is already active, DO NOTHING.
    // This prevents accidental closing or re-toggling when trying to type.
    if (isInput && isOpen) {
        return;
    }

    // Close ALL OTHER dropdowns first
    closeAllDropdowns(parent);

    // 4. Handle the Toggle
    if (isInput) {
        // If clicking the input and it's closed -> Open it
        if (!isOpen) {
            parent.classList.add('active');
        }
        // If it's already open and we clicked the input, DO NOTHING (let user type)
    } else {
        // Clicking the container (icon, padding, etc) -> Toggle normally
        parent.classList.toggle('active');

        // If we just opened it, focus the input if it exists
        if (parent.classList.contains('active')) {
            const input = parent.querySelector('input[type="text"]');
            if (input) setTimeout(() => input.focus(), 50);
        }
    }

    e.stopPropagation();
});

// Global focus handler to open dropdowns on tab/click-focus and close others
document.addEventListener('focusin', function (e) {
    if (e.target.matches('.dropdown-trigger input[type="text"]')) {
        const parent = e.target.closest('.custom-dropdown, .custom-multiselect');
        if (parent) {
            // Close others
            closeAllDropdowns(parent);

            // Open this one if not active
            if (!parent.classList.contains('active')) {
                parent.classList.add('active');
            }
        }
    }
});

// Manual toggle function for inline handlers (forms, etc.)
window.toggleDropdown = function (dropdownId, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) return;

    const isOpen = dropdown.classList.contains('active');

    // Close ALL OTHER dropdowns first
    window.closeAllDropdowns(dropdown);

    // Toggle state
    if (isOpen) {
        dropdown.classList.remove('active');
    } else {
        dropdown.classList.add('active');
        // Focus input if exists
        const input = dropdown.querySelector('input[type="text"]');
        if (input) setTimeout(() => input.focus(), 50);
    }
};

/**
 * ═══════════════════════════════════════════════════════════════════════
 * GLOBAL selectOption - ID-AGNOSTIC ARCHITECTURE (v2.0)
 * ═══════════════════════════════════════════════════════════════════════
 * Uses data attributes for all lookups. No hardcoded IDs.
 * 
 * Required HTML structure:
 * <div class="custom-dropdown" id="uniqueId" data-filter-type="type" data-default-label="...">
 *     <input type="hidden" data-filter-value>
 *     <div class="dropdown-trigger">
 *         <input type="text" data-filter-search>
 *         <i data-clear-btn>close</i>
 *     </div>
 *     <div class="dropdown-content">
 *         <div class="dropdown-item" data-value="val">Label</div>
 *     </div>
 * </div>
 * ═══════════════════════════════════════════════════════════════════════
 */
window.selectOption = function (dropdownId, value, label, legacyType) {
    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) {
        console.warn('[selectOption] Dropdown not found:', dropdownId);
        return;
    }

    // Determine filter type from data attribute or legacy parameter
    const type = dropdown.dataset.filterType || legacyType || dropdownId;

    // Find elements using data attributes (PRIMARY) or fallback to legacy patterns
    let hiddenInput = dropdown.querySelector('[data-filter-value]');
    let searchInput = dropdown.querySelector('[data-filter-search]');
    let labelSpan = dropdown.querySelector('[data-filter-label]');
    let clearBtn = dropdown.querySelector('[data-clear-btn]');

    // LEGACY FALLBACK: Support old structure while migrating
    if (!hiddenInput) {
        hiddenInput = dropdown.querySelector('input[type="hidden"]');
    }
    if (!searchInput) {
        searchInput = dropdown.querySelector('.dropdown-trigger input[type="text"]');
    }
    if (!labelSpan && legacyType) {
        // Try legacy ID pattern: #label_tipo, #label_rol, etc.
        labelSpan = document.getElementById('label_' + legacyType);
    }
    if (!clearBtn) {
        // Try to find by class pattern used in some modules
        clearBtn = dropdown.querySelector('.dropdown-trigger .material-icons[data-clear-btn]');
    }

    // Normalize value
    const effectiveValue = (value === null || value === undefined) ? '' : String(value);

    // Update hidden input
    if (hiddenInput) {
        hiddenInput.value = effectiveValue;
    }

    // Update search input placeholder (for filter dropdowns)
    if (searchInput) {
        searchInput.placeholder = label;
        searchInput.value = '';
    }

    // Update label span text (for form dropdowns)
    if (labelSpan) {
        labelSpan.textContent = label;
    }

    // Visual feedback on trigger
    const trigger = dropdown.querySelector('.dropdown-trigger');
    if (trigger) {
        if (effectiveValue && effectiveValue !== 'all' && effectiveValue !== '') {
            trigger.style.background = '#e1effa';
            trigger.style.borderColor = '#0067b1';
        } else {
            trigger.style.background = '#fbfcfd';
            trigger.style.borderColor = '#cbd5e0';
        }
    }

    // Update selected state on items
    dropdown.querySelectorAll('.dropdown-item').forEach(item => {
        const itemValue = item.dataset.value !== undefined ? item.dataset.value : null;
        const isSelected = itemValue !== null
            ? itemValue === effectiveValue
            : item.innerText.trim() === label.trim();
        item.classList.toggle('selected', isSelected);
    });

    // Close dropdown
    dropdown.classList.remove('active');

    // Toggle clear button visibility
    if (clearBtn) {
        clearBtn.style.display = (effectiveValue && effectiveValue !== 'all' && effectiveValue !== '') ? 'block' : 'none';
    }

    // Dispatch custom event for module-specific reactions
    window.dispatchEvent(new CustomEvent('dropdown-selection', {
        detail: { dropdownId, value: effectiveValue, label, inputName: type }
    }));
};

/**
 * Generic clear function for any dropdown
 */
window.clearDropdownFilter = function (dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) return;

    const defaultLabel = dropdown.dataset.defaultLabel || 'Seleccionar...';
    window.selectOption(dropdownId, '', defaultLabel);
};

window.updateSelectedCount = function () {
    const checkboxes = document.querySelectorAll('input[name="PERMISOS[]"]:checked');
    const countSpan = document.getElementById('selectedCount');
    if (!countSpan) return;

    if (checkboxes.length === 0) {
        countSpan.innerText = 'Seleccione permisos...';
        countSpan.style.color = '#a0aec0';
    } else {
        const labels = Array.from(checkboxes).map(cb => cb.value);
        countSpan.innerText = labels.join(', ');
        countSpan.style.color = 'inherit';
    }
};

// Confirm Delete (Hybrid: Custom Modal if available, fallback to Native)
window.confirmDelete = function (id, name) {
    // Try to find custom modal elements (used in Usuarios, etc.)
    const modal = document.getElementById('deleteModal');
    const nameSpan = document.getElementById('deleteModalUserName');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const form = document.getElementById('delete-form-global'); // Global form preferred

    if (modal && nameSpan && confirmBtn && form) {
        // UI: Use Custom Modal
        nameSpan.innerText = name;

        // Handle routes dynamically based on context if needed, but standard is /admin/usuarios/id
        // If we need to support multiple modules, we might need a type argument, or use the form's data-action-base
        // For now, defaulting to standard global form behavior or dynamic path setting

        // Check if form has a specific base action or default to usuarios
        // To be safe and generic: We assume the caller or the form setup knows the route, 
        // OR we infer it. 
        // Given existing code used /admin/usuarios/, let's support that default but be flexible.

        // Strategy: If form has 'action', use it? No, we need to append ID.
        // Let's assume this is mostly for Usuarios as per previous code.
        // If we want it generic, we should pass the URL. 
        // But for now, let's keep the previous behavior:

        // If we are functioning globally, we need to know the Model. 
        // But confirmDelete(id, name) signature lacks Model.
        // Falls back to Usuarios logic for now as it was the only one using it.
        // OR checks if we are on a specific page.

        if (window.location.pathname.includes('usuarios')) {
            form.action = `/admin/usuarios/${id}`;
        } else {
            // Fallback for other modules if they introduce this modal
            form.action = window.location.pathname.replace(/\/create|\/edit/, '') + '/' + id;
        }

        confirmBtn.onclick = function () {
            window.closeDeleteModal();
            if (window.showPreloader) window.showPreloader();
            form.submit();
        };

        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            modal.classList.add('active');
            modal.style.opacity = '1';
        });
    } else {
        // Fallback: Use native confirm
        if (confirm(`¿Estás seguro de que deseas eliminar a "${name}"?\n\nEsta acción no se puede deshacer.`)) {
            // Check for specific form pattern (delete-form-ID) or global form
            let specificForm = document.getElementById('delete-form-' + id);
            if (specificForm) {
                specificForm.submit();
            } else if (form) {
                if (window.location.pathname.includes('usuarios')) {
                    form.action = `/admin/usuarios/${id}`;
                } else {
                    form.action = window.location.pathname.replace(/\/create|\/edit/, '') + '/' + id;
                }
                form.submit();
            } else {
                console.error('Delete form not found');
            }
        }
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

// Manual toggle function for inline handlers (forms, etc.) - CONSOLIDATED & ROBUST
window.toggleDropdown = function (dropdownId, event) {
    if (event) event.stopPropagation();

    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) return;

    // Prevent closing if clicking an input/label inside an open dropdown (e.g., Search Frentes)
    const e = event || window.event;
    if (e && e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'LABEL') && dropdown.classList.contains('active')) {
        return;
    }

    // Close all other dropdowns first using the CENTRAL helper
    closeAllDropdowns(dropdown);

    // Toggle this one
    dropdown.classList.toggle('active');

    // Focus input automatically when opening
    if (dropdown.classList.contains('active')) {
        const input = dropdown.querySelector('input[type="text"]');
        if (input) setTimeout(() => input.focus(), 50);
    }
};

window.toggleMultiselect = function () {
    const multiselect = document.getElementById('permissionsSelect');
    if (!multiselect) return;

    document.querySelectorAll('.custom-dropdown, .custom-multiselect').forEach(el => {
        if (el !== multiselect) el.classList.remove('active');
    });
    multiselect.classList.toggle('active');
};

window.filterDropdownOptions = function (input) {
    const dropdown = input.closest('.custom-dropdown');
    if (!dropdown) return;



    // Standard Client-Side Filtering (Original Logic) for small lists (Frente, Tipo, etc.)
    // Normalize helper: lowercase and remove accents
    const normalize = (str) => {
        return str.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    };

    const filter = normalize(input.value);
    const items = dropdown.querySelectorAll('.dropdown-item, .filter-option-item');

    items.forEach(item => {
        const text = normalize(item.innerText);
        const shouldShow = text.includes(filter);

        // Use setProperty to override any potential CSS conflicts
        item.style.setProperty('display', shouldShow ? 'block' : 'none', 'important');
    });

    // Reset scroll position to top to ensure results are seen
    const content = dropdown.querySelector('.dropdown-content') || dropdown.querySelector('.dropdown-item-list');
    if (content) content.scrollTop = 0;

    if (filter.length > 0) {
        dropdown.classList.add('active');
    }
};

// Global input listener is REDUNDANT because we added inline oninput handlers explicitly.
// Removing it to prevent double-execution and potential conflicts.


/**
 * ═══════════════════════════════════════════════════════════════════════
 * GLOBAL DROPDOWN FUNCTIONS - SINGLE SOURCE OF TRUTH
 * ═══════════════════════════════════════════════════════════════════════
 * These functions are called from inline handlers in Blade templates across
 * different modules (Equipos, Catálogo, Movilizaciones, etc.).
 * 
 * ⚠️ DO NOT duplicate these in module files - this is the authoritative source!
 * ═══════════════════════════════════════════════════════════════════════
 */

// CATÁLOGO: Advanced filter selection (Modelo, Año)
window.selectAdvancedOption = function (type, value, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    if (type === 'modelo') {
        const input = document.getElementById('searchModeloInput');
        if (input) {
            input.value = value;
            input.placeholder = value ? value : 'Buscar Modelo...';
        }
        const hidden = document.getElementById('input_modelo_filter');
        if (hidden) hidden.value = value;

        const clearBtn = document.getElementById('btn_clear_modelo');
        if (clearBtn) clearBtn.style.display = value ? 'block' : 'none';

        const dropdown = document.getElementById('modeloFilterSelect');
        if (dropdown) dropdown.classList.remove('active');
    }

    if (type === 'anio') {
        const input = document.getElementById('searchAnioInput');
        if (input) {
            input.value = value;
            input.placeholder = value ? value : 'Buscar Año...';
        }
        const hidden = document.getElementById('input_anio_filter');
        if (hidden) hidden.value = value;

        const clearBtn = document.getElementById('btn_clear_anio');
        if (clearBtn) clearBtn.style.display = value ? 'block' : 'none';

        const dropdown = document.getElementById('anioFilterSelect');
        if (dropdown) dropdown.classList.remove('active');
    }

    // Trigger catalog load if function exists
    if (typeof window.loadCatalogo === 'function') {
        window.loadCatalogo();
    }
};

// EQUIPOS: Advanced filter selection (Modelo, Marca, Año, Frente, Tipo, Search)
window.selectAdvancedFilter = function (key, value) {
    if (window.searchTimeout) clearTimeout(window.searchTimeout);

    if (key === 'modelo') {
        // Find the modelo container
        const container = document.querySelector('[data-advanced-filter="modelo"]');
        if (container) {
            const hiddenInput = container.querySelector('[data-filter-value]');
            const searchInput = container.querySelector('[data-filter-search]');
            const list = container.querySelector('.filter-list');
            const btn = container.querySelector('[data-clear-btn]');

            if (hiddenInput) hiddenInput.value = value;
            if (searchInput) searchInput.value = value;
            if (list) list.style.display = 'none';
            if (btn) btn.style.display = value ? 'block' : 'none';
        }
    }

    if (key === 'marca') {
        // Find the marca container
        const container = document.querySelector('[data-advanced-filter="marca"]');
        if (container) {
            const hiddenInput = container.querySelector('[data-filter-value]');
            const searchInput = container.querySelector('[data-filter-search]');
            const list = container.querySelector('.filter-list');
            const btn = container.querySelector('[data-clear-btn]');

            if (hiddenInput) hiddenInput.value = value;
            if (searchInput) searchInput.value = value;
            if (list) list.style.display = 'none';
            if (btn) btn.style.display = value ? 'block' : 'none';
        }
    }

    if (key === 'anio') {
        const input = document.querySelector('input[name="anio"]');
        if (input) input.value = value;
        const labelSpan = document.querySelector('#yearList')?.previousElementSibling?.querySelector('span');
        if (labelSpan) labelSpan.innerText = value || 'Seleccionar Año';
        const list = document.getElementById('yearList');
        if (list) list.style.display = 'none';
        const btn = document.getElementById('btn_clear_anio');
        if (btn) btn.style.display = value ? 'block' : 'none';
    }

    if (key === 'frente' || key === 'id_frente') {
        const input = document.querySelector('input[name="id_frente"]');
        if (input) input.value = value;
        const dropdown = document.getElementById('frenteFilterSelect');
        if (dropdown) {
            const searchInput = dropdown.querySelector('[data-filter-search]');
            const btn = dropdown.querySelector('[data-clear-btn]');
            if (searchInput) searchInput.placeholder = value ? value : 'Filtrar Frente...';
            if (btn) btn.style.display = value ? 'block' : 'none';
            dropdown.classList.remove('active');
        }
    }

    if (key === 'tipo' || key === 'id_tipo') {
        const input = document.querySelector('input[name="id_tipo"]');
        if (input) input.value = value;
        const dropdown = document.getElementById('tipoFilterSelect');
        if (dropdown) {
            const searchInput = dropdown.querySelector('[data-filter-search]');
            const btn = dropdown.querySelector('[data-clear-btn]');
            if (searchInput) searchInput.placeholder = value ? value : 'Filtrar Tipo...';
            if (btn) btn.style.display = value ? 'block' : 'none';
            dropdown.classList.remove('active');
        }
    }

    if (key === 'search') {
        const input = document.getElementById('searchInput');
        if (input) input.value = value;
        const btn = document.getElementById('btn_clear_search');
        if (btn) btn.style.display = value ? 'block' : 'none';
    }

    // Trigger equipos load if function exists
    if (typeof window.loadEquipos === 'function') {
        window.loadEquipos();
    }
};

// EQUIPOS: Clear all advanced filters
window.clearAdvancedFilters = function () {
    if (window.searchTimeout) clearTimeout(window.searchTimeout);

    // Clear Modelo
    const modeloContainer = document.querySelector('[data-advanced-filter="modelo"]');
    if (modeloContainer) {
        const input = modeloContainer.querySelector('[data-filter-search]');
        const hidden = modeloContainer.querySelector('[data-filter-value]');
        const btn = modeloContainer.querySelector('[data-clear-btn]');
        if (input) input.value = '';
        if (hidden) hidden.value = '';
        if (btn) btn.style.display = 'none';
    }

    // Clear Marca
    const marcaContainer = document.querySelector('[data-advanced-filter="marca"]');
    if (marcaContainer) {
        const input = marcaContainer.querySelector('[data-filter-search]');
        const hidden = marcaContainer.querySelector('[data-filter-value]');
        const btn = marcaContainer.querySelector('[data-clear-btn]');
        if (input) input.value = '';
        if (hidden) hidden.value = '';
        if (btn) btn.style.display = 'none';
    }

    // Clear Año
    const anioInput = document.querySelector('input[name="anio"]');
    if (anioInput) anioInput.value = '';
    const anioLabel = document.querySelector('#yearList')?.previousElementSibling?.querySelector('span');
    if (anioLabel) anioLabel.innerText = 'Seleccionar Año';
    const anioBtn = document.getElementById('btn_clear_anio');
    if (anioBtn) anioBtn.style.display = 'none';

    // Clear Doc Filters (Equipos specific)
    ['chk_propiedad', 'chk_poliza', 'chk_rotc', 'chk_racda'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.checked = false;
    });

    // Trigger reload if function exists
    if (typeof window.loadEquipos === 'function') {
        window.loadEquipos();
    }
};

// EQUIPOS: Helper alias for inline onclick (maps view filter names to internal keys)
window.clearFilter = function (filterName) {
    const map = {
        'id_frente': 'frente',
        'id_tipo': 'tipo',
        'modelo': 'modelo',
        'anio': 'anio',
        'marca': 'marca'
    };

    const key = map[filterName] || filterName;
    window.selectAdvancedFilter(key, '');
};


// Register with Module Manager
// Since we use Event Delegation now, we don't need to re-attach listeners on navigation!
// This makes the app much lighter and faster.
ModuleManager.register('uicomponents',
    () => false, // Return false prevents re-initialization since it's now globally handled
    () => { }     // No-op initializer
);



// Global Frentes Search Function (Called via inline attributes for SPA robustness)
window.searchFrentes = function (input) {
    const query = input.value.trim();
    const resultsDiv = document.getElementById('search-results');
    const clearIcon = document.getElementById('clear_search');

    // Toggle clear icon
    if (clearIcon) {
        clearIcon.style.display = query.length > 0 ? 'block' : 'none';

        // Ensure the click handler is attached (inline onclick handles logic, but display is here)
    }

    // Safety check
    if (!resultsDiv) return;

    // Debounce logic
    clearTimeout(input.searchTimeout);

    // Immediate toggle for empty (show list)
    if (query.length === 0) {
        performFrentesFetch('', resultsDiv);
        return;
    }

    input.searchTimeout = setTimeout(() => {
        performFrentesFetch(query, resultsDiv);
    }, 300);
};

// Helper for fetching
window.performFrentesFetch = function (query, resultsDiv) {
    if (!resultsDiv) return;

    fetch(`/admin/frentes/buscar?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            resultsDiv.innerHTML = '';
            if (data.length > 0) {
                data.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'search-result-item';
                    div.textContent = item.NOMBRE_FRENTE;
                    const safeName = item.NOMBRE_FRENTE.replace(/'/g, "\\'");
                    div.onclick = () => window.selectFrente(item.ID_FRENTE, safeName);
                    resultsDiv.appendChild(div);
                });
                resultsDiv.style.display = 'block';
            } else {
                const div = document.createElement('div');
                div.className = 'search-result-item';
                div.style.color = '#94a3b8';
                div.style.cursor = 'default';
                div.textContent = 'No se encontraron resultados';
                resultsDiv.appendChild(div);
                resultsDiv.style.display = 'block';
            }
        })
        .catch(error => console.error('Error:', error));
};

// Selection handler
window.selectFrente = function (id, name) {
    if (window.showPreloader) window.showPreloader();
    window.location.href = `/admin/frentes/${id}/edit`;
};

// Close handler (Global)
document.addEventListener('click', function (e) {
    const resultsDiv = document.getElementById('search-results');
    const searchInput = document.getElementById('search_query');
    if (resultsDiv && searchInput && !searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
        resultsDiv.style.display = 'none';
    }
});

// Clear handler
window.clearFrentesSearch = function () {
    const searchInput = document.getElementById('search_query');
    if (searchInput) {
        searchInput.value = '';
        window.searchFrentes(searchInput); // Refresh list
        searchInput.focus();
    }
};
// Confirm Delete Frente (Dynamic Modal)
window.confirmDeleteFrente = function (id, name) {
    if (typeof showModal === 'function') {
        showModal({
            type: 'error',
            title: '¿Eliminar Frente?',
            message: `¿Estás seguro de que deseas eliminar el frente "${name}"? Esta acción no se puede deshacer.`,
            confirmText: 'Sí, Eliminar',
            onConfirm: () => {
                const form = document.getElementById('deleteFrenteForm');
                if (form) {
                    if (window.showPreloader) window.showPreloader();
                    form.submit();
                } else {
                    alert('Error: Formulario de eliminación no encontrado.');
                }
            }
        });
    } else {
        // Fallback
        if (confirm(`¿Eliminar "${name}"?`)) {
            document.getElementById('deleteFrenteForm').submit();
        }
    }
};

/**
 * ═══════════════════════════════════════════════════════════════════════
 * GLOBAL DETAILS MODAL LOGIC (IMPROVED)
 * ═══════════════════════════════════════════════════════════════════════
 */
window.showDetailsImproved = function (target, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    if (!target || !target.dataset) {
        console.error('showDetailsImproved called without valid target');
        return;
    }

    const d = target.dataset;
    const modal = document.getElementById('detailsModal');

    // Reset Accordions (Close all sections)
    if (modal) {
        modal.querySelectorAll('details').forEach(det => det.removeAttribute('open'));
    }

    // Helper to identify empty values
    const isValid = (val) => val && val !== 'N/A' && val !== '';

    // Helper to set text
    const set = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.innerText = val || 'N/A';
    };

    // Helper to format date YYYY-MM-DD -> DD/MM/YYYY
    const formatDate = (dateStr) => {
        if (!dateStr || dateStr === 'N/A' || dateStr.trim() === '') return 'N/A';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return dateStr;
    };

    // Header
    // Header
    // Header - Simplified (Original Style)
    // FORCE UPDATE title with Type
    const typeText = target.getAttribute('data-tipo') || d.tipo || 'Equipo';
    const titleVal = (typeText !== 'undefined' && typeText !== 'null') ? typeText : 'Equipo';
    set('modal_equipo_title', titleVal);
    const titleEl = document.getElementById('modal_equipo_title');
    if (titleEl) titleEl.style.textTransform = 'uppercase';

    const subtitleParts = [];
    if (d.placa && d.placa !== 'N/A') subtitleParts.push(`Placa: ${d.placa}`);
    if (d.chasis && d.chasis !== 'N/A') subtitleParts.push(`Serial: ${d.chasis}`);
    set('modal_equipo_subtitle', subtitleParts.join(' - '));

    // GPS Button
    const gpsBtn = document.getElementById('modal_gps_btn');
    if (gpsBtn) {
        if (isValid(d.linkGps)) {
            gpsBtn.href = d.linkGps;
            gpsBtn.style.display = 'flex';
        } else {
            gpsBtn.style.display = 'none';
        }
    }

    // General Info
    set('d_marca', d.marca);
    set('d_modelo', d.modelo);
    set('d_anio', d.anio);
    set('d_categoria', d.categoria);
    set('d_motor_serial', d.motorSerial);
    set('d_combustible', d.combustible);
    set('d_consumo', d.consumo);

    // Docs
    set('d_titular', d.titular);
    set('d_placa', d.placa);
    set('d_nro_doc', d.nroDoc);

    const vencSeguroEl = document.getElementById('d_venc_seguro');
    if (vencSeguroEl) {
        vencSeguroEl.innerText = formatDate(d.vencSeguro);
        // Add color logic if needed for expiration
    }

    set('d_fecha_rotc', formatDate(d.fechaRotc));
    set('d_fecha_racda', formatDate(d.fechaRacda));

    // Document Action Buttons Generator
    const createDocBtn = (containerId, type, link, label, equipoId) => {
        const container = document.getElementById(containerId);
        if (!container) return;

        if (isValid(link)) {
            // View PDF Button
            container.innerHTML = `
                <div class="pdf-btn-container">
                    <button type="button" 
                        onclick="openPdfPreview('${link}', '${type}', '${label}', '${equipoId}')" 
                        style="width: 36px; height: 36px; border-radius: 8px; background: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; transition: all 0.2s; cursor: default;"
                        onmouseover="this.style.background='#e9ecef'" 
                        onmouseout="this.style.background='#f8f9fa'"
                        title="Ver PDF: ${label}">
                        <i class="material-icons" style="font-size: 20px; color: #6c757d;">picture_as_pdf</i>
                    </button>
                </div>
            `;
        } else {
            // Upload Button
            // Permission Check for New Uploads
            if (typeof window.CAN_UPDATE_INFO !== 'undefined' && window.CAN_UPDATE_INFO === false) {
                container.innerHTML = `<span style="color: #94a3b8; font-size: 12px; font-style: italic; display: flex; align-items: center; justify-content: flex-end; height: 36px;">Sin Documento</span>`;
                return;
            }

            const inputId = `input_upload_${type}_${equipoId}`;
            container.innerHTML = `
                <div style="position: relative; width: 30px; height: 30px;">
                    <input type="file" id="${inputId}" accept="application/pdf" style="display: none;" onchange="uploadDocument(this, '${type}', '${equipoId}', '${containerId}', '${label}')">
                    <label for="${inputId}" 
                        style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; background: #fbfcfd; color: #3b82f6; border: 1px dashed #3b82f6; border-radius: 6px; transition: 0.2s; cursor: default;" 
                        onmouseover="this.style.background='#eff6ff'" 
                        onmouseout="this.style.background='#fbfcfd'" 
                        title="Cargar ${label}">
                        <i class="material-icons" style="font-size: 18px;">cloud_upload</i>
                    </label>
                </div>
             `;
        }

    };

    const eqId = d.equipoId;
    createDocBtn('d_btn_propiedad', 'propiedad', d.linkPropiedad, 'Propiedad', eqId);
    createDocBtn('d_btn_poliza', 'poliza', d.linkSeguro, 'Póliza', eqId);
    createDocBtn('d_btn_rotc', 'rotc', d.linkRotc, 'ROTC', eqId);
    createDocBtn('d_btn_racda', 'racda', d.linkRacda, 'RACDA', eqId);
    createDocBtn('d_btn_adicional', 'adicional', d.linkAdicional, 'Adicional', eqId);

    // Show Modal
    if (modal) {
        modal.style.display = 'flex';
        // Force reflow
        void modal.offsetWidth;
        modal.classList.add('active');
    }

    window.activeEquipoButton = target;
};

window.closeDetailsModal = function (event) {
    if (event) event.preventDefault();
    const modal = document.getElementById('detailsModal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
};

window.uploadDocument = function (input, type, equipoId, containerId, label) {
    // PERMISSION CHECK (Defense in depth)
    if (typeof window.CAN_UPDATE_INFO !== 'undefined' && window.CAN_UPDATE_INFO === false) {
        input.value = ''; // Clear input
        if (window.showModal) {
            showModal({
                type: 'error',
                title: 'Acceso Denegado',
                message: 'No tienes permisos para cargar documentos.',
                confirmText: 'Entendido',
                hideCancel: true
            });
        } else {
            alert('Acceso Denegado: No tienes permisos.');
        }
        return;
    }

    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (window.showPreloader) window.showPreloader();

    const formData = new FormData();
    formData.append('file', file);
    formData.append('doc_type', type);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', `/admin/equipos/${equipoId}/upload-doc`, true);
    // CSRF fetch
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) xhr.setRequestHeader('X-CSRF-TOKEN', meta.getAttribute('content'));
    xhr.setRequestHeader('Accept', 'application/json');

    xhr.onload = function () {
        // Note: preloader is now hidden conditionally after PDF modal opens (see below)
        // This prevents visual gap during transition

        if (xhr.status === 200) {
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    // Update UI
                    const container = document.getElementById(containerId);
                    if (container) {
                        container.innerHTML = `
                            <div class="pdf-btn-container">
                                <button type="button" 
                                    onclick="openPdfPreview('${data.link}', '${type}', '${label}', '${equipoId}')" 
                                    style="width: 36px; height: 36px; border-radius: 8px; background: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; transition: all 0.2s; cursor: pointer;"
                                    onmouseover="this.style.background='#e9ecef'" 
                                    onmouseout="this.style.background='#f8f9fa'"
                                    title="Ver PDF: ${label}">
                                    <i class="material-icons" style="font-size: 20px; color: #6c757d;">picture_as_pdf</i>
                                </button>
                            </div>
                        `;
                    }

                    if (window.activeEquipoButton) {
                        const d = window.activeEquipoButton.dataset;
                        if (type === 'propiedad') d.linkPropiedad = data.link;
                        if (type === 'poliza') d.linkSeguro = data.link;
                        if (type === 'rotc') d.linkRotc = data.link;
                        if (type === 'racda') d.linkRacda = data.link;
                    }

                    if (typeof window.refreshDashboardAlerts === 'function') {
                        window.refreshDashboardAlerts();
                    }

                    // Auto-Open PDF Preview (Serves as success confirmation)
                    console.log('✅ Upload successful, attempting to open PDF preview...');
                    console.log('📄 PDF Link:', data.link);
                    console.log('🔍 openPdfPreview available?', typeof window.openPdfPreview);

                    if (typeof window.openPdfPreview === 'function') {
                        // Small delay to ensure DOM is ready and preloader has shown
                        setTimeout(() => {
                            console.log('🚀 Opening PDF modal...');
                            window.openPdfPreview(data.link, type, label, equipoId);

                            // Hide global preloader AFTER opening PDF modal (smooth transition)
                            setTimeout(() => {
                                if (window.hidePreloader) window.hidePreloader();
                            }, 150);
                        }, 50);
                    } else {
                        console.error('❌ openPdfPreview function not found!');
                        if (window.hidePreloader) window.hidePreloader();
                    }

                } else {
                    if (window.hidePreloader) window.hidePreloader();
                    if (window.showModal) showModal({ type: 'error', title: 'Error', message: data.message || 'Error al cargar.', confirmText: 'Cerrar', hideCancel: true });
                }
            } catch (e) {
                console.error(e);
                if (window.hidePreloader) window.hidePreloader();
            }
        } else {
            if (window.hidePreloader) window.hidePreloader();
            if (window.showModal) showModal({ type: 'error', title: 'Error', message: 'Error de red.', confirmText: 'Cerrar', hideCancel: true });
        }
    };

    xhr.onerror = function () {
        if (window.hidePreloader) window.hidePreloader();
        if (window.showModal) showModal({ type: 'error', title: 'Error', message: 'Error de conexión.', confirmText: 'Cerrar', hideCancel: true });
    };

    xhr.send(formData);
};

/**
 * Global Preloader Management
 * Reuses the existing #preloader element from estructura_base.blade.php
 * to avoid DOM duplication and maintain consistency.
 */
window.showPreloader = function () {
    const preloader = document.getElementById('preloader');
    if (preloader) {
        // Remove fade-out class if present
        preloader.classList.remove('fade-out');

        // Make visible immediately
        preloader.style.display = 'flex';
        preloader.style.opacity = '1';
        preloader.style.visibility = 'visible';
        preloader.style.zIndex = '99999';
    }
};

window.hidePreloader = function () {
    const preloader = document.getElementById('preloader');
    if (preloader) {
        // Add fade-out class for smooth transition
        preloader.classList.add('fade-out');

        // Hide after transition completes (500ms as defined in CSS)
        setTimeout(() => {
            if (preloader.classList.contains('fade-out')) {
                preloader.style.display = 'none';
            }
        }, 500);
    }
};
