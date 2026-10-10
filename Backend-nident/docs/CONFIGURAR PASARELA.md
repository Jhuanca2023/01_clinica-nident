# CONFIGURAR PASARELA

Guía y **prompts listos para copiar** en otros proyectos réplica (Laravel + Culqi/checkout + cPanel + correos).

Basado en correcciones reales (Liam Vercceli / proyectos similares).

---

## Índice de problemas grandes

| # | Problema | Síntoma |
|---|----------|---------|
| 1 | Cobro pasa aunque hay error | Culqi cobra, pantalla muestra error, pedido no queda o queda inconsistente |
| 2 | Correo llega a ventas (cPanel) pero no a Gmail | `ventas@dominio.com` sí, `usuario@gmail.com` no |
| 3 | Sigue usando Mailtrap aunque cambiaste el `.env` | Log: `Too many emails per second` / `mailtrap.io` |
| 4 | Número de reclamo distinto en mail vs admin | Mail `REC-8`, panel `REC-7` |
| 5 | Plantillas de correo con imágenes rotas / marca incorrecta | Footer rojo, Lucdesoft, logos rotos |
| 6 | WhatsApp con caracteres raros | `&#9472;`, `&iexcl;`, `&#128176;` en el mensaje |
| 7 | Culqi: confusión de claves RSA vs API | Ventana no abre o error de desencriptar |
| 8 | WOW.js en móvil deja secciones en blanco | Contenido invisible en celular |

---

## Prompt maestro (copiar completo)

Usa este prompt en el agente cuando abras **otro proyecto réplica**:

```
Eres un ingeniero Laravel en un e-commerce réplica (checkout + Culqi + correos SMTP en cPanel).

Aplica TODAS estas correcciones de forma mínima y enfocada (solo lo necesario):

### A) Pago online: NUNCA cobrar si hay error previo; el mail NUNCA tumba el pedido

En el flujo de checkout (típicamente CheckoutController::procesarPedido / enviar_pago_con_culqi):

1. Validar ANTES de cobrar: carrito, stock, formulario (nombre, email, dirección, teléfono, DNI, distrito, términos), token y amount.
2. Cobrar en la pasarela (Culqi/Stripe/PayPal/Izipay) SOLO si las validaciones pasaron.
3. Si el cobro falla → no crear pedido, mensaje claro, success:false.
4. Si el cobro OK → guardar pedido en BD (transacción).
5. Enviar correos DESPUÉS del commit, cada uno en try/catch independiente.
6. Si el correo falla → loguear error, PERO devolver success:true y redirigir a thank-you (el pedido ya existe).
7. Nunca hacer DB::rollback() por fallo de Mail después de haber cobrado en Culqi.

Orden correcto:
  validar → cobrar → persistir pedido → commit → emails (no bloqueantes) → vaciar carrito → success

Orden incorrecto (prohibido):
  cobrar → pedido → emails dentro del try → rollback si mail falla (deja cargo en Culqi sin pedido)

### B) Correo al cliente (Gmail) no llega, pero ventas@ del dominio sí

Causa típica en cPanel:
- Mailable usa from('no-reply@dominio.com') pero SMTP autentica con ventas@dominio.com
- El servidor entrega bien a buzones locales (ventas@) y Gmail rechaza o no entrega el externo.

Corregir:
1. OrderConfirmationMail y NotificationVentasMail (o equivalentes) deben usar:
   from(config('mail.from.address'), config('mail.from.name'))
   replyTo(config('mail.from.address'), config('mail.from.name'))
2. En .env de PRODUCCIÓN:
   MAIL_MAILER=smtp
   MAIL_HOST=mail.tudominio.com
   MAIL_PORT=465
   MAIL_ENCRYPTION=ssl
   MAIL_USERNAME=ventas@tudominio.com
   MAIL_PASSWORD=***
   MAIL_FROM_ADDRESS=ventas@tudominio.com
   MAIL_FROM_NAME="Nombre Marca"
3. MAIL_FROM_ADDRESS y MAIL_USERNAME deben ser LA MISMA cuenta SMTP.
4. Puerto 465 → encryption ssl. Puerto 587 → encryption tls. No mezclar.
5. El correo del cliente sale de form_data.txt_email (o campo equivalente del checkout), no solo del token de Culqi (a veces viene cliente@ejemplo.com en test).
6. Enviar cliente y ventas por separado, con pausa ~0.7s entre envíos, logs de éxito/error por destinatario.

### C) Config cache: el .env cambió pero sigue Mailtrap

Si el log muestra mailtrap.io aunque el .env tenga mail.tudominio.com:
1. Borrar/limpiar bootstrap/cache/config.php
2. Ejecutar: php artisan config:clear && php artisan cache:clear
   o la URL de mantenimiento del proyecto (ej. /artisan-clear.php?key=...)
3. Verificar en log el mail_host al enviar.

### D) Libro de reclamaciones: número distinto en email vs admin

El número correcto es el id real en BD (panel admin).
No usar insertGetId() sin el nombre de la PK si la columna es id_web_reclamos (no "id").
Flujo:
1. insertGetId($payload, 'id_web_reclamos')
2. Releer el id desde la tabla
3. Usar ese mismo REC-{id} en asunto, cuerpo del mail y mensaje de éxito en web.

### E) Plantillas email de reclamos

- Quitar imágenes rotas (header, hero, footer con iconos).
- Quitar footer rojo con enlaces/iconos.
- Marca solo texto: nombre del proyecto (ej. "Liam Vercceli").
- Sin referencias a Lucdesoft u otra marca ajena.

### F) WhatsApp "Comprar por WhatsApp" con entidades HTML

No poner emojis/caracteres especiales literales dentro de Blade en <script> (Blade/HTML los convierte a &#...; y WhatsApp los muestra crudos).
Usar un JS compartido con escapes Unicode (\u2500, \uD83D\uDCB0, \u00A1) y decodeEntities antes de encodeURIComponent.
Incluir código de pedido + productos + total en texto plano.

### G) Culqi claves en .env

CULQI_PUBLIC_KEY=pk_test_... o pk_live_...
CULQI_SECRET_KEY=sk_test_... o sk_live_...
CULQI_RSA_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----"
CULQI_RSA_ID=uuid-de-la-llave-rsa

La RSA NO reemplaza pk_/sk_. Deben ser del mismo entorno (test con integ-panel, live con mipanel).
Si el checkout usa script v4 sin RSA, lo crítico para abrir la ventana es CULQI_PUBLIC_KEY.
Prioridad de credenciales: BD (admin pasarela) → config → .env. Revisar que la BD no tenga claves vacías que pisen el .env.

### H) WOW.js en móvil deja todo en blanco

WOW pone visibility:hidden y en móvil a veces no dispara el scroll.
Inicializar WOW con mobile:true, offset bajo, y un fallback con IntersectionObserver que revele .wow al entrar en viewport.
CSS de seguridad: .wow.animated / .wow--revealed { visibility: visible !important; }

Al terminar, lista archivos tocados y pasos de deploy (subir archivos + config:clear en servidor).
```

---

## Prompts cortos (uno por problema)

### 1) Error no debe dejar pasar la transacción / cobro huérfano

```
En CheckoutController (o equivalente), el flujo actual cobra en Culqi y luego si falla el correo hace rollback del pedido, dejando el cargo en Culqi.

Reordena así:
1) Validar formulario, carrito y stock ANTES de cobrar.
2) Cobrar en Culqi solo si todo es válido.
3) Guardar pedido y commit.
4) Enviar correos fuera de la transacción, en try/catch: si el mail falla, loguear pero devolver success:true.

Nunca hacer rollback del pedido por error de Mail después de un charge exitoso.
Si el cobro OK pero falla al guardar el pedido, log critical con transaccion_id y mensaje al usuario con esa referencia.
```

### 2) No llega a Gmail, sí llega a ventas@ en cPanel

```
El correo de confirmación de pedido llega a ventas@dominio.com pero no a Gmail del cliente.

Causa: el Mailable usa from('no-reply@dominio.com') mientras el SMTP autentica con ventas@dominio.com. cPanel entrega local y Gmail rechaza.

Corrige OrderConfirmationMail y NotificationVentasMail para usar config('mail.from.address') y replyTo igual.
Asegura MAIL_FROM_ADDRESS = MAIL_USERNAME = ventas@dominio.com en .env.
Puerto 465 con MAIL_ENCRYPTION=ssl.
Envía al email del formulario (txt_email), no solo al email del token Culqi.
Loguea éxito/error por destinatario (cliente vs ventas).
```

### 3) Mailtrap en producción aunque el .env ya es el SMTP real

```
El log muestra error de Mailtrap (Too many emails per second) aunque el .env de producción tiene mail.tudominio.com.

Laravel está usando bootstrap/cache/config.php cacheado.
Limpia config: php artisan config:clear (o artisan-clear.php del proyecto).
Confirma que no quede host mailtrap en la caché.
Además, el fallo de mail no debe abortar el pedido (ver prompt 1).
```

### 4) REC-N distinto en email y panel admin

```
Al crear un reclamo, el email muestra REC-(N+1) y el admin REC-N.

El número correcto es id_web_reclamos en BD.
Usa insertGetId($data, 'id_web_reclamos'), relee el id de la fila insertada, y usa ese mismo valor en asunto, cuerpo y session de éxito.
No uses max(id)+1 ni insertGetId sin el nombre de la PK.
```

### 5) Emails de reclamo: imágenes rotas y footer rojo

```
En las plantillas email de reclamos (confirmacion_reclamo, nuevo_reclamo, actualizacion_estado_reclamo y partials/styles):
- Elimina todas las imágenes (header, hero, iconos del footer).
- Elimina el footer rojo con enlaces e iconos sociales rotos.
- Header y footer solo con texto de la marca del proyecto.
- Reemplaza cualquier mención a Lucdesoft por el nombre correcto de la marca.
```

### 6) WhatsApp con caracteres raros

```
El botón "Comprar por WhatsApp" abre un mensaje con &#9472;, &#128176;, &iexcl; en lugar de líneas, emojis y ¡.

Blade está HTML-encodeando caracteres dentro del <script>.
Mueve el armado del mensaje a un JS externo (ej. liam-whatsapp.js) usando escapes Unicode y decodeEntities antes de encodeURIComponent.
Incluye código de pedido, ítems y total en texto legible.
Aplícalo en carrito y ficha de producto.
```

### 7) Culqi no abre / claves

```
Revisa .env:
CULQI_PUBLIC_KEY (pk_test_ o pk_live_)
CULQI_SECRET_KEY (sk_test_ o sk_live_)
CULQI_RSA_PUBLIC_KEY (PEM en una línea con \n)
CULQI_RSA_ID (uuid)

La RSA no sustituye pk_/sk_. Mismo entorno test/live.
Si el checkout usa Culqi v4 sin RSA, lo crítico es publicKey.
Revisa si PaymentGatewayManager lee claves vacías desde BD y pisa el .env.
Tras cambiar .env: config:clear.
```

### 8) WOW.js móvil en blanco

```
En móvil, secciones con clase wow quedan en blanco (visibility:hidden).
WOW no dispara bien el scroll en touch.

Crea un inicializador (ej. liam-wow.js) con WOW mobile:true, offset bajo, IntersectionObserver de respaldo y CSS:
.wow.animated, .wow--revealed { visibility: visible !important; }
Inicializa después del preloader.
```

---

## Checklist de deploy en cPanel (cada réplica)

1. Subir archivos PHP/JS/Blade modificados (no solo el `.env`).
2. Ajustar `.env` de **ese** dominio (mail + Culqi).
3. Limpiar caché:
   - `https://DOMINIO/artisan-clear.php?key=...`  
   - o `php artisan config:clear && php artisan cache:clear`
4. Probar compra de prueba (Culqi test):
   - [ ] Thank-you sin error aunque el mail falle
   - [ ] Pedido visible en admin
   - [ ] Correo en `ventas@dominio`
   - [ ] Correo en Gmail del cliente (bandeja + spam)
5. Revisar `storage/logs/laravel.log`:
   - `Correo de pedido enviado` con `destinatario: cliente`
   - `mail_host` = `mail.tudominio.com` (no mailtrap)

---

## `.env` mínimo pasarela + correo (plantilla)

```env
# Correo (cPanel) — FROM = USERNAME
MAIL_MAILER=smtp
MAIL_HOST=mail.TUDOMINIO.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=ventas@TUDOMINIO.com
MAIL_PASSWORD=********
MAIL_FROM_ADDRESS=ventas@TUDOMINIO.com
MAIL_FROM_NAME="Nombre Marca"
MAIL_CONTACT_TO=ventas@TUDOMINIO.com

# Culqi (test)
CULQI_PUBLIC_KEY=pk_test_xxxxxxxx
CULQI_SECRET_KEY=sk_test_xxxxxxxx
CULQI_RSA_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----"
CULQI_RSA_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
CULQI_WEBHOOK_SECRET=
```

---

## Archivos típicos a tocar (Laravel réplica)

| Área | Archivos habituales |
|------|---------------------|
| Checkout / cobro | `app/Http/Controllers/Web/CheckoutController.php` |
| Mail pedido | `app/Mail/OrderConfirmationMail.php`, `NotificationVentasMail.php` |
| Vista email pedido | `resources/views/email/order_confirmation.blade.php` |
| Reclamos | `app/Http/Controllers/Web/LibroReclamosController.php` |
| Emails reclamo | `resources/views/email/confirmacion_reclamo.blade.php`, `nuevo_reclamo.blade.php`, `actualizacion_estado_reclamo.blade.php`, `partials/styles.blade.php` |
| WhatsApp | `public/temp02/js/liam-whatsapp.js`, blades de carrito/detalle |
| WOW móvil | `public/temp02/js/liam-wow.js`, `public/temp02/css/wow-fix.css`, `partials/.../scripts.blade.php` |
| Config mail | `config/mail.php`, `.env`, `bootstrap/cache/config.php` (borrar caché) |
| Zona horaria MySQL | `config/database.php`, `app/Providers/AppServiceProvider.php`, `.env` (`DB_TIMEZONE=-05:00`) |

---

## Fecha con un día de más (MySQL en UTC)

**Síntoma:** Laravel/PHP muestran la fecha correcta (Lima), pero pedidos, `created_at` o stored procedures guardan el día siguiente después de las ~19:00.

**Diagnóstico rápido** (ruta temporal en `routes/web.php`):

```php
Route::get('/test-time', function () {
    return [
        'config_timezone' => config('app.timezone'),
        'php_timezone' => date_default_timezone_get(),
        'laravel_now' => now()->toDateTimeString(),
        'mysql_now' => DB::select('SELECT NOW() AS ahora')[0]->ahora,
    ];
});
```

Si `laravel_now` es correcto y `mysql_now` va ~5 h adelante → MySQL está en UTC. **No hace falta cambiar el servidor entero.**

**Solución en la app:**

1. En `.env` de producción:
   ```env
   APP_TIMEZONE=America/Lima
   DB_TIMEZONE=-05:00
   ```
2. Subir `config/database.php` y `AppServiceProvider.php` (fijan `SET time_zone` en cada conexión).
3. `php artisan config:clear` (o `artisan-clear.php`).
4. Volver a abrir `/test-time`: `mysql_now` debe coincidir con `laravel_now` (misma fecha/hora Lima).
5. Eliminar la ruta `/test-time` cuando termine la prueba.

**Evitar en código nuevo:** `NOW()`, `CURRENT_TIMESTAMP`, `CURDATE()` en SQL crudo. Usar `now()` de Laravel.

---

## Reglas de oro (no negociables)

1. **Cobro solo después de validar.**
2. **Mail nunca deshace un pedido ya pagado.**
3. **From del correo = cuenta SMTP autenticada.**
4. **Tras cambiar `.env` en producción → siempre `config:clear`.**
5. **El número de reclamo/pedido del mail = el de la BD.**
6. **En hostings UTC, definir `DB_TIMEZONE=-05:00` para que MySQL coincida con Lima.**

---

*Documento generado para replicar correcciones entre proyectos del mismo stack. Ajustar nombres de marca, dominios y rutas según cada réplica.*
