<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\ValidarSesionUnica::class,
        ]);

        // Configuración para Easypanel/Docker (Reverse Proxy)
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);

        $middleware->alias([
            'password.change.check' => \App\Http\Middleware\EnsurePasswordChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Sesión expirada (token CSRF) → redirigir al login
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return redirect()->route('login')->with('info', 'La sesión ha caducado por seguridad. Por favor, inicie sesión nuevamente.');
        });

        // Usuario no autenticado en rutas WEB → redirigir al login (nunca mostrar JSON)
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            // Si es una petición de API (/api/...) → responder JSON normalmente
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['error' => 'No autenticado.'], 401);
            }
            // Para cualquier otra petición (web, navegador celular) → login
            return redirect()->route('login')->with('info', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        });

    })->create();
