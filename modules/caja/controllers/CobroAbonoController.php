<?php
namespace app\modules\caja\controllers;

use Yii;
use app\models\cobro\ViewCobroAbono;

/**
 * Default controller for the `clientes` module
 */
class CobroAbonoController extends \app\controllers\AppController
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
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionCobroAbonosJsonBtt(){
        return ViewCobroAbono::getJsonBtt(Yii::$app->request->get());
    }

//------------------------------------------------------------------------------------------------//
// HELPERS
//------------------------------------------------------------------------------------------------//
   /**
    * Finds the model based on its primary key value.
    * If the model is not found, a 404 HTTP exception will be thrown.
    * @return Model
    * @throws NotFoundHttpException if the model cannot be found
    */
   protected function findModel($name, $_model = 'model')
   {
       switch ($_model) {
           case 'model':
               $model = AperturaCaja::findOne($name);
               break;
       }

       if ($model !== null)
           return $model;

       else
           throw new NotFoundHttpException('La página solicitada no existe.');
   }
}
