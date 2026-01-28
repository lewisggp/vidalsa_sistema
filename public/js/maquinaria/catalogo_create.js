// catalogo_create.js - Catalog Form Handler
// Compatible with SPA navigation (navegacion.js)

(function () {
    'use strict';

    function handleSubmit(e) {
        e.preventDefault();
        const form = e.target;

        // Validate capacity unit
        const capacidadInput = form.querySelector('[name="CAPACIDAD"]');
        if (capacidadInput && capacidadInput.value.trim() !== '') {
            if (/^[\d.,\s]+$/.test(capacidadInput.value)) {
                if (window.showModal) {
                    window.showModal({
                        type: 'warning',
                        title: 'Falta la Unidad',
                        message: 'Indicar unidad (Kg, Ton, m³, Lts) en el campo Capacidad.',
                        confirmText: 'Corregir'
                    });
                } else {
                    alert('Indicar unidad (Kg, Ton, m³, Lts) en el campo Capacidad.');
                }
                capacidadInput.focus();
                return;
            }
        }

        // Show global preloader IMMEDIATELY
        if (typeof window.showPreloader === 'function') {
            window.showPreloader();
        } else {
            console.warn('window.showPreloader is not defined');
        }

        // Lock submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        let originalBtnContent = '';

        if (submitBtn) {
            originalBtnContent = submitBtn.innerHTML;
            submitBtn.style.width = submitBtn.offsetWidth + 'px';
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-mini"></span> Guardando...';
        }

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                // Add CSRF Token explicitly if needed, though cookie usually handles it. 
                // equipso_form.js adds it manually, so we should too for consistency.
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errorData => {
                        throw { status: response.status, body: errorData };
                    }).catch(() => {
                        throw { status: response.status, message: 'Error desconocido del servidor' };
                    });
                }
                return response.json();
            })
            .then(data => {
                // Success
                if (window.hidePreloader) window.hidePreloader(); // Hide immediately on success

                if (window.showModal) {
                    window.showModal({
                        type: 'success',
                        title: 'Éxito',
                        message: data.message || 'Modelo registrado correctamente.',
                        confirmText: 'Aceptar',
                        hideCancel: true,
                        onConfirm: () => {
                            form.reset();
                            const preview = document.getElementById('preview_referencial');
                            if (preview) {
                                preview.innerHTML = '<i class="material-icons" style="font-size: 16px; color: #cbd5e0;">photo_camera</i>';
                                preview.style.borderColor = '#cbd5e0';
                            }
                            // Redirect if provided
                            if (data.redirect) window.location.href = data.redirect;
                        }
                    });
                } else {
                    alert(data.message || 'Operación realizada correctamente.');
                    form.reset();
                    if (data.redirect) window.location.href = data.redirect;
                }
            })
            .catch(error => {
                if (window.hidePreloader) window.hidePreloader(); // Hide on error

                console.error('Error:', error);
                let errorMsg = 'Ocurrió un error inesperado.';

                if (error.status === 422 && error.body && error.body.errors) {
                    errorMsg = Object.values(error.body.errors).flat().join('\n');
                } else if (error.body && error.body.message) {
                    errorMsg = error.body.message;
                } else if (error.message) {
                    errorMsg = error.message;
                }

                if (window.showModal) {
                    window.showModal({
                        type: 'error',
                        title: 'Error',
                        message: errorMsg,
                        confirmText: 'Entendido',
                        hideCancel: true
                    });
                } else {
                    alert(errorMsg);
                }
            })
            .finally(() => {
                // Double check hide
                if (window.hidePreloader) window.hidePreloader();

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnContent;
                }
            });
    }

    // Initialize form handler
    function initCatalogoForm() {
        const form = document.getElementById('catalogoForm');
        if (!form) return;

        // Remove old listener if needed (robustness)
        const newForm = form.cloneNode(true);
        if (form.parentNode) {
            form.parentNode.replaceChild(newForm, form);
        }

        newForm.addEventListener('submit', handleSubmit);

        // Re-attach preview logic since we cloned the form
        const fileInput = newForm.querySelector('#foto_referencial');
        if (fileInput) {
            fileInput.addEventListener('change', function (e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (evt) {
                        const preview = document.getElementById('preview_referencial');
                        if (preview) {
                            preview.innerHTML = `<img src="${evt.target.result}" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 4px;">`;
                            preview.style.borderColor = 'var(--maquinaria-blue)';
                        }
                    }
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }

        console.log('Catalog Form Handler Initialized (Robust Mode)');
    }

    // Run on initial page load
    document.addEventListener('DOMContentLoaded', initCatalogoForm);

    // Run after SPA navigation (navegacion.js dispatches this event)
    window.addEventListener('spa:contentLoaded', initCatalogoForm);

    // Also try immediately in case DOM is already ready
    if (document.readyState !== 'loading') {
        initCatalogoForm();
    }
})();
