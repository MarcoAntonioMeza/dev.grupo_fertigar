<?php
namespace app\modules\crm\controllers;

use Yii;
use yii\base\Model;
use yii\helpers\Html;
use yii\base\InvalidParamException;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\Esys;
use app\models\cliente\Cliente;
use app\models\cliente\ViewCliente;
use app\models\sucursal\Sucursal;
use app\models\sucursal\ViewSucursal;
use app\models\esys\EsysDireccion;
use app\models\credito\Credito;
use app\models\credito\CreditoAbono;
use app\models\credito\ViewCredito;
use app\models\venta\VentaTokenPay;


/**
 * ClienteController implements the CRUD actions for Cliente model.
 */
class ClienteController extends \app\controllers\AppController
{
    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('clienteCreate'),
            'update' => Yii::$app->user->can('clienteUpdate'),
            'delete' => Yii::$app->user->can('clienteDelete'),
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
     * Displays a single Cliente model.
     *
     * @param  integer $id The cliente id. * @return string
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

    public function actionHistoricoSucursalView($id)
    {
        return $this->render('historico-sucursal-view', [
            'model' => Sucursal::findOne($id),
            'can'   => $this->can,
        ]);
    }

    /**
     * Creates a new Cliente model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model      = new Cliente();
        $clientes   = null;

        $model->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_CLIENTE,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->dir_obj->load(Yii::$app->request->post()) ) {
            if ($model->validate()) {
                if($model->save())
                    return $this->redirect(['view', 'id' => $model->id]);
            }

        }
        return $this->render('create', [
            'model'     => $model,
            'clientes'  => $clientes,
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

        $model->dir_obj   = $model->direccion;

        $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;

        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if($model->load(Yii::$app->request->post()) && $model->dir_obj->load(Yii::$app->request->post()) ){
            if($model->save())
                return $this->redirect(['view', 'id' => $model->id]);
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


    public function actionGetHistoryOperacion()
    {
        $request = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($request["credito_id"]) && $request["credito_id"] ) {
            $credito = Credito::findOne($request["credito_id"]);
            $response = [];

            if (round(floatval($credito->monto_pagado),2) != round(floatval(CreditoAbono::getSumaAbono($credito->id)),2)) {
                array_push($response,[
                    "id"         => 0,
                    "cantidad"   => round(floatval($credito->monto_pagado) -  floatval(CreditoAbono::getSumaAbono($credito->id))),
                    "token_pay"  => "----",
                    "status_text"=> "AJUSTE - CREDITO",
                    "status"     => 0,
                    "created_at" => "----",
                    "empleado"   => "----",
                ]);
            }

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


    public function actionGetTokenVentas()
    {
        $request = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($request["credito_id"]) && $request["credito_id"] ) {
            $credito    = Credito::findOne($request["credito_id"]);
            $ventaToken = VentaTokenPay::find()->andWhere([ "token_pay" => $credito->trans_token_venta ])->all();
            $response = [];
            foreach ($ventaToken as $key => $item_token) {
                //$total_deuda     = floatval($item_credito->monto) - floatval(CobroVenta::getPagoCredito($item_credito->id));
                array_push($response,[
                    "id"            => $item_token->venta->id,
                    "folio"         => str_pad($item_token->venta->id,6,"0",STR_PAD_LEFT),
                    "total"         => $item_token->venta->total,
                    "sucursal"      => isset($item_token->venta->sucursal->nombre) ? $item_token->venta->sucursal->nombre : null,
                    "created_at"    => date("Y-m-d h:i:s",$item_token->created_at),
                    "empleado"      => $item_token->createdBy->nombreCompleto,
                ]);
            }
            return [
                "code" => 202,
                "ventas" => $response,
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
    public function actionClientesJsonBtt(){
        return ViewCliente::getJsonBtt(Yii::$app->request->get());
    }

    public function actionVentasClienteJsonBtt(){
        return ViewCliente::getVentasJsonBtt(Yii::$app->request->get());
    }

    public function actionPagosDetailJsonBtt()
    {
        return ViewCredito::getPagosJsonBtt(Yii::$app->request->get());
    }

    public function actionClienteAjax($q = false, $cliente_id = false)
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($q) {
                $text = $q;

            } else {
                $text = Yii::$app->request->get('data');
                $text = $text['q'];
            }

            if (is_null($text) && $cliente_id)
                $user = ViewCliente::getClienteAjax($cliente_id,true);
            else
                $user = ViewCliente::getClienteAjax($text,false);
            // Obtenemos user


            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $user;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['q' => $text, 'results' => $user];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
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
                $model = Cliente::findOne($id);
                break;

            case 'view':
                $model = ViewCliente::findOne($id);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }

}
