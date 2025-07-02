<?php
/* @var $this yii\web\View */
use yii\helpers\Html;

$this->title = 'Creditos';
$this->params['breadcrumbs'][] = $this->title;



?>

<div class="tabs-container">
    <ul class="nav nav-tabs" role="tablist">
        <li>
            <a class="nav-link active" data-toggle="tab" href="#tab-credito-vigente">VIGENTE</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-credito-vencido">VENCIDO</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-credito-cancel">CANCELADOS</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-credito-pagado">PAGADO</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-credito-hoy">HOY</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-saldos-ruta">SALDOS POR RUTA</a>
        </li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" id="tab-credito-vigente" class="tab-pane active">
            <?= $this->render('_index_vigente',[
                "can"   => $can
                ]) ?>
        </div>
        <div id="tab-credito-vencido"  role="tabpanel" class="tab-pane">
            <?= $this->render('_index_vencido',[
                "can"   => $can
                ]) ?>
        </div>
         <div id="tab-credito-cancel"  role="tabpanel" class="tab-pane">
            <?= $this->render('_index_cancel',[
                "can"   => $can
                ]) ?>
        </div>
        <div id="tab-credito-pagado"  role="tabpanel" class="tab-pane">
            <?= $this->render('_index_pagado',[
                "can"   => $can
                ]) ?>
        </div>
        <div id="tab-credito-hoy"  role="tabpanel" class="tab-pane">
            <?= $this->render('_index_hoy',[
                "can"   => $can
            ]) ?>
        </div>
        <div id="tab-saldos-ruta"  role="tabpanel" class="tab-pane">
            <?= $this->render('_index_saldos_ruta',[
                "can"   => $can
            ]) ?>
        </div>
    </div>
</div>