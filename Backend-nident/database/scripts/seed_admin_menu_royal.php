<?php
/**
 * Seed: Menú del administrador — Royal Masajes
 * Crea/actualiza módulos y menús del sidebar, y los asigna al rol 1 (ACCESO GENERAL)
 * Uso: php artisan tinker --execute="require database_path('scripts/seed_admin_menu_royal.php');"
 */

use Illuminate\Support\Facades\DB;

$ROL_ADMIN = 1;  // ACCESO GENERAL

/* ══════════════════════════════════════════════════════
   HELPER — upsert módulo y devolver id
══════════════════════════════════════════════════════ */
function upsertModulo($nombre, $icon, $orden, $activar = true) {
    $existing = DB::table('sistema_modulo')->where('nombre', $nombre)->first();
    if ($existing) {
        DB::table('sistema_modulo')->where('id_modulo', $existing->id_modulo)->update([
            'Icon'    => $icon,
            'Orden'   => $orden,
            'Activo'  => $activar ? 'S' : 'N',
            'updated_at' => now(),
        ]);
        return $existing->id_modulo;
    }
    return DB::table('sistema_modulo')->insertGetId([
        'nombre'     => $nombre,
        'Icon'       => $icon,
        'Orden'      => $orden,
        'Activo'     => $activar ? 'S' : 'N',
        'url'        => null,
        'Nivel'      => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/* ══════════════════════════════════════════════════════
   HELPER — upsert menú y devolver id
══════════════════════════════════════════════════════ */
function upsertMenu($id_modulo, $nombre, $url, $orden, $icon = null) {
    $existing = DB::table('sistema_menu')
        ->where('id_modulo', $id_modulo)
        ->where('url', $url)
        ->first();
    if ($existing) {
        DB::table('sistema_menu')->where('id_menu', $existing->id_menu)->update([
            'nombre'     => $nombre,
            'orden'      => $orden,
            'Activo'     => 'S',
            'Icon'       => $icon,
            'updated_at' => now(),
        ]);
        return $existing->id_menu;
    }
    return DB::table('sistema_menu')->insertGetId([
        'id_modulo'      => $id_modulo,
        'id_menu_padre'  => null,
        'nombre'         => $nombre,
        'url'            => $url,
        'orden'          => $orden,
        'Activo'         => 'S',
        'Icon'           => $icon,
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);
}

/* ══════════════════════════════════════════════════════
   HELPER — asignar módulo al rol (si no existe)
══════════════════════════════════════════════════════ */
function asignarModuloRol($id_roles, $id_modulo) {
    $exists = DB::table('seguridad_roles_modulo')
        ->where('id_roles', $id_roles)
        ->where('id_modulo', $id_modulo)
        ->exists();
    if (!$exists) {
        DB::table('seguridad_roles_modulo')->insert([
            'id_roles'  => $id_roles,
            'id_modulo' => $id_modulo,
        ]);
    }
}

/* ══════════════════════════════════════════════════════
   HELPER — asignar menú al rol (si no existe)
══════════════════════════════════════════════════════ */
function asignarMenuRol($id_roles, $id_menu) {
    $exists = DB::table('seguridad_roles_menu')
        ->where('id_roles', $id_roles)
        ->where('id_menu', $id_menu)
        ->exists();
    if (!$exists) {
        DB::table('seguridad_roles_menu')->insert([
            'id_roles' => $id_roles,
            'id_menu'  => $id_menu,
        ]);
    }
}

/* ══════════════════════════════════════════════════════════════════════════
   DEFINICIÓN DE MÓDULOS Y SUS MENÚS
   Formato: [ 'nombre', 'icon (Material)', orden, [ [nombre, url, orden], ... ] ]
══════════════════════════════════════════════════════════════════════════ */
$estructura = [

    /* ── 1. INICIO (Header, Slider, Carrusel, Promo Banner) ── */
    [
        'modulo' => ['🏠 Inicio & Header', 'home', 10],
        'menus'  => [
            ['Menú / Header',     '/header/index',       1],
            ['Slider Principal',  '/slider/index',       2],
            ['Carrusel',          '/carrusel/index',     3],
            ['Promo Banner',      '/promo-banner/index', 4],
        ],
    ],

    /* ── 2. SOBRE NOSOTROS ── */
    [
        'modulo' => ['👥 Sobre Nosotros', 'diversity_3', 20],
        'menus'  => [
            ['Sección Nosotros',      '/about/index',               1],
            ['Características',       '/about-caracteristica/index', 2],
            ['Página Nosotros',       '/pagina-nosotros/index',      3],
            ['¿Por qué elegirnos?',   '/porque_elejirnos/index',    4],
            ['Nuestro Equipo',        '/nuestro_equipo/index',      5],
            ['Contadores',            '/contadores/index',           6],
        ],
    ],

    /* ── 3. SERVICIOS / MASAJES ── */
    [
        'modulo' => ['💆 Masajes & Servicios', 'spa', 30],
        'menus'  => [
            ['Experiencias / Masajes', '/experiencias/index',   1],
            ['Servicios',             '/servicios/index',       2],
            ['Testimonios',           '/testimonios/index',     3],
            ['FAQ / Preguntas',       '/faq/index',             4],
        ],
    ],

    /* ── 4. PLANES ── */
    [
        'modulo' => ['💎 Planes & Precios', 'price_check', 40],
        'menus'  => [
            ['Planes',  '/planes/index', 1],
        ],
    ],

    /* ── 5. GALERÍA ── */
    [
        'modulo' => ['🖼️ Galería', 'photo_library', 50],
        'menus'  => [
            ['Galería (imágenes)', '/pagina-galeria/index', 1],
        ],
    ],

    /* ── 6. PUBLICACIONES / BLOG ── */
    [
        'modulo' => ['📰 Publicaciones / Blog', 'article', 60],
        'menus'  => [
            ['Publicaciones', '/publicaciones/index', 1],
        ],
    ],

    /* ── 7. CONTACTO ── */
    [
        'modulo' => ['📞 Contacto', 'connect_without_contact', 70],
        'menus'  => [
            ['Config. Contacto',  '/pagina-contacto/index',   1],
            ['Mensajes recibidos','/contacto-mensajes/index', 2],
            ['Contacto Landing',  '/contacto-landing/index',  3],
            ['Libro Reclamos',    '/libro_reclamo/index',     4],
        ],
    ],

    /* ── 8. FOOTER ── */
    [
        'modulo' => ['🦶 Footer & Cierre', 'vertical_align_bottom', 80],
        'menus'  => [
            ['Footer',  '/footer/index', 1],
        ],
    ],

    /* ── 9. METADATOS SEO ── */
    [
        'modulo' => ['🔍 SEO & Metadatos', 'manage_search', 90],
        'menus'  => [
            ['Metadatos de Página', '/metadatospagina/index', 1],
            ['Favicon',             '/configuracion/favicon', 2],
        ],
    ],

    /* ── 10. ADMINISTRACIÓN ── (módulo existente id:4) */
    [
        'modulo' => ['⚙️ Administración', 'settings', 100],
        'menus'  => [
            ['Menú del sistema',   '/menu/index',                       1],
            ['Objetos / Permisos', '/objetos/index',                    2],
            ['Etiquetas de menú',  '/administracion-etiquetas/index',   3],
            ['Backup DB',          '/backup/index',                     4],
            ['Acceso Google',      '/configuracion/auth-proveedor',     5],
            ['Orden del sidebar',  '/menu/orden',                       6],
        ],
    ],

    /* ── 11. ACCESOS / SEGURIDAD ── (módulo existente id:5) */
    [
        'modulo' => ['🔐 Accesos & Seguridad', 'security', 110],
        'menus'  => [
            ['Usuarios', '/usuarios/index', 1],
            ['Roles',    '/roles/index',    2],
            ['Perfiles', '/perfiles/index', 3],
        ],
    ],

];

/* ══════════════════════════════════════════════════════
   PROCESAMIENTO
══════════════════════════════════════════════════════ */
DB::beginTransaction();
try {
    foreach ($estructura as $item) {
        [$nombre, $icon, $orden] = $item['modulo'];

        // Crear/actualizar módulo
        $id_mod = upsertModulo($nombre, $icon, $orden, true);

        // Asignar módulo al rol admin
        asignarModuloRol($ROL_ADMIN, $id_mod);

        echo "✅ Módulo [{$id_mod}]: {$nombre}" . PHP_EOL;

        // Crear/actualizar cada menú
        foreach ($item['menus'] as $menu) {
            [$mnombre, $murl, $morden] = $menu;
            $id_menu = upsertMenu($id_mod, $mnombre, $murl, $morden);
            asignarMenuRol($ROL_ADMIN, $id_menu);
            echo "   → [{$id_menu}] {$mnombre} ({$murl})" . PHP_EOL;
        }
    }

    /* ── Activar módulos existentes que estaban en Activo:N ── */
    $activar_ids = [42, 43, 40, 33, 29, 32, 31, 28, 30, 51, 48, 49, 54, 50, 52, 53, 17, 39];
    DB::table('sistema_modulo')->whereIn('id_modulo', $activar_ids)->update([
        'Activo' => 'S', 'updated_at' => now(),
    ]);
    echo PHP_EOL . "✅ Módulos existentes activados" . PHP_EOL;

    /* ── Activar todos los menús de esos módulos ── */
    DB::table('sistema_menu')->whereIn('id_modulo', $activar_ids)->update([
        'Activo' => 'S', 'updated_at' => now(),
    ]);

    /* ── Asignar módulos existentes al rol admin ── */
    foreach ($activar_ids as $id_mod) {
        asignarModuloRol($ROL_ADMIN, $id_mod);
        // Asignar todos sus menús
        $menus = DB::table('sistema_menu')->where('id_modulo', $id_mod)->get();
        foreach ($menus as $m) {
            asignarMenuRol($ROL_ADMIN, $m->id_menu);
        }
    }

    DB::commit();
    echo PHP_EOL . "🎉 COMPLETADO — Todos los módulos y menús asignados al rol ADMIN (id:1)" . PHP_EOL;

} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
