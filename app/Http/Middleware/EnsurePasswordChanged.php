<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If user is logged in, requires password change, and is NOT already on the change password page
        if ($user && 
            $user->REQUIERE_CAMBIO_CLAVE && 
            !$request->routeIs('password.change') && 
            !$request->routeIs('password.update') &&
            !$request->routeIs('logout')) {
            
            return redirect()->route('password.change')
                ->with('warning', 'Por motivos de seguridad, debe cambiar su contraseña antes de continuar.');
        }

        return $next($request);
    }
}
