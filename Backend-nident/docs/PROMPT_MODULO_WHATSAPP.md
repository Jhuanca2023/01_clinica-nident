# Prompt para IA — Módulo WhatsApp Flotante (implementación completa)

> **Estado:** Implementado en Liam Vercceli. Proyecto guía: `41-HQ-business` (HQ-Backend + HQ-Frontend).  
> Usar este documento cuando se solicite traer, replicar o restaurar el módulo WhatsApp flotante en este monorepo u otro proyecto con la misma arquitectura.

---

## Prompt corto (copiar y pegar)

```
Trae e implementa el módulo completo de WhatsApp flotante siguiendo el proyecto guía
C:\xampp\htdocs_pro\41-HQ-business\HQ-Backend y HQ-Frontend.

Debe incluir:
1) Backend Laravel: tabla web_whatsapp_config, modelo, controlador API (obtener/actualizar),
   rutas en auth:api y migración con seed del menú sistema_menu (/whatsapp/index).
2) Web pública: botón flotante dinámico (whatsapp-float.css + whatsapp-float.js),
   inyección window.HQ_WA desde Blade leyendo WebWhatsappConfig, integrado en el layout
   del sitio (footer/base del tema activo).
3) Admin React: WhatsappConfigIndexPage.jsx (CMS con canvas + panel lateral),
   web_whatsapp_config.api.js, ruta /whatsapp/index en routes.jsx, adminRoutesRegistry.js
   y TabsContext.jsx.

Adapta textos por defecto al cliente del proyecto (no copiar textos de HQ).
Ejecuta la migración. No modificar login, JWT ni módulos no relacionados.
Sigue convenciones del repo: { success, result }, CmsPanelRoot, useCmsPanelLayout.
Documentación de referencia: docs/PROMPT_MODULO_WHATSAPP.md
```

---

## Contexto del monorepo

| Repo | Rol |
|------|-----|
| `liamverceli-Backend` | Laravel 10 — API JWT + sitio público Blade |
| `liamverceli-Frontend` | React (Vite, MUI) — admin SPA en `/admin` |

**Proyecto guía (origen del módulo):**

| Repo | Rol |
|------|-----|
| `41-HQ-business/HQ-Backend` | Backend + assets públicos del float |
| `41-HQ-business/HQ-Frontend` | Admin `WhatsappConfigIndexPage.jsx` |

**Importante:** Este módulo **no** usa WhatsApp Business API. Solo genera enlaces `wa.me` y configura el botón flotante. Es independiente del WhatsApp corporativo en Footer/Header (`web_footer.url_whatsapp`, `whatsapp_mensaje`).

---

## Qué hace el módulo

- Tabla singleton `web_whatsapp_config` (id = 1).
- Admin CMS con vista previa interactiva: número, mensaje, burbuja (2 líneas), label, badge, delay, activo S/N.
- Sitio público: JS inyecta el botón flotante leyendo `window.HQ_WA` desde Blade.
- API protegida con `auth:api`.

---

## FASE 0 — Análisis obligatorio (antes de codificar)

1. Confirmar que **no existe** ya `WebWhatsappConfig`, `web_whatsapp_config` ni ruta `/whatsapp/index`.
2. Identificar el **layout Blade activo** del sitio (ej. `resources/views/web/partials/liam/footer.blade.php` incluye `@include('web.partials.liam.whatsapp')`).
3. Verificar carpeta de assets públicos (`public/temp02/css`, `public/temp02/js`).
4. En Frontend, confirmar que existen `CmsPanelRoot`, `useCmsPanelLayout`, `useCmsPanelPush`.
5. Leer en HQ-Backend los archivos fuente listados en la sección **Referencia HQ** más abajo.

---

## FASE 1 — Backend Laravel

### 1.1 Migración

Crear `database/migrations/YYYY_MM_DD_HHMMSS_create_web_whatsapp_config_table.php`:

**Tabla `web_whatsapp_config`:**

| Columna | Tipo | Notas |
|---------|------|-------|
| `wa_numero` | string(30) | Dígitos internacionales sin `+` (ej. `51999999999`) |
| `wa_mensaje` | text | Mensaje prellenado en el chat |
| `wa_burbuja_linea1` | string(200) | Tooltip línea 1 |
| `wa_burbuja_linea2` | string(200) | Tooltip línea 2 |
| `wa_label` | string(50) | Etiqueta del botón |
| `wa_badge` | string(10) | Contador badge (`0` = ocultar) |
| `wa_delay_segundos` | decimal(4,1) | Segundos antes de ocultar badge |
| `Activo` | enum S/N | Mostrar u ocultar botón |

- Insertar **fila inicial** con textos del cliente.
- En la misma migración (o una aparte), **sembrar menú** en `sistema_menu`:
  - `nombre`: `WhatsApp`
  - `url`: `/whatsapp/index`
  - `Icon`: `chat`
  - `Activo`: `S`
  - Asignar a **todos los roles** en `seguridad_roles_menu` (copiar patrón de `imprenta_jh_sistema_menu.php`).

### 1.2 Modelo

`app/Models/WebWhatsappConfig.php`:

```php
protected $table = 'web_whatsapp_config';
protected $guarded = [];
```

### 1.3 Controlador API

`app/Http/Controllers/Api/WebWhatsappConfigController.php`:

| Método | Acción |
|--------|--------|
| `obtener()` | `firstOrCreate(['id' => 1], [...defaults...])` → `{ success: true, result }` |
| `actualizar(Request)` | `update` solo campos permitidos → `{ success: true, result }` |

Campos actualizables: `wa_numero`, `wa_mensaje`, `wa_burbuja_linea1`, `wa_burbuja_linea2`, `wa_label`, `wa_badge`, `wa_delay_segundos`, `Activo`.

### 1.4 Rutas (`routes/api.php`)

Dentro del grupo `middleware => auth:api`:

```
GET  /api/web_whatsapp_config/obtener
POST /api/web_whatsapp_config/actualizar
```

### 1.5 Ejecutar migración

```powershell
cd liamverceli-Backend
php artisan migrate --path=database/migrations/YYYY_MM_DD_HHMMSS_create_web_whatsapp_config_table.php --force
```

---

## FASE 2 — Web pública (Blade + assets)

### 2.1 Assets (copiar/adaptar desde HQ-Backend)

| Origen HQ | Destino Liam |
|-----------|--------------|
| `public/temp02/assets/css/whatsapp-float.css` | `public/temp02/css/whatsapp-float.css` |
| `public/temp02/assets/js/whatsapp-float.js` | `public/temp02/js/whatsapp-float.js` |

Adaptar en el JS los **defaults** del cliente si `window.HQ_WA` no está definido.

### 2.2 Partial Blade

`resources/views/web/partials/{tema}/whatsapp.blade.php`:

```blade
@php $waConfig = \App\Models\WebWhatsappConfig::first(); @endphp
@if(!$waConfig || $waConfig->Activo === 'S')
<script>
window.HQ_WA = {
  numero:   '{{ $waConfig?->wa_numero ?? "51999999999" }}',
  mensaje:  @json($waConfig?->wa_mensaje ?? '...'),
  burbuja1: @json($waConfig?->wa_burbuja_linea1 ?? '...'),
  burbuja2: @json($waConfig?->wa_burbuja_linea2 ?? '...'),
  label:    @json($waConfig?->wa_label ?? 'WhatsApp'),
  badge:    '{{ $waConfig?->wa_badge ?? "1" }}',
  delay:    {{ $waConfig?->wa_delay_segundos ?? 2.5 }},
  activo:   {{ ($waConfig && $waConfig->Activo === 'S') ? 'true' : 'false' }},
};
</script>
<script src="{{ asset('temp02/js/whatsapp-float.js') }}"></script>
@endif
```

### 2.3 Integración en layout

1. En `head.blade.php` del tema: cargar `whatsapp-float.css` (reemplazar `whatsapp.css` legacy si aplica).
2. En `footer.blade.php` del tema: `@include('web.partials.{tema}.whatsapp')`.
3. En `main.js`: **eliminar** lógica del widget antiguo `.lv-wa-wrap` si el HTML estático fue reemplazado por JS.

---

## FASE 3 — Admin React (liamverceli-Frontend)

### 3.1 API client

`src/app/api/web_whatsapp_config.api.js`:

```js
export const obtenerWhatsappConfig = () =>
  from(apiClient.get('/web_whatsapp_config/obtener')).pipe(map((r) => r.data?.result ?? r.data));

export const actualizarWhatsappConfig = (data) =>
  from(apiClient.post('/web_whatsapp_config/actualizar', data)).pipe(map((r) => r.data?.result ?? r.data));
```

### 3.2 Página CMS

`src/app/views/web/whatsapp/WhatsappConfigIndexPage.jsx`

Copiar desde HQ-Frontend y adaptar:
- Placeholders y textos por defecto al cliente.
- Mantener: `BubblePreview`, paneles `contacto` / `burbuja` / `boton`, `CmsPanelRoot`, `useCmsPanelLayout`, `useCmsPanelPush`.

### 3.3 Registro de rutas

| Archivo | Cambio |
|---------|--------|
| `src/app/routes.jsx` | Lazy import + `{ path: "/whatsapp/index", element: <WhatsappConfigIndexPage /> }` |
| `src/app/utils/adminRoutesRegistry.js` | `{ path: "/whatsapp/index", label: "WhatsApp", grupo: "admin" }` |
| `src/app/contexts/TabsContext.jsx` | `"/whatsapp/index": "WhatsApp"` en `ROUTE_TITLES` |

### 3.4 Build admin (producción)

```powershell
cd liamverceli-Frontend
pnpm run build:production
```

Copia `dist/` → `liamverceli-Backend/public/admin/`.

---

## Checklist de archivos

### Backend — crear

- [ ] `database/migrations/*_create_web_whatsapp_config_table.php`
- [ ] `app/Models/WebWhatsappConfig.php`
- [ ] `app/Http/Controllers/Api/WebWhatsappConfigController.php`
- [ ] `public/temp02/css/whatsapp-float.css`
- [ ] `public/temp02/js/whatsapp-float.js`

### Backend — modificar

- [ ] `routes/api.php` (use + 2 rutas)
- [ ] `resources/views/web/partials/{tema}/whatsapp.blade.php`
- [ ] `resources/views/web/partials/{tema}/head.blade.php` (CSS)
- [ ] `public/temp02/js/main.js` (quitar widget legacy si aplica)

### Frontend — crear

- [ ] `src/app/api/web_whatsapp_config.api.js`
- [ ] `src/app/views/web/whatsapp/WhatsappConfigIndexPage.jsx`

### Frontend — modificar

- [ ] `src/app/routes.jsx`
- [ ] `src/app/utils/adminRoutesRegistry.js`
- [ ] `src/app/contexts/TabsContext.jsx`

---

## Referencia HQ (archivos fuente)

```
41-HQ-business/HQ-Backend/
  app/Http/Controllers/Api/WebWhatsappConfigController.php
  app/Models/WebWhatsappConfig.php
  database/migrations/2026_06_15_014100_create_web_whatsapp_config_table.php
  public/temp02/assets/css/whatsapp-float.css
  public/temp02/assets/js/whatsapp-float.js
  resources/views/web/base_hq.blade.php          ← patrón inyección HQ_WA

41-HQ-business/HQ-Frontend/
  src/app/views/web/whatsapp/WhatsappConfigIndexPage.jsx
  src/app/api/web_whatsapp_config.api.js
  src/app/routes.jsx                             ← /whatsapp/index
  src/app/contexts/TabsContext.jsx
```

---

## API — contrato

### `GET /api/web_whatsapp_config/obtener`

```json
{
  "success": true,
  "result": {
    "id": 1,
    "wa_numero": "51999999999",
    "wa_mensaje": "...",
    "wa_burbuja_linea1": "...",
    "wa_burbuja_linea2": "...",
    "wa_label": "WhatsApp",
    "wa_badge": "1",
    "wa_delay_segundos": "2.5",
    "Activo": "S"
  }
}
```

### `POST /api/web_whatsapp_config/actualizar`

Body: cualquier subconjunto de campos editables. Respuesta: mismo shape que `obtener`.

---

## Verificación

1. `php artisan route:list --path=web_whatsapp` → 2 rutas registradas.
2. Tabla `web_whatsapp_config` con 1 fila.
3. `sistema_menu` contiene `/whatsapp/index` asignado a roles.
4. Admin: `/admin/whatsapp/index` carga, guarda y muestra toast de éxito.
5. Web pública: botón flotante visible; hover muestra burbuja; click abre `wa.me` con mensaje.
6. Con `Activo = N`: botón no aparece en el sitio.
7. Tras cambiar menú: `refreshAppSidebarMenu()` o re-login para ver ítem en sidebar.

---

## Restricciones

- **No** modificar login, JWT, usuarios ni permisos core.
- **No** usar stored procedures; solo Eloquent / Query Builder.
- **No** duplicar si ya existe el módulo; extender o sincronizar.
- Panel CMS: usar **CmsPanelRoot** (panel lateral), no modales como patrón principal.
- Respuestas API: `{ success, message?, result }`.
- Adaptar textos por defecto al **cliente del proyecto**, no copiar literales de HQ.

---

## Implementación actual en Liam Vercceli

| Capa | Ubicación |
|------|-----------|
| Migración | `database/migrations/2026_06_22_100000_create_web_whatsapp_config_table.php` |
| API | `WebWhatsappConfigController` |
| Web | `resources/views/web/partials/liam/whatsapp.blade.php` |
| Assets | `public/temp02/css/whatsapp-float.css`, `public/temp02/js/whatsapp-float.js` |
| Admin | `liamverceli-Frontend/src/app/views/web/whatsapp/WhatsappConfigIndexPage.jsx` |
