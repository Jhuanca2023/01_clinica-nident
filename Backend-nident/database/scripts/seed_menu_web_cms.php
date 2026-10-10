<?php
/**
 * Script: seed_menu_web_cms.php
 * Crea el módulo "Sitio Web CMS" con todos los submenús organizados
 * para Royal Masajes y los asigna al rol administrador (id_roles = 1).
 *
 * Ejecutar con: php artisan tinker
 *   require database_path('scripts/seed_menu_web_cms.php');
 */

use Illuminate\Support\Facades\DB;

$ROL_ADMIN = 1; // ACCESO GENERAL

// ─────────────────────────────────────────────────────────────────────────────
// MÓDULOS A CREAR (agrupaciones del sidebar)
// ─────────────────────────────────────────────────────────────────────────────
$modulos = [
    // [ nombre, Icon, Orden ]
    'WEB_INICIO'      => ['🏠 Inicio & Slider',          'home',              23],
    'WEB_NOSOTROS'    => ['👥 Sobre Nosotros',            'people',            24],
    'WEB_SERVICIOS'   => ['💆 Masajes & Servicios',       'spa',               25],
    'WEB_GALERIA'     => ['🖼️ Galería',                  'photo_library',     26],
    'WEB_CONTACTO'    => ['📞 Contacto & Reservas',       'contact_mail',      27],
    'WEB_PUBLICACIONES'=> ['📰 Publicaciones',            'article',           28],
    'WEB_GLOBAL'      => ['⚙️ Configuración Web',         'settings',          29],
    'WEB_MENSAJES'    => ['✉️ Mensajes Recibidos',        'mark_email_unread', 30],
];

// ─────────────────────────────────────────────────────────────────────────────
// MENÚS POR MÓDULO
// ─────────────────────────────────────────────────────────────────────────────
$menus = [
    'WEB_INICIO' => [
        // [ nombre, url, Icon, orden ]
        ['Slider / Carrusel',      '/slider/index',          'view_carousel',   1],
        ['Promo Banner',           '/promo-banner/index',    'campaign',        2],
        ['Contadores',             '/contadores/index',      'bar_chart',       3],
        ['Experiencias (cards)',   '/experiencias/index',    'auto_awesome',    4],
        ['Planes & Precios',       '/planes/index',          'price_check',     5],
        ['Publicaciones Recientes','/publicaciones/index',   'rss_feed',        6],
    ],
    'WEB_NOSOTROS' => [
        ['Página Sobre Nosotros',  '/about/index',                 'info',           1],
        ['Características',        '/about-caracteristica/index',  'checklist',      2],
        ['Nuestro Equipo',         '/nuestro_equipo/index',        'groups',         3],
        ['Testimonios',            '/testimonios/index',           'format_quote',   4],
        ['¿Por qué Elegirnos?',   '/porque_elejirnos/index',      'verified',       5],
        ['Confianza Items',        '/confianza-item/index',        'shield',         6],
        ['Página Nosotros (CMS)',  '/pagina-nosotros/index',       'description',    7],
    ],
    'WEB_SERVICIOS' => [
        ['Masajes & Experiencias', '/experiencias/index',         'spa',            1],
        ['Servicios Web',          '/servicios/index',            'medical_services',2],
        ['Preguntas Frecuentes',   '/faq/index',                  'quiz',           3],
        ['Beneficios',             '/beneficio/index',            'stars',          4],
        ['Metodología',            '/metodologia/index',          'account_tree',   5],
    ],
    'WEB_GALERIA' => [
        ['Galería de Imágenes',    '/pagina-galeria/index',       'photo_library',  1],
        ['Portafolio',             '/portafolio/index',           'collections',    2],
        ['Carrusel',               '/carrusel/index',             'view_carousel',  3],
        ['Videos',                 '/videos/index',               'smart_display',  4],
    ],
    'WEB_CONTACTO' => [
        ['Página de Contacto',     '/pagina-contacto/index',      'contact_page',   1],
        ['Contacto Landing',       '/contacto-landing/index',     'touch_app',      2],
        ['Libro de Reclamaciones', '/libro_reclamo/index',        'menu_book',      3],
    ],
    'WEB_PUBLICACIONES' => [
        ['Publicaciones / Blog',   '/publicaciones/index',        'article',        1],
    ],
    'WEB_GLOBAL' => [
        ['Menú & Navegación',      '/header/index',               'menu',           1],
        ['Footer',                 '/footer/index',               'web_asset',      2],
        ['Metadatos SEO',          '/metadatospagina/index',      'manage_search',  3],
        ['Favicon',                '/configuracion/favicon',      'image',          4],
        ['Clientes',               '/clientes/index',             'business',       5],
        ['Eventos',                '/eventos/index',              'event',          6],
        ['Ejemplares',             '/ejemplares/index',           'library_books',  7],
    ],
    'WEB_MENSAJES' => [
        ['Mensajes de Contacto',   '/contacto-mensajes/index',    'email',          1],
    ],
];

// ─────────────────────────────────────────────────────────────────────────────
// EJECUCIÓN
// ─────────────────────────────────────────────────────────────────────────────

$createdModulos = [];
$createdMenus   = [];

echo "=== Creando módulos CMS Web ===\n";

foreach ($modulos as $key => $mod) {
    [$nombre, $icon, $orden] = $mod;

    // Buscar si ya existe por nombre exacto
    $existing = DB::table('sistema_modulo')->where('nombre', $nombre)->first();

    if ($existing) {
        echo "  ⚠  Módulo ya existe: {$nombre} (id:{$existing->id_modulo})\n";
        $createdModulos[$key] = $existing->id_modulo;
    } else {
        $id = DB::table('sistema_modulo')->insertGetId([
            'nombre'         => $nombre,
            'url'            => null,
            'Activo'         => 'S',
            'Icon'           => $icon,
            'Nivel'          => 1,
            'Orden'          => $orden,
            'orden_sidebar'  => $orden,
            'expanded'       => 0,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        echo "  ✓  Módulo creado: {$nombre} (id:{$id})\n";
        $createdModulos[$key] = $id;
    }

    // Asignar módulo al rol administrador si no está
    $rolMod = DB::table('seguridad_roles_modulo')
        ->where('id_roles', $ROL_ADMIN)
        ->where('id_modulo', $createdModulos[$key])
        ->first();

    if (!$rolMod) {
        DB::table('seguridad_roles_modulo')->insert([
            'id_roles'   => $ROL_ADMIN,
            'id_modulo'  => $createdModulos[$key],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "     → Asignado al rol administrador\n";
    }
}

echo "\n=== Creando ítems de menú ===\n";

foreach ($menus as $modKey => $items) {
    $id_modulo = $createdModulos[$modKey] ?? null;
    if (!$id_modulo) {
        echo "  ✗  Módulo no encontrado para key: {$modKey}\n";
        continue;
    }

    foreach ($items as $item) {
        [$nombre, $url, $icon, $orden] = $item;

        // Buscar si ya existe por url
        $existing = DB::table('sistema_menu')->where('url', $url)->first();

        if ($existing) {
            echo "  ⚠  Menú ya existe: {$nombre} ({$url})\n";
            $id_menu = $existing->id_menu;
            // Actualizar módulo por si estaba mal asignado
            DB::table('sistema_menu')->where('id_menu', $id_menu)->update([
                'id_modulo' => $id_modulo,
                'Icon'      => $icon,
                'orden'     => $orden,
                'Activo'    => 'S',
            ]);
        } else {
            $id_menu = DB::table('sistema_menu')->insertGetId([
                'id_modulo'      => $id_modulo,
                'id_menu_padre'  => null,
                'nombre'         => $nombre,
                'url'            => $url,
                'Activo'         => 'S',
                'orden'          => $orden,
                'orden_sidebar'  => $orden,
                'Icon'           => $icon,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            echo "  ✓  Menú creado: {$nombre} → {$url} (id:{$id_menu})\n";
        }

        // Asignar menú al rol administrador
        $rolMenu = DB::table('seguridad_roles_menu')
            ->where('id_roles', $ROL_ADMIN)
            ->where('id_menu', $id_menu)
            ->first();

        if (!$rolMenu) {
            DB::table('seguridad_roles_menu')->insert([
                'id_roles' => $ROL_ADMIN,
                'id_menu'  => $id_menu,
            ]);
        }

        $createdMenus[] = $id_menu;
    }
}

echo "\n=== Resumen ===\n";
echo "  Módulos CMS web: " . count($createdModulos) . "\n";
echo "  Menús CMS web:   " . count($createdMenus) . "\n";
echo "\n✅ Listo. Recarga el administrador y los verás en el sidebar.\n";
echo "   Si el sidebar está cacheado, cierra sesión y vuelve a entrar.\n";
