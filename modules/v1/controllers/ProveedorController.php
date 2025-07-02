<?php
namespace app\modules\v1\controllers;

use Yii;
use yii\db\Query;
use yii\db\Expression;
use app\models\proveedor\Proveedor;

class ProveedorController extends DefaultController
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
     *  SUCURSAL GET PROVEEDOR
    *****************************************/
    public function actionGetProveedor()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user           = $this->authToken($post["token"]);
        $Proveedor       = Proveedor::getItems();
        $ResponseArray  = [];

        foreach ($Proveedor as $key => $proveedor) {
            array_push($ResponseArray, [
                "id" => $key,
                "proveedor" => $proveedor,
            ]);
        }

        return [
            "code"    => 202,
            "name"    => "Proveedor",
            "proveedor" => $ResponseArray,
            "type"    => "Success",
        ];
    }


    /*****************************************
     *  SUCURSAL GET PROVEEDOR
    *****************************************/

    public function actionPostCreate()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $token      = $this->authToken($post["token"]);
        $nombre         = isset($post["nombre"]) ? $post["nombre"] : null;
        $telefono_movil = isset($post["telefono_movil"]) ? $post["telefono_movil"] : null;
        $email          = isset($post["email"]) ? $post["email"] : null;
        $descripcion          = isset($post["descripcion"]) ? $post["descripcion"] : null;

        if ($nombre && $telefono_movil) {
            # code...
            $model      = new Proveedor();

            $model->nombre      = $nombre;
            $model->tel         = $telefono_movil;
            $model->email       = $email;
            $model->descripcion = $descripcion;
            $model->created_by  = $token->id;
            $model->status      = Proveedor::STATUS_ACTIVE;
            if ($model->save()) {
                return [
                    "code" => 202,
                    "name" => "Proveedor",
                    "cliente" => [
                        "id" => $model->id,
                        "text" => $model->nombre ." [ Tel: ". $model->tel ."]",
                        "nombre" => $model->nombre,
                        "email" => $model->email,
                        "telefono_movil" =>$model->tel,
                    ],
                    "type" => "Success",
                ];
            }
            return [
                "code" => 10,
                "name" => "Proveedor",
                "message" => "Ocurrio un error, intenta nuevamente",
                "type" => "Error",
            ];
        }
        return [
            "code" => 10,
            "name" => "Proveedor",
            "message" => "Verifica tu información, intenta nuevamente",
            "type" => "Error",
        ];

    }
}
