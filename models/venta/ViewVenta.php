<?php
namespace app\models\venta;

use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\venta\VentaDetalle;
use yii\db\Expression;

/**
 * This is the model class for table "view_venta".
 *
 * @property int $id ID
 * @property string|null $cliente
 * @property int|null $cliente_id Cliente
 * @property int $tipo Tipo
 * @property int $status Estatus
 * @property float $total Total
 * @property string|null $created_by_user
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int|null $updated_at Modificado
 * @property int|null $updated_by Modificado por
 * @property string|null $updated_by_user
 */
class ViewVenta extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_venta';
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cliente' => 'Cliente',
            'cliente_id' => 'Cliente ID',
            'tipo' => 'Tipo',
            'status' => 'Status',
            'total' => 'Total',
            'created_by_user' => 'Created By User',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
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
                    'cliente',
                    'ruta_asignada',
                    'cliente_id',
                    'pertenece',
                    'tipo',
                    'status',
                    'is_especial',
                    'total',
                    'created_by_user',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                    'updated_by_user',
                ])
                ->from(self::tableName())
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);


        /************************************
        / Filtramos la consulta
        /***********************************/
            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $query->andWhere(['between','created_at', $date_ini, $date_fin]);
            }


            if (isset($filters['status']) && $filters['status'])
                $query->andWhere(['status' =>  $filters['status']]);


            if (isset($filters['status_preventas_comandera']) && $filters['status_preventas_comandera'])
                $query->andWhere([ "or",
                    [ '=', 'status',  Venta::STATUS_PREVENTA ],
                    [ '=', 'status',  Venta::STATUS_PROCESO_VERIFICACION ],
                    [ '=', 'status',  Venta::STATUS_VERIFICADO ],
                ]);
            if($filters['status']=='1'){
                $fechaLimite = new Expression('UNIX_TIMESTAMP(NOW()) - (30 * 24 * 60 * 60)');
                $date_ini = new Expression('UNIX_TIMESTAMP(NOW())');
//                $query->andWhere(['<', 'created_at', $fechaLimite]);
                $query->andWhere(['between','created_at', $fechaLimite, $date_ini]);
            }


            if (isset($filters['tipo']) && $filters['tipo'])
                $query->andWhere(['tipo' =>  $filters['tipo']]);


            if (isset($filters['tipo_venta']) && $filters['tipo_venta'])
                $query->andWhere(['is_especial' =>  $filters['tipo_venta']]);

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $query->andWhere(['sucursal_id' =>  $filters['sucursal_id']]);

            if (isset($filters['sucursal_venta_id']) && $filters['sucursal_venta_id']){
                $query->andWhere(["or",
                    ['=','sucursal_id',  $filters['sucursal_venta_id']],
                    ['=','ruta_sucursal_id',  $filters['sucursal_venta_id']],
                ]);
            }

            if (isset($filters['ruta_asignada']) && $filters['ruta_asignada'])
                $query->andWhere(['ruta_sucursal_id' =>  $filters['ruta_asignada']]);


            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'cliente', $search],
                ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';


        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }


    public static function getVentaDetalleAjax($arr){
        Yii::$app->response->format = Response::FORMAT_JSON;

        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                'venta_detalle.id',
                'venta_detalle.venta_id',
                'venta_detalle.producto_id',
                'venta_detalle.cantidad',
                'venta_detalle.precio_venta',
                'venta_detalle.is_conversion',
                'venta_detalle.conversion_cantidad',
                'producto.nombre as producto',
                'producto.clave  as producto_clave',
                'producto.precio_publico as publico',
                'producto.precio_mayoreo as mayoreo',
                'producto.precio_menudeo as menudeo',
                'producto.costo as costo',
                'producto.tipo_medida as producto_unidad',
                'IF(producto.tipo_medida = 10, "Piezas","Kilo") as tipo_medida_text',
            ])
            ->from(VentaDetalle::tableName())
            ->innerJoin("producto","venta_detalle.producto_id = producto.id")
            ->andWhere(['venta_detalle.venta_id' =>  $arr['venta_id'] ]);

        return [
            'rows'  => $query->all()
        ];
    }

    public static function getVentaPreCaptura($sucursal_id, $pertenece_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
        ->select([
            "SQL_CALC_FOUND_ROWS `id`",
            'cliente',
            'cliente_id',
            'sucursal_id',
            'pertenece',
            'tipo',
            'pertenece',
            'status',
            'total',
            'created_by_user',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'updated_by_user',
        ])
        ->from(self::tableName())
        ->andWhere(["and",
            ["=","status",Venta::STATUS_PRECAPTURA ],
            ["=","ruta_sucursal_id", $sucursal_id],
        ]);

        if ($pertenece_id)
            $query->andWhere([ "sucursal_id" => $pertenece_id ]);


        return $query->orderBy("id desc")->all();
    }

    public static function getProductoPreCaptura($sucursal_id, $embarque)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ventaIDs = [];
        foreach (json_decode($embarque) as $key => $item) {
            if ($item->check_true == 10 ) {
                array_push($ventaIDs, $item->item_id);
            }
        }

        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
        ->select([
            "producto.nombre as producto",
            "producto.clave as clave",
            "producto.tipo_medida as tipo_medida",
            "SUM(venta_detalle.cantidad) as total_producto",
        ])
        ->from('venta')
        ->innerJoin("venta_detalle","venta.id = venta_detalle.venta_id")
        ->innerJoin("producto","venta_detalle.producto_id = producto.id")
        ->andWhere(["and",
            ["=","venta.status",Venta::STATUS_PRECAPTURA ],
            ["=","venta.ruta_sucursal_id", $sucursal_id],
        ])
        ->andWhere([ "or",
            ["IN", 'venta.id', $ventaIDs ]
        ])
        ->groupBy("producto.id");

        return $query->all();
    }


    public static function getListaCompraProductoPreCaptura($sucursal_id, $ruta_id = false)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
        ->select([
            "venta.id as folio",
            "sucursal.nombre as ruta",
            "producto.nombre as producto",
            "venta.cliente_id as cliente_id",
            "concat_ws(' ',trim(`cliente`.`nombre`),trim(`cliente`.`apellidos`)) AS `cliente`",
            "producto.clave as clave",
            "producto.tipo_medida as tipo_medida",
            "venta_detalle.cantidad as total_producto",
            "( select inv_producto_sucursal.cantidad from inv_producto_sucursal where inv_producto_sucursal.producto_id = producto.id and inv_producto_sucursal.sucursal_id = venta.sucursal_id) as inventario",
        ])
        ->from('venta')
        ->innerJoin("venta_detalle","venta.id = venta_detalle.venta_id")
        ->leftJoin("sucursal","venta.ruta_sucursal_id = sucursal.id")
        ->innerJoin("cliente","venta.cliente_id = cliente.id")
        ->innerJoin("producto","venta_detalle.producto_id = producto.id")
        ->andWhere(["and",
            ["=","venta.status",Venta::STATUS_PRECAPTURA ],
            ["=","venta.sucursal_id", $sucursal_id],
        ])->orderBy("venta.cliente_id");
        //->groupBy("producto.id");

        if ($ruta_id)
            $query->andWhere([ "venta.ruta_sucursal_id" => $ruta_id ]);


        return $query->all();
    }

    public static function getCountPreventaCliente($sucursal_id, $cliente_id, $ruta_id = false)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
        ->select([
            "producto.nombre as producto",
            "concat_ws(' ',trim(`cliente`.`nombre`),trim(`cliente`.`apellidos`)) AS `cliente`",
            "producto.clave as clave",
            "producto.tipo_medida as tipo_medida",
            "venta_detalle.cantidad as total_producto",
            "( select inv_producto_sucursal.cantidad from inv_producto_sucursal where inv_producto_sucursal.producto_id = producto.id and inv_producto_sucursal.sucursal_id = venta.sucursal_id) as inventario",
        ])
        ->from('venta')
        ->innerJoin("venta_detalle","venta.id = venta_detalle.venta_id")
        ->innerJoin("cliente","venta.cliente_id = cliente.id")
        ->innerJoin("producto","venta_detalle.producto_id = producto.id")
        ->andWhere(["and",
            ["=","venta.status",Venta::STATUS_PRECAPTURA ],
            ["=","venta.sucursal_id", $sucursal_id],
            ["=","venta.cliente_id", $cliente_id],
        ]);

        if ($ruta_id)
            $query->andWhere([ "venta.ruta_sucursal_id" => $ruta_id ]);

        //->groupBy("producto.id");

        return $query->count();
    }

    public static function getListaCompraProductoPreCapturaGroup($sucursal_id, $ruta_id = false)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
        ->select([
            "producto.nombre as producto",
            "concat_ws(' ',trim(`cliente`.`nombre`),trim(`cliente`.`apellidos`)) AS `cliente`",
            "producto.clave as clave",
            "producto.tipo_medida as tipo_medida",
            "sum(venta_detalle.cantidad) as total_producto",
            "(select inv_producto_sucursal.cantidad from inv_producto_sucursal where inv_producto_sucursal.producto_id = producto.id and inv_producto_sucursal.sucursal_id = venta.sucursal_id) as inventario",
        ])
        ->from('venta')
        ->innerJoin("venta_detalle","venta.id = venta_detalle.venta_id")
        ->innerJoin("cliente","venta.cliente_id = cliente.id")
        ->innerJoin("producto","venta_detalle.producto_id = producto.id")
        ->andWhere(["and",
            ["=","venta.status",Venta::STATUS_PRECAPTURA ],
            ["=","venta.sucursal_id", $sucursal_id],
        ])
        ->groupBy("producto.id");

        if ($ruta_id)
            $query->andWhere([ "venta.ruta_sucursal_id" => $ruta_id ]);

        return $query->all();
    }

    public static function getVentaPreCapturaDetail($sucursal_id, $pertenece_id = false)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
        ->select([
            'venta_detalle.id as venta_detalle_id',
            'venta_detalle.producto_id as producto_id',
            'producto.nombre as producto',
            'venta_detalle.cantidad as cantidad',
            'venta_detalle.precio_venta as precio_venta',
            'view_venta.cliente',
            'view_venta.cliente_id',
            'view_venta.status',
            'view_venta.total',
            'view_venta.created_by_user',
            'FROM_UNIXTIME(view_venta.created_at,"%Y-%m-%d") as fecha_registro',
            'view_venta.created_at',
            'view_venta.created_by',
        ])
        ->from(self::tableName())
        ->innerJoin("venta_detalle", "view_venta.id = venta_detalle.venta_id")
        ->innerJoin("producto","venta_detalle.producto_id = producto.id")
        ->andWhere(["and",
            ["=","view_venta.status",Venta::STATUS_PRECAPTURA ],
            ["=","view_venta.ruta_sucursal_id", $sucursal_id],
        ]);

        if ($pertenece_id)
            $query->andWhere(["view_venta.sucursal_id" => $pertenece_id]);


        return $query->all();
    }
}
