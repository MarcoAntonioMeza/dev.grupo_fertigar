<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\apertura\AperturaCaja;
use app\models\apertura\AperturaCajaDetalle;
use app\models\cobro\CobroVenta;
use app\models\venta\Venta;
use app\models\Esys;

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title = "#" . $model->id ."- ". $model->user->nombreCompleto;

$this->params['breadcrumbs'][] = ['label' => 'Apertura de caja', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Ver';
?>

<div class="apertura-caja-view">
    <div class="row">
        <div class="col-md-8">
            <div class="ibox">
                <div class="ibox-content">
                    <h2><?= $model->user->nombreCompleto ?> <small class="float-right"
                            style="font-size: 14px;"><?= Esys::unixTimeToString($model->fecha_apertura,"Y-m-d h:i a") ?>
                            / <?= Esys::unixTimeToString($model->fecha_cierre,"Y-m-d h:i a") ?></small></h2>
                    <div class="text-center">
                        <div class="row">
                            <div class="col">
                                <div class=" m-l-md">
                                    <span class="h5 font-bold m-t block">$
                                        <?= number_format($model->cantidad_caja,2) ?></span>
                                    <small class="text-muted m-b block">APERTURA</small>
                                </div>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?= number_format($model->total,2) ?></span>
                                <small class="text-muted m-b block">CIERRE</small>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?=number_format((( AperturaCaja::getTotalEfectivoTpv($model->id)  + $model->cantidad_caja ) - (AperturaCaja::getTotalRetiroTpv($model->id) + AperturaCaja::getTotalGastoTpv($model->id)))  - $model->total ,2)  ?></span>
                                <small class="text-muted m-b block"  style="font-size:10px">FALTANTE ( (EFECTIVO CAJA + APERTURA)  -  CIERRE )</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="ibox">
                        <div class="ibox-content">
                            <div class="text-center">
                                <div class="row">
                                    <div class="col">
                                        <div class=" m-l-md">
                                            <span class="h5 font-bold m-t block">$
                                                <?= number_format(AperturaCaja::getTotalVentaTpv($model->id),2) ?></span>
                                            <small class="text-muted m-b block">TOTAL DE VENTA <strong class="text-warning"> [EFECTIVO]</strong></small>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalCreditoTpv($model->id),2) ?></span>
                                        <small class="text-muted m-b block">TOTAL ABONADO <strong class="text-warning"> [EFECTIVO]</strong></small>
                                    </div>
                                    <div class="col">
                                        <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalRetiroTpv($model->id),2) ?></span>
                                        <small class="text-muted m-b block">TOTAL RETIRO</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-info">
                <div class="panel-heading text-center">
                    <h3 style="margin:4%"><?= AperturaCaja::$statusList[$model->status] ?></h3>
                </div>
            </div>
            <div class="panel">
                <?= Html::button("TICKET  [OPERACIONES]", ["class" => "btn btn-success btn-block", "style" => "padding:5%", "onclick" => "openTicketOperacion()" ] ) ?>
            </div>
            <div class="panel">
                <?= Html::button("REPORTE [OPERACIONES]", ["class" => "btn btn-danger btn-block", "style" => "padding:5%", "onclick" => "openReporteOperacion()" ] ) ?>
            </div>
            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
        <?php /* ?>
        <div class="col-md-4">
            <div class="row">
                <iframe width="100%" class="panel" height="300px" src="<?= Url::to(['imprimir-recibo', 'id' => $model->id ])  ?>"></iframe>
            </div>
            <br>
            <div class="row">
                <iframe width="100%" class="panel" height="300px" src="<?= Url::to(['imprimir-reporte', 'id' => $model->id ])  ?>"></iframe>
            </div>
        </div>
        */?>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="text-center">
                        <div class="row">
                            <div class="col">
                                <div class=" m-l-md">
                                    <span class="h5 font-bold m-t block">
                                        $<?= number_format(( AperturaCaja::getTotalEfectivoTpv($model->id)) - ( AperturaCaja::getTotalRetiroTpv($model->id) +  AperturaCaja::getTotalGastoTpv($model->id))  ,2)  ?>
                                    </span>
                                    <strong class="text-muted m-b block" style="font-size:10px">EFECTIVO CAJA ( T. EFECTIVO - ( RETIRO + GASTO) )</strong>
                                </div>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalTranferenciaTpv($model->id),2) ?></span>
                                <strong class="text-muted m-b block">TRANFERENCIA</strong>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalChequeTpv($model->id),2) ?></span>
                                <strong class="text-muted m-b block">CHEQUE</strong>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalTarjetaCreditoTpv($model->id),2) ?></span>
                                <strong class="text-muted m-b block">TARJETA DE CREDITO</strong>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalTarjetaDebitoTpv($model->id),2) ?></span>
                                <strong class="text-muted m-b block">TARJETA DE DEBITO</strong>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalDepositoTpv($model->id),2) ?></span>
                                <strong class="text-muted m-b block">DEPOSITO</strong>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalCreditoPayTpv($model->id),2) ?></span>
                                <strong class="text-muted m-b block">CREDITO</strong>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block text-info">$ <?= number_format(AperturaCaja::getTotalOtrosTpv($model->id),2) ?></span>
                                <strong class=" block text-info">OTRO</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h3>VENTAS / CREDITOS RELACIONADOS</h3>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table" style="font-size:12px">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">TIPO</th>
                                    <th class="text-center">VENTA</th>
                                    <th class="text-center">CREDITO</th>
                                    <th class="text-center">MONTO</th>
                                    <th class="text-center">EFECTIVO</th>
                                    <th class="text-center">CHEQUE</th>
                                    <th class="text-center">TRANFERENCIA</th>
                                    <th class="text-center">TARJETA CREDITO</th>
                                    <th class="text-center">TARJETA DEBITO</th>
                                    <th class="text-center">DEPOSITO</th>
                                    <th class="text-center">CREDITO</th>
                                    <th class="text-center">FECHA</th>
                                    <th class="text-center">REGISTRADOR POR</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $count = 0; ?>
                            <?php foreach (AperturaCaja::getAperturaCajaDetallesGroup($model->id) as $key => $aperturaCaja): ?>
                                <?php $count++?>
                                <tr class="<?= $aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_RETIRO ? 'text-danger font-bold' : '' ?>">
                                    <td class="text-center"><?= $count ?></td>
                                    <td class="text-center">
                                        <?= AperturaCajaDetalle::$tipoList[$aperturaCaja["tipo"]] ?>

                                        <?php if (!AperturaCajaDetalle::isVigentePago(($aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? $aperturaCaja["venta_id"] : $aperturaCaja["token_pay"]),$aperturaCaja["tipo"])): ?>
                                            <p><strong class="text-danger">CANCELADO</strong></p>
                                        <?php endif ?>

                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["venta_id"]): ?>
                                            <p style="font-size:16px; font-weight: bold;"><?= Html::a("#".str_pad($aperturaCaja["venta_id"],6,"0",STR_PAD_LEFT), ["/tpv/venta/view", "id" => $aperturaCaja["venta_id"],  ],["target" => "_blank"] ) ?></p>
                                        <?php endif ?>

                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_CREDITO): ?>
                                            <small><a href="javascript:void(0)" class="text-link" onclick="open_ticket('<?= $aperturaCaja["token_pay"] ?>')"><?= $aperturaCaja["token_pay"] ?></a></small>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_VENTA): ?>
                                            <?php if (!AperturaCajaDetalle::isVigentePago(($aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? $aperturaCaja["venta_id"] : $aperturaCaja["token_pay"]),$aperturaCaja["tipo"])): ?>
                                                <strong class="text-danger">$<?= number_format($aperturaCaja["cantidad_venta"],2) ?></strong>
                                            <?php else: ?>
                                                $<?= number_format($aperturaCaja["cantidad_venta"],2) ?>
                                            <?php endif ?>
                                        <?php else: ?>
                                            <?php if (!AperturaCajaDetalle::isVigentePago(($aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_VENTA ? $aperturaCaja["venta_id"] : $aperturaCaja["token_pay"]),$aperturaCaja["tipo"])): ?>
                                                <strong class="text-danger">$<?= number_format($aperturaCaja["cantidad_credito"],2) ?></strong>
                                            <?php else: ?>
                                                <?php if ($aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_RETIRO || $aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_GASTO): ?>
                                                        $<?= number_format($aperturaCaja["cantidad"],2) ?>
                                                <?php else: ?>
                                                        $<?= number_format($aperturaCaja["cantidad_venta"],2) ?>
                                                <?php endif ?>
                                            <?php endif ?>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_VENTA): ?>
                                            <?php $total_efectivo = CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_EFECTIVO, AperturaCajaDetalle::TIPO_VENTA) ?>

                                            <strong style="font-size:16px" class="<?= $total_efectivo > 0 ? 'text-warning': '' ?>">$<?= number_format($total_efectivo,2) ?></strong>

                                        <?php else: ?>
                                            <?php $total_efectivo = CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_EFECTIVO, AperturaCajaDetalle::TIPO_CREDITO) ?>

                                            <strong style="font-size:16px" class="<?= $total_efectivo > 0 ? 'text-warning': '' ?>">$<?= number_format($total_efectivo,2) ?></strong>

                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_VENTA): ?>
                                            <?php $total_cheque = CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_CHEQUE, AperturaCajaDetalle::TIPO_VENTA) ?>

                                            <strong style="font-size:16px" class="<?= $total_cheque > 0 ? 'text-warning': '' ?>">$<?= number_format($total_cheque,2) ?></strong>

                                        <?php else: ?>
                                            <?php $total_cheque = CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_CHEQUE, AperturaCajaDetalle::TIPO_CREDITO) ?>

                                            <strong style="font-size:16px" class="<?= $total_cheque > 0 ? 'text-warning': '' ?>">$<?= number_format($total_cheque,2) ?></strong>

                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_VENTA): ?>
                                            <?php $total_tranferencia =  CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_TRANFERENCIA, AperturaCajaDetalle::TIPO_VENTA) ?>

                                            <strong style="font-size:16px" class="<?= $total_tranferencia > 0 ? 'text-warning': '' ?>">$<?= number_format($total_tranferencia,2) ?></strong>

                                        <?php else: ?>

                                            <?php $total_tranferencia = CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_TRANFERENCIA, AperturaCajaDetalle::TIPO_CREDITO) ?>

                                            <strong style="font-size:16px" class="<?= $total_tranferencia > 0 ? 'text-warning': '' ?>">$<?= number_format($total_tranferencia,2) ?></strong>

                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_VENTA): ?>
                                            <?php $total_tarjeta_credito = CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_TARJETA_CREDITO, AperturaCajaDetalle::TIPO_VENTA) ?>

                                            <strong style="font-size:16px" class="<?= $total_tarjeta_credito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_tarjeta_credito,2) ?></strong>
                                        <?php else: ?>
                                            <?php $total_tarjeta_credito = CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_TARJETA_CREDITO, AperturaCajaDetalle::TIPO_CREDITO) ?>

                                            <strong style="font-size:16px" class="<?= $total_tarjeta_credito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_tarjeta_credito,2) ?></strong>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_VENTA): ?>
                                            <?php $total_tarjeta_debito = CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_TARJETA_DEBITO, AperturaCajaDetalle::TIPO_VENTA) ?>
                                            <strong style="font-size:16px" class="<?= $total_tarjeta_debito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_tarjeta_debito,2) ?></strong>
                                        <?php else: ?>
                                            <?php $total_tarjeta_debito =  CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_TARJETA_DEBITO, AperturaCajaDetalle::TIPO_CREDITO) ?>
                                            <strong style="font-size:16px" class="<?= $total_tarjeta_debito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_tarjeta_debito,2) ?></strong>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_VENTA): ?>
                                            <?php $total_deposito = CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_DEPOSITO, AperturaCajaDetalle::TIPO_VENTA) ?>
                                            <strong style="font-size:16px" class="<?= $total_deposito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_deposito,2) ?></strong>
                                        <?php else: ?>
                                            <?php $total_deposito = CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_DEPOSITO, AperturaCajaDetalle::TIPO_CREDITO) ?>

                                            <strong style="font-size:16px" class="<?= $total_deposito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_deposito,2) ?></strong>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == AperturaCajaDetalle::TIPO_VENTA): ?>
                                            <?php $total_credito = CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_CREDITO, AperturaCajaDetalle::TIPO_VENTA)  ?>
                                            <strong style="font-size:16px" class="<?= $total_credito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_credito,2) ?></strong>
                                        <?php else: ?>
                                            <?php $total_credito = CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_CREDITO, AperturaCajaDetalle::TIPO_CREDITO); ?>

                                            <strong style="font-size:16px" class="<?= $total_credito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_credito,2) ?></strong>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center"><?= date("Y-m-d h:i a",$aperturaCaja["created_at"])?></td>
                                    <td class="text-center"><?= $aperturaCaja["created_by_user"] ?></td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h3>OPERACIONES POR [TRANSFERENCIA,  DEPOSITO, CHEQUE, TARJETA] </h3>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table" style="font-size:12px">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">TIPO</th>
                                    <th class="text-center">VENTA</th>
                                    <th class="text-center">CREDITO</th>
                                    <th class="text-center">MONTO</th>
                                    <th class="text-center">EFECTIVO</th>
                                    <th class="text-center">CHEQUE</th>
                                    <th class="text-center">TRANFERENCIA</th>
                                    <th class="text-center">TARJETA CREDITO</th>
                                    <th class="text-center">TARJETA DEBITO</th>
                                    <th class="text-center">DEPOSITO</th>
                                    <th class="text-center">CREDITO</th>
                                    <th class="text-center">OTRO</th>
                                    <th class="text-center">FECHA</th>
                                    <th class="text-center">REGISTRADOR POR</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $count = 0; ?>
                            <?php foreach (CobroVenta::getOtrasOperaciones($model->id, $model->created_at, ( $model->fecha_cierre ? $model->fecha_cierre : time() ), $model->created_by ) as $key => $aperturaCaja): ?>
                                <?php $count++?>
                                <tr>
                                    <td class="text-center"><?= $count ?></td>
                                    <td class="text-center">
                                        <?= $aperturaCaja["tipo"] == 10 ? 'VENTA' : 'CREDITO' ?>
                                        <?php if (!AperturaCajaDetalle::isVigentePago(($aperturaCaja["tipo"] == 10 ? $aperturaCaja["venta_id"] : $aperturaCaja["token_pay"]),$aperturaCaja["tipo"])): ?>
                                            <p><strong class="text-danger">CANCELADO</strong></p>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">

                                        <?php if ($aperturaCaja["venta_id"]): ?>
                                            <p style="font-size:16px; font-weight: bold;"><?= Html::a("#".str_pad($aperturaCaja["venta_id"],6,"0",STR_PAD_LEFT), ["/tpv/venta/view", "id" => $aperturaCaja["venta_id"],  ],["target" => "_blank"] ) ?></p>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] != 10): ?>
                                            <small><a href="javascript:void(0)" class="text-link" onclick="open_ticket('<?= $aperturaCaja["token_pay"] ?>')"><?= $aperturaCaja["token_pay"] ?></a></small>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == 10): ?>
                                            $<?= number_format($aperturaCaja["cantidad_venta"],2) ?>
                                        <?php else: ?>
                                            $<?= number_format($aperturaCaja["cantidad_credito"],2) ?>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == 10): ?>
                                            <?php $total_efectivo = CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_EFECTIVO, 10) ?>

                                            <strong style="font-size:16px" class="<?= $total_efectivo > 0 ? 'text-warning': '' ?>">$<?= number_format($total_efectivo,2) ?></strong>

                                        <?php else: ?>
                                            <?php $total_efectivo = CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_EFECTIVO, 20) ?>

                                            <strong style="font-size:16px" class="<?= $total_efectivo > 0 ? 'text-warning': '' ?>">$<?= number_format($total_efectivo,2) ?></strong>

                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == 10): ?>
                                            <?php $total_cheque = CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_CHEQUE, 10) ?>

                                            <strong style="font-size:16px" class="<?= $total_cheque > 0 ? 'text-warning': '' ?>">$<?= number_format($total_cheque,2) ?></strong>

                                        <?php else: ?>
                                            <?php $total_cheque = CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_CHEQUE, 20) ?>

                                            <strong style="font-size:16px" class="<?= $total_cheque > 0 ? 'text-warning': '' ?>">$<?= number_format($total_cheque,2) ?></strong>

                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == 10): ?>
                                            <?php $total_tranferencia =  CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_TRANFERENCIA, 10) ?>

                                            <strong style="font-size:16px" class="<?= $total_tranferencia > 0 ? 'text-warning': '' ?>">$<?= number_format($total_tranferencia,2) ?></strong>

                                        <?php else: ?>

                                            <?php $total_tranferencia = CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_TRANFERENCIA, 20) ?>

                                            <strong style="font-size:16px" class="<?= $total_tranferencia > 0 ? 'text-warning': '' ?>">$<?= number_format($total_tranferencia,2) ?></strong>

                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == 10): ?>
                                            <?php $total_tarjeta_credito = CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_TARJETA_CREDITO, 10) ?>

                                            <strong style="font-size:16px" class="<?= $total_tarjeta_credito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_tarjeta_credito,2) ?></strong>
                                        <?php else: ?>
                                            <?php $total_tarjeta_credito = CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_TARJETA_CREDITO, 20) ?>

                                            <strong style="font-size:16px" class="<?= $total_tarjeta_credito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_tarjeta_credito,2) ?></strong>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == 10): ?>
                                            <?php $total_tarjeta_debito = CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_TARJETA_DEBITO, 10) ?>
                                            <strong style="font-size:16px" class="<?= $total_tarjeta_debito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_tarjeta_debito,2) ?></strong>
                                        <?php else: ?>
                                            <?php $total_tarjeta_debito =  CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_TARJETA_DEBITO, 20) ?>
                                            <strong style="font-size:16px" class="<?= $total_tarjeta_debito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_tarjeta_debito,2) ?></strong>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == 10): ?>
                                            <?php $total_deposito = CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_DEPOSITO, 10) ?>
                                            <strong style="font-size:16px" class="<?= $total_deposito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_deposito,2) ?></strong>
                                        <?php else: ?>
                                            <?php $total_deposito = CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_DEPOSITO, 20) ?>

                                            <strong style="font-size:16px" class="<?= $total_deposito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_deposito,2) ?></strong>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == 10): ?>
                                            <?php $total_credito = CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_CREDITO, 10)  ?>
                                            <strong style="font-size:16px" class="<?= $total_credito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_credito,2) ?></strong>
                                        <?php else: ?>
                                            <?php $total_credito = CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_CREDITO, 20); ?>

                                            <strong style="font-size:16px" class="<?= $total_credito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_credito,2) ?></strong>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($aperturaCaja["tipo"] == 10): ?>
                                            <?php $total_credito = CobroVenta::getTotalMetodoTpv($aperturaCaja["venta_id"], CobroVenta::COBRO_OTRO, 10)  ?>
                                            <strong style="font-size:16px" class="<?= $total_credito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_credito,2) ?></strong>
                                        <?php else: ?>
                                            <?php $total_credito = CobroVenta::getTotalMetodoTpv($aperturaCaja["token_pay"], CobroVenta::COBRO_OTRO, 20); ?>

                                            <strong style="font-size:16px" class="<?= $total_credito > 0 ? 'text-warning': '' ?>">$<?= number_format($total_credito,2) ?></strong>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center"><?= date("Y-m-d h:i a",$aperturaCaja["created_at"])?></td>
                                    <td class="text-center"><?= $aperturaCaja["created_by_user"] ?></td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

var VAR_ITEM = "<?= $model->id ?>";
var openTicketOperacion = function(token_pay){
    window.open("<?= Url::to(['imprimir-recibo']) ?>" + "?id=" + VAR_ITEM
                ,'imprimir', 'width=600,height=500');
}

var openReporteOperacion = function(token_pay){
    window.open("<?= Url::to(['imprimir-reporte']) ?>" + "?id=" + VAR_ITEM
                ,'imprimir', 'width=600,height=500');
}


var open_ticket = function(token_pay){
    window.open("<?= Url::to(['imprimir-credito']) ?>" + "?pay_items=" + token_pay
                ,'imprimir', 'width=600,height=500');
}

</script>