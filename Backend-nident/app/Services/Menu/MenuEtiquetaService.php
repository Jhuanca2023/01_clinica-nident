<?php

namespace App\Services\Menu;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MenuEtiquetaService
{
    public function tablesReady(): bool
    {
        return Schema::hasTable('administracion_etiquetas')
            && Schema::hasTable('administracion_entidad_etiqueta');
    }

    /** Etiquetas disponibles para selector (globales + del rol). */
    public function listarOpciones(int $idRoles): array
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $q = DB::table('administracion_etiquetas')
            ->where('Activo', 'S')
            ->where(function ($query) use ($idRoles) {
                $query->whereNull('id_roles');
                if ($idRoles > 0) {
                    $query->orWhere('id_roles', $idRoles);
                }
            })
            ->orderBy('es_recomendada', 'desc')
            ->orderBy('nombre');

        return $q->get([
            'id_etiqueta',
            'nombre',
            'slug',
            'color',
            'id_roles',
            'es_recomendada',
        ])->map(fn ($r) => (array) $r)->all();
    }

    public function listarPorRol(int $idRoles, bool $soloActivas = true): array
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $q = DB::table('administracion_etiquetas')->orderBy('nombre');
        if ($soloActivas) {
            $q->where('Activo', 'S');
        }
        if ($idRoles > 0) {
            $q->where(function ($query) use ($idRoles) {
                $query->whereNull('id_roles')->orWhere('id_roles', $idRoles);
            });
        }

        return $q->get()->map(fn ($r) => (array) $r)->all();
    }

    /** @param Collection|array $menus */
    public function enriquecerMenus($menus, int $idRoles): array
    {
        if (!$this->tablesReady() || $idRoles < 1) {
            return $menus instanceof Collection ? $menus->all() : (array) $menus;
        }

        $rows = collect($menus);
        if ($rows->isEmpty()) {
            return [];
        }

        $ids = $rows->pluck('id_menu')->filter()->unique()->values()->all();
        $map = $this->mapEtiquetasPorMenus($ids, $idRoles);

        return $rows->map(function ($item) use ($map) {
            $arr = is_array($item) ? $item : (array) $item;
            $id = (int) ($arr['id_menu'] ?? 0);
            $arr['etiquetas'] = $map[$id] ?? [];
            return $arr;
        })->all();
    }

    /** @param Collection|array $modulos */
    public function enriquecerModulos($modulos, int $idRoles): array
    {
        if (!$this->tablesReady() || $idRoles < 1) {
            return $modulos instanceof Collection ? $modulos->all() : (array) $modulos;
        }

        $rows = collect($modulos);
        if ($rows->isEmpty()) {
            return [];
        }

        $ids = $rows->pluck('id_modulo')->filter()->unique()->values()->all();
        $map = $this->mapEtiquetasPorModulos($ids, $idRoles);

        return $rows->map(function ($item) use ($map) {
            $arr = is_array($item) ? $item : (array) $item;
            $id = (int) ($arr['id_modulo'] ?? 0);
            $arr['etiquetas'] = $map[$id] ?? [];
            return $arr;
        })->all();
    }

    public function syncMenu(int $idRoles, int $idMenu, ?array $idEtiquetas): void
    {
        if (!$this->tablesReady() || $idRoles < 1 || $idMenu < 1) {
            return;
        }

        DB::table('administracion_entidad_etiqueta')
            ->where('id_roles', $idRoles)
            ->where('id_menu', $idMenu)
            ->delete();

        $this->insertPivot($idRoles, $idEtiquetas, $idMenu, null);
    }

    public function syncModulo(int $idRoles, int $idModulo, ?array $idEtiquetas): void
    {
        if (!$this->tablesReady() || $idRoles < 1 || $idModulo < 1) {
            return;
        }

        DB::table('administracion_entidad_etiqueta')
            ->where('id_roles', $idRoles)
            ->where('id_modulo', $idModulo)
            ->delete();

        $this->insertPivot($idRoles, $idEtiquetas, null, $idModulo);
    }

    private function insertPivot(int $idRoles, ?array $idEtiquetas, ?int $idMenu, ?int $idModulo): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idEtiquetas ?? []))));
        if ($ids === []) {
            return;
        }

        $permitidos = DB::table('administracion_etiquetas')
            ->whereIn('id_etiqueta', $ids)
            ->where('Activo', 'S')
            ->where(function ($q) use ($idRoles) {
                $q->whereNull('id_roles')->orWhere('id_roles', $idRoles);
            })
            ->pluck('id_etiqueta')
            ->all();

        $now = now();
        foreach ($permitidos as $idEtiqueta) {
            DB::table('administracion_entidad_etiqueta')->insert([
                'id_etiqueta' => $idEtiqueta,
                'id_menu'     => $idMenu,
                'id_modulo'   => $idModulo,
                'id_roles'    => $idRoles,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    private function mapEtiquetasPorMenus(array $idMenus, int $idRoles): array
    {
        if ($idMenus === []) {
            return [];
        }

        $rows = DB::table('administracion_entidad_etiqueta as ee')
            ->join('administracion_etiquetas as e', 'e.id_etiqueta', '=', 'ee.id_etiqueta')
            ->where('ee.id_roles', $idRoles)
            ->whereIn('ee.id_menu', $idMenus)
            ->where('e.Activo', 'S')
            ->orderBy('e.nombre')
            ->get([
                'ee.id_menu',
                'e.id_etiqueta',
                'e.nombre',
                'e.slug',
                'e.color',
            ]);

        return $this->groupMap($rows, 'id_menu');
    }

    private function mapEtiquetasPorModulos(array $idModulos, int $idRoles): array
    {
        if ($idModulos === []) {
            return [];
        }

        $rows = DB::table('administracion_entidad_etiqueta as ee')
            ->join('administracion_etiquetas as e', 'e.id_etiqueta', '=', 'ee.id_etiqueta')
            ->where('ee.id_roles', $idRoles)
            ->whereIn('ee.id_modulo', $idModulos)
            ->where('e.Activo', 'S')
            ->orderBy('e.nombre')
            ->get([
                'ee.id_modulo',
                'e.id_etiqueta',
                'e.nombre',
                'e.slug',
                'e.color',
            ]);

        return $this->groupMap($rows, 'id_modulo');
    }

    private function groupMap($rows, string $keyField): array
    {
        $map = [];
        foreach ($rows as $row) {
            $key = (int) $row->{$keyField};
            if (!isset($map[$key])) {
                $map[$key] = [];
            }
            $map[$key][] = [
                'id_etiqueta' => (int) $row->id_etiqueta,
                'nombre'      => $row->nombre,
                'slug'        => $row->slug,
                'color'       => $row->color,
            ];
        }

        return $map;
    }

    public static function slugFromNombre(string $nombre): string
    {
        return Str::slug(trim($nombre)) ?: 'etiqueta';
    }
}
