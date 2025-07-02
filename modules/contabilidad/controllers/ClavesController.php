<?php
namespace app\modules\contabilidad\controllers;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use app\models\contabilidad\ContabilidadCuenta;
use app\models\contabilidad\ContabilidadCuentaRel;
use app\models\contabilidad\ViewContabilidadCuenta;
/**
 * ClavesController implements the CRUD actions for Cliente model.
 */
class ClavesController extends \app\controllers\AppController
{
    private $can;
    public function init()
    {
        parent::init();
            $this->can = [
                'create' => Yii::$app->user->can('admin'),
                'update' => Yii::$app->user->can('admin'),
                'delete' => Yii::$app->user->can('admin'),
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
    
    public function actionCreate()
    { 
        $model  = new ContabilidadCuenta();
        if ( $model->load(Yii::$app->request->post())){
            if($model->save())
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Cliente model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if($model->save())
                    return $this->redirect(['index']);
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try{
            // Eliminamos la clave
            $parentChild    = ContabilidadCuentaRel::find()->andWhere(["id_child" => $model->id ])->all();
            $parentParent   = ContabilidadCuentaRel::find()->andWhere(["id_parent" => $model->id ])->all();

            foreach ($parentChild as $key => $item_child) {
                $item_child->delete();
            }

            foreach ($parentParent as $key => $item_parent) {
                $item_parent->delete();
            }

            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', "Se ha eliminado correctamente la cuenta #" . $id);
        }catch(\Exception $e){
            if($e->getCode() == 23000){

                $model->status = ContabilidadCuenta::STATUS_INACTIVE;
                $model->update();

                Yii::$app->session->setFlash('success', "Se ha suspendiio correctamente la cuenta #" . $id);
            }
        }

        return $this->redirect(['index', 'tab' => 'index']);
    }


    public function actionSubcuenta($id)
    {
        $model = new ContabilidadCuenta();
        $cuenta = $this->findModel($id);
        $hijos = ContabilidadCuentaRel::find()->andWhere(["=","id_parent", $id ])->Count();

            if(!empty($hijos))
            {
                $numerador = $hijos;
            }else{
                $numerador = 0;
            }

            if($numerador < 10)
            {
                $subindice = $numerador;
                $coders = $subindice + 1;
                $codigo = "0".$coders;
            }else{
                $subindice = $numerador;
                $codigo = $subindice + 1;
            }

            $valid = true;
            while($valid){
                $queryValid =  ContabilidadCuenta::find()->andWhere(["code" => $cuenta->code.'.'.$codigo ])->count();

                if (  intval($queryValid)  == 0  ) {
                    $valid = false;
                    $cuenta->code = $cuenta->code.'.'.$codigo;
                }
                $codigo++;
            }

            if( $model->load(Yii::$app->request->post()) )
            {   
                $model->code = $cuenta->code;
                if($model->save()){
                    $Rel = new ContabilidadCuentaRel();
                    $Rel->id_parent = $id;
                    $Rel->id_child = $model->id;
                    $Rel->save();
                    return $this->redirect(['index']);
                }else {
                    return 'Datos no guardados, busca la respuesta.';
                }
            }
       return $this->render('subcuenta',[
            'model' => $model,
            'cuenta'=> $cuenta,
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
        public function actionCatalogosContaJsonBtt(){
            return ViewContabilidadCuenta::getJsonBtt(Yii::$app->request->get());
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

        protected function findModel($id, $_model = 'model')
        {
            switch ($_model) {
                case 'model':
                    $model = ContabilidadCuenta::findOne($id);
                    break;
                case 'view':
                    $model = ViewContabilidadCuenta::findOne($id);
                    break;
            }
            if ($model !== null)
                return $model;
            else
                throw new NotFoundHttpException('La página solicitada no existe.');
        }
}