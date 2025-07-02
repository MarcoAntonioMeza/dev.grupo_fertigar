<?php
namespace app\models\temp;

use Yii;
use app\models\user\User;
use app\models\producto\Producto;

/**
 * This is the model class for table "venta_ruta_detalle".
 *
 * @property int $id ID
 * @property int $temp_venta_ruta_id Venta
 * @property int $producto_id Producto
 * @property float $cantidad Cantidad
 * @property float|null $precio_venta Precio de venta
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int|null $updated_at Modificado
 * @property int|null $updated_by Modificado por
 *
 * @property User $createdBy
 * @property Producto $producto
 * @property User $updatedBy
 * @property VentaRuta $ventaRuta
 */
class TempVentaRutaDetalle extends \yii\db\ActiveRecord
{
    const IS_APPLY_ON   = 10;
    const IS_APPLY_OFF  = 20;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temp_venta_ruta_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['temp_venta_ruta_id', 'producto_id', 'cantidad'], 'required'],
            [['temp_venta_ruta_id', 'producto_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['cantidad', 'precio_venta'], 'number'],
            [['is_apply'], 'default','value' => self::IS_APPLY_OFF],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['producto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Producto::className(), 'targetAttribute' => ['producto_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['temp_venta_ruta_id'], 'exist', 'skipOnError' => true, 'targetClass' => TempVentaRuta::className(), 'targetAttribute' => ['temp_venta_ruta_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                    => 'ID',
            'temp_venta_ruta_id'    => 'Venta Ruta ID',
            'producto_id'           => 'Producto ID',
            'cantidad'              => 'Cantidad',
            'precio_venta'          => 'Precio Venta',
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
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * Gets query for [[VentaRuta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTempVentaRuta()
    {
        return $this->hasOne(TempVentaRuta::className(), ['id' => 'temp_venta_ruta_id']);
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
