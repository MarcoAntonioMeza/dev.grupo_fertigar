<?php
use yii\helpers\Html;
use yii\helpers\Url;
use Da\QrCode\QrCode;
use app\models\venta\Venta;
use app\models\venta\VentaDetalle;
use app\models\sucursal\Sucursal;
use app\models\esys\EsysSetting;


$color      = "#OOOOOO";
$colorGris  = "#D8D8D8";


$qrCode = (new QrCode($model->id))
            ->setSize(100)
            ->setMargin(2)
            ->setErrorCorrectionLevel('medium');
$code = [];

$code['qrBase64'] =  $qrCode->writeDataUri();


$array_cliente_id   =  [];
$suma_asegurada     = 0;
$total_pieza        = 0;
$pesoPAQUETE        = 0;
?>
<body>
    <table style="font-size: 12px">
    	<tr>
    		<td colspan="2" align="center">
        		<h5><strong>DEVOLUCIÓN</strong></h5>
        	</td>
    	</tr>
        <tr>
            <td colspan="2" align="center">
                <img src="http://erp.pescadosymariscosarroyo.com/web/img/logo.png" height="150px" alt="MARISCOS ARROYO">
            </td>
        </tr>
        <tr>
            <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 16px; padding:  10px;">
                <p><strong style="font-weight: bold;">CLIENTE: </strong></p>
            </td>
            <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 14px;">
                <?= isset($model->venta->cliente->id) ?  $model->venta->cliente->nombreCompleto: 'PUBLICO EN GENERAL'  ?></p>
            </td>
        </tr>

        <tr>
            <td style="background-color:<?php echo $color; ?>;  color: white; padding:  10px; font-size: 14px;">
                <p><strong style="font-weight: bold;">SUCURSAL QUE RECIBE: </strong></p>
            </td>
            <td style="background-color:<?php echo $colorGris; ?>;  color: black;font-size: 14px;">
                <p> [<?= $model->almacenSucursal->id ?>] <?= $model->almacenSucursal->nombre  ?></p>
            </td>
        </tr>

        <?php /* ?>
            <tr>
                <td align='center' colspan="2" style="background-color:<?php echo $color; ?>;  color: white;">
                    <strong style="font-size: 14px;">Paquetes del Envío</strong>
                </td>
            </tr>
            <tr>
                <td colspan="2"></td>
            </tr>*/
            ?>

    	<?php foreach ($model->operacionDetalles as $key => $item): ?>

            <tr style="padding: 0">
                <td colspan="2" style="padding: 0">
                    <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
                </td>
            </tr>

            <tr>
                <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 12px; padding:  5px;">
                    <p><strong style="font-weight: bold;">Producto </strong></p>
                </td>
                <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 12px;">
                    <p>
                        <?= isset( $item->producto->id) ?  $item->producto->nombre : ''  ?>
                    </p>
                </td>
            </tr>
            <tr>
                <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 12px; padding:  5px;">
                    <p><strong style="font-weight: bold;">Cantidad </strong></p>
                </td>
                <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 12px;">
                    <p>
                        <?=   $item->cantidad  ?>
                    </p>
                </td>
            </tr>
            <tr>
                <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 12px; padding:  5px;">
                    <p><strong style="font-weight: bold;">Precio </strong></p>
                </td>
                <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 12px;">
                    <p>
                        $<?=   number_format($item->costo,2)  ?>
                    </p>
                </td>
            </tr>
    	<?php endforeach ?>

        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center" style=" font-size: 16px; padding: 5px"><strong style="font-weight: bold;">RESUMEN</strong></td>
        </tr>

        <tr>
            <td width="50%" style="border: none; text-align: center;">
                 <small><strong style="font-size: 9px;">TOTAL REEMBOLSO</strong></small>
            </td>
            <td width="50%" style="border: none; text-align: center;">
               <p><?= $model->venta_reembolso_cantidad ?></p>
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
            <td colspan="2" align="center" style="font-size: 10px">Su pescado fresco
                <br>www.marisqueria-arroyo.com
            </td>
        </tr>
        <tr>
        </tr>
    </table>

</body>
