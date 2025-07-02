<?php
use yii\helpers\Html;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $model common\models\ViewCliente */

$this->title = $model->nombre . ' '. $model->apellidos;

$this->params['breadcrumbs'][] = ['label' => 'Clientes', 'url' => ['index']];
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
            'confirm' => '¿Estás seguro de que deseas eliminar este cliente?',
            'method' => 'post',
        ],
    ]): '' ?>

</p>

<div class="tab-base">
    <ul class="nav nav-tabs" role="tablist">
        <li >
            <a data-toggle="tab"  class="nav-link active" href="#tab-info">INFORMACIÓN</a>
        </li>

        <li>
            <a data-toggle="tab"  class="nav-link" href="#tab-ventas">VENTAS</a>
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

        <div id="tab-ventas" class="tab-pane">
            <?= $this->render('_view_venta',[
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