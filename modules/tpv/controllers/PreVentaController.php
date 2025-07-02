<?php
namespace app\modules\tpv\controllers;

use Yii;
use yii\web\Response;
use app\models\venta\Venta;
use app\models\venta\ViewVenta;
use app\models\sucursal\Sucursal;
use app\models\producto\Producto;
use app\models\venta\VentaDetalle;
use app\models\producto\ViewProducto;
use app\models\inv\InvProductoSucursal;
use app\models\venta\TransVenta;

/**
 * Default controller for the `clientes` module
 */
class PreVentaController extends \app\controllers\AppController
{
    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'cancel' => Yii::$app->user->can('precapturaCancel'),
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

    public function actionCancel($id)
    {
        $model = $this->findModel($id);

        $model->status = Venta::STATUS_CANCEL;

        if ($model->save()) {

            Yii::$app->session->setFlash('success', "Se ha cancelado correctamente la PRE-CAPTURA #" . $id);
        }else{
            Yii::$app->session->setFlash('success', "Ocurrio un error al cancelar la PRE-CAPTURA #" . $id);
        }

        return $this->redirect(['index']);
    }

    public function actionIndexComandaAutorizacion()
    {
        return $this->render('index-comanda-autorizacion');
    }

    public function actionUpdatePreventa($preventa_id)
    {
        $model = $this->findModel($preventa_id);

        return $this->render('form-preventa',[
            "model" => $model
        ]);
    }

    public function actionGetDetailPreventaAlmacen()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('preventa_id')) {

                $response  = VentaDetalle::getDetailPorVerificar($request->get('preventa_id'));
                return [
                    "code"      => 202,
                    "detail"    => $response,
                ];
            }

            return [
                "code" => 10,
                "message" => "Error al buscar el producto, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionGetValidaInventarioAlmacen()
    {

        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('producto_id') && $request->get('sucursal_id')) {

                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $request->get('sucursal_id') ], [ "=", "producto_id", $request->get('producto_id') ] ] )->one();

                return [
                    "code"          => 202,
                    "disponible"    => isset($InvProducto->id) && $InvProducto->id ? $InvProducto->cantidad : 0,
                ];
            }

            return [
                "code" => 10,
                "message" => "Error al buscar el producto, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');

    }

    public function actionPostAutorizarPreventa()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->post('preventa_id') && $request->post('preventaDetailObject')) {

                $response  = VentaDetalle::postVerificarPreventa($request->post('preventa_id'), $request->post('preventaDetailObject'));
                return [
                    "code"      => 202,
                    "detail"    => $response,
                ];
            }

            return [
                "code" => 10,
                "message" => "Error al buscar el producto, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }


    public function actionProductoAjax($q = false)
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

            $user = ViewProducto::getProductoSeachAjax($text);
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

    public function actionGetComandaAbierta()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            return [
                "code"          => 202,
                "item_count"    => count(Venta::getPreventaProceso()),
            ];
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    //------------------------------------------------------------------------------------------------//
	// BootstrapTable list
	//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionVentasJsonBtt(){
        return ViewVenta::getJsonBtt(Yii::$app->request->get());
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
                $model = Venta::findOne($name);
                break;

            case 'view':
                $model = ViewVenta::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
