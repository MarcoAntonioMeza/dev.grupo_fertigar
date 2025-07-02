<?php
/* @var $this yii\web\View */
/* @var $model backend\models\cliente\Cliente */

$this->title = "Folio #".$model->id;
//$this->params['breadcrumbs'][] = 'Clientes';
$this->params['breadcrumbs'][] = ['label' => 'Tpv', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';

?>


<div class="tpv-pre-captura-update">

    <?= $this->render('_form', [
    	'model' => $model,
    	//'sucursal' => $sucursal,
    ]) ?>

</div>
