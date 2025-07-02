<?php
namespace app\models\venta;

use Yii;
use app\models\user\User;
use app\models\producto\Producto;
use app\models\inv\InvProductoSucursal;
use app\models\esys\EsysCambiosLog;
use app\models\reparto\RepartoDetalle;
use app\models\trans\TransProductoInventario;
use app\models\temp\TempVentaRutaDetalle;
use app\models\temp\TempVentaRuta;
use app\models\temp\TempVentaTokenPay;

/**
 * This is the model class for table "venta_detalle".
 *
 * @property int $id ID
 * @property int $producto_id Producto
 * @property int $cantidad Cantidad
 * @property float|null $precio_venta Precio de venta
 * @property int $created_at Creado
 * @property int|null $created_by Creado por
 * @property int|null $updated_at Modificado
 * @property int|null $updated_by Modificado por
 *
 * @property User $createdBy
 * @property Producto $producto
 * @property User $updatedBy
 */
class VentaDetalle extends \yii\db\ActiveRecord
{

    const CONVERSION_ON = 10;

    const STATUS_ACTIVE   = 10;
    const STATUS_CANCEL   = 20;

    const APPLY_BODEGA_ON   = 10;
    const APPLY_BODEGA_OFF  = 20;

    public $venta_detalle_array;
    public $CambiosLog;

    const ENTREGA_REPARTO_ON      = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'venta_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['producto_id', 'cantidad', 'venta_id'], 'required'],
            [['venta_detalle_array'], 'safe'],
            [['producto_id', 'sucursal_id', 'created_at', 'created_by', 'updated_at', 'updated_by','venta_id','is_reparto_entrega','is_conversion','status','apply_bodega'], 'integer'],
            [['precio_venta'], 'number'],
            [['cantidad','conversion_cantidad'], 'number'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['producto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Producto::className(), 'targetAttribute' => ['producto_id' => 'id']],
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
            'producto_id' => 'Producto ID',
            'cantidad' => 'Cantidad',
            'precio_venta' => 'Precio Venta',
            'is_reparto_entrega' => 'Entrega por reparto',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
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
     * Gets query for [[Producto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducto()
    {
        return $this->hasOne(Producto::className(), ['id' => 'producto_id']);
    }

    /**
     * Gets query for [[Producto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVenta()
    {
        return $this->hasOne(Venta::className(), ['id' => 'venta_id']);
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

    public function getRepartoAdd()
    {
        return $this->hasMany(RepartoDetalle::className(), ['venta_detalle_id' => 'id']);
    }

    public static function getVentaRuta($reparto_id)
    {
        return VentaDetalle::find()
        ->innerJoin('venta','venta_detalle.venta_id = venta.id')
        ->andWhere(["and",
            ["=","venta.reparto_id", $reparto_id],
            ["=","venta.is_tpv_ruta", Venta::IS_TPV_RUTA_ON ],
        ])->all();
    }

    /*************************************************************************/
    /*               GUARDAMOS PRODUCTOS DE UNA PRE-CAPTURA
    /*************************************************************************/

    public function saveVentaDetalle($venta_id, $venta_status = false)
    {
        $venta_detalles_array = json_decode($this->venta_detalle_array);

        if ($venta_detalles_array) {

            $this->CambiosLog = new EsysCambiosLog((new Venta));

            // CARGA LA LISTA DE PRODUCTO QUE SON POR SEGUNDA VES Y QUE SERAN ELIMINADOS
            foreach ($venta_detalles_array as $key => $v_detalle_delete) {

                if ($v_detalle_delete->status == 1 && $v_detalle_delete->origen == 2 ) {
                    $VentaDetalle = VentaDetalle::findOne($v_detalle_delete->item_id);

                    $Producto = Producto::findOne($VentaDetalle->producto_id);
                    $sucursal = Venta::findOne($venta_id)->sucursal_id;

                    // ELIMINAMOS TODOS LOS REGISTROS DEL DETALLE DE LA VENTA

                    RepartoDetalle::deleteVenta($VentaDetalle->id);

                    if ($VentaDetalle->venta->status == Venta::STATUS_PROCESO) {

                        /* ENTRADA */

                        if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();

                        else
                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->sucursal_id ], [ "=", "producto_id", $VentaDetalle->producto_id ] ] )->one();

                        if (isset($InvProducto->id)) {
                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {
                                $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                                if (isset($InvProducto2->id)) {
                                    // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO
                                    $InvProducto2->cantidad = floatval($InvProducto2->cantidad) + ( floatval($VentaDetalle->cantidad) * $Producto->sub_cantidad_equivalente) ;
                                    $InvProducto2->save();

                                }else{
                                    // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO
                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $VentaDetalle->venta->sucursal_id;
                                    $InvProductoSucursal->producto_id   = $VentaDetalle->sub_producto_id;
                                    $InvProductoSucursal->cantidad      = $VentaDetalle->cantidad;
                                    $InvProductoSucursal->save();
                                }

                            }else{
                                $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval($VentaDetalle->cantidad);
                                $InvProducto->save();
                            }
                        }else{
                            // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                            $InvProductoSucursal  =  new InvProductoSucursal();
                            $InvProductoSucursal->sucursal_id   = $VentaDetalle->venta->sucursal_id;
                            $InvProductoSucursal->producto_id   = $VentaDetalle->producto_id;
                            $InvProductoSucursal->cantidad      = $VentaDetalle->cantidad;
                            $InvProductoSucursal->save();
                        }

                        TransProductoInventario::saveTransAjusteVenta($VentaDetalle->venta->sucursal_id,$venta_id,$VentaDetalle->producto_id,$VentaDetalle->cantidad,TransProductoInventario::TIPO_ENTRADA);

                        /*SALIDA*/

                        if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->ruta_sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();

                        else
                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->ruta_sucursal_id ], [ "=", "producto_id", $VentaDetalle->producto_id ] ] )->one();

                        if (isset($InvProducto->id)) {
                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {
                                $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->ruta_sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                                if (isset($InvProducto2->id)) {
                                    // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO
                                    $InvProducto2->cantidad = floatval($InvProducto2->cantidad) - ( floatval($VentaDetalle->cantidad) * $Producto->sub_cantidad_equivalente) ;
                                    $InvProducto2->save();

                                }else{
                                    // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO
                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $VentaDetalle->venta->ruta_sucursal_id;
                                    $InvProductoSucursal->producto_id   = $VentaDetalle->sub_producto_id;
                                    $InvProductoSucursal->cantidad      = (floatval($VentaDetalle->cantidad) * $Producto->sub_cantidad_equivalente) * -1;
                                    $InvProductoSucursal->save();
                                }

                            }else{
                                $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($VentaDetalle->cantidad);
                                $InvProducto->save();
                            }
                        }else{
                            // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                            $InvProductoSucursal  =  new InvProductoSucursal();
                            $InvProductoSucursal->sucursal_id   = $VentaDetalle->venta->ruta_sucursal_id;
                            $InvProductoSucursal->producto_id   = $VentaDetalle->producto_id;
                            $InvProductoSucursal->cantidad      = $VentaDetalle->cantidad * -1;
                            $InvProductoSucursal->save();
                        }

                        TransProductoInventario::saveTransAjusteVenta($VentaDetalle->venta->ruta_sucursal_id,$venta_id,$VentaDetalle->producto_id,$VentaDetalle->cantidad,TransProductoInventario::TIPO_SALIDA);

                    }

                    if ($VentaDetalle->delete()) {
                        //$InvProductoSucursal->save();
                        $this->CambiosLog->updateValue('#precaptura', 'old', '********* Se elimino el producto : #'. $v_detalle_delete->producto_nombre  . '['. $v_detalle_delete->producto_clave .'] *********');
                        $this->CambiosLog->updateValue('#precaptura', 'dirty', '');
                        $this->CambiosLog->createLog($venta_id);
                    }

                }
            }
            // CARGA LA LISTA DE PRODUCTO QUE SON POR PRIMERA VES
            foreach ($venta_detalles_array as $key => $venta_detalle) {
                if ($venta_detalle->origen  ==  1 ) {
                    $VentaDetalle = new VentaDetalle();
                    $VentaDetalle->venta_id     = $venta_id;
                    $VentaDetalle->producto_id  = $venta_detalle->producto_id;
                    $VentaDetalle->cantidad             = $venta_detalle->is_conversion == 1 ? 0 : $venta_detalle->cantidad;
                    $VentaDetalle->conversion_cantidad  = $venta_detalle->is_conversion == 1 ? $venta_detalle->cantidad : 0;
                    $VentaDetalle->precio_venta         = $venta_detalle->precio_venta;
                    $VentaDetalle->is_conversion        = $venta_detalle->is_conversion == 1 ? 10 : 20;
                    $VentaDetalle->save();


                    /*SE AGREGO OPERACION DE CARGA DE PRODUCTOS A PREVENTAS EN PROCESO DE ENTREGA*/
                    if ($VentaDetalle->venta->status == Venta::STATUS_PROCESO) {

                        $Producto = Producto::findOne($VentaDetalle->producto_id);
                        /* ENTRADA */

                        if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->ruta_sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();

                        else
                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->ruta_sucursal_id ], [ "=", "producto_id", $VentaDetalle->producto_id ] ] )->one();

                        if (isset($InvProducto->id)) {
                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {
                                $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->ruta_sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                                if (isset($InvProducto2->id)) {
                                    // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO
                                    $InvProducto2->cantidad = floatval($InvProducto2->cantidad) + ( floatval($VentaDetalle->cantidad) * $Producto->sub_cantidad_equivalente) ;
                                    $InvProducto2->save();

                                }else{
                                    // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO
                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $VentaDetalle->venta->ruta_sucursal_id;
                                    $InvProductoSucursal->producto_id   = $VentaDetalle->sub_producto_id;
                                    $InvProductoSucursal->cantidad      = $VentaDetalle->cantidad;
                                    $InvProductoSucursal->save();
                                }

                            }else{
                                $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval($VentaDetalle->cantidad);
                                $InvProducto->save();
                            }
                        }else{
                            // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                            $InvProductoSucursal  =  new InvProductoSucursal();
                            $InvProductoSucursal->sucursal_id   = $VentaDetalle->venta->ruta_sucursal_id;
                            $InvProductoSucursal->producto_id   = $VentaDetalle->producto_id;
                            $InvProductoSucursal->cantidad      = $VentaDetalle->cantidad;
                            $InvProductoSucursal->save();
                        }



                        TransProductoInventario::saveTransAjusteVenta($VentaDetalle->venta->ruta_sucursal_id,$venta_id,$VentaDetalle->producto_id,$VentaDetalle->cantidad,TransProductoInventario::TIPO_ENTRADA);

                        /*SALIDA*/

                        if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();

                        else
                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->sucursal_id ], [ "=", "producto_id", $VentaDetalle->producto_id ] ] )->one();

                        if (isset($InvProducto->id)) {
                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {
                                $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                                if (isset($InvProducto2->id)) {
                                    // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO
                                    $InvProducto2->cantidad = floatval($InvProducto2->cantidad) - ( floatval($VentaDetalle->cantidad) * $Producto->sub_cantidad_equivalente) ;
                                    $InvProducto2->save();

                                }else{
                                    // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO
                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $VentaDetalle->venta->sucursal_id;
                                    $InvProductoSucursal->producto_id   = $VentaDetalle->sub_producto_id;
                                    $InvProductoSucursal->cantidad      = (floatval($VentaDetalle->cantidad) * $Producto->sub_cantidad_equivalente) * -1;
                                    $InvProductoSucursal->save();
                                }

                            }else{
                                $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($VentaDetalle->cantidad);
                                $InvProducto->save();
                            }
                        }else{
                            // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                            $InvProductoSucursal  =  new InvProductoSucursal();
                            $InvProductoSucursal->sucursal_id   = $VentaDetalle->venta->sucursal_id;
                            $InvProductoSucursal->producto_id   = $VentaDetalle->producto_id;
                            $InvProductoSucursal->cantidad      = $VentaDetalle->cantidad * -1;
                            $InvProductoSucursal->save();
                        }
                        RepartoDetalle::registerPreventa($VentaDetalle->id,$VentaDetalle->venta->ruta_sucursal_id,$VentaDetalle->producto_id,$VentaDetalle->cantidad);
                        TransProductoInventario::saveTransAjusteVenta($VentaDetalle->venta->sucursal_id,$venta_id,$VentaDetalle->producto_id,$VentaDetalle->cantidad,TransProductoInventario::TIPO_SALIDA);
                    }

                }
            }

            // EDICION DE PRECIO, EN DADO CASO QUE CAMBIEN A PRECIO PUBLICO,MAYOREO, MENUDEO  A PRODUCTOS POR SEGUNDA VES
            foreach ($venta_detalles_array as $key => $venta_detalle) {
                if ($venta_detalle->origen  ==  2 && $venta_detalle->status == 10 ) {
                    $VentaDetalle = VentaDetalle::findOne($venta_detalle->item_id);
                    $cantidad_old = $VentaDetalle->cantidad;
                    $VentaDetalle->precio_venta = $venta_detalle->precio_venta;
                    //$VentaDetalle->cantidad     = $venta_detalle->cantidad;
                    $VentaDetalle->cantidad             = $venta_detalle->is_conversion == 1 ? 0 : $venta_detalle->cantidad;
                    $VentaDetalle->conversion_cantidad  = $venta_detalle->is_conversion == 1 ? $venta_detalle->cantidad : 0;
                    $VentaDetalle->update();


                    if ($cantidad_old !=  $venta_detalle->cantidad ) {

                        /* AGREGAMOS PRODUCTOS A RUTA */

                        if ($venta_detalle->cantidad > $cantidad_old ) {
                            if ($VentaDetalle->venta->status == Venta::STATUS_PROCESO) {
                                $Producto = Producto::findOne($VentaDetalle->producto_id);
                                /* ENTRADA */

                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->ruta_sucursal_id ], [ "=", "producto_id", $VentaDetalle->producto_id ] ] )->one();

                                if (isset($InvProducto->id)) {
                                    $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval($venta_detalle->cantidad - $cantidad_old );
                                    $InvProducto->save();
                                }else{
                                    // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $VentaDetalle->venta->ruta_sucursal_id;
                                    $InvProductoSucursal->producto_id   = $VentaDetalle->producto_id;
                                    $InvProductoSucursal->cantidad      = ($venta_detalle->cantidad - $cantidad_old );
                                    $InvProductoSucursal->save();
                                }

                                TransProductoInventario::saveTransAjusteVenta($VentaDetalle->venta->ruta_sucursal_id,$venta_id,$VentaDetalle->producto_id,$VentaDetalle->cantidad,TransProductoInventario::TIPO_ENTRADA);

                                /*SALIDA*/

                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->sucursal_id ], [ "=", "producto_id", $VentaDetalle->producto_id ] ] )->one();

                                if (isset($InvProducto->id)) {
                                    $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($venta_detalle->cantidad - $cantidad_old);
                                    $InvProducto->save();
                                }else{
                                    // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $VentaDetalle->venta->sucursal_id;
                                    $InvProductoSucursal->producto_id   = $VentaDetalle->producto_id;
                                    $InvProductoSucursal->cantidad      = ($venta_detalle->cantidad - $cantidad_old) * -1;
                                    $InvProductoSucursal->save();
                                }

                                RepartoDetalle::updatePreventa($VentaDetalle->id,$VentaDetalle->producto_id,$VentaDetalle->cantidad);

                                TransProductoInventario::saveTransAjusteVenta($VentaDetalle->venta->sucursal_id,$venta_id,$VentaDetalle->producto_id,($venta_detalle->cantidad - $cantidad_old),TransProductoInventario::TIPO_SALIDA);


                            }
                        }

                        /* QUITAMOS PRODUCTOS DE RUTA */
                        if ($cantidad_old > $venta_detalle->cantidad  ) {
                            if ($VentaDetalle->venta->status == Venta::STATUS_PROCESO) {

                                /* ENTRADA */

                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->sucursal_id ], [ "=", "producto_id", $VentaDetalle->producto_id ] ] )->one();

                                if (isset($InvProducto->id)) {
                                    $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval(( $cantidad_old - $venta_detalle->cantidad ));
                                    $InvProducto->save();

                                }else{
                                    // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $VentaDetalle->venta->sucursal_id;
                                    $InvProductoSucursal->producto_id   = $VentaDetalle->producto_id;
                                    $InvProductoSucursal->cantidad      = ($cantidad_old - $venta_detalle->cantidad);
                                    $InvProductoSucursal->save();
                                }

                                TransProductoInventario::saveTransAjusteVenta($VentaDetalle->venta->sucursal_id,$venta_id,$VentaDetalle->producto_id,($cantidad_old - $venta_detalle->cantidad),TransProductoInventario::TIPO_ENTRADA);

                                /*SALIDA*/

                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $VentaDetalle->venta->ruta_sucursal_id ], [ "=", "producto_id", $VentaDetalle->producto_id ] ] )->one();

                                if (isset($InvProducto->id)) {
                                    $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($cantidad_old - $venta_detalle->cantidad);
                                    $InvProducto->save();
                                }else{
                                    // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $VentaDetalle->venta->ruta_sucursal_id;
                                    $InvProductoSucursal->producto_id   = $VentaDetalle->producto_id;
                                    $InvProductoSucursal->cantidad      = ($cantidad_old - $venta_detalle->cantidad) * -1;
                                    $InvProductoSucursal->save();
                                }

                                RepartoDetalle::updatePreventa($VentaDetalle->id,$VentaDetalle->producto_id,$VentaDetalle->cantidad);

                                TransProductoInventario::saveTransAjusteVenta($VentaDetalle->venta->ruta_sucursal_id,$venta_id,$VentaDetalle->producto_id,($cantidad_old - $venta_detalle->cantidad),TransProductoInventario::TIPO_SALIDA);
                            }

                        }
                    }
                }
            }

        }
        return true;
    }

    /*************************************************************************/
    /*                     FINALIZA LA VENTA WEB Y AFECTAMOS INVETARIO
    /*************************************************************************/
    public function saveCerrarVenta($venta_id)
    {
        // CARGA LA LISTA DE PRODUCTO QUE SON POR PRIMERA VES

        $VentaDetalle = self::find()->andWhere([ "venta_id" => $venta_id ])->all();

        foreach ($VentaDetalle as $key => $venta_detalle) {

            $Producto = Producto::findOne($venta_detalle->producto_id);
            $sucursal = $venta_detalle->apply_bodega == VentaDetalle::APPLY_BODEGA_ON ? $venta_detalle->sucursal_id : $venta_detalle->venta->sucursal_id;


            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $venta_detalle->producto_id ] ] )->one();

            if (isset($InvProducto->id)) {
                // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($venta_detalle->cantidad);
                $InvProducto->save();
                // SE REGISTRA EVENTO DEL MOVIMIENTO
            }else{

                // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                $InvProductoSucursal  =  new InvProductoSucursal();
                $InvProductoSucursal->sucursal_id   = $sucursal;
                $InvProductoSucursal->producto_id   = $venta_detalle->producto_id;
                $InvProductoSucursal->cantidad      = $venta_detalle->cantidad * -1;
                $InvProductoSucursal->save();

            }

            TransProductoInventario::saveTransVenta($sucursal,$venta_detalle->id,$venta_detalle->producto_id,$venta_detalle->cantidad,TransProductoInventario::TIPO_SALIDA);

        }

        return true;
    }

    /*************************************************************************/
    /*                     VALIDAMOS EL DETALLE DE PRODUCTO
    /*************************************************************************/
    public static function validateVentaApp($venta_detalle_array,$created_by,$sucursal_id)
    {

        $response = [
            "code"  => 202,
            "inv"   => [],
        ];

        foreach ($venta_detalle_array as $key => $venta_detalle) {

            $is_add = false;

            $Producto = Producto::findOne($venta_detalle["producto_id"]);

            $InvProducto = null;

            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id ], [ "=", "producto_id", $venta_detalle["producto_id"] ] ] )->one();

            if (isset($InvProducto->id)) {
                // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                if (floatval($venta_detalle["cantidad"]) > floatval($InvProducto->cantidad)) {
                    $is_add = true;
                }
            }

            if ($is_add) {

                $response["code"]    = 10;

                array_push($response["inv"],[
                    "producto" => $Producto->nombre,
                    "inv" => $InvProducto,
                ]);
            }

        }

        return $response;
    }

    /*************************************************************************/
    /*                     FINALIZA LA VENTA APP Y AFECTAMOS INVETARIO
    /*************************************************************************/
    public static function saveCerrarVentaApp($venta_detalle_array,$venta_id,$created_by,$sucursal_id)
    {

        foreach ($venta_detalle_array as $key => $venta_detalle) {

            if (isset($venta_detalle["envio_detalle_id"]))
                $VentaDetalle = VentaDetalle::findOne($venta_detalle["envio_detalle_id"]);
            else
                $VentaDetalle = new VentaDetalle();


            $VentaDetalle->venta_id     = $venta_id;
            $VentaDetalle->producto_id  = $venta_detalle["producto_id"];
            $VentaDetalle->cantidad     = $venta_detalle["cantidad"];
            $VentaDetalle->precio_venta = $venta_detalle["precio_venta"];
            if ($VentaDetalle->save()) {

                $Producto = Producto::findOne($VentaDetalle->producto_id);


                if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                    $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                else
                    $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id ], [ "=", "producto_id", $VentaDetalle->producto_id ] ] )->one();


                if (isset($InvProducto->id)) {

                    if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {
                        $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                        if (isset($InvProducto2->id)) {
                            // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                $InvProducto2->cantidad = floatval($InvProducto2->cantidad) - ( floatval($VentaDetalle->cantidad) * $Producto->sub_cantidad_equivalente);
                                $InvProducto2->save();

                            // SE REGISTRA EVENTO DEL MOVIMIENTO


                        }else{
                            // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO

                                $InvProductoSucursal  =  new InvProductoSucursal();
                                $InvProductoSucursal->sucursal_id   = $sucursal_id;
                                $InvProductoSucursal->producto_id   = $VentaDetalle->producto_id;
                                $InvProductoSucursal->cantidad      = $VentaDetalle->cantidad * -1;
                                $InvProductoSucursal->created_by    = $created_by;
                                $InvProductoSucursal->save();

                            // SE REGISTRA EVENTO DEL MOVIMIENTO

                        }

                    }else{

                        // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO

                            $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($VentaDetalle->cantidad);
                            $InvProducto->save();

                        // SE REGISTRA EVENTO DEL MOVIMIENTO

                    }

                }else{

                    // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO

                    $InvProductoSucursal  =  new InvProductoSucursal();
                    $InvProductoSucursal->sucursal_id   = $sucursal_id;
                    $InvProductoSucursal->producto_id   = $VentaDetalle->producto_id;
                    $InvProductoSucursal->cantidad      = $VentaDetalle->cantidad * -1;
                    $InvProductoSucursal->created_by    = $created_by;
                    $InvProductoSucursal->save();

                    // SE REGISTRA EVENTO DEL MOVIMIENTO

                }

                TransProductoInventario::saveTransVenta($sucursal_id,$VentaDetalle->id,$VentaDetalle->producto_id,$VentaDetalle->cantidad,TransProductoInventario::TIPO_SALIDA, $created_by);
            }

        }
    }


    /*************************************************************************/
    /*                     FINALIZA LA VENTAS EN GRUPO EN APP Y AFECTAMOS INVETARIO
    /*************************************************************************/

    public static function saveCerrarVentaGroupApp($venta_detalle_array, $created_by, $sucursal_id, $reparto_id,$cliente_id)
    {
        $array_result = [
            "productos" => [],
        ];

        /**
         * recorremos todo el array del detalle de las ventas
         * validacion de producto en existencia antes de realizar la operacion
         * por item se agregara un indicador de que ya fue entregado
         * valdiacion de items que pertenezcan a la misma venta para no cerrar la venta y actualizar el total real
         *
         * cuando sea el ultimo o el unico registro de la venta se realizara el cierre de la venta con su total real
         * */

        $array_trans_preventa  = [];

        if ($venta_detalle_array) {
            foreach ($venta_detalle_array as $key => $item_detalle) {
                /** DESCONTAMOS DEL INVENTARIO EL PRODCUTO **/
                $Producto = Producto::findOne($item_detalle["producto_id"]);
                $validExistencia = self::validateItemApp($item_detalle,$sucursal_id);

                if (intval($validExistencia["code"]) == 10 ) {
                    // VERIFICAMOS SI EL ITEM CORRESPONDE A UN PEDIDO
                    if (isset($item_detalle["envio_detalle_id"]) && $item_detalle["envio_detalle_id"]) {
                        // code...
                        $VentaDetalle = VentaDetalle::findOne($item_detalle["envio_detalle_id"]);
                        $VentaDetalle->is_reparto_entrega   = self::ENTREGA_REPARTO_ON;
                        $VentaDetalle->cantidad             = $item_detalle["cantidad"];
                        $VentaDetalle->precio_venta         = $item_detalle["precio_venta"];

                        if ($VentaDetalle->update()) {


                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                            else
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id ], [ "=", "producto_id", $VentaDetalle->producto_id ] ] )->one();

                            if (isset($InvProducto->id)) {

                                if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {
                                    $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                                    if (isset($InvProducto2->id)) {
                                        // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                            $InvProducto2->cantidad = floatval($InvProducto2->cantidad) - ( floatval($VentaDetalle->cantidad) * $Producto->sub_cantidad_equivalente);
                                            $InvProducto2->save();

                                        // SE REGISTRA EVENTO DEL MOVIMIENTO


                                    }

                                }else{
                                    // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                                    $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($VentaDetalle->cantidad);
                                    $InvProducto->save();
                                    // SE REGISTRA EVENTO DEL MOVIMIENTO

                                }

                            }

                            TransProductoInventario::saveTransVenta($sucursal_id,$VentaDetalle->id,$VentaDetalle->producto_id,$VentaDetalle->cantidad,TransProductoInventario::TIPO_SALIDA, $created_by);



                            //AGREGAMOS QUE EJECUTO SATISFACTORIAMENTE ESTE PRODUCTO
                            array_push($array_trans_preventa,$item_detalle["envio_detalle_id"]);

                            /** VALIDAMOS EL CIERRE DE LA VENTA  **/
                            $is_cierre = true;
                            foreach ($venta_detalle_array as $key => $valid_detalle) {
                                if ( isset($valid_detalle["preventa_id"]) && $valid_detalle["preventa_id"] == $item_detalle["preventa_id"]) {
                                    if (!in_array( $valid_detalle["envio_detalle_id"] ,$array_trans_preventa)) {
                                        $is_cierre = false;
                                    }
                                }
                            }

                            if ($is_cierre)
                                Venta::preventaCierre($item_detalle["preventa_id"], $reparto_id,$created_by);

                        }

                        array_push($array_result["productos"], [
                            "code" => 202,
                            "preventa_id"   => $item_detalle["preventa_id"],
                            "producto"      => $Producto->nombre,
                            "tipo"          => TempVentaTokenPay::TIPO_VENTA_CENTRAL,
                            "cantidad"      => $item_detalle["cantidad"],
                            "message"       => "VENTA EXITOSA",
                        ]);
                    }else{
                        //SE GENERAR UNA NUEVA VENTA
                        $TempVentaRuta      = new TempVentaRuta();
                        $TempVentaRuta->cliente_id  = $cliente_id;
                        $TempVentaRuta->sucursal_id = $sucursal_id;
                        $TempVentaRuta->total       = 0;
                        $TempVentaRuta->reparto_id  = $reparto_id;
                        $TempVentaRuta->tipo        = Venta::TIPO_GENERAL;
                        $TempVentaRuta->status      = Venta::STATUS_VENTA;
                        $TempVentaRuta->created_by   = $created_by;

                        if ($TempVentaRuta->save()) {
                            //$total_new = 0;

                                $TempVentaRutaDetalle = new TempVentaRutaDetalle();
                                $TempVentaRutaDetalle->temp_venta_ruta_id   = $TempVentaRuta->id;
                                $TempVentaRutaDetalle->producto_id          = $item_detalle["producto_id"];
                                $TempVentaRutaDetalle->cantidad             = $item_detalle["cantidad"];
                                $TempVentaRutaDetalle->precio_venta         = $item_detalle["precio_venta"];
                                $TempVentaRutaDetalle->created_by           = $created_by;
                                $TempVentaRutaDetalle->save();
                                //$total_new = $total_new + ( floatval($VentaDetalle->precio_venta) * floatval($VentaDetalle->cantidad));

                                $Producto = Producto::findOne($item_detalle['producto_id']);

                                if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                                    $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                                else
                                    $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id ], [ "=", "producto_id", $item_detalle['producto_id'] ] ] )->one();

                                if (isset($InvProducto->id)) {

                                    if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                                        $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id  ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                                        if (isset($InvProducto2->id)) {
                                            // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                            $InvProducto2->cantidad = floatval($InvProducto2->cantidad) - ( floatval($item_detalle["cantidad"]) * $Producto->sub_cantidad_equivalente) ;
                                            $InvProducto2->save();

                                        }
                                    }else{
                                        // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                                        $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($item_detalle["cantidad"]);
                                        $InvProducto->save();
                                    }
                                }

                                TransProductoInventario::saveTransTempVentaRuta($sucursal_id,$TempVentaRutaDetalle->id,$TempVentaRutaDetalle->producto_id,$TempVentaRutaDetalle->cantidad,TransProductoInventario::TIPO_SALIDA, $created_by);


                            //$venta->total = round($total_new,2);
                            //$venta->update();
                            Venta::preventaTempCierre($TempVentaRuta->id, $reparto_id,$created_by);

                            array_push($array_result["productos"], [
                                "code" => 202,
                                "preventa_id"   => isset($TempVentaRuta->id) ? $TempVentaRuta->id : null,
                                "producto"      => $Producto->nombre,
                                "tipo"          => TempVentaTokenPay::TIPO_VENTA_TEMP,
                                "cantidad"      => $item_detalle["cantidad"],
                                "message"       => "VENTA EXITOSA",
                            ]);
                        }
                    }
                }else{
                    //AGREGAMOS QUE EJECUTO SATISFACTORIAMENTE ESTE PRODUCTO
                    array_push($array_trans_preventa,$item_detalle["envio_detalle_id"]);
                    array_push($array_result["productos"], [
                        "code" => 10,
                        "preventa_id"   => isset($item_detalle["preventa_id"]) ? $item_detalle["preventa_id"] : null,
                        "producto"      => $Producto->nombre,
                        "cantidad"      => $item_detalle["cantidad"],
                        "message"       => $validExistencia["message"],
                    ]);
                }
            }

            Venta::preventaCierreAll($reparto_id,$created_by,$cliente_id);
        }

        return $array_result;

    }


    public static function validateItemApp($v_detalle,$inventario_sucursal_id)
    {

        $response = [];

        $is_add = false;

        $Producto = Producto::findOne($v_detalle["producto_id"]);

        $InvProducto = null;

        if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $inventario_sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
        else
            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $inventario_sucursal_id ], [ "=", "producto_id", $v_detalle["producto_id"] ] ] )->one();


        if (isset($InvProducto->id)) {
            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {
                $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $inventario_sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                if (isset($InvProducto2->id)) {
                    // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO
                    if (floatval($InvProducto2->cantidad >= (floatval($v_detalle["cantidad"]) *  $Producto->sub_cantidad_equivalente) ) ) {
                        $is_add = true;
                    }
                }
            }else{
                // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                if (floatval($InvProducto->cantidad) >= floatval($v_detalle["cantidad"]) ) {
                    $is_add = true;
                }
            }
        }

        if (isset($v_detalle["preventa_id"]))
            $isVentaProceso = Venta::findOne($v_detalle["preventa_id"]);


        if ($is_add) {
            if (isset($v_detalle["preventa_id"]) && $isVentaProceso->status != Venta::STATUS_PROCESO)
                $response = [
                    "code"     =>  20,
                    "producto" => $Producto->nombre,
                    "message" => "** Actualmente la venta ya no se puede procesar**",
                ];
            else
                $response = [
                    "code"     =>  10,
                    "producto" => $Producto->nombre,
                    "inv" => $InvProducto,
                ];
        }else{
            $response = [
                "code"     =>  20,
                "producto" => $Producto->nombre,
                "message" => "** Actualmente no cuentas con la existencia [". $v_detalle["cantidad"] ."] para abastecer el producto **",
            ];
        }



        return $response;

    }

    public static function getDetailPorVerificar($preventa_id)
    {
        $responseArray = [];
        $queryPreventa = VentaDetalle::find()->andWhere(["and",
            [ "=", "venta_id", $preventa_id ],
            [ "=", "apply_bodega", VentaDetalle::APPLY_BODEGA_ON ],
        ])->all();

        foreach ($queryPreventa as $key => $item_detalle) {
            array_push($responseArray, [
                "detail_id"     => $item_detalle->id,
                "producto_id"   => $item_detalle->producto_id,
                "producto"      => $item_detalle->producto->nombre,
                "cantidad"      => $item_detalle->cantidad,
                "status"        => $item_detalle->status,
                "sucursal_id"   => $item_detalle->sucursal_id,
            ]);
        }

        return $responseArray;

    }

    public static function postVerificarPreventa($venta_id, $preventaDetailObject)
    {
        $connection = \Yii::$app->db;

        $transaction = $connection->beginTransaction();

        try {
            $queryUpdateVenta =  $connection->createCommand()
            ->update('venta', [
                'status'        => Venta::STATUS_VERIFICADO,
                'updated_by'    => Yii::$app->user->identity->id,
                'updated_at'    => time(),
            ], "id=". $venta_id )->execute();

            foreach ($preventaDetailObject as $key => $item_detalle) {

                if ($item_detalle["status"] == VentaDetalle::STATUS_ACTIVE ) {
                    $ventaDetalle =  $connection->createCommand()
                    ->update('venta_detalle', [
                        'producto_id'   => $item_detalle['producto_id'],
                        'cantidad'      => $item_detalle['cantidad'],
                        'updated_by'    => Yii::$app->user->identity->id,
                        'updated_at'    => time(),
                    ], "id=". $item_detalle["detail_id"] )->execute();
                }

                if ($item_detalle["status"] == VentaDetalle::STATUS_CANCEL){
                    $ventaDetalle =  $connection->createCommand()
                    ->delete('venta_detalle', "id=". $item_detalle["detail_id"] )->execute();
                }
            }

            $transaction->commit();

            return [
                "code"    => 202,
                "message" => 'Se genero correctamente la pre-venta',
                "type"    => "Success",
            ];

        } catch(Exception $e) {
            $transaction->rollback();
            return [
                "code"    => 10,
                "message" => 'Ocurrio un error, intenta nuevamente',
                "type"    => "Error",
            ];
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
                $this->created_by = Yii::$app->user->identity? ( $this->created_by ? $this->created_by : Yii::$app->user->identity->id) : $this->created_by;

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
