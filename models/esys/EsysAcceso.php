<?php
namespace app\models\esys;

use Yii;
use app\models\user\User;

/**
 * This is the model class for table "esys_acceso".
 *
 * @property int $id Id
 * @property string $user Nombre de usuario
 * @property string $wrong_password Password erroneo
 * @property int $user_id Usuario
 * @property string $ip IP cliente
 * @property int $access_login Hora de acceso
 * @property int $access_logout Cierre de sesión
 * @property int $access Acceso concedido
 *
 * @property User $user0
 */
class EsysAcceso extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'esys_acceso';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user', 'access_login', 'access'], 'required'],
            [['user_id', 'access_login', 'access_logout', 'access'], 'integer'],
            [['user', 'wrong_password'], 'string', 'max' => 100],
            [['ip'], 'string', 'max' => 20],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'user' => 'Nombre de usuario',
            'wrong_password' => 'Password erroneo',
            'user_id' => 'Usuario',
            'ip' => 'IP cliente',
            'access_login' => 'Hora de acceso',
            'access_logout' => 'Cierre de sesión',
            'access' => 'Acceso concedido',
        ];
    }


//------------------------------------------------------------------------------------------------//
// RELACIONES
//------------------------------------------------------------------------------------------------//
    /*
    public function getUser0()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }*/

}
