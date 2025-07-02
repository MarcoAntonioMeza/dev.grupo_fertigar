ALTER TABLE `cliente`
ADD COLUMN `regimen_fiscal_id` INT DEFAULT NULL AFTER `nombre`;
ALTER TABLE `cliente`
ADD COLUMN `pais` INT DEFAULT NULL AFTER `nombre`;

ALTER TABLE `cliente`
ADD COLUMN `uso_cfdi` VARCHAR(10) DEFAULT NULL AFTER `nombre`;

ALTER TABLE `cliente`
ADD COLUMN `agente_id` smallint UNSIGNED DEFAULT NULL AFTER `nombre`;

ALTER TABLE `cliente`
ADD COLUMN `lista_precios` int DEFAULT 10 AFTER `nombre`;
