<?php
$this->title = 'SOLICITUD DE AJUSTE DE INVENTARIO';
$this->params['breadcrumbs'][] = ['label' => 'Inventario', 'url' => ['index']];

?>

<div class="inventario-ajuste-inventario-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
