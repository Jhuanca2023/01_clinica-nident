<?php

namespace App\Support;

use App\Models\WebPaginaNosotros;
use Illuminate\Support\Facades\Schema;

/** Contenido editable de /nosotros (admin ▸ Página Nosotros). Los valores por defecto son los textos originales de la web. */
class RossetyNosotros
{
    public static function defaults(): array
    {
        return [
            'hero' => ['crumb' => 'Nosotros', 'titulo' => 'Nosotros', 'subtitulo' => 'Detalles que enamoran, momentos que perduran.'],
            'historia' => [
                'eyebrow' => 'Nuestra historia',
                'titulo' => 'Un emprendimiento nacido en Lima, Perú',
                'parrafos' => [
                    'En Detalles Rossety creemos que un detalle bien hecho puede cambiar el día de alguien. Lo que empezó como una pasión por armar ramos y regalos para familiares y amigos, hoy es una tienda de regalos con ramos de rosas, peluches, cajas temáticas, accesorios y mucho más.',
                    'Bajo la dirección de Patricia Hancco, cada pedido se prepara a mano, con materiales seleccionados y mucho cariño, para que quien lo recibe sienta que alguien pensó especialmente en él o ella.',
                ],
                'lista' => [
                    'Ramos de rosas, tulipanes, girasoles y lirios con papel coreano, luces y mariposas.',
                    'Peluches de calidad: Stich, Snoopy, Kitty, Lotso, Pooh y muchos más.',
                    'Cajas, accesorios y complementos para quienes crean sus propios detalles.',
                    'Atención personalizada por WhatsApp: frase, colores y fecha a tu gusto.',
                ],
                'imagen' => '',
                'imagen_alt' => 'Equipo de Detalles Rossety preparando un ramo',
                'badge_num' => '+500',
                'badge_txt' => 'detalles entregados con amor',
            ],
            'stats' => [
                ['num' => '+500', 'label' => 'Detalles entregados'],
                ['num' => '+200', 'label' => 'Productos en catálogo'],
                ['num' => '7', 'label' => 'Líneas de productos'],
                ['num' => '100%', 'label' => 'Hecho con amor'],
            ],
            'valores' => [
                'eyebrow' => 'Lo que nos mueve',
                'titulo' => 'Misión, visión y valores',
                'items' => [
                    ['icono' => 'ph-target', 'titulo' => 'Misión', 'texto' => 'Crear detalles únicos y de calidad que acompañen los momentos más importantes de nuestros clientes, con atención cercana y precios justos.'],
                    ['icono' => 'ph-binoculars', 'titulo' => 'Visión', 'texto' => 'Ser la tienda de regalos y detalles más querida de Ayacucho, reconocida por su creatividad y su cariño en cada entrega.'],
                    ['icono' => 'ph-heart', 'titulo' => 'Valores', 'texto' => 'Cariño, puntualidad, creatividad, honestidad y compromiso con cada pedido, grande o pequeño.'],
                ],
            ],
            'lineas' => [
                'eyebrow' => 'Qué encontrarás',
                'titulo' => 'Nuestras líneas de productos',
                'items' => [
                    ['icono' => 'ph-flower-lotus', 'titulo' => 'Ramos y Detalles', 'sub' => '5 subcategorías', 'url' => '/tienda?cat=ramos'],
                    ['icono' => 'ph-paw-print', 'titulo' => 'Peluches', 'sub' => '6 subcategorías', 'url' => '/tienda?cat=peluches'],
                    ['icono' => 'ph-gift', 'titulo' => 'Cajas de Regalo', 'sub' => '5 subcategorías', 'url' => '/tienda?cat=cajas'],
                    ['icono' => 'ph-crown-simple', 'titulo' => 'Accesorios', 'sub' => '5 subcategorías', 'url' => '/tienda?cat=accesorios'],
                    ['icono' => 'ph-motorcycle', 'titulo' => 'Hot Wheels y Motos', 'sub' => '3 subcategorías', 'url' => '/tienda?cat=hotwheels'],
                    ['icono' => 'ph-balloon', 'titulo' => 'Globos y Complementos', 'sub' => '3 subcategorías', 'url' => '/tienda?cat=complementos'],
                    ['icono' => 'ph-sun', 'titulo' => 'Temporada Girasoles', 'sub' => '5 subcategorías', 'url' => '/tienda?cat=girasoles'],
                ],
            ],
        ];
    }

    /** Configuración guardada mezclada con los valores por defecto. */
    public static function config(): array
    {
        $saved = [];
        if (Schema::hasTable('web_pagina_nosotros') && Schema::hasColumn('web_pagina_nosotros', 'rossety_nosotros')) {
            $raw = WebPaginaNosotros::query()->value('rossety_nosotros');
            $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
            $saved = is_array($decoded) ? $decoded : [];
        }

        $d = self::defaults();
        $cfg = $d;
        $cfg['hero'] = array_merge($d['hero'], (array) ($saved['hero'] ?? []));
        $cfg['historia'] = array_merge($d['historia'], (array) ($saved['historia'] ?? []));
        foreach (['valores', 'lineas'] as $k) {
            $cfg[$k] = array_merge($d[$k], (array) ($saved[$k] ?? []));
            $cfg[$k]['items'] = is_array($cfg[$k]['items'])
                ? array_values(array_filter($cfg[$k]['items'], 'is_array')) : $d[$k]['items'];
        }
        foreach (['parrafos', 'lista'] as $k) {
            if (! is_array($cfg['historia'][$k])) {
                $cfg['historia'][$k] = $d['historia'][$k];
            }
        }
        if (isset($saved['stats']) && is_array($saved['stats'])) {
            $cfg['stats'] = array_values(array_filter($saved['stats'], 'is_array'));
        }
        $cfg['historia']['imagen_url'] = trim((string) $cfg['historia']['imagen']) !== ''
            ? CmsStorageUrl::forWeb($cfg['historia']['imagen']) : '';

        return $cfg;
    }
}
