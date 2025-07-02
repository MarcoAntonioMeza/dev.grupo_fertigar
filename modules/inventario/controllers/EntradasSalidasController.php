<?php
namespace app\modules\inventario\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\inv\Operacion;
use app\models\venta\TransVenta;
use app\models\producto\Producto;
use app\models\inv\OperacionDetalle;
use app\models\inv\ViewEntradaSalida;
use app\models\compra\Compra;
use app\models\inv\InvProductoSucursal;

/**
 * Default controller for the `clientes` module
 */
class EntradasSalidasController extends \app\controllers\AppController
{

	private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('entradasalidaCreate'),
            'cancel' => Yii::$app->user->can('entradasalidaCancel'),
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
        $model = new Operacion();
        $model->operacion_detalle   = new OperacionDetalle();

        if ($model->load(Yii::$app->request->post()) && $model->operacion_detalle->load(Yii::$app->request->post())) {

            $model->status = $model->tipo == Operacion::TIPO_SALIDA && $model->motivo == Operacion::SALIDA_TRASPASO ? Operacion::STATUS_PROCESO : Operacion::STATUS_ACTIVE;


        	if ($model->save()) {
                if($model->operacion_detalle->saveOperacionDetalle($model->id,$model->almacen_sucursal_id,$model->tipo, $model->compra_id,$model->motivo)){

                    if ($model->tipo == Operacion::TIPO_ENTRADA && $model->motivo == Operacion::ENTRADA_TRASPASO ) {
                        if ($model->operacion_child_id){
                            $operacionChild = Operacion::findOne($model->operacion_child_id);
                            $operacionChild->status = Operacion::STATUS_ACTIVE;
                            $operacionChild->update();
                        }
                    }

                    if ($model->tipo == Operacion::TIPO_ENTRADA && $model->motivo == Operacion::ENTRADA_MERCANCIA_NUEVA) {
                        if ($model->compra_id) {
                            $compra = Compra::findOne($model->compra_id);
                            $compra->status = Compra::STATUS_TERMINADA;
                            if ($compra->update())
                                Compra::cierreCompra($compra->id);
                                //Compra::calPromedioProducto($compra->id);
                        }
                    }

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



    public function actionSearchProducto(){

        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            $sucursal_id = $request->get('sucursal_id');

            if ($request->get('id')) {
                $producto  = Producto::findOne(["id" => trim($request->get('id')) ]);
                if (isset($producto->id)) {

                    $existencia = 0;
                    $sub_existencia = 0;

                    if ($sucursal_id) {

                        $InvProductoSucursal = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , $sucursal_id ],[ "=", "producto_id" , $producto->id ] ])->one();

                        if (isset($InvProductoSucursal->id))
                          $existencia = $InvProductoSucursal->cantidad;

                        if ($producto->is_subproducto == Producto::TIPO_SUBPRODUCTO) {
                            $SubInvProductoSucursal = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , $sucursal_id  ],[ "=", "producto_id" , $producto->is_subproducto ] ])->one();

                            if (isset($SubInvProductoSucursal->id))
                              $sub_existencia = $SubInvProductoSucursal->cantidad;
                        }
                    }




                    return [
                        "code" => 202,
                        "producto" => [
                            "id"        => $producto->id,
                            "clave"    => $producto->clave,
                            "nombre"    => $producto->nombre,
                            "costo"     => $producto->costo,
                            "precio_publico"    => $producto->precio_publico,
                            "proveedor"         => isset($producto->proveedor->nombre) ? $producto->proveedor->nombre : 'N/A',
                            "tipo_medida"       => $producto->tipo_medida,
                            "tipo_medida_text"  =>Producto::$medidaList[$producto->tipo_medida],
                            "existencia"        => $existencia,
                            "sub_existencia"    => $sub_existencia,
                        ],
                    ];
                }
            }
            return [
                "code" => 10,
                "message" => "Error al buscar el producto, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionImprimirEtiqueta($id)
    {
        $model = $this->findModel($id);
        $content = $this->renderPartial('etiqueta', ["model" => $model]);

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => array(120,110),//Pdf::FORMAT_LETTER,
            // portrait orientation
            'orientation' => Pdf::ORIENT_LANDSCAPE,
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

    public function actionImprimirReporte($id)
    {
        $model = $this->findModel($id);
        $content = $this->renderPartial('acuse', ["model" => $model]);

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

    public function actionGetCompra()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('compra_id')) {
                $Compra  = Compra::findOne($request->get('compra_id'));
                if (isset($Compra->id)) {
                    $response = [];
                    foreach ($Compra->compraDetalles as $key => $c_detalle) {
                        array_push($response,[
                            "compra_id"             => $Compra->id,
                            "producto_id"           => $c_detalle->producto->id,
                            "producto_nombre"       => $c_detalle->producto->nombre,
                            "producto_clave"        => $c_detalle->producto->clave,
                            "costo"                 => $c_detalle->costo,
                            "producto_proveedor"    => $Compra->proveedor->nombre,
                            "producto_unidad"       => $c_detalle->producto->tipo_medida,
                            "producto_unidad_text"  => Producto::$medidaList[$c_detalle->producto->tipo_medida],
                            "cantidad"              => $c_detalle->cantidad,
                        ]);
                    }
                    return [
                        "code" => 202,
                        "compra" => $response,
                    ];
                }
            }
            return [
                "code" => 10,
                "message" => "Error al buscar compra, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionGetAbastecimientoDisponible(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('sucursal_id')) {
                $Operacion  = Operacion::find()->andWhere([ "and",
                    [ "=", "status", Operacion::STATUS_PROCESO ],
                    [ "=", "tipo", Operacion::TIPO_SALIDA ],
                    [ "=", "motivo", Operacion::SALIDA_TRASPASO ],
                    [ "=", "sucursal_recibe_id", $request->get('sucursal_id') ],

                ])->all();


                $response = [];
                foreach ($Operacion as $key => $operacionItem) {
                    array_push($response,[
                        "operacion_id"      => $operacionItem->id,
                        "operacion_folio"      => str_pad($operacionItem->id,6,"0",STR_PAD_LEFT),
                        "sucursal_nombre"   => $operacionItem->almacenSucursal->nombre,
                        "fecha"             => date("Y-m-d",$operacionItem->created_at),
                    ]);
                }
                return [
                    "code" => 202,
                    "solicitud" => $response,
                ];

            }
            return [
                "code" => 10,
                "message" => "Error al buscar compra, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionGetAbastecimiento(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('operacion_id')) {
                $Operacion  = Operacion::findOne($request->get('operacion_id'));
                if (isset($Operacion->id)) {
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
                        ]);
                    }
                    return [
                        "code" => 202,
                        "compra" => $response,
                    ];
                }
            }
            return [
                "code" => 10,
                "message" => "Error al buscar operacion, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionCancel($id)
    {
        $model = $this->findModel($id);

        if ($model->tipo == Operacion::TIPO_SALIDA && $model->motivo == Operacion::SALIDA_TRASPASO ) {

            if ($model->status  == Operacion::STATUS_PROCESO) {
                if (OperacionDetalle::cancelOperacion($model->id)) {
                    $model->status = Operacion::STATUS_CANCEL;
                    if ($model->update()) {

                        Yii::$app->session->setFlash('success', 'La operacion se cancelo correctamente');
                        return $this->redirect(['view',
                            'id' => $model->id
                        ]);
                    }
                }
            }else{
                Yii::$app->session->setFlash('danger', 'LA OPERACION NO SE PUEDE CANCELAR YA FUE INGRESADA A SU DESTINO - GENERAR UN NUEVO TRASPASO');

            }
        }else
            Yii::$app->session->setFlash('danger', 'La operación no se puede CANCELAR, no cumple con los requirimientos');

        return $this->redirect(['view',
            'id' => $model->id
        ]);

    }

     public function actionGetOperacionDetail()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('operacion_id')) {

                $Operacion  = Operacion::findOne(trim($request->get('operacion_id')));


                if (isset($Operacion->id)) {

                        $origen = $destino = "";

                        if ($Operacion->tipo == Operacion::TIPO_ENTRADA) {
                            if ($Operacion->motivo == Operacion::ENTRADA_MERCANCIA_NUEVA) {
                                $origen = "COMPRA - FOLIO #". $Operacion->compra_id;
                                $destino =  $Operacion->almacenSucursal->nombre;
                            }

                            if ($Operacion->motivo == Operacion::ENTRADA_TRASPASO_UNIDAD) {
                                $origen     = $Operacion->almacenSucursal->nombre;
                                $destino    = "RECOLECCION [REPARTO] - FOLIO #". $Operacion->reparto_id;
                            }

                            if ($Operacion->motivo == Operacion::ENTRADA_TRASPASO_RECOLECCION) {
                                $origen = "RECOLECCION [REPARTO] - FOLIO #". $Operacion->reparto_id;
                                $destino =  $Operacion->almacenSucursal->nombre;
                            }

                            if ($Operacion->motivo == Operacion::ENTRADA_TRASPASO) {
                                $origen  = $Operacion->operacionChild->almacenSucursal->nombre;
                                $destino = $Operacion->almacenSucursal->nombre;
                            }

                            if ($Operacion->motivo == Operacion::ENTRADA_DEVOLUCION) {
                                $origen = "DEVOLUCION - FOLIO #" . $Operacion->venta_id;
                                $destino =  $Operacion->almacenSucursal->nombre;
                            }

                            if ($Operacion->motivo == Operacion::ENTRADA_RUTA_AJUSTE) {
                                $origen = "REPARTO [AJUSTE] ";
                                $destino =  $Operacion->almacenSucursal->nombre;
                            }
                        }

                        if ($Operacion->tipo == Operacion::TIPO_SALIDA) {
                            if ($Operacion->motivo == Operacion::SALIDA_TRASPASO) {
                                $destino  =  $Operacion->sucursalRecibe->nombre;
                                $origen =  $Operacion->almacenSucursal->nombre;
                            }
                            if ($Operacion->motivo == Operacion::SALIDA_TRASPASO_UNIDAD) {
                                $destino = "RECOLECCION [REPARTO] - FOLIO #". $Operacion->operacionChild->almacenSucursal->nombre;
                                $origen =  $Operacion->almacenSucursal->nombre;
                            }

                            /*if ($Operacion->motivo == Operacion::SALIDA_RUTA_AJUSTE) {
                                $destino = "RECOLECCION [REPARTO] - FOLIO #". $Operacion->reparto_id;
                                $origen =  $Operacion->almacenSucursal->nombre;
                            }*/

                            if ($Operacion->motivo == Operacion::SALIDA_RUTA_AJUSTE) {
                                $destino = "REPARTO [AJUSTE]";
                                $origen =  $Operacion->almacenSucursal->nombre;
                            }
                        }

                        $responseArray = [
                            "id"                => $Operacion->id,
                            "folio"             => "#".$Operacion->id,
                            "tipo"              => $Operacion->tipo,
                            "tipo_text"         => Operacion::$tipoList[$Operacion->tipo],
                            "motivo"            => $Operacion->motivo,
                            "motivo_text"       => Operacion::$operacionList[$Operacion->motivo],
                            "origen"            => $origen,
                            "destino"           => $destino,
                            "compra_id"         => $Operacion->compra_id,
                            "status"            => $Operacion->status,
                            "responsable"       => $Operacion->createdBy->nombreCompleto,
                            "created_at"       => date("Y-m-d h:i a", $Operacion->created_at),
                            "producto_detalle"  => [],
                        ];

                        foreach (Operacion::getOperacionDetalleGroup($Operacion->id) as $key => $v_detalle) {
                            array_push($responseArray["producto_detalle"], [
                                "producto"      => $v_detalle["producto"],
                                "clave"         => $v_detalle["producto_clave"],
                                "cantidad"      => $v_detalle["cantidad"],
                                "producto_unidad"  => Producto::$medidaList[$v_detalle["producto_tipo_medida"]],
                            ]);
                        }

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
    public function actionEntradasSalidasJsonBtt(){
        return ViewEntradaSalida::getJsonBtt(Yii::$app->request->get());
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
