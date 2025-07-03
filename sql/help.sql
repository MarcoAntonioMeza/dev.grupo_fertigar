select
    `producto`.`id` AS `id`,
    `producto`.`clave` AS `clave`,
    `producto`.`avatar` AS `avatar`,
    `producto`.`nombre` AS `nombre`,
    `producto`.`descripcion` AS `descripcion`,
    `producto`.`is_subproducto` AS `is_subproducto`,
    `producto`.`sub_cantidad_equivalente` AS `sub_cantidad_equivalente`,
    `producto`.`sub_producto_id` AS `sub_producto_id`,
    `sub_producto`.`nombre` AS `sub_producto_nombre`,
    `producto`.`categoria_id` AS `categoria_id`,
    `categoria`.`singular` AS `categoria`,
(
        select
            sum(`inv_producto_sucursal`.`cantidad`)
        from
            `inv_producto_sucursal`
        where
            (
                `inv_producto_sucursal`.`producto_id` = `producto`.`id`
            )
    ) AS `stock_total`,
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