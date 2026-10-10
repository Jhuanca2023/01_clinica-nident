# Base de datos — Landing valentostile

Registro de cambios en BD relacionados con la integración del landing (hero slider + footer corporativo).

**Fecha de referencia:** 2026-05-19  
**Migraciones:** `2026_05_19_000001_*`, `2026_05_19_000002_*`, `2026_05_19_000003_*`

---

## Tablas creadas

| Tabla | Estado |
|-------|--------|
| — | **Ninguna.** No se crearon tablas nuevas. |

---

## Tablas afectadas (columnas agregadas)

### `web_slider`

**Migración:** `database/migrations/2026_05_19_000001_add_hero_fields_to_web_slider_table.php`

| Columna | Tipo | Nullable | Uso |
|---------|------|----------|-----|
| `subtitulo` | `varchar(255)` | Sí | Etiqueta superior del slide (hero) |
| `texto_boton` | `varchar(120)` | Sí | Texto del botón CTA del slide |

**Datos iniciales:** `UPDATE` en registros `id_slider` = 1, 2, 3.

**Consultas en código:**
- `App\Http\Controllers\Web\HomeController::Home()`
- `App\Http\Controllers\Api\WebSliderController`
- `App\Models\WebSlider`
- `App\Helpers\Helper::slider_()` (slide `id_slider` = 4, banner interno)

---

### `web_footer`

**Migración:** `database/migrations/2026_05_19_000002_add_landing_footer_fields_to_web_footer_table.php`

| Columna | Tipo | Nullable | Uso |
|---------|------|----------|-----|
| `contacto_telefono_secundario` | `varchar(50)` | Sí | Call Center (segundo teléfono) |
| `url_whatsapp` | `varchar(500)` | Sí | Enlace WhatsApp (CTA y contacto) |
| `footer_cta_subtitulo` | `varchar(255)` | Sí | Subtítulo del bloque CTA del footer |
| `footer_cta_titulo` | `varchar(500)` | Sí | Título del bloque CTA del footer |

**Migración:** `database/migrations/2026_05_19_000003_add_red_social_tiktok_to_web_footer_table.php`

| Columna | Tipo | Nullable | Uso |
|---------|------|----------|-----|
| `red_social_tiktok` | `varchar(500)` | Sí | Enlace perfil TikTok |

**Datos iniciales:** `UPDATE` en registro `id_footer` = 1.

**Consultas en código:**
- `App\Http\Controllers\Web\HomeController::Home()`
- `App\Http\Controllers\Api\WebFooterController`
- `App\Models\WebFooter`
- `App\Helpers\Helper::footer_()`

---

## Stored procedures (SP)

| SP | Estado |
|----|--------|
| — | **Ninguno creado, modificado ni invocado** para slider/footer del landing. |

El acceso a datos del landing usa:
- Eloquent (`WebSlider`, `WebFooter`)
- Query Builder / SQL directo (`DB::table`, `DB::select`)

---

## Resumen

| Concepto | Cantidad |
|----------|----------|
| Tablas nuevas | 0 |
| Tablas alteradas | 2 (`web_slider`, `web_footer`) |
| Columnas nuevas | 7 (2 + 4 + 1) |
| SP afectados | 0 |
