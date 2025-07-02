<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \app\models\ResetPasswordForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\ActiveForm;
use kartik\password\PasswordInput;

use app\assets\AppAsset;
use app\widgets\Alert;

AppAsset::register($this);

$this->title = "Cambiar contraseña";
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
                <div id="bg-overlay" class="bg-img"></div>
                <div class="cls-content">
                    <?= Alert::widget(); ?>

                    <div class="cls-content-sm panel">
                        <div class="panel-body">
                            <div class="mar-ver pad-btm">
                                <p class="text-muted">Por favor elija tu nueva contraseña</p>
                            </div>
                            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
                                <?= $form->field($model, 'password')->widget(PasswordInput::classname(), ['options' => ['placeholder' => 'Ingresa tu contraseña', 'autofocus' => true]]) ?>

                                <?= Html::submitButton('Cambiar contraseña', ['class' => 'btn btn-primary btn-lg btn-block', 'name' => 'login-button']) ?>
                            <?php ActiveForm::end(); ?>
                        </div>

                        <div class="pad-all">
                            <?php if ($model->scenario === 'rna'): ?>
                                <div style="color:#666;margin:1em 0">
                                    <i>*Te enviaremos un correo electrónico con el enlace de activación de tu cuenta</i>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php $this->endBody() ?>
    </body>
</html>

<?php $this->endPage() ?>
