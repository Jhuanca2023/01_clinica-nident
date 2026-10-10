-- Asegura que la tabla datatreeview tenga columnas para objetos (nivel 2).
-- Ejecutar solo si el SP falla con "Unknown column 'id_menu_objetos'" (o 'IdObjeto').
-- Ejecutar en la BD que usa la app (ej. bd_analisis).

-- Si falta id_menu_objetos:
-- ALTER TABLE datatreeview ADD COLUMN id_menu_objetos INT DEFAULT NULL AFTER id_menu;

-- Si falta IdObjeto:
-- ALTER TABLE datatreeview ADD COLUMN IdObjeto INT DEFAULT NULL AFTER id_menu_objetos;
