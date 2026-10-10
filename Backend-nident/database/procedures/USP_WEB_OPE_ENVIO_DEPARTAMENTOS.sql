DROP PROCEDURE IF EXISTS `USP_WEB_OPE_ENVIO_DEPARTAMENTOS`;

CREATE PROCEDURE `USP_WEB_OPE_ENVIO_DEPARTAMENTOS`()
BEGIN
    SELECT DISTINCT
        address_departamento AS address_departamento
    FROM oferta_precios_peso
    ORDER BY address_departamento;
END
