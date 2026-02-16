// Fleet Dashboard Modal Manager - Filtered by Frente
// Uses Chart.js for visualizations + DataLabels Plugin

let fleetCharts = {}; // Store chart instances globally
let currentFrenteId = ''; // Track selected frente

// Color palettes
const CHART_COLORS = {
    status: {
        'OPERATIVO': '#1b0d95ff',      // Operativo
        'MANTENIMIENTO': '#7b7b7bff',    // Mantenimiento
        'INOPERATIVO': '#a31616ff',    // Inoperativo
        'DESINCORPORADO': '#07090aff'    // Desincorporado
    },
    age: ['#1b0d95ff', '#a31616ff'], // New (Brand Blue), Old (Dark Red)
    category: ['#a31616ff', '#1b0d95ff', '#94a3b8'] // Pesada, Liviana, Sin Asignar (Gris)
};

/**
 * Update stat cards with data
 */
function updateStatCards(stats) {
    document.getElementById('stat_total').textContent = stats.total || 0;
    document.getElementById('stat_fleet_new').textContent = stats.fleet_new || 0;
    document.getElementById('stat_fleet_old').textContent = stats.fleet_old || 0;

    // Update Consumption (if element exists)
    const consumptionEl = document.getElementById('stat_consumption');
    if (consumptionEl) {
        consumptionEl.textContent = stats.total_consumption || 0;
    }
}

/**
 * Open Fleet Dashboard Modal
 */
window.openFleetDashboard = async function () {
    const modal = document.getElementById('fleetDashboardModal');
    if (!modal) return;

    modal.classList.add('active');

    // Load Chart.js and Datalabels Plugin if not already loaded
    if (typeof Chart === 'undefined') {
        await loadChartJS();
    }

    // Setup Dropdown Events
    setupDropdownEvents();

    // Get first frente from hidden inputs
    const firstFrenteId = document.getElementById('selectedFrenteId').value;
    const firstFrenteName = document.getElementById('selectedFrenteNombre').value;

    // Set initial value in search input
    document.getElementById('frenteSearchInput').value = firstFrenteName;
    currentFrenteId = firstFrenteId;

    // Fetch and render data for first frente
    await loadFleetDashboardData(firstFrenteId);
};

/**
 * Export Fleet Statistics to Excel (CSV)
 */
window.exportFleetStats = function () {
    const frenteId = document.getElementById('selectedFrenteId').value;
    const url = new URL('/admin/equipos/fleet-export', window.location.origin);
    if (frenteId && frenteId !== 'all') {
        url.searchParams.set('frente_id', frenteId);
    }

    // Trigger download
    window.location.href = url.toString();
};


/**
 * Setup Dropdown Events (Close when clicking outside)
 * Initializes only once by checking a global flag.
 */
let dropdownEventsInitialized = false;

function setupDropdownEvents() {
    if (dropdownEventsInitialized) return;

    const input = document.getElementById('frenteSearchInput');
    const dropdown = document.getElementById('frenteDropdownList');
    const container = input ? input.parentElement : null;

    if (!input || !dropdown || !container) return;

    // Open dropdown on input click - REMOVED (Handled by onclick HTML)

    // Open dropdown on input focus/keyup - REMOVED (Optional, but removed to simplify)

    // Close dropdown when clicking outside
    document.addEventListener('click', function (event) {
        if (!container.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });

    // Handle option selection click (delegation)
    dropdown.addEventListener('click', function (e) {
        if (e.target.classList.contains('frente-option')) {
            // Already handled by onclick="selectFrente..." in HTML
            // Just close the dropdown here
            dropdown.style.display = 'none';
        }
    });

    // Hover effects via CSS class ideally, but keeping JS for now if needed or remove
    // (Removed manual hover listeners as CSS :hover is better and already present in blade style)

    dropdownEventsInitialized = true;
}

/**
 * Toggle Dropdown Visibility (Deprecated/Helper)
 * Kept if called from HTML, but logic moved to listeners above.
 * Can be used by the arrow icon if needed.
 */
window.toggleFrenteDropdown = function () {
    const dropdown = document.getElementById('frenteDropdownList');
    if (dropdown) {
        // Simple toggle
        if (dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        } else {
            dropdown.style.display = 'block';
            document.getElementById('frenteSearchInput').focus();
        }
    }
};

/**
 * Filter Frentes List
 */
window.filterFrentes = function () {
    const input = document.getElementById('frenteSearchInput');
    const filter = input.value.toUpperCase();
    const dropdown = document.getElementById('frenteDropdownList');
    const options = dropdown.getElementsByClassName('frente-option');
    let hasResults = false;

    for (let i = 0; i < options.length; i++) {
        const txtValue = options[i].textContent || options[i].innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            options[i].style.display = "";
            hasResults = true;
        } else {
            options[i].style.display = "none";
        }
    }

    dropdown.style.display = 'block';
};

/**
 * Select a Frente
 */
window.selectFrente = async function (id, name, loadData = true) {
    document.getElementById('selectedFrenteId').value = id;
    document.getElementById('frenteSearchInput').value = name;
    document.getElementById('frenteDropdownList').style.display = 'none';

    currentFrenteId = id;

    if (loadData) {
        await loadFleetDashboardData(id);
    }
};

/**
 * Close Fleet Dashboard Modal
 */
window.closeFleetDashboard = function () {
    const modal = document.getElementById('fleetDashboardModal');
    if (modal) {
        modal.classList.remove('active');
    }

    // Destroy charts to free memory
    destroyAllCharts();
};

/**
 * Load Chart.js library dynamically
 */
async function loadChartJS() {
    return new Promise((resolve, reject) => {
        if (typeof Chart !== 'undefined') {
            resolve();
            return;
        }

        // Load Chart.js
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';

        script.onload = () => {
            // Load DataLabels Plugin after Chart.js is loaded
            const pluginScript = document.createElement('script');
            pluginScript.src = 'https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js';
            pluginScript.onload = () => {
                // Register plugin globally
                Chart.register(ChartDataLabels);
                resolve();
            };
            pluginScript.onerror = () => {
                console.warn('Failed to load DataLabels plugin, charts will work without it.');
                resolve(); // Resolve anyway to at least show charts
            };
            document.head.appendChild(pluginScript);
        };

        script.onerror = () => reject(new Error('Failed to load Chart.js'));
        document.head.appendChild(script);
    });
}

/**
 * Fetch fleet statistics from backend with frente filter
 */
async function loadFleetDashboardData(frenteId) {
    const spinner = document.getElementById('fleetDashboardSpinner');

    try {
        // Show spinner
        if (spinner) spinner.style.display = 'flex';

        const url = new URL('/admin/equipos/fleet-stats', window.location.origin);
        if (frenteId && frenteId !== 'all') {
            url.searchParams.set('frente_id', frenteId);
        }

        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (!response.ok) throw new Error('Failed to fetch fleet data');

        const data = await response.json();

        // Update stat cards
        updateStatCards(data.stats);

        // Create/update charts
        createCharts(data);

        // Hide spinner after everything is rendered
        setTimeout(() => {
            if (spinner) spinner.style.display = 'none';
        }, 300);

    } catch (error) {
        console.error('Fleet Dashboard Error:', error);

        // Hide spinner on error
        if (spinner) spinner.style.display = 'none';

        if (window.showModal) {
            showModal({
                type: 'error',
                title: 'Error',
                message: 'No se pudieron cargar las estadísticas de la flota.',
                confirmText: 'Cerrar',
                hideCancel: true
            });
        }
    }
}



/**
 * Create all charts with data from selected frente
 */
function createCharts(data) {
    // Destroy existing charts first
    destroyAllCharts();

    // Common options for clean look
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: { size: 14, weight: '600' }, // Increased font size
                    boxWidth: 14,
                    boxHeight: 14
                }
            },
            datalabels: {
                color: 'white',
                font: { weight: 'bold', size: 12 },
                formatter: (value) => value > 0 ? value : '' // Only show if > 0
            }
        }
    };

    // 1. Estado Operativo - Doughnut Chart
    fleetCharts.byStatus = new Chart(document.getElementById('chartStatusByFront'), {
        type: 'doughnut',
        data: {
            labels: data.byStatus.labels,
            datasets: [{
                data: data.byStatus.values,
                backgroundColor: data.byStatus.labels.map(label => CHART_COLORS.status[label] || '#64748b'),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: commonOptions
    });

    // 3. Flota Nueva vs Vieja por Tipo - Stacked Bar (Horizontal)
    fleetCharts.ageByType = createCleanStackedBarChart('chartAgeByType', {
        labels: data.ageByType.labels,
        datasets: data.ageByType.datasets.map((ds, idx) => ({
            label: ds.label,
            data: ds.data,
            backgroundColor: CHART_COLORS.age[idx],
            borderWidth: 0,
            borderRadius: 4
        }))
    });

    // 4. Flota Pesada vs Liviana por Tipo - Stacked Bar (Horizontal)
    fleetCharts.categoryByType = createCleanStackedBarChart('chartCategoryByType', {
        labels: data.categoryByType.labels,
        datasets: data.categoryByType.datasets.map((ds, idx) => ({
            label: ds.label,
            data: ds.data,
            backgroundColor: CHART_COLORS.category[idx],
            borderWidth: 0,
            borderRadius: 4
        }))
    });
}

/**
 * Create Clean Stacked Bar Chart (No X Axis, Data inside bars)
 */
function createCleanStackedBarChart(canvasId, config) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: config.labels,
            datasets: config.datasets
        },
        options: {
            indexAxis: 'y', // Horizontal
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: { size: 14, weight: '600' }, // Increased font size
                        boxWidth: 14,
                        boxHeight: 14
                    }
                },
                tooltip: {
                    callbacks: {
                        footer: function (tooltipItems) {
                            let total = 0;
                            const dataIndex = tooltipItems[0].dataIndex;
                            const datasets = tooltipItems[0].chart.data.datasets;

                            datasets.forEach(dataset => {
                                total += dataset.data[dataIndex] || 0;
                            });

                            return 'Total: ' + total;
                        }
                    }
                },
                datalabels: {
                    color: 'white',
                    font: { weight: 'bold', size: 12 },
                    display: function (context) {
                        return context.dataset.data[context.dataIndex] > 0; // Hide 0 values
                    },
                    formatter: Math.round
                }
            },
            scales: {
                x: {
                    stacked: true,
                    display: false, // HIDE X AXIS
                    grid: { display: false }
                },
                y: {
                    stacked: true,
                    grid: { display: false },
                    ticks: {
                        font: { size: 12, weight: '600' }, // Increased font size for axis labels
                        color: '#475569'
                    }
                }
            }
        }
    });
}

/**
 * Destroy all chart instances
 */
function destroyAllCharts() {
    for (const key in fleetCharts) {
        if (fleetCharts[key] && typeof fleetCharts[key].destroy === 'function') {
            fleetCharts[key].destroy();
        }
    }
    fleetCharts = {};
}
