<?php
namespace app\modules\reportes\controllers;

use Yii;
use app\models\reporte\ViewAbastecimiento;
/**
 * Default controller for the `clientes` module
 */
class ReporteAbastecimientoController extends \app\controllers\AppController
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

    public function actionAbastecimientoJsonBtt(){
        return ViewAbastecimiento::getJsonBtt(Yii::$app->request->get());
    }


}
