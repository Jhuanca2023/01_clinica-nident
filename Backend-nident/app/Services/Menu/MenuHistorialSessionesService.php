<?php

namespace App\Services\Menu;

use App\Models\MenuHistorialSessiones;
use Illuminate\Support\Facades\Schema;

class MenuHistorialSessionesService
{
    public const MAX_CHIPS = 6;

    /** Rutas que no deben aparecer como chips frecuentes. */
    private const EXCLUDE_PATHS = [
        '/',
        '/dashboard',
        '/dashboard/default',
        '/profile/index',
        '/signin',
        '/login',
    ];

    public function hasTable(): bool
    {
        return Schema::hasTable('menu_historial_sessiones');
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

    /** @return array<int, array<string, mixed>> */
    public function listarTop(int $idUsuario, int $limit = self::MAX_CHIPS): array
    {
        if (! $this->hasTable() || $idUsuario < 1) {
            return [];
        }

        $limit = max(1, min(12, $limit));

        return MenuHistorialSessiones::query()
            ->where('id_usuario', $idUsuario)
            ->where('visitas', '>', 0)
            ->whereNotIn('path', self::EXCLUDE_PATHS)
            ->orderByDesc('visitas')
            ->orderByDesc('ultima_visita')
            ->limit($limit)
            ->get()
            ->map(fn (MenuHistorialSessiones $row) => $this->format($row))
            ->all();
    }

    /**
     * Registra una visita y recorta el historial del usuario a un tope razonable.
     *
     * @return array{items: array<int, array<string, mixed>>, registered: bool}
     */
    public function registrar(int $idUsuario, string $path, ?string $titulo = null, ?string $icon = null): array
    {
        if (! $this->hasTable() || $idUsuario < 1) {
            return ['items' => [], 'registered' => false];
        }

        $path = $this->normalizePath($path);
        if ($path === '' || in_array($path, self::EXCLUDE_PATHS, true)) {
            return ['items' => $this->listarTop($idUsuario), 'registered' => false];
        }

        $titulo = trim((string) $titulo);
        if ($titulo === '') {
            $titulo = $this->guessTitle($path);
        }

        $row = MenuHistorialSessiones::query()->firstOrNew([
            'id_usuario' => $idUsuario,
            'path' => $path,
        ]);

        $row->titulo = $titulo;
        if ($icon !== null && $icon !== '') {
            $row->icon = $icon;
        }
        $row->visitas = (int) $row->visitas + 1;
        $row->ultima_visita = now();
        $row->save();

        $this->prune($idUsuario);

        return [
            'items' => $this->listarTop($idUsuario),
            'registered' => true,
        ];
    }

    /**
     * Elimina una ruta del historial del usuario, o todo su historial si $path es null.
     *
     * @return array<int, array<string, mixed>>
     */
    public function eliminar(int $idUsuario, ?string $path = null): array
    {
        if (! $this->hasTable() || $idUsuario < 1) {
            return [];
        }

        $query = MenuHistorialSessiones::query()->where('id_usuario', $idUsuario);
        if ($path !== null && trim($path) !== '') {
            $query->where('path', $this->normalizePath($path));
        }
        $query->delete();

        return $this->listarTop($idUsuario);
    }

    /** Mantiene como máximo 40 filas por usuario (los chips usan top 6). */
    private function prune(int $idUsuario): void
    {
        $keepIds = MenuHistorialSessiones::query()
            ->where('id_usuario', $idUsuario)
            ->orderByDesc('visitas')
            ->orderByDesc('ultima_visita')
            ->limit(40)
            ->pluck('id_historial');

        if ($keepIds->isEmpty()) {
            return;
        }

        MenuHistorialSessiones::query()
            ->where('id_usuario', $idUsuario)
            ->whereNotIn('id_historial', $keepIds)
            ->delete();
    }

    private function format(MenuHistorialSessiones $row): array
    {
        return [
            'id_historial' => $row->id_historial,
            'path' => $row->path,
            'titulo' => $row->titulo,
            'icon' => $row->icon,
            'visitas' => (int) $row->visitas,
            'ultima_visita' => $row->ultima_visita?->toIso8601String(),
        ];
    }

    private function guessTitle(string $path): string
    {
        $seg = collect(explode('/', trim($path, '/')))->filter()->last() ?: 'Menu';

        return str_replace(['-', '_'], ' ', ucwords($seg));
    }
}
