<?php
namespace app\models\user;

use Yii;
use yii\base\Model;
use app\models\user\User;

/**
 * Password reset request form.
 */
class PasswordResetRequestForm extends Model
{
    public $email;

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => '\app\models\user\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'Wrong email.'
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool Whether the email was send.
     */
    public function sendEmail()
    {
        /* @var $user User */
        $user = User::findOne(['status' => User::STATUS_ACTIVE, 'email' => $this->email]);

        if (!$user) {
            return false;
        }

        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
        }

        if (!$user->save()) {
            return false;
        }

        return Yii::$app->mailer->compose('passwordResetToken', ['user' => $user])
                                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                                ->setTo($this->email)
                                ->setSubject('Cambiar contraseña ' . Yii::$app->name)
                                ->send();
    }
}
