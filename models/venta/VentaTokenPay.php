<?php
namespace app\models\venta;

use Yii;
use app\models\user\User;

/**
 * This is the model class for table "venta_token_pay".
 *
 * @property int $id ID
 * @property int $venta_id Venta ID
 * @property string $token_pay Token pay
 * @property int $created_at Created at
 * @property int $created_by Creado por
 *
 * @property User $createdBy
 * @property Venta $venta
 */
class VentaTokenPay extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'venta_token_pay';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['venta_id', 'token_pay'], 'required'],
            [['venta_id', 'created_at', 'created_by'], 'integer'],
            [['token_pay'], 'string', 'max' => 150],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
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
     * Gets query for [[Venta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVenta()
    {
        return $this->hasOne(Venta::className(), ['id' => 'venta_id']);
    }

    public static function getOperacionVentaCount($token_pay)
    {
        return self::find()->andWhere(["token_pay" => $token_pay ])->count();
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
