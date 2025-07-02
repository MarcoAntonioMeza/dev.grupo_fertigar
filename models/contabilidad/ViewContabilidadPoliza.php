<?php
namespace app\models\contabilidad;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_contabilidad_poliza".
 *
 * @property int $id ID
 * @property string|null $transaccion
 * @property string|null $referencia
 * @property int $tipo Tipo
 * @property int $pertenece Pertenece
 * @property string|null $concepto concepto
 * @property int $status Estatus
 * @property float|null $total Total
 * @property int $created_by Creado por
 * @property int $created_at Creado
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 */
class ViewContabilidadPoliza extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_contabilidad_poliza';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'tipo', 'pertenece', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['tipo', 'pertenece', 'created_by', 'created_at'], 'required'],
            [['total'], 'number'],
            [['transaccion'], 'string', 'max' => 32],
            [['referencia'], 'string', 'max' => 6],
            [['concepto'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'transaccion' => 'Transaccion',
            'referencia' => 'Referencia',
            'tipo' => 'Tipo',
            'pertenece' => 'Pertenece',
            'concepto' => 'Concepto',
            'status' => 'Status',
            'total' => 'Total',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
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
                    'transaccion',
                    'referencia',
                    'tipo',
                    'pertenece',
                    'concepto',
                    'status',
                    'total',
                    'created_by',
                    'created_by_user',
                    'created_at',
                    'updated_by',
                    'updated_by_user',
                    'updated_at',
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
                    ['like', 'transaccion', $search],
                ]);
        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';
        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
