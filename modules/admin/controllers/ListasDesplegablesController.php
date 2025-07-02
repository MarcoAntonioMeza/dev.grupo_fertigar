<?php
namespace app\modules\admin\controllers;

use Yii;
use yii\web\Response;
use app\models\esys\EsysListaDesplegable;

/**
 * Default controller for the `sucursal-listas_desplegables` module
 */
class ListasDesplegablesController extends \app\controllers\AppController
{
    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('listaDesplegableCreate'),
            'update' => Yii::$app->user->can('listaDesplegableUpdate'),
            'delete' => Yii::$app->user->can('listaDesplegableDelete'),
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'can' => $this->can,
        ]);
    }

    /**
     * Lista ListaDesplegable
     * @return JSON string
     */
    public function actionListas($modulo_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return EsysListaDesplegable::getListas($modulo_id);
    }

    /**
     * Items ListaDesplegable
     * @return JSON string
     */
    public function actionItems($label)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return EsysListaDesplegable::getItems($label, true);
    }

    /**
     * Obtiene la tabla de un mdodulo
     * @return JSON string
     */
    public function actionTabla($modulo_id)
    {
        return EsysListaDesplegable::getTable($modulo_id);
    }

    /**
     * Items ListaDesplegable
     * @return JSON string
     */
    public function actionCreateAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->getRequest();

        $orden = EsysListaDesplegable::find()
            ->select(['orden'])
            ->where(['label' => $request->post('label')])
            ->orderBy('orden desc')
            ->limit(1)
            ->one();

        $orden = $orden? $orden->orden +1: 1;

        try{
            $model = new EsysListaDesplegable([
                'label'                         => $request->post('label'),
                'singular'                      => $request->post('singular'),
                'plural'                        => $request->post('plural'),
                'orden'                         => $orden,
            ]);

            #if ($request->post('is_mex') && $request->post('is_mex') != false && $request->post('is_mex') != 'false') {
            #    #$model->is_mex = EsysListaDesplegable::IS_MEX;
            #    $model->mex_costo_extra     = $request->post('costo_extra');
            #    $model->mex_intervalo       = $request->post('intervalo');
            #    $model->mex_required_min    = $request->post('required_min');
            #}

            $model->save();

        }catch(\Exception $e){
            if($e->getCode() === 23000){
                return ['errno' => 23000, 'error' => 'Ya existe un elemento con el mismo nombre.'];

            }else{
                throw $e;
            }
        }

        if($model->id == null)
            return ['errno' => 23000, 'error' => 'Ya existe un elemento con el mismo nombre.'];

        return ['errno' => 0, 'id' => $model->id];
    }

    /**
     * Items ListaDesplegable
     * @return JSON string
     */
    public function actionUpdateAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try{
            $request = Yii::$app->getRequest();

            $model = EsysListaDesplegable::findOne($request->post('id'));
            $model->singular = $request->post('singular');
            $model->plural   = $request->post('plural');

            #if ($request->post('is_mex') && $request->post('is_mex') != false && $request->post('is_mex') != 'false') {
            #    #$model->is_mex = EsysListaDesplegable::IS_MEX;
            #    $model->mex_costo_extra     = $request->post('costo_extra');
            #    $model->mex_intervalo       = $request->post('intervalo');
            #    $model->mex_required_min    = $request->post('required_min');
            #}else{
            #    #$model->is_mex = null;
            #    $model->mex_costo_extra     = null;
            #    $model->mex_intervalo       = null;
            #    $model->mex_required_min    = null;
            #}


            $model->save();

        }catch(\Exception $e){
            if($e->getCode() === 23000){
                return ['errno' => 23000, 'error' => 'Ya existe un elemento con el mismo nombre.'];

            }else{
                throw $e;
            }
        }

        if($model->id == null)
            return ['errno' => 23000, 'error' => 'Ya existe un elemento con el mismo nombre.'];

        return ['errno' => 0, 'id' => $model->id];
    }

    /**
     * Items ListaDesplegable
     * @return JSON string
     */
    public function actionDeleteAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->getRequest();
        //$params  = $request->bodyParams;
        $return  = ['errno' => 0];

        foreach ($request->getBodyParam('ids') as $key => $value) {
            try{
                $model = EsysListaDesplegable::deleteAll(['id' => $value]);

            }catch(\Exception $e){
                if($e->getCode() === 23000){
                    $return = ['errno' => 23000, 'error' => 'Alguno(s) de los elemento que deseas eliminar contiene(n) dependencias y no puede(n) ser eliminado.'];

                }else{
                    throw $e;
                }
            }
        }

        return $return;
    }

    /**
     * Items ListaDesplegable
     * @return JSON string
     */
    public function actionSortAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->getRequest();
        //$params  = $request->bodyParams;
        $orden   = 1;
        $return  = ['errno' => 0];

        foreach ($request->getBodyParam('ids') as $key => $value) {
            $model = EsysListaDesplegable::findOne($value);
            $model->orden = $orden++;
            $model->save();
        }

        return $return;
    }

}
