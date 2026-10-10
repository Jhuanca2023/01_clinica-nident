# Pruebas — Rol QA y 2FA obligatorio

## Credenciales de prueba (usuario QA)

| Campo | Valor |
|--------|--------|
| **Email** | `qa@prestomart.test` |
| **Password** | `QaTest2026!` |

Rol: **QA PRUEBAS** (acceso total + **2FA obligatorio**).

---

## Instalación (una vez)

```powershell
cd C:\xampp\htdocs_pro\33-impacto-gigante\impacto-gigante-Backend

php artisan migrate --path=database/migrations/2026_05_20_150000_add_requiere_2fa_to_seguridad_roles_and_challenge_tipo.php

php artisan db:seed --class=QaRoleAndUserSeeder
```

Servicios:

```powershell
php artisan serve
# En otra terminal, frontend:
cd ..\impacto-gigante-Frontend
npm run dev
```

Login: **http://localhost:5173/admin/session/signin**

Para probar solo **Local** (recomendado la primera vez): en **Proveedores de autenticación** deja **Local** activo.

---

## Flujo primera vez (2FA obligatorio)

1. Inicia sesión con `qa@prestomart.test` / `QaTest2026!`
2. Te redirige a **Configuración obligatoria 2FA** (no entras al panel aún).
3. Escanea el **QR** con Google Authenticator o Microsoft Authenticator.
4. Ingresa el código de **6 dígitos** → **Activar y continuar**.
5. Guarda los **8 códigos de recuperación** → **Continuar al panel**.
6. Si tienes un solo perfil, entras al dashboard con permisos QA (casi todo el menú).

---

## Flujo siguientes logins

1. Email/contraseña (o Google si está activo y el email coincide).
2. Pantalla **Verificación en dos pasos** → código de la app (6 dígitos).
3. Entrada al panel.

---

## Asignar más usuarios al rol QA (sin configurar 2FA uno a uno)

En **Seguridad → Usuarios** (o SQL):

1. Asigna el perfil **Equipo QA** al usuario (mismo email que usará en Google si usas OAuth).
2. Al **primer login**, el sistema fuerza el QR y la activación 2FA automáticamente.

Para marcar **otro rol** con 2FA obligatorio:

```sql
UPDATE seguridad_roles SET requiere_2fa = 1 WHERE nombre = 'NOMBRE_DEL_ROL';
```

---

## Probar Google OAuth + 2FA QA

1. Panel → **Proveedores de autenticación** → credenciales Google → activar **solo Google**.
2. Usuario QA debe tener email = cuenta Google (`qa@prestomart.test` solo si esa es tu cuenta Google; si no, cambia el email del usuario QA).
3. Cerrar sesión → Iniciar con Google → setup 2FA (primera vez) o código TOTP (siguientes).

---

## Resetear 2FA del usuario QA (solo pruebas)

```sql
DELETE FROM seguridad_usuario_2fa WHERE id_usuario = (SELECT id FROM users WHERE email = 'qa@prestomart.test');
DELETE FROM seguridad_auth_2fa_challenge WHERE id_usuario = (SELECT id FROM users WHERE email = 'qa@prestomart.test');
```

Vuelve a iniciar sesión: te pedirá configurar el QR otra vez.
