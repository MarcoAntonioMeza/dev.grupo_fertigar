<?php
namespace app\modules\reportes\controllers;

use Yii;
use app\models\reporte\ViewEntrada;

/**
 * Default controller for the `clientes` module
 */
class ReporteEntradaController extends \app\controllers\AppController
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

    public function actionEntradasJsonBtt(){
        return ViewEntrada::getJsonBtt(Yii::$app->request->get());
    }


}
