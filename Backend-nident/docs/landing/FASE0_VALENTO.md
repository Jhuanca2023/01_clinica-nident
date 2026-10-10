# Fase 0 — Valento (resumen)

Análisis de acople entre la plantilla `public/temp02` y el motor heredado de Liam
Vercceli. Documento breve: la matriz completa se discutió en chat, aquí queda lo
que hace falta para seguir ejecutando Fase A.

Referencia: `docs/PROMPT_MAESTRO_TIENDA_VALENTO.md` (raíz del repo).

---

## 1. Matriz resumida (home y flujo de compra)

Acción: **SKIP** | **Acoplar** (HTML de temp02 sobre vista existente) | **Cablear**
(vista lista, faltan datos de BD) | **Extender** | **Crear**.

| Página | Sección | Selector real | ¿En motor? | Acción | Admin | ¿Migración? |
|---|---|---|---|---|---|---|
| Home | Topbar / header / sticky | `.vl-topbar` `.lv-header` `.lv-sticky-bar` | Sí (`web_header`) | Acoplar | `/header/index` | No |
| Home | Hero | `.vl-hero` | Sí (`web_slider`) | Acoplar | `/slider/index` | No |
| Home | Categorías | `.lv-categories-section` | Sí (`producto_categoria`) | Acoplar | `/categoria/index` | No |
| Home | Productos | `.latest-products` | Sí | Acoplar | `/producto/index` | No |
| Home | Promo | `.promo-section` | Sí (`web_promo_bloque`) | Acoplar | `/promo-bloque/index` | No |
| Home | Lookbook | `.lookbok-section` | Sí (`web_lookbook`) | Acoplar | `/lookbook/index` | No |
| Home | Footer banner / footer | `.lv-footer-banner` `footer.vf` | Sí (`web_footer`) | Acoplar | `/footer/index` | No |
| Home | WhatsApp / popup | `.lv-wa-wrap` | Sí | Acoplar | `/whatsapp/index` | No |
| /tienda | Filtros talla/color/precio | `.cat-chk-talla` `.cat-chk-color` `#cat-price-range` | Sí (`GaleriaController` + JSON variantes) | Acoplar | — | No |
| Detalle | Galería, color, talla | `.sd-main` `.sd-color-dot` `.sd-size-btn` | Sí | Acoplar | — | No |
| Detalle | Tarjeta asesor WhatsApp | `.sd-wa-card` | Sí (acoplada en Blade + ContactoValento) | Acoplar | `/whatsapp/index` | No |
| Carrito | Sidebar + página | `.lv-cart-sidebar` `.sc-*` | Sí | Acoplar | — | No |
| Checkout | Pasos, ubigeo, totales | `.co-section` | Sí (`CheckoutController`, `EnvioService`) | Acoplar | `/pasarela` | No |
| Thank you | Confirmación | `.ty-hero` | Sí (`LiamPageData::thankYou()`) | Acoplar | — | No |
| Legales | Términos / privacidad | `.doc-page` `.doc-body` | Sí (`web_terminos`) | Acoplar | CMS legales | Hecha (contenido) |
| Guía de tallas | Tabla pecho/cintura/largo | `/guia-de-tallas` | Sí (Blade + ruta) | Crear | — | No |
| Nosotros | CTA corporativo | `.ab-cta__frame` | Sí | Acoplar | `/pagina-nosotros` | No |
| Contacto | Form email + privacidad | `#ct-form` `.ct-check-row` | Sí | Acoplar | `/pagina-contacto` | No |
| SKIP | blog, spa, campus, maquinarias, videos, planes, imprenta, rodantes | — | Sí | SKIP (`sistema_menu.Activo='N'`) | — | Hecha |

---

## 2. Checklist de capacidades

| Ítem | Estado | Archivo de referencia |
|---|---|---|
| Variantes talla | YA EXISTE | `administracion_producto.tallas_variantes`, `ProductoEcommerceCatalog` |
| Variantes color | PARCIAL | `colores_disponibles` es paleta visual, sin stock propio |
| Guía de tallas | OK | `pages/liam/guia_tallas.blade.php` + footer/PDP |
| `.sd-wa-card` | OK | Blade + ContactoValento |
| Login tienda | OK | `Auth\LoginController` → `auth.login` |
| Checkout grid 3 cols | OK | `web_checkout.blade.php` + `.co-comprobante-*` CSS |
| Wishlist | YA EXISTE | `web_favoritos`, `temp02/js/liam-wishlist.js` |
| Ratings | YA EXISTE | `product_user_ratings`, `Producto::rating_summary` |
| Filtros de catálogo | YA EXISTE | `ProductoEcommerceCatalog` |
| Envío por ubigeo | YA EXISTE | `EnvioService` |
| Pasarelas | PARCIAL | `PaymentGatewayManager`, `CulqiService` (SANDBOX sin llaves) |
| Emails de pedido | YA EXISTE | `OrderConfirmationMail`, `NotificationVentasMail` |
| Estados de pedido | YA EXISTE | `historial_estado_pedido` |
| Mi cuenta / comprobante | YA EXISTE | `ope_pedidos`, `subir_comprobante_pedido` |
| Libro de reclamaciones | YA EXISTE | `LibroReclamosController` |
| Popup promo | YA EXISTE | `web_popup_promo` |
| Ofertas | YA EXISTE | `OfertaDelDia`, `PromoPackOffer`, `PromoPercentOffer` |

---

## 3. Variantes — opción elegida

**Opción A ampliada ligeramente.** El color es visual y el stock sigue siendo por
talla o por producto, así que no se crea `administracion_producto_variante`.

Lo único que se añadió es un *snapshot* del color elegido:
`ShopDetailController::web_shopDetail_agregar` guarda `color` en las opciones del
ítem del carrito y lo incluye en el id de línea para que dos colores no compartan
fila. El precio, el stock y `ProductoStockService` no se tocaron. Carrito, checkout
y `PedidoProductoRepositorio` (`color_pedido`) ya leían ese dato.

---

## 4. Seeds previstos `seed_valento_{modulo}`

| Migración | Tabla | Contenido | Filas a `Activo='N'` |
|---|---|---|---|
| `rebrand_liam_to_valento` (hecha) | header, footer, whatsapp, contacto, nosotros, lookbook, metadatos | textos Valento | — |
| `valento_canonical_contact_and_skip_menus` (hecha) | ídem + `sistema_menu` | datos canónicos temp02 | menús SKIP |
| `seed_valento_legales_privacidad` (hecha) | `web_terminos` | limpia "Tiendas Él" / Lucdesoft / valentostile y añade bloque de datos de empresa | — |
| `seed_valento_home` (pendiente) | `web_slider`, `web_promo_bloque`, `web_lookbook` | textos e imágenes de `index.html` | filas del cliente anterior |
| `valento_cms_menu` (pendiente) | `sistema_menu` | árbol "Sitio Web — Valento" | menús viejos |

---

## 5. Riesgos de dinero, stock y pedido

- **Precio de línea**: `resolverPrecioLineaCarrito` / `aplicarOfertaPackSobreBase`
  recalculan en servidor en cada add y update. No se modificaron.
- **rowId inestable**: el carrito (Gloudemans) deriva el `rowId` de `id + options`.
  Tocar `options` en caliente regenera la fila y rompe los botones +/−; por eso
  `recalcularPacksEnCarrito` solo actualiza `price` cuando la metadata no cambió.
  El color se fija al agregar y ya no se modifica después.
- **Stock**: baja solo con pago confirmado o pedido aprobado. El color no participa
  del control de stock (el stock es por talla o por producto).
- **Culqi**: la confirmación debe hacerse contra la API, nunca con el redirect.
  Hoy está en SANDBOX y sin credenciales cargadas (ver `PAGOS_VALENTO.md`).

---

## 6. Inventario de marca

- **VISIBLE (a limpiar)**: contenido legal en `web_terminos` ("Tiendas Él",
  Lucdesoft, `rossety.pe`) — resuelto por la migración de legales.
- **SEMILLA YA REBRANDEADA**: `web_header`, `web_footer`, `web_whatsapp_config`,
  `web_pagina_contacto`, `web_pagina_nosotros`, `web_lookbook`, `metadatos_paginas`.
- **CÓDIGO INTERNO (no renombrar)**: `LiamPageData`, `LiamVercceliController`,
  rutas `liam.*`, `resources/views/web/pages/liam`, `partials/liam`, clases
  `.lv-*` y `.vl-*`, `window.__liamCheckoutUbigeo`, nombres de migraciones viejas.
- **Placeholders `51999999999` / `informacion@valento.com`**: centralizados en
  `App\Support\ContactoValento` (fallback `51964381275` y `ventas@valentostile.pe`).
