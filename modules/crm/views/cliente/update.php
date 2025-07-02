<?php
/* @var $this yii\web\View */
/* @var $model backend\models\cliente\Cliente */

$this->title = $model->nombre . ' '. $model->apellidos;
//$this->params['breadcrumbs'][] = 'Clientes';
$this->params['breadcrumbs'][] = ['label' => 'Clientes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>



<div class="clientes-cliente-update">

    <?= $this->render('_form', [
    	'model' => $model,
    ]) ?>

</div>
