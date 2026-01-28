<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\Usuario;
use App\Models\BloqueoIp;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Handle expired CSRF token gracefully (419 error prevention)
        try {
            $credentials = $request->validate([
                'login_identifier' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);
        } catch (\Illuminate\Session\TokenMismatchException $e) {
            // Token expired, redirect to fresh login
            return redirect()->route('login')->with('info', 'Su sesión expiró. Por favor, inicie sesión nuevamente.');
        }

        $ip = $request->ip();

        // 0. BLOQUEO PERMANENTE (Base de Datos)
        $bloqueo = BloqueoIp::where('DIRECCION_IP', $ip)->first();
        if ($bloqueo && $bloqueo->BLOQUEO_PERMANENTE) {
            return back()->withErrors([
                'login_error' => 'Su dirección IP ha sido bloqueada permanentemente por seguridad. Contacte al administrador.',
            ])->withInput($request->except('password'));
        }

        // 1. Rate Limiting (Protección contra fuerza bruta Temporal)
        // Usamos el email + IP como clave única para el bloqueo
        $throttleKey = Str::lower($credentials['login_identifier']) . '|' . $ip;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            return back()->withErrors([
                'login_error' => 'Demasiados intentos fallidos. Por favor idente de nuevo en ' . $seconds . ' segundos.',
            ])->withInput($request->except('password'));
        }

        try {
            // 2. Auth::attempt (Estándar de Laravel)
            // Busca usuario, hashea la clave y compara, todo en uno.
            // Mapeamos 'login_identifier' a 'CORREO_ELECTRONICO' que es tu columna real
            $authCredentials = [
                'CORREO_ELECTRONICO' => $credentials['login_identifier'],
                'password' => $credentials['password']
            ];

            if (Auth::attempt($authCredentials)) {
                $user = Auth::user();

                // 3. Verificación de Estatus (Lógica de Negocio)
                if ($user->ESTATUS === 'INACTIVO') {
                    Auth::logout(); // Cerramos la sesión que attempt acaba de abrir
                    RateLimiter::hit($throttleKey); // Contamos como intento fallido para seguridad
                    
                    return back()->withErrors([
                        'login_error' => 'Usuario inactivo. Contacte al administrador.',
                    ])->withInput($request->except('password'));
                }

                // 4. Token de Sesión Única (Tu lógica personalizada)
                try {
                    $sessionToken = bin2hex(random_bytes(32));
                } catch (\Exception $e) {
                     $sessionToken = md5(uniqid(rand(), true)); 
                }
                
                $user->SESSION_TOKEN = $sessionToken;
                $user->save();

                // 5. Éxito: Regenerar sesión y limpiar Rate Limiter
                $request->session()->regenerate();
                $request->session()->put('current_session_token', $sessionToken);
                $request->session()->save(); 

                RateLimiter::clear($throttleKey); // Limpiamos el contador de fallos

                // Limpiar contador de bloqueo permanente si existe
                if ($bloqueo) {
                    $bloqueo->CANTIDAD_INTENTOS = 0;
                    $bloqueo->save();
                }

                return redirect()->route('menu');
            }

            // 6. Fallo de Credenciales
            RateLimiter::hit($throttleKey); // Incrementamos contador de fallos

            // REGISTRO DE FALLO EN BD (Para bloqueo permanente)
            if (!$bloqueo) {
                $bloqueo = new BloqueoIp();
                $bloqueo->DIRECCION_IP = $ip;
                $bloqueo->CANTIDAD_INTENTOS = 0;
            }
            
            $bloqueo->CANTIDAD_INTENTOS++;
            $bloqueo->ULTIMO_INTENTO = Carbon::now();
            
            if ($bloqueo->CANTIDAD_INTENTOS >= 10) {
                $bloqueo->BLOQUEO_PERMANENTE = true;
            }
            
            $bloqueo->save();

            if ($bloqueo->BLOQUEO_PERMANENTE) {
                 return back()->withErrors([
                    'login_error' => 'Ha excedido el límite de intentos. Su IP ha sido bloqueada permanentemente.',
                ])->withInput($request->except('password'));
            }

            return back()->withErrors([
                'login_error' => 'Usuario o clave incorrecta.',
            ])->withInput($request->except('password'));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Login Error: ' . $e->getMessage());
            return back()->withErrors([
                'login_error' => 'Error del sistema al iniciar sesión. Intente nuevamente.',
            ])->withInput($request->except('password'));
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->SESSION_TOKEN = null;
            $user->save();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
