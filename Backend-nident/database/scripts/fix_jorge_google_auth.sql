-- Habilitar Google para jorgebasicoa@gmail.com (login OAuth)
-- Ejecutar en bd_prestomart04 si aparece: "no tiene permitido iniciar sesión con google"

SET @email = 'jorgebasicoa@gmail.com';
SET @id_usuario = (SELECT id FROM users WHERE email = @email LIMIT 1);

INSERT INTO seguridad_usuario_auth_config (
  id_usuario,
  permitir_local,
  permitir_google,
  permitir_microsoft,
  requiere_2fa,
  permite_2fa_voluntario,
  metodo_predeterminado,
  created_at,
  updated_at
)
VALUES (
  @id_usuario,
  0,
  1,
  0,
  0,
  1,
  'google',
  NOW(),
  NOW()
)
ON DUPLICATE KEY UPDATE
  permitir_local = 0,
  permitir_google = 1,
  permitir_microsoft = 0,
  metodo_predeterminado = 'google',
  updated_at = NOW();

SELECT id_usuario, permitir_local, permitir_google, permitir_microsoft, metodo_predeterminado
FROM seguridad_usuario_auth_config
WHERE id_usuario = @id_usuario;
