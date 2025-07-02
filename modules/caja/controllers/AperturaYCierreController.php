<?php
namespace app\modules\caja\controllers;


use Yii;
use kartik\mpdf\Pdf;
use app\models\venta\Venta;
use app\models\cobro\CobroVenta;
use app\models\credito\CreditoAbono;
use app\models\apertura\ViewAperturaCierre;
use app\models\apertura\ViewReporteGastos;
use app\models\apertura\AperturaCaja;
use app\models\credito\Credito;
use app\models\credito\CreditoTokenPay;
use app\models\apertura\AperturaCajaDetalle;

/**
 * Default controller for the `clientes` module
 */
class AperturaYCierreController extends \app\controllers\AppController
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

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionImprimirRecibo($id)
    {
        $model = $this->findModel($id);
        $lengh = 350;
        $width = 80;
        $count = 0;
        $total_piezas = 0;
        foreach ($model->aperturaCajaDetalles as $key => $item) {
            $count = $count + 1;
        }

        $lengh = $lengh + ($count  * 30 );


        //$width= $width + ($count  * 2 );

        $content = $this->renderPartial('recibo', ["model" => $model]);

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
            'options' => ['title' => 'Ticket de recibo'],
             // call mPDF methods on the fly
            'methods' => [
                'SetHeader'=>[ 'RECIBO #' . $model->id],
                //'SetFooter'=>['{PAGENO}'],
            ]
        ]);

        $pdf->marginLeft = 3;
        $pdf->marginRight = 3;

        $pdf->setApi();

        /*$pdf_api = $pdf->getApi();
        $pdf_api->SetWatermarkImage(Yii::getAlias('@web').'/img/marca_agua_cora.png');
        $pdf_api->showWatermarkImage = true;*/


        // return the pdf output as per the destination setting
        return $pdf->render();

    }

    public function actionImprimirReporte($id)
    {
        $model = $this->findModel($id);
        $lengh = 280;
        $width = 210;
        //$view_reporte =ViewReporteGastos::getGastosXcaja($id);
        $view_reporte = AperturaCajaDetalle::getGastosXcaja($id);


        //$width= $width + ($count  * 2 );

        $content = $this->renderPartial('reporte', ["model" => $model,"view_reporte"=>$view_reporte]);

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
            'cssInline' => '.kv-heading-1{font-size:18px}
                .table-resumen{
                    width: 100%;
                    border-collapse: collapse;
                }
                .table-totales{
                    width: 100%;
                    border-collapse: collapse;
                }
                .total{
                    font: oblique bold cursive;
                    text-align:center;
                }',
             // set mPDF properties on the fly
            'options' => ['title' => 'Ticket de recibo'],
             // call mPDF methods on the fly
            'methods' => [
                'SetHeader'=>[ 'REPORTE #' . $model->id],
                //'SetFooter'=>['{PAGENO}'],
            ]
        ]);

        $pdf->marginLeft = 3;
        $pdf->marginRight = 3;

        $pdf->setApi();

        /*$pdf_api = $pdf->getApi();
        $pdf_api->SetWatermarkImage(Yii::getAlias('@web').'/img/marca_agua_cora.png');
        $pdf_api->showWatermarkImage = true;*/


        // return the pdf output as per the destination setting
        return $pdf->render();

    }
    public function actionGetCompraVenta($token_pay){
        $cobros = CreditoAbono::findOne(['token_pay' => $token_pay]);
        $credito = Credito::findOne(['id' => $cobros->credito_id]);

        return $credito->id;
    }

    public function actionImprimirCredito($pay_items)
    {
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

        $pdf->marginLeft = 3;
        $pdf->marginRight = 3;

        $pdf->setApi();

        /*$pdf_api = $pdf->getApi();
        $pdf_api->SetWatermarkImage(Yii::getAlias('@web').'/img/marca_agua_cora.png');
        $pdf_api->showWatermarkImage = true;*/


        // return the pdf output as per the destination setting
        return $pdf->render();
    }

    
    //------------------------------------------------------------------------------------------------//
	// BootstrapTable list
	//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionAperturaCierreJsonBtt(){
        return ViewAperturaCierre::getJsonBtt(Yii::$app->request->get());
    }

    public function actionAperturaCierreOperacionDetailJsonBtt(){
        return AperturaCaja::getHistoryMovimientoJsonBtt(Yii::$app->request->get());
    }

    public function actionAperturaCierreOperacionDetailOtrasJsonBtt(){
        return AperturaCaja::getHistoryMovimientoOtrasJsonBtt(Yii::$app->request->get());
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
               $model = AperturaCaja::findOne($name);
               break;
       }

       if ($model !== null)
           return $model;

       else
           throw new NotFoundHttpException('La página solicitada no existe.');
   }
}
