<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidarSesionUnica
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Excluir la ruta de logout de la validación para evitar conflictos
        if ($request->is('logout')) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            $sessionToken = $request->session()->get('current_session_token');

            // Si hay un token en sesión y no coincide con el de la DB
            // (Si no hay token en sesión, es probable que la sesión haya expirado naturalmente, 
            // dejar que el framework maneje la expiración normal en lugar de forzar logout)
            if ($sessionToken && $user->SESSION_TOKEN !== $sessionToken) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Sesión iniciada en otro dispositivo.'], 401);
                }
                
                return redirect('/')->withErrors([
                    'login_error' => 'Tu sesión se ha iniciado en otro dispositivo.',
                ]);
            }
        }

        return $next($request);
    }
}
