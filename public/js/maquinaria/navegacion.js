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
        history.pushState(null, '', url);
        await loadPage(url);
    }

    async function loadPage(url, animate = true) {
        try {
            // Start Loading State (Show Preloader & Bar)
            if (window.showPreloader) window.showPreloader();

            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });

            const html = await response.text();

            // Extract the section content
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('.main-viewport');
            const newTitle = doc.querySelector('title').innerText;

            if (!newContent) {
                window.location.href = url; // Fallback
                return;
            }

            document.title = newTitle;
            mainViewport.innerHTML = newContent.innerHTML;

            // Update active links in header
            updateActiveLinks(url);

            // Dispatch event for re-initialization
            window.dispatchEvent(new CustomEvent('spa:contentLoaded'));

            // Finish Loading State (Hide Preloader)
            setTimeout(() => {
                if (window.hidePreloader) window.hidePreloader();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }, 100);

            // Auto-close mobile menu if open
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenu && mobileMenu.classList.contains('active')) {
                mobileMenu.classList.remove('active');
            }
        } catch (error) {
            console.error('Error loading page:', error);
            window.location.href = url; // Fallback to normal load
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
