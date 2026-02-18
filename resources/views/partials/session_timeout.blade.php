
    <!-- Session Timeout Modal -->
    <div id="sessionTimeoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
        <div style="background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
            <h3 style="margin: 0 0 10px; color: #1e293b; font-size: 20px; font-weight: 700;">Tu sesión está por expirar</h3>
            <p style="margin: 0 0 25px; color: #64748b; font-size: 15px; line-height: 1.5;">Por seguridad, tu sesión se cerrará automáticamente en <strong id="sessionCountdown" style="color: #dc2626;">60</strong> segundos debido a inactividad.</p>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button id="btnExtendSession" onclick="extendSession()" style="width: 100%; padding: 12px; background: var(--maquinaria-blue); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 15px; transition: background 0.2s; box-shadow: 0 4px 6px -1px rgba(0, 103, 177, 0.3); cursor: default;">
                    Mantener Sesión
                </button>
            </div>
        </div>
    </div>

    <script>
        /**
         * Robust Session Timeout Manager (Fixed)
         * - Prevents freezing with 5s timeout
         * - Updates CSRF Token dynamically
         * - Handles background tab throttling
         */
        (function() {
            // Configuration
            const SESSION_LIFETIME_MINUTES = {{ config('session.lifetime') ?? 20 }};
            const WARNING_DURATION_SECONDS = 120; // Warn 2 minutes before expiration
            const PING_INTERVAL_MS = 60000;       // Check activity every minute
            
            // State
            let sessionExpirationTime;
            let checkInterval;
            let isModalVisible = false;

            function initSession() {
                updateExpirationTime();
                startCheckInterval();
                setupEventListeners();
                console.log(`✅ Session Monitor: Started (Expires in ${SESSION_LIFETIME_MINUTES}m)`);
            }

            function updateExpirationTime() {
                // Set expiration to NOW + Lifetime
                sessionExpirationTime = Date.now() + (SESSION_LIFETIME_MINUTES * 60 * 1000);
            }

            function checkSessionStatus() {
                const now = Date.now();
                const msRemaining = sessionExpirationTime - now;
                const secondsRemaining = Math.ceil(msRemaining / 1000);

                // Debug log (optional, remove in prod)
                // console.log('Session Check:', secondsRemaining, 's remaining');

                if (secondsRemaining <= 0) {
                    performLogout();
                } else if (secondsRemaining <= WARNING_DURATION_SECONDS) {
                    showWarning(secondsRemaining);
                } else {
                    if (isModalVisible) hideWarning();
                }
            }

            function startCheckInterval() {
                if (checkInterval) clearInterval(checkInterval);
                checkInterval = setInterval(checkSessionStatus, 1000); // Check every second
            }

            function showWarning(secondsRemaining) {
                const modal = document.getElementById('sessionTimeoutModal');
                const counter = document.getElementById('sessionCountdown');
                
                if (modal && !isModalVisible) {
                    modal.style.display = 'flex';
                    modal.style.zIndex = '99999'; // Ensure it's on top
                    isModalVisible = true;
                }
                
                if (counter) {
                    counter.innerText = secondsRemaining > 0 ? secondsRemaining : 0;
                }
            }

            function hideWarning() {
                const modal = document.getElementById('sessionTimeoutModal');
                if (modal) {
                    modal.style.display = 'none';
                    isModalVisible = false;
                }
                
                const btn = document.getElementById('btnExtendSession');
                if(btn) {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.innerHTML = 'Mantener Sesión';
                }
            }

            // --- CORE FIX: Extend Session with Timeout & Token Update ---
            window.extendSession = function() {
                const btn = document.getElementById('btnExtendSession');
                if(btn) {
                    btn.disabled = true;
                    btn.style.opacity = '0.7';
                    btn.innerHTML = 'Renovando...';
                }

                // 1. Force Preloader (if available)
                if (typeof window.showPreloader === 'function') window.showPreloader();

                // 2. Setup Timeout to prevent "Freezing"
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 8000); // 8 seconds max

                fetch('/refresh-csrf', { 
                    method: 'GET',
                    signal: controller.signal
                })
                .then(async response => {
                    clearTimeout(timeoutId);
                    
                    if (response.ok) {
                        // 3. CRITICAL: Get new token and update DOM
                        const newToken = await response.text(); // Assuming route returns token string
                        
                        if (newToken && newToken.length > 10) {
                            // Update Meta Tag
                            const metaToken = document.querySelector('meta[name="csrf-token"]');
                            if (metaToken) {
                                metaToken.setAttribute('content', newToken);
                                console.log('✅ CSRF Token Updated');
                            }
                            
                            // Update Global Headers (if Axios/jQuery exists)
                            // Note: This covers common libraries if used
                            if (window.axios) window.axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
                            if (window.jQuery) window.jQuery.ajaxSetup({ headers: { 'X-CSRF-TOKEN': newToken } });
                        }

                        // Reset Timers
                        updateExpirationTime();
                        hideWarning();
                        
                        // Notify User
                        if (typeof window.showModal === 'function') {
                            // Optional: Small toast, but maybe too intrusive? 
                            // Better to just seamlessly continue.
                        }
                    } else {
                        throw new Error('Server returned ' + response.status);
                    }
                })
                .catch(error => {
                    console.error('Session extension failed:', error);
                    // Don't reload immediately, give user a chance to save manually if possible?
                    // Or just warn them.
                    alert('No se pudo renovar la sesión automáticamente. Por favor recarga la página para evitar errores.');
                    
                    // Fallback: Reload logic if we want to force it
                    // window.location.reload(); 
                })
                .finally(() => {
                    // 4. ALWAYS Hide Preloader
                    if (typeof window.hidePreloader === 'function') window.hidePreloader();
                    
                    // Restore button if we didn't reload
                    if(btn) {
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        btn.innerHTML = 'Mantener Sesión';
                    }
                });
            };

            function performLogout() {
                clearInterval(checkInterval);
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/logout';
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if(csrfToken) {
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = csrfToken.getAttribute('content');
                    form.appendChild(csrf);
                }
                document.body.appendChild(form);
                form.submit();
            }

            function handleActivity() {
                if (isModalVisible) return;
                // Simple sliding expiration: activity pushes the deadline
                // Only update internal timer, DON'T ping server every click (too heavy)
                // The /refresh-csrf will happen only when the warning appears and user confirms.
                updateExpirationTime(); 
            }

            function setupEventListeners() {
                const events = ['click', 'keydown', 'scroll', 'touchstart'];
                events.forEach(evt => {
                    document.addEventListener(evt, handleActivity, { passive: true });
                });
                
                // Wake up check
                document.addEventListener('visibilitychange', () => {
                   if (!document.hidden) checkSessionStatus();
                });
            }

            // Start everything
            initSession();
        })();
    </script>
