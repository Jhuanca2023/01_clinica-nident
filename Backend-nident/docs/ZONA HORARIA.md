# ZONA HORARIA — Réplica entre proyectos Laravel (cPanel / Perú)

Documento para copiar y pegar en otros clones del mismo stack (Laravel + MySQL + cPanel).

---

## PROMPT MAESTRO (copiar y pegar en el agente)

```
================================================================================
PROMPT — ZONA HORARIA (Laravel + MySQL en cPanel Perú)
================================================================================

## CONTEXTO DEL PROBLEMA

En hostings cPanel (Perú), es habitual esta configuración:

- Sistema del servidor: UTC
- MySQL (@@system_time_zone): UTC
- PHP / Laravel (APP_TIMEZONE): America/Lima ✅

Laravel y PHP pueden estar bien configurados, pero MySQL usa NOW() en UTC.
Después de las ~19:00 en Perú, NOW() de MySQL ya es el DÍA SIGUIENTE (+5 h).

Síntomas:
- created_at / updated_at en BD con fecha un día adelante (ej. 05 en lugar de 04)
- Stored procedures (USP_WEB_OPE_PEDIDOS_CREAR, etc.) guardan hora UTC
- CONVERT_TZ(..., 'UTC', 'America/Lima') devuelve NULL (tablas de zonas MySQL no cargadas en hosting compartido)
- Navicat muestra @@session.time_zone = SYSTEM (UTC) al conectar directo — eso es NORMAL sin Laravel
- En otros hostings "funcionaba" porque MySQL estaba en hora local, no en UTC

NO es necesario pedir al proveedor que cambie el servidor entero.
La solución es de aplicación: alinear la sesión MySQL con Lima usando offset -05:00.

## DIAGNÓSTICO (antes de tocar código)

Crear ruta temporal en routes/web.php:

Route::get('/test-time', function () {
    \App\Support\MysqlTimezone::applySession();
    $mysql = DB::selectOne('SELECT @@session.time_zone AS session_tz, NOW() AS ahora');
    return response()->json([
        'config_timezone' => config('app.timezone'),
        'db_timezone_config' => config('database.connections.mysql.timezone'),
        'php_timezone' => date_default_timezone_get(),
        'laravel_now' => now()->toDateTimeString(),
        'mysql_session_tz' => $mysql->session_tz ?? null,
        'mysql_now' => $mysql->ahora ?? null,
    ]);
});

Si laravel_now es correcto (Lima) y mysql_now va ~5 h adelante → confirmar fix.

## IMPLEMENTACIÓN REQUERIDA

### 1. .env y .env.example

Agregar:

APP_TIMEZONE=America/Lima
DB_TIMEZONE=-05:00

Verificar que config/app.php tenga:
'timezone' => env('APP_TIMEZONE', 'America/Lima'),

### 2. config/database.php — conexión mysql

Agregar clave timezone (Laravel la aplica al conectar vía MySqlConnector):

'timezone' => env('DB_TIMEZONE', '-05:00'),

IMPORTANTE — NO usar PDO::MYSQL_ATTR_INIT_COMMAND en options.
En PHP 8+ de cPanel puede causar:
"Attribute value must be of type bool for selected attribute, string given"

Dejar options solo para SSL si aplica:

'options' => extension_loaded('pdo_mysql') && env('MYSQL_ATTR_SSL_CA') ? [
    PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
] : [],

### 3. Crear app/Support/MysqlTimezone.php

<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class MysqlTimezone
{
    public static function applySession(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        $timezone = config('database.connections.mysql.timezone', '-05:00');
        DB::statement('SET time_zone = ?', [$timezone]);
    }
}

### 4. app/Providers/AppServiceProvider.php — boot()

Registrar SET time_zone en cada conexión MySQL:

use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

En boot(), llamar registerMysqlSessionTimezone() que:
- Escuche ConnectionEstablished y ejecute SET time_zone = ? con config database.connections.mysql.timezone
- En la conexión actual también ejecute SET time_zone al arrancar

### 5. Antes de stored procedures que usan NOW()

En controladores que llaman CALL USP_WEB_OPE_PEDIDOS_CREAR (o similares):

use App\Support\MysqlTimezone;

MysqlTimezone::applySession();
// luego DB::select('CALL USP_...', [...]);

Archivos típicos:
- app/Http/Controllers/Web/CheckoutController.php
- app/Http/Controllers/Web/ShopDetailController.php

### 6. Reemplazar NOW() en SQL crudo de PHP

Buscar en el proyecto:
NOW()
CURRENT_TIMESTAMP
CURDATE()
DB::raw('NOW()')

Reemplazar por now() de Laravel donde sea fecha de negocio en PHP.

Ejemplo:
updated_at = NOW()  →  updated_at = ? con now() como parámetro

### 7. Script de verificación en public/ (opcional, producción)

Crear public/verificar-timezone.php protegido con ?key=...
Que bootstrapee Laravel, llame MysqlTimezone::applySession() y devuelva JSON con:
- laravel_now, mysql_now, mysql_session_tz, fix_activo (true si session_tz = -05:00 y misma fecha)

### 8. artisan-clear.php (si existe en el proyecto)

- Ejecutar config:clear ANTES de migraciones (no después)
- Mostrar diagnóstico de timezone al final
- NO subir bootstrap/cache/config.php desde local

## DEPLOY EN cPanel

1. Subir archivos modificados (no solo .env)
2. Agregar DB_TIMEZONE=-05:00 al .env de producción
3. Borrar bootstrap/cache/config.php en servidor si existe (caché vieja)
4. Abrir artisan-clear.php?key=... o php artisan config:clear && config:cache
5. Verificar https://DOMINIO/verificar-timezone.php?key=... → fix_activo: true
6. Pedido de prueba → created_at debe ser fecha/hora Lima
7. Eliminar rutas /test-time y verificar-timezone.php cuando termine

## VERIFICACIÓN EN NAVICAT

Navicat sin Laravel siempre muestra UTC. Para ver como la tienda:

SET time_zone = '-05:00';
SELECT @@session.time_zone, NOW();
SELECT id_pedido, created_at FROM ope_pedidos ORDER BY id_pedido DESC LIMIT 1;

created_at es la hora EN QUE SE GUARDÓ el pedido, no la hora actual del reloj.

## POR QUÉ -05:00 Y NO America/Lima EN MYSQL

Perú = UTC-5 fijo (sin horario de verano desde 1994).
America/Lima = -05:00 en la práctica.

En MySQL de cPanel, CONVERT_TZ con 'America/Lima' suele dar NULL (sin tablas de zonas).
El offset numérico -05:00 funciona siempre en SET time_zone.

Laravel/PHP: usar America/Lima (nombre).
MySQL sesión: usar -05:00 (offset).

## REGLAS DE ORO

1. No pedir al proveedor cambiar el servidor a hora Perú — UTC en MySQL es normal.
2. No usar PDO::MYSQL_ATTR_INIT_COMMAND para timezone en database.php.
3. No confundir hora del registro (created_at) con hora actual al consultar en Navicat.
4. Tras cambiar .env en producción → siempre config:clear.
5. No subir bootstrap/cache/config.php desde máquina local.

## ARCHIVOS A TOCAR (checklist)

- [ ] .env / .env.example (APP_TIMEZONE, DB_TIMEZONE)
- [ ] config/database.php (timezone en mysql)
- [ ] config/app.php (verificar timezone)
- [ ] app/Support/MysqlTimezone.php (crear)
- [ ] app/Providers/AppServiceProvider.php
- [ ] CheckoutController.php (MysqlTimezone antes de SP)
- [ ] ShopDetailController.php (MysqlTimezone antes de SP)
- [ ] Cualquier controlador con NOW() en SQL crudo
- [ ] public/verificar-timezone.php (opcional diagnóstico)
- [ ] public/artisan-clear.php (config:clear antes de migrate)

================================================================================
FIN PROMPT ZONA HORARIA
================================================================================
```

---

## PROMPT CORTO (réplica rápida en otro clone)

```
Aplica el fix ZONA HORARIA en este proyecto Laravel (cPanel Perú):

Problema: MySQL en UTC, Laravel en America/Lima → pedidos con fecha +1 día después de las 19:00.

Haz:
1. .env: APP_TIMEZONE=America/Lima y DB_TIMEZONE=-05:00
2. config/database.php → mysql.timezone = env('DB_TIMEZONE', '-05:00') — SIN PDO::MYSQL_ATTR_INIT_COMMAND
3. Crear app/Support/MysqlTimezone.php con applySession() → SET time_zone
4. AppServiceProvider: ConnectionEstablished + SET time_zone en boot
5. MysqlTimezone::applySession() antes de CALL USP_WEB_OPE_PEDIDOS_CREAR en Checkout y ShopDetail
6. Reemplazar NOW() en SQL crudo por now() de Laravel
7. Ruta /test-time y public/verificar-timezone.php para verificar fix_activo=true
8. Deploy: subir archivos, config:clear, NO subir bootstrap/cache/config.php desde local

Ver docs/ZONA HORARIA.md del proyecto Liam Vercceli como referencia.
```

---

## Resultado esperado tras el fix

```json
{
  "config_timezone": "America/Lima",
  "db_timezone_config": "-05:00",
  "mysql_session_tz": "-05:00",
  "laravel_now": "2026-07-04 22:07:55",
  "mysql_now": "2026-07-04 22:07:55",
  "fix_activo": true
}
```

---

*Basado en incidente real: rossety.pe · cPanel · MySQL UTC · solución aplicada julio 2026.*
