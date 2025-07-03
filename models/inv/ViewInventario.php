<?php
namespace app\models\inv;

use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\inv\Operacion;
use app\models\trans\TransProductoInventario;


/**
 * This is the model class for table "view_inventario".
 *
 * @property int|null $id ID
 * @property string|null $clave Clave
 * @property string|null $avatar Avatar
 * @property string|null $nombre Nombre
 * @property string|null $descripcion Descripcion
 * @property int|null $is_subproducto Sub Producto
 * @property float|null $sub_cantidad_equivalente Cantidad Equivalente
 * @property int|null $sub_producto_id Sub Producto ID
 * @property string|null $sub_producto_nombre Nombre
 * @property int|null $categoria_id Categoria ID
 * @property string $categoria Singular
 * @property int|null $proveedor_id Proveedor ID
 * @property string|null $proveedor Nombre
 * @property int|null $almacen_id Almacen ID
 * @property string|null $almacen
 * @property int|null $seccion_id Seccion
 * @property float|null $stock_total
 * @property string|null $seccion Singular
 * @property int|null $inventariable Inventariable
 * @property float|null $costo Costo
 * @property float|null $precio_publico Precio publico
 * @property float|null $precio_mayoreo Precio mayoreo
 * @property float|null $precio_sub Precio sub
 * @property int|null $descuento Descuento
 * @property int|null $stock_minimo Stock minimo
 * @property int|null $status Estatus
 * @property string|null $created_by_user
 * @property int|null $created_by Creado por
 * @property int|null $created_at Creado
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 * @property string|null $updated_by_user
 */
class ViewInventario extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_inventario';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clave' => 'Clave',
            'avatar' => 'Avatar',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripcion',
            
            'is_subproducto' => 'Is Subproducto',
            'sub_cantidad_equivalente' => 'Sub Cantidad Equivalente',
            'sub_producto_id' => 'Sub Producto ID',
            'sub_producto_nombre' => 'Sub Producto Nombre',
            'categoria_id' => 'Categoria ID',
            'categoria' => 'Categoria',
            'stock_total' => 'Stock Total',
            'inventariable' => 'Inventariable',
            'costo' => 'Costo',
            'precio_publico' => 'Precio Publico',
            'precio_mayoreo' => 'Precio Mayoreo',
            'precio_sub' => 'Precio Menudeo',
            'descuento' => 'Descuento',
            'stock_minimo' => 'Stock Minimo',
            'status' => 'Status',
            'created_by_user' => 'Created By User',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
            'updated_by_user' => 'Updated By User',
        ];
    }

    //------------------------------------------------------------------------------------------------//
// JSON Bootstrap Table
//------------------------------------------------------------------------------------------------//
    public static function getJsonBtt($arr)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort'])?   $arr['sort']:   'id';
        $order   = isset($arr['order'])?  $arr['order']:  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset'])? $arr['offset']: 0;
        $limit   = isset($arr['limit'])?  $arr['limit']:  50;

        $search = isset($arr['search'])? $arr['search']: false;
        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/
            $query = (new Query())
                ->select([
                    "SQL_CALC_FOUND_ROWS `id`",
                    'clave',
                    'avatar',
                    'nombre',
                    'descripcion',
                    
                    'categoria_id',
                    'categoria',
                    'inventariable',
                    'costo',
                    'precio_publico',
                    'precio_mayoreo',
                    'precio_sub',
                    'descuento',
                    'stock_total',
                    'stock_minimo',
                    'status',
                    'created_by_user',
                    'created_by',
                    'created_at',
                    'updated_by',
                    'updated_at',
                    'updated_by_user',
                ])
                ->from(self::tableName())
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);

            /**==============================================
             * Filtamos por asiganacion agente de ventas
             ================================================*/


            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'nombre', $search],
                ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

    public static function getBalanceJsonBtt($arr)
    {
          // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort'])?   $arr['sort']:   'id';
        $order   = isset($arr['order'])?  $arr['order']:  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset'])? $arr['offset']: 0;
        $limit   = isset($arr['limit'])?  $arr['limit']:  50;

        $search = isset($arr['search'])? $arr['search']: false;
        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                "producto.`id`",
                "sucursal.nombre as sucursal",
                'producto.clave',
                'producto.avatar',
                'producto.nombre',
                'producto.descripcion',
              
                'producto.costo',
                'producto.precio_publico',
                'producto.precio_mayoreo',
                'producto.precio_sub',
                'producto.status',
                'inv_producto_sucursal.cantidad as stock_total'
            ])
            ->from("producto")
            ->leftJoin("inv_producto_sucursal","producto.id = inv_producto_sucursal.producto_id")
            ->leftJoin("sucursal","inv_producto_sucursal.sucursal_id = sucursal.id");

            /**==============================================
             * Filtamos por asiganacion agente de ventas
             ================================================*/
            if (isset($filters["sucursal_id"]) && $filters["sucursal_id"]) {
                $query->andWhere(["and",
                    ["=",'inv_producto_sucursal.sucursal_id', $filters["sucursal_id"] ],
                ]);
            }

            if (isset($filters["existencia"]) && $filters["existencia"] ) {
                if ($filters["existencia"] == 1)
                     $query->andWhere(["or",
                            [">",'cantidad', 0 ],
                    ]);

                if ($filters["existencia"] == 10)
                     $query->andWhere(["or",
                            ["<=",'cantidad', 0 ],
                            ["IS",'cantidad', new \yii\db\Expression('null') ]
                    ]);

                if ($filters["existencia"] == 20)
                    $query->andWhere(["and",
                            [">",'cantidad', 0 ],
                            ["<",'cantidad', 6 ]
                    ]);

                if ($filters["existencia"] == 30)
                    $query->andWhere(["and",
                            [">",'cantidad', 5 ]
                    ]);
            }

        if($search)
            $query->andFilterWhere([
                'or',
                ['like', 'producto.clave', $search],
                ['like', 'producto.nombre', $search],
            ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        //die();

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];

    }

    public static function getInvRuta($sucursal_id)
    {
         // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;


        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                "producto.`id`",
                'producto.clave',
                'producto.avatar',
                'producto.nombre',
                'producto.descripcion',
                
                'producto.costo',
                'producto.precio_publico',
                'producto.precio_mayoreo',
                'producto.precio_sub',
                'producto.status',
                'inv_producto_sucursal.cantidad'
            ])
            ->from("producto")
            ->innerJoin("inv_producto_sucursal","producto.id = inv_producto_sucursal.producto_id")
            ->innerJoin("sucursal","inv_producto_sucursal.sucursal_id = sucursal.id");

            /**==============================================
             * Filtamos por asiganacion agente de ventas
             ================================================*/
        $query->andWhere(["and",
            ["=",'inv_producto_sucursal.sucursal_id', $sucursal_id ],
            [">",'cantidad', 0 ]
        ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return $query->all();
    }


    public static function getHistoryMovJsonBtt($arr)
    {
          // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort'])?   $arr['sort']:   'id';
        $order   = isset($arr['order'])?  $arr['order']:  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset'])? $arr['offset']: 0;
        $limit   = isset($arr['limit'])?  $arr['limit']:  50;

        $search = isset($arr['search'])? $arr['search']: false;
        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/


        $select = [
            "SQL_CALC_FOUND_ROWS `trans_producto_inventario`.`id`",
            "trans_producto_inventario.venta_detalle_id",
            "trans_producto_inventario.operacion_detalle_id",
            "operacion_detalle.operacion_id",
            "trans_producto_inventario.transformacion_detalle_id",
            "trans_producto_inventario.sucursal_id",
            "sucursal.nombre as origen",
            "(CASE
                WHEN trans_producto_inventario.tipo  = 10
                    THEN concat_ws(' ', 'VENTA #', (SELECT venta_detalle.venta_id FROM venta_detalle  where venta_detalle.id = trans_producto_inventario.venta_detalle_id limit 1))
                WHEN trans_producto_inventario.tipo  = 15
                    THEN concat_ws(' ', 'VENTA #', (SELECT temp_venta_ruta.venta_id FROM temp_venta_ruta  INNER JOIN temp_venta_ruta_detalle on temp_venta_ruta.id = temp_venta_ruta_detalle.temp_venta_ruta_id   where temp_venta_ruta_detalle.id = trans_producto_inventario.temp_venta_ruta_detalle_id limit 1))
                WHEN trans_producto_inventario.tipo  = 20
                    THEN concat_ws(' ', 'OPERACION #', (SELECT operacion.id FROM operacion  INNER JOIN operacion_detalle on operacion.id = operacion_detalle.operacion_id   where operacion_detalle.id = trans_producto_inventario.operacion_detalle_id limit 1))
                WHEN trans_producto_inventario.tipo  = 30
                    THEN concat_ws(' ', 'TRANSFORMACION #', (SELECT tranformacion_devolucion.id FROM tranformacion_devolucion  INNER JOIN tranformacion_devolucion_detalle on tranformacion_devolucion.id = tranformacion_devolucion_detalle.tranformacion_devolucion_id   where tranformacion_devolucion_detalle.id = trans_producto_inventario.transformacion_detalle_id limit 1))
                WHEN trans_producto_inventario.tipo  = 40
                    THEN concat_ws(' ', 'REPARTO #',(SELECT reparto.id FROM reparto  INNER JOIN reparto_detalle on reparto.id = reparto_detalle.reparto_id   where reparto_detalle.id = trans_producto_inventario.reparto_detalle_id limit 1))
                WHEN trans_producto_inventario.tipo  = 50
                    THEN 'AJUSTE DE INVENTARIO'
                WHEN trans_producto_inventario.tipo  = 60
                    THEN 'AJUSTE A PREVENTA #'
            END) as destino",
            "trans_producto_inventario.producto_id",
            "trans_producto_inventario.tranformacion_id",
            "trans_producto_inventario.tipo",
            "trans_producto_inventario.motivo",
            "trans_producto_inventario.created_by",
            "trans_producto_inventario.created_at",
            "from_unixtime(trans_producto_inventario.created_at, '%Y-%m-%d') as fecha_operacion",
            "concat_ws(' ',`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`",
        ];

        if( isset($filters['agrupar']['traspaso'])) {
            $select = array_merge($select, [
                'SUM( trans_producto_inventario.cantidad  ) as cantidad',
                'trans_producto_inventario.inventario   as inventario',
                '(CASE
                     WHEN trans_producto_inventario.motivo = 20 THEN trans_producto_inventario.inventario - SUM(trans_producto_inventario.cantidad)
                     WHEN trans_producto_inventario.motivo = 10 THEN trans_producto_inventario.inventario + SUM(trans_producto_inventario.cantidad)
                 END) as inventario_new',
            ]);
        }else{
             $select = array_merge($select, [
                "trans_producto_inventario.cantidad",
                "trans_producto_inventario.inventario",
                "(CASE
                     WHEN trans_producto_inventario.motivo = 20 THEN trans_producto_inventario.inventario - trans_producto_inventario.cantidad
                     WHEN trans_producto_inventario.motivo = 10 THEN trans_producto_inventario.inventario + trans_producto_inventario.cantidad
                 END) as inventario_new",
           ]);
        }



        $query = (new Query())
            ->select($select)
            ->from("trans_producto_inventario")
            ->innerJoin("sucursal", "trans_producto_inventario.sucursal_id = sucursal.id")
            ->leftJoin("operacion_detalle", "trans_producto_inventario.operacion_detalle_id = operacion_detalle.id")
            ->leftJoin("user created","trans_producto_inventario.created_by = created.id")
            ->andWhere([ "trans_producto_inventario.producto_id" => $filters["producto_id"] ])
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);


        /************************************
        / Filtramos la consulta
        /***********************************/
        if(isset($filters['date_range']) && $filters['date_range']){
            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

            $query->andWhere(['between','trans_producto_inventario.created_at', $date_ini, $date_fin]);
        }

        if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
            $query->andWhere(['trans_producto_inventario.sucursal_id' =>  $filters['sucursal_id']]);


        if (isset($filters['operacion']) && $filters['operacion'])
            $query->andWhere(['trans_producto_inventario.tipo' =>  $filters['operacion']]);


        if (isset($filters['tipo']) && $filters['tipo'])
            $query->andWhere(['trans_producto_inventario.motivo' =>  $filters['tipo']]);


        if($search)
            $query->andFilterWhere([
                'or',
                ['like', 'producto.clave', $search],
                ['like', 'producto.nombre', $search],
            ]);



        /************************************
        / Agrupamos
        /***********************************/

        $groupBy = [];



        if(isset($filters['agrupar']['traspaso'])){
            /*$groupBy[] = 'trans_producto_inventario.sucursal_id';
            $groupBy[] = 'trans_producto_inventario.motivo';*/

            $groupBy[] = 'operacion_detalle.operacion_id';
            $groupBy[] = 'trans_producto_inventario.tipo';
            $groupBy[] = 'trans_producto_inventario.motivo';
            $groupBy[] = 'trans_producto_inventario.transformacion_detalle_id';
            $groupBy[] = 'trans_producto_inventario.tranformacion_id';
            $groupBy[] = 'trans_producto_inventario.venta_detalle_id';
            $groupBy[] = 'trans_producto_inventario.temp_venta_ruta_detalle_id';
            $groupBy[] = 'fecha_operacion';
        }

        if(count($groupBy) > 0)
            $query->groupBy($groupBy);
            //$query->having(["=", "trans_producto_inventario.tipo", TransProductoInventario::TIPO_OPERACION]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        //die();


        $responseArray  = [];
        foreach ($query->all() as $key => $item_query) {
            if ($item_query["tipo"] == TransProductoInventario::TIPO_OPERACION)
            {
                array_push($responseArray, $item_query);
                $TransProductoInventario  = TransProductoInventario::findOne($item_query["id"]);
                $folio = isset($TransProductoInventario->operacionDetalle->operacion_id) && $TransProductoInventario->operacionDetalle->operacion_id ? $TransProductoInventario->operacionDetalle->operacion_id : null;

                if ($folio) {
                    $operacion = Operacion::findOne($folio);

                    if ($operacion->motivo == Operacion::ENTRADA_MERCANCIA_NUEVA){
                        $responseArray[$key]["origen"] = "COMPRA DE MERCANCIA #" . $operacion->compra_id;
                        $responseArray[$key]["destino"] = $operacion->almacenSucursal->nombre;
                    }

                    if ($operacion->motivo == Operacion::ENTRADA_TRASPASO_RECOLECCION){
                        $responseArray[$key]["origen"] = $operacion->reparto->sucursal->nombre;
                        $responseArray[$key]["destino"] = $operacion->almacenSucursal->nombre;
                    }

                    if ($operacion->motivo == Operacion::ENTRADA_TRASPASO){
                        $responseArray[$key]["origen"] = $operacion->operacionChild->almacenSucursal->nombre;
                        $responseArray[$key]["destino"] = $operacion->almacenSucursal->nombre;
                    }

                    if ($operacion->motivo == Operacion::ENTRADA_RUTA_AJUSTE){
                        $responseArray[$key]["origen"] = $operacion->sucursal_recibe_id ? $operacion->sucursalRecibe->nombre  : '';
                        $responseArray[$key]["destino"] = $operacion->sucursalRecibe->nombre;
                    }

                    if ($operacion->motivo == Operacion::SALIDA_RUTA_AJUSTE){
                        $responseArray[$key]["origen"] =  $operacion->almacen_sucursal_id ? $operacion->almacenSucursal->nombre  : '';
                        $responseArray[$key]["destino"] = $operacion->sucursal_recibe_id ? $operacion->sucursalRecibe->nombre  : '';
                    }

                    if ($operacion->motivo == Operacion::SALIDA_TRASPASO){
                        $responseArray[$key]["origen"]  = $operacion->almacenSucursal->nombre;
                        $responseArray[$key]["destino"] = $operacion->sucursalRecibe->nombre;
                    }

                    if ($operacion->motivo == Operacion::SALIDA_TRASPASO_RECOLECCION){
                        $responseArray[$key]["origen"] = $operacion->reparto_id ? $operacion->reparto->sucursal->nombre : '';
                        $responseArray[$key]["destino"] =  $operacion->operacionChild->almacenSucursal->nombre;
                    }

                    if ($operacion->motivo == Operacion::SALIDA_TRASPASO_UNIDAD){
                        $responseArray[$key]["origen"] = $operacion->almacenSucursal->nombre;
                        $responseArray[$key]["destino"] = $operacion->operacionChild->almacenSucursal->nombre;
                    }

                    if ($operacion->motivo == Operacion::ENTRADA_TRASPASO_UNIDAD){
                        $responseArray[$key]["origen"]  = Operacion::searchOrigenTraspaso($operacion->id);
                        $responseArray[$key]["destino"] = $operacion->almacenSucursal->nombre;
                    }
                }

            }else{
                array_push($responseArray, $item_query);
            }
        }

        $query->all();

        return [
            'rows'  => $responseArray,
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];

    }
}
