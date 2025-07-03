<?php
namespace app\models\producto;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_producto".
 *
 * @property int $id ID
 * @property string $clave Clave
 * @property string|null $avatar Avatar
 * @property string $nombre Nombre

 * @property int $categoria_id Categoria ID
 * @property string $categoria Singular
 * @property int|null $proveedor_id Proveedor ID
 * @property string|null $proveedor Nombre
 * @property int $almacen_id Almacen ID
 * @property string|null $almacen
 * @property int $seccion_id Seccion
 * @property string|null $seccion Singular
 * @property int|null $inventariable Inventariable
 * @property float $costo Costo
 * @property float $precio_publico Precio publico
 * @property float $precio_mayoreo Precio mayoreo
 * @property float|null $precio_sub Precio menudeo
 * @property int|null $descuento Descuento
 * @property int|null $stock_minimo Stock minimo
 * @property int $status Estatus
 * @property string|null $created_by_user
 * @property int $created_by Creado por
 * @property int $created_at Creado
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 * @property string|null $updated_by_user
 */
class ViewProducto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_producto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'categoria_id', 'proveedor_id', 'almacen_id', 'seccion_id', 'inventariable', 'descuento', 'stock_minimo', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['clave', 'nombre',  'categoria_id', 'categoria', 'almacen_id', 'seccion_id', 'costo', 'precio_publico', 'precio_mayoreo', 'status', 'created_by', 'created_at'], 'required'],
            [['descripcion'], 'string'],
            [['costo', 'precio_publico', 'precio_mayoreo', 'precio_sub'], 'number'],
            [['clave'], 'string', 'max' => 8],
            [['avatar', 'nombre', 'proveedor'], 'string', 'max' => 150],
            [['categoria', 'seccion'], 'string', 'max' => 128],
            [['almacen'], 'string', 'max' => 200],
            [['created_by_user', 'updated_by_user'], 'string', 'max' => 201],
        ];
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
            
            'categoria_id' => 'Categoria ID',
            'categoria' => 'Categoria',
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
                    'stock_minimo',
                    'status',
                    'validate',
                    'fecha_autorizar',
                    'validate_create_at',
                    'is_app',
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


            if (isset($filters['status']) && $filters['status'])
                $query->andWhere(['status' =>  $filters['status']]);


            if (isset($filters['categoria_id']) && $filters['categoria_id'])
                $query->andWhere(['categoria_id' =>  $filters['categoria_id']]);


            if (isset($filters['permisos']) && $filters['permisos'])
                $query->andWhere(['validate' =>  $filters['permisos']]);

            if (isset($filters['permisos_reporte']) && $filters['permisos_reporte']){
                if ($filters['permisos_reporte'] == 10)
                    $query->andWhere(['validate' =>  Producto::VALIDATE_OFF ]);

                if ($filters['permisos_reporte'] == 20) {
                    $query->andWhere(["and",
                        ['=', 'validate', Producto::VALIDATE_OFF ],
                        ['<', 'fecha_autorizar', time() ],
                    ]);
                }

                if ($filters['permisos_reporte'] == 30)
                    $query->andWhere(['validate' =>  Producto::VALIDATE_ON ]);


            }

            if (isset($filters['is_app']) && $filters['is_app'])
                $query->andWhere(['is_app' =>  $filters['is_app']]);


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


    public static function getReporteVentaProductoJsonBtt($arr)
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
                "SQL_CALC_FOUND_ROWS `producto`.`id`",
                'producto.clave',
                'producto.nombre',
                'producto.costo',
                'producto.precio_publico',
                'producto.precio_mayoreo',
                'producto.precio_sub',
            ];


            if ((isset($filters['sucursal_id']) && $filters['sucursal_id']) &&  !$filters['date_range'] ) {


                $select = array_merge($select, [
                    'COALESCE((SELECT SUM(venta_detalle.precio_venta * venta_detalle.cantidad)  FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE venta.sucursal_id = '. $filters['sucursal_id'] .' AND venta_detalle.producto_id = producto.id AND (venta.status = 10 OR venta.status = 1 ) AND venta.ruta_sucursal_id IS null ),0) + COALESCE((SELECT SUM(venta_detalle.precio_venta * venta_detalle.cantidad)  FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE venta.ruta_sucursal_id = '. $filters['sucursal_id'] .' AND venta_detalle.producto_id = producto.id AND (venta.status = 10 OR venta.status = 1 ) ),0)  as total_ingreso',
                    'COALESCE((SELECT SUM(venta_detalle.cantidad) FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE venta.sucursal_id = '. $filters['sucursal_id'] .' AND venta_detalle.producto_id = producto.id and (venta.status = 10 OR venta.status = 1 ) AND venta.ruta_sucursal_id IS null ),0) + COALESCE((SELECT SUM(venta_detalle.cantidad) FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE venta.ruta_sucursal_id = '. $filters['sucursal_id'] .' AND venta_detalle.producto_id = producto.id and (venta.status = 10 OR venta.status = 1 )  ),0)  as total_kilogramos'
                ]);

            }else if ((isset($filters['date_range']) && $filters['date_range']) &&  !$filters['sucursal_id'] ) {

                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $select = array_merge($select, [
                    '(SELECT SUM(venta_detalle.precio_venta * venta_detalle.cantidad)  FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE  venta_detalle.producto_id = producto.id AND (venta.status = 10 OR venta.status = 1 ) AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.' ) as total_ingreso',
                    '(SELECT SUM(venta_detalle.cantidad) FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE venta_detalle.producto_id = producto.id and venta.status  = 10 AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.') as total_kilogramos'
                ]);

            }else if( (isset($filters['sucursal_id']) && $filters['sucursal_id']) && (isset($filters['date_range']) && $filters['date_range']) ){

                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $select = array_merge($select, [
                    'COALESCE((SELECT SUM(venta_detalle.precio_venta * venta_detalle.cantidad)  FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE  venta.sucursal_id = '. $filters['sucursal_id'] .' AND  venta_detalle.producto_id = producto.id AND (venta.status = 10 OR venta.status = 1 ) AND venta.ruta_sucursal_id IS null  AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.'),0)  + COALESCE((SELECT SUM(venta_detalle.precio_venta * venta_detalle.cantidad)  FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE  venta.ruta_sucursal_id = '. $filters['sucursal_id'] .' AND  venta_detalle.producto_id = producto.id AND (venta.status = 10 OR venta.status = 1 )   AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.'),0) as total_ingreso',

                    'COALESCE((SELECT SUM(venta_detalle.cantidad) FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE   venta.sucursal_id = '. $filters['sucursal_id'] .'  AND venta_detalle.producto_id = producto.id and (venta.status = 10 OR venta.status = 1 ) AND venta.ruta_sucursal_id IS null  AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.'),0) + COALESCE((SELECT SUM(venta_detalle.cantidad) FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE   venta.ruta_sucursal_id = '. $filters['sucursal_id'] .'  AND venta_detalle.producto_id = producto.id and (venta.status = 10 OR venta.status = 1 ) AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.'),0) as total_kilogramos'
                ]);

            }else{


                $select = array_merge($select, [
                    '(SELECT SUM(venta_detalle.precio_venta * venta_detalle.cantidad)  FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE venta_detalle.producto_id = producto.id AND (venta.status = 10 OR venta.status = 1 )) as total_ingreso',
                    '(SELECT SUM(venta_detalle.cantidad) FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE venta_detalle.producto_id = producto.id and (venta.status = 10 OR venta.status = 1 )) as total_kilogramos'
                ]);
            }


            $query = (new Query())
                ->select($select)
                ->from('producto')
                ->andWhere([ "status" => Producto::STATUS_ACTIVE ])
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);

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

    public static function getReporteProductoTop($arr)
    {
        $topProductoArray = [
            "productos" =>  [],
            "vendido"   =>  [],
            "pesos"     =>  [],
        ];

        parse_str($arr['filters'], $filters);

        $select = [
                "`producto`.`id`",
                'producto.clave',
                'producto.nombre',
                'producto.costo',
                'producto.precio_publico',
                'producto.precio_mayoreo',
                'producto.precio_sub',
            ];


        if ((isset($filters['sucursal_id']) && $filters['sucursal_id']) &&  !$filters['date_range'] ) {
            $select = array_merge($select, [
                '(SELECT SUM(venta_detalle.precio_venta * venta_detalle.cantidad)  FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE venta.sucursal_id = '. $filters['sucursal_id'] .' AND venta_detalle.producto_id = producto.id AND venta.status = 10) as total_ingreso',
                '(SELECT SUM(venta_detalle.cantidad) FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE venta.sucursal_id = '. $filters['sucursal_id'] .' AND venta_detalle.producto_id = producto.id and venta.status  = 10) as total_kilogramos'
            ]);

        }else if ((isset($filters['date_range']) && $filters['date_range']) &&  !$filters['sucursal_id'] ) {

            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

            $select = array_merge($select, [
                '(SELECT SUM(venta_detalle.precio_venta * venta_detalle.cantidad)  FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE  venta_detalle.producto_id = producto.id AND venta.status = 10 AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.' ) as total_ingreso',
                '(SELECT SUM(venta_detalle.cantidad) FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE venta_detalle.producto_id = producto.id and venta.status  = 10 AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.') as total_kilogramos'
            ]);

        }else if( (isset($filters['sucursal_id']) && $filters['sucursal_id']) && (isset($filters['date_range']) && $filters['date_range']) ){

            $date_ini = strtotime(substr($filters['date_range'], 0, 10));
            $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

            $select = array_merge($select, [
                '(SELECT SUM(venta_detalle.precio_venta * venta_detalle.cantidad)  FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE  venta.sucursal_id = '. $filters['sucursal_id'] .' AND  venta_detalle.producto_id = producto.id AND venta.status = 10 AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.') as total_ingreso',
                '(SELECT SUM(venta_detalle.cantidad) FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE   venta.sucursal_id = '. $filters['sucursal_id'] .'  AND venta_detalle.producto_id = producto.id and venta.status  = 10 AND venta.created_at BETWEEN '. $date_ini .' AND '. $date_fin.') as total_kilogramos'
            ]);

        }else{
            $select = array_merge($select, [
                '(SELECT SUM(venta_detalle.precio_venta * venta_detalle.cantidad)  FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE venta_detalle.producto_id = producto.id AND venta.status = 10) as total_ingreso',
                '(SELECT SUM(venta_detalle.cantidad) FROM venta_detalle  INNER JOIN venta ON (venta_detalle.venta_id = venta.id) WHERE venta_detalle.producto_id = producto.id and venta.status  = 10) as total_kilogramos'
            ]);
        }


        $query = (new Query())
        ->select($select)
        ->from('producto')
        ->andWhere([ "status" => Producto::STATUS_ACTIVE ])
        ->limit(10)
        ->orderBy('total_kilogramos desc')
        ->all();


        foreach ($query as $key => $item_producto) {
            array_push($topProductoArray["productos"], $item_producto["nombre"] . " / PRECIO DE VENTA: $". number_format($item_producto["precio_publico"], 2));
            array_push($topProductoArray["vendido"], floatval($item_producto["total_kilogramos"]));
            array_push($topProductoArray["pesos"], floatval($item_producto["total_ingreso"]));
        }
        return $topProductoArray;
    }


    public static function getProductoSeachAjax($producto_search)
    {
        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = (new Query())
            ->select([
                "SQL_CALC_FOUND_ROWS `id`",
                'clave',
                'avatar',
                'nombre',
                "CONCAT_WS(' ', `nombre`,'[ clave: ',`clave`,']') AS `text`",
                'descripcion',
                'categoria',
                'is_subproducto',
                'sub_cantidad_equivalente',
                'sub_producto_id',
                "sub_producto_nombre",
                'costo',
                'precio_publico',
                'precio_mayoreo',
                'precio_sub',
            ])
            ->from(self::tableName())
            ->orderBy('id desc');

        $query->andFilterWhere([
            'or',
            ['like', 'clave', $producto_search],
            ['like', 'nombre', $producto_search],
        ]);



        $query->andWhere(['status' => Producto::STATUS_ACTIVE ]);

        return $query->all();
    }




}
