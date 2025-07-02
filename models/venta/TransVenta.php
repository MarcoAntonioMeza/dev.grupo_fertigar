<?php
namespace app\models\venta;

use Yii;
use yii\db\Query;
use app\models\user\User;
use app\models\producto\Producto;

/**
 * This is the model class for table "trans_venta".
 *
 * @property int $id ID
 * @property int $venta_id Venta ID
 * @property int $venta_detalle_id Venta detalle ID
 * @property int $producto_id Producto ID
 * @property float $cantidad Cantidad
 * @property int $status Status
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property User $createdBy
 * @property Producto $producto
 * @property VentaDetalle $ventaDetalle
 * @property Venta $venta
 */
class TransVenta extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'trans_venta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['venta_id', 'venta_detalle_id', 'producto_id', 'cantidad', 'status'], 'required'],
            [['venta_id', 'venta_detalle_id', 'producto_id', 'status', 'created_at', 'created_by'], 'integer'],
            [['cantidad'], 'number'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['producto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Producto::className(), 'targetAttribute' => ['producto_id' => 'id']],
            [['venta_detalle_id'], 'exist', 'skipOnError' => true, 'targetClass' => VentaDetalle::className(), 'targetAttribute' => ['venta_detalle_id' => 'id']],
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
            'venta_detalle_id' => 'Venta Detalle ID',
            'producto_id' => 'Producto ID',
            'cantidad' => 'Cantidad',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
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
     * Gets query for [[VentaDetalle]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentaDetalle()
    {
        return $this->hasOne(VentaDetalle::className(), ['id' => 'venta_detalle_id']);
    }

    /**
     * Gets query for [[Venta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVenta()
    {
        return $this->hasOne(Venta::className(), ['id' => 'venta_id']);
    }

    public static function deleteTransVenta($venta_detalle_id){
        TransVenta::deleteAll([ "venta_detalle_id" => $venta_detalle_id ]);
    }

    public static function saveTransVenta($venta_id,$venta_detalle_id,$producto_id,$cantidad,$status_venta, $created_by = null)
    {
        $TransVenta = new TransVenta();
        $TransVenta->venta_id           = $venta_id;
        $TransVenta->venta_detalle_id   = $venta_detalle_id;
        $TransVenta->producto_id        = $producto_id;
        $TransVenta->cantidad           = $cantidad;
        $TransVenta->status             = $status_venta;
        $TransVenta->created_by         = $created_by;
        $TransVenta->save();
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
            //["=", "( select t_venta.status from trans_venta  t_venta where  t_venta.venta_detalle_id = trans_venta.venta_detalle_id order by id desc limit 1 )", Venta::STATUS_PRECAPTURA ]
         ])
        /*->andWhere(["or",
            //["=", "( select t_venta.status from trans_venta  t_venta where  t_venta.venta_detalle_id = trans_venta.venta_detalle_id order by id desc limit 1 )", Venta::STATUS_PREVENTA ],
        ])*/
        ->one();
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
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;
            }

            return true;

        } else
            return false;
    }
}
