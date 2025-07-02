<?php

$this->title = 'Nueva operación';
$this->params['breadcrumbs'][] = ['label' => 'Operaciones', 'url' => ['index']];

?>

<div class="inv-entradas-salidas-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
