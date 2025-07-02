<?php
namespace app\modules\admin\controllers;

use Yii;
use app\models\esys\ViewAcceso;

/**
 * HistorialDeAccesoController implements the CRUD actions for EsysAcceso model.
 */
class HistorialDeAccesoController extends \app\controllers\AppController
{
    /**
     * Lists all EsysAcceso models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }


//------------------------------------------------------------------------------------------------//
// BootstrapTable list
//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionHistorialDeAccesosJsonBtt()
    {
        return ViewAcceso::getJsonBtt(Yii::$app->request->get());
    }

}
