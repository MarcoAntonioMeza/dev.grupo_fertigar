<?php
namespace app\modules\reportes\controllers;

use Yii;
use yii\web\Response;
use app\models\reparto\ViewReparto;

/**
 * Default controller for the `clientes` module
 */

class ReporteCentroNegocioController extends \app\controllers\AppController
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
    public function actionReporteCentroNegocioAjax()
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            $filters = $request->get('filters');

            $reporteCentroNegocio = ViewReparto::getReporteCentroNegocio([ "filters" => $filters]);

            return [
                "code" => 202,
                "reporte" => $reporteCentroNegocio,
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }
}
