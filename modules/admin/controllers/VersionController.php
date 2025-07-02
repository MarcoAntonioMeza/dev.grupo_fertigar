<?php
namespace app\modules\admin\controllers;

use Yii;
use app\models\version\Version;


/**
 * HistorialDeAccesoController implements the CRUD actions for EsysAcceso model.
 */
class VersionController extends \app\controllers\AppController
{
    /**
     * Lists all EsysAcceso models.
     * @return mixed
     */
    public function actionList()
    {
        $model = new Version();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['list']);
            }
        }
        return $this->render('index', [
            'model' => $model,
        ]);
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
        //return ViewAcceso::getJsonBtt(Yii::$app->request->get());
    }

}
