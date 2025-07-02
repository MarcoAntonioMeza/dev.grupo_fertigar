<?php
namespace app\models\tranformacion;

use Yii;
use app\models\esys\EsysListaDesplegable;
use app\models\producto\Producto;
use app\models\user\User;
use app\models\sucursal\Sucursal;

/**
 * This is the model class for table "tranformacion_devolucion".
 *
 * @property int $id ID
 * @property int $sucursal_id Sucursal ID
 * @property int $motivo_id Motivo
 * @property int|null $producto_new Producto New
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property User $createdBy
 * @property EsysListaDesplegable $motivo
 * @property Producto $productoNew
 * @property Sucursal $sucursal
 * @property TranformacionDevolucionDetalle[] $tranformacionDevolucionDetalles
 */
class TranformacionDevolucion extends \yii\db\ActiveRecord
{

    //const TRANS_NEW_PRODUCTO = 10;
    const TRANS_OND_PRODUCTO = 20;
    const TRANS_MERMA        = 30;
    const TRANS_VENDIDO      = 40;


    public static $transList = [
        //self::TRANS_NEW_PRODUCTO   => 'NUEVO PRODUCTO',
        self::TRANS_OND_PRODUCTO   => 'TRANSFORMAR',
        self::TRANS_MERMA          => 'MERMA',
        self::TRANS_VENDIDO        => 'CORTESIA',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tranformacion_devolucion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['motivo_id'], 'required'],
            [['producto_cantidad'],'number'],
            [['token','nota'],'string'],
            [['sucursal_id', 'motivo_id', 'producto_new', 'created_at', 'created_by'], 'integer'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['producto_new'], 'exist', 'skipOnError' => true, 'targetClass' => Producto::className(), 'targetAttribute' => ['producto_new' => 'id']],
            [['sucursal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sucursal::className(), 'targetAttribute' => ['sucursal_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sucursal_id' => 'Sucursal ID',
            'producto_cantidad' => 'Cantidad',
            'motivo_id' => 'Motivo ID',
            'nota'          => 'Nota',
            'producto_new' => 'Producto New',
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
     * Gets query for [[ProductoNew]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductoNew()
    {
        return $this->hasOne(Producto::className(), ['id' => 'producto_new']);
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
     * Gets query for [[TranformacionDevolucionDetalles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTranformacionDevolucionDetalles()
    {
        return $this->hasMany(TranformacionDevolucionDetalle::className(), ['tranformacion_devolucion_id' => 'id']);
    }

    public static function getItemsGroup($token, $operacion_id)
    {
        return self::find()->andWhere(["or",
            ["=","token",$token],
            ["=","id",$operacion_id],
        ])->all();
    }

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;

            }

            return true;

        } else
            return false;
    }
}
