<?php
use yii\helpers\Html;
use app\models\Esys;
use app\models\reparto\Reparto;
use app\models\venta\Venta;
use app\models\reparto\RepartoDetalle;
use app\models\cobro\CobroVenta;

$resultPagosaCredito 	= CobroVenta::getVentaRutaCredito($model->id);
$resultVentaRuta 		= Venta::getVentaRuta($model->id);
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
			<h4 style="font-size: 24px"><strong>Cuenta</strong></h4>
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


<hr>

<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
	<thead>
		<tr >
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">VENTA #ID</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CLIENTE</th>
			
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">TOTAL</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">PAGO</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CREDITO</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">SALDO</th>
		</tr>
	</thead>
	<tbody >
		<?php $count = 0; ?>

		<?php foreach (Venta::getVentaRutaPublicoGeneral($model->id) as $key => $item_pub_general): ?>
			<tr style="border-style:  solid;border-width: 1px;border-spacing: 0px;">
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;">
					<strong>
						#<?= str_pad($item_pub_general->id,6,"0",STR_PAD_LEFT)  ?>
					</strong>
				</td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= 'PUBLICO EN GENERAL' ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;">0.00</td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;">0.00</td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;">$<?= number_format( $item_pub_general->total ,2) ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= number_format(  $item_pub_general->total ,2) ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;">NO</td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;">0.00</td>
			</tr>
		<?php endforeach ?>

		<?php foreach ($resultVentaRuta as $key => $item_venta): ?>
			<?php $count++ ?>
			<tr style="border-style:  solid;border-width: 1px;border-spacing: 0px;">
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;">
					<strong>
					<?php foreach (Venta::getVentaRutaFoliosCliente($model->id,$item_venta->cliente_id) as $keyFolio => $item_folio): ?>
						#<?= str_pad($item_folio->id,6,"0",STR_PAD_LEFT)  ?>
					<?php endforeach ?>
					</strong>
				</td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item_venta->cliente_id ?  $item_venta->cliente->nombreCompleto : 'PUBLICO EN GENERAL' ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;">$<?= number_format( Venta::getVentaRutaTotalCliente($model->id,$item_venta->cliente_id) ,2) ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= number_format(  Venta::getVentaRutaTotalCliente($model->id,$item_venta->cliente_id) - CobroVenta::getPagoCreditoVenta($item_venta->id),2) ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= CobroVenta::getIsPagoCredito($item_venta->id) ? '<strong>SI</strong>' : 'NO' ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= number_format(CobroVenta::getPagoCreditoVenta($item_venta->id),2) ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>


<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
	<thead>
		<tr >
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CREDITO #ID</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CLIENTE</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">TRANSFERENCIA</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CHEQUE</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">DEPOSITO</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">OTROS</th>
			<?php /* ?><th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">TOTAL DEL CREDITO</th>*/?>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">ABONO</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">SALDO</th>
		</tr>
	</thead>
	<tbody >
		<?php foreach ($resultPagosaCredito as $key => $item_credito): ?>
			<tr style="border-style:  solid;border-width: 1px;border-spacing: 0px;">
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item_credito["credito_ids"]  ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item_credito["cliente"] ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item_credito["transferencia"] ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item_credito["cheque"] ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item_credito["deposito"] ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item_credito["otro"] ?></td>
				<?php /* ?><td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;">$<?= number_format($item_credito["total_credito"],2) ?></td>*/?>

				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;">$<?= number_format($item_credito["total_pago"],2) ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;">$<?= number_format($item_credito["total_por_pagado"],2) ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>


<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="80%">
	<tr >
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 25%;">EFECTIVO [VENTA]</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionMetodo($model->id, CobroVenta::COBRO_EFECTIVO)) ?></td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 25%;">EFECTIVO [ABONOS]</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionCreditoMetodo($model->id, CobroVenta::COBRO_EFECTIVO)) ?></td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 20%;">TOTAL</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionMetodo($model->id, CobroVenta::COBRO_EFECTIVO) + CobroVenta::getTotalOperacionCreditoMetodo($model->id, CobroVenta::COBRO_EFECTIVO)) ?></td>
	</tr>
	<tr >
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 25%;">CHEQUE [VENTAS]</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionMetodo($model->id, CobroVenta::COBRO_CHEQUE)) ?></td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 25%;">CHEQUE [ABONOS]</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionCreditoMetodo($model->id, CobroVenta::COBRO_CHEQUE)) ?></td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 20%;">TOTAL</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionMetodo($model->id, CobroVenta::COBRO_CHEQUE) + CobroVenta::getTotalOperacionCreditoMetodo($model->id, CobroVenta::COBRO_CHEQUE)) ?></td>
	</tr>
	<tr >
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 25%;">TRANSFERENCIA [VENTA]</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionMetodo($model->id, CobroVenta::COBRO_TRANFERENCIA)) ?></td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 25%;">TRANSFERENCIA [ABONOS]</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionCreditoMetodo($model->id, CobroVenta::COBRO_TRANFERENCIA)) ?></td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 20%;">TOTAL</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionMetodo($model->id, CobroVenta::COBRO_TRANFERENCIA) + CobroVenta::getTotalOperacionCreditoMetodo($model->id, CobroVenta::COBRO_TRANFERENCIA)) ?></td>
	</tr >
	<tr >
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 25%;">TARJETA DE CREDITO [VENTA]</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionMetodo($model->id, CobroVenta::COBRO_TARJETA_CREDITO)) ?></td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 25%;">TARJETA DE CREDITO [ABONOS]</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionCreditoMetodo($model->id, CobroVenta::COBRO_TARJETA_CREDITO)) ?></td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 20%;">TOTAL</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionMetodo($model->id, CobroVenta::COBRO_TARJETA_CREDITO) + CobroVenta::getTotalOperacionCreditoMetodo($model->id, CobroVenta::COBRO_TARJETA_CREDITO)) ?></td>
	</tr >
	<tr >
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">TARJETA DE DEBITO [VENTAS]</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">$ <?= number_format(CobroVenta::getTotalOperacionMetodo($model->id, CobroVenta::COBRO_TARJETA_DEBITO)) ?></td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 25%;">TARJETA DE DEBITO [ABONOS]</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionCreditoMetodo($model->id, CobroVenta::COBRO_TARJETA_DEBITO)) ?></td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 20%;">TOTAL</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionMetodo($model->id, CobroVenta::COBRO_TARJETA_DEBITO) + CobroVenta::getTotalOperacionCreditoMetodo($model->id, CobroVenta::COBRO_TARJETA_DEBITO)) ?></td>
	</tr >
	<tr >
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">DEPOSITO [VENTAS]</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">$ <?= number_format(CobroVenta::getTotalOperacionMetodo($model->id, CobroVenta::COBRO_DEPOSITO)) ?></td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 25%;">DEPOSITO [ABONOS]</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionCreditoMetodo($model->id, CobroVenta::COBRO_DEPOSITO)) ?></td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 20%;">TOTAL</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; width: 10%;">$ <?= number_format(CobroVenta::getTotalOperacionMetodo($model->id, CobroVenta::COBRO_DEPOSITO) + CobroVenta::getTotalOperacionCreditoMetodo($model->id, CobroVenta::COBRO_DEPOSITO)) ?></td>
	</tr>
	<tr >
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CREDITO [VENTAS]</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">$ <?= number_format(CobroVenta::getTotalOperacionMetodo($model->id, CobroVenta::COBRO_CREDITO)) ?></td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">OTROS [ABONOS]</td>
		<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">$ <?= number_format(CobroVenta::getTotalOperacionCreditoMetodo($model->id, CobroVenta::COBRO_OTRO)) ?></td>
	</tr >
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


