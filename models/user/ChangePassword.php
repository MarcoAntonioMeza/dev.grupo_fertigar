<?php
namespace app\models\user;

use Yii;
use yii\base\Model;
use app\rbac\helpers\RbacHelper;
use kartik\password\StrengthValidator;

/**
 * Model representing ChangPassword Form.
 */
class ChangePassword extends Model
{
    public $username;
    public $old_password;
    public $password;
    public $status;

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['password', 'required'],
            ['old_password', 'validatePassword'],
            [['username', 'old_password'], 'string'],
            // use passwordStrengthRule() method to determine password strength
            $this->passwordStrengthRule(),
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

        $user = User::findByUsername($this->username);

        if (!$user || !$user->validatePassword($this->old_password))
            $this->addError($attribute, 'La contraseña actual no es correcta.');
    }


    /**
     * Set password rule based on our setting value ( Force Strong Password ).
     *
     * @return array Password strength rule
     */
    private function passwordStrengthRule()
    {
        // get setting value for 'Force Strong Password'
        $fsp = Yii::$app->params['fsp'];

        // password strength rule is determined by StrengthValidator 
        // presets are located in: vendor/kartik-v/yii2-password/presets.php
        $strong = [['password'], StrengthValidator::className(), 'preset'=>'normal'];

        // use normal yii rule
        $normal = ['password', 'string', 'min' => 6];

        // if 'Force Strong Password' is set to 'true' use $strong rule, else use $normal rule
        return ($fsp) ? $strong : $normal;
    }    

    /**
     * Returns the attribute labels.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'old_password' => 'Contraseña actual',
            'password'     => 'Nueva contraseña',
        ];
    }

    /**
     * Signs up the user.
     * If scenario is set to "rna" (registration needs activation), this means
     * that user need to activate his account using email confirmation method.
     *
     * @return User|null The saved model or null if saving fails.
     */
    public function updatePassword()
    {
        $user = User::findByUsername($this->username);

        $user->scenario = User::SCENARIO_UPDATE_PASSWORD;
        $user->password = $this->password;
        $user->setPassword($user->password);

        $user->save();
    }
}
