<?php

$this->title = 'Nueva Devolución';
$this->params['breadcrumbs'][] = 'Inventario';
$this->params['breadcrumbs'][] = ['label' => 'Devoluciones', 'url' => ['index']];

?>

<div class="inv-devolucion-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
