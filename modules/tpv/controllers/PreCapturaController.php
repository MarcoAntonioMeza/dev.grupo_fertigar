<?php
namespace app\modules\tpv\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\web\Response;
use app\models\venta\Venta;
use app\models\venta\ViewVenta;
use yii\web\NotFoundHttpException;
use app\models\sucursal\Sucursal;
use app\models\producto\Producto;
use app\models\esys\EsysDireccion;
use app\models\venta\VentaDetalle;
use app\models\producto\ViewProducto;
use app\models\inv\InvProductoSucursal;
use app\models\venta\TransVenta;

/**
 * Default controller for the `clientes` module
 */
class PreCapturaController extends \app\controllers\AppController
{

	private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('precapturaCreate'),
            'update' => Yii::$app->user->can('precapturaUpdate'),
            'cancel' => Yii::$app->user->can('precapturaCancel'),
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
        $model = new Venta();
        $model->venta_detalle   = new VentaDetalle();

        $model->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_VENTA,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->venta_detalle->load(Yii::$app->request->post())) {

            $model->is_especial = Venta::VENTA_GENERAL;
            //$model->pay_credito = $model->pay_credito  ? Venta::PAY_CREDITO_ON : null;

            if ($model->is_especial ==  Venta::VENTA_ESPECIAL )
                $model->dir_obj->load(Yii::$app->request->post());


            $model->tipo = Venta::TIPO_GENERAL;

            if ( isset(Yii::$app->request->post()['CheckPrecioMenudeo']) && Yii::$app->request->post()['CheckPrecioMenudeo'])
                $model->tipo = Venta::TIPO_MENUDEO;

            if ( isset(Yii::$app->request->post()['CheckPrecioMayoreo']) && Yii::$app->request->post()['CheckPrecioMayoreo'])
                $model->tipo = Venta::TIPO_MAYOREO;


            $model->status = Venta::STATUS_PRECAPTURA;
            if ($model->save()) {
                if($model->venta_detalle->saveVentaDetalle($model->id, $model->status )){
                    return $this->redirect(['view',
                        'id' => $model->id
                    ]);
                }
            }
        }

        return $this->render('create' , [
            'model' => $model,
            //'sucursal' => Sucursal::findOne(Yii::$app->user->identity->sucursal_id),
        ]);
    }

    public function actionUpdate($id)
    {

        $model = $this->findModel($id);

        if ($model->status == Venta::STATUS_PRECAPTURA ||  $model->status == Venta::STATUS_PROCESO ) {
            $model->venta_detalle   = new VentaDetalle();

             $model->dir_obj   = $model->direccion;
            if ($model->dir_obj)
                $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;
            else{
                $model->dir_obj = new EsysDireccion([
                    'cuenta' => EsysDireccion::CUENTA_VENTA,
                    'tipo'   => EsysDireccion::TIPO_PERSONAL,
                ]);
            }

            // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
            if($model->load(Yii::$app->request->post()) && $model->venta_detalle->load(Yii::$app->request->post())) {

                $model->is_especial = Venta::VENTA_GENERAL;
                $model->pay_credito = $model->pay_credito  ? Venta::PAY_CREDITO_ON : null;

                if ($model->is_especial ==  Venta::VENTA_ESPECIAL )
                    $model->dir_obj->load(Yii::$app->request->post());

                $model->tipo = Venta::TIPO_GENERAL;

                if ( isset(Yii::$app->request->post()['CheckPrecioMenudeo']) && Yii::$app->request->post()['CheckPrecioMenudeo'])
                    $model->tipo = Venta::TIPO_MENUDEO;

                if ( isset(Yii::$app->request->post()['CheckPrecioMayoreo']) && Yii::$app->request->post()['CheckPrecioMayoreo'])
                    $model->tipo = Venta::TIPO_MAYOREO;

                //$model->status = Venta::STATUS_PRECAPTURA;

                if ($model->save()) {
                    if($model->venta_detalle->saveVentaDetalle($model->id, $model->status )){
                        return $this->redirect(['view',
                            'id' => $model->id
                        ]);
                    }
                }
            }

            return $this->render('update', [
                'model' => $model,
                //'sucursal' => Sucursal::findOne(Yii::$app->user->identity->sucursal_id),
            ]);
        }else{

            Yii::$app->session->setFlash('danger', "LA VENTA YA NO SE PUEDE EDITAR, CONSULTA AL ADMINISTRADOR ");

            return $this->redirect(['view',
                'id' => $model->id
            ]);
        }
    }

    public function actionImprimirAcusePdf($venta_id)
    {

        $Reparto = $this->findModel($venta_id);

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

        $content = $this->renderPartial('pagare', ["model" => $Reparto, "copy" => false ]);
        $pdf_api->WriteHTML($content);
        $pdf_api->setFooter('<table width="100%" style="padding-top: 5px;margin-top: 15px">
            <tr>
                <td   style="text-align:justify; ">
                    <p style="font-size:12px;color: #000;">SE SUSCRIBE EL PRESENTE PAGARÉ EN LA CIUDAD DE __ <strong>VERACRUZ VER.</strong> __ a __ <strong>'. date("Y-m-d", time()) .'</strong> __ DEBE(MOS) Y PAGARE(MOS) INCONDICIONALMENTE POR ESTE PAGARÉ A LA ORDEN DE : __<strong>PESCADOS Y MARISCOS ARROYOS SA DE CV</strong>__, EN LA CUIDAD DE __ <strong>VERACRUZ</strong> __ EL DIA ____________________________ LA CANTIDAD DE: __ <strong>'. number_format($Reparto->total,2) .'</strong> __  CANTIDAD QUE HE(MOS) RECIBIDO A ENTERA SATISFACCION, ESTE PAGARÉ DOMICILIADO DE NO CUBRIR INTEGRALMENTE EL VALOR QUE AMPARA ESTE DOCUMENTO PRECISAMENTE EN LA FECHA DE SU VENCIMIENTO CAUSARA INTERES MORATORIOS DEL 5% MENSUAL DURANTE TODO EL TIEMPO QUE PERMANECIERE TOTAL O PARCIALMENTE INSOLUTO, SIN QUE POR ELLO SE ENTIENDA PRORROGADO EL PLAZO.</p>
                </td>
            </tr>
        </table>
        <table width="100%" style="margin-top: 15px;">
            <tr>
                <td width="70%" >
                    <strong style="font-size:12px">CLIENTE:  <small style="font-size:16px; font-weight: 100;">'. $Reparto->cliente->nombreCompleto .'</small></strong>
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

        $content = $this->renderPartial('pagare', ["model" => $Reparto, "copy" => true]);
        $pdf_api->WriteHTML($content);
        $pdf_api->setFooter('<table width="100%" style="padding-top: 5px;margin-top: 15px">
            <tr>
                <td   style="text-align:justify; ">
                    <p style="font-size:12px;color: #000;">SE SUSCRIBE EL PRESENTE PAGARÉ EN LA CIUDAD DE __ <strong>VERACRUZ VER.</strong> __ a __ <strong>'. date("Y-m-d", time()) .'</strong> __ DEBE(MOS) Y PAGARE(MOS) INCONDICIONALMENTE POR ESTE PAGARÉ A LA ORDEN DE : __<strong>PESCADOS Y MARISCOS ARROYOS SA DE CV</strong>__, EN LA CUIDAD DE __ <strong>VERACRUZ</strong> __ EL DIA ____________________________ LA CANTIDAD DE: __ <strong>'. number_format($Reparto->total,2) .'</strong> __  CANTIDAD QUE HE(MOS) RECIBIDO A ENTERA SATISFACCION, ESTE PAGARÉ DOMICILIADO DE NO CUBRIR INTEGRALMENTE EL VALOR QUE AMPARA ESTE DOCUMENTO PRECISAMENTE EN LA FECHA DE SU VENCIMIENTO CAUSARA INTERES MORATORIOS DEL 5% MENSUAL DURANTE TODO EL TIEMPO QUE PERMANECIERE TOTAL O PARCIALMENTE INSOLUTO, SIN QUE POR ELLO SE ENTIENDA PRORROGADO EL PLAZO.</p>
                </td>
            </tr>
        </table>
        <table width="100%" style="margin-top: 15px;">
            <tr>
                <td width="70%" >
                    <strong style="font-size:12px">CLIENTE:  <small style="font-size:16px; font-weight: 100;">'. $Reparto->cliente->nombreCompleto .'</small></strong>
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


        return $pdf->render();

    }

    public function actionSaveConversionProducto()
    {

        $request = Yii::$app->request->post()["Conversion"];

        if ( isset($request["id"]) && $request["cantidad"] ) {

            $VentaDetalle = VentaDetalle::findOne($request["id"]);
            if ($VentaDetalle) {
                $VentaDetalle->cantidad                 = $request["cantidad"];
                if ($VentaDetalle->update()) {

                    $preventa   = Venta::findOne($VentaDetalle->venta_id);

                    $total_new  = 0;
                    foreach ($preventa->ventaDetalle as $key => $item_detalle) {
                        $total_new = $total_new + floatval($item_detalle->cantidad * $item_detalle->precio_venta);
                    }
                    $preventa->total = round($total_new,2);
                    $preventa->update();

                    Yii::$app->session->setFlash('success', 'SE REALIZO CORRECTAMENTE LA OPERACION');
                    return $this->redirect(['view', 'id' => $preventa->id]);
                }
            }else{

                Yii::$app->session->setFlash('danger', 'Ocurrio un error al REALIZAR LA OPERACION, intenta nuevamente');
                return $this->redirect(['index']);
            }
        }
        Yii::$app->session->setFlash('danger', 'Ocurrio un error al REALIZAR LA OPERACION, intenta nuevamente');
        return $this->redirect(['index']);
    }


    public function actionSearchProductoId(){

        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('id')) {

                $producto  = Producto::findOne(trim($request->get('id')));
                $sucursal_id = $request->get('sucursal_id') ? $request->get('sucursal_id') : Yii::$app->user->identity->sucursal_id;
                $existencia = 0;

                $existencia_tienda = 0;
                $existencia_bodega = 0;

                if ($sucursal_id) {

                    $InvProductoSucursal = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , $sucursal_id  ],[ "=", "producto_id" , $producto->id ] ])->one();

                    if (isset($InvProductoSucursal->id))
                        $existencia = $InvProductoSucursal->cantidad;
                }

                $sub_existencia = 0;

                if ($producto->is_subproducto == Producto::TIPO_SUBPRODUCTO) {
                    $SubInvProductoSucursal = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , $sucursal_id  ],[ "=", "producto_id" , $producto->sub_producto_id ] ])->one();

                    if (isset($SubInvProductoSucursal->id))
                      $sub_existencia = $SubInvProductoSucursal->cantidad;

                    // CONSULTAMOS CUANTO PRODUCTO SE ENCUENTRA COMPROMETIDO
                    $sub_existencia = $sub_existencia;
                }

                $InvTienda = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , 4  ],[ "=", "producto_id" , $producto->id ] ])->one();

                    if (isset($InvTienda->id))
                        $existencia_tienda = $InvTienda->cantidad;

                $InvBodega = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , 3  ],[ "=", "producto_id" , $producto->id ] ])->one();

                    if (isset($InvBodega->id))
                        $existencia_bodega = $InvBodega->cantidad;

                // CONSULTAMOS CUANTO PRODUCTO SE ENCUENTRA COMPROMETIDO
                $existencia = $existencia;

                if (isset($producto->id)) {
                    return [
                        "code" => 202,
                        "producto" => [
                            "id"                        => $producto->id,
                            "clave"                     => $producto->clave,
                            "nombre"                    => $producto->nombre,
                            "is_subproducto"            => $producto->is_subproducto,
                            "sub_cantidad_equivalente"  => $producto->sub_cantidad_equivalente,
                            "sub_producto_id"           => $producto->sub_producto_id,
                            "sub_producto_nombre"       => isset($producto->subProducto->id) ? $producto->subProducto->nombre : null,
                            "sub_producto_unidad"       => isset($producto->subProducto->id) ? Producto::$medidaList[$producto->subProducto->tipo_medida] : null,
                            "sub_existencia"            => $sub_existencia,
                            "costo"                     => $producto->costo,
                            "publico"                   => $producto->precio_publico,
                            "mayoreo"                   => $producto->precio_mayoreo,
                            "menudeo"                   => $producto->precio_menudeo,
                            "existencia"                => $existencia,
                            "existencia_bodega"         => $existencia_bodega,
                            "existencia_tienda"         => $existencia_tienda,
                            "proveedor"                 => isset($producto->proveedor->nombre) ? $producto->proveedor->nombre : 'N/A',
                            "tipo_medida"               => $producto->tipo_medida,
                            "tipo_medida_text"          =>Producto::$medidaList[$producto->tipo_medida],
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

    public function actionUpdatePrecio(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->post('producto_id') && $request->post('precio') && $request->post('tipo')) {

                $producto  = Producto::findOne($request->post('producto_id'));



                if (intval($request->post('tipo')) == 10 )
                    $producto->precio_publico = $request->post('precio');

                if (intval($request->post('tipo')) == 20 )
                    $producto->precio_menudeo = $request->post('precio');

                if (intval($request->post('tipo')) == 30 )
                    $producto->precio_mayoreo = $request->post('precio');

                if ($producto->update()) {
                    return [
                        "code" => 202,
                        "message" => "Se actualizo correctamente",
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

    public function actionSearchProductoNombre(){

        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('nombre')) {

                $productos  = Producto::find()->andFilterWhere([ 'or',
                    ['like', 'nombre', trim($request->get('nombre'))],
                    ['like', 'clave', trim($request->get('nombre'))],
                ])->orderBy("nombre")->limit(20)->all();



                $responseArray = [];

                foreach ($productos as $key => $producto) {

                    $existencia_tienda = 0;
                    $existencia_bodega = 0;


                    $InvTienda = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , 4  ],[ "=", "producto_id" , $producto->id ] ])->one();

                        if (isset($InvTienda->id))
                            $existencia_tienda = $InvTienda->cantidad;

                    $InvBodega = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , 3  ],[ "=", "producto_id" , $producto->id ] ])->one();

                        if (isset($InvBodega->id))
                            $existencia_bodega = $InvBodega->cantidad;

                    array_push($responseArray, [
                        "id"                => $producto->id,
                        "clave"             => $producto->clave,
                        "nombre"            => $producto->nombre,
                        "publico"           => $producto->precio_publico,
                        "mayoreo"           => $producto->precio_mayoreo,
                        "menudeo"           => $producto->precio_menudeo,
                        "existencia_bodega" => $existencia_bodega,
                        "existencia_tienda" => $existencia_tienda,
                        "proveedor"         => isset($producto->proveedor->nombre) ? $producto->proveedor->nombre : 'N/A',
                        "tipo_medida"       => $producto->tipo_medida,
                        "tipo_medida_text"  => $producto->tipo_medida ? Producto::$medidaList[$producto->tipo_medida] : null,
                    ]);
                }

                if (isset($producto->id)) {
                    return [
                        "code" => 202,
                        "productos" => $responseArray,
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

            $responseArray  = [];
            $producto       = ViewProducto::getProductoSeachAjax($text);
            // Obtenemos user
            foreach ($producto as $key => $item_producto) {
                $existencia = 0;
                $InvProductoSucursal = InvProductoSucursal::find()->andWhere(["and",["=", "sucursal_id" , Yii::$app->user->identity->sucursal_id ],[ "=", "producto_id" , $item_producto["id"] ] ])->one();

                if (isset($InvProductoSucursal->id))
                    $existencia = $InvProductoSucursal->cantidad;

                $item_producto["text"]     = $item_producto["nombre"] ." / Stock:  [" . $existencia . " ". ( $item_producto["tipo_medida"] ? Producto::$medidaList[$item_producto["tipo_medida"]] : '') ."]";
                $item_producto["existencia"] = $existencia;

                array_push($responseArray, $item_producto);

            }

            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $responseArray;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['q' => $text, 'results' => $responseArray];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
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

        $model->status = Venta::STATUS_CANCEL;

        if ($model->save()) {

            Yii::$app->session->setFlash('success', "Se ha cancelado correctamente la PRE-CAPTURA #" . $id);
        }else{
            Yii::$app->session->setFlash('success', "Ocurrio un error al cancelar la PRE-CAPTURA #" . $id);
        }

        return $this->redirect(['index']);
    }

    //------------------------------------------------------------------------------------------------//
	// BootstrapTable list
	//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionVentasJsonBtt(){
        return ViewVenta::getJsonBtt(Yii::$app->request->get());
    }

    public function actionVentaDetalleAjax(){
        return ViewVenta::getVentaDetalleAjax(Yii::$app->request->get());
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
                $model = Venta::findOne($name);
                break;

            case 'view':
                $model = ViewVenta::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
