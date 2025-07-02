<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \app\models\ResetPasswordForm */

use kartik\password\PasswordInput;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

$this->title = 'Cambiar contraseña';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-change-password">
    <?php $form = ActiveForm::begin(['id' => 'form-changePassword']); ?>
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <p>Ingresa tu contraseña actual y nueva:</p>
                <?= $form->field($model, 'old_password')->input('password', ['placeholder' => 'Ingresa tu contraseña actual', 'autofocus' => true]) ?>
                <?= $form->field($model, 'password')->widget(PasswordInput::classname(), ['options' => ['placeholder' => 'Ingresa tu nueva contraseña']]) ?>
                <div class="form-group">
                    <?= Html::submitButton('Cambiar contraseña', ['class' => 'btn btn-primary', 'name' => 'changePassword-button']) ?>
                </div>
            </div>
        </div>
    <?php ActiveForm::end(); ?>
</div>
