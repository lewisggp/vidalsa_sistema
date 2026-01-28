// form_logic.js - Externalized Logic for Form Fields to comply with CSP
// Version: 2.0 - Using programmatic event listeners instead of inline handlers

// --- CRITICAL: Global Dropdown Functions (Must be outside IIFE for inline handlers) ---
window.toggleDropdown = function (id) {
    const dropdown = document.getElementById(id);
    // Close all other dropdowns and multiselects
    document.querySelectorAll('.custom-dropdown, .custom-multiselect').forEach(d => {
        if (d.id !== id) d.classList.remove('active');
    });

    if (dropdown) {
        dropdown.classList.toggle('active');
    }

    if (window.event) window.event.stopPropagation();
};

window.updateSelectedCount = function () {
    const checkboxes = document.querySelectorAll('input[name="PERMISOS[]"]:checked');
    const label = document.getElementById('selectedCount');
    if (label) {
        if (checkboxes.length === 0) {
            label.innerText = 'Seleccione permisos...';
        } else if (checkboxes.length === 1) {
            // Get text from the label sibling of the checkbox
            const text = checkboxes[0].nextElementSibling.innerText;
            label.innerText = text;
        } else {
            label.innerText = `${checkboxes.length} permisos seleccionados`;
        }
    }
};

window.selectOption = function (dropdownId, value, display, type) {
    const dropdown = document.getElementById(dropdownId);
    const input = document.getElementById('input_' + type);
    const label = document.getElementById('label_' + type);
    const searchInput = document.getElementById('filterSearchInput');
    const searchInputTipo = document.getElementById('filterTipoSearchInput');

    if (input) input.value = value;
    if (label) label.innerText = display;

    if (type === 'frente_filter' && searchInput) searchInput.placeholder = display;
    if (type === 'tipo_filter' && searchInputTipo) searchInputTipo.placeholder = display;

    if (dropdown) dropdown.classList.remove('active');

    if (input) {
        const event = new Event('change', { bubbles: true });
        input.dispatchEvent(event);
    }

    window.dispatchEvent(new CustomEvent('dropdown-selection', { detail: { type: type, value: value } }));
};

(function () {
    // Helper function to update PDF button with loading animation
    window.updatePdfBtn = function (input, wrapperId) {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;

        if (input.files && input.files[0]) {
            const file = input.files[0];

            // 1. Loading State (Spinner)
            wrapper.className = 'upload-placeholder-mini';
            wrapper.style.width = '30px';
            wrapper.style.height = '30px';
            wrapper.style.borderRadius = '50%';
            wrapper.style.border = '2px solid rgba(0, 103, 177, 0.1)';

            // Spinner HTML
            wrapper.innerHTML = `
                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                    <i class="material-icons" style="font-size: 18px; color: var(--maquinaria-blue); animation: spin 0.8s linear infinite;">sync</i>
                </div>
            `;

            // Simulate processing time then show success
            setTimeout(() => {
                // 2. Success State (Green Circle Check)
                wrapper.innerHTML = `
                    <label for="${input.id}" title="${file.name}" style="width: 100%; height: 100%; border-radius: 50%; background: #2c7a7b; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; border: none; animation: popIn 0.3s ease;">
                        <i class="material-icons" style="font-size: 18px;">check</i>
                    </label>
                `;
                // Clear border style after success
                wrapper.style.border = 'none';

            }, 600);

        } else {
            // Revert to "Add" style if cleared
            wrapper.className = 'upload-placeholder-mini';
            wrapper.style.borderRadius = '50%';
            wrapper.style.border = 'none';
            wrapper.innerHTML = `
                <label for="${input.id}" title="Cargar Documento" style="border-radius: 50%; border-style: solid; border-width: 2px;">
                    <i class="material-icons" style="font-size: 18px;">add</i>
                </label>
             `;
        }
        if (window.validateDocPair) window.validateDocPair();
    };

    window.updateFileName = window.updatePdfBtn; // Alias for backwards compatibility

    // Image preview function with loading animation
    window.previewImage = function (input, previewId) {
        const preview = document.getElementById(previewId);
        if (!preview) return;

        if (input.files && input.files[0]) {
            preview.innerHTML = `<i class="material-icons" style="font-size: 16px; color: var(--maquinaria-blue); animation: spin 1s linear infinite;">sync</i>`;
            preview.style.borderColor = 'var(--maquinaria-blue)';

            const startTime = Date.now();
            const minTime = 1200;

            const reader = new FileReader();
            reader.onload = function (e) {
                const elapsed = Date.now() - startTime;
                const remaining = Math.max(0, minTime - elapsed);

                setTimeout(() => {
                    preview.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 4px;">`;
                    preview.style.transform = 'scale(1.1)';
                    setTimeout(() => preview.style.transform = 'scale(1)', 200);
                }, remaining);
            }
            reader.readAsDataURL(input.files[0]);
        }
    };

    // Validation for document pairs (meta field + file input)
    window.validateDocPair = function () {
        try {
            const metaInputs = document.querySelectorAll('.doc-meta');
            const fileInputs = document.querySelectorAll('.doc-file');

            // 1. Meta -> File requirement
            metaInputs.forEach(meta => {
                const fileId = meta.dataset.fileTarget;
                const fileInput = document.getElementById(fileId);
                if (!fileInput) return;
                const hasExisting = meta.dataset.hasExisting === 'true';

                if (meta.value.trim() !== '') {
                    if (!hasExisting && (!fileInput.files || fileInput.files.length === 0)) {
                        // Optional validation logic
                    }
                }
            });

            // 2. File -> Meta requirement
            fileInputs.forEach(fileInput => {
                const metaId = fileInput.dataset.metaTarget;
                const meta = document.getElementById(metaId);
                if (!meta) return;

                const errorMsg = document.getElementById('error_meta_' + metaId);

                // Logic: If new file selected AND meta is empty -> ERROR
                if (fileInput.files && fileInput.files.length > 0) {
                    if (meta.value.trim() === '') {
                        meta.style.borderColor = '#e53e3e';
                        meta.setCustomValidity('Debe indicar el dato asociado.');
                        if (errorMsg) errorMsg.style.display = 'block';
                    } else {
                        meta.style.borderColor = '';
                        meta.setCustomValidity('');
                        if (errorMsg) errorMsg.style.display = 'none';
                    }
                } else {
                    if (meta.style.borderColor === 'rgb(229, 62, 62)' || meta.style.borderColor === '#e53e3e') {
                        meta.style.borderColor = '';
                        meta.setCustomValidity('');
                        if (errorMsg) errorMsg.style.display = 'none';
                    }
                }
            });
        } catch (e) {
            console.error('Validation logic error', e);
        }
    };

    // Attach event listeners to PDF file inputs
    function attachPdfListeners() {
        // PDF document file inputs (doc_propiedad, poliza_seguro, doc_rotc, doc_racda)
        const pdfInputs = {
            'doc_propiedad': 'wrapper_propiedad',
            'poliza_seguro': 'wrapper_poliza',
            'doc_rotc': 'wrapper_rotc',
            'doc_racda': 'wrapper_racda'
        };

        Object.entries(pdfInputs).forEach(([inputId, wrapperId]) => {
            const input = document.getElementById(inputId);
            if (input && !input.dataset.listenerAttached) {
                input.addEventListener('change', function () {
                    window.updatePdfBtn(this, wrapperId);
                });
                input.dataset.listenerAttached = 'true';
            }
        });

        // Photo input listener
        const fotoInput = document.getElementById('foto_equipo');
        if (fotoInput && !fotoInput.dataset.listenerAttached) {
            fotoInput.addEventListener('change', function () {
                window.previewImage(this, 'preview_equipo');
            });
            fotoInput.dataset.listenerAttached = 'true';
        }

        // Catalog Model Photo input listener
        const catalogFotoInput = document.getElementById('foto_referencial');
        if (catalogFotoInput && !catalogFotoInput.dataset.listenerAttached) {
            catalogFotoInput.addEventListener('change', function () {
                window.previewImage(this, 'preview_referencial');
            });
            catalogFotoInput.dataset.listenerAttached = 'true';
        }
    }

    // --- Dynamic Autocomplete Logic ---
    function setupAutocomplete() {
        const fields = [
            { id: 'marca', listId: 'marcas_list', type: 'MARCA' },
            { id: 'modelo', listId: 'modelos_list', type: 'MODELO' }
        ];

        fields.forEach(field => {
            const input = document.getElementById(field.id);
            const dataList = document.getElementById(field.listId);

            if (!input || !dataList) return;
            // Prevent multiple attachments if SPA re-runs
            if (input.dataset.autocompleteAttached) return;

            input.dataset.autocompleteAttached = 'true';

            let timeout = null;

            input.addEventListener('input', function () {
                const val = this.value;
                if (val.length < 2) return; // Wait for 2 chars

                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    fetch(`/admin/equipos/search-field?field=${field.type}&query=${encodeURIComponent(val)}`)
                        .then(r => r.json())
                        .then(data => {
                            dataList.innerHTML = '';
                            data.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item;
                                dataList.appendChild(option);
                            });
                        })
                        .catch(e => console.error('Autocomplete error', e));
                }, 300); // 300ms debounce
            });
        });

        // Specs Search Listener (Programmatic Attachment)
        const specsInput = document.getElementById('searchInputSpecs');
        if (specsInput && !specsInput.dataset.searchAttached) {
            specsInput.addEventListener('input', function () {
                window.searchSpecs(this);
            });
            specsInput.dataset.searchAttached = 'true';
        }
    }

    // Initialize on page load
    function initFormLogic() {
        // Attach PDF upload listeners
        attachPdfListeners();

        // Run initial validation
        if (window.validateDocPair) window.validateDocPair();

        // Setup Autocomplete
        setupAutocomplete();

        // Attach validation listeners to meta inputs
        document.querySelectorAll('.doc-meta').forEach(input => {
            if (!input.dataset.validationAttached) {
                input.addEventListener('input', window.validateDocPair);
                input.dataset.validationAttached = 'true';
            }
        });

        // Attach validation listeners to file inputs
        document.querySelectorAll('.doc-file').forEach(input => {
            if (!input.dataset.validationAttached) {
                input.addEventListener('change', window.validateDocPair);
                input.dataset.validationAttached = 'true';
            }
        });
    }

    // Execute when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFormLogic);
    } else {
        initFormLogic();
    }

    // RE-INITIALIZE when SPA navigation loads new content
    // This handles navigation via the "Nuevo" button from index page
    window.addEventListener('spa:contentLoaded', function () {
        // Small delay to ensure DOM is fully updated
        setTimeout(initFormLogic, 50);
    });

    window.filterDropdownOptions = function (input) {
        const filter = input.value.toLowerCase();
        const dropdown = input.closest('.custom-dropdown');
        if (!dropdown) return;
        const items = dropdown.querySelectorAll('.dropdown-item');

        items.forEach(item => {
            const text = item.textContent || item.innerText;
            if (text.toLowerCase().indexOf(filter) > -1) {
                item.style.display = "";
            } else {
                item.style.display = "none";
            }
        });
    };

    // Close dropdowns when clicking outside
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.custom-dropdown')) {
            document.querySelectorAll('.custom-dropdown').forEach(d => {
                d.classList.remove('active');
            });
        }
    });

    let specsTimeout = null;
    window.searchSpecs = function (input) {
        const val = input.value;
        const list = document.getElementById('specs_results_list');

        if (!list) return;

        if (val.length < 1) {
            list.innerHTML = '<div class="dropdown-item" onclick="selectOption(\'specSelect\', \'\', \'Ninguno\', \'spec\')">Sin Ficha Técnica</div>';
            return;
        }

        clearTimeout(specsTimeout);
        specsTimeout = setTimeout(() => {
            list.innerHTML = '<div style="padding:10px; color:#cbd5e0; font-size:12px; text-align:center;">Buscando...</div>';

            fetch(`/admin/equipos/search-specs?query=${encodeURIComponent(val)}`)
                .then(r => r.json())
                .then(data => {
                    list.innerHTML = '';
                    // Always add 'None' option
                    const noneDiv = document.createElement('div');
                    noneDiv.className = 'dropdown-item';
                    noneDiv.onclick = () => window.selectOption('specSelect', '', 'Ninguno', 'spec');
                    noneDiv.innerText = 'Sin Ficha Técnica';
                    list.appendChild(noneDiv);

                    if (data.length === 0) {
                        const empty = document.createElement('div');
                        empty.style.padding = '10px';
                        empty.style.color = '#718096';
                        empty.style.fontSize = '12px';
                        empty.innerText = 'No se encontraron resultados';
                        list.appendChild(empty);
                    }

                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'dropdown-item';
                        div.onclick = () => window.selectOption('specSelect', item.ID_ESPEC, item.MODELO, 'spec');
                        div.innerText = item.MODELO;
                        list.appendChild(div);
                    });
                })
                .catch(e => {
                    console.error('Specs search error', e);
                    list.innerHTML = '<div style="padding:10px; color:red; font-size:12px;">Error al buscar</div>';
                });
        }, 300);
    };



    // --- User Form Logic (Centralized) ---
    document.addEventListener('submit', function (e) {
        if (e.target && e.target.id === 'userForm') {
            e.preventDefault();
            handleUserFormSubmit(e.target);
        }
    });

    function handleUserFormSubmit(form) {
        // Clear previous errors
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.is-invalid-border').forEach(el => el.classList.remove('is-invalid-border'));
        document.querySelectorAll('.error-message-inline').forEach(el => el.remove());

        if (window.showPreloader) window.showPreloader();

        const formData = new FormData(form);

        // Client-side validation
        const clientErrors = validateClientSide(formData);
        if (Object.keys(clientErrors).length > 0) {
            if (window.hidePreloader) window.hidePreloader();
            handleValidationErrors(clientErrors);
            showModal({
                type: 'warning',
                title: 'Atención',
                message: 'Por favor complete todos los campos obligatorios marcados en rojo.',
                confirmText: 'Entendido',
                hideCancel: true
            });
            return;
        }

        const url = form.action;
        const methodInput = form.querySelector('input[name="_method"]');
        const method = methodInput ? methodInput.value : 'POST';

        fetch(url, {
            method: method === 'GET' ? 'GET' : 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(({ status, body }) => {
                if (status === 200 || status === 201) {
                    if (window.hidePreloader) window.hidePreloader();

                    // Check if it's Edit or Create
                    const isEdit = form.querySelector('input[name="_method"][value="PUT"]') ||
                        form.querySelector('input[name="_method"][value="PATCH"]');

                    if (!isEdit) {
                        // CREATE MODE: Reset Form without reload
                        form.reset();

                        // Reset Custom Dropdowns UI
                        const resets = [
                            { id: 'input_rol', label: 'label_rol', text: 'Seleccione un rol...' },
                            { id: 'input_nivel', label: 'label_nivel', text: 'Seleccione nivel de acceso...' },
                            { id: 'input_frente', label: 'label_frente', text: 'Seleccione frente de trabajo...' },
                            { id: 'input_estatus', label: 'label_estatus', text: 'ACTIVO', val: 'ACTIVO' } // Default Active
                        ];

                        resets.forEach(field => {
                            const input = document.getElementById(field.id);
                            const label = document.getElementById(field.label);
                            if (input) input.value = field.val || '';
                            if (label) label.innerText = field.text;
                        });

                        // Reset Permissions Multiselect
                        document.querySelectorAll('input[name="PERMISOS[]"]').forEach(cb => cb.checked = false);
                        const permLabel = document.getElementById('selectedCount');
                        if (permLabel) permLabel.innerText = 'Seleccione permisos...';

                        // Remove 'selected' class from all dropdown items
                        document.querySelectorAll('.dropdown-item.selected').forEach(el => el.classList.remove('selected'));
                        // Re-select default ACTIVO
                        const activeItem = document.querySelector(`[onclick*="'ACTIVO', 'ACTIVO'"]`);
                        if (activeItem) activeItem.classList.add('selected');

                        showModal({
                            type: 'success',
                            title: '¡Éxito!',
                            message: body.message || 'Usuario creado correctamente.',
                            confirmText: 'Aceptar',
                            hideCancel: true
                        });

                        // No reload needed. User can register another immediately.

                    } else {
                        // EDIT MODE: Redirect to index
                        showModal({
                            type: 'success',
                            title: '¡Éxito!',
                            message: body.message || 'Usuario actualizado correctamente.',
                            confirmText: 'Aceptar',
                            hideCancel: true
                        });

                        setTimeout(() => {
                            if (body.redirect) {
                                if (window.navigateTo) {
                                    window.navigateTo(body.redirect);
                                } else {
                                    window.location.href = body.redirect;
                                }
                            } else {
                                window.location.reload();
                            }
                        }, 1500);
                    }

                } else if (status === 422) {
                    if (window.hidePreloader) window.hidePreloader();
                    handleValidationErrors(body.errors);
                    showModal({
                        type: 'warning',
                        title: 'Atención',
                        message: 'El formulario contiene errores. Por favor revise los campos marcados.',
                        confirmText: 'Entendido',
                        hideCancel: true
                    });
                } else {
                    throw new Error(body.message || 'Error desconocido del servidor');
                }
            })
            .catch(error => {
                if (window.hidePreloader) window.hidePreloader();
                console.error('Submission error:', error);
                showModal({
                    type: 'error',
                    title: 'Error',
                    message: error.message || 'Ocurrió un error al procesar la solicitud.',
                    confirmText: 'Cerrar',
                    hideCancel: true
                });
            });
    }

    function validateClientSide(formData) {
        const errors = {};

        // 1. Basic Fields
        if (!formData.get('NOMBRE_COMPLETO') || formData.get('NOMBRE_COMPLETO').trim() === '') {
            errors['NOMBRE_COMPLETO'] = ['El nombre completo es obligatorio.'];
        }

        const email = formData.get('CORREO_ELECTRONICO');
        if (!email || email.trim() === '') {
            errors['CORREO_ELECTRONICO'] = ['El correo electrónico es obligatorio.'];
        } else if (!email.includes('@cvidalsa27.com')) {
            errors['CORREO_ELECTRONICO'] = ['Solo se permiten correos con el dominio @cvidalsa27.com'];
        }

        // 2. Dropdowns (Hidden Inputs)
        if (!formData.get('ID_ROL')) errors['ID_ROL'] = ['Debes asignar un rol al usuario.'];
        if (!formData.get('NIVEL_ACCESO')) errors['NIVEL_ACCESO'] = ['El nivel de acceso es obligatorio.'];
        if (!formData.get('ESTATUS')) errors['ESTATUS'] = ['El estatus es obligatorio.'];
        if (!formData.get('ID_FRENTE_ASIGNADO')) errors['ID_FRENTE_ASIGNADO'] = ['Debes asignar un frente de trabajo.'];

        // 3. Permissions
        let hasPermissions = false;
        for (const key of formData.keys()) {
            if (key.startsWith('PERMISOS')) {
                hasPermissions = true;
                break;
            }
        }
        if (!hasPermissions) {
            errors['PERMISOS'] = ['Debes seleccionar al menos un permiso.'];
        }

        // 4. Password (Only if creating)
        const method = formData.get('_method');
        const isUpdate = method === 'PUT' || method === 'PATCH';

        if (!isUpdate) {
            const password = formData.get('password');
            if (!password || password.length < 6) {
                errors['password'] = ['La clave de acceso es obligatoria y debe tener al menos 6 caracteres.'];
            }
        }

        return errors;
    }

    function handleValidationErrors(errors) {
        for (const [field, messages] of Object.entries(errors)) {
            const input = document.querySelector(`[name="${field}"]`) || document.querySelector(`[name="${field}[]"]`);
            if (input) {
                input.classList.add('is-invalid');

                const dropdown = input.closest('.custom-dropdown') || input.closest('.custom-multiselect');
                if (dropdown) {
                    const trigger = dropdown.querySelector('.dropdown-trigger') || dropdown.querySelector('.multiselect-trigger');
                    if (trigger) trigger.classList.add('is-invalid-border');
                }

                const errorSpan = document.createElement('span');
                errorSpan.className = 'error-message-inline';
                errorSpan.innerText = messages[0];

                let parent = input.closest('div');
                if (dropdown) {
                    parent = dropdown.closest('div');
                }

                if (parent) {
                    parent.appendChild(errorSpan);
                }
            }
        }
    }

})();
