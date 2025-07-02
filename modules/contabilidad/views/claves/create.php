<?php
$this->title = 'NUEVA CLAVE';
$this->params['breadcrumbs'][] = ['label' => 'CONTABILIDAD'];
$this->params['breadcrumbs'][] = ['label' => 'CLAVES', 'url' => ['index']];
?>
<div class="contabilidad-claves-create">
    <?= $this->render('_form', [
		'model' 	=> $model,
	]) ?>
</div>