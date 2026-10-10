-- =============================================================================
-- Función y SPs de seguridad_perfil (Perfiles admin)
-- =============================================================================

DROP FUNCTION IF EXISTS `SPLIT_STR`;
DELIMITER $$
CREATE FUNCTION `SPLIT_STR`(
  x VARCHAR(255),
  delim VARCHAR(12),
  pos INT
) RETURNS varchar(255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci
DETERMINISTIC
RETURN REPLACE(
  SUBSTRING(
    SUBSTRING_INDEX(x, delim, pos),
    LENGTH(SUBSTRING_INDEX(x, delim, pos - 1)) + 1
  ),
  delim,
  ''
)$$
DELIMITER ;

DROP PROCEDURE IF EXISTS `USP_SEGURIDAD_PERFIL_LISTAR`;
DELIMITER $$
CREATE PROCEDURE `USP_SEGURIDAD_PERFIL_LISTAR`()
BEGIN
  SELECT
    ROW_NUMBER() OVER (ORDER BY DM.id_perfil) AS RowIndex,
    DM.id_perfil,
    DM.nombre,
    DM.created_at,
    DM.Activo
  FROM seguridad_perfil DM;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS `USP_SEGURIDAD_PERFIL_ASIGNAR`;
DELIMITER $$
CREATE PROCEDURE `USP_SEGURIDAD_PERFIL_ASIGNAR`(
  IN id_usuario_ INT,
  IN cadena_id_perfil TEXT
)
BEGIN
  DECLARE count INT DEFAULT 0;
  DECLARE valor1_ VARCHAR(255);

  read_loop: LOOP
    SET count = count + 1;
    SET valor1_ = SPLIT_STR(cadena_id_perfil, '|', count);

    IF valor1_ = '' THEN
      LEAVE read_loop;
    END IF;

    INSERT INTO seguridad_perfil_users (id_usuario, id_perfil, created_at)
    VALUES (id_usuario_, valor1_, NOW());
  END LOOP read_loop;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS `USP_SEGURIDAD_PERFIL_ELIMINAR_MULTIPLE`;
DELIMITER $$
CREATE PROCEDURE `USP_SEGURIDAD_PERFIL_ELIMINAR_MULTIPLE`(
  IN cadena_id_perfil TEXT
)
BEGIN
  DECLARE count INT DEFAULT 0;
  DECLARE valor1_ VARCHAR(255);

  read_loop: LOOP
    SET count = count + 1;
    SET valor1_ = SPLIT_STR(cadena_id_perfil, '|', count);

    IF valor1_ = '' THEN
      LEAVE read_loop;
    END IF;

    DELETE FROM seguridad_perfil_users WHERE id_perfil_users = valor1_;
  END LOOP read_loop;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS `USP_SEGURIDAD_PERFIL_OBTENER_CHECK`;
DELIMITER $$
CREATE PROCEDURE `USP_SEGURIDAD_PERFIL_OBTENER_CHECK`(
  IN id_usuario_ INT
)
BEGIN
  SELECT
    ROW_NUMBER() OVER (ORDER BY DM.id_perfil) AS RowIndex,
    DM.id_perfil,
    DM.nombre,
    DM.created_at,
    DM.Activo
  FROM seguridad_perfil DM
  WHERE DM.id_perfil NOT IN (
    SELECT SP.id_perfil
    FROM seguridad_perfil SP
    INNER JOIN seguridad_perfil_users SPU ON SP.id_perfil = SPU.id_perfil
    WHERE SPU.id_usuario = id_usuario_
  );
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS `USP_SEGURIDAD_PERFIL_OBTENER_ELIMINAR`;
DELIMITER $$
CREATE PROCEDURE `USP_SEGURIDAD_PERFIL_OBTENER_ELIMINAR`(
  IN id_perfil_users_ INT
)
BEGIN
  DELETE FROM seguridad_perfil_users WHERE id_perfil_users = id_perfil_users_;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS `USP_SEGURIDAD_PERFIL_OBTENER_LISTA`;
DELIMITER $$
CREATE PROCEDURE `USP_SEGURIDAD_PERFIL_OBTENER_LISTA`(
  IN id_usuario_ INT
)
BEGIN
  SELECT
    ROW_NUMBER() OVER (ORDER BY SP.id_perfil) AS RowIndex,
    SP.id_perfil,
    SPU.id_perfil_users,
    SP.nombre,
    SP.created_at,
    SP.updated_at,
    SPU.id_usuario,
    SP.Activo
  FROM seguridad_perfil SP
  INNER JOIN seguridad_perfil_users SPU ON SP.id_perfil = SPU.id_perfil
  WHERE SPU.id_usuario = id_usuario_;
END$$
DELIMITER ;
