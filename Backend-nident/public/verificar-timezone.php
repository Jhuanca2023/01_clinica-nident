<?php
/**
 * Verificación de zona horaria MySQL vía Laravel (no Navicat).
 *
 * Navicat conecta directo a MySQL sin SET time_zone → siempre verá UTC/SYSTEM.
 * Esta URL usa la misma conexión que la tienda al guardar pedidos.
 *
 * Prod: https://rossety.pe/verificar-timezone.php?key=lucdesoft2026
 */

$KEY = 'lucdesoft2026';
if (($_GET['key'] ?? '') !== $KEY) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Acceso denegado. Agrega ?key=lucdesoft2026']);
    exit;
}

function resolveLaravelRoot(): ?string
{
    $ds = DIRECTORY_SEPARATOR;
    $candidates = [
        dirname(__DIR__),
        dirname(__DIR__) . $ds . 'laravel',
        '/home/liamverc2e21/laravel',
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

$laravelRoot = resolveLaravelRoot();
if (!$laravelRoot) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'No se encontró Laravel']);
    exit;
}

define('LARAVEL_START', microtime(true));
require $laravelRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$app = require_once $laravelRoot . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json; charset=utf-8');

try {
    \App\Support\MysqlTimezone::applySession();

    $mysql = Illuminate\Support\Facades\DB::selectOne(
        'SELECT @@system_time_zone AS system_tz, @@session.time_zone AS session_tz, NOW() AS ahora, UTC_TIMESTAMP() AS utc'
    );

    $laravelNow = now()->toDateTimeString();
    $mysqlNow = $mysql->ahora ?? null;
    $sameDay = $mysqlNow
        ? now()->format('Y-m-d') === \Carbon\Carbon::parse($mysqlNow)->format('Y-m-d')
        : false;

    echo json_encode([
        'laravel_root' => $laravelRoot,
        'config_timezone' => config('app.timezone'),
        'db_timezone_config' => config('database.connections.mysql.timezone'),
        'php_timezone' => date_default_timezone_get(),
        'laravel_now' => $laravelNow,
        'mysql_system_tz' => $mysql->system_tz ?? null,
        'mysql_session_tz' => $mysql->session_tz ?? null,
        'mysql_now' => $mysqlNow,
        'mysql_utc' => $mysql->utc ?? null,
        'misma_fecha_dia' => $sameDay,
        'fix_activo' => ($mysql->session_tz ?? '') === '-05:00' && $sameDay,
        'nota' => 'Navicat sin Laravel siempre muestra session_tz=SYSTEM (UTC). Esta URL refleja lo que usa la tienda.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'hint' => 'Suba config/database.php, AppServiceProvider.php, app/Support/MysqlTimezone.php y ejecute artisan-clear.php',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
