<?php

$this->title = 'Nuevo almacen';
$this->params['breadcrumbs'][] = ['label' => 'Almacenes', 'url' => ['index']];

?>


<div class="almacenes-almacen-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
