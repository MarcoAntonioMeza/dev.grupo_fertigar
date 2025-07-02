<?php
namespace app\modules\v1\controllers;

use Yii;
use yii\rest\ActiveController;;
use yii\filters\auth\HttpBasicAuth;
use app\models\auth\AuthRest;

class DefaultActiveController extends ActiveController
{
    public $paquete;

    public function init()
    {
        parent::init();

        Yii::$app->user->enableSession = false;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::className(),
            'auth'  => [$this, 'auth'],
        ];

        return $behaviors;
    }

    public function auth($username, $password)
    {
        return AuthRest::auth($username, $password);
    }

    public function authToken($token)
    {
        $this->paquete = AuthRest::authToken($token);

        return $this->paquete;
    }

}
