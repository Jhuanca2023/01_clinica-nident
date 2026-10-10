<?php
/**
 * seed_royal_menu_clean.php
 * Organiza el sidebar de Royal Masajes: actualiza íconos y orden
 * de los módulos ya asignados, y verifica que todos los menús
 * de Royal estén correctamente creados y asignados al rol 1.
 */

use Illuminate\Support\Facades\DB;

$ID_ROL = 1; // ACCESO GENERAL

// ── Módulos que queremos mantener visibles con orden limpio ──────────
// [id_modulo, nombre_display, icon_material, orden_sidebar]
$modulosRoyal = [
    // Sistema
    [4,  'Administracion',        'manage_accounts',         1],
    [33, 'Menu',                  'dynamic_feed',            2],
    [39, 'Ordernar Admin',        'compare_arrows',          3],

    // Web — Inicio
    [54, 'Slider',                'slideshow',               10],
    [72, 'Menú Web',              'navigation',              11],

    // Web — Contenido
    [27, 'Servicios',             'spa',                     20],
    [49, 'Galeria',               'photo_library',           21],
    [29, 'Testimonios',           'format_quote',            22],
    [73, 'Promo Banner',          'campaign',                23],

    // Web — Páginas
    [42, 'Nosotros',              'diversity_3',             30],
    [53, 'Contactos',             'connect_without_contact', 31],

    // Publicaciones
    [51, 'Publicaciones',         'article',                 40],

    // Footer
    [50, 'Footer',                'web_asset',               50],

    // Mensajes
    [52, 'Mensajes de Contactos', 'mark_email_unread',       60],

    // Accesos
    [5,  'Accesos',               'security',                70],
    [17, 'Metadatos de Página',   'av_timer',                80],
];

// ── Todos los menús para Royal Masajes ──────────────────────────────
// [id_modulo, nombre_menu, url, orden]
$menus = [
    // Administracion (id=4)
    [4,  'Usuarios',              '/usuarios/index',              1],
    [4,  'Roles',                 '/roles/index',                 2],
    [4,  'Perfil',                '/perfiles/index',              3],
    [4,  'Menú',                  '/menu/index',                  4],
    [4,  'Ordenar Admin',         '/menu/orden',                  5],
    [4,  'Metadatos de Página',   '/metadatospagina/index',       6],

    // Slider (id=54)
    [54, 'Slider',                '/slider/index',                1],

    // Menú Web (id=72)
    [72, 'Menú de Navegación',    '/header/index',                1],

    // Servicios (id=27)
    [27, 'Servicios',             '/experiencias/index',          1],
    [27, 'Planes',                '/planes/index',                2],
    [27, 'Por qué Elegirnos',     '/porque_elejirnos/index',      3],
    [27, 'FAQ',                   '/faq/index',                   4],

    // Galeria (id=49)
    [49, 'Galería',               '/pagina-galeria/index',        1],

    // Testimonios (id=29)
    [29, 'Testimonios',           '/testimonios/index',           1],

    // Promo Banner (id=73)
    [73, 'Promo Banner',          '/promo-banner/index',          1],

    // Nosotros (id=42)
    [42, 'Página Nosotros',       '/pagina-nosotros/index',       1],
    [42, 'Nuestro Equipo',        '/nuestro_equipo/index',        2],
    [42, 'Contadores',            '/contadores/index',            3],

    // Contactos (id=53)
    [53, 'Página Contacto',       '/pagina-contacto/index',       1],
    [53, 'Mensajes Recibidos',    '/contacto-mensajes/index',     2],

    // Publicaciones (id=51)
    [51, 'Publicaciones',         '/publicaciones/index',         1],

    // Footer (id=50)
    [50, 'Footer',                '/footer/index',                1],

    // Mensajes (id=52)
    [52, 'Mensajes',              '/contacto-mensajes/index',     1],

    // Accesos (id=5)
    [5,  'Usuarios',              '/usuarios/index',              1],
    [5,  'Perfil',                '/perfiles/index',              2],
    [5,  'Roles',                 '/roles/index',                 3],

    // Metadatos (id=17)
    [17, 'Metadatos SEO',         '/metadatospagina/index',       1],
];

echo "\n============================================\n";
echo " ROYAL MASAJES — ORGANIZAR MENÚ ADMIN\n";
echo "============================================\n";

// 1. Actualizar ícono y orden de módulos
echo "\n[1] Actualizando módulos...\n";
foreach ($modulosRoyal as [$id, $nombre, $icon, $orden]) {
    DB::table('sistema_modulo')->where('id_modulo', $id)->update([
        'nombre' => $nombre,
        'Icon'   => $icon,
        'orden'  => $orden,
    ]);
    // Asegurar asignado al rol
    if (!DB::table('seguridad_roles_modulo')->where('id_roles',$ID_ROL)->where('id_modulo',$id)->exists()) {
        DB::table('seguridad_roles_modulo')->insert(['id_roles'=>$ID_ROL,'id_modulo'=>$id]);
    }
    echo "  ✓ $nombre (orden=$orden)\n";
}

// 2. Crear menús si no existen
echo "\n[2] Creando/verificando menús...\n";
foreach ($menus as [$idMod, $nombre, $url, $orden]) {
    $ex = DB::table('sistema_menu')
        ->where('id_modulo', $idMod)
        ->where('nombre', $nombre)
        ->first();
    if ($ex) {
        DB::table('sistema_menu')->where('id_menu', $ex->id_menu)
            ->update(['url'=>$url,'orden'=>$orden,'Activo'=>'S','updated_at'=>now()]);
        $idMenu = $ex->id_menu;
    } else {
        $idMenu = DB::table('sistema_menu')->insertGetId([
            'id_modulo'=>$idMod, 'id_menu_padre'=>null,
            'nombre'=>$nombre, 'url'=>$url,
            'Activo'=>'S', 'orden'=>$orden,
            'created_at'=>now(), 'updated_at'=>now(),
        ]);
        echo "  + Creado: $nombre → $url\n";
    }
    // Asignar al rol
    if (!DB::table('seguridad_roles_menu')->where('id_roles',$ID_ROL)->where('id_menu',$idMenu)->exists()) {
        DB::table('seguridad_roles_menu')->insert(['id_roles'=>$ID_ROL,'id_menu'=>$idMenu]);
    }
}

// 3. Limpiar caché
echo "\n[3] Limpiando caché sidebar...\n";
DB::table('seguridad_roles_sidebar_orden')->where('id_roles',$ID_ROL)->delete();

echo "\n============================================\n";
echo " ✅ COMPLETADO — Recarga el navegador\n";
echo "============================================\n\n";

// 4. Resumen final
echo "MÓDULOS ACTIVOS EN SIDEBAR (rol=$ID_ROL):\n";
$mods = DB::table('seguridad_roles_modulo')
    ->join('sistema_modulo','sistema_modulo.id_modulo','=','seguridad_roles_modulo.id_modulo')
    ->where('id_roles',$ID_ROL)
    ->orderBy('sistema_modulo.orden')
    ->select('sistema_modulo.id_modulo','sistema_modulo.nombre','sistema_modulo.Icon','sistema_modulo.orden')
    ->get();
foreach ($mods as $m) {
    echo sprintf("  [%2d] %-30s icon=%-20s orden=%s\n",
        $m->id_modulo, $m->nombre, $m->Icon ?? '-', $m->orden ?? '-');
}
