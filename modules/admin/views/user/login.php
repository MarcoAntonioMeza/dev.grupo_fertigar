<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \app\models\LoginForm */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use app\assets\AppAsset;
use app\widgets\Alert;

AppAsset::register($this);

$this->title = "Iniciar sesión";
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title . ($this->title? ' | ': '') . Yii::$app->name) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="gray-bg">
        <?php $this->beginBody() ?>
            <div id="container" class="cls-container">
                <div id="bg-overlay8" class="bg-img"></div>

                <div class="cls-content" style="position: relative;">
                    <?= Alert::widget(); ?>

                    <div class="cls-content-sm  panel panel-colorful middle-box text-center loginscreen animated fadeInDown" style="max-width: 350px;">
                        <div class="panel-body " >
                            <div class="mar-ver pad-btm">
                                <h3 class="h4 mar-no">Iniciar sesión</h3>
                                <p class="text-muted">Ingresa con tu cuenta de usuario</p>
                                <div class="mar-hor mar-top">
                                    <?= Html::img(Url::to('@web/img/logo.png'), ['class' => 'img-responsive','style' => 'text-align: center;width: 50%;' ]) ?>
                                </div>
                            </div>
                            <?php $form = ActiveForm::begin(['id' => 'login-form', "options" => ["class" => "m-t"] ]) ?>
                                <?php if($model->scenario === 'e'): ?>
                                    <?= $form->field($model, 'email')->input('email', ['placeholder' => 'Ingresa tu correo electrónico', 'autofocus' => true])->label(false) ?>

                                <?php elseif($model->scenario === 'u'): ?>
                                    <?= $form->field($model, 'username')->textInput(['placeholder' => 'Ingresa tu nombre de usuario', 'autofocus' => true])->label(false) ?>

                                <?php else: ?>
                                    <?= $form->field($model, 'username')->textInput(['placeholder' => 'Ingresa tu correo o nombre de usuario', 'autofocus' => true])->label(false) ?>

                                <?php endif ?>

                                <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Ingresa tu contraseña'])->label(false) ?>

                                <?= $form->field($model, 'rememberMe', ['template' => "<div class='checkbox pad-btm text-left'>\n{input}\n{label}\n</div>\n<div>{error}</div>"])->checkbox(["class" => "magic-checkbox"], false) ?>

                                <?= Html::submitButton('Iniciar sesión', ['class' => 'btn btn-primary btn-lg btn-block', 'name' => 'login-button']) ?>
                            <?php ActiveForm::end(); ?>
                            <div class="pad-all">
                                <?= Html::a('¿Olvidaste tu contraseña?', ['/admin/user/request-password-reset'], ['class' => 'btn-link mar-rgt']) ?>
                                <?php //= Html::a('Registrate', ['/sucursal/user/signup'], ['class' => 'btn-link mar-lft']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
