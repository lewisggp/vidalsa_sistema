/**
 * uicomponents.js - Shared UI Components
 * Version: 2.0 - Clean Architecture with Event Delegation
 */

// Global click handler for dropdowns (event delegation)
document.addEventListener('click', function (e) {
    const trigger = e.target.closest('.dropdown-trigger, .multiselect-trigger');

    if (trigger) {
        const parent = trigger.closest('.custom-dropdown, .custom-multiselect');
        if (parent) {
            document.querySelectorAll('.custom-dropdown, .custom-multiselect').forEach(el => {
                if (el !== parent) el.classList.remove('active');
            });
            parent.classList.toggle('active');
            e.stopPropagation();
            return;
        }
    }

    const isClickInside = e.target.closest('.custom-dropdown, .custom-multiselect');
    if (!isClickInside) {
        document.querySelectorAll('.custom-dropdown, .custom-multiselect').forEach(el => {
            el.classList.remove('active');
        });
    }
});

// Global selectOption function
window.selectOption = function (dropdownId, value, label, type) {
    console.log('🟢 selectOption llamado:', { dropdownId, value, label, type });
    const dropdown = document.getElementById(dropdownId);
    const input = document.getElementById('input_' + type);
    const labelSpan = document.getElementById('label_' + type);
    const searchInput = (dropdownId === 'frenteFilterSelect') ? document.getElementById('filterSearchInput') :
        (dropdownId === 'tipoFilterSelect') ? document.getElementById('filterTipoSearchInput') : null;

    // Tratar valores vacíos como vacíos, pero PERMITIR 'all' para indicar "Todos" explícitamente al backend
    const effectiveValue = (value === null || value === undefined) ? '' : value;
    console.log('   effectiveValue:', effectiveValue);

    if (input) input.value = effectiveValue;
    if (labelSpan) labelSpan.innerText = label;
    if (searchInput) {
        searchInput.placeholder = label;
        searchInput.value = '';
    }

    // Resetear estilos visuales del trigger cuando se limpia
    const trigger = searchInput?.closest('.dropdown-trigger');
    if (trigger && !effectiveValue) {
        trigger.style.background = '#fbfcfd';
        trigger.style.borderColor = '#cbd5e0';
    }

    if (dropdown) {
        dropdown.querySelectorAll('.dropdown-item').forEach(item => {
            item.classList.remove('selected');
            if (effectiveValue && item.innerText.trim() === label.trim()) {
                item.classList.add('selected');
            }
        });
        dropdown.classList.remove('active');
    }

    const clearBtnId = 'btn_clear_' + type.replace('_filter', '');
    const clearBtn = document.getElementById(clearBtnId);
    if (clearBtn) {
        clearBtn.style.display = effectiveValue ? 'block' : 'none';
    }

    // Dispatch custom event para que módulos reaccionen
    console.log('   📤 Disparando evento dropdown-selection');
    window.dispatchEvent(new CustomEvent('dropdown-selection', {
        detail: { dropdownId, value: effectiveValue, label, inputName: type }
    }));
    console.log('   ✅ Evento disparado');
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

window.confirmDelete = function (id, name) {
    // Use native confirm since showModal is not defined
    if (confirm(`¿Estás seguro de que deseas eliminar a "${name}"?\n\nEsta acción no se puede deshacer.`)) {
        // Use the global delete form
        const form = document.getElementById('delete-form-global');
        if (form) {
            // Set the action to the correct delete route
            form.action = `/admin/usuarios/${id}`;
            form.submit();
        } else {
            console.error('Delete form not found');
        }
    }
};

window.toggleDropdown = function (id) {
    const dropdown = document.getElementById(id);
    if (!dropdown) return;

    document.querySelectorAll('.custom-dropdown, .custom-multiselect').forEach(el => {
        if (el.id !== id) el.classList.remove('active');
    });
    dropdown.classList.toggle('active');
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

    const filter = input.value.toLowerCase();
    // Fix: Support both standard dropdown items and catalog filter items
    const items = dropdown.querySelectorAll('.dropdown-item, .filter-option-item');

    items.forEach(item => {
        const text = item.innerText.toLowerCase();
        item.style.display = text.includes(filter) ? 'block' : 'none';
    });

    if (filter.length > 0) {
        dropdown.classList.add('active');
    }
};

// Global input handler for dropdown search (event delegation)
document.addEventListener('input', function (e) {
    // Check if the target is a text input inside a dropdown trigger
    if (e.target.matches('.dropdown-trigger input[type="text"]')) {
        window.filterDropdownOptions(e.target);
    }
});




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
