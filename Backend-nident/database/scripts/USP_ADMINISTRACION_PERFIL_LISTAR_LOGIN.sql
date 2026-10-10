-- =============================================================================
-- SP: Lista menús y objetos por usuario/perfil/rol para login.
-- Parámetros: p_id_usuario (obligatorio), p_id_perfil (opcional), p_id_roles (opcional).
-- Si p_id_perfil y p_id_roles son NULL, devuelve TODOS los perfiles del usuario
-- (para que el front muestre el selector cuando count > 1).
-- Formato menu_objetos: |id_menu_id_objeto1,id_objeto2,...|-|...
-- No usa datatreeview; usa sistema_menu_objetos.
-- =============================================================================

DROP PROCEDURE IF EXISTS `USP_ADMINISTRACION_PERFIL_LISTAR_LOGIN`;
DELIMITER $$
CREATE PROCEDURE `USP_ADMINISTRACION_PERFIL_LISTAR_LOGIN`(
  IN p_id_usuario INT,
  IN p_id_perfil INT,
  IN p_id_roles INT
)
BEGIN
  SELECT
    SPU.id_usuario,
    U.name AS nombre_usuario,
    U.email,
    SP.nombre AS nombre_perfil,
    SP.id_perfil,
    SRP.id_roles,
    (
      SELECT GROUP_CONCAT(DISTINCT CONCAT('|', SRM.id_menu, '_', objs.objeto_list) ORDER BY SRM.id_menu SEPARATOR '-')
      FROM (
        SELECT SRM2.id_menu,
               GROUP_CONCAT(DISTINCT SMO.id_objetos ORDER BY SMO.id_objetos SEPARATOR ',') AS objeto_list
        FROM seguridad_roles_perfil SRP2
        INNER JOIN seguridad_roles_menu SRM2 ON SRP2.id_roles = SRM2.id_roles
        INNER JOIN sistema_menu_objetos SMO ON SMO.id_menu = SRM2.id_menu AND (SMO.Activo = 'S' OR SMO.Activo IS NULL)
        WHERE SRP2.id_perfil = SP.id_perfil
          AND SRP2.id_roles = SRP.id_roles
        GROUP BY SRM2.id_menu
      ) objs
      INNER JOIN seguridad_roles_menu SRM ON SRM.id_menu = objs.id_menu AND SRM.id_roles = SRP.id_roles
    ) AS menu_objetos
  FROM users U
  INNER JOIN seguridad_perfil_users SPU ON U.id = SPU.id_usuario
  INNER JOIN seguridad_perfil SP ON SPU.id_perfil = SP.id_perfil
  LEFT JOIN seguridad_roles_perfil SRP ON SP.id_perfil = SRP.id_perfil
  WHERE SPU.id_usuario = p_id_usuario
    AND (p_id_perfil IS NULL OR SP.id_perfil = p_id_perfil)
    AND (p_id_roles IS NULL OR SRP.id_roles = p_id_roles)
  ORDER BY SP.id_perfil ASC, SRP.id_roles ASC;
END$$
DELIMITER ;
