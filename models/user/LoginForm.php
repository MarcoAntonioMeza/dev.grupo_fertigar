<?php
namespace app\models\user;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $rememberMe = true;
    public $status; // holds the information about user status

    /**
     * @var \app\models\user\User
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
            ['email', 'email'],
            ['password', 'validatePassword'],
            ['rememberMe', 'boolean'],

            [['email', 'password'], 'required', 'on' => 'e'],
            [['username', 'password'], 'required', 'on' => 'u'],
            [['username', 'password'], 'required', 'on' => 'eu'],
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
        if ($this->hasErrors()) {
            return false;
        }

        $user = $this->getUser();

        if (!$user || !$user->validatePassword($this->password)) {
            // if scenario is 'lwe' we use email, otherwise we use username
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
            'username'   => 'Nombre de usuario',
            'password'   => 'Contraseña',
            'email'      => 'Correo electrónico',
            'rememberMe' => 'Recuérdame',
        ];
    }

    /**
     * Logs in a user using the provided username|email and password.
     *
     * @return bool Whether the user is logged in successfully.
     */
    public function login($is_app = false, $get_user = false)
    {
        if (!$is_app) {
            if (!$this->validate()) {
                return false;
            }
        }

        $user = $this->getUser();

        if (!$user) {
            return false;
        }

        // if there is user but his status is inactive, write that in status property so we know for later
        if ($user->status == User::STATUS_INACTIVE) {
            $this->status = $user->status;
            return false;
        }
 
        return Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 30 : 0);
    }

    /**
     * Helper method responsible for finding user based on the model scenario.
     * In Login With Email 'lwe' scenario we find user by email, otherwise by username
     * 
     * @return object The found User object.
     */
    private function findUser()
    {
        switch ($this->scenario) {
            case 'u':
                return User::findByUsername($this->username);

            case 'e':
                return $this->_user = User::findByEmail($this->email);

            case 'eu':
                $this->_user = User::findByUsername($this->username);
                return !$this->_user? User::findByEmail($this->username): $this->_user;
        }
    }

    /**
     * Method that is returning User object.
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = $this->findUser();
        }

        return $this->_user;
    }
}
