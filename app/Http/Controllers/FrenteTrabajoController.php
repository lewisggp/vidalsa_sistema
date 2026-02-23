<?php

namespace App\Http\Controllers;

use App\Models\FrenteTrabajo;
use Illuminate\Http\Request;

class FrenteTrabajoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // El usuario prefiere trabajar exclusivamente desde el formulario de creación/edición
        // con el buscador integrado. Redirigimos siempre a CREATE.
        return redirect()->route('frentes.create');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Stats
        $stats = FrenteTrabajo::selectRaw("
            count(case when ESTATUS_FRENTE = 'ACTIVO' then 1 end) as activos, 
            count(case when ESTATUS_FRENTE = 'FINALIZADO' then 1 end) as finalizados
        ")->first();

        // Pre-load for Search Dropdown (Simple list)
        $allFrentes = FrenteTrabajo::select('ID_FRENTE', 'NOMBRE_FRENTE')
            ->orderBy('NOMBRE_FRENTE')
            ->get();

        // Create empty frente instance for the form
        $frente = new FrenteTrabajo();
        $categorias = ['FLOTA LIVIANA', 'FLOTA PESADA'];

        return view('admin.frentes.formulario', compact('frente', 'stats', 'allFrentes', 'categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        // Force uppercase for all text fields
        $request->merge([
            'NOMBRE_FRENTE' => mb_strtoupper($request->input('NOMBRE_FRENTE')),
            'UBICACION' => mb_strtoupper($request->input('UBICACION')),
            'SUBDIVISIONES' => $request->filled('SUBDIVISIONES') ? mb_strtoupper($request->input('SUBDIVISIONES')) : null,
            'RESP_1_NOM' => mb_strtoupper($request->input('RESP_1_NOM')),
            'RESP_1_CAR' => mb_strtoupper($request->input('RESP_1_CAR')),
            'RESP_1_EQU' => mb_strtoupper($request->input('RESP_1_EQU')),
            'RESP_2_NOM' => mb_strtoupper($request->input('RESP_2_NOM')),
            'RESP_2_CAR' => mb_strtoupper($request->input('RESP_2_CAR')),
            'RESP_2_EQU' => mb_strtoupper($request->input('RESP_2_EQU')),
            'RESP_3_NOM' => mb_strtoupper($request->input('RESP_3_NOM')),
            'RESP_3_CAR' => mb_strtoupper($request->input('RESP_3_CAR')),
            'RESP_3_EQU' => mb_strtoupper($request->input('RESP_3_EQU')),
            'RESP_4_NOM' => mb_strtoupper($request->input('RESP_4_NOM')),
            'RESP_4_CAR' => mb_strtoupper($request->input('RESP_4_CAR')),
            'RESP_4_EQU' => mb_strtoupper($request->input('RESP_4_EQU')),
        ]);

        $validated = $request->validate([
            'NOMBRE_FRENTE' => 'required|string|max:150|unique:frentes_trabajo,NOMBRE_FRENTE',
            'UBICACION' => 'required|string|max:100',
            'TIPO_FRENTE' => 'required|in:OPERACION,RESGUARDO',
            'ESTATUS_FRENTE' => 'required|in:ACTIVO,FINALIZADO',
            'SUBDIVISIONES' => 'nullable|string',
            'RESP_1_NOM' => 'required|string|max:60',
            'RESP_1_CAR' => 'required|string|max:40',
            'RESP_2_NOM' => 'nullable|string|max:60',
            'RESP_2_CAR' => 'nullable|string|max:40',
            'RESP_3_NOM' => 'nullable|string|max:60',
            'RESP_3_CAR' => 'nullable|string|max:40',
            'RESP_4_NOM' => 'nullable|string|max:60',
            'RESP_4_CAR' => 'nullable|string|max:40',
            'RESP_1_EQU' => 'nullable|string|max:40',
            'RESP_2_EQU' => 'nullable|string|max:40',
            'RESP_3_EQU' => 'nullable|string|max:40',
            'RESP_4_EQU' => 'nullable|string|max:40',
        ], [
            'NOMBRE_FRENTE.required' => 'El nombre del frente es obligatorio.',
            'NOMBRE_FRENTE.unique' => 'Ya existe un frente de trabajo con este nombre.',
            'UBICACION.required' => 'La ubicación es obligatoria.',
            'TIPO_FRENTE.required' => 'El tipo de frente es obligatorio.',
            'ESTATUS_FRENTE.required' => 'El estatus es obligatorio.',
            'RESP_1_NOM.required' => 'El nombre del responsable principal es obligatorio.',
            'RESP_1_CAR.required' => 'El cargo del responsable principal es obligatorio.',
        ]);

        $frente = FrenteTrabajo::create($validated);

        if ($request->wantsJson() || $request->has('json')) {
            return response()->json([
                'success' => true,
                'message' => 'Frente de trabajo creado correctamente.',
                'frente' => $frente,
                'redirect' => route('frentes.create')
            ]);
        }

        return redirect()->route('frentes.create')->with('success', 'Frente de trabajo creado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $frente = FrenteTrabajo::findOrFail($id);

        if ($request->wantsJson() || $request->has('json')) {
            return response()->json($frente);
        }

        // Stats
        $stats = FrenteTrabajo::selectRaw("
            count(case when ESTATUS_FRENTE = 'ACTIVO' then 1 end) as activos, 
            count(case when ESTATUS_FRENTE = 'FINALIZADO' then 1 end) as finalizados
        ")->first();

        // Pre-load for Search Dropdown
        $allFrentes = FrenteTrabajo::select('ID_FRENTE', 'NOMBRE_FRENTE')
            ->orderBy('NOMBRE_FRENTE')
            ->get();

        $categorias = ['FLOTA LIVIANA', 'FLOTA PESADA'];

        return view('admin.frentes.formulario', compact('frente', 'stats', 'allFrentes', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $frente = FrenteTrabajo::findOrFail($id);

        // Force uppercase for all text fields
        $request->merge([
            'NOMBRE_FRENTE' => mb_strtoupper($request->input('NOMBRE_FRENTE')),
            'UBICACION' => mb_strtoupper($request->input('UBICACION')),
            'SUBDIVISIONES' => $request->filled('SUBDIVISIONES') ? mb_strtoupper($request->input('SUBDIVISIONES')) : null,
            'RESP_1_NOM' => mb_strtoupper($request->input('RESP_1_NOM')),
            'RESP_1_CAR' => mb_strtoupper($request->input('RESP_1_CAR')),
            'RESP_2_NOM' => mb_strtoupper($request->input('RESP_2_NOM')),
            'RESP_2_CAR' => mb_strtoupper($request->input('RESP_2_CAR')),
            'RESP_3_NOM' => mb_strtoupper($request->input('RESP_3_NOM')),
            'RESP_3_CAR' => mb_strtoupper($request->input('RESP_3_CAR')),
            'RESP_4_NOM' => mb_strtoupper($request->input('RESP_4_NOM')),
            'RESP_4_CAR' => mb_strtoupper($request->input('RESP_4_CAR')),
            'RESP_1_EQU' => mb_strtoupper($request->input('RESP_1_EQU')),
            'RESP_2_EQU' => mb_strtoupper($request->input('RESP_2_EQU')),
            'RESP_3_EQU' => mb_strtoupper($request->input('RESP_3_EQU')),
            'RESP_4_EQU' => mb_strtoupper($request->input('RESP_4_EQU')),
        ]);

        $validated = $request->validate([
            'NOMBRE_FRENTE' => 'required|string|max:150|unique:frentes_trabajo,NOMBRE_FRENTE,' . $id . ',ID_FRENTE',
            'UBICACION' => 'required|string|max:100',
            'TIPO_FRENTE' => 'required|in:OPERACION,RESGUARDO',
            'ESTATUS_FRENTE' => 'required|in:ACTIVO,FINALIZADO',
            'SUBDIVISIONES' => 'nullable|string',
            'RESP_1_NOM' => 'required|string|max:60',
            'RESP_1_CAR' => 'required|string|max:40',
            'RESP_2_NOM' => 'nullable|string|max:60',
            'RESP_2_CAR' => 'nullable|string|max:40',
            'RESP_3_NOM' => 'nullable|string|max:60',
            'RESP_3_CAR' => 'nullable|string|max:40',
            'RESP_4_NOM' => 'nullable|string|max:60',
            'RESP_4_CAR' => 'nullable|string|max:40',
            'RESP_1_EQU' => 'nullable|string|max:40',
            'RESP_2_EQU' => 'nullable|string|max:40',
            'RESP_3_EQU' => 'nullable|string|max:40',
            'RESP_4_EQU' => 'nullable|string|max:40',
        ], [
            'NOMBRE_FRENTE.required' => 'El nombre del frente es obligatorio.',
            'NOMBRE_FRENTE.unique' => 'Ya existe un frente de trabajo con este nombre.',
            'UBICACION.required' => 'La ubicación es obligatoria.',
            'RESP_1_NOM.required' => 'El nombre del responsable principal es obligatorio.',
            'RESP_1_CAR.required' => 'El cargo del responsable principal es obligatorio.',
        ]);

        $frente->update($validated);

        if ($request->wantsJson() || $request->has('json')) {
            return response()->json([
                'success' => true,
                'message' => 'Frente de trabajo actualizado correctamente.',
                'frente' => $frente
            ]);
        }

        return redirect()->route('frentes.create')->with('success', 'Frente de trabajo actualizado correctamente.');
    }


}
