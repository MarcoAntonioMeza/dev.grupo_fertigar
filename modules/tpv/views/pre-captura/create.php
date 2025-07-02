<?php

$this->title = 'Nueva PRE-VENTA';
$this->params['breadcrumbs'][] = ['label' => 'PreCapturas', 'url' => ['index']];

?>


<div class="tpv-pre-captura-create">

    <?= $this->render('_form' , [
        'model' => $model,
        //'sucursal' => $sucursal,
    ]) ?>

</div>




































