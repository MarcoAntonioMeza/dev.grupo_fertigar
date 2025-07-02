<?php
namespace app\modules\inventario\controllers;

use Yii;
use yii\helpers\Url;
use kartik\mpdf\Pdf;
use yii\web\Response;
use yii\web\Controller;
use app\models\producto\Producto;
use app\models\inventario\ViewPromedioProducto;

use app\models\venta\Venta;
use app\models\inv\Operacion;
use app\models\inv\ViewInventario;
use app\models\trans\TransProductoInventario;
use app\models\inv\InvProductoSucursal;
use app\models\tranformacion\TranformacionDevolucion;
use yii\web\NotFoundHttpException;

/**
 * Default controller for the `clientes` module
 */
class ArqueoInventarioController extends \app\controllers\AppController
{

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

     /**
     * Displays a single EsysDivisa model.
     * @param integer $name
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $query = ViewPromedioProducto::find()
        ->where(['producto_id' => $id])
        ->all();

        return $this->render('view', [
            'model' => $model,
            'promedio' => $query,
        ]);
    }

    public function actionArqueoInventarioProductos()
    {
        $model = new InvProductoSucursal();

        set_time_limit(-1);

        return $this->render('arqueo-inventario-producto', [
            'model' => $model,
        ]);
    }

    public function actionRedirectOperacionView($operacion_id)
    {
        $TransProductoInventario  = TransProductoInventario::findOne(trim($operacion_id));
        if (isset($TransProductoInventario->id)) {
                $UrlFolio = $folio = "";

                if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_VENTA) {
                    $folio      = isset($TransProductoInventario->ventaDetalle->venta_id) && $TransProductoInventario->ventaDetalle->venta_id ? $TransProductoInventario->ventaDetalle->venta_id : null;

                    if ($folio)
                        return $this->redirect(['/tpv/venta/view', 'id' => $folio ]);
                }

                if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_VENTA_RUTA) {
                    $folio      = isset($TransProductoInventario->tempVentaDetalle->tempVentaRuta->venta_id) && $TransProductoInventario->tempVentaDetalle->tempVentaRuta->venta_id ? $TransProductoInventario->tempVentaDetalle->tempVentaRuta->venta_id : null;

                    if ($folio)
                        return $this->redirect(['/tpv/venta/view', 'id' => $folio ]);
                }

                if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_OPERACION) {
                    $folio = isset($TransProductoInventario->operacionDetalle->operacion_id) && $TransProductoInventario->operacionDetalle->operacion_id ? $TransProductoInventario->operacionDetalle->operacion_id : null;

                    if ($folio)
                        return $this->redirect(['/inventario/entradas-salidas/view', 'id' => $folio ]);
                }

                if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_TRANSFORMACION) {
                    $folio = isset($TransProductoInventario->transformacionDetalle->tranformacion_devolucion_id) && $TransProductoInventario->transformacionDetalle->tranformacion_devolucion_id ? $TransProductoInventario->transformacionDetalle->tranformacion_devolucion_id : null;

                    $folio = $folio ? $folio : $TransProductoInventario->tranformacion_id;

                    if ($folio)
                        return $this->redirect(['/inventario/devolucion/tranformacion-view', 'id' => $folio ]);
                }

                if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_REPARTO) {
                    $folio = isset($TransProductoInventario->repartoDetalle->reparto_id) && $TransProductoInventario->repartoDetalle->reparto_id ? $TransProductoInventario->repartoDetalle->reparto_id : null;

                    if ($folio)
                        return $this->redirect(['/logistica/ruta/view', 'id' => $folio ]);
                }

                if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_AJUSTE) {
                    $folio = null;
                    $UrlFolio   = null;
                }

                if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_AJUSTE_PREVENTA) {
                    $folio = isset($TransProductoInventario->venta_id) && $TransProductoInventario->venta_id ? $TransProductoInventario->venta_id : null;
                    if ($folio)
                        return $this->redirect(['/tpv/venta/view', 'id' => $folio ]);
                }
        }

        throw new NotFoundHttpException('La página solicitada no existe.');
    }

    public function actionImprimirOperacion($operacion_id)
    {
        $TransProductoInventario  = TransProductoInventario::findOne(trim($operacion_id));
        if (isset($TransProductoInventario->id)) {
            $UrlFolio = $folio = "";

            if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_VENTA) {
                $folio      = isset($TransProductoInventario->ventaDetalle->venta_id) && $TransProductoInventario->ventaDetalle->venta_id ? $TransProductoInventario->ventaDetalle->venta_id : null;

                if ($folio){
                    $model = Venta::findOne($folio);
                    $lengh = 270;
                    $width = 80;
                    $count = 0;
                    $total_piezas = 0;
                    foreach ($model->ventaDetalle as $key => $item) {
                        $count = $count + 1;

                    }

                    $lengh = $lengh + ($count  * 40 );

                    //$width= $width + ($count  * 2 );

                    $content = $this->renderPartial('../../../tpv/views/venta/ticket', ["model" => $model]);

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
                            //'SetHeader'=>[ 'TICKET #' . $model->id],
                            //'SetFooter'=>['{PAGENO}'],
                        ]
                    ]);

                    $pdf->marginLeft = 1;
                    $pdf->marginRight = 1;

                    // return the pdf output as per the destination setting
                    return $pdf->render();

                }
            }

            if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_VENTA_RUTA) {
                $folio      = isset($TransProductoInventario->tempVentaDetalle->tempVentaRuta->venta_id) && $TransProductoInventario->tempVentaDetalle->tempVentaRuta->venta_id ? $TransProductoInventario->tempVentaDetalle->tempVentaRuta->venta_id : null;

                if ($folio){

                    $model = Venta::findOne($folio);
                    $lengh = 270;
                    $width = 80;
                    $count = 0;
                    $total_piezas = 0;
                    foreach ($model->ventaDetalle as $key => $item) {
                        $count = $count + 1;

                    }

                    $lengh = $lengh + ($count  * 40 );

                    //$width= $width + ($count  * 2 );

                    $content = $this->renderPartial('../../../tpv/views/venta/ticket', ["model" => $model]);

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
                            //'SetHeader'=>[ 'TICKET #' . $model->id],
                            //'SetFooter'=>['{PAGENO}'],
                        ]
                    ]);

                    $pdf->marginLeft = 1;
                    $pdf->marginRight = 1;

                    // return the pdf output as per the destination setting
                    return $pdf->render();
                }
            }

            if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_OPERACION) {
                $folio = isset($TransProductoInventario->operacionDetalle->operacion_id) && $TransProductoInventario->operacionDetalle->operacion_id ? $TransProductoInventario->operacionDetalle->operacion_id : null;

                if ($folio){

                    $model      = Operacion::findOne($folio);
                    $content    = $this->renderPartial('../entradas-salidas/acuse', ["model" => $model]);

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
                         // call mPDF methods on the fly
                    ]);



                    // return the pdf output as per the destination setting
                    return $pdf->render();
                }
            }

            if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_TRANSFORMACION) {
                $folio = isset($TransProductoInventario->transformacionDetalle->tranformacion_devolucion_id) && $TransProductoInventario->transformacionDetalle->tranformacion_devolucion_id ? $TransProductoInventario->transformacionDetalle->tranformacion_devolucion_id : null;

                $folio = $folio ? $folio : $TransProductoInventario->tranformacion_id;

                if ($folio){

                    $model      = TranformacionDevolucion::findOne($folio);
                    $content    = $this->renderPartial('acuse-transformacion', ["model" => $model]);

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
                         // call mPDF methods on the fly
                    ]);

                    return $pdf->render();
                }
            }

            if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_AJUSTE_PREVENTA) {
                $folio = isset($TransProductoInventario->venta_id) && $TransProductoInventario->venta_id ? $TransProductoInventario->venta_id : null;
                if ($folio){

                    $model = Venta::findOne($folio);
                    $lengh = 270;
                    $width = 80;
                    $count = 0;
                    $total_piezas = 0;
                    foreach ($model->ventaDetalle as $key => $item) {
                        $count = $count + 1;

                    }

                    $lengh = $lengh + ($count  * 40 );

                    //$width= $width + ($count  * 2 );

                    $content = $this->renderPartial('../../../tpv/views/venta/ticket', ["model" => $model]);

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
                            //'SetHeader'=>[ 'TICKET #' . $model->id],
                            //'SetFooter'=>['{PAGENO}'],
                        ]
                    ]);

                    $pdf->marginLeft = 1;
                    $pdf->marginRight = 1;

                    // return the pdf output as per the destination setting
                    return $pdf->render();
                }
            }
        }
    }

    public function actionSaveInventarioProducto()
    {
        if (Yii::$app->request->post()){

            $request = Yii::$app->request->post();

            $productos_array = isset($request['PRODUCTO']) && $request['PRODUCTO'] ? $request['PRODUCTO'] : null;
            $producto_id     = isset($request['producto_id']) && $request['producto_id'] ? $request['producto_id'] : null;

            if (count($productos_array) > 0 && $producto_id ) {


                foreach ($productos_array as $key => $producto_item) {
                    $Producto = Producto::findOne($producto_id);

                    $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $producto_item['sucursal_id'] ], [ "=", "producto_id", $producto_id ] ] )->one();

                    if (isset($InvProducto->id)) {

                        $cantidadProducto       = $InvProducto->cantidad;
                        $InvProducto->cantidad  = $producto_item['input_inv_cantidad'];

                        $InvProducto->save();

                        if ($cantidadProducto != $producto_item['input_inv_cantidad']){
                            if ($cantidadProducto > $producto_item['input_inv_cantidad'])
                                TransProductoInventario::saveTransAjuste($producto_item['sucursal_id'],null,$producto_id, ( $cantidadProducto  - $producto_item['input_inv_cantidad'] ),TransProductoInventario::TIPO_SALIDA);
                            else
                                TransProductoInventario::saveTransAjuste($producto_item['sucursal_id'],null,$producto_id, ($producto_item['input_inv_cantidad'] - $cantidadProducto ),TransProductoInventario::TIPO_ENTRADA);
                        }

                    }else{
                        $InvProductoSucursal  =  new InvProductoSucursal();
                        $InvProductoSucursal->sucursal_id   = $producto_item['sucursal_id'];
                        $InvProductoSucursal->producto_id   = $producto_id;
                        $InvProductoSucursal->cantidad      = $producto_item['input_inv_cantidad'];
                        $InvProductoSucursal->save();

                        if (intval($producto_item['input_inv_cantidad']) > 0 )
                            TransProductoInventario::saveTransAjuste($producto_item['sucursal_id'],null,$producto_id, $producto_item['input_inv_cantidad'],TransProductoInventario::TIPO_ENTRADA);


                    }

                }

                Yii::$app->session->setFlash('success', 'Se realizarón los ajustes en el inventario.');

            }else{
                Yii::$app->session->setFlash('warning', 'No se realizarón cambios, verifica tu información.');
            }

            return $this->redirect(['view', 'id' => $producto_id ]);
        }
    }

    public function actionSaveInventarioAll()
    {
         if (Yii::$app->request->post()){

            $request = Yii::$app->request->post();
            if (isset($request['InvProductoSucursal']) && $request['InvProductoSucursal']) {
                if (isset($request['InvProductoSucursal']['producto_array_update'])) {

                    $producto_list = json_decode($request['InvProductoSucursal']['producto_array_update']);

                    if (count($producto_list) > 0) {

                        foreach ($producto_list as $key => $producto_item) {

                            $Producto = Producto::findOne($producto_item->producto_id);

                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $producto_item->sucursal_id ], [ "=", "producto_id", $producto_item->producto_id ] ] )->one();

                            if (isset($InvProducto->id)) {
                                $InvProducto->cantidad = $producto_item->cantidad;
                                $InvProducto->save();
                            }else{
                                $InvProductoSucursal  =  new InvProductoSucursal();
                                $InvProductoSucursal->sucursal_id   = $producto_item->sucursal_id;
                                $InvProductoSucursal->producto_id   = $producto_item->producto_id;
                                $InvProductoSucursal->cantidad      = $producto_item->cantidad;
                                $InvProductoSucursal->save();
                            }
                        }

                        Yii::$app->session->setFlash('success', 'Se realizarón: '. count($producto_list) .' ajustes en el inventario.');
                        return $this->redirect(['index' ]);


                    }else{
                        Yii::$app->session->setFlash('warning', 'No se detecto ningun cambio en el inventario.');
                        return $this->redirect(['index' ]);
                    }

                }
            }
            Yii::$app->session->setFlash('warning', 'No se realizarón cambios, verifica tu información.');
            return $this->redirect(['index' ]);
        }
    }

    public function actionGetOperacionDetail()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('operacion_id')) {

                $TransProductoInventario  = TransProductoInventario::findOne(trim($request->get('operacion_id')));


                if (isset($TransProductoInventario->id)) {


                        $UrlFolio = $folio = "";

                        if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_VENTA) {
                            $folio      = isset($TransProductoInventario->ventaDetalle->venta_id) && $TransProductoInventario->ventaDetalle->venta_id ? $TransProductoInventario->ventaDetalle->venta_id : null;
                            $UrlFolio   = $folio ?  Url::to(['/tpv/venta/view', 'id' => $folio]) : null;
                        }

                        if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_VENTA_RUTA) {
                            $folio      = isset($TransProductoInventario->tempVentaDetalle->tempVentaRuta->venta_id) && $TransProductoInventario->tempVentaDetalle->tempVentaRuta->venta_id ? $TransProductoInventario->tempVentaDetalle->tempVentaRuta->venta_id : null;
                            $UrlFolio   = $folio ?  Url::to(['/tpv/venta/view', 'id' => $folio]) : null;
                        }

                        if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_OPERACION) {
                            $folio = isset($TransProductoInventario->operacionDetalle->operacion_id) && $TransProductoInventario->operacionDetalle->operacion_id ? $TransProductoInventario->operacionDetalle->operacion_id : null;
                            $UrlFolio   = $folio ?  Url::to(['/inventario/entradas-salidas/view', 'id' => $folio]) : null;
                        }

                        if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_TRANSFORMACION) {
                            $folio = isset($TransProductoInventario->transformacionDetalle->tranformacion_devolucion_id) && $TransProductoInventario->transformacionDetalle->tranformacion_devolucion_id ? $TransProductoInventario->transformacionDetalle->tranformacion_devolucion_id : null;

                            $folio = $folio ? $folio : $TransProductoInventario->tranformacion_id;
                            $UrlFolio   = $folio ? Url::to(['/inventario/devolucion/tranformacion-view', 'id' => $folio]) : null;

                        }

                        if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_REPARTO) {
                            $folio = isset($TransProductoInventario->repartoDetalle->reparto_id) && $TransProductoInventario->repartoDetalle->reparto_id ? $TransProductoInventario->repartoDetalle->reparto_id : null;
                            $UrlFolio   = $folio ? Url::to(['/logistica/ruta/view', 'id' => $folio]) : null;
                        }

                        if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_AJUSTE) {
                            $folio = null;
                            $UrlFolio   = null;
                        }

                         if ($TransProductoInventario->tipo == TransProductoInventario::TIPO_AJUSTE_PREVENTA) {
                            $folio = isset($TransProductoInventario->venta_id) && $TransProductoInventario->venta_id ? $TransProductoInventario->venta_id : null;
                            $UrlFolio   = $folio ?  Url::to(['/tpv/venta/view', 'id' => $folio]) : null;
                        }


                        $responseArray = [
                            "id"                => $TransProductoInventario->id,
                            "folio"             =>  "#".$folio,
                            "url_folio"         => $UrlFolio,
                            "tipo"              => $TransProductoInventario->tipo,
                            "tipo_text"         => TransProductoInventario::$motivoList[$TransProductoInventario->tipo],
                            "motivo"            => $TransProductoInventario->motivo,
                            "motivo_text"       => TransProductoInventario::$tipoList[$TransProductoInventario->motivo],
                            "origen"            => $TransProductoInventario->sucursal->nombre,
                            ///"destino"           => $destino,
                            "cantidad"          => $TransProductoInventario->cantidad,
                            "responsable"       => $TransProductoInventario->createdBy->nombreCompleto,
                            "created_at"       => date("Y-m-d h:i a", $TransProductoInventario->created_at),
                            //"producto_detalle"  => [],
                        ];

                        /*foreach ($Operacion->operacionDetalles as $key => $v_detalle) {
                            array_push($responseArray["producto_detalle"], [
                                "id"            => $v_detalle->id,
                                "producto_id"   => $v_detalle->producto_id,
                                "producto"      => isset($v_detalle->producto->id) ? $v_detalle->producto->nombre : null,
                                "clave"         => $v_detalle->producto->clave,
                                "cantidad"      => $v_detalle->cantidad,
                                "producto_unidad"  => Producto::$medidaList[$v_detalle->producto->tipo_medida],
                            ]);
                        }*/

                        return [
                            "code" => 202,
                            "operacion" => $responseArray,
                        ];

                }
            }

            return [
                "code" => 10,
                "message" => "Error al buscar la venta, intenta nuevamente",
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
    public function actionArqueoInventarioJsonBtt(){
        return ViewInventario::getJsonBtt(Yii::$app->request->get());
    }

    public function actionHistorialMovimientosJsonBtt(){
        return ViewInventario::getHistoryMovJsonBtt(Yii::$app->request->get());
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
                $model = Producto::findOne($name);
                break;

            case 'view':
                $model = ViewInventario::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
