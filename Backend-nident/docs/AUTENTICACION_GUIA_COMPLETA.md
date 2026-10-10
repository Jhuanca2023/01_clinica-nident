# Guía completa de autenticación — valentostile

Documento consolidado de la conversación de implementación: multi-proveedor (Local / Google / Microsoft), 2FA TOTP, administrador principal, rol QA y **política de acceso por usuario**.

---

## 1. Resumen ejecutivo

| Capa | Qué controla |
|------|----------------|
| **Sistema** (`seguridad_auth_proveedor`) | Qué proveedores existen y cuál es predeterminado global (panel *Configuración → Proveedores de autenticación*). |
| **Por usuario** (`seguridad_usuario_auth_config`) | Qué métodos puede usar **cada usuario** y si debe tener 2FA. |
| **Por rol** (`seguridad_roles.requiere_2fa`) | 2FA obligatorio para todos los usuarios con ese rol (ej. **QA PRUEBAS**). |
| **Perfil del usuario** | Puede activar 2FA voluntario si el admin lo permite. |

La regla efectiva de 2FA es: **obligatorio** si `requiere_2fa` del usuario **o** algún rol del usuario tiene `requiere_2fa = 1`.

---

## 2. Métodos de inicio de sesión

### 2.1 Local (JWT)

- Endpoint: `POST /api/login`
- Correo + contraseña → JWT en cookie/header según configuración del SPA.

### 2.2 Google OAuth 2.0

- Redirect: `GET /api/auth/oauth/redirect/google`
- Callback API: `GET /api/auth/oauth/callback/google`
- En Google Cloud Console la URI autorizada debe ser **exactamente**:
  `http://localhost:8000/api/auth/oauth/callback/google` (desarrollo)
- Variable `.env`: `GOOGLE_REDIRECT_URI` con la misma URL.

### 2.3 Microsoft OAuth 2.0 (Entra ID)

- Callback: `http://localhost:8000/api/auth/oauth/callback/microsoft`
- Ver también: `docs/PRUEBA_MICROSOFT_PASO_A_PASO.md`

### 2.4 Un solo proveedor activo a nivel sistema

En el panel global solo un método puede estar “activo” como pasarela principal; la política **por usuario** filtra además qué ve cada persona en el login.

---

## 3. Configuración por usuario (administrador)

### 3.1 Tabla `seguridad_usuario_auth_config`

| Campo | Descripción |
|-------|-------------|
| `permitir_local` | Login con correo y contraseña |
| `permitir_google` | Login con cuenta Google |
| `permitir_microsoft` | Login con Microsoft |
| `requiere_2fa` | Obliga configurar QR + TOTP en el primer acceso; no puede desactivar desde perfil |
| `permite_2fa_voluntario` | El usuario puede activar 2FA en **Mi perfil** |
| `metodo_predeterminado` | `local`, `google` o `microsoft` — preselección en login |

### 3.2 API (requiere JWT de administrador)

| Método | Ruta | Uso |
|--------|------|-----|
| POST | `/api/usuario/auth-config/obtener` | `{ "id_usuario": 1 }` |
| POST | `/api/usuario/auth-config/guardar` | Body con flags booleanos |
| POST | `/api/auth/metodos-por-email` | **Público** — `{ "email": "..." }` antes del login |

### 3.3 Interfaz admin

1. **Seguridad → Usuarios** → Editar usuario.
2. Bloque **Configuración de acceso** (debajo del formulario de datos).
3. Marque los métodos permitidos y opciones de 2FA → **Guardar configuración de acceso**.

Casos típicos:

| Caso | Local | Google | Microsoft | Requiere 2FA | 2FA voluntario perfil |
|------|-------|--------|-----------|--------------|------------------------|
| Solo Gmail | No | Sí | No | No | Sí |
| Solo local | Sí | No | No | Opcional | Sí |
| QA con QR obligatorio | Sí | No | No | **Sí** | No |
| Admin principal flexible | Sí | Sí | Sí | No | Sí |

### 3.4 Pantalla de login (usuario final)

1. Ingresa **correo** y sale del campo (blur).
2. El sistema consulta `POST /api/auth/metodos-por-email`.
3. Solo aparecen las tarjetas (Local / Google / Microsoft) permitidas para esa cuenta.
4. Si `requiere_2fa`, se muestra aviso antes de elegir método.

---

## 4. Verificación en dos pasos (2FA TOTP)

### 4.1 Paquetes

- `pragmarx/google2fa`
- `bacon/bacon-qr-code`

### 4.2 Tablas

- `seguridad_usuario_2fa` — secreto cifrado, estado habilitado
- `seguridad_auth_2fa_challenge` — desafíos temporales (`verify`, `setup_mandatory`)

### 4.3 Flujo

1. Login u OAuth exitoso.
2. Si debe configurar 2FA obligatorio → pantalla **setup** con QR.
3. Si ya tiene 2FA → pantalla **código TOTP** (6 dígitos o recuperación).
4. Tras validar → JWT completo y acceso al dashboard.

### 4.4 Endpoints 2FA

| Ruta | Descripción |
|------|-------------|
| `POST /api/auth/2fa/verify` | Código tras login |
| `POST /api/auth/2fa/mandatory/init` | Genera QR (obligatorio) |
| `POST /api/auth/2fa/mandatory/confirm` | Confirma setup obligatorio |
| `GET /api/auth/2fa/status` | Estado en perfil (JWT) |
| `POST /api/auth/2fa/setup` | Setup voluntario (si admin permite) |
| `POST /api/auth/2fa/confirm` | Confirma setup voluntario |
| `POST /api/auth/2fa/disable` | Desactivar (si no es obligatorio) |

### 4.5 Rol QA

- Rol **QA PRUEBAS** con `requiere_2fa = 1`.
- Usuario de prueba: `qa@valentostile.test` / `QaTest2026!`
- Ver: `docs/PRUEBA_QA_2FA.md`

### 4.6 Perfil del usuario

En **Mi perfil** → tarjeta **Verificación en dos pasos**:

- Visible solo si `permite_2fa_voluntario` y no hay 2FA obligatorio por admin/rol.
- Si es obligatorio, solo puede completar el setup al iniciar sesión.

---

## 5. Administrador principal

- Email: `ghiovani666@gmail.com`
- Columna: `users.es_administrador_principal`
- No se puede eliminar sin transferir el cargo.
- Seeder: `php artisan db:seed --class=PrincipalAdminSeeder`
- SQL manual: `database/scripts/asignar_admin_principal_ghiovani.sql`
- Documento: `docs/ADMIN_PRINCIPAL.md`

Configuración por defecto del principal (seeder):

- Local + Google + Microsoft habilitados
- 2FA no obligatorio; puede activarlo desde perfil
- Método predeterminado en login: **Google**

---

## 6. Variables de entorno (desarrollo)

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173/admin
FRONTEND_OAUTH_PATH=/admin

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/oauth/callback/google

MICROSOFT_CLIENT_ID=
MICROSOFT_CLIENT_SECRET=
MICROSOFT_TENANT_ID=common
MICROSOFT_REDIRECT_URI=http://localhost:8000/api/auth/oauth/callback/microsoft
```

---

## 7. Pasos que debe ejecutar usted

### 7.1 Base de datos

```bash
cd impacto-gigante-Backend
php artisan migrate --path=database/migrations/2026_05_21_110000_create_seguridad_usuario_auth_config_table.php
php artisan db:seed --class=PrincipalAdminSeeder
```

(Si aún no tiene proveedores OAuth: `php artisan db:seed --class=SeguridadAuthProveedorSeeder`)

### 7.2 Google Cloud (si usa Gmail)

1. Consola → Credenciales OAuth → URI de redirección:
   `http://localhost:8000/api/auth/oauth/callback/google`
2. Copiar Client ID/Secret al panel o `.env`.
3. Ver: `docs/PRUEBA_GOOGLE_FIX_REDIRECT.md`

### 7.3 Asignar políticas a usuarios

1. Iniciar sesión como administrador.
2. **Usuarios** → Editar cada cuenta → **Configuración de acceso**.
3. Marque métodos y 2FA según la tabla de casos (sección 3.3).

### 7.4 Probar login

1. Cerrar sesión.
2. En login escribir el correo del usuario de prueba.
3. Verificar que solo aparecen los métodos asignados.
4. Si marcó **Exigir 2FA**, completar QR en el primer acceso.

### 7.5 Frontend

Reiniciar el servidor Vite si estaba corriendo:

```bash
cd impacto-gigante-Frontend
npm run dev
```

---

## 8. Problemas resueltos en la conversación

| Problema | Causa | Solución |
|----------|-------|----------|
| `redirect_uri_mismatch` (Google) | URI antigua en Console | Usar `/api/auth/oauth/callback/google` |
| Pantalla en blanco OAuth | Import incorrecto `useAuth` | Import desde `app/hooks/useAuth` |
| 422 código inválido / carga infinita | Doble exchange (StrictMode) + loading inicial | Anti-doble consumo + `initialLoading: false` |
| Admin principal sin 2FA | Rol ACCESO GENERAL no exige 2FA | Activar `requiere_2fa` en config del usuario o rol QA |
| Error permisos rol TreeView | `itemData` undefined | `ensureUniqueTreeKeys` en menú |

---

## 9. Credenciales de prueba

| Cuenta | Contraseña / método | Notas |
|--------|---------------------|-------|
| `ghiovani666@gmail.com` | Google o `AdminPrincipal2026!` (local) | Admin principal |
| `qa@valentostile.test` | `QaTest2026!` | Rol QA, 2FA obligatorio por rol |

---

## 10. Documentos relacionados

- `docs/AUTH_SETUP.md` — Instalación OAuth global
- `docs/Autenticacion-2pasosmd` — Detalle técnico 2FA
- `docs/ADMIN_PRINCIPAL.md` — Mandante principal
- `docs/PRUEBA_QA_2FA.md` — Prueba rol QA
- `docs/PRUEBA_GOOGLE_FIX_REDIRECT.md` — Google redirect
- `docs/PRUEBA_MICROSOFT_PASO_A_PASO.md` — Microsoft

---

## 11. Archivos clave del código

**Backend**

- `app/Services/Seguridad/UsuarioAuthConfigService.php`
- `app/Http/Controllers/Api/SeguridadUsuarioAuthConfigController.php`
- `app/Services/Auth/AuthTwoFactorService.php`
- `app/Services/Auth/AuthOAuthService.php`
- `app/Http/Controllers/Api/AuthController.php`

**Frontend**

- `src/app/views/usuarios/UsuarioAuthConfigPanel.jsx`
- `src/app/views/sessions/login/JwtLogin.jsx`
- `src/app/api/authUsuarioConfig.api.js`
- `src/app/views/profile/components/TwoFactorSettingsCard.jsx`

---

*Última actualización: mayo 2026 — política de acceso por usuario.*
