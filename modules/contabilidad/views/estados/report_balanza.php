<?php
use yii\helpers\Html;
use app\models\Esys;
use app\models\contabilidad\ContabilidadPoliza;
use app\models\contabilidad\ContabilidadTransaccion;
/* echo('<pre>');
print_r($report_totales);
echo('<pre>');
die() */
?>
<h3>BALANZA DE COMPROBACION DEL: <?=$report_totales['fecha_inicial']?>  AL  <?=$report_totales['fecha_final']?></h3>
<table class="table table-hover">
                    <thead style ="background-color:#dcdcdc">
                        <tr>
                        <th scope="col">CUENTA</th>
                        <th scope="col">CARGO</th>
                        <th scope="col">ABONO</th>
                        <th scope="col">DEUDOR</th>
                        <th scope="col">ACREEDOR</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($responseArray as $key => $cuenta):?>
                        <tr>
                        <td><?= $cuenta['cuenta']?></td>
                        <td>$<?= $cuenta['historico_cargo']?></td>
                        <td>$<?= $cuenta['historico_abono']?></td>
                        <td>$<?= $cuenta['deudor']?></td>
                        <td>$<?= $cuenta['acreedor']?></td>
                        </tr>
                        <?php endforeach;?>
                        <tr>
                        <td style="text-align: right; font-weight:bold;">TOTAL: </td>
                        <td style="font-weight:bold;">$<?= $report_totales['total_cargo']?></td>
                        <td style="font-weight:bold;">$<?= $report_totales['total_abono']?></td>
                        <td style="font-weight:bold;">$<?= $report_totales['total_deudor']?></td>
                        <td style="font-weight:bold;">$<?= $report_totales['total_acreedor']?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>