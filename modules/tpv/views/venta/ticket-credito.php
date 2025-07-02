<?php
use yii\helpers\Html;
use yii\helpers\Url;
use Da\QrCode\QrCode;
use app\models\venta\Venta;
use app\models\venta\VentaDetalle;
use app\models\sucursal\Sucursal;
use app\models\esys\EsysSetting;
use app\models\cobro\CobroVenta;
use app\models\credito\Credito;

$color      = "#OOOOOO";
$colorGris  = "#D8D8D8";

$total_pagado   = 0;
$total_abonado  = 0;
$total_credito  = 0;
$itemID         = 0;


?>
<body>
    <table style="font-size: 12px">
        <tr>
            <td colspan="2" align="center">
                <img src="http://erp.pescadosymariscosarroyo.com/web/img/logo2.png" height="150px" alt="MARISCOS ARROYO">
            </td>
        </tr>
        <tr>
            <td  colspan="2" style="   color: black; font-size: 24px; text-align: center;font-weight: bold; ">
                <p>ABONO A CREDITO</p>
            </td>
        </tr>


        <?php foreach ($model as $key => $item): ?>

            <tr style="padding: 0">
                <td colspan="2" style="padding: 0">
                    <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
                </td>
            </tr>

            <tr>
                <td style="font-weight: bold;  padding:  5px;">
                    <p><strong style="font-weight: bold;font-size: 14px; color: #000;">FECHA </strong></p>
                </td>
                <td style="  color: black; font-size: 12px;text-align: right;">
                    <p style="font-size: 14px;font-weight: bold;">
                        <?= date('Y-m-d')  ?>
                    </p>
                </td>
            </tr>
            <tr>
                <td style=" font-weight: bold;  padding:  5px;">
                    <p><strong style="font-weight: bold;font-size: 14px;color: #000;">CREDITO FOLIO:</strong></p>
                </td>
                <td style="  color: black; font-size: 12px;text-align: right;">
                    <p style="font-size: 14px;font-weight: bold;">
                        #<?= str_pad($item["credito_id"],6,"0",STR_PAD_LEFT)  ?>
                    </p>
                </td>
            </tr>
            <?php if($item["credito"]["tipo"] == 10): ?>
                <tr>
                    <td style=" font-weight: bold;  padding:  5px;">
                        <p><strong style="font-weight: bold;font-size: 14px; color: #000;">CLIENTE </strong></p>
                    </td>
                    <td style="  color: black; font-size: 12px;text-align: right;">
                        <p style="font-size: 14px;font-weight: bold;">
                            <?= $item["credito"]->venta ? $item["credito"]->venta->cliente->nombre.' '.$item["credito"]->venta->cliente->apellidos : $item["credito"]->cliente->nombre.' '.$item["credito"]->cliente->apellidos ?>
                            <?php $itemID = $item["credito"]->venta ? $item["credito"]->venta->cliente->id  : $item["credito"]->cliente->id ?>
                        </p>
                    </td>
                </tr>
            <?php endif ?>
            <?php if($item["credito"]["tipo"] == 20): ?>
                <tr>
                    <td style=" font-weight: bold;  padding:  5px;">
                        <p><strong style="font-weight: bold;font-size: 14px; color: #000;">PROVEEDOR </strong></p>
                    </td>
                    <td style="  color: black; font-size: 12px;text-align: right;">
                        <p style="font-size: 14px;font-weight: bold;">
                            <?php if ( $item["credito"]->proveedor_id): ?>
                                <?= $item["credito"]->proveedor->nombre//$item["credito"]//->compra->proveedor->nombre  ?>
                                <?php $itemID  = $item["credito"]->proveedor_id; ?>
                            <?php else: ?>
                                <?= $item["credito"]->compra->proveedor->nombre  ?>
                                <?php $itemID  = $item["credito"]->compra->proveedor->id;  ?>
                            <?php endif ?>
                        </p>
                    </td>
                </tr>
            <?php endif ?>
            <tr>
                <td style=" font-weight: bold; font-size: 9px; padding:  5px;">
                    <p><strong style="font-weight: bold; font-size: 14px; color: #000;">NOTA DE CREDITO</strong></p>
                </td>
                <td style="  color: black; font-size: 12px;text-align: right;">
                    <p style="font-size: 14px;font-weight: bold;">
                        $ <?=   number_format($item["cantidad_credito"], 2)  ?>
                    </p>
                </td>
            </tr>
            <tr>
                <td style=" font-weight: bold;  padding:  5px;">
                    <p><strong style="font-weight: bold;font-size: 14px; color: #000;">ABONADO NOTA DE CREDITO</strong></p>
                </td>
                <td style="  color: black; font-size: 12px;text-align: right;">
                    <p style="font-size: 14px;font-weight: bold;">
                        $ <?=   number_format($item["total_abonado"], 2)  ?>
                    </p>
                </td>
            </tr>
            <tr>
                <td style=" font-weight: bold; font-size: 9px; padding:  5px;">
                    <p><strong style="font-weight: bold; font-size: 14px;color: #000;">FOLIO DE VENTA</strong></p>
                </td>
                <td style="  color: black; font-size: 12px;text-align: right;">
                    <p style="font-size: 14px;font-weight: bold;">
                        #<?=  str_pad($item["venta"],6,"0",STR_PAD_LEFT)    ?>
                    </p>
                </td>
            </tr>

            <?php $total_abonado    = $total_abonado + floatval($item["total_abonado"]) ?>
            <?php $total_credito    = $total_credito + floatval($item["cantidad_credito"]) ?>
        <?php endforeach ?>

        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center" style=" font-size: 16px; padding: 5px"><strong style="font-weight: bold;color: #000;">RESUMEN</strong></td>
        </tr>
        <?php foreach (CobroVenta::getTokenPayAll($token) as $key => $cobro): ?>
            <tr>
                <td width="80%" style="border: none; text-align: right;">
                    <p><strong style="font-size: 16px;color: #000;"><?= CobroVenta::$servicioListAll[$cobro->metodo_pago] ?></strong></p>
                      <?php if ($cobro->metodo_pago == CobroVenta::COBRO_OTRO ): ?>
                        <p><strong style="font-size: 12px;color: #000;">[ <?= $cobro->nota_otro ?> ]</strong></p>
                    <?php endif ?>
                </td>
                <br>
                <td width="20%" style="border: none; text-align: right;">
                   <p style="font-weight: bold; font-size: 16px; color: #000;">$<?= number_format($cobro->cantidad,2) ?></p>
                </td>
                <br>
            </tr>

        <?php endforeach ?>
        <tr>
            <td width="50%" style="border: none; text-align: center;">
                 <small><strong style="font-size: 14px; color: #000;">TOTAL ABONADO</strong></small>
            </td>
            <td width="50%" style="border: none; text-align: center;">
               <p style="font-size: 14px;color: #000;">$<?= number_format(CobroVenta::getTotalAbonado($token),2) ?></p>
            </td>
        </tr>
        <tr>
            <td width="50%" style="border: none; text-align: center;">
                 <small><strong style="font-size: 14px;color: #000;">SALDO ACTUAL</strong></small>
            </td>
            <td width="50%" style="border: none; text-align: center;">
               <p style="font-size: 14px;color: #000;">$<?= number_format((Credito::getTotalesCredito($itemID,$item["credito"]["tipo"])["total_por_pagar"]), 2 ) ?></p>
            </td>
        </tr>

        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>

        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
          <td colspan="2" align="center" style="font-family: Georgia, serif; line-height: 15px;font-size: 12px;">
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
            <td colspan="2" align="center" style="font-size: 12px">Su pescado fresco
                <br>www.marisqueria-arroyo.com
            </td>
        </tr>
        <tr>
        </tr>
    </table>

</body>
