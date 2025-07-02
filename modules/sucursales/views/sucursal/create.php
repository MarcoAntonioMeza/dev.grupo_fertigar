<?php

$this->title = 'Nueva sucursal';
$this->params['breadcrumbs'][] = 'Sucursal';
$this->params['breadcrumbs'][] = ['label' => 'Sucursales', 'url' => ['index']];

?>


<div class="sucursales-sucursal-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
