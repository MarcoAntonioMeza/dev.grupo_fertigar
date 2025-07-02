<?php
namespace app\models\inv;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_entrada_salida".
 *
 * @property int|null $id ID
 * @property int|null $tipo Tipo
 * @property int|null $motivo Motivo
 * @property string $sucursal
 * @property int|null $sucursal_tipo Tipo de sucursal
 * @property float|null $operacion_cantidad
 * @property int|null $almacen_sucursal_id Almacen
 * @property int|null $created_by Creado por
 * @property string|null $created_by_user
 * @property int|null $created_at Creado
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 * @property string|null $updated_by_user
 */
class ViewEntradaSalida extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_entrada_salida';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tipo' => 'Tipo',
            'motivo' => 'Motivo',
            'sucursal' => 'Sucursal',
            'sucursal_tipo' => 'Sucursal Tipo',
            'operacion_cantidad' => 'Operacion Cantidad',
            'almacen_sucursal_id' => 'Almacen Sucursal ID',
            'created_by' => 'Created By',
            'created_by_user' => 'Created By User',
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
                    'tipo',
                    'motivo',
                    'sucursal',
                    'origen',
                    'movimiento',
                    'destino',
                    'sucursal_tipo',
                    'operacion_cantidad',
                    'almacen_sucursal_id',
                    'status',
                    'created_by',
                    'created_by_user',
                    'created_at',
                    'updated_by',
                    'updated_at',
                    'updated_by_user',
                ])
                ->from(self::tableName())
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);


            /************************************
            / Filtramos la consulta
            /***********************************/

            if (isset($filters['off_reembolso']) && $filters['off_reembolso'])
                $query->where(["<>","tipo", Operacion::TIPO_DEVOLUCION ]);


            if (isset($filters['on_reembolso']) && $filters['on_reembolso'])
                $query->where(["=","tipo", Operacion::TIPO_DEVOLUCION ]);

            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $query->andWhere(['between','created_at', $date_ini, $date_fin]);
            }


            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $query->andWhere(['almacen_sucursal_id' =>  $filters['sucursal_id']]);

            if (isset($filters['tipo']) && $filters['tipo'])
                $query->andWhere(['tipo' =>  $filters['tipo']]);





            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'sucursal', $search],
                ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';



        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }


    public static function getDevolucionDetalle($sucursal_id)
    {

         // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;


        /************************************
        / Preparamos consulta
        /***********************************/
            $query = (new Query())
                ->select([
                    "operacion.id",
                    'operacion.venta_id',
                    'operacion.venta_reembolso_cantidad',
                    'concat_ws(" ",`cliente`.`nombre`,`cliente`.`apellidos`) AS cliente',
                    'sucursal.nombre as sucursal',
                    'operacion_detalle.cantidad',
                    'operacion_detalle.producto_id',
                    'operacion_detalle.venta_detalle_id',
                    'producto.nombre as producto',
                    'operacion.status',
                    'operacion.created_at',
                    'operacion.created_by',
                    'concat_ws(" ",`created`.`nombre`,`created`.`apellidos`) AS created_by_user',
                    'operacion_detalle.costo',
                    'operacion.almacen_sucursal_id',

                ])
                ->from('operacion')
                ->innerJoin('sucursal','operacion.almacen_sucursal_id = sucursal.id')
                ->innerJoin('operacion_detalle', 'operacion.id = operacion_detalle.operacion_id')
                ->innerJoin('producto','operacion_detalle.producto_id = producto.id')
                ->leftJoin('user created','operacion.created_by = created.id')
                ->innerJoin('venta','operacion.venta_id = venta.id')
                ->leftJoin('cliente','venta.cliente_id = cliente.id')
                ->leftJoin('tranformacion_devolucion_detalle transforma','operacion.id = transforma.operacion_id  and operacion.venta_id = transforma.venta_id and operacion_detalle.venta_detalle_id = transforma.venta_detalle_id')
                ->andWhere(["and",
                    ["=","operacion.tipo", Operacion::TIPO_DEVOLUCION ],
                    ["=","operacion.almacen_sucursal_id", $sucursal_id],
                    ['IS', 'transforma.id', new \yii\db\Expression('null')]
                ])
                ->orderBy("operacion_detalle.producto_id");

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';


        return  $query->all();
    }
}
