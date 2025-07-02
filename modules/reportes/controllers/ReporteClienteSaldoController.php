<?php
namespace app\modules\reportes\controllers;

use Yii;
use yii\web\Response;
use app\models\cliente\ViewCliente;
use yii\web\BadRequestHttpException;

/**
 * Default controller for the `clientes` module
 */
class ReporteClienteSaldoController extends \app\controllers\AppController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }


    //------------------------------------------------------------------------------------------------//
    //                          ACTIONS AJAX
    //------------------------------------------------------------------------------------------------//
    public function actionReporteClienteSaldoAjax()
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            $filters = $request->get('filters');

            $reporteProducto = ViewCliente::getReporteClienteSaldoTop([ "filters" => $filters]);

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

    public function actionClienteSaldoJsonBtt(){
        return ViewCliente::getReporteClienteSaldoJsonBtt(Yii::$app->request->get());
    }
}
