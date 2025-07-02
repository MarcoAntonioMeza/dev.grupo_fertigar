<?php
namespace app\models\credito;

use Yii;
use app\models\user\User;

/**
 * This is the model class for table "credito_token_pay".
 *
 * @property int $id ID
 * @property int $credito_id Credito ID
 * @property string $token_pay Token pay
 * @property int $created_at Created at
 * @property int $created_by Creado por
 *
 * @property Credito $credito
 * @property User $createdBy
 */
class CreditoTokenPay extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'credito_token_pay';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['credito_id', 'token_pay'], 'required'],
            [['credito_id', 'created_at', 'created_by'], 'integer'],
            [['token_pay'], 'string', 'max' => 150],
            [['credito_id'], 'exist', 'skipOnError' => true, 'targetClass' => Credito::className(), 'targetAttribute' => ['credito_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'credito_id' => 'Credito ID',
            'token_pay' => 'Token Pay',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * Gets query for [[Credito]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCredito()
    {
        return $this->hasOne(Credito::className(), ['id' => 'credito_id']);
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


    public static function getCreditoToken($token)
    {
        return  self::find()->andWhere([ "token_pay" => $token ])->all();
    }


    public static function getCreditoAbono($token)
    {
        return  CreditoAbono::find()->andWhere([ "token_pay" => $token ])->one();
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
