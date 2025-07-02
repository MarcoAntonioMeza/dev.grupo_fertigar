<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \app\models\SignupForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\password\PasswordInput;
use app\assets\AppAsset;
use app\widgets\Alert;

AppAsset::register($this);

$this->title = "Registrate";
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
                
                <!-- BACKGROUND IMAGE -->
                <!--===================================================-->
                <div id="bg-overlay2" class="bg-img"></div>
                
                
                <!-- LOGIN FORM -->
                <!--===================================================-->
                <div class="cls-content">
                    <div class="cls-content-sm panel">
                        <div class="panel-body">
                            <?= Alert::widget(); ?>

                            <div class="mar-ver pad-btm">
                                <h3 class="h4 mar-no">Cuear una nueva cuenta</h3>
                                <p class="text-muted">Registrate en <strong class="text-custom"><?= Yii::$app->name ?></strong></p>
                                <!--
                                <div class="mar-hor mar-top">
                                    <?= Html::img(Url::to('@themes/img/logo-elitesystems.png'), ['class' => 'img-responsive']) ?>
                                </div>
                                -->
                            </div>

                            <?php $form = ActiveForm::begin(['id' => 'form-signup']) ?>
                                <?= $form->field($model, 'username')->textInput(['placeholder' => Yii::t('app', 'Create your username'), 'autofocus' => true])->label(false) ?>

                                <?= $form->field($model, 'email')->input('email', ['placeholder' => Yii::t('app', 'Enter your e-mail')])->label(false) ?>

                                <?= $form->field($model, 'password')->widget(PasswordInput::classname(), ['options' => ['placeholder' => Yii::t('app', 'Create your password')]])->label(false) ?>

                                <?= Html::submitButton('Registrate', ['class' => 'btn btn-primary btn-block', 'name' => 'login-button']) ?>
                            <?php ActiveForm::end(); ?>
                    
                            <?php if ($model->scenario === 'rna'): ?>
                                <small><i>*<?= Yii::t('app', 'We will send you an email with account activation link.') ?></i></small>
                            <?php endif ?>

                            <div class="pad-all">
                                ¿Ya cuentas con una cuenta? <?= Html::a('Iniciar seción', ['/sucursal/user/login'], ['class' => 'btn-link mar-rgt']) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!--===================================================-->

            </div>
            <!--===================================================-->
            <!-- END OF CONTAINER -->


        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
