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
class ViewAbonoGlobal extends \yii\db\ActiveRecord
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
        $limit   = isset($arr['limit'])?  $arr['limit']:  50;

        $search = isset($arr['search'])? $arr['search']: false;
        parse_str($arr['filters'], $filters);


        /************************************
        / Preparamos consulta
        /***********************************/
        $creditoArray =  (new Query())
            ->select([
                "SQL_CALC_FOUND_ROWS `credito`.`id`",
                "credito.id as credito_id",
                "credito_abono.id as credito_operacion_id",
                "credito_abono.token_pay as credito_token_pay",
            ])
            ->from('credito')
            ->leftJoin('venta','credito.venta_id = venta.id')
            ->innerJoin("credito_abono", "credito.id = credito_abono.credito_id")
            ->andWhere(["and",
                ["=","credito.tipo", Credito::TIPO_CLIENTE ],
            ])
            ->andWhere([ "or",
                ["=","venta.cliente_id", $filters['cliente-cliente_id']],
                ["=","credito.cliente_id", $filters['cliente-cliente_id']]
            ])
            ->orderBy("credito_abono.created_at desc")
            ->groupBy("token_pay")
            ->offset($offset)
            ->limit($limit)
            ->all();

        $totalResultados = \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar();
        /*$creditoAll = Credito::find()->leftJoin('venta','credito.venta_id = venta.id')
        ->andWhere(["and",
            ["=","credito.tipo", Credito::TIPO_CLIENTE ],
        ])
        ->andWhere([ "or",
            ["=","venta.cliente_id", $request["cliente_id"] ],
            ["=","credito.cliente_id", $request["cliente_id"] ]
        ])
        ->all();*/

        $response = [];
        $counter=0;
        foreach ($creditoArray as $key => $item_credito) {
            $CobroVenta          = CobroVenta::find()->andWhere([ "trans_token_credito" => $item_credito["credito_token_pay"] ])->all();
            $CobroVentaCancelado = CreditoAbono::find()->andWhere([ "and",
                [ "=", "token_pay", $item_credito["credito_token_pay"]],
                [ "=", "status", CreditoAbono::STATUS_CANCEL ]
            ])->sum("cantidad");

            $is_add = true;

            foreach ($CobroVenta as $key_pago => $item_pago) {
                foreach ($response as $key_array_pago => $item_array_pago) {
                    if ($item_pago->trans_token_credito == $item_array_pago["trans_token_pay"]) {
                        $is_add = false;
                        $response[$key_array_pago]["cantidad"] = floatval($item_array_pago["cantidad"])  + floatval($item_pago->cantidad);
                    }
                }

                if ($is_add) {
                    array_push($response,[
                        "trans_token_pay"   => $item_pago->trans_token_credito,
                        "opera_token_pay"   => $item_credito["credito_token_pay"],
                        "cantidad"          => floatval($item_pago->cantidad) - floatval($CobroVentaCancelado),
                        "fecha"             => $item_pago->created_at,
                        "registrado_por"    => $item_pago->createdBy->nombreCompleto,
                    ]);
                }

            }
            $counter++;
        }


        return [
            'rows'  => $response,
            'total' => $totalResultados,
        ];
    }
}
