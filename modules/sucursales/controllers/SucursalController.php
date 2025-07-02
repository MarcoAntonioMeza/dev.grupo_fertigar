<?php
namespace app\modules\sucursales\controllers;

use Yii;
use yii\web\Controller;
use app\models\sucursal\Sucursal;
use yii\web\NotFoundHttpException;
use app\models\sucursal\ViewSucursal;
use app\models\esys\EsysDireccion;
use yii\web\UploadedFile;

/**
 * Default controller for the `clientes` module
 */
class SucursalController extends \app\controllers\AppController
{

	private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' =>  Yii::$app->user->can('sucursalCreate'),
            'update' =>  Yii::$app->user->can('sucursalUpdate'),
            'delete' =>  Yii::$app->user->can('sucursalDelete'),
        ];
    }


    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index',[
        	"can" => $this->can]);
    }

     /**
     * Displays a single EsysDivisa model.
     * @param integer $name
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'can'   => $this->can,
        ]);
    }

    /**
     * Creates a new Sucursal model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Sucursal();

		$model->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_SUCURSAL,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);

        //$model->tipo = Sucursal::TIPO_SUCURSAL;


        if ($model->load(Yii::$app->request->post()) && $model->dir_obj->load(Yii::$app->request->post())) {
        	if ($model->save()) {
	            return $this->redirect(['view',
	                'id' => $model->id
	            ]);
        	}
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {

        $model = $this->findModel($id);

        // Cargamos datos de dirección
        $model->dir_obj   = $model->direccion;

        $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;

        //$model->tipo = Sucursal::TIPO_SUCURSAL;

        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if($model->load(Yii::$app->request->post()) && $model->dir_obj->load(Yii::$app->request->post())){

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
            return $this->render('update', [
                'model' => $model,
            ]);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Sucursal model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param  integer $id The user id.
     * @return \yii\web\Response
     *
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);


        try{
            // Eliminamos el usuario
            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', "Se ha eliminado correctamente la sucursal #" . $id);

        }catch(\Exception $e){
            if($e->getCode() == 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación de la sucursal.');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }

        return $this->redirect(['index']);
    }

    //------------------------------------------------------------------------------------------------//
	// BootstrapTable list
	//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionSucursalesJsonBtt(){
        return ViewSucursal::getJsonBtt(Yii::$app->request->get());
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
                $model = Sucursal::findOne($name);
                break;

            case 'view':
                $model = ViewSucursal::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
