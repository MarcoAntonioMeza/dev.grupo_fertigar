<?php
namespace app\models\apertura;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_apertura_cierre".
 *
 * @property int $id ID
 * @property int $user_id Vendedor ID
 * @property string|null $vendedor
 * @property int $fecha_apertura Fecha apertura
 * @property int|null $fecha_cierre Fecha cierre
 * @property float $cantidad_caja Cantidad en caja
 * @property float|null $total Total
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property string|null $created_by_user
 * @property int|null $updated_at Modificado
 * @property int|null $updated_by Modificado por
 * @property string|null $updated_by_user
 */
class ViewAperturaCierre extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_apertura_cierre';
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'vendedor' => 'Vendedor',
            'fecha_apertura' => 'Fecha Apertura',
            'fecha_cierre' => 'Fecha Cierre',
            'cantidad_caja' => 'Cantidad Caja',
            'total' => 'Total',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'created_by_user' => 'Created By User',
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
						'user_id',
						'vendedor',
						'fecha_apertura',
						'fecha_cierre',
						'cantidad_caja',
						'total',
						'status',
						'created_at',
						'created_by',
						'created_by_user',
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


            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'vendedor', $search],
                ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
