<?php
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = $model->nombre;

$this->params['breadcrumbs'][] = ['label' => 'Proveedor', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';

?>

<p>
    <?= $can['update'] ?
        Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>

    <?= $can['delete']?
        Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas eliminar este proveedor?',
                'method' => 'post',
            ],
    ]): '' ?>
</p>

<div class="tab-base">
    <ul class="nav nav-tabs" role="tablist">
        <li >
            <a data-toggle="tab"  class="nav-link active" href="#tab-info">INFORMACIÓN</a>
        </li>
        <li >
            <a data-toggle="tab"  class="nav-link" href="#tab-compra">COMPRA</a>
        </li>
        <li>
            <a data-toggle="tab"  class="nav-link" href="#tab-credito-cobranza">CREDITO Y COBRANZA</a>
        </li>
    </ul>
    <div class="tab-content">

        <div id="tab-info" class="tab-pane active">
            <?= $this->render('_view_info',[
                "model" => $model,
            ]) ?>
        </div>

        <div id="tab-compra" class="tab-pane">
            <?= $this->render('_view_compra',[
                "model" => $model,
            ]) ?>
        </div>

        <div id="tab-credito-cobranza" class="tab-pane">
            <?= $this->render('_view_credito',[
                "model" => $model,
            ]) ?>
        </div>
    </div>
</div>