<?php
namespace app\models\credito;

use app\models\cobro\CobroVenta;
use Yii;
use yii\db\Query;
use yii\web\Response;
use yii\db\Expression;

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
class ViewCredito extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'view_credito';
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
            'nota' => 'Nota',
            'descripcion' => 'Descripcion',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_by_user' => 'Created By User',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
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
                    "count(id) as item_count",
                    'cliente_id',
                    //'compra_id',
                    //'venta_id',
                    'tipo',
                    'proveedor',
                    //'tpv_cliente',
                    'cliente',
                    'sum(monto) as monto',
                    'fecha_credito',
                    'sum(deuda) as deuda',
                    'sum(monto_pagado) as monto_pagado',
                    'nota',
                    'descripcion',
                    'status',
                    'created_by',
                    'created_by_user',
                    'created_at',
                    'created_by_sucursal_id',
                    'updated_by',
                    'updated_at',
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

                $query->andWhere(['between','fecha_credito', $date_ini, $date_fin]);
            }

            if (isset($filters['status']) && $filters['status'])
                $query->andWhere(['status' =>  $filters['status']]);

            if (isset($filters['cancel_on']) && $filters['cancel_on']) {
                $query->andWhere(['status' =>  Credito::STATUS_CANCEL ]);
            }else{
                if (isset($filters['pagado_on']) && $filters['pagado_on']) {
                    $query->andWhere(['status' =>  Credito::STATUS_PAGADA ]);
                }else{
                    if(isset($filters['day_on']) && $filters['day_on']){
                        $query->andWhere(['=', new Expression('DATE_FORMAT(FROM_UNIXTIME(fecha_credito), "%Y-%m-%d")'), new Expression('DATE(NOW())')]);
                    }else{
                        if (isset($filters['vencido_on']) && $filters['vencido_on'] or isset($filters['sucursales_on'])){
                            $query->andWhere([ "and",
                                ["<>","status", Credito::STATUS_PAGADA ],
                                ["<>","status", Credito::STATUS_CANCEL ],
                                ["<","fecha_credito", time() ]
                            ]);
                        }else{
                            $query->andWhere([ "and",
                                ["<>","status", Credito::STATUS_CANCEL ],
                                ["<>","status", Credito::STATUS_PAGADA ],
                                [">","fecha_credito", time() ]
                            ]);
                        }
                    }
                }
            }


            if (isset($filters['tipo']) && $filters['tipo'])
                $query->andWhere(['tipo' =>  $filters['tipo']]);

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'])
                $query->andWhere(['created_by_sucursal_id' =>  $filters['sucursal_id']]);

            if (isset($filters['tipo']) && $filters['tipo']){
                if (Credito::TIPO_CLIENTE == $filters['tipo'])
                    $query->groupBy("cliente_id");

                if (Credito::TIPO_PROVEEDOR == $filters['tipo'])
                    $query->groupBy("get_provedor_id");
            }else
                $query->groupBy("cliente_id, get_provedor_id");


            if($search)
                $query->andFilterWhere([
                    'or',
                    ['like', 'id', $search],
                    ['like', 'cliente', $search],
                    ['like', 'proveedor', $search],
                    //['like', 'tpv_cliente', $search],
                ]);

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';


        return [
            'rows'  => $query->all(),
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }


    public static function getPagosJsonBtt($arr){

        // La respuesta sera en Formato JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Preparamos las variables
        $sort    = isset($arr['sort'])?   $arr['sort']:   'id';
        $order   = isset($arr['order'])?  $arr['order']:  'asc';
        $orderBy = $sort . ' ' . $order;
        $offset  = isset($arr['offset'])? $arr['offset']: 0;
        $limit   = isset($arr['limit'])?  $arr['limit']:  30;

        $search = isset($arr['search'])? $arr['search']: false;
        parse_str($arr['filters'], $filters);


      
        
            $creditoArray   = null;
            $pagoArray      = [];
            if ($filters["tipo"] == Credito::TIPO_CLIENTE) {
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
                    ["=","venta.cliente_id", $filters["item_id"] ],
                    ["=","credito.cliente_id", $filters["item_id"] ]
                ])
                ->orderBy("credito_abono.created_at desc")
                ->groupBy("token_pay")
                ->offset($offset)
                ->limit($limit)
                ->all();
            }
            if ($filters["tipo"] == Credito::TIPO_PROVEEDOR) {
                $creditoArray = (new Query())
                ->select([
                    "SQL_CALC_FOUND_ROWS `credito`.`id`",
                    "credito.id as credito_id",
                    "credito_abono.id as credito_operacion_id",
                    "credito_abono.token_pay as credito_token_pay",
                ])
                ->from('credito')
                ->leftJoin('compra','credito.compra_id = compra.id')
                ->innerJoin("credito_abono", "credito.id = credito_abono.credito_id")
                ->andWhere(["and",
                    ["=","credito.tipo", Credito::TIPO_PROVEEDOR ],
                ])
                ->andWhere([ "or",
                    ["=","compra.proveedor_id", $filters["item_id"] ],
                    ["=","credito.proveedor_id", $filters["item_id"] ]
                ])
                ->orderBy("credito_abono.created_at desc")
                ->groupBy("token_pay")
                ->offset($offset)
                ->limit($limit)
                ->all();
            }

            $totalResultados = \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar();
            

            if ($creditoArray) {
                foreach ($creditoArray as $key_credito => $item_credito) {
                    
                    $CobroVenta          = CobroVenta::find()->andWhere([ "trans_token_credito" => $item_credito["credito_token_pay"] ])->all();
                    $CobroVentaCancelado = CreditoAbono::find()->andWhere([ "and",
                        [ "=", "token_pay", $item_credito["credito_token_pay"]],
                        [ "=", "status", CreditoAbono::STATUS_CANCEL ]
                    ])->sum("cantidad");
    
                    $is_add = true;
    
                    foreach ($CobroVenta as $key_pago => $item_pago) {
                        foreach ($pagoArray as $key_array_pago => $item_array_pago) {
                            if ($item_pago->trans_token_credito == $item_array_pago["trans_token_pay"]) {
                                $is_add = false;
                                $pagoArray[$key_array_pago]["cantidad"] = floatval($item_array_pago["cantidad"])  + floatval($item_pago->cantidad);
                                $pagoArray[$key_array_pago]["cantidad_final"] = floatval($item_array_pago["cantidad_final"])  + floatval($item_pago->cantidad);
                            }
                        }

                        if ($is_add) {
                            array_push($pagoArray, [
                                "trans_token_pay"   => $item_pago->trans_token_credito,
                                "opera_token_pay"   => $item_credito["credito_token_pay"],
                                "cantidad"          => floatval($item_pago->cantidad),
                                "cantidad_cancelado"=> $CobroVentaCancelado,
                                "cantidad_final"    => round(floatval($item_pago->cantidad) - floatval(($CobroVentaCancelado ? $CobroVentaCancelado : 0)),2),
                                "fecha"             => date("Y-m-d h:i a",$item_pago->created_at),
                                "registrado_por"    => $item_pago->createdBy->nombreCompleto,
                            ]);
                        }
                    }
                }
            }
    
        /************************************
        / Preparamos consulta
        /***********************************/
            $query = (new Query())
                ->select([
                    "SQL_CALC_FOUND_ROWS `id`",
                    "count(id) as item_count",
                    'cliente_id',
                    //'compra_id',
                    //'venta_id',
                    'tipo',
                    'proveedor',
                    //'tpv_cliente',
                    'cliente',
                    'sum(monto) as monto',
                    'fecha_credito',
                    'sum(deuda) as deuda',
                    'sum(monto_pagado) as monto_pagado',
                    'nota',
                    'descripcion',
                    'status',
                    'created_by',
                    'created_by_user',
                    'created_at',
                    'created_by_sucursal_id',
                    'updated_by',
                    'updated_at',
                    'updated_by_user',
                ])
                ->from(self::tableName())
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit);


 
 
     

        
        return [
            'rows'  => $pagoArray,
            'total' => $totalResultados,
        ];

    }
}
