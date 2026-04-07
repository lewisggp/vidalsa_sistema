/**
 * Mantenimiento Fallas - Fault registration, recommendations, detail modal
 */
(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    /* ═══════════════════════════════════════════
       MODAL: Registrar Falla
    ═══════════════════════════════════════════ */
    window.abrirFormularioFalla = function () {
        const modal = document.getElementById('modalRegistrarFalla');
        if (modal) modal.style.display = 'flex';
    };

    window.cerrarModalFalla = function () {
        const modal = document.getElementById('modalRegistrarFalla');
        if (modal) modal.style.display = 'none';
        // Reset hidden inputs
        const eq = document.getElementById('fallaEquipo');
        if (eq) eq.value = '';
        const tipo = document.getElementById('fallaTipo');
        if (tipo) tipo.value = 'MECANICA';
        const desc = document.getElementById('fallaDescripcion');
        if (desc) desc.value = '';
        const sis = document.getElementById('fallaSistema');
        if (sis) sis.value = '';
        const rec = document.getElementById('recomendacionesContainer');
        if (rec) { rec.style.display = 'none'; rec.innerHTML = ''; }
        // Reset priority to MEDIA
        const mediaRadio = document.querySelector('input[name="fallaPrioridad"][value="MEDIA"]');
        if (mediaRadio) mediaRadio.checked = true;
        // Reset custom dropdowns
        if (window.clearDropdownFilter) {
            clearDropdownFilter('fallaEquipoDropdown');
            clearDropdownFilter('fallaTipoDropdown');
        }
        // Restore tipo placeholder
        const tipoSearch = document.querySelector('#fallaTipoDropdown [data-filter-search]');
        if (tipoSearch) tipoSearch.placeholder = 'Mecánica';
    };

    window.guardarFalla = async function () {
        const equipoId = document.getElementById('fallaEquipo')?.value;
        const tipoFalla = document.getElementById('fallaTipo')?.value || 'MECANICA';
        const sistemaAfectado = document.getElementById('fallaSistema')?.value;
        const descripcion = document.getElementById('fallaDescripcion')?.value;
        const prioridad = document.querySelector('input[name="fallaPrioridad"]:checked')?.value || 'MEDIA';

        if (!equipoId) {
            showMsg('warning', 'Selecciona un equipo');
            return;
        }
        if (!descripcion || descripcion.trim().length < 5) {
            showMsg('warning', 'Describe la falla (mínimo 5 caracteres)');
            return;
        }

        // Get frente from stored value or hidden input
        const frenteId = window._mantCurrentFrente || document.getElementById('filterFrente')?.value;
        if (!frenteId) {
            showMsg('warning', 'Selecciona un frente de trabajo en los filtros primero');
            return;
        }

        try {
            // Ensure we have a report for today
            const repResp = await fetch('/admin/mantenimiento/reporte-hoy', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ ID_FRENTE: frenteId }),
            });
            const repData = await repResp.json();

            if (!repData.success || !repData.reporte) {
                showMsg('error', 'No se pudo crear/obtener el reporte del día');
                return;
            }

            // Now create the fault
            const resp = await fetch('/admin/mantenimiento/falla', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    ID_REPORTE: repData.reporte.ID_REPORTE,
                    ID_EQUIPO: equipoId,
                    TIPO_FALLA: tipoFalla,
                    SISTEMA_AFECTADO: sistemaAfectado || null,
                    DESCRIPCION_FALLA: descripcion,
                    PRIORIDAD: prioridad,
                }),
            });

            const data = await resp.json();

            if (resp.ok && data.success) {
                cerrarModalFalla();
                showMsg('success', data.message || 'Falla registrada');
                // Refresh views
                if (typeof cargarReportes === 'function') cargarReportes();
                if (typeof verReporte === 'function') verReporte(repData.reporte.ID_REPORTE);
                // Refresh stats
                cargarStatsQuick();
            } else {
                showMsg('error', data.error || 'Error al registrar la falla');
            }
        } catch (e) {
            console.error('Error guardando falla:', e);
            showMsg('error', 'Error de conexión');
        }
    };

    /* ═══════════════════════════════════════════
       AUTO-RECOMENDACIÓN DE MATERIALES
    ═══════════════════════════════════════════ */
    window.onEquipoSelected = async function () {
        const equipoId = document.getElementById('fallaEquipo')?.value;
        console.log('Equipo seleccionado:', equipoId);
        const container = document.getElementById('recomendacionesContainer');
        if (!container) return;

        if (!equipoId) {
            container.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        try {
            const resp = await fetch('/admin/mantenimiento/recomendar/' + equipoId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await resp.json();

            if (data.recomendaciones && data.recomendaciones.length > 0) {
                container.style.display = 'block';
                container.innerHTML = renderRecomendaciones(data);
            } else {
                container.style.display = 'block';
                container.innerHTML = '<div style="padding:8px 12px; background:#f8fafc; border-radius:8px; font-size:11px; color:#94a3b8;"><i class="material-icons" style="font-size:14px; vertical-align:middle;">info</i> Este equipo no tiene especificaciones de catálogo vinculadas.</div>';
            }
        } catch (e) {
            console.error('Error cargando recomendaciones:', e);
        }
    };

    function renderRecomendaciones(data) {
        let html = '<div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:10px 14px;">';
        html += '<div style="font-size:11px; font-weight:700; color:#2563eb; margin-bottom:8px; display:flex; align-items:center; gap:4px;"><i class="material-icons" style="font-size:14px;">auto_awesome</i> Recomendaciones para ' + (data.equipo?.MARCA || '') + ' ' + (data.equipo?.MODELO || '') + '</div>';
        html += '<div style="display:flex; flex-wrap:wrap; gap:6px;">';

        data.recomendaciones.forEach(rec => {
            html += `<span onclick="copiarRecomendacion('${escapeHtml(rec.DESCRIPCION_MATERIAL)}', '${escapeHtml(rec.ESPECIFICACION || '')}')"
                style="display:inline-flex; align-items:center; gap:4px; padding:5px 10px; background:white; border:1px solid #93c5fd; border-radius:8px; font-size:11px; font-weight:600; color:#1e40af; cursor:pointer; transition:all 0.15s;"
                onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='white'"
                title="Click para copiar al portapapeles">
                <i class="material-icons" style="font-size:12px;">add_circle</i> ${escapeHtml(rec.DESCRIPCION_MATERIAL)}
            </span>`;
        });

        html += '</div>';

        // Historical materials
        if (data.historicos && data.historicos.length > 0) {
            html += '<div style="margin-top:8px; padding-top:8px; border-top:1px solid #bfdbfe;">';
            html += '<div style="font-size:10px; font-weight:600; color:#64748b; margin-bottom:4px;">Materiales usados anteriormente:</div>';
            html += '<div style="font-size:11px; color:#475569;">' + data.historicos.map(m => escapeHtml(m)).join(', ') + '</div>';
            html += '</div>';
        }

        html += '</div>';
        return html;
    }

    window.copiarRecomendacion = function (desc, spec) {
        navigator.clipboard.writeText(desc).catch(() => {});
        showMsg('success', 'Copiado: ' + desc);
    };

    /* ═══════════════════════════════════════════
       RESOLVER FALLA (Quick action)
    ═══════════════════════════════════════════ */
    window.resolverFalla = function (fallaId) {
        if (window.showModal) {
            window.showModal({
                type: 'info',
                title: 'Resolver Falla',
                message: '¿Marcar esta falla como resuelta?',
                confirmText: 'Sí, Resolver',
                cancelText: 'Cancelar',
                onConfirm: async function () {
                    try {
                        const resp = await fetch('/admin/mantenimiento/falla/' + fallaId, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                ESTADO_FALLA: 'RESUELTA',
                                DESCRIPCION_RESOLUCION: 'Resuelta desde panel de mantenimiento',
                            }),
                        });
                        const data = await resp.json();
                        if (data.success) {
                            showMsg('success', 'Falla resuelta');
                            if (typeof cargarReportes === 'function') cargarReportes();
                            // Refresh the current report view
                            const currentReport = document.querySelector('[data-falla-id="' + fallaId + '"]')?.closest('.mant-card');
                            if (currentReport && typeof verReporte === 'function' && window.currentReporteId) {
                                verReporte(window.currentReporteId);
                            }
                            cargarStatsQuick();
                        }
                    } catch (e) {
                        console.error('Error resolviendo falla:', e);
                    }
                }
            });
        }
    };

    /* ═══════════════════════════════════════════
       DETALLE FALLA MODAL
    ═══════════════════════════════════════════ */
    window.verDetalleFalla = async function (fallaId) {
        const modal = document.getElementById('modalDetalleFalla');
        const body = document.getElementById('detalleFallaBody');
        if (!modal || !body) return;

        modal.style.display = 'flex';
        body.innerHTML = '<p style="color:#94a3b8; text-align:center; padding:20px;">Cargando...</p>';

        // For now, show basic info from the table row
        const row = document.querySelector(`[data-falla-id="${fallaId}"]`);
        if (row) {
            const cells = row.querySelectorAll('td');
            body.innerHTML = `
                <div style="margin-bottom:16px;">
                    <label style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Equipo</label>
                    <p style="font-size:14px; font-weight:700; color:#1e293b; margin:4px 0;">${cells[1]?.innerHTML || ''}</p>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Tipo de Falla</label>
                    <p style="font-size:14px; color:#334155; margin:4px 0;">${cells[2]?.textContent?.trim() || ''}</p>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Descripción</label>
                    <p style="font-size:14px; color:#334155; margin:4px 0;">${cells[3]?.textContent?.trim() || ''}</p>
                </div>
                <div style="display:flex; gap:16px; margin-bottom:16px;">
                    <div>
                        <label style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Prioridad</label>
                        <p style="margin:4px 0;">${cells[4]?.innerHTML || ''}</p>
                    </div>
                    <div>
                        <label style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Estado</label>
                        <p style="margin:4px 0;">${cells[5]?.innerHTML || ''}</p>
                    </div>
                </div>
            `;
        }
    };

    window.cerrarDetalleFalla = function () {
        const modal = document.getElementById('modalDetalleFalla');
        if (modal) modal.style.display = 'none';
    };

    /* ═══════════════════════════════════════════
       HELPERS
    ═══════════════════════════════════════════ */
    function showMsg(type, message) {
        if (window.showModal) {
            window.showModal({ type: type, title: type === 'success' ? '¡Éxito!' : type === 'error' ? 'Error' : 'Aviso', message: message, confirmText: 'Aceptar', hideCancel: true });
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    async function cargarStatsQuick() {
        try {
            const resp = await fetch('/admin/mantenimiento/stats', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await resp.json();
            const el = (id) => document.getElementById(id);
            if (el('statFallasAbiertas')) el('statFallasAbiertas').textContent = data.fallas_abiertas_hoy ?? 0;
            if (el('statInoperativos')) el('statInoperativos').textContent = data.equipos_inoperativos ?? 0;
        } catch (e) { /* silent */ }
    }

})();
