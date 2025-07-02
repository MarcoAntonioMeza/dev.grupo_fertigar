<?php
namespace app\modules\inventario\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\models\producto\Producto;
use app\models\inv\ViewInventario;
use app\models\inv\InvProductoSucursal;

/**
 * Default controller for the `clientes` module
 */
class BalanceInventarioController extends \app\controllers\AppController
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
    public function actionBalanceInventarioJsonBtt(){
        return ViewInventario::getBalanceJsonBtt(Yii::$app->request->get());
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
                $model = Producto::findOne($name);
                break;

            case 'view':
                $model = ViewInventario::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
