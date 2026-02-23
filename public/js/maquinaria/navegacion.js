/* spa-nav.js - Handle dynamic content loading with progress bar */
document.addEventListener('DOMContentLoaded', () => {
    const mainViewport = document.querySelector('.main-viewport');

    // Intercept clicks on links
    document.addEventListener('click', async (e) => {
        const link = e.target.closest('a');

        if (!link || !link.href) return;

        // Skip if link has target="_blank", or it's not a primary click (left click), or has alt/ctrl/meta keys
        if (link.target === '_blank' || e.button !== 0 || e.altKey || e.ctrlKey || e.metaKey || e.shiftKey) {
            return;
        }

        // Only internal links, ignore logout or external
        const url = new URL(link.href);

        // Skip blob, data, and javascript URLs
        if (url.protocol === 'blob:' || url.protocol === 'data:' || url.protocol === 'javascript:') {
            return;
        }

        if (url.origin !== window.location.origin || link.hasAttribute('data-no-spa') || link.href.includes('logout')) {
            return;
        }

        e.preventDefault();
        navigateTo(link.href);
    });

    // Handle back/forward buttons
    window.addEventListener('popstate', () => {
        loadPage(window.location.href, false);
    });

    async function navigateTo(url) {
        await loadPage(url, true);
    }

    async function loadPage(url, pushHistory = true) {
        try {
            if (window.showPreloader) window.showPreloader();

            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });

            // Respuesta HTTP con error → navegación normal
            if (!response.ok) {
                window.location.href = url;
                return;
            }

            // Si la respuesta no es HTML (PDF, JSON, archivo) → navegación normal
            const contentType = response.headers.get('Content-Type') || '';
            if (!contentType.includes('text/html')) {
                window.location.href = url;
                return;
            }

            const html = await response.text();

            // Extraer contenido del viewport
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('.main-viewport');

            if (!newContent) {
                window.location.href = url;
                return;
            }

            // Solo modificar historial después de confirmar que es contenido válido
            if (pushHistory) {
                history.pushState(null, '', url);
            }

            const titleEl = doc.querySelector('title');
            document.title = titleEl ? titleEl.innerText : document.title;
            mainViewport.innerHTML = newContent.innerHTML;

            updateActiveLinks(url);
            window.dispatchEvent(new CustomEvent('spa:contentLoaded'));

            setTimeout(() => {
                if (window.hidePreloader) window.hidePreloader();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }, 100);

            // Cerrar menú mobile si está abierto
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenu && mobileMenu.classList.contains('active')) {
                mobileMenu.classList.remove('active');
            }
        } catch (error) {
            console.error('Error loading page:', error);
            window.location.href = url;
        } finally {
            // Garantizar que el preloader se oculte en todos los casos
            if (window.hidePreloader) window.hidePreloader();
        }
    }

    function updateActiveLinks(url) {
        document.querySelectorAll('.nav-link, .mobile-nav-link').forEach(link => {
            if (link.href === url) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }
});
