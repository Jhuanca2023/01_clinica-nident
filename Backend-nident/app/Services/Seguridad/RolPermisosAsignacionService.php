<?php

namespace App\Services\Seguridad;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Asignación de permisos por rol (tablas de unión). Sin mezclar id_modulo en seguridad_roles_menu.
 */
class RolPermisosAsignacionService
{
    public function limpiarTodasLasAsignaciones(): void
    {
        DB::table('seguridad_roles_menu')->truncate();
        if (Schema::hasTable('seguridad_menu_objetos_roles')) {
            DB::table('seguridad_menu_objetos_roles')->truncate();
        }
        if (Schema::hasTable('seguridad_roles_modulo')) {
            DB::table('seguridad_roles_modulo')->truncate();
        }
        if (Schema::hasTable('seguridad_modulo_objetos_roles')) {
            DB::table('seguridad_modulo_objetos_roles')->truncate();
        }
    }

    public function limpiarRol(int $idRoles): void
    {
        DB::table('seguridad_roles_menu')->where('id_roles', $idRoles)->delete();
        if (Schema::hasTable('seguridad_menu_objetos_roles')) {
            DB::table('seguridad_menu_objetos_roles')->where('id_roles', $idRoles)->delete();
        }
        if (Schema::hasTable('seguridad_roles_modulo')) {
            DB::table('seguridad_roles_modulo')->where('id_roles', $idRoles)->delete();
        }
        if (Schema::hasTable('seguridad_modulo_objetos_roles')) {
            DB::table('seguridad_modulo_objetos_roles')->where('id_roles', $idRoles)->delete();
        }
    }

    /**
     * Asigna menús, módulos y objetos al rol (uso explícito: seeders o botón admin).
     * Los módulos van a seguridad_roles_modulo, NUNCA a seguridad_roles_menu.
     */
    public function asignarTodoAlRol(int $idRoles): void
    {
        $now = now();

        if (Schema::hasTable('seguridad_roles_modulo')) {
            foreach (DB::table('sistema_modulo')->where('Activo', 'S')->pluck('id_modulo') as $idModulo) {
                DB::table('seguridad_roles_modulo')->insertOrIgnore([
                    'id_roles' => $idRoles,
                    'id_modulo' => (int) $idModulo,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        foreach (DB::table('sistema_menu')->where('Activo', 'S')->pluck('id_menu') as $idMenu) {
            DB::table('seguridad_roles_menu')->insertOrIgnore([
                'id_roles' => $idRoles,
                'id_menu' => (int) $idMenu,
            ]);
        }

        if (Schema::hasTable('seguridad_menu_objetos_roles')) {
            foreach (DB::table('sistema_menu_objetos')->where('Activo', 'S')->pluck('id_menu_objetos') as $idMo) {
                DB::table('seguridad_menu_objetos_roles')->insertOrIgnore([
                    'id_roles' => $idRoles,
                    'id_menu_objetos' => (int) $idMo,
                ]);
            }
        }

        if (Schema::hasTable('seguridad_modulo_objetos_roles') && Schema::hasTable('sistema_modulo_objetos')) {
            foreach (DB::table('sistema_modulo_objetos')->where('Activo', 'S')->pluck('id_modulo_objetos') as $idOm) {
                DB::table('seguridad_modulo_objetos_roles')->insertOrIgnore([
                    'id_roles' => $idRoles,
                    'id_modulo_objetos' => (int) $idOm,
                ]);
            }
        }
    }

    /**
     * Para cada menú ya asignado al rol, asigna todos los objetos activos de ese menú
     * (los que están en sistema_menu_objetos). Útil tras configurar objetos en Menú → Objetos.
     */
    public function sincronizarObjetosDeMenusAsignados(int $idRoles): int
    {
        if (!Schema::hasTable('seguridad_menu_objetos_roles')) {
            return 0;
        }

        $menuIds = DB::table('seguridad_roles_menu')
            ->where('id_roles', $idRoles)
            ->pluck('id_menu')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($menuIds === []) {
            return 0;
        }

        $inserted = 0;
        $objetos = DB::table('sistema_menu_objetos')
            ->whereIn('id_menu', $menuIds)
            ->where(function ($q) {
                $q->where('Activo', 'S')->orWhereNull('Activo');
            })
            ->pluck('id_menu_objetos');

        foreach ($objetos as $idMo) {
            $ok = DB::table('seguridad_menu_objetos_roles')->insertOrIgnore([
                'id_roles' => $idRoles,
                'id_menu_objetos' => (int) $idMo,
            ]);
            if ($ok) {
                $inserted++;
            }
        }

        return $inserted;
    }

    /**
     * Copia menús, módulos, objetos y opcionalmente orden/etiquetas del sidebar de un rol a otro.
     *
     * @return array{menus: int, objetos_menu: int, modulos: int, objetos_modulo: int, sidebar_orden: int}
     */
    public function copiarPermisosDesdeRol(int $idOrigen, int $idDestino, bool $incluirSidebarOrden = false): array
    {
        if ($idOrigen < 1 || $idDestino < 1) {
            throw new \InvalidArgumentException('Roles origen y destino son requeridos');
        }

        if ($idOrigen === $idDestino) {
            throw new \InvalidArgumentException('El rol origen y destino no pueden ser el mismo');
        }

        $origenExiste = DB::table('seguridad_roles')->where('id_roles', $idOrigen)->exists();
        $destinoExiste = DB::table('seguridad_roles')->where('id_roles', $idDestino)->exists();

        if (!$origenExiste || !$destinoExiste) {
            throw new \InvalidArgumentException('Uno de los roles no existe');
        }

        $counts = [
            'menus'           => 0,
            'objetos_menu'    => 0,
            'modulos'         => 0,
            'objetos_modulo'  => 0,
            'sidebar_orden'   => 0,
        ];

        DB::transaction(function () use ($idOrigen, $idDestino, $incluirSidebarOrden, &$counts) {
            $this->limpiarRol($idDestino);

            $counts['menus'] = DB::table('seguridad_roles_menu')
                ->where('id_roles', $idOrigen)
                ->get()
                ->each(function ($row) use ($idDestino) {
                    DB::table('seguridad_roles_menu')->insert([
                        'id_roles' => $idDestino,
                        'id_menu'  => (int) $row->id_menu,
                    ]);
                })
                ->count();

            if (Schema::hasTable('seguridad_menu_objetos_roles')) {
                $counts['objetos_menu'] = DB::table('seguridad_menu_objetos_roles')
                    ->where('id_roles', $idOrigen)
                    ->get()
                    ->each(function ($row) use ($idDestino) {
                        DB::table('seguridad_menu_objetos_roles')->insert([
                            'id_roles'          => $idDestino,
                            'id_menu_objetos'   => (int) $row->id_menu_objetos,
                        ]);
                    })
                    ->count();
            }

            if (Schema::hasTable('seguridad_roles_modulo')) {
                $now = now();
                $counts['modulos'] = DB::table('seguridad_roles_modulo')
                    ->where('id_roles', $idOrigen)
                    ->get()
                    ->each(function ($row) use ($idDestino, $now) {
                        DB::table('seguridad_roles_modulo')->insert([
                            'id_roles'   => $idDestino,
                            'id_modulo'  => (int) $row->id_modulo,
                            'created_at' => $row->created_at ?? $now,
                            'updated_at' => $row->updated_at ?? $now,
                        ]);
                    })
                    ->count();
            }

            if (Schema::hasTable('seguridad_modulo_objetos_roles')) {
                $counts['objetos_modulo'] = DB::table('seguridad_modulo_objetos_roles')
                    ->where('id_roles', $idOrigen)
                    ->get()
                    ->each(function ($row) use ($idDestino) {
                        DB::table('seguridad_modulo_objetos_roles')->insert([
                            'id_roles'            => $idDestino,
                            'id_modulo_objetos'   => (int) $row->id_modulo_objetos,
                        ]);
                    })
                    ->count();
            }

            if ($incluirSidebarOrden && Schema::hasTable('seguridad_roles_sidebar_orden')) {
                DB::table('seguridad_roles_sidebar_orden')->where('id_roles', $idDestino)->delete();

                $now = now();
                $counts['sidebar_orden'] = DB::table('seguridad_roles_sidebar_orden')
                    ->where('id_roles', $idOrigen)
                    ->get()
                    ->each(function ($row) use ($idDestino, $now) {
                        $insert = [
                            'id_roles'  => $idDestino,
                            'tipo'      => $row->tipo,
                            'id_menu'   => $row->id_menu,
                            'id_modulo' => $row->id_modulo,
                            'orden'     => (int) $row->orden,
                            'created_at'=> $row->created_at ?? $now,
                            'updated_at'=> $row->updated_at ?? $now,
                        ];
                        if (Schema::hasColumn('seguridad_roles_sidebar_orden', 'nombre_sidebar')) {
                            $insert['nombre_sidebar'] = $row->nombre_sidebar ?? null;
                        }
                        if (Schema::hasColumn('seguridad_roles_sidebar_orden', 'icon_sidebar')) {
                            $insert['icon_sidebar'] = $row->icon_sidebar ?? null;
                        }
                        DB::table('seguridad_roles_sidebar_orden')->insert($insert);
                    })
                    ->count();
            }
        });

        return $counts;
    }

    /** Elimina filas erróneas: id_menu que en realidad es id_modulo. */
    public function eliminarAsignacionesMenuCorruptas(): int
    {
        $moduloIds = DB::table('sistema_modulo')->pluck('id_modulo')->map(fn ($id) => (int) $id)->all();
        if ($moduloIds === []) {
            return 0;
        }

        return DB::table('seguridad_roles_menu')->whereIn('id_menu', $moduloIds)->delete();
    }
}
