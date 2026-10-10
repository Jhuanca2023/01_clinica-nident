# Prueba de login con Microsoft — Paso a paso (valentostile)

## ¿Tengo que pagar?

**No** para desarrollo y pruebas básicas.

- **Microsoft Entra ID** (antes Azure AD): registrar una aplicación en [Azure Portal](https://portal.azure.com) es **gratuito** con cuenta Microsoft personal o corporativa.
- No necesitas suscripción de pago a Azure solo para probar “Iniciar sesión con Microsoft” en local.
- Solo pagarías si más adelante usas funciones premium de Azure o muchos usuarios en producción con licencias específicas (no aplica a tu primera prueba).

---

## Resumen rápido (5 pasos)

| Paso | Qué haces |
|------|-----------|
| 1 | Tener backend en `http://localhost:8000` y frontend en `http://localhost:5173/admin` |
| 2 | Crear app en Azure Portal y copiar Client ID + Secret |
| 3 | Pegar credenciales en el panel **Proveedores de autenticación** |
| 4 | **Habilitar login** en la tarjeta Microsoft |
| 5 | Ir a **Iniciar sesión** y pulsar la tarjeta **Microsoft** |

---

## Paso 0 — Servicios en marcha (en tu PC)

### Terminal 1 — Backend Laravel

```powershell
cd C:\xampp\htdocs_pro\33-impacto-gigante\impacto-gigante-Backend
php artisan serve
```

Debe quedar en: **http://localhost:8000**

Comprueba en el navegador:

`http://localhost:8000/api/auth/metodos-login`

Debe responder JSON con `local`, `microsoft`, `google`.

### Terminal 2 — Frontend React

```powershell
cd C:\xampp\htdocs_pro\33-impacto-gigante\impacto-gigante-Frontend
npm run dev
```

Abre: **http://localhost:5173/admin/session/signin**

### Sincronizar URLs en base de datos

```powershell
cd C:\xampp\htdocs_pro\33-impacto-gigante\impacto-gigante-Backend
php artisan auth:setup-sync
```

Esto alinea `REDIRECT_URI` con `APP_URL=http://localhost:8000`.

---

## Paso 1 — Crear la aplicación en Microsoft (Azure)

1. Entra a **https://portal.azure.com** (inicia sesión con tu cuenta Microsoft o trabajo).

2. Busca en el buscador superior: **Microsoft Entra ID** (o “Azure Active Directory”).

3. Menú izquierdo → **App registrations** (Registros de aplicaciones).

4. Clic en **+ New registration** (Nuevo registro).

5. Completa:
   - **Name:** `Prestomart Admin Local` (o el nombre que quieras)
   - **Supported account types:** elige según tu caso:
     - **“Accounts in any organizational directory and personal Microsoft accounts”** → más flexible para pruebas (`tenant` = `common`)
     - O solo tu organización si es cuenta de empresa
   - **Redirect URI:**
     - Tipo: **Web**
     - URL: **`http://localhost:8000/api/auth/oauth/callback/microsoft`**
     - ⚠️ Debe ser **exactamente** esa URL (mismo puerto, sin `/admin`, sin barra final extra)

6. Clic **Register**.

7. En la página **Overview** copia:
   - **Application (client) ID** → es tu `CLIENT_ID`

8. Menú **Certificates & secrets**:
   - **+ New client secret**
   - Descripción: `local dev`
   - Expiración: 6 o 24 meses
   - **Copia el Value del secret inmediatamente** (solo se muestra una vez) → es tu `CLIENT_SECRET`

9. Menú **API permissions**:
   - **Add a permission** → **Microsoft Graph** → **Delegated permissions**
   - Marca: `openid`, `profile`, `email`, `User.Read`
   - **Add permissions**
   - Si tu tenant lo exige: **Grant admin consent** (en dev a veces no es obligatorio con cuenta personal)

10. (Opcional) **Directory (tenant) ID** en Overview:
    - Si usas cuentas solo de tu empresa, puedes poner ese ID en `TENANT_ID`
    - Para cuentas personales + varias organizaciones, deja **`common`** en el panel

---

## Paso 2 — Configurar credenciales en tu proyecto

### Opción A — Panel admin (recomendado)

1. Inicia sesión con **email y contraseña** (método local).

2. Ve a:

   **http://localhost:5173/admin/configuracion/auth-proveedor**

3. Tarjeta **Microsoft (Entra ID)**:
   - Clic **Configurar credenciales**
   - **CLIENT_ID:** pega el Application (client) ID
   - **CLIENT_SECRET:** pega el secret
   - **TENANT_ID:** `common` (o tu Tenant ID)
   - **REDIRECT_URI:** debe ser  
     `http://localhost:8000/api/auth/oauth/callback/microsoft`  
     (si está vacío o distinto, corrige y **Guardar**)
   - Clic **Guardar**

4. Clic **Habilitar login** en la tarjeta Microsoft.

5. Deja **Local** como **predeterminado** si quieres que el formulario email siga siendo el principal (recomendado).

### Opción B — Archivo `.env` (alternativa)

En `impacto-gigante-Backend/.env`:

```env
MICROSOFT_CLIENT_ID=tu-application-client-id
MICROSOFT_CLIENT_SECRET=tu-client-secret
MICROSOFT_TENANT_ID=common
```

Luego en el panel admin igual debes **Guardar** en BD o usar solo BD (el código lee primero la configuración en **base de datos** del panel).

---

## Paso 3 — Probar el login

1. Cierra sesión o ventana incógnito.

2. Abre: **http://localhost:5173/admin/session/signin**

3. Debes ver **3 tarjetas** (si Microsoft está habilitado):
   - Autenticación local
   - Microsoft
   - Google (si no está habilitado, no aparece)

4. Clic en **Microsoft**:
   - Te lleva a `login.microsoftonline.com`
   - Inicia sesión con tu cuenta Microsoft
   - Acepta permisos si lo pide

5. Vuelta automática a:
   - `http://localhost:5173/admin/session/oauth-callback?code=...`
   - Luego el sistema completa el JWT y te deja en el admin (o selección de perfil si tienes varios perfiles).

---

## Errores frecuentes y solución

| Error | Causa | Solución |
|-------|--------|----------|
| `redirect_uri_mismatch` | URI en Azure ≠ URI en BD | Azure y panel deben tener `http://localhost:8000/api/auth/oauth/callback/microsoft` |
| `El proveedor microsoft no está habilitado` | Falta habilitar en panel | auth-proveedor → **Habilitar login** |
| `AADSTS7000215` secret inválido | Secret mal copiado o expirado | Crear nuevo secret en Azure y guardar de nuevo |
| Pantalla en blanco tras login | Frontend no corre o URL mal | `npm run dev` y `FRONTEND_URL=http://localhost:5173/admin` en `.env` |
| Usuario sin acceso al menú | Sin perfil en `seguridad_perfil_users` | Asignar perfil al usuario en módulo Usuarios/Perfiles |
| CORS / Network Error | API no en 8000 | `php artisan serve` y `VITE_AUTHJWT_DOMAIN=http://localhost:8000` |

---

## Comandos útiles

```powershell
# Ver estado y URLs
php artisan auth:setup-sync --check

# Actualizar REDIRECT_URI en BD según APP_URL
php artisan auth:setup-sync

# Re-sembrar proveedores (si borraste tablas)
php artisan db:seed --class=SeguridadAuthProveedorSeeder
```

---

## Qué revisar si algo falla

1. `php artisan auth:setup-sync` — CLIENT_ID debe decir “configurado” tras guardar en panel.
2. Navegador → F12 → Network → al pulsar Microsoft debe llamar a  
   `GET http://localhost:8000/api/auth/oauth/redirect/microsoft`  
   y responder `authorization_url`.
3. Tabla `seguridad_auth_login_log` — filas con `proveedor=microsoft` y `exito=S` o mensaje de error.

---

## Siguiente: Google

Mismo flujo en [Google Cloud Console](https://console.cloud.google.com/) con redirect  
`http://localhost:8000/api/auth/oauth/callback/google`  
y habilitar tarjeta Google en el panel.
