<?php

namespace App\Services\Menu;

use App\Models\SeguridadUsuarioMenuFavorito;
use Illuminate\Support\Facades\Schema;

class SistemaMenuFavoritosService
{
    public function hasTable(): bool
    {
        return Schema::hasTable('seguridad_usuario_menu_favorito');
    }

    public function hasIdRolesColumn(): bool
    {
        return $this->hasTable() && Schema::hasColumn('seguridad_usuario_menu_favorito', 'id_roles');
    }

    public function normalizePath(?string $path): string
    {
        $p = trim((string) $path);
        if ($p === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $p) || str_starts_with($p, '#')) {
            return $p;
        }

        $p = '/' . ltrim($p, '/');
        if (str_starts_with($p, '/admin/')) {
            $p = '/' . ltrim(substr($p, 6), '/');
        } elseif ($p === '/admin') {
            $p = '/';
        }

        return rtrim($p, '/') ?: '/';
    }

    private function baseQuery(int $idUsuario, int $idRoles)
    {
        $q = SeguridadUsuarioMenuFavorito::query()->where('id_usuario', $idUsuario);
        if ($this->hasIdRolesColumn() && $idRoles > 0) {
            $q->where('id_roles', $idRoles);
        }

        return $q;
    }

    /** @return array<int, array<string, mixed>> */
    public function listar(int $idUsuario, int $idRoles): array
    {
        if (!$this->hasTable() || $idUsuario < 1 || $idRoles < 1) {
            return [];
        }

        return $this->baseQuery($idUsuario, $idRoles)
            ->orderBy('orden')
            ->orderBy('id_favorito')
            ->orderBy('nombre')
            ->get()
            ->map(fn (SeguridadUsuarioMenuFavorito $row) => [
                'id_favorito' => $row->id_favorito,
                'id_roles' => (int) ($row->id_roles ?? $idRoles),
                'path' => $row->path,
                'nombre' => $row->nombre,
                'icon' => $row->icon,
                'id_menu' => $row->id_menu,
                'id_modulo' => $row->id_modulo,
                'visitas' => (int) $row->visitas,
                'ultima_visita' => $row->ultima_visita?->toIso8601String(),
            ])
            ->all();
    }

    /**
     * @param  array{path: string, nombre?: string, icon?: string|null, id_menu?: int|null, id_modulo?: int|null}  $data
     * @return array{accion: string, favorito: array<string, mixed>|null}
     */
    public function toggle(int $idUsuario, int $idRoles, array $data): array
    {
        if (!$this->hasTable()) {
            throw new \RuntimeException('Ejecute las migraciones: php artisan migrate');
        }
        if ($idUsuario < 1) {
            throw new \InvalidArgumentException('Usuario no válido');
        }
        if ($idRoles < 1) {
            throw new \InvalidArgumentException('id_roles requerido');
        }

        $path = $this->normalizePath($data['path'] ?? '');
        if ($path === '' || $path === '/') {
            throw new \InvalidArgumentException('path requerido');
        }

        $existing = $this->baseQuery($idUsuario, $idRoles)->where('path', $path)->first();

        if ($existing) {
            $existing->delete();

            return ['accion' => 'eliminado', 'favorito' => null];
        }

        $nombre = trim((string) ($data['nombre'] ?? ''));
        if ($nombre === '') {
            $nombre = $path;
        }

        $maxOrden = (int) $this->baseQuery($idUsuario, $idRoles)->max('orden');

        $payload = [
            'id_usuario' => $idUsuario,
            'path' => $path,
            'id_menu' => !empty($data['id_menu']) ? (int) $data['id_menu'] : null,
            'id_modulo' => !empty($data['id_modulo']) ? (int) $data['id_modulo'] : null,
            'nombre' => $nombre,
            'icon' => $data['icon'] ?? null,
            'orden' => $maxOrden + 1,
            'visitas' => 1,
            'ultima_visita' => now(),
        ];
        if ($this->hasIdRolesColumn()) {
            $payload['id_roles'] = $idRoles;
        }

        $row = SeguridadUsuarioMenuFavorito::create($payload);

        return [
            'accion' => 'agregado',
            'favorito' => [
                'id_favorito' => $row->id_favorito,
                'id_roles' => $idRoles,
                'path' => $row->path,
                'nombre' => $row->nombre,
                'icon' => $row->icon,
                'id_menu' => $row->id_menu,
                'id_modulo' => $row->id_modulo,
                'visitas' => (int) $row->visitas,
                'ultima_visita' => $row->ultima_visita?->toIso8601String(),
            ],
        ];
    }

    public function registrarVisita(int $idUsuario, int $idRoles, string $path, ?string $nombre = null): void
    {
        if (!$this->hasTable() || $idUsuario < 1 || $idRoles < 1) {
            return;
        }

        $path = $this->normalizePath($path);
        if ($path === '' || $path === '/' || preg_match('#^https?://#i', $path) || str_starts_with($path, '#')) {
            return;
        }

        $row = $this->baseQuery($idUsuario, $idRoles)->where('path', $path)->first();

        if ($row) {
            $row->visitas = (int) $row->visitas + 1;
            $row->ultima_visita = now();
            if ($nombre && trim($nombre) !== '') {
                $row->nombre = trim($nombre);
            }
            $row->save();
        }
    }
}
