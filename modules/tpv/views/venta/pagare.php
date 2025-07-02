<?php
use yii\helpers\Html;
use app\models\Esys;
use app\models\venta\Venta;
use app\models\credito\Credito;
use app\models\producto\Producto;
?>
<table style="padding: 5px;margin:0; border-spacing: 0px" width="100%">
	<tr>
		<td style=" padding: 10px;" align="left" width="20%" >
			<img src="http://erp.pescadosymariscosarroyo.com/web/img/logo.png" height="80px" alt="MARISCOS ARROYO">
		</td>
		<td colspan="2" align="center" style="#000; padding: 0px; border:0px; padding: 20px;" width="60%">
			<h4 style="font-size: 16px"><strong>PESCADOS Y MARISCOS ARROYO</strong></h4>
			<p style="font-size:12px">RAZON SOCIAL: PESCADOS Y MARISCOS ARROYOS SA DE CV R.F.C:  PMA2108068N6</p>
			<p style="font-size:12px">VERACRUZ , VERACRUZ DE IGNACIO DE LA LLAVE, COL. MIGUEL ALEMAN LOCAL 15 #91898</p>

		</td>
	</tr>
</table>

<table style=" border-spacing: 0px;" width="100%">
	<tr>
		<td width="50%">
			<p style="font-size:14px"><strong>CLIENTE:</strong> <?= $model->cliente_id ? $model->cliente->nombreCompleto : '** PUBLICO EN GENERAL **' ?></p>
			<h5><strong>Vendedor / Empleado:</strong> <?= $model->createdBy->nombreCompleto ?></h5>
		</td>
		<td width="50%">
			<p style="font-size:14px"> <strong>NOTA DE VENTA : #</strong> <?= $model->id ?></p>
			<p style="font-size:14px"> <strong>FECHA  : </strong> <?= date("Y-m-d",$model->created_at) ?></p>
			<h5><strong>Ruta:</strong> <?=  $model->ruta_sucursal_id ?  $model->sucursal->nombre : 'N/A' ?></h5>
		</td>
	</tr>
</table>



<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
	<thead>
		<tr >
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CANTIDAD</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">UNIDAD</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CLAVE</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">DESCRIPCION</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">% DESC</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">P/U</th>
			<th align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">IMPORTE</th>
		</tr>
	</thead>
	<tbody >
		<?php $count = 0; ?>

		<?php foreach ($model->ventaDetalle as $key => $ventaItem): ?>
			<tr>
				<td align="center" ><?= $ventaItem->cantidad ?></td>
				<td align="center" ><?= Producto::$medidaList[$ventaItem->producto->tipo_medida]  ?> </td>
				<td align="center" ><?= $ventaItem->producto->clave ?></td>
				<td align="center" ><?= $ventaItem->producto->nombre ?></td>
				<td align="center" >$ 00.00</td>
				<td align="center" >$ <?= number_format($ventaItem->precio_venta,2) ?></td>
				<td align="center" >$ <?= number_format($ventaItem->precio_venta * $ventaItem->cantidad ,2)?></td>
			</tr>
		<?php endforeach ?>
	</tbody>

</table>


<?php /* ?>
<table style="padding: 5px;margin:0; border-spacing: 0px" width="100%">
	<tr>
		<td width="50%" align="left"><h5 style="font-weight:bold">OBSERVACIONES</h5></td>
		<td width="50%" align="center" style="border-bottom-style:solid; border-width: 1px;">
			<table width="100%">
				<tr>
					<td width="50%">
						<p style="font-size:16px; font-weight: bold;">Subtotal</p>
					</td>
					<td width="50%">
						<p style="font-size:16px; font-weight: bold;">$<?= number_format($model->total,2) ?></p>
					</td>
				</tr>
				<tr>
					<td width="50%">
						<p style="font-size:16px; font-weight: bold;">Descuento</p>
					</td>
					<td width="50%">
						<p style="font-size:16px; font-weight: bold;">$ 00.00</p>
					</td>
				</tr>

				<tr>
					<td width="50%">
						<p style="font-size:16px; font-weight: bold;">Iva</p>
					</td>
					<td width="50%">
						<p style="font-size:16px; font-weight: bold;">$ 00.00</p>
					</td>
				</tr>

				<tr>
					<td width="50%" >
						<p style="font-size:16px; font-weight: bold;">TOTAL</p>
					</td>
					<td width="50%">
						<p style="font-size:16px; font-weight: bold;">$<?= number_format($model->total,2) ?></p>
					</td>
				</tr>
			</table>

		</td>
	</tr>
	<tr>
		<td></td>
		<td align="right"><p style="font-weight: bold;">PESOS 00/100 M.N</p></td>

	</tr>
</table>
<br>
<br>
<br>
<br>
<br>
*/?>

<table style="padding-bottom: 5px;margin:0; border-spacing: 0px;" width="100%">
	<tr>
		<td width="50%" align="right"><h5 style="font-weight:bold">TOTAL VENTA</h5></td>
		<td width="50%" align="center" style="border-bottom-style:solid; border-width: 2px;">
			 <p style="font-size:16px; font-weight: bold;">$<?= number_format($model->total,2) ?></p>
		</td>
	</tr>
	<tr>
		<td></td>
		<td align="right"><p style="font-weight: bold;">PESOS 00/100 M.N</p></td>
	</tr>

	<tr>
		<td width="50%" align="right"><h5 style="font-weight:bold">TOTAL CREDITO</h5></td>
		<td width="50%" align="center" style="border-bottom-style:solid; border-width: 2px;">
			 <p style="font-size:16px; font-weight: bold;">$<?= number_format(Credito::getTotaCreditoVenta($model->id),2) ?></p>
		</td>
	</tr>
	<tr>
		<td></td>
		<td align="right"><p style="font-weight: bold;">PESOS 00/100 M.N</p></td>
	</tr>
</table>




