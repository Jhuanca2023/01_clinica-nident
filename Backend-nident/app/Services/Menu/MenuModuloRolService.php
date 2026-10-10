<?php

namespace App\Services\Menu;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MenuModuloRolService
{
    public function tablaInactivoLista(): bool
    {
        return Schema::hasTable('seguridad_roles_modulo_inactivo');
    }

    /** @return int[] */
    public function modulosInactivosParaRol(int $idRoles): array
    {
        if ($idRoles < 1 || !$this->tablaInactivoLista()) {
            return [];
        }

        return DB::table('seguridad_roles_modulo_inactivo')
            ->where('id_roles', $idRoles)
            ->pluck('id_modulo')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function moduloOcultoParaRol(int $idModulo, int $idRoles): bool
    {
        if ($idModulo < 1 || $idRoles < 1) {
            return false;
        }

        return in_array($idModulo, $this->modulosInactivosParaRol($idRoles), true);
    }

    public function normalizarRutaAdmin(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '/';
        }
        $path = '/' . ltrim($path, '/');
        $path = preg_replace('#^/admin#i', '', $path) ?: '/';

        return rtrim($path, '/') ?: '/';
    }

    /**
     * Resuelve el ítem más específico (menú hijo o módulo raíz) para una ruta del admin.
     *
     * @return array<string, mixed>|null
     */
    public function resolverPorRuta(string $path, int $idRoles): ?array
    {
        if (!Schema::hasTable('sistema_menu')) {
            return null;
        }

        $path = $this->normalizarRutaAdmin($path);
        if ($path === '/' || $path === '/dashboard/default') {
            return null;
        }

        $menu = $this->buscarMenuPorRutaExacta($path);
        if ($menu) {
            return $this->contextoMenu($menu, $idRoles);
        }

        $hasModUrl = Schema::hasColumn('sistema_modulo', 'url');
        if ($hasModUrl && Schema::hasTable('sistema_modulo')) {
            $modulo = DB::table('sistema_modulo')
                ->where('Activo', 'S')
                ->where('url', $path)
                ->first();
            if ($modulo) {
                return $this->contextoModulo($modulo, $idRoles);
            }
        }

        $menu = $this->buscarMenuPorPrefijoRuta($path);
        if ($menu) {
            return $this->contextoMenu($menu, $idRoles);
        }

        return null;
    }

    private function buscarMenuPorRutaExacta(string $path): ?object
    {
        $candidates = [$path, ltrim($path, '/')];
        if (!str_starts_with($path, '/')) {
            $candidates[] = '/' . $path;
        }

        return DB::table('sistema_menu')
            ->where('Activo', 'S')
            ->whereIn('url', array_values(array_unique(array_filter($candidates))))
            ->first();
    }

    private function buscarMenuPorPrefijoRuta(string $path): ?object
    {
        $menus = DB::table('sistema_menu')
            ->where('Activo', 'S')
            ->whereNotNull('url')
            ->where('url', '!=', '')
            ->orderByRaw('CHAR_LENGTH(url) DESC')
            ->get(['id_menu', 'id_modulo', 'nombre', 'url', 'Activo']);

        foreach ($menus as $m) {
            $u = $this->normalizarRutaAdmin($m->url);
            if ($u !== '/' && ($path === $u || str_starts_with($path, $u . '/'))) {
                return $m;
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function contextoMenu(object $menu, int $idRoles): array
    {
        $idMenu = (int) $menu->id_menu;
        $permitidos = $idRoles > 0 ? array_flip($this->menuIdsPermitidosParaRol($idRoles)) : [];

        return [
            'tipo'            => 'menu',
            'id_menu'         => $idMenu,
            'id_modulo'       => !empty($menu->id_modulo) ? (int) $menu->id_modulo : null,
            'nombre'          => $menu->nombre ?? '',
            'activo_global'   => ($menu->Activo ?? 'S') === 'S',
            'oculto_para_rol' => $idRoles > 0 && !isset($permitidos[$idMenu]),
        ];
    }

    /** @return array<string, mixed> */
    private function contextoModulo(object $modulo, int $idRoles): array
    {
        $idModulo = (int) $modulo->id_modulo;

        return [
            'tipo'            => 'modulo',
            'id_modulo'       => $idModulo,
            'id_menu'         => null,
            'nombre'          => $modulo->nombre ?? '',
            'activo_global'   => ($modulo->Activo ?? 'S') === 'S',
            'oculto_para_rol' => $this->moduloOcultoParaRol($idModulo, $idRoles),
        ];
    }

    /** @return int[] */
    private function menuIdsPermitidosParaRol(int $idRoles): array
    {
        if ($idRoles < 1 || !Schema::hasTable('seguridad_roles_menu')) {
            return [];
        }

        return DB::table('seguridad_roles_menu')
            ->where('id_roles', $idRoles)
            ->pluck('id_menu')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function desactivarModuloParaRol(int $idModulo, int $idRoles): void
    {
        if ($idModulo < 1 || $idRoles < 1 || !$this->tablaInactivoLista()) {
            throw new \InvalidArgumentException('Parámetros inválidos');
        }

        if (!DB::table('sistema_modulo')->where('id_modulo', $idModulo)->exists()) {
            throw new \InvalidArgumentException('Módulo no encontrado');
        }

        $exists = DB::table('seguridad_roles_modulo_inactivo')
            ->where('id_roles', $idRoles)
            ->where('id_modulo', $idModulo)
            ->exists();

        if (!$exists) {
            DB::table('seguridad_roles_modulo_inactivo')->insert([
                'id_roles'    => $idRoles,
                'id_modulo'   => $idModulo,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $this->quitarPermisosMenusDelModuloParaRol($idModulo, $idRoles);
    }

    public function reactivarModuloParaRol(int $idModulo, int $idRoles): void
    {
        if ($idModulo < 1 || $idRoles < 1) {
            return;
        }

        if ($this->tablaInactivoLista()) {
            DB::table('seguridad_roles_modulo_inactivo')
                ->where('id_roles', $idRoles)
                ->where('id_modulo', $idModulo)
                ->delete();
        }

        if (Schema::hasTable('sistema_modulo') && Schema::hasColumn('sistema_modulo', 'Activo')) {
            DB::table('sistema_modulo')
                ->where('id_modulo', $idModulo)
                ->update(['Activo' => 'S', 'updated_at' => now()]);
        }
    }

    /** Hace visible el menú y sus padres solamente para el rol indicado. */
    public function activarMenuParaRol(int $idMenu, int $idRoles): void
    {
        if ($idRoles < 1 || !DB::table('seguridad_roles')->where('id_roles', $idRoles)->exists()) {
            throw new \InvalidArgumentException('Rol no encontrado');
        }
        $visited = [];
        while ($idMenu > 0 && !isset($visited[$idMenu])) {
            $visited[$idMenu] = true;
            $menu = DB::table('sistema_menu')->where('id_menu', $idMenu)->first();
            if (!$menu) throw new \InvalidArgumentException('Menú no encontrado');
            if (!DB::table('seguridad_roles_menu')->where('id_roles', $idRoles)->where('id_menu', $idMenu)->exists()) {
                DB::table('seguridad_roles_menu')->insert(['id_roles' => $idRoles, 'id_menu' => $idMenu]);
            }
            $idMenu = (int) ($menu->id_menu_padre ?? 0);
        }
    }

    public function desactivarMenuParaRol(int $idMenu, int $idRoles): void
    {
        if ($idMenu < 1 || $idRoles < 1) {
            throw new \InvalidArgumentException('Parámetros inválidos');
        }

        if (!DB::table('sistema_menu')->where('id_menu', $idMenu)->exists()) {
            throw new \InvalidArgumentException('Menú no encontrado');
        }

        $ids = $this->collectMenuIdsConDescendientes($idMenu);

        if (Schema::hasTable('seguridad_roles_menu')) {
            DB::table('seguridad_roles_menu')
                ->where('id_roles', $idRoles)
                ->whereIn('id_menu', $ids)
                ->delete();
        }
    }

    /**
     * IDs del menú y todos sus hijos (recursivo por id_menu_padre).
     *
     * @return int[]
     */
    public function collectMenuIdsConDescendientes(int $idMenu): array
    {
        if ($idMenu < 1 || !Schema::hasTable('sistema_menu')) {
            return [];
        }

        $all = [$idMenu];
        if (!Schema::hasColumn('sistema_menu', 'id_menu_padre')) {
            return $all;
        }

        $pending = [$idMenu];
        while (!empty($pending)) {
            $children = DB::table('sistema_menu')
                ->whereIn('id_menu_padre', $pending)
                ->pluck('id_menu')
                ->map(fn ($id) => (int) $id)
                ->all();
            $new = array_values(array_diff($children, $all));
            $all = array_merge($all, $new);
            $pending = $new;
        }

        return array_values(array_unique($all));
    }

    private function quitarPermisosMenusDelModuloParaRol(int $idModulo, int $idRoles): void
    {
        if (!Schema::hasTable('seguridad_roles_menu') || !Schema::hasTable('sistema_menu')) {
            return;
        }

        $menuIds = DB::table('sistema_menu')
            ->where('id_modulo', $idModulo)
            ->pluck('id_menu')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($menuIds === []) {
            return;
        }

        DB::table('seguridad_roles_menu')
            ->where('id_roles', $idRoles)
            ->whereIn('id_menu', $menuIds)
            ->delete();
    }

    public function puedeGestionarModulos(?int $userId, int $idRoles): bool
    {
        if ($userId !== null && (int) $userId === 1) {
            return true;
        }

        if ($idRoles < 1) {
            return false;
        }

        if (!Schema::hasTable('sistema_menu') || !Schema::hasTable('seguridad_roles_menu')) {
            return false;
        }

        $idMenuGestion = DB::table('sistema_menu')
            ->where('url', '/menu/index')
            ->orWhere('url', 'menu/index')
            ->value('id_menu');

        if (!$idMenuGestion) {
            return (int) $userId === 1;
        }

        return DB::table('seguridad_roles_menu')
            ->where('id_roles', $idRoles)
            ->where('id_menu', $idMenuGestion)
            ->exists();
    }
}
