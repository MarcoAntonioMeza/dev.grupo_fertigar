<?php
namespace app\models\cobro;

use Yii;
use yii\db\Query;
use app\models\Esys;
use app\models\user\User;
use app\models\venta\Venta;
use app\models\credito\Credito;
use app\models\reparto\Reparto;
use app\models\apertura\AperturaCaja;
use app\models\apertura\AperturaCajaDetalle;
use app\models\credito\CreditoTokenPay;
use app\models\credito\CreditoAbono;
use app\models\venta\VentaTokenPay;
use app\models\sucursal\Sucursal;
use app\models\producto\Producto;

/**
 * This is the model class for table "cobro_rembolso_envio".
 *
 * @property int $id ID
 * @property int $envio_id Envio ID
 * @property int $tipo Tipo
 * @property int $metodo_pago Metodo de pago
 * @property double $cantidad Cantidad
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property User $createdBy
 * @property Envio $envio
 */
class CobroVenta extends \yii\db\ActiveRecord
{

    const COBRO_EFECTIVO        = 10;
    const COBRO_CHEQUE          = 20;
    const COBRO_TRANFERENCIA    = 30;
    const COBRO_TARJETA_CREDITO = 40;
    const COBRO_TARJETA_DEBITO  = 50;
    const COBRO_DEPOSITO        = 60;
    const COBRO_CREDITO         = 70;
    const COBRO_OTRO            = 80;


    const ISCOBRO_MEX            = 10;


    const IS_CANCEL_ON            = 10;
    const IS_CANCEL_OFF            = 20;


    const PERTENECE_COBRO      = 10;
    const PERTENECE_PAGO       = 20;
    const PERTENECE_REEMBOLSO  = 30;

    public static $perteneceList = [
        self::PERTENECE_COBRO      => 'Cobro',
        self::PERTENECE_PAGO       => 'Pago',
        self::PERTENECE_REEMBOLSO  => 'Reembolso',
    ];

    const TIPO_VENTA        = 10;
    const TIPO_COMPRA       = 20;
    const TIPO_CREDITO      = 30;
    const TIPO_REEMBOLSO    = 40;

    public static $tipoList = [
        self::TIPO_VENTA        => 'VENTA',
        self::TIPO_COMPRA       => 'COMPRA',
        self::TIPO_CREDITO      => 'CREDITO',
        self::TIPO_REEMBOLSO    => 'REEMBOLSO',
    ];


    public static $servicioList = [
        self::COBRO_EFECTIVO        => 'Efectivo',
        self::COBRO_CHEQUE          => 'Cheque',
        self::COBRO_TRANFERENCIA    => 'Tranferencia',
        self::COBRO_TARJETA_CREDITO => 'Tarjeta de credito',
        self::COBRO_TARJETA_DEBITO  => 'Tarjeta de debito',
        self::COBRO_DEPOSITO        => 'Deposito',
        self::COBRO_CREDITO         => 'Credito',

    ];

    public static $servicioTpvList = [
        self::COBRO_EFECTIVO        => 'Efectivo',
        self::COBRO_CHEQUE          => 'Cheque',
        self::COBRO_TRANFERENCIA    => 'Tranferencia',
        self::COBRO_TARJETA_CREDITO => 'Tarjeta de credito',
        self::COBRO_TARJETA_DEBITO  => 'Tarjeta de debito',
        self::COBRO_DEPOSITO        => 'Deposito',
        self::COBRO_CREDITO         => 'Credito',
        self::COBRO_OTRO            => 'Otro',

    ];


    public static $servicioListAll = [
        self::COBRO_EFECTIVO        => 'Efectivo',
        self::COBRO_CHEQUE          => 'Cheque',
        self::COBRO_TRANFERENCIA    => 'Tranferencia',
        self::COBRO_TARJETA_CREDITO => 'Tarjeta de credito',
        self::COBRO_TARJETA_DEBITO  => 'Tarjeta de debito',
        self::COBRO_DEPOSITO        => 'Deposito',
        self::COBRO_OTRO            => 'Otro',

    ];

    public $cobroVentaArray;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cobro_venta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['metodo_pago','cantidad'], 'required'],
            [['is_cancel'], 'default', 'value' =>  CobroVenta::IS_CANCEL_OFF ],
            [['venta_id', 'compra_id','tipo', 'tipo_cobro_pago', 'credito_id','fecha_credito', 'metodo_pago', 'created_at', 'created_by','producto_id'], 'integer'],
            [['cantidad', 'cantidad_pago','cargo_extra','cantidad_recibe'], 'number'],
            [['nota','trans_token_credito','nota_otro'], 'string'],
            [['cobroVentaArray'], 'safe'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['venta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Venta::className(), 'targetAttribute' => ['venta_id' => 'id']],
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
            'credito_id' => 'Credito ID',
            'compra_id' => 'Compra ID',
            'tipo' => 'Tipo',
            'metodo_pago' => 'Metodo Pago',
            'cantidad' => 'Cantidad',
            'cantidad_pago' => 'Pago',
            'nota' => 'Nota',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVenta()
    {
        return $this->hasOne(Venta::className(), ['id' => 'venta_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompra()
    {
        return $this->hasOne(Compra::className(), ['id' => 'compra_id']);
    }

     /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducto()
    {
        return $this->hasOne(Producto::className(), ['id' => 'producto_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCredito()
    {
        return $this->hasOne(Credito::className(), ['id' => 'credito_id']);
    }

    public static function getPagoCredito($credito_id)
    {
        return self::find()->andWhere([ "and",
            ["=","credito_id",$credito_id],
            ["=","tipo", self::TIPO_CREDITO]
        ])->sum("cantidad");
    }

    public static function getVentaRutaOperacion($token_pay)
    {
        return self::find()->andWhere([ "and",
            ["=","trans_token_venta",$token_pay],
            ["=","is_cancel", CobroVenta::IS_CANCEL_OFF]
        ])->all();
    }

    public static function getTotalOperacionMetodo( $reparto_id, $metodo_pago)
    {
        $result  = 0;

        $ventas = Venta::find()
        ->innerJoin("venta_token_pay","venta.id = venta_token_pay.venta_id")
        ->andWhere(["and",
            [ "=", "reparto_id", $reparto_id],
            [ "=", "status",  Venta::STATUS_VENTA ],
            ["IS NOT","venta.cliente_id", new \yii\db\Expression('null') ],
        ])
        ->groupBy("venta.cliente_id, venta_token_pay.token_pay")
        ->all();

        foreach ($ventas as $key => $item_venta) {
            $ventaToken = VentaTokenPay::find()->andWhere(["venta_id" => $item_venta->id ])->groupBy('token_pay')->all();
            if ($ventaToken) {
                foreach ($ventaToken as $key => $item_token) {
                    $CobroVenta = CobroVenta::find()->andWhere(["and",
                        [ "=","trans_token_venta", $item_token->token_pay ],
                        [ "=","metodo_pago", $metodo_pago ],
                        [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
                    ])->one();

                    $result = $result + floatval((isset($CobroVenta->cantidad)  && $CobroVenta->cantidad ? $CobroVenta->cantidad : 0 ));
                }
            }else{
                $CobroVenta = CobroVenta::find()->andWhere(["and",
                        [ "=","venta_id", $item_venta->id ],
                        [ "=","metodo_pago", $metodo_pago ],
                        [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
                    ])->one();


                $result = $result + floatval((isset($CobroVenta->cantidad)  && $CobroVenta->cantidad ? $CobroVenta->cantidad : 0 ));
            }
        }


        $ventasDirectas = Venta::find()
        ->leftJoin("venta_token_pay","venta.id = venta_token_pay.venta_id")
        ->andWhere(["and",
            [ "=", "reparto_id", $reparto_id],
            [ "=", "status",  Venta::STATUS_VENTA ],
            ["IS NOT","venta.cliente_id", new \yii\db\Expression('null') ],
            ["IS","venta_token_pay.venta_id", new \yii\db\Expression('null') ],
        ])
        ->all();

        foreach ($ventasDirectas as $key => $item_venta) {
            $CobroVenta = CobroVenta::find()->andWhere(["and",
                    [ "=","venta_id", $item_venta->id ],
                    [ "=","metodo_pago", $metodo_pago ],
                    [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
                ])->one();

            $result = $result + floatval((isset($CobroVenta->cantidad)  && $CobroVenta->cantidad ? $CobroVenta->cantidad : 0 ));
        }


        $ventaPublicoGeneral = Venta::find()->andWhere(["and",
            [ "=", "reparto_id", $reparto_id],
            [ "=", "status",  Venta::STATUS_VENTA ],
            ["IS","venta.cliente_id", new \yii\db\Expression('null') ],
        ])
        ->all();

        foreach ($ventaPublicoGeneral as $key => $item_venta) {
            $ventaToken = VentaTokenPay::find()->andWhere(["venta_id" => $item_venta->id ])->groupBy('token_pay')->all();
            if ($ventaToken) {
                foreach ($ventaToken as $key => $item_token) {
                    $CobroVenta = CobroVenta::find()->andWhere(["and",
                        [ "=","trans_token_venta", $item_token->token_pay ],
                        [ "=","metodo_pago", $metodo_pago ],
                        [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
                    ])->one();

                    $result = $result + floatval((isset($CobroVenta->cantidad)  && $CobroVenta->cantidad ? $CobroVenta->cantidad : 0 ));
                }
            }else{
                $CobroVenta = CobroVenta::find()->andWhere(["and",
                        [ "=","venta_id", $item_venta->id ],
                        [ "=","metodo_pago", $metodo_pago ],
                        [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
                    ])->one();
                $result = $result + floatval((isset($CobroVenta->cantidad)  && $CobroVenta->cantidad ? $CobroVenta->cantidad : 0 ));
            }
        }

        return $result;
    }


    public static function getTotalOperacionCreditoMetodo( $reparto_id, $metodo_pago)
    {
        $result  = 0;


        $creditoArray   = [];
        $reparto        = Reparto::findOne($reparto_id);
        $repartidores   = User::find()->andWhere(["sucursal_id" => $reparto->sucursal_id ])->all();

        //BUSCAMOS LOS USUARIOS ENCARGDOS DE ESTA RUTA
        $item_user      = [];
        foreach ($repartidores as $key => $item_repartido) {
            array_push($item_user, $item_repartido->id);
        }

        // BUSCAMOS LOS COBROS REGISTRADOS DURANTE EL REPARTO
        $cobroVenta =  CobroVenta::find()
        ->andWhere(["and",
            ["=","tipo", CobroVenta::TIPO_CREDITO ],
            ['IN','created_by', $item_user ],
            ["=","metodo_pago", $metodo_pago ],
            ['between','created_at', $reparto->created_at,( $reparto->cierre_reparto ? $reparto->cierre_reparto : time()) ]
        ])->all();

        foreach ($cobroVenta as $key => $item_cobro) {
            $CreditoAbono = CreditoTokenPay::getCreditoAbono($item_cobro->trans_token_credito);
            if ($CreditoAbono->status == CreditoAbono::STATUS_ACTIVE) {
                $result = $result + floatval($item_cobro->cantidad);
            }
        }

        return $result;
    }

    public static function getIsPagoCredito( $venta_id )
    {
        $result  = false;
        $ventaToken = VentaTokenPay::find()->andWhere(["venta_id" => $venta_id ])->groupBy('token_pay')->all();

        if ($ventaToken) {
            foreach ($ventaToken as $key => $item_token) {
                $CobroVenta = CobroVenta::find()->andWhere(["and",
                    [ "=","trans_token_venta", $item_token->token_pay ],
                    [ "=","metodo_pago", CobroVenta::COBRO_CREDITO ],
                ])->one();

                $result = isset($CobroVenta->cantidad)  && $CobroVenta->cantidad ? true :  false;
            }
        }else{
            $CobroVenta = CobroVenta::find()->andWhere(["and",
                [ "=","venta_id", $venta_id ],
                [ "=","metodo_pago", CobroVenta::COBRO_CREDITO ],
            ])->one();

            $result = isset($CobroVenta->cantidad)  && $CobroVenta->cantidad ? true :  false;
        }

        return $result;
    }

    public static function getPagoCreditoVenta( $venta_id )
    {
        $result  = 0;
        $ventaToken = VentaTokenPay::find()->andWhere(["venta_id" => $venta_id ])->groupBy('token_pay')->all();

        if ($ventaToken) {
            foreach ($ventaToken as $key => $item_token) {
                $CobroVenta = CobroVenta::find()->andWhere(["and",
                    [ "=","trans_token_venta", $item_token->token_pay ],
                    [ "=","metodo_pago", CobroVenta::COBRO_CREDITO ],
                ])->one();

                $result = floatval((isset($CobroVenta->cantidad)  && $CobroVenta->cantidad ? $CobroVenta->cantidad : 0 ));

            }
        }

        $GetVenta = Venta::findOne($venta_id);
        $query = (new Query())
        ->select([
            "venta.id",
        ])
        ->from("venta")
        ->andWhere(["and",
            ["=","venta.reparto_id", $GetVenta->reparto_id],
            ["=","venta.cliente_id", $GetVenta->cliente_id],
            ["=","venta.status", Venta::STATUS_VENTA ],
        ])->all();

        foreach ($query as $key => $item_venta) {
            $CobroVenta = CobroVenta::find()->andWhere(["and",
                    [ "=","venta_id", $item_venta["id"] ],
                    [ "=","metodo_pago", CobroVenta::COBRO_CREDITO ],
            ])->one();

            $result = $result + floatval((isset($CobroVenta->cantidad)  && $CobroVenta->cantidad ? $CobroVenta->cantidad : 0 ));
        }


        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function getCompraAll($compra_id)
    {
        return self::find()->andWhere(["compra_id" => $compra_id])->all();
    }

    public static function getTotalAbonado($token_pay)
    {
        return self::find()->andWhere([ "trans_token_credito" => $token_pay])->sum('cantidad');
    }

    public static function getTokenPayAll($token_pay)
    {
        return self::find()->andWhere([ "trans_token_credito" => $token_pay])->all();
    }

    public function saveCobroVenta($venta_id)
    {
        $cobroVenta = json_decode($this->cobroVentaArray);

        if ($cobroVenta) {
            foreach ($cobroVenta as $key => $cobro) {
                if ($cobro->origen  ==  1 ) {
                    $CobroVenta  =  new CobroVenta();
                    $CobroVenta->venta_id       = $venta_id;
                    $CobroVenta->tipo           = self::TIPO_VENTA;
                    $CobroVenta->tipo_cobro_pago= self::PERTENECE_COBRO;
                    $CobroVenta->metodo_pago    = $cobro->metodo_pago_id;
                    $CobroVenta->cantidad       = $cobro->cantidad;
                    $CobroVenta->cantidad_pago       = $cobro->cantidad_pago;
                    $CobroVenta->cargo_extra    = $cobro->cargo_extra;
                    $CobroVenta->nota_otro       = $cobro->nota_otro;
                    $CobroVenta->fecha_credito  = $CobroVenta->metodo_pago == CobroVenta::COBRO_CREDITO ? Esys::stringToTimeUnix($cobro->fecha_credito)   : null;
                    $CobroVenta->save();

                    if ($CobroVenta->metodo_pago == CobroVenta::COBRO_EFECTIVO) {
                        $AperturaCaja = AperturaCaja::find()->andWhere(["and",["=", "user_id", Yii::$app->user->identity->id ], [ "=", "status", AperturaCaja::STATUS_PROCESO ] ] )->one();

                        if (isset($AperturaCaja->id)) {
                            $AperturaCajaDetalle = new AperturaCajaDetalle();
                            $AperturaCajaDetalle->apertura_caja_id  = $AperturaCaja->id;
                            $AperturaCajaDetalle->tipo              = AperturaCajaDetalle::TIPO_VENTA;
                            $AperturaCajaDetalle->venta_id          = $venta_id;
                            $AperturaCajaDetalle->cantidad          = $CobroVenta->cantidad;
                            $AperturaCajaDetalle->status            = AperturaCajaDetalle::STATUS_SUCCESS;
                            $AperturaCajaDetalle->save();
                        }

                    }

                    if ($CobroVenta->metodo_pago == CobroVenta::COBRO_CREDITO ) {
                        $Credito = new  Credito();
                        $Credito->venta_id      = $venta_id;
                        $Credito->monto         = $CobroVenta->cantidad;
                        $Credito->tipo          = CobroVenta::PERTENECE_COBRO;
                        $Credito->fecha_credito = $CobroVenta->fecha_credito;
                        $Credito->save();
                    }
                }
            }
        }
        return true;
    }

    public static function getTotalMetodoTpv($item_id, $metodo_pago, $tipo)
    {
        $CobroVenta                 = null;
        $result                     = 0;
        $CreditoAbonoCancelado      = 0;

        if ($tipo == AperturaCajaDetalle::TIPO_VENTA) {
            $CobroVenta = CobroVenta::find()->andWhere(["and",
                [ "=","venta_id", $item_id ],
                [ "=","metodo_pago", $metodo_pago ],
                [ "=","is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ])->all();

        }

        if ($tipo == AperturaCajaDetalle::TIPO_CREDITO) {
            $CobroVenta = CobroVenta::find()->andWhere(["and",
                [ "=","trans_token_credito", $item_id ],
                [ "=","metodo_pago", $metodo_pago ],
            ])->all();

            /*SI EXISTE UN COBRO REGISTRADO, CONSULTAMOS LOS ABONOS CANCELADO */
            if (count($CobroVenta) > 0 ) {

                /*RETORNA TODOS LOS ABONOS CANCELADOS*/
                $query = (new Query())
                ->select([
                    "credito_abono.token_pay as token_pay",
                    "credito_abono.cantidad as total_abonado",
                    "cobro_venta.cantidad as total_metodo_pago",
                ])
                ->from('credito_abono')
                ->innerJoin("cobro_venta", "credito_abono.token_pay = cobro_venta.trans_token_credito")
                ->andWhere([ "and",
                    [ "=", "credito_abono.token_pay", $item_id ],
                    [ "=", "credito_abono.status", CreditoAbono::STATUS_CANCEL ],
                    [ "=", "cobro_venta.metodo_pago", $metodo_pago ],
                ])->all();

                if ($query){



                    foreach ($query as $key => $item_query) {
                        $CreditoAbonoCancelado = $CreditoAbonoCancelado + ($item_query["total_metodo_pago"] >= $item_query["total_abonado"]   ? floatval($item_query["total_abonado"]) : floatval($item_query["total_metodo_pago"]) - floatval($item_query["total_abonado"]) ) ;
                        /*if ($query["token_pay"] == "51e27aed70b16617ab288b17dd0d65df") {
                            echo "<pre>";
                            print_r($CreditoAbonoCancelado);
                            die();
                        }*/
                    }

                }
            }
        }

        foreach ($CobroVenta as $key => $item_cobro) {
            $result = ( $result + $item_cobro->cantidad ) + $item_cobro->cargo_extra;
        }
        return floatval($result) - floatval($CreditoAbonoCancelado);
    }

    public static function getPagosMetodoTpv($item_id, $tipo)
    {
        $CobroVenta = null;

        //VENTA EN RUTA
        if ($tipo == 10) {
             $CobroVenta = CobroVenta::find()->andWhere(["and",
                [ "=","venta_id", $item_id ],
            ])->all();
        }

        //VENTA POR PREVENTA
        if ($tipo == 20) {
             $CobroVenta = CobroVenta::find()->andWhere(["and",
                [ "=","trans_token_venta", $item_id ],
            ])->all();
        }

        //COBRO POR CREDITO
        if ($tipo == 30) {
             $CobroVenta = CobroVenta::find()->andWhere(["and",
                [ "=","trans_token_credito", $item_id ],
            ])->all();
        }

        return $CobroVenta;
    }


    public static function getIsEfectivoTpv($item_id, $tipo)
    {
        $CobroVenta = [];

        if ($tipo == AperturaCajaDetalle::TIPO_VENTA) {
            $CobroVenta = CobroVenta::find()->andWhere(["and",
                [ "=","venta_id", $item_id ],
                //[ "=","metodo_pago", CobroVenta::COBRO_EFECTIVO ],
            ])->all();
        }

         if ($tipo == AperturaCajaDetalle::TIPO_CREDITO) {
            $CobroVenta = CobroVenta::find()->andWhere(["and",
                [ "=","trans_token_credito", $item_id ],
                //[ "=","metodo_pago", CobroVenta::COBRO_EFECTIVO ],
            ])->all();
        }
        return $CobroVenta;
    }

    public static function getVentaRutaCredito($reparto_id)
    {
        $creditoArray   = [];
        $reparto        = Reparto::findOne($reparto_id);
        $repartidores   = User::find()->andWhere(["sucursal_id" => $reparto->sucursal_id ])->all();

        $item_user      = [];
        foreach ($repartidores as $key => $item_repartido) {
            array_push($item_user, $item_repartido->id);
        }


        $cobroVenta =  (new Query())
        ->select([
            "cobro_venta.id", 
            "SUM(cantidad) AS cantidad", 
            "trans_token_credito",
            "(SELECT SUM(cv_trans.cantidad) FROM cobro_venta cv_trans WHERE cv_trans.id = cobro_venta.id and cv_trans.metodo_pago = ". CobroVenta::COBRO_TRANFERENCIA .") as trans",
            "(SELECT SUM(cv_cheque.cantidad) FROM cobro_venta cv_cheque WHERE cv_cheque.trans_token_credito = cobro_venta.trans_token_credito and cv_cheque.metodo_pago = ". CobroVenta::COBRO_CHEQUE .") as cheque",
            "(SELECT SUM(cv_deposito.cantidad) FROM cobro_venta cv_deposito WHERE cv_deposito.id = cobro_venta.id and cv_deposito.metodo_pago = ". CobroVenta::COBRO_DEPOSITO .") as deposito",
            "(SELECT SUM(cv_otro.cantidad) FROM cobro_venta cv_otro WHERE cv_otro.id = cobro_venta.id and cv_otro.metodo_pago = ". CobroVenta::COBRO_OTRO .") as otro",
        ])
        ->from('cobro_venta')
        ->andWhere(["and",
            ["=","tipo", CobroVenta::TIPO_CREDITO ],
            ['IN','created_by', $item_user ],
            ['between','created_at', $reparto->created_at,( $reparto->cierre_reparto ? $reparto->cierre_reparto : time()) ]
        ])
        ->groupBy("trans_token_credito")
        ->all();



        foreach ($cobroVenta as $key => $item_cobro) {
            $creditoToken = CreditoTokenPay::getCreditoToken($item_cobro["trans_token_credito"]);
            $is_add = true;
            foreach ($creditoToken as $key_credito => $item_credito) {

                $CreditoAbono = CreditoTokenPay::getCreditoAbono($item_credito->token_pay);

                if ($CreditoAbono->status == CreditoAbono::STATUS_ACTIVE) {
                    foreach ($creditoArray as $key_add => $item_add) {
                        if ($item_add["token_pay"] == $item_credito->token_pay) {
                            $is_add = false;
                            $creditoArray[$key_add]["credito_ids"] = $item_add["credito_ids"] ."#".str_pad( $item_credito->credito_id,6,"0",STR_PAD_LEFT) . " ";
                        }
                    }
                    if ($is_add) {
                        $cliente_id = $item_credito->credito->cliente_id ? $item_credito->credito->cliente_id  : Venta::findOne($item_credito->credito->venta_id)->cliente_id;
                        $totales = Credito::getTotalesCredito( $cliente_id  , Credito::TIPO_CLIENTE);

                       

                        array_push($creditoArray,[
                            "credito_ids"    => "#". str_pad( $item_credito->credito_id,6,"0",STR_PAD_LEFT) . " ",
                            "cliente"       => $item_credito->credito->cliente_id ? $item_credito->credito->cliente->nombreCompleto : ( $item_credito->credito->venta_id ? Venta::findOne($item_credito->credito->venta_id)->cliente->nombreCompleto : '' ),
                            "total_pago"        => $item_cobro["cantidad"],
                            "transferencia"     => '$'. number_format($item_cobro["trans"],2),
                            "cheque"            => '$'. number_format($item_cobro["cheque"],2),
                            "deposito"          => '$'. number_format($item_cobro["deposito"],2),
                            "otro"              => '$'. number_format($item_cobro["otro"],2),
                            "token_pay"         => $item_cobro["trans_token_credito"],
                            "total_credito"     => $totales["total_credito"],
                            "total_pagado"      => $item_credito->credito->monto_pagado,
                            "total_por_pagado"  => $totales["total_por_pagar"],
                        ]);
                    }
                }
            }
        }

        return $creditoArray;
    }


    public static function getOtrasOperaciones($caja_id,$apertura, $cierre, $created_by)
    {
        return (new Query())
        ->select([
            "cobro_venta.id",
            "cobro_venta.venta_id",
            "cobro_venta.credito_id",
            "cobro_venta.tipo",
            "sum(cobro_venta.cantidad) as cantidad_venta",
            "sum(cobro_venta.cantidad) - ( SELECT SUM(credito_abono.cantidad) FROM credito_abono where credito_abono.token_pay = cobro_venta.trans_token_credito and credito_abono.status = ". CreditoAbono::STATUS_CANCEL ." ) as cantidad_credito",
            "cobro_venta.trans_token_venta",
            "cobro_venta.trans_token_credito as token_pay",
            "cobro_venta.created_at",
            "cobro_venta.created_by",
            "cobro_venta.is_cancel",
            "concat_ws(' ',`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`",
        ])
        ->from('cobro_venta')
        ->leftJoin('apertura_caja_detalle','cobro_venta.venta_id = apertura_caja_detalle.venta_id or apertura_caja_detalle.token_pay = cobro_venta.trans_token_credito')
        ->leftJoin('user created','cobro_venta.created_by = created.id')
        ->andWhere([ "and",
            ["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["=","cobro_venta.created_by", $created_by ],
            ["<>","cobro_venta.tipo", self::TIPO_COMPRA ],
            ['between','cobro_venta.created_at', $apertura, $cierre ]
        ])
        ->groupBy("cobro_venta.venta_id, cobro_venta.trans_token_venta, cobro_venta.trans_token_credito")
        ->all();
    }

    public static function getDireccionOperaciones($caja_id,$apertura, $cierre, $created_by)
    {
        return (new Query())
        ->select([
            "cobro_venta.id",
            "cobro_venta.venta_id",
            "cobro_venta.credito_id",
            "cobro_venta.tipo",
            "sum(cobro_venta.cantidad) as cantidad_venta",
            "sum(cobro_venta.cantidad) as cantidad_credito",
            "cobro_venta.trans_token_venta",
            "cobro_venta.trans_token_credito as token_pay",
            "cobro_venta.created_at",
            "cobro_venta.created_by",
            "concat_ws(' ',`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`",
        ])
        ->from('cobro_venta')
        ->leftJoin('apertura_caja_detalle','apertura_caja_detalle.token_pay = cobro_venta.trans_token_credito')
        ->leftJoin('user created','cobro_venta.created_by = created.id')
        ->leftJoin('sucursal','created.sucursal_id = sucursal.id')
        ->andWhere([ "and",
            ["IS","apertura_caja_id", new \yii\db\Expression('null')],
            ["<>","cobro_venta.created_by", $created_by ],
            ["=","cobro_venta.tipo", CobroVenta::TIPO_CREDITO ],
            ["<>","sucursal.tipo", Sucursal::TIPO_RUTA ],
            ['between','cobro_venta.created_at', $apertura, $cierre ]
        ])
        ->groupBy("cobro_venta.trans_token_credito")
        ->all();
    }



    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? ( $this->created_by ? $this->created_by : Yii::$app->user->identity->id) : $this->created_by;

            }

            return true;

        } else
            return false;
    }
}
