-- =============================================================================
-- Script MySQL 8.1 - Gestión de menús y objetos para permisos por rol
-- Ejecutar en la base de datos correspondiente (bd_analisis o la que use la app)
-- =============================================================================

-- 0) Tabla para refresh token (JWT) si no existe
CREATE TABLE IF NOT EXISTS `refresh_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `refresh_tokens_token_unique` (`token`),
  KEY `refresh_tokens_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1) Tabla para permisos a nivel de objeto (botón, vista, etc.) por rol
-- La tabla seguridad_roles_menu sigue siendo para permisos a nivel menú
DROP TABLE IF EXISTS `seguridad_menu_objetos_roles`;
CREATE TABLE `seguridad_menu_objetos_roles` (
  `id_roles` int NOT NULL,
  `id_menu_objetos` int NOT NULL,
  PRIMARY KEY (`id_roles`, `id_menu_objetos`),
  KEY `fk_smor_roles` (`id_roles`),
  KEY `fk_smor_menu_objetos` (`id_menu_objetos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2) Tabla temporal para el treeview (si no existe)
-- Ajustar según tu BD: si ya tienes datatreeview con esta estructura, no hace falta crearla
DROP TABLE IF EXISTS `datatreeview`;
CREATE TABLE `datatreeview` (
  `IdMenu` varchar(64) NOT NULL,
  `IdMenuPadre` varchar(64) DEFAULT NULL,
  `id_menu` int DEFAULT NULL,
  `id_menu_objetos` int DEFAULT NULL,
  `IdObjeto` int DEFAULT NULL,
  `Menu` varchar(255) DEFAULT NULL,
  `Icon` varchar(64) DEFAULT 'activefolder',
  `Icono` varchar(64) DEFAULT NULL,
  `Nivel` int DEFAULT 0,
  `Orden` int DEFAULT 0,
  `Activo` char(1) DEFAULT 'S',
  `selected` tinyint(1) DEFAULT 0,
  `Expanded` tinyint(1) DEFAULT 1,
  `IsNew` int DEFAULT 0,
  `DataProtecion` int DEFAULT 0,
  `Tipo` varchar(20) DEFAULT 'menu',
  PRIMARY KEY (`IdMenu`),
  KEY `idx_padre` (`IdMenuPadre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3) SP: Listar treeview de menús y objetos con selección por rol
-- Devuelve: menús y objetos; selected=1 según seguridad_roles_menu y seguridad_menu_objetos_roles
-- Para objetos se usa id_menu_objetos como IdMenu (único) para evitar duplicados
DROP PROCEDURE IF EXISTS `USP_SEGURIDAD_PERFIL_MENU_LISTAR_TREEVIEW`;
DELIMITER $$
CREATE PROCEDURE `USP_SEGURIDAD_PERFIL_MENU_LISTAR_TREEVIEW`(IN p_id_roles INT)
BEGIN
  DECLARE v_id_roles INT DEFAULT NULL;

  SET v_id_roles = NULLIF(p_id_roles, 0);

  DELETE FROM datatreeview;

  -- NIVEL 0: Módulos (padre de menús). Prefijo 'mod_' para no colisionar con id_menu (que usa 'm')
  INSERT INTO datatreeview (IdMenu, IdMenuPadre, id_menu, Menu, Icon, Nivel, Orden, Activo, selected, Expanded, Tipo)
  SELECT
    CONCAT('mod_', SM.id_modulo) AS IdMenu,
    NULL AS IdMenuPadre,
    NULL AS id_menu,
    SM.nombre AS Menu,
    COALESCE(SM.Icon, 'folder') AS Icon,
    0 AS Nivel,
    0 AS Orden,
    COALESCE(SM.Activo, 'S') AS Activo,
    0 AS selected,
    1 AS Expanded,
    'modulo'
  FROM sistema_modulo SM
  WHERE SM.Activo = 'S' OR SM.Activo IS NULL;

  -- NIVEL 1: Menús (hijos de módulo o de otro menú). IdMenuPadre: 'mod_X' si raíz, 'mY' si hijo de menú
  INSERT INTO datatreeview (IdMenu, IdMenuPadre, id_menu, Menu, Icon, Nivel, Orden, Activo, selected, Expanded, Tipo)
  SELECT
    CONCAT('m', SME.id_menu) AS IdMenu,
    CASE
      WHEN SME.id_menu_padre IS NOT NULL THEN CONCAT('m', SME.id_menu_padre)
      ELSE CONCAT('mod_', SME.id_modulo)
    END AS IdMenuPadre,
    SME.id_menu,
    SME.nombre AS Menu,
    'activefolder' AS Icon,
    1 AS Nivel,
    0 AS Orden,
    COALESCE(SME.Activo, 'S') AS Activo,
    CASE WHEN v_id_roles IS NOT NULL AND EXISTS (
      SELECT 1 FROM seguridad_roles_menu SRM
      WHERE SRM.id_roles = v_id_roles AND SRM.id_menu = SME.id_menu
    ) THEN 1 ELSE 0 END AS selected,
    1 AS Expanded,
    'menu'
  FROM sistema_menu SME
  WHERE SME.id_modulo IS NOT NULL
    AND (SME.Activo = 'S' OR SME.Activo IS NULL);

  -- NIVEL 2: Objetos (hijos de menú)
  INSERT INTO datatreeview (IdMenu, IdMenuPadre, id_menu, id_menu_objetos, IdObjeto, Menu, Icon, Nivel, Orden, Activo, selected, Expanded, Tipo)
  SELECT
    CONCAT('o', SMO.id_menu_objetos) AS IdMenu,
    CONCAT('m', SMO.id_menu) AS IdMenuPadre,
    SMO.id_menu,
    SMO.id_menu_objetos,
    SMO.id_objetos AS IdObjeto,
    COALESCE(SO.nombre, 'Sin nombre') AS Menu,
    'bullet' AS Icon,
    2 AS Nivel,
    0 AS Orden,
    COALESCE(SMO.Activo, 'S') AS Activo,
    CASE WHEN v_id_roles IS NOT NULL AND EXISTS (
      SELECT 1 FROM seguridad_menu_objetos_roles SMOR
      WHERE SMOR.id_roles = v_id_roles AND SMOR.id_menu_objetos = SMO.id_menu_objetos
    ) THEN 1 ELSE 0 END AS selected,
    0 AS Expanded,
    'objeto'
  FROM sistema_menu_objetos SMO
  LEFT JOIN sistema_objetos SO ON SO.id_objetos = SMO.id_objetos
  WHERE (SMO.Activo = 'S' OR SMO.Activo IS NULL);

  -- Resultado: mismo formato que antes para compatibilidad con el front
  SELECT
    TD.IdMenu,
    TD.IdMenuPadre,
    TD.id_menu,
    TD.id_menu_objetos,
    TD.IdObjeto,
    TD.Menu AS Nombre,
    TD.Menu,
    TD.Icon AS Icono,
    TD.Icon,
    TD.Nivel,
    TD.Orden,
    TD.selected,
    TD.Expanded,
    TD.Activo,
    TD.Tipo
  FROM datatreeview TD
  WHERE TD.Menu IS NOT NULL AND TD.Menu <> ''
  ORDER BY TD.Nivel, TD.Orden, TD.Menu;
END$$
DELIMITER ;

-- 4) SP: Listar menús para sidebar (por usuario/perfil/rol)
-- Opcional: si quieres que el sidebar venga de BD según permisos
-- Aquí devuelve todos los módulos y menús con path/icon; puedes filtrar por id_roles después en la app
-- (O crear otro SP que reciba id_usuario y devuelva solo los permitidos)

-- Ejemplo de uso:
-- CALL USP_SEGURIDAD_PERFIL_MENU_LISTAR_TREEVIEW(1);
