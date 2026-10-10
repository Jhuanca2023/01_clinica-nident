# Fix: Menú de navegación cortado (Sticky Header)

## Síntoma
Al cargar la página de inicio, la barra de navegación (`.header-bottom`) desaparecía detrás del borde superior del viewport — el menú estaba "cortado". Quitando la propiedad `top` en DevTools se restauraba su aspecto normal.

---

## Causa raíz

El header usa la clase `header-intro-clearance` con `position: absolute` sobre el intro-slider. Esto hace que `anchor.offset().top ≈ 0`.

**Cadena de fallo:**

1. El plugin **`Waypoint.Sticky`** (en `public/temp/assets/js/main.js`) se inicializa con:
   ```js
   offset: -300, stuckClass: 'fixed'
   ```
2. Como `anchor.offset().top = 0`, el `triggerPoint` resultaba `0`.
3. La condición `scrollTop(0) >= triggerPoint(0)` era `true` al cargar la página → se aplicaba `.fixed` inmediatamente.
4. El CSS del tema (`public/temp/assets/css/style.css` ~línea 2103) define:
   ```css
   .sticky-header.fixed {
     position: fixed;
     top: 0;
     z-index: 1040;
     animation-name: fixedHeader;
   }
   ```
5. Ese `top: 0` subía la barra al tope del viewport al cargar, cortando el menú sobre el header-top y header-middle.

**Factor adicional:** El `overflow-x: hidden !important` en `html, body` (en `customise_01.scss`) puede convertir a `body` en el scroll container, evitando que `window.scroll` se dispare correctamente en los Waypoints.

---

## Solución aplicada

**Archivo:** `resources/js/customise_01.js`

### Decisión de diseño
No se modificó el CSS del tema (`style.css`) ni `customise_01.scss`, para preservar la animación de entrada del sticky (`@keyframes fixedHeader`: `translateY(-60px) → translateY(0)`). La corrección fue 100% en JavaScript.

### Cambios clave

**1. Calcular `triggerPoint` desde alturas reales del header cuando el header es overlay:**
```js
function recalculate() {
    var rawTop = anchor.offset().top;

    if (rawTop < 20) {
        // Header overlay: offset().top ≈ 0, usar alturas reales de secciones superiores
        triggerPoint = ($('.header-top').outerHeight(true) || 0)
                     + ($('.header-middle').outerHeight(true) || 0);
    } else {
        triggerPoint = rawTop;
    }

    if (!triggerPoint) {
        triggerPoint = $('.header').outerHeight(true) || 80;
    }
}
```

**2. Corregir estado inicial prematuro del Waypoint:**
```js
recalculate();
handleScroll(); // elimina cualquier .fixed aplicado antes de tiempo
```

**3. Getter universal de scroll (cubre body como scroll container):**
```js
function getScrollTop() {
    return window.pageYOffset
        || document.documentElement.scrollTop
        || document.body.scrollTop
        || 0;
}
```

**4. Escuchar scroll en `window` y en `body`:**
```js
$(window).on('scroll.stickyFix', handleScroll);
$('body').on('scroll.stickyFix', handleScroll);
```

---

## Archivos involucrados

| Archivo | Rol |
|---|---|
| `resources/js/customise_01.js` | **Fix aplicado aquí** |
| `public/temp/assets/js/main.js` | Plugin Waypoint.Sticky (no modificar) |
| `public/temp/assets/css/style.css` | CSS del tema con `.sticky-header.fixed { top: 0 }` (no modificar) |
| `resources/sass/customise_01.scss` | Tiene `overflow-x: hidden` en html/body (factor contribuyente) |

---

## Comportamiento esperado tras el fix

- **Al cargar la página:** menú visible en su posición normal (no sticky todavía).
- **Al hacer scroll** más allá del header-top + header-middle: la barra se vuelve sticky con la animación de deslizamiento.
- **Al regresar al inicio:** la barra vuelve a su posición original.
- **En mobile (< 992px):** sin sticky, comportamiento normal.
