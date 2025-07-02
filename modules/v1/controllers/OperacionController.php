<?php
namespace app\modules\v1\controllers;

use Yii;
use yii\helpers\Url;
use yii\db\Query;
use app\models\Esys;
use kartik\mpdf\Pdf;
use yii\db\Expression;
use app\models\user\User;
use app\models\venta\Venta;
use app\models\inv\Operacion;
use app\models\venta\ViewVenta;
use app\models\esys\EsysSetting;
use app\models\venta\VentaDetalle;
use app\models\inv\OperacionDetalle;
use app\models\inv\InvProductoSucursal;
use app\models\producto\Producto;
use app\models\cobro\CobroVenta;
use app\models\reparto\Reparto;
use app\models\compra\Compra;
use app\models\trans\TransProductoInventario;
use app\models\cliente\Cliente;

class OperacionController extends DefaultController
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
     *  OPERACION ENTRADA
    *****************************************/
    public function actionPostEntrada()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user        = $this->authToken($post["token"]);
        $sucursal    = isset($post["sucursal"]) ? $post["sucursal"] : null;
        $productos   = isset($post["productos"]) ? $post["productos"] : null;
        $compra_id   = isset($post["compra_id"]) ? $post["compra_id"] : null;


        if ($compra_id && $sucursal && count($productos) > 0 ) {

            $connection = \Yii::$app->db;

            $transaction = $connection->beginTransaction();

            try {

                $Operacion = $connection->createCommand()
                        ->insert('operacion', [
                                'tipo' => Operacion::TIPO_ENTRADA,
                                'motivo' => Operacion::ENTRADA_MERCANCIA_NUEVA,
                                'compra_id' => $compra_id,
                                'status' => Operacion::STATUS_ACTIVE,
                                'almacen_sucursal_id' => $sucursal,
                                'created_by' => $user->id,
                                'created_at' => time(),
                        ])->execute();



                $OperacionID = Yii::$app->db->getLastInsertID();

                foreach ($productos as $key => $item) {
                    $OperacionDetalle =  $connection->createCommand()
                    ->insert('operacion_detalle', [
                        'operacion_id'  => $OperacionID,
                        'producto_id'   => $item['producto_id'],
                        'cantidad'      => $item['cantidad'],
                        'costo'         =>  isset($item['costo'])  && $item['costo']  && $item['costo'] > 10 ? $item['costo'] :  Compra::getCostoCompra($compra_id,$item['producto_id']),
                        //'costo'         => Compra::getCostoCompra($compra_id,$item['producto_id']),
                    ])->execute();

                    $itemDetailOperacionID = Yii::$app->db->getLastInsertID();

                    $Producto = Producto::findOne($item['producto_id']);

                    if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                    else
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $item['producto_id'] ] ] )->one();

                    if (isset($InvProducto->id)) {

                        if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                            $InvProductoSucursal =  $connection->createCommand()
                            ->update('inv_producto_sucursal', [
                                'cantidad'   => floatval($InvProducto->cantidad) + ( intval($item['cantidad']) * $Producto->sub_cantidad_equivalente  ) ,
                                'updated_by' => $user->id,
                                'updated_at' => time(),
                            ], "producto_id=". $Producto->sub_producto_id . " and sucursal_id=" . $sucursal )->execute();
                        }else{

                            $InvProductoSucursal =  $connection->createCommand()
                            ->update('inv_producto_sucursal', [
                                'cantidad'   => floatval($InvProducto->cantidad) +  floatval($item['cantidad']),
                                'updated_by' => $user->id,
                                'updated_at' => time(),
                            ], "producto_id=". $item['producto_id'] . " and sucursal_id=" . $sucursal )->execute();

                        }

                    }else{

                        $InvProductoSucursal =  $connection->createCommand()
                        ->insert('inv_producto_sucursal', [
                            'producto_id'=> $Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ? $Producto->sub_producto_id  : $item['producto_id'],
                            'sucursal_id'=> $sucursal,
                            'cantidad'   => $Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ?  ( $Producto->sub_cantidad_equivalente  * intval($item['cantidad']) ) : $item['cantidad'],
                            'created_by' => $user->id,
                            'created_at' => time(),
                        ])->execute();
                    }

                    // $getPromedio = Compra::getPromedioCompra($item['producto_id']);
                    // if(isset($getPromedio["valor"]) && $getPromedio["valor"] > 0){
                    //     $connection->createCommand()
                    //     ->update('producto', [
                    //         'costo'      => $getPromedio["valor"],
                    //         'updated_by' => $user->id,
                    //         'updated_at' => time(),
                    //     ], "id=". $item['producto_id'] )->execute();    
                    // }

                    TransProductoInventario::saveTransOperacion($sucursal,$itemDetailOperacionID,$item['producto_id'],$item['cantidad'],TransProductoInventario::TIPO_ENTRADA, $user->id);
                }

                $Transcompra =  $connection->createCommand()
                ->update('compra', [
                    'status'   => Compra::STATUS_TERMINADA,
                    'updated_by' => $user->id,
                    'updated_at' => time(),
                ], "id=". $compra_id )->execute();

                $transaction->commit();


                Compra::cierreCompra($compra_id);

                return [
                    "code"    => 202,
                    "name"    => "Entrada",
                    "message" => 'Se genero correctamente la operación',
                    "folio"   => $OperacionID,
                    "type"    => "Success",
                ];

            } catch(Exception $e) {
                $transaction->rollback();
                return [
                    "code"    => 10,
                    "name"    => "Entrada",
                    "message" => 'Ocurrio un error, intenta nuevamente',
                    "type"    => "Error",
                ];
            }
        }
        return [
            "code"    => 10,
            "name"    => "Entrada",
            "message" => 'Verifica tu información, intenta nuevamente',
            "type"    => "Error",
        ];
    }

    /*****************************************
     *  OPERACION ABASTECIMIENTO
    *****************************************/
    public function actionPostAbastecimiento()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);
        $getUser            = User::findOne($user->id);
        $sucursal           = $getUser->sucursal_id;
        $productos          = isset($post["productos"]) ? $post["productos"] : null;
        $abastecimiento_id  = isset($post["abastecimiento_id"]) ? $post["abastecimiento_id"] : null;


        if ( $abastecimiento_id &&  $sucursal && count($productos) > 0 ) {

            $Operacion  = Operacion::findOne($abastecimiento_id);
            if($Operacion->status != Operacion::STATUS_CANCEL){
                $connection = \Yii::$app->db;
                $transaction = $connection->beginTransaction();

                try {

                    $Operacion = $connection->createCommand()
                    ->insert('operacion', [
                            'tipo' => Operacion::TIPO_ENTRADA,
                            'motivo' => Operacion::ENTRADA_TRASPASO,
                            'almacen_sucursal_id' => $sucursal,
                            'operacion_child_id' => $abastecimiento_id,
                            'status' => Operacion::STATUS_ACTIVE,
                            'created_by' => $user->id,
                            'created_at' => time(),
                    ])->execute();

                    $OperacionID = Yii::$app->db->getLastInsertID();

                    $OperacionChild =  $connection->createCommand()
                    ->update('operacion', [
                        'status'   => Operacion::STATUS_ACTIVE,
                        'updated_by' => $user->id,
                        'updated_at' => time(),
                    ], "id=". $abastecimiento_id )->execute();

                    foreach ($productos as $key => $item) {
                        $OperacionDetalle =  $connection->createCommand()
                        ->insert('operacion_detalle', [
                            'operacion_id'=> $OperacionID,
                            'producto_id'=> $item['producto_id'],
                            'cantidad'=> $item['cantidad'],
                            'costo'=> $item['costo'],
                        ])->execute();

                        $itemDetailOperacionID = Yii::$app->db->getLastInsertID();
                        $Producto = Producto::findOne($item['producto_id']);
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $item['producto_id'] ] ] )->one();

                        if (isset($InvProducto->id)) {
                            $InvProductoSucursal =  $connection->createCommand()
                            ->update('inv_producto_sucursal', [
                                'cantidad'   => floatval($InvProducto->cantidad) +  floatval($item['cantidad']),
                                'updated_by' => $user->id,
                                'updated_at' => time(),
                            ], "producto_id=". $item['producto_id'] . " and sucursal_id=" . $sucursal )->execute();                        
                        }else{
                            $InvProductoSucursal =  $connection->createCommand()
                            ->insert('inv_producto_sucursal', [
                                'producto_id'=> $item['producto_id'],
                                'sucursal_id'=> $sucursal,
                                'cantidad'   => $item['cantidad'],
                                'created_by' => $user->id,
                                'created_at' => time(),
                            ])->execute();
                        }

                        TransProductoInventario::saveTransOperacion($sucursal,$itemDetailOperacionID,$item['producto_id'],$item['cantidad'],TransProductoInventario::TIPO_ENTRADA, $user->id);

                    }
                    //EL RETORNO DEL PRODUCTO AUTOMATICO NO ES FUNCIONAL Y CAUSA CONFLICTO CON LA ADMINISTRACION
                    //Operacion::returnProductoFaltante($abastecimiento_id,$productos, $user->id);
                    Operacion::findChangeOperacion($abastecimiento_id,$OperacionID,$productos, $user->id);

                    $transaction->commit();

                    return [
                        "code"    => 202,
                        "name"    => "Abastecimiento",
                        "message" => 'Se genero correctamente la operación',
                        "folio"   => $OperacionID,
                        "type"    => "Success",
                    ];

                } catch(\Exception $e) {
                    $transaction->rollback();
                    return [
                        "code"    => 10,
                        "name"    => "Abastecimiento",
                        "message" => 'Ocurrio un error, intenta nuevamente',
                        "type"    => "Error",
                    ];
                }
            }else{

                return [
                    "code"    => 10,
                    "name"    => "Abastecimiento",
                    "message" => 'La operación de abastacimiento no se encuentra disponible',
                    "type"    => "Error",
                ];
            }
        }
        return [
            "code"    => 10,
            "name"    => "Abastecimiento",
            "message" => 'Verifica tu información, intenta nuevamente',
            "type"    => "Error",
        ];
    }

    /*****************************************
     *  OPERACION SURTIR
    *****************************************/
    public function actionPostSurtir()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user        = $this->authToken($post["token"]);
        $productos   = isset($post["productos"]) ? $post["productos"] : null;
        $sucursal_envia_id      = isset($post["sucursal_envia_id"]) ? $post["sucursal_envia_id"] : null;
        $sucursal_recibe_id     = isset($post["sucursal_recibe_id"]) ? $post["sucursal_recibe_id"] : null;
        $sucursal               = isset($post["sucursal_envia_id"]) ? $post["sucursal_envia_id"] : null;
        $nota                   = isset($post["nota"]) ? $post["nota"] : null;
        $myUser                 = User::findOne($user->id);
        $mySucursal             = $myUser->sucursal_id;

        if ($mySucursal !== $sucursal_envia_id ) {
            return [
                "code"    => 10,
                "name"    => "Surtir",
                "message" => 'Operación no permitida, tu sucursal asignada no coincide con la sucursal que envia',
                "type"    => "Error",
            ];
        }

        if ($sucursal_recibe_id && $sucursal_envia_id && count($productos) > 0 ) {

            $valid = Operacion::validateOperacionApp($productos,$sucursal_envia_id);
            if (empty($valid)) {

                $connection = \Yii::$app->db;

                $transaction = $connection->beginTransaction();

                try {

                    $Operacion = $connection->createCommand()
                            ->insert('operacion', [
                                    'tipo' => Operacion::TIPO_SALIDA,
                                    'motivo' => Operacion::SALIDA_TRASPASO,
                                    'almacen_sucursal_id' => $sucursal_envia_id,
                                    'sucursal_recibe_id' => $sucursal_recibe_id,
                                    'status' => Operacion::STATUS_PROCESO,
                                    'nota'       => $nota,
                                    'created_by' => $user->id,
                                    'created_at' => time(),
                            ])->execute();

                    $OperacionID = Yii::$app->db->getLastInsertID();

                    foreach ($productos as $key => $item) {
                        $OperacionDetalle =  $connection->createCommand()
                        ->insert('operacion_detalle', [
                            'operacion_id'=> $OperacionID,
                            'producto_id'=> $item['producto_id'],
                            'cantidad'=> $item['cantidad'],
                            'costo'=> $item['costo'],
                        ])->execute();

                        $itemDetailOperacionID = Yii::$app->db->getLastInsertID();

                        $Producto = Producto::findOne($item['producto_id']);

                        if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                        else
                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $item['producto_id'] ] ] )->one();


                        if (isset($InvProducto->id)) {

                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                                $InvProductoSucursal =  $connection->createCommand()
                                ->update('inv_producto_sucursal', [
                                    'cantidad'   => floatval($InvProducto->cantidad) -  ( intval($item['cantidad']) * $Producto->sub_cantidad_equivalente  ) ,
                                    'updated_by' => $user->id,
                                    'updated_at' => time(),
                                ], "producto_id=". $Producto->sub_producto_id . " and sucursal_id=" . $sucursal )->execute();
                            }else{

                                $InvProductoSucursal =  $connection->createCommand()
                                ->update('inv_producto_sucursal', [
                                    'cantidad'   => floatval($InvProducto->cantidad) -  floatval($item['cantidad']),
                                    'updated_by' => $user->id,
                                    'updated_at' => time(),
                                ], "producto_id=". $item['producto_id'] . " and sucursal_id=" . $sucursal )->execute();

                            }

                        }else{

                            $InvProductoSucursal =  $connection->createCommand()
                            ->insert('inv_producto_sucursal', [
                                'producto_id'=> $Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ? $Producto->sub_producto_id  : $item['producto_id'],
                                'sucursal_id'=> $sucursal,
                                'cantidad'   => $Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ?  ( ($Producto->sub_cantidad_equivalente  * intval($item['cantidad'])) * -1 ) : ( $item['cantidad'] * -1 ),
                                'created_by' => $user->id,
                                'created_at' => time(),
                            ])->execute();
                        }


                        TransProductoInventario::saveTransOperacion($sucursal,$itemDetailOperacionID,$item['producto_id'],$item['cantidad'],TransProductoInventario::TIPO_SALIDA, $user->id);

                    }

                    $transaction->commit();

                    return [
                        "code"    => 202,
                        "name"    => "Surtir",
                        "message" => 'Se genero correctamente la operación',
                        "folio"   => $OperacionID,
                        "type"    => "Success",
                    ];

                } catch(Exception $e) {
                    $transaction->rollback();
                    return [
                        "code"    => 10,
                        "name"    => "Surtir",
                        "message" => 'Ocurrio un error, intenta nuevamente',
                        "type"    => "Error",
                    ];
                }
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
            "code"    => 10,
            "name"    => "Surtir",
            "message" => 'Verifica tu información, intenta nuevamente',
            "type"    => "Error",
        ];

    }

    /*****************************************
     *  OPERACION LISTA DE ABASTECIMIENTOS DISPONIBLES
    *****************************************/
    public function actionGetListAbastecimiento()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user           = $this->authToken($post["token"]);
        $getUser = User::findOne($user->id);

        if ($getUser->sucursal_id) {
            $Operacion  = Operacion::find()->andWhere([ "and",
                [ "=", "status", Operacion::STATUS_PROCESO ],
                [ "=", "tipo", Operacion::TIPO_SALIDA ],
                [ "=", "motivo", Operacion::SALIDA_TRASPASO ],
                [ "=", "sucursal_recibe_id", $getUser->sucursal_id ],

            ])->all();

             $response = [];
            foreach ($Operacion as $key => $operacionItem) {
                array_push($response,[
                    "operacion_id"      => $operacionItem->id,
                    "operacion_folio"      => str_pad($operacionItem->id,6,"0",STR_PAD_LEFT),
                    "sucursal_nombre"   => $operacionItem->almacenSucursal->nombre,
                    "fecha"             => date("Y-m-d",$operacionItem->created_at),
                    "nota"              => $operacionItem->nota,
                ]);
            }

            return [
                "code"    => 202,
                "name"    => "Abastecimiento",
                "abastecimiento" => $response,
                "type"    => "Success",
            ];
        }
        return [
            "code"    => 10,
            "name"    => "Abastecimiento",
            "message" => 'Verifica tu información, intenta nuevamente',
            "type"    => "Error",
        ];
    }

    /*****************************************
     *  OPERACION DETALLADA DEL ABASTECIMIENTO
    *****************************************/
    public function actionGetAbastecimiento()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user           = $this->authToken($post["token"]);
        $abastecimiento_id   = isset($post["abastecimiento_id"]) ? $post["abastecimiento_id"] : null;

        if ($abastecimiento_id) {
            $Operacion  = Operacion::findOne($abastecimiento_id);
            $response = [];
            foreach ($Operacion->operacionDetalles as $key => $o_detalle) {
                array_push($response,[
                    "operacion_id"             => $Operacion->id,
                    "producto_id"           => $o_detalle->producto->id,
                    "producto_nombre"       => $o_detalle->producto->nombre,
                    "producto_clave"        => $o_detalle->producto->clave,
                    "costo"                 => $o_detalle->costo,
                    "producto_proveedor"    => null,
                    "producto_unidad"       => $o_detalle->producto->tipo_medida,
                    "producto_unidad_text"  => Producto::$medidaList[$o_detalle->producto->tipo_medida],
                    "cantidad"              => $o_detalle->cantidad,
                    "nota"                  => $Operacion->nota,
                ]);
            }

            return [
                "code"          => 202,
                "name"          => "Abastecimiento",
                "abas_detalle"  => $response,
                "type"          => "Success",
            ];
        }
        return [
            "code"    => 10,
            "name"    => "Abastecimiento",
            "message" => 'Verifica tu información, intenta nuevamente',
            "type"    => "Error",
        ];
    }


    /*****************************************
     *  OPERACION LISTA DE PRECAPTURAS
    *****************************************/
    public function actionGetListPrecaptura()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);
        $getUser            = User::findOne($user->id);
        $sucursal_ruta_id   = isset($post["sucursal_ruta_id"]) ? $post["sucursal_ruta_id"] : null;

        if ($sucursal_ruta_id) {

            $GetPrecaptura  = ViewVenta::getVentaPreCapturaDetail($sucursal_ruta_id, $getUser->sucursal_id);

            return [
                "code"    => 202,
                "name"    => "Lista de precapturas",
                "precapturas" => $GetPrecaptura,
                "type"    => "Success",
            ];
        }

        return [
            "code"    => 10,
            "name"    => "Lista de precapturas",
            "message" => 'Verifica tu información, intenta nuevamente',
            "type"    => "Error",
        ];
    }

    /*****************************************
     *  OPERACION CONSULTAMOS ALGUNA CARGAR DE UNIDAD
    *****************************************/
    public function actionGetRepartoProceso()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);
        $getUser            = User::findOne($user->id);
        $sucursal_ruta_id   = isset($post["sucursal_ruta_id"]) ? $post["sucursal_ruta_id"] : null;

        /********
            VALIDAMOS QUE NO EXISTA UNA UN REPARTO EN RUTA
        /********/
        $reparto = Reparto::getRepartoProceso($sucursal_ruta_id);

        $repartoArray = null;

        if ($reparto) {
            $repartoArray = [
                "id" => $reparto->id,
                "aperturado" => date("Y-m-d h:i",$reparto->created_at),
                "aperturado_por" => $reparto->createdBy->nombreCompleto,
            ];
        }
        return [
            "code"    => 10,
            "name"    => "Carga de unidad",
            "reparto" => $repartoArray,
            "type"    => "Success",
        ];
    }

    /*****************************************
     *  OPERACION CARGA DE UNIDADES
    *****************************************/
    public function actionPostCargaUnidad()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);
        $getUser            = User::findOne($user->id);
        $sucursal           = $getUser->sucursal_id;
        $productos          = isset($post["productos"]) ? $post["productos"] : [];
        $precapturas        = isset($post["precapturas"]) ? $post["precapturas"] : [];
        $sucursal_ruta_id   = isset($post["sucursal_ruta_id"]) ? $post["sucursal_ruta_id"] : null;
        $reparto_id         = isset($post["reparto_id"]) ? $post["reparto_id"] : null;

        /********
            VALIDAMOS QUE NO EXISTA UNA UN REPARTO EN RUTA
        /********/
        $errors = Reparto::getRepartoAperturado( $sucursal_ruta_id );

        if (empty($errors)) {

            if ( (!empty($precapturas)  || !empty($productos) ) && $sucursal ) {

                if (Reparto::movInventarioRepartoPrecapturaApp($sucursal_ruta_id, $precapturas, $productos, $sucursal, $user->id, $reparto_id )) {

                    if ( !empty($precapturas)   && !empty($productos)  )
                        return [
                            "code"    => 202,
                            "name"    => "Carga de Unidad",
                            "message" => 'Se cargaron las Precapturas y Productos a la Unidad',
                            "type"    => "Success",
                        ];

                    if (!empty($productos))
                        return [
                            "code"    => 202,
                            "name"    => "Carga de Unidad",
                            "message" => 'Se cargaron los Productos a la Unidad',
                            "type"    => "Success",
                        ];

                    if (!empty($precapturas))
                        return [
                            "code"    => 202,
                            "name"    => "Carga de Unidad",
                            "message" => 'Se cargaron las Precapturas a la Unidad',
                            "type"    => "Success",
                        ];
                }

                return [
                    "code"    => 10,
                    "name"    => "Carga de Unidad",
                    "message" => 'Ocurrio un error, intenta nuevamente',
                    "type"    => "Success",
                ];

            }

            return [
                "code"    => 10,
                "name"    => "Carga de unidad",
                "message" => 'Verifica tu información, intenta nuevamente',
                "type"    => "Error",
            ];
        }

        return [
            "code"    => 10,
            "name"    => "Carga de unidad",
            "message" => "Existe un REPARTO EN RUTA [" . date("Y-m-d h:i:s", $errors["created_at"]) ."], solicita a gerente de CEDIS que realice la recolección",
            "type"    => "Error",
        ];

    }


    /*********************************************
        DEVOLUCION DEVELOPER
    **********************************************/

    public function actionDevolucion()
    {

        $post = Yii::$app->request->post();
        // Validamos Token
        $user        = $this->authToken($post["token"]);


        $venta_id               = isset($post["venta_id"]) ? $post["venta_id"] : null;
        $reembolso_cantidad     = isset($post["reembolso_cantidad"]) ? $post["reembolso_cantidad"] : null;
        //$pago_cantidad          = isset($post["pago_cantidad"]) ? $post["pago_cantidad"] : null;
        $nota                   = isset($post["nota"]) ? $post["nota"] : null;
        $getUser                = User::findOne($user->id);
        $sucursal               = $getUser->sucursal_id;
        $productos              = isset($post["productos"]) ? $post["productos"]  : null;
        $getUser                = User::findOne($user->id);


        if ($getUser->pertenece_a == User::PERTENECE_REPARTIDO ) {

            if ($venta_id && $nota &&  $productos ) {

                $Venta = Venta::findOne($venta_id);

                $Reparto = Reparto::getRepartoAperturado( $sucursal );

                if (isset($Reparto->id)) {
                    if ($Venta->status == Venta::STATUS_VENTA ) {

                        $is_devolucion = Operacion::findOne(["venta_id" => $venta_id ]);

                        if (!isset($is_devolucion->id)) {

                            $connection = \Yii::$app->db;

                            $transaction = $connection->beginTransaction();

                            try {

                                $Operacion = $connection->createCommand()
                                        ->insert('operacion', [
                                                'tipo'                  => Operacion::TIPO_DEVOLUCION,
                                                'motivo'                => Operacion::ENTRADA_DEVOLUCION,
                                                'venta_id'              => $venta_id,
                                                'almacen_sucursal_id'   => $getUser->sucursal_id,
                                                'venta_reembolso_cantidad'  =>  $reembolso_cantidad,
                                                'reparto_id'                =>  $Reparto->id,
                                                //'venta_pago_cantidad'      => $pago_cantidad,
                                                'nota'          => $nota,
                                                'created_by'    => $user->id,
                                                'created_at'    => time(),
                                        ])->execute();

                                $OperacionID = Yii::$app->db->getLastInsertID();

                                /*if (isset($pago_cantidad["metodo_pago"]) && $pago_cantidad["metodo_pago"] ) {
                                    $CobroVenta = $connection->createCommand()
                                    ->insert('cobro_venta', [
                                            'venta_id'              => $venta_id,
                                            'tipo'                  => CobroVenta::TIPO_VENTA,
                                            'metodo_pago'           => $pago_cantidad["metodo_pago"],
                                            'tipo_cobro_pago'       => CobroVenta::PERTENECE_COBRO,
                                            'cantidad'              => $pago_cantidad["cantidad"],
                                            'created_by'            => $user->id,
                                            'created_at'            => time(),
                                    ])->execute();
                                }*/

                                if ( $reembolso_cantidad && floatval($reembolso_cantidad) > 0 ) {
                                    $CobroVenta = $connection->createCommand()
                                    ->insert('cobro_venta', [
                                            'venta_id'              => $venta_id,
                                            'tipo'                  => CobroVenta::TIPO_REEMBOLSO,
                                            'metodo_pago'           => CobroVenta::COBRO_EFECTIVO,
                                            'tipo_cobro_pago'       => CobroVenta::PERTENECE_REEMBOLSO,
                                            'cantidad'              =>  $reembolso_cantidad,
                                            'created_by'            => $user->id,
                                            'created_at'            => time(),
                                    ])->execute();
                                }



                                foreach ($productos as $key => $productoItem) {

                                    //********************* VALIDAMOS EL PRODUCTO EN LA VENTA **************************//
                                    $ProductoVenta = Venta::getProductoVenta($venta_id, $productoItem['producto_id'] );

                                    if (isset($ProductoVenta["id"])) {

                                        if ( floatval($ProductoVenta["cantidad"]) >= floatval($productoItem["cantidad"]) ) {

                                            $OperacionDetalle =  $connection->createCommand()
                                            ->insert('operacion_detalle', [
                                                'operacion_id'      => $OperacionID,
                                                'producto_id'       => $productoItem['producto_id'],
                                                'cantidad'          => $productoItem['cantidad'],
                                                'venta_detalle_id'  => $productoItem['v_detalle_id'],
                                                'costo'             => isset(VentaDetalle::findOne($productoItem['v_detalle_id'])->id) ? VentaDetalle::findOne($productoItem['v_detalle_id'])->precio_venta : 0,
                                            ])->execute();

                                            $Producto = Producto::findOne($productoItem['producto_id']);

                                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();
                                            else
                                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal ], [ "=", "producto_id", $productoItem['producto_id'] ] ] )->one();



                                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {

                                                $InvProductoSucursal =  $connection->createCommand()
                                                ->update('inv_producto_sucursal', [
                                                    'cantidad'   => floatval($InvProducto->cantidad) + ( intval($productoItem['cantidad']) * $Producto->sub_cantidad_equivalente  ) ,
                                                    'updated_by' => $user->id,
                                                    'updated_at' => time(),
                                                ], "producto_id=". $Producto->sub_producto_id . " and sucursal_id=" . $sucursal )->execute();
                                            }else{

                                                $InvProductoSucursal =  $connection->createCommand()
                                                ->update('inv_producto_sucursal', [
                                                    'cantidad'   => floatval($InvProducto->cantidad) +  floatval($productoItem['cantidad']),
                                                    'updated_by' => $user->id,
                                                    'updated_at' => time(),
                                                ], "producto_id=". $productoItem['producto_id'] . " and sucursal_id=" . $sucursal )->execute();

                                            }
                                        }
                                    }
                                }

                                $transaction->commit();

                                $Operacion = Operacion::findOne($OperacionID);

                                return [
                                    "code"    => 202,
                                    "name"    => "Devolucion",
                                    "message" => 'Se genero correctamente la operación',
                                    "folio"   => $OperacionID,
                                    "sucursal_recibe"   => $Operacion->almacenSucursal->nombre,
                                    "cliente"           => $Operacion->venta->cliente->nombreCompleto ,
                                    "resumen" => Operacion::getOperacionDetalleList($OperacionID),
                                    "type"    => "Success",
                                ];

                            } catch(Exception $e) {
                                $transaction->rollback();
                                return [
                                    "code"    => 10,
                                    "name"    => "Devolucion",
                                    "message" => 'Ocurrio un error, intenta nuevamente',
                                    "type"    => "Error",
                                ];
                            }
                        }else{
                            return [
                                "code"      => 10,
                                "message"   => "Ya se genero una devolución de esta venta, contacta al administración",
                                "type"    => "Error",
                            ];
                        }
                    }else{
                        return [
                            "code"      => 10,
                            "message"   => "Para generar una DEVOLUCIóN la venta debe estar TERMINADA",
                            "type"    => "Error",
                        ];
                    }
                }else{
                    return [
                        "code"      => 10,
                        "message"   => "No se puede realizar una DEVOLUCIÓN, el repartidor no se encuentra en OPERACION",
                        "type"    => "Error",
                    ];
                }
            }

            return [
                "code"    => 10,
                "name"    => "Devolucion",
                "message" => 'Verifica tu información, intenta nuevamente',
                "type"    => "Error",
            ];
        }

        return [
            "code"    => 10,
            "name"    => "Devolucion",
            "message" => 'La devolución solo se puede generar por ENCARGADO DE RUTA',
            "type"    => "Error",
        ];
    }

    /*****************************************
     *  OPERACION DETALLADA DEL RECOLECCION
    *****************************************/
    public function actionGetStockRuta()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user           = $this->authToken($post["token"]);
        $ruta_id   = isset($post["ruta_id"]) ? $post["ruta_id"] : null;

        if ($ruta_id) {
            $InvProductoSucursal  = InvProductoSucursal::getStockRuta($ruta_id);


            $response = [];
            foreach ($InvProductoSucursal as $key => $o_detalle) {
                array_push($response,[
                    "producto_id"           => $o_detalle->producto_id,
                    "producto_nombre"       => $o_detalle->producto->nombre,
                    "producto_clave"        => $o_detalle->producto->clave,
                    "cantidad"              => $o_detalle->cantidad,
                ]);
            }

            return [
                "code"          => 202,
                "name"          => "Stock ruta",
                "stock_detalle" => $response,
                "type"          => "Success",
            ];
        }
        return [
            "code"    => 10,
            "name"    => "Stock ruta",
            "message" => 'Verifica tu información, intenta nuevamente',
            "type"    => "Error",
        ];
    }



    /*****************************************
     *  OPERACION RECOLECCION
    *****************************************/
    public function actionPostRecoleccionUnidad()
    {
        $post = Yii::$app->request->post();
        // Validamos Token
        $user               = $this->authToken($post["token"]);
        $getUser            = User::findOne($user->id);
        $bodega_cedis_id    = $getUser->sucursal_id;
        $productos          = isset($post["productos"]) ? $post["productos"] : null;
        $sucursal_ruta_id   = isset($post["ruta_id"]) ? $post["ruta_id"] : null;

        $Reparto                    = Reparto::getRepartoAperturado($sucursal_ruta_id);


        if (!empty($Reparto)) {

            $Reparto->status            = Reparto::STATUS_TERMINADO;
            $Reparto->cierre_reparto    = time();
            $Reparto->updated_by        = $user->id;
            $Reparto->update();


            Reparto::setPrecapturaRechazo( $Reparto->id , $user->id);

            if ( empty($productos) ) {


                /*try {

                    $notificacionEmails = EsysSetting::getNotificacionEmail();
                    $emailArray = explode(",", $notificacionEmails);
                    $ventaArray = [];

                    foreach (Reparto::getPrecapturaCliente($Reparto->id) as $key => $item_precaptura) {
                        if ($item_precaptura["cliente_id"])
                            array_push($ventaArray,$item_precaptura["cliente_id"]);
                    }

                    $content = "";

                    $pdf = new Pdf([
                        // set to use core fonts only
                        'mode' => Pdf::MODE_CORE,
                        // A4 paper format
                        'format' => Pdf::FORMAT_LETTER,
                        // portrait orientation
                        'orientation' => Pdf::ORIENT_PORTRAIT,
                        // stream to browser inline
                        'destination' => Pdf::DEST_DOWNLOAD,
                        // your html content input
                        'content' => $content,
                        // format content from your own css file if needed or use the
                        // enhanced bootstrap css built by Krajee for mPDF formatting
                        'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
                        // any css to be embedded if required
                        'cssInline' => '.kv-heading-1{font-size:18px}',
                         // set mPDF properties on the fly
                        'options' => ['title' => 'Acuse'],
                         // call mPDF methods on the fly
                    ]);


                    $pdf->setApi();
                    $pdf_api = $pdf->getApi();
                    $count_show = 0;

                    foreach ($ventaArray as $key => $item_cliente) {

                            $Cliente = Cliente::findOne($item_cliente);

                            $RepartoAll = Reparto::getPreventaAll($item_cliente, $Reparto->id);

                            $foliosArray = [];
                            $totalPagare = 0;
                            $arrayPreventaDetail = [];
                            foreach ($RepartoAll as $key => $item_operacion) {
                                array_push($foliosArray,$item_operacion["id"]);
                                $totalPagare    = $totalPagare + floatval($item_operacion["total"]);
                                $ventaModel     = Venta::findOne($item_operacion["id"]);
                                foreach ($ventaModel->ventaDetalle as $key => $item_detail) {
                                    array_push($arrayPreventaDetail,[
                                        "cantidad" => $item_detail->cantidad,
                                        "tipo_medida" => $item_detail->producto->tipo_medida,
                                        "clave" => $item_detail->producto->clave,
                                        "nombre" => $item_detail->producto->nombre,
                                        "precio_venta" => $item_detail->precio_venta,
                                    ]);
                                }
                            }

                            $content = $this->renderFile(Yii::getAlias('@app') .'/modules/logistica/views/ruta/pagare.php',["cliente" =>  $Cliente, "copy" => false, "detail" => $arrayPreventaDetail, "foliosArray" => $foliosArray, "reparto" => $Reparto, "total" => $totalPagare ]);
                            //$content = $this->renderPartial('pagare', ["cliente" =>  $Cliente, "copy" => false, "detail" => $arrayPreventaDetail, "foliosArray" => $foliosArray, "reparto" => $reparto, "total" => $totalPagare ]);

                            $pdf_api->WriteHTML($content);

                            $pdf_api->setFooter('<table width="100%" style="padding-top: 5px;margin-top: 15px">
                                <tr>
                                    <td   style="text-align:justify; ">
                                        <p style="font-size:12px;color: #000;">SE SUSCRIBE EL PRESENTE PAGARÉ EN LA CIUDAD DE __ <strong>VERACRUZ VER.</strong> __ a __ <strong>'. date("Y-m-d", time()) .'</strong> __ DEBE(MOS) Y PAGARE(MOS) INCONDICIONALMENTE POR ESTE PAGARÉ A LA ORDEN DE : __<strong>AMERICA MORALES RAMIREZ</strong>__, EN LA CUIDAD DE __ <strong>VERACRUZ</strong> __ EL DIA ____________________________ LA CANTIDAD DE: __ <strong>'. number_format($totalPagare,2) .'</strong> __  CANTIDAD QUE HE(MOS) RECIBIDO A ENTERA SATISFACCION, ESTE PAGARÉ DOMICILIADO DE NO CUBRIR INTEGRALMENTE EL VALOR QUE AMPARA ESTE DOCUMENTO PRECISAMENTE EN LA FECHA DE SU VENCIMIENTO CAUSARA INTERES MORATORIOS DEL 5% MENSUAL DURANTE TODO EL TIEMPO QUE PERMANECIERE TOTAL O PARCIALMENTE INSOLUTO, SIN QUE POR ELLO SE ENTIENDA PRORROGADO EL PLAZO.</p>
                                    </td>
                                </tr>
                            </table>
                            <table width="100%" style="margin-top: 15px;">
                                <tr>
                                    <td width="70%" >
                                        <strong style="font-size:12px">CLIENTE:  <small style="font-size:16px; font-weight: 100;">'. $Cliente->nombreCompleto .'</small></strong>
                                    </td>
                                    <td align="center" width="30%">
                                        <table width="100%">
                                            <tr>
                                                <td align="center" style="border-bottom-style:solid; border-width: 2px; "></td>
                                            </tr>
                                            <tr>
                                                <td align="center" style="font-size: 14px">ACEPTA(MOS)</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <p style="text-align:right; border-width: 1px; border-bottom-style: solid;">ORIGINAL</p>');

                            $pdf_api->AddPage();

                            $content = $this->renderFile(Yii::getAlias('@app') .'/modules/logistica/views/ruta/pagare.php',["cliente" =>  $Cliente, "copy" => true, "detail" => $arrayPreventaDetail , "foliosArray" => $foliosArray,"reparto" => $Reparto , "total" => $totalPagare]);
                            //$content = $this->renderPartial('pagare', ["cliente" =>  $Cliente, "copy" => true, "detail" => $arrayPreventaDetail , "foliosArray" => $foliosArray,"reparto" => $reparto , "total" => $totalPagare]);
                            $pdf_api->WriteHTML($content);
                            $pdf_api->setFooter('<table width="100%" style="padding-top: 5px;margin-top: 15px">
                                <tr>
                                    <td   style="text-align:justify; ">
                                        <p style="font-size:12px;color: #000;">SE SUSCRIBE EL PRESENTE PAGARÉ EN LA CIUDAD DE __ <strong>VERACRUZ VER.</strong> __ a __ <strong>'. date("Y-m-d", time()) .'</strong> __ DEBE(MOS) Y PAGARE(MOS) INCONDICIONALMENTE POR ESTE PAGARÉ A LA ORDEN DE : __<strong>AMERICA MORALES RAMIREZ</strong>__, EN LA CUIDAD DE __ <strong>VERACRUZ</strong> __ EL DIA ____________________________ LA CANTIDAD DE: __ <strong>'. number_format($totalPagare,2) .'</strong> __  CANTIDAD QUE HE(MOS) RECIBIDO A ENTERA SATISFACCION, ESTE PAGARÉ DOMICILIADO DE NO CUBRIR INTEGRALMENTE EL VALOR QUE AMPARA ESTE DOCUMENTO PRECISAMENTE EN LA FECHA DE SU VENCIMIENTO CAUSARA INTERES MORATORIOS DEL 5% MENSUAL DURANTE TODO EL TIEMPO QUE PERMANECIERE TOTAL O PARCIALMENTE INSOLUTO, SIN QUE POR ELLO SE ENTIENDA PRORROGADO EL PLAZO.</p>
                                    </td>
                                </tr>
                            </table>
                            <table width="100%" style="margin-top: 15px;">
                                <tr>
                                    <td width="70%" >
                                        <strong style="font-size:12px">CLIENTE:  <small style="font-size:16px; font-weight: 100;">'. $Cliente->nombreCompleto .'</small></strong>
                                    </td>
                                    <td align="center" width="30%">
                                        <table width="100%">
                                            <tr>
                                                <td align="center" style="border-bottom-style:solid; border-width: 2px; "></td>
                                            </tr>
                                            <tr>
                                                <td align="center" style="font-size: 14px">ACEPTA(MOS)</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <p style="text-align:right; border-width: 1px; border-bottom-style: solid;">COPIA</p>');



                            $count_show = $count_show + 1;

                            //if (count($model) > $count_show)
                            if (count($ventaArray) > $count_show)
                                $pdf_api->AddPage();

                    }


                    $filename = Yii::getAlias('@webroot') . '/temp/PAGARES_DE_'.$Reparto->sucursal->nombre."-".date("Y-m-d",time()).".pdf";
                    $pdf_api->Output( $filename, \Mpdf\Output\Destination::FILE);

                    Yii::$app->mailer->compose('notificationMessage', ['message' => 'REPORTE DE PAGARES DE   '.  $Reparto->sucursal->nombre .' - ' . Esys::fecha_en_texto(time()) ])
                          ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                          ->setTo($emailArray)
                          ->attach($filename)
                          ->setSubject('NOTAS DE VENTAS DE -  '. date("Y-m-d",time()) .' ['. $Reparto->sucursal->nombre .']- '. Yii::$app->name)
                          ->send();

                } catch (Exception $e) {

                }*/

                Reparto::clearRuta($Reparto->sucursal_id, $user->id);

                return [
                    "code"    => 202,
                    "name"    => "Recoleccion",
                    "message" => 'Se genero el CIERRE DE LA RUTA correctamente.',
                    "type"    => "Success",
                ];
            }



            if ( $sucursal_ruta_id &&  $bodega_cedis_id && !empty($productos) ) {

                if (Reparto::movInventarioRecoleccionApp($sucursal_ruta_id, $productos, $bodega_cedis_id, $user->id, $Reparto->id )) {

                    /*try {

                        $notificacionEmails = EsysSetting::getNotificacionEmail();
                        $emailArray = explode(",", $notificacionEmails);
                        $ventaArray = [];

                        foreach (Reparto::getPrecapturaCliente($Reparto->id) as $key => $item_precaptura) {
                            if ($item_precaptura["cliente_id"])
                                array_push($ventaArray,$item_precaptura["cliente_id"]);
                        }

                        $content = "";

                        $pdf = new Pdf([
                            // set to use core fonts only
                            'mode' => Pdf::MODE_CORE,
                            // A4 paper format
                            'format' => Pdf::FORMAT_LETTER,
                            // portrait orientation
                            'orientation' => Pdf::ORIENT_PORTRAIT,
                            // stream to browser inline
                            'destination' => Pdf::DEST_DOWNLOAD,
                            // your html content input
                            'content' => $content,
                            // format content from your own css file if needed or use the
                            // enhanced bootstrap css built by Krajee for mPDF formatting
                            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
                            // any css to be embedded if required
                            'cssInline' => '.kv-heading-1{font-size:18px}',
                             // set mPDF properties on the fly
                            'options' => ['title' => 'Acuse'],
                             // call mPDF methods on the fly
                        ]);


                        $pdf->setApi();
                        $pdf_api = $pdf->getApi();
                        $count_show = 0;

                        foreach ($ventaArray as $key => $item_cliente) {

                                $Cliente = Cliente::findOne($item_cliente);

                                $RepartoAll = Reparto::getPreventaAll($item_cliente, $Reparto->id);

                                $foliosArray = [];
                                $totalPagare = 0;
                                $arrayPreventaDetail = [];
                                foreach ($RepartoAll as $key => $item_operacion) {
                                    array_push($foliosArray,$item_operacion["id"]);
                                    $totalPagare    = $totalPagare + floatval($item_operacion["total"]);
                                    $ventaModel     = Venta::findOne($item_operacion["id"]);
                                    foreach ($ventaModel->ventaDetalle as $key => $item_detail) {
                                        array_push($arrayPreventaDetail,[
                                            "cantidad" => $item_detail->cantidad,
                                            "tipo_medida" => $item_detail->producto->tipo_medida,
                                            "clave" => $item_detail->producto->clave,
                                            "nombre" => $item_detail->producto->nombre,
                                            "precio_venta" => $item_detail->precio_venta,
                                        ]);
                                    }
                                }

                                $content = $this->renderFile(Yii::getAlias('@app') .'/modules/logistica/views/ruta/pagare.php',["cliente" =>  $Cliente, "copy" => false, "detail" => $arrayPreventaDetail, "foliosArray" => $foliosArray, "reparto" => $Reparto, "total" => $totalPagare ]);
                                //$content = $this->renderPartial('pagare', ["cliente" =>  $Cliente, "copy" => false, "detail" => $arrayPreventaDetail, "foliosArray" => $foliosArray, "reparto" => $reparto, "total" => $totalPagare ]);

                                $pdf_api->WriteHTML($content);

                                $pdf_api->setFooter('<table width="100%" style="padding-top: 5px;margin-top: 15px">
                                    <tr>
                                        <td   style="text-align:justify; ">
                                            <p style="font-size:12px;color: #000;">SE SUSCRIBE EL PRESENTE PAGARÉ EN LA CIUDAD DE __ <strong>VERACRUZ VER.</strong> __ a __ <strong>'. date("Y-m-d", time()) .'</strong> __ DEBE(MOS) Y PAGARE(MOS) INCONDICIONALMENTE POR ESTE PAGARÉ A LA ORDEN DE : __<strong>AMERICA MORALES RAMIREZ</strong>__, EN LA CUIDAD DE __ <strong>VERACRUZ</strong> __ EL DIA ____________________________ LA CANTIDAD DE: __ <strong>'. number_format($totalPagare,2) .'</strong> __  CANTIDAD QUE HE(MOS) RECIBIDO A ENTERA SATISFACCION, ESTE PAGARÉ DOMICILIADO DE NO CUBRIR INTEGRALMENTE EL VALOR QUE AMPARA ESTE DOCUMENTO PRECISAMENTE EN LA FECHA DE SU VENCIMIENTO CAUSARA INTERES MORATORIOS DEL 5% MENSUAL DURANTE TODO EL TIEMPO QUE PERMANECIERE TOTAL O PARCIALMENTE INSOLUTO, SIN QUE POR ELLO SE ENTIENDA PRORROGADO EL PLAZO.</p>
                                        </td>
                                    </tr>
                                </table>
                                <table width="100%" style="margin-top: 15px;">
                                    <tr>
                                        <td width="70%" >
                                            <strong style="font-size:12px">CLIENTE:  <small style="font-size:16px; font-weight: 100;">'. $Cliente->nombreCompleto .'</small></strong>
                                        </td>
                                        <td align="center" width="30%">
                                            <table width="100%">
                                                <tr>
                                                    <td align="center" style="border-bottom-style:solid; border-width: 2px; "></td>
                                                </tr>
                                                <tr>
                                                    <td align="center" style="font-size: 14px">ACEPTA(MOS)</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                <p style="text-align:right; border-width: 1px; border-bottom-style: solid;">ORIGINAL</p>');

                                $pdf_api->AddPage();

                                $content = $this->renderFile(Yii::getAlias('@app') .'/modules/logistica/views/ruta/pagare.php',["cliente" =>  $Cliente, "copy" => true, "detail" => $arrayPreventaDetail , "foliosArray" => $foliosArray,"reparto" => $Reparto , "total" => $totalPagare]);
                                //$content = $this->renderPartial('pagare', ["cliente" =>  $Cliente, "copy" => true, "detail" => $arrayPreventaDetail , "foliosArray" => $foliosArray,"reparto" => $reparto , "total" => $totalPagare]);
                                $pdf_api->WriteHTML($content);
                                $pdf_api->setFooter('<table width="100%" style="padding-top: 5px;margin-top: 15px">
                                    <tr>
                                        <td   style="text-align:justify; ">
                                            <p style="font-size:12px;color: #000;">SE SUSCRIBE EL PRESENTE PAGARÉ EN LA CIUDAD DE __ <strong>VERACRUZ VER.</strong> __ a __ <strong>'. date("Y-m-d", time()) .'</strong> __ DEBE(MOS) Y PAGARE(MOS) INCONDICIONALMENTE POR ESTE PAGARÉ A LA ORDEN DE : __<strong>AMERICA MORALES RAMIREZ</strong>__, EN LA CUIDAD DE __ <strong>VERACRUZ</strong> __ EL DIA ____________________________ LA CANTIDAD DE: __ <strong>'. number_format($totalPagare,2) .'</strong> __  CANTIDAD QUE HE(MOS) RECIBIDO A ENTERA SATISFACCION, ESTE PAGARÉ DOMICILIADO DE NO CUBRIR INTEGRALMENTE EL VALOR QUE AMPARA ESTE DOCUMENTO PRECISAMENTE EN LA FECHA DE SU VENCIMIENTO CAUSARA INTERES MORATORIOS DEL 5% MENSUAL DURANTE TODO EL TIEMPO QUE PERMANECIERE TOTAL O PARCIALMENTE INSOLUTO, SIN QUE POR ELLO SE ENTIENDA PRORROGADO EL PLAZO.</p>
                                        </td>
                                    </tr>
                                </table>
                                <table width="100%" style="margin-top: 15px;">
                                    <tr>
                                        <td width="70%" >
                                            <strong style="font-size:12px">CLIENTE:  <small style="font-size:16px; font-weight: 100;">'. $Cliente->nombreCompleto .'</small></strong>
                                        </td>
                                        <td align="center" width="30%">
                                            <table width="100%">
                                                <tr>
                                                    <td align="center" style="border-bottom-style:solid; border-width: 2px; "></td>
                                                </tr>
                                                <tr>
                                                    <td align="center" style="font-size: 14px">ACEPTA(MOS)</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                <p style="text-align:right; border-width: 1px; border-bottom-style: solid;">COPIA</p>');



                                $count_show = $count_show + 1;

                                //if (count($model) > $count_show)
                                if (count($ventaArray) > $count_show)
                                    $pdf_api->AddPage();

                        }


                        $filename = Yii::getAlias('@webroot') . '/temp/PAGARES_DE_'.$Reparto->sucursal->nombre."-".date("Y-m-d",time()).".pdf";
                        $pdf_api->Output( $filename, \Mpdf\Output\Destination::FILE);

                        Yii::$app->mailer->compose('notificationMessage', ['message' => 'REPORTE DE PAGARES DE   '.  $Reparto->sucursal->nombre .' - ' . Esys::fecha_en_texto(time()) ])
                              ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                              ->setTo($emailArray)
                              ->attach($filename)
                              ->setSubject('NOTAS DE VENTAS DE -  '. date("Y-m-d",time()) .' ['. $Reparto->sucursal->nombre .']- '. Yii::$app->name)
                              ->send();

                    } catch (Exception $e) {

                    }*/

                    Reparto::clearRuta($Reparto->sucursal_id,$user->id);
                    return [
                        "code"    => 202,
                        "name"    => "Recoleccion",
                        "message" => 'Se genero correctamente la operación y el CIERRE DE LA RUTA',
                        "type"    => "Success",
                    ];
                }

                return [
                    "code"    => 10,
                    "name"    => "Carga de Unidad",
                    "message" => 'Ocurrio un error, intenta nuevamente',
                    "type"    => "Success",
                ];
            }

        }else{
             return [
                "code"    => 10,
                "name"    => "Recoleccion",
                "message" => 'NO SE REALIZO NINGUN CAMBIO ES NECESARIO TENER UN REPARTO EN PROCESO.',
                "type"    => "Error",
            ];
        }

        return [
            "code"    => 10,
            "name"    => "Recoleccion",
            "message" => 'Verifica tu información, intenta nuevamente',
            "type"    => "Error",
        ];
    }
}


