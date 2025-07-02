<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\esys\EsysSetting;
use app\models\cobro\CobroVenta;
use app\models\apertura\AperturaCaja;
use app\models\apertura\AperturaCajaDetalle;
use app\models\esys\EsysListaDesplegable;

$color      = "#OOOOOO";
$colorGris  = "#D8D8D8";

?>
<body>
    <table style="font-size: 12px" width="100%">
        <tr>
            <td colspan="2" align="center">
                <?= Html::img('@web/img/logo2.png', ["height"=>"150px"]) ?>
            </td>
        </tr>
        <tr>
            <td style="  font-weight: bold; font-size: 16px; padding:  10px;">
                <p><strong style="font-weight: bold; color: #000;font-size: 16px;">EMPLEADO: </strong></p>
            </td>
            <td >
                <p style="font-weight: bold; color: #000;font-size: 16px;"><?=  $model->createdBy->nombreCompleto  ?></p>
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
    	<?php foreach ($model->aperturaCajaDetalles as $key => $aperturaItem): ?>
            <tr>
                <td style="font-weight: bold;  padding:  5px;">
                    <strong style="font-weight: bold; color: #000;font-size: 12px;">
                        <?php $count_item = 0 ?>
                        <?php foreach (CobroVenta::getIsEfectivoTpv(($aperturaItem->tipo == AperturaCajaDetalle::TIPO_VENTA ? $aperturaItem->venta_id : $aperturaItem->token_pay),$aperturaItem->tipo) as $key => $item_pago): ?>
                            <?= ($count_item > 0 ? '&': '') . CobroVenta::$servicioList[$item_pago->metodo_pago]  ?>
                            <?php $count_item = $count_item + 1 ?>
                        <?php endforeach ?>
                        [ <?= AperturaCajaDetalle::$tipoList[$aperturaItem->tipo] ?><?= $aperturaItem->tipo == AperturaCajaDetalle::TIPO_VENTA ?  "- #". str_pad($aperturaItem->venta_id,6,"0",STR_PAD_LEFT) : '' ?>] </strong>


                </td>
                <td  class="text-center">
                    <?php if (AperturaCajaDetalle::isVigentePago(($aperturaItem->tipo == AperturaCajaDetalle::TIPO_VENTA ? $aperturaItem->venta_id : $aperturaItem->token_pay),$aperturaItem->tipo)): ?>
                        <p style="font-weight: bold; color: #000;font-size: 14px;">
                            $<?= number_format($aperturaItem->cantidad,2)   ?>
                        </p>
                        <?php else: ?>
                        <p style="font-weight: bold; color:#c82323;font-size: 14px;">
                           - $<?= number_format($aperturaItem->cantidad,2)   ?>
                        <small>CANCELADO</small>
                        </p>
                    <?php endif ?>
                </td>
            </tr>
            <?php if($aperturaItem->tipo==40): ?>
                <tr>
                    <td><?=   ($aperturaItem->tipo==40)? $aperturaItem->tipoGasto->singular : ' '?></td>
                </tr>
            <?php endif ?>
    	<?php endforeach ?>
    </table>
    <table width="100%">
        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center" style=" font-size: 16px; padding: 5px"><strong style="font-weight: bold; color:#000">CIERRE DE CAJA</strong></td>
        </tr>
    </table>

    <table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
        <tr >
            <td width="50%" align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;color:#000;font-weight: bold; font-size: 16px; ">EFECTIVO</td>
            <td width="50%" align="right" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">
                <table width="100%" style="border-spacing: 0px;margin:0; ">
                    <tr >
                        <td style="border-style:  solid;border-bottom-width: 1px;border-spacing: 0px; text-align: left; color:#000;font-weight: bold; font-size: 16px;">TOTAL BRUTO</td>
                        <td style="border-style:  solid;border-bottom-width: 1px;border-spacing: 0px; color:#000;font-weight: bold; font-size: 16px;">$<?= number_format(AperturaCaja::getTotalEfectivoTpv($model->id),2) ?></td>
                    </tr>
                    <tr >
                        <td style="border-style:  solid;border-bottom-width: 1px;border-spacing: 0px;text-align: left; color:#000;font-weight: bold; font-size: 16px;">MONTO APERTURADO</td>
                        <td style="border-style:  solid;border-bottom-width: 1px;border-spacing: 0px; color:#000;font-weight: bold; font-size: 16px;">$<?= number_format($model->cantidad_caja,2) ?></td>
                    </tr>
                    <tr >
                        <td style="border-style:  solid;border-bottom-width: 1px;border-spacing: 0px; text-align: left;color:#000;font-weight: bold; font-size: 16px;">RETIRO</td>
                        <td style="border-style:  solid;border-bottom-width: 1px;border-spacing: 0px;color:#000;font-weight: bold; font-size: 16px; "> - $<?= number_format(AperturaCaja::getTotalRetiroTpv($model->id),2) ?></td>
                    </tr>
                    <tr >
                        <td style="border-style:  solid;border-bottom-width: 1px;border-spacing: 0px; text-align: left;color:#000;font-weight: bold; font-size: 16px;">GASTOS</td>
                        <td style="border-style:  solid;border-bottom-width: 1px;border-spacing: 0px;color:#000;font-weight: bold; font-size: 16px; "> - $<?= number_format(AperturaCaja::getTotalGastoTpv($model->id),2) ?></td>
                    </tr>
                    <tr >
                        <td style="border-style:  solid;border-bottom-width: 1px;border-spacing: 0px; text-align: left;color:#000;font-weight: bold; font-size: 16px;">TOTAL DE EFECTIVO</td>
                        <td style="border-style:  solid;border-bottom-width: 1px;border-spacing: 0px; color:#000;font-weight: bold; font-size: 16px;">$<?= number_format(( AperturaCaja::getTotalEfectivoTpv($model->id) + $model->cantidad_caja) - AperturaCaja::getTotalRetiroTpv($model->id) ,2) ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr >
            <td width="50%" align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; color:#000; font-weight: bold; font-size: 16px;">CHEQUE</td>
            <td width="50%" align="right" style="border-style:  solid;border-width: 1px;border-spacing: 0px; color:#000; font-weight: bold; font-size: 16px;">$ <?= number_format(AperturaCaja::getTotalChequeTpv($model->id),2) ?></td>
        </tr>
        <tr >
            <td width="50%" align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; color:#000; font-weight: bold; font-size: 16px;">TRANSFERENCIA</td>
            <td width="50%" align="right" style="border-style:  solid;border-width: 1px;border-spacing: 0px; color:#000; font-weight: bold; font-size: 16px;">$ <?= number_format(AperturaCaja::getTotalTranferenciaTpv($model->id),2) ?></td>
        </tr >
        <tr >
            <td width="50%" align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;color:#000; font-weight: bold; font-size: 16px; ">TARJETA DE CREDITO</td>
            <td width="50%" align="right" style="border-style:  solid;border-width: 1px;border-spacing: 0px; color:#000; font-weight: bold; font-size: 16px;">$ <?= number_format(AperturaCaja::getTotalTarjetaCreditoTpv($model->id),2) ?></td>
        </tr >
        <tr >
            <td width="50%" align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;color:#000; font-weight: bold; font-size: 16px; ">TARJETA DE DEBITO</td>
            <td width="50%" align="right" style="border-style:  solid;border-width: 1px;border-spacing: 0px; color:#000; font-weight: bold; font-size: 16px;">$ <?= number_format(AperturaCaja::getTotalTarjetaDebitoTpv($model->id),2) ?></td>
        </tr >
        <tr >
            <td width="50%" align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;color:#000; font-weight: bold; font-size: 16px; ">DEPOSITO</td>
            <td width="50%" align="right" style="border-style:  solid;border-width: 1px;border-spacing: 0px;color:#000; font-weight: bold; font-size: 16px; ">$ <?= number_format(AperturaCaja::getTotalDepositoTpv($model->id),2) ?></td>
        </tr>
        <tr >
            <td width="50%" align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; color:#000; font-weight: bold; font-size: 16px;">CREDITO</td>
            <td width="50%" align="right" style="border-style:  solid;border-width: 1px;border-spacing: 0px; color:#000; font-weight: bold; font-size: 16px;">$ <?= number_format(AperturaCaja::getTotalCreditoPayTpv($model->id),2) ?></td>
        </tr >
        <tr >
            <td width="50%" align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; color:#000; font-weight: bold; font-size: 16px;">OTROS</td>
            <td width="50%" align="right" style="border-style:  solid;border-width: 1px;border-spacing: 0px; color:#000; font-weight: bold; font-size: 16px;">$ <?= number_format(AperturaCaja::getTotalOtrosTpv($model->id),2) ?></td>
        </tr >
    </table>

    <table width="100%">
        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center" style=" font-size: 16px; padding: 5px"><strong style="font-weight: bold;color:#000">RESUMEN DE OPERACION</strong></td>
        </tr>
    </table>


    <table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
        <tr >
            <td width="50%" align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; color:#000;font-weight: bold; font-size: 14px;">VENTA [EFECTIVO]</td>
            <td width="50%" align="right" style="border-style:  solid;border-width: 1px;border-spacing: 0px;color:#000;font-weight: bold; font-size: 14px;">$ <?= number_format(AperturaCaja::getTotalVentaTpv($model->id),2) ?></td>
        </tr>
        <tr >
            <td width="50%" align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;color:#000;font-weight: bold; font-size: 14px;">ABONOS [EFECTIVO]</td>
            <td width="50%" align="right" style="border-style:  solid;border-width: 1px;border-spacing: 0px;color:#000;font-weight: bold; font-size: 14px;">$ <?= number_format(AperturaCaja::getTotalCreditoTpv($model->id),2) ?></td>
        </tr>
        <tr >
            <td width="50%" align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;color:#000;font-weight: bold; font-size: 14px;">RETIRO [EFECTIVO]</td>
            <td width="50%" align="right" style="border-style:  solid;border-width: 1px;border-spacing: 0px; color:#000;font-weight: bold; font-size: 14px;">$ <?= number_format(AperturaCaja::getTotalRetiroTpv($model->id),2) ?></td>
        </tr >
    </table>

    <table width="100%">

        <tr>
            <td width="50%" style="border: none; text-align: center;">
                 <strong style="font-size: 16px;color:#000">CIERRE</strong>
            </td>
            <td width="50%" style="border: none; text-align: center;color:#000">
               <p style="font-size:16px; font-weight: bold;">$<?= number_format($model->total,2) ?></p>
            </td>
        </tr>
    </table>

    <br>
    <br>
    <br>
    <br>
    <br>

    <table  width="100%">
        <tr >
            <td align="center" width="40%" style="border-bottom-style:solid; border-width: 2px; "></td>
            <td width="20%"></td>
            <td align="center" width="40%" style="border-bottom-style:solid; border-width: 2px; "></td>
        </tr>
        <tr>
            <td align="center" width="40%" style="font-size: 14px;color:#000; font-weight: bold;">FIRMA DE EMPLEADO [ <?= $model->createdBy->nombreCompleto  ?> ] </td>
            <td width="20%"></td>
            <td align="center" width="40%" style="font-size: 14px;color:#000; font-weight: bold;">FIRMA DE GERENTE</td>
        </tr>
    </table>

</body>
