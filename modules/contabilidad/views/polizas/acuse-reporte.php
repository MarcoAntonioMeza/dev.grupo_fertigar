<?php
use yii\helpers\Html;
use app\models\Esys;
use app\models\contabilidad\ContabilidadPoliza;
use app\models\contabilidad\ContabilidadTransaccion;

?>

<table style="padding-top: 15px;margin:0; border-spacing: 0px" width="100%">
    <tr>
        <td width="20%">
            <?= Html::img('@web/img/logo.png', ["style"=>" width:80px; height:80px"]) ?>
        </td>
        <td width="80%" align="right">
            <p style="font-style: 9px"><strong>FECHA DE OPERACION: </strong><?= Esys::fecha_en_texto($model->created_at, true)  ?></p>
        </td>

    </tr>
    <tr>

        <td colspan="2" align="left" style= "padding: 0px; border:0px; padding: 20px;" width="60%">
            <h4 style="font-size: 24px"><strong>REPORTE DE POLIZAS</strong></h4>
        </td>
    </tr>
</table>

<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
    <thead>
        <tr>
            <th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CUENTA</th>
            <th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">REFERENCIA</th>
            <th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CONCEPTO</th>
            <th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">DEBE</th>
            <th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">HABER</th>
        </tr>
    </thead>
    <tbody>
        <?php $totalDebe = $totaHaber = 0; ?>
        <?php foreach ($model->contabilidadPolizaVerificacionDetails as $key => $item_detail): ?>
            <?php foreach (ContabilidadTransaccion::getConfigContable($item_detail->contabilidad_poliza_id) as $key => $item_configuracion): ?>
                <tr>
                    <td class="text-center"><p style="font-size:12px"><?= $item_configuracion["cuenta"] ?> [<?= $item_configuracion["cuenta_numero"] ?>]</p></td>
                    <td class="text-center"><p style="font-size:12px">#<?= str_pad($item_detail->contabilidadPoliza->id,6,"0",STR_PAD_LEFT) ?></p></td>
                    <td class="text-center"><p style="font-size:12px"><?= $item_detail->contabilidadPoliza->tipo == ContabilidadPoliza::TIPO_SISTEMA ?  ( ContabilidadTransaccion::$tipoList[$item_detail->contabilidadPoliza->pertenece] ) : 'POLIZA MANUAL' ?> - <?= date("d/m/Y",$item_configuracion["created_at"])  ?>  <?= $item_detail->contabilidadPoliza->tipo == ContabilidadPoliza::TIPO_SISTEMA ? ContabilidadTransaccion::$motivoList[$item_configuracion["motivo"]] : ''  ?> - <?= $item_detail->contabilidadPoliza->concepto ?> </p></td>
                    <td class="text-center"><p style="font-size:12px; font-weight: bold;"><?=  $item_detail->contabilidadPoliza->tipo == ContabilidadPoliza::TIPO_SISTEMA ?  number_format(($item_detail->contabilidadPoliza->total * $item_configuracion["cargo"]) / 100,2) : number_format( $item_configuracion["cargo"],2 ) ?>MXN</p></td>
                    <td class="text-center"><p style="font-size:12px; font-weight: bold;"><?= $item_detail->contabilidadPoliza->tipo == ContabilidadPoliza::TIPO_SISTEMA ? number_format( ($item_detail->contabilidadPoliza->total * $item_configuracion["abono"] )  / 100,2) : number_format( $item_configuracion["abono"],2 ) ?>MXN</p></td>
                </tr>
                <?php $totalDebe = $totalDebe +   ( $item_detail->contabilidadPoliza->tipo == ContabilidadPoliza::TIPO_SISTEMA  ? ($item_detail->contabilidadPoliza->total * floatval($item_configuracion["cargo"]) ) / 100 : floatval($item_configuracion["cargo"]) );  ?>
                <?php $totaHaber = $totaHaber + ( $item_detail->contabilidadPoliza->tipo == ContabilidadPoliza::TIPO_SISTEMA  ?  ($item_detail->contabilidadPoliza->total * floatval($item_configuracion["abono"]) )  / 100 : floatval($item_configuracion["abono"]) );  ?>
            <?php endforeach ?>
        <?php endforeach ?>
    </tbody>
    <tfoot>
        <tr>
            <td class="text-right" colspan="3">
                <p  style=" font-size: 18px;font-weight: 700; color: #000">TOTAL</p>
            </td>
            <td class="text-center">
                <p  class="text-primary" style=" font-size: 18px;"><?= number_format($totalDebe,2) ?>MXN</p>
            </td>
            <td class="text-center">
                <p  class="text-primary" style=" font-size: 18px;"><?= number_format($totaHaber,2) ?>MXN</p>
            </td>
        </tr>
    </tfoot>
</table>