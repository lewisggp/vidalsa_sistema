
    <!-- Session Timeout Modal -->
    <div id="sessionTimeoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
        <div style="background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
            <div style="background: #fef2f2; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="material-icons" style="font-size: 32px; color: #dc2626;">timer</i>
            </div>
            <h3 style="margin: 0 0 10px; color: #1e293b; font-size: 20px; font-weight: 700;">Tu sesión está por expirar</h3>
            <p style="margin: 0 0 25px; color: #64748b; font-size: 15px; line-height: 1.5;">Por seguridad, tu sesión se cerrará automáticamente en <strong id="sessionCountdown" style="color: #dc2626;">60</strong> segundos debido a inactividad.</p>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button onclick="extendSession()" style="width: 100%; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; transition: background 0.2s; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">
                    Mantener Sesión Activa
                </button>
                <button onclick="logoutNow()" style="width: 100%; padding: 12px; background: transparent; color: #64748b; border: 1px solid #cbd5e0; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s;">
                    Cerrar Sesión
                </button>
            </div>
        </div>
    </div>

    <script>
        // Session Timeout Logic (Restored)
        (function() {
            const lifetime = {{ config('session.lifetime') ?? 10 }} * 60 * 1000; // Minutes to ms
            const warningBuffer = 60 * 1000; // 60 seconds warning
            // Ensure proper calculation even if lifetime is small
            const warningTime = (lifetime > warningBuffer) ? lifetime - warningBuffer : lifetime * 0.9;
            
            let warningTimer;
            let logoutTimer;
            let countdownInterval;
            let isWarningShown = false;

            function startTimers() {
                clearTimeout(warningTimer);
                clearTimeout(logoutTimer);
                clearInterval(countdownInterval);

                warningTimer = setTimeout(showWarning, warningTime);
                logoutTimer = setTimeout(performLogout, lifetime);
            }

            function resetTimers() {
                if (isWarningShown) return; // Don't reset if modal is open - user must explicitly choose
                startTimers();
            }

            function showWarning() {
                isWarningShown = true;
                const modal = document.getElementById('sessionTimeoutModal');
                if(modal) modal.style.display = 'flex';
                
                // Countdown visual
                let seconds = 60;
                const el = document.getElementById('sessionCountdown');
                if(el) el.innerText = seconds;
                
                // Beep or sound could go here
                
                countdownInterval = setInterval(() => {
                    seconds--;
                    if(el) el.innerText = seconds;
                    if(seconds <= 0) clearInterval(countdownInterval);
                }, 1000);
            }
            
            window.extendSession = function() {
                // Ping to keep server session alive
                fetch(window.location.href, { method: 'HEAD' });
                
                // Hide modal
                const modal = document.getElementById('sessionTimeoutModal');
                if(modal) modal.style.display = 'none';
                
                isWarningShown = false;
                startTimers();
            };

            window.logoutNow = function() {
               performLogout(); 
            };

            function performLogout() {
                // Post to standard Laravel logout route
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/logout';
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrf);
                
                document.body.appendChild(form);
                form.submit();
            }

            // Attach events to reset timer on activity
            ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(evt => {
                document.addEventListener(evt, resetTimers, true);
            });

            // Start on load
            startTimers();
        })();
    </script>
