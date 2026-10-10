-- =============================================================================
-- SP: Lista todos los perfiles asignados a un usuario (para selector de perfil en login).
-- Uso: CALL USP_SEGURIDAD_PERFIL_OBTENER_LISTA(id_usuario);
-- Devuelve: id_perfil, nombre (uno por cada fila en seguridad_perfil_users).
-- =============================================================================

DROP PROCEDURE IF EXISTS `USP_SEGURIDAD_PERFIL_OBTENER_LISTA`;
DELIMITER $$
CREATE PROCEDURE `USP_SEGURIDAD_PERFIL_OBTENER_LISTA`(IN p_id_usuario INT)
BEGIN
  SELECT
    SP.id_perfil,
    SP.nombre
  FROM seguridad_perfil_users SPU
  INNER JOIN seguridad_perfil SP ON SPU.id_perfil = SP.id_perfil
  WHERE SPU.id_usuario = p_id_usuario
  ORDER BY SP.nombre ASC;
END$$
DELIMITER ;
