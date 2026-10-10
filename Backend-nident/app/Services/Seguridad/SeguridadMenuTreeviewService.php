<?php

namespace App\Services\Seguridad;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Árbol de permisos por rol (módulos, menús, objetos) sin SP ni tabla datatreeview.
 * Equivalente a USP_SEGURIDAD_PERFIL_MENU_LISTAR_TREEVIEW en Eloquent/Query Builder.
 */
class SeguridadMenuTreeviewService
{
    public function listarParaRol(int $idRoles): array
    {
        $idRoles = max(0, $idRoles);
        $hasRolesModulo = Schema::hasTable('seguridad_roles_modulo');
        $hasModuloObjetos = Schema::hasTable('sistema_modulo_objetos');
        $hasModuloObjetosRoles = Schema::hasTable('seguridad_modulo_objetos_roles');
        $hasMenuPadre = Schema::hasColumn('sistema_menu', 'id_menu_padre');

        $rolesModuloIds = $hasRolesModulo && $idRoles > 0
            ? DB::table('seguridad_roles_modulo')->where('id_roles', $idRoles)->pluck('id_modulo')->flip()->all()
            : [];

        $rolesMenuIds = $idRoles > 0
            ? DB::table('seguridad_roles_menu')->where('id_roles', $idRoles)->pluck('id_menu')->flip()->all()
            : [];

        $rolesMenuObjetoIds = $idRoles > 0
            ? DB::table('seguridad_menu_objetos_roles')->where('id_roles', $idRoles)->pluck('id_menu_objetos')->flip()->all()
            : [];

        $rolesModuloObjetoIds = $hasModuloObjetosRoles && $idRoles > 0
            ? DB::table('seguridad_modulo_objetos_roles')->where('id_roles', $idRoles)->pluck('id_modulo_objetos')->flip()->all()
            : [];

        $result = [];

        // Nivel 0: módulos activos
        $moduloCols = ['SM.id_modulo', 'SM.nombre', 'SM.Icon', 'SM.Activo'];
        if (Schema::hasColumn('sistema_modulo', 'Orden')) {
            $moduloCols[] = 'SM.Orden';
        }

        $modulos = DB::table('sistema_modulo as SM')
            ->where(function ($q) {
                $q->where('SM.Activo', 'S')->orWhereNull('SM.Activo');
            })
            ->orderBy(Schema::hasColumn('sistema_modulo', 'Orden') ? 'SM.Orden' : 'SM.nombre')
            ->select($moduloCols)
            ->get();

        foreach ($modulos as $sm) {
            $idModulo = (int) $sm->id_modulo;
            $result[] = $this->nodo(
                idMenu: 'mod_' . $idModulo,
                idMenuPadre: null,
                menu: (string) $sm->nombre,
                tipo: 'modulo',
                nivel: 0,
                icon: $sm->Icon ?? 'folder',
                activo: $sm->Activo ?? 'S',
                selected: $idRoles > 0 && isset($rolesModuloIds[$idModulo]),
                expanded: false,
            );
        }

        $moduloIdsActivos = $modulos->pluck('id_modulo')->map(fn ($id) => (int) $id)->flip()->all();

        // Nivel 1: menús con módulo
        $menuCols = ['SME.id_menu', 'SME.id_modulo', 'SME.nombre', 'SME.Activo'];
        if ($hasMenuPadre) {
            $menuCols[] = 'SME.id_menu_padre';
        }
        if (Schema::hasColumn('sistema_menu', 'orden')) {
            $menuCols[] = 'SME.orden';
        }

        $menus = DB::table('sistema_menu as SME')
            ->whereNotNull('SME.id_modulo')
            ->where(function ($q) {
                $q->where('SME.Activo', 'S')->orWhereNull('SME.Activo');
            })
            ->select($menuCols)
            ->orderBy(
                Schema::hasColumn('sistema_menu', 'orden') ? 'SME.orden' : 'SME.nombre'
            )
            ->get();

        $menuById = $menus->keyBy(fn ($row) => (int) $row->id_menu);
        $menuKeys = [];
        foreach ($menus as $sme) {
            $nombre = trim((string) ($sme->nombre ?? ''));
            if ($nombre === '') {
                continue;
            }

            $idMenu = (int) $sme->id_menu;
            $idModulo = (int) $sme->id_modulo;
            if (!isset($moduloIdsActivos[$idModulo])) {
                continue;
            }

            $idMenuStr = 'm' . $idMenu;
            $menuKeys[$idMenuStr] = true;

            $padre = null;
            if ($hasMenuPadre && !empty($sme->id_menu_padre)) {
                $padre = 'm' . (int) $sme->id_menu_padre;
            } else {
                $padre = 'mod_' . $idModulo;
            }

            $nivelMenu = $this->profundidadMenu($menuById, $idMenu);

            $result[] = $this->nodo(
                idMenu: $idMenuStr,
                idMenuPadre: $padre,
                menu: $nombre,
                tipo: 'menu',
                nivel: $nivelMenu,
                idMenuNum: $idMenu,
                icon: 'activefolder',
                activo: $sme->Activo ?? 'S',
                selected: $idRoles > 0 && isset($rolesMenuIds[$idMenu]),
                expanded: false,
            );
        }

        $menuIdsValidos = $menus->pluck('id_menu')->map(fn ($id) => (int) $id)->all();

        // Nivel 2: objetos de menú (solo menús existentes y activos)
        if (!empty($menuIdsValidos)) {
            $objetosMenu = DB::table('sistema_menu_objetos as SMO')
                ->leftJoin('sistema_objetos as SO', 'SO.id_objetos', '=', 'SMO.id_objetos')
                ->whereIn('SMO.id_menu', $menuIdsValidos)
                ->where(function ($q) {
                    $q->where('SMO.Activo', 'S')->orWhereNull('SMO.Activo');
                })
                ->orderBy('SO.nombre')
                ->get([
                    'SMO.id_menu_objetos',
                    'SMO.id_menu',
                    'SMO.id_objetos',
                    'SO.nombre as objeto_nombre',
                    'SMO.Activo',
                ]);

            foreach ($objetosMenu as $row) {
                $nombre = trim((string) ($row->objeto_nombre ?? ''));
                if ($nombre === '') {
                    $nombre = 'Sin nombre';
                }

                $idMo = (int) $row->id_menu_objetos;
                $idMenu = (int) $row->id_menu;
                $padreMenu = 'm' . $idMenu;
                if (!isset($menuKeys[$padreMenu])) {
                    continue;
                }

                $nivelObj = ($this->profundidadMenu($menuById, $idMenu)) + 1;

                $result[] = $this->nodo(
                    idMenu: 'o' . $idMo,
                    idMenuPadre: $padreMenu,
                    menu: $nombre,
                    tipo: 'objeto',
                    nivel: $nivelObj,
                    idMenuNum: $idMenu,
                    idMenuObjetos: $idMo,
                    idObjeto: (int) $row->id_objetos,
                    icon: 'bullet',
                    activo: $row->Activo ?? 'S',
                    selected: $idRoles > 0 && isset($rolesMenuObjetoIds[$idMo]),
                    expanded: false,
                );
            }
        }

        // Nivel 2: objetos bajo módulo (solo módulos activos)
        if ($hasModuloObjetos) {
            $objetosModulo = DB::table('sistema_modulo_objetos as SMO')
                ->leftJoin('sistema_objetos as SO', 'SO.id_objetos', '=', 'SMO.id_objetos')
                ->join('sistema_modulo as SM', function ($join) {
                    $join->on('SM.id_modulo', '=', 'SMO.id_modulo')
                        ->where(function ($q) {
                            $q->where('SM.Activo', 'S')->orWhereNull('SM.Activo');
                        });
                })
                ->where(function ($q) {
                    $q->where('SMO.Activo', 'S')->orWhereNull('SMO.Activo');
                })
                ->orderBy('SO.nombre')
                ->get([
                    'SMO.id_modulo_objetos',
                    'SMO.id_modulo',
                    'SMO.id_objetos',
                    'SO.nombre as objeto_nombre',
                    'SMO.Activo',
                ]);

            foreach ($objetosModulo as $row) {
                $idModulo = (int) $row->id_modulo;
                if (!isset($moduloIdsActivos[$idModulo])) {
                    continue;
                }

                $nombre = trim((string) ($row->objeto_nombre ?? ''));
                if ($nombre === '') {
                    $nombre = 'Sin nombre';
                }

                $idMo = (int) $row->id_modulo_objetos;
                $result[] = $this->nodo(
                    idMenu: 'om' . $idMo,
                    idMenuPadre: 'mod_' . $idModulo,
                    menu: $nombre,
                    tipo: 'objeto',
                    nivel: 2,
                    idMenuObjetos: $idMo,
                    idObjeto: (int) $row->id_objetos,
                    icon: 'bullet',
                    activo: $row->Activo ?? 'S',
                    selected: $idRoles > 0 && isset($rolesModuloObjetoIds[$idMo]),
                    expanded: false,
                );
            }
        }

        $result = $this->filtrarHuerfanos($result);
        $result = $this->ajustarSelectedSoloHojas($result);

        usort($result, function ($a, $b) use ($menus) {
            $na = (int) ($a['Nivel'] ?? 0);
            $nb = (int) ($b['Nivel'] ?? 0);
            if ($na !== $nb) {
                return $na <=> $nb;
            }
            $oa = $this->ordenMenuNodo($menus, $a);
            $ob = $this->ordenMenuNodo($menus, $b);
            if ($oa !== $ob) {
                return $oa <=> $ob;
            }

            return strcasecmp((string) ($a['Menu'] ?? ''), (string) ($b['Menu'] ?? ''));
        });

        return $result;
    }

    /**
     * Nodos con hijos no llevan selected=true: si no, DevExtreme marca todos los hijos
     * (incluidos módulos/menús nuevos) como asignados sin permiso real en BD.
     */
    private function ajustarSelectedSoloHojas(array $nodes): array
    {
        $parentIds = [];
        foreach ($nodes as $node) {
            $padre = $node['IdMenuPadre'] ?? null;
            if ($padre !== null && $padre !== '') {
                $parentIds[$padre] = true;
            }
        }

        foreach ($nodes as $i => $node) {
            if (isset($parentIds[$node['IdMenu']])) {
                $nodes[$i]['selected'] = false;
            }
        }

        return $nodes;
    }

    private function ordenMenuNodo($menus, array $nodo): int
    {
        $id = (int) ($nodo['id_menu'] ?? 0);
        if ($id < 1) {
            return 0;
        }
        $row = $menus->firstWhere('id_menu', $id);

        return (int) ($row->orden ?? 0);
    }

    /** Profundidad del menú bajo el módulo (1 = raíz del módulo, 2+ = hijos multinivel). */
    private function profundidadMenu($menuById, int $idMenu): int
    {
        $depth = 1;
        $current = $idMenu;
        $visited = [];

        for ($i = 0; $i < 32; $i++) {
            if (isset($visited[$current])) {
                break;
            }
            $visited[$current] = true;
            $row = $menuById->get($current);
            if ($row === null || empty($row->id_menu_padre)) {
                break;
            }
            $depth++;
            $current = (int) $row->id_menu_padre;
        }

        return $depth;
    }

    private function filtrarHuerfanos(array $nodes): array
    {
        $validIds = array_flip(array_column($nodes, 'IdMenu'));

        return array_values(array_filter($nodes, function ($item) use ($validIds) {
            $parent = $item['IdMenuPadre'] ?? null;
            if ($parent === null || $parent === '') {
                return true;
            }

            return isset($validIds[$parent]);
        }));
    }

    private function nodo(
        string $idMenu,
        ?string $idMenuPadre,
        string $menu,
        string $tipo,
        int $nivel,
        string $icon = 'folder',
        string $activo = 'S',
        bool $selected = false,
        bool $expanded = false,
        ?int $idMenuNum = null,
        ?int $idMenuObjetos = null,
        ?int $idObjeto = null,
    ): array {
        return [
            'IdMenu' => $idMenu,
            'IdMenuPadre' => $idMenuPadre,
            'id_menu' => $idMenuNum,
            'id_menu_objetos' => $idMenuObjetos,
            'IdObjeto' => $idObjeto,
            'Menu' => $menu,
            'Nombre' => $menu,
            'Icon' => $icon,
            'Icono' => $icon,
            'Nivel' => $nivel,
            'Orden' => 0,
            'selected' => $selected,
            'Expanded' => $expanded ? 1 : 0,
            'Activo' => $activo,
            'Tipo' => $tipo,
        ];
    }
}
