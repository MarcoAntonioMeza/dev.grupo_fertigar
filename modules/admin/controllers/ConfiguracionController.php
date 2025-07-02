<?php
namespace app\modules\admin\controllers;

use Yii;
use yii\web\Response;
use app\models\esys\EsysSetting;


/**
 * Default controller for the `admin` module
 */
class ConfiguracionController extends \app\controllers\AppController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionConfiguracionUpdate()
    {
    	$model = new EsysSetting();
    	if (Yii::$app->request->post()) {

    		$model->saveConfiguracion(Yii::$app->request->post());
    		return $this->render('configuracion-update',[ 'model' => $model ]);
    	}

        return $this->render('configuracion-update',[ 'model' => $model ]);
    }

    public function actionPrecioLibraAjax($arr = false)
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->validateCsrfToken() && $request->isAjax) {

            $result = 0;
            $requestGet = Yii::$app->request->get();
            if (isset($requestGet["tipo_servicio"]) && $requestGet["tipo_servicio"]) {
                $result = EsysSetting::getPrecioLibra($requestGet["tipo_servicio"]);
            }
            return $result;
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }
}
