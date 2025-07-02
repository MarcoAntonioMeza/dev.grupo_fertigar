<?php
$this->title = 'Nueva Poliza';
$this->params['breadcrumbs'][] = ['label' => 'Nueva Poliza', 'url' => ['index']];
?>
<div class="contabilidad-polizas-create">
    <?= $this->render('_form', [
		'model' 	=> $model,
	]) ?>
</div>