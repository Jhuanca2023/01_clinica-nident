<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RolesMenu;
use App\Models\SeguridadMenuObjetosRol;
use App\Services\Seguridad\SeguridadMenuTreeviewService;
use App\Services\Seguridad\RolPermisosAsignacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Treeview de menús y permisos por rol (ORM Laravel, sin SP ni datatreeview).
 */
class SistemaMenuTreeviewController extends Controller
{
    public function __construct(
        private readonly SeguridadMenuTreeviewService $treeviewService,
        private readonly RolPermisosAsignacionService $permisosService,
    ) {
    }

    /**
     * @OA\Get(
     *     path="/menu_treeview/listar",
     *     tags={"Roles"},
     *     summary="Listar el árbol de menús con estado de selección por rol",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_roles", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Árbol de menús segmentado")
     * )
     */
    public function listar(Request $request): JsonResponse
    {
        $id_roles = (int) $request->input('id_roles', 0);

        try {
            $result = $this->treeviewService->listarParaRol($id_roles);
        } catch (\Throwable $e) {
            Log::error('SistemaMenuTreeviewController::listar', [
                'id_roles' => $id_roles,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el árbol de menús: ' . $e->getMessage(),
                'result' => [],
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $result,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/menu_treeview/guardar",
     *     tags={"Roles"},
     *     summary="Guardar permisos asignados a un rol (módulos, menús, objetos)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_roles","id_menu"},
     *         @OA\Property(property="id_roles", type="integer", example=1),
     *         @OA\Property(property="id_menu", type="string", description="IDs concatenados por '|' (ej: mod_1|m10|o20)")
     *     )),
     *     @OA\Response(response=200, description="Permisos guardados correctamente")
     * )
     */
    public function guardar(Request $request): JsonResponse
    {
        $id_roles = (int) $request->input('id_roles');
        $id_menu = is_string($request->input('id_menu')) ? trim($request->input('id_menu', '')) : '';

        if ($id_roles < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no válido.',
                'result' => 0,
            ], 422);
        }

        $items = $id_menu === '' ? [] : array_filter(array_map('trim', explode('|', $id_menu)));

        if ($items === []) {
            return response()->json([
                'success' => false,
                'message' => 'No hay permisos seleccionados en el árbol.',
                'result' => 0,
            ], 422);
        }
        $menuIds = [];
        $menuObjetoIds = [];
        $moduloObjetoIds = [];
        $moduloIds = [];

        foreach ($items as $row) {
            if (preg_match('/^mod_(\d+)(?:-\d+)?$/', $row, $m)) {
                $moduloIds[] = (int) $m[1];
            } elseif (preg_match('/^m-?(\d+)(?:-\d+)?$/', $row, $m)) {
                $menuIds[] = (int) $m[1];
            } elseif (preg_match('/^o(\d+)(?:-\d+)?$/', $row, $m)) {
                $menuObjetoIds[] = (int) $m[1];
            } elseif (preg_match('/^om(\d+)(?:-\d+)?$/', $row, $m)) {
                $moduloObjetoIds[] = (int) $m[1];
            } elseif (preg_match('/^o-(\d+)-(\d+)$/', $row, $m)) {
                $id_mo = DB::table('sistema_menu_objetos')
                    ->where('id_menu', (int) $m[1])
                    ->where('id_objetos', (int) $m[2])
                    ->value('id_menu_objetos');
                if ($id_mo !== null) {
                    $menuObjetoIds[] = (int) $id_mo;
                }
            } elseif (ctype_digit($row)) {
                $menuIds[] = (int) $row;
            }
        }

        if (!empty($menuObjetoIds)) {
            $menusFromObjetos = DB::table('sistema_menu_objetos')
                ->whereIn('id_menu_objetos', array_unique($menuObjetoIds))
                ->pluck('id_menu')
                ->unique()
                ->values()
                ->all();
            foreach ($menusFromObjetos as $id_m) {
                $menuIds[] = (int) $id_m;
            }
        }

        if (!empty($menuIds) && Schema::hasTable('seguridad_roles_modulo')) {
            $modulosFromMenus = DB::table('sistema_menu')
                ->whereIn('id_menu', array_unique($menuIds))
                ->whereNotNull('id_modulo')
                ->pluck('id_modulo')
                ->unique()
                ->values()
                ->all();
            foreach ($modulosFromMenus as $id_mod) {
                $moduloIds[] = (int) $id_mod;
            }
        }

        if (!empty($moduloObjetoIds) && Schema::hasTable('sistema_modulo_objetos')) {
            $modulosFromObj = DB::table('sistema_modulo_objetos')
                ->whereIn('id_modulo_objetos', array_unique($moduloObjetoIds))
                ->pluck('id_modulo')
                ->unique()
                ->values()
                ->all();
            foreach ($modulosFromObj as $id_mod) {
                $moduloIds[] = (int) $id_mod;
            }
        }

        // Menú asignado sin ningún objeto en el payload → todos los del catálogo (sistema_menu_objetos)
        if (!empty($menuIds)) {
            $menusConObjetoEnPayload = [];
            if (!empty($menuObjetoIds)) {
                $menusConObjetoEnPayload = DB::table('sistema_menu_objetos')
                    ->whereIn('id_menu_objetos', array_unique($menuObjetoIds))
                    ->pluck('id_menu')
                    ->map(fn ($id) => (int) $id)
                    ->flip()
                    ->all();
            }
            foreach (array_unique($menuIds) as $idMenuVal) {
                if (isset($menusConObjetoEnPayload[$idMenuVal])) {
                    continue;
                }
                $extras = DB::table('sistema_menu_objetos')
                    ->where('id_menu', $idMenuVal)
                    ->where(function ($q) {
                        $q->where('Activo', 'S')->orWhereNull('Activo');
                    })
                    ->pluck('id_menu_objetos');
                foreach ($extras as $idMo) {
                    $menuObjetoIds[] = (int) $idMo;
                }
            }
            $menuObjetoIds = array_values(array_unique($menuObjetoIds));
        }

        DB::beginTransaction();
        try {
            RolesMenu::where('id_roles', $id_roles)->delete();
            SeguridadMenuObjetosRol::where('id_roles', $id_roles)->delete();
            if (Schema::hasTable('seguridad_modulo_objetos_roles')) {
                DB::table('seguridad_modulo_objetos_roles')->where('id_roles', $id_roles)->delete();
            }
            if (Schema::hasTable('seguridad_roles_modulo')) {
                DB::table('seguridad_roles_modulo')->where('id_roles', $id_roles)->delete();
            }

            foreach (array_unique($menuIds) as $id_menu_val) {
                RolesMenu::insert(['id_roles' => $id_roles, 'id_menu' => $id_menu_val]);
            }
            foreach (array_unique($menuObjetoIds) as $id_mo) {
                SeguridadMenuObjetosRol::insert(['id_roles' => $id_roles, 'id_menu_objetos' => $id_mo]);
            }
            if (Schema::hasTable('seguridad_modulo_objetos_roles')) {
                foreach (array_unique($moduloObjetoIds) as $id_modulo_objetos) {
                    DB::table('seguridad_modulo_objetos_roles')->insert([
                        'id_roles' => $id_roles,
                        'id_modulo_objetos' => $id_modulo_objetos,
                    ]);
                }
            }
            if (Schema::hasTable('seguridad_roles_modulo')) {
                $now = now();
                foreach (array_unique($moduloIds) as $id_modulo) {
                    DB::table('seguridad_roles_modulo')->insert([
                        'id_roles' => $id_roles,
                        'id_modulo' => $id_modulo,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            Log::error('SistemaMenuTreeviewController::guardar', [
                'id_roles' => $id_roles,
                'error' => $e->getMessage(),
            ]);
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar permisos: ' . $e->getMessage(),
                'result' => 0,
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permisos guardados correctamente',
            'result' => 1,
        ]);
    }

    /**
     * Copia permisos (menús, objetos, módulos) de un rol a otro.
     */
    public function copiar_permisos(Request $request): JsonResponse
    {
        $idOrigen = (int) $request->input('id_roles_origen', 0);
        $idDestino = (int) $request->input('id_roles_destino', 0);
        $incluirSidebar = filter_var($request->input('incluir_sidebar_orden', false), FILTER_VALIDATE_BOOLEAN);

        if ($idOrigen < 1 || $idDestino < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Debe indicar rol origen y rol destino.',
                'result' => null,
            ], 422);
        }

        try {
            $counts = $this->permisosService->copiarPermisosDesdeRol($idOrigen, $idDestino, $incluirSidebar);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'result' => null,
            ], 422);
        } catch (\Throwable $e) {
            Log::error('SistemaMenuTreeviewController::copiar_permisos', [
                'id_roles_origen'  => $idOrigen,
                'id_roles_destino' => $idDestino,
                'error'            => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al copiar permisos: ' . $e->getMessage(),
                'result' => null,
            ], 500);
        }

        $origenNombre = DB::table('seguridad_roles')->where('id_roles', $idOrigen)->value('nombre') ?? (string) $idOrigen;
        $destinoNombre = DB::table('seguridad_roles')->where('id_roles', $idDestino)->value('nombre') ?? (string) $idDestino;

        return response()->json([
            'success' => true,
            'message' => sprintf(
                'Permisos de «%s» copiados a «%s» (%d menús, %d objetos).',
                $origenNombre,
                $destinoNombre,
                $counts['menus'],
                $counts['objetos_menu'] + $counts['objetos_modulo']
            ),
            'result' => $counts,
        ]);
    }
}
