<?php
namespace app\models\contabilidad;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_contabilidad_transaccion".
 *
 * @property int $id
 * @property string|null $categoria
 * @property string|null $subcategoria
 * @property string|null $nombre
 * @property int|null $afectable
 * @property string|null $estatus
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property string|null $created_by
 * @property string|null $updated_by
 */
class ViewContabilidadTransaccion extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_contabilidad_transaccion';
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'categoria' => 'Categoria',
            'subcategoria' => 'Subcategoria',
            'nombre' => 'Nombre',
            'afectable' => 'Afectable',
            'estatus' => 'Estatus',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
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
                    'transaccion',
                    'tipo',
                    'motivo',
                    'status',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
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
            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'cuenta', $search],
                    ['like', 'subcuenta', $search],
                    ['like', 'nombre', $search],
                ]);
        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}


