/**
 * consumibles_graficos.js
 * Registro en ModuleManager para compatibilidad SPA.
 * Solo reinicializa TomSelect al navegar con SPA.
 * La carga inicial de datos la hace el script inline de la vista.
 */
if (typeof window.ModuleManager !== 'undefined') {
    window.ModuleManager.register('consumibles_graficos',
        () => document.getElementById('chartFrente') !== null,
        () => {
            // En SPA, la página ya llamó cargarDatos() al cargarse.
            // El ModuleManager se activa DESPUÉS, así que también lo llamamos
            // para garantizar que los gráficos se carguen si el usuario navegó
            // de vuelta mediante las flechas del navegador.
            if (typeof window.cargarDatos === 'function') {
                window.cargarDatos();
            }
        }
    );
}
