<?php
namespace app\modules\contabilidad\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\web\Response;
use app\models\venta\Venta;
use app\models\venta\VentaCobro;
use app\models\compra\Compra;
use app\models\compra\CompraPago;
use yii\web\NotFoundHttpException;
use app\models\contabilidad\ContabilidadCuenta;
use app\models\contabilidad\ContabilidadTransaccion;
use app\models\contabilidad\ContabilidadPoliza;
use app\models\contabilidad\ViewContabilidadPoliza;
use app\models\contabilidad\ContabilidadTransaccionDetail;
use app\models\contabilidad\ContabilidadPolizaVerificacion;
use app\models\contabilidad\ViewContabilidadPolizaVerificacion;

class PolizasController extends \app\controllers\AppController
{
    private $can;
    public function init()
    {
        parent::init();
            $this->can = [
                'create' => Yii::$app->user->can('admin'),
                'update' => Yii::$app->user->can('admin'),
                'delete' => Yii::$app->user->can('admin'),
            ];
    }
    public function actionIndex()
    {
        return $this->render('index',[
            'can'=> $this->can,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'can'   => $this->can,
        ]);
    }

    public function actionVerificacionView($id)
    {
        $model = ContabilidadPolizaVerificacion::findOne($id);
        if ($model !== null)
            return $this->render('view-verificacion', [
                'model' => $model,
            ]);

        throw new NotFoundHttpException('La página solicitada no existe.');
    }

    public function actionCreate()
    {
        $model = new ContabilidadPolizasDetails();
        return $this->render('create', [
            'model' => $model,
        ]);
    }
    public function actionUpdate($id,$type)
    {
        $model = $this->findModel($id,$type);
        return $this->render('update',[
        'model' => $model
        ]);
    }


    public function actionNewManual()
    {

        return $this->render('new-polizas', [
            'can'   => $this->can,
        ]);
    }

    public function actionAsientosContables()
    {
        $request = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;


        if (isset( $request['poliza_type']) &&  isset($request['concepto']) && isset($request['cuentas']) && isset($request['id_cuenta']) && isset($request['cargos']) && isset($request['abonos']) )
        {

            $response = ContabilidadPoliza::createPoliza( $request['poliza_type'],  $request['concepto'], $request['cuentas'], $request['id_cuenta'], $request['cargos'], $request['abonos']);

            if ($response["code"] == 202) {
                 return [
                    "code" => 202,
                    "message" => "SE REGISTRO CORRECTAMENTE LA OPERACION"
                ];
            }
        }

        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente"
        ];
    }

    public function actionVerificacionCortePoliza()
    {
        return $this->render('create-verificacion-poliza', [
            'can'   => $this->can,
        ]);
    }

    public function actionGetPolizasCorte()
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->get('tipoTransaccion')) {
                return [
                    "code"          => 202,
                    "polizas"        => ContabilidadPolizaVerificacion::getTransaccionPolizas($request->get('tipoTransaccion'))
                ];
            }

            return [
                "code"          => 10,
                "message"        => "OCURRIO UN ERROR, INTENTA NUEVAMENTE"
            ];
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');

    }

    public function actionPostCreateVerificacion()
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->post('operacionVerificacionPoliza') && $request->post('tipoTransaccion') ) {
                $response = ContabilidadPolizaVerificacion::postCreateVerificacionPolizas($request->post('operacionVerificacionPoliza'), $request->post('tipoTransaccion'));
                if ($response["code"] == 202 ) {
                    return [
                        "code"          => 202,
                        "message"       => "SE REALIZO CORRECTAMENTE LA OPERACION"
                    ];
                }
            }
            return [
                "code"          => 10,
                "message"        => "OCURRIO UN ERROR, INTENTA NUEVAMENTE"
            ];
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionImprimirReporte($id)
    { 
        $model = ContabilidadPolizaVerificacion::findOne($id);

        $content = $this->renderPartial('acuse-reporte', ["model" => $model ]);

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
    //------------------------------------------------------------------------------------------------//
    // BootstrapTable list
    //------------------------------------------------------------------------------------------------//

    public function actionPolizasJsonBtt()
    {
        return ViewContabilidadPoliza::getJsonBtt(Yii::$app->request->get());
    }

    public function actionVerificacionPolizasJsonBtt()
    {
        return ViewContabilidadPolizaVerificacion::getJsonBtt(Yii::$app->request->get());
    }

    protected function findModel($id, $_model = 'model')
    {

        switch ($_model) {
            case 'model':
                $model = ContabilidadPoliza::findOne($id);
                break;
            case 'view':
                $model = ViewContabilidadPoliza::findOne($id);
                break;
        }
        if ($model !== null)
            return $model;
        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }

}


    
