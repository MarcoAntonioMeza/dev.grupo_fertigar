<?php
namespace app\models\compra;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_compra".
 *
 * @property int $id ID
 * @property int $sucursal_id Sucursal ID
 * @property string|null $sucursal
 * @property int $proveedor_id Proveedor ID
 * @property string|null $proveedor Nombre
 * @property int $tiempo_recorrido Tiempo aprox
 * @property int $fecha_salida Fecha salida
 * @property float $total Total
 * @property int $status Estatus
 * @property int $created_by Creado por
 * @property int $created_at Creado
 * @property string|null $created_by_user
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 * @property string|null $updated_by_user
 */
class ViewCompra extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_compra';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sucursal_id' => 'Sucursal ID',
            'sucursal' => 'Sucursal',
            'proveedor_id' => 'Proveedor ID',
            'proveedor' => 'Proveedor',
            'tiempo_recorrido' => 'Tiempo Recorrido',
            'fecha_salida' => 'Fecha Salida',
            'total' => 'Total',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'created_by_user' => 'Created By User',
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
                    'sucursal_id',
                    'sucursal',
                    'proveedor_id',
                    'proveedor',
                    'tiempo_recorrido',
                    'fecha_salida',
                    'is_especial',
                    'count_detalle',
                    'count_entrada_detalle',
                    '( count_detalle - count_entrada_detalle) AS diferencia',
                    'total',
                    'status',
                    'created_by',
                    'created_at',
                    'created_by_user',
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
            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $query->andWhere(['between','created_at', $date_ini, $date_fin]);
            }

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $query->andWhere(['sucursal_id' =>  $filters['sucursal_id']]);

            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'proveedor', $search],
                ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';


        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
