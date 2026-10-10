# Prompt para IA — Autenticación multi-proveedor (referencia)

> **Estado:** Implementado en el repositorio. Usar este texto para extensiones (2FA, más proveedores) o en nuevas sesiones de Agent mode.

## Contexto del repositorio

Stack: **Laravel 10 (API)** con **JWT (`tymon/jwt-auth`)**, refresh tokens y `AuthController` API que arma perfil/roles/menús; **React (Vite)** con `JWTAuthContext.jsx` (login email/contraseña, Bearer, refresh en interceptor). En web existen rutas Google OAuth (Blade/Socialite), pero el **panel admin SPA** usa JWT por API. Referencia de patrón: **`PagoPasarelaController`** + **`PasarelaIndexPage.jsx`**. Prefijo de tablas de seguridad: **`seguridad_`**.

## Objetivo

Capa abstracta de proveedores de autenticación para login **SPA + API** con tres modos:

1. **Local (Laravel/JWT)** — predeterminado.
2. **Microsoft (Entra ID)** — OAuth con credenciales de prueba.
3. **Google** — OAuth configurable.

Desactivar OAuth deja solo la tarjeta local. No romper el contrato actual de `/api/login`.

## Requisitos de seguridad

- OAuth: **state + PKCE**; redirect URIs explícitos; sin secretos en frontend.
- Secretos en BD **cifrados**; listado admin **enmascarado** (`••••••••`).
- Post-OAuth: mismo payload JWT que login (`buildLoginResponseForUser`).
- Exchange code de un solo uso (no JWT en URL).
- Rate limit login/OAuth.
- Auditoría: `seguridad_auth_login_log`.
- 2FA TOTP: fase 2 (`seguridad_usuario_2fa` ya creada).

## Tablas (`seguridad_*`)

- `seguridad_auth_proveedor`
- `seguridad_auth_proveedor_config`
- `seguridad_usuario_oauth`
- `seguridad_auth_oauth_sesion`
- `seguridad_auth_login_log`
- `seguridad_usuario_2fa`

## Instrucción corta para la IA

Implementa sobre el repo real: respeta **JWT + AuthController + JWTAuthContext**; proveedores Microsoft/Google con capa tipo **Pasarela**, **local por defecto**, **OAuth + PKCE + state**, secretos enmascarados, prefijo **`seguridad_`**, y roadmap **2FA** sin romper el contrato del login.

## Documentación operativa

Ver **`docs/AUTH_SETUP.md`** para `.env`, portales Azure/Google y pasos de prueba.
