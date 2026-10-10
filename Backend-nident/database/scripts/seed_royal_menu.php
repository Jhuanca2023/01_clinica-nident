<?php
/**
 * seed_royal_menu.php
 * Crea todos los módulos + menús del administrador web Royal Masajes
 * y los asigna al rol 1 (ACCESO GENERAL / Administrador Principal).
 *
 * Ejecutar desde Tinker:
 *   require database_path('scripts/seed_royal_menu.php');
 */

use Illuminate\Support\Facades\DB;

$ID_ROL_ADMIN = 1; // ACCESO GENERAL

/* ══════════════════════════════════════════════════════════════
   HELPER: crear o actualizar módulo
══════════════════════════════════════════════════════════════ */
function upsertModulo(string $nombre, string $icon, int $orden): int
{
    $existing = DB::table('sistema_modulo')->where('nombre', $nombre)->first();
    if ($existing) {
        DB::table('sistema_modulo')
            ->where('id_modulo', $existing->id_modulo)
            ->update(['Icon' => $icon, 'orden' => $orden]);
        echo "  [MOD] Actualizado: $nombre (id={$existing->id_modulo})\n";
        return $existing->id_modulo;
    }
    $id = DB::table('sistema_modulo')->insertGetId([
        'nombre' => $nombre,
        'Icon'   => $icon,
        'orden'  => $orden,
    ]);
    echo "  [MOD] Creado: $nombre (id=$id)\n";
    return $id;
}

/* ══════════════════════════════════════════════════════════════
   HELPER: crear o actualizar menú
══════════════════════════════════════════════════════════════ */
function upsertMenu(int $idModulo, string $nombre, ?string $url, int $orden): int
{
    $existing = DB::table('sistema_menu')
        ->where('id_modulo', $idModulo)
        ->where('nombre', $nombre)
        ->first();
    if ($existing) {
        DB::table('sistema_menu')
            ->where('id_menu', $existing->id_menu)
            ->update(['url' => $url, 'orden' => $orden, 'Activo' => 'S']);
        echo "    [MENU] Actualizado: $nombre → $url\n";
        return $existing->id_menu;
    }
    $id = DB::table('sistema_menu')->insertGetId([
        'id_modulo'    => $idModulo,
        'id_menu_padre'=> null,
        'nombre'       => $nombre,
        'url'          => $url,
        'Activo'       => 'S',
        'orden'        => $orden,
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);
    echo "    [MENU] Creado: $nombre → $url\n";
    return $id;
}

/* ══════════════════════════════════════════════════════════════
   HELPER: asignar módulo al rol
══════════════════════════════════════════════════════════════ */
function asignarModuloRol(int $idRol, int $idModulo): void
{
    $exists = DB::table('seguridad_roles_modulo')
        ->where('id_roles', $idRol)
        ->where('id_modulo', $idModulo)
        ->exists();
    if (!$exists) {
        DB::table('seguridad_roles_modulo')->insert([
            'id_roles'  => $idRol,
            'id_modulo' => $idModulo,
        ]);
        echo "    [ROL] Módulo $idModulo asignado al rol $idRol\n";
    }
}

/* ══════════════════════════════════════════════════════════════
   HELPER: asignar menú al rol
══════════════════════════════════════════════════════════════ */
function asignarMenuRol(int $idRol, int $idMenu): void
{
    $exists = DB::table('seguridad_roles_menu')
        ->where('id_roles', $idRol)
        ->where('id_menu', $idMenu)
        ->exists();
    if (!$exists) {
        DB::table('seguridad_roles_menu')->insert([
            'id_roles' => $idRol,
            'id_menu'  => $idMenu,
        ]);
    }
}

/* ══════════════════════════════════════════════════════════════
   DEFINICIÓN DE MÓDULOS + MENÚS
   Estructura: [ nombre_modulo, icon_material, orden, [ [nombre_menu, url, orden] ] ]
══════════════════════════════════════════════════════════════ */
$grupos = [

    // ── SISTEMA ──────────────────────────────────────────────
    ['Administracion', 'manage_accounts', 1, [
        ['Usuarios',          '/usuarios/index',               1],
        ['Roles',             '/roles/index',                  2],
        ['Perfil',            '/perfiles/index',               3],
        ['Menú',              '/menu/index',                   4],
        ['Ordenar Admin',     '/menu/orden',                   5],
        ['Metadatos de Página','/metadatospagina/index',       6],
    ]],

    // ── WEB — INICIO ─────────────────────────────────────────
    ['Slider',          'slideshow',            10, [
        ['Slider',            '/slider/index',                 1],
    ]],

    ['Menú Web',        'navigation',           11, [
        ['Menú de Navegación','/header/index',                 1],
    ]],

    // ── WEB — CONTENIDO PRINCIPAL ────────────────────────────
    ['Servicios',       'spa',                  20, [
        ['Servicios (tarjetas)','/experiencias/index',          1],
        ['Planes',             '/planes/index',                 2],
        ['Por qué Elegirnos',  '/porque_elejirnos/index',      3],
        ['FAQ',                '/faq/index',                   4],
    ]],

    ['Galeria',         'photo_library',        21, [
        ['Galería',           '/pagina-galeria/index',         1],
    ]],

    ['Testimonios',     'format_quote',         22, [
        ['Testimonios',       '/testimonios/index',            1],
    ]],

    // ── WEB — PÁGINAS INTERNAS ────────────────────────────────
    ['Nosotros',        'diversity_3',          30, [
        ['Página Nosotros',   '/pagina-nosotros/index',        1],
        ['Nuestro Equipo',    '/nuestro_equipo/index',         2],
        ['Contadores',        '/contadores/index',             3],
        ['About Sección',     '/about/index',                  4],
    ]],

    ['Contactos',       'connect_without_contact', 31, [
        ['Página Contacto',   '/pagina-contacto/index',        1],
        ['Mensajes Recibidos','/contacto-mensajes/index',      2],
    ]],

    // ── WEB — PUBLICACIONES ───────────────────────────────────
    ['Publicaciones',   'article',              40, [
        ['Publicaciones',     '/publicaciones/index',          1],
    ]],

    // ── WEB — PROMO / BANNER ──────────────────────────────────
    ['Promo Banner',    'campaign',             45, [
        ['Promo Banner',      '/promo-banner/index',           1],
    ]],

    // ── WEB — FOOTER ─────────────────────────────────────────
    ['Footer',          'web_asset',            50, [
        ['Footer',            '/footer/index',                 1],
    ]],

    // ── MENSAJES DE CONTACTO ──────────────────────────────────
    ['Mensajes de Contactos', 'mark_email_unread', 60, [
        ['Mensajes',          '/contacto-mensajes/index',      1],
    ]],

];

/* ══════════════════════════════════════════════════════════════
   EJECUCIÓN
══════════════════════════════════════════════════════════════ */
echo "\n========================================\n";
echo " SEED ROYAL MASAJES — MENÚ ADMIN\n";
echo "========================================\n\n";

foreach ($grupos as [$nombre, $icon, $orden, $menus]) {
    echo "\n▶ Módulo: $nombre\n";

    $idModulo = upsertModulo($nombre, $icon, $orden);
    asignarModuloRol($ID_ROL_ADMIN, $idModulo);

    foreach ($menus as [$nombreMenu, $url, $ordenMenu]) {
        $idMenu = upsertMenu($idModulo, $nombreMenu, $url, $ordenMenu);
        asignarMenuRol($ID_ROL_ADMIN, $idMenu);
    }
}

/* ══════════════════════════════════════════════════════════════
   TAMBIÉN ASIGNAR módulos existentes que ya tienen ID fijo
══════════════════════════════════════════════════════════════ */
// Módulos que ya existen en DB con IDs conocidos
$modulosExistentes = [39, 40, 42, 43, 48, 49, 50, 51, 52, 53, 54];
echo "\n\n▶ Asignando módulos existentes al rol $ID_ROL_ADMIN...\n";
foreach ($modulosExistentes as $idMod) {
    asignarModuloRol($ID_ROL_ADMIN, $idMod);
}

/* ══════════════════════════════════════════════════════════════
   LIMPIAR CACHÉ del sidebar (si existe columna/tabla de caché)
══════════════════════════════════════════════════════════════ */
try {
    DB::table('seguridad_roles_sidebar_orden')
        ->where('id_roles', $ID_ROL_ADMIN)
        ->delete();
    echo "\n✓ Caché sidebar limpiada\n";
} catch (\Exception $e) {
    echo "\n! No se pudo limpiar caché sidebar: " . $e->getMessage() . "\n";
}

echo "\n========================================\n";
echo " ✅ COMPLETADO\n";
echo "========================================\n\n";
echo "Recarga el navegador en el admin para ver el menú actualizado.\n\n";
