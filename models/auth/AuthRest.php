<?php
namespace app\models\Auth;

use Yii;
use yii\base\Model;
use app\models\user\User;
use app\models\user\LoginApi;
use app\models\cliente\ClientePaquete;

class AuthRest extends Model
{
	public static function auth($username, $password)
	{
        $model = new LoginApi();

        $successfulLogin = true;

		if(!$model->load(['LoginApi' => [
			'username' => $username,
			'password'     => $password,
		]]))
		    $successfulLogin = false;

		if(!$model->login())
		    $successfulLogin = false;

        if($successfulLogin)
            return User::findOne(['username' => $username]);
	}

    public static function authToken($token)
    {
        $User = User::find()
        ->select([
            'id',
            'api_enabled',
            'status',
        ])
        ->where([
          'token'      => $token,
        ])->one();


        // No se encontro Token considente
        if (!$User)
	        throw new \yii\web\HttpException(202, 'Token invalido o inexistente.', 10);


        // Paquete Suspendido
        if($User['status'] != User::STATUS_ACTIVE)
            throw new \yii\web\HttpException(202, 'El usuario se encuentra Suspendido.', 11);

        if($User['api_enabled'] != User::API_ACTIVE)
            throw new \yii\web\HttpException(202, 'El usuario no tiene acceso al servicio de API RESTFULL.', 11);

        // El paquete esta habilitado y cuenta con créditos
        return $User;
    }
}
