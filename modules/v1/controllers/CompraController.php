<?php
namespace app\modules\v1\controllers;

use Yii;
use app\models\compra\Compra;
use app\models\compra\CompraDetalle;
use app\models\cobro\CobroVenta;
use app\models\credito\Credito;
use app\models\venta\Venta;
use app\models\user\User;

class CompraController extends DefaultController
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


    public function actionPostCompra()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user                = $this->authToken($post["token"]);
        $sucursal_id         = isset($post["sucursal_id"]) ? $post["sucursal_id"] : null;
        $proveedor_id         = isset($post["proveedor_id"]) ? $post["proveedor_id"] : null;
        $tiempo_recorrido    = isset($post["tiempo_recorrido"]) ? $post["tiempo_recorrido"] : null;
        $fecha_salida        = isset($post["fecha_salida"]) ? $post["fecha_salida"] : null;
        $total               = isset($post["total"]) ? $post["total"] : null;
        $compra_detalle      = isset($post["compra_detalle"]) ? $post["compra_detalle"] : null;
        $nota                = isset($post["nota"]) ? $post["nota"] : null;

        $is_especial         = isset($post["is_especial"])  ? $post["is_especial"] : null;
        $venta_id            = isset($post["venta_id"])     ? $post["venta_id"] : null;

        $lat                 = isset($post["lat"])     ? $post["lat"] : null;
        $lng                 = isset($post["lng"])     ? $post["lng"] : null;

        $metodo_pago         = isset($post["metodo_pago"]) ? $post["metodo_pago"] : null;


        if (count($compra_detalle) > 0 &&  count($metodo_pago) > 0 && $sucursal_id ) {
            # code...
            $compra      = new Compra();
            $compra->sucursal_id        = $sucursal_id;
            $compra->proveedor_id        = $proveedor_id;
            $compra->tiempo_recorrido   = $tiempo_recorrido;
            $compra->fecha_salida       = strtotime($fecha_salida);
            $compra->is_especial        = $is_especial == Compra::COMPRA_ESPECIAL ? Compra::COMPRA_ESPECIAL : Compra::COMPRA_GENERAL;
            $compra->venta_id           = $is_especial == Compra::COMPRA_ESPECIAL ? $venta_id : null;
            $compra->total              = round($total,2);
            $compra->lat                = $lat;
            $compra->lng                = $lng;
            $compra->nota               = $nota;
            $compra->status             = Compra::STATUS_PROCESO;
            $compra->created_by         = $user->id;
            if ($compra->save()) {

                foreach ($compra_detalle as $key => $v_detalle) {
                    $CompraDetalle = new CompraDetalle();
                    $CompraDetalle->compra_id    = $compra->id;
                    $CompraDetalle->producto_id  = $v_detalle["producto_id"];
                    $CompraDetalle->cantidad     = floatval($v_detalle["cantidad"]);
                    $CompraDetalle->costo        = $v_detalle["costo"];
                    $CompraDetalle->created_by   = $user->id;
                    $CompraDetalle->save();
                }


                foreach ($metodo_pago as $key => $cobro) {

                    $CobroVenta  =  new CobroVenta();
                    $CobroVenta->compra_id      = $compra->id;
                    $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                    $CobroVenta->tipo_cobro_pago= CobroVenta::PERTENECE_PAGO;
                    $CobroVenta->metodo_pago    = $cobro["metodo_pago_id"];
                    $CobroVenta->cantidad       = round($cobro["cantidad"],2);
                    $CobroVenta->created_by   = $user->id;

                    if ($CobroVenta->metodo_pago == CobroVenta::COBRO_CREDITO ) {
                        $CobroVenta->fecha_credito = isset($cobro["fecha_liquidacion"]) ? strtotime($cobro["fecha_liquidacion"]) : time();
                        $Credito = new  Credito();
                        $Credito->compra_id  = $compra->id;
                        $Credito->monto      = round($cobro["cantidad"],2);
                        $Credito->fecha_credito = isset($cobro["fecha_credito"]) ? strtotime($cobro["fecha_credito"]) : time();
                        $Credito->tipo       = CobroVenta::PERTENECE_PAGO;
                        $Credito->created_by = $user->id;
                        $Credito->save();
                    }

                    $CobroVenta->save();


                }


                return [
                    "code" => 202,
                    "name" => "Compra",
                    "folio" => $compra->id,
                    "type" => "Success",
                ];
            }


            return [
                "code" => 10,
                "name" => "Compra",
                "message" => "Ocurrio un error, intenta nuevamente",
                "type" => "Error",
            ];
        }

        return [
            "code" => 10,
            "name" => "Compra",
            "message" => "Verifica tu información, intenta nuevamente",
            "type" => "Error",
        ];

    }


    public function actionGetProcesoCompra()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);
        $getUser            = User::findOne($user->id);


        if ($getUser->perfil->item_name == 'ENCARGADO CEDIS' || $getUser->perfil->item_name == 'GERENTE DE TIENDA' || $getUser->perfil->item_name == 'TIENDA' ){
            $compra = Compra::find()->leftJoin('operacion', 'compra.id = operacion.compra_id')->andWhere(["and",
                [ "<>","compra.status", Compra::STATUS_CANCEL ],
                [ "=", "compra.sucursal_id",  $getUser->sucursal_id ],
                [ "IS","operacion.compra_id", new \yii\db\Expression('null') ]
            ])->orderBy(['id' => SORT_DESC])->all();

        }else{
            $compra = Compra::find()->leftJoin('operacion', 'compra.id = operacion.compra_id')->andWhere(["and",
                [ "<>","compra.status", Compra::STATUS_CANCEL ],
                [ "IS","operacion.compra_id", new \yii\db\Expression('null') ]
            ])->orderBy(['id' => SORT_DESC])->all();
        }



        $responseArray = [];

        foreach ($compra as $key => $item) {
            array_push($responseArray, [
                "id" => $item->id,
                "folio"         => str_pad($item->id,6,"0",STR_PAD_LEFT),
                "proveedor_id"  => $item->proveedor_id,
                "proveedor"     => isset($item->proveedor->id) ?  $item->proveedor->nombre : null,
                "sucursal_id"   => $item->sucursal_id,
                "sucursal"      => isset($item->sucursal->id) ?  $item->sucursal->nombre : null,
                "tiempo_recorrido" =>   $item->tiempo_recorrido,
                "fecha_salida"  =>   date("Y-m-d",$item->fecha_salida),
                "status"        =>   $item->status,
                "is_especial"   =>   $item->is_especial,

                "venta_folio"   => isset($item->venta->id) ?  str_pad($item->venta->id,5,"0",STR_PAD_LEFT) : null,
                "venta_cliente" => isset($item->venta->cliente->id) ?  $item->venta->cliente->nombreCompleto : null,
                "venta_total"   => isset($item->venta->id) ?  $item->venta->total : null,

                "lat"           =>   $item->lat,
                "lng"           =>   $item->lng,
                "nota"          =>   $item->nota,
                "venta_id"      =>   $item->venta_id,
                "c_detalle"     => [],
                "metodo_pago"   => [],
                "total"         =>   $item->total,
                "created_by_user" =>   $item->createdBy->nombreCompleto,
                "created_by"    =>   $item->created_by,
            ]);
        }

        foreach ($responseArray as $key => $item) {
            $CompraDetalle = CompraDetalle::find()->andWhere([ "compra_id" => $item["id"] ])->all();
            if ($CompraDetalle) {

                foreach ($CompraDetalle as $key2 => $item2) {
                    array_push($responseArray[$key]["c_detalle"], [
                        "compra_detalle_id" => $item2->id,
                        "producto_id" => $item2->producto_id,
                        "producto" => isset($item2->producto->id) ? $item2->producto->nombre : null,
                        "cantidad" => $item2->cantidad,
                        "costo" => $item2->costo,
                    ]);
                }
            }
        }

        foreach ($responseArray as $key => $item) {
            $CobroVenta = CobroVenta::find()->andWhere([ "compra_id" => $item["id"] ])->all();
            foreach ($CobroVenta as $key2 => $pago) {
                array_push($responseArray[$key]["metodo_pago"], [
                    "cobro_id" => $pago->id,
                    "metodo_pago" => $pago->metodo_pago,
                    "metodo_pago_text" => CobroVenta::$servicioList[$pago->metodo_pago],
                    "cantidad" => $pago->cantidad,
                ]);
            }
        }


        return [
            "code" => 202,
            "name" => "Compra",
            "ventas" => $responseArray,
            "type" => "Success",
        ];

    }

    public function actionGetMetodoPago()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $token          = $this->authToken($post["token"]);
        $metodoPago     = CobroVenta::$servicioList;

        $responseArray = [];
        foreach ($metodoPago as $key => $item) {
            array_push($responseArray, [
                "id" => $key,
                "metodo" => $item,
            ]);
        }

        return [
            "code"  => 202,
            "name"  => "Compra",
            "metodo_pago" =>  $responseArray,
            "type" => "Success",
        ];
    }

    public function actionGetVentaEspecial()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);
        $venta_id     = isset($post["venta_id"]) && $post["venta_id"] ? $post["venta_id"] : null;

        if ($venta_id){

            $venta = Venta::findOne($venta_id);

            $ventaArray =  [];

            if (isset($venta->id)) {
                if ($venta->is_especial == Venta::VENTA_ESPECIAL) {
                    if ($venta->status == Venta::STATUS_PRECAPTURA ) {
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
                                "producto"      => isset($item2->producto->id) ? $item2->producto->nombre : null,
                                "cantidad"      => $item2->cantidad,
                                "precio_venta"  => $item2->precio_venta
                            ]);
                        }

                        return [
                            "code" => 202,
                            "name" => "Tpv",
                            "venta" => $ventaArray,
                            "type" => "Success",
                        ];
                    }else{
                        return [
                            "code" => 10,
                            "name" => "Tpv",
                            "message" => "Ingresaste una VENTA, INGRESA UNA PRE-CAPTURA ESPECIAL",
                            "type" => "Success",
                        ];
                    }
                }else{
                    return [
                        "code" => 10,
                        "name" => "Tpv",
                        "message" => "La PRE-CAPTURA no es TIPO [ESPECIAL], INGRESA UNA PRE-CAPTURA ESPECIAL",
                        "type" => "Success",
                    ];
                }

            }
        }

        return [
            "code" => 10,
            "name" => "Tpv",
            "message" => "Ocurrio un error, ingresa correctamente el folio de la venta",
            "type" => "Success",
        ];
    }

    public function actionUpdateCompra()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user        = $this->authToken($post["token"]);
        $compra_id           = isset($post["compra_id"]) ? $post["compra_id"] : null;
        $sucursal_id         = isset($post["sucursal_id"]) ? $post["sucursal_id"] : null;
        $proveedor_id        = isset($post["proveedor_id"]) ? $post["proveedor_id"] : null;
        $tiempo_recorrido    = isset($post["tiempo_recorrido"]) ? $post["tiempo_recorrido"] : null;
        $fecha_salida        = isset($post["fecha_salida"]) ? $post["fecha_salida"] : null;
        $total               = isset($post["total"]) ? $post["total"] : null;
        $compra_detalle      = isset($post["compra_detalle"]) ? $post["compra_detalle"] : null;
        $nota                = isset($post["nota"]) ? $post["nota"] : null;
        $is_especial         = isset($post["is_especial"])  ? $post["is_especial"] : null;
        $venta_id            = isset($post["venta_id"])     ? $post["venta_id"] : null;
        $lat                 = isset($post["lat"])     ? $post["lat"] : null;
        $lng                 = isset($post["lng"])     ? $post["lng"] : null;
        $metodo_pago         = isset($post["metodo_pago"]) ? $post["metodo_pago"] : null;
        $validador = Compra::findOne($compra_id);
        if($validador->status == Compra::STATUS_TERMINADA){
            return [
                "code"    => 10,
                "name"    => "Compra",
                "message" => 'La compra fue terminada',
                "type"    => "Error",
            ];
        }

        if (count($compra_detalle) > 0 &&  count($metodo_pago) > 0 && $sucursal_id && $compra_id ) {
            $compra      = Compra::findOne($compra_id);
            if (isset($compra->id)) {

                $compra->sucursal_id        = $sucursal_id;
                $compra->proveedor_id       = $proveedor_id;
                $compra->tiempo_recorrido   = $tiempo_recorrido;
                $compra->fecha_salida       = strtotime($fecha_salida);
                $compra->is_especial        = $is_especial == Compra::COMPRA_ESPECIAL ? Compra::COMPRA_ESPECIAL : null;
                $compra->venta_id           = $is_especial == Compra::COMPRA_ESPECIAL ? $venta_id : null;
                $compra->total              = $total;
                $compra->lat                = $lat;
                $compra->lng                = $lng;
                $compra->nota               = $nota;
                $compra->updated_by         = $user->id;
                if ($compra->update()) {

                    foreach ($compra_detalle as $key => $v_detalle) {
                        if (isset($v_detalle["compra_detalle_id"]) && $v_detalle["compra_detalle_id"])
                            $CompraDetalle = CompraDetalle::findOne($v_detalle["compra_detalle_id"]);
                        else
                            $CompraDetalle = new CompraDetalle();

                        if ($v_detalle["status"] == 10) {
                            $CompraDetalle->compra_id    = $compra->id;
                            $CompraDetalle->producto_id  = $v_detalle["producto_id"];
                            $CompraDetalle->cantidad     = $v_detalle["cantidad"];
                            $CompraDetalle->costo        = round($v_detalle["costo"]);
                            $CompraDetalle->created_by   = $user->id;
                            $CompraDetalle->save();
                        }else
                            $CompraDetalle->delete();

                    }


                    foreach ($metodo_pago as $key => $cobro) {

                        if (isset($cobro["cobro_id"]) && $cobro["cobro_id"])
                            $CobroVenta  =  CobroVenta::findOne($cobro["cobro_id"]);
                        else
                            $CobroVenta  =  new CobroVenta();

                        if ($cobro["status"] == 10) {
                            $CobroVenta->compra_id      = $compra->id;
                            $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                            $CobroVenta->tipo_cobro_pago= CobroVenta::PERTENECE_PAGO;
                            $CobroVenta->metodo_pago    = $cobro["metodo_pago_id"];
                            $CobroVenta->cantidad       = $cobro["cantidad"];
                            $CobroVenta->created_by     = $user->id;


                            if ($CobroVenta->metodo_pago == CobroVenta::COBRO_CREDITO ) {
                                $CobroVenta->fecha_credito = isset($cobro["fecha_liquidacion"]) ? strtotime($cobro["fecha_liquidacion"]) : time();
                                $Credito =  Credito::findOne(["compra_id" => $compra->id ]);
                                if (isset($Credito->id)) {
                                    $Credito->monto         = round($cobro["cantidad"],2);
                                    $Credito->fecha_credito = isset($cobro["fecha_credito"]) ? strtotime($cobro["fecha_credito"]) : time();
                                    $Credito->updated_by    = $user->id;
                                    $Credito->update();
                                }else{
                                    $Credito = new  Credito();
                                    $Credito->compra_id  = $compra->id;
                                    $Credito->monto      = round($cobro["cantidad"],2);
                                    $Credito->fecha_credito = isset($cobro["fecha_credito"]) ? strtotime($cobro["fecha_credito"]) : time();
                                    $Credito->tipo       = CobroVenta::PERTENECE_PAGO;
                                    $Credito->created_by = $user->id;
                                    $Credito->save();
                                }
                            }
                            
                            $CobroVenta->save();

                        }else{
                            if ($CobroVenta->metodo_pago == CobroVenta::COBRO_CREDITO ) {

                                $Credito =  Credito::findOne(["compra_id" => $compra->id ]);

                                if ($Credito) {
                                    $Credito->status        = Credito::STATUS_CANCEL;
                                    $Credito->updated_by    = $user->id;
                                    $Credito->save();
                                }
                            }
                            $CobroVenta->delete();
                        }
                    }
                    
                    return [
                        "code" => 202,
                        "name" => "Compra",
                        "folio" => $compra->id,
                        "type" => "Success",
                    ];
                }

                return [
                    "code" => 10,
                    "name" => "Compra",
                    "message" => "Ocurrio un error, intenta nuevamente",
                    "type" => "Error",
                ];

            }else{
                return [
                    "code"    => 10,
                    "name"    => "Compra",
                    "message" => 'La compra no EXISTE, intenta nuevamente',
                    "type"    => "Error",
                ];
            }
        }
        return [
            "code"    => 10,
            "name"    => "Compra",
            "message" => 'Verifica tu información, intenta nuevamente',
            "type"    => "Error",
        ];
    }
}
?>
