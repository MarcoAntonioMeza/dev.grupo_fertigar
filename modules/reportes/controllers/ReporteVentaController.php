<?php
namespace app\modules\reportes\controllers;

use Yii;
use yii\web\Response;
use app\models\producto\ViewProducto;

/**
 * Default controller for the `clientes` module
 */
class ReporteVentaController extends \app\controllers\AppController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionProducto()
    {
        return $this->render('producto');
    }


    //------------------------------------------------------------------------------------------------//
    //                          ACTIONS AJAX
    //------------------------------------------------------------------------------------------------//
    public function actionReporteVentaProductoAjax()
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            $filters = $request->get('filters');

            $reporteProducto = ViewProducto::getReporteProductoTop([ "filters" => $filters]);

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

    public function actionVentaProductoJsonBtt(){
        return ViewProducto::getReporteVentaProductoJsonBtt(Yii::$app->request->get());
    }
}
