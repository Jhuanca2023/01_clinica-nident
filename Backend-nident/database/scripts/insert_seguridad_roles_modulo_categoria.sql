-- Asignar al rol 16 (perfil 2) acceso al módulo Categoria (id_modulo 22).
-- Ejecutar después de crear la tabla seguridad_roles_modulo (migración 2026_02_18_000001).
-- Así el usuario con perfil 2 verá solo el ítem "Categoria" en el sidebar.

INSERT IGNORE INTO seguridad_roles_modulo (id_roles, id_modulo, created_at, updated_at)
VALUES (16, 22, NOW(), NOW());
