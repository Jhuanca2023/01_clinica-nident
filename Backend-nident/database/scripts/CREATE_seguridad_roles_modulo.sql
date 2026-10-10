-- ============================================================
-- Crear tabla seguridad_roles_modulo (permisos por rol a módulos tipo link, ej. Categoria)
-- Ejecutar en Navicat, phpMyAdmin o cualquier cliente MySQL sobre bd_prestomart
-- ============================================================

USE bd_prestomart;

-- Si ya existe, eliminar (opcional; quitar el DROP si no quieres borrar datos)
DROP TABLE IF EXISTS seguridad_roles_modulo;

CREATE TABLE seguridad_roles_modulo (
  id_roles    INT UNSIGNED NOT NULL,
  id_modulo   INT UNSIGNED NOT NULL,
  created_at  TIMESTAMP NULL DEFAULT NULL,
  updated_at  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id_roles, id_modulo),
  CONSTRAINT fk_srm_roles   FOREIGN KEY (id_roles)  REFERENCES seguridad_roles (id_roles)  ON DELETE CASCADE,
  CONSTRAINT fk_srm_modulo  FOREIGN KEY (id_modulo) REFERENCES sistema_modulo (id_modulo) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Asignar al rol 16 (perfil 2) acceso al módulo Categoria (id_modulo 22)
INSERT INTO seguridad_roles_modulo (id_roles, id_modulo, created_at, updated_at)
VALUES (16, 22, NOW(), NOW());
