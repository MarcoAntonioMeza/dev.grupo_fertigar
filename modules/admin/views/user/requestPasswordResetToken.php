<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\ActiveForm;
use kartik\password\PasswordInput;
use app\widgets\Alert;
use app\assets\AppAsset;

AppAsset::register($this);

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \app\models\ResetPasswordForm */

$this->title = "¿Olvidaste tu contraseña?";
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
    <body>
        <?php $this->beginBody() ?>

            <div id="container" class="cls-container">
                <div id="bg-overlay9" class="bg-img"></div>
                <div class="cls-content">
                    <div class="cls-content-sm panel">
                        <div class="panel-body">
                            <div class="pad-ver">
                                <?= Alert::widget(); ?>
                            </div>

                            <p class="text-muted pad-btm">Se te enviará un enlace a tu correo electrónico para restablecer la contraseña.</p>

                            <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
                                <?= $form->field($model, 'email')->input('email', ['placeholder' => 'Por favor ingresa tu correo electrónico.', 'autofocus' => true])->label(false) ?>

                                <?= Html::submitButton('Enviar', ['class' => 'btn btn-success btn-block', 'name' => 'login-button']) ?>
                            <?php ActiveForm::end(); ?>

                            <div class="pad-top">
                                <?= Html::a('Regresar a iniciar sesión', ['/'], ["class" => "btn-link mar-rgt"]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php $this->endBody() ?>
    </body>
</html>

<?php $this->endPage() ?>
