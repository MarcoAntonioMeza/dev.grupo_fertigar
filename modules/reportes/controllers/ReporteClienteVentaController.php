<?php
namespace app\modules\reportes\controllers;

use Yii;
use yii\web\Response;
use app\models\cliente\ViewCliente;

/**
 * Default controller for the `clientes` module
 */
class ReporteClienteVentaController extends \app\controllers\AppController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionCliente()
    {
        return $this->render('cliente');
    }


    //------------------------------------------------------------------------------------------------//
    //                          ACTIONS AJAX
    //------------------------------------------------------------------------------------------------//
    public function actionReporteVentaClienteAjax()
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            $filters = $request->get('filters');

            $reporteProducto = ViewCliente::getReporteClienteTop([ "filters" => $filters]);

            return [
                "code" => 202,
                "reporte" => $reporteProducto,
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    //------------------------------------------------------------------------------------------------//
    // BootstrapTable list
    //------------------------------------------------------------------------------------------------//

    public function actionVentaClienteJsonBtt(){
        return ViewCliente::getReporteVentaClienteJsonBtt(Yii::$app->request->get());
    }
}
