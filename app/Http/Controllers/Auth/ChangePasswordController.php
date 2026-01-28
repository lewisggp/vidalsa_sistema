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
        ]);

        $user = Auth::user();
        
        // Update password and clear flag
        $user->PASSWORD_HASH = Hash::make($request->password);
        $user->REQUIERE_CAMBIO_CLAVE = 0;
        $user->save();

        return redirect()->route('menu')->with('success', 'Contraseña actualizada correctamente. Bienvenido.');
    }
}
