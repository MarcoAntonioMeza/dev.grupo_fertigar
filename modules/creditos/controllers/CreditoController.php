<?php
namespace app\modules\creditos\controllers;

use app\components\AuditLogger;
use Yii;
use yii\db\Query;
use kartik\mpdf\Pdf;
use yii\web\Response;
use app\models\credito\Credito;
use app\models\credito\ViewCredito;
use app\models\cliente\ViewCliente;
use app\models\credito\CreditoTokenPay;
use app\models\venta\Venta;
use app\models\cobro\CobroVenta;
use app\models\producto\Producto;
use app\models\credito\CreditoAbono;
use app\models\producto\ViewProducto;
use app\models\proveedor\ViewProveedor;
use app\models\inv\InvProductoSucursal;
use app\models\venta\VentaTokenPay;
use app\models\credito\ViewAbono;
use app\models\credito\ViewAbonoGlobal;
use app\models\credito\ViewProveedorCredito;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Default controller for the `clientes` module
 */
class CreditoController extends \app\controllers\AppController
{

    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('creditoCreate'),
            'update' => Yii::$app->user->can('creditoUpdate'),
            'cancel' => Yii::$app->user->can('creditoCancel'),
        ];
    }


    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index',[
            "can" => $this->can]);
    }

     /**
     * Displays a single EsysDivisa model.
     * @param integer $name
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'can'   => $this->can,
        ]);
    }

    /**
     * Creates a new Sucursal model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Credito();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['view',
                    'id' => $model->id
                ]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {

        $model = $this->findModel($id);

        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if($model->load(Yii::$app->request->post())){
            if ($model->save())
                return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionRegisterPayCliente()
    {
        return $this->render('register_pay');
    }

    public function actionRegisterPayProveedor()
    {
        return $this->render('register_pay_proveedor');
    }

    /**
     * Deletes an existing Sucursal model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param  integer $id The user id.
     * @return \yii\web\Response
     *
     * @throws NotFoundHttpException
     */
    public function actionCancel($id)
    {
        $model = $this->findModel($id);

        $model->status = Credito::STATUS_CANCEL;

        if ($model->update()) {
            Yii::$app->session->setFlash('success', "Se ha cancelado correctamente #" . $id);
        }else{
            Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la cancelacion del credito.');
        }

        return $this->redirect(['view', 'id' => $id ]);
    }

    public function actionProveedorAjax($q = false)
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($q) {
                $text = $q;

            } else {
                $text = Yii::$app->request->get('data');
                $text = $text['q'];
            }

            $user = ViewProveedor::getProveedorAjax($text,false);
            // Obtenemos user


            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $user;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['q' => $text, 'results' => $user];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionClienteAjax($q = false, $cliente_id = false)
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($q) {
                $text = $q;

            } else {
                $text = Yii::$app->request->get('data');
                $text = $text['q'];
            }

            if (is_null($text) && $cliente_id)
                $user = ViewCliente::getClienteAjax($cliente_id,true);
            else
                $user = ViewCliente::getClienteAjax($text,false);
            // Obtenemos user


            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $user;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['q' => $text, 'results' => $user];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionGetCreditoCliente()
    {
        $request = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($request["cliente_id"]) && $request["cliente_id"] ) {
            $credito = (new Query())
            ->select([
                'credito.id',
                'credito.cliente_id',
                'credito.trans_token_venta',
                'credito.venta_id',
                'credito.compra_id',
                'credito.descripcion',
                'credito.fecha_credito',
                'credito.monto',
                'credito.monto_pagado',
                'credito.nota',
                'credito.status',
                'credito.tipo',
                'credito.created_at',
                'concat_ws(" ",`user`.`nombre`,`user`.`apellidos`) AS `created_by_user`',
                'credito.created_by',
                'credito.updated_at',
                'credito.updated_by',
            ])
            ->from('credito')
            ->leftJoin('venta','credito.venta_id = venta.id')
            ->innerJoin('user','credito.created_by = user.id')
            ->andWhere(["and",
                ["=","credito.tipo", Credito::TIPO_CLIENTE ],
            ])
            ->andWhere([ "or",
                ["=","venta.cliente_id", $request["cliente_id"] ],
                ["=","credito.cliente_id", $request["cliente_id"] ]
            ])
            ->andWhere([ "or",
                ["=","credito.status", Credito::STATUS_ACTIVE ],
                ["=","credito.status", Credito::STATUS_POR_PAGADA ],
            ])
            ->orderBy("id asc")->all();

            $response = [];
            foreach ($credito as $key => $item_credito) {
                //$total_deuda     = floatval($item_credito->monto) - floatval(CobroVenta::getPagoCredito($item_credito->id));

                $ventaIDs = $item_credito["venta_id"];
                if(empty($item_credito["venta_id"])){
                    $query = VentaTokenPay::find()->andWhere([ "token_pay" => $item_credito["trans_token_venta"] ])->all();
                    foreach ($query as $key => $itemOperacion) {
                        $ventaIDs = $ventaIDs .( empty($ventaIDs) ? '' : ',' ). $itemOperacion->venta_id;
                    }
                }
                array_push($response,[
                    "id"         => $item_credito["id"],
                    "cliente_id" => $item_credito["cliente_id"],
                    "venta_id"   => $ventaIDs,
                    "compra_id"  => $item_credito["compra_id"],
                    "descripcion"=> $item_credito["descripcion"],
                    "fecha_credito" => $item_credito["fecha_credito"],
                    "monto"      => floatval($item_credito["monto"]) - floatval( $item_credito["monto_pagado"]),
                    "nota"       => $item_credito["nota"],
                    "status"     => $item_credito["status"],
                    "tipo"       => $item_credito["tipo"],
                    "created_at" => $item_credito["created_at"],
                    "created_by_user" => $item_credito["created_by_user"],
                    "created_by" => $item_credito["created_by"],
                    "updated_at" => $item_credito["updated_at"],
                    "updated_by" => $item_credito["updated_by"],
                ]);
            }
            return [
                "code" => 202,
                "credito" => $response,
            ];
        }

        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente.",
        ];
    }

    public function actionGetCreditoProveedor()
    {
        $request = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($request["proveedor_id"]) && $request["proveedor_id"] ) {
            $credito = Credito::find()->leftJoin('compra','credito.compra_id = compra.id')
            ->andWhere(["and",
                ["=","credito.tipo", Credito::TIPO_PROVEEDOR ],
            ])
            ->andWhere([ "or",
                ["=","compra.proveedor_id", $request["proveedor_id"] ],
                ["=","credito.proveedor_id", $request["proveedor_id"] ]
            ])
            ->andWhere([ "or",
                ["=","credito.status", Credito::STATUS_ACTIVE ],
                ["=","credito.status", Credito::STATUS_POR_PAGADA ],
            ])
            ->all();
            $response = [];
            foreach ($credito as $key => $item_credito) {
                //$total_deuda     = floatval($item_credito->monto) - floatval(CobroVenta::getPagoCredito($item_credito->id));
                array_push($response,[
                    "id"         => $item_credito->id,
                    "cliente_id" => $item_credito->cliente_id,
                    "venta_id"   => $item_credito->venta_id,
                    "compra_id"  => $item_credito->compra_id,
                    "descripcion"=> $item_credito->descripcion,
                    "fecha_credito" => $item_credito->fecha_credito,
                    "monto"      => floatval($item_credito->monto) - floatval( $item_credito->monto_pagado),
                    "nota"       => $item_credito->nota,
                    "status"     => $item_credito->status,
                    "tipo"       => $item_credito->tipo,
                    "created_at" => $item_credito->created_at,
                    "created_by" => $item_credito->created_by,
                    "created_by_user" => $item_credito->createdBy->nombreCompleto,
                    "updated_at" => $item_credito->updated_at,
                    "updated_by" => $item_credito->updated_by,
                ]);
            }
            return [
                "code" => 202,
                "credito" => $response,
            ];
        }

        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente.",
        ];
    }

    public function actionPostCreditoCreate()
    {
        $request = Yii::$app->request->post();

        Yii::$app->response->format = Response::FORMAT_JSON;

        AuditLogger::log('Credito - registro pago', 'Credito Pago', $request);

        $array_credito  = isset($request["listCredito"]) && count($request["listCredito"]) > 0 ? $request["listCredito"] : null;
        $array_metodo   = isset($request["metodoPagoArray"]) && count($request["metodoPagoArray"]) > 0 ? $request["metodoPagoArray"] : null;

        if ($array_credito && $array_metodo) {
            // Validacion donde los creditos afectar tengan el mismo monto de pago 
            $totalCredito   = 0;
            $totalMetodo    = 0;
            foreach ($array_credito as $key => $val_credito ) {
                if (isset($val_credito["monto"]) && floatval($val_credito["monto"]) > 0) {
                    $totalCredito += floatval($val_credito["monto"]);
                }
            }

            foreach ($array_metodo as $key => $item_pago) {
                if (isset($item_pago["cantidad"]) && floatval($item_pago["cantidad"]) > 0) {
                    $totalMetodo += floatval($item_pago["cantidad"]);
                }
            }

            if (round($totalCredito,2) !== round($totalMetodo,2)) {
                
                AuditLogger::log('Credito - error registro pago', 'Credito Pago [Error en montos]', $request);

                return [
                    "code" => 10,
                    "message" => "Los montos de los creditos y los metodos de pago no coinciden.",
                ];
            }


            $response = [];

            $token_pay = bin2hex(random_bytes(16));
            $transaction = Yii::$app->db->beginTransaction();

            try {

                foreach ($array_credito as $key => $credito) {
                    // MODIFICAMOS EL CREDITO

                    if (floatval($credito["monto"]) > 0 ) {

                        $Credito            = Credito::findOne($credito["credito_id"]);

                        $CreditoTokenPay    = new CreditoTokenPay();
                        $CreditoTokenPay->credito_id    = $Credito->id;
                        $CreditoTokenPay->token_pay     = $token_pay;
                        $CreditoTokenPay->save();

                        CreditoAbono::saveItem($Credito->id, $token_pay,floatval($credito["monto"]));

                        //$total_deuda     = floatval($Credito->monto) - floatval(CobroVenta::getPagoCredito($credito["credito_id"]));
                        $total_deuda     = round(floatval($Credito->monto),2) - round(floatval($Credito->monto_pagado),2);
                        $monto_temp      = floatval($credito["monto"]) > $total_deuda ? $total_deuda : floatval($credito["monto"]) ;

                        if (round($monto_temp,2) === round($total_deuda,2))
                            $Credito->status = Credito::STATUS_PAGADA;
                        else
                            $Credito->status = Credito::STATUS_POR_PAGADA;

                        $Credito->monto_pagado =  round(floatval($Credito->monto_pagado) + floatval($credito["monto"]),2);
                        $Credito->update();
                    }
                }

                foreach ($array_metodo as $key => $item_pago) {
                        $CobroVenta  =  new CobroVenta();
                        $CobroVenta->tipo              = CobroVenta::TIPO_CREDITO;
                        $CobroVenta->tipo_cobro_pago   = CobroVenta::PERTENECE_COBRO;
                        $CobroVenta->metodo_pago       = $item_pago["metodo_pago_id"];
                        $CobroVenta->trans_token_credito   = $token_pay;
                        $CobroVenta->cantidad              = $item_pago["cantidad"];
                        $CobroVenta->cantidad_pago         = $item_pago["cantidad"];
                        $CobroVenta->cargo_extra           = $item_pago["cargo_extra"];
                        $CobroVenta->nota_otro       = $item_pago["nota_otro"];
                        $CobroVenta->save();


                }

                $transaction->commit();

                return [
                    "code" => 202,
                    "credito" => $token_pay,
                ];

            }catch(\Exception $e) {

                $transaction->rollback();

                return [
                    "code" => 10,
                    "message" => "Ocurrio un error, intenta nuevamente.",
                ];
            }

        }
        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente.",
        ];
    }

    public function actionPostGastoCreate()
    {
        $request = Yii::$app->request->post();

        if ($request["Credito"]["titulo_gasto"] && $request["Credito"]["proveedor_id"] && $request["Credito"]["monto"] && $request["Credito"]["fecha_credito"]) {

            $Credito                = new Credito();
            $Credito->proveedor_id  =  $request["Credito"]["proveedor_id"];
            $Credito->monto         =  str_replace(",","",$request["Credito"]["monto"]);
            $Credito->fecha_credito =  strtotime($request["Credito"]["fecha_credito"]);
            $Credito->titulo_gasto  =  $request["Credito"]["titulo_gasto"];
            $Credito->descripcion   =  $request["Credito"]["descripcion"];
            $Credito->pertenece     =  Credito::PERTENECE_GASTO;
            $Credito->tipo          =  Credito::TIPO_PROVEEDOR;
            $Credito->status        =  Credito::STATUS_ACTIVE;

            if ($Credito->save()) {
                 Yii::$app->session->setFlash('success', "SE REGISTRO CORRECTAMENTE #" . $Credito->id);
                 return $this->redirect(['view', 'id' => $Credito->id ]);
            }
        }
        Yii::$app->session->setFlash('danger', "OCURRIO UN ERROR, INTENTA NUEVAMENTE");
        return $this->redirect(['view', 'id' => $request["Credito"]["credito_id"] ]);
    }

    public function actionImprimirCredito($pay_items)
    {
        //$pay_id = explode(',', $pay_items);


        $lengh = 270;
        $width = 72;
        $count = 0;

        $model = [];

        ///foreach ($pay_id as $key => $payment) {
            $getCreditos = CreditoTokenPay::find()->andWhere([ "token_pay" => $pay_items ])->all();

            $lengh = $lengh + ( 80 * count($getCreditos));

            foreach ($getCreditos as $key => $item_credito) {
                //$CobroVenta = CobroVenta::findOne($payment);
                $credito = Credito::find()->where(['id' => $item_credito->credito_id])->one();
                array_push($model,[
                    "credito_id"   => $item_credito->credito_id,
                    //"cantidad"  => $CobroVenta->cantidad,
                    "credito"           => $credito,
                    "venta"             => isset($item_credito->credito->venta->id) ? $item_credito->credito->venta->id : '00',
                    "cantidad_credito"  => $item_credito->credito->monto,
                    "total_abonado"     => $item_credito->credito->monto_pagado,
                ]);
            }
        //}

        $content = $this->renderPartial('ticket-credito', ["model" => $model, 'token' => $pay_items]);

        ini_set('memory_limit', '-1');

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => array($width, $lengh),//Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline' => '.kv-heading-1{font-size:18px}',
             // set mPDF properties on the fly
            'options' => ['title' => 'Ticket de envio'],
             // call mPDF methods on the fly
            'methods' => [
                //'SetHeader'=>[ 'TICKET'],
                //'SetFooter'=>['{PAGENO}'],
            ]
        ]);

        $pdf->marginLeft = 1;
        $pdf->marginRight = 1;

        $pdf->setApi();

        // return the pdf output as per the destination setting
        return $pdf->render();
    }

    public function actionGetHistoryOperacion()
    {
        $request = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($request["credito_id"]) && $request["credito_id"] ) {
            $credito = Credito::findOne($request["credito_id"]);
            $response = [];

            if (round(floatval($credito->monto_pagado),2) != round(floatval(CreditoAbono::getSumaAbono($credito->id)),2)) {
                array_push($response,[
                    "id"         => 0,
                    "cantidad"   => round(floatval($credito->monto_pagado) -  floatval(CreditoAbono::getSumaAbono($credito->id))),
                    "token_pay"  => "----",
                    "status_text"=> "AJUSTE - CREDITO",
                    "status"     => 0,
                    "created_at" => "----",
                    "empleado"   => "----",
                ]);
            }

            foreach ($credito->abono as $key => $item_transaccion) {
                //$total_deuda     = floatval($item_credito->monto) - floatval(CobroVenta::getPagoCredito($item_credito->id));
                array_push($response,[
                    "id"         => $item_transaccion->id,
                    "cantidad"   => $item_transaccion->cantidad,
                    "token_pay"  => $item_transaccion->token_pay,
                    "status_text"=> CreditoAbono::$statusList[$item_transaccion->status],
                    "status"     => $item_transaccion->status,
                    "created_at" => date("Y-m-d h:i:s",$item_transaccion->created_at),
                    "empleado"   => $item_transaccion->createdBy->nombreCompleto ,
                    "updated_at" => $item_transaccion->updated_by ? date("Y-m-d h:i:s a",$item_transaccion->updated_at) : '-----',
                    "modificado" => $item_transaccion->updated_by ? $item_transaccion->updatedBy->nombreCompleto : '----',
                ]);
            }
            return [
                "code" => 202,
                "transaccion" => $response,
            ];
        }

        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente.",
        ];

    }

    public function actionGetHistoryPago()
    {
        $request = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($request["opera_token_pay"]) && $request["opera_token_pay"] ) {
            $CreditoAbono = CreditoAbono::find()->andWhere([ "token_pay" => $request["opera_token_pay"] ])->all();
            $response = [];
            foreach ($CreditoAbono as $key => $item_transaccion) {
                array_push($response,[
                    "id"         => $item_transaccion->id,
                    "credito_id" => $item_transaccion->credito_id,
                    "cantidad"   => $item_transaccion->cantidad,
                    "token_pay"  => $item_transaccion->token_pay,
                    "status_text"=> CreditoAbono::$statusList[$item_transaccion->status],
                    "status"     => $item_transaccion->status,
                    "created_at" => date("Y-m-d h:i:s",$item_transaccion->created_at),
                    "empleado"   => $item_transaccion->createdBy->nombreCompleto ,
                    "updated_at" => $item_transaccion->updated_by ? date("Y-m-d h:i:s a",$item_transaccion->updated_at) : '-----',
                    "modificado" => $item_transaccion->updated_by ? $item_transaccion->updatedBy->nombreCompleto : '----',
                ]);
            }
            return [
                "code" => 202,
                "transaccion" => $response,
            ];
        }

        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente.",
        ];

    }


    public function actionGetTokenVentas()
    {
        $request = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($request["credito_id"]) && $request["credito_id"] ) {
            $credito    = Credito::findOne($request["credito_id"]);
            $ventaToken = VentaTokenPay::find()->andWhere([ "token_pay" => $credito->trans_token_venta ])->all();
            $response = [];
            foreach ($ventaToken as $key => $item_token) {
                //$total_deuda     = floatval($item_credito->monto) - floatval(CobroVenta::getPagoCredito($item_credito->id));
                array_push($response,[
                    "id"            => $item_token->venta->id,
                    "folio"         => str_pad($item_token->venta->id,6,"0",STR_PAD_LEFT),
                    "total"         => $item_token->venta->total,
                    "sucursal"      => isset($item_token->venta->sucursal->nombre) ? $item_token->venta->sucursal->nombre : null,
                    "created_at"    => date("Y-m-d h:i:s",$item_token->created_at),
                    "empleado"      => $item_token->createdBy->nombreCompleto,
                ]);
            }
            return [
                "code" => 202,
                "ventas" => $response,
            ];
        }

        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente.",
        ];

    }

    public function actionGetHistoryOperacionCliente()
    {
        $request = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($request["cliente_id"]) && $request["cliente_id"] ) {
            $creditoAll = Credito::find()->leftJoin('venta','credito.venta_id = venta.id')
            ->andWhere(["and",
                ["=","credito.tipo", Credito::TIPO_CLIENTE ],
            ])
            ->andWhere([ "or",
                ["=","venta.cliente_id", $request["cliente_id"] ],
                ["=","credito.cliente_id", $request["cliente_id"] ]
            ])
            ->andWhere([ "or",
                ["=","credito.status", Credito::STATUS_ACTIVE ],
                ["=","credito.status", Credito::STATUS_POR_PAGADA ],
            ])
            ->all();

            $response = [];
            foreach ($creditoAll as $key => $item_credito) {
                foreach ($item_credito->abono as $key => $item_transaccion) {
                    array_push($response,[
                        "id"         => $item_transaccion->id,
                        "credito_id" => $item_transaccion->credito_id,
                        "cantidad"   => $item_transaccion->cantidad,
                        "token_pay"  => $item_transaccion->token_pay,
                        "status_text"=> CreditoAbono::$statusList[$item_transaccion->status],
                        "status"     => $item_transaccion->status,
                        "created_at" => date("Y-m-d h:i:s",$item_transaccion->created_at),
                        "empleado"   => $item_transaccion->createdBy->nombreCompleto ,
                    ]);
                }
            }

            return [
                "code" => 202,
                "transaccion" => $response,
            ];
        }

        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente.",
        ];

    }

    public function actionGetSaldosRuta(){
        return ViewAbono::getJsonBtt(Yii::$app->request->get());
    }
    public function actionGetHistoryOperacionClienteGlobal()
    {
        return ViewAbonoGlobal::getJsonBtt(Yii::$app->request->get());
    }

    public function actionGetHistoryOperacionProveedor()
    {
        $request = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($request["proveedor_id"]) && $request["proveedor_id"] ) {
            $creditoAll = Credito::find()->leftJoin('compra','credito.compra_id = compra.id')
            ->andWhere(["and",
                ["=","credito.tipo", Credito::TIPO_PROVEEDOR ],
            ])
            ->andWhere([ "or",
                ["=","compra.proveedor_id", $request["proveedor_id"] ],
                ["=","credito.proveedor_id", $request["proveedor_id"] ]
            ])
            ->andWhere([ "or",
                ["=","credito.status", Credito::STATUS_ACTIVE ],
                ["=","credito.status", Credito::STATUS_POR_PAGADA ],
            ])
            ->all();

            $response = [];
            foreach ($creditoAll as $key => $item_credito) {
                foreach ($item_credito->abono as $key => $item_transaccion) {
                    array_push($response,[
                        "id"         => $item_transaccion->id,
                        "credito_id" => $item_transaccion->credito_id,
                        "cantidad"   => $item_transaccion->cantidad,
                        "token_pay"  => $item_transaccion->token_pay,
                        "status_text"=> CreditoAbono::$statusList[$item_transaccion->status],
                        "status"     => $item_transaccion->status,
                        "created_at" => date("Y-m-d h:i:s",$item_transaccion->created_at),
                        "empleado"   => $item_transaccion->createdBy->nombreCompleto ,
                    ]);
                }
            }

            return [
                "code" => 202,
                "transaccion" => $response,
            ];
        }

        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente.",
        ];

    }
    public function actionGetCompraVenta($token_pay){
        $cobros = CreditoAbono::findOne(['token_pay' => $token_pay]);
        $credito = Credito::findOne(['id' => $cobros->credito_id]);

        return $credito->id;
    }
    public function actionGetHistoryOperacionProveedorGlobal()
    {
        return ViewProveedorCredito::getJsonBtt(Yii::$app->request->get());
    }

    public function actionDeleteAbono()
    {
        $request = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $abono_id  = isset($request["abono_id"]) ? $request["abono_id"] : null;
        if ($abono_id) {
            $CreditoAbono = CreditoAbono::findOne($abono_id);
            $CreditoAbono->status = CreditoAbono::STATUS_CANCEL;
            if ($CreditoAbono->update()) {
                $Credito                = Credito::findOne($CreditoAbono->credito_id);
                $Credito->monto_pagado  = $Credito->monto_pagado - $CreditoAbono->cantidad;


                if ($Credito->monto_pagado > 0 )
                    $Credito->status = Credito::STATUS_POR_PAGADA;
                else
                    $Credito->status = Credito::STATUS_ACTIVE;


                $Credito->update();

                return [
                    "code" => 202,
                    "message" => "SE REALIZO CORRECTAMENTE LA CANCELACION DEL ABONO AL CREDITO",
                ];
            }
        }
        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente.",
        ];
    }

    public function actionProductoAjax($q = false)
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($q) {
                $text = $q;

            } else {
                $text = Yii::$app->request->get('data');
                $text = $text['q'];
            }

            $user = ViewProducto::getProductoSeachAjax($text);
            // Obtenemos user


            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $user;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['q' => $text, 'results' => $user];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionGetProducto()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('producto_id')) {

                $producto  = Producto::findOne(trim($request->get('producto_id')));
                return [
                    "code" => 202,
                    "producto" => [
                        "id" => $producto->id,
                        "producto" => $producto->nombre,
                        "tipo_text" => Producto::$medidaList[$producto->tipo_medida],
                    ],
                ];
            }

            return [
                "code" => 10,
                "message" => "Error al buscar el producto, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }


    //------------------------------------------------------------------------------------------------//
    // BootstrapTable list
    //------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionCreditosJsonBtt(){
        return ViewCredito::getJsonBtt(Yii::$app->request->get());
    }
    public function actionCreditosAbonoJsonBtt(){

        return ViewAbono::getJsonBtt(Yii::$app->request->get());
    }
    
    public function actionPagosDetailJsonBtt()
    {
        return ViewCredito::getPagosJsonBtt(Yii::$app->request->get());
    }

 //------------------------------------------------------------------------------------------------//
// HELPERS
//------------------------------------------------------------------------------------------------//
    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @return Model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($name, $_model = 'model')
    {
        switch ($_model) {
            case 'model':
                $model = Credito::findOne($name);
                break;

            case 'view':
                $model = ViewCredito::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
