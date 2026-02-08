/**
 * module_manager.js - Central SPA Module Coordination
 * Version: 1.0 - Professional Architecture
 * 
 * Ensures all modules initialize properly on both:
 * - Initial page load
 * - SPA navigation (spa:contentLoaded event)
 */

if (typeof window.ModuleManager === 'undefined') {
    window.ModuleManager = {
        modules: {},

        /**
         * Register a module with its detector and initializer
         * @param {string} name - Module name for logging
         * @param {function} detector - Function that returns true if module should initialize
         * @param {function} initializer - Function to call to initialize the module
         */
        register(name, detector, initializer) {
            this.modules[name] = { detector, initializer };
        },

        /**
         * Initialize all active modules based on their detectors
         */
        initializeActiveModules() {
            Object.entries(this.modules).forEach(([name, module]) => {
                try {
                    if (module.detector()) {
                        // console.log(`[ModuleManager] Initializing: ${name}`);
                        module.initializer();
                    }
                } catch (error) {
                    console.error(`[ModuleManager] Error initializing ${name}:`, error);
                }
            });
        },

        /**
         * Initialize the Module Manager
         */
        init() {
            // console.log('[ModuleManager] Starting...');

            // Initialize modules on first load
            this.initializeActiveModules();

            // Reinitialize modules when SPA loads new content
            window.addEventListener('spa:contentLoaded', () => {
                // console.log('[ModuleManager] SPA content loaded, reinitializing modules...');
                this.initializeActiveModules();
            });
        }
    };

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => window.ModuleManager.init());
    } else {
        window.ModuleManager.init();
    }
}
