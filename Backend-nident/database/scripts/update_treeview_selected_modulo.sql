-- Actualiza USP_SEGURIDAD_PERFIL_MENU_LISTAR_TREEVIEW para marcar selected=1
-- en módulos que están en seguridad_roles_modulo (ej. Categoria, Envios x Peso).
-- Ejecutar después de crear la tabla seguridad_roles_modulo.

DROP PROCEDURE IF EXISTS `USP_SEGURIDAD_PERFIL_MENU_LISTAR_TREEVIEW`;
DELIMITER $$
CREATE PROCEDURE `USP_SEGURIDAD_PERFIL_MENU_LISTAR_TREEVIEW`(IN p_id_roles INT)
BEGIN
  DECLARE v_id_roles INT DEFAULT NULL;

  SET v_id_roles = NULLIF(p_id_roles, 0);

  DELETE FROM datatreeview;

  -- NIVEL 0: Módulos. selected=1 si el rol tiene el módulo en seguridad_roles_modulo
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
    CASE WHEN v_id_roles IS NOT NULL AND EXISTS (
      SELECT 1 FROM seguridad_roles_modulo SRM
      WHERE SRM.id_roles = v_id_roles AND SRM.id_modulo = SM.id_modulo
    ) THEN 1 ELSE 0 END AS selected,
    1 AS Expanded,
    'modulo'
  FROM sistema_modulo SM
  WHERE SM.Activo = 'S' OR SM.Activo IS NULL;

  -- NIVEL 1: Menús.
  INSERT INTO datatreeview (IdMenu, IdMenuPadre, id_menu, Menu, Icon, Nivel, Orden, Activo, selected, Expanded, Tipo)
  SELECT
    CONCAT('m', SME.id_menu) AS IdMenu,
    IF(SME.id_menu_padre IS NOT NULL, CONCAT('m', SME.id_menu_padre), CONCAT('mod_', SME.id_modulo)) AS IdMenuPadre,
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

  -- NIVEL 2 (bajo módulo): Objetos del módulo
  INSERT INTO datatreeview (IdMenu, IdMenuPadre, id_menu, id_menu_objetos, IdObjeto, Menu, Icon, Nivel, Orden, Activo, selected, Expanded, Tipo)
  SELECT
    CONCAT('om', SMO.id_modulo_objetos) AS IdMenu,
    CONCAT('mod_', SMO.id_modulo) AS IdMenuPadre,
    NULL AS id_menu,
    SMO.id_modulo_objetos AS id_menu_objetos,
    SMO.id_objetos AS IdObjeto,
    COALESCE(SO.nombre, 'Sin nombre') AS Menu,
    'bullet' AS Icon,
    2 AS Nivel,
    0 AS Orden,
    COALESCE(SMO.Activo, 'S') AS Activo,
    CASE WHEN v_id_roles IS NOT NULL AND EXISTS (
      SELECT 1 FROM seguridad_modulo_objetos_roles SMOR
      WHERE SMOR.id_roles = v_id_roles AND SMOR.id_modulo_objetos = SMO.id_modulo_objetos
    ) THEN 1 ELSE 0 END AS selected,
    0 AS Expanded,
    'objeto'
  FROM sistema_modulo_objetos SMO
  LEFT JOIN sistema_objetos SO ON SO.id_objetos = SMO.id_objetos
  WHERE (SMO.Activo = 'S' OR SMO.Activo IS NULL);

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
