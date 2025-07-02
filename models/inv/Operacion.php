<?php
namespace app\models\inv;

use Yii;
use yii\db\Query;
use app\models\user\User;
use app\models\sucursal\Sucursal;
use app\models\cobro\CobroVenta;
use app\models\venta\Venta;
use app\models\producto\Producto;
use app\models\reparto\Reparto;
use app\models\inv\InvProductoSucursal;
use app\models\trans\TransProductoInventario;


/**
 * This is the model class for table "operacion".
 *
 * @property int $id ID
 * @property int $tipo Tipo
 * @property int $almacen_sucursal_id Almacen
 * @property int $created_by Creado por
 * @property int $created_at Creado
 *
 * @property Sucursal $almacenSucursal
 * @property User $createdBy
 * @property OperacionDetalle[] $operacionDetalles
 */
class Operacion extends \yii\db\ActiveRecord
{

    const TIPO_ENTRADA      = 10;
    const TIPO_SALIDA       = 20;
    const TIPO_DEVOLUCION   = 30;

    public static $tipoList = [
        self::TIPO_ENTRADA   => 'ENTRADA',
        self::TIPO_SALIDA  => 'SALIDA',
        self::TIPO_DEVOLUCION  => 'DEVOLUCION',
    ];

    const STATUS_ACTIVE = 10;
    const STATUS_PROCESO = 20;
    const STATUS_CANCEL = 30;

    public static $statusList = [
        self::STATUS_ACTIVE   => 'TERMINADO',
        self::STATUS_PROCESO  => 'PROCESO',
        self::STATUS_CANCEL   => 'CANCELADO',
    ];

    public static $statusAlertList = [
        self::STATUS_ACTIVE   => 'primary',
        self::STATUS_PROCESO  => 'warning',
        self::STATUS_CANCEL   => 'danger',
    ];


    const ENTRADA_MERCANCIA_NUEVA   = 10;
    const ENTRADA_TRASPASO_RECOLECCION = 15;
    const ENTRADA_TRASPASO          = 20;
    const ENTRADA_TRASPASO_UNIDAD   = 25;
    const SALIDA_TRASPASO           = 30;
    const SALIDA_TRASPASO_UNIDAD    = 35;
    const SALIDA_CADUCIDAD          = 40;
    const SALIDA_TRASPASO_RECOLECCION = 45;

    const ENTRADA_DEVOLUCION        = 50;
    const ENTRADA_RUTA_AJUSTE       = 55;
    const SALIDA_RUTA_AJUSTE        = 60;

    public static $operacionList = [
        self::ENTRADA_MERCANCIA_NUEVA       => 'NUEVA MERCANCIA',
        self::ENTRADA_TRASPASO_RECOLECCION  => 'ENTRADA - RECOLECCION POR UNIDADES',
        self::ENTRADA_TRASPASO              => 'ABASTECIMIENTO',
        self::ENTRADA_TRASPASO_UNIDAD       => 'TRASPASO UNIDAD',
        self::SALIDA_TRASPASO               => 'TRASPASO - SURTIR ',
        self::SALIDA_TRASPASO_UNIDAD        => 'TRASPASO UNIDAD',
        self::SALIDA_CADUCIDAD              => 'CADUCIDAD',
        self::ENTRADA_DEVOLUCION            => 'DEVOLUCION',
        self::SALIDA_TRASPASO_RECOLECCION   => 'SALIDA - TRASPASO RECOLECCION',
        self::SALIDA_RUTA_AJUSTE            => 'SALIDA - RUTA AJUSTE',
        self::ENTRADA_RUTA_AJUSTE           => 'ENTRADA - RUTA AJUSTE',

    ];

    public $operacion_detalle;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'operacion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tipo', 'almacen_sucursal_id'], 'required'],
            [['tipo', 'almacen_sucursal_id','venta_id','compra_id', 'created_by','operacion_child_id', 'sucursal_recibe_id','created_at','updated_by','updated_at','motivo','status','devolucion_motivo_id','ruta_recoleccion_id'], 'integer'],
            [['almacen_sucursal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sucursal::className(), 'targetAttribute' => ['almacen_sucursal_id' => 'id']],
            [['nota'], 'string'],
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
            'tipo' => 'Operación',
            'venta_id' => 'Venta',
            'motivo' => 'Tipo',
            'compra_id' => 'Compra',
            'devolucion_motivo_id' => 'Motivo',
            'almacenSucursal.nombre' => 'Almacen  / Sucursal',
            'almacen_sucursal_id' => 'Almacen  / Sucursal',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[AlmacenSucursal]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAlmacenSucursal()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'almacen_sucursal_id']);
    }

    public function getSucursalRecibe()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_recibe_id']);
    }

    public function getOperacionChild(){
        return $this->hasOne(Operacion::className(), ['id' => 'operacion_child_id']);
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


    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public function getVenta()
    {
        return $this->hasOne(Venta::className(), ['id' => 'venta_id']);
    }

    public function getReparto()
    {
        return $this->hasOne(Reparto::className(), ['id' => 'reparto_id']);
    }

    /**
     * Gets query for [[OperacionDetalles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOperacionDetalles()
    {
        return $this->hasMany(OperacionDetalle::className(), ['operacion_id' => 'id']);
    }

    public static function getOperacionDetalleGroup($operacion_id)
    {
        return  (new Query())
        ->select([
            'operacion_detalle.id',
            'operacion_detalle.producto_id',
            'producto.clave as producto_clave',
            'producto.tipo_medida as producto_tipo_medida',
            'producto.nombre as producto',
            'sum(operacion_detalle.cantidad) as cantidad',
        ])
        ->from("operacion_detalle")
        ->innerJoin("producto","operacion_detalle.producto_id = producto.id")
        ->andWhere(["and",
            ["=", "operacion_detalle.operacion_id", $operacion_id ],
        ])
        ->groupBy("operacion_detalle.producto_id")
        ->all();

    }

    public function getTotalOperacion()
    {
        return OperacionDetalle::find()->andWhere(['operacion_id' => $this->id ])->sum('costo');
    }

    public function getTotalUnidades()
    {
        return OperacionDetalle::find()->andWhere(['operacion_id' => $this->id ])->sum('cantidad');
    }

    public static function searchOrigenTraspaso($operacion_id)
    {
        $operacion = self::findOne(["operacion_child_id" => $operacion_id]);

        return isset($operacion->id) ? $operacion->almacenSucursal->nombre : '';
    }

    public static function getOperacionDetalleList($operacion_id)
    {
        $Operacion = self::findOne($operacion_id);

        $operacionDetalleArray = [];

        foreach ($Operacion->operacionDetalles as $key => $detalleItem) {
            array_push($operacionDetalleArray, [
                "producto"  => $detalleItem->producto->nombre,
                "cantidad"  => $detalleItem->cantidad,
                "costo"     => $detalleItem->costo
            ]);
        }
        return $operacionDetalleArray;
    }

    public static function getReembolsoVenta($venta_id, $metodo_pago_array)
    {
        $metodo_pago_array = json_decode($metodo_pago_array);
        if ($metodo_pago_array) {
            foreach ($metodo_pago_array as $key => $pagoItem) {
                $CobroVenta  =  new CobroVenta();
                $CobroVenta->venta_id       = $venta_id;
                $CobroVenta->tipo           = CobroVenta::TIPO_REEMBOLSO;
                $CobroVenta->tipo_cobro_pago= CobroVenta::PERTENECE_REEMBOLSO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_EFECTIVO;
                $CobroVenta->cantidad       = $pagoItem->cantidad;
                $CobroVenta->save();
            }
        }
        return true;
    }

    public static function validateOperacionApp($mv_detalle,$inventario_sucursal_id)
    {

        $response       = [];
        $response_group = [];

        foreach ($mv_detalle as $key => $item_producto) {

            /* SOLO VALIDAMOS LA EXISTENCIA DEL PRODUCTO DE TIENDA*/
            if ($item_producto["sucursal_id"] == $inventario_sucursal_id) {
                $is_add = true;
                foreach ($response_group as $key_group => $item_group) {
                    if ($item_group["producto_id"] == $item_producto["producto_id"] ) {
                        $is_add = false;
                        $response_group[$key_group]["cantidad"] = floatval($response_group[$key_group]["cantidad"])  + floatval($item_producto["cantidad"]);
                    }
                }

                if ($is_add)
                    array_push($response_group, $item_producto);
            }
        }

        foreach ($response_group as $key => $item_detalle) {

            $is_add = false;

            $Producto = Producto::findOne($item_detalle["producto_id"]);

            $InvProducto = null;


            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $inventario_sucursal_id ], [ "=", "producto_id", $item_detalle["producto_id"] ] ] )->one();

            if (isset($InvProducto->id)) {
                // CONSULTAMOS CUANTO PRODUCTO SE ENCUENTRA COMPROMETIDO
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
                        "code"      =>  10,
                        "producto"  => $Producto->nombre,
                        "inv"       => $InvProducto,
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


    public static function validateOperacionPuntoVenta($mv_detalle)
    {

        $response       = [];
        $response_group = [];

        foreach ($mv_detalle as $key => $item_producto) {

                $is_add = true;
                foreach ($response_group as $key_group => $item_group) {
                    if ($item_group["producto_id"] == $item_producto["producto_id"] && $item_group["sucursal_id"] == $item_producto["sucursal_id"] ) {
                        $is_add = false;
                        $response_group[$key_group]["cantidad"] = floatval($response_group[$key_group]["cantidad"])  + floatval($item_producto["cantidad"]);
                    }
                }

                if ($is_add)
                    array_push($response_group, $item_producto);

        }

        foreach ($response_group as $key => $item_detalle) {

            $is_add = false;

            $Producto = Producto::findOne($item_detalle["producto_id"]);

            $InvProducto = null;


            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $item_detalle["sucursal_id"] ], [ "=", "producto_id", $item_detalle["producto_id"] ] ] )->one();

            if (isset($InvProducto->id)) {
                // CONSULTAMOS CUANTO PRODUCTO SE ENCUENTRA COMPROMETIDO
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
                        "code"      =>  10,
                        "producto"  => $Producto->nombre,
                        "inv"       => $InvProducto,
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

    public static function returnProductoFaltante($operacion_surtir_id, $producto_entrada, $created_by)
    {

        $Operacion = Operacion::findOne($operacion_surtir_id);

        foreach ($Operacion->operacionDetalles as $key => $item_detail) {

            $is_search = false;

            foreach ($producto_entrada as $key => $item_producto) {
                if (intval($item_detail->producto_id) == intval($item_producto["producto_id"]) ) {

                    $is_search = true;

                    /*EL INGRESO FUE MENOR*/
                    if (floatval($item_detail->cantidad) > floatval($item_producto["cantidad"]) ) {

                        $cantidadDiferencia = 0;
                        $cantidadDiferencia = $item_detail->cantidad - floatval($item_producto["cantidad"]);

                        $sucursal   = $Operacion->almacen_sucursal_id;

                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $item_producto["producto_id"] ] ] )->one();

                        if (isset($InvProducto->id)) {
                            $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval($cantidadDiferencia);
                            $InvProducto->save();
                        }

                        TransProductoInventario::saveTransOperacion($sucursal,$item_detail->id,$item_producto['producto_id'],$cantidadDiferencia,TransProductoInventario::TIPO_ENTRADA, $created_by);
                    }
                }
            }

            if (!$is_search) {
                $sucursal   = $Operacion->almacen_sucursal_id;

                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $item_detail->producto_id ] ] )->one();

                if (isset($InvProducto->id)) {
                    $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval($item_detail->cantidad);
                    $InvProducto->save();
                }

                TransProductoInventario::saveTransOperacion($sucursal,$item_detail->id,$item_detail->producto_id,$item_detail->cantidad,TransProductoInventario::TIPO_ENTRADA, $created_by);
            }
        }
    }


    public static function findChangeOperacion($enviaId, $recibeID, $producto_entrada, $created_by)
    {
        $Operacion = Operacion::findOne($enviaId);
        foreach ($Operacion->operacionDetalles as $key => $item_detail) {
            
            foreach ($producto_entrada as $key => $item_producto) {
                
                if (intval($item_detail->producto_id) == intval($item_producto["producto_id"])  && $item_detail->cantidad != $item_producto["cantidad"] ) {                    
                    TraspasoOperacion::saveIncidenciaOperacionTranspaso($enviaId, $item_detail->id, $recibeID, $created_by, $item_producto["producto_id"], $item_detail->cantidad, $item_producto["cantidad"] );
                }
            }
        }
    }

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : $this->created_by;

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
