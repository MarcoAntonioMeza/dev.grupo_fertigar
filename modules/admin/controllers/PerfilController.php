<?php
namespace app\modules\admin\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\auth\Perfil;
use app\models\auth\AuthAssignment;

/**
 * PerfilController implements the CRUD actions for Perfil model.
 */
class PerfilController extends \app\controllers\AppController
{
    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('perfilUserCreate'),
            'update' => Yii::$app->user->can('perfilUserUpdate'),
            'delete' => Yii::$app->user->can('perfilUserDelete'),
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
     * Displays a single EsysDivisa model.
     * @param integer $name
     * @return mixed
     */
    public function actionView($name)
    {
        $model = $this->findModel($name);

        return $this->render('view', [
            'model' => $model,
            'can'   => $this->can,
        ]);
    }

    /**
     * Creates a new Perfil model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Perfil();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view',
                'name' => $model->name
            ]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Perfil model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $name
     * @return mixed
     */
    public function actionUpdate($name)
    {
        $model = $this->findModel($name);

        if ($model->load(Yii::$app->request->post())) {

            $temp_assignment = AuthAssignment::removeAssignment($model->old_name);

            if ($model->save()) {
                //$model->updatePerfil();
                AuthAssignment::perfilAssignment($temp_assignment,$model->name);


                return $this->redirect(['view',
                    'name' => $model->name
                ]);

            }else{
                Yii::$app->session->setFlash('danger', 'No se puede cambiar el nombre del perfil, existen dependencias que impiden el cambio');
                return $this->redirect(['view',
                    'name' => $model->old_name
                ]);
            }
        } else {
            $model->privilegios_count = 1;
        }

        return $this->render('update', [
            'model' => $model
        ]);
    }

    /**
     * Deletes an existing Perfil model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $name
     * @return mixed
     */
    public function actionDelete($name)
    {
        try{
            $this->findModel($name)->delete();

            Yii::$app->session->setFlash('success', "Se ha eliminado el perfil: " . $name);

        }catch(\Exception $e){
            if($e->getCode() == 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación del perfil.');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }

        return $this->redirect('index');
    }


//------------------------------------------------------------------------------------------------//
// BootstrapTable list
//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionPerfilesJsonBtt(){
        return Perfil::getJsonBtt(Yii::$app->request->get());
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
                $model = Perfil::findOne($name);
                break;

            case 'view':
                $model = Perfil::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }

}
