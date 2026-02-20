
    <!-- Session Timeout Modal -->
    <div id="sessionTimeoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
        <div style="background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
            <h3 style="margin: 0 0 10px; color: #1e293b; font-size: 20px; font-weight: 700;">Tu sesión está por expirar</h3>
            <p style="margin: 0 0 25px; color: #64748b; font-size: 15px; line-height: 1.5;">Por seguridad, tu sesión se cerrará automáticamente en <strong id="sessionCountdown" style="color: #dc2626;">120</strong> segundos debido a inactividad.</p>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button id="btnExtendSession" onclick="extendSession()" style="width: 100%; padding: 12px; background: var(--maquinaria-blue); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 15px; transition: background 0.2s; box-shadow: 0 4px 6px -1px rgba(0, 103, 177, 0.3); cursor: default;">
                    Mantener Sesión
                </button>
            </div>
        </div>
    </div>

    <script>
        /**
         * Session Timeout Manager (Corrected)
         * - Throttle de 30s: actividad real no reinicia el timer en cada micro-evento
         * - Solo escucha: click y keydown (elimina scroll/touchstart como ruido)
         * - Ping real al servidor cada 5 minutos para verificar sesión backend
         * - Contador del modal sincronizado con WARNING_DURATION_SECONDS
         */
        (function() {
            // ── Configuración ──────────────────────────────────────────
            const SESSION_LIFETIME_MS   = {{ config('session.lifetime') ?? 20 }} * 60 * 1000;
            const WARNING_DURATION_SEC  = 120;   // Avisar 2 minutos antes
            const ACTIVITY_THROTTLE_MS  = 30000; // Mínimo 30s entre reinicios por actividad
            const SERVER_PING_MS        = 5 * 60 * 1000; // Ping al servidor cada 5 minutos

            // ── Estado interno ──────────────────────────────────────────
            let sessionExpirationTime;
            let lastActivityReset = 0;
            let checkInterval;
            let serverPingInterval;
            let isModalVisible = false;

            // ── Inicialización ──────────────────────────────────────────
            function initSession() {
                updateExpirationTime();
                startCheckInterval();
                startServerPing();
                setupEventListeners();
                console.log(`✅ Session Monitor: Activo (Expira en ${SESSION_LIFETIME_MS / 60000}m, avisa con ${WARNING_DURATION_SEC}s de antelación)`);
            }

            // ── Timer Frontend ──────────────────────────────────────────
            function updateExpirationTime() {
                sessionExpirationTime = Date.now() + SESSION_LIFETIME_MS;
                lastActivityReset = Date.now();
            }

            function startCheckInterval() {
                if (checkInterval) clearInterval(checkInterval);
                // Verificar cada segundo para que la cuenta atrás sea precisa
                checkInterval = setInterval(checkSessionStatus, 1000);
            }

            // ── Verificación de estado ──────────────────────────────────
            function checkSessionStatus() {
                const msRemaining  = sessionExpirationTime - Date.now();
                const secRemaining = Math.ceil(msRemaining / 1000);

                if (secRemaining <= 0) {
                    performLogout();
                } else if (secRemaining <= WARNING_DURATION_SEC) {
                    showWarning(secRemaining);
                } else {
                    if (isModalVisible) hideWarning();
                }
            }

            // ── Ping al servidor (verifica sesión real backend) ─────────
            function startServerPing() {
                if (serverPingInterval) clearInterval(serverPingInterval);
                serverPingInterval = setInterval(pingServer, SERVER_PING_MS);
            }

            function pingServer() {
                // Solo hace ping si el modal NO está visible (si ya está visible,
                // el usuario debe actuar, no extender silenciosamente)
                if (isModalVisible) return;

                fetch('/refresh-csrf', { method: 'GET' })
                    .then(response => {
                        if (response.ok) {
                            // Sesión backend viva → actualizamos token CSRF
                            return response.text().then(token => {
                                if (token && token.length > 10) {
                                    const meta = document.querySelector('meta[name="csrf-token"]');
                                    if (meta) meta.setAttribute('content', token);
                                    if (window.axios) window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
                                }
                                console.log('🔄 Ping OK: sesión backend activa');
                            });
                        } else {
                            // Servidor rechazó → sesión ya muerta en backend
                            console.warn('⚠️ Ping fallido: sesión expirada en servidor');
                            performLogout();
                        }
                    })
                    .catch(() => {
                        // Sin conexión → no hacemos nada, el timer frontend decidirá
                        console.warn('⚠️ Ping sin respuesta (sin conexión)');
                    });
            }

            // ── Modal ───────────────────────────────────────────────────
            function showWarning(secRemaining) {
                const modal   = document.getElementById('sessionTimeoutModal');
                const counter = document.getElementById('sessionCountdown');

                if (modal && !isModalVisible) {
                    modal.style.display = 'flex';
                    modal.style.zIndex  = '99999';
                    isModalVisible = true;
                }
                if (counter) {
                    counter.innerText = Math.max(secRemaining, 0);
                }
            }

            function hideWarning() {
                const modal = document.getElementById('sessionTimeoutModal');
                if (modal) {
                    modal.style.display = 'none';
                    isModalVisible = false;
                }
                const btn = document.getElementById('btnExtendSession');
                if (btn) {
                    btn.disabled      = false;
                    btn.style.opacity = '1';
                    btn.innerHTML     = 'Mantener Sesión';
                }
            }

            // ── Extender sesión (botón del modal) ───────────────────────
            window.extendSession = function() {
                const btn = document.getElementById('btnExtendSession');
                if (btn) {
                    btn.disabled      = true;
                    btn.style.opacity = '0.7';
                    btn.innerHTML     = 'Renovando...';
                }

                if (typeof window.showPreloader === 'function') window.showPreloader();

                const controller = new AbortController();
                const timeoutId  = setTimeout(() => controller.abort(), 8000);

                fetch('/refresh-csrf', { method: 'GET', signal: controller.signal })
                    .then(async response => {
                        clearTimeout(timeoutId);
                        if (response.ok) {
                            const token = await response.text();
                            if (token && token.length > 10) {
                                const meta = document.querySelector('meta[name="csrf-token"]');
                                if (meta) meta.setAttribute('content', token);
                                if (window.axios) window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
                                if (window.jQuery) window.jQuery.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token } });
                            }
                            // Reiniciar AMBOS timers (frontend + ping)
                            updateExpirationTime();
                            startServerPing();
                            hideWarning();
                        } else {
                            throw new Error('Server ' + response.status);
                        }
                    })
                    .catch(error => {
                        console.error('Error al renovar sesión:', error);
                        alert('No se pudo renovar la sesión. Por favor recarga la página.');
                    })
                    .finally(() => {
                        if (typeof window.hidePreloader === 'function') window.hidePreloader();
                        if (btn) {
                            btn.disabled      = false;
                            btn.style.opacity = '1';
                            btn.innerHTML     = 'Mantener Sesión';
                        }
                    });
            };

            // ── Logout automático ───────────────────────────────────────
            function performLogout() {
                clearInterval(checkInterval);
                clearInterval(serverPingInterval);
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/logout';
                const csrf = document.querySelector('meta[name="csrf-token"]');
                if (csrf) {
                    const input = document.createElement('input');
                    input.type  = 'hidden';
                    input.name  = '_token';
                    input.value = csrf.getAttribute('content');
                    form.appendChild(input);
                }
                document.body.appendChild(form);
                form.submit();
            }

            // ── Actividad del usuario (con throttle) ────────────────────
            function handleActivity() {
                if (isModalVisible) return; // Modal visible → no reiniciar

                const now = Date.now();
                // CORRECCIÓN CLAVE: solo reiniciar si han pasado al menos 30 segundos
                // desde el último reinicio. Evita que micro-clicks/animaciones eternicen la sesión.
                if (now - lastActivityReset >= ACTIVITY_THROTTLE_MS) {
                    updateExpirationTime();
                }
            }

            function setupEventListeners() {
                // SOLO click y keydown - eliminamos scroll y touchstart (ruido)
                ['click', 'keydown'].forEach(evt => {
                    document.addEventListener(evt, handleActivity, { passive: true });
                });

                // Cuando el usuario vuelve a la pestaña, verificar inmediatamente
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) checkSessionStatus();
                });
            }

            // ── Arranque ────────────────────────────────────────────────
            initSession();
        })();
    </script>
