<?php
namespace app\modules\v1\controllers;

use Yii;
use yii\db\Query;
use yii\db\Expression;
use app\models\sucursal\Sucursal;

class SucursalController extends DefaultController
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



  	/*****************************************
     *  SUCURSAL GET SUCURSAL
    *****************************************/
    public function actionGetSucursal()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user           = $this->authToken($post["token"]);
        $Sucursal       = Sucursal::getItems();
        $ResponseArray  = [];

        foreach ($Sucursal as $key => $sucursal) {
            array_push($ResponseArray, [
                "id" => $key,
                "sucursal" => $sucursal,
            ]);
        }

        return [
            "code"    => 202,
            "name"    => "Sucursal",
            "sucursal" => $ResponseArray,
            "type"    => "Success",
        ];
    }


    /*****************************************
     *  SUCURSAL GET SUCURSAL
    *****************************************/
    public function actionGetRuta()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user           = $this->authToken($post["token"]);
        $Sucursal       = Sucursal::getRuta();
        $ResponseArray  = [];

        foreach ($Sucursal as $key => $sucursal) {
            array_push($ResponseArray, [
                "id" => $key,
                "sucursal" => $sucursal,
            ]);
        }

        return [
            "code"    => 202,
            "name"    => "Sucursal",
            "sucursal" => $ResponseArray,
            "type"    => "Success",
        ];
    }
}
