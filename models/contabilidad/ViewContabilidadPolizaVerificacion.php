<?php
namespace app\models\contabilidad;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_contabilidad_poliza_verificacion".
 *
 * @property int $id ID
 * @property string|null $transaccion_text
 * @property int $transaccion Tipo transaccion
 * @property float $total Total
 * @property int $created_at Creado
 * @property string|null $created_by_user
 * @property int $created_by Creado por
 */
class ViewContabilidadPolizaVerificacion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_contabilidad_poliza_verificacion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'transaccion', 'created_at', 'created_by'], 'integer'],
            [['transaccion', 'total', 'created_at', 'created_by'], 'required'],
            [['total'], 'number'],
            [['transaccion_text'], 'string', 'max' => 28],
            [['created_by_user'], 'string', 'max' => 201],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'transaccion_text' => 'Transaccion Text',
            'transaccion' => 'Transaccion',
            'total' => 'Total',
            'created_at' => 'Created At',
            'created_by_user' => 'Created By User',
            'created_by' => 'Created By',
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
                    'transaccion_text',
                    'total',
                    'created_at',
                    'created_by_user',
                    'created_by',
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
                    ['like', 'transaccion_text', $search],
                ]);
        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
