<?php

namespace app\models\reparto;

use Yii;
use app\models\user\User;
use app\models\venta\VentaDetalle;
use app\models\producto\Producto;


/**
 * This is the model class for table "reparto_detalle".
 *
 * @property int $id ID
 * @property int $reparto_id Reparto ID
 * @property int|null $venta_id Venta ID
 * @property int|null $producto_id Producto ID
 * @property float|null $cantidad Cantidad
 * @property int $created_by Creado por
 * @property int $created_at Creado
 *
 * @property User $createdBy
 * @property Producto $producto
 * @property Reparto $reparto
 * @property Venta $venta
 */
class RepartoDetalle extends \yii\db\ActiveRecord
{
    public $reparto_detalle_array;

    const TIPO_PRECAPTURA = 10;
    const TIPO_PRODUCTO = 20;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reparto_detalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['reparto_id', 'venta_detalle_id', 'producto_id','tipo'], 'integer'],
            [['cantidad'], 'number'],
            [['reparto_detalle_array'], 'safe'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['producto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Producto::className(), 'targetAttribute' => ['producto_id' => 'id']],
            [['reparto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Reparto::className(), 'targetAttribute' => ['reparto_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reparto_id' => 'Reparto ID',
            'venta_detalle_id' => 'Venta detalle ID',
            'producto_id' => 'Producto ID',
            'cantidad' => 'Cantidad',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
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
    public function getVentaDetalle()
    {
        return $this->hasOne(VentaDetalle::className(), ['id' => 'venta_detalle_id']);
    }

    /**
     * Gets query for [[Reparto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReparto()
    {
        return $this->hasOne(Reparto::className(), ['id' => 'reparto_id']);
    }

    public function saveDetalleReparto($reparto_id)
    {
        $reparto_detalle_array = json_decode($this->reparto_detalle_array);

        if ($reparto_detalle_array) {
            // CARGA LA LISTA
            foreach ($reparto_detalle_array as $key => $reparto_detalle) {
                if ($reparto_detalle->check_true == 10) {
                    $RepartoDetalle = new RepartoDetalle();
                    $RepartoDetalle->reparto_id   = $reparto_id;
                    $RepartoDetalle->venta_id     = $reparto_detalle->item_id;
                    $RepartoDetalle->tipo         = $reparto_detalle->tipo;
                    $RepartoDetalle->producto_id  = $reparto_detalle->producto_id;
                    $RepartoDetalle->cantidad     = $reparto_detalle->cantidad;
                    $RepartoDetalle->save();
                }
            }
        }
        return true;
    }

    public static function deleteVenta($venta_detalle_id){
        RepartoDetalle::deleteAll([ "venta_detalle_id" => $venta_detalle_id ]);
    }


    public static function updatePreventa($venta_detalle_id, $producto_id, $cantidad)
    {
        $RepartoDetalle = RepartoDetalle::find()->andWhere(["and",
            ["=" , "venta_detalle_id",  $venta_detalle_id],
            ["=", "producto_id", $producto_id],
        ])->one();

        if (isset($RepartoDetalle->id)) {
            $RepartoDetalle->cantidad = $cantidad;
            $RepartoDetalle->update();
        }
    }

    public static function registerPreventa($venta_detalle_id,$sucursal_ruta_id , $producto_id, $cantidad)
    {

        $Reparto = Reparto::find()->andWhere(["and",
            ["=", "sucursal_id", $sucursal_ruta_id],
            ["<>", "status", Reparto::STATUS_TERMINADO ]
        ])->orderBy('id desc')->one();

        if (isset($Reparto->id)) {
            $RepartoDetalle                     = new RepartoDetalle();
            $RepartoDetalle->reparto_id         = $Reparto->id;
            $RepartoDetalle->tipo               = RepartoDetalle::TIPO_PRECAPTURA;
            $RepartoDetalle->venta_detalle_id   = $venta_detalle_id;
            $RepartoDetalle->producto_id        = $producto_id;
            $RepartoDetalle->cantidad           = $cantidad;
            $RepartoDetalle->save();
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
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: $this->created_by;

            }
            return true;

        } else
            return false;
    }
}
