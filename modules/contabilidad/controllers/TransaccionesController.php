<?php
namespace app\modules\contabilidad\controllers;

use Yii;
use yii\base\Model;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use app\models\contabilidad\ViewContabilidadTransaccion;
use app\models\contabilidad\ContabilidadCuenta;
use app\models\contabilidad\ContabilidadTransaccion;
use app\models\contabilidad\ContabilidadTransaccionDetail;

class TransaccionesController extends \app\controllers\AppController
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
    public function actionIndex()
    {
        return $this->render('index',[
            'can'=> $this->can,
        ]);
    }
    public function actionView($id) 
    {
        $model = ContabilidadTransaccion::find($id);
            return $this->render('view', [
                'model' => $this->findModel($id),
                'can'   => $this->can,
            ]);
    }
    
    public function actionUpdate($id)
    {
        $nombres = null;

        $model = ContabilidadTransaccionDetail::find()->Where(['=', 'contabilidad_transaccion_id', $id])->all();
        foreach ($model as $key => $componente) {
            $nombres[$key]['nombre'] = ContabilidadCuenta::getNombreCuentas($componente->contabilidad_cuenta_id);
            $nombres[$key]['data'] = $componente;
        }
        $request = Yii::$app->request->post();

        if($model !== null && $request == null && $nombres != null)
        {
            return $this->render('update',[
                'model' => $model,
                'cuentas'=>$nombres
            ]);
        }
        else
        {
            ContabilidadTransaccionDetail::deleteAll(['contabilidad_transaccion_id' => $request['parent_id']]);


            foreach ($request['cuentas'] as $key => $item_cuenta) 
            {
                $model= new ContabilidadTransaccionDetail();


                $model->contabilidad_transaccion_id = $request['parent_id'];
                $model->tipo_poliza = $request['poliza_type'];
                $model->apply_afectable = ContabilidadTransaccionDetail::APPLY_AFECTABLE_SI;
                $model->contabilidad_cuenta_id = $request['id_cuenta'][$key];      

                    if($request['cargos'][$key] == 0)
                    {
                        $model->cargo = 0; 
                    }
                    else
                    {
                        $model->cargo = $request['cargos'][$key]; 
                    }
                    if($request['abonos'][$key] == 0)
                    {
                        $model->abono = 0; 
                    }
                    else
                    {
                        $model->abono = $request['abonos'][$key]; 
                    } 
                


                $model->save();   


            }                


        Yii::$app->response->format = Response::FORMAT_JSON;    


            return [
                'code'    => 202,
                'message' => 'operacion exitosa'
            ];
   
        }


    }
    
    public function actionDetail($id)
    {
      
       $asientos_trans = ContabilidadTransaccionDetail::find()->Where(["=","contabilidad_transaccion_id", $id ])->all();

        if(!empty($asientos_trans)){
            foreach ($asientos_trans as $key => $asiento){
                $cuentas = ContabilidadCuenta::find()->where(["=","id",$asiento->contabilidad_cuenta_id])->all();
                $valor_trans = array('cargo'=>$asiento->cargo ,'abono'=>$asiento->abono);
                $des_cuentas['asientos'][$key] = array_merge($cuentas,$valor_trans); 
            }   

            $des_cuentas['transaccion_cuenta'] = $id;

            return $this->render('detail',[
                'des_cuentas' => $des_cuentas,
                'model'=> $this->findModel($id),
            ]);
        }

        Yii::$app->session->setFlash('danger', 'No existe configuracion, intenta nuevamente');
        return $this->redirect(['index']);
    }

    public function actionCuentasAjax()
    {
        $request = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $buscar = $request['busqueda'];
        if($buscar){
            $cuentas = ContabilidadCuenta::getClaves($buscar);
            return $cuentas;
        }
    }

    public  function actionAsientosContables()
    {
        $request = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $transaccion_id = null;


        foreach ($request['cuentas'] as $key => $asiento){
            $model = new ContabilidadTransaccionDetail();
            $id_cuenta = $request['id_cuenta'][$key];
            $abono = $request['abonos'][$key];
            $cargo = $request['cargos'][$key];

            $model->tipo_poliza = $request['poliza_type'];
            $model->contabilidad_transaccion_id = $request['parent_id'];
            $model->contabilidad_cuenta_id = $id_cuenta;
            $model->cargo = $cargo;
            $model->abono = $abono;

            $model->save();
            $transaccion_id = $request['parent_id'];
        }

         $ContabilidadTransaccion = ContabilidadTransaccion::findOne($transaccion_id);
         $ContabilidadTransaccion->status = ContabilidadTransaccion::STATUS_CONFIGURADO;
         $ContabilidadTransaccion->update();

        return [
            'code'    => 202,
            'message' => 'operacion exitosa'
        ];
    }

    //------------------------------------------------------------------------------------------------//
    // BootstrapTable list
    //------------------------------------------------------------------------------------------------//
        /**
         * Return JSON bootstrap-table
         * @param  array $_GET
         * @return json
         */
        public function actionTransaccionesContaJsonBtt()
        {
            return ViewContabilidadTransaccion::getJsonBtt(Yii::$app->request->get());
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
                    $model = ContabilidadTransaccion::findOne($id);
                    break;
                case 'view':
                    $model = ViewContabilidadTransaccion::findOne($id);
                    break;
            }
            if ($model !== null)
                return $model;
            else
                throw new NotFoundHttpException('La página solicitada no existe.');
        }
} 