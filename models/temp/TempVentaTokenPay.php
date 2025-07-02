<?php
namespace app\models\temp;

use Yii;
use app\models\user\User;
use app\models\venta\Venta;
/**
 * This is the model class for table "temp_venta_token_pay".
 *
 * @property int $id ID
 * @property int $venta_id Venta ID
 * @property int $temp_venta_ruta_id Venta ID
 * @property int $tipo Tipo
 * @property string $token_pay Token pay
 * @property int $created_at Created at
 * @property int $created_by Creado por
 *
 * @property User $createdBy
 * @property TempVentaRuta $tempVentaRuta
 * @property Venta $venta
 */
class TempVentaTokenPay extends \yii\db\ActiveRecord
{

    const TIPO_VENTA_TEMP       = 10;
    const TIPO_VENTA_CENTRAL    = 20;

    const IS_APPLY_ON   = 10;
    const IS_APPLY_OFF  = 20;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temp_venta_token_pay';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tipo', 'token_pay'], 'required'],
            [['venta_id', 'temp_venta_ruta_id', 'tipo', 'created_at', 'created_by','operacion_reparto_id'], 'integer'],
            [['token_pay'], 'string', 'max' => 150],
            [['is_apply'], 'default','value' => self::IS_APPLY_OFF],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['temp_venta_ruta_id'], 'exist', 'skipOnError' => true, 'targetClass' => TempVentaRuta::className(), 'targetAttribute' => ['temp_venta_ruta_id' => 'id']],
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
            'temp_venta_ruta_id' => 'Temp Venta Ruta ID',
            'tipo' => 'Tipo',
            'token_pay' => 'Token Pay',
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
     * Gets query for [[TempVentaRuta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTempVentaRuta()
    {
        return $this->hasOne(TempVentaRuta::className(), ['id' => 'temp_venta_ruta_id']);
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
