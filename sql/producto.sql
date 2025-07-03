-- ADD S
ALTER TABLE
  `producto`
ADD
  COLUMN `unidad_medida_id` INT UNSIGNED NULL
AFTER
  `nombre`;

ALTER TABLE
  `producto`
ADD
  COLUMN `peso_aprox` DECIMAL(6, 2) UNSIGNED 0
AFTER
  `nombre`;

-- Agrega precio_sub con valor por defecto 0
ALTER TABLE
  `producto`
ADD
  COLUMN `precio_sub` DECIMAL(10, 3) DEFAULT 0
AFTER
  `precio_publico`;

-- Comisiones
ALTER TABLE
  `producto`
ADD
  COLUMN `comision_publico` DECIMAL(6, 3) DEFAULT 0
AFTER
  `precio_publico`;

ALTER TABLE
  `producto`
ADD
  COLUMN `comision_mayoreo` DECIMAL(6, 3) DEFAULT 0
AFTER
  `precio_mayoreo`;

ALTER TABLE
  `producto`
ADD
  COLUMN `comision_sub` DECIMAL(6, 3) DEFAULT 0
AFTER
  `precio_sub`;

ALTER TABLE
  `producto`
ADD
  COLUMN `iva` DECIMAL(6, 3) DEFAULT 0
AFTER
  `nombre`;

ALTER TABLE
  `producto`
ADD
  COLUMN `ieps` DECIMAL(6, 3) DEFAULT 0
AFTER
  `nombre`;

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
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

INSERT INTO
  `unidadsat` (
    `id`,
    `created_at`,
    `updated_at`,
    `clave`,
    `nombre`,
    `created_by_id`,
    `updated_by_id`
  )
VALUES
  (
    1,
    1749343290,
    1749436864,
    'KGM',
    'KILOGRAMO',
    NULL,
    NULL
  ),
  (
    2,
    1749343315,
    1749436859,
    'LTR',
    'LITRO',
    NULL,
    NULL
  ),
  (
    3,
    1749343326,
    1749436861,
    'H87',
    'PIEZA',
    NULL,
    NULL
  ),
  (
    4,
    1749343337,
    1749436856,
    'MTR',
    'METRO',
    NULL,
    NULL
  ),
  (
    5,
    1749343346,
    1749436852,
    'TNE',
    'TONELADA',
    NULL,
    NULL
  ),
  (
    6,
    1749343357,
    1749436850,
    'E48',
    'UNIDAD DE SERVICIO',
    NULL,
    NULL
  ),
  (
    7,
    1749343369,
    1749436847,
    'XBX',
    'CAJA',
    NULL,
    NULL
  ),
  (
    8,
    1749343377,
    1749436845,
    'XPK',
    'PAQUETE',
    NULL,
    NULL
  ),
  (
    9,
    1749343387,
    1749436842,
    'XBG',
    'BOLSA',
    NULL,
    NULL
  );

ALTER TABLE
  producto DROP COLUMN tipo;

ALTER TABLE
  producto DROP COLUMN tipo_medida;

ALTER TABLE
  producto DROP COLUMN pertenece_a;

--------------------------------------------------------------
--  view 

DROP view if exists `producto_view`;

CREATE VIEW `producto_view` AS
select
  `producto`.`id` AS `id`,
  `producto`.`clave` AS `clave`,
  `producto`.`avatar` AS `avatar`,
  `producto`.`nombre` AS `nombre`,
  `producto`.`descripcion` AS `descripcion`,
 

  `producto`.`is_app` AS `is_app`,
  `producto`.`validate` AS `validate`,
 
  `producto`.`is_subproducto` AS `is_subproducto`,
  `producto`.`sub_cantidad_equivalente` AS `sub_cantidad_equivalente`,
  `producto`.`sub_producto_id` AS `sub_producto_id`,
  `sub_producto`.`nombre` AS `sub_producto_nombre`,
  `producto`.`categoria_id` AS `categoria_id`,
  `categoria`.`singular` AS `categoria`,
  if(
    (`producto`.`is_app` = 10),
    unix_timestamp(
      (
        from_unixtime(`producto`.`created_at`) + interval 7 day
      )
    ),
    0
  ) AS `fecha_autorizar`,
  `producto`.`validate_create_at` AS `validate_create_at`,
  `producto`.`inventariable` AS `inventariable`,
  `producto`.`costo` AS `costo`,
  `producto`.`precio_publico` AS `precio_publico`,
  `producto`.`precio_mayoreo` AS `precio_mayoreo`,
  `producto`.`precio_sub` AS `precio_sub`,
  `producto`.`descuento` AS `descuento`,
  `producto`.`stock_minimo` AS `stock_minimo`,
  `producto`.`status` AS `status`,
  concat_ws(' ', `created`.`nombre`, `created`.`apellidos`) AS `created_by_user`,
  `producto`.`created_by` AS `created_by`,
  `producto`.`created_at` AS `created_at`,
  `producto`.`updated_by` AS `updated_by`,
  `producto`.`updated_at` AS `updated_at`,
  concat_ws(' ', `updated`.`nombre`, `updated`.`apellidos`) AS `updated_by_user`
from
  (
    (
      (
        (
          `producto`
          join `esys_lista_desplegable` `categoria` on((`producto`.`categoria_id` = `categoria`.`id`))
        )
        left join `producto` `sub_producto` on(
          (
            `producto`.`sub_producto_id` = `sub_producto`.`id`
          )
        )
      )
      left join `user` `created` on((`producto`.`created_by` = `created`.`id`))
    )
    left join `user` `updated` on((`producto`.`updated_by` = `updated`.`id`))
  )