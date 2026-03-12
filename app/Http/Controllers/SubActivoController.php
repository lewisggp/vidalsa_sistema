<?php

namespace App\Http\Controllers;

use App\Models\SubActivo;
use App\Models\FrenteTrabajo;
use App\Models\Equipo;
use Illuminate\Http\Request;

class SubActivoController extends Controller
{
    /**
     * GET /admin/sub-activos
     * Devuelve JSON con todos los sub-activos (para cargar el modal).
     * Incluye frente y vehículo host.
     */
    public function index(Request $request)
    {
        $query = SubActivo::with(['frente', 'equipoHost.tipo', 'equipoHost.documentacion'])
            ->orderBy('tipo')
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('id_frente')) {
            $query->where('ID_FRENTE', $request->id_frente);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('serial', 'like', "%{$q}%")
                   ->orWhere('marca',  'like', "%{$q}%")
                   ->orWhere('modelo', 'like', "%{$q}%");
            });
        }
        if ($request->filled('host')) {
            // host=null → sueltos; host=<id> → del vehículo
            if ($request->host === 'null') {
                $query->whereNull('ID_EQUIPO_HOST');
            } else {
                $query->where('ID_EQUIPO_HOST', $request->host);
            }
        }

        $items = $query->get()->map(function ($sa) {
            return [
                'id'              => $sa->id,
                'tipo'            => $sa->tipo,
                'tipo_label'      => $sa->tipo_label,
                'tipo_icono'      => $sa->tipo_icono,
                'serial'          => $sa->serial,
                'marca'           => $sa->marca,
                'modelo'          => $sa->modelo,
                'capacidad'       => $sa->capacidad,
                'anio'            => $sa->anio,
                'estado'          => $sa->estado,
                'observaciones'   => $sa->observaciones,
                'frente_id'       => $sa->ID_FRENTE,
                'frente_nombre'   => $sa->frente->NOMBRE_FRENTE  ?? null,
                'host_id'         => $sa->ID_EQUIPO_HOST,
                'host_codigo'     => $sa->equipoHost->CODIGO_PATIO  ?? null,
                'host_tipo'       => $sa->equipoHost->tipo->nombre  ?? null,
                'host_placa'      => $sa->equipoHost->documentacion->PLACA ?? null,
                'host_foto'       => $sa->equipoHost->FOTO_EQUIPO ?? ($sa->equipoHost->especificaciones->FOTO_REFERENCIAL ?? null),
            ];
        });

        return response()->json([
            'ok'    => true,
            'data'  => $items,
            'total' => $items->count(),
        ]);
    }

    /**
     * POST /admin/sub-activos
     * Crea un nuevo sub-activo.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo'            => 'required|in:MAQUINA_SOLDADURA,PLANTA_ELECTRICA,CONTENEDOR,COMPRESOR,OTRO',
            'serial'          => 'nullable|string|max:100',
            'marca'           => 'nullable|string|max:80',
            'modelo'          => 'nullable|string|max:80',
            'capacidad'       => 'nullable|string|max:80',
            'anio'            => 'nullable|integer|min:1950|max:2100',
            'ID_FRENTE'       => 'nullable|exists:frentes_trabajo,ID_FRENTE',
            'ID_EQUIPO_HOST'  => 'nullable|exists:equipos,ID_EQUIPO',
            'estado'          => 'required|in:OPERATIVO,INOPERATIVO,EN_ALMACEN',
            'observaciones'   => 'nullable|string|max:1000',
        ]);

        $sa = SubActivo::create($validated);
        $sa->load(['frente', 'equipoHost.tipo', 'equipoHost.documentacion']);

        return response()->json([
            'ok'   => true,
            'data' => [
                'id'            => $sa->id,
                'tipo'          => $sa->tipo,
                'tipo_label'    => $sa->tipo_label,
                'tipo_icono'    => $sa->tipo_icono,
                'serial'        => $sa->serial,
                'marca'         => $sa->marca,
                'modelo'        => $sa->modelo,
                'capacidad'     => $sa->capacidad,
                'anio'          => $sa->anio,
                'estado'        => $sa->estado,
                'observaciones' => $sa->observaciones,
                'frente_id'     => $sa->ID_FRENTE,
                'frente_nombre' => $sa->frente->NOMBRE_FRENTE ?? null,
                'host_id'       => $sa->ID_EQUIPO_HOST,
                'host_codigo'   => $sa->equipoHost->CODIGO_PATIO ?? null,
                'host_tipo'     => $sa->equipoHost->tipo->nombre ?? null,
            ],
        ]);
    }

    /**
     * PATCH /admin/sub-activos/{id}
     * Actualiza un sub-activo.
     */
    public function update(Request $request, $id)
    {
        $sa = SubActivo::findOrFail($id);

        $validated = $request->validate([
            'tipo'            => 'sometimes|in:MAQUINA_SOLDADURA,PLANTA_ELECTRICA,CONTENEDOR,COMPRESOR,OTRO',
            'serial'          => 'nullable|string|max:100',
            'marca'           => 'nullable|string|max:80',
            'modelo'          => 'nullable|string|max:80',
            'capacidad'       => 'nullable|string|max:80',
            'anio'            => 'nullable|integer|min:1950|max:2100',
            'ID_FRENTE'       => 'nullable|exists:frentes_trabajo,ID_FRENTE',
            'ID_EQUIPO_HOST'  => 'nullable|exists:equipos,ID_EQUIPO',
            'estado'          => 'sometimes|in:OPERATIVO,INOPERATIVO,EN_ALMACEN',
            'observaciones'   => 'nullable|string|max:1000',
        ]);

        $sa->update($validated);

        return response()->json(['ok' => true]);
    }

    /**
     * DELETE /admin/sub-activos/{id}
     */
    public function destroy($id)
    {
        SubActivo::findOrFail($id)->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * GET /admin/sub-activos/count
     * Devuelve el total de sub-activos (para el badge en el botón Acciones).
     */
    public function count()
    {
        return response()->json(['total' => SubActivo::count()]);
    }
}
