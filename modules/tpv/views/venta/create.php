<?php
$this->title = 'Nueva VENTA';
$this->params['breadcrumbs'][] = ['label' => 'Ventas', 'url' => ['index']];
?>

<div class="tpv-venta-create">

    <?= $this->render('_form' , [
        'model'     => $model,
        'bloqueo'   => $bloqueo,
        'can'       => $can,
    ]) ?>

</div>




































