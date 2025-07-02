<?php
namespace app\modules\v1\controllers;

use Yii;
use app\models\Esys;
use app\models\user\User;
use app\models\cliente\Cliente;
use app\models\cliente\ViewCliente;
use app\models\esys\EsysDireccion;

class ClienteController extends DefaultController
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

    public function actionGetCliente()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $token          = $this->authToken($post["token"]);
        $search_cliente = isset($post["search_cliente"]) ? $post["search_cliente"] : null;

        $cliente = ViewCliente::getClienteAjax($search_cliente);

        return [
            "code" => 202,
            "name" => "Cliente",
            "clientes" => $cliente,
            "type" => "Success",
        ];
    }

    public function actionPostCreate()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $token      = $this->authToken($post["token"]);
        $nombre         = isset($post["nombre"]) ? $post["nombre"] : null;
        $apellidos      = isset($post["apellidos"]) ? $post["apellidos"] : null;
        $telefono_movil = isset($post["telefono_movil"]) ? $post["telefono_movil"] : null;
        $telefono       = isset($post["telefono"]) ? $post["telefono"] : null;
        $email          = isset($post["email"]) ? $post["email"] : null;

        if ($nombre && $apellidos && $telefono_movil) {
            # code...
            $model      = new Cliente();


            $model->dir_obj = new EsysDireccion([
                'cuenta' => EsysDireccion::CUENTA_CLIENTE,
                'tipo'   => EsysDireccion::TIPO_PERSONAL,
            ]);

            $model->nombre      = $nombre;
            $model->apellidos   = $apellidos;
            $model->email       = $email;
            $model->telefono_movil = $telefono_movil;
            $model->telefono    = $telefono;
            if ($model->save()) {
                return [
                    "code" => 202,
                    "name" => "Cliente",
                    "cliente" => [
                        "id" => $model->id,
                        "text" => $model->nombre ." [ Tel: ". $model->telefono_movil ."]",
                        "nombre" => $model->nombre,
                        "apellidos" => $model->apellidos,
                        "email" => $model->email,
                        "telefono" => $model->telefono,
                        "telefono_movil" =>$model->telefono_movil,
                    ],
                    "type" => "Success",
                ];
            }
            return [
                "code" => 10,
                "name" => "Cliente",
                "message" => "Ocurrio un error, intenta nuevamente",
                "type" => "Error",
            ];
        }
        return [
            "code" => 10,
            "name" => "Cliente",
            "message" => "Verifica tu información, intenta nuevamente",
            "type" => "Error",
        ];

    }
}
?>
