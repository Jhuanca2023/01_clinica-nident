<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menú admin "Sitio Web — Valento": asegura padres/hijos activos
 * y deja SKIP (planes, videos, maquinarias, etc.) en Activo=N.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sistema_menu')) {
            return;
        }

        $activoCol = Schema::hasColumn('sistema_menu', 'Activo')
            ? 'Activo'
            : (Schema::hasColumn('sistema_menu', 'activo') ? 'activo' : null);
        if (! $activoCol) {
            return;
        }

        $nombreCol = Schema::hasColumn('sistema_menu', 'nombre') ? 'nombre' : null;
        $urlCol = Schema::hasColumn('sistema_menu', 'url') ? 'url' : null;

        // Renombrar padre CMS si existe con nombre Liam / Sitio Web genérico
        if ($nombreCol) {
            DB::table('sistema_menu')
                ->where(function ($q) use ($nombreCol) {
                    $q->where($nombreCol, 'like', '%Liam%')
                        ->orWhere($nombreCol, 'Sitio Web')
                        ->orWhere($nombreCol, 'like', '%Vercceli%');
                })
                ->where(function ($q) use ($urlCol) {
                    if ($urlCol) {
                        $q->whereNull($urlCol)->orWhere($urlCol, '')->orWhere($urlCol, '#');
                    }
                })
                ->update([$nombreCol => 'Sitio Web — Valento']);
        }

        $activarUrls = [
            '/header/index',
            '/slider/index',
            '/categoria/index',
            '/popular/index',
            '/producto/index',
            '/producto-destacado/index',
            '/promo-bloque/index',
            '/lookbook/index',
            '/footer-banner/index',
            '/popup-promo/index',
            '/footer/index',
            '/whatsapp/index',
            '/ofertas/index',
            '/promocion/index',
            '/producto/importar',
            '/pedidos/index',
            '/clientes/index',
            '/clientes-ventas/index',
            '/balance_ventas/index',
            '/libro_reclamo/index',
            '/envios/index',
            '/pagina-nosotros/index',
            '/pagina-contacto/index',
            '/contacto-mensajes/index',
            '/configuracion/pasarela',
            '/metadatospagina/index',
            '/configuracion/favicon',
            '/configuracion/auth-proveedor',
        ];

        if ($urlCol) {
            DB::table('sistema_menu')
                ->whereIn($urlCol, $activarUrls)
                ->update([$activoCol => 'S']);
        }

        $skipUrls = [
            '/planes/index',
            '/videos/index',
            '/pagina-maquinarias/index',
            '/pagina-galeria/index',
            '/carrusel/index',
            '/nuestros-clientes/index',
            '/contadores/index',
            '/faq/index',
            '/testimonios/index',
            '/beneficio/index',
            '/home-coleccion/index',
        ];

        if ($urlCol) {
            DB::table('sistema_menu')
                ->whereIn($urlCol, $skipUrls)
                ->update([$activoCol => 'N']);
        }

        if ($nombreCol) {
            DB::table('sistema_menu')
                ->whereIn($nombreCol, [
                    'Planes', 'Videos', 'Maquinarias', 'Blog', 'Spa', 'Campus',
                    'Imprenta', 'Rodantes', 'Galería de maquinarias', 'Nuestros Clientes',
                    'Contadores', 'FAQ', 'Testimonios',
                ])
                ->update([$activoCol => 'N']);
        }
    }

    public function down(): void
    {
        // Menú de marca: no se revierte automáticamente.
    }
};
