<?php
namespace app\modules\productos\controllers;

use app\models\compra\Compra;
use Yii;
use yii\web\Controller;
use app\models\UploadForm;
use yii\web\UploadedFile;
use app\models\producto\Producto;
use app\models\producto\ViewProducto;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Default controller for the `clientes` module
 */
class ProductoController extends \app\controllers\AppController
{

	private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create'    =>  Yii::$app->user->can('productoCreate'),
            'update'    =>  Yii::$app->user->can('productoUpdate'),
            'delete'    =>  Yii::$app->user->can('productoDelete'),
            'hideMonto' =>  Yii::$app->user->can('ENCARGADO CEDIS SIN MONTOS'),
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
        $model = new Producto();

        if ($model->load(Yii::$app->request->post())) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            if ($model->is_subproducto == Producto::TIPO_SUBPRODUCTO ){
                $model->tipo_medida     = Producto::MEDIDA_PZ;
                $model->inventariable   = Producto::INV_NO;
                $model->costo           = 0;
                $model->precio_menudeo  = 0;
                $model->precio_mayoreo  = 0;
            }

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

        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if($model->load(Yii::$app->request->post())){
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            if ($model->is_subproducto == Producto::TIPO_SUBPRODUCTO ){
                $model->tipo_medida     = Producto::MEDIDA_PZ;
                $model->inventariable   = Producto::INV_NO;
                $model->costo           = 0;
                $model->precio_menudeo  = 0;
                $model->precio_mayoreo  = 0;
            }
            if ($model->save())
                return $this->redirect(['view', 'id' => $model->id]);
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
            if($e->getCode() === 23000){
                Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación de la sucursal.');

                header("HTTP/1.0 400 Relation Restriction");
            }else{
                throw $e;
            }
        }

        return $this->redirect(['index']);
    }

    public static function actionGetPromedioCompra()
    {

        $request = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($request["productoID"]) && $request["productoID"] ) {
            return [
                "code"      => 202,
                "promedio"  => Compra::getPromedioCompra($request["productoID"])
            ];
        }

        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente.",
        ];

    }

    //------------------------------------------------------------------------------------------------//
    //                          FUNCTIONS POST
    //------------------------------------------------------------------------------------------------//
    public function actionValidarProducto($id)
    {
        $model = $this->findModel($id);
        $model->validate = Producto::VALIDATE_ON;
        $model->validate_user_by    = Yii::$app->user->identity->id;
        $model->validate_create_at  = time();
        $model->update();

        Yii::$app->session->setFlash('success', "Se valido correctamente el producto #" . $model->clave);

        return $this->redirect(['view', 'id' => $model->id]);
    }

    //------------------------------------------------------------------------------------------------//
    //                          ACTIONS AJAX
    //------------------------------------------------------------------------------------------------//
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


    //------------------------------------------------------------------------------------------------//
	// BootstrapTable list
	//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionProductosJsonBtt(){
        return ViewProducto::getJsonBtt(Yii::$app->request->get());
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
                $model = Producto::findOne($name);
                break;

            case 'view':
                $model = ViewProducto::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }


}
