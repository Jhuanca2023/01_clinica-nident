<?php

namespace App\Support;

/**
 * Rutas canónicas de imágenes de producto bajo public/storage_/.
 * No usar carpetas sueltas producto_principal/ en la raíz de public.
 */
class ProductoImagePath
{
    public const DIR_PRINCIPAL = 'storage_/producto_principal';

    public const DIR_FOTOS = 'storage_/producto_principal_fotos';

    public const DIR_CLIENTE = 'storage_/cliente/producto_principal';

    public static function normalize(?string $ruta): ?string
    {
        if ($ruta === null) {
            return null;
        }

        $ruta = trim(str_replace('\\', '/', $ruta));
        if ($ruta === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $ruta)) {
            return $ruta;
        }

        $ruta = ltrim($ruta, '/');

        // Tipografía errónea: storage/_/...
        if (str_starts_with(strtolower($ruta), 'storage/_')) {
            $ruta = 'storage_/' . substr($ruta, strlen('storage/_'));
        }

        // Legacy en raíz pública → bajo storage_/
        if (preg_match('#^producto_principal(?:_fotos)?/#i', $ruta)) {
            $ruta = 'storage_/' . $ruta;
        }

        // storage_cliente/... → storage_/cliente/...
        if (preg_match('#^storage_cliente/#i', $ruta)) {
            $ruta = 'storage_/cliente/' . substr($ruta, strlen('storage_cliente/'));
        }

        return $ruta;
    }

    public static function ensureUnderStorage(?string $ruta): ?string
    {
        $ruta = self::normalize($ruta);
        if ($ruta === null || preg_match('#^https?://#i', $ruta)) {
            return $ruta;
        }

        if (!str_starts_with($ruta, 'storage_/')) {
            $ruta = 'storage_/' . ltrim($ruta, '/');
        }

        return $ruta;
    }
}
