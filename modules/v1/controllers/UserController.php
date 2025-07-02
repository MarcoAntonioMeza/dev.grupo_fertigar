<?php

namespace app\modules\v1\controllers;

use Yii;
use app\models\cliente\Cliente;
use yii\data\ActiveDataProvider;
use app\models\Esys;
use app\models\user\User;

class UserController extends DefaultController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                // restrict access to
                'Origin' => ['*'],
                // Allow only POST and PUT methods
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                // Allow only headers 'X-Wsse'
                'Access-Control-Request-Headers' => ['*'],
                // Allow credentials (cookies, authorization headers, etc.) to be exposed to the browser
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Allow-Origin' => ['*'],
                // Allow OPTIONS caching
                'Access-Control-Max-Age' => 3600,
                // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
            ],
        ];

        return $behaviors;
    }

    public function actionGetVendedores()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $token  = $this->authToken($post["token"]);

        $vendedores = User::find()->andWhere(["and",["=", "pertenece_a", User::PERTENECE_VENDEDOR ], [ "=", "status", User::STATUS_ACTIVE  ] ])->all();

        $responseArray = [];
        foreach ($vendedores as $key => $item) {
            array_push($responseArray, [
                'token'       => $item->token,
                'email'       => $item->email,
                'username'    => $item->username,
                'nombre'      => $item->nombre,
                'apellidos'   => $item->apellidos,
                'email'       => $item->email,
                'sexo'        => $item->sexo,
                'pertenece_a' => $item->pertenece_a,
                'telefono'    => $item->telefono,
            ]);
        }

        return [
            "code" => 202,
            "name" => "User",
            "vendedores" => $responseArray,
            "type" => "Success",
        ];
    }

    public function actionMe()
    {
    	$post = Yii::$app->request->post();
		// Validamos Token
        $token  = $this->authToken($post["token"]);


        return [
        	"code" => 202,
        	"name" => "User",
        	"data" => Cliente::find()->where(["id" => $paquete->cliente_id ])->asArray()->one(),
        	"type" => "Success",
        ];
    }
}
?>
