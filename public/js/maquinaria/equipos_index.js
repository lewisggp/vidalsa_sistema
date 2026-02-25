// equipos_index.js - Equipos Module Logic
// Version: 2.2 - Global Selection & CSP Fixes

// Use window to ensure persistent state across SPA reloads if the script is re-executed
window.selectedEquipos = window.selectedEquipos || {};

// Global Status Dropdown Logic
window.toggleStatusDropdown = function (trigger) {
    if (!trigger) return;

    // PERMISSION CHECK
    if (
        typeof window.CAN_CHANGE_STATUS !== "undefined" &&
        window.CAN_CHANGE_STATUS === false
    ) {
        if (window.showModal) {
            showModal({
                type: "error",
                title: "Acceso Denegado",
                message: "No tienes permisos para cambiar el estatus.",
                confirmText: "Entendido",
                hideCancel: true,
            });
        }
        return;
    }

    document.querySelectorAll(".status-dropdown-menu").forEach((menu) => {
        if (menu.previousElementSibling !== trigger) {
            menu.style.display = "none";
        }
    });

    const menu = trigger.nextElementSibling;
    if (menu) {
        const isHidden =
            menu.style.display === "none" || menu.style.display === "";
        menu.style.display = isHidden ? "block" : "none";
    }
};

// Selection UI Update Tracker
function updateSelectionUI() {
    const ids = Object.keys(window.selectedEquipos);
    const count = ids.length;
    const bar = document.getElementById("bulkFloatingBar");
    const text = document.getElementById("bulkCountText");

    if (bar && text) {
        if (count > 0) {
            text.innerText = count;
            bar.classList.add("active");

            // Role-based visibility for Anchor button
            const anchorBtn = bar.querySelector(
                'button[onclick="openAnchorModal(event)"]',
            );
            if (anchorBtn) {
                const selections = Object.values(window.selectedEquipos);
                const canAnchor =
                    selections.length === 1 &&
                    (selections[0].rolAnclaje === "REMOLCADOR" ||
                        selections[0].rolAnclaje === "REMOLCABLE");
                anchorBtn.style.display = canAnchor ? "flex" : "none";

                // Unanchor button visibility
                const unanchorBtn = document.getElementById("btnUnanchor");
                if (unanchorBtn) {
                    let canUnanchor = false;
                    if (selections.length === 2) {
                        const s1 = selections[0];
                        const s2 = selections[1];
                        // If cross-referenced
                        if (
                            String(s1.anchorId) === String(s2.id) &&
                            String(s2.anchorId) === String(s1.id)
                        ) {
                            canUnanchor = true;
                        }
                    }
                    unanchorBtn.style.display = canUnanchor ? "flex" : "none";
                }
            }
        } else {
            bar.classList.remove("active");
        }
    }
}

// Re-apply blue highlight to all rows that are in selectedEquipos
// Called after every table render to keep visual state in sync
function reApplySelections() {
    if (
        !window.selectedEquipos ||
        Object.keys(window.selectedEquipos).length === 0
    )
        return;

    const tableBody = document.getElementById("equiposTableBody");
    if (!tableBody) return;

    tableBody.querySelectorAll("tr").forEach((row) => {
        const btn = row.querySelector(".btn-details-mini");
        if (!btn) return;
        const id = String(btn.dataset.equipoId);
        if (window.selectedEquipos.hasOwnProperty(id)) {
            row.classList.add("selected-row-maquinaria");
        }
    });
}

// Global Selection Action
window.clearSelection = function (event) {
    // Prevent event bubbling to avoid conflicts
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }

    // Defensive check to prevent re-execution
    if (
        !window.selectedEquipos ||
        Object.keys(window.selectedEquipos).length === 0
    ) {
        return;
    }

    window.selectedEquipos = {};
    document.querySelectorAll(".selected-row-maquinaria").forEach((row) => {
        row.classList.remove("selected-row-maquinaria");
    });
    updateSelectionUI();
};

// Row Click Logic (Delegated)
function handleRowClick(e) {
    // Look for target row in the equipos table
    const row = e.target.closest("#equiposTableBody tr");
    if (!row) return;

    // Ignore if clicking interactive elements
    if (
        e.target.closest("button") ||
        e.target.closest(".custom-dropdown") ||
        e.target.closest(".material-icons") ||
        e.target.closest("a") ||
        e.target.closest("input")
    )
        return;

    const btnDetails = row.querySelector(".btn-details-mini");
    if (!btnDetails) return;

    const id = btnDetails.dataset.equipoId;
    const code = btnDetails.dataset.codigo;
    const frenteId = btnDetails.dataset.frenteId;
    const rolAnclaje = btnDetails.dataset.rolAnclaje;
    const anchorId = btnDetails.dataset.anchorId;

    const isSelecting = !(id in window.selectedEquipos);

    const toggleSelection = (
        targetId,
        targetCode,
        targetFrente,
        targetRol,
        targetAnchorId, // Added parameter for anchorId
        targetRow,
    ) => {
        if (isSelecting) {
            window.selectedEquipos[targetId] = {
                id: targetId, // Added id to the stored object
                code: targetCode,
                frenteId: targetFrente,
                rolAnclaje: targetRol,
                anchorId: targetAnchorId, // Store the anchorId
            };
            if (targetRow) targetRow.classList.add("selected-row-maquinaria");
        } else {
            delete window.selectedEquipos[targetId];
            if (targetRow)
                targetRow.classList.remove("selected-row-maquinaria");
        }
    };

    // Toggle main equipment
    toggleSelection(id, code, frenteId, rolAnclaje, anchorId, row);

    // Toggle anchored partner if exists
    if (anchorId && anchorId !== "" && anchorId !== "null") {
        const partnerCode = btnDetails.dataset.anchorCode;
        const partnerRol = btnDetails.dataset.anchorRol;

        // Try to find partner row in DOM for visual feedback
        const partnerBtn = document.querySelector(
            `.btn-details-mini[data-equipo-id="${anchorId}"]`,
        );
        const partnerRow = partnerBtn ? partnerBtn.closest("tr") : null;

        toggleSelection(
            anchorId,
            partnerCode || (partnerBtn ? partnerBtn.dataset.codigo : ""),
            frenteId, // Always same frente
            partnerRol || (partnerBtn ? partnerBtn.dataset.rolAnclaje : ""),
            partnerBtn ? partnerBtn.dataset.anchorId : id, // Partner's anchor is me or its metadata
            partnerRow,
        );

        // Selection Feedback (Toast)
        if (window.showToast) {
            // Priority: Partner in DOM > Clicked row dataset
            const partnerTipo = partnerBtn
                ? partnerBtn.dataset.tipo
                : btnDetails.dataset.anchorTipoNombre || "Equipo";
            const partnerPlaca = partnerBtn
                ? partnerBtn.dataset.placa
                : btnDetails.dataset.anchorPlaca;
            const partnerChasis = partnerBtn
                ? partnerBtn.dataset.chasis
                : btnDetails.dataset.anchorSerial;

            const identificador =
                partnerPlaca && partnerPlaca !== "N/A" && partnerPlaca !== ""
                    ? partnerPlaca
                    : partnerChasis || anchorId;

            if (isSelecting) {
                window.showToast(
                    `Has seleccionado también el ${partnerTipo}: ${identificador}`,
                    "info",
                );
            } else {
                window.showToast(
                    `Haz retirado también el ${partnerTipo}: ${identificador}`,
                    "info",
                );
            }
        }
    }

    updateSelectionUI();
}

// Global Event Listeners (Always attach via delegation - safe to re-run)
// NOTE: Event delegation to document allows these to work even after DOM changes
document.addEventListener("click", handleRowClick);

// Unanchoring Logic
window.unanchorEquipos = async function (e) {
    if (e) e.preventDefault();

    const selections = Object.values(window.selectedEquipos);
    if (selections.length !== 2) {
        showModal({
            type: "warning",
            title: "Selección Incorrecta",
            message:
                "Para desanclar, debes seleccionar exactamente dos equipos.",
            confirmText: "Entendido",
            hideCancel: true,
        });
        return;
    }

    const ids = selections.map((s) => s.id);

    const result = await showModal({
        type: "warning",
        title: "Desanclar Equipos",
        message:
            "¿Estás seguro de que deseas eliminar el vínculo de anclaje entre estos dos equipos?",
        confirmText: "Sí, Desanclar",
        cancelText: "Cancelar",
    });

    if (result) {
        try {
            const response = await fetch("/admin/equipos/clear-anchor", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({ ids: ids }),
            });

            if (response.status === 419 || response.status === 401) {
                window.location.reload();
                return;
            }

            const data = await response.json();

            if (data.success) {
                window.selectedEquipos = {};
                location.reload();
            } else {
                showModal({
                    type: "error",
                    title: "Error",
                    message: data.error || "Ocurrió un error al desanclar.",
                    confirmText: "Entendido",
                    hideCancel: true,
                });
            }
        } catch (error) {
            console.error(error);
            showModal({
                type: "error",
                title: "Error",
                message: "Ocurrió un error de red.",
                confirmText: "Entendido",
                hideCancel: true,
            });
        }
    }
};

document.addEventListener("click", function (e) {
    // Close status dropdowns when clicking outside
    if (!e.target.closest(".custom-dropdown")) {
        document.querySelectorAll(".status-dropdown-menu").forEach((menu) => {
            menu.style.display = "none";
        });
    }

    // 1. Identify specific clear actions
    // (Search, Filter etc are handled by global selectors or inline)

    // 2. Clear Advanced Filters Button
    const clearBtn = e.target.closest('[data-action="clear-advanced-filters"]');
    if (clearBtn) {
        e.preventDefault();
        e.stopPropagation();
        window.clearAdvancedFilters();
        return;
    }

    // 3. Clear Specific Filter (Generic)
    const clearSpecific = e.target.closest("[data-clear-target]");
    if (clearSpecific) {
        e.preventDefault();
        e.stopPropagation();
        const target = clearSpecific.dataset.clearTarget; // 'id_frente' or 'modelo' etc

        // All filters now use selectAdvancedFilter
        window.selectAdvancedFilter(target, "");
    }
});

window.enlargeImage = function (src) {
    const overlay = document.getElementById("imageOverlay");
    const img = document.getElementById("enlargedImg");
    if (!overlay || !img) return;
    img.src = src;
    overlay.style.display = "flex";
};

window.toggleDocFilter = function (type) {
    window.loadEquipos();
};

window.filterByStatus = function (status) {
    const dropdown = document.getElementById("estadoAdvFilter");
    if (!dropdown) return;

    if (status === "") {
        window.clearDropdownFilter("estadoAdvFilter");
    } else {
        // Obtenemos el input oculto para verificar si ya está seleccionado (toggle)
        const hiddenInput = dropdown.querySelector('input[name="estado"]');
        if (hiddenInput && hiddenInput.value === status) {
            window.clearDropdownFilter("estadoAdvFilter");
        } else {
            window.selectOption("estadoAdvFilter", status, status);
        }
    }

    // El sistema selectOption despacha un evento, pero llamamos manualmente para asegurar inmediatez
    window.loadEquipos();
};

window.loadEquipos = function (url = null, silent = false) {
    const tableBody = document.getElementById("equiposTableBody");
    if (!tableBody) return Promise.resolve();

    let baseUrl = url || window.location.pathname;
    const searchInput = document.getElementById("searchInput");
    const frenteInput = document.querySelector('input[name="id_frente"]');
    const tipoInput = document.querySelector('input[name="id_tipo"]');
    const advancedPanel = document.getElementById("advancedFilterPanel");

    // Helper robusto para obtener valores de inputs
    const getVal = (selector, parent = document) => {
        const el = parent.querySelector(selector);
        if (!el) return null;
        return el.value && el.value.trim() !== "" ? el.value.trim() : null;
    };

    // Unified Filter Object
    const filters = {
        search_query: getVal("#searchInput"),
        id_frente: getVal('input[name="id_frente"]'),
        id_tipo: getVal('input[name="id_tipo"]'),
        modelo: getVal('input[name="modelo"]', advancedPanel || document),
        marca: getVal('input[name="marca"]', advancedPanel || document),
        anio: getVal('input[name="anio"]', advancedPanel || document),
        categoria: getVal('input[name="categoria"]', advancedPanel || document),
        estado: getVal('input[name="estado"]', advancedPanel || document),
        filter_propiedad: document.getElementById("chk_propiedad")?.checked
            ? "true"
            : null,
        filter_poliza: document.getElementById("chk_poliza")?.checked
            ? "true"
            : null,
        filter_rotc: document.getElementById("chk_rotc")?.checked
            ? "true"
            : null,
        filter_racda: document.getElementById("chk_racda")?.checked
            ? "true"
            : null,
    };

    const params = new URLSearchParams();

    // Cleanly append only valid filter values (non-null, non-empty)
    Object.entries(filters).forEach(([key, value]) => {
        if (value && typeof value === "string" && value.trim() !== "") {
            params.append(key, value.trim());
        } else if (value && typeof value !== "string") {
            params.append(key, value);
        }
    });

    /* 
    // OPTIMIZATION: Check if there are any meaningful filters
    // Strategy: Only skip server request if EVERYTHING is null/empty (truly no input from user)
    const hasAnyInput = Object.entries(filters).some(([key, value]) => {
        if (value === null || value === '' || value === undefined) return false;
        if (typeof value === 'string' && value.trim() === '') return false;
        return true; 
    });

    if (!hasAnyInput) {
        console.log('No active filters detected - showing empty state');
        tableBody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 40px; color: #94a3b8; font-style: italic;">SELECCIONE UN FILTRO PARA VISUALIZAR LOS EQUIPOS</td></tr>';
        return Promise.resolve();
    }
    */

    // NOTE: reApplySelections() is NOT called here because the table
    // shows a "no filters" message with no real rows to highlight.

    const finalUrl =
        baseUrl + (baseUrl.includes("?") ? "&" : "?") + params.toString();
    tableBody.style.opacity = "0.5";

    if (!silent && window.showPreloader) window.showPreloader();

    return fetch(finalUrl, {
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
    })
        .then((response) => {
            if (response.status === 419 || response.status === 401) {
                window.location.reload();
                return;
            }
            if (!response.ok) throw new Error("Network response was not ok");
            return response.json();
        })
        .then((data) => {
            if (!data) return;

            tableBody.innerHTML = data.html;
            tableBody.style.opacity = "1";

            // Re-apply blue highlight to all previously selected rows
            // Uses dedicated function with String() cast to avoid int/string key mismatches
            reApplySelections();

            const paginationContainer =
                document.getElementById("equiposPagination");
            if (paginationContainer) paginationContainer.innerHTML = "";

            const statsTotal = document.getElementById("stats_total");
            const statsInactivos = document.getElementById("stats_inactivos");
            const statsMantenimiento = document.getElementById(
                "stats_mantenimiento",
            );
            if (statsTotal) statsTotal.textContent = data.stats.total;
            if (statsInactivos)
                statsInactivos.textContent = data.stats.inactivos;
            if (statsMantenimiento)
                statsMantenimiento.textContent = data.stats.mantenimiento;

            const distroContainer = document.getElementById(
                "distributionStatsContainer",
            );
            if (distroContainer) distroContainer.innerHTML = data.distribution;

            window.history.pushState(null, "", finalUrl);
        })
        .catch((error) => {
            console.error("Error loading equipos:", error);
            tableBody.style.opacity = "1";
        })
        .finally(() => {
            if (window.hidePreloader) window.hidePreloader();
        });
};

window.filterList = function (inputArg, listArg) {
    // Support both element references and ID strings (backward compatible)
    const input =
        typeof inputArg === "string"
            ? document.getElementById(inputArg)
            : inputArg;
    const list =
        typeof listArg === "string"
            ? document.getElementById(listArg)
            : listArg;
    if (!input || !list) return;

    const filter = input.value.toUpperCase();
    const items = list.querySelectorAll(".filter-option-item");

    items.forEach((item) => {
        const txt = item.textContent || item.innerText;
        item.style.display =
            txt.toUpperCase().indexOf(filter) > -1 ? "" : "none";
    });

    list.style.display = "block";
};

window.changeStatus = function (id, newStatus, url, element) {
    // PERMISSION CHECK
    if (
        typeof window.CAN_CHANGE_STATUS !== "undefined" &&
        window.CAN_CHANGE_STATUS === false
    ) {
        return; // Should be caught by toggleStatusDropdown, but double check
    }

    if (!element) return;
    const dropdown = element.closest(".custom-dropdown");
    if (!dropdown) return;

    const oldStatus = dropdown.getAttribute("data-current-status");
    if (oldStatus === newStatus) {
        window.toggleStatusDropdown(dropdown.querySelector(".status-trigger"));
        return;
    }

    const trigger = dropdown.querySelector(".status-trigger");
    const menu = dropdown.querySelector(".status-dropdown-menu");

    const statusConfig = {
        OPERATIVO: {
            color: "#16a34a",
            icon: "check_circle",
            label: "Operativo",
        },
        INOPERATIVO: { color: "#dc2626", icon: "cancel", label: "Inoperativo" },
        "EN MANTENIMIENTO": {
            color: "#d97706",
            icon: "engineering",
            label: "Mantenimiento",
        },
        DESINCORPORADO: {
            color: "#475569",
            icon: "archive",
            label: "Desincorp.",
        },
    };

    const config = statusConfig[newStatus] || statusConfig["DESINCORPORADO"];
    if (trigger) {
        trigger.innerHTML = `
            <div style="display: flex; align-items: center; gap: 6px; color: ${config.color};">
                <i class="material-icons" style="font-size: 16px;">${config.icon}</i>
                <span style="color: #334155;">${config.label}</span>
            </div>
            <i class="material-icons" style="font-size: 16px; color: #94a3b8;">expand_more</i>
        `;
    }
    if (menu) menu.style.display = "none";

    window.updateLocalStats(oldStatus, newStatus);
    dropdown.setAttribute("data-current-status", newStatus);

    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            "X-HTTP-Method-Override": "PATCH",
        },
        body: JSON.stringify({ status: newStatus }),
    })
        .then((response) => {
            if (response.status === 419 || response.status === 401) {
                window.location.reload();
            }
        })
        .catch((error) => {
            console.error("Update failed:", error);
            window.loadEquipos();
        });
};

window.openBulkModal = function (event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }

    // PERMISSION CHECK (Specific to Assignment/Mobilization)
    if (
        typeof window.CAN_ASSIGN_EQUIPOS !== "undefined" &&
        window.CAN_ASSIGN_EQUIPOS === false
    ) {
        if (window.showModal) {
            showModal({
                type: "error",
                title: "Acceso Denegado",
                message: "No tienes permisos para movilizar (asignar) equipos.",
                confirmText: "Entendido",
                hideCancel: true,
            });
        } else {
            alert("Acceso Denegado: No tienes permisos.");
        }
        return;
    }

    // 1. Validation
    if (
        !window.selectedEquipos ||
        Object.keys(window.selectedEquipos).length === 0
    ) {
        alert("Por favor seleccione equipos primero.");
        return;
    }

    /**
     * Muestra el modal para seleccionar el equipo que servirá como ancla (maestro)
     */

    // 2. Nuclear Cleanup: Remove any existing dynamic modals
    const oldModals = document.querySelectorAll(".dynamic-bulk-modal");
    oldModals.forEach((el) => el.remove());

    // 3. Create Overlay (Safe, Isolated Context)
    const overlay = document.createElement("div");
    overlay.className = "dynamic-bulk-modal";
    overlay.style.position = "fixed";
    overlay.style.top = "0";
    overlay.style.left = "0";
    overlay.style.width = "100vw";
    overlay.style.height = "100vh";
    overlay.style.backgroundColor = "rgba(0,0,0,0.5)";
    overlay.style.zIndex = "2500"; // Corrected Z-Index (Below Standard Modal 3000, Above Header 1000)
    overlay.style.display = "flex";
    overlay.style.justifyContent = "center";
    overlay.style.alignItems = "center";
    overlay.style.backdropFilter = "blur(2px)";

    // 4. Create Content Box
    const content = document.createElement("div");
    content.style.backgroundColor = "white";
    content.style.borderRadius = "16px";
    content.style.width = "90%";
    content.style.maxWidth = "500px";
    content.style.overflow = "hidden";
    content.style.boxShadow = "0 25px 50px -12px rgba(0,0,0,0.25)";
    content.style.animation = "slideDown 0.2s ease-out"; // Defined in CSS

    // 5. Header
    const header = document.createElement("div");
    header.style.background = "#1e293b";
    header.style.padding = "20px";
    header.style.color = "white";
    header.style.display = "flex";
    header.style.justifyContent = "center";
    header.style.alignItems = "center";
    header.style.position = "relative";
    header.innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="material-icons" style="color: #60a5fa;">local_shipping</i>
            <h2 style="margin: 0; font-size: 18px; font-weight: 700;">Movilización</h2>
        </div>
        <button type="button" id="btnCloseDynamic" style="position: absolute; right: 20px; background: transparent; border: none; color: white;">
            <i class="material-icons">close</i>
        </button>
    `;

    // 6. Body & Form Construction
    const body = document.createElement("div");
    body.style.padding = "25px";

    // Generate Equipments List
    let listHtml = "";
    Object.values(window.selectedEquipos).forEach((item) => {
        const code = typeof item === "object" ? item.code : item;
        listHtml += `<span style="background: #e2e8f0; padding: 2px 8px; border-radius: 4px; margin-right: 5px; display: inline-block; margin-bottom: 5px; font-size: 12px; color: #64748b;">${code}</span>`;
    });

    // Clone Datalist Options safely from main DOM (extract only <option> elements)
    let optionsHtml = "";
    const existingDatalist = document.querySelector("#frentesList");
    if (existingDatalist) {
        const options = existingDatalist.querySelectorAll("option");
        options.forEach((opt) => {
            const value = opt.getAttribute("value") || "";
            const dataId = opt.getAttribute("data-id") || "";
            optionsHtml += `<option value="${value}" data-id="${dataId}"></option>`;
        });
    }

    body.innerHTML = `
        <form id="dynamicBulkForm">

            <div style="margin-bottom: 25px;">
                <label for="dynamicDestInput" style="display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px;">Frente de Destino</label>
                <div style="position: relative;">
                    <i class="material-icons" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #94a3b8;">place</i>
                    <input type="text" id="dynamicDestInput" list="dynamicFrentesList"
                        placeholder="Escriba o busque destino..."
                        autocomplete="off"
                        style="width: 100%; padding: 12px 12px 12px 40px; border: 2px solid #e2e8f0; border-radius: 10px; outline: none; box-sizing: border-box; font-size: 14px;">
                     <datalist id="dynamicFrentesList">
                        ${optionsHtml}
                     </datalist>
                </div>
            </div>

            <button type="submit" style="width: 100%; height: 48px; border-radius: 10px; font-weight: 700; font-size: 15px; background: #0067b1; color: white; border: none; display: flex; align-items: center; justify-content: center; gap: 10px;">
                <i class="material-icons" style="font-size: 18px;">save</i> Confirmar
            </button>
        </form>
    `;

    // 7. Assemble
    content.appendChild(header);
    content.appendChild(body);
    overlay.appendChild(content);
    document.body.appendChild(overlay);

    // 8. Attach Event Listeners (Directly to new elements)

    // Close Button
    const closeBtn = overlay.querySelector("#btnCloseDynamic");
    closeBtn.onclick = function () {
        overlay.remove();
    };

    // Close on Overlay Click (Optional)
    overlay.onclick = function (e) {
        if (e.target === overlay) overlay.remove();
    };

    // Form Submit
    const form = body.querySelector("#dynamicBulkForm");
    form.onsubmit = function (e) {
        e.preventDefault();

        const destInput = body.querySelector("#dynamicDestInput");
        const dest = destInput ? destInput.value.trim() : "";

        if (!dest) {
            showModal({
                type: "warning",
                title: "Campo Requerido",
                message: "Por favor ingrese un frente de destino.",
                confirmText: "Entendido",
                hideCancel: true,
            });
            return;
        }

        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;

        // Show visual loading state in button
        btn.innerHTML =
            '<i class="material-icons" style="font-size: 18px; animation: spin 1s linear infinite;">sync</i> Procesando...';
        btn.disabled = true;
        btn.style.opacity = "0.7";
        btn.style.cursor = "wait";

        const ids = Object.keys(window.selectedEquipos);

        // Show global preloader (may be behind modal)
        if (window.showPreloader) window.showPreloader();

        fetch("/admin/equipos/bulk-mobilize", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
                Accept: "application/json",
            },
            body: JSON.stringify({ ids: ids, destination: dest }),
        })
            .then(function (res) {
                if (res.status === 419) {
                    if (window.hidePreloader) window.hidePreloader();
                    showModal({
                        type: "error",
                        title: "Sesión Expirada",
                        message:
                            "Su sesión ha expirado. La página se recargará.",
                        confirmText: "Recargar",
                        hideCancel: true,
                        onConfirm: () => window.location.reload(),
                    });
                    return;
                }
                if (!res.ok) throw new Error("Error en la respuesta");
                return res.json();
            })
            .then(function (data) {
                if (!data) return; // Session expired case

                // Hide preloader
                if (window.hidePreloader) window.hidePreloader();

                overlay.remove();
                window.clearSelection();

                // CRITICAL: Wait for table to fully reload before showing success
                return window.loadEquipos().then(() => data); // Pasar data al siguiente then
            })
            .then(function (data) {
                if (!data) return;

                // 1. Iniciar Descarga Automática (Si hay ID)
                const firstId =
                    data.movilizacion_ids && data.movilizacion_ids.length > 0
                        ? data.movilizacion_ids[0]
                        : null;

                if (firstId) {
                    const downloadLink = document.createElement("a");
                    downloadLink.href = `/admin/movilizaciones/${firstId}/acta-traslado`;
                    downloadLink.style.display = "none";
                    document.body.appendChild(downloadLink);

                    // Pequeño delay para asegurar que el DOM lo procese
                    setTimeout(() => {
                        downloadLink.click();
                        setTimeout(
                            () => document.body.removeChild(downloadLink),
                            1000,
                        );
                    }, 100);
                }

                // 2. Mostrar Modal de Éxito usando el sistema global de la aplicación
                if (window.showModal) {
                    showModal({
                        type: "info",
                        title: "¡Operación Exitosa!",
                        message: `Se generaron ${data.count} traslados exitosamente.<br><strong>Descargando Acta de Traslado...</strong>`,
                        confirmText: "Aceptar",
                        hideCancel: true,
                    });
                    // Auto-cerrar después de 3 segundos (Manipulación directa del DOM para garantizar cierre)
                    setTimeout(() => {
                        const modalEl =
                            document.getElementById("standardModal");
                        if (modalEl) modalEl.classList.remove("active");
                    }, 3000);
                }

                if (document.activeElement) document.activeElement.blur();
                document
                    .querySelectorAll(".custom-dropdown.active")
                    .forEach((el) => el.classList.remove("active"));
            })
            .catch(function (err) {
                console.error(err);

                // Hide preloader
                if (window.hidePreloader) window.hidePreloader();

                // Remove overlay to prevent UI blocking
                overlay.remove();

                // Restore button state (though overlay is gone, this variable reference persists)
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.style.opacity = "1";
                btn.style.cursor = "pointer";

                showModal({
                    type: "error",
                    title: "Error",
                    message:
                        "Hubo un error al procesar la movilización. Por favor intente nuevamente.",
                    confirmText: "Entendido",
                    hideCancel: true,
                });
            });
    };
};

window.openAnchorModal = async function (event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const selections = Object.entries(window.selectedEquipos);
    if (selections.length !== 1) return; // Only 1-to-1

    const sourceId = selections[0][0];
    const sourceData = selections[0][1];
    const firstFrenteId = sourceData.frenteId;
    const sourceRole = sourceData.rolAnclaje;

    if (!firstFrenteId || firstFrenteId === "null") {
        showModal({
            type: "warning",
            title: "Frente no Asignado",
            message: "Los equipos seleccionados no tienen un frente asignado.",
            confirmText: "Entendido",
            hideCancel: true,
        });
        return;
    }

    // Modal Construction
    const oldModals = document.querySelectorAll(".dynamic-anchor-modal");
    oldModals.forEach((el) => el.remove());

    const overlay = document.createElement("div");
    overlay.className = "dynamic-anchor-modal";
    overlay.style.cssText =
        "position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:2500; display:flex; justify-content:center; align-items:center; backdrop-filter:blur(2px);";

    const content = document.createElement("div");
    content.style.cssText =
        "background:white; border-radius:16px; width:90%; max-width:440px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);";

    content.innerHTML = `
        <div style="background:#1e293b; padding:18px; color:white; display:flex; justify-content:center; align-items:center; position:relative;">
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="material-icons" style="color:#10b981; font-size:20px;">anchor</i>
                <h2 style="margin:0; font-size:16px; font-weight:700;">Anclaje de Equipos</h2>
            </div>
            <button type="button" id="btnCloseAnchor" style="position:absolute; right:15px; background:transparent; border:none; color:white; cursor:pointer; opacity:0.7;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                <i class="material-icons">close</i>
            </button>
        </div>
        <div style="padding:20px;">
            <div id="anchorEquiposList" style="max-height:400px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:12px; margin-bottom:20px; background:#f8fafc; padding:8px;">
                <div style="padding:20px; text-align:center;"><i class="material-icons spin">sync</i> Cargando equipos...</div>
            </div>
            <button id="btnConfirmAnchor" disabled style="width:100%; height:46px; border-radius:12px; font-weight:700; font-size:14px; background:#10b981; color:white; border:none; display:flex; align-items:center; justify-content:center; gap:8px; opacity:0.5; cursor:not-allowed; transition:all 0.2s;">
                <i class="material-icons">check_circle</i> Confirmar Anclaje
            </button>
        </div>
    `;

    overlay.appendChild(content);
    document.body.appendChild(overlay);

    overlay.querySelector("#btnCloseAnchor").onclick = () => overlay.remove();

    // Fetch Equipos
    try {
        const response = await fetch(
            `/admin/equipos/get-equipos-by-frente?id_frente=${firstFrenteId}&source_role=${sourceRole}`,
            {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            },
        );
        const equipos = await response.json();
        const listContainer = content.querySelector("#anchorEquiposList");
        listContainer.innerHTML = "";

        const selectedIds = selections.map((s) => String(s[0]));

        if (equipos.length === 0) {
            listContainer.innerHTML =
                '<div style="padding:40px 20px; text-align:center; color:#94a3b8;"><i class="material-icons" style="font-size:32px; display:block; margin-bottom:10px;">assignment_late</i>No existe equipos de tipo remolcador</div>';
        } else {
            equipos.forEach((eq) => {
                const isSelected = selectedIds.includes(String(eq.ID_EQUIPO));
                const item = document.createElement("div");
                item.className = "anchor-option-item";
                item.style.cssText = `padding:10px; border-radius:8px; background:white; border:1px solid #e2e8f0; margin-bottom:6px; cursor:${isSelected ? "not-allowed" : "pointer"}; opacity:${isSelected ? "0.6" : "1"}; display:flex; align-items:center; gap:12px; transition:all 0.2s; position:relative;`;

                if (!isSelected) {
                    item.onmouseover = () => {
                        if (!item.dataset.selected)
                            item.style.borderColor = "#10b981";
                        item.style.boxShadow =
                            "0 4px 6px -1px rgba(0,0,0,0.05)";
                    };
                    item.onmouseout = () => {
                        if (!item.dataset.selected)
                            item.style.borderColor = "#e2e8f0";
                        item.style.boxShadow = "none";
                    };
                    item.onclick = () => {
                        content
                            .querySelectorAll(".anchor-option-item")
                            .forEach((el) => {
                                el.style.borderColor = "#e2e8f0";
                                el.style.background = "white";
                                el.dataset.selected = "";
                                el.querySelector(".check-mark").style.display =
                                    "none";
                            });
                        item.style.borderColor = "#10b981";
                        item.style.background = "#f0fdf4";
                        item.dataset.selected = "true";
                        item.querySelector(".check-mark").style.display =
                            "block";

                        window.selectedMasterId = eq.ID_EQUIPO;
                        const btn = content.querySelector("#btnConfirmAnchor");
                        btn.disabled = false;
                        btn.style.opacity = "1";
                        btn.style.cursor = "pointer";
                    };
                }

                // Photo Handling
                let fotoHtml = "";
                if (eq.FOTO) {
                    const driveId = eq.FOTO.replace(
                        "/storage/google/",
                        "",
                    ).split("?")[0];
                    fotoHtml = `<img src="/storage/google/${driveId}" style="width:100%; height:100%; object-fit:cover;">`;
                } else {
                    fotoHtml = `<i class="material-icons" style="font-size:20px; color:#cbd5e0;">image_not_supported</i>`;
                }

                item.innerHTML = `
                    <div style="width:40px; height:40px; background:#f1f5f9; border-radius:6px; overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        ${fotoHtml}
                    </div>
                    <div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:1px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                            <span style="font-weight:800; font-size:13px; color:#1e293b; text-transform:uppercase; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${eq.CODIGO_PATIO || "S/ID"}</span>
                        </div>
                        <div style="font-size:11px; color:#1e293b; font-weight:600;">${eq.MARCA} · ${eq.MODELO}</div>
                        <div style="display:flex; align-items:center; gap:8px; margin-top:1px;">
                             <span style="font-size:10px; color:#64748b; display:flex; align-items:center; gap:2px;"><i class="material-icons" style="font-size:10px;">fingerprint</i>${eq.SERIAL_CHASIS || "S/S"}</span>
                             ${eq.PLACA ? `<span style="font-size:10px; color:#0067b1; font-weight:700; display:flex; align-items:center; gap:2px;"><i class="material-icons" style="font-size:10px;">featured_play_list</i>${eq.PLACA}</span>` : ""}
                        </div>
                    </div>
                    <div class="check-mark" style="display:none; color:#10b981;">
                        <i class="material-icons" style="font-size:20px;">check_circle</i>
                    </div>
                    ${isSelected ? `<i class="material-icons" style="color:#cbd5e0; font-size:20px; margin-left:auto;">lock</i>` : ""}
                `;
                listContainer.appendChild(item);
            });
        }
    } catch (error) {
        console.error(error);
        overlay.remove();
    }

    content.querySelector("#btnConfirmAnchor").onclick = async function () {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="material-icons spin">sync</i> Procesando...';

        try {
            const response = await fetch("/admin/equipos/bulk-anchor", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                },
                body: JSON.stringify({
                    ids: selections.map((s) => s[0]),
                    master_id: window.selectedMasterId,
                }),
            });
            const data = await response.json();
            if (data.success) {
                overlay.remove();
                window.clearSelection();
                window.loadEquipos();
                showModal({
                    type: "success",
                    title: "¡Operación Exitosa!",
                    message: data.message,
                    confirmText: "Aceptar",
                    hideCancel: true,
                });
            } else {
                alert(data.error || "Error al anclar equipos");
            }
        } catch (error) {
            console.error(error);
        } finally {
            btn.disabled = false;
            btn.innerHTML =
                '<i class="material-icons">save</i> Confirmar Anclaje';
        }
    };
};

window.updateLocalStats = function (oldStatus, newStatus) {
    const elOper = document.getElementById("stats_activos");
    const elInop = document.getElementById("stats_inactivos");
    const elMant = document.getElementById("stats_mantenimiento");

    const adjust = (el, amount) => {
        if (el) {
            let val = parseInt(el.textContent.replace(/\D/g, "")) || 0;
            val += amount;
            el.textContent = val < 0 ? 0 : val;
        }
    };

    if (oldStatus === "OPERATIVO") adjust(elOper, -1);
    if (oldStatus === "INOPERATIVO" || oldStatus === "DESINCORPORADO")
        adjust(elInop, -1);
    if (oldStatus === "EN MANTENIMIENTO") adjust(elMant, -1);

    if (newStatus === "OPERATIVO") adjust(elOper, 1);
    if (newStatus === "INOPERATIVO" || newStatus === "DESINCORPORADO")
        adjust(elInop, 1);
    if (newStatus === "EN MANTENIMIENTO") adjust(elMant, 1);
};

window.exportEquipos = function () {
    const searchInput = document.getElementById("searchInput");
    const frenteInput = document.querySelector('input[name="id_frente"]');
    const tipoInput = document.querySelector('input[name="id_tipo"]');
    const advancedPanel = document.getElementById("advancedFilterPanel");

    // Prioritize inputs within the Advanced Filter Panel if it exists
    const modeloInput = advancedPanel
        ? advancedPanel.querySelector('input[name="modelo"]')
        : document.querySelector('input[name="modelo"]');
    const anioInput = advancedPanel
        ? advancedPanel.querySelector('input[name="anio"]')
        : document.querySelector('input[name="anio"]');
    const marcaInput = advancedPanel
        ? advancedPanel.querySelector('input[name="marca"]')
        : document.querySelector('input[name="marca"]');
    const categoriaInput = advancedPanel
        ? advancedPanel.querySelector('input[name="categoria"]')
        : null;
    const estadoInput = advancedPanel
        ? advancedPanel.querySelector('input[name="estado"]')
        : null;

    const params = new URLSearchParams();

    // Helper to append if valid
    const appendIfValid = (key, value) => {
        if (
            value &&
            typeof value === "string" &&
            value.trim() !== "" &&
            value.trim() !== "all"
        ) {
            params.append(key, value.trim());
            return true;
        }
        return false;
    };

    // Track if we have any filter
    let hasAnyFilter = false;

    hasAnyFilter |= appendIfValid("search_query", searchInput?.value);

    // id_frente: 'all' es un filtro explícito válido (Todos los Frentes)
    const frenteVal = frenteInput?.value?.trim();
    if (frenteVal === "all") {
        params.append("id_frente", "all");
        hasAnyFilter = true;
    } else {
        hasAnyFilter |= appendIfValid("id_frente", frenteVal);
    }

    hasAnyFilter |= appendIfValid("id_tipo", tipoInput?.value);
    hasAnyFilter |= appendIfValid("modelo", modeloInput?.value);
    hasAnyFilter |= appendIfValid("marca", marcaInput?.value);
    hasAnyFilter |= appendIfValid("anio", anioInput?.value);
    hasAnyFilter |= appendIfValid("categoria", categoriaInput?.value);
    hasAnyFilter |= appendIfValid("estado", estadoInput?.value);

    // Documentation Boolean Filters
    if (document.getElementById("chk_propiedad")?.checked) {
        params.append("filter_propiedad", "true");
        hasAnyFilter = true;
    }
    if (document.getElementById("chk_poliza")?.checked) {
        params.append("filter_poliza", "true");
        hasAnyFilter = true;
    }
    if (document.getElementById("chk_rotc")?.checked) {
        params.append("filter_rotc", "true");
        hasAnyFilter = true;
    }
    if (document.getElementById("chk_racda")?.checked) {
        params.append("filter_racda", "true");
        hasAnyFilter = true;
    }

    // Validate: At least one filter must be active
    if (!hasAnyFilter) {
        if (window.showModal) {
            showModal({
                type: "warning",
                title: "Filtro Requerido",
                message:
                    "Debe aplicar al menos un filtro antes de exportar datos. Esto previene la descarga masiva de toda la base de datos.",
                confirmText: "Entendido",
                hideCancel: true,
            });
        } else {
            alert("Debe aplicar al menos un filtro antes de exportar datos.");
        }
        return;
    }

    window.location.href = "/admin/equipos/export?" + params.toString();
};

function initEquipos() {
    if (!document.getElementById("equiposTableBody")) return;

    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
        searchInput.addEventListener("keyup", function () {
            const val = this.value;
            const clearBtn = document.getElementById("btn_clear_search");
            if (clearBtn)
                clearBtn.style.display = val.length > 0 ? "block" : "none";

            clearTimeout(window.searchTimeout);
            if (val.length >= 4 || val.length === 0) {
                window.searchTimeout = setTimeout(
                    () => window.loadEquipos(),
                    1000,
                );
            }
        });
    }

    const form = document.getElementById("search-form");
    if (form) {
        form.onsubmit = function (e) {
            e.preventDefault();
            window.loadEquipos();
            return false;
        };
    }

    updateSelectionUI();
}

// Listen for SPA navigation to reinitialize module and clear selections if leaving
window.addEventListener("spa:contentLoaded", function () {
    const isOnEquiposPage =
        document.getElementById("equiposTableBody") !== null;

    if (isOnEquiposPage) {
        // Reinitialize module when navigating TO equipos
        initEquipos();
    } else if (
        window.selectedEquipos &&
        Object.keys(window.selectedEquipos).length > 0
    ) {
        // Clear selections when navigating AWAY from equipos
        window.selectedEquipos = {};
        updateSelectionUI();
    }
});

// ==========================================
// ADVANCED FILTER LOGIC (Restored)
// ==========================================

window.clearAdvancedFilters = function () {
    // 1. Clear Advanced Panel Inputs
    const panel = document.getElementById("advancedFilterPanel");
    if (panel) {
        panel
            .querySelectorAll('input[type="text"], input[type="hidden"]')
            .forEach((el) => (el.value = ""));
        panel
            .querySelectorAll('input[type="checkbox"]')
            .forEach((el) => (el.checked = false));
    }

    // 2. Clear Global Inputs (if matched)
    ["modelo", "anio", "marca"].forEach((name) => {
        const el = document.querySelector(`input[name="${name}"]`);
        if (el) el.value = "";
    });

    // 3. Reset Dropdown Displays
    document.querySelectorAll(".custom-dropdown").forEach((dd) => {
        const display = dd.querySelector("input[readonly]");
        const label = dd.dataset.defaultLabel || "Seleccionar...";
        if (display) display.placeholder = label;

        // Hide clear btn
        const clearBtn = dd.querySelector("[data-clear-btn]");
        if (clearBtn) clearBtn.style.display = "none";

        // Remove 'selected' class from options
        dd.querySelectorAll(".dropdown-item.selected").forEach((opt) =>
            opt.classList.remove("selected"),
        );

        // Reset search inputs inside dropdowns
        const searchInDD = dd.querySelector("[data-filter-search]");
        if (searchInDD) {
            searchInDD.value = "";
            window.filterDropdownOptions(searchInDD); // Show all options
        }
    });

    // 4. Reset Button Styles
    const advBtn = document.getElementById("btnAdvancedFilter");
    if (advBtn) {
        advBtn.style.background = "white";
        advBtn.style.color = "#64748b";
        advBtn.style.border = "1px solid #cbd5e0";
    }

    // 5. Hide Clear Search button
    const clearSearch = document.getElementById("btn_clear_search");
    if (clearSearch) clearSearch.style.display = "none";
};

window.selectAdvancedFilter = function (type, value) {
    // Generic setter if needed (mostly handled by inputs directly)
    const input = document.querySelector(`input[name="${type}"]`);
    if (input) input.value = value;
};

window.clearDropdownFilter = function (dropdownId) {
    const dd = document.getElementById(dropdownId);
    if (!dd) return;

    const hiddenInput = dd.querySelector("[data-filter-value]");
    if (hiddenInput) hiddenInput.value = "";

    const searchInput = dd.querySelector("[data-filter-search]");
    if (searchInput) {
        searchInput.value = "";
        searchInput.placeholder = dd.dataset.defaultLabel || "Seleccionar...";
        window.filterDropdownOptions(searchInput);
    }

    const displayInput = dd.querySelector("input[readonly]");
    if (displayInput) {
        displayInput.placeholder = dd.dataset.defaultLabel || "Seleccionar...";
    }

    const clearBtn = dd.querySelector("[data-clear-btn]");
    if (clearBtn) clearBtn.style.display = "none";

    dd.querySelectorAll(".dropdown-item.selected").forEach((el) =>
        el.classList.remove("selected"),
    );

    dd.classList.remove("active");
};

window.filterDropdownOptions = function (input) {
    const filter = input.value.toUpperCase();
    const list = input
        .closest(".custom-dropdown")
        .querySelector(".dropdown-item-list");
    if (!list) return;

    const items = list.querySelectorAll(".dropdown-item");
    items.forEach((item) => {
        const txt = item.dataset.value || item.textContent;
        item.style.display =
            txt.toUpperCase().indexOf(filter) > -1 ? "" : "none";
    });
};

window.selectOption = function (dropdownId, value, displayLabel) {
    const dd = document.getElementById(dropdownId);
    if (!dd) return;

    // Set Hidden Value
    const hiddenInput = dd.querySelector("[data-filter-value]");
    if (hiddenInput) hiddenInput.value = value;

    // Update Display
    const searchInput = dd.querySelector("[data-filter-search]");
    if (searchInput) {
        searchInput.value = ""; // Clear search
        searchInput.placeholder = displayLabel;
    }

    const displayInput = dd.querySelector("input[readonly]");
    if (displayInput) {
        displayInput.placeholder = displayLabel;
    }

    // Show Clear Button
    const clearBtn = dd.querySelector("[data-clear-btn]");
    if (clearBtn) clearBtn.style.display = "block";

    // Mark Selected
    dd.querySelectorAll(".dropdown-item").forEach((el) =>
        el.classList.remove("selected"),
    );
    const selectedItem = dd.querySelector(
        `.dropdown-item[data-value="${value}"]`,
    );
    if (selectedItem) selectedItem.classList.add("selected");

    // Close Dropdown
    dd.classList.remove("active");

    // Highlight Advanced Button
    const advBtn = document.getElementById("btnAdvancedFilter");
    if (advBtn) {
        advBtn.style.background = "#e1effa";
        advBtn.style.color = "#0067b1";
        advBtn.style.border = "1px solid #0067b1";
    }
};

// ==========================================
// FLEET DASHBOARD LOGIC (Restored)
// ==========================================

window.currentDashboardFrente = null;

window.openFleetDashboard = function () {
    const modal = document.getElementById("fleetDashboardModal");
    if (modal) {
        modal.classList.add("active"); // Use CSS class for visibility
        // If your modal uses display:none/flex style directly:
        modal.style.display = "flex";

        // Initialize if not loaded
        window.loadFleetStats();
    }
};

window.closeFleetDashboard = function () {
    const modal = document.getElementById("fleetDashboardModal");
    if (modal) {
        modal.classList.remove("active");
        modal.style.display = "none";
    }
};

// Permission Handler for Create Action
window.handleCreateCheck = function (event) {
    // 1. Check Permission
    if (
        typeof window.CAN_CREATE_INFO !== "undefined" &&
        window.CAN_CREATE_INFO === false
    ) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (window.showModal) {
            showModal({
                type: "error",
                title: "Acceso Denegado",
                message: "No tienes permisos para crear nuevos equipos.",
                confirmText: "Entendido",
                hideCancel: true,
            });
        } else {
            alert("Acceso Denegado: No tienes permisos para crear.");
        }
        return false;
    }

    // 2. SPA Navigation Strategy
    // Instead of forcing location.href, we inject the URL into the link and let the event bubble.
    // The global 'navegacion.js' script will catch the click, see the valid href, and perform SPA load.
    if (event && window.CREATE_URL) {
        const link = event.currentTarget || event.target.closest("a");
        if (link) {
            link.href = window.CREATE_URL;
            // Do NOT preventDefault() here. Let it bubble to 'navegacion.js'.
            return true;
        }
    }

    // 3. Fallback (if called programmatically or something failed)
    if (window.CREATE_URL) {
        window.location.href = window.CREATE_URL;
    }
    return true;
};

// [End of dashboard cleanup]

// Register with Module Manager for SPA compatibility
ModuleManager.register(
    "equipos",
    () => document.getElementById("equiposTableBody") !== null,
    initEquipos,
);
