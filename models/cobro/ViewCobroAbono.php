<?php
namespace app\models\cobro;

use Yii;
use yii\db\Query;
use yii\web\Response;
/**
 * This is the model class for table "view_cobro_abono".
 *
 * @property int $id ID
 * @property int|null $venta_id Venta ID
 * @property int|null $compra_id Compra ID
 * @property int|null $credito_id Credito ID
 * @property string|null $trans_token_credito Token operacion
 * @property string|null $trans_token_venta Token operacion
 * @property int $tipo Tipo
 * @property int $metodo_pago Metodo de pago
 * @property int $tipo_cobro_pago Cobro / Pago
 * @property float $cantidad Cantidad
 * @property float|null $cantidad_pago Cantidad Pago
 * @property int|null $fecha_credito Fecha credito
 * @property string|null $nota Nota
 * @property float|null $cargo_extra Cargo extra
 * @property int $created_at Creado
 * @property string|null $sucursal_recibe
 * @property string|null $created_by_user
 * @property int $created_by Creado por
 */
class ViewCobroAbono extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_cobro_abono';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'venta_id', 'compra_id', 'credito_id', 'tipo', 'metodo_pago', 'tipo_cobro_pago', 'fecha_credito', 'created_at', 'created_by'], 'integer'],
            [['tipo', 'metodo_pago', 'tipo_cobro_pago', 'cantidad', 'created_at', 'created_by'], 'required'],
            [['cantidad', 'cantidad_pago', 'cargo_extra'], 'number'],
            [['nota'], 'string'],
            [['trans_token_credito', 'trans_token_venta'], 'string', 'max' => 150],
            [['sucursal_recibe'], 'string', 'max' => 200],
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
            'venta_id' => 'Venta ID',
            'compra_id' => 'Compra ID',
            'credito_id' => 'Credito ID',
            'trans_token_credito' => 'Trans Token Credito',
            'trans_token_venta' => 'Trans Token Venta',
            'tipo' => 'Tipo',
            'metodo_pago' => 'Metodo Pago',
            'tipo_cobro_pago' => 'Tipo Cobro Pago',
            'cantidad' => 'Cantidad',
            'cantidad_pago' => 'Cantidad Pago',
            'fecha_credito' => 'Fecha Credito',
            'nota' => 'Nota',
            'cargo_extra' => 'Cargo Extra',
            'created_at' => 'Created At',
            'sucursal_recibe' => 'Sucursal Recibe',
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
                    'venta_id',
                    'compra_id',
                    'credito_id',
                    'trans_token_credito',
                    'trans_token_venta',
                    'tipo',
                    'metodo_pago',
                    'tipo_cobro_pago',
                    'cantidad',
                    'cantidad_pago',
                    'fecha_credito',
                    'nota',
                    'cargo_extra',
                    'created_at',
                    'sucursal_recibe',
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
                $date_ini = strtotime($filters['from_date']);
                $date_fin = strtotime($filters['to_date']);

                $query->andWhere(['between','created_at', $date_ini, $date_fin]);
            }

            if (isset($filters['metodo_pago']) && $filters['metodo_pago'])
                $query->andWhere(['metodo_pago' =>  $filters['metodo_pago']]);

            if (isset($filters['tipo']) && $filters['tipo'])
                $query->andWhere(['tipo' =>  $filters['tipo']]);

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $query->andWhere(['sucursal_recibe_id' =>  $filters['sucursal_id']]);

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
