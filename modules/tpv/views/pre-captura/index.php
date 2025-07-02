<?php
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Pre Ventas';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="ibox">
    <div class="ibox-content">
        <p>
            <?= $can['create']?
                Html::a('<i class="fa fa-plus"></i> Nueva PRE - VENTA', ['create'], ['class' => 'btn btn-success add']): '' ?>
        </p>

        <div class="tabs-container">
            <ul class="nav nav-tabs" role="tablist">
                <li>
                    <a class="nav-link active" data-toggle="tab" href="#tab-precaptura">PRE-VENTAS</a>
                </li>
                <li>
                    <a class="nav-link" data-toggle="tab" href="#tab-proceso">PROCESO</a>
                </li>
                <li>
                    <a class="nav-link" data-toggle="tab" href="#tab-cancel">CANCELADAS</a>
                </li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" id="tab-precaptura" class="tab-pane active">
                    <?= $this->render('_index_precaptura',[
                        "can"   => $can
                        ]) ?>
                </div>
                <div id="tab-proceso"  role="tabpanel" class="tab-pane">
                    <?= $this->render('_index_proceso',[
                        "can"   => $can
                        ]) ?>
                </div>
                 <div id="tab-cancel"  role="tabpanel" class="tab-pane">
                    <?= $this->render('_index_cancel',[
                        "can"   => $can
                        ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

