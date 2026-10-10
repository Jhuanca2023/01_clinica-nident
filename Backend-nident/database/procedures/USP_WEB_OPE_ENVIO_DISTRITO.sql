DROP PROCEDURE IF EXISTS `USP_WEB_OPE_ENVIO_DISTRITO`;

CREATE PROCEDURE `USP_WEB_OPE_ENVIO_DISTRITO`(
    IN p_address_departamento VARCHAR(255)
)
BEGIN
    DECLARE v_depto VARCHAR(255);

    SET v_depto = TRIM(IFNULL(p_address_departamento, ''));

    IF v_depto = '' THEN
        SELECT DISTINCT address_distrito AS address_distrito
        FROM oferta_precios_peso
        WHERE 1 = 0;
    ELSE
        IF LOWER(CONVERT(v_depto USING utf8mb4)) IN ('lima', 'provincia de lima') THEN
            SET v_depto = 'Lima Metropolitana';
        END IF;

        SELECT DISTINCT
            opp.address_distrito AS address_distrito
        FROM oferta_precios_peso opp
        WHERE
            CONVERT(opp.address_departamento USING utf8mb4) = CONVERT(v_depto USING utf8mb4)
            OR (
                LOWER(CONVERT(TRIM(opp.address_departamento) USING utf8mb4)) IN ('lima', 'lima metropolitana', 'provincia de lima')
                AND LOWER(CONVERT(v_depto USING utf8mb4)) IN ('lima', 'lima metropolitana', 'provincia de lima')
            )
        ORDER BY opp.address_distrito;
    END IF;
END
