<?php

$this->title = $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'CONTABILIDAD'];
$this->params['breadcrumbs'][] = ['label' => 'CLAVES', 'url' => ['index']];

?>

<div class="contabilidad-claves-update">

    <?= $this->render('_form', [
    	'model' => $model,
    ]) ?> 

</div>