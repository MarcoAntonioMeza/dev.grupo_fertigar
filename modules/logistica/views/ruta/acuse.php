<?php
use yii\helpers\Html;
use app\models\Esys;
use app\models\reparto\Reparto;
use app\models\venta\Venta;
use app\models\reparto\RepartoDetalle;
?>
<table style="padding: 5px;margin:0; border-spacing: 0px" width="100%">
	<tr>
		<td width="20%"></td>
		<td width="80%" align="right">
			<p style="font-style: 9px"><strong>FECHA DE REPARTO: </strong><?= Esys::fecha_en_texto($model->created_at, true)  ?></p>
		</td>

	</tr>
	<tr>
		<td style=" padding: 10px;" align="left" width="40%" >
			<?= Html::img('@web/img/logo.png', ["height"=>"80px"]) ?>
		</td>
		<td colspan="2" align="left" style="#000; padding: 0px; border:0px; padding: 20px;" width="60%">
			<h4 style="font-size: 24px"><strong>Acuse</strong></h4>
			<h5>REPARTO: <strong><small>#<?= str_pad($model->id, 6 , "0",STR_PAD_LEFT) ?></small></strong></h5>
		</td>
	</tr>
</table>

<hr>
<table style=" border-spacing: 0px;" width="100%">
	<tr>
		<td style="padding: 10px" width="30%"><strong>RUTA / UNIDAD:</strong></td>
		<td width="70%" style="font-size: 12px;  padding: 10px; border-bottom: 2px; border-style: solid;   "><p ><?= $model->sucursal->nombre ?></p></td>
	</tr>

	<tr >
		<td style="padding: 10px" width="15%" ><strong>FECHA - CIERRE  / EMPLEADO : </strong></td>
		<td  width="80%" style="font-size: 12px; padding: 10px;border-bottom: 2px; border-style: solid;"><p> <?= $model->cierre_reparto ? Esys::fecha_en_texto($model->cierre_reparto, true) . '/'. $model->updatedBy->nombreCompleto  : ' PROCESO' ?> </p></td>
	</tr>
</table>



<table style=" border-spacing: 0px;margin-top:50px;  " width="100%">
	<tr>
		<td align="center" width="33.33%"><strong><?= Reparto::getCarga($model->id)   ?></strong></td>
		<td align="center" width="33.33%"><strong><?= Reparto::getPreventa($model->id) ?> </strong></td>
		<td align="center" width="33.33%"><strong><?= Reparto::getTaraAbierta($model->id) ?></strong></td>
	</tr>
	<tr>
		<td align="center" width="33.33%" style="font-size: 10px"># CARGA TOTAL</td>
		<td align="center" width="33.33%" style="font-size: 10px"># TOTAL DE PRECAPTURAS</td>
		<td align="center" width="33.33%" style="font-size: 10px"># TOTAL DE TARA ABIERTA</td>
	</tr>
</table>

<table style=" border-spacing: 0px;margin-top:50px;  " width="100%">
	<tr >
		<td align="center" width="33.33%"><strong><?= Reparto::getDevolucion($model->id)   ?></strong></td>
		<td align="center" width="33.33%"><strong><?= Reparto::getRecoleccion($model->id) ?> </strong></td>
		<td align="center" width="33.33%"><strong><?= Reparto::getFaltante($model->id) ?></strong></td>
	</tr>
	<tr>
		<td align="center" width="33.33%" style="font-size: 10px"># DEVOLUCION</td>
		<td align="center" width="33.33%" style="font-size: 10px"># RECOLECCION</td>
		<td align="center" width="33.33%" style="font-size: 10px"># PRODUCTO FALTANTE</td>
	</tr>
</table>

<hr>

<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
	<thead>
		<tr >
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">N°</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">VENTA #ID</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">PRODUCTO</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CLIENTE</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CANTIDAD</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">PROCESO</th>
		</tr>
	</thead>
	<tbody >
		<?php $count = 0; ?>

		<?php foreach ($model->repartoDetalles as $key => $repartoItem): ?>
			<?php $count++ ?>
			<?php if ($repartoItem->tipo == RepartoDetalle::TIPO_PRECAPTURA): ?>

			<tr style="border-style:  solid;border-width: 1px;border-spacing: 0px;">
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $count ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $repartoItem->ventaDetalle->venta_id  ?> </td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $repartoItem->ventaDetalle->producto->nombre ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= isset($repartoItem->ventaDetalle->venta->cliente_id ) ? $repartoItem->ventaDetalle->venta->cliente->nombreCompleto : 'N/A' ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $repartoItem->cantidad  ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $repartoItem->ventaDetalle->venta->status == Venta::STATUS_VENTA ? '** VENDIDO **' : '** PROCESO **' ?></td>
			</tr>

			<?php else: ?>
                <tr>
                    <td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $count ?></td>
                    <td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"> ** PRODUCTO EXTRA ** </td>
                    <td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?=   $repartoItem->producto->nombre  ?>  </td>
                    <td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"> N/A </td>
                    <td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?=   $repartoItem->cantidad  ?>     </td>
                    <td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"></td>
                </tr>
			<?php endif ?>
		<?php endforeach ?>
	</tbody>

</table>


<table style=" border-spacing: 0px;margin-top:50px;  " width="100%">
	<tr >
		<td align="center" width="50%"><strong><?= Reparto::getPzVendidas($model->id) ?></strong></td>
		<td align="center" width="50%"><strong>$<?= number_format(Reparto::getTotalVendido($model->id),2) ?></strong></td>
	</tr>
	<tr>
		<td align="center" width="50%" style="font-size: 10px"># PZ VENDIDAS</td>
		<td align="center" width="50%" style="font-size: 10px">TOTAL VENDIDO</td>
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
		<td align="center" width="40%" style="font-size: 10px">FIRMA DE GERENTE DE RUTA</td>
		<td width="20%"></td>
		<td align="center" width="40%" style="font-size: 10px">FIRMA DE GERENTE DE CEDIS</td>
	</tr>
</table>


