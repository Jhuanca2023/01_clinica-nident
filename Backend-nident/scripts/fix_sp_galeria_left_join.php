<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sql = <<<'SQL'
DROP PROCEDURE IF EXISTS `USP_WEB_PRODUCTO_LISTAR_GALERIA`;
SQL;
DB::unprepared($sql);

$sql = <<<'SQL'
CREATE PROCEDURE `USP_WEB_PRODUCTO_LISTAR_GALERIA`(
    IN `page` INT,
    IN `page_size` INT,
    IN `id_usuario_` INT,
    IN `id_producto_categoria_` VARCHAR(255),
    IN `id_producto_categoria_sub_` VARCHAR(255),
    IN `solo_categorias_` VARCHAR(255),
    IN `orden_` VARCHAR(50),
    IN `busqueda_` VARCHAR(255)
)
BEGIN
    DECLARE offset_ INT;
    SET offset_ = (page - 1) * page_size;

    SET @sql = 'SELECT
        P.id_producto
        , P.nombre AS titulo
        , P.descripcion
        , P.precio
        , P.precio_old
        , P.precio_mayorista
        , P.precio_mayorista_old
        , P.numero_estrellas
        , P.stock
        , P.corte_tiempo_promocion
        , P.precio_yape
        , P.created_at
        , P.Activo
        , PC.nombre AS nombre_categoria
        , P.url_imagen, ';

    IF id_usuario_ = 0 THEN
        SET @sql = CONCAT(@sql, '0 AS id_usuario, 0 AS es_favorito ');
    ELSE
        SET @sql = CONCAT(@sql, id_usuario_, ' AS id_usuario,
            IFNULL((SELECT id_usuario FROM web_favoritos
                   WHERE id_producto = P.id_producto AND id_usuario = ', id_usuario_, '), 0) AS es_favorito ');
    END IF;

    -- LEFT JOIN: productos sin categoría también aparecen en la tienda (sin filtros)
    SET @sql = CONCAT(@sql, 'FROM administracion_producto P
        LEFT JOIN administracion_producto_categoria PC ON P.id_producto_categoria = PC.id_producto_categoria
        WHERE P.Activo = ''S''');

    IF (id_producto_categoria_ IS NOT NULL AND id_producto_categoria_ != '') THEN
        SET @sql = CONCAT(@sql, ' AND P.id_producto_categoria IN (', id_producto_categoria_, ')');

        IF (id_producto_categoria_sub_ IS NOT NULL AND id_producto_categoria_sub_ != '') THEN
            SET @sql = CONCAT(@sql, ' AND P.id_producto_categoria_sub IN (', id_producto_categoria_sub_, ')');
        ELSE
            SET @sql = CONCAT(@sql, ' AND P.id_producto_categoria_sub IS NULL');
        END IF;

    ELSEIF (solo_categorias_ IS NOT NULL AND solo_categorias_ != '') THEN
        SET @sql = CONCAT(@sql, ' AND P.id_producto_categoria IN (', solo_categorias_, ')
                                  AND P.id_producto_categoria_sub IS NULL');
    END IF;

    IF busqueda_ IS NOT NULL AND busqueda_ != '' THEN
        SET @sql = CONCAT(@sql, ' AND (P.nombre LIKE ''%', busqueda_, '%''
                          OR P.descripcion LIKE ''%', busqueda_, '%''
                          OR P.codigo_producto LIKE ''%', busqueda_, '%'')');
    END IF;

    SET @sql = CONCAT(@sql, ' ORDER BY ');
    CASE orden_
        WHEN 'precio_asc' THEN SET @sql = CONCAT(@sql, 'P.precio ASC');
        WHEN 'precio_desc' THEN SET @sql = CONCAT(@sql, 'P.precio DESC');
        WHEN 'mas_vendidos' THEN SET @sql = CONCAT(@sql, 'P.ventas DESC');
        WHEN 'mas_favoritos' THEN SET @sql = CONCAT(@sql, '(SELECT COUNT(*) FROM web_favoritos WF WHERE WF.id_producto = P.id_producto) DESC');
        WHEN 'relevancia' THEN
            IF busqueda_ IS NOT NULL AND busqueda_ != '' THEN
                SET @sql = CONCAT(@sql,
                    'CASE
                        WHEN P.nombre LIKE ''%', busqueda_, '%'' THEN 1
                        WHEN P.descripcion LIKE ''%', busqueda_, '%'' THEN 2
                        WHEN P.codigo_producto LIKE ''%', busqueda_, '%'' THEN 3
                        ELSE 4
                    END, P.id_producto DESC');
            ELSE
                SET @sql = CONCAT(@sql, 'P.id_producto DESC');
            END IF;
        ELSE SET @sql = CONCAT(@sql, 'P.id_producto DESC');
    END CASE;

    SET @sql = CONCAT(@sql, ' LIMIT ', page_size, ' OFFSET ', offset_);

    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END
SQL;

DB::unprepared($sql);
echo "SP USP_WEB_PRODUCTO_LISTAR_GALERIA updated (LEFT JOIN)\n";
