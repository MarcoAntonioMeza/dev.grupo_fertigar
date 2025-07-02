<?php

$this->title = 'Nueva carga';
$this->params['breadcrumbs'][] = 'Ruta';
$this->params['breadcrumbs'][] = ['label' => 'CARGA DE RUTA', 'url' => ['index']];

?>

<div class="logistica-carga-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
