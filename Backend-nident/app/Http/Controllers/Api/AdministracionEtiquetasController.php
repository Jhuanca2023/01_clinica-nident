<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdministracionEtiqueta;
use App\Services\Menu\MenuEtiquetaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdministracionEtiquetasController extends Controller
{
    public function __construct(
        private readonly MenuEtiquetaService $etiquetaService
    ) {
    }

    public function listar(Request $request): JsonResponse
    {
        if (!Schema::hasTable('administracion_etiquetas')) {
            return response()->json(['success' => true, 'message' => 'Sin tabla', 'result' => []]);
        }

        $q = DB::table('administracion_etiquetas')->orderBy('nombre');
        if ($request->input('activos_only') === '1') {
            $q->where('Activo', 'S');
        }
        if ($request->has('id_roles')) {
            $idRoles = (int) $request->input('id_roles');
            if ($idRoles === 0) {
                $q->whereNull('id_roles');
            } elseif ($idRoles > 0) {
                $q->where(function ($query) use ($idRoles) {
                    $query->whereNull('id_roles')->orWhere('id_roles', $idRoles);
                });
            }
        }

        $items = $q->get();

        return response()->json([
            'success' => true,
            'message' => 'Listar etiquetas',
            'result'  => $items,
        ]);
    }

    public function listar_opciones(Request $request): JsonResponse
    {
        $idRoles = (int) $request->input('id_roles', 0);

        return response()->json([
            'success' => true,
            'message' => 'Opciones de etiquetas',
            'result'  => $this->etiquetaService->listarOpciones($idRoles),
        ]);
    }

    public function crear(Request $request): JsonResponse
    {
        $request->validate([
            'nombre'   => 'required|string|max:120',
            'color'    => 'nullable|string|max:20',
            'id_roles' => 'nullable|integer|min:0',
        ]);

        $nombre = trim($request->nombre);
        $idRoles = $request->filled('id_roles') ? (int) $request->id_roles : null;
        if ($idRoles !== null && $idRoles < 1) {
            $idRoles = null;
        }

        $slug = MenuEtiquetaService::slugFromNombre($nombre);
        $baseSlug = $slug;
        $n = 1;
        while (DB::table('administracion_etiquetas')
            ->where('slug', $slug)
            ->where(function ($q) use ($idRoles) {
                if ($idRoles === null) {
                    $q->whereNull('id_roles');
                } else {
                    $q->where('id_roles', $idRoles);
                }
            })
            ->exists()) {
            $slug = $baseSlug . '-' . (++$n);
        }

        $row = AdministracionEtiqueta::create([
            'nombre'         => $nombre,
            'slug'           => $slug,
            'color'          => $request->input('color', '#6366f1'),
            'id_roles'       => $idRoles,
            'es_recomendada' => $idRoles === null ? 'S' : 'N',
            'Activo'         => 'S',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Etiqueta creada',
            'result'  => $row,
        ]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'id_etiqueta' => 'required|integer|exists:administracion_etiquetas,id_etiqueta',
            'nombre'      => 'nullable|string|max:120',
            'color'       => 'nullable|string|max:20',
            'Activo'      => 'nullable|in:S,N',
        ]);

        $row = AdministracionEtiqueta::findOrFail($request->id_etiqueta);
        $data = ['updated_at' => now()];

        if ($request->filled('nombre')) {
            $data['nombre'] = trim($request->nombre);
            $data['slug'] = MenuEtiquetaService::slugFromNombre($data['nombre']);
        }
        if ($request->has('color')) {
            $data['color'] = $request->color;
        }
        if ($request->has('Activo')) {
            $data['Activo'] = $request->Activo;
        }

        $row->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Etiqueta actualizada',
            'result'  => $row->fresh(),
        ]);
    }

    public function eliminar(Request $request): JsonResponse
    {
        $request->validate([
            'id_etiqueta' => 'required|integer|exists:administracion_etiquetas,id_etiqueta',
        ]);

        $id = (int) $request->id_etiqueta;

        if (Schema::hasTable('administracion_entidad_etiqueta')) {
            DB::table('administracion_entidad_etiqueta')->where('id_etiqueta', $id)->delete();
        }

        AdministracionEtiqueta::where('id_etiqueta', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Etiqueta eliminada',
            'result'  => 1,
        ]);
    }
}
