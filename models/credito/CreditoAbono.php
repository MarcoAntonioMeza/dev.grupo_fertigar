<?php
namespace app\models\credito;

use Yii;
use app\models\user\User;

/**
 * This is the model class for table "credito_abono".
 *
 * @property int $id ID
 * @property int $credito_id Credito
 * @property float $cantidad Cantidad
 * @property string $token_pay Token pay
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property int $created_by Creado por
 *
 * @property User $createdBy
 * @property Credito $credito
 */
class CreditoAbono extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE     = 10;
    const STATUS_CANCEL     = 20;

    public static $statusList = [
        self::STATUS_ACTIVE => "VIGENTE",
        self::STATUS_CANCEL => "CANCELADO"
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'credito_abono';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['credito_id', 'cantidad', 'token_pay'], 'required'],
            [['status'], 'default', 'value'=> self::STATUS_ACTIVE],
            [['credito_id', 'status', 'created_at', 'created_by','updated_by','updated_at'], 'integer'],
            [['cantidad'], 'number'],
            [['token_pay'], 'string', 'max' => 50],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['credito_id'], 'exist', 'skipOnError' => true, 'targetClass' => Credito::className(), 'targetAttribute' => ['credito_id' => 'id']],
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
            'cantidad' => 'Cantidad',
            'token_pay' => 'Token Pay',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated By',
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

    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
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

    public static function getSumaAbono($credito_id)
    {
        return self::find()->andWhere([ "and",
            ["=", "credito_id", $credito_id ],
            ["=", "status", self::STATUS_ACTIVE ],
        ])->sum("cantidad");
    }

    public static function saveItem($credito_id, $token_pay, $cantidad)
    {
        $CreditoAbono               = new CreditoAbono();
        $CreditoAbono->credito_id   = $credito_id;
        $CreditoAbono->cantidad     = $cantidad;
        $CreditoAbono->token_pay    = $token_pay;
        $CreditoAbono->save();
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
