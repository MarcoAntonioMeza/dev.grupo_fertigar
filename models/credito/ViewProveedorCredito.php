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
class ViewProveedorCredito extends \yii\db\ActiveRecord
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
        $creditoArray =  Credito::find()->leftJoin('compra','credito.compra_id = compra.id')
            ->andWhere(["and",
                ["=","credito.tipo", Credito::TIPO_PROVEEDOR ],
            ])
            ->andWhere([ "or",
                ["=","compra.proveedor_id", $filters['proveedor-proveedor_id'] ],
                ["=","credito.proveedor_id", $filters['proveedor-proveedor_id'] ]
            ])
            ->all();

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
                foreach ($item_credito->abono as $key => $item_transaccion) {
                    array_push($response,[
                        "id"         => $item_transaccion->id,
                        "credito_id" => $item_transaccion->credito_id,
                        "cantidad"   => $item_transaccion->cantidad,
                        "token_pay"  => $item_transaccion->token_pay,
                        "status_text"=> CreditoAbono::$statusList[$item_transaccion->status],
                        "status"     => $item_transaccion->status,
                        "created_at" => date("Y-m-d h:i:s",$item_transaccion->created_at),
                        "empleado"   => $item_transaccion->createdBy->nombreCompleto ,
                    ]);
                }
                $counter++;
            }


        return [
            'rows'  => $response,
            'total' => $counter,
        ];
    }
}
