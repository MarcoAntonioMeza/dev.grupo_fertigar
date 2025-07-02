
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
