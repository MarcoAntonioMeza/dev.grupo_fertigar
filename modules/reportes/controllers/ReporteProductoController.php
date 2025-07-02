<?php
namespace app\modules\reportes\controllers;

use Yii;
use app\models\producto\ViewProducto;

/**
 * Default controller for the `clientes` module
 */
class ReporteProductoController extends \app\controllers\AppController
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

    public function actionProductosJsonBtt(){
        return ViewProducto::getJsonBtt(Yii::$app->request->get());
    }


}
