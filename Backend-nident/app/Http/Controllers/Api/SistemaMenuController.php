<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Menu\MenuEtiquetaService;
use App\Services\Menu\MenuHistorialSessionesService;
use App\Services\Menu\MenuModuloRolService;
use App\Services\Menu\SistemaMenuFavoritosService;
use App\Services\Menu\SistemaMenuOrdenService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class SistemaMenuController extends Controller
{
    public function __construct(
        private readonly SistemaMenuOrdenService $ordenService,
        private readonly SistemaMenuFavoritosService $favoritosService,
        private readonly MenuEtiquetaService $etiquetaService,
        private readonly MenuModuloRolService $moduloRolService,
        private readonly MenuHistorialSessionesService $historialSessionesService
    ) {
    }

    private function resolveIdRoles(Request $request): int
    {
        return max(0, (int) $request->input('id_roles', 0));
    }
    /**
     * Lista menús. Opcional: ?id_menu_padre= (solo raíces) o ?id_menu_padre=5 (solo hijos de 5).
     * Sin param devuelve todos (para formularios).
     */
    /**
     * @OA\Get(
     *     path="/menu/listar",
     *     tags={"Menú"},
     *     summary="Listar menús del sistema",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_menu_padre", in="query", required=false, @OA\Schema(type="integer"), description="Filtrar por padre"),
     *     @OA\Response(response=200, description="Lista de menús", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar(Request $request): JsonResponse
    {
        $hasParent = Schema::hasColumn('sistema_menu', 'id_menu_padre');
        $hasOrden = Schema::hasColumn('sistema_menu', 'orden');
        $hasIcon = Schema::hasColumn('sistema_menu', 'Icon');
        $query = DB::table('sistema_menu as m')
            ->leftJoin('sistema_modulo as mod', 'm.id_modulo', '=', 'mod.id_modulo')
            ->select(
                'm.id_menu',
                'm.id_modulo',
                $hasParent ? 'm.id_menu_padre' : DB::raw('NULL as id_menu_padre'),
                $hasOrden ? 'm.orden' : DB::raw('0 as orden'),
                'm.nombre',
                'm.url',
                $hasIcon ? 'm.Icon as icon' : DB::raw('NULL as icon'),
                'm.Activo',
                'm.created_at',
                'mod.nombre as modulo_nombre'
            );
        if ($hasParent && $request->has('id_menu_padre')) {
            $pid = $request->input('id_menu_padre');
            if ($pid === '' || $pid === null) {
                $query->whereNull('m.id_menu_padre');
            } else {
                $query->where('m.id_menu_padre', (int) $pid);
            }
        }
        if ($hasParent) {
            $query->orderByRaw('m.id_menu_padre IS NULL DESC')->orderBy($hasOrden ? 'm.orden' : 'm.id_menu');
        } else {
            $query->orderBy('m.id_modulo')->orderBy('m.id_menu');
        }
        $items = $query->get();
        $idRoles = $this->resolveIdRoles($request);
        if ($idRoles > 0) {
            $items = $this->etiquetaService->enriquecerMenus($items, $idRoles);
        }

        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $items,
        ]);
    }

    /** Lista todos los módulos (sistema_modulo) para selector y árbol */
    /**
     * @OA\Get(
     *     path="/menu/listar_modulos",
     *     tags={"Módulos"},
     *     summary="Listar módulos del sistema",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="activos_only", in="query", required=false, @OA\Schema(type="string", enum={"0","1"})),
     *     @OA\Response(response=200, description="Lista de módulos", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar_modulos(Request $request): JsonResponse
    {
        $hasOrden = Schema::hasColumn('sistema_modulo', 'orden');
        $query = DB::table('sistema_modulo')->orderBy($hasOrden ? 'orden' : 'id_modulo');
        if ($request->input('activos_only') === '1') {
            $query->where('Activo', 'S');
        }
        $cols = ['id_modulo', 'nombre', 'Activo', 'Icon'];
        if (Schema::hasColumn('sistema_modulo', 'url')) {
            $cols[] = 'url';
        }
        if ($hasOrden) {
            $cols[] = 'orden';
        }
        $items = $query->get($cols);
        $idRoles = $this->resolveIdRoles($request);
        if ($idRoles > 0) {
            $items = $this->etiquetaService->enriquecerModulos($items, $idRoles);
        }
        return response()->json([
            'success' => true,
            'message' => 'Listar módulos',
            'result' => $items,
        ]);
    }

    /** Crea un nuevo módulo (sistema_modulo). Acepta nombre y url. */
    /**
     * @OA\Post(
     *     path="/menu/crear_modulo",
     *     tags={"Módulos"},
     *     summary="Crear nuevo módulo",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"nombre"},
     *         @OA\Property(property="nombre", type="string"),
     *         @OA\Property(property="url", type="string"),
     *         @OA\Property(property="Icon", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Módulo creado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear_modulo(Request $request): JsonResponse
    {
        $nombre = trim((string) $request->input('nombre'));
        $url = trim((string) $request->input('url', ''));
        if ($nombre === '' && $url === '') {
            return response()->json([
                'success' => false,
                'message' => 'Indique al menos el nombre o la URL del módulo',
                'result' => 0,
            ], 422);
        }
        $now = now();
        $data = [
            'nombre' => $nombre !== '' ? $nombre : ($url !== '' ? 'Módulo' : 'Módulo'),
            'Activo' => $request->input('Activo', 'S'),
            'created_at' => $now,
            'updated_at' => $now,
        ];
        if (Schema::hasColumn('sistema_modulo', 'Icon')) {
            $data['Icon'] = $request->input('Icon');
        }
        if (Schema::hasColumn('sistema_modulo', 'url')) {
            $data['url'] = $url !== '' ? $url : null;
        }
        $id = DB::table('sistema_modulo')->insertGetId($data);

        $cols = ['id_modulo', 'nombre', 'Activo', 'Icon'];
        if (Schema::hasColumn('sistema_modulo', 'url')) {
            $cols[] = 'url';
        }
        $row = DB::table('sistema_modulo')->where('id_modulo', $id)->first($cols);
        $idRoles = $this->resolveIdRoles($request);
        if ($idRoles > 0) {
            $this->etiquetaService->syncModulo($idRoles, (int) $id, $request->input('id_etiquetas'));
        }
        return response()->json([
            'success' => true,
            'message' => 'Módulo creado',
            'result' => $row,
        ]);
    }

    /**
     * Convierte el grupo virtual «Sin módulo» en un módulo real y asigna todos los menús huérfanos.
     */
    public function convertir_sin_modulo(Request $request): JsonResponse
    {
        $nombre = trim((string) $request->input('nombre'));
        $url = trim((string) $request->input('url', ''));
        if ($nombre === '' && $url === '') {
            return response()->json([
                'success' => false,
                'message' => 'Indique al menos el nombre o la URL del módulo',
                'result' => 0,
            ], 422);
        }

        if (!Schema::hasTable('sistema_menu')) {
            return response()->json(['success' => false, 'message' => 'Tabla sistema_menu no disponible', 'result' => 0], 422);
        }

        $huerfanos = DB::table('sistema_menu')->whereNull('id_modulo')->count();
        if ($huerfanos < 1) {
            return response()->json([
                'success' => false,
                'message' => 'No hay menús sin módulo para asignar',
                'result' => 0,
            ], 422);
        }

        $now = now();
        $data = [
            'nombre' => $nombre !== '' ? $nombre : 'Módulo',
            'Activo' => $request->input('Activo', 'S'),
            'created_at' => $now,
            'updated_at' => $now,
        ];
        if (Schema::hasColumn('sistema_modulo', 'Icon')) {
            $data['Icon'] = $request->input('Icon');
        }
        if (Schema::hasColumn('sistema_modulo', 'url')) {
            $data['url'] = $url !== '' ? $url : null;
        }
        if (Schema::hasColumn('sistema_modulo', 'orden')) {
            $maxOrden = (int) DB::table('sistema_modulo')->max('orden');
            $data['orden'] = $maxOrden + 1;
        }

        $idModulo = DB::table('sistema_modulo')->insertGetId($data);

        $asignados = DB::table('sistema_menu')
            ->whereNull('id_modulo')
            ->update([
                'id_modulo' => $idModulo,
                'updated_at' => $now,
            ]);

        $cols = ['id_modulo', 'nombre', 'Activo', 'Icon'];
        if (Schema::hasColumn('sistema_modulo', 'url')) {
            $cols[] = 'url';
        }
        $row = DB::table('sistema_modulo')->where('id_modulo', $idModulo)->first($cols);

        $idRoles = $this->resolveIdRoles($request);
        if ($idRoles > 0) {
            $this->etiquetaService->syncModulo($idRoles, (int) $idModulo, $request->input('id_etiquetas'));
        }

        return response()->json([
            'success' => true,
            'message' => "Módulo creado y {$asignados} menú(s) asignados",
            'result' => [
                'modulo' => $row,
                'menus_asignados' => $asignados,
            ],
        ]);
    }

    /** Actualiza un módulo existente (sistema_modulo). */
    /**
     * @OA\Put(
     *     path="/menu/actualizar_modulo",
     *     tags={"Módulos"},
     *     summary="Actualizar módulo existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_modulo"},
     *         @OA\Property(property="id_modulo", type="integer"),
     *         @OA\Property(property="nombre", type="string"),
     *         @OA\Property(property="url", type="string"),
     *         @OA\Property(property="Icon", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Módulo actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar_modulo(Request $request): JsonResponse
    {
        $id = $request->input('id_modulo');
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'id_modulo requerido', 'result' => 0], 422);
        }
        $data = [
            'nombre' => $request->input('nombre', ''),
            'Activo' => $request->input('Activo', 'S'),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('sistema_modulo', 'Icon')) {
            $data['Icon'] = $request->input('Icon');
        }
        if (Schema::hasColumn('sistema_modulo', 'url')) {
            $data['url'] = $request->input('url') ?: null;
        }
        DB::table('sistema_modulo')->where('id_modulo', $id)->update($data);
        $nombre = trim((string) ($data['nombre'] ?? ''));
        if ($nombre !== '') {
            $this->ordenService->syncNombreSidebarModulo((int) $id, $nombre);
        }
        $cols = ['id_modulo', 'nombre', 'Activo', 'Icon'];
        if (Schema::hasColumn('sistema_modulo', 'url')) {
            $cols[] = 'url';
        }
        $row = DB::table('sistema_modulo')->where('id_modulo', $id)->first($cols);
        $idRoles = $this->resolveIdRoles($request);
        if ($idRoles > 0) {
            $activo = $request->input('Activo', 'S');
            if ($activo === 'N') {
                $this->moduloRolService->desactivarModuloParaRol((int) $id, $idRoles);
            } else {
                $this->moduloRolService->reactivarModuloParaRol((int) $id, $idRoles);
            }
            if ($request->has('id_etiquetas')) {
                $this->etiquetaService->syncModulo($idRoles, (int) $id, $request->input('id_etiquetas'));
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Módulo actualizado',
            'result' => $row,
        ]);
    }

    /** Elimina un módulo y opcionalmente sus menús huérfanos. */
    /**
     * @OA\Delete(
     *     path="/menu/eliminar_modulo",
     *     tags={"Módulos"},
     *     summary="Eliminar módulo",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_modulo"},
     *         @OA\Property(property="id_modulo", type="integer")
     *     )),
     *     @OA\Response(response=200, description="Módulo eliminado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar_modulo(Request $request): JsonResponse
    {
        $id = $request->input('id_modulo');
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'id_modulo requerido', 'result' => 0], 422);
        }
        // Eliminar menús que pertenecen a este módulo (y sus objetos)
        $menuIds = DB::table('sistema_menu')->where('id_modulo', $id)->pluck('id_menu');
        foreach ($menuIds as $menuId) {
            $this->eliminarMenuRecursivo((int) $menuId);
        }
        DB::table('sistema_modulo')->where('id_modulo', $id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Módulo eliminado',
            'result' => 1,
        ]);
    }

    /** Reordena módulos. Recibe items: [ { id_modulo, orden } ] */
    /**
     * @OA\Post(
     *     path="/menu/reordenar_modulos",
     *     tags={"Módulos"},
     *     summary="Reordenar módulos del sistema",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="items", type="array", @OA\Items(
     *             @OA\Property(property="id_modulo", type="integer"),
     *             @OA\Property(property="orden", type="integer")
     *         ))
     *     )),
     *     @OA\Response(response=200, description="Módulos reordenados", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function reordenar_modulos(Request $request): JsonResponse
    {
        $items = $request->input('items', []);
        if (!is_array($items) || count($items) === 0) {
            return response()->json(['success' => false, 'message' => 'items requerido', 'result' => 0], 422);
        }

        try {
            $count = $this->ordenService->reordenarModulos($items);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => 0], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Módulos reordenados',
            'result' => $count,
        ]);
    }

    /** Orden del sidebar del rol de la sesión (valida que el usuario tenga ese rol). */
    public function listar_mi_orden_sidebar(Request $request): JsonResponse
    {
        $idRoles = (int) $request->input('id_roles', 0);
        try {
            $this->assertUsuarioTieneRol(auth()->id(), $idRoles);
            $result = $this->ordenService->listarItemsOrdenSidebar(true, $idRoles);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => []], 422);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => []], $e->getStatusCode());
        }

        return response()->json([
            'success' => true,
            'message' => 'Orden del sidebar (mi sesión)',
            'result' => $result,
        ]);
    }

    /** Guarda orden del sidebar solo para el rol de la sesión del usuario. */
    public function guardar_mi_orden_sidebar(Request $request): JsonResponse
    {
        $idRoles = (int) $request->input('id_roles', 0);
        $items = $request->input('items', []);

        if (!is_array($items) || count($items) === 0) {
            return response()->json(['success' => false, 'message' => 'items requerido', 'result' => 0], 422);
        }

        try {
            $this->assertUsuarioTieneRol(auth()->id(), $idRoles);
            $count = $this->ordenService->reordenarSidebar($items, $idRoles);
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => 0], 422);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => 0], $e->getStatusCode());
        }

        return response()->json([
            'success' => true,
            'message' => 'Orden guardado para su rol',
            'result' => $count,
        ]);
    }

    /** Nombre e ícono del sidebar solo para el rol de la sesión (no altera sistema_menu). */
    public function guardar_mi_etiqueta_sidebar(Request $request): JsonResponse
    {
        $idRoles = (int) $request->input('id_roles', 0);

        try {
            $this->assertUsuarioTieneRol(auth()->id(), $idRoles);
            $result = $this->ordenService->guardarEtiquetaSidebar($idRoles, [
                'tipo' => $request->input('tipo'),
                'id_menu' => $request->input('id_menu'),
                'id_modulo' => $request->input('id_modulo'),
                'nombre' => $request->input('nombre'),
                'icon' => $request->input('icon'),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => null], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => null], 422);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => null], $e->getStatusCode());
        }

        return response()->json([
            'success' => true,
            'message' => 'Nombre del menú guardado para su rol',
            'result' => $result,
        ]);
    }

    /** Nombre e ícono del sidebar por rol (administración). */
    public function guardar_etiqueta_sidebar(Request $request): JsonResponse
    {
        $idRoles = (int) $request->input('id_roles', 0);

        if ($idRoles < 1) {
            return response()->json(['success' => false, 'message' => 'id_roles requerido', 'result' => null], 422);
        }

        try {
            $result = $this->ordenService->guardarEtiquetaSidebar($idRoles, [
                'tipo' => $request->input('tipo'),
                'id_menu' => $request->input('id_menu'),
                'id_modulo' => $request->input('id_modulo'),
                'nombre' => $request->input('nombre'),
                'icon' => $request->input('icon'),
            ], true);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => null], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => null], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Nombre del menú actualizado',
            'result' => $result,
        ]);
    }

    /** Lista plana para el lienzo de orden del sidebar por rol (?id_roles=). Administración. */
    public function listar_orden_sidebar(Request $request): JsonResponse
    {
        $idRoles = (int) $request->input('id_roles', 0);
        if ($idRoles < 1) {
            return response()->json(['success' => false, 'message' => 'id_roles requerido', 'result' => []], 422);
        }

        try {
            $result = $this->ordenService->listarItemsOrdenSidebar(true, $idRoles);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => []], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Orden del sidebar por rol',
            'result' => $result,
        ]);
    }

    /** Guarda orden del sidebar por rol: id_roles + items [ { tipo, id_menu?, id_modulo?, orden } ] */
    public function reordenar_sidebar(Request $request): JsonResponse
    {
        $idRoles = (int) $request->input('id_roles', 0);
        $items = $request->input('items', []);

        if ($idRoles < 1) {
            return response()->json(['success' => false, 'message' => 'id_roles requerido', 'result' => 0], 422);
        }
        if (!is_array($items) || count($items) === 0) {
            return response()->json(['success' => false, 'message' => 'items requerido', 'result' => 0], 422);
        }

        try {
            $count = $this->ordenService->reordenarSidebar($items, $idRoles);
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => 0], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Orden del sidebar guardado para el rol',
            'result' => $count,
        ]);
    }

    /** Favoritos del menú lateral por usuario y rol activo (tabla seguridad_usuario_menu_favorito). */
    public function listar_favoritos(Request $request): JsonResponse
    {
        try {
            $idRoles = $this->resolveIdRolesFavoritos($request);
            $result = $this->favoritosService->listar((int) auth()->id(), $idRoles);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => []], 422);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => []], $e->getStatusCode());
        }

        return response()->json([
            'success' => true,
            'message' => 'Favoritos del menú (por rol)',
            'result' => $result,
        ]);
    }

    public function toggle_favorito(Request $request): JsonResponse
    {
        try {
            $idRoles = $this->resolveIdRolesFavoritos($request);
            $result = $this->favoritosService->toggle((int) auth()->id(), $idRoles, [
                'path' => $request->input('path'),
                'nombre' => $request->input('nombre'),
                'icon' => $request->input('icon'),
                'id_menu' => $request->input('id_menu'),
                'id_modulo' => $request->input('id_modulo'),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => null], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => null], 422);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => null], $e->getStatusCode());
        }

        return response()->json([
            'success' => true,
            'message' => $result['accion'] === 'agregado' ? 'Agregado a favoritos' : 'Quitado de favoritos',
            'result' => $result,
        ]);
    }

    public function registrar_visita_favorito(Request $request): JsonResponse
    {
        try {
            $idRoles = $this->resolveIdRolesFavoritos($request);
            $this->favoritosService->registrarVisita(
                (int) auth()->id(),
                $idRoles,
                (string) $request->input('path', ''),
                $request->input('nombre')
            );
        } catch (\InvalidArgumentException|\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => 0], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Visita registrada',
            'result' => 1,
        ]);
    }

    /** GET /api/menu/historial_sessiones/listar — top menús más visitados del usuario. */
    public function listar_historial_sessiones(Request $request): JsonResponse
    {
        $limit = (int) $request->input('limit', MenuHistorialSessionesService::MAX_CHIPS);
        $items = $this->historialSessionesService->listarTop((int) auth()->id(), $limit);

        return response()->json([
            'success' => true,
            'message' => 'Historial de menús por sesión de usuario',
            'result' => $items,
        ]);
    }

    /** POST /api/menu/historial_sessiones/registrar — registra visita y devuelve top chips. */
    public function registrar_historial_sessiones(Request $request): JsonResponse
    {
        $result = $this->historialSessionesService->registrar(
            (int) auth()->id(),
            (string) $request->input('path', ''),
            $request->input('titulo'),
            $request->input('icon')
        );

        return response()->json([
            'success' => true,
            'message' => $result['registered'] ? 'Visita registrada' : 'Sin registro',
            'result' => $result['items'],
        ]);
    }

    /** POST /api/menu/historial_sessiones/eliminar — borra una ruta (path) o todo el historial del usuario. */
    public function eliminar_historial_sessiones(Request $request): JsonResponse
    {
        $path = $request->input('path');
        $items = $this->historialSessionesService->eliminar(
            (int) auth()->id(),
            is_string($path) ? $path : null
        );

        return response()->json([
            'success' => true,
            'message' => is_string($path) && $path !== '' ? 'Menú eliminado del historial' : 'Historial eliminado',
            'result' => $items,
        ]);
    }

    private function resolveIdRolesFavoritos(Request $request): int
    {
        $idRoles = (int) $request->input('id_roles', 0);
        if ($idRoles < 1) {
            throw new \InvalidArgumentException('id_roles requerido');
        }
        $this->assertUsuarioTieneRol((int) auth()->id(), $idRoles);

        return $idRoles;
    }

    /** Sidebar: estructura Módulo → Menús multinivel usando id_menu_padre. Menús sin módulo aparecen como ítems de nivel superior. */
    /**
     * @OA\Get(
     *     path="/menu/listar_sidebar",
     *     tags={"Menú"},
     *     summary="Obtener estructura de menús para el sidebar",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Estructura de menús")
     * )
     */
    public function listar_sidebar(Request $request): JsonResponse
    {
        $hasParent = Schema::hasColumn('sistema_menu', 'id_menu_padre');
        $hasOrden = Schema::hasColumn('sistema_menu', 'orden');
        $hasOrdenSidebar = Schema::hasColumn('sistema_menu', 'orden_sidebar')
            && Schema::hasColumn('sistema_modulo', 'orden_sidebar');

        $cols = ['id_menu', 'id_modulo', 'nombre', 'url', 'Icon as icon'];
        if ($hasParent) {
            $cols[] = 'id_menu_padre';
        }
        if ($hasOrden) {
            $cols[] = 'orden';
        }
        if ($hasOrdenSidebar) {
            $cols[] = 'orden_sidebar';
        }

        $menus = DB::table('sistema_menu')
            ->where('Activo', 'S')
            ->orderBy($hasOrden ? 'orden' : 'id_menu')
            ->get($cols);

        $hasModUrl = Schema::hasColumn('sistema_modulo', 'url');
        $hasModOrden = Schema::hasColumn('sistema_modulo', 'orden');
        $modCols = ['id_modulo', 'nombre', 'Icon as icon'];
        if ($hasModUrl) {
            $modCols[] = 'url';
        }
        if ($hasModOrden) {
            $modCols[] = 'orden';
        }
        if ($hasOrdenSidebar) {
            $modCols[] = 'orden_sidebar';
        }
        $modulos = DB::table('sistema_modulo')
            ->where('Activo', 'S')
            ->orderBy($hasModOrden ? 'orden' : 'id_modulo')
            ->get($modCols);

        $idRoles = (int) $request->input('id_roles', 0);
        $roots = [];

        // Menús sin módulo aparecen como ítems de nivel superior (enlaces directos o carpetas)
        $sinModulo = $menus->filter(fn ($m) => empty($m->id_modulo));
        if ($sinModulo->count() > 0) {
            if ($hasParent) {
                $topLevel = $this->buildSidebarTree($sinModulo, null, $hasOrden);
                if ($idRoles > 0) {
                    $topLevel = $this->ordenService->filterSidebarTreeNodesForRole($topLevel, $idRoles);
                }
            } else {
                $topLevel = $sinModulo->values()->map(fn ($m) => [
                    'name' => $m->nombre,
                    'path' => $m->url ?? '',
                    'id_menu' => $m->id_menu,
                    'icon' => $m->icon ?? null,
                ])->toArray();
            }
            foreach ($topLevel as $item) {
                $idMenu = $item['id_menu'] ?? null;
                $menuRow = $idMenu ? $sinModulo->firstWhere('id_menu', $idMenu) : null;
                $roots[] = [
                    'node' => $item,
                    'orden_sidebar' => $hasOrdenSidebar && $menuRow ? ($menuRow->orden_sidebar ?? 99999) : 0,
                ];
            }
        }

        // Módulos con sus menús en árbol multinivel
        foreach ($modulos as $mod) {
            $moduloMenus = $menus->where('id_modulo', $mod->id_modulo);

            if ($hasParent) {
                $children = $this->buildSidebarTree($moduloMenus, null, $hasOrden);
            } else {
                $children = $moduloMenus->values()->map(fn ($m) => [
                    'name' => $m->nombre,
                    'path' => $m->url ?? '',
                    'id_menu' => $m->id_menu,
                    'icon' => $m->icon ?? null,
                ])->toArray();
            }

            if ($idRoles > 0 && count($children) > 0) {
                $children = $this->ordenService->filterSidebarTreeNodesForRole($children, $idRoles);
            }

            if (count($children) === 0) {
                $modUrl = $hasModUrl ? ($mod->url ?? null) : null;
                $roots[] = [
                    'node' => [
                        'name' => $mod->nombre,
                        'icon' => $mod->icon ?? 'folder',
                        'id_modulo' => $mod->id_modulo,
                        'path' => $modUrl ?? '',
                    ],
                    'orden_sidebar' => $hasOrdenSidebar ? ($mod->orden_sidebar ?? 99999) : 0,
                ];
                continue;
            }

            $roots[] = [
                'node' => [
                    'name' => $mod->nombre,
                    'icon' => $mod->icon ?? 'folder',
                    'id_menu' => $mod->id_modulo,
                    'children' => $children,
                ],
                'orden_sidebar' => $hasOrdenSidebar ? ($mod->orden_sidebar ?? 99999) : 0,
            ];
        }

        if ($idRoles > 0) {
            $roots = array_values(array_filter(
                $roots,
                fn ($r) => $this->ordenService->rootVisibleForRole($r, $idRoles)
            ));
            usort(
                $roots,
                fn ($a, $b) => $this->ordenService->resolveRootSortKey($a, $idRoles)
                    <=> $this->ordenService->resolveRootSortKey($b, $idRoles)
            );
            $roots = $this->ordenService->applyEtiquetasToSidebarRoots($roots, $idRoles);
        } elseif ($hasOrdenSidebar) {
            usort($roots, fn ($a, $b) => ($a['orden_sidebar'] ?? 99999) <=> ($b['orden_sidebar'] ?? 99999));
        }

        $tree = array_map(fn ($r) => $r['node'], $roots);

        return response()->json([
            'success' => true,
            'message' => 'Menús para sidebar',
            'result' => $tree,
        ]);
    }

    /**
     * Construye árbol para sidebar usando id_menu_padre.
     * Solo incluye 'children' cuando hay hijos reales (para que el frontend distinga enlace vs carpeta).
     */
    private function buildSidebarTree($menus, $parentId, $useOrden, $depth = 0): array
    {
        if ($depth > 10) return []; // Protección contra referencias circulares

        if ($parentId === null) {
            $filtered = $menus->filter(fn ($m) => empty($m->id_menu_padre));
        } else {
            $filtered = $menus->where('id_menu_padre', $parentId);
        }

        if ($useOrden) {
            $filtered = $filtered->sortBy('orden')->values();
        } else {
            $filtered = $filtered->sortBy('id_menu')->values();
        }

        $nodes = [];
        foreach ($filtered as $m) {
            $childNodes = $this->buildSidebarTree($menus, $m->id_menu, $useOrden, $depth + 1);
            $node = [
                'name' => $m->nombre,
                'path' => $m->url ?? '',
                'id_menu' => $m->id_menu,
                'icon' => $m->icon ?? null,
            ];
            if (count($childNodes) > 0) {
                $node['children'] = $childNodes;
            }
            $nodes[] = $node;
        }
        return $nodes;
    }

    private function buildMenuTree($menus, $idPadre, $useOrden): array
    {
        $filtered = $menus->where('id_menu_padre', $idPadre);
        if ($useOrden) {
            $filtered = $filtered->sortBy('orden')->values();
        } else {
            $filtered = $filtered->sortBy('id_menu')->values();
        }
        $nodes = [];
        foreach ($filtered as $m) {
            $children = $this->buildMenuTree($menus, $m->id_menu, $useOrden);
            $nodes[] = [
                'name' => $m->nombre,
                'path' => $m->url ?? '',
                'id_menu' => $m->id_menu,
                'icon' => $m->icon ?? 'folder',
                'children' => $children,
            ];
        }
        return $nodes;
    }

    /**
     * @OA\Post(
     *     path="/menu/crear",
     *     tags={"Menú"},
     *     summary="Crear nuevo menú",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"nombre"},
     *         @OA\Property(property="nombre", type="string"),
     *         @OA\Property(property="url", type="string"),
     *         @OA\Property(property="id_modulo", type="integer"),
     *         @OA\Property(property="id_menu_padre", type="integer"),
     *         @OA\Property(property="Icon", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Menú creado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request): JsonResponse
    {
        $idRoles = $this->resolveIdRoles($request);
        if ($idRoles > 0) $this->assertPuedeActivarMenu($request, $idRoles);
        $data = [
            'id_modulo' => $request->id_modulo ?? null,
            'nombre' => $request->nombre,
            'url' => $request->url ?? null,
            'Activo' => $request->Activo ?? 'S',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('sistema_menu', 'Icon')) {
            $data['Icon'] = $request->Icon ?? $request->icon ?? null;
        }
        if (Schema::hasColumn('sistema_menu', 'id_menu_padre')) {
            $data['id_menu_padre'] = $request->id_menu_padre ?? null;
        }
        if (Schema::hasColumn('sistema_menu', 'orden')) {
            $maxOrden = (int) DB::table('sistema_menu')
                ->where('id_menu_padre', $request->id_menu_padre ?? null)
                ->max('orden');
            $data['orden'] = $request->orden ?? ($maxOrden + 1);
        }
        $id = DB::table('sistema_menu')->insertGetId($data);

        $idRoles = $this->resolveIdRoles($request);
        if ($idRoles > 0) {
            $this->etiquetaService->syncMenu($idRoles, (int) $id, $request->input('id_etiquetas'));
            if (($data['Activo'] ?? 'S') === 'S') $this->moduloRolService->activarMenuParaRol((int) $id, $idRoles);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registro insertado',
            'result' => ['id_menu' => $id],
        ]);
    }

    /**
     * @OA\Put(
     *     path="/menu/actualizar",
     *     tags={"Menú"},
     *     summary="Actualizar menú existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_menu"},
     *         @OA\Property(property="id_menu", type="integer"),
     *         @OA\Property(property="nombre", type="string"),
     *         @OA\Property(property="url", type="string"),
     *         @OA\Property(property="id_modulo", type="integer"),
     *         @OA\Property(property="id_menu_padre", type="integer"),
     *         @OA\Property(property="Icon", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Menú actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request): JsonResponse
    {
        $idRoles = $this->resolveIdRoles($request);
        $mostrar = $request->boolean('mostrar_en_sidebar') && $request->input('Activo', 'S') === 'S';
        if ($mostrar) {
            abort_if($idRoles < 1, 422, 'id_roles requerido');
            $this->assertPuedeActivarMenu($request, $idRoles);
        }
        $id = $request->input('id_menu');
        $data = [
            'id_modulo' => $request->id_modulo ?? null,
            'nombre' => $request->nombre,
            'url' => $request->url ?? null,
            'Activo' => $request->Activo ?? 'S',
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('sistema_menu', 'Icon')) {
            $data['Icon'] = $request->Icon ?? $request->icon ?? null;
        }
        if (Schema::hasColumn('sistema_menu', 'id_menu_padre')) {
            $data['id_menu_padre'] = $request->id_menu_padre ?? null;
        }
        if (Schema::hasColumn('sistema_menu', 'orden') && $request->has('orden')) {
            $data['orden'] = (int) $request->orden;
        }
        DB::table('sistema_menu')->where('id_menu', $id)->update($data);
        $nombre = trim((string) ($data['nombre'] ?? ''));
        if ($nombre !== '') {
            $this->ordenService->syncNombreSidebarMenu((int) $id, $nombre);
        }

        $idRoles = $this->resolveIdRoles($request);
        if ($idRoles > 0 && $request->has('id_etiquetas')) {
            $this->etiquetaService->syncMenu($idRoles, (int) $id, $request->input('id_etiquetas'));
        }

        if ($mostrar) $this->moduloRolService->activarMenuParaRol((int) $id, $idRoles);

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado',
            'result' => 1,
        ]);
    }

    /** Reordenar / cambiar padre: body { items: [ { id_menu, id_menu_padre, orden } ] } */
    /**
     * @OA\Post(
     *     path="/menu/reordenar",
     *     tags={"Menú"},
     *     summary="Reordenar menús o cambiar de nivel",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="items", type="array", @OA\Items(
     *             @OA\Property(property="id_menu", type="integer"),
     *             @OA\Property(property="id_menu_padre", type="integer"),
     *             @OA\Property(property="orden", type="integer")
     *         ))
     *     )),
     *     @OA\Response(response=200, description="Menús reordenados", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function reordenar(Request $request): JsonResponse
    {
        $items = $request->input('items', []);

        try {
            $count = $this->ordenService->reordenarMenus($items);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'result' => 0,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Orden actualizado',
            'result' => $count,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/menu/eliminar",
     *     tags={"Menú"},
     *     summary="Eliminar menú y sus hijos recursivamente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_menu"},
     *         @OA\Property(property="id_menu", type="integer")
     *     )),
     *     @OA\Response(response=200, description="Menú eliminado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar(Request $request): JsonResponse
    {
        $id = $request->input('id_menu');
        $menu = DB::table('sistema_menu')->where('id_menu', $id)->first();
        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menú no encontrado',
                'result' => 0,
            ], 404);
        }
        $id_modulo = $menu->id_modulo;
        $this->eliminarMenuRecursivo($id);
        $tieneMasMenus = DB::table('sistema_menu')->where('id_modulo', $id_modulo)->exists();
        if (!$tieneMasMenus && $id_modulo !== null) {
            DB::table('sistema_modulo')->where('id_modulo', $id_modulo)->delete();
        }
        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado',
            'result' => 1,
        ]);
    }

    private function eliminarMenuRecursivo(int $id_menu): void
    {
        if (Schema::hasColumn('sistema_menu', 'id_menu_padre')) {
            $hijos = DB::table('sistema_menu')->where('id_menu_padre', $id_menu)->pluck('id_menu');
            foreach ($hijos as $h) {
                $this->eliminarMenuRecursivo((int) $h);
            }
        }
        DB::table('sistema_menu_objetos')->where('id_menu', $id_menu)->delete();
        DB::table('sistema_menu')->where('id_menu', $id_menu)->delete();
    }

    /** Objetos asignados a un menú */
    /**
     * @OA\Get(
     *     path="/menu/listar_objetos_menu",
     *     tags={"Objetos"},
     *     summary="Listar objetos asignados a un menú",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_menu", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lista de objetos del menú", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar_objetos_menu(Request $request): JsonResponse
    {
        $id_menu = $request->input('id_menu');
        $items = DB::table('sistema_menu_objetos as mo')
            ->join('sistema_objetos as o', 'mo.id_objetos', '=', 'o.id_objetos')
            ->where('mo.id_menu', $id_menu)
            ->select('mo.id_menu_objetos', 'mo.id_menu', 'mo.id_objetos', 'mo.orden', 'mo.Activo', 'o.nombre as objeto_nombre')
            ->orderBy('mo.orden')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Objetos del menú',
            'result' => $items,
        ]);
    }

    /** Asignar objeto a menú */
    /**
     * @OA\Post(
     *     path="/menu/asignar_objeto",
     *     tags={"Objetos"},
     *     summary="Asignar un objeto a un menú",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_menu","id_objetos"},
     *         @OA\Property(property="id_menu", type="integer"),
     *         @OA\Property(property="id_objetos", type="integer")
     *     )),
     *     @OA\Response(response=200, description="Objeto asignado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function asignar_objeto(Request $request): JsonResponse
    {
        $id_menu = $request->id_menu;
        $id_objetos = $request->id_objetos;
        $exists = DB::table('sistema_menu_objetos')
            ->where('id_menu', $id_menu)
            ->where('id_objetos', $id_objetos)
            ->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'El objeto ya está asignado a este menú',
                'result' => 0,
            ], 422);
        }
        $id = DB::table('sistema_menu_objetos')->insertGetId([
            'id_menu' => $id_menu,
            'id_objetos' => $id_objetos,
            'Activo' => 'S',
            'orden' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Objeto asignado',
            'result' => ['id_menu_objetos' => $id],
        ]);
    }

    /** Quitar objeto de menú */
    /**
     * @OA\Delete(
     *     path="/menu/quitar_objeto",
     *     tags={"Objetos"},
     *     summary="Quitar un objeto asignado a un menú",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_menu_objetos"},
     *         @OA\Property(property="id_menu_objetos", type="integer")
     *     )),
     *     @OA\Response(response=200, description="Objeto quitado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function quitar_objeto(Request $request): JsonResponse
    {
        $id_menu_objetos = $request->input('id_menu_objetos');
        DB::table('sistema_menu_objetos')->where('id_menu_objetos', $id_menu_objetos)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Objeto quitado',
            'result' => 1,
        ]);
    }

    /** Asegura que la tabla sistema_modulo_objetos exista */
    private function ensureModuloObjetosTable(): void
    {
        if (!Schema::hasTable('sistema_modulo_objetos')) {
            Schema::create('sistema_modulo_objetos', function ($table) {
                $table->increments('id_modulo_objetos');
                $table->unsignedInteger('id_modulo');
                $table->unsignedInteger('id_objetos');
                $table->char('Activo', 1)->default('S');
                $table->integer('orden')->default(0);
                $table->timestamps();
            });
        }
    }

    /** Objetos asignados a un módulo */
    /**
     * @OA\Get(
     *     path="/menu/listar_objetos_modulo",
     *     tags={"Objetos"},
     *     summary="Listar objetos asignados a un módulo",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_modulo", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lista de objetos del módulo", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar_objetos_modulo(Request $request): JsonResponse
    {
        $this->ensureModuloObjetosTable();
        $id_modulo = $request->input('id_modulo');
        $items = DB::table('sistema_modulo_objetos as mo')
            ->join('sistema_objetos as o', 'mo.id_objetos', '=', 'o.id_objetos')
            ->where('mo.id_modulo', $id_modulo)
            ->select('mo.id_modulo_objetos', 'mo.id_modulo', 'mo.id_objetos', 'mo.orden', 'mo.Activo', 'o.nombre as objeto_nombre')
            ->orderBy('mo.orden')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Objetos del módulo',
            'result' => $items,
        ]);
    }

    /** Asignar objeto a módulo */
    /**
     * @OA\Post(
     *     path="/menu/asignar_objeto_modulo",
     *     tags={"Objetos"},
     *     summary="Asignar un objeto a un módulo",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_modulo","id_objetos"},
     *         @OA\Property(property="id_modulo", type="integer"),
     *         @OA\Property(property="id_objetos", type="integer")
     *     )),
     *     @OA\Response(response=200, description="Objeto asignado al módulo", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function asignar_objeto_modulo(Request $request): JsonResponse
    {
        $this->ensureModuloObjetosTable();
        $id_modulo = $request->input('id_modulo');
        $id_objetos = $request->input('id_objetos');
        $exists = DB::table('sistema_modulo_objetos')
            ->where('id_modulo', $id_modulo)
            ->where('id_objetos', $id_objetos)
            ->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'El objeto ya está asignado a este módulo',
                'result' => 0,
            ], 422);
        }
        $id = DB::table('sistema_modulo_objetos')->insertGetId([
            'id_modulo' => $id_modulo,
            'id_objetos' => $id_objetos,
            'Activo' => 'S',
            'orden' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Objeto asignado al módulo',
            'result' => ['id_modulo_objetos' => $id],
        ]);
    }

    /** Quitar objeto de módulo */
    /**
     * @OA\Delete(
     *     path="/menu/quitar_objeto_modulo",
     *     tags={"Objetos"},
     *     summary="Quitar un objeto asignado a un módulo",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_modulo_objetos"},
     *         @OA\Property(property="id_modulo_objetos", type="integer")
     *     )),
     *     @OA\Response(response=200, description="Objeto quitado del módulo", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function quitar_objeto_modulo(Request $request): JsonResponse
    {
        $this->ensureModuloObjetosTable();
        $id_modulo_objetos = $request->input('id_modulo_objetos');
        DB::table('sistema_modulo_objetos')->where('id_modulo_objetos', $id_modulo_objetos)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Objeto quitado del módulo',
            'result' => 1,
        ]);
    }

    private function assertPuedeActivarMenu(Request $request, int $idRoles): void
    {
        $userId = $request->user()?->id_usuario ?? $request->user()?->id ?? null;
        $this->assertUsuarioTieneRol($userId !== null ? (int) $userId : null, $idRoles);
        abort_unless($this->moduloRolService->puedeGestionarModulos($userId !== null ? (int) $userId : null, $idRoles), 403, 'Su rol no puede activar opciones del menú');
    }

    /** El usuario autenticado debe tener asignado el rol (perfil + rol en sesión). */
    private function assertUsuarioTieneRol(?int $userId, int $idRoles): void
    {
        if ($idRoles < 1) {
            throw new \InvalidArgumentException('id_roles requerido');
        }

        if ($userId === null) {
            abort(401, 'No autenticado');
        }

        if ((int) $userId === 1) {
            return;
        }

        $tieneRol = DB::table('seguridad_perfil_users as SPU')
            ->join('seguridad_roles_perfil as SRP', 'SRP.id_perfil', '=', 'SPU.id_perfil')
            ->where('SPU.id_usuario', $userId)
            ->where('SRP.id_roles', $idRoles)
            ->exists();

        if (!$tieneRol) {
            abort(403, 'No tiene permiso para ordenar menús con ese rol');
        }
    }

    /** Contexto de módulo/menú para la ruta actual (pestaña del admin). */
    public function resolver_modulo_ruta(Request $request): JsonResponse
    {
        $path = (string) $request->input('path', '');
        $idRoles = $this->resolveIdRoles($request);
        if ($idRoles < 1) {
            $idRoles = (int) ($request->user()?->id_roles ?? 0);
        }

        $userId = $request->user()?->id_usuario ?? $request->user()?->id ?? null;
        $ctx = $this->moduloRolService->resolverPorRuta($path, $idRoles);

        if ($ctx) {
            $ctx['puede_desactivar'] = $this->moduloRolService->puedeGestionarModulos(
                $userId !== null ? (int) $userId : null,
                $idRoles
            );
            $ctx['id_roles'] = $idRoles;
        }

        return response()->json([
            'success' => true,
            'message' => 'Contexto de ruta',
            'result'  => $ctx,
        ]);
    }

    /** Oculta módulo o menú para el rol indicado (sidebar + gestión inactivos por rol). */
    public function desactivar_modulo_rol(Request $request): JsonResponse
    {
        $idRoles = $this->resolveIdRoles($request);
        if ($idRoles < 1) {
            return response()->json(['success' => false, 'message' => 'id_roles requerido', 'result' => 0], 422);
        }

        $userId = $request->user()?->id_usuario ?? $request->user()?->id ?? null;
        try {
            $this->assertUsuarioTieneRol($userId !== null ? (int) $userId : null, $idRoles);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => 0], 403);
        }

        if (!$this->moduloRolService->puedeGestionarModulos($userId !== null ? (int) $userId : null, $idRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Su rol no puede desactivar módulos del menú',
                'result'  => 0,
            ], 403);
        }

        try {
            if ($request->filled('id_modulo')) {
                $this->moduloRolService->desactivarModuloParaRol((int) $request->id_modulo, $idRoles);
            } elseif ($request->filled('id_menu')) {
                $this->moduloRolService->desactivarMenuParaRol((int) $request->id_menu, $idRoles);
            } else {
                return response()->json(['success' => false, 'message' => 'id_modulo o id_menu requerido', 'result' => 0], 422);
            }
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'result' => 0], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Módulo desactivado para este rol',
            'result'  => 1,
        ]);
    }

    /**
     * Rutas registradas en Laravel (web + API) para el buscador de URLs del admin.
     */
    public function listarRutasSistema(): JsonResponse
    {
        $rutas = [];
        $vistos = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if ($this->debeOmitirRutaSistema($uri)) {
                continue;
            }

            $methods = array_values(array_filter(
                $route->methods(),
                static fn (string $m) => !in_array($m, ['HEAD', 'OPTIONS'], true)
            ));
            if ($methods === []) {
                continue;
            }

            $grupo = str_starts_with($uri, 'api/') ? 'api' : 'web';
            $url = '/' . ltrim($uri, '/');

            foreach ($methods as $method) {
                $key = strtoupper($method) . '|' . $url;
                if (isset($vistos[$key])) {
                    continue;
                }
                $vistos[$key] = true;
                $rutas[] = [
                    'url' => $url,
                    'method' => $method,
                    'nombre_ruta' => $route->getName() ?? '',
                    'grupo' => $grupo,
                ];
            }
        }

        usort($rutas, static function (array $a, array $b) {
            $cmp = strcmp($a['grupo'], $b['grupo']);
            if ($cmp !== 0) {
                return $cmp;
            }
            $cmp = strcmp($a['url'], $b['url']);
            return $cmp !== 0 ? $cmp : strcmp($a['method'], $b['method']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Rutas del sistema',
            'result' => $rutas,
        ]);
    }

    private function debeOmitirRutaSistema(string $uri): bool
    {
        $omit = [
            'sanctum/',
            '_ignition/',
            'livewire/',
            'telescope/',
            'horizon/',
            'broadcasting/',
            'storage/',
        ];
        foreach ($omit as $pref) {
            if (str_starts_with($uri, $pref)) {
                return true;
            }
        }
        if (str_contains($uri, '{') && preg_match('/\{[a-zA-Z_]+\}/', $uri) === 1) {
            // Rutas con muchos parámetros dinámicos (catálogo legacy); mantener las estáticas.
            if (preg_match('#\{[a-zA-Z_]+\}/\{[a-zA-Z_]+\}#', $uri) === 1) {
                return true;
            }
        }
        return false;
    }
}
