<?php
namespace app\modules\v1\controllers;

use Yii;
use yii\rest\Controller;

use app\models\auth\AuthRest;

class DefaultController extends Controller
{
    public $paquete;

    public function init()
    {
        parent::init();
        Yii::$app->user->enableSession = false;
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
