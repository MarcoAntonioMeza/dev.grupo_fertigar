<?php
/* @var $this yii\web\View */
/* @var $model backend\models\cliente\Cliente */

$this->title = $model->nombre;
//$this->params['breadcrumbs'][] = 'Clientes';
$this->params['breadcrumbs'][] = ['label' => 'Sucursales', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';

?>


<div class="sucursales-sucursal-update">

    <?= $this->render('_form', [
    	'model' => $model,
    ]) ?>

</div>
