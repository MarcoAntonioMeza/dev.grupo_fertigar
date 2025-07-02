<?php
/* @var $this yii\web\View */

$this->title = 'Nuevo proveedor';
//$this->params['breadcrumbs'][] = 'Clientes';
$this->params['breadcrumbs'][] = ['label' => 'Proveedores', 'url' => ['index']];
?>


<div class="proveedores-proveedor-create">

    <?= $this->render('_form', [
		'model' 	=> $model,
	]) ?>

</div>
