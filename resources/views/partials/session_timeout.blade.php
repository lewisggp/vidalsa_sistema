
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
         * Robust Session Timeout Manager
         * Uses absolute timestamps to prevent drift when tab is backgrounded/sleeping.
         */
        (function() {
            // Configuration
            const SESSION_LIFETIME_MINUTES = {{ config('session.lifetime') ?? 120 }};
            const WARNING_DURATION_SECONDS = 60;
            const PING_INTERVAL_MS = 300000; // Ping server every 5 minutes of activity
            
            // State
            let sessionExpirationTime;
            let lastServerPingTime = Date.now();
            let checkInterval;
            let isModalVisible = false;

            function initSession() {
                updateExpirationTime();
                startCheckInterval();
                setupEventListeners();
            }

            function updateExpirationTime() {
                // Set expiration to NOW + Lifetime
                sessionExpirationTime = Date.now() + (SESSION_LIFETIME_MINUTES * 60 * 1000);
            }

            function checkSessionStatus() {
                const now = Date.now();
                const msRemaining = sessionExpirationTime - now;

                if (msRemaining <= 0) {
                    // Time is up!
                    performLogout();
                } else if (msRemaining <= (WARNING_DURATION_SECONDS * 1000)) {
                    // Enter Warning Zone
                    showWarning(Math.ceil(msRemaining / 1000));
                } else {
                    // Safe Zone
                    if (isModalVisible) hideWarning();
                }
            }

            function startCheckInterval() {
                if (checkInterval) clearInterval(checkInterval);
                // Check every second
                checkInterval = setInterval(checkSessionStatus, 1000);
            }

            function showWarning(secondsRemaining) {
                const modal = document.getElementById('sessionTimeoutModal');
                const counter = document.getElementById('sessionCountdown');
                
                if (modal) {
                    modal.style.display = 'flex';
                    // Force high z-index
                    modal.style.zIndex = '99999'; 
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
                
                // Reset button state just in case it was left pending
                const btn = document.getElementById('btnExtendSession');
                if(btn) {
                    btn.disabled = false;
                    btn.innerText = 'Mantener Sesión';
                    btn.style.opacity = '1';
                }
            }

            // Expose function to global scope for the button click
            window.extendSession = function() {
                const btn = document.getElementById('btnExtendSession');
                
                // UX: Prevent double click
                if(btn) {
                    btn.disabled = true;
                    btn.style.opacity = '0.7';
                }

                // Show system preloader
                if (typeof window.showPreloader === 'function') {
                    window.showPreloader();
                }

                fetch('/refresh-csrf', { method: 'GET' })
                    .then(response => {
                        if (response.ok) {
                            // Success! Reset everything.
                            updateExpirationTime();
                            lastServerPingTime = Date.now();
                            hideWarning();
                        } else {
                            // If ping fails (e.g. 401/419), we are likely already logged out
                            window.location.reload(); 
                        }
                    })
                    .catch(() => {
                        // Network error or offline
                         window.location.reload(); 
                    })
                    .finally(() => {
                         // Hide system preloader
                         if (typeof window.hidePreloader === 'function') {
                             window.hidePreloader();
                         }
                    });
            };

            function performLogout() {
                // Stop checking to prevent loops
                clearInterval(checkInterval);
                
                // Create and submit logout form
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
                // If the modal is visible, ONLY the button should extend the session.
                // Ignore passive mouse movements.
                if (isModalVisible) return;

                // Update local expiration time so popup doesn't appear while working
                updateExpirationTime();

                // Throttle: Only ping server if 5 minutes (PING_INTERVAL_MS) have passed since last ping
                const now = Date.now();
                if (now - lastServerPingTime > PING_INTERVAL_MS) {
                    lastServerPingTime = now;
                    
                    // Lightweight background ping to keep session alive on server
                    fetch('/refresh-csrf', { method: 'GET' }).catch(() => {
                        // Ignore errors in background ping (e.g. if offline)
                    });
                }
            }

            function setupEventListeners() {
                // Activity Listeners
                const events = ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'];
                events.forEach(evt => {
                    document.addEventListener(evt, handleActivity, { passive: true });
                });

                // Tab Visibility Listener (The Key Fix for Sleeping Tabs)
                document.addEventListener('visibilitychange', () => {
                   if (!document.hidden) {
                       // Immediately check status when tab wakes up
                       checkSessionStatus();
                       
                       // Optional: If we woke up and it's been a long time, maybe force a ping?
                       // But checkSessionStatus will handle logout if expired.
                   }
                });
            }

            // Start
            initSession();
        })();
    </script>
