<?php
namespace app\modules\admin\controllers;

use Yii;
use yii\base\Model;
use yii\helpers\Html;
use yii\web\Response;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\Esys;
use app\models\user\User;
use app\models\user\ViewUser;
use app\models\user\SignupForm;
use app\models\user\LoginForm;
use app\models\user\AccountActivation;
use app\models\user\ResetPasswordForm;
use app\models\user\PasswordResetRequestForm;
use app\models\user\ChangePassword;
use app\models\user\UserAsignarPerfil;
use app\models\esys\EsysDireccion;
use app\models\esys\EsysAcceso;
use app\models\producto\ViewProducto;
use app\models\cliente\ViewCliente;


/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends \app\controllers\AppController
{
    private $can;

    public function init()
    {
        parent::init();
        $this->can = [
            'create' => Yii::$app->user->can('userCreate'),
            'update' => Yii::$app->user->can('userUpdate'),
            'delete' => Yii::$app->user->can('userDelete'),
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
     * Displays a single User model.
     *
     * @param  integer $id The user id.
     * @return string
     *
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $this->can['update'] = $this->can['update'] && $id != 1 || Yii::$app->user->can('theCreator');
        $this->can['delete'] = $this->can['delete'] && $id != 1;

        return $this->render('view', [
            'model' => $this->findModel($id),
            'can'   => $this->can,
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $user = new User(['scenario' => User::SCENARIO_CREATE]);

        $user->dir_obj = new EsysDireccion([
            'cuenta' => EsysDireccion::CUENTA_USUARIO,
            'tipo'   => EsysDireccion::TIPO_PERSONAL,
        ]);

        if ($user->load(Yii::$app->request->post()) && $user->dir_obj->load(Yii::$app->request->post())) {
            if ($user->validate()) {
                // Ajuste datos user
                $user->setPassword($user->password);
                $user->generateAuthKey();

                $user->token = Yii::$app->security->generateRandomString();

                // Guardar user
                if($user->save())
                    return $this->redirect(['view', 'id' => $user->id]);
            }

        } else {
            // Cargamos perfiles por default
            $user->setPerfilesAsignarNames();
        }

        return $this->render('create', [
            'user' => $user,
        ]);
    }


    /**
     * Updates an existing User and Role models.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param  integer $id The user id.
     * @return string|\yii\web\Response
     *
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        if($id == 1 && !Yii::$app->user->can('theCreator'))
            return $this->redirect(['view', 'id' => $id]);

        $user = $this->findModel($id);

        // Verificamos el perfil del usuario a editar le sea permitido
        if (!Yii::$app->user->can('admin') && !in_array($user->getRoleName(), UserAsignarPerfil::getItems(['with@uthenticated' => true]))) {
            throw new \yii\web\NotFoundHttpException('El perfil de este usuario no te permite editarlo.');

            return $this->render('@app/views/site/error', [
                'exception' => Yii::$app->errorHandler->exception,
            ]);
        }


        // Cargamos datos de dirección
        $user->dir_obj   = $user->direccion;
        $user->dir_obj->codigo_search   = isset($user->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $user->direccion->esysDireccionCodigoPostal->codigo_postal : null;

        $user->fecha_nac = Esys::unixTimeToString($user->fecha_nac);

        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if($user->load(Yii::$app->request->post()) && $user->dir_obj->load(Yii::$app->request->post())){
            $user->scenario = User::SCENARIO_UPDATE;

            if(Model::validateMultiple([$user, $user->dir_obj])){
                // only if user entered new password we want to hash and save it

                if($user->password)
                    $user->setPassword($user->password);

                if (!$user->token)
                    $user->token = Yii::$app->security->generateRandomString();


                // if admin is activating user manually we want to remove account activation token
                if($user->status == User::STATUS_ACTIVE && $user->account_activation_token != null)
                    $user->removeAccountActivationToken();

                // Guardar Usuario, Perfiles y dirección
                $user->save();


                return $this->redirect(['view', 'id' => $user->id]);
            }


            return $this->render('update', [
                'user' => $user,
            ]);
        }

        $user->item_name = $user->roleName;
        $user->setPerfilesAsignarNames();


        return $this->render('update', [
            'user' => $user,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param  integer $id The user id.
     * @return \yii\web\Response
     *
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $user = $this->findModel($id);
 
        $user->status = User::STATUS_DELETED;

        if($user->save()){
            Yii::$app->session->setFlash('success', "Se ha eliminado correctamente al usuario #" . $id);
        }else{
            Yii::$app->session->setFlash('warning', "Ocurrio un error, intenta nuevamente");
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

    public function actionLoginUser($id){

        $model = new LoginForm(['scenario' => Yii::$app->params['lw']]);
        $user  = User::findOne($id);
        if (isset($user->id)) {
            $model->username = $user->username;
            $model->email    = $user->email;
            // posting data or login has failed
            if (!$model->login(true, $user)) {
                Yii::$app->session->setFlash('danger', 'Ocurrio un error al iniciar sesion, intenta nuevamente.');
                return $this->redirect(['view', 'id' => $id]);
            }else{
                Yii::$app->session->setFlash('success', 'Se inicio correctamente la session');
                return $this->goBack();
            }
        }
        Yii::$app->session->setFlash('danger', 'Ocurrio un error, el usuario no existe intenta nuevamente.');
        return $this->redirect(['index']);
    }
//------------------------------------------------------------------------------------------------//
// SIGN UP / ACCOUNT ACTIVATION
//------------------------------------------------------------------------------------------------//
    /**
     * Signs up the user.
     * If user need to activate his account via email, we will display him
     * message with instructions and send him account activation email with link containing account activation token.
     * If activation is not necessary, we will log him in right after sign up process is complete.
     * NOTE: You can decide whether or not activation is necessary, @see config/params.php
     *
     * @return string|\yii\web\Response
     */
    public function actionSignup()
    {
        // get setting value for 'Registration Needs Activation'
        $rna = Yii::$app->params['rna'];

        // if 'rna' value is 'true', we instantiate SignupForm in 'rna' scenario
        $model = $rna ? new SignupForm(['scenario' => 'rna']) : new SignupForm();

        // if validation didn't pass, reload the form to show errors
        if (!$model->load(Yii::$app->request->post()) || !$model->validate()) {
            return $this->renderPartial('signup', ['model' => $model]);
        }

        // try to save user data in database, if successful, the user object will be returned
        $user = $model->signup();

        if (!$user) {
            // display error message to user
            Yii::$app->session->setFlash('error', "No pudimos registrarte, contáctenos.");
            return $this->refresh();
        }

        // user is saved but activation is needed, use signupWithActivation()
        if ($user->status === User::STATUS_INACTIVE) {
            $this->signupWithActivation($model, $user);

            return $this->refresh();
        }

        // now we will try to log user in
        // if login fails we will display error message, else just redirect to home page

        if (!Yii::$app->user->login($user)) {
            // display error message to user
            Yii::$app->session->setFlash('warning', "Intenta iniciar sesión.");

            // log this error, so we can debug possible problem easier.
            Yii::error("¡El inicio de sesión después del registro a fallado! Usuario " . Html::encode($user->username) . ' no pudimos iniciar la cuenta.');
        }

        return $this->goHome();
    }

    /**
     * Tries to send account activation email.
     *
     * @param $model
     * @param $user
     */
    private function signupWithActivation($model, $user)
    {
        // sending email has failed
        if (!$model->sendAccountActivationEmail($user)) {
            // display error message to user
            Yii::$app->session->setFlash('error', "No pudimos enviarte el correos electrónicos de activación de la cuenta, contáctenos.");

            // log this error, so we can debug possible problem easier.
            Yii::error('¡El registro a fallado! Usuario ' . Html::encode($user->username) . ' no pudo registrarse. Causas posibles: el correo electrónico de verificación no pudo ser enviado.');
        }

        // everything is OK
        Yii::$app->session->setFlash('success', "Hola " . Html::encode($user->username) . '. Para poder iniciar sesión, debes confirmar tu registro. Por favor revisa tu correo electrónico, te hemos enviado un mensaje.');
    }

    /**
     * Activates the user account so he can log in into system.
     *
     * @param  string $token
     * @return \yii\web\Response
     *
     * @throws BadRequestHttpException
     */
    public function actionActivateAccount($token)
    {
        try {
            $user = new AccountActivation($token);

        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if (!$user->activateAccount()) {
            Yii::$app->session->setFlash('error', Html::encode($user->username) . " tu cuenta no pudo ser activada, contáctenos!");

            return $this->goHome();
        }

        Yii::$app->session->setFlash('success', "¡Excelente! Ahora puede iniciar sesión. Gracias " . Html::encode($user->username) . " por unirte a nosotros!");

        return $this->redirect('login');
    }


//------------------------------------------------------------------------------------------------//
// LOG IN / LOG OUT / PASSWORD RESET / MI PERFIL
//------------------------------------------------------------------------------------------------//
    /**
     * Logs in the user if his account is activated,
     * if not, displays appropriate message.
     *
     * @return string|\yii\web\Response
     */
    public function actionLogin()
    {
        // user is logged in, he doesn't need to login
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm(['scenario' => Yii::$app->params['lw']]);

        // monitor login status
        $successfulLogin = true;

        // posting data or login has failed

        if (!$model->load(Yii::$app->request->post()) || !$model->login()) {
            $successfulLogin = false;
        }

        // Guardamos registro de accesos
        if(Yii::$app->request->post()){
            $EsysAcceso = new EsysAcceso([
                'user'           => $model->username,
                'wrong_password' => $successfulLogin? null: $model->password,
                'ip'             => Yii::$app->getRequest()->getUserIP(),
                'access_login'   => time(),
                'access'         => 0,
            ]);
        }

        // if user's account is not activated, he will have to activate it first
        if ($model->status === User::STATUS_INACTIVE && $successfulLogin === false) {
            Yii::$app->session->setFlash('error', "Primero debes activar tu cuenta. Por favor revise tu correo electrónico.");


            $EsysAcceso->save();

            return $this->refresh();
        }

        // if user is not denied because he is not active, then his credentials are not good
        if ($successfulLogin === false) {
            if(isset($EsysAcceso))
                $EsysAcceso->save();


            return $this->renderPartial('login', ['model' => $model]);
        }

        $user = $model->getUser();

        $EsysAcceso->user_id = $user->id;
        $EsysAcceso->access  = 1;

        $EsysAcceso->save();

        // login was successful, let user go wherever he previously wanted
        return $this->goBack();
    }

    /**
     * Logs out the user.
     *
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        $EsysAcceso = EsysAcceso::find()->where([
                'user_id'       => Yii::$app->user->identity->id,
                'access'        => 1,
                'access_logout' => null,
            ])
            ->orderBy('id desc')
            ->limit(1)
            ->one();

        if($EsysAcceso){
            $EsysAcceso->access_logout = time();
            $EsysAcceso->save();
        }

        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Sends email that contains link for password reset action.
     *
     * @return string|\yii\web\Response
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();

        if (!$model->load(Yii::$app->request->post()) || !$model->validate()) {
            return $this->renderPartial('requestPasswordResetToken', ['model' => $model]);
        }

        if (!$model->sendEmail()) {
            Yii::$app->session->setFlash('error', "Lo sentimos, no podemos restablecer la contraseña para el correo electrónico proporcionado.");
            return $this->refresh();
        }

        Yii::$app->session->setFlash('success', "Revise su correo electrónico para obtener más instrucciones.");

        return $this->goHome();
    }

    /**
     * Resets password.
     *
     * @param  string $token Password reset token.
     * @return string|\yii\web\Response
     *
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);

        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if (!$model->load(Yii::$app->request->post()) || !$model->validate() || !$model->resetPassword()) {
            return $this->renderPartial('resetPassword', ['model' => $model]);
        }

        Yii::$app->session->setFlash('success', "Se guardó la nueva contraseña.");

        return $this->goHome();
    }

    /**
     * Displays Change Password page.
     *
     * @return string
     */
    public function actionChangePassword()
    {
        $model = new ChangePassword(['username' => \Yii::$app->user->identity->username]);

        // if validation didn't pass, reload the form to show errors
        if (!$model->load(Yii::$app->request->post()) || !$model->validate()) {
            return $this->render('changePassword', ['model' => $model]);
        }

        // Cambiamos la contraseña
        $model->updatePassword();

        Yii::$app->session->setFlash('success', 'La contraseña ha sido cambiada satisfactoriamente.');


        return $this->goHome();
    }

    /**
     * Displays a single User model.
     *
     * @param  integer $id The user id.
     * @return string
     *
     * @throws NotFoundHttpException
     */
    public function actionMiPerfil()
    {
        $model = $this->findModel(Yii::$app->user->identity->id);

        return $this->render('mi-perfil', [
            'model' => $model,
        ]);
    }



    public function actionEnableAccesoApp($user_id){
        $model = User::findOne($user_id);
        $model->api_enabled = User::API_ACTIVE;
        if ($model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        Yii::$app->session->setFlash('danger', 'No se realizo el cambio correctamente, intente nuevamente por favor.');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionDesabledAccesoApp($user_id){
        $model = User::findOne($user_id);
        $model->api_enabled = User::API_INACTIVE;
        if ($model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        Yii::$app->session->setFlash('danger', 'No se realizo el cambio correctamente, intente nuevamente por favor.');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionReporteVentaProductoAjax()
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            $filters = $request->get('filters');

            $reporteProducto = ViewProducto::getReporteProductoTop([ "filters" => $filters]);

            return [
                "code" => 202,
                "reporte" => $reporteProducto,
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionReporteVentaClienteAjax()
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            $filters = $request->get('filters');

            $reporteProducto = ViewCliente::getReporteClienteTop([ "filters" => $filters]);

            return [
                "code" => 202,
                "reporte" => $reporteProducto,
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
    public function actionUsersJsonBtt(){
        return ViewUser::getJsonBtt(Yii::$app->request->get());
    }

    public function actionUserAjax($q = false)
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

            // Obtenemos user
            $user = ViewUser::getUserAjax($text);

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
                $model = User::findOne($id);
                break;

            case 'view':
                $model = ViewUser::findOne($id);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }

}
