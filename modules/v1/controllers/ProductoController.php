<?php
namespace app\modules\v1\controllers;

use Yii;
use yii\db\Query;
use yii\helpers\Url;
use yii\db\Expression;
use app\models\user\User;
use app\models\venta\Venta;
use app\models\venta\TransVenta;
use app\models\producto\Producto;
use app\models\producto\ViewProducto;
use app\models\inv\InvProductoSucursal;
use app\models\esys\EsysListaDesplegable;

class ProductoController extends DefaultController
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
     *  PRODUCTO GET PRODUCTO NAME
    *****************************************/
    public function actionGetProducto()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user           = $this->authToken($post["token"]);
        $producto_name   = isset($post["producto_name"]) ? $post["producto_name"] : null;

        $user_get = User::findOne($user->id);

        $isCompra = isset($post["is_compra"]) && $post["is_compra"] == 10 ? true  : false;


        if ($producto_name) {
            $Productod = ViewProducto::getProductoSeachAjax($producto_name);
            $responseArray = [];
            foreach ($Productod as $key => $producto) {

                $fecha_rango_1 = \DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d",time())."03:00:00");
                $fecha_rango_2 = \DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d",time())."07:00:00");
                $fecha_rango_3 = \DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d",time())."07:00:00");
                $fecha_rango_4 = \DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d",time())."10:00:00");

                $tipo_venta = Venta::TIPO_GENERAL;
                if (time() >= $fecha_rango_1->format('U') && time() <  $fecha_rango_2->format('U') )
                    $tipo_venta = Venta::TIPO_MAYOREO;

                if (time() >= $fecha_rango_3->format('U') && time() <  $fecha_rango_4->format('U') )
                    $tipo_venta = Venta::TIPO_MENUDEO;

                $precio_venta = $producto["precio_publico"];

                if ($tipo_venta == Venta::TIPO_MAYOREO )
                    $precio_venta = $producto["precio_mayoreo"] ? $producto["precio_mayoreo"] : $precio_venta;

                if ($tipo_venta == Venta::TIPO_MENUDEO )
                    $precio_venta = $producto["precio_menudeo"] ? $producto["precio_menudeo"] : $precio_venta;

                $existencia = 0;
                if ($user_get->sucursal_id) {

                    $InvProductoSucursal = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , $user_get->sucursal_id ],[ "=", "producto_id" , $producto["id"] ] ])->one();

                    if (isset($InvProductoSucursal->id))
                      $existencia = $InvProductoSucursal->cantidad;
                }

                 $sub_existencia = 0;

                if ($producto["is_subproducto"] == Producto::TIPO_SUBPRODUCTO) {
                    $SubInvProductoSucursal = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , $user_get->sucursal_id  ],[ "=", "producto_id" , $producto["sub_producto_id"] ] ])->one();

                    if (isset($SubInvProductoSucursal->id))
                      $sub_existencia = $SubInvProductoSucursal->cantidad;
                }

                if ($isCompra) {
                    array_push($responseArray, [
    					"id"        => $producto["id"],
    					"clave"       => $producto["clave"],
    					"avatar"      => $producto["avatar"] ? Url::to('@web/uploads/', "https") . $producto["avatar"] : null,
    					"nombre"      => $producto["nombre"],
    					"text"        => $producto["text"],
    					"descripcion" => $producto["descripcion"],
    					"is_subproducto"          => $producto["is_subproducto"],
    					"sub_cantidad_equivalente" => $producto["sub_cantidad_equivalente"],
    					"sub_producto_id"         => $producto["sub_producto_id"],
    					"sub_producto_nombre"     => $producto["sub_producto_nombre"],
    					"sub_existencia" 	=> $sub_existencia,
    					"tipo"        => $producto["tipo"],
    					"existencia"        => $existencia,
    					"tipo_medida" => $producto["tipo_medida"],
    					"categoria"   => $producto["categoria"],
    					"proveedor"   => null,
    					"costo"       => $producto["costo"],
    					"precio_publico"  => $producto["precio_publico"],
    					"precio_mayoreo"  => $producto["precio_mayoreo"],
    					"precio_menudeo"  => $producto["precio_menudeo"],
    					"precio_venta"    => $precio_venta,
    					"tipo_venta"      => $tipo_venta,
                    ]);
                }else{
                    if ($existencia > 0 ) {
                        array_push($responseArray, [
                            "id"        => $producto["id"],
                            "clave"       => $producto["clave"],
                            "avatar"      => $producto["avatar"] ? Url::to('@web/uploads/', "https") . $producto["avatar"] : null,
                            "nombre"      => $producto["nombre"],
                            "text"        => $producto["text"],
                            "descripcion" => $producto["descripcion"],
                            "is_subproducto"          => $producto["is_subproducto"],
                            "sub_cantidad_equivalente" => $producto["sub_cantidad_equivalente"],
                            "sub_producto_id"         => $producto["sub_producto_id"],
                            "sub_producto_nombre"     => $producto["sub_producto_nombre"],
                            "sub_existencia"    => $sub_existencia,
                            "tipo"        => $producto["tipo"],
                            "existencia"        => $existencia,
                            "tipo_medida" => $producto["tipo_medida"],
                            "categoria"   => $producto["categoria"],
                            "proveedor"   => null,
                            "costo"       => $producto["costo"],
                            "precio_publico"  => $producto["precio_publico"],
                            "precio_mayoreo"  => $producto["precio_mayoreo"],
                            "precio_menudeo"  => $producto["precio_menudeo"],
                            "precio_venta"    => $precio_venta,
                            "tipo_venta"      => $tipo_venta,
                        ]);
                    }
                }


            }
            return [
                "code"    => 202,
                "name"    => "Producto",
                "producto" => $responseArray,
                "type"    => "Success",
            ];
        }
        return [
            "code"    => 10,
            "name"    => "Producto",
            "message" => 'Verifica tu información, intenta nuevamente',
            "type"    => "Error",
        ];
    }

    /*****************************************
     *  PRODUCTO GET PRODUCTO NAME
    *****************************************/
    public function actionGetProductoCedis()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user           = $this->authToken($post["token"]);
        $producto_name   = isset($post["producto_name"]) ? $post["producto_name"] : null;

        $user_get = User::findOne($user->id);

        if ($producto_name) {
            $Productod = ViewProducto::getProductoSeachAjax($producto_name);
            $responseArray = [];
            foreach ($Productod as $key => $producto) {

                $fecha_rango_1 = \DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d",time())."03:00:00");
                $fecha_rango_2 = \DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d",time())."07:00:00");
                $fecha_rango_3 = \DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d",time())."07:00:00");
                $fecha_rango_4 = \DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d",time())."10:00:00");

                $tipo_venta = Venta::TIPO_GENERAL;
                if (time() >= $fecha_rango_1->format('U') && time() <  $fecha_rango_2->format('U') )
                    $tipo_venta = Venta::TIPO_MAYOREO;

                if (time() >= $fecha_rango_3->format('U') && time() <  $fecha_rango_4->format('U') )
                    $tipo_venta = Venta::TIPO_MENUDEO;

                $precio_venta = $producto["precio_publico"];

                if ($tipo_venta == Venta::TIPO_MAYOREO )
                    $precio_venta = $producto["precio_mayoreo"] ? $producto["precio_mayoreo"] : $precio_venta;

                if ($tipo_venta == Venta::TIPO_MENUDEO )
                    $precio_venta = $producto["precio_menudeo"] ? $producto["precio_menudeo"] : $precio_venta;

                $existencia = 0;

                $InvProductoSucursal = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , 3 ],[ "=", "producto_id" , $producto["id"] ] ])->one();

                if (isset($InvProductoSucursal->id))
                  $existencia = $InvProductoSucursal->cantidad;

                $sub_existencia = 0;

                if ($producto["is_subproducto"] == Producto::TIPO_SUBPRODUCTO) {
                    $SubInvProductoSucursal = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , 3  ],[ "=", "producto_id" , $producto["sub_producto_id"] ] ])->one();

                    if (isset($SubInvProductoSucursal->id))
                      $sub_existencia = $SubInvProductoSucursal->cantidad;
                }

                array_push($responseArray, [
                    "id"        => $producto["id"],
                    "clave"       => $producto["clave"],
                    "avatar"      => $producto["avatar"] ? Url::to('@web/uploads/', "https") . $producto["avatar"] : null,
                    "nombre"      => $producto["nombre"],
                    "text"        => $producto["text"],
                    "descripcion" => $producto["descripcion"],
                    "is_subproducto"          => $producto["is_subproducto"],
                    "sub_cantidad_equivalente" => $producto["sub_cantidad_equivalente"],
                    "sub_producto_id"         => $producto["sub_producto_id"],
                    "sub_producto_nombre"     => $producto["sub_producto_nombre"],
                    "sub_existencia"    => $sub_existencia,
                    "tipo"        => $producto["tipo"],
                    "existencia"        => $existencia,
                    "tipo_medida" => $producto["tipo_medida"],
                    "categoria"   => $producto["categoria"],
                    "proveedor"   => null,
                    "costo"       => $producto["costo"],
                    "precio_publico"  => $producto["precio_publico"],
                    "precio_mayoreo"  => $producto["precio_mayoreo"],
                    "precio_menudeo"  => $producto["precio_menudeo"],
                    "precio_venta"    => $precio_venta,
                    "tipo_venta"      => $tipo_venta,
                ]);
            }
            return [
                "code"    => 202,
                "name"    => "Producto",
                "producto" => $responseArray,
                "type"    => "Success",
            ];
        }
        return [
            "code"    => 10,
            "name"    => "Producto",
            "message" => 'Verifica tu información, intenta nuevamente',
            "type"    => "Error",
        ];
    }

    /*****************************************
     *  PRODUCTO POST PRODUCTO ID
    *****************************************/
    public function actionPostProducto()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user           = $this->authToken($post["token"]);
        $Producto["Producto"]["nombre"]         = isset($post["nombre"]) ? $post["nombre"] : null;
        $Producto["Producto"]["descripcion"]    = isset($post["descripcion"]) ? $post["descripcion"] : null;
        $Producto["Producto"]["categoria_id"]   = isset($post["categoria_id"]) ? $post["categoria_id"] : null;
        $Producto["Producto"]["tipo"]           = isset($post["tipo"]) ? $post["tipo"] : null;
        $Producto["Producto"]["tipo_medida"]    = isset($post["tipo_medida"]) ? $post["tipo_medida"] : null;
        $Producto["Producto"]["costo"]          = isset($post["costo"]) ? $post["costo"] : null;


        $ProductoNew = new Producto();

        if ($ProductoNew->load($Producto)) {

            $ProductoNew->clave = Producto::generateClave();
            $ProductoNew->status = Producto::STATUS_ACTIVE;
            $ProductoNew->precio_publico = 0;
            $ProductoNew->precio_mayoreo = 0;
            $ProductoNew->precio_menudeo = 0;
            $ProductoNew->is_app         = Producto::IS_APP_ON;
            $ProductoNew->validate       = Producto::VALIDATE_OFF;
            $ProductoNew->inventariable  = Producto::INV_SI;
            $ProductoNew->created_by     = $user->id;
            if ($ProductoNew->validate()) {
                if ($ProductoNew->save()) {
                    return [
                        "code"    => 202,
                        "name"    => "Producto",
                        "message" => "Se genero correctamente el producto",
                        "producto_id" => $ProductoNew->id,
                        "producto_clave" => $ProductoNew->clave,
                        "type"    => "Success",
                    ];
                }
            }
        }
        return [
            "code"    => 10,
            "name"    => "Producto",
            "message" => 'Verifica tu información, intenta nuevamente',
            "type"    => "Error",
        ];
    }


    /*****************************************
     *  PRODUCTO GET CATEGORIA ID
    *****************************************/
    public function actionGetCategoria()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user      = $this->authToken($post["token"]);

        $Categoria      = EsysListaDesplegable::getItems('producto_categoria');
        $CategoriaObj   = [];
        foreach ($Categoria as $categoria_id => $text) {
            array_push($CategoriaObj, [
                "id" => $categoria_id,
                "nombre" => $text,
            ]);
        }

        return [
            "code"    => 202,
            "name"    => "Producto - Categoria",
            "categoria" => $CategoriaObj,
            "type"    => "Success",
        ];
    }
}