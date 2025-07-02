<?php
namespace app\models\compra;

use Yii;
use app\models\user\User;
use app\models\producto\Producto;
/**
 * This is the model class for table "compra_detalle".
 *
 * @property int $id ID
 * @property int $compra_id Compra ID
 * @property int $producto_id Producto
 * @property int $cantidad Cantidad
 * @property float|null $costo Precio de venta
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property Compra $compra
 * @property User $createdBy
 * @property Producto $producto
 */
class CompraDetalle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'compra_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['compra_id', 'producto_id', 'cantidad'], 'required'],
            [['compra_id', 'producto_id', 'created_at', 'created_by'], 'integer'],
            [['costo','cantidad'], 'number'],
            [['compra_id'], 'exist', 'skipOnError' => true, 'targetClass' => Compra::className(), 'targetAttribute' => ['compra_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
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
            'compra_id' => 'Compra ID',
            'producto_id' => 'Producto ID',
            'cantidad' => 'Cantidad',
            'costo' => 'Precio Venta',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * Gets query for [[Compra]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompra()
    {
        return $this->hasOne(Compra::className(), ['id' => 'compra_id']);
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
