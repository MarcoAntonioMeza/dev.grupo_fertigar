<?php
namespace app\modules\logistica\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use app\models\venta\ViewVenta;
use app\models\reparto\Reparto;
use app\models\reparto\ViewReparto;
use app\models\reparto\RepartoDetalle;
use app\models\sucursal\Sucursal;
use app\models\venta\Venta;
use app\models\cliente\Cliente;
use app\models\producto\ViewProducto;
use app\models\venta\VentaDetalle;
use app\models\inv\InvProductoSucursal;
use app\models\venta\TransVenta;
use app\models\venta\VentaTokenPay;
use app\models\cobro\CobroVenta;
use app\models\producto\Producto;
use app\models\temp\TempVentaRuta;
use app\models\temp\TempCobroRutaVenta;

/**
 * Default controller for the `clientes` module
 */
class RutaController extends \app\controllers\AppController
{

	private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('repartoCreate'),
            'update' => Yii::$app->user->can('repartoUpdate'),
            'delete' => Yii::$app->user->can('repartoDelete'),
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
        $model = new Reparto();
        $model->reparto_detalle = new RepartoDetalle();

        if ($model->load(Yii::$app->request->post()) && $model->reparto_detalle->load(Yii::$app->request->post()) ) {
            $model->status = Reparto::STATUS_PROCESO;
            if ($model->save()) {
                if ($model->reparto_detalle->saveDetalleReparto($model->id)) {
                    return $this->redirect(['view',
                        'id' => $model->id
                    ]);
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionGetPrecapturaSucursal()
    {
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('sucursal_id')) {

                $GetPrecaptura      = ViewVenta::getVentaPreCaptura($request->get('sucursal_id'), $request->get('pertenece_id'));

                foreach ($GetPrecaptura as $key => $item_precaptura) {
                    $venta_detail       = VentaDetalle::find()->andWhere([ "venta_id" => $item_precaptura["id"]])->all();

                    $is_abastecimiento  = 10;
                    foreach ($venta_detail as $key2 => $item_detail) {
                        $cantidadPreventa = Venta::getProductoPreCapturado($item_detail->producto_id,$item_precaptura["sucursal_id"]);
                        $cantidadPreventa = $cantidadPreventa["cantidad_precaptura"] ? $cantidadPreventa["cantidad_precaptura"] : 0;
                        $cantidadAlmacen  = InvProductoSucursal::getStockProducto($item_detail->producto_id,$item_precaptura["sucursal_id"]);
                        $cantidadAlmacen  = isset($cantidadAlmacen->cantidad) ? $cantidadAlmacen->cantidad  : 0;

                        if ( $cantidadPreventa > $cantidadAlmacen)
                            $is_abastecimiento = 20;

                        if ($item_detail->cantidad == 0 && $item_detail->is_conversion == VentaDetalle::CONVERSION_ON)
                            $is_abastecimiento = 20;
                    }

                    $GetPrecaptura[$key]["is_abastecimiento"] = $is_abastecimiento;
                }

                return [
                    "code" => 202,
                    "precaptura" => $GetPrecaptura,
                ];
            }
            return [
                "code" => 10,
                "message" => "Error al buscar la ruta / sucursal, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }


    public function actionGetPedido()
    {
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('pertenece_id')) {

                $GetPedido      = ViewVenta::getListaCompraProductoPreCaptura($request->get('pertenece_id'), $request->get('sucursal_id'));

                return [
                    "code" => 202,
                    "pedido" => $GetPedido,
                ];
            }
            return [
                "code" => 10,
                "message" => "Error al buscar la sucursal, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionGetPrecapturasInventario()
    {
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($request->get('preventa_id')) {

                $venta_detail       = VentaDetalle::find()->andWhere([ "venta_id" => $request->get('preventa_id') ])->all();

                $array_detail       = [];

                foreach ($venta_detail as $key2 => $item_detail) {

                    $is_abastecimiento  = 10;

                    $cantidadPreventa = Venta::getProductoPreCapturado($item_detail->producto_id,$item_detail->venta->sucursal_id);
                    $cantidadPreventa = $cantidadPreventa["cantidad_precaptura"] ? $cantidadPreventa["cantidad_precaptura"] : 0;
                    $cantidadAlmacen  = InvProductoSucursal::getStockProducto($item_detail->producto_id,$item_detail->venta->sucursal_id);
                    $cantidadAlmacen  = isset($cantidadAlmacen->cantidad) ? $cantidadAlmacen->cantidad  : 0;


                    if ($cantidadPreventa > $cantidadAlmacen)
                        $is_abastecimiento = 20;


                    array_push($array_detail,[
                        "producto_id"           => $item_detail->producto_id,
                        "producto"              => $item_detail->producto->nombre,
                        "sucursal_id"           => $item_detail->venta->sucursal_id,
                        "abastecimiento"        => $is_abastecimiento,
                        "abastecimiento_ventas" => Venta::getProductoPreCapturadoAll($item_detail->producto_id,$item_detail->venta->sucursal_id),
                        "inventario"  => isset(InvProductoSucursal::getStockProducto($item_detail->producto_id, $item_detail->venta->sucursal_id)->cantidad) ? InvProductoSucursal::getStockProducto($item_detail->producto_id, $item_detail->venta->sucursal_id)->cantidad : 0,
                        "preventas"   => [],
                    ]);
                }


                foreach ($array_detail as $key3 => $item_detail) {
                    $preventaAll = VentaDetalle::find()
                                ->innerJoin("venta","venta_detalle.venta_id = venta.id")
                                ->andWhere(["and",
                                    ["=", "venta.sucursal_id", $item_detail["sucursal_id"] ],
                                    //["=", "venta.is_especial", Venta::VENTA_GENERAL ],
                                    ["=", "venta_detalle.producto_id", $item_detail["producto_id"] ],
                                    ["=", "venta_detalle.venta_id", $request->get('preventa_id') ], // SE AGREGO PARA SOLO MOSTRAR LOS PRODUCTOS DE SU PREVENTA Y NO TODOS LOS INVOLUCRADOS
                                    ["=", "venta.status", Venta::STATUS_PRECAPTURA ]
                                ])->all();






                    foreach ($preventaAll as $key => $item_precaptura) {
                        array_push($array_detail[$key3]["preventas"],[
                            "id"           => $item_precaptura->id,
                            "preventa_id"  => $item_precaptura->venta_id,
                            "cliente"      => $item_precaptura->venta->cliente_id ? $item_precaptura->venta->cliente->nombreCompleto : '** PUBLICO EN GENERAL **',
                            "telefono"     => $item_precaptura->venta->cliente_id ? $item_precaptura->venta->cliente->telefono_movil : 'N/A',
                            "cantidad"     => $item_precaptura->cantidad,
                            "is_conversion"  => $item_precaptura->is_conversion,
                            "conversion_cantidad"=> $item_precaptura->conversion_cantidad,
                            "producto"     => $item_precaptura->producto->nombre,
                            "tipo_medida_text" => Producto::$medidaList[$item_precaptura->producto->tipo_medida],
                            "vendedor"     => $item_precaptura->venta->createdBy->nombreCompleto,
                        ]);
                    }
                }
                return [
                    "code" => 202,
                    "precaptura" => $array_detail,
                ];
            }
            return [
                "code" => 10,
                "message" => "Error al buscar la ruta / sucursal, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');

    }

    public function actionDownloadPedidoPdf($sucursal_id, $ruta_id)
    {
        ini_set('memory_limit', '-1');

        $content = $this->renderPartial('lista-pedido',[ "sucursal" => Sucursal::findOne($sucursal_id), "ruta_id" => $ruta_id ]);

         $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => Pdf::FORMAT_LETTER,
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
            'options' => ['title' => 'Acuse'],
             // call mPDF methods on the fly
        ]);


        return $pdf->render();

    }



    public function actionUpdatePreventa()
    {
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($request->post('preventa_detail_id') && $request->post('cantidad')) {

                $venta_detail       = VentaDetalle::findOne($request->post('preventa_detail_id'));
                $venta_detail->cantidad = $request->post('cantidad');

                $venta = Venta::findOne($venta_detail->venta_id);
                $new_total = 0;

                foreach ($venta->ventaDetalle as $key => $item_ventaDetalle) {
                    $new_total = $new_total + floatval($item_ventaDetalle->cantidad * $item_ventaDetalle->precio_venta);
                }

                $venta->total = round($new_total,2);

                if ($venta_detail->update()) {
                    $venta->update();
                    return [
                        "code" => 202,
                        "message" => 'Se realizo correctamente el cambio',
                    ];
                }
            }
            return [
                "code" => 10,
                "message" => "Error al buscar la ruta / sucursal, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionRemovePreventa()
    {
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($request->post('preventa_detail_id')) {

                $venta_detail       = VentaDetalle::findOne($request->post('preventa_detail_id'));
                RepartoDetalle::deleteVenta($venta_detail->id);

                $venta = Venta::findOne($venta_detail->venta_id);

                if ($venta_detail->delete()) {

                    $new_total = 0;
                    foreach ($venta->ventaDetalle as $key => $item_ventaDetalle) {
                        $new_total = $new_total + floatval($item_ventaDetalle->cantidad * $item_ventaDetalle->precio_venta);
                    }

                    $venta->total = $new_total;
                    if (count($venta->ventaDetalle) ==  0) {
                        $venta->status = Venta::STATUS_CANCEL;
                    }
                    $venta->update();

                    return [
                        "code" => 202,
                        "message" => 'Se removio correctamente el producto de la preventa',
                    ];
                }
            }
            return [
                "code" => 10,
                "message" => "Error al buscar la ruta / sucursal, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionEnviarRutaTraspaso($id)
    {
        $Reparto = Reparto::findOne($id);
        if ($Reparto->movInventarioReparto()) {
            Yii::$app->session->setFlash('success', "SE REALIZO EL TRASPASO DE PRODUCTO CORRECTAMENTE");

            return $this->redirect(['view','id' => $id]);
        }
        Yii::$app->session->setFlash('danger', "OCURRIO UN ERROR DURANTE EL PROCESO, INTENTA NUEVAMENTE");

        return $this->redirect(['view','id' => $id]);
    }

    public function actionEnviarReparto($id)
    {
        $Reparto = Reparto::findOne($id);
        $Reparto->status = Reparto::STATUS_RUTA;
        if ($Reparto->update()) {
            Yii::$app->session->setFlash('success', "SE REALIZO EL ENVIO DEL REPARTO CORRECTAMENTE");

            return $this->redirect(['view','id' => $id]);
        }
        Yii::$app->session->setFlash('danger', "OCURRIO UN ERROR DURANTE EL PROCESO, INTENTA NUEVAMENTE");

        return $this->redirect(['view','id' => $id]);
    }


    public function actionAbrirReparto($id)
    {
        $Reparto = Reparto::findOne($id);
        $Reparto->status = Reparto::STATUS_PROCESO;
        if ($Reparto->update()) {
            Yii::$app->session->setFlash('success', "SE REALIZO LA APERTURA CORRECTAMENTE");
            return $this->redirect(['view','id' => $id]);
        }
        Yii::$app->session->setFlash('danger', "OCURRIO UN ERROR DURANTE EL PROCESO, INTENTA NUEVAMENTE");

        return $this->redirect(['view','id' => $id]);
    }



    public function actionDelete($id)
    {
        try{

            $ruta = $this->findModel($id);
            if ($ruta->status == Reparto::STATUS_PROCESO ) {

                foreach ($ruta->repartoDetalles as $key => $r_detalle) {
                    $r_detalle->delete();
                }

                $ruta->delete();

                Yii::$app->session->setFlash('success', "Se ha eliminado correctamente la carga #" . $id);
            }else
                Yii::$app->session->setFlash('warning', "La carga #" . $id. " no puede ser eliminada, contacta al administrador");

        }catch(\Exception $e){
            if($e->getCode() === 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación del reparto .');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }

        return $this->redirect(['index']);
    }

    public function actionHabilitarReparto($id)
    {
        $model = $this->findModel($id);
        $model->status = Reparto::STATUS_RUTA;
        if ($model->update()) {

            Yii::$app->session->setFlash('success', 'Se habilito correctamente el reparto');
            return $this->redirect(['view',
                'id' => $model->id
            ]);
        }
        return $this->redirect(['view',
            'id' => $model->id
        ]);

    }

    public function actionImprimirAcusePdf($reparto_id)
    {

        $Reparto = $this->findModel($reparto_id);

        ini_set('memory_limit', '-1');

        $content = $this->renderPartial('acuse', ["model" => $Reparto]);

         $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => Pdf::FORMAT_LETTER,
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
            'options' => ['title' => 'Acuse'],
             // call mPDF methods on the fly
        ]);


        return $pdf->render();

    }

    public function actionImprimirSaldoPdf($reparto_id)
    {

        $Reparto = $this->findModel($reparto_id);

        ini_set('memory_limit', '-1');

        $content = $this->renderPartial('acuse-cliente-saldo', ["model" => $Reparto]);

         $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => Pdf::FORMAT_LETTER,
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
            'options' => ['title' => 'Acuse'],
             // call mPDF methods on the fly
        ]);


        return $pdf->render();

    }

    public function actionImprimirCuentaPdf($reparto_id)
    {

        $Reparto = $this->findModel($reparto_id);

        ini_set('memory_limit', '-1');

        $content = $this->renderPartial('cuenta', ["model" => $Reparto]);

         $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => Pdf::FORMAT_LETTER,
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
            'options' => ['title' => 'Acuse'],
             // call mPDF methods on the fly
        ]);


        return $pdf->render();

    }



    public function actionDownloadEmbarquePdf($embarque, $ruta_sucursal_id)
    {

        ini_set('memory_limit', '-1');

        $Sucursal = Sucursal::findOne($ruta_sucursal_id);



        $content = $this->renderPartial('embarque', ["sucursal" => $Sucursal, "embarque" => $embarque ]);

         $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => Pdf::FORMAT_LETTER,
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
            'options' => ['title' => 'Acuse'],
             // call mPDF methods on the fly
        ]);


        return $pdf->render();

    }


    public function actionImprimirPagarePdf($reparto_id)
    {

        $ventaArray = [];
        $reparto = Reparto::findOne($reparto_id);
        foreach (Reparto::getPrecapturaCliente($reparto_id) as $key => $item_precaptura) {
            if ($item_precaptura["cliente_id"])
                array_push($ventaArray,$item_precaptura["cliente_id"]);
        }


        ini_set('memory_limit', '-1');


        $content = "";

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => Pdf::FORMAT_LETTER,
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
            'options' => ['title' => 'Acuse'],
             // call mPDF methods on the fly
        ]);


        $pdf->setApi();
        $pdf_api = $pdf->getApi();
        $count_show = 0;
        foreach ($ventaArray as $key => $item_cliente) {

                $Cliente = Cliente::findOne($item_cliente);

                $Reparto = Reparto::getPreventaAll($item_cliente, $reparto_id);

                $foliosArray = [];
                $totalPagare = 0;
                $arrayPreventaDetail = [];
                foreach ($Reparto as $key => $item_operacion) {
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

                $content = $this->renderPartial('pagare', ["cliente" =>  $Cliente, "copy" => false, "detail" => $arrayPreventaDetail, "foliosArray" => $foliosArray, "reparto" => $reparto, "total" => $totalPagare ]);

                $pdf_api->WriteHTML($content);

                $pdf_api->setFooter('<table width="100%" style="padding-top: 5px;margin-top: 15px">
                    <tr>
                        <td   style="text-align:justify; ">
                            <p style="font-size:12px;color: #000;">SE SUSCRIBE EL PRESENTE PAGARÉ EN LA CIUDAD DE __ <strong>VERACRUZ VER.</strong> __ a __ <strong>'. date("Y-m-d", time()) .'</strong> __ DEBE(MOS) Y PAGARE(MOS) INCONDICIONALMENTE POR ESTE PAGARÉ A LA ORDEN DE : __<strong>PESCADOS Y MARISCOS ARROYOS SA DE CV</strong>__, EN LA CUIDAD DE __ <strong>VERACRUZ</strong> __ EL DIA ____________________________ LA CANTIDAD DE: __ <strong>'. number_format($totalPagare,2) .'</strong> __  CANTIDAD QUE HE(MOS) RECIBIDO A ENTERA SATISFACCION, ESTE PAGARÉ DOMICILIADO DE NO CUBRIR INTEGRALMENTE EL VALOR QUE AMPARA ESTE DOCUMENTO PRECISAMENTE EN LA FECHA DE SU VENCIMIENTO CAUSARA INTERES MORATORIOS DEL 5% MENSUAL DURANTE TODO EL TIEMPO QUE PERMANECIERE TOTAL O PARCIALMENTE INSOLUTO, SIN QUE POR ELLO SE ENTIENDA PRORROGADO EL PLAZO.</p>
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

                $content = $this->renderPartial('pagare', ["cliente" =>  $Cliente, "copy" => true, "detail" => $arrayPreventaDetail , "foliosArray" => $foliosArray,"reparto" => $reparto , "total" => $totalPagare]);
                $pdf_api->WriteHTML($content);
                $pdf_api->setFooter('<table width="100%" style="padding-top: 5px;margin-top: 15px">
                    <tr>
                        <td   style="text-align:justify; ">
                            <p style="font-size:12px;color: #000;">SE SUSCRIBE EL PRESENTE PAGARÉ EN LA CIUDAD DE __ <strong>VERACRUZ VER.</strong> __ a __ <strong>'. date("Y-m-d", time()) .'</strong> __ DEBE(MOS) Y PAGARE(MOS) INCONDICIONALMENTE POR ESTE PAGARÉ A LA ORDEN DE : __<strong>PESCADOS Y MARISCOS ARROYOS SA DE CV</strong>__, EN LA CUIDAD DE __ <strong>VERACRUZ</strong> __ EL DIA ____________________________ LA CANTIDAD DE: __ <strong>'. number_format($totalPagare,2) .'</strong> __  CANTIDAD QUE HE(MOS) RECIBIDO A ENTERA SATISFACCION, ESTE PAGARÉ DOMICILIADO DE NO CUBRIR INTEGRALMENTE EL VALOR QUE AMPARA ESTE DOCUMENTO PRECISAMENTE EN LA FECHA DE SU VENCIMIENTO CAUSARA INTERES MORATORIOS DEL 5% MENSUAL DURANTE TODO EL TIEMPO QUE PERMANECIERE TOTAL O PARCIALMENTE INSOLUTO, SIN QUE POR ELLO SE ENTIENDA PRORROGADO EL PLAZO.</p>
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
        return $pdf->render();

    }

    public function actionRutaReporteInventario($reparto_id)
    {
        $request = Yii::$app->request;
        $objPHPExcel = new \PHPExcel();


        $sheet=0;

        $objPHPExcel->setActiveSheetIndex($sheet);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);

        $objPHPExcel->getActiveSheet()->setTitle('REPORTE DE INVENTARIO');

        $row=1;

        $concentradoProducto = ViewReparto::getConcentradoProducto($reparto_id);

        foreach ($concentradoProducto as $key => $item_concentrado) {
            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(40);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':F'.$row)->getFill()->getStartColor()->setRGB('9A7D0A');
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':F'.$row)->applyFromArray(array(
                'borders' => array(
                  'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                  )
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':F'.$row)->getFont()->setBold(true);


            $objPHPExcel->getActiveSheet()
                ->setCellValue('A'.$row, 'PRODUCTO')
                ->setCellValue('D'.$row, 'CANTIDAD');

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':C'.$row);
            $objPHPExcel->getActiveSheet()->mergeCells('D'.$row.':F'.$row);

            $row = $row + 1;


            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':F'.$row)->applyFromArray(array(
                'borders' => array(
                  'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                  )
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
            ));


            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,$item_concentrado["producto"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$row,$item_concentrado["cantidad_total"]);

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':C'.$row);
            $objPHPExcel->getActiveSheet()->mergeCells('D'.$row.':F'.$row);

            $row = $row + 1;


            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,"DESGLOSE" );
            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(20);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':F'.$row)->getFill()->getStartColor()->setRGB('9A7D0A');
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':F'.$row)->applyFromArray(array(
                'borders' => array(
                  'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                  )
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'color' => array('rgb' => 'FFFFFF'),
                )
            ));

            $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':F'.$row);

            $row = $row + 1;

            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':F'.$row)->applyFromArray(array(
                'borders' => array(
                  'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                  )
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
            ));

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row,"CLIENTE" );
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':B'.$row);

            $objPHPExcel->getActiveSheet()->setCellValue('C'.$row,"CANTIDAD ASIGNADA" );
            $objPHPExcel->getActiveSheet()->mergeCells('C'.$row.':D'.$row);

            $objPHPExcel->getActiveSheet()->setCellValue('E'.$row,"CANTIDAD VENDIDA" );
            $objPHPExcel->getActiveSheet()->mergeCells('E'.$row.':F'.$row);

            $row = $row + 1;
            $degloseInventario = ViewReparto::getDegloseInventario($reparto_id, $item_concentrado["producto_id"]);


            foreach ($degloseInventario as $key => $item_desglose) {

                $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':F'.$row)->applyFromArray(array(
                    'borders' => array(
                      'allborders' => array(
                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                      )
                    ),
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                ));

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $item_desglose['tipo'] == RepartoDetalle::TIPO_PRECAPTURA ? $item_desglose['cliente'] : '** TARA ABIERTA **' );
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':B'.$row);

                $objPHPExcel->getActiveSheet()->setCellValue('C'.$row, $item_desglose['cantidad_asignada'] );
                $objPHPExcel->getActiveSheet()->mergeCells('C'.$row.':D'.$row);

                $producto_count = 0;

                if ($item_desglose['tipo'] == RepartoDetalle::TIPO_PRODUCTO) {
                    $Ventas = Venta::find()->andWhere(["and",
                        ["=","reparto_id", $reparto_id ],
                        ["=", "status",  Venta::STATUS_VENTA ],
                        //["=", "is_tpv_ruta",  Venta::IS_TPV_RUTA_OFF ],
                    ])->all();

                    foreach ($Ventas as $key => $venta) {
                        foreach ($venta->ventaDetalle as $key => $DetalleItem) {
                            if ($DetalleItem->producto_id == $item_concentrado["producto_id"] ) {
                                $producto_count = $producto_count + floatval($DetalleItem->cantidad);
                            }
                        }
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$row, $producto_count );
                }else{
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$row, $item_desglose['venta_status'] == Venta::STATUS_VENTA ? $item_desglose['venta_cantidad'] : 0 );
                }

                $objPHPExcel->getActiveSheet()->mergeCells('E'.$row.':F'.$row);
                $row = $row + 1;
            }

            $row = $row + 2;
        }


        header('Content-Type: application/vnd.ms-excel');

        $filename = "Reporte-inventario-ruta_".date("d-m-Y-His").".xls";

        header('Content-Disposition: attachment;filename='.$filename .' ');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();

        $objWriter->save('php://output');

    }

    public function actionSaveAjusteRuta()
    {
        $request = Yii::$app->request->post()["Carga"];
        if (isset($request["tipo"])) {
            $ruta           = Reparto::findOne($request["ruta_id"]);
            $sucursal_id    = Yii::$app->user->identity->sucursal_id;
            $movimiento_detalle = [];

            if ($request["tipo"] == RepartoDetalle::TIPO_PRECAPTURA) {
                $arrayItems = json_decode($request["inputArrayItems"]);
                if ($arrayItems) {
                    foreach ($arrayItems as $key => $item_movimiento) {
                        if (intval($item_movimiento->check_true) == 10 ) {
                            $getVenta = Venta::findOne($item_movimiento->item_id);
                            foreach ($getVenta->ventaDetalle as $key => $item_venta_detalle) {
                                array_push($movimiento_detalle, [
                                    "producto_id"   => $item_venta_detalle->producto_id,
                                    "cantidad"      => $item_venta_detalle->cantidad,
                                    "venta_id"      => $item_venta_detalle->venta_id,
                                    "venta_detalle_id"  => $item_venta_detalle->id,
                                    "tipo"              => 10,
                                ]);
                            }
                        }
                    }
                }
            }

            if ($request["tipo"] == RepartoDetalle::TIPO_PRODUCTO) {
                if ($request["input_producto_array"]){
                    $jsonProducto = json_decode($request["input_producto_array"]);
                    if ($jsonProducto) {
                        foreach ($jsonProducto as $key => $item_producto) {
                            if ($item_producto->cantidad > 0 ) {
                                array_push($movimiento_detalle,[
                                    "producto_id"   => $item_producto->producto_id,
                                    "cantidad"      => $item_producto->cantidad,
                                    "tipo"          => 20,
                                ]);
                            }
                        }
                    }
                }
            }


            if ($sucursal_id) {
                if (!empty($movimiento_detalle)) {
                    $valid = Reparto::validateVentaRuta($movimiento_detalle,$sucursal_id);
                    if (empty($valid)) {
                        if (Reparto::saveOperacion($movimiento_detalle,$sucursal_id, $ruta->sucursal_id, $request["ruta_id"] )) {
                            Yii::$app->session->setFlash('success', "SE REALIZO LA OPERACION CORRECTAMENTE");
                            return $this->redirect(['view', "id" => $request["ruta_id"] ]);
                        }
                        Yii::$app->session->setFlash('danger', 'Ocurrio un error al realizar la operacion, intenta nuevamente .');
                        return $this->redirect(['view', "id" => $request["ruta_id"] ]);
                    }else{
                        $text = "";
                        foreach ($valid as $key => $error_message) {
                            if ($error_message["code"] == 10) {
                                $text = $text ."  <li>". $error_message["producto"] . " - stock actual: ". $error_message["inv"]["cantidad"] ."</li>";
                            }else{
                                $text = $text ."  <li>". $error_message["producto"] . " - stock actual: ". $error_message["message"] ."</li>";
                            }
                        }

                        Yii::$app->session->setFlash('danger', "No cuentas con el suficiente inventario, revisa el stock de los producto(s) : " . $text . "");
                        return $this->redirect(['view', "id" => $request["ruta_id"] ]);
                    }
                }else{
                    Yii::$app->session->setFlash('success', "NO SE DETECTO NINGUN CAMBIO A REALIZAR");
                    return $this->redirect(['view', "id" => $request["ruta_id"] ]);
                }

            }else{
                Yii::$app->session->setFlash('danger', 'El usuario no tiene asignada una sucursal, contacta al administrador.');
                return $this->redirect(['view', "id" => $request["ruta_id"] ]);
            }
        }

        Yii::$app->session->setFlash('danger', 'Ocurrio un error al realizar la operacion, intenta nuevamente .');
        return $this->redirect(['view', "id" => $request["ruta_id"] ]);
    }


    public function actionAddReparto()
    {

        $request = Yii::$app->request->get();

        $Reparto           = Reparto::findOne($request["id"]);
        $sucursal_id    = Yii::$app->user->identity->sucursal_id;
        $movimiento_detalle = [];


        $getVenta = Venta::findOne($request["venta_id"]);

        foreach ($getVenta->ventaDetalle as $key => $item_venta_detalle) {
            array_push($movimiento_detalle, [
                "producto_id"   => $item_venta_detalle->producto_id,
                "cantidad"      => $item_venta_detalle->cantidad,
                "venta_id"      => $item_venta_detalle->venta_id,
                "venta_detalle_id"  => $item_venta_detalle->id,
                "tipo"              => 10,
            ]);
        }

        if ($sucursal_id) {
            if (!empty($movimiento_detalle)) {
                $valid = Reparto::validateVentaRuta($movimiento_detalle,$sucursal_id);
                if (empty($valid)) {
                    if (Reparto::saveOperacionReApertura($movimiento_detalle,$sucursal_id, $Reparto->sucursal_id, $Reparto->id )) {
                        Yii::$app->session->setFlash('success', "SE REALIZO LA OPERACION CORRECTAMENTE");
                        return $this->redirect(['view', "id" => $Reparto->id ]);
                    }
                    Yii::$app->session->setFlash('danger', 'Ocurrio un error al realizar la operacion, intenta nuevamente .');
                    return $this->redirect(['view', "id" => $Reparto->id ]);
                }else{
                    $text = "";
                    foreach ($valid as $key => $error_message) {
                        if ($error_message["code"] == 10) {
                            $text = $text ."  <li>". $error_message["producto"] . " - stock actual: ". $error_message["inv"]["cantidad"] ."</li>";
                        }else{
                            $text = $text ."  <li>". $error_message["producto"] . " - stock actual: ". $error_message["message"] ."</li>";
                        }
                    }

                    Yii::$app->session->setFlash('danger', "No cuentas con el suficiente inventario, revisa el stock de los producto(s) : " . $text . "");
                    return $this->redirect(['view', "id" => $Reparto->id ]);
                }
            }else{
                Yii::$app->session->setFlash('success', "NO SE DETECTO NINGUN CAMBIO A REALIZAR");
                return $this->redirect(['view', "id" => $Reparto->id ]);
            }
        }else{
            Yii::$app->session->setFlash('danger', 'El usuario no tiene asignada una sucursal, contacta al administrador.');
            return $this->redirect(['view', "id" => $Reparto->id ]);
        }
    }

    public function actionGetProducto()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('producto_id')) {

                $sucursal = Sucursal::getMySucursal();
                $producto  = Producto::findOne(trim($request->get('producto_id')));
                return [
                    "code" => 202,
                    "producto" => [
                        "id" => $producto->id,
                        "nombre" => $producto->nombre,
                        "inventario_actual" =>  isset($sucursal->id) ?  InvProductoSucursal::getInventarioActual($sucursal->id,$producto->id) : null,
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

    public function actionGetMetodoPagoVenta()
    {
        $request = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($request["venta_id"]) && $request["venta_id"] ) {
            $VentaTokenPay    = VentaTokenPay::find()->andWhere([ "venta_id" => $request["venta_id"] ])->one();
            $response       = [];
            $responsePago   = [];
            if ($VentaTokenPay) {
                $ventaToken = VentaTokenPay::find()->andWhere([ "token_pay" => $VentaTokenPay->token_pay ])->all();
                foreach ($ventaToken as $key => $item_token) {
                    array_push($response,[
                        "id"            => $item_token->venta->id,
                        "folio"         => str_pad($item_token->venta->id,6,"0",STR_PAD_LEFT),
                        "total"         => $item_token->venta->total,
                        "sucursal"      => isset($item_token->venta->reparto->sucursal->nombre) ? $item_token->venta->reparto->sucursal->nombre : null,
                        "created_at"    => date("Y-m-d h:i:s",$item_token->created_at),
                        "empleado"      => $item_token->createdBy->nombreCompleto,
                    ]);
                }

                $cobroTpvVenta = CobroVenta::find()->andWhere([ "and",
                    [ "=", "trans_token_venta", $VentaTokenPay->token_pay ],
                    [ "=", "is_cancel", CobroVenta::IS_CANCEL_OFF ]
                ])->all();

                foreach ($cobroTpvVenta as $key => $item_cobro) {
                    array_push($responsePago,[
                        "id" => $item_cobro->id,
                        "metodo_pago"       => $item_cobro->metodo_pago,
                        "metodo_pago_text"  => CobroVenta::$servicioTpvList[$item_cobro->metodo_pago],
                        "cantidad"          => $item_cobro->cantidad,
                    ]);
                }
            }else{

                $Venta = Venta::findOne($request["venta_id"]);
                array_push($response,[
                    "id"            => $Venta->id,
                    "folio"         => str_pad($Venta->id,6,"0",STR_PAD_LEFT),
                    "total"         => $Venta->total,
                    "sucursal"      => isset($Venta->reparto->sucursal->nombre) ? $Venta->reparto->sucursal->nombre : null,
                    "created_at"    => date("Y-m-d h:i:s",$Venta->created_at),
                    "empleado"      => $Venta->createdBy->nombreCompleto,
                ]);

                $cobroTpvVenta = CobroVenta::find()->andWhere([ "venta_id" => $Venta->id ])->all();

                foreach ($cobroTpvVenta as $key => $item_cobro) {
                    array_push($responsePago,[
                        "id" => $item_cobro->id,
                        "metodo_pago"       => $item_cobro->metodo_pago,
                        "metodo_pago_text"  => CobroVenta::$servicioTpvList[$item_cobro->metodo_pago],
                        "cantidad"          => $item_cobro->cantidad,
                    ]);
                }

            }


            return [
                "code" => 202,
                "ventas" => $response,
                "cobro" => $responsePago,
            ];
        }

        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente.",
        ];

    }


    public function actionGetLiquidacionVenta()
    {
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($request->get('reparto_id')) {

                $venta_array = TempVentaRuta::getTempVenta($request->get('reparto_id'));

                return [
                    "code"          => 202,
                    "temp_venta"    => $venta_array,
                ];
            }
            return [
                "code" => 10,
                "message" => "Error al buscar la liquidacion, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');

    }

    public function actionGetLiquidacionCobro()
    {
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($request->get('reparto_id')) {
                $cobro_array = TempCobroRutaVenta::getTempCobro($request->get('reparto_id'));

                return [
                    "code"      => 202,
                    "temp_cobro"=> $cobro_array,
                ];
            }
            return [
                "code" => 10,
                "message" => "Error al buscar la liquidacion, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionPostLiquidacionCobro()
    {
        $request = Yii::$app->request;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($request->post('reparto_id')) {

                if (Reparto::setLiquidacion($request->post('reparto_id'))) {
                    return [
                        "code"      => 202,
                        "message"   => "SE GENERO CORRECTAMENTE LA OPERACION",
                    ];
                }

                return [
                    "code"      => 10,
                    "message"   => "Ocurrio un error, intenta nuevamente",
                ];
            }
            return [
                "code" => 10,
                "message" => "Error al buscar la liquidacion, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    //------------------------------------------------------------------------------------------------//
    //                          ACTIONS AJAX
    //------------------------------------------------------------------------------------------------//
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

    //------------------------------------------------------------------------------------------------//
	// BootstrapTable list
	//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionRutaJsonBtt(){
        return ViewReparto::getJsonBtt(Yii::$app->request->get());
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
                $model = Reparto::findOne($name);
                break;

            case 'view':
                $model = ViewReparto::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
