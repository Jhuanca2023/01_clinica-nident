# PROMPT — Vista previa WhatsApp/Facebook desde logo (reutilizable)

**Versión:** 1.0  
**Proyecto de referencia:** `liamverceli-Backend` — https://rossety.pe  
**Uso:** Copia el bloque de `PROMPT_FIX_WHATSAPP_OG_COPIAR.txt` al chat del proyecto destino. Adjunta el logo.

---

## Qué resuelve

Al compartir el sitio en WhatsApp/Facebook aparece:

- Logo de **otro proyecto** (mandala, favicon viejo, nombre de marca anterior, etc.)
- **Con barra** (`dominio.com/`) y **sin barra** (`dominio.com`) muestran cosas distintas
- Título/descripción correctos pero **imagen incorrecta**
- Logo con **fondo negro** cuando debería ser blanco

**Causas habituales (proyectos copiados):**

1. Imagen OG hardcodeada del proyecto origen
2. La imagen OG **no** se cambia desde Metadatos SEO del admin (solo título/descripción)
3. WhatsApp usa miniatura **cuadrada** — si `og:image` falla, toma `apple-touch-icon` o `favicon`
4. Meta **cachea por separado** cada URL: `dominio.com`, `dominio.com/`, `dominio.com/compartir`
5. WhatsApp **móvil** y **laptop/Web** tienen cachés **independientes**

---

## Datos que debes completar antes de pegar

| Placeholder | Obligatorio | Ejemplo Liam Vercceli |
|-------------|-------------|------------------------|
| `«NOMBRE_MARCA»` | ✅ | `Liam Vercceli` |
| `«URL_PRODUCCION»` | ✅ | `https://rossety.pe` |
| `«SLUG_MARCA»` | ✅ | `liam` |
| `«RUTA_LOGO»` | ✅ | `public/imagenes/logo-principal.jpg` |
| `«LAYOUT_BLADE»` | ✅ | `web.base_shop` |
| `«PROYECTO_COPIADO»` | ✅ | `Royal Masajes` |
| `«TITULO_OG»` | Opcional | Desde `metadatos_paginas` slug `home` |
| `«DESCRIPCION_OG»` | Opcional | Desde `metadatos_paginas` slug `home` |

---

## Logo recomendado

| Archivo | Uso |
|---------|-----|
| `public/imagenes/logo-principal-v02.png` | **Preferido** — logo con fondo blanco |
| `public/imagenes/logo-principal.jpg` | Respaldo — puede verse oscuro en WhatsApp |

> Usar fondo **blanco** RGB(255,255,255) en imágenes OG. No usar fondo negro.

---

## Arquitectura de la solución

```
Usuario pega URL en WhatsApp
        ↓
Crawler (facebookexternalhit / WhatsApp)
        ↓
SocialSharePreview middleware → HTML mínimo solo meta OG
        ↓
og:image  → wa-og/«SLUG_MARCA»-«NOMBRE_MARCA_SLUG»-square.jpg (1200×1200, fondo BLANCO)
og:url    → siempre «URL_PRODUCCION» (sin barra en home)
og:title/description → metadatos_paginas home
        ↓
SIN favicon ni apple-touch-icon en HTML del crawler
```

**URL alternativa sin caché vieja:** `«URL_PRODUCCION»/compartir`  
→ `public/compartir/index.html` (HTML estático con meta OG)

---

## Archivos que la IA debe generar

```
public/imagenes/logo-principal-v02.png
public/wa-og/«SLUG_MARCA»-«NOMBRE_MARCA_SLUG»-square.jpg   ← 1200×1200 (WhatsApp)
public/wa-og/«SLUG_MARCA»-«NOMBRE_MARCA_SLUG»-wide.jpg     ← 1200×630 (Facebook)
public/og-share.jpg
public/favicon.ico
public/apple-touch-icon.png
public/imagenes/«SLUG_MARCA»-favicon-32.png
public/icon/«SLUG_MARCA»-icon-512.png
public/compartir/index.html
scripts/generate_«SLUG_MARCA»_brand_icons.php
```

**Script de generación:**

- Preferir `logo-principal-v02.png`, fallback `logo-principal.jpg`
- Fondo blanco `RGB(255,255,255)` en `fitImage()` y favicons
- En `gdToIco()`, mezclar transparencia con blanco (no negro)
- Ejecutar: `php scripts/generate_«SLUG_MARCA»_brand_icons.php`

---

## Archivos que la IA debe crear o actualizar (Laravel)

| Archivo | Cambio clave |
|---------|--------------|
| `app/Http/Middleware/SocialSharePreview.php` | Detectar crawler y responder HTML OG mínimo |
| `app/Support/SocialSharePreviewData.php` | `siteName`, `canonicalUrlForRequest()`, dimensiones imagen |
| `app/Helpers/Helper.php` (o clase Support) | Constante `SITE_OG_IMAGE = 'wa-og/...-square.jpg'`, WIDTH/HEIGHT 1200 |
| `resources/views/web/social_preview.blade.php` | Meta OG completo; **SIN** favicon ni apple-touch-icon |
| `resources/views/web/partials/og_meta.blade.php` | `og:site_name`, dimensiones 1200×1200 |
| `resources/views/web/partials/favicon.blade.php` | Marca correcta, iconos nuevos |
| Layout activo (`«LAYOUT_BLADE».blade.php`) | `@include('web.partials.og_meta')` |
| `app/Http/Kernel.php` | Registrar middleware `SocialSharePreview` en grupo `web` |
| `public/.htaccess` | Redirect 301 imágenes OG viejas → wa-og square |
| `public/robots.txt` | `Allow: /wa-og/`, `Allow: /compartir/` |
| `public/icon/site.webmanifest` | Nombre e iconos de la marca |
| `public/artisan-clear.php` | Sección diagnóstico OG (archivos + probe crawler) |

### Redirects en `.htaccess` (proyectos copia)

```apache
RewriteRule ^imagenes/royal-share-og[^/]*\.jpg$ /wa-og/«SLUG_MARCA»-«NOMBRE_MARCA_SLUG»-square.jpg [R=301,L]
RewriteRule ^imagenes/«SLUG_MARCA»-share-og-v[0-9]+\.jpg$ /wa-og/«SLUG_MARCA»-«NOMBRE_MARCA_SLUG»-square.jpg [R=301,L]
RewriteRule ^og-share\.jpg$ /wa-og/«SLUG_MARCA»-«NOMBRE_MARCA_SLUG»-square.jpg [R=301,L]
```

Adaptar reglas al nombre de imágenes del proyecto origen (`«PROYECTO_COPIADO»`).

---

## Deploy en cPanel (sin terminal)

1. Subir archivos PHP/Blade modificados + carpeta `public/wa-og/` + `public/compartir/`
2. Subir `public/.htaccess`, `public/robots.txt`, favicons
3. Abrir: `«URL_PRODUCCION»/artisan-clear.php?key=TU_CLAVE`
4. Verificar sección **Vista previa WhatsApp / Facebook**

---

## Validación post-deploy

### 1. Servidor

```bash
curl -A "facebookexternalhit/1.1" «URL_PRODUCCION» | grep og:image
curl -I «URL_PRODUCCION»/wa-og/«SLUG_MARCA»-«NOMBRE_MARCA_SLUG»-square.jpg
```

Imagen blanca nueva ≈ **80–85 KB**. Versión negra vieja ≈ **93 KB**.

### 2. Facebook Sharing Debugger (3 URLs)

https://developers.facebook.com/tools/debug/

| URL | Acción |
|-----|--------|
| `«URL_PRODUCCION»/compartir` | **Recuperar nueva información** |
| `«URL_PRODUCCION»` | **Volver a rastrear** |
| `«URL_PRODUCCION»/` | **Volver a rastrear** |

### 3. WhatsApp

- Compartir en chat **nuevo**: `«URL_PRODUCCION»/compartir`
- **Móvil:** Ajustes → Almacenamiento → Borrar caché
- **Laptop/Web:** Borrar caché del navegador + cerrar WhatsApp Web

---

## Errores comunes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| Logo otro proyecto | OG viejo o favicon en HTML crawler | `wa-og/` + quitar favicon de `social_preview.blade.php` |
| Con `/` texto viejo, sin `/` texto nuevo | Caché Meta separada | Depurar **ambas** URLs en Facebook Debugger |
| Móvil OK, laptop mal | Caché WhatsApp Web independiente | Borrar caché navegador + WhatsApp |
| Logo muy negro | JPG sobre fondo negro | `logo-principal-v02.png` + fondo blanco en script |
| Sin tags og:* | Layout sin `og_meta.blade.php` | Crear partial e incluir en layout activo |

---

## Referencia Liam Vercceli

```
Marca: Liam Vercceli
Dominio: https://rossety.pe
Slug: liam
Logo: public/imagenes/logo-principal.jpg
Layout: web.base_shop
Metadatos: MetadatosPagina::metaVigente('home')
OG objetivo: public/wa-og/liam-vercceli-square.jpg
Compartir: https://rossety.pe/compartir
```

**Prompt para copiar:** `docs/PROMPT_FIX_WHATSAPP_OG_COPIAR.txt`

---

*Prompt v1 — Liam Vercceli, julio 2026.*
