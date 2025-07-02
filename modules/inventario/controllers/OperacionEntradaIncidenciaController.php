<?php
namespace app\modules\inventario\controllers;

use Yii;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\inv\Operacion;
use app\models\inv\TraspasoOperacion;
use app\models\inv\ViewTraspasoOperacion;
use app\models\inv\InvProductoSucursal;
use app\models\trans\TransProductoInventario;

class OperacionEntradaIncidenciaController extends \app\controllers\AppController
{

    public function actionIndex()
    {
        return $this->render('index');
    }
    
    public function actionGetOperacionIncidencia()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('operacion_id')) {

                $Operacion  = TraspasoOperacion::findOne(trim($request->get('operacion_id')));
                $responseArray = [
                    "id"               => $Operacion->id,
                    "folio_ingreso"    => "#".$Operacion->operacion_recibe_id,                    
                    "operador"         => $Operacion->operador->nombreCompleto,
                    "producto"         => $Operacion->producto->nombre,
                    "cantidad_old"     => $Operacion->cantidad_old,
                    "cantidad_new"     => $Operacion->cantidad_new,
                    "created_at"       => date("Y-m-d h:i a", $Operacion->created_at),
                    
                ];               

                return [
                    "code" => 202,
                    "operacion" => $responseArray,
                ];
            }

            return [
                "code" => 10,
                "message" => "Error al buscar la venta, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }
    
    public function actionPostOperacionOmitir()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->post('operacion_id')) {

                $Operacion  = TraspasoOperacion::findOne(trim($request->post('operacion_id')));
                $Operacion->status = TraspasoOperacion::STATUS_CERRADO;
                if($Operacion->save()){
                    return [
                        "code" => 202,
                        "message" => "Se realizo correctamente la operacion",
                    ];
                }else{
                    return [
                        "code" => 10,
                        "message" => "Ocurrio un error, intenta nuevamente",
                    ];
                }               
            }

            return [
                "code" => 10,
                "message" => "Error al buscar la venta, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }


    public function actionPostOperacionGuardar()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            $operacion_id   = $request->post('operacion_id');
            $sucursal_id    = $request->post('sucursal_id');
            $tipo           = $request->post('tipo');
            $cantidad       = $request->post('cantidad');

            if ($operacion_id && $sucursal_id && $tipo && $cantidad ) {

                $Operacion  = TraspasoOperacion::findOne(trim($request->post('operacion_id')));
                $Operacion->status = TraspasoOperacion::STATUS_CERRADO;

                $InvProducto = InvProductoSucursal::find()->andWhere(["and", [ "=","sucursal_id", $sucursal_id ], [ "=", "producto_id", $Operacion->producto_id ] ] )->one();

                if (isset($InvProducto->id)) {

                    if ($tipo == TransProductoInventario::TIPO_SALIDA) {                        
                        $InvProducto->cantidad  =$InvProducto->cantidad -  $cantidad;
                        TransProductoInventario::saveTransAjuste($sucursal_id,null,$Operacion->producto_id, $cantidad ,TransProductoInventario::TIPO_SALIDA);
                    }

                    if ($tipo == TransProductoInventario::TIPO_ENTRADA) {
                        $InvProducto->cantidad  =$InvProducto->cantidad +  $cantidad;
                        TransProductoInventario::saveTransAjuste($sucursal_id,null,$Operacion->producto_id, $cantidad ,TransProductoInventario::TIPO_ENTRADA);
                    }                  
                    $InvProducto->save();                 
                }

                if($Operacion->save()){
                    return [
                        "code" => 202,
                        "message" => "Se realizo correctamente la operacion",
                    ];
                }else{
                    return [
                        "code" => 10,
                        "message" => "Ocurrio un error, intenta nuevamente",
                    ];
                }               
            }

            return [
                "code" => 10,
                "message" => "Error al buscar la venta, intenta nuevamente",
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
    public function actionOperacionEntradaIncidenciaJsonBtt(){
        return ViewTraspasoOperacion::getJsonBtt(Yii::$app->request->get());
    }
}
