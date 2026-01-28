/**
 * equipos_form.js - Logic for Create/Edit Equipo Forms
 * SPA-Compatible via ModuleManager
 */

function initEquiposForm() {
    const form = document.getElementById('createEquipoForm') || document.getElementById('editEquipoForm'); // Support both
    if (!form) return;

    // --- HELPER FUNCTIONS (Unified Validation Engine) ---
    const showFieldError = (input, message) => {
        if (!input) return;
        input.classList.add('is-invalid');
        const parent = input.parentNode;
        if (!parent) return;

        // Remove existing
        const existing = parent.querySelectorAll('.invalid-feedback');
        existing.forEach(el => el.remove());

        // Add new
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.style.color = '#e53e3e';
        feedback.style.fontSize = '12px';
        feedback.style.marginTop = '4px';
        feedback.style.display = 'block';
        feedback.innerText = message;
        parent.appendChild(feedback);
    };

    const clearFieldError = (input) => {
        if (!input) return;
        input.classList.remove('is-invalid');
        const parent = input.parentNode;
        if (parent) {
            const existing = parent.querySelectorAll('.invalid-feedback');
            existing.forEach(el => el.remove());
        }
    };

    const showGlobalSummary = (messages = []) => {
        const existing = document.getElementById('errorSummary');
        if (existing) existing.remove();

        const summaryHtml = `
            <div id="errorSummary" style="background: #fff5f5; border: 1px solid #fed7d7; color: #c53030; padding: 12px 15px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600;">
                <i class="material-icons" style="color: var(--maquinaria-red);">error_outline</i>
                <span>Atención: Hemos detectado errores. Por favor, verifica los campos marcados en rojo.</span>
            </div>
        `;
        form.insertAdjacentHTML('afterbegin', summaryHtml);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // --- LIVE VALIDATION ---
    const checkUniqueness = (input) => {
        if (!input.value.trim()) return;
        if (input.dataset.lastChecked === input.value.trim()) return;

        const fieldMap = {
            'SERIAL_CHASIS': 'SERIAL_CHASIS',
            'SERIAL_DE_MOTOR': 'SERIAL_DE_MOTOR',
            'CODIGO_PATIO': 'CODIGO_PATIO',
            'PLACA': 'PLACA',
            'documentacion[PLACA]': 'PLACA'
        };

        const fieldName = fieldMap[input.name] || fieldMap[input.id] || (input.id === 'placa' ? 'PLACA' : null);
        if (!fieldName) return;

        input.dataset.lastChecked = input.value.trim();

        // Loader
        let feedbackLoader = input.parentNode.querySelector('.validation-loader');
        if (!feedbackLoader) {
            feedbackLoader = document.createElement('span');
            feedbackLoader.className = 'validation-loader';
            feedbackLoader.style.fontSize = '12px';
            feedbackLoader.style.color = '#0067b1';
            feedbackLoader.style.marginLeft = '8px';
            feedbackLoader.style.fontWeight = '600';
            feedbackLoader.innerText = 'Verificando...';
            input.parentNode.appendChild(feedbackLoader);
        }
        feedbackLoader.style.display = 'inline';

        // Assuming endpoint exists
        fetch(`/admin/equipos/check-unique?field=${fieldName}&value=${encodeURIComponent(input.value.trim())}`)
            .then(r => r.json())
            .then(data => {
                feedbackLoader.style.display = 'none';
                if (data.exists) {
                    showFieldError(input, `Este valor ya ha sido registrado.`);
                    input.dataset.isDuplicate = "true";
                } else {
                    clearFieldError(input);
                    input.dataset.isDuplicate = "false";
                }
            })
            .catch(err => {
                console.error(err);
                feedbackLoader.style.display = 'none';
            });
    };

    // Attach Blur Listeners
    ['serial_chasis', 'serial_motor', 'codigo_patio', 'placa'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            // Remove old listener if any (cleansing)
            const newNode = input.cloneNode(true);
            input.parentNode.replaceChild(newNode, input);
            newNode.addEventListener('blur', () => checkUniqueness(newNode));
        }
    });

    // --- SUBMIT HANDLER ---
    // Remove previous listener by cloning form? No, risky with other plugins.
    // We rely on ModuleManager running this ONCE per page load.

    // Check if handler already attached?
    if (form.dataset.handlerAttached) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();

        // A. Pending Validation Check
        const pendingValidations = Array.from(form.querySelectorAll('.validation-loader')).filter(el => el.style.display !== 'none');
        if (pendingValidations.length > 0) {
            if (typeof window.showModal === 'function') {
                window.showModal({ type: 'info', title: 'Verificando Datos', message: 'Estamos validando seriales...', confirmText: 'Entendido', hideCancel: true });
            }
            return;
        }

        // B. Clear Errors
        const summary = document.getElementById('errorSummary');
        if (summary) summary.style.display = 'none';

        // C. Client Validation
        let hasEmpty = false;
        form.querySelectorAll('[required]').forEach(input => {
            if (!input.value.trim()) {
                let label = input.closest('div').querySelector('label')?.innerText.replace('*', '').trim() || input.name;
                showFieldError(input, `El campo ${label} es obligatorio.`);
                hasEmpty = true;
            }
        });

        // Custom dropdowns hidden inputs
        ['TIPO_EQUIPO', 'CATEGORIA_FLOTA', 'ESTADO_OPERATIVO', 'MARCA', 'MODELO'].forEach(name => {
            const input = form.querySelector(`input[name="${name}"]`);
            if (input && !input.value.trim()) {
                showFieldError(input, `El campo ${name} es obligatorio.`);
                hasEmpty = true;
            }
        });

        const invalidInputs = form.querySelectorAll('.is-invalid');
        if (hasEmpty || invalidInputs.length > 0) {
            showGlobalSummary();
            return;
        }

        // D. Submit
        if (typeof window.showPreloader === 'function') window.showPreloader();

        const formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
            .then(r => r.json().then(data => ({ status: r.status, body: data })))
            .then(({ status, body }) => {
                if (window.hidePreloader) window.hidePreloader();

                if (status === 200 || status === 201) {
                    if (typeof window.showModal === 'function') {
                        window.showModal({
                            type: 'success',
                            title: '¡Éxito!',
                            message: body.message || 'Operación exitosa.',
                            confirmText: 'Aceptar',
                            hideCancel: true,
                            onConfirm: () => window.location.href = body.redirect || '/admin/equipos'
                        });
                        setTimeout(() => window.location.href = body.redirect || '/admin/equipos', 2000);
                    } else {
                        window.location.href = body.redirect || '/admin/equipos';
                    }
                } else if (status === 422) {
                    Object.entries(body.errors).forEach(([field, msgs]) => {
                        let input = form.querySelector(`[name="${field}"]`);
                        if (!input && field.includes('.')) {
                            const parts = field.split('.');
                            const bracketName = parts.shift() + parts.map(p => `[${p}]`).join('');
                            input = form.querySelector(`[name="${bracketName}"]`);
                        }
                        if (input) showFieldError(input, msgs[0]);
                    });
                    showGlobalSummary();
                } else {
                    throw new Error(body.message || 'Error desconocido.');
                }
            })
            .catch(err => {
                if (window.hidePreloader) window.hidePreloader();
                console.error(err);
                if (typeof window.showModal === 'function') window.showModal({ type: 'error', title: 'Error', message: 'Ocurrió un error inesperado.', confirmText: 'Cerrar', hideCancel: true });
            });
    });

    form.dataset.handlerAttached = "true";
}

// Register with Module Manager
ModuleManager.register('equipos_form',
    () => document.getElementById('createEquipoForm') !== null || document.getElementById('editEquipoForm') !== null,
    initEquiposForm
);
