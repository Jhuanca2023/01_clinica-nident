-- Administrador principal: ghiovani666@gmail.com
-- Ejecutar en bd_prestomart04 después de la migración es_administrador_principal.
-- Requiere columna users.es_administrador_principal (migración 2026_05_21_100000).

SET @email := 'ghiovani666@gmail.com';

-- 1) Perfil Administrador
SET @id_perfil := (SELECT id_perfil FROM seguridad_perfil WHERE nombre = 'Administrador' LIMIT 1);
INSERT INTO seguridad_perfil (nombre, Activo, created_at, updated_at)
SELECT 'Administrador', 'S', NOW(), NOW()
WHERE @id_perfil IS NULL;
SET @id_perfil := (SELECT id_perfil FROM seguridad_perfil WHERE nombre = 'Administrador' LIMIT 1);

-- 2) Rol ACCESO GENERAL
SET @id_roles := (SELECT id_roles FROM seguridad_roles WHERE nombre = 'ACCESO GENERAL' LIMIT 1);

INSERT IGNORE INTO seguridad_roles_perfil (id_perfil, id_roles, created_at)
VALUES (@id_perfil, @id_roles, NOW());

-- 3) Usuario (si no existe por OAuth, créelo; ajuste password si usa login local)
INSERT INTO users (name, email, password, Activo, es_administrador_principal, created_at, updated_at)
SELECT 'Administrador Principal', @email,
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
       'S', 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = @email);

SET @id_usuario := (SELECT id FROM users WHERE email = @email LIMIT 1);

-- 4) Un solo administrador principal
UPDATE users SET es_administrador_principal = 0;
UPDATE users SET es_administrador_principal = 1, Activo = 'S' WHERE id = @id_usuario;

-- 5) Vincular perfil
INSERT IGNORE INTO seguridad_perfil_users (id_usuario, id_perfil, created_at)
VALUES (@id_usuario, @id_perfil, NOW());

-- 6) Configuración de acceso (si existe tabla seguridad_usuario_auth_config)
INSERT INTO seguridad_usuario_auth_config (
  id_usuario, permitir_local, permitir_google, permitir_microsoft,
  requiere_2fa, permite_2fa_voluntario, metodo_predeterminado, created_at, updated_at
)
VALUES (@id_usuario, 1, 1, 1, 0, 1, 'google', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  permitir_local = 1,
  permitir_google = 1,
  permitir_microsoft = 1,
  requiere_2fa = 0,
  permite_2fa_voluntario = 1,
  metodo_predeterminado = 'google',
  updated_at = NOW();

SELECT id, name, email, es_administrador_principal, Activo FROM users WHERE email = @email;
