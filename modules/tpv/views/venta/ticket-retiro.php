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
use app\models\apertura\AperturaCajaDetalle;


$color      = "#OOOOOO";
$colorGris  = "#D8D8D8";

$total_pagado = 0;
$total_abonado = 0;
$total_credito = 0;



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
                <p><?= AperturaCajaDetalle::$tipoList[$model->tipo]?></p>
            </td>
        </tr>


            <tr style="padding: 0">
                <td colspan="2" style="padding: 0">
                    <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
                </td>
            </tr>

            <tr>
                <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold;  padding:  5px;">
                    <p><strong style="font-weight: bold;font-size: 14px;">FECHA </strong></p>
                </td>
                <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 12px;text-align: center;">
                    <p style="font-size: 14px;font-weight: bold;">
                        <?= Esys::unixTimeToString($model->created_at);?>
                    </p>
                </td>
            </tr>
            <tr>
                <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold;  padding:  5px;">
                    <p><strong style="font-weight: bold;font-size: 14px;">HORA </strong></p>
                </td>
                <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 12px;text-align: center;">
                    <p style="font-size: 14px;font-weight: bold;">
                        <?= date('h:i:s a', $model->created_at);?>
                    </p>
                </td>
            </tr>

            <tr>
                <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 9px; padding:  5px;">
                    <p><strong style="font-weight: bold; font-size: 14px;">CANTIDAD</strong></p>
                </td>
                <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 12px;text-align: center;">
                    <p style="font-size: 14px;font-weight: bold;">
                        $<?= number_format($model->cantidad,2) ?>
                    </p>
                </td>
            </tr>


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
                 <small><strong style="font-size: 14px; color: #000;">RETIRO</strong></small>
            </td>
            <td width="50%" style="border: none; text-align: center;">
               <p style="font-size: 14px;">$<?= number_format($model->cantidad,2) ?></p>
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
        <?php /* ?>
        <tr style="font-size: 8px">
            <td colspan="2" align='justify' style="font-family: Georgia, serif; line-height: 15px">
                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Ex ipsam ad possimus totam ipsum aliquid sunt, facere fuga dicta odit atque culpa voluptatibus iusto error, cumque perspiciatis eos deserunt itaque.
                Deserunt asperiores, laboriosam, dolorum quo nostrum blanditiis labore voluptatibus, pariatur ex optio facilis ea in. Odit, quae! Labore reprehenderit asperiores illo possimus ducimus magnam cupiditate atque nemo amet maxime! Nihil.
                <br>
                <br>

                <strong>Lorem ipsum dolor, sit, amet consectetur adipisicing elit. Quidem debitis iusto et, esse iure fugiat earum perspiciatis</strong>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Ex laborum et numquam animi aliquam molestiae vitae similique tempore possimus nesciunt dolore eaque quidem aperiam quibusdam nam enim, a ipsum aliquid!
                Necessitatibus quisquam neque, eveniet, ipsam fugit quidem dignissimos voluptas repellendus officiis vel fuga molestias culpa doloribus, laboriosam. Deleniti officiis non, architecto pariatur dolores vel qui aspernatur ea consequatur excepturi consectetur.
                <br/>
                <br/>
                <br/>
            </td>
        </tr>
        php*/?>
        <!--<tr>
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
        </tr>-->
        <tr>
        </tr>
    </table>

</body>
