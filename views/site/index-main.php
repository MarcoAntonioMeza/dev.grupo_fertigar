<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \app\models\LoginForm */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\assets\MainAsset;
use app\widgets\Alert;

MainAsset::register($this);

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
    <body>
        <?php $this->beginBody() ?>
           <!-- preloader section -->
			<div class="preloader">
				<div class="sk-spinner sk-spinner-circle">
			       <div class="sk-circle1 sk-circle"></div>
			       <div class="sk-circle2 sk-circle"></div>
			       <div class="sk-circle3 sk-circle"></div>
			       <div class="sk-circle4 sk-circle"></div>
			       <div class="sk-circle5 sk-circle"></div>
			       <div class="sk-circle6 sk-circle"></div>
			       <div class="sk-circle7 sk-circle"></div>
			       <div class="sk-circle8 sk-circle"></div>
			       <div class="sk-circle9 sk-circle"></div>
			       <div class="sk-circle10 sk-circle"></div>
			       <div class="sk-circle11 sk-circle"></div>
			       <div class="sk-circle12 sk-circle"></div>
			    </div>
			</div>

			<!-- navigation section -->
			<section class="navbar navbar-fixed-top custom-navbar" role="navigation">
				<div class="container">
					<div class="navbar-header">
						<button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
							<span class="icon icon-bar"></span>
							<span class="icon icon-bar"></span>
							<span class="icon icon-bar"></span>
						</button>
						<a href="#" class="navbar-brand"><?= Html::img(Url::to('@web/img/logo.png'), ['class' => 'navbar-brand', 'style' => 'width:160px; height: auto;']) ?></a>
					</div>
					<div class="collapse navbar-collapse">
					</div>
				</div>
			</section>

			<!-- home section -->
			<section id="home">
				<div class="container">
					<div class="row">
						<div class="col-md-12 col-sm-12">

							<h3 style="color:#fff; " >PANEL DE CONTROL</h3>
							<h1 style="color:#fff;font-weight: bold; font-size: 64px;" ><?= Html::encode(Yii::$app->name) ?></h1>
							<hr>
							<a href="<?= Url::to(['/admin/user/login']) ?>" class="smoothScroll btn btn-danger">Iniciar sesión</a>
							<a href="<?= Url::to(['/admin/user/request-password-reset'])   ?>" class="smoothScroll btn btn-default">Olvide contraseña</a>
						</div>
					</div>
				</div>
			</section>



        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
