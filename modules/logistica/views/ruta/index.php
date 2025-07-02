<?php
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Ruta / Carga';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="tabs-container">
    <ul class="nav nav-tabs" role="tablist">
        <li>
            <a class="nav-link active" data-toggle="tab" href="#tab-carga-ruta">CARGA DE RUTA</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-lista-pedido">LISTA DE PEDIDOS</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-lista-embarque">LISTA DE EMBARQUE</a>
        </li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" id="tab-carga-ruta" class="tab-pane active">
            <?= $this->render('_index_ruta',[
                "can"   => $can
                ]) ?>
        </div>
        <div id="tab-lista-pedido"  role="tabpanel" class="tab-pane">
            <?= $this->render('_index_pedido',[
                "can"   => $can
                ]) ?>
        </div>
        <div id="tab-lista-embarque"  role="tabpanel" class="tab-pane">
            <?= $this->render('_index_embarque',[
                "can"   => $can
                ]) ?>
        </div>
    </div>
</div>
