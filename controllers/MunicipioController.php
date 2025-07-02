<?php
namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\BadRequestHttpException;
use app\models\esys\EsysListaDesplegable;
use app\models\esys\EsysDireccionCodigoPostal;

/**
 * MunicipioController implements the CRUD actions for EsysListaDesplegable model.
 */
class MunicipioController extends Controller
{
    public function actionMunicipiosAjax($estado_id) {
        if(Yii::$app->request->isAjax){
	        Yii::$app->response->format = Response::FORMAT_JSON;

	        return EsysListaDesplegable::getMunicipios(['estado_id' => $estado_id]);
        }

        throw new BadRequestHttpException();
    }

    public function actionCodigoPostalAjax($codigo_postal) {
        if(Yii::$app->request->isAjax){
	        Yii::$app->response->format = Response::FORMAT_JSON;

	        return EsysDireccionCodigoPostal::getEstadoMunicipiosColonia(['codigo_postal' => $codigo_postal]);
        }

        throw new BadRequestHttpException();
    }

    public function actionColoniaAjax($estado_id, $municipio_id) {
        if(Yii::$app->request->isAjax){
	        Yii::$app->response->format = Response::FORMAT_JSON;

	        return EsysDireccionCodigoPostal::getEstadoMunicipiosColonia(['estado_id' => $estado_id,'municipio_id' => $municipio_id]);
        }

        throw new BadRequestHttpException();
    }

}
