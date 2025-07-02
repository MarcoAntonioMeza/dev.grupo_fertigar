<?php
namespace app\modules\crm\controllers;

use Yii;
use yii\base\Model;
use yii\web\Response;
use yii\helpers\Html;
use yii\base\InvalidParamException;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\proveedor\Proveedor;
use app\models\proveedor\ViewProveedor;
use app\models\esys\EsysDireccion;
use app\models\credito\Credito;
use app\models\credito\CreditoAbono;
use app\models\credito\ViewCredito;

/**
 * ClienteController implements the CRUD actions for Cliente model.
 */
class ProveedorController extends \app\controllers\AppController
{
    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('proveedorCreate'),
            'update' => Yii::$app->user->can('proveedorUpdate'),
            'delete' => Yii::$app->user->can('proveedorDelete'),
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
     * Displays a single 'Proveedor' model.
     *
     * @param  integer $id The 'Proveedor' id. * @return string
     *
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'can'   => $this->can,
        ]);
    }


    /**
     * Creates a new 'Proveedor' model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model      = new Proveedor();

        $model->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_PROVEEDOR,
            //'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);


        if ($model->load(Yii::$app->request->post())) {
            $nameFile =  Yii::$app->user->identity->id  ."_". Yii::$app->security->generateRandomString();
            $model->avatar_file = UploadedFile::getInstance($model, 'avatar_file');

            if ($model->avatar_file) {
                $model->avatar =  $nameFile .".". $model->avatar_file->extension;
                $model->upload($nameFile);
            }

            if($model->save()){
                $model->saveDireccion($model->id);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model'     => $model,
        ]);
    }



    /**
     * Updates an existing Cliente and Role models.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param  integer $id The cliente id.
     * @return string|\yii\web\Response
     *
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model      = $this->findModel($id);

        $model->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_PROVEEDOR,
           // 'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);


        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if($model->load(Yii::$app->request->post())){

            $nameFile       = Yii::$app->security->generateRandomString();

            $nameFileOld    = $model->avatar;

            $model->avatar_file = UploadedFile::getInstance($model, 'avatar_file');

            if ($model->avatar_file) {
                $model->avatar =  $nameFile .".". $model->avatar_file->extension ;
                $model->upload($nameFile);
                if ($nameFileOld)
                    $model->removeFileOld($nameFileOld);

            }

            if($model->save()){
                $model->saveDireccion($model->id);
                return $this->redirect(['view', 'id' => $model->id]);
            }

        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    /**
     * Deletes an existing Cliente model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param  integer $id The cliente id.
     * @return \yii\web\Response
     *
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try{
            // Eliminamos el cliente
            $this->findModel($id)->delete();

            Yii::$app->session->setFlash('success', "Se ha eliminado correctamente al cliente #" . $id);

        }catch(\Exception $e){
            if($e->getCode() == 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación del cliente.');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }

        return $this->redirect(['index', 'tab' => 'index']);
    }

    public function actionHistorialCambios($id)
    {
        $model = $this->findModel($id);

        return $this->render("historial-cambios", [
            'model' => $model,
        ]);
    }
    public function actionGetCompraVenta($token_pay){
        $cobros = CreditoAbono::findOne(['token_pay' => $token_pay]);
        $credito = Credito::findOne(['id' => $cobros->credito_id]);

        return $credito->id;
    }
    public function actionGetDireccionAjax()
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            $proveedor_id = Yii::$app->request->get('proveedor_id');
            if ($proveedor_id) {
                $direccionArray = ViewProveedor::getDireccionAjax($proveedor_id);
                return [
                    "code" => 202,
                    "direccion" => $direccionArray
                ];
            }

            return [
                "code" => 10,
                "message" => "Ocurrio un error, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionGetHistoryOperacion()
    {
        $request = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($request["credito_id"]) && $request["credito_id"] ) {
            $credito = Credito::findOne($request["credito_id"]);
            $response = [];
            foreach ($credito->abono as $key => $item_transaccion) {
                //$total_deuda     = floatval($item_credito->monto) - floatval(CobroVenta::getPagoCredito($item_credito->id));
                array_push($response,[
                    "id"         => $item_transaccion->id,
                    "cantidad"   => $item_transaccion->cantidad,
                    "token_pay"  => $item_transaccion->token_pay,
                    "status_text"=> CreditoAbono::$statusList[$item_transaccion->status],
                    "status"     => $item_transaccion->status,
                    "created_at" => date("Y-m-d h:i:s",$item_transaccion->created_at),
                    "empleado"   => $item_transaccion->createdBy->nombreCompleto ,
                ]);
            }
            return [
                "code" => 202,
                "transaccion" => $response,
            ];
        }

        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente.",
        ];

    }


    public function actionGetHistoryPago()
    {
        $request = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($request["opera_token_pay"]) && $request["opera_token_pay"] ) {
            $CreditoAbono = CreditoAbono::find()->andWhere([ "token_pay" => $request["opera_token_pay"] ])->all();
            $response = [];
            foreach ($CreditoAbono as $key => $item_transaccion) {
                array_push($response,[
                    "id"         => $item_transaccion->id,
                    "credito_id" => $item_transaccion->credito_id,
                    "cantidad"   => $item_transaccion->cantidad,
                    "token_pay"  => $item_transaccion->token_pay,
                    "status_text"=> CreditoAbono::$statusList[$item_transaccion->status],
                    "status"     => $item_transaccion->status,
                    "created_at" => date("Y-m-d h:i:s",$item_transaccion->created_at),
                    "empleado"   => $item_transaccion->createdBy->nombreCompleto ,
                ]);
            }
            return [
                "code" => 202,
                "transaccion" => $response,
            ];
        }

        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente.",
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
    public function actionProveedoresJsonBtt(){
        return ViewProveedor::getJsonBtt(Yii::$app->request->get());
    }

    public function actionComprasProveedorJsonBtt(){
        return ViewProveedor::getComprasJsonBtt(Yii::$app->request->get());
    }

    public function actionPagosDetailJsonBtt()
    {
        return ViewCredito::getPagosJsonBtt(Yii::$app->request->get());
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
                $model = Proveedor::findOne($id);
                break;

            case 'view':
                $model = ViewProveedor::findOne($id);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }

}
