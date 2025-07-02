<?php
namespace app\models\reparto;

use Yii;
use yii\db\Query;
use kartik\mpdf\Pdf;
use yii\web\Response;
use app\models\user\User;
use app\models\sucursal\Sucursal;
//use app\models\venta\TransVenta;
use app\models\producto\Producto;
use app\models\inv\InvProductoSucursal;
use app\models\venta\Venta;
use app\models\inv\Operacion;
use app\models\inv\OperacionDetalle;
use app\models\venta\VentaDetalle;
use app\models\esys\EsysSetting;
use app\models\trans\TransProductoInventario;
use app\models\temp\TempVentaRutaDetalle;
use app\models\temp\TempVentaRuta;
use app\models\cobro\CobroVenta;
use app\models\temp\TempCobroRutaVenta;
use app\models\venta\VentaTokenPay;
use app\models\temp\TempVentaTokenPay;
use app\models\credito\Credito;
use app\models\Esys;

/**
 * This is the model class for table "reparto".
 *
 * @property int $id ID
 * @property int $sucursal_id Sucursal ID
 * @property int $status Status
 * @property int $created_by Creado por
 * @property int $created_at Creado
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 *
 * @property User $createdBy
 * @property Sucursal $sucursal
 * @property User $updatedBy
 * @property RepartoDetalle[] $repartoDetalles
 */
class Reparto extends \yii\db\ActiveRecord
{

    const STATUS_PROCESO    = 30;
    const STATUS_RUTA       = 20;
    const STATUS_TERMINADO  = 10;


    public static $statusList = [
        self::STATUS_PROCESO    => 'PROCESO',
        self::STATUS_RUTA       => 'RUTA',
        self::STATUS_TERMINADO  => 'TERMINADO',
    ];

    public static $statusAlertList = [
        self::STATUS_PROCESO    => 'info',
        self::STATUS_RUTA       => 'warning',
        self::STATUS_TERMINADO  => 'success',
    ];

    public $reparto_detalle;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reparto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sucursal_id'], 'required'],
            [['sucursal_id', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at','cierre_reparto'], 'integer'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['sucursal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sucursal::className(), 'targetAttribute' => ['sucursal_id' => 'id']],
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
            'sucursal_id' => 'Sucursal ID',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    public static function validaReparto($reparto_id)
    {
        $reparto    = self::findOne($reparto_id);
        $is_val     = false;
        if (isset($reparto->id) && $reparto->id) {
            if ($reparto->status == Reparto::STATUS_RUTA)
                $is_val     = true;
        }
        return $is_val;
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
     * Gets query for [[Sucursal]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSucursal()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_id']);
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

    public function getClienteReparto()
    {
        $clienteArray = [];
        foreach ($this->repartoDetalles as $key => $repartoDetalle) {
            if($repartoDetalle->tipo == RepartoDetalle::TIPO_PRECAPTURA){
                
                if($repartoDetalle->ventaDetalle->venta->cliente_id){
                    $isAdd = true;
                    foreach ($clienteArray as $key => $itemCliente) {
                        if($itemCliente["cliente_id"] == $repartoDetalle->ventaDetalle->venta->cliente_id){
                            $isAdd = false;
    
                        }
                    }
    
                    if($isAdd){
                        array_push($clienteArray, [
                            "cliente_id"        => $repartoDetalle->ventaDetalle->venta->cliente_id,
                            "cliente_nombre"    => $repartoDetalle->ventaDetalle->venta->cliente->nombreCompleto,
                        ]);
                    }
                }
                
            }
        }
        return $clienteArray;
    }

    public static function getTotalProductoLiquidacion($reparto_id)
    {
        $querySum = TempVentaRutaDetalle::find()->innerJoin("temp_venta_ruta","temp_venta_ruta_detalle.temp_venta_ruta_id = temp_venta_ruta.id")->andWhere(["and",
            ["=","temp_venta_ruta.reparto_id",$reparto_id],
        ])->sum("temp_venta_ruta_detalle.cantidad");

        return $querySum ? $querySum : 0;
    }

    public static function getTotalCobroLiquidacion($reparto_id)
    {
        return TempCobroRutaVenta::find()->andWhere(["and",
            ["=","operacion_reparto_id",$reparto_id],
        ])->sum("cantidad");
    }

    public static function getCarga($reparto_id)
    {
        $Reparto = Reparto::findOne($reparto_id);
        $producto_count = 0;
        foreach ($Reparto->repartoDetalles as $key => $repartoItem) {
            $producto_count = $producto_count + floatval($repartoItem->cantidad);
        }

        return $producto_count;
    }

    public static function getPreventa($reparto_id)
    {
        $Reparto = Reparto::findOne($reparto_id);
        $producto_count = 0;
        foreach ($Reparto->repartoDetalles as $key => $repartoItem) {
            if ($repartoItem->tipo == RepartoDetalle::TIPO_PRECAPTURA ) {
                $producto_count = $producto_count + floatval($repartoItem->cantidad);
            }
        }

        return $producto_count;
    }

    public static function getTaraAbierta($reparto_id)
    {
        $Reparto = Reparto::findOne($reparto_id);
        $producto_count = 0;
        foreach ($Reparto->repartoDetalles as $key => $repartoItem) {
            if ($repartoItem->tipo == RepartoDetalle::TIPO_PRODUCTO ) {
                $producto_count = $producto_count + floatval($repartoItem->cantidad);
            }
        }

        return $producto_count;
    }

    public static function getTotalPreventa($reparto_id)
    {
        $totalPreventa = 0;

        $RepartoDetalle = RepartoDetalle::find()->andWhere([ "reparto_id" => $reparto_id ])->all();

        foreach ($RepartoDetalle as $key => $item_detail) {
            if ($item_detail->tipo == RepartoDetalle::TIPO_PRECAPTURA) {
                $totalPreventa = $totalPreventa + ( $item_detail->cantidad *  $item_detail->ventaDetalle->precio_venta);
            }
        }

        return round($totalPreventa,2);
    }

    public static function getTotalTaraAbierta($reparto_id)
    {
        $totalTaraAbierta = 0;

        $RepartoDetalle = RepartoDetalle::find()->andWhere([ "reparto_id" => $reparto_id ])->all();

        foreach ($RepartoDetalle as $key => $item_detail) {
            if ($item_detail->tipo == RepartoDetalle::TIPO_PRODUCTO) {
                $totalTaraAbierta = $totalTaraAbierta + ( $item_detail->cantidad *  $item_detail->producto->precio_publico);
            }
        }

        return round($totalTaraAbierta,2);
    }

    public static function getTotalValorCredito($reparto_id)
    {
        return round(CobroVenta::getTotalOperacionMetodo($reparto_id, CobroVenta::COBRO_CREDITO) ,2);
    }

    public static function getTotalValorContable($reparto_id)
    {
        return round(floatval(CobroVenta::getTotalOperacionMetodo($reparto_id, CobroVenta::COBRO_EFECTIVO)) + floatval(CobroVenta::getTotalOperacionMetodo($reparto_id, CobroVenta::COBRO_CHEQUE)) + floatval(CobroVenta::getTotalOperacionMetodo($reparto_id, CobroVenta::COBRO_TRANFERENCIA)) + floatval(CobroVenta::getTotalOperacionMetodo($reparto_id, CobroVenta::COBRO_TARJETA_CREDITO)) + floatval(CobroVenta::getTotalOperacionMetodo($reparto_id, CobroVenta::COBRO_TARJETA_DEBITO)) + floatval(CobroVenta::getTotalOperacionMetodo($reparto_id, CobroVenta::COBRO_DEPOSITO)) ,2);
    }


    public static function getTotalValorAbonos($reparto_id)
    {
        return round(floatval(CobroVenta::getTotalOperacionCreditoMetodo($reparto_id, CobroVenta::COBRO_EFECTIVO)) + floatval(CobroVenta::getTotalOperacionCreditoMetodo($reparto_id, CobroVenta::COBRO_CHEQUE)) + floatval(CobroVenta::getTotalOperacionCreditoMetodo($reparto_id, CobroVenta::COBRO_TRANFERENCIA)) + floatval(CobroVenta::getTotalOperacionCreditoMetodo($reparto_id, CobroVenta::COBRO_TARJETA_CREDITO)) + floatval(CobroVenta::getTotalOperacionCreditoMetodo($reparto_id, CobroVenta::COBRO_TARJETA_DEBITO)) + floatval(CobroVenta::getTotalOperacionCreditoMetodo($reparto_id, CobroVenta::COBRO_DEPOSITO)) ,2);
    }

    public static function getTotalDevoluciones($reparto_id)
    {
        $totalDevolucion = 0;
        $Devoluciones = Operacion::find()->andWhere(["and",
            ["=","reparto_id", $reparto_id ],
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

    public static function getDevolucion($reparto_id)
    {
        $Devoluciones = Operacion::find()->andWhere(["and",
            ["=","reparto_id", $reparto_id ],
            ["=", "tipo",  Operacion::TIPO_DEVOLUCION ],
            ["=", "motivo", Operacion::ENTRADA_DEVOLUCION ],
        ])->all();
        $producto_count = 0;

        foreach ($Devoluciones as $key => $devolucionItem) {
            foreach ($devolucionItem->operacionDetalles as $key => $DetalleItem) {
                $producto_count = $producto_count + floatval($DetalleItem->cantidad);
            }
        }

        return $producto_count;
    }

    public static function getDevolucionVenta($reparto_id)
    {
        $Devoluciones = Operacion::find()->andWhere(["and",
            ["=","reparto_id", $reparto_id ],
            ["=", "tipo",  Operacion::TIPO_DEVOLUCION ],
            ["=", "motivo", Operacion::ENTRADA_DEVOLUCION ],
        ])->all();
        //$producto_count = 0;

        /*foreach ($Devoluciones as $key => $devolucionItem) {
            foreach ($devolucionItem->operacionDetalles as $key => $DetalleItem) {
                $producto_count = $producto_count + floatval($DetalleItem->cantidad);
            }
        }*/

        return $Devoluciones;
    }

    public static function getRecoleccion($reparto_id)
    {
        $Devoluciones = Operacion::find()->andWhere(["and",
            ["=","reparto_id", $reparto_id ],
            ["=", "tipo",  Operacion::TIPO_ENTRADA ],
            ["=", "motivo", Operacion::ENTRADA_TRASPASO_RECOLECCION ],
        ])->all();

        $producto_count = 0;

        foreach ($Devoluciones as $key => $devolucionItem) {
            foreach ($devolucionItem->operacionDetalles as $key => $DetalleItem) {
                $producto_count = $producto_count + floatval($DetalleItem->cantidad);
            }
        }
        return $producto_count;
    }


    public static function getFaltante($reparto_id)
    {
        /*$Ventas = Venta::find()->andWhere(["and",
            ["=","reparto_id", $reparto_id ],
            ["=", "status",  Venta::STATUS_VENTA ],
            //["=", "is_tpv_ruta",  Venta::IS_TPV_RUTA_OFF ],
        ])->all();

        $producto_count = 0;

        foreach ($Ventas as $key => $venta) {
            foreach ($venta->ventaDetalle as $key => $DetalleItem) {
                $producto_count = $producto_count + floatval($DetalleItem->cantidad);
            }
        }

        $recoleccion_count = self::getRecoleccion($reparto_id);

        return ($producto_count + $recoleccion_count  ) - ( self::getCarga($reparto_id)  + self::getDevolucion($reparto_id) ) ;*/

        $Reparto = Reparto::findOne($reparto_id);
        $query =  InvProductoSucursal::getStockRuta($Reparto->sucursal_id);

        $count_producto = 0;
        foreach ($query as $key => $item_producto) {
            $count_producto =  floatval($count_producto) +  floatval($item_producto->cantidad);
        }

        return $count_producto;
    }


    public static function getPzVendidas($reparto_id)
    {
        $Ventas = Venta::find()->andWhere(["and",
            ["=","reparto_id", $reparto_id ],
            ["=", "status",  Venta::STATUS_VENTA ],
        ])->all();

        $producto_count = 0;

        foreach ($Ventas as $key => $venta) {
            foreach ($venta->ventaDetalle as $key => $DetalleItem) {
                $producto_count = $producto_count + floatval($DetalleItem->cantidad);
            }
        }

        return $producto_count;
    }


    public static function getTotalVendido($reparto_id)
    {
        $Ventas = Venta::find()->andWhere(["and",
            ["=","reparto_id", $reparto_id ],
            ["=", "status",  Venta::STATUS_VENTA ],
        ])->all();

        $total_reparto = 0;

        foreach ($Ventas as $key => $venta) {
            $total_reparto = $total_reparto + floatval($venta->total);
        }

        return $total_reparto;
    }

    public static function getRepartoAperturado($sucursal_ruta_id)
    {
        return Reparto::find()->andWhere(["and",
            [ "=", "sucursal_id", $sucursal_ruta_id ],
            [ "=", "status" , Reparto::STATUS_RUTA ],
        ])->one();

    }

    public static function getRepartoProceso($sucursal_ruta_id)
    {
        return Reparto::find()->andWhere(["and",
            [ "=", "sucursal_id", $sucursal_ruta_id ],
            [ "=", "status" , Reparto::STATUS_PROCESO ],
        ])->one();

    }

    public static function setPrecapturaRechazo( $reparto_id, $user_id = null )
    {

        $RepartoDetalle = RepartoDetalle::find()->andWhere(["and",
            ["=","reparto_id", $reparto_id ],
            ["=","tipo", RepartoDetalle::TIPO_PRECAPTURA ],
        ])->all();


        foreach ($RepartoDetalle as $key => $detalleItem) {
            $ventaItem = Venta::findOne($detalleItem->ventaDetalle->venta_id);
            if ($ventaItem->status  == Venta::STATUS_PROCESO ) {
                //$ventaItem->reparto_id  = null;
                $ventaItem->reparto_id          = $reparto_id;
                $ventaItem->status              = Venta::STATUS_CANCEL;
                $ventaItem->updated_by          = $user_id;
                $ventaItem->update();
            }
        }
    }

    public static function getPrecapturaCliente($reparto_id)
    {
        return  (new Query())
                ->select([
                    'venta.cliente_id',
                ])
                ->from("venta")
                //->innerJoin("venta_detalle","reparto_detalle.venta_detalle_id = venta_detalle.id")
                //->innerJoin("venta", "venta_detalle.venta_id = venta.id")
                ->andWhere(["and",
                    ["=", "venta.reparto_id", $reparto_id ],
                    //["=", "reparto_detalle.tipo", RepartoDetalle::TIPO_PRECAPTURA ],
                    //["=", "venta.status", Venta::STATUS_VENTA ],
                    //["=","venta.pay_credito", Venta::PAY_CREDITO_ON ]
                ])
                ->groupBy("venta.cliente_id")
                ->all();
    }

    public static function getPreventaAll($item_cliente,$reparto_id)
    {
        return  (new Query())
                ->select([
                    'venta.id',
                    'venta.total',
                ])
                ->from("venta")
                ->andWhere(["and",
                    ["=", "venta.reparto_id", $reparto_id ],
                    ["=", "venta.cliente_id", $item_cliente ],
                    ["<>", "venta.status", Venta::STATUS_CANCEL ],
                    //["=","venta.pay_credito", Venta::PAY_CREDITO_ON ]
                ])
                //->groupBy("venta.id")
                ->all();
    }

    public static function getPreventaInfoAll($reparto_id)
    {
        return  (new Query())
                ->select([
                    'venta.id',
                    'venta.total',
                    'venta.status',
                    'venta.reparto_id',
                    'concat_ws(" ",cliente.nombre,cliente.apellidos) as cliente',
                    '(select sum(v_d.cantidad) from venta_detalle v_d where v_d.venta_id = venta.id) as productos',
                    '(select sum(r_d.cantidad) from reparto_detalle r_d inner join venta_detalle v_d_r on r_d.venta_detalle_id = v_d_r.id where  v_d_r.venta_id = venta.id ) as productos_carga',
                ])
                ->from("reparto_detalle")
                ->innerJoin("venta_detalle","reparto_detalle.venta_detalle_id = venta_detalle.id")
                ->innerJoin("venta", "venta_detalle.venta_id = venta.id")
                ->leftJoin("cliente","venta.cliente_id = cliente.id")
                ->andWhere(["and",
                    ["=", "reparto_detalle.reparto_id", $reparto_id ],
                    ["=", "reparto_detalle.tipo", RepartoDetalle::TIPO_PRECAPTURA ],
                ])
                ->groupBy("venta.id")
                ->all();
    }

    public static function clearRuta($ruta_id, $created_by)
    {

        $InvProductoSucursal_List = InvProductoSucursal::getStockRuta($ruta_id);

        foreach ($InvProductoSucursal_List as $key => $producto_item) {
            $Producto = Producto::findOne($producto_item->producto_id);

            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $producto_item->sucursal_id ], [ "=", "producto_id", $producto_item->producto_id ] ] )->one();

            if ($InvProducto->cantidad > 0)
                $InvProducto->cantidad = 0;

            $InvProducto->save();

            if ($InvProducto->cantidad > 0)
                TransProductoInventario::saveTransAjuste($producto_item->sucursal_id,null,$producto_item->producto_id,$InvProducto->cantidad,TransProductoInventario::TIPO_SALIDA,$created_by);
        }
    }


    /*public function movInventarioReparto()
    {
        $transaction = Yii::$app->db->beginTransaction();

        try
        {

            foreach ($this->repartoDetalles as $r_detalle_key => $r_detalle) {

                    $venta = $r_detalle->venta;

                    $venta_transaction = Yii::$app->db->beginTransaction();

                    try
                    {
                        $OperacionEntrada = new Operacion();
                        $OperacionEntrada->tipo     = Operacion::TIPO_ENTRADA;
                        $OperacionEntrada->motivo   = Operacion::ENTRADA_TRASPASO;
                        $OperacionEntrada->almacen_sucursal_id = $this->sucursal_id;
                        $OperacionEntrada->status   = Operacion::STATUS_ACTIVE;
                        $OperacionEntrada->save();
                        /*******************************************************************************************/
                        // REALIZAR LA OPERACION DE TRASPASO Y REGISTRA EL INGRESO EN EL INVENTARIO
                        /******************************************************************************************
                        foreach ($venta->ventaDetalle as $v_detalle_key => $v_detalle) {

                            $Producto = Producto::findOne($v_detalle->producto_id);
                            $sucursal = $this->sucursal_id;



                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                            else
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $v_detalle->producto_id ] ] )->one();


                            $inventario_entrada_transaction = Yii::$app->db->beginTransaction();




                            try
                            {

                                $OperacionEntradaDetalle = new OperacionDetalle();
                                $OperacionEntradaDetalle->operacion_id = $OperacionEntrada->id;
                                $OperacionEntradaDetalle->cantidad     = $v_detalle->cantidad;
                                $OperacionEntradaDetalle->costo        = $v_detalle->precio_venta;

                                if (isset($InvProducto->id)) {

                                    if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                                        $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();



                                        if (isset($InvProducto2->id)) {
                                            // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                                $InvProducto2->cantidad = floatval($InvProducto2->cantidad) + ( floatval($v_detalle->cantidad) * $Producto->sub_cantidad_equivalente);
                                                $InvProducto2->save();

                                                $OperacionEntradaDetalle->producto_id  = $Producto->sub_producto_id;



                                        }else{
                                            // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO

                                                $InvProductoSucursal  =  new InvProductoSucursal();
                                                $InvProductoSucursal->sucursal_id   = $sucursal;
                                                $InvProductoSucursal->producto_id   = $v_detalle->producto_id;
                                                $InvProductoSucursal->cantidad      = $v_detalle->cantidad;
                                                $InvProductoSucursal->save();

                                                $OperacionEntradaDetalle->producto_id  = $v_detalle->producto_id;


                                        }

                                    }else{


                                        // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO

                                            $InvProducto->cantidad = floatval($InvProducto->cantidad) + floatval($v_detalle->cantidad);
                                            $InvProducto->save();

                                            $OperacionEntradaDetalle->producto_id  = $v_detalle->producto_id;
                                    }

                                }else{

                                    // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO

                                        $InvProductoSucursal  =  new InvProductoSucursal();
                                        $InvProductoSucursal->sucursal_id   = $sucursal;
                                        $InvProductoSucursal->producto_id   = $v_detalle->producto_id;
                                        $InvProductoSucursal->cantidad      = $v_detalle->cantidad;
                                        $InvProductoSucursal->save();

                                        $OperacionEntradaDetalle->producto_id  = $v_detalle->producto_id;
                                }

                                $OperacionEntradaDetalle->save();

                                $inventario_entrada_transaction->commit();
                            }catch(Exception $e)
                            {
                                $inventario_entrada_transaction->rollBack();
                                return false;
                            }


                        }
                        /*******************************************************************************************/


                        /*******************************************************************************************/
                        // REALIZAR LA OPERACION DE TRASPASO Y QUITA EL PRODUCTO DEL INVENTARIO
                        /******************************************************************************************

                        $OperacionSalida = new Operacion();
                        $OperacionSalida->tipo     = Operacion::TIPO_SALIDA;
                        $OperacionSalida->motivo   = Operacion::SALIDA_TRASPASO;
                        $OperacionSalida->almacen_sucursal_id = Yii::$app->user->identity->sucursal_id;
                        $OperacionSalida->status   = Operacion::STATUS_ACTIVE;
                        $OperacionSalida->save();

                        foreach ($venta->ventaDetalle as $v_detalle_key => $v_detalle) {

                            $Producto = Producto::findOne($v_detalle->producto_id);
                            $sucursal = Yii::$app->user->identity->sucursal_id;



                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                            else
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $v_detalle->producto_id ] ] )->one();


                            $inventario_salida_transaction = Yii::$app->db->beginTransaction();

                            try
                            {

                                $OperacionSalidaDetalle = new OperacionDetalle();
                                $OperacionSalidaDetalle->operacion_id = $OperacionSalida->id;
                                $OperacionSalidaDetalle->cantidad     = $v_detalle->cantidad;
                                $OperacionSalidaDetalle->costo        = $v_detalle->precio_venta;

                                if (isset($InvProducto->id)) {

                                    if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                                        $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();



                                        if (isset($InvProducto2->id)) {
                                            // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                                $InvProducto2->cantidad = floatval($InvProducto2->cantidad) - ( floatval($v_detalle->cantidad) * $Producto->sub_cantidad_equivalente);
                                                $InvProducto2->save();

                                                $OperacionSalidaDetalle->producto_id  = $Producto->sub_producto_id;

                                        }else{
                                            // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO
                                            $InvProductoSucursal  =  new InvProductoSucursal();
                                            $InvProductoSucursal->sucursal_id   = $sucursal;
                                            $InvProductoSucursal->producto_id   = $v_detalle->producto_id;
                                            $InvProductoSucursal->cantidad      = $v_detalle->cantidad * -1;
                                            $InvProductoSucursal->save();
                                            $OperacionSalidaDetalle->producto_id  = $v_detalle->producto_id;
                                        }

                                    }else{

                                        // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                                        $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($v_detalle->cantidad);
                                        $InvProducto->save();
                                        $OperacionSalidaDetalle->producto_id  = $v_detalle->producto_id;
                                    }
                                }else{

                                    // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $sucursal;
                                    $InvProductoSucursal->producto_id   = $v_detalle->producto_id;
                                    $InvProductoSucursal->cantidad      = $v_detalle->cantidad * -1;
                                    $InvProductoSucursal->save();

                                    $OperacionSalidaDetalle->producto_id  = $v_detalle->producto_id;
                                }

                                $OperacionSalidaDetalle->save();
                                $inventario_salida_transaction->commit();

                            }catch(Exception $e)
                            {
                                $inventario_salida_transaction->rollBack();
                                return false;
                            }
                        }
                        /******************************************************************************************

                        $venta->status = Venta::STATUS_PROCESO;
                        $venta->update();
                        $venta_transaction->commit();
                    }catch(Exception $e)
                    {

                        $venta_transaction->rollBack();

                        return false;
                    }

            }

            $this->status = Reparto::STATUS_RUTA;
            $this->update();
            $transaction->commit();
            return true;
        }
        catch(Exception $e)
        {
            $transaction->rollBack();
            return false;
        }
    }*/


    public static function movInventarioRepartoPrecapturaApp($sucursal_ruta_id, $precapturas, $productos, $sucursal_bodega_id, $created_by, $reparto_id)
    {

        $transReparto = Yii::$app->db->beginTransaction();

        try
        {
            // VERIFICAMOS SI EXISTE UN REPARTO O SE ADICIONA PREVENTAS / PRODUCTO A UNO EXISTENTE
            if (empty($reparto_id)) {
                $reparto                = new Reparto();
                $reparto->sucursal_id   = $sucursal_ruta_id;
                $reparto->status        = Reparto::STATUS_RUTA;
                $reparto->created_by    = $created_by;
                $reparto->save();
            }else{
                $reparto                = Reparto::findOne($reparto_id);
            }

            $OperacionEntrada = new Operacion();
            $OperacionEntrada->tipo     = Operacion::TIPO_ENTRADA;
            $OperacionEntrada->motivo   = Operacion::ENTRADA_TRASPASO_UNIDAD;
            $OperacionEntrada->almacen_sucursal_id = $sucursal_ruta_id;
            $OperacionEntrada->reparto_id = $reparto->id;
            $OperacionEntrada->status       = Operacion::STATUS_ACTIVE;
            $OperacionEntrada->created_by   = $created_by;
            $OperacionEntrada->save();



            $OperacionSalida = new Operacion();
            $OperacionSalida->tipo     = Operacion::TIPO_SALIDA;
            $OperacionSalida->motivo   = Operacion::SALIDA_TRASPASO_UNIDAD;
            $OperacionSalida->almacen_sucursal_id = $sucursal_bodega_id;
            $OperacionSalida->operacion_child_id  = $OperacionEntrada->id;
            $OperacionSalida->status   = Operacion::STATUS_ACTIVE;
            $OperacionSalida->created_by   = $created_by;
            $OperacionSalida->save();

            /**
             *
            */

            $preventa_valid = [];
            foreach ($precapturas as $r_detalle_key => $r_detalle) {

                $ventaDetalle   = VentaDetalle::findOne($r_detalle["venta_detalle_id"]);
                $is_add         = false;

                $is_search      = false;
                foreach ($preventa_valid as $key => $item_search) {
                    if (intval($item_search["venta_id"]) == $ventaDetalle->venta_id ) {
                        $is_search = true;
                    }
                }

                if (!$is_search) {
                    /*VALIDAMOS LA PREVENTA SOLO UNA VEZ, NO ES NECESAIO VALIDARLO POR CADA PRODUCTO PORQUE VALIDA TODA LA PREVENTA*/
                    $is_valid       = self::validateCargaUnidadVenta($ventaDetalle->venta_id,$sucursal_bodega_id);
                    array_push($preventa_valid, [
                        "venta_id"  => intval($ventaDetalle->venta_id),
                        "valid"     => empty($is_valid) ? 10 : 20 , // SI LA PREVENTA ES VALIDADA CORRECTAMENTE SE INGRESA EL VALOR DE 10, SI EXISTE UN PROBLEMA DE ABASTECIMIENTO VALOR 20
                    ]);
                }

                /*BUSCAMOS EL RESULTADO DE SU VALIDACION DE LA PREVENTA*/
                foreach ($preventa_valid as $key => $item_valid) {
                    if (intval($item_valid["venta_id"]) == $ventaDetalle->venta_id ) {
                        if ($item_valid["valid"] == 10 ) {
                            $is_add = true;
                        }
                    }
                }

                if ($is_add) {

                    $transactionItem = Yii::$app->db->beginTransaction();
                    try
                    {
                        $RepartoDetalle = new RepartoDetalle();
                        $RepartoDetalle->reparto_id   = $reparto->id;
                        $RepartoDetalle->tipo         = 10;
                        $RepartoDetalle->venta_detalle_id  = $ventaDetalle->id;
                        $RepartoDetalle->producto_id  = $r_detalle["producto_id"];
                        $RepartoDetalle->cantidad     = $r_detalle["cantidad"];
                        $RepartoDetalle->created_by   = $created_by;
                        $RepartoDetalle->save();


                        /*******************************************************************************************/
                        // REALIZAR LA OPERACION DE TRASPASO Y REGISTRA EL INGRESO EN EL INVENTARIO
                        /*******************************************************************************************/


                            $Producto = Producto::findOne($r_detalle["producto_id"]);
                            $sucursal = $sucursal_ruta_id;

                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $r_detalle["producto_id"] ] ] )->one();


                            $inventario_entrada_transaction = Yii::$app->db->beginTransaction();

                            try
                            {

                                $OperacionEntradaDetalle = new OperacionDetalle();
                                $OperacionEntradaDetalle->operacion_id = $OperacionEntrada->id;
                                $OperacionEntradaDetalle->cantidad     = $r_detalle["cantidad"];
                                $OperacionEntradaDetalle->costo        = $ventaDetalle->precio_venta;

                                if (isset($InvProducto->id)) {
                                    // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                                        $InvProducto->cantidad = floatval($InvProducto->cantidad) + floatval($r_detalle["cantidad"]);
                                        $InvProducto->save();

                                        $OperacionEntradaDetalle->producto_id  = $r_detalle["producto_id"];
                                    // SE REGISTRA EVENTO DEL MOVIMIENTO
                                }else{

                                    // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO

                                        $InvProductoSucursal  =  new InvProductoSucursal();
                                        $InvProductoSucursal->sucursal_id   = $sucursal;
                                        $InvProductoSucursal->producto_id   = $r_detalle["producto_id"];
                                        $InvProductoSucursal->cantidad      = $r_detalle["cantidad"];
                                        $InvProductoSucursal->created_by    = $created_by;
                                        $InvProductoSucursal->save();

                                        $OperacionEntradaDetalle->producto_id  = $r_detalle["producto_id"];

                                    // SE REGISTRA EVENTO DEL MOVIMIENTO
                                }

                                $OperacionEntradaDetalle->save();

                                 TransProductoInventario::saveTransOperacion($sucursal_ruta_id,$OperacionEntradaDetalle->id,$OperacionEntradaDetalle->producto_id,$OperacionEntradaDetalle->cantidad,TransProductoInventario::TIPO_ENTRADA,$created_by);



                                $inventario_entrada_transaction->commit();
                            }catch(\Exception $e)
                            {
                                $inventario_entrada_transaction->rollBack();
                                return false;
                            }



                        /*******************************************************************************************/

                        /*******************************************************************************************/
                        // REALIZAR LA OPERACION DE TRASPASO Y QUITA EL PRODUCTO DEL INVENTARIO
                        /*******************************************************************************************/

                            $Producto = Producto::findOne($r_detalle["producto_id"]);
                            $sucursal = $sucursal_bodega_id;

                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $r_detalle["producto_id"] ] ] )->one();

                            $inventario_salida_transaction = Yii::$app->db->beginTransaction();

                            try
                            {

                                $OperacionSalidaDetalle = new OperacionDetalle();
                                $OperacionSalidaDetalle->operacion_id = $OperacionSalida->id;
                                $OperacionSalidaDetalle->cantidad     = $r_detalle["cantidad"];
                                $OperacionSalidaDetalle->costo        = $ventaDetalle->precio_venta;



                                if (isset($InvProducto->id)) {
                                    // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                                    $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($r_detalle["cantidad"]);
                                    $InvProducto->save();
                                    $OperacionSalidaDetalle->producto_id  = $r_detalle["producto_id"];

                                }else{

                                    // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $sucursal;
                                    $InvProductoSucursal->producto_id   = $r_detalle["producto_id"];
                                    $InvProductoSucursal->cantidad      = $r_detalle["cantidad"] * -1;
                                    $InvProductoSucursal->created_by    = $created_by;
                                    $InvProductoSucursal->save();

                                    $OperacionSalidaDetalle->producto_id  = $r_detalle["producto_id"];
                                }

                                $OperacionSalidaDetalle->save();
                                TransProductoInventario::saveTransOperacion($sucursal_bodega_id,$OperacionSalidaDetalle->id,$OperacionSalidaDetalle->producto_id,$OperacionSalidaDetalle->cantidad,TransProductoInventario::TIPO_SALIDA,  $created_by);


                                $inventario_salida_transaction->commit();

                            }catch(\Exception $e)
                            {
                                $inventario_salida_transaction->rollBack();
                                return false;
                            }

                        /*******************************************************************************************/

                        $venta = Venta::findOne($ventaDetalle->venta_id);
                        $venta->status = Venta::STATUS_PROCESO;
                        $venta->update();
                        $transactionItem->commit();
                    }catch(\Exception $e)
                    {

                        $transactionItem->rollBack();

                        return false;
                    }
                }
           }

            /**
             *
            */

            foreach ($productos as $r_detalle_key => $r_detalle) {

                $validTaraAbierta = [];

                array_push($validTaraAbierta, [
                    "producto_id" => $r_detalle["producto_id"],
                    "cantidad" => $r_detalle["cantidad"],
                ]);

                $validResponse = self::validateVentaRuta($validTaraAbierta, $sucursal_bodega_id);

                if (empty($validResponse)) {
                    $transactionItem = Yii::$app->db->beginTransaction();
                    try
                    {
                        $RepartoDetalle = new RepartoDetalle();
                        $RepartoDetalle->reparto_id   = $reparto->id;
                        $RepartoDetalle->tipo         = RepartoDetalle::TIPO_PRODUCTO;
                        $RepartoDetalle->producto_id  = $r_detalle["producto_id"];
                        $RepartoDetalle->cantidad     = $r_detalle["cantidad"];
                        $RepartoDetalle->created_by   = $created_by;
                        $RepartoDetalle->save();


                        /*******************************************************************************************/
                        // REALIZAR LA OPERACION DE TRASPASO Y REGISTRA EL INGRESO EN EL INVENTARIO
                        /*******************************************************************************************/


                            $Producto = Producto::findOne($r_detalle["producto_id"]);
                            $sucursal = $sucursal_ruta_id;

                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $r_detalle["producto_id"] ] ] )->one();


                            $inventario_entrada_transaction = Yii::$app->db->beginTransaction();

                            try
                            {

                                $OperacionEntradaDetalle = new OperacionDetalle();
                                $OperacionEntradaDetalle->operacion_id = $OperacionEntrada->id;
                                $OperacionEntradaDetalle->cantidad     = $r_detalle["cantidad"];
                                $OperacionEntradaDetalle->costo        = $r_detalle["precio_venta"];

                                if (isset($InvProducto->id)) {

                                    // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO

                                        $InvProducto->cantidad = floatval($InvProducto->cantidad) + floatval($r_detalle["cantidad"]);
                                        $InvProducto->save();

                                        $OperacionEntradaDetalle->producto_id  = $r_detalle["producto_id"];

                                    // SE REGISTRA EVENTO DEL MOVIMIENTO
                                }else{

                                    // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO

                                        $InvProductoSucursal  =  new InvProductoSucursal();
                                        $InvProductoSucursal->sucursal_id   = $sucursal;
                                        $InvProductoSucursal->producto_id   = $r_detalle["producto_id"];
                                        $InvProductoSucursal->cantidad      = $r_detalle["cantidad"];
                                        $InvProductoSucursal->created_by    = $created_by;
                                        $InvProductoSucursal->save();

                                        $OperacionEntradaDetalle->producto_id  = $r_detalle["producto_id"];

                                    // SE REGISTRA EVENTO DEL MOVIMIENTO
                                }

                                $OperacionEntradaDetalle->save();

                                TransProductoInventario::saveTransOperacion($sucursal_ruta_id,$OperacionEntradaDetalle->id,$OperacionEntradaDetalle->producto_id,$OperacionEntradaDetalle->cantidad,TransProductoInventario::TIPO_ENTRADA, $created_by);


                                $inventario_entrada_transaction->commit();
                            }catch(\Exception $e)
                            {
                                $inventario_entrada_transaction->rollBack();
                                return false;
                            }



                        /*******************************************************************************************/


                        /*******************************************************************************************/
                        // REALIZAR LA OPERACION DE TRASPASO Y QUITA EL PRODUCTO DEL INVENTARIO
                        /*******************************************************************************************/

                            $Producto = Producto::findOne($r_detalle["producto_id"]);
                            $sucursal = $sucursal_bodega_id;



                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $r_detalle["producto_id"] ] ] )->one();


                            $inventario_salida_transaction = Yii::$app->db->beginTransaction();

                            try
                            {

                                $OperacionSalidaDetalle = new OperacionDetalle();
                                $OperacionSalidaDetalle->operacion_id = $OperacionSalida->id;
                                $OperacionSalidaDetalle->cantidad     = $r_detalle["cantidad"];
                                $OperacionSalidaDetalle->costo        = $r_detalle["precio_venta"];



                                if (isset($InvProducto->id)) {
                                    // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                                    $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($r_detalle["cantidad"]);
                                    $InvProducto->save();
                                    $OperacionSalidaDetalle->producto_id  = $r_detalle["producto_id"];

                                }else{

                                    // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $sucursal;
                                    $InvProductoSucursal->producto_id   = $r_detalle["producto_id"];
                                    $InvProductoSucursal->cantidad      = $r_detalle["cantidad"] * -1;
                                    $InvProductoSucursal->created_by    = $created_by;
                                    $InvProductoSucursal->save();

                                    $OperacionSalidaDetalle->producto_id  = $r_detalle["producto_id"];
                                }

                                $OperacionSalidaDetalle->save();

                                TransProductoInventario::saveTransOperacion($sucursal_bodega_id,$OperacionSalidaDetalle->id,$OperacionSalidaDetalle->producto_id,$OperacionSalidaDetalle->cantidad,TransProductoInventario::TIPO_SALIDA, $created_by);


                                $inventario_salida_transaction->commit();

                            }catch(\Exception $e)
                            {
                                $inventario_salida_transaction->rollBack();
                                return false;
                            }

                        /*******************************************************************************************/

                        $transactionItem->commit();
                    }catch(\Exception $e)
                    {

                        $transactionItem->rollBack();

                        return false;
                    }
                }
            }

            $transReparto->commit();
            return true;
        }
        catch(\Exception $e)
        {
            $transReparto->rollBack();
            return false;
        }
    }


    public static function validateCargaUnidadVenta($venta_id,$inventario_sucursal_id)
    {
        $response = [];

        $venta = Venta::findOne($venta_id);

        foreach ($venta->ventaDetalle as $key => $item_detalle) {

            $is_add = false;

            $Producto = Producto::findOne($item_detalle->producto_id);

            $InvProducto = null;


            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $inventario_sucursal_id ], [ "=", "producto_id", $item_detalle->producto_id ] ] )->one();


            if (isset($InvProducto->id)) {
                // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                if (floatval($item_detalle->cantidad) > floatval($InvProducto->cantidad)) {
                    $is_add = true;
                }
            }else{
                $is_add = true; // Cuando agregan producto que su inventario no maneja en ese momento
            }


            if($item_detalle->cantidad == 0)
                $is_add = true; // No subir preventas con alguna conversion pendiende de pz a kilos

            if ($is_add) {
                if ($InvProducto) {
                    array_push($response,[
                        "code"     =>  10,
                        "producto" => $Producto->nombre,
                        "inv" => $InvProducto,
                    ]);
                }else{
                    array_push($response,[
                        "code"     =>  20,
                        "producto" => $Producto->nombre,
                        "message" => "** Actualmente este producto no lo maneja tu inventario **",
                    ]);
                }
            }

        }

        return $response;

    }

    public static function movInventarioRecoleccionApp($sucursal_ruta_id,  $productos, $sucursal_bodega_id, $created_by, $reparto_id)
    {
        $transReparto = Yii::$app->db->beginTransaction();
        try
        {


            $OperacionEntrada = new Operacion();
            $OperacionEntrada->tipo     = Operacion::TIPO_ENTRADA;
            $OperacionEntrada->motivo   = Operacion::ENTRADA_TRASPASO_RECOLECCION;
            $OperacionEntrada->almacen_sucursal_id = $sucursal_bodega_id;
            $OperacionEntrada->ruta_recoleccion_id = $sucursal_ruta_id;
            $OperacionEntrada->status       = Operacion::STATUS_ACTIVE;
            $OperacionEntrada->reparto_id   = $reparto_id;
            $OperacionEntrada->created_by   = $created_by;
            $OperacionEntrada->save();



            $OperacionSalida = new Operacion();
            $OperacionSalida->tipo     = Operacion::TIPO_SALIDA;
            $OperacionSalida->motivo   = Operacion::SALIDA_TRASPASO_RECOLECCION;
            $OperacionSalida->almacen_sucursal_id = $sucursal_ruta_id;
            $OperacionSalida->operacion_child_id  = $OperacionEntrada->id;
            $OperacionSalida->status   = Operacion::STATUS_ACTIVE;
            $OperacionSalida->reparto_id   = $reparto_id;
            $OperacionSalida->created_by   = $created_by;
            $OperacionSalida->save();



            foreach ($productos as $r_detalle_key => $r_detalle) {



                    $transactionItem = Yii::$app->db->beginTransaction();

                    try
                    {



                        /*******************************************************************************************/
                        // REALIZAR LA OPERACION DE TRASPASO Y REGISTRA EL INGRESO EN EL INVENTARIO
                        /*******************************************************************************************/


                            $Producto = Producto::findOne($r_detalle["producto_id"]);
                            $sucursal = $sucursal_bodega_id;



                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                            else
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $r_detalle["producto_id"] ] ] )->one();


                            $inventario_entrada_transaction = Yii::$app->db->beginTransaction();

                            try
                            {

                                $OperacionEntradaDetalle = new OperacionDetalle();
                                $OperacionEntradaDetalle->operacion_id = $OperacionEntrada->id;
                                $OperacionEntradaDetalle->cantidad     = $r_detalle["cantidad"];
                                $OperacionEntradaDetalle->costo        = 0;



                                if (isset($InvProducto->id)) {

                                    if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                                        $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();



                                        if (isset($InvProducto2->id)) {
                                            // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                                $InvProducto2->cantidad = floatval($InvProducto2->cantidad) + ( floatval($r_detalle["cantidad"]) * $Producto->sub_cantidad_equivalente);
                                                $InvProducto2->save();

                                                $OperacionEntradaDetalle->producto_id  = $Producto->sub_producto_id;

                                            // SE REGISTRA EVENTO DEL MOVIMIENTO


                                        }else{
                                            // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO

                                                $InvProductoSucursal  =  new InvProductoSucursal();
                                                $InvProductoSucursal->sucursal_id   = $sucursal;
                                                $InvProductoSucursal->producto_id   = $r_detalle["producto_id"];
                                                $InvProductoSucursal->cantidad      = $r_detalle["cantidad"];
                                                $InvProductoSucursal->created_by    = $created_by;
                                                $InvProductoSucursal->save();

                                                $OperacionEntradaDetalle->producto_id  = $r_detalle["producto_id"];

                                            // SE REGISTRA EVENTO DEL MOVIMIENTO
                                        }

                                    }else{


                                        // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO

                                            $InvProducto->cantidad = floatval($InvProducto->cantidad) + floatval($r_detalle["cantidad"]);
                                            $InvProducto->save();

                                            $OperacionEntradaDetalle->producto_id  = $r_detalle["producto_id"];

                                        // SE REGISTRA EVENTO DEL MOVIMIENTO


                                    }

                                }else{

                                    // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO

                                        $InvProductoSucursal  =  new InvProductoSucursal();
                                        $InvProductoSucursal->sucursal_id   = $sucursal;
                                        $InvProductoSucursal->producto_id   = $r_detalle["producto_id"];
                                        $InvProductoSucursal->cantidad      = $r_detalle["cantidad"];
                                        $InvProductoSucursal->created_by    = $created_by;
                                        $InvProductoSucursal->save();

                                        $OperacionEntradaDetalle->producto_id  = $r_detalle["producto_id"];

                                    // SE REGISTRA EVENTO DEL MOVIMIENTO

                                }

                                $OperacionEntradaDetalle->save();

                                TransProductoInventario::saveTransOperacion($sucursal_bodega_id,$OperacionEntradaDetalle->id,$OperacionEntradaDetalle->producto_id,$OperacionEntradaDetalle->cantidad,TransProductoInventario::TIPO_ENTRADA, $created_by);

                                $inventario_entrada_transaction->commit();
                            }catch(Exception $e)
                            {
                                $inventario_entrada_transaction->rollBack();
                                return false;
                            }



                        /*******************************************************************************************/


                        /*******************************************************************************************/
                        // REALIZAR LA OPERACION DE TRASPASO Y QUITA EL PRODUCTO DEL INVENTARIO
                        /*******************************************************************************************/


                            $Producto = Producto::findOne($r_detalle["producto_id"]);
                            $sucursal = $sucursal_ruta_id;

                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                            else
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $r_detalle["producto_id"] ] ] )->one();


                            $inventario_salida_transaction = Yii::$app->db->beginTransaction();

                            try
                            {

                                $OperacionSalidaDetalle = new OperacionDetalle();
                                $OperacionSalidaDetalle->operacion_id = $OperacionSalida->id;
                                $OperacionSalidaDetalle->cantidad     = $r_detalle["cantidad"];
                                $OperacionSalidaDetalle->costo        = 0;



                                if (isset($InvProducto->id)) {

                                    if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                                        $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();



                                        if (isset($InvProducto2->id)) {
                                            // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                                $InvProducto2->cantidad = floatval($InvProducto2->cantidad) - ( floatval($r_detalle["cantidad"]) * $Producto->sub_cantidad_equivalente);
                                                $InvProducto2->save();

                                                $OperacionSalidaDetalle->producto_id  = $Producto->sub_producto_id;

                                        }else{
                                            // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO
                                            $InvProductoSucursal  =  new InvProductoSucursal();
                                            $InvProductoSucursal->sucursal_id   = $sucursal;
                                            $InvProductoSucursal->producto_id   = $r_detalle["producto_id"];
                                            $InvProductoSucursal->cantidad      = $r_detalle["cantidad"] * -1;
                                            $InvProductoSucursal->created_by    = $created_by;
                                            $InvProductoSucursal->save();
                                            $OperacionSalidaDetalle->producto_id  = $r_detalle["producto_id"];
                                        }

                                    }else{

                                        // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                                        $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($r_detalle["cantidad"]);
                                        $InvProducto->save();
                                        $OperacionSalidaDetalle->producto_id  = $r_detalle["producto_id"];
                                    }
                                }else{

                                    // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $sucursal;
                                    $InvProductoSucursal->producto_id   = $r_detalle["producto_id"];
                                    $InvProductoSucursal->cantidad      = $r_detalle["cantidad"] * -1;
                                    $InvProductoSucursal->created_by    = $created_by;
                                    $InvProductoSucursal->save();

                                    $OperacionSalidaDetalle->producto_id  = $r_detalle["producto_id"];
                                }

                                $OperacionSalidaDetalle->save();

                                TransProductoInventario::saveTransOperacion($sucursal_ruta_id,$OperacionSalidaDetalle->id,$OperacionSalidaDetalle->producto_id,$OperacionSalidaDetalle->cantidad,TransProductoInventario::TIPO_SALIDA, $created_by);

                                $inventario_salida_transaction->commit();

                            }catch(Exception $e)
                            {
                                $inventario_salida_transaction->rollBack();
                                return false;
                            }

                        /*******************************************************************************************/

                        $transactionItem->commit();
                    }catch(Exception $e)
                    {

                        $transactionItem->rollBack();

                        return false;
                    }

            }

            $transReparto->commit();
            return true;
        }
        catch(Exception $e)
        {
            $transReparto->rollBack();
            return false;
        }
    }


    public static function validateVentaRuta($mv_detalle,$inventario_sucursal_id)
    {
        $response = [];

        foreach ($mv_detalle as $key => $item_detalle) {

            $is_add = false;

            $Producto = Producto::findOne($item_detalle["producto_id"]);

            $InvProducto = null;


            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $inventario_sucursal_id ], [ "=", "producto_id", $item_detalle["producto_id"] ] ] )->one();


            if (isset($InvProducto->id)) {
                // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                if (floatval($item_detalle["cantidad"]) > floatval($InvProducto->cantidad)) {
                    $is_add = true;
                }
            }else{
                $is_add = true; // Cuando agregan producto que su inventario no maneja en ese momento
            }


            if ($is_add) {
                if ($InvProducto) {
                    array_push($response,[
                        "code"     =>  10,
                        "producto" => $Producto->nombre,
                        "inv" => $InvProducto,
                    ]);
                }else{
                    array_push($response,[
                        "code"     =>  20,
                        "producto" => $Producto->nombre,
                        "message" => "** Actualmente este producto no lo maneja tu inventario **",
                    ]);
                }
            }

            if (floatval($item_detalle["cantidad"]) == 0)
                array_push($response,[
                    "code"     =>  20,
                    "producto" => $Producto->nombre,
                    "message" => "** La cantidad no puede ser igual a [0], verifica tu PREVENTA **",
                ]);

        }

        return $response;

    }


    public static function saveOperacion($movimiento_detalle, $sucursal_envia_id, $sucursal_recibe_id, $reparto_id)
    {

        $transaction = Yii::$app->db->beginTransaction();

        try
        {

                $OperacionEntrada = new Operacion();
                $OperacionEntrada->tipo     = Operacion::TIPO_ENTRADA;
                $OperacionEntrada->motivo   = Operacion::ENTRADA_RUTA_AJUSTE;
                $OperacionEntrada->almacen_sucursal_id      = $sucursal_recibe_id;
                $OperacionEntrada->sucursal_recibe_id       = $sucursal_envia_id;
                $OperacionEntrada->reparto_id = $reparto_id;
                $OperacionEntrada->status   = Operacion::STATUS_ACTIVE;
                $OperacionEntrada->save();
                /*******************************************************************************************/
                // REALIZAR LA OPERACION DE TRASPASO Y REGISTRA EL INGRESO EN EL INVENTARIO
                /*******************************************************************************************/
                foreach ($movimiento_detalle as $v_detalle_key => $v_detalle) {

                    $Producto = Producto::findOne($v_detalle["producto_id"]);
                    $sucursal = $sucursal_recibe_id;



                    if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                    else
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $v_detalle["producto_id"] ] ] )->one();


                    $inventario_entrada_transaction = Yii::$app->db->beginTransaction();

                    try
                    {

                        $OperacionEntradaDetalle = new OperacionDetalle();
                        $OperacionEntradaDetalle->operacion_id = $OperacionEntrada->id;
                        $OperacionEntradaDetalle->cantidad     = $v_detalle["cantidad"];
                        $OperacionEntradaDetalle->costo        = 0;

                        if ($v_detalle["tipo"] == 10 ) {
                            $RepartoDetalle = new RepartoDetalle();
                            $RepartoDetalle->reparto_id   = $reparto_id;
                            $RepartoDetalle->tipo         = RepartoDetalle::TIPO_PRECAPTURA;
                            $RepartoDetalle->venta_detalle_id  = $v_detalle["venta_detalle_id"];
                            $RepartoDetalle->producto_id  = $v_detalle["producto_id"];
                            $RepartoDetalle->cantidad     = $v_detalle["cantidad"];
                            //$RepartoDetalle->created_by   = $created_by;
                            $RepartoDetalle->save();
                        }else{
                            $RepartoDetalle = new RepartoDetalle();
                            $RepartoDetalle->reparto_id   = $reparto_id;
                            $RepartoDetalle->tipo         = RepartoDetalle::TIPO_PRODUCTO;
                            $RepartoDetalle->producto_id  = $v_detalle["producto_id"];
                            $RepartoDetalle->cantidad     = $v_detalle["cantidad"];
                            //$RepartoDetalle->created_by   = $created_by;
                            $RepartoDetalle->save();
                        }

                        if (isset($InvProducto->id)) {



                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                                $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();



                                if (isset($InvProducto2->id)) {
                                    // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                        $InvProducto2->cantidad = floatval($InvProducto2->cantidad) + ( floatval($v_detalle["cantidad"]) * $Producto->sub_cantidad_equivalente);
                                        $InvProducto2->save();

                                        $OperacionEntradaDetalle->producto_id  = $Producto->sub_producto_id;

                                    if ($v_detalle["tipo"] == 10 ) {
                                        // SE REGISTRA EVENTO DEL MOVIMIENTO
                                    }

                                }else{
                                    // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO

                                        $InvProductoSucursal  =  new InvProductoSucursal();
                                        $InvProductoSucursal->sucursal_id   = $sucursal;
                                        $InvProductoSucursal->producto_id   = $v_detalle["producto_id"];
                                        $InvProductoSucursal->cantidad      = $v_detalle["cantidad"];
                                        $InvProductoSucursal->save();

                                        $OperacionEntradaDetalle->producto_id  = $v_detalle["producto_id"];

                                    if ($v_detalle["tipo"] == 10 ) {
                                        // SE REGISTRA EVENTO DEL MOVIMIENTO


                                    }
                                }

                            }else{

                                // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO

                                    $InvProducto->cantidad = floatval($InvProducto->cantidad) + floatval($v_detalle["cantidad"]);
                                    $InvProducto->save();

                                    $OperacionEntradaDetalle->producto_id  = $v_detalle["producto_id"];

                                if ($v_detalle["tipo"] == 10 ) {
                                    // SE REGISTRA EVENTO DEL MOVIMIENTO

                                }
                            }

                        }else{

                            // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO

                                $InvProductoSucursal  =  new InvProductoSucursal();
                                $InvProductoSucursal->sucursal_id   = $sucursal;
                                $InvProductoSucursal->producto_id   = $v_detalle["producto_id"];
                                $InvProductoSucursal->cantidad      = $v_detalle["cantidad"];
                                $InvProductoSucursal->save();

                                $OperacionEntradaDetalle->producto_id  = $v_detalle["producto_id"];

                            // SE REGISTRA EVENTO DEL MOVIMIENTO

                            if ($v_detalle["tipo"] == 10 ) {
                                // SE REGISTRA EVENTO DEL MOVIMIENTO
                            }
                        }

                        if ($v_detalle["tipo"] == 10 ) {
                            $venta = Venta::findOne($v_detalle["venta_id"]);
                            $venta->status = Venta::STATUS_PROCESO;
                            $venta->update();
                        }

                        $OperacionEntradaDetalle->save();

                        TransProductoInventario::saveTransOperacion($sucursal_recibe_id,$OperacionEntradaDetalle->id,$OperacionEntradaDetalle->producto_id,$OperacionEntradaDetalle->cantidad,TransProductoInventario::TIPO_ENTRADA);

                        $inventario_entrada_transaction->commit();
                    }catch(Exception $e)
                    {
                        $inventario_entrada_transaction->rollBack();
                        return false;
                    }


                }
                /*******************************************************************************************/


                /*******************************************************************************************/
                // REALIZAR LA OPERACION DE TRASPASO Y QUITA EL PRODUCTO DEL INVENTARIO
                /*******************************************************************************************/

                $OperacionSalida = new Operacion();
                $OperacionSalida->tipo     = Operacion::TIPO_SALIDA;
                $OperacionSalida->motivo   = Operacion::SALIDA_RUTA_AJUSTE;
                $OperacionSalida->almacen_sucursal_id = $sucursal_envia_id;
                $OperacionSalida->sucursal_recibe_id = $sucursal_recibe_id;
                $OperacionSalida->status   = Operacion::STATUS_ACTIVE;
                $OperacionSalida->save();

                foreach ($movimiento_detalle as $v_detalle_key => $v_detalle) {

                    $Producto = Producto::findOne($v_detalle["producto_id"]);
                    $sucursal = $sucursal_envia_id;



                    if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                    else
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $v_detalle["producto_id"] ] ] )->one();


                    $inventario_salida_transaction = Yii::$app->db->beginTransaction();

                    try
                    {

                        $OperacionSalidaDetalle = new OperacionDetalle();
                        $OperacionSalidaDetalle->operacion_id = $OperacionSalida->id;
                        $OperacionSalidaDetalle->cantidad     = $v_detalle["cantidad"];
                        $OperacionSalidaDetalle->costo        = 0;

                        if (isset($InvProducto->id)) {

                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                                $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();



                                if (isset($InvProducto2->id)) {
                                    // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                        $InvProducto2->cantidad = floatval($InvProducto2->cantidad) - ( floatval($v_detalle["cantidad"]) * $Producto->sub_cantidad_equivalente);
                                        $InvProducto2->save();

                                        $OperacionSalidaDetalle->producto_id  = $Producto->sub_producto_id;

                                }else{
                                    // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO
                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $sucursal;
                                    $InvProductoSucursal->producto_id   = $v_detalle["producto_id"];
                                    $InvProductoSucursal->cantidad      = $v_detalle["cantidad"] * -1;
                                    $InvProductoSucursal->save();
                                    $OperacionSalidaDetalle->producto_id  = $v_detalle["producto_id"];
                                }

                            }else{

                                // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                                $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($v_detalle["cantidad"]);
                                $InvProducto->save();
                                $OperacionSalidaDetalle->producto_id  = $v_detalle["producto_id"];
                            }
                        }else{

                            // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                            $InvProductoSucursal  =  new InvProductoSucursal();
                            $InvProductoSucursal->sucursal_id   = $sucursal;
                            $InvProductoSucursal->producto_id   = $v_detalle["producto_id"];
                            $InvProductoSucursal->cantidad      = $v_detalle["cantidad"] * -1;
                            $InvProductoSucursal->save();

                            $OperacionSalidaDetalle->producto_id  = $v_detalle["producto_id"];
                        }

                        $OperacionSalidaDetalle->save();

                        TransProductoInventario::saveTransOperacion($sucursal_envia_id,$OperacionSalidaDetalle->id,$OperacionSalidaDetalle->producto_id,$OperacionSalidaDetalle->cantidad,TransProductoInventario::TIPO_SALIDA);

                        $inventario_salida_transaction->commit();

                    }catch(Exception $e)
                    {
                        $inventario_salida_transaction->rollBack();
                        return false;
                    }
                }
                /******************************************************************************************/

            $transaction->commit();
            return true;
        }
        catch(Exception $e)
        {
            $transaction->rollBack();
            return false;
        }

    }

    public static function saveOperacionReApertura($movimiento_detalle, $sucursal_envia_id, $sucursal_recibe_id, $reparto_id)
    {

        $transaction = Yii::$app->db->beginTransaction();

        try
        {
                /*******************************************************************************************/
                // REALIZAR LA OPERACION DE TRASPASO Y REGISTRA EL INGRESO EN EL INVENTARIO
                /*******************************************************************************************/
                foreach ($movimiento_detalle as $v_detalle_key => $v_detalle) {

                    $Producto = Producto::findOne($v_detalle["producto_id"]);
                    $sucursal = $sucursal_recibe_id;



                    if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                    else
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $v_detalle["producto_id"] ] ] )->one();


                    $inventario_entrada_transaction = Yii::$app->db->beginTransaction();

                    try
                    {

                        $validExistencia = RepartoDetalle::find()->andWhere(["and",
                            ["=", "reparto_id", $reparto_id ],
                            ["=","venta_detalle_id", $v_detalle["venta_detalle_id"]]
                        ])->one();

                        if (!$validExistencia->id) {
                            $RepartoDetalle = new RepartoDetalle();
                            $RepartoDetalle->reparto_id   = $reparto_id;
                            $RepartoDetalle->tipo         = RepartoDetalle::TIPO_PRECAPTURA;
                            $RepartoDetalle->venta_detalle_id  = $v_detalle["venta_detalle_id"];
                            $RepartoDetalle->producto_id  = $v_detalle["producto_id"];
                            $RepartoDetalle->cantidad     = $v_detalle["cantidad"];
                            $RepartoDetalle->save();

                            TransProductoInventario::saveTransReparto($sucursal_recibe_id,$RepartoDetalle->id,$RepartoDetalle->producto_id,$RepartoDetalle->cantidad,TransProductoInventario::TIPO_ENTRADA);
                        }



                        if ($validExistencia->id)
                            TransProductoInventario::saveTransReparto($sucursal_recibe_id,$validExistencia->id,$validExistencia->producto_id,$validExistencia->cantidad,TransProductoInventario::TIPO_ENTRADA);

                        if (isset($InvProducto->id)) {

                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                                $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                                if (isset($InvProducto2->id)) {
                                    // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                        $InvProducto2->cantidad = floatval($InvProducto2->cantidad) + ( floatval($v_detalle["cantidad"]) * $Producto->sub_cantidad_equivalente);
                                        $InvProducto2->save();


                                }else{
                                    // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO

                                        $InvProductoSucursal  =  new InvProductoSucursal();
                                        $InvProductoSucursal->sucursal_id   = $sucursal;
                                        $InvProductoSucursal->producto_id   = $v_detalle["producto_id"];
                                        $InvProductoSucursal->cantidad      = $v_detalle["cantidad"];
                                        $InvProductoSucursal->save();

                                        // SE REGISTRA EVENTO DEL MOVIMIENTO

                                }

                            }else{

                                // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                                $InvProducto->cantidad = floatval($InvProducto->cantidad) + floatval($v_detalle["cantidad"]);
                                $InvProducto->save();
                                // SE REGISTRA EVENTO DEL MOVIMIENTO

                            }

                        }else{

                            // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO

                                $InvProductoSucursal  =  new InvProductoSucursal();
                                $InvProductoSucursal->sucursal_id   = $sucursal;
                                $InvProductoSucursal->producto_id   = $v_detalle["producto_id"];
                                $InvProductoSucursal->cantidad      = $v_detalle["cantidad"];
                                $InvProductoSucursal->save();
                            // SE REGISTRA EVENTO DEL MOVIMIENTO

                            // SE REGISTRA EVENTO DEL MOVIMIENTO

                        }

                        if ($v_detalle["tipo"] == 10 ) {
                            $venta = Venta::findOne($v_detalle["venta_id"]);
                            $venta->status = Venta::STATUS_PROCESO;
                            $venta->update();
                        }



                        $inventario_entrada_transaction->commit();
                    }catch(Exception $e)
                    {
                        $inventario_entrada_transaction->rollBack();
                        return false;
                    }


                }
                /*******************************************************************************************/


                /*******************************************************************************************/
                // REALIZAR LA OPERACION DE TRASPASO Y QUITA EL PRODUCTO DEL INVENTARIO
                /*******************************************************************************************/

                foreach ($movimiento_detalle as $v_detalle_key => $v_detalle) {

                    $Producto = Producto::findOne($v_detalle["producto_id"]);
                    $sucursal = $sucursal_envia_id;

                    if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                    else
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $v_detalle["producto_id"] ] ] )->one();

                    $inventario_salida_transaction = Yii::$app->db->beginTransaction();

                    try
                    {

                        if (isset($InvProducto->id)) {

                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                                $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                                if (isset($InvProducto2->id)) {
                                    // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                        $InvProducto2->cantidad = floatval($InvProducto2->cantidad) - ( floatval($v_detalle["cantidad"]) * $Producto->sub_cantidad_equivalente);
                                        $InvProducto2->save();
                                }else{
                                    // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO
                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $sucursal;
                                    $InvProductoSucursal->producto_id   = $v_detalle["producto_id"];
                                    $InvProductoSucursal->cantidad      = $v_detalle["cantidad"] * -1;
                                    $InvProductoSucursal->save();
                                }

                            }else{

                                // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                                $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($v_detalle["cantidad"]);
                                $InvProducto->save();
                            }
                        }else{

                            // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                            $InvProductoSucursal  =  new InvProductoSucursal();
                            $InvProductoSucursal->sucursal_id   = $sucursal;
                            $InvProductoSucursal->producto_id   = $v_detalle["producto_id"];
                            $InvProductoSucursal->cantidad      = $v_detalle["cantidad"] * -1;
                            $InvProductoSucursal->save();
                        }

                        TransProductoInventario::saveTransOperacion($sucursal_recibe_id,null,$v_detalle["producto_id"],$v_detalle["cantidad"],TransProductoInventario::TIPO_SALIDA);

                        $inventario_salida_transaction->commit();

                    }catch(Exception $e)
                    {
                        $inventario_salida_transaction->rollBack();
                        return false;
                    }
                }
                /******************************************************************************************/

            $transaction->commit();
            return true;
        }
        catch(Exception $e)
        {
            $transaction->rollBack();
            return false;
        }

    }

    public static function setLiquidacion($reparto_id){
        $transReparto   = Yii::$app->db->beginTransaction();

        try
        {
            /* GET VENTAS TEMP **/

            $VentaTemp = TempVentaRuta::find()->andWhere(["and",
                [ "=", "reparto_id", $reparto_id ],
                [ "=", "is_apply", TempVentaRuta::IS_APPLY_OFF ]
            ])->all();

            foreach ($VentaTemp as $key => $item_venta) {
                $venta              = new Venta();
                $venta->cliente_id  = $item_venta->cliente_id;
                $venta->sucursal_id = $item_venta->sucursal_id;
                $venta->total       = $item_venta->total;
                $venta->reparto_id  = $reparto_id;
                $venta->tipo        = $item_venta->tipo;
                $venta->is_especial = Venta::VENTA_GENERAL;
                $venta->status      = $item_venta->status;
                $venta->is_tpv_ruta = Venta::IS_TPV_RUTA_ON;
                $venta->created_by   = $item_venta->created_by;


                if ($venta->save()) {
                    $item_venta->is_apply = TempVentaRuta::IS_APPLY_ON;
                    $item_venta->venta_id = $venta->id;
                    $item_venta->update();

                    foreach ($item_venta->tempVentaRutaDetalle as $key => $v_detalle) {
                        $VentaDetalle               = new VentaDetalle();
                        $VentaDetalle->venta_id     = $venta->id;
                        $VentaDetalle->producto_id  = $v_detalle->producto_id;
                        $VentaDetalle->cantidad     = $v_detalle->cantidad;
                        $VentaDetalle->precio_venta = $v_detalle->precio_venta;
                        $VentaDetalle->created_by   = $v_detalle->created_by;
                        if ($VentaDetalle->save()) {
                            $v_detalle->is_apply = TempVentaRutaDetalle::IS_APPLY_ON;
                            $v_detalle->save();
                        }
                    }
                }
            }


            /* GET COBROS TEMP **/
            $TempCobroRutaVenta = TempCobroRutaVenta::find()->andWhere(["and",
                [ "=", "operacion_reparto_id", $reparto_id ],
                [ "=", "is_apply", TempVentaRuta::IS_APPLY_OFF ]
            ])->all();


            foreach ($TempCobroRutaVenta as $key => $item_cobro) {
                if ($item_cobro->tipo == CobroVenta::TIPO_VENTA) {
                    $tempVentaRuta              = TempVentaRuta::find()->andWhere(["id" => $item_cobro->temp_venta_ruta_id])->one();
                    if (isset($tempVentaRuta->venta_id)) {
                        $CobroVenta                 = new CobroVenta();
                        $CobroVenta->venta_id       = $tempVentaRuta->venta_id;
                        $CobroVenta->tipo           = $item_cobro->tipo;
                        $CobroVenta->tipo_cobro_pago= $item_cobro->tipo_cobro_pago;
                        $CobroVenta->metodo_pago    = $item_cobro->metodo_pago;
                        $CobroVenta->cantidad       = $item_cobro->cantidad;
                        $CobroVenta->nota           = $item_cobro->nota;
                        $CobroVenta->created_by     = $item_cobro->created_by;

                        if ($CobroVenta->metodo_pago == CobroVenta::COBRO_CREDITO ) {
                            $CobroVenta->fecha_credito = $item_cobro->fecha_credito;
                            $Credito = new  Credito();
                            $Credito->venta_id      = $tempVentaRuta->venta_id;
                            $Credito->monto         = $item_cobro->cantidad;
                            $Credito->fecha_credito = $item_cobro->fecha_credito;
                            $Credito->tipo          = CobroVenta::PERTENECE_COBRO;
                            $Credito->created_by    = $item_cobro->created_by;
                            $Credito->save();
                        }
                        if ($CobroVenta->save()) {
                            $item_cobro->is_apply = TempCobroRutaVenta::IS_APPLY_ON;
                            $item_cobro->update();
                        }
                    }
                }
            }


            /* GET COBROS PARCILES TEMP **/
            $TempVentaTokenPay = TempVentaTokenPay::find()->andWhere(["and",
                [ "=", "operacion_reparto_id", $reparto_id ],
                [ "=", "is_apply", TempVentaTokenPay::IS_APPLY_OFF ]
            ])->all();

            foreach ($TempVentaTokenPay as $key => $item_token) {
                $venta_id = $item_token->tipo == TempVentaTokenPay::TIPO_VENTA_CENTRAL ? $item_token->venta_id : null;

                if ($item_token->tipo == TempVentaTokenPay::TIPO_VENTA_TEMP) {
                    $tempVentaRuta  = TempVentaRuta::find()->andWhere(["id" => $item_token->temp_venta_ruta_id])->one();
                    $venta_id       = isset($tempVentaRuta->venta_id) ? $tempVentaRuta->venta_id : null;
                }

                if ($venta_id) {

                    $VentaTokenPay                      = new VentaTokenPay();
                    $VentaTokenPay->venta_id            = $venta_id;
                    $VentaTokenPay->token_pay           = $item_token->token_pay;
                    $VentaTokenPay->created_by          = $item_token->created_by;
                    if($VentaTokenPay->save()){
                        $item_token->is_apply = TempVentaTokenPay::IS_APPLY_ON;
                        $item_token->update();

                        $TempCobroRutaVenta = TempCobroRutaVenta::find()->andWhere(["and",
                            [ "=", "trans_token_venta", $VentaTokenPay->token_pay ],
                            [ "=", "is_apply", TempVentaRuta::IS_APPLY_OFF ]
                        ])->all();

                        //* CARGAMOS TODOS LOS COBROS RELACIONADOS CON ESTA VENTA */
                        foreach ($TempCobroRutaVenta as $key => $item_temp_cobro) {
                            //if (isset($TempCobroRutaVenta->id)) {
                                $CobroVenta                     = new CobroVenta();
                                $CobroVenta->trans_token_venta  = $item_temp_cobro->trans_token_venta;
                                $CobroVenta->tipo               = $item_temp_cobro->tipo;
                                $CobroVenta->tipo_cobro_pago    = $item_temp_cobro->tipo_cobro_pago;
                                $CobroVenta->metodo_pago        = $item_temp_cobro->metodo_pago;
                                $CobroVenta->cantidad           = $item_temp_cobro->cantidad;
                                $CobroVenta->nota               = $item_temp_cobro->nota;
                                $CobroVenta->created_by         = $item_temp_cobro->created_by;

                                if ($CobroVenta->metodo_pago == CobroVenta::COBRO_CREDITO ) {
                                    $CobroVenta->fecha_credito = $item_temp_cobro->fecha_credito;
                                    $Credito = new  Credito();
                                    $Credito->venta_id      = $venta_id;
                                    $Credito->monto         = $item_temp_cobro->cantidad;
                                    $Credito->fecha_credito = $item_temp_cobro->fecha_credito;
                                    $Credito->tipo          = CobroVenta::PERTENECE_COBRO;
                                    $Credito->created_by    = $item_temp_cobro->created_by;
                                    $Credito->save();
                                }
                                if ($CobroVenta->save()) {
                                    $item_temp_cobro->is_apply = TempCobroRutaVenta::IS_APPLY_ON;
                                    $item_temp_cobro->update();
                                }
                            //}
                        }
                    }

                }
            }


           $transReparto->commit();
           return true;

        }
        catch(Exception $e){

            $transReparto->rollBack();
            return false;
        }
    }


    /**
     * Gets query for [[RepartoDetalles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRepartoDetalles()
    {
        return $this->hasMany(RepartoDetalle::className(), ['reparto_id' => 'id']);
    }

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: $this->created_by;

            }else{

                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: $this->updated_by;
            }

            return true;

        } else
            return false;
    }
}
