# Autenticación multi-proveedor (valentostile)

> **Guía consolidada (conversación completa):** ver [`AUTENTICACION_GUIA_COMPLETA.md`](./AUTENTICACION_GUIA_COMPLETA.md) — incluye política por usuario, 2FA, admin principal y pasos a ejecutar.

## Resumen

- **Local (JWT)**: `POST /api/login` — predeterminado.
- **Microsoft / Google**: OAuth 2.0 con **PKCE + state**, callback en API, intercambio seguro con el SPA.
- Tablas con prefijo **`seguridad_`** (mismo criterio que el resto del módulo de seguridad).
- Admin: **Configuración → Proveedores de autenticación** (`/configuracion/auth-proveedor`).

## 1. Migración y seed

```bash
cd impacto-gigante-Backend
php artisan migrate
php artisan db:seed --class=SeguridadAuthProveedorSeeder
```

## 2. Variables `.env` (ejemplo)

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173/admin
FRONTEND_OAUTH_PATH=/admin

# Microsoft Entra ID (Azure Portal → App registrations)
MICROSOFT_CLIENT_ID=
MICROSOFT_CLIENT_SECRET=
MICROSOFT_TENANT_ID=common
MICROSOFT_REDIRECT_URI=http://localhost:8000/api/auth/oauth/callback/microsoft

# Google Cloud Console → OAuth 2.0 Client
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/oauth/callback/google
```

Los valores también pueden guardarse en **BD** desde el panel admin (recomendado en producción; secretos cifrados).

## 3. Registrar aplicaciones OAuth

### Microsoft (prioridad)

1. [Azure Portal](https://portal.azure.com) → **Microsoft Entra ID** → **App registrations** → **New registration**.
2. Redirect URI (Web): `http://localhost:8000/api/auth/oauth/callback/microsoft`
3. **Certificates & secrets** → nuevo client secret.
4. **API permissions**: `openid`, `profile`, `email`, `User.Read`.
5. Copiar **Application (client) ID**, **Tenant ID** (o `common`), **Secret** al panel admin o `.env`.

### Google

1. [Google Cloud Console](https://console.cloud.google.com/) → APIs & Services → Credentials → OAuth client ID (Web).
2. Authorized redirect URI: `http://localhost:8000/api/auth/oauth/callback/google`
3. Copiar Client ID y Secret.

## 4. Habilitar en el panel

1. Iniciar sesión con usuario admin (local).
2. Ir a `/admin/configuracion/auth-proveedor` (ruta React).
3. En la tarjeta del proveedor deseado: pegar credenciales OAuth → **Guardar** → activar el **switch** (o **Marcar predeterminado**).
4. Solo **un método** puede estar activo: al activar Google (por ejemplo), Local y Microsoft se desactivan solos.

## 5. Probar login

1. Cerrar sesión → `/admin/session/signin`.
2. Debe verse **solo el método activo** (una tarjeta: Local, Microsoft o Google).
3. **Local**: email/contraseña como siempre.
4. **Microsoft/Google**: redirección al proveedor → vuelta a `/admin/session/oauth-callback` → sesión JWT (mismo contrato que `/api/login`).
5. Si el usuario tiene varios perfiles, el flujo de selección de perfil se muestra en signin (igual que login local).

## 6. Endpoints API

| Método | Ruta | Auth |
|--------|------|------|
| GET | `/api/auth/metodos-login` | Público |
| GET | `/api/auth/oauth/redirect/{microsoft\|google}` | Público |
| GET | `/api/auth/oauth/callback/{proveedor}` | Público (navegador) |
| POST | `/api/auth/oauth/exchange` | Público (código un solo uso) |
| GET | `/api/auth-proveedor/listar` | JWT |
| POST | `/api/auth-proveedor/habilitar` | JWT |
| POST | `/api/auth-proveedor/predeterminado` | JWT |
| POST | `/api/auth-proveedor/actualizar-config` | JWT |

## 7. Seguridad

- **PKCE** (`S256`) en Microsoft y Google.
- **State** almacenado en `seguridad_auth_oauth_sesion` (15 min).
- **Exchange code** de un solo uso (5 min), sin JWT en query string.
- Secretos en `seguridad_auth_proveedor_config` con `es_secreto=1` → cifrados con `Crypt`.
- Auditoría en `seguridad_auth_login_log`.
- Rate limit en login y OAuth.

## 8. Verificación en dos pasos (2FA TOTP)

Implementado **sin Laravel Fortify**, compatible con JWT + OAuth existente.

### Dependencias

```bash
composer require pragmarx/google2fa bacon/bacon-qr-code
php artisan migrate --path=database/migrations/2026_05_20_140000_create_seguridad_auth_2fa_challenge_table.php
```

### Flujo de login

1. Usuario autentica (local, Microsoft o Google).
2. Si tiene 2FA activo, la API responde `{ requires_2fa: true, challenge_token, expires_in }` **sin JWT**.
3. El SPA guarda el challenge en `sessionStorage` y redirige a `/admin/session/two-factor`.
4. `POST /api/auth/2fa/verify` con `challenge_token` + código TOTP (6 dígitos) o código de recuperación (`XXXX-XXXX`).
5. Respuesta idéntica a `/api/login` (JWT + user + perfil).

### Enrolamiento (usuario autenticado)

1. **Perfil** → tarjeta «Verificación en dos pasos».
2. **Configurar 2FA** → escanear QR (Google Authenticator, Microsoft Authenticator, etc.).
3. Confirmar con un código de 6 dígitos → se muestran **8 códigos de recuperación** (una sola vez).
4. Desactivar o regenerar códigos requiere un código TOTP válido.

### 2FA obligatorio por rol

- Columna `seguridad_roles.requiere_2fa = 1` (ej. rol **QA PRUEBAS**).
- Tras login/OAuth, si el usuario tiene ese rol y aún no tiene 2FA → `requires_2fa_setup` → pantalla `/admin/session/two-factor-setup` (QR obligatorio, sin JWT hasta confirmar).
- No puede desactivar 2FA desde Perfil si su rol lo exige.
- Seeder de prueba: `php artisan db:seed --class=QaRoleAndUserSeeder` → ver `docs/PRUEBA_QA_2FA.md`.

### Endpoints 2FA

| Método | Ruta | Auth |
|--------|------|------|
| POST | `/api/auth/2fa/verify` | Público (challenge) |
| GET | `/api/auth/2fa/status` | JWT |
| POST | `/api/auth/2fa/setup` | JWT |
| POST | `/api/auth/2fa/confirm` | JWT |
| POST | `/api/auth/2fa/disable` | JWT |
| POST | `/api/auth/2fa/recovery/regenerate` | JWT |

### Seguridad 2FA

- Secreto TOTP cifrado (`Crypt`) en `seguridad_usuario_2fa`.
- Códigos de recuperación almacenados como hashes `bcrypt` (un solo uso).
- Challenge de un solo uso, 5 minutos, token hasheado en BD.
- Rate limit en verify, setup y confirm.
- Auditoría en `seguridad_auth_login_log` (proveedor `2fa`).

## 9. Amenazas a tener en cuenta

- **CSRF**: mitigado con `state` en OAuth.
- **Fixation**: no reutilizar `exchange_code`.
- **Token en URL**: solo código efímero; JWT vía POST `/auth/oauth/exchange`.
- Usuarios OAuth nuevos deben tener **perfil en `seguridad_perfil_users`** para acceder al admin.
