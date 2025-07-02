<?php
namespace app\models\trans;

use Yii;
use app\models\user\User;
use app\models\venta\VentaDetalle;
use app\models\inv\OperacionDetalle;
use app\models\tranformacion\TranformacionDevolucionDetalle;
use app\models\inv\InvProductoSucursal;
use app\models\sucursal\Sucursal;
use app\models\reparto\RepartoDetalle;
use app\models\temp\TempVentaRutaDetalle;


/**
 * This is the model class for table "trans_producto_inventario".
 *
 * @property int $id ID
 * @property int|null $venta_detalle_id Venta detalle ID
 * @property int|null $operacion_detalle_id Operacion detalle ID
 * @property int|null $transformacion_detalle_id Transformacion detalle ID
 * @property int|null $tipo Tipo
 * @property int|null $motivo Motivo
 * @property int $created_by Creado por
 * @property int|null $created_at
 *
 * @property OperacionDetalle $operacionDetalle
 * @property TranformacionDevolucionDetalle $transformacionDetalle
 * @property VentaDetalle $ventaDetalle
 * @property User $createdBy
 */
class TransProductoInventario extends \yii\db\ActiveRecord
{


    const TIPO_VENTA            = 10;
    const TIPO_VENTA_RUTA       = 15;
    const TIPO_OPERACION        = 20;
    const TIPO_TRANSFORMACION   = 30;
    const TIPO_REPARTO          = 40;
    const TIPO_AJUSTE           = 50;
    const TIPO_AJUSTE_PREVENTA  = 60;


    const TIPO_ENTRADA      = 10;
    const TIPO_SALIDA       = 20;

    public static $tipoList = [
        self::TIPO_ENTRADA  => 'ENTRADA',
        self::TIPO_SALIDA   => 'SALIDA',
    ];


    public static $motivoList = [
        self::TIPO_VENTA            => "OPERACION DE VENTA",
        self::TIPO_VENTA_RUTA       => "OPERACION DE VENTA RUTA",
        self::TIPO_OPERACION        => "OPERACION DE TRASPASO",
        self::TIPO_TRANSFORMACION   => "OPERACION DE TRANSFORMACIÓN",
        self::TIPO_REPARTO          => "OPERACION DE REPARTO",
        self::TIPO_AJUSTE           => "OPERACION DE AJUSTE",
        self::TIPO_AJUSTE_PREVENTA  => "OPERACION DE AJUSTE [PREVENTA]",
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'trans_producto_inventario';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['venta_detalle_id', 'operacion_detalle_id', 'transformacion_detalle_id','reparto_detalle_id', 'tipo', 'motivo','producto_id','created_by','created_at','sucursal_id','tranformacion_id','venta_id'], 'integer'],
            [['producto_id','cantidad','sucursal_id'], 'required'],
            [['inventario'],'number'],
            [['operacion_detalle_id'], 'exist', 'skipOnError' => true, 'targetClass' => OperacionDetalle::className(), 'targetAttribute' => ['operacion_detalle_id' => 'id']],
            [['transformacion_detalle_id'], 'exist', 'skipOnError' => true, 'targetClass' => TranformacionDevolucionDetalle::className(), 'targetAttribute' => ['transformacion_detalle_id' => 'id']],
            [['venta_detalle_id'], 'exist', 'skipOnError' => true, 'targetClass' => VentaDetalle::className(), 'targetAttribute' => ['venta_detalle_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'venta_detalle_id' => 'Venta Detalle ID',
            'operacion_detalle_id' => 'Operacion Detalle ID',
            'transformacion_detalle_id' => 'Transformacion Detalle ID',
            'tipo' => 'Tipo',
            'motivo' => 'Motivo',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[OperacionDetalle]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOperacionDetalle()
    {
        return $this->hasOne(OperacionDetalle::className(), ['id' => 'operacion_detalle_id']);
    }


    public function getRepartoDetalle()
    {
        return $this->hasOne(RepartoDetalle::className(), ['id' => 'reparto_detalle_id']);
    }

    /**
     * Gets query for [[TransformacionDetalle]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTransformacionDetalle()
    {
        return $this->hasOne(TranformacionDevolucionDetalle::className(), ['id' => 'transformacion_detalle_id']);
    }

    /**
     * Gets query for [[VentaDetalle]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentaDetalle()
    {
        return $this->hasOne(VentaDetalle::className(), ['id' => 'venta_detalle_id']);
    }

    public function getTempVentaDetalle()
    {
        return $this->hasOne(TempVentaRutaDetalle::className(), ['id' => 'temp_venta_ruta_detalle_id']);
    }


    public function getSucursal()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_id']);
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

    public static function saveTransVenta($sucursal_id, $venta_detalle_id,$producto_id,$cantidad,$motivo, $created_by = null)
    {
        $TransProductoInventario = new TransProductoInventario();
        $TransProductoInventario->sucursal_id        = $sucursal_id;
        $TransProductoInventario->venta_detalle_id   = $venta_detalle_id;
        $TransProductoInventario->producto_id        = $producto_id;
        $TransProductoInventario->cantidad           = $cantidad;
        $TransProductoInventario->motivo             = $motivo;
        $TransProductoInventario->tipo               = self::TIPO_VENTA;
        if ($motivo == TransProductoInventario::TIPO_ENTRADA) {
            $TransProductoInventario->inventario         = floatval(InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id)) - floatval($cantidad);
        }else{
            $TransProductoInventario->inventario         = floatval(InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id)) + floatval($cantidad);
            //$TransProductoInventario->inventario         = InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id);
        }
        $TransProductoInventario->created_by         = $created_by;
        $TransProductoInventario->save();
    }


    public static function saveTransTempVentaRuta($sucursal_id, $temp_venta_ruta_detalle_id,$producto_id,$cantidad,$motivo, $created_by = null)
    {
        $TransProductoInventario = new TransProductoInventario();
        $TransProductoInventario->sucursal_id                = $sucursal_id;
        $TransProductoInventario->temp_venta_ruta_detalle_id = $temp_venta_ruta_detalle_id;
        $TransProductoInventario->producto_id        = $producto_id;
        $TransProductoInventario->cantidad           = $cantidad;
        $TransProductoInventario->motivo             = $motivo;
        $TransProductoInventario->tipo               = self::TIPO_VENTA_RUTA;

        if ($motivo == TransProductoInventario::TIPO_ENTRADA) {
            $TransProductoInventario->inventario         = floatval(InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id)) - floatval($cantidad);
        }else{
            $TransProductoInventario->inventario         = floatval(InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id)) + floatval($cantidad);
            //$TransProductoInventario->inventario         = InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id);
        }

        //$TransProductoInventario->inventario         = InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id);
        $TransProductoInventario->created_by         = $created_by;
        $TransProductoInventario->save();
    }

    public static function saveTransOperacion($sucursal_id, $operacion_detalle_id,$producto_id,$cantidad,$motivo, $created_by = null)
    {
        $TransProductoInventario = new TransProductoInventario();
        $TransProductoInventario->sucursal_id           = $sucursal_id;
        $TransProductoInventario->operacion_detalle_id  = $operacion_detalle_id;
        $TransProductoInventario->producto_id        = $producto_id;
        $TransProductoInventario->cantidad           = $cantidad;
        $TransProductoInventario->motivo             = $motivo;
        $TransProductoInventario->tipo               = self::TIPO_OPERACION;
        if ($motivo == TransProductoInventario::TIPO_ENTRADA) {
            $TransProductoInventario->inventario         = floatval(InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id)) - floatval($cantidad);
        }else{
            $TransProductoInventario->inventario         = floatval(InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id)) + floatval($cantidad);
        }

        $TransProductoInventario->created_by         = $created_by;
        $TransProductoInventario->save();
    }

    public static function saveTransTransformacion($sucursal_id, $transformacion_detalle_id,$producto_id,$cantidad,$motivo, $created_by = null)
    {
        $TransProductoInventario = new TransProductoInventario();
        $TransProductoInventario->sucursal_id                   = $sucursal_id;
        $TransProductoInventario->transformacion_detalle_id     = $transformacion_detalle_id;
        $TransProductoInventario->producto_id                   = $producto_id;
        $TransProductoInventario->cantidad                      = $cantidad;
        $TransProductoInventario->motivo                        = $motivo;
        $TransProductoInventario->tipo                          = self::TIPO_TRANSFORMACION;
        $TransProductoInventario->inventario                    = InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id);
        $TransProductoInventario->created_by                    = $created_by;
        $TransProductoInventario->save();
    }

    public static function saveTransTransformacionEntrada($sucursal_id, $tranformacion_id,$producto_id,$cantidad,$motivo, $created_by = null)
    {
        $TransProductoInventario = new TransProductoInventario();
        $TransProductoInventario->sucursal_id                   = $sucursal_id;
        $TransProductoInventario->tranformacion_id              = $tranformacion_id;
        $TransProductoInventario->producto_id                   = $producto_id;
        $TransProductoInventario->cantidad                      = $cantidad;
        $TransProductoInventario->motivo                        = $motivo;
        $TransProductoInventario->tipo                          = self::TIPO_TRANSFORMACION;
        $TransProductoInventario->inventario                    = InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id);
        $TransProductoInventario->created_by                    = $created_by;
        $TransProductoInventario->save();
    }

    public static function saveTransReparto($sucursal_id, $reparto_detalle_id,$producto_id,$cantidad,$motivo, $created_by = null)
    {
        $TransProductoInventario = new TransProductoInventario();
        $TransProductoInventario->sucursal_id                   = $sucursal_id;
        $TransProductoInventario->reparto_detalle_id            = $reparto_detalle_id;
        $TransProductoInventario->producto_id                   = $producto_id;
        $TransProductoInventario->cantidad                      = $cantidad;
        $TransProductoInventario->motivo                        = $motivo;
        $TransProductoInventario->tipo                          = self::TIPO_REPARTO;
        $TransProductoInventario->inventario                    = InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id);
        $TransProductoInventario->created_by                    = $created_by;
        $TransProductoInventario->save();
    }

    public static function saveTransAjuste($sucursal_id, $reparto_detalle_id,$producto_id,$cantidad,$motivo, $created_by = null)
    {
        $TransProductoInventario = new TransProductoInventario();
        $TransProductoInventario->sucursal_id                   = $sucursal_id;
        $TransProductoInventario->producto_id                   = $producto_id;
        $TransProductoInventario->cantidad                      = $cantidad;
        $TransProductoInventario->motivo                        = $motivo;
        $TransProductoInventario->tipo                          = self::TIPO_AJUSTE;
        //$TransProductoInventario->inventario                    = InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id);
        if ($motivo == TransProductoInventario::TIPO_ENTRADA) {
            $TransProductoInventario->inventario         = floatval(InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id)) - floatval($cantidad);
        }else{
            $TransProductoInventario->inventario         = floatval(InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id)) + floatval($cantidad);
        }

        $TransProductoInventario->created_by                    = $created_by;
        $TransProductoInventario->save();
    }

    public static function saveTransAjusteVenta($sucursal_id, $venta_id,$producto_id,$cantidad,$motivo, $created_by = null)
    {
        $TransProductoInventario = new TransProductoInventario();
        $TransProductoInventario->sucursal_id                   = $sucursal_id;
        $TransProductoInventario->venta_id                      = $venta_id;
        $TransProductoInventario->producto_id                   = $producto_id;
        $TransProductoInventario->cantidad                      = $cantidad;
        $TransProductoInventario->motivo                        = $motivo;
        $TransProductoInventario->tipo                          = self::TIPO_AJUSTE_PREVENTA;
        $TransProductoInventario->inventario                    = InvProductoSucursal::getInventarioActual($sucursal_id, $producto_id);
        $TransProductoInventario->created_by                    = $created_by;
        $TransProductoInventario->save();
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

            }
            return true;

        } else
            return false;
    }
}
