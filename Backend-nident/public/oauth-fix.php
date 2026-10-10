<?php
/**
 * ┌─────────────────────────────────────────────────────────────┐
 * │  LUCDESOFT — Fix OAuth Google (BD + Config Cache)           │
 * │  Subir a: public_html/oauth-fix.php                         │
 * │  URL: https://rossety.pe/oauth-fix.php?key=lucdesoft2026 │
 * │  ⚠️  BORRAR este archivo después de usarlo                   │
 * └─────────────────────────────────────────────────────────────┘
 */

$KEY = 'lucdesoft2026';
if (($_GET['key'] ?? '') !== $KEY) {
    http_response_code(403);
    die('<h2 style="font-family:sans-serif;color:red">🔒 Acceso denegado</h2>');
}

$laravelRoot = null;
$ds = DIRECTORY_SEPARATOR;
foreach ([
    dirname(__DIR__) . $ds . 'laravel',
    __DIR__ . $ds . '..' . $ds . 'laravel',
    dirname(__DIR__),
    '/home/liamverc2e21/laravel',
] as $candidate) {
    $candidate = rtrim(str_replace(['/', '\\'], $ds, $candidate), $ds);
    if (is_file($candidate . $ds . 'vendor' . $ds . 'autoload.php')
        && is_file($candidate . $ds . 'artisan')) {
        $laravelRoot = realpath($candidate) ?: $candidate;
        break;
    }
}
if (!$laravelRoot) {
    http_response_code(500);
    die('<h2 style="font-family:sans-serif;color:orange">No se encontró Laravel en /home/USUARIO/laravel</h2>');
}
foreach (['config.php', 'packages.php', 'services.php'] as $f) {
    $p = $laravelRoot . '/bootstrap/cache/' . $f;
    if (is_file($p)) {
        @unlink($p);
    }
}

define('LARAVEL_START', microtime(true));
require $laravelRoot . '/vendor/autoload.php';

$app    = require_once $laravelRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// ── Valores correctos ────────────────────────────────────────────────────────
// Acepta el secreto por parámetro URL: ?key=lucdesoft2026&secret=GOCSPX-xxxxx
$GOOGLE_CLIENT_ID     = '865446869846-vlubkklnl9m01gtdqksss29inmn645kj.apps.googleusercontent.com';
$GOOGLE_CLIENT_SECRET = $_GET['secret'] ?? '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';
$GOOGLE_REDIRECT_URI  = 'https://rossety.pe/api/auth/oauth/callback/google';

// Validar que el secreto tiene formato GOCSPX-
if (!str_starts_with($GOOGLE_CLIENT_SECRET, 'GOCSPX-')) {
    http_response_code(400);
    die('<div style="font-family:sans-serif;padding:30px;background:#1e293b;color:#fca5a5;border-radius:12px;max-width:500px;margin:40px auto">
        <h2>⚠️ Secreto inválido</h2>
        <p>El secreto debe empezar con <strong>GOCSPX-</strong></p>
        <p>Uso: <code>?key=lucdesoft2026&secret=GOCSPX-xxxxxxxxx</code></p>
    </div>');
}

$steps = [];

// ── 1. Buscar el proveedor Google en BD ─────────────────────────────────────
try {
    $proveedor = \App\Models\SeguridadAuthProveedor::porCodigo('google');
    if (!$proveedor) {
        $steps[] = ['ok' => false, 'msg' => 'No se encontró el proveedor Google en BD (seguridad_auth_proveedor)'];
    } else {
        $steps[] = ['ok' => true, 'msg' => "Proveedor Google encontrado — ID: {$proveedor->id_seguridad_auth_proveedor}, habilitado: " . ($proveedor->is_habilitado ? 'SÍ' : 'NO')];
    }
} catch (\Throwable $e) {
    $steps[] = ['ok' => false, 'msg' => 'Error al buscar proveedor: ' . $e->getMessage()];
    $proveedor = null;
}

// ── 2. Leer configs actuales y mostrarlas ───────────────────────────────────
if ($proveedor) {
    try {
        $configs = $proveedor->configs;
        foreach ($configs as $c) {
            $display = $c->es_secreto ? '(encriptado)' : ($c->valor ?? 'NULL');
            $steps[] = ['ok' => true, 'msg' => "BD actual — [{$c->clave}] = {$display}"];
        }
    } catch (\Throwable $e) {
        $steps[] = ['ok' => false, 'msg' => 'Error al leer configs: ' . $e->getMessage()];
    }
}

// ── 3. Actualizar / crear cada campo ────────────────────────────────────────
if ($proveedor) {
    $fieldsToUpdate = [
        ['clave' => 'CLIENT_ID',     'valor' => $GOOGLE_CLIENT_ID,     'es_secreto' => false, 'etiqueta' => 'Client ID'],
        ['clave' => 'CLIENT_SECRET', 'valor' => $GOOGLE_CLIENT_SECRET, 'es_secreto' => true,  'etiqueta' => 'Client Secret'],
        ['clave' => 'REDIRECT_URI',  'valor' => $GOOGLE_REDIRECT_URI,  'es_secreto' => false, 'etiqueta' => 'Redirect URI'],
    ];

    foreach ($fieldsToUpdate as $field) {
        try {
            $valorGuardado = \App\Models\SeguridadAuthProveedorConfig::guardarValor(
                $field['es_secreto'],
                $field['valor']
            );

            $updated = \App\Models\SeguridadAuthProveedorConfig::updateOrCreate(
                [
                    'id_seguridad_auth_proveedor' => $proveedor->id_seguridad_auth_proveedor,
                    'clave' => $field['clave'],
                ],
                [
                    'valor' => $valorGuardado,
                    'es_secreto' => $field['es_secreto'],
                    'etiqueta' => $field['etiqueta'],
                ]
            );

            $display = $field['es_secreto'] ? '(encriptado ✅)' : $field['valor'];
            $steps[] = ['ok' => true, 'msg' => "✅ [{$field['clave']}] actualizado → {$display}"];
        } catch (\Throwable $e) {
            $steps[] = ['ok' => false, 'msg' => "❌ Error en [{$field['clave']}]: " . $e->getMessage()];
        }
    }

    // Habilitar proveedor si estaba deshabilitado
    try {
        if (!$proveedor->is_habilitado) {
            $proveedor->update(['is_habilitado' => true]);
            $steps[] = ['ok' => true, 'msg' => '✅ Proveedor Google habilitado en BD'];
        }
    } catch (\Throwable $e) {
        $steps[] = ['ok' => false, 'msg' => 'Error al habilitar proveedor: ' . $e->getMessage()];
    }
}

// ── 4. Limpiar config cache de Laravel ──────────────────────────────────────
$cacheCmds = ['config:clear', 'cache:clear', 'route:clear'];
foreach ($cacheCmds as $cmd) {
    try {
        $kernel->call($cmd);
        $steps[] = ['ok' => true, 'msg' => "✅ php artisan {$cmd}"];
    } catch (\Throwable $e) {
        $steps[] = ['ok' => false, 'msg' => "❌ {$cmd}: " . $e->getMessage()];
    }
}

// ── 5. Verificar resultado final ─────────────────────────────────────────────
try {
    $provFinal = \App\Models\SeguridadAuthProveedor::porCodigo('google');
    $cfgFinal  = [];
    if ($provFinal) {
        foreach ($provFinal->configs as $c) {
            $cfgFinal[$c->clave] = $c->es_secreto ? '(encriptado)' : ($c->valor ?? 'NULL');
        }
    }
    $steps[] = ['ok' => true, 'msg' => '📋 Estado final — CLIENT_ID: ' . ($cfgFinal['CLIENT_ID'] ?? 'no encontrado')];
    $steps[] = ['ok' => true, 'msg' => '📋 Estado final — REDIRECT_URI: ' . ($cfgFinal['REDIRECT_URI'] ?? 'no encontrado')];
    $steps[] = ['ok' => true, 'msg' => '📋 Estado final — CLIENT_SECRET: ' . ($cfgFinal['CLIENT_SECRET'] ?? 'no encontrado')];
} catch (\Throwable $e) {
    $steps[] = ['ok' => false, 'msg' => 'Error en verificación final: ' . $e->getMessage()];
}

$elapsed = number_format((microtime(true) - LARAVEL_START) * 1000, 1);
$allOk   = !in_array(false, array_column($steps, 'ok'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>OAuth Fix — Lucdesoft</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
  .card{background:#1e293b;border-radius:16px;padding:32px;max-width:700px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.4)}
  h1{font-size:1.35rem;margin-bottom:4px}
  .sub{font-size:.80rem;color:#64748b;margin-bottom:24px}
  .row{display:flex;align-items:flex-start;gap:12px;padding:10px 14px;border-radius:10px;margin-bottom:7px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);font-size:.80rem;word-break:break-all}
  .ok {color:#86efac}
  .fail{color:#fca5a5}
  .badge{font-size:.68rem;font-weight:700;padding:3px 10px;border-radius:99px;flex-shrink:0;margin-top:2px}
  .badge-ok  {background:#166534;color:#86efac}
  .badge-fail{background:#7f1d1d;color:#fca5a5}
  hr{border:none;border-top:1px solid rgba(255,255,255,.07);margin:18px 0}
  .footer{font-size:.80rem;color:#94a3b8;text-align:center;margin-top:16px}
  .warn{margin-top:18px;background:rgba(234,179,8,.10);border:1px solid rgba(234,179,8,.3);border-radius:10px;padding:13px 15px;font-size:.79rem;color:#fde68a;line-height:1.6}
  .summary{padding:14px 16px;border-radius:12px;margin-bottom:20px;font-weight:700;font-size:1rem}
  .sum-ok  {background:rgba(22,163,74,.15);border:1px solid rgba(22,163,74,.4);color:#86efac}
  .sum-fail{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.4);color:#fca5a5}
  code{background:rgba(255,255,255,.1);padding:2px 7px;border-radius:5px;font-size:.75rem}
</style>
</head>
<body>
<div class="card">
  <h1>🔧 OAuth Google Fix</h1>
  <p class="sub"><?= date('d/m/Y H:i:s') ?> · <?= $elapsed ?> ms</p>

  <div class="summary <?= $allOk ? 'sum-ok' : 'sum-fail' ?>">
    <?= $allOk ? '✅ Corrección aplicada correctamente' : '⚠️ Algunos pasos fallaron — revisa abajo' ?>
  </div>

  <?php foreach ($steps as $s): ?>
  <div class="row">
    <span class="badge <?= $s['ok'] ? 'badge-ok' : 'badge-fail' ?>"><?= $s['ok'] ? 'OK' : 'ERR' ?></span>
    <span class="<?= $s['ok'] ? 'ok' : 'fail' ?>"><?= htmlspecialchars($s['msg']) ?></span>
  </div>
  <?php endforeach; ?>

  <hr>

  <?php if ($allOk): ?>
  <div style="background:rgba(22,163,74,.15);border:1px solid rgba(22,163,74,.4);border-radius:10px;padding:14px;font-size:.85rem;color:#86efac">
    <strong>✅ ¿Qué sigue?</strong><br><br>
    1. Prueba el login en <code>https://rossety.pe/admin/session/signin</code><br>
    2. Si funciona → borra este archivo del servidor<br>
    3. Si el secreto terminaba en <strong>WCrH</strong> (nuevo), ve al panel admin y actualiza el <code>CLIENT_SECRET</code>
  </div>
  <?php endif; ?>

  <div class="warn">
    <strong>⚠️ BORRAR ESTE ARCHIVO</strong> del servidor cuando termines.<br>
    Ruta en cPanel: <code>public_html/oauth-fix.php</code>
  </div>

  <div class="footer" style="margin-top:14px">
    Después prueba: <a href="https://rossety.pe/admin/session/signin" style="color:#60a5fa">rossety.pe/admin/session/signin</a>
  </div>
</div>
</body>
</html>
