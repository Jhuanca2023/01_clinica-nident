# Endpoints de autenticación y sesión

## Login (`POST /api/login`)
- Valida credenciales (email, password).
- Si falla: responde **422** con mensaje en español (ej. "El correo electrónico o la contraseña no son correctos...").
- Si ok: devuelve token, refreshToken, user y perfil (lista de perfiles del usuario para el segundo formulario).

## validar_conexion (`POST /api/perfil/validar_conexion`)
- **Uso:** Restaurar sesión cuando la app se carga con un token en localStorage (recarga de página, pestaña cerrada y reabierta).
- Devuelve **user** y **perfil** asociados al JWT actual. No renueva el token.
- **Por qué es necesario:** El refresh token solo se usa en el interceptor ante 401 para obtener un nuevo JWT; no devuelve user ni perfil. Para tener user/perfil en estado tras recargar, hace falta este endpoint.

## validar_perfil (`POST /api/perfil/validar_perfil`)
- **Body:** `{ id_perfil, id_rol }` (selección del usuario en el segundo formulario del login).
- **Uso:** Fijar el perfil y rol activos y obtener **un único perfil** con `menu_objetos` y `modulos_permitidos` de ese rol.
- **Por qué es necesario:** El sidebar filtra ítems con `perfil.menu_objetos` y `perfil.modulos_permitidos`. Esos datos dependen del rol; sin este endpoint no habría forma de tener el perfil correcto para el rol elegido.

## Resumen
| Endpoint           | Cuándo se usa                          | Sustituible por refresh token / Zustand |
|--------------------|----------------------------------------|----------------------------------------|
| validar_conexion   | Init con token (recarga), refreshUser  | No. El refresh solo da JWT.           |
| validar_perfil     | Tras elegir perfil + rol en el login  | No. Define permisos del rol activo.   |
