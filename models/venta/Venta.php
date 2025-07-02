<?php
namespace app\models\venta;

use Yii;
use yii\db\Query;
use yii\web\Response;
use app\models\user\User;
use app\models\cliente\Cliente;
use app\models\venta\VentaDetalle;
use app\models\sucursal\Sucursal;
use app\models\cobro\CobroVenta;
use app\models\esys\EsysCambiosLog;
use app\models\esys\EsysDireccion;
use app\models\reparto\RepartoDetalle;
use app\models\venta\VentaTokenPay;
use app\models\credito\Credito;
use app\models\reparto\Reparto;
use app\models\producto\Producto;
use app\models\inv\InvProductoSucursal;
use app\models\trans\TransProductoInventario;
use app\models\temp\TempVentaRuta;
use app\models\inv\Operacion;

/**
 * This is the model class for table "venta".
 *
 * @property int $id ID
 * @property int|null $cliente_id Cliente
 * @property int $tipo Tipo
 * @property int $status Estatus
 * @property float $total Total
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int|null $updated_at Modificado
 * @property int|null $updated_by Modificado por
 *
 * @property Cliente $cliente
 * @property User $createdBy
 * @property User $updatedBy
 */
class Venta extends \yii\db\ActiveRecord
{

    public $venta_detalle;

    public $cobroVenta;


    const VENTA_ESPECIAL   = 10;
    const VENTA_GENERAL    = 20;

    const IS_TPV_RUTA_OFF   = 10;
    const IS_TPV_RUTA_ON    = 20;

    const PAY_CREDITO_ON   = 10;

    const TIPO_MAYOREO = 30;
    const TIPO_MENUDEO = 20;
    const TIPO_GENERAL = 10;

    public static $ventaList = [
        self::VENTA_ESPECIAL    => 'VENTA ESPECIAL',
        self::VENTA_GENERAL     => 'VENTA NORMAL',
    ];

    public static $tipoList = [
        self::TIPO_GENERAL  => 'VENTA PUBLICO GENERAL',
        self::TIPO_MENUDEO  => 'VENTA MENUDEO',
        self::TIPO_MAYOREO  => 'VENTA MAYOREO',
        //self::STATUS_DELETED  => 'Eliminado'
    ];


    const STATUS_VERIFICADO = 60;
    const STATUS_PROCESO_VERIFICACION = 50;
    const STATUS_PROCESO    = 40;
    const STATUS_PREVENTA   = 30;
    const STATUS_PRECAPTURA = 20;
    const STATUS_VENTA      = 10;
    const STATUS_CANCEL     = 1;

    private $CambiosLog;
    public  $dir_obj;

    public static $statusList = [
        self::STATUS_VERIFICADO    => 'VERIFICADO',
        self::STATUS_PROCESO_VERIFICACION    => 'PROCESO - VERIFICACION',
        self::STATUS_PROCESO    => 'PROCESO',
        self::STATUS_PREVENTA   => 'PRE-VENTAS APP',
        self::STATUS_PRECAPTURA => 'PRE-VENTAS',
        self::STATUS_VENTA      => 'VENTA TERMINADA',
        self::STATUS_CANCEL     => 'CANCEL',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'venta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cliente_id', 'tipo', 'status', 'is_tpv_ruta','pay_credito','sucursal_id', 'is_especial','reparto_id','created_at', 'created_by', 'updated_at', 'updated_by','ruta_sucursal_id','devolucion_transaccion_id'], 'integer'],
            [['tipo', 'status', 'total','sucursal_id'], 'required'],
            [['is_tpv_ruta'], 'default', 'value'=> self::IS_TPV_RUTA_OFF],
            [['total'], 'number'],
            [['nota_cancelacion'], 'string'],
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
            'reparto_id' => 'Reparto',
            'tipo' => 'Tipo',
            'sucursal_id' => 'Sucursal',
            'ruta_sucursal_id' => 'Ruta / Sucursal',
            'status' => 'Status',
            'is_especial' => 'VENTA ESPECIAL',
            'pay_credito' => 'PAGO CREDITO',
            'total' => 'Total',
            'is_tpv_ruta' => 'VENTA EN RUTA',
            'nota_cancelacion' => 'NOTA DE CANCELACION',
            '#precaptura' => 'Precaptura',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function getSucursalVende()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_id']);
    }

    public function getTotalOperacion()
    {
        return VentaDetalle::find()->andWhere(['venta_id' => $this->id ])->sum('precio_venta');
    }

    public function getTotalUnidades()
    {
        return VentaDetalle::find()->andWhere(['venta_id' => $this->id ])->sum('cantidad');
    }

    /**
     * Gets query for [[Cliente]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentaDetalle()
    {
        return $this->hasMany(VentaDetalle::className(), ['venta_id' => 'id']);
    }

    public function getCobroTpvVenta()
    {
        return $this->hasMany(CobroVenta::className(), ['venta_id' => 'id']);
    }

    public function getSucursal()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'ruta_sucursal_id']);
    }

    /**
     * Gets query for [[Cliente]].
     *
     * @return \yii\db\ActiveQuery
     */
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

    public function getDireccion()
    {
        return $this->hasOne(EsysDireccion::className(), ['cuenta_id' => 'id'])
            ->where(['cuenta' => EsysDireccion::CUENTA_VENTA, 'tipo' => EsysDireccion::TIPO_PERSONAL]);
    }

    public function getTransaccion()
    {
        return $this->hasMany(VentaTokenPay::className(), ['venta_id' => 'id']);
    }

    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public function getReparto()
    {
        return $this->hasOne(Reparto::className(), ['id' => 'reparto_id']);
    }

    public static function registerOperacionDevolucion($operacion_id, $venta_id)
    {
        $Venta = Venta::findOne($venta_id);
        $Venta->devolucion_transaccion_id = $operacion_id;

        if ($Venta->update())
            return true;

        return false;

    }

    public static function isVentaRuta($venta_id)
    {
        $venta      = Venta::findOne($venta_id);
        $is_ruta    = false;
        if ($venta->is_tpv_ruta == Venta::IS_TPV_RUTA_ON || $venta->ruta_sucursal_id){
            $is_ruta =  true;
        }
        return $is_ruta;

    }

    public static function isPagoCredito($venta_id)
    {
        $is_val = false;
        $venta = Venta::findOne($venta_id);

        foreach ($venta->cobroTpvVenta as $key => $item_cobro_tienda) {
            if ($item_cobro_tienda->metodo_pago == CobroVenta::COBRO_CREDITO)
                $is_val = true;
        }


        return $is_val;
    }

    public static function preventaCierre($preventa_id, $reparto_id,$updated_by)
    {
        $preventa   = self::findOne($preventa_id);
        $total_new  = 0;
        foreach ($preventa->ventaDetalle as $key => $item_detalle) {
            if ($item_detalle->is_reparto_entrega != VentaDetalle::ENTREGA_REPARTO_ON ){
                RepartoDetalle::deleteVenta($item_detalle->id);
                $item_detalle->delete();
            }
            else
                $total_new = $total_new + floatval($item_detalle->cantidad * $item_detalle->precio_venta);
        }

        $preventa->reparto_id = $reparto_id;
        $preventa->status     = Venta::STATUS_VENTA;
        $preventa->updated_by = $updated_by;
        $preventa->total = round($total_new,2);
        $preventa->update();

    }


    public static function preventaTempCierre($temp_preventa_id, $reparto_id,$updated_by)
    {
        $preventa   = TempVentaRuta::findOne($temp_preventa_id);
        $total_new  = 0;
        foreach ($preventa->tempVentaRutaDetalle as $key => $item_detalle) {
            $total_new = $total_new + floatval($item_detalle->cantidad * $item_detalle->precio_venta);
        }

        $preventa->status       = Venta::STATUS_VENTA;
        $preventa->updated_by   = $updated_by;
        $preventa->total        = round($total_new,2);
        $preventa->update();
    }

    public static function preventaCierreAll($reparto_id,$updated_by,$cliente_id)
    {
        $preventa   = self::find()->andWhere([ "and",
            [ "=", "status", Venta::STATUS_PROCESO ],
            [ "=", "cliente_id", $cliente_id ]
        ])->all();

        foreach ($preventa as $key => $item_preventa) {
            $item_preventa->reparto_id = $reparto_id;
            $item_preventa->status     = Venta::STATUS_CANCEL;
            $item_preventa->updated_by = $updated_by;
            $item_preventa->update();
        }
    }

    public static function getVentaRuta($reparto_id)
    {
        return Venta::find()
        ->andWhere(["and",
            ["=","venta.reparto_id", $reparto_id],
            ["=","venta.status", Venta::STATUS_VENTA ],
            ["IS NOT","venta.cliente_id", new \yii\db\Expression('null') ],
            ["IS","venta.devolucion_transaccion_id", new \yii\db\Expression('null') ],
        ])->groupBy("venta.cliente_id")->all();
    }

    public static function getVentaRutaPublicoGeneral($reparto_id)
    {
        return Venta::find()
        ->andWhere(["and",
            ["=","venta.reparto_id", $reparto_id],
            ["=","venta.status", Venta::STATUS_VENTA ],
            ["IS","venta.cliente_id", new \yii\db\Expression('null') ],
            ["IS","venta.devolucion_transaccion_id", new \yii\db\Expression('null') ],
        ])->all();
    }

    public static function getVentaRutaFoliosCliente($reparto_id,$cliente_id)
    {
        return Venta::find()
        ->andWhere(["and",
            ["=","venta.reparto_id", $reparto_id],
            ["=","venta.cliente_id", $cliente_id],
            ["IS","venta.devolucion_transaccion_id", new \yii\db\Expression('null') ],
            //["=","venta.is_tpv_ruta", Venta::IS_TPV_RUTA_ON ],
        ])->all();
    }

    public static function getVentaRutaTotalCliente($reparto_id,$cliente_id)
    {
        $query = (new Query())
        ->select([
            "sum(venta.total) as total",
        ])
        ->from(self::tableName())
        ->andWhere(["and",
            ["=","venta.reparto_id", $reparto_id],
            ["=","venta.cliente_id", $cliente_id],
            ["=","venta.status", Venta::STATUS_VENTA ],
            ["IS","venta.devolucion_transaccion_id", new \yii\db\Expression('null') ],
        ])->groupBy('venta.cliente_id')->one();

        return $query["total"];
    }

    public static function getProductoVenta($venta_id, $producto_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /************************************
        / Preparamos consulta
        /***********************************/
        $query = (new Query())
            ->select([
                "venta.id",
                'venta.total',
                'venta_detalle.producto_id',
                'venta_detalle.cantidad',
            ])
        ->from(self::tableName())
        ->innerJoin("venta_detalle","venta.id = venta_detalle.venta_id")
        ->andWhere([ "and",
            ["=","venta.id", $venta_id],
            ["=","venta_detalle.producto_id", $producto_id]
        ]);

        return $query->one();
    }

    public static function getProductoPreCapturado($producto_id, $sucursal_id)
    {
        return (new Query())
        ->select([
            "sum(venta_detalle.cantidad) as cantidad_precaptura"
        ])
        ->from("venta_detalle")
        ->innerJoin("venta","venta_detalle.venta_id = venta.id")
        ->andWhere(["and",
            ["=", "venta.sucursal_id", $sucursal_id ],
            ["=", "venta.status", Venta::STATUS_PRECAPTURA ],
            ["=", "venta_detalle.producto_id", $producto_id ],
         ])
        ->one();
    }


    public static function getProductoPreCapturadoAll($producto_id, $sucursal_id)
    {
        $preventa_array = [];

        $query = (new Query())
        ->select([
            "venta_detalle.venta_id as folio",
            "venta_detalle.cantidad as cantidad"
        ])
        ->from("venta_detalle")
        ->innerJoin("venta","venta_detalle.venta_id = venta.id")
        ->andWhere(["and",
            ["=", "venta.sucursal_id", $sucursal_id ],
            ["=", "venta.status", Venta::STATUS_PRECAPTURA ],
            ["=", "venta_detalle.producto_id", $producto_id ],
         ])
        ->all();

        foreach ($query as $key => $item_folio) {
            array_push($preventa_array, [
                "folio"     => $item_folio["folio"],
                "cantidad"  => $item_folio["cantidad"],
            ]);
        }

        return $preventa_array;
    }


    public static function getOperacionVentaRuta($venta_id)
    {
        $VentaTokenPay      = VentaTokenPay::findOne([ "venta_id" => $venta_id ]);

        $responseArray  = null;
        $responseVenta  = [];
        $responsePago   = [];

        if (isset($VentaTokenPay->id) && $VentaTokenPay->id) {
            $ventaToken         = VentaTokenPay::find()->andWhere([ "token_pay" => $VentaTokenPay->token_pay ])->all();

            foreach ($ventaToken as $key_venta => $item_token) {
                array_push($responseVenta,[
                    "id"            => $item_token->venta->id,
                    "folio"         => str_pad($item_token->venta->id,6,"0",STR_PAD_LEFT),
                    "total"         => $item_token->venta->total,
                    "sucursal"      => isset($item_token->venta->reparto->sucursal->nombre) ? $item_token->venta->reparto->sucursal->nombre : null,
                    "created_at"    => date("Y-m-d h:i:s",$item_token->created_at),
                    "empleado"      => $item_token->createdBy->nombreCompleto,
                    "detail"        => [],
                ]);

                foreach ($item_token->venta->ventaDetalle as $key_detail => $item_producto) {
                    array_push($responseVenta[$key_venta]["detail"], [
                        "producto" => $item_producto->producto->nombre,
                        "cantidad" => $item_producto->cantidad ." - [". Producto::$medidaList[ $item_producto->producto->tipo_medida ] ."]",
                    ]);
                }
            }

            $cobroTpvVenta = CobroVenta::find()->andWhere([ "and",
                [ "=", "trans_token_venta", $VentaTokenPay->token_pay ],
                [ "=", "is_cancel", CobroVenta::IS_CANCEL_OFF ]
            ])->all();

            foreach ($cobroTpvVenta as $key => $item_cobro) {
                array_push($responsePago,[
                    "id" => $item_cobro->id,
                    "metodo_pago"       => $item_cobro->metodo_pago,
                    "metodo_pago_text"  => CobroVenta::$servicioTpvList[$item_cobro->metodo_pago],
                    "cantidad"          => $item_cobro->cantidad,
                ]);
            }
        }else{

            $venta = Venta::findOne($venta_id);

            array_push($responseVenta, [
                "id"            => $venta->id,
                "folio"         => str_pad($venta->id,6,"0",STR_PAD_LEFT),
                "total"         => $venta->total,
                "sucursal"      => isset($venta->reparto->sucursal->nombre) ? $venta->reparto->sucursal->nombre : null,
                "created_at"    => date("Y-m-d h:i:s",$venta->created_at),
                "empleado"      => $venta->createdBy->nombreCompleto,
                "detail"        => [],
            ]);

            foreach ($venta->ventaDetalle as $key_detail => $item_producto) {
                array_push($responseVenta[0]["detail"], [
                    "producto" => $item_producto->producto->nombre,
                    "cantidad" => $item_producto->cantidad ." - [". Producto::$medidaList[ $item_producto->producto->tipo_medida ] ."]",
                ]);
            }

            $cobroTpvVenta = CobroVenta::find()->andWhere([ "venta_id" => $venta->id ])->all();

            foreach ($cobroTpvVenta as $key => $item_cobro) {
                array_push($responsePago,[
                    "id" => $item_cobro->id,
                    "metodo_pago"       => $item_cobro->metodo_pago,
                    "metodo_pago_text"  => CobroVenta::$servicioTpvList[$item_cobro->metodo_pago],
                    "cantidad"          => $item_cobro->cantidad,
                ]);
            }

        }



        $responseArray = [
            "cobro" => $responsePago,
            "venta" => $responseVenta,
        ];


        return $responseArray;

    }

    public static function setCancelacionVentaRuta($venta_id, $sucursal_id, $nota_cancelacion = null)
    {
        $VentaTokenPay      = VentaTokenPay::findOne([ "venta_id" => $venta_id ]);

        $ventaCancelArray   = [];
        if (isset($VentaTokenPay->id) && $VentaTokenPay->id) {
            $ventaToken         = VentaTokenPay::find()->andWhere([ "token_pay" => $VentaTokenPay->token_pay ])->all();

            foreach ($ventaToken as $key_venta => $item_token) {
                foreach ($item_token->venta->ventaDetalle as $key_detail => $item_detalle) {
                    $Producto   = Producto::findOne($item_detalle->producto_id);


                    $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id ], [ "=", "producto_id", $item_detalle->producto_id ] ] )->one();

                    if (isset($InvProducto->id)) {
                        $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval($item_detalle->cantidad);
                        $InvProducto->save();
                    }else{
                        // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                        $InvProductoSucursal  =  new InvProductoSucursal();
                        $InvProductoSucursal->sucursal_id   = $sucursal_id;
                        $InvProductoSucursal->producto_id   = $item_detalle->producto_id;
                        $InvProductoSucursal->cantidad      = $item_detalle->cantidad;
                        $InvProductoSucursal->save();
                    }

                    TransProductoInventario::saveTransVenta($sucursal_id,$item_detalle->id,$item_detalle->producto_id,$item_detalle->cantidad,TransProductoInventario::TIPO_ENTRADA);
                }

                $venta                      = Venta::findOne($item_token->venta_id);
                $venta->status              = Venta::STATUS_CANCEL;
                $venta->nota_cancelacion    = $nota_cancelacion;

                $venta->update();
                array_push($ventaCancelArray, $item_token->venta_id);
            }

            $cobroTpvVenta = CobroVenta::find()->andWhere([ "trans_token_venta" => $VentaTokenPay->token_pay ])->all();

            foreach ($cobroTpvVenta as $key => $item_pago) {
                if ($item_pago->metodo_pago == CobroVenta::COBRO_CREDITO) {
                    $creditoArray = Credito::find()->andWhere([ "or",
                        ["=","trans_token_venta", $VentaTokenPay->token_pay ],
                        ["IN","venta_id", $ventaCancelArray ],
                    ])->all();
                    foreach ($creditoArray as $key => $item_credito) {
                        $item_credito->status = Credito::STATUS_CANCEL;
                        $item_credito->update();
                    }
                }
                $item_pago->is_cancel = CobroVenta::IS_CANCEL_ON;
                $item_pago->update();
            }
        }else{

            $venta = Venta::findOne($venta_id);

            foreach ($venta->ventaDetalle as $key_detail => $item_detalle) {
                    $Producto   = Producto::findOne($item_detalle->producto_id);

                    if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                    else
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id ], [ "=", "producto_id", $item_detalle->producto_id ] ] )->one();

                    if (isset($InvProducto->id)) {
                        if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {


                            $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                            if (isset($InvProducto2->id)) {
                                // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                $InvProducto2->cantidad = floatval($InvProducto2->cantidad) + ( floatval($item_detalle->cantidad) * $Producto->sub_cantidad_equivalente) ;
                                $InvProducto2->save();

                            }else{
                                // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO

                                $InvProductoSucursal  =  new InvProductoSucursal();
                                $InvProductoSucursal->sucursal_id   = $sucursal_id;
                                $InvProductoSucursal->producto_id   = $item_detalle->producto_id;
                                $InvProductoSucursal->cantidad      = $item_detalle->cantidad;
                                $InvProductoSucursal->save();
                            }

                        }else{
                            $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval($item_detalle->cantidad);
                            $InvProducto->save();
                        }
                    }else{
                        // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                        $InvProductoSucursal  =  new InvProductoSucursal();
                        $InvProductoSucursal->sucursal_id   = $sucursal_id;
                        $InvProductoSucursal->producto_id   = $item_detalle->producto_id;
                        $InvProductoSucursal->cantidad      = $item_detalle->cantidad;
                        $InvProductoSucursal->save();
                    }

                    TransProductoInventario::saveTransVenta($sucursal_id,$item_detalle->id,$item_detalle->producto_id,$item_detalle->cantidad,TransProductoInventario::TIPO_ENTRADA);
                }

                $venta->status              = Venta::STATUS_CANCEL;
                $venta->nota_cancelacion    = $nota_cancelacion;
                $venta->update();

            $cobroTpvVenta = CobroVenta::find()->andWhere([ "venta_id" => $venta->id ])->all();

            foreach ($cobroTpvVenta as $key => $item_pago) {
                if ($item_pago->metodo_pago == CobroVenta::COBRO_CREDITO) {
                    $creditoArray = Credito::find()->andWhere(["venta_id" => $venta->id ])->all();
                    foreach ($creditoArray as $key => $item_credito) {
                        $item_credito->status = Credito::STATUS_CANCEL;
                        $item_credito->update();
                    }
                }
                $item_pago->is_cancel = CobroVenta::IS_CANCEL_ON;
                $item_pago->update();
            }
        }

        return true;
    }


    public static function getTotalCredito($tienda_id, $fecha_inicio, $fecha_final)
    {
        $query = (new Query())
        ->select([
            "sum(cobro_venta.cantidad) as cantidad_total"
        ])
        ->from("cobro_venta")
        ->innerJoin("venta","cobro_venta.venta_id = venta.id")
        ->andWhere(["and",
            ["=", "cobro_venta.metodo_pago", CobroVenta::COBRO_CREDITO ],
            ["=", "cobro_venta.is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ["=", "venta.status", Venta::STATUS_VENTA ],
            ["=", "venta.sucursal_id", $tienda_id ],
            ["=", "venta.is_tpv_ruta", Venta::IS_TPV_RUTA_OFF ],
         ])
        ->andWhere(['between','cobro_venta.created_at', $fecha_inicio, $fecha_final])
        ->one();

        return $query["cantidad_total"];
    }

    public static function getTotalContado($tienda_id, $fecha_inicio, $fecha_final)
    {
        $totalCredito = 0;

        $query = (new Query())
        ->select([
            "sum(cobro_venta.cantidad) as cantidad_total"
        ])
        ->from("cobro_venta")
        ->innerJoin("venta","cobro_venta.venta_id = venta.id")
        ->andWhere(["and",
            ["=", "cobro_venta.is_cancel", CobroVenta::IS_CANCEL_OFF ],
            ["=", "venta.status", Venta::STATUS_VENTA ],
            ["=", "venta.sucursal_id", $tienda_id ],
            ["=", "venta.is_tpv_ruta", Venta::IS_TPV_RUTA_OFF ],
         ])
        ->andWhere(["or",
            ["=", "cobro_venta.metodo_pago", CobroVenta::COBRO_EFECTIVO ],
            ["=", "cobro_venta.metodo_pago", CobroVenta::COBRO_CHEQUE ],
            ["=", "cobro_venta.metodo_pago", CobroVenta::COBRO_TRANFERENCIA ],
            ["=", "cobro_venta.metodo_pago", CobroVenta::COBRO_TARJETA_CREDITO ],
            ["=", "cobro_venta.metodo_pago", CobroVenta::COBRO_TARJETA_DEBITO ],
            ["=", "cobro_venta.metodo_pago", CobroVenta::COBRO_DEPOSITO ],
            ["=", "cobro_venta.metodo_pago", CobroVenta::COBRO_OTRO ],
        ])
        ->andWhere(['between','cobro_venta.created_at', $fecha_inicio, $fecha_final])
        ->one();



        return $query["cantidad_total"];
    }

    public static function getTotalAbonado($tienda_id, $fecha_inicio, $fecha_final)
    {
        $repartidores   = User::find()->andWhere(["sucursal_id" => $tienda_id ])->all();

        //BUSCAMOS LOS USUARIOS ENCARGDOS DE ESTA RUTA
        $item_user      = [];
        foreach ($repartidores as $key => $item_repartido) {
            array_push($item_user, $item_repartido->id);
        }

        $query = (new Query())
        ->select([
            "sum(cobro_venta.cantidad) as cantidad_total"
        ])
        ->from("cobro_venta")
        ->andWhere(["and",
            ["=","tipo", CobroVenta::TIPO_CREDITO ],
            ['IN','created_by', $item_user ],
         ])
        ->andWhere(['between','cobro_venta.created_at', $fecha_inicio, $fecha_final])
        ->one();

        return $query["cantidad_total"];
    }

    public static function getTotalDevolucion($tienda_id, $fecha_inicio, $fecha_final)
    {
        $totalDevolucion = 0;
        $Devoluciones = Operacion::find()->andWhere(["and",
            ["=","almacen_sucursal_id", $tienda_id ],
            ["=", "tipo",  Operacion::TIPO_DEVOLUCION ],
            ["=", "motivo", Operacion::ENTRADA_DEVOLUCION ],
        ])->all();

        foreach ($Devoluciones as $key => $item_operacion) {
            foreach ($item_operacion->operacionDetalles as $key => $item_detail) {
                $totalDevolucion = $totalDevolucion + floatval( $item_detail->cantidad * $item_detail->producto->precio_publico);
            }
        }

        return round($totalDevolucion,2);
    }

    public static function isEditVentaRuta($venta_id)
    {
        $is_val = false;
        $venta   = Venta::findOne($venta_id);
        if ($venta->reparto_id) {
            if ($venta->reparto->status == Reparto::STATUS_PROCESO || $venta->reparto->status == Reparto::STATUS_RUTA)
                $is_val = true;

        }
        return $is_val;
    }

    public static function ventaInfo($venta_id)
    {
        $VentaTokenPay      = VentaTokenPay::findOne([ "venta_id" => $venta_id ]);
        $responseArray      = [];
        $responseVenta      = [];
        $responsePago       = [];
        $is_add             = true;

        if (isset($VentaTokenPay->id) && $VentaTokenPay->id) {
            $ventaToken         = VentaTokenPay::find()->andWhere([ "token_pay" => $VentaTokenPay->token_pay ])->all();

            foreach ($ventaToken as $key_venta => $item_token) {
                if ($is_add) {
                    $responseVenta = [
                        "cliente_id"    => $item_token->venta->cliente_id,
                        "cliente"       => $item_token->venta->cliente_id ? $item_token->venta->cliente->nombreCompleto : null,
                        "total"         => 0,
                        "detail"        => [],
                    ];
                    $is_add = false;
                }

                $responseVenta["total"]  = floatval($responseVenta["total"]) + floatval($item_token->venta->total);

                foreach ($item_token->venta->ventaDetalle as $key_detail => $item_producto) {
                    array_push($responseVenta["detail"], [
                        "venta_id"              => $item_token->venta->id,
                        "venta_detail_id"       => $item_producto->id,
                        "folio"         => str_pad($item_token->venta->id,6,"0",STR_PAD_LEFT),
                        "producto_id"   => $item_producto->producto_id,
                        "producto"      => $item_producto->producto->nombre,
                        "unidad_medida" => Producto::$medidaList[$item_producto->producto->tipo_medida],
                        "precio_venta"  => $item_producto->precio_venta,
                        "status"        => 10,
                        "cantidad"      => $item_producto->cantidad,
                    ]);
                }
            }

            $cobroTpvVenta = CobroVenta::find()->andWhere([ "and",
                [ "=", "trans_token_venta", $VentaTokenPay->token_pay ],
                [ "=", "is_cancel", CobroVenta::IS_CANCEL_OFF ]
            ])->all();

            foreach ($cobroTpvVenta as $key => $item_cobro) {
                array_push($responsePago,[
                    "id"                => $item_cobro->id,
                    "metodo_pago"       => $item_cobro->metodo_pago,
                    "metodo_pago_text"  => CobroVenta::$servicioTpvList[$item_cobro->metodo_pago],
                    "cantidad"          => $item_cobro->cantidad,
                ]);
            }
        }else{

            $venta = Venta::findOne($venta_id);

            $responseVenta = [
                "id"            => $venta->id,
                "folio"         => str_pad($venta->id,6,"0",STR_PAD_LEFT),
                "cliente_id"    => $venta->cliente_id,
                "cliente"       => $venta->cliente_id ? $venta->cliente->nombreCompleto : null,
                "total"         => $venta->total,
                "detail"        => [],
            ];

            foreach ($venta->ventaDetalle as $key_detail => $item_producto) {
                array_push($responseVenta["detail"], [
                    "venta_id"              => $venta->id,
                    "venta_detail_id"       => $item_producto->id,
                    "folio"                 => str_pad($venta->id,6,"0",STR_PAD_LEFT),
                    "producto_id"           => $item_producto->producto_id,
                    "producto"              => $item_producto->producto->nombre,
                    "unidad_medida"         => Producto::$medidaList[$item_producto->producto->tipo_medida],
                    "precio_venta"          => $item_producto->precio_venta,
                    "status"                => 10,
                    "cantidad"              => $item_producto->cantidad,
                ]);
            }

            $cobroTpvVenta = CobroVenta::find()->andWhere([ "and",
                [ "=","venta_id", $venta->id ],
                [ "=", "is_cancel", CobroVenta::IS_CANCEL_OFF ]
            ])->all();

            foreach ($cobroTpvVenta as $key => $item_cobro) {
                array_push($responsePago,[
                    "id"                => $item_cobro->id,
                    "metodo_pago"       => $item_cobro->metodo_pago,
                    "metodo_pago_text"  => CobroVenta::$servicioTpvList[$item_cobro->metodo_pago],
                    "cantidad"          => $item_cobro->cantidad,
                ]);
            }

        }

        $responseArray = [
            "cobro" => $responsePago,
            "info"  => $responseVenta,
        ];


        return  $responseArray;

    }


    public static function setUpdateVentaRuta($ventaArray, $nota)
    {

        $notaVenta = [];

        foreach ($ventaArray['venta']['detail'] as $key => $item_venta) {
            array_push($notaVenta, $item_venta["venta_id"]);
        }

        /*MODIFICACION DEL CLIENTE*/
        if (isset($ventaArray['venta']["edit_cliente"]) && $ventaArray['venta']["edit_cliente"] == 10)
            self::updateVentaCliente($notaVenta, $ventaArray['venta']["cliente_id"], $nota);

        self::updateProductoVenta($ventaArray['venta']['detail']);


        self::updateCobroVenta($ventaArray['cobro'],$ventaArray['venta']["cliente_id"]);


        return true;
    }

    public static function updateVentaCliente($notasVenta, $cliente_id, $nota)
    {
        foreach ($notasVenta as $key => $item_venta) {
            $venta                      = Venta::findOne($item_venta);
            $venta->cliente_id          = $cliente_id;
            $venta->nota_cancelacion    = $nota;
            $venta->save();
        }
    }

    public static function updateCobroVenta($cobroArray, $cliente_id)
    {
        foreach ($cobroArray as $key => $item_cobro) {
            if ( isset($item_cobro["status"]) && $item_cobro["status"] == 1 ) {
                /* SI SE ELIMINO EL METODO DE PAGO  */
                $CobroVenta             = CobroVenta::findOne($item_cobro["id"]);
                $CobroVenta->is_cancel  = CobroVenta::IS_CANCEL_ON;

                /*SI EL METODO DE PAGO A ELIMINAR ES NOTA DE CREDITO HAY QUE CANCELAR LA NOTA*/
                if ($CobroVenta->metodo_pago == CobroVenta::COBRO_CREDITO) {
                    if ($CobroVenta->trans_token_venta)
                        Credito::getCancelCredito($CobroVenta->trans_token_venta, 10);

                    if ($CobroVenta->venta_id)
                        Credito::getCancelCredito($CobroVenta->venta_id, 20);
                }

                $CobroVenta->update();
            }else{
                /* SI EXISTE ALGUNA MODIFICACION  */
                $CobroVenta                  = CobroVenta::findOne($item_cobro["id"]);

                if ($CobroVenta->metodo_pago == CobroVenta::COBRO_CREDITO) {


                    if ($CobroVenta->metodo_pago != $item_cobro["metodo_pago"]) {

                        if ($CobroVenta->trans_token_venta)
                            Credito::getCancelCredito($CobroVenta->trans_token_venta, 10);

                        if ($CobroVenta->venta_id)
                            Credito::getCancelCredito($CobroVenta->venta_id, 20);

                        $CobroVenta->metodo_pago    = $item_cobro["metodo_pago"];
                        $CobroVenta->cantidad       = $item_cobro["cantidad"];
                        $CobroVenta->fecha_credito  = null;
                        $CobroVenta->update();
                    }else{
                        if ($CobroVenta->trans_token_venta)
                            Credito::updateCredito($CobroVenta->trans_token_venta, 10, $item_cobro["cantidad"]);

                        if ($CobroVenta->venta_id)
                            Credito::updateCredito($CobroVenta->venta_id, 20, $item_cobro["cantidad"]);

                        $CobroVenta->cantidad       = $item_cobro["cantidad"];
                        $CobroVenta->update();
                    }
                }else{
                    if ($item_cobro["metodo_pago"] == CobroVenta::COBRO_CREDITO) {
                         if ($CobroVenta->trans_token_venta)
                            Credito::createCredito($CobroVenta->trans_token_venta, 10, $item_cobro["cantidad"], $cliente_id);

                        if ($CobroVenta->venta_id)
                            Credito::createCredito($CobroVenta->venta_id, 20, $item_cobro["cantidad"], $cliente_id);

                        $CobroVenta->metodo_pago    = $item_cobro["metodo_pago"];
                        $CobroVenta->cantidad       = $item_cobro["cantidad"];
                        $CobroVenta->fecha_credito  = time();
                        $CobroVenta->update();

                    }else{
                        $CobroVenta->metodo_pago    = $item_cobro["metodo_pago"];
                        $CobroVenta->cantidad       = $item_cobro["cantidad"];
                        $CobroVenta->update();
                    }
                }
            }
        }
    }

    public static function updateProductoVenta($ventaArray)
    {
        foreach ($ventaArray as $key => $item_venta) {
            if ( isset($item_venta["edit_producto"]) && $item_venta["edit_producto"] == 10 )
            {
                /*SE SE MODIFICA EL PRODUCTO */
                $VentaDetalle = VentaDetalle::findOne($item_venta["venta_detail_id"]);
                if ($VentaDetalle->producto_id != $item_venta["producto_id"] && $VentaDetalle->cantidad == $item_venta["cantidad"] ) {
                    /*RETORNO DEL PRODUCTO*/

                    $venta      = Venta::findOne($item_venta["venta_id"]);
                    $sucursal   = $venta->reparto->sucursal_id;

                    $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $VentaDetalle->producto_id ] ] )->one();

                    if (isset($InvProducto->id)) {
                        $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval($VentaDetalle->cantidad);
                        $InvProducto->save();
                    }

                    TransProductoInventario::saveTransVenta($sucursal,$item_venta["venta_detail_id"],$VentaDetalle->producto_id,$VentaDetalle->cantidad,TransProductoInventario::TIPO_ENTRADA);

                    /*SALIDA DE PRODUCTO*/

                    $venta      = Venta::findOne($item_venta["venta_id"]);
                    $sucursal   = $venta->reparto->sucursal_id;

                    $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $item_venta["producto_id"] ]])->one();

                    if (isset($InvProducto->id)) {
                        $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($item_venta["cantidad"]);
                        $InvProducto->save();
                    }

                    TransProductoInventario::saveTransVenta($sucursal,$item_venta["venta_detail_id"],$item_venta["producto_id"],$item_venta["cantidad"],TransProductoInventario::TIPO_SALIDA);
                }
            }else{
                if ( isset($item_venta["edit_precio"]) && $item_venta["edit_precio"] == 10 ) {
                    /*SE SE MODIFICA EL PRECIO DE VENTA */
                    $VentaDetalle = VentaDetalle::findOne($item_venta["venta_detail_id"]);
                    $VentaDetalle->precio_venta = $item_venta["precio_venta"];
                    $VentaDetalle->save();
                }

                if ( isset($item_venta["edit_cantidad"]) && $item_venta["edit_cantidad"] == 10 ) {
                    /*SE SE MODIFICA EL PRODUCTO */
                    $VentaDetalle = VentaDetalle::findOne($item_venta["venta_detail_id"]);
                    if ($VentaDetalle->producto_id == $item_venta["producto_id"] && $VentaDetalle->cantidad != $item_venta["cantidad"] ) {
                        /*RETORNO DEL PRODUCTO*/

                        $venta      = Venta::findOne($item_venta["venta_id"]);
                        if ($item_venta["cantidad"] > $VentaDetalle->cantidad) {
                            $cantidadExtra = 0;
                            $cantidadExtra = $item_venta["cantidad"] - $VentaDetalle->cantidad;

                            $sucursal   = $venta->reparto->sucursal_id;

                            /*SALIDA DE PRODUCTO*/

                            $venta      = Venta::findOne($item_venta["venta_id"]);
                            $sucursal   = $venta->reparto->sucursal_id;

                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $item_venta["producto_id"] ]] )->one();

                            if (isset($InvProducto->id)) {
                                $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($cantidadExtra);
                                $InvProducto->save();
                            }

                            TransProductoInventario::saveTransVenta($sucursal,$item_venta["venta_detail_id"],$item_venta["producto_id"],$cantidadExtra,TransProductoInventario::TIPO_SALIDA);
                        }

                        if ($item_venta["cantidad"] < $VentaDetalle->cantidad) {
                            $cantidadExcedente = 0;
                            $cantidadExcedente = $VentaDetalle->cantidad - $item_venta["cantidad"];

                            $sucursal   = $venta->reparto->sucursal_id;


                            /*SALIDA DE PRODUCTO*/

                            $venta      = Venta::findOne($item_venta["venta_id"]);
                            $sucursal   = $venta->reparto->sucursal_id;

                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $item_venta["producto_id"] ]])->one();

                            if (isset($InvProducto->id)) {
                                $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval($cantidadExcedente);
                                $InvProducto->save();
                            }

                            TransProductoInventario::saveTransVenta($sucursal,$item_venta["venta_detail_id"],$item_venta["producto_id"],$cantidadExcedente,TransProductoInventario::TIPO_ENTRADA);

                        }

                        $VentaDetalle->cantidad = $item_venta["cantidad"];
                        $VentaDetalle->update();
                    }
                }
            }
        }

        foreach ($ventaArray as $key => $item_venta) {
            $venta          = Venta::findOne($item_venta["venta_id"]);
            $newTotal       = 0;
            foreach ($venta->ventaDetalle as $key => $item_detail) {
                $newTotal = $newTotal + ( $item_detail->cantidad * $item_detail->precio_venta );
            }
            $venta->total   = $newTotal;
            $venta->update();
        }
    }

    public static function savePreventaComanda($cliente_id, $total, $tipo, $user_id, $sucursal_id, $venta_detalle)
    {
        $connection = \Yii::$app->db;

        $transaction = $connection->beginTransaction();

        try {

            $venta = $connection->createCommand()
            ->insert('venta', [
                    'cliente_id'        => $cliente_id,
                    'total'             => $total,
                    'tipo'              => $tipo,
                    'status'            => Venta::STATUS_PREVENTA,
                    'is_especial'       => Venta::VENTA_GENERAL,
                    'created_by'        => $user_id,
                    'created_at'        => time(),
                    'sucursal_id'       => $sucursal_id,
            ])->execute();

            $ventaID = Yii::$app->db->getLastInsertID();

            $apply_bodega =  VentaDetalle::APPLY_BODEGA_OFF;

            foreach ($venta_detalle as $key => $item_detalle) {

                if (!isset($item_detalle['status']) || ( isset($item_detalle['status']) && $item_detalle['status'] != 20 ) ) {

                    $ventaDetalle =  $connection->createCommand()
                    ->insert('venta_detalle', [
                        'venta_id'      => $ventaID,
                        'producto_id'   => $item_detalle['producto_id'],
                        'cantidad'      => $item_detalle['cantidad'],
                        'precio_venta'  => $item_detalle['precio_venta'],
                        'created_by'    => $user_id,
                        'created_at'    => time(),
                        'apply_bodega'  => $item_detalle['sucursal_id'] != $sucursal_id ? VentaDetalle::APPLY_BODEGA_ON : VentaDetalle::APPLY_BODEGA_OFF,
                        'sucursal_id'   => $item_detalle['sucursal_id'],
                    ])->execute();

                    if ( $item_detalle['sucursal_id'] != $sucursal_id)
                        $apply_bodega =  VentaDetalle::APPLY_BODEGA_ON;
                }

            }

            if ( $apply_bodega ==  VentaDetalle::APPLY_BODEGA_ON ) {
                $queryUpdateVenta =  $connection->createCommand()
                ->update('venta', [
                    'status'    => Venta::STATUS_PROCESO_VERIFICACION,
                ], "id=". $ventaID )->execute();
            }

            $transaction->commit();

            if ($apply_bodega ==  VentaDetalle::APPLY_BODEGA_ON )
                return [
                    "code"    => 302,
                    "name"    => "Tpv",
                    "message" => 'Se genero correctamente la pre-venta',
                    "folio"   => $ventaID,
                    "type"    => "Success",
                ];

            else
                return [
                    "code"    => 202,
                    "name"    => "Tpv",
                    "message" => 'Se genero correctamente la pre-venta',
                    "folio"   => $ventaID,
                    "type"    => "Success",
                ];


        } catch(Exception $e) {
            $transaction->rollback();
            return [
                "code"    => 13,
                "name"    => "Tpv",
                "message" => 'Ocurrio un error, intenta nuevamente',
                "type"    => "Error",
            ];
        }
    }


    public static function aprovedPreventaComanda($venta_id, $total, $user_id, $sucursal_id, $venta_detalle)
    {
        $connection = \Yii::$app->db;

        $transaction = $connection->beginTransaction();

        try {

            $queryVenta = Venta::findOne($venta_id);

            if ($queryVenta->status == Venta::STATUS_VERIFICADO ) {
                $queryUpdateVenta =  $connection->createCommand()
                ->update('venta', [
                    'status'        => Venta::STATUS_PREVENTA,
                    'total'         => $total,
                    'updated_by'    => $user_id,
                    'updated_at'    => time(),
                ], "id=". $venta_id )->execute();

                foreach ($venta_detalle as $key => $item_detalle) {
                    if (isset($item_detalle["detail_id"]) && $item_detalle["detail_id"]) {

                        $queryDetail= VentaDetalle::findOne($item_detalle["detail_id"]);


                        if ($queryDetail) {
                            if ($queryDetail->apply_bodega == VentaDetalle::APPLY_BODEGA_OFF && $item_detalle["status"] == VentaDetalle::STATUS_ACTIVE ) {
                                $ventaDetalle =  $connection->createCommand()
                                ->update('venta_detalle', [
                                    'cantidad'      => $item_detalle['cantidad'],
                                    'updated_by'    => $user_id,
                                    'updated_at'    => time(),
                                ], "id=". $item_detalle["detail_id"] )->execute();
                            }

                            if ($item_detalle["status"] == VentaDetalle::STATUS_CANCEL){
                                $ventaDetalle =  $connection->createCommand()
                                ->delete('venta_detalle', "id=". $item_detalle["detail_id"] )->execute();
                            }
                        }

                    }else{
                        if ($item_detalle['sucursal_id'] == $sucursal_id && $item_detalle["status"] == VentaDetalle::STATUS_ACTIVE) {
                            $ventaDetalle =  $connection->createCommand()
                            ->insert('venta_detalle', [
                                'venta_id'      => $venta_id,
                                'producto_id'   => $item_detalle['producto_id'],
                                'cantidad'      => $item_detalle['cantidad'],
                                'precio_venta'  => $item_detalle['precio_venta'],
                                'created_by'    => $user_id,
                                'created_at'    => time(),
                                'apply_bodega'  => VentaDetalle::APPLY_BODEGA_OFF,
                                'sucursal_id'   => $item_detalle['sucursal_id'],
                            ])->execute();
                        }
                    }
                }

                $transaction->commit();

                return [
                    "code"    => 202,
                    "name"    => "Tpv",
                    "message" => 'Se genero correctamente la pre-venta',
                    "folio"   => $venta_id,
                    "type"    => "Success",
                ];
            }else{
                 return [
                    "code"    => 202,
                    "name"    => "Tpv",
                    "message" => 'La preventa es',
                    "folio"   => $venta_id,
                    "type"    => "Success",
                ];
            }

        } catch(Exception $e) {
            $transaction->rollback();
            return [
                "code"    => 13,
                "name"    => "Tpv",
                "message" => 'Ocurrio un error, intenta nuevamente',
                "type"    => "Error",
            ];
        }
    }

    public static function getPreventaProceso()
    {
        return Venta::find()->andWhere(["status" => Venta::STATUS_PROCESO_VERIFICACION ])->orderBy("created_at ASC")->all();
    }

    public static function cancelacionPreventa($preventa_id, $user_id)
    {
        $queryPreventa = Venta::findOne($preventa_id);

        if ($queryPreventa->status == Venta::STATUS_VERIFICADO || $queryPreventa->status == Venta::STATUS_PROCESO_VERIFICACION || $queryPreventa->status == Venta::STATUS_PREVENTA ) {
            $queryPreventa->status      = Venta::STATUS_CANCEL;
            $queryPreventa->updated_by  = $user_id;
            if ($queryPreventa->update()) {
                return [
                    "code" => 202,
                    "message" => "Se realizo correctamente la operacion"
                ];
            }
        }

        return [
            "code"      => 10,
            "message"   => "Ocurrio un error, intenta nuevamente"
        ];

    }

    public static function pagoTransferenciaVenta($operacionesArray){

    }

    public static function pagoTransferenciaCredito($operacionesArray){

    }

    public static function getTotalTransferencia($ventaID)
    {
        $VentaTokenPay      = VentaTokenPay::findOne([ "venta_id" => $ventaID ]);
        
        if (isset($VentaTokenPay->id) && $VentaTokenPay->id) {

            return CobroVenta::find()->andWhere([ "and",
                [ "=", "trans_token_venta", $VentaTokenPay->token_pay ],
                [ "=", "metodo_pago", CobroVenta::COBRO_TRANFERENCIA ],
                [ "=", "is_cancel", CobroVenta::IS_CANCEL_OFF ]
            ])->sum('cantidad');

        }else
            return CobroVenta::find()->andWhere([ "and",["=", "venta_id", $ventaID ], [ "=", "metodo_pago", CobroVenta::COBRO_TRANFERENCIA ] ])->sum('cantidad');
    }

    public static function getTotalCheque($ventaID)
    {
        $VentaTokenPay      = VentaTokenPay::findOne([ "venta_id" => $ventaID ]);
        
        if (isset($VentaTokenPay->id) && $VentaTokenPay->id) {

            return CobroVenta::find()->andWhere([ "and",
                [ "=", "trans_token_venta", $VentaTokenPay->token_pay ],
                [ "=", "metodo_pago", CobroVenta::COBRO_CHEQUE ],
                [ "=", "is_cancel", CobroVenta::IS_CANCEL_OFF ]
            ])->sum('cantidad');

        }else
            return CobroVenta::find()->andWhere([ "and",["=","venta_id", $ventaID], ["=", "metodo_pago", CobroVenta::COBRO_CHEQUE ] ])->sum('cantidad');
    }

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at   = time();
                $this->created_by   = Yii::$app->user->identity  ? ( $this->created_by ? $this->created_by : Yii::$app->user->identity->id): $this->created_by;
                //$this->sucursal_id  = $this->sucursal_id;

            }else{

                $this->CambiosLog = new EsysCambiosLog($this);
                // Remplazamos manualmente valores del log de cambios
                foreach($this->CambiosLog->getListArray() as $attribute => $value) {
                    switch ($attribute) {
                        case 'status':
                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', self::$statusList[$value['old']]);

                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', self::$statusList[$value['dirty']]);
                        break;

                        case 'tipo':
                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', self::$tipoList[$value['old']]);

                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', self::$tipoList[$value['dirty']]);
                        break;

                    }
                }

                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: $this->updated_by;
            }

            return true;

        } else
            return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if($insert){

        }else{
            // Guardamos un registro de los cambios
            $this->CambiosLog->createLog($this->id);
        }

        if ($this->is_especial == Venta::VENTA_ESPECIAL){
            if (isset($this->dir_obj->cuenta_id) && $this->dir_obj->cuenta_id) {
                $this->dir_obj->save();
            }else{
                if (isset($this->dir_obj->estado_id) && $this->dir_obj->estado_id) {
                    $this->dir_obj->cuenta_id = $this->id;
                    $this->dir_obj->save();
                }
            }
        }
    }
}
