<?php
namespace app\models\credito;

use Yii;
use yii\db\Query;
use app\models\cliente\Cliente;
use app\models\user\User;
use app\models\compra\Compra;
use app\models\venta\Venta;
use app\models\proveedor\Proveedor;
use app\models\cobro\CobroVenta;
use app\models\credito\CreditoTokenPay;
use app\models\venta\VentaTokenPay;
use app\models\sucursal\Sucursal;
use app\models\reparto\Reparto;

/**
 * This is the model class for table "credito".
 *
 * @property int $id Credito
 * @property int $cliente_id Cliente
 * @property float $monto Monto
 * @property string|null $nota Nota
 * @property string|null $descripcion Descripcion
 * @property int $status Estatus
 * @property int $created_by Creado por
 * @property int|null $created_at Creado por
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 *
 * @property Cliente $cliente
 * @property User $createdBy
 * @property User $updatedBy
 */
class Credito extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE     = 10;
    const STATUS_CANCEL     = 20;
    const STATUS_PAGADA     = 30;
    const STATUS_POR_PAGADA = 40;


    public static $statusList = [
        self::STATUS_ACTIVE     => 'VIGENTE',
        self::STATUS_CANCEL     => 'CANCELADO',
        self::STATUS_PAGADA     => 'PAGADA',
        self::STATUS_POR_PAGADA => 'POR PAGAR',
    ];


    const TIPO_CLIENTE     = 10;
    const TIPO_PROVEEDOR   = 20;

    const PERTENECE_SISTEMA     = 10;
    const PERTENECE_GASTO       = 20;


    public static $tipoList = [
        self::TIPO_CLIENTE     => 'CLIENTE',
        self::TIPO_PROVEEDOR   => 'PROVEEDOR',
    ];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'credito';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['monto'], 'required'],
            [['cliente_id', 'status','compra_id','venta_id', 'tipo', 'fecha_credito', 'created_by', 'created_at', 'updated_by', 'updated_at','pertenece'], 'integer'],
            [['monto','monto_pagado'], 'number'],
            [['pertenece'],'default', 'value' => self::PERTENECE_SISTEMA ],
            [['trans_token_venta'], 'string'],
            [['titulo_gasto'], 'string','max' => 255],
            [['nota', 'descripcion','descripcion_gasto'], 'string'],
            [['cliente_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cliente::className(), 'targetAttribute' => ['cliente_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cliente_id' => 'Cliente',
            'cliente.nombreCompleto' => 'Nombre',
            'monto' => 'Monto',
            'nota' => 'Nota',
            'descripcion' => 'Descripcion',
            'status' => 'Status',
            'venta.cliente.nombreCompleto' => 'Cliente',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Cliente]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVenta()
    {
        return $this->hasOne(Venta::className(), ['id' => 'venta_id']);
    }

    public function getProveedor()
    {
        return $this->hasOne(Proveedor::className(), ['id' => 'proveedor_id']);
    }

    public function getCompra()
    {
        return $this->hasOne(Compra::className(), ['id' => 'compra_id']);
    }

    public function getCliente()
    {
        return $this->hasOne(Cliente::className(), ['id' => 'cliente_id']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public function getTransaccion()
    {
        return $this->hasMany(CreditoTokenPay::className(), ['credito_id' => 'id']);
    }

    public static function getTotaCreditoVenta($venta_id)
    {
        $queryCredito = Credito::find()->andWhere(["venta_id" => $venta_id ])->one();
        return isset($queryCredito->id) ? $queryCredito->monto : 0;
    }

    public static function validUserRepartidoApertura()
    {
        /* VALIDAMOS SI EXISTE ALGUN REPARTO EN CURSO RELACIOANDO AL USUARIO */
        $querySearch  = Reparto::find()
        ->andWhere(["or",
            [ "=", "status", Reparto::STATUS_PROCESO ],
            [ "=", "status", Reparto::STATUS_RUTA ],
        ])
        ->andWhere(["sucursal_id" => Yii::$app->user->identity->sucursal_id ])
        ->one();

        return isset($querySearch->id) && $querySearch->id ? true : false;
    }

    public static function validUserAdministrativo()
    {
        /* VALIDAMOS SI LA SUCURSAL NO PERTENECE A UNA RUTA*/
        $querySucursal = Sucursal::findOne(Yii::$app->user->identity->sucursal_id);

        return  $querySucursal->tipo == Sucursal::TIPO_SUCURSAL || $querySucursal->tipo == Sucursal::TIPO_ALMACEN ? true : false;
    }

    public static function getOperacionPagoDetail($item_id, $tipo)
    {
        $creditoArray   = null;
        $pagoArray      = [];
        if ($tipo == self::TIPO_CLIENTE) {
            $creditoArray =  (new Query())
            ->select([
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
                ["=","venta.cliente_id", $item_id ],
                ["=","credito.cliente_id", $item_id ]
            ])
            ->orderBy("credito_abono.created_at desc")
            ->groupBy("token_pay")
            ->all();
        }
        if ($tipo == self::TIPO_PROVEEDOR) {
            $creditoArray = (new Query())
            ->select([
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
                ["=","compra.proveedor_id", $item_id ],
                ["=","credito.proveedor_id", $item_id ]
            ])
            ->orderBy("credito_abono.created_at desc")
            ->groupBy("token_pay")
            ->all();
        }

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
                        }
                    }


                    if ($is_add) {
                        array_push($pagoArray, [
                            "trans_token_pay"   => $item_pago->trans_token_credito,
                            "opera_token_pay"   => $item_credito["credito_token_pay"],
                            "cantidad"          => floatval($item_pago->cantidad),
                            "cantidad_cancelado"=> $CobroVentaCancelado,
                            "fecha"             => date("Y-m-d h:i a",$item_pago->created_at),
                            "registrado_por"    => $item_pago->createdBy->nombreCompleto,
                        ]);
                    }


                }

            }
        }

        return $pagoArray;

    }

  

    public function getAbono()
    {
        return $this->hasMany(CreditoAbono::className(), ['credito_id' => 'id']);
    }

    public static function getCredito( $item_id, $tipo )
    {
        $result = null;

        if ($tipo == self::TIPO_CLIENTE) {
            $result =  self::find()->leftJoin('venta','credito.venta_id = venta.id')
            ->andWhere(["and",
                ["=","credito.tipo", Credito::TIPO_CLIENTE ],
                ["=","credito.pertenece", Credito::PERTENECE_SISTEMA ],
            ])
            ->andWhere([ "or",
                ["=","venta.cliente_id", $item_id ],
                ["=","credito.cliente_id", $item_id ]
            ])
            /*->andWhere([ "or",
                ["=","credito.status", Credito::STATUS_ACTIVE ],
                ["=","credito.status", Credito::STATUS_POR_PAGADA ],
            ])*/
            ->all();
        }
        if ($tipo == self::TIPO_PROVEEDOR) {
            $result = self::find()->leftJoin('compra','credito.compra_id = compra.id')
            ->andWhere(["and",
                ["=","credito.tipo", Credito::TIPO_PROVEEDOR ],
                ["=","credito.pertenece", Credito::PERTENECE_SISTEMA ],
            ])
            ->andWhere([ "or",
                ["=","compra.proveedor_id", $item_id ],
                ["=","credito.proveedor_id", $item_id ]
            ])
            /*->andWhere([ "or",
                ["=","credito.status", Credito::STATUS_ACTIVE ],
                ["=","credito.status", Credito::STATUS_POR_PAGADA ],
            ])*/
            ->all();
        }
        return $result;
    }

    public static function getCreditoGasto( $item_id, $tipo )
    {
        $result = null;

        if ($tipo == self::TIPO_CLIENTE) {
            $result =  self::find()->leftJoin('venta','credito.venta_id = venta.id')
            ->andWhere(["and",
                ["=","credito.tipo", Credito::TIPO_CLIENTE ],
                ["=","credito.pertenece", Credito::PERTENECE_GASTO ],
            ])
            ->andWhere([ "or",
                ["=","venta.cliente_id", $item_id ],
                ["=","credito.cliente_id", $item_id ]
            ])
            /*->andWhere([ "or",
                ["=","credito.status", Credito::STATUS_ACTIVE ],
                ["=","credito.status", Credito::STATUS_POR_PAGADA ],
            ])*/
            ->all();
        }
        if ($tipo == self::TIPO_PROVEEDOR) {
            $result = self::find()->leftJoin('compra','credito.compra_id = compra.id')
                ->andWhere(["and",
                    ["=","credito.tipo", Credito::TIPO_PROVEEDOR ],
                    ["=","credito.pertenece", Credito::PERTENECE_GASTO ],
                ])
                ->andWhere([ "or",
                    ["=","compra.proveedor_id", $item_id ],
                    ["=","credito.proveedor_id", $item_id ]
                ])
                /*->andWhere([ "or",
                    ["=","credito.status", Credito::STATUS_ACTIVE ],
                    ["=","credito.status", Credito::STATUS_POR_PAGADA ],
                ])*/
                ->all();
        }
        return $result;
    }

    public static function getHistoryPagos($cliente_id)
    {
        $result =  self::find()->leftJoin('venta','credito.venta_id = venta.id')
            ->andWhere(["and",
                ["=","credito.tipo", Credito::TIPO_CLIENTE ],
            ])
            ->andWhere([ "or",
                ["=","venta.cliente_id", $cliente_id ],
                ["=","credito.cliente_id", $cliente_id ]
            ])
            /*->andWhere([ "or",
                ["=","credito.status", Credito::STATUS_ACTIVE ],
                ["=","credito.status", Credito::STATUS_POR_PAGADA ],
            ])*/
            ->all();
        $creditoArray = [];
        $pagoArray = [];

        foreach ($result as $key => $item_credito) {
            array_push($creditoArray, $item_credito->id);
        }

        $pagoArray = (new Query())
                    ->select([
                        'credito_abono.credito_id',
                        'credito_abono.cantidad',
                        'credito_abono.status',
                        'credito_abono.created_at',
                        'credito_abono.created_by',
                        'concat_ws(" ",`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`',
                    ])
                    ->from('credito_abono')
                    ->innerJoin('user created', 'credito_abono.created_by = created.id')
                    ->andWhere([ "IN", "credito_abono.credito_id", $creditoArray])->orderBy("credito_abono.created_at desc")->all();
        return $pagoArray;
    }

    public static function getHistoryAbonos($provedor_id)
    {
        $result =  self::find()->leftJoin('compra','credito.compra_id = compra.id')
            ->andWhere(["and",
                ["=","credito.tipo", Credito::TIPO_PROVEEDOR ],
            ])
            ->andWhere([ "or",
                ["=","compra.proveedor_id", $provedor_id ],
                ["=","credito.proveedor_id", $provedor_id ]
            ])
            /*->andWhere([ "or",
                ["=","credito.status", Credito::STATUS_ACTIVE ],
                ["=","credito.status", Credito::STATUS_POR_PAGADA ],
            ])*/
            ->all();
        $creditoArray = [];
        $pagoArray = [];

        foreach ($result as $key => $item_credito) {
            array_push($creditoArray, $item_credito->id);
        }

        $pagoArray = (new Query())
                    ->select([
                        'credito_abono.credito_id',
                        'credito_abono.cantidad',
                        'credito_abono.status',
                        'credito_abono.created_at',
                        'credito_abono.created_by',
                        'concat_ws(" ",`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`',
                    ])
                    ->from('credito_abono')
                    ->innerJoin('user created', 'credito_abono.created_by = created.id')
                    ->andWhere([ "IN", "credito_abono.credito_id", $creditoArray])->orderBy("credito_abono.created_at desc")->all();
        return $pagoArray;
    }


    public static function getTotalesCredito($item_id, $tipo)
    {
        $result = [
            "total_credito"     => 0,
            "total_pagado"      => 0,
            "total_por_pagar"   => 0,
        ];

        if ($tipo == self::TIPO_CLIENTE) {
            $query =  self::find()->leftJoin('venta','credito.venta_id = venta.id')
            ->andWhere(["and",
                ["=","credito.tipo", Credito::TIPO_CLIENTE ],
            ])
            ->andWhere([ "or",
                ["=","venta.cliente_id", $item_id ],
                ["=","credito.cliente_id", $item_id ]
            ])
            ->andWhere([ "or",
                ["=","credito.status", Credito::STATUS_ACTIVE ],
                ["=","credito.status", Credito::STATUS_POR_PAGADA ],
            ])
            ->all();

            foreach ($query as $key => $item_credito) {
                $result["total_credito"]    = $result["total_credito"] + $item_credito->monto;
                $result["total_pagado"]     = $result["total_pagado"] + $item_credito->monto_pagado;
                $result["total_por_pagar"]  = $result["total_por_pagar"] + $item_credito->monto - $item_credito->monto_pagado;
            }
        }
        if ($tipo == self::TIPO_PROVEEDOR) {
            $query = self::find()->leftJoin('compra','credito.compra_id = compra.id')
            ->andWhere(["and",
                ["=","credito.tipo", Credito::TIPO_PROVEEDOR ],
            ])
            ->andWhere([ "or",
                ["=","compra.proveedor_id", $item_id ],
                ["=","credito.proveedor_id", $item_id ]
            ])
            ->andWhere([ "or",
                ["=","credito.status", Credito::STATUS_ACTIVE ],
                ["=","credito.status", Credito::STATUS_POR_PAGADA ],
            ])
            ->all();

            foreach ($query as $key => $item_credito) {
                $result["total_credito"]    = $result["total_credito"]   + $item_credito->monto;
                $result["total_pagado"]     = $result["total_pagado"]    + $item_credito->monto_pagado;
                $result["total_por_pagar"]  = $result["total_por_pagar"] + $item_credito->monto - $item_credito->monto_pagado;
            }
        }

        return $result;

    }


    /*public static function getTotalPorPagar($cliente_id){
        $totalPorPagar = 0;
         $query =  self::find()->leftJoin('venta','credito.venta_id = venta.id')
            ->andWhere(["and",
                ["=","credito.tipo", Credito::TIPO_CLIENTE ],
            ])
            ->andWhere([ "or",
                ["=","venta.cliente_id", $cliente_id ],
                ["=","credito.cliente_id", $cliente_id ]
            ])
            ->andWhere([ "or",
                ["=","credito.status", Credito::STATUS_ACTIVE ],
                ["=","credito.status", Credito::STATUS_POR_PAGADA ],
            ])
            ->all();

            foreach ($query as $key => $item_credito) {
                $totalPorPagar = $totalPorPagar + $item_credito->monto - $item_credito->monto_pagado;
            }

        return $totalPorPagar;
    }*/

    public static function totalPagadoCredito($credito_id)
    {
        return CobroVenta::find()->andWhere([ "and",
            ["=", "credito_id", $credito_id ],
            ["=", "tipo", CobroVenta::TIPO_CREDITO ],
            []
        ])->sum("cantidad");
    }

    public static function getCancelCredito($venta_id, $tipo)
    {
        if ($tipo == 10) {
            $Credito = self::find()->andWhere(["trans_token_venta" => $venta_id])->one();


            if (isset($Credito->id) && $Credito->id) {
                $Credito->status = Credito::STATUS_CANCEL;
                $Credito->update();
            }else{
                $VentaTokenPay = VentaTokenPay::find()->andWhere(["token_pay" => $venta_id ])->all();

                foreach ($VentaTokenPay as $key => $item_trans) {
                    $Credito        = self::find()->andWhere([ "venta_id" => $item_trans->venta_id ])->one();
                    if (isset($Credito->id) && $Credito->id) {
                        $Credito->status = Credito::STATUS_CANCEL;
                        $Credito->update();
                    }
                }
            }
        }

        if ($tipo == 20) {
            $Credito = self::find()->andWhere(["venta_id" => $venta_id])->one();
            $Credito->status = Credito::STATUS_CANCEL;
            $Credito->update();
        }
    }

    public static function updateCredito($venta_id, $tipo, $cantidad)
    {
        if ($tipo == 10) {
            $Credito        = self::find()->andWhere(["trans_token_venta" => $venta_id])->one();
            if (isset($Credito->id) && $Credito->id) {
                $Credito->monto = $cantidad;
                $Credito->update();
            }else{
                $VentaTokenPay = VentaTokenPay::find()->andWhere(["token_pay" => $venta_id ])->one();
                if (isset($VentaTokenPay->id) && $VentaTokenPay->id) {
                    $Credito        = self::find()->andWhere(["venta_id" => $VentaTokenPay->venta_id])->one();
                    if (isset($Credito->id) && $Credito->id) {
                        $Credito->monto = $cantidad;
                        $Credito->update();
                    }
                }
            }
        }

        if ($tipo == 20) {
            $Credito        = self::find()->andWhere(["venta_id" => $venta_id])->one();
            $Credito->monto = $cantidad;
            $Credito->update();
        }
    }

    public static function createCredito($venta_id, $tipo, $cantidad, $cliente_id)
    {
        if ($tipo == 10) {
            $Credito                        = new Credito();
            $Credito->trans_token_venta     = $venta_id;
            $Credito->cliente_id            = $cliente_id;
            $Credito->monto                 = $cantidad;
            $Credito->pertenece             = Credito::PERTENECE_SISTEMA;
            $Credito->fecha_credito         = time();
            $Credito->tipo                  = Credito::TIPO_CLIENTE;
            $Credito->status                = Credito::STATUS_ACTIVE;
            $Credito->save();
        }

        if ($tipo == 20) {
            $Credito                = new Credito();
            $Credito->venta_id      = $venta_id;
            $Credito->cliente_id    = $cliente_id;
            $Credito->monto         = $cantidad;
            $Credito->pertenece     = Credito::PERTENECE_SISTEMA;
            $Credito->fecha_credito = time();
            $Credito->tipo          = Credito::TIPO_CLIENTE;
            $Credito->status        = Credito::STATUS_ACTIVE;
            $Credito->save();
        }
    }

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

            if ($insert) {
                $this->status     = self::STATUS_ACTIVE;
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity ? ( $this->created_by ? $this->created_by : Yii::$app->user->identity->id) : $this->created_by;

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
