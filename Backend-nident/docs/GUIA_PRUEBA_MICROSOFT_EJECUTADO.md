# Guía de prueba Microsoft — Lo ejecutado y pasos para el usuario

Documento generado a partir de la conversación de implementación y puesta en marcha de autenticación OAuth (Microsoft).

---

## Lo que ya ejecuté / configuré en tu proyecto

1. **`.env`**: `APP_URL=http://localhost:8000`, `FRONTEND_URL`, variables `MICROSOFT_*` (vacías para que las completes).
2. **Redirect URIs en BD** sincronizadas:  
   `http://localhost:8000/api/auth/oauth/callback/microsoft`
3. Comando **`php artisan auth:setup-sync`** para revisar estado cuando quieras.
4. Guía detallada: **`docs/PRUEBA_MICROSOFT_PASO_A_PASO.md`**
5. La API responde OK en: `http://localhost:8000/api/auth/metodos-login`

---

## ¿Tengo que pagar? ¿A dónde voy?

| Pregunta | Respuesta |
|----------|-----------|
| ¿Pago? | **No** para probar en local. Solo creas una app gratis en Azure. |
| ¿Dónde? | **https://portal.azure.com** → **Microsoft Entra ID** → **App registrations** → **New registration** |
| Guía paso a paso | Abre `impacto-gigante-Backend/docs/PRUEBA_MICROSOFT_PASO_A_PASO.md` |

---

## Tus pasos ahora (orden exacto)

### 1) Levantar servicios (2 terminales)

**Backend:**

```powershell
cd C:\xampp\htdocs_pro\33-impacto-gigante\impacto-gigante-Backend
php artisan serve
```

**Frontend:**

```powershell
cd C:\xampp\htdocs_pro\33-impacto-gigante\impacto-gigante-Frontend
npm run dev
```

Comprueba: http://localhost:8000/api/auth/metodos-login (debe verse JSON).

---

### 2) Crear app en Microsoft (5–10 min)

1. https://portal.azure.com  
2. Busca **Microsoft Entra ID**  
3. **App registrations** → **New registration**  
4. **Redirect URI (Web):**  
   ```
   http://localhost:8000/api/auth/oauth/callback/microsoft
   ```
   (copiar/pegar exacto, con puerto **8000**)
5. Copia **Application (client) ID**  
6. **Certificates & secrets** → **New client secret** → copia el **Value** (solo se ve una vez)  
7. **API permissions** → Microsoft Graph → Delegated: `openid`, `profile`, `email`, `User.Read`

---

### 3) Pegar credenciales en tu admin

1. Entra con **email/contraseña**:  
   http://localhost:5173/admin/session/signin  
2. Ve a:  
   http://localhost:5173/admin/configuracion/auth-proveedor  
3. Tarjeta **Microsoft** → **Configurar credenciales**  
   - CLIENT_ID = Application (client) ID  
   - CLIENT_SECRET = el secret de Azure  
   - TENANT_ID = `common`  
   - REDIRECT_URI = `http://localhost:8000/api/auth/oauth/callback/microsoft`  
4. **Guardar**  
5. Clic **Habilitar login**

*(También puedes poner CLIENT_ID y CLIENT_SECRET en `.env`; el sistema los usa si la BD aún no los tiene.)*

---

### 4) Probar login Microsoft

1. Cierra sesión o usa ventana privada  
2. http://localhost:5173/admin/session/signin  
3. Clic en la tarjeta **Microsoft**  
4. Inicia sesión con tu cuenta Microsoft  
5. Vuelves al admin con sesión JWT (o selección de perfil si tienes varios)

---

## Verificar que todo está bien

```powershell
cd C:\xampp\htdocs_pro\33-impacto-gigante\impacto-gigante-Backend
php artisan auth:setup-sync --check
```

Debe mostrar:

- `REDIRECT_URI: http://localhost:8000/api/auth/oauth/callback/microsoft`
- Tras guardar en panel: `CLIENT_ID: configurado`
- Tras **Habilitar login**: `microsoft — habilitado: SÍ`

---

## Si algo falla

| Error | Qué hacer |
|-------|-----------|
| `redirect_uri_mismatch` | La URI en Azure debe ser **idéntica** a la de arriba |
| `microsoft no está habilitado` | Panel → **Habilitar login** |
| Sin menú tras login | El usuario debe tener **perfil** en `seguridad_perfil_users` |
| No aparece tarjeta Microsoft | Habilitar en auth-proveedor |

---

## Resumen

El proyecto quedó con URLs y BD listas; **falta por tu parte**: crear la app en Azure (gratis), pegar Client ID + Secret, habilitar Microsoft y pulsar la tarjeta en el login.

Guía ampliada: **`docs/PRUEBA_MICROSOFT_PASO_A_PASO.md`**

---

## Archivos relacionados en el repo

| Archivo | Descripción |
|---------|-------------|
| `docs/PRUEBA_MICROSOFT_PASO_A_PASO.md` | Guía completa Azure + errores frecuentes |
| `docs/AUTH_SETUP.md` | Configuración general auth multi-proveedor |
| `docs/Autenticacion-2pasosmd` | Conversación y prompt de diseño |
| `docs/PROMPT_IA_AUTH.md` | Prompt reutilizable para otros proyectos |
| `.env` | `APP_URL`, `FRONTEND_URL`, `MICROSOFT_*` |
| `app/Console/Commands/AuthSetupSyncCommand.php` | Comando `php artisan auth:setup-sync` |
