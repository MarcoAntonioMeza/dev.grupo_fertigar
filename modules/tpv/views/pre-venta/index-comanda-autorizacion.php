<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\venta\Venta;

/* @var $this yii\web\View */

$this->title = 'PREVENTAS EN PROCESO';
$this->params['breadcrumbs'][] = $this->title;

$preventaAll = Venta::getPreventaProceso();

?>

<style>
    .contact-box:hover{
        transition: background-color 2s ease-out 100ms
    }
    .contact-box:hover{
        background: #559d67;
        color: #fff;
    }
</style>
<div class="ibox">
    <div class="ibox-content">
        <div class="tpv-preventa-index">
            <?php if ($preventaAll): ?>
                <div class="row">
                <?php foreach ($preventaAll as $key => $item_preventa): ?>
                        <div class="col-sm-4">
                            <div class="contact-box">
                                <a class="row" href="<?= Url::to(["update-preventa", "preventa_id" => $item_preventa->id ]) ?>">
                                <div class="col-4">
                                    <div class="text-center">
                                        <i class="fa fa-shopping-cart"  id="icon-proceso-verificacion" style="font-size:54px; "></i>
                                        <div class="m-t-xs font-bold">COMANDA</div>
                                        <strong style="font-size:14px">#<?= $item_preventa->id ?></strong>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <h3><strong><?= $item_preventa->cliente_id ? $item_preventa->cliente->nombreCompleto : 'PUBLICO GENERAL'  ?></strong></h3>
                                    <p><i class="fa fa-clock-o"></i> <?= date("h:i a d/m/Y",$item_preventa->created_at) ?></p>
                                    <address>
                                        <strong>Vendedor</strong><br>
                                        <?= $item_preventa->createdBy->nombreCompleto ?><br>
                                    </address>
                                </div>
                                    </a>
                            </div>
                        </div>
                <?php endforeach ?>
                </div>

            <?php else: ?>
                <div class="text-center">
                    <h3>NINGUNA COMANDA</h3>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>

