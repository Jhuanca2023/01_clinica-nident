<?php

namespace App\Services\Menu;

use App\Models\SeguridadRolesSidebarOrden;
use App\Models\SistemaMenu;
use App\Models\SistemaModulo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SistemaMenuOrdenService
{
    public function hasOrdenSidebar(): bool
    {
        return Schema::hasColumn('sistema_menu', 'orden_sidebar')
            && Schema::hasColumn('sistema_modulo', 'orden_sidebar');
    }

    public function hasRolesSidebarOrdenTable(): bool
    {
        return Schema::hasTable('seguridad_roles_sidebar_orden');
    }

    public function hasMenuOrden(): bool
    {
        return Schema::hasColumn('sistema_menu', 'id_menu_padre')
            && Schema::hasColumn('sistema_menu', 'orden');
    }

    /**
     * Lista plana de ítems de primer nivel del sidebar para un rol.
     */
    public function listarItemsOrdenSidebar(bool $soloActivos, int $idRoles): array
    {
        if ($idRoles < 1) {
            throw new \InvalidArgumentException('id_roles requerido');
        }

        $items = $this->buildSidebarRootItems($soloActivos);
        $items = $this->filterItemsForRole($items, $idRoles);
        $roleOrden = $this->getOrdenMapForRole($idRoles);

        $etiquetas = $this->getEtiquetasMapForRole($idRoles);

        return $items
            ->sortBy(fn (array $row) => $this->resolveSortKey($row, $roleOrden))
            ->values()
            ->map(fn (array $row, int $index) => $this->applyEtiquetasToRow(array_merge($row, [
                'orden' => $index,
                'id_roles' => $idRoles,
            ]), $etiquetas))
            ->all();
    }

    /**
     * Guarda el orden del sidebar solo para el rol indicado.
     *
     * @param  array<int, array{tipo: string, id_menu?: int, id_modulo?: int, orden?: int}>  $items
     */
    public function reordenarSidebar(array $items, int $idRoles): int
    {
        if ($idRoles < 1) {
            throw new \InvalidArgumentException('id_roles requerido');
        }

        if (!$this->hasRolesSidebarOrdenTable()) {
            throw new \RuntimeException('Ejecute las migraciones: php artisan migrate');
        }

        $etiquetas = $this->getEtiquetasMapForRole($idRoles);

        DB::transaction(function () use ($items, $idRoles, $etiquetas) {
            SeguridadRolesSidebarOrden::query()->paraRol($idRoles)->delete();

            foreach ($items as $index => $row) {
                $tipo = $row['tipo'] ?? '';
                $orden = (int) ($row['orden'] ?? $index);
                $key = $this->rowToEtiquetaKey($row);
                $labels = $etiquetas[$key] ?? [];
                $nombreSidebar = $row['nombre_sidebar'] ?? $labels['nombre'] ?? null;
                $iconSidebar = $row['icon_sidebar'] ?? $labels['icon'] ?? null;

                if ($tipo === 'modulo' && !empty($row['id_modulo'])) {
                    SeguridadRolesSidebarOrden::create([
                        'id_roles' => $idRoles,
                        'tipo' => 'modulo',
                        'id_modulo' => (int) $row['id_modulo'],
                        'id_menu' => null,
                        'orden' => $orden,
                        'nombre_sidebar' => $nombreSidebar,
                        'icon_sidebar' => $iconSidebar,
                    ]);
                    continue;
                }

                if ($tipo === 'menu' && !empty($row['id_menu'])) {
                    SeguridadRolesSidebarOrden::create([
                        'id_roles' => $idRoles,
                        'tipo' => 'menu',
                        'id_menu' => (int) $row['id_menu'],
                        'id_modulo' => null,
                        'orden' => $orden,
                        'nombre_sidebar' => $nombreSidebar,
                        'icon_sidebar' => $iconSidebar,
                    ]);
                }
            }
        });

        return count($items);
    }

    /**
     * Guarda nombre e ícono personalizados del sidebar solo para un rol (no modifica sistema_menu).
     *
     * @param  array{tipo: string, id_menu?: int, id_modulo?: int, nombre: string, icon?: string|null}  $data
     * @param  bool  $syncGlobal  Si true, también actualiza sistema_modulo/sistema_menu (pestaña Activos).
     */
    public function guardarEtiquetaSidebar(int $idRoles, array $data, bool $syncGlobal = false): array
    {
        if ($idRoles < 1) {
            throw new \InvalidArgumentException('id_roles requerido');
        }

        if (!$this->hasRolesSidebarOrdenTable()) {
            throw new \RuntimeException('Ejecute las migraciones: php artisan migrate');
        }

        $tipo = $data['tipo'] ?? '';
        $nombre = trim((string) ($data['nombre'] ?? ''));
        $icon = trim((string) ($data['icon'] ?? ''));
        $icon = $icon !== '' ? $icon : null;

        if ($nombre === '') {
            throw new \InvalidArgumentException('El nombre es requerido');
        }

        if ($tipo === 'modulo') {
            $idModulo = (int) ($data['id_modulo'] ?? 0);
            if ($idModulo < 1) {
                throw new \InvalidArgumentException('id_modulo requerido');
            }
            $query = SeguridadRolesSidebarOrden::query()
                ->paraRol($idRoles)
                ->where('tipo', 'modulo')
                ->where('id_modulo', $idModulo);
        } elseif ($tipo === 'menu') {
            $idMenu = (int) ($data['id_menu'] ?? 0);
            if ($idMenu < 1) {
                throw new \InvalidArgumentException('id_menu requerido');
            }
            $query = SeguridadRolesSidebarOrden::query()
                ->paraRol($idRoles)
                ->where('tipo', 'menu')
                ->where('id_menu', $idMenu);
        } else {
            throw new \InvalidArgumentException('tipo inválido');
        }

        $row = $query->first();

        if ($row) {
            $row->update([
                'nombre_sidebar' => $nombre,
                'icon_sidebar' => $icon,
            ]);
        } else {
            $maxOrden = (int) (SeguridadRolesSidebarOrden::query()->paraRol($idRoles)->max('orden') ?? -1);
            $attrs = [
                'id_roles' => $idRoles,
                'tipo' => $tipo,
                'orden' => $maxOrden + 1,
                'nombre_sidebar' => $nombre,
                'icon_sidebar' => $icon,
            ];
            if ($tipo === 'modulo') {
                $attrs['id_modulo'] = (int) $data['id_modulo'];
                $attrs['id_menu'] = null;
            } else {
                $attrs['id_menu'] = (int) $data['id_menu'];
                $attrs['id_modulo'] = null;
            }
            $row = SeguridadRolesSidebarOrden::create($attrs);
        }

        if ($syncGlobal) {
            $this->syncGlobalFromSidebarEtiqueta($tipo, $nombre, $icon, $data);
        }

        return [
            'tipo' => $tipo,
            'id_menu' => $row->id_menu,
            'id_modulo' => $row->id_modulo,
            'nombre' => $nombre,
            'icon' => $icon,
        ];
    }

    /**
     * Alinea nombre/ícono global del menú o módulo con la etiqueta del sidebar (admin).
     */
    public function syncGlobalFromSidebarEtiqueta(string $tipo, string $nombre, ?string $icon, array $data): void
    {
        if ($tipo === 'modulo') {
            $idModulo = (int) ($data['id_modulo'] ?? 0);
            if ($idModulo < 1) {
                return;
            }

            $update = [
                'nombre'     => $nombre,
                'updated_at' => now(),
            ];
            if ($icon !== null && Schema::hasColumn('sistema_modulo', 'Icon')) {
                $update['Icon'] = $icon;
            }

            SistemaModulo::query()->where('id_modulo', $idModulo)->update($update);
            $this->syncNombreSidebarModulo($idModulo, $nombre);

            if ($icon !== null && $this->hasRolesSidebarOrdenTable() && Schema::hasColumn('seguridad_roles_sidebar_orden', 'icon_sidebar')) {
                SeguridadRolesSidebarOrden::query()
                    ->where('tipo', 'modulo')
                    ->where('id_modulo', $idModulo)
                    ->update(['icon_sidebar' => $icon, 'updated_at' => now()]);
            }

            return;
        }

        if ($tipo === 'menu') {
            $idMenu = (int) ($data['id_menu'] ?? 0);
            if ($idMenu < 1) {
                return;
            }

            $update = [
                'nombre'     => $nombre,
                'updated_at' => now(),
            ];
            if ($icon !== null && Schema::hasColumn('sistema_menu', 'Icon')) {
                $update['Icon'] = $icon;
            }

            SistemaMenu::query()->where('id_menu', $idMenu)->update($update);
            $this->syncNombreSidebarMenu($idMenu, $nombre);

            if ($icon !== null && $this->hasRolesSidebarOrdenTable() && Schema::hasColumn('seguridad_roles_sidebar_orden', 'icon_sidebar')) {
                SeguridadRolesSidebarOrden::query()
                    ->where('tipo', 'menu')
                    ->where('id_menu', $idMenu)
                    ->update(['icon_sidebar' => $icon, 'updated_at' => now()]);
            }
        }
    }

    /**
     * Aplica etiquetas por rol a nodos raíz del árbol del sidebar.
     *
     * @param  array<int, array{node: array, orden_sidebar?: int}>  $roots
     * @return array<int, array{node: array, orden_sidebar?: int}>
     */
    public function applyEtiquetasToSidebarRoots(array $roots, int $idRoles): array
    {
        if ($idRoles < 1) {
            return $roots;
        }

        $etiquetas = $this->getEtiquetasMapForRole($idRoles);

        return array_map(function (array $rootMeta) use ($etiquetas, $idRoles) {
            $key = $this->rowToEtiquetaKey($this->rootMetaToOrdenRow($rootMeta));
            $node = $rootMeta['node'] ?? [];

            if (isset($etiquetas[$key])) {
                if (!empty($etiquetas[$key]['nombre'])) {
                    $node['name'] = $etiquetas[$key]['nombre'];
                }
                if (!empty($etiquetas[$key]['icon'])) {
                    $node['icon'] = $etiquetas[$key]['icon'];
                }
            }

            if (!empty($node['children'])) {
                $node['children'] = $this->applyEtiquetasToMenuNodes($node['children'], $idRoles);
            }

            $rootMeta['node'] = $node;

            return $rootMeta;
        }, $roots);
    }

    /**
     * Aplica nombre/icono por rol a menús anidados (hijos dentro de módulos o carpetas).
     *
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array<string, mixed>>
     */
    public function applyEtiquetasToMenuNodes(array $nodes, int $idRoles): array
    {
        if ($idRoles < 1 || empty($nodes)) {
            return $nodes;
        }

        $etiquetas = $this->getEtiquetasMapForRole($idRoles);

        return array_map(function (array $node) use ($etiquetas, $idRoles) {
            $key = !empty($node['id_menu']) ? 'menu:' . (int) $node['id_menu'] : null;

            if ($key && isset($etiquetas[$key])) {
                if (!empty($etiquetas[$key]['nombre'])) {
                    $node['name'] = $etiquetas[$key]['nombre'];
                }
                if (!empty($etiquetas[$key]['icon'])) {
                    $node['icon'] = $etiquetas[$key]['icon'];
                }
            }

            if (!empty($node['children'])) {
                $node['children'] = $this->applyEtiquetasToMenuNodes($node['children'], $idRoles);
            }

            return $node;
        }, $nodes);
    }

    /**
     * Tras renombrar un módulo en el árbol (nombre global), alinea las etiquetas por rol del sidebar.
     */
    public function syncNombreSidebarModulo(int $idModulo, string $nombre): void
    {
        if ($idModulo < 1 || trim($nombre) === '' || !$this->hasRolesSidebarOrdenTable()) {
            return;
        }

        if (!Schema::hasColumn('seguridad_roles_sidebar_orden', 'nombre_sidebar')) {
            return;
        }

        SeguridadRolesSidebarOrden::query()
            ->where('tipo', 'modulo')
            ->where('id_modulo', $idModulo)
            ->update([
                'nombre_sidebar' => trim($nombre),
                'updated_at' => now(),
            ]);
    }

    /**
     * Tras renombrar un menú en el árbol (nombre global), alinea las etiquetas por rol del sidebar.
     */
    public function syncNombreSidebarMenu(int $idMenu, string $nombre): void
    {
        if ($idMenu < 1 || trim($nombre) === '' || !$this->hasRolesSidebarOrdenTable()) {
            return;
        }

        if (!Schema::hasColumn('seguridad_roles_sidebar_orden', 'nombre_sidebar')) {
            return;
        }

        SeguridadRolesSidebarOrden::query()
            ->where('tipo', 'menu')
            ->where('id_menu', $idMenu)
            ->update([
                'nombre_sidebar' => trim($nombre),
                'updated_at' => now(),
            ]);
    }

    /**
     * Orden numérico para ordenar raíces del sidebar (menor = primero).
     *
     * @param  array{node: array, orden_sidebar?: int, tipo?: string, id_menu?: int, id_modulo?: int}  $rootMeta
     */
    public function resolveRootSortKey(array $rootMeta, int $idRoles): int
    {
        $node = $rootMeta['node'] ?? [];
        $row = $this->rootMetaToOrdenRow($rootMeta);

        return $this->resolveSortKey($row, $this->getOrdenMapForRole($idRoles));
    }

    /** Convierte nodo raíz del sidebar a fila tipo menu|modulo para orden. */
    public function rootMetaToOrdenRow(array $rootMeta): array
    {
        $node = $rootMeta['node'] ?? [];

        if (!empty($node['id_modulo'])) {
            return [
                'tipo' => 'modulo',
                'id_modulo' => (int) $node['id_modulo'],
                'orden_sidebar' => $rootMeta['orden_sidebar'] ?? null,
            ];
        }

        if (!empty($node['children']) && !empty($node['id_menu'])) {
            return [
                'tipo' => 'modulo',
                'id_modulo' => (int) $node['id_menu'],
                'orden_sidebar' => $rootMeta['orden_sidebar'] ?? null,
            ];
        }

        return [
            'tipo' => 'menu',
            'id_menu' => (int) ($node['id_menu'] ?? 0),
            'orden_sidebar' => $rootMeta['orden_sidebar'] ?? null,
        ];
    }

    /**
     * Filtra nodos hijos del sidebar según menús asignados al rol (no oculta hermanos del padre).
     *
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array<string, mixed>>
     */
    public function filterSidebarTreeNodesForRole(array $nodes, int $idRoles): array
    {
        if ($idRoles < 1 || $nodes === []) {
            return $nodes;
        }

        $menuIds = array_flip($this->getMenuIdsPermitidosRol($idRoles));
        $filtered = [];

        foreach ($nodes as $node) {
            $idMenu = (int) ($node['id_menu'] ?? 0);
            if (!empty($node['children'])) {
                $node['children'] = $this->filterSidebarTreeNodesForRole($node['children'], $idRoles);
            }

            if ($idMenu > 0 && !isset($menuIds[$idMenu])) {
                continue;
            }

            if (!empty($node['children']) || $idMenu < 1 || !empty($node['path'])) {
                $filtered[] = $node;
            }
        }

        return $filtered;
    }

    public function rootVisibleForRole(array $rootMeta, int $idRoles): bool
    {
        $row = $this->rootMetaToOrdenRow($rootMeta);
        $menuIds = array_flip($this->getMenuIdsPermitidosRol($idRoles));
        $moduloIds = array_flip($this->getModuloIdsPermitidosRol($idRoles));
        $inactivosRol = array_flip($this->getModulosInactivosRol($idRoles));

        if (($row['tipo'] ?? '') === 'modulo') {
            $idMod = (int) ($row['id_modulo'] ?? 0);
            if (isset($inactivosRol[$idMod])) {
                return false;
            }

            return isset($moduloIds[$idMod]);
        }

        $idMenu = (int) ($row['id_menu'] ?? 0);
        if ($idMenu > 0 && Schema::hasTable('sistema_menu')) {
            $idModMenu = (int) DB::table('sistema_menu')->where('id_menu', $idMenu)->value('id_modulo');
            if ($idModMenu > 0 && isset($inactivosRol[$idModMenu])) {
                return false;
            }
        }

        return isset($menuIds[$idMenu]);
    }

    /** Módulos ocultos en sidebar para un rol (no global). */
    public function getModulosInactivosRol(int $idRoles): array
    {
        if ($idRoles < 1 || !Schema::hasTable('seguridad_roles_modulo_inactivo')) {
            return [];
        }

        return DB::table('seguridad_roles_modulo_inactivo')
            ->where('id_roles', $idRoles)
            ->pluck('id_modulo')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** IDs de menús asignados al rol. */
    public function getMenuIdsPermitidosRol(int $idRoles): array
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

    /** IDs de módulos asignados al rol (+ módulos con menús del rol). */
    public function getModuloIdsPermitidosRol(int $idRoles): array
    {
        if ($idRoles < 1) {
            return [];
        }

        $ids = [];

        if (Schema::hasTable('seguridad_roles_modulo')) {
            $ids = DB::table('seguridad_roles_modulo')
                ->where('id_roles', $idRoles)
                ->pluck('id_modulo')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $menuIds = $this->getMenuIdsPermitidosRol($idRoles);
        if (!empty($menuIds)) {
            $fromMenus = DB::table('sistema_menu')
                ->whereIn('id_menu', $menuIds)
                ->whereNotNull('id_modulo')
                ->distinct()
                ->pluck('id_modulo')
                ->map(fn ($id) => (int) $id)
                ->all();
            $ids = array_values(array_unique(array_merge($ids, $fromMenus)));
        }

        return $ids;
    }

    /**
     * @return array<string, array{nombre?: string, icon?: string|null}> clave menu:12 | modulo:3
     */
    public function getEtiquetasMapForRole(int $idRoles): array
    {
        $map = [];

        if ($idRoles < 1 || !$this->hasRolesSidebarOrdenTable()) {
            return $map;
        }

        if (!Schema::hasColumn('seguridad_roles_sidebar_orden', 'nombre_sidebar')) {
            return $map;
        }

        foreach (SeguridadRolesSidebarOrden::query()->paraRol($idRoles)->get() as $row) {
            $key = $row->tipo === 'modulo' && $row->id_modulo
                ? 'modulo:' . $row->id_modulo
                : ($row->tipo === 'menu' && $row->id_menu ? 'menu:' . $row->id_menu : null);

            if (!$key) {
                continue;
            }

            $nombre = trim((string) ($row->nombre_sidebar ?? ''));
            $icon = trim((string) ($row->icon_sidebar ?? ''));

            if ($nombre === '' && $icon === '') {
                continue;
            }

            $map[$key] = [
                'nombre' => $nombre !== '' ? $nombre : null,
                'icon' => $icon !== '' ? $icon : null,
            ];
        }

        return $map;
    }

    private function rowToEtiquetaKey(array $row): string
    {
        return ($row['tipo'] ?? '') === 'modulo'
            ? 'modulo:' . ($row['id_modulo'] ?? 0)
            : 'menu:' . ($row['id_menu'] ?? 0);
    }

    private function applyEtiquetasToRow(array $row, array $etiquetas): array
    {
        $key = $this->rowToEtiquetaKey($row);
        if (!isset($etiquetas[$key])) {
            return $row;
        }

        if (!empty($etiquetas[$key]['nombre'])) {
            $row['nombre'] = $etiquetas[$key]['nombre'];
        }
        if (array_key_exists('icon', $etiquetas[$key]) && $etiquetas[$key]['icon'] !== null && $etiquetas[$key]['icon'] !== '') {
            $row['icon'] = $etiquetas[$key]['icon'];
        }

        return $row;
    }

    /**
     * @return array<string, int> clave "menu:12" | "modulo:3" => orden
     */
    public function getOrdenMapForRole(int $idRoles): array
    {
        $map = [];

        if ($idRoles < 1 || !$this->hasRolesSidebarOrdenTable()) {
            return $map;
        }

        foreach (SeguridadRolesSidebarOrden::query()->paraRol($idRoles)->orderBy('orden')->get() as $row) {
            if ($row->tipo === 'modulo' && $row->id_modulo) {
                $map['modulo:' . $row->id_modulo] = (int) $row->orden;
            } elseif ($row->tipo === 'menu' && $row->id_menu) {
                $map['menu:' . $row->id_menu] = (int) $row->orden;
            }
        }

        return $map;
    }

    /**
     * Reordena menús (hermanos bajo el mismo padre).
     *
     * @param  array<int, array{id_menu: int, id_menu_padre?: int|null, orden?: int}>  $items
     */
    public function reordenarMenus(array $items): int
    {
        if (!$this->hasMenuOrden()) {
            throw new \RuntimeException('Reordenación de menús no disponible. Ejecute: php artisan migrate');
        }

        DB::transaction(function () use ($items) {
            foreach ($items as $row) {
                $id = $row['id_menu'] ?? null;
                if (!$id) {
                    continue;
                }

                SistemaMenu::query()
                    ->where('id_menu', (int) $id)
                    ->update([
                        'id_menu_padre' => $row['id_menu_padre'] ?? null,
                        'orden' => (int) ($row['orden'] ?? 0),
                        'updated_at' => now(),
                    ]);
            }
        });

        return count($items);
    }

    /**
     * Reordena módulos (campo orden global en sistema_modulo).
     *
     * @param  array<int, array{id_modulo: int, orden?: int}>  $items
     */
    public function reordenarModulos(array $items): int
    {
        $this->ensureModuloOrdenColumn();

        DB::transaction(function () use ($items) {
            foreach ($items as $row) {
                if (empty($row['id_modulo'])) {
                    continue;
                }

                SistemaModulo::query()
                    ->where('id_modulo', (int) $row['id_modulo'])
                    ->update([
                        'orden' => (int) ($row['orden'] ?? 0),
                        'updated_at' => now(),
                    ]);
            }
        });

        return count($items);
    }

    public function ensureModuloOrdenColumn(): void
    {
        if (!Schema::hasColumn('sistema_modulo', 'orden')) {
            Schema::table('sistema_modulo', function ($table) {
                $table->unsignedInteger('orden')->default(0)->after('Activo');
            });
        }
    }

    private function filterItemsForRole(Collection $items, int $idRoles): Collection
    {
        $menuIds = array_flip($this->getMenuIdsPermitidosRol($idRoles));
        $moduloIds = array_flip($this->getModuloIdsPermitidosRol($idRoles));

        return $items->filter(function (array $row) use ($menuIds, $moduloIds) {
            if (($row['tipo'] ?? '') === 'menu') {
                return isset($menuIds[(int) ($row['id_menu'] ?? 0)]);
            }

            if (($row['tipo'] ?? '') === 'modulo') {
                return isset($moduloIds[(int) ($row['id_modulo'] ?? 0)]);
            }

            return false;
        });
    }

    private function resolveSortKey(array $row, array $roleOrden): int
    {
        $tipo = $row['tipo'] ?? '';
        $key = $tipo === 'modulo'
            ? 'modulo:' . ($row['id_modulo'] ?? 0)
            : 'menu:' . ($row['id_menu'] ?? 0);

        if (isset($roleOrden[$key])) {
            return $roleOrden[$key];
        }

        if ($this->hasOrdenSidebar() && isset($row['orden_sidebar']) && $row['orden_sidebar'] !== null) {
            return (int) $row['orden_sidebar'];
        }

        return 99999;
    }

    private function buildSidebarRootItems(bool $soloActivos): Collection
    {
        $items = collect();

        $menuQuery = SistemaMenu::query()->raizSinModulo();
        if ($soloActivos) {
            $menuQuery->activos();
        }
        if (Schema::hasColumn('sistema_menu', 'orden')) {
            $menuQuery->orderBy('orden');
        } else {
            $menuQuery->orderBy('id_menu');
        }

        foreach ($menuQuery->get() as $menu) {
            $items->push([
                'tipo' => 'menu',
                'id_menu' => $menu->id_menu,
                'id_modulo' => null,
                'nombre' => $menu->nombre,
                'icon' => $menu->Icon ?? null,
                'Activo' => $menu->Activo ?? 'S',
                'orden_sidebar' => $menu->orden_sidebar ?? null,
                'hijos_count' => $this->countMenuDescendants((int) $menu->id_menu),
            ]);
        }

        $modQuery = SistemaModulo::query();
        if ($soloActivos) {
            $modQuery->activos();
        }
        if (Schema::hasColumn('sistema_modulo', 'orden')) {
            $modQuery->orderBy('orden');
        } else {
            $modQuery->orderBy('id_modulo');
        }

        foreach ($modQuery->get() as $mod) {
            $menusQuery = SistemaMenu::query()->where('id_modulo', $mod->id_modulo);
            if ($soloActivos) {
                $menusQuery->where('Activo', 'S');
            }
            if (Schema::hasColumn('sistema_menu', 'id_menu_padre')) {
                $menusQuery->whereNull('id_menu_padre');
            }
            if (Schema::hasColumn('sistema_menu', 'orden')) {
                $menusQuery->orderBy('orden');
            } else {
                $menusQuery->orderBy('id_menu');
            }

            $menusRaiz = $menusQuery->get();
            $menusCount = SistemaMenu::query()
                ->where('id_modulo', $mod->id_modulo)
                ->when($soloActivos, fn ($q) => $q->where('Activo', 'S'))
                ->count();

            $nombreModulo = trim((string) ($mod->nombre ?? ''));
            if ($nombreModulo === '' && $menusRaiz->isNotEmpty()) {
                $nombreModulo = trim((string) ($menusRaiz->first()->nombre ?? ''));
            }
            if ($nombreModulo === '') {
                $nombreModulo = 'Módulo ' . $mod->id_modulo;
            }

            $items->push([
                'tipo' => 'modulo',
                'id_menu' => null,
                'id_modulo' => $mod->id_modulo,
                'nombre' => $nombreModulo,
                'icon' => $mod->Icon ?? 'folder',
                'Activo' => $mod->Activo ?? 'S',
                'orden_sidebar' => $mod->orden_sidebar ?? null,
                'hijos_count' => $menusCount,
            ]);
        }

        return $items;
    }

    private function countMenuDescendants(int $idMenu): int
    {
        if (!Schema::hasColumn('sistema_menu', 'id_menu_padre')) {
            return 0;
        }

        $total = 0;
        $hijos = SistemaMenu::query()->where('id_menu_padre', $idMenu)->pluck('id_menu');
        foreach ($hijos as $hijoId) {
            $total += 1 + $this->countMenuDescendants((int) $hijoId);
        }

        return $total;
    }
}
