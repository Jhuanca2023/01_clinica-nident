-- ============================================================
--  Script: Agregar nuevas columnas para importación de productos
--  Tabla : administracion_producto
--  SGBD  : MySQL 8.1
--  Fecha : 2026-04-03
--
--  Columnas nuevas mapeadas desde el Excel de importación:
--    codigo_barra                <- Excel: codigo_barra
--    ubicacion                   <- Excel: ubicación
--    stock_minimo                <- Excel: stock_minimo
--
--  Columnas ya existentes (no se tocan):
--    codigo_producto_new  -> Excel: codigo_producto
--    nombre               -> Excel: nombre_producto
--    descripcion          -> Excel: descripcion
--    precio               -> Excel: precio_venta
--    precio_old           -> Excel: precio_anterior
--    precio_costo         -> Excel: precio_costo
--    precio_mayorista     -> Excel: precio_mayorista
--    stock                -> Excel: stock
--    peso_kilogramo       -> Excel: peso_kilogramos
--    url_imagen           -> Excel: imagen_principal
--    id_producto_categoria     (resuelto por nombre de categoria)
--    id_producto_categoria_sub (resuelto por nombre de sub_categoria)
--
--  NOTA: imagen_segundaria (Excel col V) NO se agrega aquí.
--        Las rutas separadas por coma se insertan como filas
--        en la tabla administracion_producto_fotos con referencia
--        al id_producto correspondiente.
-- ============================================================

ALTER TABLE `administracion_producto`
    ADD COLUMN `codigo_barra` VARCHAR(100)  NULL COMMENT 'Código de barras del producto'  AFTER `codigo_producto_new`,
    ADD COLUMN `ubicacion`    VARCHAR(255)  NULL COMMENT 'Ubicación física del producto'  AFTER `descripcion`,
    ADD COLUMN `stock_minimo` DECIMAL(10,2) NULL COMMENT 'Stock mínimo permitido'         AFTER `stock`;
