<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;


BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Devoluciones';
$this->params['breadcrumbs'][] = $this->title;

?>
<p>
    <?= $can['create'] ?
            Html::a('Nueva Devolución', ['create'], ['class' => 'btn btn-success add']): '' ?>

    <?= $can['tranformacion'] ?
            Html::a('NUEVA TRANSFORMACIÓN', ['tranformacion'], ['class' => 'btn btn-success add']): '' ?>
</p>

<div class="tabs-container">
    <ul class="nav nav-tabs" role="tablist">
        <li>
            <a class="nav-link active" data-toggle="tab" href="#tab-devolucion">DEVOLUCIONES</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-tranformacion">TRANSFORMACIONES</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-merma">MERMAS</a>
        </li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" id="tab-devolucion" class="tab-pane active">
            <?= $this->render('tab_devolucion',[
                "can"   => $can
                ]) ?>
        </div>
        <div id="tab-tranformacion"  role="tabpanel" class="tab-pane">
            <?= $this->render('tab_tranformacion',[
                "can"   => $can
                ]) ?>
        </div>
        <div id="tab-merma"  role="tabpanel" class="tab-pane">
            <?= $this->render('tab_merma',[
                "can"   => $can
            ]) ?>
        </div>
    </div>
</div>


