<?php
namespace app\models\user;

use Yii;
use yii\base\Model;

/**
 * LoginApi is the model behind the login form.
 */
class LoginApi extends Model
{
    public $username;
    public $password;
    public $status;

    /**
     * @var \app\models\cliente\Cliente
     */
    private $_user = false;

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['password'], 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute The attribute currently being validated.
     * @param array  $params    The additional name-value pairs.
     */
    public function validatePassword($attribute, $params)
    {
        if($this->hasErrors())
            return false;

        $user = $this->getUser();

        if (!$user || !$user->validatePassword($this->password)) {
            $field = ($this->scenario === 'e') ? 'Correo electrónico' : 'Nombre de usuario' ;

            $this->addError($attribute, $field . ' o contraseña incorrectos.');
        }
    }

    /**
     * Returns the attribute labels.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Nombre de usuario',
            'password'     => 'Contraseña',
        ];
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool Whether the user is logged in successfully.
     */
    public function login()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->getUser();


        if (!$user) {
            return false;
        }

        // if there is user but his status is inactive, write that in status property so we know for later
        if ($user->status == User::STATUS_INACTIVE || $user->api_enabled != User::API_ACTIVE) {
            $this->status = $user->status;
            return false;
        }

        if (!$user->token) {
            $user->token = Yii::$app->security->generateRandomString();
            $user->update();
        }

        return Yii::$app->user->login($user, 0);
    }

    /**
     * Helper method responsible for finding user based on the model scenario.
     *
     * @return object The found User object.
     */
    private function findUser()
    {
        return User::findByUsername($this->username);
    }

    /**
     * Method that is returning User object.
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false)
            $this->_user = $this->findUser();

        return $this->_user;
    }
}
