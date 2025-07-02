<?php
namespace app\modules\inventario\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\web\Response;
use yii\web\Controller;
use app\models\user\ViewUser;
use yii\web\BadRequestHttpException;
use app\models\producto\ViewProducto;
use app\models\inv\InventarioOperacion;
use app\models\inv\ViewInventarioOperacion;
use app\models\inv\InventarioOperacionDetalle;

/**
 * Default controller for the `clientes` module
 */
class OperacionController extends \app\controllers\AppController
{

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionViewAjuste($id)
    {
        $model = $this->findModel($id);

        return $this->render('view-ajuste', [
            'model' => $model,
        ]);
    }


    public function actionCreate()
    {
        $model = new InventarioOperacion();
        if ($model->load(Yii::$app->request->post())) {

            if ($model->save()) {
                 if ($model->tipo == InventarioOperacion::TIPO_AJUSTE_PARCIAL)
                    InventarioOperacionDetalle::saveItemProducto(Yii::$app->request->post()["Detail"], $model->id);

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
        if($model->load(Yii::$app->request->post())) {
            if ($model->save()) {

                if ($model->tipo == InventarioOperacion::TIPO_AJUSTE_PARCIAL)
                    InventarioOperacionDetalle::saveItemProducto(Yii::$app->request->post()["Detail"], $model->id);

                return $this->redirect(['view',
                    'id' => $model->id
                ]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);

    }

    public function actionCancel($id)
    {
        $model = $this->findModel($id);

        if ($model->status == InventarioOperacion::STATUS_SOLICITUD ) {
            $model->status = InventarioOperacion::STATUS_CANCEL;
            if ($model->update()) {
                Yii::$app->session->setFlash('success', 'La  cancelo correctamente');
                return $this->redirect(['view',
                    'id' => $model->id
                ]);
            }
        }

        Yii::$app->session->setFlash('danger', 'La operación no se puede CANCELAR, no cumple con los requirimientos');

        return $this->redirect(['view',
            'id' => $model->id
        ]);

    }

    public function actionSendInventario($id)
    {
        $model = $this->findModel($id);

        if (InventarioOperacion::validInvetario($model->id) || $model->verificacion == InventarioOperacion::VERIFICACION_ON) {
            $model->status = InventarioOperacion::STATUS_REVISION;
            $model->save();

            Yii::$app->session->setFlash('success', 'SE ENVIO CORRECTAMENTE');
            return $this->redirect(['view-ajuste',
                'id' => $model->id
            ]);

        }else{
            $model->verificacion = InventarioOperacion::VERIFICACION_ON;
            $model->save();

            Yii::$app->session->setFlash('warning', 'EXISTEN DIFERENCIA EN EL INVENTARIO ENVIADO Y EL INVENTARIO EN SISTEMA');

            return $this->redirect(['view-ajuste',
                'id' => $model->id
            ]);
        }
    }

    public function actionSetInventarioOperador($id)
    {
        $model = $this->findModel($id);

        if ($model->status = InventarioOperacion::STATUS_REVISION) {

            if (InventarioOperacion::loadInventarioOperador($model->id)) {
                Yii::$app->session->setFlash('success', 'SE CARGO CORRECTAMENTE EL INVENTARIO DEL OPERADOR');

                return $this->redirect(['view',
                    'id' => $model->id
                ]);
            }
        }else{
            Yii::$app->session->setFlash('danger', 'OCURRIO UN ERROR AL CARGAR EL INVENTARIO, INTENTA NUEVAMENTE');

            return $this->redirect(['view',
                'id' => $model->id
            ]);
        }
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

    public function actionLoadInventario($id)
    {
        $model = $this->findModel($id);
        if ($model->status == InventarioOperacion::STATUS_SOLICITUD || $model->status == InventarioOperacion::STATUS_PROCESO) {
            if ($model->asignado_id == Yii::$app->user->identity->id) {
                return $this->render('load-inventario', [
                    'model' => $model,
                ]);
            }
        }
        Yii::$app->session->setFlash('danger', 'La operación no se puede realizar, intenta nuevamente');
        return $this->redirect(['view-ajuste',
            'id' => $model->id
        ]);
    }

    public function actionAjustarInventario($id)
    {
        $model = $this->findModel($id);
        if ($model->status == InventarioOperacion::STATUS_REVISION) {
            return $this->render('ajuste-inventario', [
                'model' => $model,
            ]);
        }
        Yii::$app->session->setFlash('danger', 'La operación no se puede realizar, intenta nuevamente');
        return $this->redirect(['view-ajuste',
            'id' => $model->id
        ]);
    }

    public function actionImprimirAcusePdf($ajuste_inventario_id)
    {
        $model = $this->findModel($ajuste_inventario_id);

        ini_set('memory_limit', '-1');

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
            'options' => ['title' => 'Acuse'],
             // call mPDF methods on the fly
        ]);


        return $pdf->render();
    }

    public function actionImprimirAcuseOperacion($id)
    {
        $model = $this->findModel($id);

        ini_set('memory_limit', '-1');

        $content = $this->renderPartial('acuse-operacion', ["model" => $model]);

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

    public function actionGetProductoInventario(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            if ($request->get('solicitud_id')) {
                $inventario = InventarioOperacion::getInventario($request->get('solicitud_id'));
                return [
                    "code"          => 202,
                    "inventario"    => $inventario
                ];
            }

            return [
                "code" => 10,
                "message" => "Ocurrio un error, intenta nuevamente"
            ];
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionGetProductoAjustarInventario(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            if ($request->get('solicitud_id')) {
                $inventario = InventarioOperacion::getAjustarInventario($request->get('solicitud_id'));
                return [
                    "code"          => 202,
                    "inventario"    => $inventario
                ];
            }

            return [
                "code" => 10,
                "message" => "Ocurrio un error, intenta nuevamente"
            ];
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionPostProductoInventario(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            if ($request->post('inventario_array') && $request->post('solicitud_id')) {
                if (InventarioOperacion::saveInventario($request->post('inventario_array'),$request->post('solicitud_id'),true)) {
                    return [
                        "code"          => 202,
                        "message"       => "SE GUARDO CORRECTAMENTE"
                    ];

                }
            }

            return [
                "code" => 10,
                "message" => "Ocurrio un error, intenta nuevamente"
            ];
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionPostProductoAjusteInventario(){
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            if ($request->post('inventario_array') && $request->post('solicitud_id')) {
                if (InventarioOperacion::saveInventario($request->post('inventario_array'),$request->post('solicitud_id'),false)) {
                    return [
                        "code"          => 202,
                        "message"       => "SE GUARDO CORRECTAMENTE"
                    ];

                }
            }

            return [
                "code" => 10,
                "message" => "Ocurrio un error, intenta nuevamente"
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
    public function actionOperacionDetalleAjax(){
        $request =  Yii::$app->request;
        return ViewInventarioOperacion::getOperacionDetalleJsonBtt($request->get('solicitud_id'));
    }

    public function actionAjusteInventarioJsonBtt(){
        return ViewInventarioOperacion::getJsonBtt(Yii::$app->request->get());
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
                $model = InventarioOperacion::findOne($name);
                break;

            case 'view':
                $model = ViewInventarioOperacion::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
