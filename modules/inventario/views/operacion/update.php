<?php

$this->title = "Operacion #".$model->id;
$this->params['breadcrumbs'][] = ['label' => 'Operaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
?>


<div class="inventario-ajuste-operacion-update">
    <?= $this->render('_form', [
    	'model' => $model,
    ]) ?>
</div>
