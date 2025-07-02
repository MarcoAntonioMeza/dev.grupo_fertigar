<?php
use yii\helpers\Html;
use yii\helpers\Url;
use Da\QrCode\QrCode;
use app\models\venta\Venta;
use app\models\venta\VentaDetalle;
use app\models\sucursal\Sucursal;
use app\models\esys\EsysSetting;
use app\models\cobro\CobroVenta;
use app\models\Esys;
use app\models\producto\Producto;


$color      = "#OOOOOO";
$colorGris  = "#D8D8D8";


$qrCode = (new QrCode($id))
            ->setSize(100)
            ->setMargin(2)
            ->setErrorCorrectionLevel('medium');
$code = [];

$code['qrBase64'] =  $qrCode->writeDataUri();


$array_cliente_id   =  [];
$suma_asegurada     = 0;
$total_pieza        = 0;
$pesoPAQUETE        = 0;

$total_operacion    = 0;
foreach ($model as $key => $item_venta) {
    $total_operacion = $total_operacion + $item_venta->total;
}
?>
<body>
    <table style="font-size: 12px">
        <tr>
            <td colspan="2" align="center">
                <img src="http://erp.pescadosymariscosarroyo.com/web/img/logo2.png" height="150px" alt="MARISCOS ARROYO">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <h4 style="font-weight:bold; color: #000;">PESCADOS Y MARISCOS ARROYO</h4>
                <p style="color: #000;font-weight:bold; font-size: 13px;">PESCADOS Y MARISCOS ARROYOS SA DE CV, VERACRUZ DE IGNACIO DE LA LLAVE, VERACRUZ  91898</p>
                <p style="color: #000;font-weight:bold; font-size: 13px;">REGIMEN FISCAL : SOCIEDAD ANONIMA DE CAPITAL VARIABLE</p>
                <p style="color: #000;font-weight:bold;font-size: 13px;">PMA2108068N6</p>

            </td>
        </tr>
        <tr>
            <td colspan="2">
                <p style="color: #000; font-size: 14px;border-style: solid;border-width: 1px;"></p>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <h3 style="font-weight:bold; color: #000;"># <?= str_pad($id,6,"0",STR_PAD_LEFT) ?></h3>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <p style="color: #000; font-size: 14px;font-weight:bold;"><strong >FECHA:  </strong> <?= Esys::fecha_en_texto($model[0]->created_at,true) ?></p>
            </td>
        </tr>
        <tr>
            <td>
                <strong style="color: #000; font-size: 14px;">CAJERO:  </strong>
            </td>
            <td>
                <p style="color: #000; font-size: 14px;font-weight:bold;"> <?= isset($model[0]->updatedBy->nombreCompleto) ?$model[0]->updatedBy->nombreCompleto : $model[0]->createdBy->nombreCompleto ?></p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <p style="color: #000; font-size: 14px;border-style: solid;border-width: 1px;"></p>
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; font-size: 16px;">
                <p><strong style="font-weight: bold;color: #000;">CLIENTE: </strong></p>
            </td>
            <td style="color: #000; font-size: 14px;font-weight:bold;">
                <?= isset($model[0]->cliente->id) ?  $model[0]->cliente->nombreCompleto: 'PUBLICO EN GENERAL'  ?>
            </td>
        </tr>
    </table>
    <table width="100%">

    	<?php foreach ($model_detalle_venta as $key => $item): ?>
            <tr>
                <td style=" font-weight: bold; font-size: 14px; padding:  5px;" width="70%">
                    <p><strong style="font-weight: bold; color: #000;"><?= isset( $item->producto->id) ?  $item->producto->nombre : ''  ?> </strong></p>

                    <?php if ( $item->apply_bodega == VentaDetalle::APPLY_BODEGA_ON ): ?>
                        <p><strong style="font-weight: bold; color: #000;font-size:10px; ">*** ABASTECE BODEGA ***</strong></p>
                <?php endif ?>
                </td>
                <td  width="30%" align="right">
                    <p style="color: #000; font-size: 16px;font-weight: bold;">
                        $<?=   number_format($item->precio_venta * $item->cantidad,2)  ?>
                    </p>
                </td>
            </tr>

            <tr>
                <td style="font-weight: bold; padding:  5px;"  colspan="2">
                    <table width="80%" >
                        <tr>
                            <td width="50%" style="text-align:center;">
                                <p><strong style="font-weight: bold;font-size: 16px;color: #000;"><?= $item->cantidad   ?> </strong></p>
                            </td>
                            <td width="50%" style="text-align:center;">
                                <p><strong style="font-weight: bold;font-size: 16px;color: #000;"><?= number_format($item->precio_venta,2)   ?></strong></p>
                            </td>

                        </tr>
                        <tr>
                            <td width="50%" style="text-align:center;">
                                <strong style="font-weight: bold;font-size: 13px;color: #000;"><?= isset($item->producto->tipo_medida) ?  Producto::$medidaList[$item->producto->tipo_medida] : 'N/A' ?> </strong>
                            </td>
                            <td width="50%" style="text-align:center;">
                                <strong style="font-weight: bold;font-size: 13px;color: #000;"> P.U</strong>
                            </td>
                        </tr>
                    </table>
                </td>

            </tr>

    	<?php endforeach ?>
    </table>
    <table width="100%">
        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="right" style=" font-size: 16px; padding: 5px"><strong style="font-weight: bold;color:#000">TOTAL DE VENTA: $<?= number_format($total_operacion,2) ?></strong></td>
        </tr>
        <tr>
            <td colspan="2" align="center" style=" font-size: 16px; padding: 5px"><strong style="font-weight: bold;color:#000">RESUMEN</strong></td>
        </tr>

        <?php foreach ($model[0]->cobroTpvVenta as $key => $cobro): ?>
            <tr>
                <td width="80%" style="border: none; text-align: right;">

                    <p><strong style="font-size: 16px;color: #000;"><?= CobroVenta::$servicioTpvList[$cobro->metodo_pago] ?></strong></p>
                    <?php if ($cobro->metodo_pago == CobroVenta::COBRO_OTRO ): ?>
                        <p><strong style="font-size: 16px;color: #000;">CONCEPTO [ <?= $cobro->nota_otro ?> ]</strong></p>
                    <?php endif ?>

                    <?php if ($cobro->metodo_pago == CobroVenta::COBRO_TARJETA_DEBITO || $cobro->metodo_pago == CobroVenta::COBRO_TARJETA_CREDITO ): ?>
                        <p><strong style="font-size: 16px;color: #000;">CARGO EXTRA [ $<?= $cobro->cargo_extra ?> ]</strong></p>
                    <?php endif ?>
                    <?php if ($cobro->metodo_pago == CobroVenta::COBRO_EFECTIVO): ?>
                        <p><strong style="font-size: 16px;color: #000;">Pago con </strong></p>
                        <p><strong style="font-size: 16px;color: #000;">Cambio </strong></p>
                    <?php endif ?>
                </td>
                <br>
                <td width="20%" style="border: none; text-align: right;">
                   <p style="font-weight: bold; font-size: 16px; color: #000;">$<?= number_format($cobro->cantidad,2) ?>

                   </p>
                   <?php if ($cobro->metodo_pago == CobroVenta::COBRO_EFECTIVO): ?>
                        <p style="font-weight: bold; font-size: 16px; color: #000;">$<?= number_format($cobro->cantidad_pago,2) ?>
                        </p>
                        <p style="font-weight: bold; font-size: 16px; color: #000;">$<?= number_format($cobro->cantidad_pago-$cobro->cantidad,2) ?>
                        </p>
                    <?php endif ?>
                </td>
                <br>
            </tr>
        <?php endforeach ?>

        <?php if ($model[0]->transaccion): ?>
            <?php foreach ($model[0]->transaccion as $key => $item_transaccion): ?>
                <?php foreach (CobroVenta::getVentaRutaOperacion($item_transaccion->token_pay) as $key => $item_cobro): ?>
                    <tr>
                        <td width="80%" style="border: none; text-align: right;">
                            <p><strong style="font-size: 16px;color: #000;"><?= CobroVenta::$servicioTpvList[$item_cobro->metodo_pago] ?></strong></p>
                        </td>
                        <br>
                        <td width="20%" style="border: none; text-align: right;">
                           <p style="font-weight: bold; font-size: 16px; color: #000;">$<?= number_format($item_cobro->cantidad,2) ?></p>
                        </td>
                        <br>
                    </tr>
                <?php endforeach ?>
            <?php endforeach ?>
        <?php endif ?>
    </table>
    <table width="100%">


        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>

        <tr>
            <td colspan="2"></td>
        </tr>

        <tr>
          <td colspan="2" align="center" style="font-family: Georgia, serif; line-height: 15px; font-size:16px;">
                Consulta terminos y condiciones en <strong>http://www.mariscos-arroyo.com</strong>
          </td>
        </tr>

        <tr>
            <td colspan="2"><br><br><br><br></td>
        </tr>
        <tr>
            <td colspan="2">
                <hr>
            </td>
        </tr>

        <tr>
            <td colspan="2" align="center" style="font-size: 14px; color: #000;font-weight: bold;">Su pescado fresco
                <br>www.marisqueria-arroyo.com
            </td>
        </tr>
        <tr>
        </tr>
    </table>

</body>
