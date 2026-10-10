-- =============================================
-- MÓDULO: Administrador de Pasarelas de Pago
-- Base de datos: MySQL 8.1
-- Prefijo de tablas: pago_
-- =============================================

-- ─────────────────────────────────────────────
-- TABLA 1: pago_pasarela
-- Registra las pasarelas disponibles en el sistema
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS pago_pasarela (
    id_pago_pasarela  INT              NOT NULL AUTO_INCREMENT,
    codigo            VARCHAR(50)      NOT NULL                  COMMENT 'Identificador único de la pasarela: culqi | izipay',
    nombre            VARCHAR(100)     NOT NULL                  COMMENT 'Nombre legible: Culqi, Izipay',
    descripcion       TEXT             NULL                      COMMENT 'Descripción visible en el panel de admin',
    logo_url          VARCHAR(255)     NULL                      COMMENT 'Ruta al logo (assets/images/gateways/...)',
    is_activo         TINYINT(1)       NOT NULL DEFAULT 0        COMMENT '1 = pasarela seleccionada para procesar cobros',
    modo              VARCHAR(20)      NOT NULL DEFAULT 'produccion' COMMENT 'Modo activo del checkout: produccion | test',
    orden             INT              NOT NULL DEFAULT 0        COMMENT 'Orden de visualización en el panel',
    created_at        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_pago_pasarela),
    UNIQUE KEY uq_pago_pasarela_codigo (codigo)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Pasarelas de pago disponibles en el sistema';


-- ─────────────────────────────────────────────
-- TABLA 2: pago_pasarela_config
-- Claves de configuración (API keys) por pasarela
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS pago_pasarela_config (
    id_pago_pasarela_config INT          NOT NULL AUTO_INCREMENT,
    id_pago_pasarela        INT          NOT NULL                  COMMENT 'FK → pago_pasarela',
    clave                   VARCHAR(100) NOT NULL                  COMMENT 'Nombre de la variable: CULQI_PUBLIC_KEY, IZIPAY_USERNAME, etc.',
    valor                   TEXT         NULL                      COMMENT 'Claves de PRODUCCIÓN',
    valor_test              TEXT         NULL                      COMMENT 'Claves de PRUEBA (test)',
    etiqueta                VARCHAR(150) NULL                      COMMENT 'Nombre amigable para mostrar en la UI',
    es_secreto              TINYINT(1)   NOT NULL DEFAULT 0        COMMENT '1 = campo sensible (ocultar en frontend)',
    created_at              TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_pago_pasarela_config),
    UNIQUE KEY uq_pasarela_clave (id_pago_pasarela, clave),
    CONSTRAINT fk_pago_config_pasarela
        FOREIGN KEY (id_pago_pasarela)
        REFERENCES pago_pasarela (id_pago_pasarela)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Claves de configuración por pasarela de pago';


-- =============================================
-- SEED: Datos iniciales
-- =============================================

-- 1. Pasarelas disponibles (Culqi activa por defecto)
INSERT INTO pago_pasarela (codigo, nombre, descripcion, logo_url, is_activo, orden)
VALUES
(
    'culqi',
    'Culqi',
    'Pasarela de pagos peruana. Acepta tarjetas Visa, Mastercard, Yape, billeteras digitales, banca móvil y cuotéalo.',
    '/assets/images/gateways/culqi.png',
    1,
    1
),
(
    'izipay',
    'Izipay',
    'Pasarela con integración embebida Pop-In (micuentaweb.pe). Acepta tarjetas de crédito/débito y pagos Yape.',
    '/assets/images/gateways/izipay.png',
    0,
    2
);


-- 2. Claves de configuración para Culqi
INSERT INTO pago_pasarela_config (id_pago_pasarela, clave, valor, etiqueta, es_secreto)
SELECT id_pago_pasarela, 'CULQI_PUBLIC_KEY',     NULL, 'Public Key',         0 FROM pago_pasarela WHERE codigo = 'culqi';

INSERT INTO pago_pasarela_config (id_pago_pasarela, clave, valor, etiqueta, es_secreto)
SELECT id_pago_pasarela, 'CULQI_SECRET_KEY',     NULL, 'Secret Key',         1 FROM pago_pasarela WHERE codigo = 'culqi';

INSERT INTO pago_pasarela_config (id_pago_pasarela, clave, valor, etiqueta, es_secreto)
SELECT id_pago_pasarela, 'CULQI_RSA_ID',         NULL, 'RSA ID',             0 FROM pago_pasarela WHERE codigo = 'culqi';

INSERT INTO pago_pasarela_config (id_pago_pasarela, clave, valor, etiqueta, es_secreto)
SELECT id_pago_pasarela, 'CULQI_RSA_PUBLIC_KEY', NULL, 'RSA Public Key',     1 FROM pago_pasarela WHERE codigo = 'culqi';

INSERT INTO pago_pasarela_config (id_pago_pasarela, clave, valor, etiqueta, es_secreto)
SELECT id_pago_pasarela, 'CULQI_WEBHOOK_SECRET', NULL, 'Webhook Secret',     1 FROM pago_pasarela WHERE codigo = 'culqi';


-- 3. Claves de configuración para Izipay
INSERT INTO pago_pasarela_config (id_pago_pasarela, clave, valor, etiqueta, es_secreto)
SELECT id_pago_pasarela, 'IZIPAY_USERNAME',   NULL,       'Username (Merchant ID)',       0 FROM pago_pasarela WHERE codigo = 'izipay';

INSERT INTO pago_pasarela_config (id_pago_pasarela, clave, valor, etiqueta, es_secreto)
SELECT id_pago_pasarela, 'IZIPAY_PASSWORD',   NULL,       'Password',                     1 FROM pago_pasarela WHERE codigo = 'izipay';

INSERT INTO pago_pasarela_config (id_pago_pasarela, clave, valor, etiqueta, es_secreto)
SELECT id_pago_pasarela, 'IZIPAY_PUBLIC_KEY', NULL,       'Public Key',                   0 FROM pago_pasarela WHERE codigo = 'izipay';

INSERT INTO pago_pasarela_config (id_pago_pasarela, clave, valor, etiqueta, es_secreto)
SELECT id_pago_pasarela, 'IZIPAY_SHA256_KEY', NULL,       'SHA-256 Key (HMAC Signature)', 1 FROM pago_pasarela WHERE codigo = 'izipay';

INSERT INTO pago_pasarela_config (id_pago_pasarela, clave, valor, etiqueta, es_secreto)
SELECT id_pago_pasarela, 'IZIPAY_ENV',        'sandbox',  'Entorno (sandbox | production)', 0 FROM pago_pasarela WHERE codigo = 'izipay';


-- =============================================
-- REPARACIÓN (tablas ya creadas sin modo / valor_test)
-- artisan-clear.php también aplica esto vía PagoPasarelaSchema
-- =============================================
-- ALTER TABLE pago_pasarela
--   ADD COLUMN modo VARCHAR(20) NOT NULL DEFAULT 'produccion' AFTER is_activo;
-- ALTER TABLE pago_pasarela_config
--   ADD COLUMN valor_test TEXT NULL AFTER valor;

-- =============================================
-- VERIFICACIÓN
-- =============================================
SELECT
    pp.id_pago_pasarela,
    pp.codigo,
    pp.nombre,
    pp.is_activo,
    COUNT(pc.id_pago_pasarela_config) AS total_claves
FROM pago_pasarela pp
LEFT JOIN pago_pasarela_config pc ON pc.id_pago_pasarela = pp.id_pago_pasarela
GROUP BY pp.id_pago_pasarela;
