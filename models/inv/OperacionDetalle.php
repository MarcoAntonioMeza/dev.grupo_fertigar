<?php
namespace app\models\inv;

use Yii;
use app\models\producto\Producto;
use app\models\compra\Compra;
use app\models\venta\VentaDetalle;
use app\models\trans\TransProductoInventario;
/**
 * This is the model class for table "operacion_detalle".
 *
 * @property int $id ID
 * @property int $operacion_id Operación ID
 * @property int $producto_id Producto ID
 * @property int $cantidad Cantidad
 *
 * @property Operacion $operacion
 * @property Producto $producto
 */
class OperacionDetalle extends \yii\db\ActiveRecord
{

    public $operacion_detalle_array;

    const REQUIRED_OLD         = 10;
    const REQUIRED_NEW         = 20;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'operacion_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['operacion_id', 'producto_id', 'cantidad'], 'required'],
            [['operacion_id', 'producto_id','new_required','venta_detalle_id'], 'integer'],
            [['costo'], 'number'],
            [['cantidad'], 'number'],
            [['operacion_detalle_array'], 'safe'],
            [['operacion_id'], 'exist', 'skipOnError' => true, 'targetClass' => Operacion::className(), 'targetAttribute' => ['operacion_id' => 'id']],
            [['producto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Producto::className(), 'targetAttribute' => ['producto_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'operacion_id' => 'Operacion ID',
            'producto_id' => 'Producto ID',
            'cantidad' => 'Cantidad',
        ];
    }

    /**
     * Gets query for [[Operacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOperacion()
    {
        return $this->hasOne(Operacion::className(), ['id' => 'operacion_id']);
    }

    public function getVentaDetalle()
    {
        return $this->hasOne(VentaDetalle::className(), ['id' => 'venta_detalle_id']);
    }

    /**
     * Gets query for [[Producto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducto()
    {
        return $this->hasOne(Producto::className(), ['id' => 'producto_id']);
    }

    public static function getOperacionSucursal($sucursal_id,$producto_id)
    {
        return OperacionDetalle::find()->innerJoin("operacion","operacion.id = operacion_detalle.operacion_id")->andWhere([ "and",
            ["=","operacion.status", Operacion::STATUS_ACTIVE],
            ["=","operacion.almacen_sucursal_id",$sucursal_id],
            ["=","operacion_detalle.producto_id",$producto_id],
        ])->limit(50)->orderBy("operacion.created_at desc")->all();
    }

    public function saveOperacionDetalle($operacion_id,$almacen_sucursal_id,$tipo,$compra_id = false,$motivo = false)
    {
        $operacion_detalles_array = json_decode($this->operacion_detalle_array);
        if ($operacion_detalles_array) {
            foreach ($operacion_detalles_array as $key => $opera_detalle) {
                $OperacionDetalle = new OperacionDetalle();
                $OperacionDetalle->operacion_id = $operacion_id;
                $OperacionDetalle->producto_id  = $opera_detalle->producto_id;
                $OperacionDetalle->cantidad     = floatval($opera_detalle->cantidad);
                $OperacionDetalle->costo        = $opera_detalle->costo;


                if ($motivo == Operacion::ENTRADA_MERCANCIA_NUEVA )
                    $OperacionDetalle->new_required = Compra::validItemCompra($compra_id,$OperacionDetalle->producto_id,$OperacionDetalle->cantidad) ?  self::REQUIRED_OLD  : self::REQUIRED_NEW;
                else
                    $OperacionDetalle->new_required =  self::REQUIRED_OLD;


                $OperacionDetalle->save();

                $Producto = Producto::findOne($OperacionDetalle->producto_id);

                if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $almacen_sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                else
                    $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $almacen_sucursal_id ], [ "=", "producto_id", $OperacionDetalle->producto_id ] ] )->one();

                if ($tipo == Operacion::TIPO_ENTRADA || $tipo == Operacion::TIPO_DEVOLUCION ) {

                    if (isset($InvProducto->id)) {

                        if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                            $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $almacen_sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                            if (isset($InvProducto2->id)) {
                                // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                $InvProducto2->cantidad = floatval($InvProducto2->cantidad) + ( floatval( $OperacionDetalle->cantidad ) * $Producto->sub_cantidad_equivalente) ;
                                $InvProducto2->save();

                            }else{
                                // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO
                                $InvProductoSucursal  =  new InvProductoSucursal();
                                $InvProductoSucursal->sucursal_id   = $almacen_sucursal_id;
                                $InvProductoSucursal->producto_id   = $OperacionDetalle->producto_id;
                                $InvProductoSucursal->cantidad      = $OperacionDetalle->cantidad;
                                $InvProductoSucursal->save();
                            }

                        }else{
                            // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                            $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval($OperacionDetalle->cantidad);
                            $InvProducto->save();
                        }

                    }else{
                        // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                        $InvProductoSucursal  =  new InvProductoSucursal();
                        $InvProductoSucursal->sucursal_id   = $almacen_sucursal_id;
                        $InvProductoSucursal->producto_id   = $OperacionDetalle->producto_id;
                        $InvProductoSucursal->cantidad      = $OperacionDetalle->cantidad;
                        $InvProductoSucursal->save();
                    }

                    TransProductoInventario::saveTransOperacion($almacen_sucursal_id,$OperacionDetalle->id,$OperacionDetalle->producto_id,$OperacionDetalle->cantidad,TransProductoInventario::TIPO_ENTRADA);

                }

                if ($tipo == Operacion::TIPO_SALIDA ) {

                    if (isset($InvProducto->id)) {

                        if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                            $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $almacen_sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                            if (isset($InvProducto2->id)) {
                                // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                $InvProducto2->cantidad = floatval($InvProducto2->cantidad) - ( floatval($OperacionDetalle->cantidad) * $Producto->sub_cantidad_equivalente) ;
                                $InvProducto2->save();

                            }else{
                                // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO

                                $InvProductoSucursal  =  new InvProductoSucursal();
                                $InvProductoSucursal->sucursal_id   = $almacen_sucursal_id;
                                $InvProductoSucursal->producto_id   = $OperacionDetalle->producto_id;
                                $InvProductoSucursal->cantidad      = $OperacionDetalle->cantidad * -1;
                                $InvProductoSucursal->save();
                            }

                        }else{
                            $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($OperacionDetalle->cantidad);
                            $InvProducto->save();
                        }
                    }else{

                        $InvProductoSucursal  =  new InvProductoSucursal();
                        $InvProductoSucursal->sucursal_id   = $almacen_sucursal_id;
                        $InvProductoSucursal->producto_id   =   $Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ? $Producto->sub_producto_id  : $OperacionDetalle->producto_id ;
                        $InvProductoSucursal->cantidad      = $Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ?  ( ($Producto->sub_cantidad_equivalente  * intval($OperacionDetalle->cantidad)) * -1 ) : ( $OperacionDetalle->cantidad * -1 );
                        $InvProductoSucursal->save();
                    }

                    TransProductoInventario::saveTransOperacion($almacen_sucursal_id,$OperacionDetalle->id,$OperacionDetalle->producto_id,$OperacionDetalle->cantidad,TransProductoInventario::TIPO_SALIDA);

                }
            }
        }
        return true;
    }



    public function saveDevolucionDetalle($operacion_id,$almacen_sucursal_id,$motivo = false)
    {
        $operacion_detalles_array = json_decode($this->operacion_detalle_array);
        if ($operacion_detalles_array) {
            foreach ($operacion_detalles_array as $key => $opera_detalle) {
                $OperacionDetalle = new OperacionDetalle();
                $OperacionDetalle->operacion_id = $operacion_id;
                $OperacionDetalle->producto_id  = $opera_detalle->producto_id;
                $OperacionDetalle->cantidad     = floatval($opera_detalle->cantidad_devolucion);
                $OperacionDetalle->costo        = $opera_detalle->costo;
                $OperacionDetalle->save();

                $Producto = Producto::findOne($OperacionDetalle->producto_id);

                if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $almacen_sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                else
                    $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $almacen_sucursal_id ], [ "=", "producto_id", $OperacionDetalle->producto_id ] ] )->one();


                TransProductoInventario::saveTransOperacion($almacen_sucursal_id,$OperacionDetalle->id,$OperacionDetalle->producto_id,$OperacionDetalle->cantidad,TransProductoInventario::TIPO_ENTRADA);

                if (isset($InvProducto->id)) {

                    if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                        $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $almacen_sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                        if (isset($InvProducto2->id)) {
                            // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                            $InvProducto2->cantidad = floatval($InvProducto2->cantidad) + ( floatval( $OperacionDetalle->cantidad ) * $Producto->sub_cantidad_equivalente) ;
                            $InvProducto2->save();

                        }else{
                            // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO
                            $InvProductoSucursal  =  new InvProductoSucursal();
                            $InvProductoSucursal->sucursal_id   = $almacen_sucursal_id;
                            $InvProductoSucursal->producto_id   = $OperacionDetalle->producto_id;
                            $InvProductoSucursal->cantidad      = $OperacionDetalle->cantidad;
                            $InvProductoSucursal->save();
                        }

                    }else{
                        // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                        $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval($OperacionDetalle->cantidad);
                        $InvProducto->save();
                    }

                }else{
                    // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                    $InvProductoSucursal  =  new InvProductoSucursal();
                    $InvProductoSucursal->sucursal_id   = $almacen_sucursal_id;
                    $InvProductoSucursal->producto_id   = $OperacionDetalle->producto_id;
                    $InvProductoSucursal->cantidad      = $OperacionDetalle->cantidad;
                    $InvProductoSucursal->save();
                }

            }
        }
        return true;
    }


    public static function cancelOperacion($operacion_id)
    {
        $operacion = Operacion::findOne($operacion_id);


        $almacen_sucursal_id = $operacion->almacen_sucursal_id;

        foreach ($operacion->operacionDetalles as $key => $opera_detalle) {


            $Producto = Producto::findOne($opera_detalle->producto_id);

            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                    $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $almacen_sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
            else
                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $almacen_sucursal_id ], [ "=", "producto_id", $opera_detalle->producto_id ] ] )->one();


            if (isset($InvProducto->id)) {

                if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                    $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $almacen_sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                    if (isset($InvProducto2->id)) {
                        // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                        $InvProducto2->cantidad = floatval($InvProducto2->cantidad) + ( floatval( $opera_detalle->cantidad ) * $Producto->sub_cantidad_equivalente) ;
                        $InvProducto2->save();

                    }else{
                        // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO
                        $InvProductoSucursal  =  new InvProductoSucursal();
                        $InvProductoSucursal->sucursal_id   = $almacen_sucursal_id;
                        $InvProductoSucursal->producto_id   = $opera_detalle->producto_id;
                        $InvProductoSucursal->cantidad      = $opera_detalle->cantidad;
                        $InvProductoSucursal->save();
                    }

                }else{
                    // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                    $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval($opera_detalle->cantidad);
                    $InvProducto->save();
                }

            }else{
                // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                $InvProductoSucursal  =  new InvProductoSucursal();
                $InvProductoSucursal->sucursal_id   = $almacen_sucursal_id;
                $InvProductoSucursal->producto_id   = $opera_detalle->producto_id;
                $InvProductoSucursal->cantidad      = $opera_detalle->cantidad;
                $InvProductoSucursal->save();
            }

            TransProductoInventario::saveTransOperacion($almacen_sucursal_id,$opera_detalle->id,$opera_detalle->producto_id,$opera_detalle->cantidad,TransProductoInventario::TIPO_ENTRADA);
        }
        return true;
    }
}
