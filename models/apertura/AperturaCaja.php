<?php
namespace app\models\apertura;

use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\user\User;
use app\models\cobro\CobroVenta;
use app\models\credito\CreditoAbono;

/**
 * This is the model class for table "apertura_caja".
 *
 * @property int $id ID
 * @property int $user_id Vendedor ID
 * @property int $fecha_apertura Fecha apertura
 * @property int $fecha_cierre Fecha cierre
 * @property float|null $total Total
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int|null $updated_at Modificado
 * @property int|null $updated_by Modificado por
 *
 * @property User $createdBy
 * @property User $updatedBy
 * @property User $user
 */
class AperturaCaja extends \yii\db\ActiveRecord
{

    const STATUS_CERRADA    = 10;
    const STATUS_PROCESO    = 20;

    public static $statusList = [
        self::STATUS_CERRADA   => 'CERRADO',
        self::STATUS_PROCESO => 'PROCESO',
        //self::STATUS_DELETED  => 'Eliminado'
    ];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'apertura_caja';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'fecha_apertura'], 'required'],
            [['user_id', 'fecha_apertura', 'fecha_cierre','created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['total','cantidad_caja'], 'number'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'fecha_apertura' => 'Fecha Apertura',
            'fecha_cierre' => 'Fecha Cierre',
            'total' => 'Total',
            'apertura_caja_id' => 'Apertura caja',
            'cantidad_caja' => 'Cantidad apertura',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getAperturaCajaDetalles()
    {
        return $this->hasMany(AperturaCajaDetalle::className(), ['apertura_caja_id' => 'id']);
    }

    public static function getAperturaCajaDetallesGroup($caja_id)
    {
        return (new Query())
        ->select([
            "apertura_caja_detalle.id",
            "apertura_caja_detalle.venta_id",
            "apertura_caja_detalle.tipo",
            "apertura_caja_detalle.cantidad",
            "( select sum(cobro_venta.cantidad) from cobro_venta where cobro_venta.venta_id = apertura_caja_detalle.venta_id  ) as cantidad_venta",
            "( select sum(cobro_venta.cantidad) from cobro_venta where cobro_venta.trans_token_credito = apertura_caja_detalle.token_pay  ) as cantidad_credito",
            "( select venta.status from venta where venta.id = apertura_caja_detalle.venta_id ) as status_venta",
            "apertura_caja_detalle.token_pay",
            "apertura_caja_detalle.created_at",
            "apertura_caja_detalle.created_by",
            "concat_ws(' ',`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`",
        ])
        ->from('apertura_caja_detalle')
        ->leftJoin('user created','apertura_caja_detalle.created_by = created.id')
        ->andWhere(["apertura_caja_id" => $caja_id])
        ->groupBy("apertura_caja_detalle.venta_id, apertura_caja_detalle.token_pay, apertura_caja_detalle.tipo")
        ->all();
    }

    public static function getHistoryMovimientoJsonBtt($arr)
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

        $query =  (new Query())
        ->select([
            "SQL_CALC_FOUND_ROWS `apertura_caja_detalle`.`id`",
            new \yii\db\Expression('@i := @i + 1 as count_item'),
            "apertura_caja_detalle.venta_id",
            "apertura_caja_detalle.tipo",
            '(CASE
                WHEN apertura_caja_detalle.tipo = 10 THEN "VENTA"
                WHEN apertura_caja_detalle.tipo = 20 THEN "CREDITO"
                WHEN apertura_caja_detalle.tipo = 30 THEN "RETIRO"
                WHEN apertura_caja_detalle.tipo = 40 THEN "GASTO"
                WHEN apertura_caja_detalle.tipo = 50 THEN "CANCELACION DE VENTA"
            END) as tipo_text',
            '(CASE
                WHEN apertura_caja_detalle.tipo = 10 THEN (SELECT concat_ws(" ", cliente.nombre, cliente.apellidos) FROM venta inner join cliente on venta.cliente_id = cliente.id where venta.id = apertura_caja_detalle.venta_id)

                WHEN apertura_caja_detalle.tipo = 20 THEN if( ( SELECT credito.venta_id FROM credito inner join credito_abono on credito.id = credito_abono.credito_id  where credito_abono.token_pay = apertura_caja_detalle.token_pay  limit 1) ,(SELECT concat_ws(" ", cliente.nombre, cliente.apellidos) FROM credito inner join venta on credito.venta_id = venta.id inner join cliente on  venta.cliente_id = cliente.id   inner join credito_abono on credito.id = credito_abono.credito_id  where credito_abono.token_pay = apertura_caja_detalle.token_pay  limit 1),(SELECT concat_ws(" ", cliente.nombre, cliente.apellidos) FROM credito  inner join cliente on  credito.cliente_id = cliente.id   inner join credito_abono on credito.id = credito_abono.credito_id  where credito_abono.token_pay = apertura_caja_detalle.token_pay  limit 1))

                WHEN apertura_caja_detalle.tipo = 50 THEN (SELECT concat_ws(" ", cliente.nombre, cliente.apellidos) FROM venta inner join cliente on venta.cliente_id = cliente.id where venta.id = apertura_caja_detalle.venta_id)
            END) as cliente',

            '(CASE
                WHEN apertura_caja_detalle.tipo = 10 THEN ( select sum(cobro_venta.cantidad) from cobro_venta where cobro_venta.venta_id = apertura_caja_detalle.venta_id  )
                WHEN apertura_caja_detalle.tipo = 20 THEN ( select sum(cobro_venta.cantidad) from cobro_venta where cobro_venta.trans_token_credito = apertura_caja_detalle.token_pay  )
                ELSE apertura_caja_detalle.cantidad
            END) as cantidad',
            "( select venta.status from venta where venta.id = apertura_caja_detalle.venta_id ) as status_venta",
            "apertura_caja_detalle.token_pay",
            "apertura_caja_detalle.created_at",
            "apertura_caja_detalle.created_by",
            "concat_ws(' ',`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`",
        ])
        ->from('apertura_caja_detalle ' . new \yii\db\Expression('cross join (select @i := 0) r'))

        ->leftJoin('user created','apertura_caja_detalle.created_by = created.id')
        ->groupBy("apertura_caja_detalle.venta_id, apertura_caja_detalle.token_pay, apertura_caja_detalle.tipo")
        ->orderBy($orderBy)
        ->offset($offset)
        ->limit($limit);


        $query->andWhere(['apertura_caja_id' =>  $filters['caja_id']]);

        if($search)
            $query->andFilterWhere([
                'or',
                ['like', 'id', $search],
                ['like', 'cliente', $search],
            ]);

        $responseArray = [];
        foreach ($query->all() as $key => $item_detail) {

            array_push($responseArray, $item_detail);

            $responseArray[$key]["efectivo"]          = $item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_EFECTIVO, AperturaCajaDetalle::TIPO_VENTA) : CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_EFECTIVO, AperturaCajaDetalle::TIPO_CREDITO);

            $responseArray[$key]["cheque"]            = $item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_CHEQUE, AperturaCajaDetalle::TIPO_VENTA) : CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_CHEQUE, AperturaCajaDetalle::TIPO_CREDITO);

            $responseArray[$key]["transferencia"]     = $item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_TRANFERENCIA, AperturaCajaDetalle::TIPO_VENTA) : CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_TRANFERENCIA, AperturaCajaDetalle::TIPO_CREDITO);

            $responseArray[$key]["tarjeta_credito"]   = $item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_TARJETA_CREDITO, AperturaCajaDetalle::TIPO_VENTA) : CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_TARJETA_CREDITO, AperturaCajaDetalle::TIPO_CREDITO);

            $responseArray[$key]["tarjeta_debito"]    = $item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_TARJETA_DEBITO, AperturaCajaDetalle::TIPO_VENTA) : CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_TARJETA_DEBITO, AperturaCajaDetalle::TIPO_CREDITO);

            $responseArray[$key]["deposito"]          = $item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_DEPOSITO, AperturaCajaDetalle::TIPO_VENTA) : CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_DEPOSITO, AperturaCajaDetalle::TIPO_CREDITO);

            $responseArray[$key]["credito"]           = $item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_CREDITO, AperturaCajaDetalle::TIPO_VENTA) : CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_CREDITO, AperturaCajaDetalle::TIPO_CREDITO);
        }

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        $query->all();

        return [
            'rows'  => $responseArray,
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];
    }


    public static function getHistoryMovimientoOtrasJsonBtt($arr)
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

        $queryApertura = AperturaCaja::findOne($filters['caja_id']);

        $query =  (new Query())
        ->select([
            "SQL_CALC_FOUND_ROWS `cobro_venta`.`id`",
            new \yii\db\Expression('@i := @i + 1 as count_item'),
            "cobro_venta.id",
            "cobro_venta.venta_id",
            '(CASE
                WHEN cobro_venta.tipo = 10 THEN "VENTA"
                WHEN cobro_venta.tipo = 30 THEN "CREDITO"
            END) as tipo_text',

            "cobro_venta.credito_id",
            "cobro_venta.tipo",
            '(CASE
                WHEN cobro_venta.tipo = 10 THEN sum(cobro_venta.cantidad)
                WHEN cobro_venta.tipo = 30 THEN sum(cobro_venta.cantidad) - ( SELECT SUM(credito_abono.cantidad) FROM credito_abono where credito_abono.token_pay = cobro_venta.trans_token_credito and credito_abono.status = '. CreditoAbono::STATUS_CANCEL .' )
                ELSE cobro_venta.cantidad
            END) as cantidad',

            '(CASE
                WHEN cobro_venta.tipo = 10 THEN (SELECT concat_ws(" ", cliente.nombre, cliente.apellidos) FROM venta inner join cliente on venta.cliente_id = cliente.id where venta.id = cobro_venta.venta_id)
                WHEN cobro_venta.tipo = 30 THEN (SELECT concat_ws(" ", cliente.nombre, cliente.apellidos) FROM credito inner join venta on credito.venta_id = venta.id inner join cliente on  venta.cliente_id = cliente.id   inner join credito_abono on credito.id = credito_abono.credito_id  where credito_abono.token_pay = cobro_venta.trans_token_credito  limit 1)

            END) as cliente',

            "cobro_venta.trans_token_venta",
            "cobro_venta.trans_token_credito as token_pay",
            "cobro_venta.created_at",
            "cobro_venta.created_by",
            "cobro_venta.is_cancel",
            "concat_ws(' ',`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`",
        ])
        ->from('cobro_venta ' . new \yii\db\Expression('cross join (select @i := 0) r'))
        ->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id or apertura_caja_detalle.token_pay = cobro_venta.trans_token_credito')
        ->leftJoin('user created','cobro_venta.created_by = created.id')
        ->andWhere([ "and",
            ["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $queryApertura->created_by],
            ["<>","cobro_venta.tipo", CobroVenta::TIPO_COMPRA ],
            ['between','cobro_venta.created_at', $queryApertura->fecha_apertura, ($queryApertura->fecha_cierre ? $queryApertura->fecha_cierre : time()) ]
        ])
        ->groupBy("cobro_venta.venta_id, cobro_venta.trans_token_venta, cobro_venta.trans_token_credito")
        ->orderBy($orderBy)
        ->offset($offset)
        ->limit($limit);


        if($search)
            $query->andFilterWhere([
                'or',
                ['like', 'id', $search],
                ['like', 'cliente', $search],
            ]);

        $responseArray = [];
        foreach ($query->all() as $key => $item_detail) {

            array_push($responseArray, $item_detail);

            $responseArray[$key]["efectivo"]          = $item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_EFECTIVO, AperturaCajaDetalle::TIPO_VENTA) : CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_EFECTIVO, AperturaCajaDetalle::TIPO_CREDITO);

            $responseArray[$key]["cheque"]            = $item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_CHEQUE, AperturaCajaDetalle::TIPO_VENTA) : CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_CHEQUE, AperturaCajaDetalle::TIPO_CREDITO);

            $responseArray[$key]["transferencia"]     = $item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_TRANFERENCIA, AperturaCajaDetalle::TIPO_VENTA) : CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_TRANFERENCIA, AperturaCajaDetalle::TIPO_CREDITO);

            $responseArray[$key]["tarjeta_credito"]   = $item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_TARJETA_CREDITO, AperturaCajaDetalle::TIPO_VENTA) : CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_TARJETA_CREDITO, AperturaCajaDetalle::TIPO_CREDITO);

            $responseArray[$key]["tarjeta_debito"]    = $item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_TARJETA_DEBITO, AperturaCajaDetalle::TIPO_VENTA) : CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_TARJETA_DEBITO, AperturaCajaDetalle::TIPO_CREDITO);

            $responseArray[$key]["deposito"]          = $item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_DEPOSITO, AperturaCajaDetalle::TIPO_VENTA) : CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_DEPOSITO, AperturaCajaDetalle::TIPO_CREDITO);

            $responseArray[$key]["credito"]           = $item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_CREDITO, AperturaCajaDetalle::TIPO_VENTA) : CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_CREDITO, AperturaCajaDetalle::TIPO_CREDITO);
        }

        // Imprime String de la consulta SQL
        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        $query->all();

        return [
            'rows'  => $responseArray,
            'total' => \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar(),
        ];

    }


    //------------------------------------------------------------------------------------------------//
    // FUNCTIONS
    //------------------------------------------------------------------------------------------------//

    /*public function getMontoFaltante()
    {   
        $total_caja = 0;
        foreach ($this->aperturaCajaDetalles as $key => $item) {
            if ($item->status == AperturaCajaDetalle::STATUS_SUCCESS ){
                if ($item->tipo == AperturaCajaDetalle::TIPO_VENTA  || $item->tipo == AperturaCajaDetalle::TIPO_CREDITO)
                    $total_caja = $total_caja + $item->cantidad;

                if ($item->tipo == AperturaCajaDetalle::TIPO_RETIRO ){
                    $total_caja   = $total_caja - $item->cantidad;
                }
            }
        }

        return ($total_caja + $this->cantidad_caja) - $this->total;

    }*/

    //------------------------------------------------------------------------------------------------
    // OPERATIONS
    //------------------------------------------------------------------------------------------------//
    public static function getAperturaActual()
    {
        return self::find()->andWhere(["and",["=", "user_id", Yii::$app->user->identity->id ], [ "=", "status", self::STATUS_PROCESO ] ] )->count() ? true: false;
    }

    public static function getInfoAperturaActual()
    {
        return self::find()->andWhere(["and",["=", "user_id", Yii::$app->user->identity->id ], [ "=", "status", self::STATUS_PROCESO ] ] )->one();
    }


    public static function getTotalCaja()
    {
        $AperturaCaja = self::find()->andWhere(["and",["=", "user_id", Yii::$app->user->identity->id ], [ "=", "status", self::STATUS_PROCESO ] ] )->one();

        $totalCaja = 0;

        if ($AperturaCaja) {
            $totalCaja = floatval($AperturaCaja->cantidad_caja);

            foreach ($AperturaCaja->aperturaCajaDetalles as $key => $apertura_venta) {
                if ($apertura_venta->pertenece == AperturaCajaDetalle::PERTENECE_INGRESO)
                    $totalCaja = $totalCaja + floatval($apertura_venta->cantidad);

                if ($apertura_venta->pertenece == AperturaCajaDetalle::PERTENECE_RETIRO)
                    $totalCaja = $totalCaja - floatval($apertura_venta->cantidad);
            }
        }

        return $totalCaja;

    }
    

    public static function getTotalEfectivoTpv($caja_id)
    {
        $total = 0;

        foreach (AperturaCaja::getAperturaCajaDetallesGroup($caja_id) as $key => $item_detail) {
            /**
             *  BUSCAMOS VENTAS COBRADAS EN EFECTIVO
            **/
            if ($item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA) {
                $total = $total + CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_EFECTIVO, AperturaCajaDetalle::TIPO_VENTA );
            }


            /**
             *  BUSCAMOS CREDITOS COBRADAS EN EFECTIVO
            **/

            if ($item_detail["tipo"] == AperturaCajaDetalle::TIPO_CREDITO) {
                $total = $total + CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_EFECTIVO, AperturaCajaDetalle::TIPO_CREDITO );
            }
        }
        return $total;
    }

    public static function getTotalOtrosTpv($caja_id)
    {

        $AperturaCaja = AperturaCaja::findOne($caja_id);


        /* =====================================================================
        // SUMAMOS TODAS LAS OPERACIONES EN FUERON TOTALMENTE EN CHEQUE y NO SE REGISTRAN COMO INGRESO A LA CAJA
        $query = (new Query())
        ->select([
            "sum(cobro_venta.cantidad) as cantidad",
        ])
        ->from('cobro_venta')
        ->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id')
        ->andWhere([ "and",
            //["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $AperturaCaja->created_by ],
            //["=","cobro_venta.tipo", CobroVenta::TIPO_VENTA ],
            ["=","cobro_venta.metodo_pago", CobroVenta::COBRO_OTRO ],
            [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ['between','cobro_venta.created_at', $AperturaCaja->fecha_apertura, ($AperturaCaja->fecha_cierre ? $AperturaCaja->fecha_cierre : time() )]
        ])
        ->one();
        ============================================================================ */
        $query = (new Query())
        ->select([
            "sum(cobro_venta.cantidad) as cantidad",
            "sum(cobro_venta.cargo_extra) as cargo_extra",
        ])
        ->from('cobro_venta')
        // ->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id')
        ->andWhere([ "and",
            //["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $AperturaCaja->created_by ],
            //["=","cobro_venta.tipo", CobroVenta::TIPO_VENTA ],
            ["=","cobro_venta.metodo_pago", CobroVenta::COBRO_OTRO ],
            [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ['between','cobro_venta.created_at', $AperturaCaja->fecha_apertura, ($AperturaCaja->fecha_cierre ? $AperturaCaja->fecha_cierre : time() )]
        ])
        ->one();

        /**
         * BUSCAMOS SI EXISTE ABONOS CANCELADOS PARA AJUSTAR EL TOTAL
         * */

        $query2 = (new Query())
        ->select([
            "( SELECT SUM(credito_abono.cantidad) FROM credito_abono where credito_abono.token_pay = cobro_venta.trans_token_credito and credito_abono.status = ". CreditoAbono::STATUS_CANCEL ." ) as cantidad ",
        ])
        ->from('cobro_venta')
        //->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id')
        ->andWhere([ "and",
            //["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $AperturaCaja->created_by ],
            //["=","cobro_venta.tipo", CobroVenta::TIPO_VENTA ],
            ["=","cobro_venta.metodo_pago", CobroVenta::COBRO_OTRO ],
            [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ['between','cobro_venta.created_at', $AperturaCaja->fecha_apertura, ($AperturaCaja->fecha_cierre ? $AperturaCaja->fecha_cierre : time() )]
        ])
        ->all();


        $totalCancel = 0 ;

        foreach ($query2 as $key => $item_elemet) {
            if (isset($item_elemet["cantidad"]) && $item_elemet["cantidad"]) {
                $totalCancel = $totalCancel + floatval($item_elemet["cantidad"]);
            }
        }

        // return   floatval($query["cantidad"]);
        return  floatval($query["cantidad"]) + floatval($query["cargo_extra"]) - floatval($totalCancel);
    }

    public static function getTotalChequeTpv($caja_id)
    {

        $AperturaCaja = AperturaCaja::findOne($caja_id);


        // SUMAMOS TODAS LAS OPERACIONES EN FUERON TOTALMENTE EN CHEQUE y NO SE REGISTRAN COMO INGRESO A LA CAJA
        $query = (new Query())
        ->select([
            "sum(cobro_venta.cantidad) as cantidad",
        ])
        ->from('cobro_venta')
        ->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id')
        ->andWhere([ "and",
           // ["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $AperturaCaja->created_by ],
            //["=","cobro_venta.tipo", CobroVenta::TIPO_VENTA ],
            ["=","cobro_venta.metodo_pago", CobroVenta::COBRO_CHEQUE ],
            [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ['between','cobro_venta.created_at', $AperturaCaja->fecha_apertura, ($AperturaCaja->fecha_cierre ? $AperturaCaja->fecha_cierre : time() )]
        ])
        ->one();

         /**
         * BUSCAMOS SI EXISTE ABONOS CANCELADOS PARA AJUSTAR EL TOTAL
         * */

        $query2 = (new Query())
        ->select([
            "( SELECT SUM(credito_abono.cantidad) FROM credito_abono where credito_abono.token_pay = cobro_venta.trans_token_credito and credito_abono.status = ". CreditoAbono::STATUS_CANCEL ." ) as cantidad ",
        ])
        ->from('cobro_venta')
        //->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id')
        ->andWhere([ "and",
            //["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $AperturaCaja->created_by ],
            //["=","cobro_venta.tipo", CobroVenta::TIPO_VENTA ],
            ["=","cobro_venta.metodo_pago", CobroVenta::COBRO_CHEQUE ],
            [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ['between','cobro_venta.created_at', $AperturaCaja->fecha_apertura, ($AperturaCaja->fecha_cierre ? $AperturaCaja->fecha_cierre : time() )]
        ])
        ->all();


        $totalCancel = 0 ;

        foreach ($query2 as $key => $item_elemet) {
            if (isset($item_elemet["cantidad"]) && $item_elemet["cantidad"]) {
                $totalCancel = $totalCancel + floatval($item_elemet["cantidad"]);
            }
        }


        return   floatval($query["cantidad"]) - floatval($totalCancel);
    }

    public static function getTotalTranferenciaTpv($caja_id)
    {

        $AperturaCaja = AperturaCaja::findOne($caja_id);

         // SUMAMOS TODAS LAS OPERACIONES EN FUERON TOTALMENTE EN CHEQUE y NO SE REGISTRAN COMO INGRESO A LA CAJA
        $query = (new Query())
        ->select([
            "sum(cobro_venta.cantidad) as cantidad ",
        ])
        ->from('cobro_venta')
        //->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id')
        ->andWhere([ "and",
            //["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $AperturaCaja->created_by ],
            //["=","cobro_venta.tipo", CobroVenta::TIPO_VENTA ],
            ["=","cobro_venta.metodo_pago", CobroVenta::COBRO_TRANFERENCIA ],
            [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ['between','cobro_venta.created_at', $AperturaCaja->fecha_apertura, ($AperturaCaja->fecha_cierre ? $AperturaCaja->fecha_cierre : time() )]
        ])
        ->one();

        /**
         * BUSCAMOS SI EXISTE ABONOS CANCELADOS PARA AJUSTAR EL TOTAL
         * */

        $query2 = (new Query())
        ->select([
            "( SELECT SUM(credito_abono.cantidad) FROM credito_abono where credito_abono.token_pay = cobro_venta.trans_token_credito and credito_abono.status = ". CreditoAbono::STATUS_CANCEL ." ) as cantidad ",
        ])
        ->from('cobro_venta')
        //->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id')
        ->andWhere([ "and",
            //["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $AperturaCaja->created_by ],
            //["=","cobro_venta.tipo", CobroVenta::TIPO_VENTA ],
            ["=","cobro_venta.metodo_pago", CobroVenta::COBRO_TRANFERENCIA ],
            [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ['between','cobro_venta.created_at', $AperturaCaja->fecha_apertura, ($AperturaCaja->fecha_cierre ? $AperturaCaja->fecha_cierre : time() )]
        ])
        ->all();


        $totalCancel = 0 ;

        foreach ($query2 as $key => $item_elemet) {
            if (isset($item_elemet["cantidad"]) && $item_elemet["cantidad"]) {
                $totalCancel = $totalCancel + floatval($item_elemet["cantidad"]);
            }
        }

        return   floatval($query["cantidad"]) - floatval($totalCancel);
    }

    public static function getTotalTarjetaCreditoTpv($caja_id)
    {

        $AperturaCaja = AperturaCaja::findOne($caja_id);

         // SUMAMOS TODAS LAS OPERACIONES EN FUERON TOTALMENTE EN CHEQUE y NO SE REGISTRAN COMO INGRESO A LA CAJA
        $query = (new Query())
        ->select([
            "sum(cobro_venta.cantidad) as cantidad",
            "sum(cobro_venta.cargo_extra) as cargo_extra",
        ])
        ->from('cobro_venta')
        ->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id')
        ->andWhere([ "and",
            //["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $AperturaCaja->created_by ],
            //["=","cobro_venta.tipo", CobroVenta::TIPO_VENTA ],
            ["=","cobro_venta.metodo_pago", CobroVenta::COBRO_TARJETA_CREDITO ],
            [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ['between','cobro_venta.created_at', $AperturaCaja->fecha_apertura, ($AperturaCaja->fecha_cierre ? $AperturaCaja->fecha_cierre : time() )]
        ])
        ->one();


            /**
         * BUSCAMOS SI EXISTE ABONOS CANCELADOS PARA AJUSTAR EL TOTAL
         * */

        $query2 = (new Query())
        ->select([
            "( SELECT SUM(credito_abono.cantidad) FROM credito_abono where credito_abono.token_pay = cobro_venta.trans_token_credito and credito_abono.status = ". CreditoAbono::STATUS_CANCEL ." ) as cantidad ",
        ])
        ->from('cobro_venta')
        //->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id')
        ->andWhere([ "and",
            //["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $AperturaCaja->created_by ],
            //["=","cobro_venta.tipo", CobroVenta::TIPO_VENTA ],
            ["=","cobro_venta.metodo_pago", CobroVenta::COBRO_TARJETA_CREDITO ],
            [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ['between','cobro_venta.created_at', $AperturaCaja->fecha_apertura, ($AperturaCaja->fecha_cierre ? $AperturaCaja->fecha_cierre : time() )]
        ])
        ->all();


        $totalCancel = 0 ;

        foreach ($query2 as $key => $item_elemet) {
            if (isset($item_elemet["cantidad"]) && $item_elemet["cantidad"]) {
                $totalCancel = $totalCancel + floatval($item_elemet["cantidad"]);
            }
        }


        return floatval($query["cantidad"]) + floatval($query["cargo_extra"]) - floatval($totalCancel);
    }


    public static function getTotalTarjetaDebitoTpv($caja_id)
    {
        $AperturaCaja = AperturaCaja::findOne($caja_id);


         // SUMAMOS TODAS LAS OPERACIONES EN FUERON TOTALMENTE EN CHEQUE y NO SE REGISTRAN COMO INGRESO A LA CAJA
        $query = (new Query())
        ->select([
            "sum(cobro_venta.cantidad) as cantidad",
            "sum(cobro_venta.cargo_extra) as cargo_extra",
        ])
        ->from('cobro_venta')
        ->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id')
        ->andWhere([ "and",
            //["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $AperturaCaja->created_by ],
            //["=","cobro_venta.tipo", CobroVenta::TIPO_VENTA ],
            ["=","cobro_venta.metodo_pago", CobroVenta::COBRO_TARJETA_DEBITO ],
            [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ['between','cobro_venta.created_at', $AperturaCaja->fecha_apertura, ($AperturaCaja->fecha_cierre ? $AperturaCaja->fecha_cierre : time() )]
        ])
        ->one();


        /**
         * BUSCAMOS SI EXISTE ABONOS CANCELADOS PARA AJUSTAR EL TOTAL
         * */

        $query2 = (new Query())
        ->select([
            "( SELECT SUM(credito_abono.cantidad) FROM credito_abono where credito_abono.token_pay = cobro_venta.trans_token_credito and credito_abono.status = ". CreditoAbono::STATUS_CANCEL ." ) as cantidad ",
        ])
        ->from('cobro_venta')
        //->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id')
        ->andWhere([ "and",
            //["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $AperturaCaja->created_by ],
            //["=","cobro_venta.tipo", CobroVenta::TIPO_VENTA ],
            ["=","cobro_venta.metodo_pago", CobroVenta::COBRO_TARJETA_DEBITO ],
            [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ['between','cobro_venta.created_at', $AperturaCaja->fecha_apertura, ($AperturaCaja->fecha_cierre ? $AperturaCaja->fecha_cierre : time() )]
        ])
        ->all();


        $totalCancel = 0 ;

        foreach ($query2 as $key => $item_elemet) {
            if (isset($item_elemet["cantidad"]) && $item_elemet["cantidad"]) {
                $totalCancel = $totalCancel + floatval($item_elemet["cantidad"]);
            }
        }


        return  floatval($query["cantidad"]) + floatval($query["cargo_extra"]) - floatval($totalCancel);
    }

    public static function getTotalDepositoTpv($caja_id)
    {
        $AperturaCaja = AperturaCaja::findOne($caja_id);

        // SUMAMOS TODAS LAS OPERACIONES EN FUERON TOTALMENTE EN CHEQUE y NO SE REGISTRAN COMO INGRESO A LA CAJA
        $query = (new Query())
        ->select([
            "sum(cobro_venta.cantidad) as cantidad",
        ])
        ->from('cobro_venta')
        //->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id')
        ->andWhere([ "and",
            //["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $AperturaCaja->created_by ],
            //["=","cobro_venta.tipo", CobroVenta::TIPO_VENTA ],
            ["=","cobro_venta.metodo_pago", CobroVenta::COBRO_DEPOSITO ],
            [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ['between','cobro_venta.created_at', $AperturaCaja->fecha_apertura, ($AperturaCaja->fecha_cierre ? $AperturaCaja->fecha_cierre : time() )]
        ])
        ->one();

        /**
         * BUSCAMOS SI EXISTE ABONOS CANCELADOS PARA AJUSTAR EL TOTAL
         * */

        $query2 = (new Query())
        ->select([
            "( SELECT SUM(credito_abono.cantidad) FROM credito_abono where credito_abono.token_pay = cobro_venta.trans_token_credito and credito_abono.status = ". CreditoAbono::STATUS_CANCEL ." ) as cantidad ",
        ])
        ->from('cobro_venta')
        //->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id')
        ->andWhere([ "and",
            //["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $AperturaCaja->created_by ],
            //["=","cobro_venta.tipo", CobroVenta::TIPO_VENTA ],
            ["=","cobro_venta.metodo_pago", CobroVenta::COBRO_DEPOSITO ],
            [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ['between','cobro_venta.created_at', $AperturaCaja->fecha_apertura, ($AperturaCaja->fecha_cierre ? $AperturaCaja->fecha_cierre : time() )]
        ])
        ->all();


        $totalCancel = 0 ;

        foreach ($query2 as $key => $item_elemet) {
            if (isset($item_elemet["cantidad"]) && $item_elemet["cantidad"]) {
                $totalCancel = $totalCancel + floatval($item_elemet["cantidad"]);
            }
        }


        return   floatval($query["cantidad"]) - floatval($totalCancel);
    }

    public static function getTotalCreditoPayTpv($caja_id)
    {
        $total = 0;

        $AperturaCaja = AperturaCaja::findOne($caja_id);

        foreach (AperturaCaja::getAperturaCajaDetallesGroup($caja_id) as $key => $item_detail) {
            /**
             *  BUSCAMOS VENTAS COBRADAS EN CREDITO
            **/
            if ($item_detail["tipo"] == AperturaCajaDetalle::TIPO_VENTA) {
                $total = $total + CobroVenta::getTotalMetodoTpv($item_detail["venta_id"], CobroVenta::COBRO_CREDITO, AperturaCajaDetalle::TIPO_VENTA );
            }

            /**
             *  BUSCAMOS CREDITOS COBRADAS EN TARJETA DEBITO
            **/

            if ($item_detail["tipo"] == AperturaCajaDetalle::TIPO_CREDITO) {
                $total = $total + CobroVenta::getTotalMetodoTpv($item_detail["token_pay"], CobroVenta::COBRO_CREDITO, AperturaCajaDetalle::TIPO_CREDITO );
            }
        }

         $query = (new Query())
        ->select([
            "sum(cobro_venta.cantidad) as cantidad",
        ])
        ->from('cobro_venta')
        ->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id')
        ->andWhere([ "and",
            ["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $AperturaCaja->created_by ],
            //["=","cobro_venta.tipo", CobroVenta::TIPO_VENTA ],
            ["=","cobro_venta.metodo_pago", CobroVenta::COBRO_CREDITO ],
            [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ['between','cobro_venta.created_at', $AperturaCaja->fecha_apertura, ($AperturaCaja->fecha_cierre ? $AperturaCaja->fecha_cierre : time() )]
        ])
        ->one();

        return $total + floatval($query["cantidad"]);
    }

    public static function getTotalVentaTpv($caja_id)
    {
        $total = 0;
        $aperturaCaja = self::findOne($caja_id);
        foreach ($aperturaCaja->aperturaCajaDetalles as $key => $item_detail) {
            if ($item_detail->tipo == AperturaCajaDetalle::TIPO_VENTA) {
                if (AperturaCajaDetalle::isVigentePago($item_detail->venta_id,$item_detail->tipo))
                    $total = $total + $item_detail->cantidad;

            }
        }
        return $total;
    }

    public static function getTotalCreditoTpv($caja_id)
    {
        $total = 0;
        $aperturaCaja = self::findOne($caja_id);
        foreach ($aperturaCaja->aperturaCajaDetalles as $key => $item_detail) {
            if ($item_detail->tipo == AperturaCajaDetalle::TIPO_CREDITO) {
                if (AperturaCajaDetalle::isVigentePago($item_detail->token_pay,$item_detail->tipo))
                    $total = $total + $item_detail->cantidad;
            }
        }
        return $total;
    }

    public static function getTotalRetiroTpv($caja_id)
    {
        $total = 0;
        $aperturaCaja = self::findOne($caja_id);
        foreach ($aperturaCaja->aperturaCajaDetalles as $key => $item_detail) {
            if ($item_detail->tipo == AperturaCajaDetalle::TIPO_RETIRO) {
                $total = $total + $item_detail->cantidad;
            }
        }
        return $total;
    }
    
    public static function getTotalGastoTpv($caja_id)
    {
        $total = 0;
        $aperturaCaja = self::findOne($caja_id);
        foreach ($aperturaCaja->aperturaCajaDetalles as $key => $item_detail) {
            if ($item_detail->tipo == AperturaCajaDetalle::TIPO_GASTO) {
                $total = $total + $item_detail->cantidad;
            }
        }
        return $total;
    }

    
   

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;

            }else{
                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;
            }

            return true;

        } else
            return false;
    }
}
