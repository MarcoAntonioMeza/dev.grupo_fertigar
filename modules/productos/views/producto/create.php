<?php

$this->title = 'Nuevo producto';
$this->params['breadcrumbs'][] = ['label' => 'Productos', 'url' => ['index']];

?>
<div class="productos-producto-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
