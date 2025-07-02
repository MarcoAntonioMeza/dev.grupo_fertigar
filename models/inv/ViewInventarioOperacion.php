<?php
namespace app\models\inv;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_inventario_operacion".
 *
 * @property int $id ID
 * @property int $inventario_sucursal_id Sucursal ID
 * @property string $sucursal
 * @property int $asignado_id Asignado
 * @property string|null $asignado
 * @property int $status Estatus
 * @property int $tipo Tipo
 * @property int $created_at Creado
 * @property string|null $created_by_user
 * @property int $created_by Creado por
 * @property int|null $updated_at Modificado
 * @property int|null $updated_by Modificado por
 * @property string|null $updated_by_user
 */
class ViewInventarioOperacion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_inventario_operacion';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'inventario_sucursal_id' => 'Inventario Sucursal ID',
            'sucursal' => 'Sucursal',
            'asignado_id' => 'Asignado ID',
            'asignado' => 'Asignado',
            'status' => 'Status',
            'tipo' => 'Tipo',
            'created_at' => 'Created At',
            'created_by_user' => 'Created By User',
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
                    'inventario_sucursal_id',
                    'sucursal',
                    'asignado_id',
                    'asignado',
                    'status',
                    'tipo',
                    'created_at',
                    'created_by_user',
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

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $query->andWhere(['inventario_sucursal_id' =>  $filters['sucursal_id']]);

            if (isset($filters['tipo']) && $filters['tipo'])
                $query->andWhere(['tipo' =>  $filters['tipo']]);

            if (isset($filters['status']) && $filters['status'])
                $query->andWhere(['status' =>  $filters['status']]);

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

    public static function getOperacionDetalleJsonBtt($solicitud_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query =  (new Query())
            ->select([
                "inventario_operacion_detalle.id",
                "inventario_operacion_detalle.inventario_operacion_id",
                "inventario_operacion_detalle.producto_id",
                "producto.nombre as producto",
            ])
            ->from("inventario_operacion_detalle")
            ->innerJoin("producto", "inventario_operacion_detalle.producto_id = producto.id")
            ->andWhere(["inventario_operacion_detalle.inventario_operacion_id" => $solicitud_id])
            ->all();

        return $query;
    }
}
