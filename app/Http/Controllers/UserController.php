<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Role;
use App\Models\FrenteTrabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start with base query
        $query = Usuario::select('ID_USUARIO', 'NOMBRE_COMPLETO', 'CORREO_ELECTRONICO', 'ID_ROL', 'ID_FRENTE_ASIGNADO', 'NIVEL_ACCESO', 'ESTATUS')
            ->with([
                'rol:ID_ROL,NOMBRE_ROL', 
                'frenteAsignado:ID_FRENTE,NOMBRE_FRENTE'
            ]);

        // FILTER 1: Search by name or email (independent)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('NOMBRE_COMPLETO', 'like', "%{$search}%")
                  ->orWhere('CORREO_ELECTRONICO', 'like', "%{$search}%");
            });
        }

        // FILTER 2: Frente de Trabajo (independent)
        if ($request->filled('id_frente')) {
            $query->where('ID_FRENTE_ASIGNADO', $request->input('id_frente'));
        }

        // Execute query with pagination
        $users = $query->paginate(10)->withQueryString();
        
        // Frentes for dropdown
        $frentes = FrenteTrabajo::where('ESTATUS_FRENTE', 'ACTIVO')->get();

        // Statistics for info cards (Optimized to 1 query)
        $stats = Usuario::selectRaw("
            count(*) as total, 
            count(case when ESTATUS = 'ACTIVO' then 1 end) as activos, 
            count(case when ESTATUS = 'INACTIVO' then 1 end) as inactivos
        ")->first();

        $totalUsuarios = $stats->total;
        $usuariosActivos = $stats->activos;
        $usuariosInactivos = $stats->inactivos;

        // AJAX Response
        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('admin.usuarios.partials.table_rows', compact('users'))->render(),
                'pagination' => $users->links()->toHtml(),
                'count' => $users->total()
            ]);
        }

        return view('admin.usuarios.lista', compact('users', 'frentes', 'totalUsuarios', 'usuariosActivos', 'usuariosInactivos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::select('ID_ROL', 'NOMBRE_ROL')->get();
        $frentes = FrenteTrabajo::where('ESTATUS_FRENTE', 'ACTIVO')->select('ID_FRENTE', 'NOMBRE_FRENTE')->get();
        $available_permissions = ['VER', 'EDT', 'DOC', 'LOG', 'OPS', 'ADM'];
        
        return view('admin.usuarios.formulario', compact('roles', 'frentes', 'available_permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'NOMBRE_COMPLETO' => 'required|string|max:150',
            'CORREO_ELECTRONICO' => [
                'required',
                'email',
                'unique:usuarios,CORREO_ELECTRONICO',
                'regex:/^.+@cvidalsa27\.com$/i'
            ],
            'password' => 'required|string|min:6',
            'ID_ROL' => 'required|exists:roles,ID_ROL',
            'ID_FRENTE_ASIGNADO' => 'required|exists:frentes_trabajo,ID_FRENTE',
            'NIVEL_ACCESO' => 'required|integer|in:1,2',
            'ESTATUS' => 'required|in:ACTIVO,INACTIVO',
            'PERMISOS' => 'required|array',
            'PERMISOS.*' => 'in:VER,EDT,DOC,LOG,OPS,ADM',
        ], [
            'NOMBRE_COMPLETO.required' => 'El nombre completo es obligatorio.',
            'CORREO_ELECTRONICO.required' => 'El correo electrónico es obligatorio.',
            'CORREO_ELECTRONICO.email' => 'El formato del correo electrónico no es válido.',
            'CORREO_ELECTRONICO.unique' => 'Este correo electrónico ya está registrado en el sistema.',
            'CORREO_ELECTRONICO.regex' => 'Solo se permiten correos con el dominio @cvidalsa27.com',
            'password.required' => 'La clave de acceso es obligatoria.',
            'password.min' => 'La clave de acceso debe tener al menos 6 caracteres.',
            'ID_ROL.required' => 'Debes asignar un rol al usuario.',
            'ID_ROL.exists' => 'El rol seleccionado no es válido.',
            'ID_FRENTE_ASIGNADO.required' => 'Debes asignar un frente de trabajo.',
            'ID_FRENTE_ASIGNADO.exists' => 'El frente asignado no es válido.',
            'NIVEL_ACCESO.required' => 'El nivel de acceso es obligatorio.',
            'NIVEL_ACCESO.in' => 'El nivel de acceso seleccionado no es válido.',
            'ESTATUS.required' => 'El estatus es obligatorio.',
            'ESTATUS.in' => 'El estatus seleccionado no es válido.',
            'PERMISOS.required' => 'Debes seleccionar al menos un permiso.',
        ]);

        // Create user with mass assignment for validated data
        $user = new Usuario($validated);
        $user->NOMBRE_COMPLETO = mb_convert_case($request->NOMBRE_COMPLETO, MB_CASE_TITLE, 'UTF-8');
        $user->CORREO_ELECTRONICO = strtolower($request->CORREO_ELECTRONICO);
        $user->PASSWORD_HASH = Hash::make($request->password);
        $user->REQUIERE_CAMBIO_CLAVE = 1; // Force password change for new users
        $user->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente.',
                'redirect' => route('usuarios.create') // Reload create form
            ]);
        }

        return redirect()->route('usuarios.create')->with('success', 'Usuario creado correctamente.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = Usuario::findOrFail($id);
        $roles = Role::select('ID_ROL', 'NOMBRE_ROL')->get();
        $frentes = FrenteTrabajo::where('ESTATUS_FRENTE', 'ACTIVO')->select('ID_FRENTE', 'NOMBRE_FRENTE')->get();
        $available_permissions = ['VER', 'EDT', 'DOC', 'LOG', 'OPS', 'ADM'];

        return view('admin.usuarios.formulario', compact('user', 'roles', 'frentes', 'available_permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Usuario::findOrFail($id);

        $validated = $request->validate([
            'NOMBRE_COMPLETO' => 'required|string|max:150',
            'CORREO_ELECTRONICO' => [
                'required',
                'email',
                Rule::unique('usuarios', 'CORREO_ELECTRONICO')->ignore($user->ID_USUARIO, 'ID_USUARIO'),
                'regex:/^.+@cvidalsa27\.com$/i'
            ],
            'password' => 'nullable|string|min:6',
            'ID_ROL' => 'required|exists:roles,ID_ROL',
            'ID_FRENTE_ASIGNADO' => 'required|exists:frentes_trabajo,ID_FRENTE',
            'NIVEL_ACCESO' => 'required|integer|in:1,2',
            'ESTATUS' => 'required|in:ACTIVO,INACTIVO',
            'PERMISOS' => 'required|array',
            'PERMISOS.*' => 'in:VER,EDT,DOC,LOG,OPS,ADM',
        ], [
            'NOMBRE_COMPLETO.required' => 'El nombre completo es obligatorio.',
            'CORREO_ELECTRONICO.required' => 'El correo electrónico es obligatorio.',
            'CORREO_ELECTRONICO.email' => 'El formato del correo electrónico no es válido.',
            'CORREO_ELECTRONICO.unique' => 'Este correo electrónico ya está registrado en el sistema.',
            'CORREO_ELECTRONICO.regex' => 'Solo se permiten correos con el dominio @cvidalsa27.com',
            'password.min' => 'La clave de acceso debe tener al menos 6 caracteres.',
            'ID_ROL.required' => 'Debes asignar un rol al usuario.',
            'ID_ROL.exists' => 'El rol seleccionado no es válido.',
            'ID_FRENTE_ASIGNADO.required' => 'Debes asignar un frente de trabajo.',
            'ID_FRENTE_ASIGNADO.exists' => 'El frente asignado no es válido.',
            'NIVEL_ACCESO.required' => 'El nivel de acceso es obligatorio.',
            'NIVEL_ACCESO.in' => 'El nivel de acceso seleccionado no es válido.',
            'ESTATUS.required' => 'El estatus es obligatorio.',
            'ESTATUS.in' => 'El estatus seleccionado no es válido.',
            'PERMISOS.required' => 'Debes seleccionar al menos un permiso.',
        ]);

        // Update user attributes
        $user->fill($validated);
        $user->NOMBRE_COMPLETO = mb_convert_case($request->NOMBRE_COMPLETO, MB_CASE_TITLE, 'UTF-8');
        $user->CORREO_ELECTRONICO = strtolower($request->CORREO_ELECTRONICO);

        if ($request->filled('password')) {
            $user->PASSWORD_HASH = Hash::make($request->password);
        }

        $user->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente.',
                'redirect' => route('usuarios.index')
            ]);
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Usuario::findOrFail($id);
        $user->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
