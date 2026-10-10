# Administrador principal del sistema

## Cuenta principal

| Campo | Valor |
|--------|--------|
| Email | `ghiovani666@gmail.com` |
| Perfil | Administrador |
| Rol | ACCESO GENERAL |
| Login local (opcional) | `AdminPrincipal2026!` |

## Instalación

```powershell
php artisan migrate --path=database/migrations/2026_05_21_100000_add_es_administrador_principal_to_users.php
php artisan db:seed --class=PrincipalAdminSeeder
```

O SQL: `database/scripts/asignar_admin_principal_ghiovani.sql`

## Reglas

- Siempre debe existir **exactamente un** usuario con `es_administrador_principal = 1`.
- No se puede **eliminar** ni **desactivar** al principal sin transferir el cargo antes.
- No se puede **cambiar el email** del principal.
- En **Usuarios**, al eliminar al principal aparece el diálogo **Transferir administrador principal**.

## API

- `GET /api/usuario/admin-principal`
- `POST /api/usuario/transferir-admin-principal` — body: `{ "id_usuario_nuevo": 123 }`
