-- Limpieza de permisos por rol (basura / id_modulo mezclado en id_menu)
-- Ejecutar en MySQL antes de reasignar permisos en la app.
-- Equivalente: php artisan db:seed --class=PermisosLimpiezaSeeder

SET FOREIGN_KEY_CHECKS = 0;

-- Filas erróneas: id_menu = id_modulo
DELETE srm FROM seguridad_roles_menu srm
INNER JOIN sistema_modulo sm ON sm.id_modulo = srm.id_menu;

TRUNCATE TABLE seguridad_roles_menu;

TRUNCATE TABLE seguridad_menu_objetos_roles;

-- Si existen:
-- TRUNCATE TABLE seguridad_roles_modulo;
-- TRUNCATE TABLE seguridad_modulo_objetos_roles;

SET FOREIGN_KEY_CHECKS = 1;

-- Luego en terminal:
-- php artisan db:seed --class=PrincipalAdminSeeder
-- php artisan db:seed --class=QaRoleAndUserSeeder
-- Asigne permisos en Seguridad → Roles → ACCESO GENERAL y guarde.
