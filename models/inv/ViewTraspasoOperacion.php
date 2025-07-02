<?php
namespace app\models\inv;

use Yii;
use yii\db\Query;
use yii\web\Response;

/**
 * This is the model class for table "view_traspaso_operacion".
 *
 * @property int $id ID
 * @property int $operacion_envia_id Operacion ID
 * @property int $operacion_detalle_id Operacion detalle ID
 * @property int $operacion_recibe_id Operacion recibe ID
 * @property int $operador_id Operador id
 * @property string|null $operador
 * @property int $producto_id Producto ID
 * @property float|null $cantidad_old Cantidad old
 * @property float|null $cantidad_new Cantidad nueva
 * @property int $status status
 * @property int|null $created_at Creado
 * @property int|null $updated_at Update at
 */
class ViewTraspasoOperacion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_traspaso_operacion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'operacion_envia_id', 'operacion_detalle_id', 'operacion_recibe_id', 'operador_id', 'producto_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['operacion_envia_id', 'operacion_detalle_id', 'operacion_recibe_id', 'operador_id', 'producto_id', 'status'], 'required'],
            [['cantidad_old', 'cantidad_new'], 'number'],
            [['operador'], 'string', 'max' => 201],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'operacion_envia_id' => 'Operacion Envia ID',
            'operacion_detalle_id' => 'Operacion Detalle ID',
            'operacion_recibe_id' => 'Operacion Recibe ID',
            'operador_id' => 'Operador ID',
            'operador' => 'Operador',
            'producto_id' => 'Producto ID',
            'cantidad_old' => 'Cantidad Old',
            'cantidad_new' => 'Cantidad New',
            'status' => 'Status',
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
                    'operacion_envia_id',
                    'operacion_detalle_id',
                    'operacion_recibe_id',
                    'operador_id',
                    'operador',
                    'producto_id',
                    'producto',
                    'cantidad_old',
                    'cantidad_new',
                    'diferencia',
                    'status',
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

            if (isset($filters['status']) && $filters['status'])
                $query->andWhere(['status' =>  $filters['status']]);

            if($search)
            $query->andFilterWhere([
                'or',
                ['like', 'id', $search],
            ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';



        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }

}
