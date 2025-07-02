<?php
namespace app\models\credito;


use Yii;
use yii\db\Query;
use yii\web\Response;
use yii\db\Expression;
use app\models\cobro\CobroVenta;
/**
 * This is the model class for table "view_credito".
 *
 * @property int $id Credito
 * @property int $cliente_id Cliente
 * @property string|null $cliente
 * @property float $monto Monto
 * @property string|null $nota Nota
 * @property string|null $descripcion Descripcion
 * @property int $status Estatus
 * @property int $created_by Creado por
 * @property string|null $created_by_user
 * @property int|null $created_at Creado por
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 * @property string|null $updated_by_user
 */
class ViewAbono extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_abono';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cliente_id' => 'Cliente ID',
            'cliente' => 'Cliente',
            'monto' => 'Monto',
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
        $limit   = isset($arr['limit'])?  $arr['limit']:  10;

        $search = isset($arr['search'])? $arr['search']: false;
        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/
        $query =  (new Query())
            ->select([
                "nombre",
                "created_at",
                "cantidad",
                "metodo_pago",
                "cliente_id",
                "token_pay",
            ])
            ->from(self::tableName())
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit);

            if(isset($filters['date_range']) && $filters['date_range']){
                $date_ini = strtotime(substr($filters['date_range'], 0, 10));
                $date_fin = strtotime(substr($filters['date_range'], 13, 23)) + 86340;

                $query->andWhere(['between','created_at', $date_ini, $date_fin]);
            }
            if(isset($filters['cliente-cliente_id']) && $filters['cliente-cliente_id']){
                $query->andWhere([ "and",
                    ["=","cliente_id", $filters['cliente-cliente_id']]
                ]);
            }
        if(isset($filters['pago-metodo_pago']) && $filters['pago-metodo_pago']){
            $query->andWhere([ "and",
                ["=","metodo_pago", $filters['pago-metodo_pago']]
            ]);
        }


        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }
}
