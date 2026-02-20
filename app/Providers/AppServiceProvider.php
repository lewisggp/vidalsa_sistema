<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use App\Models\Equipo;
use App\Models\Movilizacion;
use App\Models\Documentacion;
use App\Observers\EquipoObserver;
use App\Observers\MovilizacionObserver;
use App\Observers\DocumentacionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Schema::defaultStringLength(191);
        
        // GLOBAL PERMISSION GATE - The definitive source of truth
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            // 1. Super Admin Bypass (ID 1 OR 'SUPER ADMIN' Role Name)
            // Use strict robust check to avoid PHP 8.1+ deprecation warnings on null
            $roleName = optional($user->rol)->NOMBRE_ROL;
            if ($user->ID_ROL == 1 || ($roleName && strtoupper($roleName) === 'SUPER ADMIN')) {
                return true;
            }

            // 2. Specific Permission Check (PERMISOS column array)
            // Ensure PERMISOS is treated as array (via accessor or manual explode)
            $permisosRaw = $user->PERMISOS;
            
            if (is_string($permisosRaw)) {
                $permisos = explode(',', $permisosRaw);
            } elseif (is_array($permisosRaw)) {
                $permisos = $permisosRaw;
            } else {
                $permisos = [];
            }
            
            // Normalize for case-insensitivity safely (filter out non-strings)
            $permisos = array_map('strtolower', array_filter($permisos, 'is_string'));
            
            if (in_array(strtolower($ability), $permisos)) {
                return true;
            }
        });

        Equipo::observe(EquipoObserver::class);
        Movilizacion::observe(MovilizacionObserver::class);
        Documentacion::observe(DocumentacionObserver::class);
    }
}

