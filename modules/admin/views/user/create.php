<?php
/* @var $this yii\web\View */
/* @var $user backend\models\user\User */

$this->title = 'Nuevo usuario interno';
$this->params['breadcrumbs'][] = 'Administración';
$this->params['breadcrumbs'][] = ['label' => 'Usuarios internos', 'url' => ['index']];
?>


<div class="admin-user-create">

    <?= $this->render('_form', [
		'user' => $user,
	]) ?>

</div>
