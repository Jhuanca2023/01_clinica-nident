<?php
/**
 * LUCDESOFT — Mantenimiento web sin terminal (cPanel / local)
 *
 * Cada visita con la clave correcta:
 *   1. Ejecuta php artisan migrate --force (tablas nuevas y cambios de BD)
 *   2. Reintenta migraciones pendientes una por una si algo falló
 *   3. Recrea los SP de database/procedures/*.sql (DROP + CREATE)
 *   4. Limpia cachés de Laravel
 *
 * Local:  http://localhost:8000/artisan-clear.php?key=lucdesoft2026
 * Prod:   https://rossety.pe/artisan-clear.php?key=lucdesoft2026
 */

$KEY = 'lucdesoft2026';
if (($_GET['key'] ?? '') !== $KEY) {
    http_response_code(403);
    die('<h2 style="font-family:sans-serif;color:red">Acceso denegado — agrega ?key=lucdesoft2026 a la URL</h2>');
}

/**
 * Detecta la raíz de Laravel (local public/ o cPanel public_html/).
 */
function resolveLaravelRoot(): ?string
{
    $ds = DIRECTORY_SEPARATOR;
    $candidates = [
        dirname(__DIR__),                          // script en laravel/public/
        dirname(__DIR__) . $ds . 'laravel',        // script en public_html/ → ../laravel
        __DIR__ . $ds . '..' . $ds . 'laravel',
        '/home/liamverc2e21/laravel',              // cPanel Valento
    ];

    $cpUser = function_exists('get_current_user') ? (string) get_current_user() : '';
    if ($cpUser !== '' && $cpUser !== 'root') {
        $candidates[] = '/home/' . $cpUser . '/laravel';
    }

    foreach (array_unique($candidates) as $root) {
        $root = rtrim(str_replace(['/', '\\'], $ds, $root), $ds);
        if (is_file($root . $ds . 'vendor' . $ds . 'autoload.php')
            && is_file($root . $ds . 'artisan')) {
            return realpath($root) ?: $root;
        }
    }

    return null;
}

/**
 * Elimina cachés compiladas (evita rutas Windows/localhost cacheadas en producción).
 */
function purgeLaravelBootstrapCache(string $laravelRoot): array
{
    $deleted = [];
    $cacheDir = $laravelRoot . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache';
    foreach (['config.php', 'packages.php', 'services.php', 'routes-v7.php'] as $file) {
        $path = $cacheDir . DIRECTORY_SEPARATOR . $file;
        if (is_file($path) && @unlink($path)) {
            $deleted[] = $file;
        }
    }
    return $deleted;
}

/**
 * Asegura carpetas de storage necesarias en el servidor.
 */
function ensureStorageDirs(string $laravelRoot): void
{
    $dirs = [
        'storage/framework/sessions',
        'storage/framework/cache/data',
        'storage/framework/views',
        'storage/logs',
    ];
    foreach ($dirs as $dir) {
        $full = $laravelRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);
        if (!is_dir($full)) {
            @mkdir($full, 0755, true);
        }
    }
}

$laravelRoot = resolveLaravelRoot();

if (!$laravelRoot) {
    http_response_code(500);
    $hint = htmlspecialchars(__DIR__);
    die('<h2 style="font-family:sans-serif;color:orange">No se encontró Laravel. Verifique que existan '
        . '<code>vendor/autoload.php</code> y <code>artisan</code> en <code>/home/USUARIO/laravel</code>.'
        . '</h2><p style="font-family:monospace;font-size:12px">Script en: ' . $hint . '</p>');
}

$cacheBorradosManual = purgeLaravelBootstrapCache($laravelRoot);
ensureStorageDirs($laravelRoot);

define('LARAVEL_START', microtime(true));
require $laravelRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$app = require_once $laravelRoot . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Limpiar config cacheada ANTES de tocar la BD (evita errores PDO por config.php vieja).
$configClearOk = false;
$configClearError = null;
try {
    $configClearOk = $kernel->call('config:clear') === 0;
} catch (\Throwable $e) {
    $configClearError = $e->getMessage();
}

$migracionesResultados = [];
$spsResultados         = [];
$pendingCountBefore    = 0;
$pendingCountAfter     = 0;

/**
 * Ejecuta migraciones pendientes: primero migrate --force global, luego una por una.
 */
function ejecutarMigraciones($app, $kernel, $laravelRoot): array
{
    $resultados = [];

    /** @var \Illuminate\Database\Migrations\Migrator $migrator */
    $migrator = $app->make('migrator');
    $migrationsPath = $laravelRoot . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';

    if (!$migrator->repositoryExists()) {
        $migrator->getRepository()->createRepository();
        $resultados[] = [
            'name' => '(setup)',
            'ok'   => true,
            'note' => 'Tabla migrations creada',
        ];
    }

    $allFiles = $migrator->getMigrationFiles($migrationsPath);
    $ran      = $migrator->getRepository()->getRan();
    $pending  = array_diff_key($allFiles, array_flip($ran));

    if (empty($pending)) {
        return [[
            'name' => '(ninguna)',
            'ok'   => true,
            'note' => 'No hay migraciones pendientes — BD al día',
        ]];
    }

    $ranBefore = $migrator->getRepository()->getRan();

    // Paso 1: migrate --force (equivalente a php artisan migrate --force)
    try {
        $exitCode = $kernel->call('migrate', ['--force' => true]);
        $ranAfter = $migrator->getRepository()->getRan();
        $newlyRan = array_diff($ranAfter, $ranBefore);

        foreach ($newlyRan as $migrationName) {
            $resultados[] = [
                'name' => $migrationName,
                'ok'   => true,
                'note' => 'Ejecutada (migrate --force)',
            ];
        }

        if ($exitCode !== 0 && empty($newlyRan)) {
            $resultados[] = [
                'name'  => 'migrate --force',
                'ok'    => false,
                'error' => 'El comando terminó con código ' . $exitCode . ' — se intentará una por una',
            ];
        }
    } catch (\Throwable $e) {
        $resultados[] = [
            'name'  => 'migrate --force',
            'ok'    => false,
            'error' => substr($e->getMessage(), 0, 180),
        ];
    }

    // Paso 2: pendientes restantes, una por una (tolerante a "ya existe")
    $ran      = $migrator->getRepository()->getRan();
    $pending  = array_diff_key($allFiles, array_flip($ran));
    $lastBatch = max($migrator->getRepository()->getLastBatchNumber(), 1);

    foreach ($pending as $migrationName => $migrationPath) {
        try {
            $relPath  = 'database/migrations/' . basename($migrationPath);
            $exitCode = $kernel->call('migrate', [
                '--force' => true,
                '--path'  => $relPath,
            ]);

            $alreadyLogged = in_array($migrationName, $migrator->getRepository()->getRan(), true);

            $resultados[] = [
                'name' => $migrationName,
                'ok'   => $exitCode === 0 || $alreadyLogged,
                'note' => $alreadyLogged ? 'Ejecutada (--path)' : null,
            ];
        } catch (\Throwable $e) {
            $msg      = $e->getMessage();
            $isExists = preg_match('/already exists|Duplicate (column|key|entry|table)|1050|1060|1061|1826/i', $msg);

            if ($isExists) {
                try {
                    if (!in_array($migrationName, $migrator->getRepository()->getRan(), true)) {
                        $migrator->getRepository()->log($migrationName, $lastBatch + 1);
                    }
                    $resultados[] = [
                        'name' => $migrationName,
                        'ok'   => true,
                        'note' => 'Ya existía en BD → registrada como ejecutada',
                    ];
                } catch (\Throwable $e2) {
                    $resultados[] = [
                        'name'  => $migrationName,
                        'ok'    => false,
                        'error' => 'Ya existía pero no se pudo registrar: ' . substr($e2->getMessage(), 0, 120),
                    ];
                }
            } else {
                $resultados[] = [
                    'name'  => $migrationName,
                    'ok'    => false,
                    'error' => substr($msg, 0, 180),
                ];
            }
        }
    }

    // Verificación final
    $stillPending = array_diff_key(
        $migrator->getMigrationFiles($migrationsPath),
        array_flip($migrator->getRepository()->getRan())
    );

    if (!empty($stillPending)) {
        $resultados[] = [
            'name'  => '(verificación)',
            'ok'    => false,
            'error' => count($stillPending) . ' migración(es) aún pendiente(s): ' . implode(', ', array_slice(array_keys($stillPending), 0, 5)),
        ];
    }

    return $resultados;
}

/**
 * Recrea stored procedures desde database/procedures/*.sql.
 * Cada visita aplica DROP + CREATE, así un SP modificado se actualiza
 * sin necesidad de una migración nueva.
 */
function ejecutarProcedimientosAlmacenados(string $laravelRoot): array
{
    $dir = $laravelRoot . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'procedures';
    if (!is_dir($dir)) {
        return [[
            'name' => '(ninguno)',
            'ok'   => true,
            'note' => 'No existe database/procedures — no hay SP que aplicar',
        ]];
    }

    $files = glob($dir . DIRECTORY_SEPARATOR . '*.sql') ?: [];
    natsort($files);
    $files = array_values($files);

    if ($files === []) {
        return [[
            'name' => '(ninguno)',
            'ok'   => true,
            'note' => 'No hay archivos .sql en database/procedures',
        ]];
    }

    $resultados = [];
    foreach ($files as $file) {
        $name = basename($file);
        try {
            $sql = (string) file_get_contents($file);
            $sql = preg_replace('/^\s*DELIMITER\s+\S+\s*$/mi', '', $sql);
            $sql = preg_replace('/;;\s*$/m', ';', $sql);
            $sql = trim($sql);
            if ($sql === '') {
                $resultados[] = [
                    'name' => $name,
                    'ok'   => true,
                    'note' => 'Archivo vacío — omitido',
                ];
                continue;
            }
            \Illuminate\Support\Facades\DB::unprepared($sql);
            $resultados[] = [
                'name' => $name,
                'ok'   => true,
                'note' => 'DROP + CREATE aplicado',
            ];
        } catch (\Throwable $e) {
            $resultados[] = [
                'name'  => $name,
                'ok'    => false,
                'error' => substr($e->getMessage(), 0, 220),
            ];
        }
    }

    return $resultados;
}

try {
    /** @var \Illuminate\Database\Migrations\Migrator $migrator */
    $migrator = $app->make('migrator');
    $migrationsPath = $laravelRoot . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';

    if ($migrator->repositoryExists()) {
        $allFiles = $migrator->getMigrationFiles($migrationsPath);
        $ran      = $migrator->getRepository()->getRan();
        $pendingCountBefore = count(array_diff_key($allFiles, array_flip($ran)));
    } else {
        $pendingCountBefore = count($migrator->getMigrationFiles($migrationsPath));
    }

    $migracionesResultados = ejecutarMigraciones($app, $kernel, $laravelRoot);

    if ($migrator->repositoryExists()) {
        $allFiles = $migrator->getMigrationFiles($migrationsPath);
        $ran      = $migrator->getRepository()->getRan();
        $pendingCountAfter = count(array_diff_key($allFiles, array_flip($ran)));
    }
} catch (\Throwable $e) {
    $migracionesResultados[] = [
        'name'  => 'migrate (general)',
        'ok'    => false,
        'error' => substr($e->getMessage(), 0, 200),
    ];
}

$spsResultados = [];
try {
    $spsResultados = ejecutarProcedimientosAlmacenados($laravelRoot);
} catch (\Throwable $e) {
    $spsResultados[] = [
        'name'  => 'procedures (general)',
        'ok'    => false,
        'error' => substr($e->getMessage(), 0, 200),
    ];
}

// Reparar columnas de pasarela (modo / valor_test) aunque la migración
// 2026_08_06 haya quedado marcada como ejecutada sin crearlas en cPanel.
$pasarelaSchemaCreated = [];
$pasarelaSchemaEstado  = [];
$pasarelaSchemaError   = null;
try {
    if (class_exists(\App\Support\PagoPasarelaSchema::class)) {
        $pasarelaSchemaCreated = \App\Support\PagoPasarelaSchema::ensureModoColumns();
        $pasarelaSchemaEstado  = \App\Support\PagoPasarelaSchema::estado();
    } else {
        $pasarelaSchemaError = 'Falta app/Support/PagoPasarelaSchema.php — suba el backend nuevo.';
    }
} catch (\Throwable $e) {
    $pasarelaSchemaError = substr($e->getMessage(), 0, 200);
}

// Caché (config:clear ya se ejecutó arriba)
$comandos = [
    'cache:clear'  => 'Caché de aplicación',
    'route:clear'  => 'Caché de rutas',
    'view:clear'   => 'Caché de vistas compiladas',
    'event:clear'  => 'Caché de eventos',
];

$resultados = [
    [
        'cmd' => 'config:clear',
        'label' => 'Caché de configuración (previo a migraciones)',
        'ok' => $configClearOk,
        'error' => isset($configClearError) ? $configClearError : null,
    ],
];
foreach ($comandos as $cmd => $label) {
    try {
        $exitCode = $kernel->call($cmd);
        $resultados[] = ['cmd' => $cmd, 'label' => $label, 'ok' => $exitCode === 0];
    } catch (\Throwable $e) {
        $resultados[] = ['cmd' => $cmd, 'label' => $label, 'ok' => false, 'error' => $e->getMessage()];
    }
}

$sessionPath     = $laravelRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'sessions';
$sessionBorrados = 0;
if (is_dir($sessionPath)) {
    foreach (glob($sessionPath . DIRECTORY_SEPARATOR . '*') as $f) {
        if (is_file($f) && (time() - filemtime($f)) > 1800) {
            @unlink($f);
            $sessionBorrados++;
        }
    }
}

$cacheOk = false;
try {
    $cacheOk = $kernel->call('config:cache') === 0;
} catch (\Throwable $e) {
}

// Diagnóstico Open Graph (WhatsApp / Facebook)
$ogChecks = [];
$publicRoot = function_exists('public_path') ? public_path() : (__DIR__);
$ogFiles = [
    'wa-og/valento-square.jpg' => 'Imagen OG cuadrada (WhatsApp)',
    'wa-og/valento-wide.jpg'   => 'Imagen OG ancha (Facebook)',
    'compartir/index.html'           => 'Página /compartir',
    'og-share.jpg'                   => 'OG raíz',
    'favicon.ico'                    => 'Favicon',
    'apple-touch-icon.png'           => 'Apple touch',
];
foreach ($ogFiles as $rel => $label) {
    $full = rtrim($publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    $ogChecks[] = [
        'label' => $label,
        'path'  => $rel,
        'ok'    => is_file($full),
        'kb'    => is_file($full) ? round(filesize($full) / 1024, 1) : 0,
    ];
}

$ogProbeBase = rtrim((string) config('app.url'), '/') ?: 'https://rossety.pe';
$ogProbeUrls = [$ogProbeBase, $ogProbeBase . '/'];
$ogProbeResults = [];
foreach ($ogProbeUrls as $probeUrl) {
    $html = @file_get_contents($probeUrl, false, stream_context_create([
        'http' => [
            'timeout' => 8,
            'header'  => "User-Agent: facebookexternalhit/1.1\r\n",
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]));
    $image = $title = null;
    if ($html) {
        if (preg_match('/property="og:image" content="([^"]+)"/', $html, $m)) {
            $image = $m[1];
        }
        if (preg_match('/property="og:title" content="([^"]+)"/', $html, $m)) {
            $title = $m[1];
        }
    }
    $ogProbeResults[] = [
        'url'   => $probeUrl,
        'ok'    => $html !== false,
        'image' => $image,
        'title' => $title,
        'legacy' => $title && (stripos($title, 'Royal') !== false || stripos($title, 'Masajes') !== false),
    ];
}

$elapsed = number_format((microtime(true) - LARAVEL_START) * 1000, 1);

$migTotalOk   = count(array_filter($migracionesResultados, fn ($r) => $r['ok']));
$migTotalFail = count(array_filter($migracionesResultados, fn ($r) => !($r['ok'] ?? false)));
$migMarked    = count(array_filter($migracionesResultados, fn ($r) => ($r['ok'] ?? false) && isset($r['note']) && str_contains($r['note'], 'registrada')));
$migExecuted  = count(array_filter($migracionesResultados, fn ($r) => ($r['ok'] ?? false) && isset($r['note']) && str_contains($r['note'], 'Ejecutada')));

$spsTotalOk   = count(array_filter($spsResultados, fn ($r) => $r['ok'] ?? false));
$spsTotalFail = count(array_filter($spsResultados, fn ($r) => !($r['ok'] ?? false)));

$pasarelaOk = empty($pasarelaSchemaError)
    && (
        empty($pasarelaSchemaEstado)
        || (!empty($pasarelaSchemaEstado['modo']) && !empty($pasarelaSchemaEstado['valor_test']))
    );
$pageOk = $migTotalFail === 0 && $pendingCountAfter === 0 && $pasarelaOk && $spsTotalFail === 0;

$timezoneDiag = null;
try {
    if (class_exists(\App\Support\MysqlTimezone::class)) {
        \App\Support\MysqlTimezone::applySession();
    }
    $tzRow = \Illuminate\Support\Facades\DB::selectOne(
        'SELECT @@session.time_zone AS session_tz, NOW() AS ahora'
    );
    $timezoneDiag = [
        'db_timezone_config' => config('database.connections.mysql.timezone'),
        'mysql_session_tz'   => $tzRow->session_tz ?? null,
        'laravel_now'        => now()->toDateTimeString(),
        'mysql_now'          => $tzRow->ahora ?? null,
        'fix_activo'         => ($tzRow->session_tz ?? '') === '-05:00',
    ];
} catch (\Throwable $e) {
    $timezoneDiag = ['error' => substr($e->getMessage(), 0, 120)];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mantenimiento — Lucdesoft</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
  .card{background:#1e293b;border-radius:16px;padding:32px;max-width:720px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.4)}
  .logo{font-size:12px;color:#64748b;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:18px}
  h1{font-size:1.35rem;color:#f8fafc;margin-bottom:4px}
  .sub{font-size:.80rem;color:#64748b;margin-bottom:22px}
  .hero{padding:12px 14px;border-radius:10px;margin-bottom:16px;font-size:.82rem;line-height:1.5}
  .hero.ok{background:rgba(22,163,74,.12);border:1px solid rgba(22,163,74,.35);color:#86efac}
  .hero.warn{background:rgba(234,179,8,.10);border:1px solid rgba(234,179,8,.3);color:#fde68a}
  .section-title{font-size:.70rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.1em;margin:18px 0 8px}
  .mig-summary{display:flex;gap:10px;margin-bottom:10px;flex-wrap:wrap}
  .mig-chip{font-size:.72rem;font-weight:700;padding:3px 12px;border-radius:99px}
  .mig-chip.ok{background:#166534;color:#86efac}
  .mig-chip.info{background:#1e3a5f;color:#93c5fd}
  .mig-chip.skip{background:#1e3a5f;color:#93c5fd}
  .mig-chip.fail{background:#7f1d1d;color:#fca5a5}
  .mig-row{display:flex;align-items:flex-start;gap:10px;padding:8px 12px;border-radius:8px;margin-bottom:6px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);font-size:.75rem}
  .mig-icon{flex-shrink:0;font-size:14px}
  .mig-name{font-family:monospace;color:#94a3b8;word-break:break-all;flex:1}
  .mig-note{color:#60a5fa;font-size:.70rem;margin-top:2px}
  .mig-err{color:#f87171;font-size:.70rem;margin-top:2px;font-family:monospace}
  .mig-badge{font-size:.65rem;font-weight:700;padding:2px 8px;border-radius:99px;flex-shrink:0;white-space:nowrap}
  .mig-badge.ok{background:#166534;color:#86efac}
  .mig-badge.skip{background:#1e3a5f;color:#93c5fd}
  .mig-badge.fail{background:#7f1d1d;color:#fca5a5}
  .row{display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;margin-bottom:8px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07)}
  .icon{font-size:17px;flex-shrink:0}
  .lbl{flex:1}
  .desc{font-size:.83rem;color:#e2e8f0;font-weight:500}
  .cmd{font-size:.70rem;font-family:monospace;color:#94a3b8}
  .err{font-size:.70rem;font-family:monospace;color:#f87171;margin-top:2px}
  .badge{font-size:.68rem;font-weight:700;padding:3px 10px;border-radius:99px;flex-shrink:0}
  .badge.ok{background:#166534;color:#86efac}
  .badge.fail{background:#7f1d1d;color:#fca5a5}
  hr{border:none;border-top:1px solid rgba(255,255,255,.07);margin:16px 0}
  .extra{display:flex;justify-content:space-between;font-size:.82rem;color:#94a3b8;padding:5px 0;gap:12px}
  .extra span:last-child{color:#e2e8f0;font-weight:600;text-align:right}
  .cache-row{display:flex;align-items:center;gap:10px;margin-top:14px;padding:12px 14px;border-radius:10px;font-size:.82rem}
  .cache-ok{background:rgba(22,163,74,.15);border:1px solid rgba(22,163,74,.3);color:#86efac}
  .cache-fail{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
  .info{margin-top:18px;background:rgba(59,130,246,.10);border:1px solid rgba(59,130,246,.25);border-radius:10px;padding:13px 15px;font-size:.79rem;color:#bfdbfe;line-height:1.6}
  code{background:rgba(255,255,255,.08);padding:1px 6px;border-radius:4px;font-size:.75rem}
  details summary{cursor:pointer;font-size:.75rem;color:#64748b;margin-bottom:6px;user-select:none}
  details summary:hover{color:#94a3b8}
</style>
</head>
<body>
<div class="card">
  <div class="logo">Valento · Mantenimiento automático</div>
  <h1><?= $pageOk ? 'BD y caché actualizadas' : 'Mantenimiento completado con avisos' ?></h1>
  <p class="sub"><?= date('d/m/Y H:i:s') ?></p>

  <div class="hero <?= $pageOk ? 'ok' : 'warn' ?>">
    <?php if ($pendingCountBefore === 0): ?>
      No había migraciones pendientes. La base de datos ya estaba al día.
    <?php elseif ($pendingCountAfter === 0): ?>
      Se detectaron <strong><?= $pendingCountBefore ?></strong> migración(es) pendiente(s) y se aplicaron correctamente
      (tablas nuevas o columnas modificadas).
    <?php else: ?>
      Quedan <strong><?= $pendingCountAfter ?></strong> migración(es) sin aplicar. Revise los errores abajo.
    <?php endif; ?>
    <br>Cada visita ejecuta automáticamente <code>php artisan migrate --force</code>
    y recrea los SP de <code>database/procedures/*.sql</code>.
  </div>

  <div class="section-title">Migraciones (php artisan migrate --force)</div>

  <div class="mig-summary">
    <span class="mig-chip info">Antes: <?= $pendingCountBefore ?> pendiente(s)</span>
    <span class="mig-chip info">Después: <?= $pendingCountAfter ?> pendiente(s)</span>
    <span class="mig-chip ok"><?= $migTotalOk ?> OK</span>
    <?php if ($migExecuted > 0): ?>
    <span class="mig-chip ok"><?= $migExecuted ?> ejecutada(s)</span>
    <?php endif; ?>
    <?php if ($migMarked > 0): ?>
    <span class="mig-chip skip"><?= $migMarked ?> sincronizada(s)</span>
    <?php endif; ?>
    <?php if ($migTotalFail > 0): ?>
    <span class="mig-chip fail"><?= $migTotalFail ?> error</span>
    <?php endif; ?>
  </div>

  <?php
  $nuevas   = array_filter($migracionesResultados, fn ($r) => ($r['ok'] ?? false) && !isset($r['note']));
  $ejecutadas = array_filter($migracionesResultados, fn ($r) => ($r['ok'] ?? false) && isset($r['note']) && str_contains($r['note'], 'Ejecutada'));
  $marcadas = array_filter($migracionesResultados, fn ($r) => ($r['ok'] ?? false) && isset($r['note']) && str_contains($r['note'], 'registrada'));
  $fallidas = array_filter($migracionesResultados, fn ($r) => !($r['ok'] ?? false));
  $sinPend  = array_filter($migracionesResultados, fn ($r) => ($r['ok'] ?? false) && isset($r['note']) && str_contains($r['note'], 'al día'));
  $otros    = array_filter($migracionesResultados, fn ($r) => ($r['ok'] ?? false) && isset($r['note']) && !str_contains($r['note'] ?? '', 'Ejecutada') && !str_contains($r['note'] ?? '', 'registrada') && !str_contains($r['note'] ?? '', 'al día'));
  ?>

  <?php foreach ($sinPend as $r): ?>
  <div class="mig-row">
    <span class="mig-icon">✅</span>
    <div style="flex:1"><div class="mig-name">Sin migraciones pendientes</div></div>
  </div>
  <?php endforeach; ?>

  <?php foreach ($ejecutadas as $r): ?>
  <div class="mig-row">
    <span class="mig-icon">✅</span>
    <div style="flex:1">
      <div class="mig-name"><?= htmlspecialchars($r['name']) ?></div>
      <?php if (!empty($r['note'])): ?><div class="mig-note"><?= htmlspecialchars($r['note']) ?></div><?php endif; ?>
    </div>
    <span class="mig-badge ok">APLICADA</span>
  </div>
  <?php endforeach; ?>

  <?php foreach ($nuevas as $r): ?>
  <div class="mig-row">
    <span class="mig-icon">✅</span>
    <div style="flex:1"><div class="mig-name"><?= htmlspecialchars($r['name']) ?></div></div>
    <span class="mig-badge ok">OK</span>
  </div>
  <?php endforeach; ?>

  <?php foreach ($otros as $r): ?>
  <div class="mig-row">
    <span class="mig-icon">ℹ️</span>
    <div style="flex:1">
      <div class="mig-name"><?= htmlspecialchars($r['name']) ?></div>
      <?php if (!empty($r['note'])): ?><div class="mig-note"><?= htmlspecialchars($r['note']) ?></div><?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <?php if (!empty($marcadas)): ?>
  <details>
    <summary><?= count($marcadas) ?> ya existían en BD — sincronizadas</summary>
    <?php foreach ($marcadas as $r): ?>
    <div class="mig-row">
      <span class="mig-icon">🔵</span>
      <div style="flex:1">
        <div class="mig-name"><?= htmlspecialchars($r['name']) ?></div>
        <div class="mig-note"><?= htmlspecialchars($r['note'] ?? '') ?></div>
      </div>
      <span class="mig-badge skip">SYNC</span>
    </div>
    <?php endforeach; ?>
  </details>
  <?php endif; ?>

  <?php foreach ($fallidas as $r): ?>
  <div class="mig-row">
    <span class="mig-icon">❌</span>
    <div style="flex:1">
      <div class="mig-name"><?= htmlspecialchars($r['name']) ?></div>
      <?php if (isset($r['error'])): ?>
      <div class="mig-err"><?= htmlspecialchars($r['error']) ?></div>
      <?php endif; ?>
    </div>
    <span class="mig-badge fail">ERROR</span>
  </div>
  <?php endforeach; ?>

  <div class="section-title">Stored procedures (database/procedures)</div>
  <div class="mig-summary">
    <span class="mig-chip ok"><?= $spsTotalOk ?> OK</span>
    <?php if ($spsTotalFail > 0): ?>
    <span class="mig-chip fail"><?= $spsTotalFail ?> error</span>
    <?php endif; ?>
  </div>
  <?php foreach ($spsResultados as $r): ?>
  <div class="mig-row">
    <span class="mig-icon"><?= ($r['ok'] ?? false) ? '✅' : '❌' ?></span>
    <div style="flex:1">
      <div class="mig-name"><?= htmlspecialchars($r['name']) ?></div>
      <?php if (!empty($r['note'])): ?><div class="mig-note"><?= htmlspecialchars($r['note']) ?></div><?php endif; ?>
      <?php if (!empty($r['error'])): ?><div class="mig-err"><?= htmlspecialchars($r['error']) ?></div><?php endif; ?>
    </div>
    <span class="mig-badge <?= ($r['ok'] ?? false) ? 'ok' : 'fail' ?>"><?= ($r['ok'] ?? false) ? 'APLICADO' : 'ERROR' ?></span>
  </div>
  <?php endforeach; ?>

  <div class="section-title">Pasarela de pago (modo prueba / producción)</div>
  <?php
    $modoOk  = !empty($pasarelaSchemaEstado['modo']);
    $testOk  = !empty($pasarelaSchemaEstado['valor_test']);
    $tblOk   = !empty($pasarelaSchemaEstado['pago_pasarela']) && !empty($pasarelaSchemaEstado['pago_pasarela_config']);
  ?>
  <?php if ($pasarelaSchemaError): ?>
  <div class="mig-row">
    <span class="mig-icon">❌</span>
    <div style="flex:1">
      <div class="mig-name">Reparación de columnas de pasarela</div>
      <div class="mig-err"><?= htmlspecialchars($pasarelaSchemaError) ?></div>
    </div>
    <span class="mig-badge fail">ERROR</span>
  </div>
  <?php else: ?>
  <div class="mig-row">
    <span class="mig-icon"><?= ($modoOk && $testOk) ? '✅' : '⚠️' ?></span>
    <div style="flex:1">
      <div class="mig-name">pago_pasarela.modo <?= $modoOk ? 'OK' : 'FALTA' ?> · pago_pasarela_config.valor_test <?= $testOk ? 'OK' : 'FALTA' ?></div>
      <?php if (!empty($pasarelaSchemaCreated)): ?>
      <div class="mig-note">Creadas ahora: <?= htmlspecialchars(implode(', ', $pasarelaSchemaCreated)) ?></div>
      <?php elseif ($modoOk && $testOk): ?>
      <div class="mig-note">Columnas listas — las claves de PRUEBA se guardan en valor_test</div>
      <?php elseif (!$tblOk): ?>
      <div class="mig-err">Las tablas de pasarela no existen. Suba las migraciones y recargue este enlace.</div>
      <?php endif; ?>
    </div>
    <span class="mig-badge <?= ($modoOk && $testOk) ? 'ok' : 'fail' ?>"><?= ($modoOk && $testOk) ? 'OK' : 'FALTA' ?></span>
  </div>
  <?php endif; ?>

  <hr>

  <div class="section-title">Limpieza de caché</div>
  <?php foreach ($resultados as $r): ?>
  <div class="row">
    <span class="icon"><?= $r['ok'] ? '✅' : '❌' ?></span>
    <div class="lbl">
      <div class="desc"><?= htmlspecialchars($r['label']) ?></div>
      <div class="cmd">php artisan <?= htmlspecialchars($r['cmd']) ?></div>
      <?php if (!$r['ok'] && isset($r['error'])): ?>
      <div class="err"><?= htmlspecialchars(substr($r['error'], 0, 120)) ?></div>
      <?php endif; ?>
    </div>
    <span class="badge <?= $r['ok'] ? 'ok' : 'fail' ?>"><?= $r['ok'] ? 'OK' : 'FALLO' ?></span>
  </div>
  <?php endforeach; ?>

  <hr>
  <div class="extra"><span>Sesiones antiguas eliminadas</span><span><?= $sessionBorrados ?> archivos</span></div>
  <div class="extra"><span>Tiempo de ejecución</span><span><?= $elapsed ?> ms</span></div>
  <div class="extra"><span>Laravel root</span><span><code><?= htmlspecialchars($laravelRoot) ?></code></span></div>
  <?php if (!empty($cacheBorradosManual)): ?>
  <div class="extra"><span>Caché bootstrap eliminada (manual)</span><span><code><?= htmlspecialchars(implode(', ', $cacheBorradosManual)) ?></code></span></div>
  <?php endif; ?>

  <div class="cache-row <?= $cacheOk ? 'cache-ok' : 'cache-fail' ?>">
    <?= $cacheOk ? '✅' : '⚠️' ?>
    <div><strong>config:cache</strong> — <?= $cacheOk ? 'Configuración recompilada' : 'No se pudo recompilar' ?></div>
  </div>

  <hr>
  <div class="section-title">Vista previa WhatsApp / Facebook (Open Graph)</div>
  <p style="font-size:.82rem;color:#94a3b8;margin-bottom:12px;line-height:1.5">
    Si WhatsApp muestra logo viejo (mandala Royal), el servidor puede estar bien pero <strong>Facebook/WhatsApp guardan caché</strong>.
    Depura <strong>las dos URLs</strong> en
    <a href="https://developers.facebook.com/tools/debug/" target="_blank" rel="noopener" style="color:#60a5fa">Facebook Sharing Debugger</a>.
  </p>
  <?php foreach ($ogChecks as $c): ?>
  <div class="row">
    <span class="icon"><?= $c['ok'] ? '✅' : '❌' ?></span>
    <div class="lbl">
      <div class="desc"><?= htmlspecialchars($c['label']) ?></div>
      <div class="cmd"><code><?= htmlspecialchars($c['path']) ?></code><?= $c['ok'] ? ' — ' . $c['kb'] . ' KB' : ' — FALTA en public/' ?></div>
    </div>
    <span class="badge <?= $c['ok'] ? 'ok' : 'fail' ?>"><?= $c['ok'] ? 'OK' : 'FALTA' ?></span>
  </div>
  <?php endforeach; ?>
  <?php foreach ($ogProbeResults as $p): ?>
  <div class="row" style="margin-top:8px">
    <span class="icon"><?= ($p['ok'] && !$p['legacy'] && $p['image'] && str_contains($p['image'], 'valento-square')) ? '✅' : '⚠️' ?></span>
    <div class="lbl">
      <div class="desc">Crawler → <?= htmlspecialchars($p['url']) ?></div>
      <?php if ($p['title']): ?><div class="cmd">og:title: <?= htmlspecialchars(mb_substr($p['title'], 0, 80)) ?></div><?php endif; ?>
      <?php if ($p['image']): ?><div class="cmd">og:image: <?= htmlspecialchars($p['image']) ?></div><?php endif; ?>
      <?php if ($p['legacy']): ?><div class="err">⚠ Aún devuelve texto Royal — sube código nuevo o limpia caché</div><?php endif; ?>
      <?php if ($p['image'] && !str_contains($p['image'], 'wa-og/valento-square')): ?><div class="err">⚠ og:image no es wa-og — sube wa-og/ y Helper.php</div><?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <div class="info" style="margin-top:12px">
    <strong>Depurar caché (obligatorio):</strong><br>
    1. <a href="https://developers.facebook.com/tools/debug/?q=https%3A%2F%2Fvalentostile.com%2Fcompartir" target="_blank" style="color:#60a5fa"><strong>compartir</strong></a> → Volver a rastrear<br>
    2. <a href="https://developers.facebook.com/tools/debug/?q=https%3A%2F%2Fvalentostile.com" target="_blank" style="color:#60a5fa">rossety.pe</a> → Volver a rastrear<br>
    3. <a href="https://developers.facebook.com/tools/debug/?q=https%3A%2F%2Fvalentostile.com%2F" target="_blank" style="color:#60a5fa">rossety.pe/</a> → Volver a rastrear<br>
    4. Comparte: <code>https://rossety.pe/compartir</code> en chat nuevo de WhatsApp<br>
    5. WhatsApp → Ajustes → Almacenamiento → Borrar caché
  </div>

  <?php if ($timezoneDiag): ?>
  <div class="section-title">Zona horaria MySQL (Laravel)</div>
  <div class="hero <?= !empty($timezoneDiag['fix_activo']) ? 'ok' : 'warn' ?>">
    <?php if (!empty($timezoneDiag['fix_activo'])): ?>
      ✅ Fix activo: sesión MySQL en <code>-05:00</code> (Lima).
      Laravel: <strong><?= htmlspecialchars($timezoneDiag['laravel_now'] ?? '') ?></strong>
      · MySQL: <strong><?= htmlspecialchars($timezoneDiag['mysql_now'] ?? '') ?></strong>
    <?php elseif (isset($timezoneDiag['error'])): ?>
      ⚠️ No se pudo verificar timezone: <?= htmlspecialchars($timezoneDiag['error']) ?>
      <br>Suba <code>app/Support/MysqlTimezone.php</code>, <code>config/database.php</code> y <code>AppServiceProvider.php</code>.
    <?php else: ?>
      ⚠️ Fix NO activo. session_tz = <code><?= htmlspecialchars($timezoneDiag['mysql_session_tz'] ?? 'SYSTEM') ?></code>
      (Navicat siempre muestra SYSTEM/UTC; Laravel debe mostrar <code>-05:00</code>).
      <br>Agregue <code>DB_TIMEZONE=-05:00</code> al .env y suba los archivos nuevos.
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="info">
    <strong>Uso:</strong> Suba el código nuevo (migraciones en <code>database/migrations/</code>
    y SP en <code>database/procedures/*.sql</code>) y abra este enlace.
    Las tablas nuevas, columnas y stored procedures se aplican solos. Protegido con <code>?key=…</code>.
    <br>Local: <code>http://localhost:8000/artisan-clear.php?key=lucdesoft2026</code>
    <br>Producción: <code>https://rossety.pe/artisan-clear.php?key=lucdesoft2026</code>
    <br><strong>Importante:</strong> No suba <code>bootstrap/cache/config.php</code> desde local (contiene rutas Windows).
    <br>Si aparece error PDO al migrar, borre manualmente <code>bootstrap/cache/config.php</code> en el servidor y vuelva a abrir este enlace.
  </div>
</div>
</body>
</html>
