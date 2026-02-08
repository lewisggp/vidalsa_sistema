// menu.js - Dashboard Interaction Logic
// Handlers for "Movilizaciones Hoy" and "Alertas Documentos" cards

// Assign directly to window to ensure global availability across SPA navigation
// No IIFE wrapper to prevent any scoping issues

window.toggleExpiredDocs = function () {
    const expiredContainer = document.getElementById('expiredDocsContainer');
    const pendingContainer = document.getElementById('pendingMovsContainer');

    if (!expiredContainer) return;

    if (expiredContainer.style.display === 'none') {
        expiredContainer.style.display = 'flex';
        // Close the other list if it exists
        if (pendingContainer) pendingContainer.style.display = 'none';
    } else {
        expiredContainer.style.display = 'none';
    }
};

window.togglePendingMovs = function () {
    const pendingContainer = document.getElementById('pendingMovsContainer');
    const expiredContainer = document.getElementById('expiredDocsContainer');

    if (!pendingContainer) return;

    if (pendingContainer.style.display === 'none') {
        pendingContainer.style.display = 'flex';
        // Close the other list if it exists
        if (expiredContainer) expiredContainer.style.display = 'none';
    } else {
        pendingContainer.style.display = 'none';
    }
};

console.log('✅ Menu Dashboard Functions Loaded (Global Scope)');

// Function to refresh alerts list via AJAX without page reload
window.refreshDashboardAlerts = async function() {
    const listContainer = document.getElementById('dashboardAlertsList');
    if (!listContainer) return;

    try {
        const response = await fetch('/dashboard/alerts-html');
        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        
        // Update List HTML
        if (data.html) {
            listContainer.innerHTML = data.html;
            // Re-apply fade-in effect if desired
            listContainer.style.opacity = '0';
            setTimeout(() => {
                listContainer.style.transition = 'opacity 0.3s ease';
                listContainer.style.opacity = '1';
            }, 50);
        }

        // Update Total Badge (if exists)
        // Assuming there might be a badge with id 'totalAlertsBadge' or similar in the sidebar layout
        // access it safely
        const totalBadge = document.querySelector('.sidebar-badge-alerts'); // Generic selector example
        if (totalBadge && data.totalAlerts !== undefined) {
            totalBadge.innerText = data.totalAlerts;
            totalBadge.style.display = data.totalAlerts > 0 ? 'inline-block' : 'none';
        }
        
    } catch (error) {
        console.error('Failed to refresh dashboard alerts:', error);
    }
};

