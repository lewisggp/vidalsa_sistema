<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    public function show()
    {
        return view('auth.change_password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', 'min:6'],
        ], [
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden. Por favor, verifique que ambos campos sean idénticos.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
        ]);

        $user = Auth::user();
        
        // Update password and clear flag
        $user->PASSWORD_HASH = Hash::make($request->password);
        $user->REQUIERE_CAMBIO_CLAVE = 0;
        $user->save();

        return redirect()->route('menu')->with('success', 'Contraseña actualizada correctamente. Bienvenido.');
    }
}
