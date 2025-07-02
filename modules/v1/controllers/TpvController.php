<?php
namespace app\modules\v1\controllers;

use app\components\AuditLogger;
use Yii;
use app\models\venta\Venta;
use yii\helpers\Url;
use app\models\venta\VentaDetalle;
use app\models\venta\ViewVenta;
use app\models\venta\TransVenta;
use app\models\cobro\CobroVenta;
use app\models\credito\Credito;
use app\models\inv\InvProductoSucursal;
use app\models\user\User;
use app\models\producto\Producto;
use app\models\inv\ViewInventario;
use app\models\reparto\Reparto;
use app\models\inv\Operacion;
use app\models\venta\VentaTokenPay;
use app\models\temp\TempVentaRuta;
use app\models\temp\TempVentaRutaDetalle;
use app\models\temp\TempCredito;
use app\models\temp\TempCobroRutaVenta;
use app\models\temp\TempVentaTokenPay;
use app\models\trans\TransProductoInventario;


class TpvController extends DefaultController
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


    public function actionPostPreCaptura()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);
        $cliente_id         = isset($post["cliente_id"]) ? $post["cliente_id"] : null;
        $total              = isset($post["total"]) ? $post["total"] : null;
        $tipo               = isset($post["tipo"]) ? $post["tipo"] : null;
        $venta_detalle      = isset($post["venta_detalle"]) ? $post["venta_detalle"] : null;
        $getUser            = User::findOne($user->id);


        if (count($venta_detalle) > 0  && $tipo) {
            $valid = Operacion::validateOperacionApp($venta_detalle,$getUser->sucursal_id);
            if (empty($valid)) {

                $response = Venta::savePreventaComanda($cliente_id, $total, $tipo, $user->id, $getUser->sucursal_id, $venta_detalle);

                return $response;

                /*$venta              = new Venta();
                $venta->cliente_id  = $cliente_id;
                $venta->total       = $total;
                $venta->tipo        = $tipo;
                $venta->status      = Venta::STATUS_PREVENTA;
                $venta->is_especial = Venta::VENTA_GENERAL;
                $venta->created_by  = $user->id;
                $venta->sucursal_id = $getUser->sucursal_id;
                if ($venta->save()) {

                    foreach ($venta_detalle as $key => $v_detalle) {
                        $VentaDetalle = new VentaDetalle();
                        $VentaDetalle->venta_id     = $venta->id;
                        $VentaDetalle->producto_id  = $v_detalle["producto_id"];
                        $VentaDetalle->cantidad     = floatval($v_detalle["cantidad"]);
                        $VentaDetalle->precio_venta = $v_detalle["precio_venta"];
                        $VentaDetalle->created_by   = $user->id;
                        $VentaDetalle->save();

                    }

                    return [
                        "code" => 202,
                        "name" => "Tpv",
                        "folio" => $venta->id,
                        "type" => "Success",
                    ];
                }

                return [
                    "code" => 10,
                    "name" => "Tpv",
                    "message" => "Ocurrio un error, intenta nuevamente",
                    "type" => "Error",
                ];*/


            }else{
                $text = "";
                foreach ($valid as $key => $error_message) {
                    $text = $text ."  ". $error_message["producto"] . " - ";
                }

                return [
                    "code" => 10,
                    "name" => "Surtir",
                    "message" => "No cuentas con el suficiente inventario, revisa el stock de los producto(s) : [" . $text . "]",
                    "type" => "Error",
                ];
            }
        }

        return [
            "code" => 10,
            "name" => "Tpv",
            "message" => "Verifica tu información, intenta nuevamente",
            "type" => "Error",
        ];

    }

    public function actionGetStatusPreventa()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user     = $this->authToken($post["token"]);
        $venta_id = isset($post["preventa_id"]) && $post["preventa_id"] ? $post["preventa_id"] : null;

        if ($venta_id){
            $venta      = Venta::findOne($venta_id);
            $ventaArray =  [];
            if (isset($venta->id)) {
                $ventaArray = [
                        "id"                => $venta->id,
                        "cliente_id"        => $venta->cliente_id,
                        "cliente"           => isset($venta->cliente->id) ?  $venta->cliente->nombreCompleto : null,
                        "tipo"              => $venta->tipo,
                        "status"            => $venta->status,
                        "total"             => $venta->total,
                        "venta_detalle"     => [],
                        "created_by_user"   => $venta->createdBy->nombreCompleto,
                        "created_by"        => $venta->created_by,
                ];

                foreach ($venta->ventaDetalle as $key2 => $item2) {

                    array_push($ventaArray["venta_detalle"], [
                        "detail_id"     => $item2->id,
                        "producto_id"   => $item2->producto_id,
                        "nombre"      => isset($item2->producto->id) ? $item2->producto->nombre : null,
                        "cantidad"      => $item2->cantidad,
                        "precio_venta"  => $item2->precio_venta,
                        "sub_total"     => round(floatval($item2->precio_venta) * floatval($item2->cantidad),2),
                        "costo"         => isset($item2->producto->id) ? $item2->producto->costo : 0 ,
                        "sucursal_id"   => $item2->sucursal_id,
                        "status"        => $item2->status,
                    ]);
                }

                return [
                    "code" => 202,
                    "name" => "Tpv",
                    "preventa" => $ventaArray,
                    "type" => "Success",
                ];
            }
        }

        return [
            "code" => 10,
            "name" => "Tpv",
            "message" => "Ocurrio un error, ingresa correctamente el folio de la venta",
            "type" => "Success",
        ];
    }


    public function actionPostAprovedPreventa()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);
        $preventa_id        = isset($post["preventa_id"]) ? $post["preventa_id"] : null;
        $total              = isset($post["total"]) ? $post["total"] : null;
        $venta_detalle      = isset($post["venta_detalle"]) ? $post["venta_detalle"] : null;
        $getUser            = User::findOne($user->id);


        if (count($venta_detalle) > 0) {
            $response = Venta::aprovedPreventaComanda($preventa_id, $total,  $user->id, $getUser->sucursal_id, $venta_detalle);

            return $response;
        }

        return [
            "code" => 10,
            "name" => "Tpv",
            "message" => "Verifica tu información, intenta nuevamente",
            "type" => "Error",
        ];

    }

    public function actionPostCancelacionPreventa()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);
        $preventa_id        = isset($post["preventa_id"]) ? $post["preventa_id"] : null;

        if ($preventa_id) {

            $response = Venta::cancelacionPreventa($preventa_id, $user->id );
            return $response;

        }else{
            return [
                "code"      => 10,
                "message"   => "Ocurrio un error, intenta nuevamente",
            ];
        }
    }

    public function actionGetPrePedido()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);
        $search_cliente     = isset($post["search_cliente"]) && $post["search_cliente"] ? $post["search_cliente"] : null;

        if ($search_cliente)
            $ventas = Venta::find()->innerJoin("cliente","venta.cliente_id = cliente.id")
                        ->andWhere([
                            "and",
                            ["=","venta.status", Venta::STATUS_PREVENTA],
                            ["like","cliente.nombre", $search_cliente ]
                        ])->all();
        else
            $ventas = Venta::find()->andWhere([ "status" => Venta::STATUS_PREVENTA ])->all();

        $user_get = User::findOne($user->id);

        $responseArray = [];

        foreach ($ventas as $key => $item) {
            array_push($responseArray, [
                "id" => $item->id,
                "cliente_id" => $item->cliente_id,
                "cliente" => isset($item->cliente->id) ?  $item->cliente->nombreCompleto : null,
                "tipo" =>   $item->tipo,
                "status" =>   $item->status,
                "v_detalle" => [],
                "total" =>   $item->total,
                "created_by_user" =>   $item->createdBy->nombreCompleto,
                "created_by" =>   $item->created_by,
            ]);
        }

        foreach ($responseArray as $key => $item) {
            $VentaDetalle = VentaDetalle::find()->andWhere([ "venta_id" => $item["id"] ])->all();

                 foreach ($VentaDetalle as $key2 => $item_detalle) {
                     $existencia = 0;

                    if ($user_get->sucursal_id) {

                        $InvProductoSucursal = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , $user_get->sucursal_id ],[ "=", "producto_id" , $item_detalle->producto_id ] ])->one();

                        if (isset($InvProductoSucursal->id))
                          $existencia = $InvProductoSucursal->cantidad;
                    }

                     $sub_existencia = 0;

                    if ($item_detalle->producto->is_subproducto == Producto::TIPO_SUBPRODUCTO) {
                        $SubInvProductoSucursal = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , $user_get->sucursal_id  ],[ "=", "producto_id" , $item_detalle->producto->sub_producto_id ] ])->one();

                        if (isset($SubInvProductoSucursal->id))
                          $sub_existencia = $SubInvProductoSucursal->cantidad;
                    }

                    array_push($responseArray[$key]["v_detalle"], [
                        "envio_detalle_id"  => $item_detalle->id,
                        "producto_id"       => $item_detalle->producto_id,
                        "avatar"            => isset($item_detalle->producto->avatar) ? $item_detalle->producto->avatar : null,
                        "tipo_medida_id"    => isset($item_detalle->producto->tipo_medida) ? $item_detalle->producto->tipo_medida : null,
                        "tipo_medida_text"  => isset($item_detalle->producto->tipo_medida) ? Producto::$medidaList[$item_detalle->producto->tipo_medida] : null,
                        "tipo_venta"  => isset($item_detalle->venta->tipo) ? $item_detalle->venta->tipo : null,
                        "producto"          => isset($item_detalle->producto->id) ? $item_detalle->producto->nombre : null,
                        "cantidad"          => $item_detalle->cantidad,
                        "sub_cantidad_equivalente"  => $item_detalle->producto->sub_cantidad_equivalente,
                        "sub_producto_id"           => $item_detalle->producto->sub_producto_id,
                        "sub_producto_nombre"       => isset($item_detalle->producto->subProducto->nombre) ? $item_detalle->producto->subProducto->nombre : null,
                        "sub_existencia"    => $sub_existencia,
                        "tipo"              => $item_detalle->producto->tipo,
                        "existencia"        => $existencia,
                        "costo"             => isset($item_detalle->producto->id) ? $item_detalle->producto->costo : 0 ,
                        "precio_venta"      => $item_detalle->precio_venta,
                    ]);
                 }

        }


        return [
            "code" => 202,
            "name" => "Tpv",
            "ventas" => $responseArray,
            "type" => "Success",
        ];
    }


    public function actionGetHistoryVentaReparto()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);
        $reparto_id     = isset($post["reparto_id"]) && $post["reparto_id"] ? $post["reparto_id"] : null;

        if ($reparto_id){
            $ventas = Venta::find()->andWhere([ "and",
                ["=","venta.reparto_id", $reparto_id ],
                ["=","venta.status", Venta::STATUS_VENTA ],
            ])->all();


            $responseArray = [];

            
            foreach ($ventas as $key => $item) {
                $applyRegistro = true;
                foreach ($responseArray as $key_response_venta => $item_venta) {
                    if( $item_venta["cliente_id"] == $item->cliente_id ){
                        $applyRegistro = false;
                        $responseArray[$key_response_venta]["folio"] = $item_venta["folio"] .' '. str_pad($item->id,6,"0",STR_PAD_LEFT) ." ";
                        array_push($responseArray[$key_response_venta]["ids"], $item->id );
                        $responseArray[$key_response_venta]["total"] = round($item_venta["total"] + $item->total,2);
                    }                        
                }

                if ($applyRegistro){
                    array_push($responseArray, [
                        "id"                => $item->id,
                        "ids"               => [$item->id],
                        "folio"             => str_pad($item->id,6,"0",STR_PAD_LEFT),
                        "cliente_id"        => $item->cliente_id,
                        "cliente"           => isset($item->cliente->id) ?  $item->cliente->nombreCompleto : "** PUBLICO EN GENERAL **",
                        "tipo"              => $item->tipo,
                        "status"            => $item->status,
                        "v_detalle"         => [],
                        "p_detalle"         => [],
                        "total"             => $item->total,
                        "created_by_user"   => $item->createdBy->nombreCompleto,
                        "created_by"        => $item->created_by,
                        "fecha_registro"    => date("Y-m-d h:i:s", $item->created_at),
                    ]);
                }
            }

            foreach ($responseArray as $key_venta => $item_venta) {
                $getModelVenta = Venta::findOne($item_venta["id"]);
                if ($getModelVenta->transaccion) {
                    foreach ($getModelVenta->transaccion as $key => $item_transaccion) {
                        $VentaTokenPay = VentaTokenPay::findOne($item_transaccion->id )->token_pay;
                    }
                    
                    $cobroTpvVenta      = CobroVenta::find()->andWhere([ "trans_token_venta" => $VentaTokenPay ])->all();
                    
                    foreach ($cobroTpvVenta as $key3 => $c_detalle) {
                        array_push($responseArray[$key_venta]['p_detalle'], [
                            "metodo_pago"  => CobroVenta::$servicioList[$c_detalle->metodo_pago],
                            "cantidad"     => $c_detalle->cantidad,
                        ]);
                    }
                }
            }

            foreach ($responseArray as $key_detalle => $item) {
                $VentaDetalle = VentaDetalle::find()->andWhere([ "venta_id" => $item["ids"] ])->all();

                foreach ($VentaDetalle as $key3 => $item_detalle) {
                    array_push($responseArray[$key_detalle]["v_detalle"], [
                        "envio_detalle_id"  => $item_detalle->id,
                        "producto_id"       => $item_detalle->producto_id,
                        "avatar"            => isset($item_detalle->producto->avatar) ?  Url::to('@web/uploads/', "https") . $item_detalle->producto->avatar   : null,
                        "tipo_medida_id"    => isset($item_detalle->producto->tipo_medida) ? $item_detalle->producto->tipo_medida : null,
                        "tipo_medida_text"  => isset($item_detalle->producto->tipo_medida) ? Producto::$medidaList[$item_detalle->producto->tipo_medida] : null,
                        "tipo_venta"  => isset($item_detalle->venta->tipo) ? $item_detalle->venta->tipo : null,
                        "producto"          => isset($item_detalle->producto->id) ? $item_detalle->producto->nombre : null,
                        "producto_clave"    => isset($item_detalle->producto->id) ? $item_detalle->producto->clave : null,
                        "cantidad"          => $item_detalle->cantidad,
                        "tipo"              => $item_detalle->producto->tipo,
                        "costo"             => isset($item_detalle->producto->id) ? $item_detalle->producto->costo : 0 ,
                        "precio_venta"      => $item_detalle->precio_venta,
                    ]);
                }
            }

            foreach ($responseArray as $key => $item) {
                $CobroVenta = CobroVenta::find()->andWhere([ "venta_id" => $item["id"] ])->all();
                foreach ($CobroVenta as $key3 => $c_detalle) {
                    array_push($responseArray[$key]["p_detalle"], [
                        "metodo_pago"  => CobroVenta::$servicioList[$c_detalle->metodo_pago],
                        "cantidad"     => $c_detalle->cantidad,
                    ]);
                }
            }

            return [
                "code" => 202,
                "name" => "Tpv",
                "ventas" => $responseArray,
                "type" => "Success",
            ];
        }

        return [
            "code" => 10,
            "name" => "Tpv",
            "ventas" => "Ocurrio un error, intenta nuevamente",
            "type" => "Success",
        ];
    }


    public function actionGetVenta()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);
        $venta_id     = isset($post["venta_id"]) && $post["venta_id"] ? $post["venta_id"] : null;

        if ($venta_id){

            $venta = Venta::findOne($venta_id);

            $ventaArray =  [];

            if (isset($venta->id)) {
                $ventaArray = [
                        "id" => $venta->id,
                        "cliente_id"    => $venta->cliente_id,
                        "cliente"       => isset($venta->cliente->id) ?  $venta->cliente->nombreCompleto : null,
                        "tipo"          =>   $venta->tipo,
                        "status"        =>   $venta->status,
                        "total"         =>   $venta->total,
                        "v_detalle" => [],
                        "created_by_user" =>   $venta->createdBy->nombreCompleto,
                        "created_by"    =>   $venta->created_by,
                ];

                foreach ($venta->ventaDetalle as $key2 => $item2) {
                    array_push($ventaArray["v_detalle"], [
                        "v_detalle_id" => $item2->id,
                        "producto_id"   => $item2->producto_id,
                        "producto_clave"   => isset($item2->producto->id) ? $item2->producto->clave : null,
                        "producto"      => isset($item2->producto->id) ? $item2->producto->nombre : null,
                        "cantidad"      => $item2->cantidad,
                        "precio_venta"  => $item2->precio_venta,
                        "costo"         => isset($item2->producto->id) ? $item2->producto->costo : 0 ,
                    ]);
                }

                return [
                    "code" => 202,
                    "name" => "Tpv",
                    "venta" => $ventaArray,
                    "type" => "Success",
                ];
            }
        }

        return [
            "code" => 10,
            "name" => "Tpv",
            "message" => "Ocurrio un error, ingresa correctamente el folio de la venta",
            "type" => "Success",
        ];
    }

    public function actionGetPreCaptura()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);

        $user_get = User::findOne($user->id);

        /** GET CLIENTES CON PEDIDOS  **/
        $cliente_pedidos = Venta::find()->andWhere([ "and",
            [ "=", "status", Venta::STATUS_PROCESO ],
            [ "=", "ruta_sucursal_id", $user_get->sucursal_id]
        ])->groupBy("cliente_id")->all();


        $responseArray = [];


        /** GET PEDIDOS DE CLIENTES **/
        foreach ($cliente_pedidos as $key_cliente => $item_cliente) {

            array_push($responseArray, [
                "cliente_id"    => $item_cliente->cliente_id,
                "cliente"       => isset($item_cliente->cliente->id) ?  $item_cliente->cliente->nombreCompleto : null,
                "v_detalle"     => [],
                "total"         => 0,
            ]);

            $ventas = Venta::find()->andWhere([ "and",
                [ "=", "status", Venta::STATUS_PROCESO ],
                [ "=", "ruta_sucursal_id", $user_get->sucursal_id],
                [ "=", "cliente_id", $item_cliente->cliente_id],
            ])->all();

            foreach ($ventas as $key_pedido => $item_pedido) {
                $VentaDetalle = VentaDetalle::find()->andWhere([ "venta_id" => $item_pedido->id ])->all();

                foreach ($VentaDetalle as $key2 => $item_detalle) {
                    $existencia = 0;
                    if ($user_get->sucursal_id) {

                        $InvProductoSucursal = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , $user_get->sucursal_id ],[ "=", "producto_id" , $item_detalle->producto_id ] ])->one();

                        if (isset($InvProductoSucursal->id))
                          $existencia = $InvProductoSucursal->cantidad;
                    }

                    array_push($responseArray[$key_cliente]["v_detalle"], [
                        "preventa_id"       => $item_detalle->venta_id,
                        "folio_venta"       => "#".str_pad($item_detalle->venta_id,6,"0",STR_PAD_LEFT),
                        "envio_detalle_id"  => $item_detalle->id,
                        "sucursal_origen"  => isset($item_pedido->sucursal_id) ?  $item_pedido->sucursalVende->nombre : null,
                        "sucursal_entrega"  => isset($item_pedido->ruta_sucursal_id ) ?  $item_pedido->sucursal->nombre : null,
                        "producto_id"   => $item_detalle->producto_id,
                        "producto_clave"   => isset($item_detalle->producto->id) ? $item_detalle->producto->clave : null,
                        "producto"      => isset($item_detalle->producto->id) ? $item_detalle->producto->nombre : null,
                        "cantidad"      => $item_detalle->cantidad,
                        "precio_venta"  => $item_detalle->precio_venta,
                        "existencia"    => $existencia,
                        "costo"         => isset($item_detalle->producto->id) ? $item_detalle->producto->costo : 0 ,
                    ]);

                    if ($existencia > 0) {
                        $responseArray[$key_cliente]["total"]  = $responseArray[$key_cliente]["total"] + floatval( $item_detalle->cantidad * $item_detalle->precio_venta);
                    }
                }
            }
        }

        return [
            "code" => 202,
            "name" => "Tpv",
            "ventas" => $responseArray,
            "type" => "Success",
        ];
    }


    /*******************************************************/
    // SERVICIOS PARA APP DE REPARTIDO - VENDEDOR
    /*******************************************************/



    //SERVICE NEW TABLE [[ VENTA RUTA ]]
    public function actionPostVenta()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);
        $cliente_id         = isset($post["cliente_id"]) ? $post["cliente_id"] : null;
        $total              = isset($post["total"]) ? $post["total"] : null;
        $tipo               = isset($post["tipo"]) ? $post["tipo"] : null;
        $venta_detalle      = isset($post["venta_detalle"]) ? $post["venta_detalle"] : null;
        $metodo_pago        = isset($post["metodo_pago"]) ? $post["metodo_pago"] : null;
        $reparto_id         = isset($post["reparto_id"]) ? $post["reparto_id"] : null;
        $getUser            = User::findOne($user->id);
        
        AuditLogger::log('Venta - Ruta', 'Crear Venta', $post);

        if ($getUser->pertenece_a == User::PERTENECE_REPARTIDO ) {
            if (Reparto::validaReparto($reparto_id)) {

                if (count($venta_detalle) > 0  && $tipo) {
                    $TempVentaRuta              = new TempVentaRuta();
                    $TempVentaRuta->cliente_id  = $cliente_id;
                    $TempVentaRuta->sucursal_id = $getUser->sucursal_id;
                    $TempVentaRuta->total       = round(floatval($total),2);
                    $TempVentaRuta->reparto_id  = $reparto_id;
                    $TempVentaRuta->tipo        = $tipo;
                    $TempVentaRuta->status      = Venta::STATUS_VENTA;
                    $TempVentaRuta->created_by   = $user->id;
                    if ($TempVentaRuta->save()) {

                        foreach ($venta_detalle as $key => $v_detalle) {
                            $TempVentaRutaDetalle = new TempVentaRutaDetalle();
                            $TempVentaRutaDetalle->temp_venta_ruta_id    = $TempVentaRuta->id;
                            $TempVentaRutaDetalle->producto_id      = $v_detalle["producto_id"];
                            $TempVentaRutaDetalle->cantidad         = $v_detalle["cantidad"];
                            $TempVentaRutaDetalle->precio_venta     = $v_detalle["precio_venta"];
                            $TempVentaRutaDetalle->created_by       = $user->id;
                            $TempVentaRutaDetalle->save();

                            $Producto = Producto::findOne($v_detalle['producto_id']);

                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $getUser->sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                            else
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $getUser->sucursal_id ], [ "=", "producto_id", $v_detalle['producto_id'] ] ] )->one();

                            if (isset($InvProducto->id)) {

                                if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                                    $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $getUser->sucursal_id  ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                                    if (isset($InvProducto2->id)) {
                                        // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                        $InvProducto2->cantidad = floatval($InvProducto2->cantidad) - ( floatval($v_detalle["cantidad"]) * $Producto->sub_cantidad_equivalente) ;
                                        $InvProducto2->save();

                                    }else{
                                        // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO
                                        $InvProductoSucursal  =  new InvProductoSucursal();
                                        $InvProductoSucursal->sucursal_id   = $getUser->sucursal_id ;
                                        $InvProductoSucursal->producto_id   = $v_detalle["producto_id"];
                                        $InvProductoSucursal->cantidad      = $v_detalle["cantidad"] * -1;
                                        $InvProductoSucursal->save();
                                    }
                                }else{
                                    // EL PRODUCTO SI SE ENCUENTRA EN INVENTARIO
                                    $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($v_detalle["cantidad"]);
                                    $InvProducto->save();
                                }
                                TransProductoInventario::saveTransTempVentaRuta( $getUser->sucursal_id,$TempVentaRutaDetalle->id,$TempVentaRutaDetalle->producto_id,$TempVentaRutaDetalle->cantidad,TransProductoInventario::TIPO_SALIDA, $user->id);
                            }
                        }


                        foreach ($metodo_pago as $key => $cobro) {

                            $TempCobroRutaVenta                         =  new TempCobroRutaVenta();
                            $TempCobroRutaVenta->operacion_reparto_id   = $reparto_id;
                            $TempCobroRutaVenta->temp_venta_ruta_id     = $TempVentaRuta->id;
                            $TempCobroRutaVenta->tipo                   = CobroVenta::TIPO_VENTA;
                            $TempCobroRutaVenta->tipo_cobro_pago        = CobroVenta::PERTENECE_COBRO;
                            $TempCobroRutaVenta->metodo_pago            = $cobro["metodo_pago_id"];
                            $TempCobroRutaVenta->cantidad               = $cobro["cantidad"];
                            $TempCobroRutaVenta->nota                   = isset($cobro["comentario"]) ? $cobro["comentario"] : null;
                            $TempCobroRutaVenta->created_by             = $user->id;

                             if ($TempCobroRutaVenta->metodo_pago == CobroVenta::COBRO_CREDITO ) {
                                $TempCobroRutaVenta->fecha_credito = isset($cobro["fecha_liquidacion"]) ? strtotime($cobro["fecha_liquidacion"]) : time();

                                /*$Credito = new  TempCredito();
                                $Credito->temp_venta_id         = $TempVentaRuta->id;
                                $Credito->monto                 = $cobro["cantidad"];
                                $Credito->fecha_credito         = isset($cobro["fecha_liquidacion"]) ? strtotime($cobro["fecha_liquidacion"]) : time();
                                $Credito->tipo                  = CobroVenta::PERTENECE_COBRO;
                                $Credito->created_by            = $user->id;
                                $Credito->save();*/
                            }

                            $TempCobroRutaVenta->save();
                        }

                        AuditLogger::log('Venta - Ruta', 'Venta Creada', $post, ['id' => $TempVentaRuta->id ] );

                        return [
                            "code" => 202,
                            "name" => "Tpv",
                            "folio" => $TempVentaRuta->id,
                            "type" => "Success",
                        ];
                    }

                    return [
                        "code" => 10,
                        "name" => "Tpv",
                        "message" => "Ocurrio un error, intenta nuevamente",
                        "type" => "Error",
                    ];
                }

                return [
                    "code" => 10,
                    "name" => "Tpv",
                    "message" => "Verifica tu información, intenta nuevamente",
                    "type" => "Error",
                ];
            }else{
                 return [
                    "code" => 10,
                    "name" => "Tpv",
                    "message" => "Verifica tu reparto, cierra sesion e ingresa nuevamente ",
                    "type" => "Error",
                ];
            }
        }else{
            return [
                "code"    => 10,
                "name"    => "Tpv",
                "message" => 'La VENTA de producto solo se puede generar por ENCARGADO DE RUTA',
                "type"    => "Error",
            ];
        }

    }

    public function actionGetStockRuta()
    {

        $post = Yii::$app->request->post();
        // Validamos Token
        $token          = $this->authToken($post["token"]);
        $GetUser        = User::findOne($token->id);

        $ViewInventario = ViewInventario::getInvRuta($GetUser->sucursal_id);

        $Reparto     = Reparto::find()->andWhere(["and",
            [ "=","status", Reparto::STATUS_RUTA ],
            [ "=","sucursal_id", $GetUser->sucursal_id ],
        ])->orderBy("id desc")->one();

        return [
            "code" => 202,
            "name" => "Tpv",
            "reparto_id" => isset($Reparto->id) ? $Reparto->id : null,
            "stock"      => $ViewInventario,
            "type" => "Success",
        ];
    }


    public function actionPostRutaEntrega()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user                   = $this->authToken($post["token"]);
        $total                  = isset($post["total"]) ? $post["total"] : null;
        $venta_detalle          = isset($post["v_detalle"]) ? $post["v_detalle"] : null;
        $cliente_id             = isset($post["cliente_id"]) ? $post["cliente_id"] : null;
        $metodo_pago            = isset($post["metodo_pago"]) ? $post["metodo_pago"] : null;
        $reparto_id             = isset($post["reparto_id"]) ? $post["reparto_id"] : null;
        $getUser                = User::findOne($user->id);

        AuditLogger::log('Venta - entrega pedido', 'Venta Entrega', $post);

        if ($getUser->pertenece_a == User::PERTENECE_REPARTIDO ) {
            if (Reparto::validaReparto($reparto_id)) {
                if (count($venta_detalle) > 0 && count($metodo_pago) > 0 && $reparto_id && $cliente_id) {

                    $valid = VentaDetalle::validateVentaApp($venta_detalle,$user->id,$getUser->sucursal_id);

                    if ($valid["code"] == 202 ) {
                        $responseResultOperacion = VentaDetalle::saveCerrarVentaGroupApp($venta_detalle,$user->id,$getUser->sucursal_id, $reparto_id,$cliente_id);

                        $array_venta = [];
                        $is_aproved = false;
                        foreach ($responseResultOperacion["productos"] as $key => $item_operacion) {
                            $is_add = true;
                            if ($item_operacion["code"] == 202 ) {
                                $is_aproved = true;

                                foreach ($array_venta as $key => $item_temp) {
                                    if ($item_operacion["preventa_id"] == $item_temp["preventa_id"]) {
                                        $is_add = false;
                                    }
                                }
                            }else{
                                $is_add = false;
                            }

                            if ($is_add) {
                                array_push($array_venta,$item_operacion);
                            }
                        }

                        $token_pay = bin2hex(random_bytes(16));
                        foreach ($array_venta as $key => $item_venta) {
                            $TempVentaTokenPay                          = new TempVentaTokenPay();
                            $TempVentaTokenPay->operacion_reparto_id    = $reparto_id;
                            $TempVentaTokenPay->venta_id                = $item_venta["tipo"] == TempVentaTokenPay::TIPO_VENTA_CENTRAL ? $item_venta["preventa_id"] : null;
                            $TempVentaTokenPay->temp_venta_ruta_id  = $item_venta["tipo"] == TempVentaTokenPay::TIPO_VENTA_TEMP ? $item_venta["preventa_id"] : null;
                            $TempVentaTokenPay->tipo                = $item_venta["tipo"];
                            $TempVentaTokenPay->token_pay           = $token_pay;
                            $TempVentaTokenPay->created_by          = $user->id;
                            $TempVentaTokenPay->save();
                        }

                        if ($is_aproved) {
                            foreach ($metodo_pago as $key => $cobro) {
                                $TempCobroRutaVenta                         = new TempCobroRutaVenta();
                                $TempCobroRutaVenta->operacion_reparto_id   = $reparto_id;
                                $TempCobroRutaVenta->tipo               = CobroVenta::TIPO_VENTA;
                                $TempCobroRutaVenta->tipo_cobro_pago    = CobroVenta::PERTENECE_COBRO;
                                $TempCobroRutaVenta->trans_token_venta  = $token_pay;
                                $TempCobroRutaVenta->metodo_pago        = $cobro["metodo_pago_id"];
                                $TempCobroRutaVenta->cantidad           = $cobro["cantidad"];
                                $TempCobroRutaVenta->created_by         = $user->id;

                                if ($TempCobroRutaVenta->metodo_pago == CobroVenta::COBRO_CREDITO ) {
                                    $TempCobroRutaVenta->fecha_credito = isset($cobro["fecha_liquidacion"]) ? strtotime($cobro["fecha_liquidacion"]) : time();
                                    $TempCredito = new  TempCredito();
                                    $TempCredito->cliente_id        = $cliente_id;
                                    $TempCredito->trans_token_venta = $token_pay;
                                    $TempCredito->monto         = $cobro["cantidad"];
                                    $TempCredito->fecha_credito = isset($cobro["fecha_liquidacion"]) ? strtotime($cobro["fecha_liquidacion"]) : time();
                                    $TempCredito->tipo          = Credito::TIPO_CLIENTE;
                                    $TempCredito->created_by    = $user->id;
                                    $TempCredito->save();
                                }
                                $TempCobroRutaVenta->save();

                            }
                        }

                        AuditLogger::log('Venta - entrega pedido', 'Venta Entregada', $post, $responseResultOperacion );

                        return [
                            "code" => 202,
                            "name" => "Tpv",
                            "resumen" => $responseResultOperacion,
                            "type" => "Success",
                        ];
                    }else{
                        return [
                            "code"      => 10,
                            "name"      => "Tpv",
                            "message"   => "Existe un problema de abastecimiento revisa tu inventario, para terminar la nota.",
                            "type"      => "Error",
                        ];
                    }
                }

                return [
                    "code" => 10,
                    "name" => "Tpv",
                    "message" => "Verifica tu información, intenta nuevamente",
                    "type" => "Error",
                ];

            }else{
                 return [
                    "code" => 10,
                    "name" => "Tpv",
                    "message" => "Verifica tu reparto, cierra sesion e ingresa nuevamente ",
                    "type" => "Error",
                ];
            }
        }
        return [
            "code"    => 10,
            "name"    => "Tpv",
            "message" => 'La entrega de producto solo se puede generar por ENCARGADO DE RUTA',
            "type"    => "Error",
        ];
    }

    public function actionValidaRepartoRuta()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user                   = $this->authToken($post["token"]);
        $reparto_id             = isset($post["reparto_id"]) ? $post["reparto_id"] : null;
        if (Reparto::validaReparto($reparto_id)) {
            return [
                "code" => 202,
                "name" => "Tpv",
                "message" => "El reparto es correcto",
                "type" => "Success",
            ];
        }else{
            return [
                "code" => 10,
                "name" => "Tpv",
                "message" => "Verifica tu reparto, cierra sesion e ingresa nuevamente ",
                "type" => "Error",
            ];

        }
    }

    /*
    public function actionPostCierreRuta()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $token          = $this->authToken($post["token"]);
        $reparto_id     = isset($post["reparto_id"]) ? $post["reparto_id"] : null;


        if ($reparto_id) {

            $Reparto     = Reparto::findOne($reparto_id);
            $Reparto->status            = Reparto::STATUS_TERMINADO;
            $Reparto->cierre_reparto    = time();
            $Reparto->updated_by        = $token->id;
            if ($Reparto->save()) {
                return [
                    "code" => 202,
                    "name" => "Tpv",
                    "message" => "Se realizo correctamente",
                    "type" => "Success",
                ];
            }
        }

        return [
            "code" => 10,
            "name" => "Tpv",
            "message" => "Ocurrio un error, verifica tu información",
            "type" => "Warning",
        ];
    }
    */


    public function actionGetIsRuta()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $token          = $this->authToken($post["token"]);
        $GetUser        = User::findOne($token->id);

        $Reparto     = Reparto::find()->andWhere(["and",
            [ "=","status", Reparto::STATUS_RUTA ],
            [ "=","sucursal_id", $GetUser->sucursal_id ],
        ])->orderBy("id desc")->one();

        return [
            "code" => 202,
            "name" => "Tpv",
            "reparto_id" => isset($Reparto->id) ? $Reparto->id : null,
            "type" => "Success",
        ];
    }
}
?>
