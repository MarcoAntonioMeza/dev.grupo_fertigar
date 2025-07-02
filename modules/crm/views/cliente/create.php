<?php
/* @var $this yii\web\View */
/* @var $model backend\models\cliente\Cliente */

$this->title = 'Nuevo cliente';
//$this->params['breadcrumbs'][] = 'Clientes';
$this->params['breadcrumbs'][] = ['label' => 'Clientes', 'url' => ['index']];
?>


<div class="clientes-cliente-create">

    <?= $this->render('_form', [
		'model' 	=> $model,
	]) ?>

</div>
