<?php

namespace app\models\contabilidad;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_contabilidad_cuenta".
 *
 * @property int $id
 * @property string|null $nombre
 * @property string|null $code
 * @property int|null $afectable
 * @property int|null $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class ViewContabilidadCuenta extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_contabilidad_cuenta';
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'code'=> 'code',
            'afectable' => 'Afectable',
            'status' => 'Status',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
                    'nombre',
                    'code',
                    'afectable',
                    'status',
                    'created_by',
                    'updated_by',
                    'created_at',
                    'updated_at',
                ])
                ->from(self::tableName())
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);
        /************************************
        / Filtramos la consulta
        /***********************************/
        if(isset($filters['status']) && $filters['status']){
            $query->andWhere(['status' =>  $filters['status']]);
        }
        if($search)
            $query->andFilterWhere(
                [
                   'or',
                   ['like', 'id', $search],
                   ['like', 'nombre', $search], 
                ]);

            return [
                'rows'  => $query->all(),
                'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
            ];
    }
}