<?php

namespace app\models\user;

use Yii;
use app\models\sucursal\Sucursal;
/**
 * This is the model class for table "user_sucursal".
 *
 * @property int $user_id User ID
 * @property int $sucursal_id Sucursal ID
 *
 * @property Sucursal $sucursal
 * @property User $user
 */
class UserSucursal extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_sucursal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'sucursal_id'], 'required'],
            [['user_id', 'sucursal_id'], 'integer'],
            [['user_id', 'sucursal_id'], 'unique', 'targetAttribute' => ['user_id', 'sucursal_id']],
            [['sucursal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sucursal::className(), 'targetAttribute' => ['sucursal_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'sucursal_id' => 'Sucursal ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSucursal()
    {
        return $this->hasOne(Sucursal::className(), ['id' => 'sucursal_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
