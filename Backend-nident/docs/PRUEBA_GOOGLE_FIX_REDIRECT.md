# Corregir Error 400: redirect_uri_mismatch (Google)

## Qué significa el error

Google dice que la app envía esta URI:

```
http://localhost:8000/api/auth/oauth/callback/google
```

Esa URI **debe estar registrada** en el mismo cliente OAuth de Google Cloud donde copiaste el Client ID.

Si en Console solo tienes `http://localhost:8000/auth/google/callback` → **error 400** (ruta antigua, incorrecta).

---

## Solución (5 pasos)

1. Abre: https://console.cloud.google.com/apis/credentials  
2. Proyecto **kristec** (o el que creó el cliente).  
3. Clic en tu **ID de cliente OAuth 2.0** (tipo **Aplicación web**).  
   - Debe empezar con: `738626213234-...` (el mismo que en `.env` → `GOOGLE_CLIENT_ID`).  
4. En **URIs de redireccionamiento autorizados**:
   - **Agregar:** `http://localhost:8000/api/auth/oauth/callback/google`
   - **Eliminar** (si existe): `http://localhost:8000/auth/google/callback`
5. **Guardar**. Espera 1–2 minutos. Prueba en ventana de incógnito.

---

## Sincronizar backend (terminal)

```powershell
cd C:\xampp\htdocs_pro\33-impacto-gigante\impacto-gigante-Backend
php artisan config:clear
php artisan auth:setup-sync
```

Reinicia `php artisan serve` si estaba corriendo.

---

## Verificar

- Panel admin → Proveedores de autenticación → Google → REDIRECT URI =  
  `http://localhost:8000/api/auth/oauth/callback/google`
- Login: http://localhost:5173/admin/session/signin

---

## Nota sobre Gmail

`ghiovani666@gmail.com` no necesita estar registrado antes; Google puede crear el usuario.  
Sí necesita **perfil asignado** en Seguridad → Usuarios para entrar al panel.
