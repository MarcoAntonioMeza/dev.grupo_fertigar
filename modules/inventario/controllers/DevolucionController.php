<?php
namespace app\modules\inventario\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\web\Response;
use yii\web\Controller;
use yii\web\BadRequestHttpException;
use app\models\inv\Operacion;
use app\models\inv\ViewEntradaSalida;
use app\models\inv\OperacionDetalle;
use app\models\venta\Venta;
use app\models\producto\Producto;
use app\models\tranformacion\TranformacionDevolucion;
use app\models\tranformacion\TranformacionDevolucionDetalle;
use app\models\inv\InvProductoSucursal;
use app\models\tranformacion\ViewTranformacion;
use app\models\tranformacion\ViewTranformacionMerma;
use app\models\trans\TransProductoInventario;

/**
 * Default controller for the `clientes` module
 */
class DevolucionController extends \app\controllers\AppController
{

	private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create'    => Yii::$app->user->can('devolucionCreate'),
            //'cancel'  => Yii::$app->user->can('entradasalidaCancel'),
            'cancel'    => false,
            'tranformacion' => Yii::$app->user->can('tranformacion'),
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


    public function actionTranformacionView($id)
    {
        $model = TranformacionDevolucion::findOne($id);

        return $this->render('tranformacion-view', [
            'model' => $model,
        ]);
    }



   /**
     * Creates a new Sucursal model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Operacion();
        $model->operacion_detalle   = new OperacionDetalle();


        if ($model->load(Yii::$app->request->post()) && $model->operacion_detalle->load(Yii::$app->request->post())) {
            $model->tipo    =  Operacion::TIPO_DEVOLUCION;
            $model->motivo  =  Operacion::ENTRADA_DEVOLUCION;
            $model->status  =  Operacion::STATUS_ACTIVE;

            $model->venta_reembolso_cantidad    =  isset(Yii::$app->request->post()["total_pago_reembolso"]) && Yii::$app->request->post()["total_pago_reembolso"] ? Yii::$app->request->post()["total_pago_reembolso"] : null;

            $metodoPagoArray = isset(Yii::$app->request->post()["metodo_pago_array"]) && Yii::$app->request->post()["metodo_pago_array"] ? Yii::$app->request->post()["metodo_pago_array"] : [];

            if ($model->save()) {
                Venta::registerOperacionDevolucion($model->id, $model->venta_id);
                if($model->operacion_detalle->saveDevolucionDetalle($model->id,$model->almacen_sucursal_id, $model->motivo)){

                    Operacion::getReembolsoVenta($model->id,$metodoPagoArray);

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

    public function actionTranformacion()
    {
        return $this->render('tranformacion');
    }

    public function actionImprimirEtiqueta($id)
    {
        $model = $this->findModel($id);
        $content = $this->renderPartial('etiqueta', ["model" => $model]);

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => array(72,270),//Pdf::FORMAT_LETTER,
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

        $pdf->marginLeft = 1;
        $pdf->marginRight = 1;

        // return the pdf output as per the destination setting
        return $pdf->render();
    }

    public function actionCreateTransformacion()
    {
        $request = Yii::$app->request->post();
        $TransArray = json_decode($request["transformacion_array"]);
        $TransProductoArray = json_decode($request["transformacion_producto_array"]);

        if (isset($request["tipo_id"]) && $request["tipo_id"] && $TransArray) {

            switch ($request["tipo_id"]) {

                case 20:

                    if ($TransProductoArray) {
                        $token_pay = bin2hex(random_bytes(16));
                        $count_apply = true;
                        foreach ($TransProductoArray as $key => $item_producto) {

                            /* TRANSFORMACION A MERMA SI ES QUE EXISTE CANTIDAD PARA MERMA */
                            if ($item_producto->tipo == 20 && intval($item_producto->cantidad_real) > 0 ) {

                                $TranformacionDevolucion = new TranformacionDevolucion();
                                //$TranformacionDevolucion->sucursal_id       =  null;
                                $TranformacionDevolucion->sucursal_id       = $request["sucursal_invetario"];
                                $TranformacionDevolucion->motivo_id         = TranformacionDevolucion::TRANS_MERMA;
                                $TranformacionDevolucion->token             = $token_pay;
                                $TranformacionDevolucion->nota              = $request["nota"];
                                $TranformacionDevolucion->producto_cantidad = $item_producto->cantidad_real;
                                $TranformacionDevolucion->producto_new      = null;

                                $TranformacionDevolucion->save();
                            }

                            /* TRANSFORMACION A PRODUCTO NUEVO  */
                            if ($item_producto->tipo == 10 ) {

                                $TranformacionDevolucion = new TranformacionDevolucion();
                                $TranformacionDevolucion->sucursal_id       = $request["sucursal_invetario"];
                                $TranformacionDevolucion->motivo_id         = $request["tipo_id"];
                                $TranformacionDevolucion->token             = $token_pay;
                                $TranformacionDevolucion->nota              = $request["nota"];
                                $TranformacionDevolucion->producto_cantidad = $item_producto->cantidad;
                                $TranformacionDevolucion->producto_new      = $item_producto->producto_id;


                                if ($TranformacionDevolucion->save()) {
                                    if ($count_apply) {
                                        foreach ($TransArray as $key => $transItem) {

                                            $TranDetalle = new TranformacionDevolucionDetalle();
                                            $TranDetalle->tranformacion_devolucion_id = $TranformacionDevolucion->id;
                                            $TranDetalle->producto_id       = $transItem->producto_id;
                                            $TranDetalle->cantidad          = $transItem->cantidad_transformar;
                                            $TranDetalle->save();

                                            /*********************************************************************/
                                            //                  QUITAMOS PRODUCTO DE INVENTARIO
                                            /*********************************************************************/
                                            $Producto = Producto::findOne($transItem->producto_id);

                                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $transItem->sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();

                                            else
                                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $transItem->sucursal_id ], [ "=", "producto_id", $transItem->producto_id ] ] )->one();


                                            if ($TranformacionDevolucion->motivo_id !=  TranformacionDevolucion::TRANS_MERMA )
                                                    TransProductoInventario::saveTransTransformacion($transItem->sucursal_id,$TranDetalle->id,$TranDetalle->producto_id,$TranDetalle->cantidad,TransProductoInventario::TIPO_SALIDA);

                                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {


                                                $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $transItem->sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                                                if (isset($InvProducto2->id)) {
                                                    // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                                    $InvProducto2->cantidad = floatval($InvProducto2->cantidad) - ( floatval($TranDetalle->cantidad) * $Producto->sub_cantidad_equivalente) ;
                                                    $InvProducto2->save();

                                                }else{
                                                    // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO

                                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                                    $InvProductoSucursal->sucursal_id   = $transItem->sucursal_id;
                                                    $InvProductoSucursal->producto_id   = $transItem->producto_id;
                                                    $InvProductoSucursal->cantidad      = floatval($TranDetalle->cantidad) * -1;
                                                    $InvProductoSucursal->save();
                                                }

                                            }else{
                                                $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($TranDetalle->cantidad);
                                                $InvProducto->save();
                                            }
                                        }
                                    }

                                    $count_apply = false;

                                    /********************************************************************/
                                    //                  AGREAMOS PRODUCTO DE INVENTARIO
                                    /*********************************************************************/
                                    if ($item_producto->tipo == 10) {
                                        $Producto = Producto::findOne($TranformacionDevolucion->producto_new);

                                        TransProductoInventario::saveTransTransformacionEntrada($TranformacionDevolucion->sucursal_id,$TranformacionDevolucion->id,$TranformacionDevolucion->producto_new,$TranformacionDevolucion->producto_cantidad,TransProductoInventario::TIPO_ENTRADA);

                                        if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $TranformacionDevolucion->sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();

                                        else
                                            $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $TranformacionDevolucion->sucursal_id ], [ "=", "producto_id", $TranformacionDevolucion->producto_new ] ] )->one();

                                        if (isset($InvProducto->id)) {
                                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {


                                                $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $TranformacionDevolucion->sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                                                if (isset($InvProducto2->id)) {
                                                    // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                                    $InvProducto2->cantidad = floatval($InvProducto2->cantidad) + ( floatval($TranformacionDevolucion->producto_cantidad) * $Producto->sub_cantidad_equivalente) ;
                                                    $InvProducto2->save();

                                                }else{
                                                    // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO

                                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                                    $InvProductoSucursal->sucursal_id   = $TranformacionDevolucion->sucursal_id;
                                                    $InvProductoSucursal->producto_id   = $TranformacionDevolucion->producto_new;
                                                    $InvProductoSucursal->cantidad      = $TranformacionDevolucion->producto_cantidad;
                                                    $InvProductoSucursal->save();
                                                }

                                            }else{
                                                $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval($TranformacionDevolucion->producto_cantidad);
                                                $InvProducto->save();
                                            }
                                        }else{
                                            // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                                            $InvProductoSucursal  =  new InvProductoSucursal();
                                            $InvProductoSucursal->sucursal_id   = $TranformacionDevolucion->sucursal_id;
                                            $InvProductoSucursal->producto_id   = $TranformacionDevolucion->producto_new;
                                            $InvProductoSucursal->cantidad      = $TranformacionDevolucion->producto_cantidad;
                                            $InvProductoSucursal->save();
                                        }
                                    }
                                }
                            }
                        }
                        Yii::$app->session->setFlash('success', 'SE REALIZO CORRECTAMENTE');
                        return $this->redirect('tranformacion');
                    }
                    Yii::$app->session->setFlash('danger', 'Ocurrio un error al realizar la tranformación, intenta nuevamente');
                    return $this->redirect('tranformacion');
                break;

                case 30:

                    $TranformacionDevolucion = new TranformacionDevolucion();
                    $TranformacionDevolucion->sucursal_id   = $request["sucursal_invetario"];
                    $TranformacionDevolucion->motivo_id     = $request["tipo_id"];
                    $TranformacionDevolucion->nota          = $request["nota"];
                    if ($TranformacionDevolucion->save()) {
                        foreach ($TransArray as $key => $transItem) {
                            $TranDetalle = new TranformacionDevolucionDetalle();
                            $TranDetalle->tranformacion_devolucion_id = $TranformacionDevolucion->id;
                            $TranDetalle->producto_id       = $transItem->producto_id;
                            $TranDetalle->cantidad          = $transItem->cantidad_transformar;
                            $TranDetalle->save();

                            $Producto = Producto::findOne($transItem->producto_id);

                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $transItem->sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();

                            else
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $transItem->sucursal_id ], [ "=", "producto_id", $transItem->producto_id ] ] )->one();

                            TransProductoInventario::saveTransTransformacion($transItem->sucursal_id,$TranDetalle->id,$TranDetalle->producto_id,$TranDetalle->cantidad,TransProductoInventario::TIPO_SALIDA);

                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {


                                $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $transItem->sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                                if (isset($InvProducto2->id)) {
                                    // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                    $InvProducto2->cantidad = floatval($InvProducto2->cantidad) - ( floatval($TranDetalle->cantidad) * $Producto->sub_cantidad_equivalente) ;
                                    $InvProducto2->save();

                                }else{
                                    // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO

                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $transItem->sucursal_id;
                                    $InvProductoSucursal->producto_id   = $transItem->producto_id;
                                    $InvProductoSucursal->cantidad      = $transItem->cantidad * -1;
                                    $InvProductoSucursal->save();
                                }

                            }else{
                                $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($TranDetalle->cantidad);
                                $InvProducto->save();
                            }


                        }

                        Yii::$app->session->setFlash('success', 'SE REALIZO CORRECTAMENTE');
                        return $this->redirect('tranformacion');
                    }

                break;

                case 40:
                    $TranformacionDevolucion = new TranformacionDevolucion();
                    $TranformacionDevolucion->sucursal_id   = $request["sucursal_invetario"];
                    $TranformacionDevolucion->motivo_id = $request["tipo_id"];
                    $TranformacionDevolucion->nota          = $request["nota"];
                    if ($TranformacionDevolucion->save()) {

                        foreach ($TransArray as $key => $transItem) {
                            $TranDetalle = new TranformacionDevolucionDetalle();
                            $TranDetalle->tranformacion_devolucion_id = $TranformacionDevolucion->id;
                            $TranDetalle->producto_id       = $transItem->producto_id;
                            $TranDetalle->cantidad          = $transItem->cantidad_transformar;
                            $TranDetalle->save();

                             $Producto = Producto::findOne($transItem->producto_id);


                             TransProductoInventario::saveTransTransformacion($transItem->sucursal_id,$TranDetalle->id,$TranDetalle->producto_id,$TranDetalle->cantidad,TransProductoInventario::TIPO_SALIDA);

                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO )
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $transItem->sucursal_id ], [ "=", "producto_id",$Producto->sub_producto_id ] ] )->one();

                            else
                                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $transItem->sucursal_id ], [ "=", "producto_id", $transItem->producto_id ] ] )->one();


                            if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO ) {


                                $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $transItem->sucursal_id ], [ "=", "producto_id", $Producto->sub_producto_id ] ] )->one();

                                if (isset($InvProducto2->id)) {
                                    // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                    $InvProducto2->cantidad = floatval($InvProducto2->cantidad) - ( floatval($TranDetalle->cantidad) * $Producto->sub_cantidad_equivalente) ;
                                    $InvProducto2->save();

                                }else{
                                    // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO

                                    $InvProductoSucursal  =  new InvProductoSucursal();
                                    $InvProductoSucursal->sucursal_id   = $transItem->sucursal_id;
                                    $InvProductoSucursal->producto_id   = $transItem->producto_id;
                                    $InvProductoSucursal->cantidad      = $transItem->cantidad * -1;
                                    $InvProductoSucursal->save();
                                }

                            }else{
                                $InvProducto->cantidad = floatval($InvProducto->cantidad) -  floatval($TranDetalle->cantidad);
                                $InvProducto->save();
                            }


                        }

                        Yii::$app->session->setFlash('success', 'SE REALIZO CORRECTAMENTE');
                        return $this->redirect('tranformacion');
                    }
                break;
            }
        }

        Yii::$app->session->setFlash('danger', 'Ocurrio un error al realizar la tranformación, intenta nuevamente');

        return $this->redirect('tranformacion');
    }

    //------------------------------------------------------------------------------------------------//
    // FUNCION AJAX
    //------------------------------------------------------------------------------------------------//

    public function actionGetVenta()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('venta_id')) {

                $venta  = Venta::findOne(trim($request->get('venta_id')));


                if (isset($venta->id)) {

                    $is_devolucion = Operacion::findOne(["venta_id" => $venta->id]);
                    if (!isset($is_devolucion->id)) {

                        $responseArray = [
                            "id"                => $venta->id,
                            "cliente_id"        => $venta->cliente_id,
                            "cliente"           => isset($venta->cliente->id) ? $venta->cliente->nombreCompleto : null,
                            "ruta_sucursal_id"  => $venta->ruta_sucursal_id,
                            "sucursal"          => isset($venta->sucursal->id) ? $venta->sucursal->nombre : null,
                            "tipo"              => $venta->tipo,
                            "status"            => $venta->status,
                            "total"             => $venta->total,
                            "venta_detalle"     => [],
                        ];

                        foreach ($venta->ventaDetalle as $key => $v_detalle) {
                            array_push($responseArray["venta_detalle"], [
                                "producto_id"   => $v_detalle->producto_id,
                                "producto"      => isset($v_detalle->producto->id) ? $v_detalle->producto->nombre : null,
                                "clave"         => $v_detalle->producto->clave,
                                "cantidad"      => $v_detalle->cantidad,
                                "precio_venta"  => $v_detalle->precio_venta,
                                "producto_unidad"  => Producto::$medidaList[$v_detalle->producto->tipo_medida],
                            ]);
                        }
                        return [
                            "code" => 202,
                            "venta" => $responseArray,
                        ];
                    }else{
                        return [
                            "code"      => 10,
                            "message"   => "Ya se genero una devolución de esta venta, contacta al administración",
                        ];
                    }
                }
            }

            return [
                "code" => 10,
                "message" => "Error al buscar la venta, intenta nuevamente",
            ];
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
                    "producto" => $producto,
                ];
            }

            return [
                "code" => 10,
                "message" => "Error al buscar el producto, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionGetInventarioSucursal()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('sucursal_id')) {
                /********************************************************************/
                /** TRABAJAR EN LAS TRANFORMACIONES YA SE CREARON MODELOS
                /********************************************************************/

                //$ViewEntradaSalida = ViewEntradaSalida::getDevolucionDetalle($request->get('sucursal_id'));
                $InvProductoSucursalArray = [];

                $InvProductoSucursal  = InvProductoSucursal::getStockTranformacion($request->get('sucursal_id'), $request->get('producto'));

                foreach ($InvProductoSucursal as $key => $item_inventario) {
                    array_push($InvProductoSucursalArray,[
                        "id"                    => $item_inventario->id,
                        "sucursal"              => $item_inventario->sucursal->nombre,
                        "sucursal_id"           => $item_inventario->sucursal_id,
                        "producto_id"           => $item_inventario->producto_id,
                        "producto_nombre"       => $item_inventario->producto->nombre,
                        "producto_clave"        => $item_inventario->producto->clave,
                        "cantidad"              => $item_inventario->cantidad,
                        "costo"                 => $item_inventario->producto->costo,
                    ]);
                }

                return [
                    "code" => 202,
                    "devoluciones" => $InvProductoSucursalArray,
                ];
            }
            return [
                "code" => 10,
                "message" => "Error al buscar las devoluciones, intenta nuevamente",
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
    public function actionDevolucionesJsonBtt(){
        return ViewEntradaSalida::getJsonBtt(Yii::$app->request->get());
    }

    public function actionTranformacionJsonBtt(){
        return ViewTranformacion::getJsonBtt(Yii::$app->request->get());
    }
    public function actionTranformacionMermaJsonBtt(){
        return ViewTranformacionMerma::getJsonBtt(Yii::$app->request->get());
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
                $model = Operacion::findOne($name);
                break;

            case 'view':
                $model = ViewSucursal::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
