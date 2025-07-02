<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use app\assets\BootboxAsset;
use app\models\sucursal\Sucursal;
use app\models\apertura\AperturaCaja;
use app\models\esys\EsysSetting;
use app\models\cobro\CobroVenta;
use app\models\Esys;
use app\models\apertura\AperturaCajaDetalle;


$color      = "#OOOOOO";
$colorGris  = "#D8D8D8";
?>

<body>
    <table style="font-size: 12px" width="100%">
        <tr>
            <td colspan="2" align="center">

                <?=  Html::img('@web/img/logo2.png', ["height"=>"150px"]) ?>
            </td>
        </tr>
        <tr>
            <td style="  font-weight: bold; font-size: 16px; padding:  10px;">
                <p><strong style="font-weight: bold; color: #000;font-size: 16px;">VENDEDOR: </strong></p>
            </td>
            <td >
                <p style="font-weight: bold; color: #000;font-size: 16px;"><?= Yii::$app->user->identity->nombre ." ". Yii::$app->user->identity->apellidos ?></p>
            </td>
        </tr>
        <tr >
            <td class="text-center" style="border-style:solid; border-width: 1px">
                <p><strong style="font-weight: bold; color: #000;font-size: 14px;">APERTURA: </strong></p>
            </td>
            <td class="text-center" style="border-style:solid; border-width: 1px">
                <p style="font-weight: bold; color: #000;font-size: 14px;">CIERRE</p>
            </td>
        </tr>
        <tr >
            <td class="text-center" style="border-style:solid; border-width: 1px">
                <p><strong style="font-weight: bold; color: #000;font-size: 14px;"> <?= date("Y-m-d h:m:s", $model->created_at) ?> </strong></p>
            </td>
            <td class="text-center" style="border-style:solid; border-width: 1px">
                <p style="font-weight: bold; color: #000;font-size: 14px;"><?= $model->fecha_cierre ? date("Y-m-d h:m:s", $model->fecha_cierre) : 'N/A' ?></p>
            </td>
        </tr>

        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>
    </table>
    <table width="100%">
        <tr>
            <td>
                <table class="table-resumen">
                    <tr>
                        <td>* MONTO DE APERTURA </td>
                        <td>$<?= number_format($model->cantidad_caja,2) ?></td>
                    </tr>
                    <tr>
                        <td>+ VENTAS EN EFECTIVO </td>
                        <td>$<?= number_format(AperturaCaja::getTotalEfectivoTpv($model->id),2) ?></td>
                    </tr>
                    <tr>
                        <td>- TOTAL DE RETIROS </td>
                        <td> - $<?= number_format(AperturaCaja::getTotalRetiroTpv($model->id),2) ?></td>
                    </tr>
                    <tr>
                        <td>- TOTAL DE GASTOS </td>
                        <td> - $<?= number_format(AperturaCaja::getTotalGastoTpv($model->id),2) ?></td>
                    </tr>
                    <tr>
                        <td><br></td>
                    </tr>

                    <tr>
                        <td>
                    <tr>
                        <td >TIPO DE GASTO</td>
                        <td  >CANTIDAD</td>
                    </tr>
                    <?php foreach ($view_reporte as $key => $reporte): ?>

                        <tr>
                            <td > <?= $reporte["singular"]  ?> </td>
                            <td  >$ <?= number_format($reporte["cantidad"],2) ?></td>
                        </tr>

                    <?php endforeach ?>
                    </td>
                    </tr>
                    <tr >
                        <td height="20px"></td>
                        <td height="20px"></td>
                    </tr>
                    <tr>
                        <?php  $totalCaja = AperturaCaja::getTotalEfectivoTpv($model->id) - (AperturaCaja::getTotalRetiroTpv($model->id) + AperturaCaja::getTotalGastoTpv($model->id)) ?>
                        <td class="total">EFECTIVO EN CAJA</td>
                        <td>$<?=number_format( $totalCaja,2)?></td>
                    </tr>
                    <tr>
                        <td class="total">MONTO APERTURA</td>
                        <td>$<?=number_format( $model->cantidad_caja,2)?></td>
                    </tr>
                    <tr>
                        <td class="total">CIERRE CAJA</td>
                        <td>$<?=number_format($totalCaja +  $model->cantidad_caja )?></td>
                    </tr>

                </table>
            </td>
            <td>
                <table class="table-totales">
                    <tr>
                        <td>EFECTIVO</td>
                        <td>$<?= number_format(AperturaCaja::getTotalEfectivoTpv($model->id),2) ?></td>
                    </tr>
                    <?php if(AperturaCaja::getTotalTranferenciaTpv($model->id) > 0):?>
                    <tr>
                        <td>TRANFERENCIA</td>
                        <td>$ <?= number_format(AperturaCaja::getTotalTranferenciaTpv($model->id),2) ?></td>
                    </tr>
                    <?php endif?>
                    <tr>
                        <td>* OTROS </td>
                        <td> $<?= number_format(AperturaCaja::getTotalOtrosTpv($model->id),2) ?></td>
                    </tr>
                    <?php if(AperturaCaja::getTotalChequeTpv($model->id) > 0):?>
                    <tr>
                        <td>CHEQUE</td>
                        <td>$ <?= number_format(AperturaCaja::getTotalChequeTpv($model->id),2) ?></td>
                    </tr>
                    <?php endif?>
                    <?php if(AperturaCaja::getTotalTarjetaCreditoTpv($model->id) > 0):?>
                    <tr>
                        <td>TARJETA DE CREDITO</td>
                        <td>$ <?= number_format(AperturaCaja::getTotalTarjetaCreditoTpv($model->id),2) ?></td>
                    </tr>
                    <?php endif?>
                    <?php if(AperturaCaja::getTotalTarjetaDebitoTpv($model->id) > 0):?>
                    <tr>
                        <td>TARJETA DE DEBITO</td>
                        <td>$ <?= number_format(AperturaCaja::getTotalTarjetaDebitoTpv($model->id),2) ?></td>
                    </tr>
                    <?php endif?>
                    <?php if(AperturaCaja::getTotalDepositoTpv($model->id) > 0):?>
                    <tr>
                        <td>DEPOSITO</td>
                        <td>$ <?= number_format(AperturaCaja::getTotalDepositoTpv($model->id),2) ?></td>
                    </tr>
                    <?php endif?>
                    <?php if(AperturaCaja::getTotalCreditoPayTpv($model->id) > 0):?>
                    <tr>
                        <td>CREDITO</td>
                        <td>$ <?= number_format(AperturaCaja::getTotalCreditoPayTpv($model->id),2) ?></td>
                    </tr>
                    <?php endif?>
                    <tr>
                        <td><br></td>
                    </tr>
                    <tr>
                        <td class="total">VENTA TOTAL</td>
                        <td>$<?=number_format((AperturaCaja::getTotalEfectivoTpv($model->id)+
                            AperturaCaja::getTotalTranferenciaTpv($model->id)+
                            AperturaCaja::getTotalChequeTpv($model->id)+
                            AperturaCaja::getTotalTarjetaCreditoTpv($model->id)+
                            AperturaCaja::getTotalTarjetaDebitoTpv($model->id)+
                            AperturaCaja::getTotalDepositoTpv($model->id)+
                            AperturaCaja::getTotalCreditoPayTpv($model->id)),2)?></td>

                    </tr>

                </table>
            </td>
        </tr>

    </table>
</body>