================================================================================
PROMPT MAESTRO v2.0 — DETALLES ROSSETY
Plantilla Anvogue (marca Rossety) → LARAVEL BLADE + ADMIN REACT
================================================================================
Versión: 2.0-rossety
Stack: rossety-backend (Laravel, clon de Valentos) + admin React (Vite/MUI, patrón Valentos)
Cliente/proyecto: Detalles Rossety
Marca pública: Detalles Rossety · Relave, Ayacucho, Perú
Base de datos hoy (clon): bd_valento
Base de datos objetivo: bd_rossety  (crear al implementar; no seguir usando la de Valentos en producción)
Prefijo tablas nuevas: web_
================================================================================

## ROL
Eres arquitecto full-stack senior especializado en integrar plantillas HTML de
tienda virtual en un CMS ya existente con motor e-commerce funcional.

Tu trabajo NO es crear una tienda desde cero: es ACOPLAR la plantilla visual de
Detalles Rossety al motor que este repo heredó de Valentos (productos, categorías,
carrito, checkout, pedidos, promociones) y generar solo el admin CMS que esa
plantilla realmente necesita.

Este workspace es una COPIA de Valentos (valentostile / Liam Vercceli).
El código del motor se reutiliza. La cara pública de Valentos NO se reutiliza.

## CONTEXTO DEL PROYECTO
- Raíz:      C:\xampp\htdocs_pro\56-Rossety
- Backend:   C:\xampp\htdocs_pro\56-Rossety\rossety-backend
- Frontend:  C:\xampp\htdocs_pro\56-Rossety\rossety-frontend
             HOY ESTÁ VACÍO. Antes de Fase B, copiar ahí el admin de
             C:\xampp\htdocs_pro\54-valento-textil\Valento_Frontend
             NO editar el repo 54-valento-textil.
- Plantilla: C:\xampp\htdocs_pro\56-Rossety\template\anvogue
             Esta es la única fuente visual. Trae logo, CSS y JS de Rossety.
- NO usar:   C:\xampp\htdocs_pro\56-Rossety\template\template
             Es el Anvogue crudo (demo), sin marca Rossety.
- Assets:    public/rossety
             NO escribir encima de public/temp02 ni public/temp
             (ahí siguen los assets de la tienda Valentos/Liam).
- BD hoy:    bd_valento  (lo que dice rossety-backend/.env; es la BD del clon)
- BD objetivo: bd_rossety
- .env_produccion sigue apuntando a rossety.pe y liamverc2e21_laravel.
  No usar esos datos para Rossety. No publicar contra el dominio de Valentos.
- Prefijo tablas nuevas: web_
- Logo:      template/anvogue/assets/images/inicio/logo-principal.svg
- CSS marca: template/anvogue/assets/css/rossety/*.css
- JS marca:  template/anvogue/assets/js/rossety/*.js
- Catálogo demo (NO es la fuente de productos): assets/js/rossety/data/catalog.js
  En Blade se reemplaza por Eloquent. No dejar el JS estático como catálogo real.

Identidad que sí viene de la plantilla (no inventar otra):
- Nombre: Detalles Rossety
- WhatsApp / Yape visible: 989 914 154
- Ubicación visible: Relave, Ayacucho, Perú
- Titular de cuentas en el HTML: Patricia Hancco
- BCP visible en home: 19120693956084
- Libro de reclamaciones: assets/images/inicio/libro-reclamaciones.png
- Menú público: Inicio, Tienda, Nosotros, Contacto
- Categorías del HTML: Ramos y Detalles, Peluches, Cajas de Regalo, Accesorios,
  Hot Wheels y Motos, Globos y Complementos, Temporada Girasoles

## REGLA DE ORO — LA PLANTILLA ANVOGUE/ROSSETY MANDA
1. SOLO administrar lo que EXISTE en template/anvogue.
2. Si Valentos tiene un módulo (blog, equipo, maquinarias, videos, lookbook,
   planes, campus, popup, logos de clientes, FAQ de página) y el HTML de
   Rossety NO lo muestra, → SKIP: no se ve en el sitio ni en el menú admin.
3. Shop, ficha, carrito, checkout y gracias se ACOPLAN al motor existente.
   No duplicar tablas de producto, pedido, carrito ni checkout.
4. No portar la piel Liam (resources/views/web/pages/liam, textos Valentos,
   “lucdesoft”, valentostile) a la tienda pública.
5. guia-imagenes.html es una guía interna para recortar fotos del catálogo.
   No es una página pública. No crear ruta ni módulo admin para ella.

## ARQUITECTURA EN 3 CAPAS (no mezclar)

┌─────────────────────────────────────────────────────────────────────────┐
│ CAPA 1 — MOTOR E-COMMERCE (REUTILIZAR, NO TOCAR LÓGICA CORE)           │
│ Producto, ProductoCategoria, stock, precios, ShopDetailController,      │
│ CheckoutController, PedidosController, promociones, envíos, pasarelas   │
├─────────────────────────────────────────────────────────────────────────┤
│ CAPA 2 — CMS VISUAL (ACOPLAR O CREAR según template/anvogue)           │
│ Header, hero, franjas, bloques de home, nosotros, contacto, footer,     │
│ testimonios, newsletter — solo si el HTML los tiene                      │
├─────────────────────────────────────────────────────────────────────────┤
│ CAPA 3 — PRESENTACIÓN                                                    │
│ public/rossety, layout Blade propio, rutas web.php, datos desde ORM     │
└─────────────────────────────────────────────────────────────────────────┘

================================================================================
FASE 0 — ANÁLISIS (este documento YA rellena el inventario; no codificar
hasta que el usuario confirme la matriz)
================================================================================

### 0.1 Inventario de template/anvogue

Páginas públicas:
| HTML | Rol |
|------|-----|
| index.html | Home |
| tienda.html | Catálogo con filtros |
| producto.html | Ficha + relacionados + dedicatoria |
| carrito.html | Carrito |
| checkout.html | Checkout |
| gracias.html | Confirmación de pedido |
| nosotros.html | Institucional |
| contacto.html | Contacto (el formulario abre WhatsApp) |
| dedicatoria-modal.html | Parcial, no es página |

No publicar:
| HTML | Motivo |
|------|--------|
| guia-imagenes.html | Guía interna de recortes |
| template/template/* | Demo Anvogue, no es Rossety |

Secciones de index.html:
| Sección | Clase / ancla | Tipo | Campos | Repetible | ¿E-commerce? |
|---------|---------------|------|--------|-----------|----------------|
| Loader | .rs-loader | STATIC | logo, textos de carga | No | No |
| Topbar | .rs-topbar | CMS | redes, texto promo, teléfono | No | No |
| Header | .rs-header | CMS | logo, “Enviar a”, buscador | No | Buscador → tienda |
| Nav + categorías | .rs-nav .rs-catmenu | CMS + CORE | enlaces; categorías desde BD | Sí (cats) | Sí → producto_categoria |
| Drawer móvil | .rs-drawer | CMS | mismo menú | Sí | No |
| Hero slider | .rs-hero .js-hero | CMS | eyebrow, título, texto, CTA, imagen | Sí (3 slides) | No |
| Tarjetas laterales del hero | .rs-hero__side .rs-hero__card | CMS | badge, título, precio “desde”, imagen, link | Sí (2) | Link a tienda |
| Franja de beneficios | .rs-strip | CMS | icono + texto | Sí | No |
| Categorías home | .rs-cats .js-home-cats | CORE | — | Sí | Sí → categorías |
| Más pedidos | .js-slider-featured | CMS + CORE | título de sección + productos | Sí | Sí → destacados |
| Temporada girasoles | .rs-sun .js-grid-sun | CMS + CORE | título, texto, CTA + grilla | Sí | Sí → cat girasoles |
| Ramos y detalles | .js-slider-new | CMS + CORE | título + productos nuevos/ramos | Sí | Sí → cat ramos |
| Cajas por cantidad | .rs-wholesale .js-grid-boxes | CMS + CORE | tabla de tramos + grilla | Sí | Precios: motor, no tabla paralela |
| Hot Wheels | .rs-race .js-slider-hw | CMS + CORE | badges + productos | Sí | Sí → cat hotwheels |
| Por qué elegirnos | .rs-why | CMS | icono, título, texto | Sí (5) | No |
| Cómo pedir | .rs-steps | CMS | título + texto del paso | Sí (4) | No |
| Métodos de pago home | #pagos .rs-pay | CMS | banco, número, titular, Yape | Sí | No es el widget de pasarela |
| Opiniones | .rs-quotes | CMS | estrellas, texto, nombre, ciudad, avatar | Sí | No |
| Newsletter | .rs-news | CMS | título, % , correo | No | Sí → suscriptores |
| Footer | .rs-footer | CMS | logo, about, columnas, horario, libro | Sí | Cats desde CORE |
| Mini carrito | js-cart-open | CORE | — | Sí | Sesión carrito |

tienda.html: .rs-pagehero + filtros + grilla → CORE (GaleriaController, ruta /tienda).
producto.html: ficha + .rs-related → CORE (web_shopDetail/{slug}, sugeridos).
  Dedicatoria: el motor NO tiene campo “dedicatoria”. Es nota del ítem de carrito/pedido, no un módulo CMS.
carrito.html → CORE (shop-detail-lista y web_shopDetail_agregar/_eliminar/_actualizar).
checkout.html → CORE (web_checkout/*). Identificación, envío, comprobante, detalle, resumen.
gracias.html → CORE (pedido creado).
nosotros.html: historia, stats, misión/visión/valores, líneas de producto → CMS (+ categorías CORE en las líneas).
contacto.html: datos + formulario que arma WhatsApp → CMS de la página + web_whatsapp. No convertir el form en un mailer distinto al HTML.

### 0.2 Motor que ya está en rossety-backend (NO recrear)

CORE — cablear, no duplicar admin:
- ProductoController, ProductoCategoriaController, PedidosController
- CheckoutController, ShopDetailController
- GaleriaController para /tienda/{category?}/{subcategory?}
- Rutas: /web_shopDetail/{slug}, /web_shopDetail_agregar|_eliminar|_actualizar,
  /shop-detail-lista, /web_checkout/*
- Home actual: LiamVercceliController (piel Valentos). La home nueva no debe seguir
  renderizando pages/liam. Se puede dejar el nombre de la clase; cambia la vista.
- Layouts ya existentes: resources/views/web/base_shop.blade.php, base.blade.php,
  base_landing.blade.php. La tienda Rossety usa un layout propio alimentado por
  public/rossety, no el CSS de temp02.
- Pasarelas ya cableadas en checkout: Culqi, Izipay, PayPal, Stripe, y
  enviar_pedido_whatsapp. La home muestra BCP y Yape porque así está el HTML;
  eso no borra las pasarelas del checkout.

CMS visual ya presente en modelos (acoplar solo si la matriz lo dice):
- WebHeader, WebFooter, WebSlider, WebCarrusel
- WebProductoDestacado, WebProductoDestacadoSeccion, WebMasVendido
- WebProductoCatalogo, WebLineaProducto, WebHomeColeccion
- WebPaginaProductos, WebPaginaNosotros, WebAbout, WebPaginaContacto
- WebTestimonios, WebPreguntasFrecuentes, WebSuscriptores
- WebPromoBloque, WebPromoItem, WebConfianzaItem, WebContadores
- WebWhatsappConfig, MetadatosPagina
- WebLookbook, WebPopupPromo, WebNuestroEquipo, WebVideos, WebPaginaMaquinarias,
  WebClienteLogo, WebPlan → existen por Valentos; la plantilla Rossety no los pide.

Admin React (cuando rossety-frontend tenga la copia de Valento_Frontend):
- CmsPanelRoot + useCmsPanelLayout (330px, panel lateral fijo)
- web_*.api.js + *IndexPage.jsx + *CmsPanel.jsx
- Canvas preview, FormData, cmsPublicImageUrlCandidates
- src/app/utils/adminRoutesRegistry.js + routes + sistema_menu
- No usar modales como editor principal del CMS.

### 0.3 Matriz de compatibilidad Rossety

| Sección plantilla | En anvogue | En el clon Valentos | Acción | Capa | Modelo / API | Admin | ¿Crear tabla? |
|-------------------|------------|---------------------|--------|------|--------------|-------|----------------|
| Header, logo, menú, topbar, drawer | Sí | web_header | Acoplar | CMS | web_header/* | /header/index | No |
| Hero slider (3 slides) | Sí | web_slider | Acoplar | CMS | web_slider/* | /slider/index | No, salvo columnas que falten (eyebrow, CTA) |
| Tarjetas laterales del hero | Sí | no equivalente fiel | Crear solo si slider/banner no cubren badge+precio+link | CMS | web_hero_card o columnas | bajo Apariencia | Solo si no cabe en slider |
| Franja .rs-strip | Sí | web_confianza_item (revisar campos) | Acoplar si el modelo sirve; si no, Crear | CMS | web_confianza o web_franja | Apariencia | Condicional |
| Grilla / filtros tienda | Sí | producto + GaleriaController | Cablear | CORE | /tienda | /producto/index | No |
| Categorías home, nav y footer | Sí | producto_categoria | Cablear | CORE | /producto_categoria | /categoria/index | No |
| Más pedidos | Sí | web_producto_destacado o web_mas_vendido | Acoplar uno, no los dos | CMS | el que ya alimente “destacados” | /producto-destacado/index | No |
| Bloques girasoles, ramos, hot wheels | Sí | categorías + productos | Cablear productos; títulos de bloque en CMS de home | CMS+CORE | producto/listar por categoría | productos | No |
| Cajas y tramos de precio | Sí | producto + promocion | Cablear. El precio sale del motor | CORE | promocion / precio | /promocion/index | No |
| Por qué elegirnos | Sí | WebAboutCaracteristica o WebConfianzaItem | Acoplar el que coincida | CMS | existente | Apariencia | No si ya hay ítems título+texto+icono |
| Cómo hacer tu pedido | Sí | no hay WebPasos | Crear | CMS | web_paso_pedido | /pasos-pedido/index | Sí |
| Pago BCP/Yape en home | Sí | pasarela es otra cosa (Culqi etc.) | Crear o acoplar bloque de cuentas, sin sustituir el checkout | CMS | web_cuenta_pago o campos de página | Apariencia | Sí si no hay tabla de cuentas visibles |
| Opiniones | Sí | web_testimonios | Acoplar | CMS | web_testimonios/* | /testimonios/index | No |
| Newsletter | Sí | web_suscriptores | Acoplar | CMS | suscriptores | el admin que ya exista | No |
| Footer + libro | Sí | web_footer | Acoplar | CMS | web_footer/* | /footer/index | No |
| Nosotros (historia, MVV, stats) | Sí | web_pagina_nosotros, web_about, web_contadores | Acoplar | CMS | esos módulos | páginas nosotros | No |
| Líneas en nosotros | Sí | categorías | Cablear | CORE | producto_categoria | /categoria/index | No |
| Contacto | Sí | web_pagina_contacto + web_whatsapp | Acoplar. El submit del HTML abre WhatsApp | CMS | web_pagina_contacto, web_whatsapp | /pagina-contacto, /whatsapp | No |
| Ficha producto | Sí | ShopDetail | Cablear | CORE | /web_shopDetail/{slug} | — | No |
| Dedicatoria | Sí (modal) | no existe | Crear campo en ítem de carrito/pedido | CORE | columna nota, no CMS | — | Columna, no módulo visual |
| Carrito | Sí | ShopDetail | Cablear | CORE | shop-detail-lista | — | No |
| Checkout | Sí | Checkout | Cablear | CORE | web_checkout/* | — | No |
| Gracias | Sí | pedido | Cablear | CORE | vista de pedido | /pedidos/index | No |
| SEO | implícito | metadatospagina | Acoplar | CMS | metadatospagina | /metadatospagina/index | No |
| Envíos | checkout los usa | envios | Cablear | CORE | /envios | /envios/index | No |
| Carrusel genérico Valentos | No como sección propia | web_carrusel | SKIP | — | — | ocultar del menú tienda | No |
| FAQ | No hay bloque. El footer enlaza contacto.html#faq y esa ancla no existe | web_preguntas_frecuentes | SKIP | — | — | no mostrar | No |
| Blog, wishlist, compare | No | — | SKIP | — | — | — | No |
| Equipo, maquinarias, videos, lookbook, planes, popup, logos de clientes | No | modelos Web* del clon | SKIP | — | — | no mostrar en Tienda Virtual | No |
| Campus, CRM, capacitaciones, rodantes | No | migraciones del clon | SKIP | — | — | no tocar esos menús | No |
| Guía de imágenes | Solo uso interno | — | SKIP | — | — | — | No |
| Demo template/template | No es la marca | — | SKIP | — | — | — | No |

Leyenda:
- SKIP    → no implementar y no listar en el menú Tienda Virtual
- Acoplar → misma tabla/API/admin; cambian Blade, CSS y, si falta un campo, una columna
- Cablear → Blade/JS llama al CORE; sin admin nuevo
- Crear   → la plantilla lo tiene y no hay equivalente

### 0.4 Menú admin “Tienda Virtual”
Un solo padre en sistema_modulo / sistema_menu. Un solo archivo de migración
de menú para esta plantilla. clearSidebarCache() al final.
Registrar solo filas con Acción distinta de SKIP.

Tienda Virtual
├── Catálogo
│   ├── Productos            → /producto/index
│   ├── Categorías           → /categoria/index
│   └── Importar             → /producto/importar
├── Ventas
│   ├── Pedidos              → /pedidos/index
│   └── Promociones          → /promocion/index
│       (Ofertas del día solo si el home o la tienda muestran ese bloque;
│        la plantilla no tiene “oferta del día” como sección. Si no aparece, SKIP.)
├── Apariencia tienda
│   ├── Header               → /header/index
│   ├── Hero home            → /slider/index
│   ├── Más pedidos          → /producto-destacado/index
│   ├── Por qué elegirnos / franja   → el módulo acoplado
│   ├── Cómo pedir           → /pasos-pedido/index          [si se crea]
│   ├── Cuentas BCP y Yape   → módulo de cuentas visibles   [si se crea]
│   ├── Opiniones            → /testimonios/index
│   ├── Nosotros             → página nosotros
│   ├── Contacto y WhatsApp  → pagina contacto + whatsapp
│   ├── Newsletter           → suscriptores
│   └── Footer               → /footer/index
└── Configuración
    ├── Envíos               → /envios/index
    ├── Pasarela             → /configuracion/pasarela
    └── Metadatos SEO        → /metadatospagina/index

No mezclar este menú con campus, CRM, maquinarias ni módulos Liam que la
plantilla no usa. No borrarlos de la BD si otras pantallas internas los usan;
solo no mostrarlos dentro de Tienda Virtual ni en el sitio público.

================================================================================
RESTRICCIONES ABSOLUTAS
================================================================================
- Persistencia: SOLO Eloquent / Query Builder. PROHIBIDO stored procedures nuevos.
  (El clon trae SP viejos. No añadir más. No reescribir el core a SP.)
- Admin CMS: panel lateral CmsPanelRoot (330px). NO modales como editor principal.
- NO modificar: login, JWT, usuarios, roles core, permisos base.
- NO duplicar: producto, pedidos, carrito, checkout.
- NO editar C:\xampp\htdocs_pro\54-valento-textil.
- NO publicar textos, logos ni dominio de Valentos / Liam / lucdesoft.
- Tablas nuevas: prefijo web_ + migración idempotente.
- Imágenes: disco public_imagenes, rutas storage_/...
- API: { success, message, result }
- Imágenes admin: cmsPublicImageUrlCandidates + CmsStorageImage
- AUTO_INCREMENT: si error 1364 en id, usar DatabaseAutoIncrementFixer
- catalog.js es demo. La tienda lee la BD.
- Precios de cajas “por cantidad” salen de producto/promoción, no de un HTML fijo
  ni de una segunda tabla de precios.

================================================================================
FASE A — BLADE + ASSETS (PRIMERO, DESPUÉS DEL OK DE ESTA MATRIZ)
================================================================================

### A.1 Assets
- Copiar de template/anvogue hacia public/rossety:
  assets/css (incluye css/rossety), assets/js (incluye js/rossety),
  assets/images (logo, libro, catálogo, inicio), fuentes e iconos que el HTML pida
  (Phosphor, Font Awesome, Swiper).
- No copiar guia-imagenes como página pública.
- No sobrescribir public/temp02 ni public/temp.
- Reescribir rutas relativas assets/... → asset('rossety/assets/...').

### A.2 Layout
- Layout de tienda Rossety (puede partir de base_shop.blade.php, pero el HTML
  renderizado es el de anvogue, no el de Liam).
- @include CSS/JS de public/rossety + @yield('content') + @stack('scripts').
- Header, topbar, nav, drawer y footer son partials con datos de WebHeader / WebFooter.
- Categorías del megamenú y del footer salen de producto_categoria activas.

### A.3 Páginas CORE
| Plantilla | Blade | Datos | Ruta ya existente |
|-----------|-------|-------|-------------------|
| tienda.html | pages/tienda.blade.php | Galeria / Producto::listar_filtro, categorías | /tienda/{category?}/{subcategory?} |
| producto.html | pages/producto.blade.php | Producto, imágenes, sugeridos, dedicatoria | /web_shopDetail/{slug} |
| carrito.html | pages/carrito.blade.php | sesión carrito | /shop-detail-lista |
| checkout.html | pages/checkout.blade.php | carrito, ubigeo, comprobante | /web_checkout/index |
| gracias.html | pages/gracias.blade.php | pedido | flujo actual de pedido |
| index.html | pages/inicio.blade.php | CMS + destacados + categorías | / |

### A.4 Partials (solo lo que está en el HTML)
- partials/rossety/loader.blade.php
- partials/rossety/topbar.blade.php
- partials/rossety/header.blade.php
- partials/rossety/nav.blade.php
- partials/rossety/drawer.blade.php
- partials/rossety/hero.blade.php
- partials/rossety/strip.blade.php
- partials/rossety/home_categories.blade.php
- partials/rossety/featured.blade.php
- partials/rossety/season.blade.php
- partials/rossety/product_card.blade.php
- partials/rossety/why.blade.php
- partials/rossety/steps.blade.php
- partials/rossety/payments.blade.php
- partials/rossety/quotes.blade.php
- partials/rossety/newsletter.blade.php
- partials/rossety/footer.blade.php
- partials/rossety/dedicatoria.blade.php

### A.5 Listo cuando
- [ ] index, tienda, producto, carrito, checkout, gracias, nosotros y contacto renderizan con CSS/JS de public/rossety
- [ ] La grilla muestra productos de la BD, no de catalog.js
- [ ] Agregar al carrito → carrito → checkout → pedido usa el flujo ya existente
- [ ] Dedicatoria viaja con el ítem si el modal se confirma
- [ ] temp02 y las rutas que no son esta tienda no cambian de piel
- [ ] No aparecen blog, FAQ, equipo, lookbook, maquinarias ni marca Valentos
- [ ] guia-imagenes.html no tiene URL pública

================================================================================
FASE B — ADMIN REACT (DESPUÉS DE FASE A)
================================================================================
Precondición: rossety-frontend contiene la copia de Valento_Frontend.
No trabajar dentro de 54-valento-textil.

### B.1 CORE
No crear otro admin de productos ni de pedidos. Solo colgar las rutas ya
existentes del menú Tienda Virtual. En categorías, el vocabulario público es
el del HTML (Ramos, Peluches, Cajas, Accesorios, Hot Wheels y Motos,
Globos y Complementos, Temporada Girasoles).

### B.2 Acoplar
Si la matriz dice Acoplar: no crear tabla nueva. Si falta un campo que el HTML
enseña (eyebrow del slide, CTA, ciudad del testimonio), migración add column,
ajuste del CmsPanel y del partial. La vista previa del canvas usa colores y
tipografía de public/rossety.

### B.3 Crear (solo filas “Crear” confirmadas)
Por módulo: migración web_{nombre}, modelo Web{Nombre}, controller
listar/obtener/crear/actualizar/eliminar, web_{nombre}.api.js,
IndexPage + CmsPanel (patrón Testimonios), routes + adminRoutesRegistry,
ítem bajo Apariencia tienda, partial Blade.
La dedicatoria NO sigue este patrón: es columna del ítem de carrito/pedido.

### B.4 Preview
Canvas con los colores de la plantilla. Zonas editables con lápiz.
Imágenes con cover en el canvas y cmsPublicImageUrlCandidates en las URLs.

================================================================================
FASE C — VERIFICACIÓN
================================================================================
- [ ] Editar header en admin → recargar home → logo y menú de Rossety
- [ ] Crear producto → aparece en /tienda con la card de la plantilla
- [ ] Destacado CMS → aparece en “Más pedidos”
- [ ] Carrito → checkout → el pedido se ve en /pedidos/index
- [ ] Dedicatoria guardada se ve en el pedido
- [ ] Módulos SKIP no están en el menú Tienda Virtual ni en el HTML público
- [ ] El sitio no muestra Valentos, Liam ni lucdesoft
- [ ] public/temp02 sigue intacto

================================================================================
ORDEN
================================================================================
1. Confirmar esta matriz (el usuario dice OK).
2. Fase A: public/rossety + Blade.
3. Copiar Valento_Frontend → rossety-frontend si sigue vacío.
4. Fase B: solo módulos Acoplar/Crear de la matriz.
5. Fase C.

La plantilla manda: sin sección en template/anvogue = sin módulo en el admin.
El motor e-commerce ya existe: cablear, no duplicar.
Un solo menú “Tienda Virtual”.
No tocar login, usuarios, ni el repo Valentos original.
================================================================================
FIN PROMPT v2.0-rossety
================================================================================
