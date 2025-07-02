<?php
namespace app\models\tranformacion;

use Yii;
use app\models\venta\Venta;
use app\models\producto\Producto;
use app\models\venta\VentaDetalle;

/**
 * This is the model class for table "tranformacion_devolucion_detalle".
 *
 * @property int $id ID
 * @property int $tranformacion_devolucion_id Tranformacion devolucion  ID
 * @property int $venta_id Venta ID
 * @property int $venta_detalle_id Venta detalle ID
 * @property float $cantidad Cantidad
 *
 * @property TranformacionDevolucion $tranformacionDevolucion
 * @property VentaDetalle $ventaDetalle
 * @property Venta $venta
 */
class TranformacionDevolucionDetalle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tranformacion_devolucion_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tranformacion_devolucion_id', 'producto_id',  'cantidad'], 'required'],
            [['tranformacion_devolucion_id', 'producto_id'], 'integer'],
            [['cantidad'], 'number'],
            [['tranformacion_devolucion_id'], 'exist', 'skipOnError' => true, 'targetClass' => TranformacionDevolucion::className(), 'targetAttribute' => ['tranformacion_devolucion_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tranformacion_devolucion_id' => 'Tranformacion Devolucion ID',
            'venta_id' => 'Venta ID',
            'operacion_id' => 'Operacion ID',
            'venta_detalle_id' => 'Venta Detalle ID',
            'cantidad' => 'Cantidad',
        ];
    }


    public function getProducto()
    {
        return $this->hasOne(Producto::className(), ['id' => 'producto_id']);
    }

    /**
     * Gets query for [[TranformacionDevolucion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTranformacionDevolucion()
    {
        return $this->hasOne(TranformacionDevolucion::className(), ['id' => 'tranformacion_devolucion_id']);
    }
}
