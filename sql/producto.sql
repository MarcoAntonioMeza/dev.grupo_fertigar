-- ADD S


ALTER TABLE `producto` 
ADD COLUMN `unidad_medida_id` INT UNSIGNED NULL AFTER `nombre`;

ALTER TABLE `producto` 
ADD COLUMN `peso_aprox` DECIMAL(6,2) UNSIGNED 0 AFTER `nombre`;

-- Agrega precio_sub con valor por defecto 0
ALTER TABLE `producto` 
ADD COLUMN `precio_sub` DECIMAL(10,3) DEFAULT 0 AFTER `precio_publico`;
-- Comisiones
ALTER TABLE `producto` 
ADD COLUMN `comision_publico` DECIMAL(6,3) DEFAULT 0 AFTER `precio_publico`;

ALTER TABLE `producto` 
ADD COLUMN `comision_mayoreo` DECIMAL(6,3) DEFAULT 0 AFTER `precio_mayoreo`;

ALTER TABLE `producto`
ADD COLUMN `comision_sub` DECIMAL(6,3) DEFAULT 0 AFTER `precio_sub`;

ALTER TABLE `producto`
ADD COLUMN `iva` DECIMAL(6,3) DEFAULT 0 AFTER `nombre`;

ALTER TABLE `producto`
ADD COLUMN `ieps` DECIMAL(6,3) DEFAULT 0 AFTER `nombre`;


CREATE TABLE IF NOT EXISTS `unidadsat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `created_at` bigint DEFAULT NULL,
  `updated_at` bigint DEFAULT NULL,
  `clave` varchar(10) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `created_by_id` smallint UNSIGNED DEFAULT NULL,
  `updated_by_id` smallint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_clave` (`clave`),
  KEY `fk_unidadsat_created_by` (`created_by_id`),
  KEY `fk_unidadsat_updated_by` (`updated_by_id`),
  CONSTRAINT `fk_unidadsat_created_by` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`),
  CONSTRAINT `fk_unidadsat_updated_by` FOREIGN KEY (`updated_by_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


INSERT INTO `unidadsat` (`id`, `created_at`, `updated_at`, `clave`, `nombre`, `created_by_id`, `updated_by_id`) VALUES
  (1, 1749343290, 1749436864, 'KGM', 'KILOGRAMO', NULL, NULL),
  (2, 1749343315, 1749436859, 'LTR', 'LITRO', NULL, NULL),
  (3, 1749343326, 1749436861, 'H87', 'PIEZA', NULL, NULL),
  (4, 1749343337, 1749436856, 'MTR', 'METRO', NULL, NULL),
  (5, 1749343346, 1749436852, 'TNE', 'TONELADA', NULL, NULL),
  (6, 1749343357, 1749436850, 'E48', 'UNIDAD DE SERVICIO', NULL, NULL),
  (7, 1749343369, 1749436847, 'XBX', 'CAJA', NULL, NULL),
  (8, 1749343377, 1749436845, 'XPK', 'PAQUETE', NULL, NULL),
  (9, 1749343387, 1749436842, 'XBG', 'BOLSA', NULL, NULL);



CREATE TABLE IF NOT EXISTS `regimenfiscal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `created_at` bigint DEFAULT NULL,
  `updated_at` bigint DEFAULT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `created_by_id` smallint UNSIGNED DEFAULT NULL,
  `updated_by_id` smallint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_codigo` (`codigo`),
  KEY `fk_regimenfiscal_created_by` (`created_by_id`),
  KEY `fk_regimenfiscal_updated_by` (`updated_by_id`),
  CONSTRAINT `fk_regimenfiscal_created_by` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`),
  CONSTRAINT `fk_regimenfiscal_updated_by` FOREIGN KEY (`updated_by_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


INSERT INTO `regimenfiscal` (`id`, `created_at`, `updated_at`, `codigo`, `nombre`, `created_by_id`, `updated_by_id`) VALUES
  (1, 1749262071, NULL, '601', 'REGIMEN GENERAL DE LEY PERSONAS MORALES', NULL, NULL),
  (2, 1749262084, NULL, '602', 'RÉGIMEN SIMPLIFICADO DE LEY PERSONAS MORALES', NULL, NULL),
  (3, 1749262107, NULL, '626', 'RÉGIMEN SIMPLIFICADO DE CONFIANZA', NULL, NULL),
  (4, 1749262125, NULL, '625', 'RÉGIMEN DE LAS ACTIVIDADES EMPRESARIALES CON INGRESOS A TRAVÉS DE PLATAFORMAS TECNOLÓGICAS.', NULL, NULL);


