<?php
namespace app\models\inv;

use Yii;
use app\models\user\User;
use app\models\producto\Producto;

/**
 * This is the model class for table "inventario_operacion_detalle".
 *
 * @property int $id ID
 * @property int $inventario_operacion_id Inventario operacion ID
 * @property int $producto_id Producto ID
 * @property float $cantidad_inventario Cantidad - Inventario
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int|null $updated_at Modificado
 * @property int|null $updated_by Modificado por
 *
 * @property User $createdBy
 * @property InventarioOperacion $inventarioOperacion
 * @property Producto $producto
 * @property User $updatedBy
 */
class InventarioOperacionDetalle extends \yii\db\ActiveRecord
{

    const TIPO_VIGENTE   = 10;
    const TIPO_REMOVE    = 20;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inventario_operacion_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['inventario_operacion_id', 'producto_id', 'cantidad_inventario','tipo'], 'required'],
            [['inventario_operacion_id', 'producto_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['cantidad_inventario','cantidad_old'], 'number'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['inventario_operacion_id'], 'exist', 'skipOnError' => true, 'targetClass' => InventarioOperacion::className(), 'targetAttribute' => ['inventario_operacion_id' => 'id']],
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
            'inventario_operacion_id' => 'Inventario Operacion ID',
            'producto_id' => 'Producto ID',
            'cantidad_inventario' => 'Cantidad Inventario',
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
     * Gets query for [[InventarioOperacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInventarioOperacion()
    {
        return $this->hasOne(InventarioOperacion::className(), ['id' => 'inventario_operacion_id']);
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


    public static function saveItemProducto($detailArray, $solicitud_id)
    {
        $inputProductoArray = json_decode($detailArray["inputProductoArray"]);

        if ($inputProductoArray) {
            foreach ($inputProductoArray as $key => $item_producto) {
                if ($item_producto->status == 10 && $item_producto->origen == 1 ) {
                    $InventarioOperacionDetalle = new InventarioOperacionDetalle();
                    $InventarioOperacionDetalle->inventario_operacion_id    = $solicitud_id;
                    $InventarioOperacionDetalle->cantidad_inventario        = 0;
                    $InventarioOperacionDetalle->tipo                       = self::TIPO_VIGENTE;
                    $InventarioOperacionDetalle->producto_id                = $item_producto->producto_id;
                    $InventarioOperacionDetalle->save();
                }

                if ($item_producto->status == 1 && $item_producto->origen == 2){
                    $InventarioOperacionDetalle =  InventarioOperacionDetalle::findOne($item_producto->item_id);
                    try {
                        $InventarioOperacionDetalle->delete();
                    } catch (Exception $e) {

                    }

                }
            }
        }
    }

    public static function getCantidadCargada($solicitud_id, $producto_id)
    {
        $InvProd = self::find()->andWhere(["and",
            ["=","inventario_operacion_id", $solicitud_id],
            ["=","producto_id", $producto_id]
        ])->one();

        return  isset($InvProd->id) && $InvProd->id ? floatval($InvProd->cantidad_inventario) : 0;
    }

    public static function getStatusCargada($solicitud_id, $producto_id)
    {
        $InvProd = self::find()->andWhere(["and",
            ["=","inventario_operacion_id", $solicitud_id],
            ["=","producto_id", $producto_id]
        ])->one();

        return  isset($InvProd->id) && $InvProd->tipo == self::TIPO_REMOVE ? self::TIPO_REMOVE  : self::TIPO_VIGENTE;
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
