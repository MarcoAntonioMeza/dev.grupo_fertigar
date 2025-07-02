<?php
use yii\helpers\Url;
use yii\helpers\Html;


$this->title = 'POLIZAS';
$this->params['breadcrumbs'][] = 'CONTABILIDAD';   
$this->params['breadcrumbs'][] = 'POLIZAS';

?>

<div class="tabs-container">
    <ul class="nav nav-tabs" role="tablist">
        <li>
            <a class="nav-link active" data-toggle="tab" href="#tab-informacion">POLIZAS</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-personal">VERIFICACION - POLIZAS</a>
        </li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" id="tab-informacion" class="tab-pane active">
            <?= $this->render('tab_index_poliza',[
                "can"  => $can
            ]) ?>
        </div>
        <div id="tab-personal"  role="tabpanel" class="tab-pane">
            <?= $this->render('tab_index_corte',[
                "can"  => $can
            ]) ?>
        </div>
    </div>
</div>
