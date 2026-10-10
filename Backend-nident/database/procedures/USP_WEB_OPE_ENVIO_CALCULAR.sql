DROP PROCEDURE IF EXISTS `USP_WEB_OPE_ENVIO_CALCULAR`;

CREATE PROCEDURE `USP_WEB_OPE_ENVIO_CALCULAR`(
    IN p_address_departamento VARCHAR(250),
    IN p_address_distrito VARCHAR(250),
    IN p_rango_min_max DECIMAL(10,3),
    IN p_paquete_medidas VARCHAR(50),
    IN p_id_producto VARCHAR(50)
)
BEGIN
    DECLARE v_precio DECIMAL(10,2) DEFAULT NULL;
    DECLARE v_distrito VARCHAR(100) DEFAULT NULL;
    DECLARE v_rango_min DECIMAL(10,3) DEFAULT NULL;
    DECLARE v_rango_max DECIMAL(10,3) DEFAULT NULL;
    DECLARE v_paquete_medidas VARCHAR(100) DEFAULT NULL;
    DECLARE v_pago_contra_entrega VARCHAR(10) DEFAULT NULL;
    DECLARE v_hora_regresiva VARCHAR(20) DEFAULT NULL;
    DECLARE v_hora_regresiva_descripcion VARCHAR(900) DEFAULT NULL;
    DECLARE v_encontrado INT DEFAULT 0;
    DECLARE v_depto VARCHAR(250);
    DECLARE v_dist VARCHAR(250);

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_encontrado = 0;

    SET v_depto = TRIM(IFNULL(p_address_departamento, ''));
    SET v_dist = TRIM(IFNULL(p_address_distrito, ''));

    IF LOWER(CONVERT(v_depto USING utf8mb4)) IN ('lima', 'provincia de lima') THEN
        SET v_depto = 'Lima Metropolitana';
    END IF;

    SELECT
        pp.pago_contra_entrega,
        pp.hora_regresiva,
        pp.hora_regresiva_descripcion,
        pp.address_distrito,
        pp.rango_min,
        pp.rango_max,
        pp.precio,
        pp.paquete_medidas,
        1 AS encontrado
    INTO
        v_pago_contra_entrega,
        v_hora_regresiva,
        v_hora_regresiva_descripcion,
        v_distrito,
        v_rango_min,
        v_rango_max,
        v_precio,
        v_paquete_medidas,
        v_encontrado
    FROM oferta_precios_peso pp
    WHERE
        (
            CONVERT(pp.address_departamento USING utf8mb4) = CONVERT(v_depto USING utf8mb4)
            OR (
                LOWER(CONVERT(TRIM(pp.address_departamento) USING utf8mb4)) IN ('lima', 'lima metropolitana', 'provincia de lima')
                AND LOWER(CONVERT(v_depto USING utf8mb4)) IN ('lima', 'lima metropolitana', 'provincia de lima')
            )
        )
        AND CONVERT(pp.address_distrito USING utf8mb4) = CONVERT(v_dist USING utf8mb4)
        AND (p_rango_min_max IS NULL OR (p_rango_min_max >= pp.rango_min AND p_rango_min_max <= pp.rango_max))
        AND (
            p_paquete_medidas IS NULL
            OR p_paquete_medidas = ''
            OR p_paquete_medidas = '0'
            OR CONVERT(pp.paquete_medidas USING utf8mb4) = CONVERT(p_paquete_medidas USING utf8mb4)
        )
    ORDER BY
        CASE WHEN p_rango_min_max IS NOT NULL THEN pp.rango_max - pp.rango_min ELSE 999999 END ASC
    LIMIT 1;

    SELECT
        (SELECT contacto_direccion FROM web_footer WHERE id_footer = 1) AS contacto_direccion,
        CASE
            WHEN v_encontrado = 0 THEN 'NO_DISPONIBLE'
            WHEN v_pago_contra_entrega = '1' THEN 1
            ELSE NULL
        END AS pago_contra_entrega,
        v_hora_regresiva AS hora_regresiva,
        v_hora_regresiva_descripcion AS hora_regresiva_descripcion,
        v_distrito AS distrito,
        v_depto AS address_departamento,
        v_rango_min AS rango_minimo,
        v_rango_max AS rango_maximo,
        v_precio AS precio_envio,
        v_paquete_medidas AS tipo_paquete,
        CASE
            WHEN v_encontrado = 0 THEN 'NO_ENCONTRADO'
            WHEN p_rango_min_max IS NOT NULL AND v_rango_min IS NOT NULL
                 AND p_rango_min_max BETWEEN v_rango_min AND v_rango_max
            THEN 'OK'
            WHEN p_rango_min_max IS NOT NULL AND v_rango_min IS NULL
            THEN 'SIN_COBERTURA'
            ELSE 'FUERA_DE_RANGO'
        END AS estado_rango;
END
