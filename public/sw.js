// Service Worker básico para permitir instalación PWA
self.addEventListener('install', (e) => {
    console.log('[Service Worker] Instalado');
});

self.addEventListener('fetch', (e) => {
    // Solo responde con la red (sin caché offline por ahora)
    // Esto es necesario para que Chrome detecte que la App "puede" funcionar offline
});
