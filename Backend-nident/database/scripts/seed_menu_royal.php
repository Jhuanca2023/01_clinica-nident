<?php
/**
 * Script: seed_menu_royal.php
 * Inserta el módulo "Sitio Web" con todos los sub-menús de Royal Masajes
 * y los asigna al rol 1 (ACCESO GENERAL = Administrador).
 *
 * Ejecutar: php artisan tinker --no-interaction
 *           require database_path('scripts/seed_menu_royal.php');
 */

use Illuminate\Support\Facades\DB;

$rolId = 1; // ACCESO GENERAL (Administrador)

// ══════════════════════════════════════════════════════════════
//  MÓDULO PRINCIPAL: 🌐 Sitio Web Royal Masajes
// ══════════════════════════════════════════════════════════════
$modNombre = '🌐 Sitio Web';

$modExiste = DB::table('sistema_modulo')->where('nombre', $modNombre)->first();
if ($modExiste) {
    $idMod = $modExiste->id_modulo;
    echo "✔ Módulo ya existe: ID {$idMod}\n";
} else {
    $idMod = DB::table('sistema_modulo')->insertGetId([
        'nombre'     => $modNombre,
        'Icon'       => 'language',
        'orden'      => 1,
        'Activo'     => 'S',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✔ Módulo creado: ID {$idMod}\n";
}

// Asignar módulo al rol si no existe
$modRolExiste = DB::table('seguridad_roles_modulo')
    ->where('id_roles', $rolId)->where('id_modulo', $idMod)->exists();
if (!$modRolExiste) {
    DB::table('seguridad_roles_modulo')->insert([
        'id_roles'  => $rolId,
        'id_modulo' => $idMod,
    ]);
    echo "✔ Módulo asignado al rol {$rolId}\n";
} else {
    echo "✔ Módulo ya asignado al rol\n";
}

// ══════════════════════════════════════════════════════════════
//  DEFINICIÓN DE MENÚS
//  [ nombre, url, icon, orden ]
// ══════════════════════════════════════════════════════════════
$menus = [
    // ── Estructura del sitio ──────────────────────────────────
    ['🔝 Menú de Navegación',      '/header/index',            'menu',                   1],
    ['🎠 Hero Slider',             '/slider/index',            'view_carousel',           2],
    ['🔻 Footer',                  '/footer/index',            'vertical_align_bottom',   3],
    ['📢 Promo Banner',            '/promo-banner/index',      'campaign',                4],

    // ── Contenido ─────────────────────────────────────────────
    ['💆 Masajes & Servicios',     '/experiencias/index',      'spa',                     5],
    ['💎 Planes & Precios',        '/planes/index',            'workspace_premium',       6],
    ['📸 Galería',                 '/pagina-galeria/index',    'photo_library',           7],
    ['📝 Publicaciones (Blog)',     '/publicaciones/index',     'article',                 8],
    ['⭐ Testimonios',             '/testimonios/index',       'star',                    9],
    ['👥 Nuestro Equipo',          '/nuestro_equipo/index',    'groups',                  10],

    // ── Páginas ───────────────────────────────────────────────
    ['ℹ️ Página: Sobre Nosotros',  '/pagina-nosotros/index',   'info',                    11],
    ['📞 Página: Contacto',        '/pagina-contacto/index',   'contact_page',            12],
    ['📋 Contacto Landing',        '/contacto-landing/index',  'contact_mail',            13],

    // ── Mensajes ──────────────────────────────────────────────
    ['✉️ Mensajes Recibidos',      '/contacto-mensajes/index', 'mark_email_unread',       14],
];

$insertedIds = [];

foreach ($menus as [$nombre, $url, $icon, $orden]) {
    // Verificar si ya existe por URL
    $existe = DB::table('sistema_menu')->where('url', $url)->first();
    if ($existe) {
        echo "  ↩ Ya existe: {$nombre} ({$url}) — ID {$existe->id_menu}\n";
        $insertedIds[] = $existe->id_menu;
        // Actualizar el módulo por si acaso
        DB::table('sistema_menu')->where('id_menu', $existe->id_menu)->update([
            'id_modulo' => $idMod,
            'nombre'    => $nombre,
            'Icon'      => $icon,
            'orden'     => $orden,
            'Activo'    => 'S',
        ]);
    } else {
        $id = DB::table('sistema_menu')->insertGetId([
            'id_modulo'     => $idMod,
            'id_menu_padre' => null,
            'nombre'        => $nombre,
            'url'           => $url,
            'Icon'          => $icon,
            'orden'         => $orden,
            'orden_sidebar' => $orden,
            'Activo'        => 'S',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
        $insertedIds[] = $id;
        echo "  ✔ Creado: {$nombre} => ID {$id}\n";
    }
}

// ══════════════════════════════════════════════════════════════
//  ASIGNAR MENÚS AL ROL 1
// ══════════════════════════════════════════════════════════════
$yaAsignados = DB::table('seguridad_roles_menu')
    ->where('id_roles', $rolId)
    ->pluck('id_menu')
    ->toArray();

$nuevos = array_diff($insertedIds, $yaAsignados);
if (count($nuevos)) {
    $rows = array_map(fn($id) => ['id_roles' => $rolId, 'id_menu' => $id], $nuevos);
    DB::table('seguridad_roles_menu')->insert($rows);
    echo "\n✔ " . count($nuevos) . " menús asignados al rol {$rolId}\n";
} else {
    echo "\n✔ Todos los menús ya estaban asignados al rol {$rolId}\n";
}

// ══════════════════════════════════════════════════════════════
//  TAMBIÉN ASIGNAR LOS MÓDULOS WEB EXISTENTES AL ROL
//  (los que ya estaban en la BD pero no asignados)
// ══════════════════════════════════════════════════════════════
$modsBD = DB::table('sistema_modulo')
    ->whereIn('id_modulo', [39,40,42,43,48,49,50,51,52,53,54,30,29,32])
    ->pluck('id_modulo');

$modRolExistentes = DB::table('seguridad_roles_modulo')
    ->where('id_roles', $rolId)->pluck('id_modulo')->toArray();

$modNuevos = $modsBD->diff($modRolExistentes)->values();
if ($modNuevos->count()) {
    $modRows = $modNuevos->map(fn($m) => ['id_roles' => $rolId, 'id_modulo' => $m])->toArray();
    DB::table('seguridad_roles_modulo')->insert($modRows);
    echo "✔ Módulos adicionales asignados al rol: " . $modNuevos->implode(', ') . "\n";
}

// Limpiar caché del sidebar (forzar recarga)
\Illuminate\Support\Facades\Cache::forget("sidebar_rol_{$rolId}");
\Illuminate\Support\Facades\Cache::forget("sidebar_menus_{$rolId}");

echo "\n✅ COMPLETADO — Recarga el admin para ver el nuevo menú '🌐 Sitio Web'\n";
