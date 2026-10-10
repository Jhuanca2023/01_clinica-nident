# Despliegue en cPanel — Liam Vercceli

## Estructura en el servidor

```
/home/liamverc2e21/
├── laravel/          ← código Laravel (app, vendor, .env, storage…)
└── public_html/      ← document root (index.php apunta a ../laravel)
```

`public_html/index.php` debe cargar:

```php
require __DIR__ . '/../laravel/vendor/autoload.php';
$app = require_once __DIR__ . '/../laravel/bootstrap/app.php';
```

## Configuración obligatoria en `.env` de producción

Laravel y los assets públicos están en carpetas distintas. Sin esto, `@vite`, `URL::asset()` y `public_path()` buscan en `laravel/public/` y fallan.

En `/home/liamverc2e21/laravel/.env` agregar:

```env
APP_PUBLIC_PATH=/home/liamverc2e21/public_html
VITE_PUBLIC_DIR=../public_html
```

Después de cambiar `.env`, borrar `bootstrap/cache/config.php` y ejecutar `artisan-clear.php`.

## Compilar assets Vite en cPanel

Desde la carpeta `laravel/` (con `VITE_PUBLIC_DIR` en `.env`):

```bash
npm run build
```

Esto genera `public_html/build/` (manifest + JS/CSS). El admin React se compila aparte con `pnpm run build:production` en el Frontend.

## Imágenes de categorías / CMS (`storage_/`)

Las imágenes subidas desde el admin se guardan en `public/storage_/` (categorías, productos, sliders, etc.). **Esa carpeta está en `.gitignore`** y no se sube con git/FTP del código.

En cPanel debe existir:

```
public_html/storage_/categorias/
  cat_polo_cuellera.jpg
  cat_polo_basico.jpg
  … (una por categoría)
```

**Síntoma si faltan:** todas las categorías muestran la misma imagen (fallback `temp02/img/inicio/productos/1.jpg`).

**Solución:** copiar desde local `liamverceli-Backend/public/storage_/` → `public_html/storage_/` en el servidor (File Manager o FTP). Requiere también `APP_PUBLIC_PATH` configurado para que Laravel encuentre los archivos.


### 1. Rutas Windows en sesiones (`C:\xampp\...`)

**Causa:** Se subió `bootstrap/cache/config.php` generado en local con `php artisan config:cache`.

**Solución:** En cPanel → File Manager, borrar en `/home/liamverc2e21/laravel/bootstrap/cache/`:

- `config.php`
- `packages.php`
- `services.php`

Luego abrir: `https://rossety.pe/artisan-clear.php?key=lucdesoft2026`

### 2. `Access denied for user 'root'@'localhost'`

**Causa:** Misma caché de config con credenciales locales, o falta `.env` en el servidor.

**Solución:** Crear/editar `/home/liamverc2e21/laravel/.env` con credenciales de cPanel (MySQL Databases):

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://rossety.pe

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=liamverc2e21_laravel
DB_USERNAME=liamverc2e21_laravel
DB_PASSWORD=<contraseña de cPanel>
```

Borrar caché bootstrap (arriba) y ejecutar `artisan-clear.php`.

### 3. `artisan-clear.php` — "No se encontró Laravel"

**Causa:** El script estaba en `public_html` pero buscaba `/home/lucdesof/laravel` (otro proyecto).

**Solución:** Subir la versión actualizada de `public/artisan-clear.php` a `public_html/artisan-clear.php`. Detecta `/home/liamverc2e21/laravel` automáticamente.

## Qué NO subir desde local

| Archivo/carpeta | Motivo |
|-----------------|--------|
| `bootstrap/cache/config.php` | Rutas y BD de Windows |
| `bootstrap/cache/packages.php` | Generado en servidor |
| `bootstrap/cache/services.php` | Generado en servidor |
| `.env` local | Credenciales distintas |
| `public/admin/` | Se compila en producción |

## Admin React (`public/admin`)

En **desarrollo local** el admin corre con Vite (`pnpm run dev` en `liamverceli-Frontend`).

En **producción (cPanel)** compilar allí o en CI:

```bash
cd liamverceli-Frontend
pnpm run build:production
```

Esto copia `dist/` → `liamverceli-Backend/public/admin/` (solo con `DEPLOY_LARAVEL_ADMIN=1`).

La carpeta `public/admin` está en `.gitignore` — no debe versionarse.

## Checklist de despliegue

1. Subir código a `/home/liamverc2e21/laravel/` (sin `bootstrap/cache/*.php` ni `public/admin` si compilas en servidor).
2. Verificar `.env` de producción en `laravel/.env`.
3. Subir `artisan-clear.php` a `public_html/`.
4. Abrir `https://rossety.pe/artisan-clear.php?key=lucdesoft2026`.
5. Compilar admin: `pnpm run build:production` (destino `public/admin` en el servidor).
6. Permisos: `storage/` y `bootstrap/cache/` escribibles (755 o 775).
