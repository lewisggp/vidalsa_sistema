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
        
        Equipo::observe(EquipoObserver::class);
        Movilizacion::observe(MovilizacionObserver::class);
        Documentacion::observe(DocumentacionObserver::class);
    }
}

