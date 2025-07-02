<?php

$this->title = 'Nueva credito';
$this->params['breadcrumbs'][] = 'Credito';
$this->params['breadcrumbs'][] = ['label' => 'creditos', 'url' => ['index']];

?>
<div class="creditos-credito-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
