<?php
namespace app\modules\reportes\controllers;

use Yii;
use app\models\reporte\ViewCargaUnidad;

/**
 * Default controller for the `clientes` module
 */
class ReporteCargaUnidadController extends \app\controllers\AppController
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
    // BootstrapTable list
    //------------------------------------------------------------------------------------------------//

    public function actionCargaUnidadJsonBtt(){
        return ViewCargaUnidad::getJsonBtt(Yii::$app->request->get());
    }


}
