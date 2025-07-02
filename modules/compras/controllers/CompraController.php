<?php
namespace app\modules\compras\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\web\Controller;
use app\models\compra\Compra;
use app\models\compra\ViewCompra;
use app\models\cobro\CobroVenta;
use app\models\credito\Credito;
use yii\web\Response;

/**
 * Default controller for the `clientes` module
 */
class CompraController extends \app\controllers\AppController
{

	private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('compraCreate'),
            'cancel' => Yii::$app->user->can('compraCancel'),
            'hideMonto' =>  Yii::$app->user->can('ENCARGADO CEDIS SIN MONTOS'),
        ];
    }

    public function actionViewModal($id)
    {
        $model = Compra::findOne($id);
        if($model->entrada){
            $entrada=$model->entrada->operacionDetalles;
            $entradaarray=array();
            foreach($entrada as $entradas){
                $nuevoRegistro = [
                    'id' => $entradas->id,
                    'producto_id' => $entradas->producto->nombre,
                    'cantidad' => $entradas->cantidad,
                    'costo' => $entradas->costo,
                    'total' => number_format($entradas->cantidad*$entradas->costo,2),
                ];
                $entradaarray[] = $nuevoRegistro;
            }
        }
        else{
            $entrada="";
        }

        if($model->compraDetalles){
            $compradetalles=$model->compraDetalles;
            $comprasdetallesarray=array();
            foreach($compradetalles as $compradetalle){
                $nuevoRegistro = [
                    'id' => $compradetalle->id,
                    'producto_id' => $compradetalle->producto->nombre,
                    'cantidad' => $compradetalle->cantidad,
                    'costo' => $compradetalle->costo,
                    'total' => number_format($compradetalle->cantidad*$compradetalle->costo,2),
                ];
                $comprasdetallesarray[] = $nuevoRegistro;
            }
        }
        else{
            $comprasdetallesarray[]="";
        }

        if($model->updatedBy)
            $nombre=$model->updatedBy->nombre;
        else
            $nombre="";
        $data = [
            'code'=>202,
            'compras' => $model,
            'entrada' => $entradaarray,
            'compradetalles' => $comprasdetallesarray,
            'nombre' =>$nombre,
        ];
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $data;
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

        $model->tipo = Sucursal::TIPO_SUCURSAL;


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

    public function actionSaveConfirmacion()
    {
        $request    = Yii::$app->request->post();
        $total      = 0;
        if ($request) {
            if ($request["input_cobro_efectivo"]) {

                $CobroVenta = CobroVenta::find()->andWhere(["and" ,["=","compra_id", $request["compra_id"] ], ["=","metodo_pago", CobroVenta::COBRO_EFECTIVO ] ])->one();
                if (!isset($CobroVenta->id)) {
                    $CobroVenta  =  new CobroVenta();
                }

                $CobroVenta->compra_id      = $request["compra_id"];
                $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                $CobroVenta->tipo_cobro_pago= CobroVenta::PERTENECE_PAGO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_EFECTIVO;
                $CobroVenta->cantidad       = $request["input_cobro_efectivo"];
                $CobroVenta->save();


                $total = $total + $request["input_cobro_efectivo"];

            }

            if ($request["input_cobro_cheque"]) {

                $CobroVenta = CobroVenta::find()->andWhere(["and" ,["=","compra_id", $request["compra_id"] ], ["=","metodo_pago", CobroVenta::COBRO_CHEQUE ] ])->one();
                if (!isset($CobroVenta->id)) {
                    $CobroVenta  =  new CobroVenta();
                }
                $CobroVenta->compra_id      = $request["compra_id"];
                $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                $CobroVenta->tipo_cobro_pago= CobroVenta::PERTENECE_PAGO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_CHEQUE;
                $CobroVenta->cantidad       = $request["input_cobro_cheque"];
                $CobroVenta->save();
                $total = $total + $request["input_cobro_cheque"];
            }

            if ($request["input_cobro_tranferencia"]) {

                $CobroVenta = CobroVenta::find()->andWhere(["and" ,["=","compra_id", $request["compra_id"] ], ["=","metodo_pago", CobroVenta::COBRO_TRANFERENCIA ] ])->one();
                if (!isset($CobroVenta->id)) {
                    $CobroVenta  =  new CobroVenta();
                }
                $CobroVenta->compra_id      = $request["compra_id"];
                $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                $CobroVenta->tipo_cobro_pago= CobroVenta::PERTENECE_PAGO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_TRANFERENCIA;
                $CobroVenta->cantidad       = $request["input_cobro_tranferencia"];
                $CobroVenta->save();

                $total = $total + $request["input_cobro_tranferencia"];
            }

            if ($request["input_cobro_tarjeta_credito"]) {

                $CobroVenta = CobroVenta::find()->andWhere(["and" ,["=","compra_id", $request["compra_id"] ], ["=","metodo_pago", CobroVenta::COBRO_TARJETA_CREDITO ] ])->one();
                if (!isset($CobroVenta->id)) {
                    $CobroVenta  =  new CobroVenta();
                }
                $CobroVenta->compra_id      = $request["compra_id"];
                $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                $CobroVenta->tipo_cobro_pago= CobroVenta::PERTENECE_PAGO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_TARJETA_CREDITO;
                $CobroVenta->cantidad       = $request["input_cobro_tarjeta_credito"];
                $CobroVenta->save();

                $total = $total + $request["input_cobro_tarjeta_credito"];
            }

            if ($request["input_cobro_tarjeta_debito"]) {

                $CobroVenta = CobroVenta::find()->andWhere(["and" ,["=","compra_id", $request["compra_id"] ], ["=","metodo_pago", CobroVenta::COBRO_TARJETA_DEBITO ] ])->one();
                if (!isset($CobroVenta->id)) {
                    $CobroVenta  =  new CobroVenta();
                }
                $CobroVenta->compra_id      = $request["compra_id"];
                $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                $CobroVenta->tipo_cobro_pago= CobroVenta::PERTENECE_PAGO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_TARJETA_DEBITO;
                $CobroVenta->cantidad       = $request["input_cobro_tarjeta_debito"];
                $CobroVenta->save();

                $total = $total + $request["input_cobro_tarjeta_debito"];
            }

            if ($request["input_cobro_deposito"]) {

                $CobroVenta = CobroVenta::find()->andWhere(["and" ,["=","compra_id", $request["compra_id"] ], ["=","metodo_pago", CobroVenta::COBRO_DEPOSITO ] ])->one();
                if (!isset($CobroVenta->id)) {
                    $CobroVenta  =  new CobroVenta();
                }
                $CobroVenta->compra_id      = $request["compra_id"];
                $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                $CobroVenta->tipo_cobro_pago= CobroVenta::PERTENECE_PAGO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_DEPOSITO;
                $CobroVenta->cantidad       = $request["input_cobro_deposito"];
                $CobroVenta->save();
                $total = $total + $request["input_cobro_deposito"];
            }

            if ($request["input_cobro_credito"]) {

                $CobroVenta = CobroVenta::find()->andWhere(["and" ,["=","compra_id", $request["compra_id"] ], ["=","metodo_pago", CobroVenta::COBRO_CREDITO ] ])->one();
                if (!isset($CobroVenta->id)) {
                    $CobroVenta  =  new CobroVenta();
                }
                $CobroVenta->compra_id      = $request["compra_id"];
                $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                $CobroVenta->tipo_cobro_pago= CobroVenta::PERTENECE_PAGO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_CREDITO;
                $CobroVenta->cantidad       = $request["input_cobro_credito"];
                $CobroVenta->save();

                $Credito = Credito::findOne([ "compra_id" => $request["compra_id"] ]);
                if (!isset($Credito->id)) {
                    $Credito = new  Credito();
                }

                $Credito->compra_id  = $request["compra_id"];
                $Credito->monto      = $request["input_cobro_credito"];
                $Credito->tipo       = Credito::TIPO_PROVEEDOR;
                //$Credito->created_by = $user->id;
                $Credito->save();


                $total = $total + $request["input_cobro_credito"];

            }

            $compra = Compra::findOne($request["compra_id"]);
            /*if ( $total >= $compra->total   )
                $compra->status = Compra::STATUS_PAGADA;
            else
                $compra->status = Compra::STATUS_PORPAGAR;
            */
            $compra->is_confirmacion = Compra::IS_CONFIRMACION_ON;

            $compra->save();
        }


        return $this->redirect(['view',
            'id' => $request["compra_id"]
        ]);
    }

    public function actionCancel($id)
    {
        $model = $this->findModel($id);
        if ($model) {
            $model->status = Compra::STATUS_CANCEL;
            foreach ($model->pagoCompra as $key => $item_pago) {

                if ($item_pago->metodo_pago == CobroVenta::COBRO_CREDITO) {
                    $credito = Credito::find()->andWhere(["compra_id" => $item_pago->compra_id ])->one();
                    if ($credito) {
                        $credito->status = Credito::STATUS_CANCEL;
                        $credito->update();
                    }
                }

                $item_pago->is_cancel = CobroVenta::IS_CANCEL_ON;
                $item_pago->update();
            }

            if ($model->save()){
                Yii::$app->session->setFlash('success', "SE GENERO LA CANCELACION CON EXITO");
                return $this->redirect(['view','id' => $model->id ]);
            }
        }
        Yii::$app->session->setFlash('danger', "OCURRIO UN ERROR, VERIFICA TU INFORMACIÓN");

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
    public function actionComprasJsonBtt(){
        return ViewCompra::getJsonBtt(Yii::$app->request->get());
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
                $model = Compra::findOne($name);
                break;

            case 'view':
                $model = ViewCompra::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
