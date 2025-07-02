<?php
namespace app\modules\reportes\controllers;

use Yii;
use app\models\reporte\ViewSurtir;

/**
 * Default controller for the `clientes` module
 */
class ReporteSurtirController extends \app\controllers\AppController
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

    public function actionSurtirJsonBtt(){
        return ViewSurtir::getJsonBtt(Yii::$app->request->get());
    }


}
