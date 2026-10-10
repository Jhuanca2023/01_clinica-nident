# Landing valentostile — Integración Laravel

## Estructura de assets

| Ruta | Descripción |
|------|-------------|
| `public/temp02/assets/css/` | Bootstrap, Fancybox, Font Awesome, style, responsive |
| `public/temp02/assets/js/` | jQuery, GSAP, custom, contact, etc. |
| `public/temp02/assets/img/` | Imágenes del landing |
| `public/temp02/assets/fonts/` | Fuentes Font Awesome |

## Vistas Blade

| Archivo | Rol |
|---------|-----|
| `resources/views/web/base_landing.blade.php` | Layout base del landing (sin header/footer de tienda) |
| `resources/views/web/partials/head_landing.blade.php` | CSS del template (`temp02`) |
| `resources/views/web/partials/footer_js_landing.blade.php` | JS del template (`temp02`) |
| `resources/views/web/pages/index.blade.php` | Contenido del `index.html` original |

## Controlador

- `App\Http\Controllers\Web\HomeController::Home()` → renderiza `web.pages.index`
- Metadatos opcionales desde `MetadatosPagina` (`nombre_pagina = home`)

## Origen del template

Plantilla HTML: `C:\Users\jhovani\Desktop\Ventas\Clientes\valentostile\index.html`

## Footer corporativo (admin ↔ landing)

| Campo BD | Uso en footer landing |
|----------|------------------------|
| `logo_footer` | Logo columna marca |
| `sobre_la_empresa` | Párrafo descriptivo |
| `footer_cta_subtitulo` / `footer_cta_titulo` | Bloque CTA superior |
| `url_whatsapp` | Botón y enlaces WhatsApp |
| `contacto_telefono` | Teléfono principal |
| `contacto_telefono_secundario` | Call Center |
| `contacto_direccion` | Texto copyright (ubicación) |
| `red_social_facebook` | Facebook |
| `red_social_instagram` | Instagram |
| `red_social_youtobe` | YouTube |
| `red_social_twitter` | Twitter / X |
| `red_social_linkedin` | LinkedIn |
| `red_social_tiktok` | TikTok |

- Admin: **Configuraciones → Información corporativa** (`FooterIndexPage` + preview canvas `FooterCanvasPreview.jsx`)
- Vista: `resources/views/web/pages/index/partials/footer_landing.blade.php`

## Slider hero (admin ↔ landing)

| Campo BD | Uso en landing |
|----------|----------------|
| `subtitulo` | Etiqueta superior naranja |
| `titulo` | H1 del slide |
| `descripcion` | Párrafo |
| `texto_boton` | Texto del CTA |
| `url_link` | Enlace del botón |
| `url_imagen` | Fondo del slide |

- API: `GET /api/web_slider/listar`, `POST /api/web_slider/actualizar`
- Admin React: `impacto-gigante-Frontend` → `SliderIndexPage` / `SliderEditPage` con preview **canvas** compacto (`HeroSlideCanvasPreview.jsx`)
- Vista: `resources/views/web/pages/index/partials/hero_slider.blade.php`

## Notas

- Las demás páginas web siguen usando `web.base` con assets en `public/temp/`.
- El formulario de contacto (`#contact-form`) conserva el comportamiento AJAX de `contact.js` (apunta a `contact.php` en el HTML original). Conectar a una ruta Laravel API es un paso posterior.
