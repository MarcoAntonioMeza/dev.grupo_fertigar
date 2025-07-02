<?php
namespace app\models\inv;

use Yii;
use app\models\producto\Producto;
use app\models\user\User;
use app\models\sucursal\Sucursal;

/**
 * This is the model class for table "inv_producto_sucursal".
 *
 * @property int $id ID
 * @property int $producto_id Producto ID
 * @property int $sucursal_id Sucursal ID
 * @property float $cantidad Cantidad
 * @property int $tipo Tipo ID
 * @property int $created_by Creado por
 * @property int $created_at Creado
 * @property int|null $updated_by Modificado por
 * @property int|null $updated_at Modificado
 *
 * @property User $createdBy
 * @property Producto $producto
 * @property Sucursal $sucursal
 * @property User $updatedBy
 */
class InvProductoSucursal extends \yii\db\ActiveRecord
{

    public $producto_array_update;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inv_producto_sucursal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['producto_id', 'sucursal_id', 'cantidad'], 'required'],
            [['producto_id', 'sucursal_id',  'created_by', 'created_at', 'updated_by', 'updated_at','producto_parent_id'], 'integer'],
            [['cantidad'], 'number'],
            [['producto_array_update'], 'safe'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['producto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Producto::className(), 'targetAttribute' => ['producto_id' => 'id']],
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
            'producto_id' => 'Producto ID',
            'sucursal_id' => 'Sucursal ID',
            'cantidad' => 'Cantidad',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
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

    public static function getStockProducto($producto_id,$sucursal_id)
    {
        return self::find()->andWhere([ "and",["=", "producto_id", $producto_id ],["=", "sucursal_id", $sucursal_id] ])->one();
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

    public static function getStockRuta($ruta_id)
    {
        return self::find()->andWhere([ "and",["=", "sucursal_id", $ruta_id ],[">", "cantidad", 0] ])->all();
    }

    public static function getStockRutaObject($ruta_id)
    {
        $responseArray = [];
        foreach (InvProductoSucursal::getStockRuta($ruta_id) as $key => $item_producto) {
            array_push($responseArray, [
                "producto_id"   => $item_producto->producto_id,
                "producto"      => $item_producto->producto->nombre,
                "cantidad"      => $item_producto->cantidad,
                "unidad_medida" => Producto::$medidaList[$item_producto->producto->tipo_medida],
            ]);
        }

        return $responseArray;
    }

    public static function getStockTranformacion($ruta_id, $producto_text)
    {
        $query = self::find()
        ->innerJoin("producto", "inv_producto_sucursal.producto_id = producto.id")
        ->andWhere(
            [ "and",["=", "sucursal_id", $ruta_id ],
            [">", "cantidad", 0 ]
        ]);

        if ($producto_text)
            $query->andFilterWhere([
                'or',
                ['like', 'producto.nombre', $producto_text],
            ]);


        return $query->all();
    }


    public static function getLoadProducto($sucursal_id,$producto_id,$cantidad)
    {
        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id], [ "=", "producto_id", $producto_id] ] )->one();
         if (isset($InvProducto->id)) {
            $InvProducto->cantidad = floatval($InvProducto->cantidad) + floatval($cantidad);
            $InvProducto->save();
        }else{
            $InvProductoSucursal  =  new InvProductoSucursal();
            $InvProductoSucursal->sucursal_id   = $sucursal_id;
            $InvProductoSucursal->producto_id   = $producto_id;
            $InvProductoSucursal->cantidad      = $cantidad;
            $InvProductoSucursal->save();
        }

    }

    public static function getInventarioActual($sucursal_id, $producto_id)
    {
        $cantidadInventario = 0;

        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id], [ "=", "producto_id", $producto_id] ] )->one();
        if (isset($InvProducto->id))
            $cantidadInventario = floatval($InvProducto->cantidad);

        return $cantidadInventario;
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
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;
            }

            return true;

        } else
            return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        //Consultamos si el stock es menor a 0
        if ($this->cantidad < 0 ) {
            /**
             *  PARA EVITAR INVETATARIO NEGATIVO
             * */
            $this->cantidad = 0;
            $this->update();
        }
    }
}
