<?php
use yii\helpers\Html;
use app\models\Esys;
use app\models\venta\ViewVenta;
use app\models\producto\Producto;
?>
<table style="padding: 5px;margin:0; border-spacing: 0px" width="100%">
	<tr>
		<td width="20%"></td>
		<td width="80%" align="right">
			<p style="font-style: 9px"><strong>FECHA DE LISTA DE PEDIDOS: </strong><?= Esys::fecha_en_texto(time(), true)  ?></p>
		</td>

	</tr>
	<tr>
		<td style=" padding: 10px;" align="left" width="40%" >
			<?= Html::img('@web/img/logo.png', ["height"=>"80px"]) ?>
		</td>
		<td colspan="2" align="left" style="#000; padding: 0px; border:0px; padding: 20px;" width="60%">
			<h4 style="font-size: 24px"><strong>Reporte</strong></h4>
			<h5>SUCURSAL: <strong><small style="font-size: 16px"># CEDIS</small></strong></h5>
		</td>
	</tr>
</table>

<hr>

<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
	<thead>
		<tr >
			<th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">FOLIO</th>
			<th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">RUTA</th>
			<th align="center" width="15%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CANTIDAD SOLICITADA</th>
			<th align="center" width="20%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">PRODUCTO</th>
			<th align="center" width="20%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CLIENTE</th>
			<th align="center" width="15%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CANTIDAD ENTREGADA</th>
			<th align="center" width="15%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CANTIDAD INVENTARIO</th>
			<th align="center" width="5%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">U.M.</th>
		</tr>
	</thead>
	<tbody >
		<?php $count = 0; ?>
		<?php $tem_cliente_count = 0; ?>
		<?php $tem_row = 0; ?>
		<?php foreach (ViewVenta::getListaCompraProductoPreCaptura(3) as $key => $producto): ?>
			<?php if ($tem_cliente_count < 0 || $tem_cliente_count == $tem_row): ?>
				<?php $tem_cliente_count = ViewVenta::getCountPreventaCliente(3,$producto["cliente_id"]) ?>
				<?php $tem_row = 0; ?>
			<?php endif ?>
			<?php $tem_row++; ?>
			<tr style="border-style:  solid;border-width: 1px;border-spacing: 0px;">

				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;">#<?= str_pad($producto["folio"],6,"0",STR_PAD_LEFT) ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $producto["ruta"] ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $producto["total_producto"] ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $producto["producto"]  ?> </td>

				<?php if ($tem_row == 1): ?>
					<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;" rowspan="<?= $tem_cliente_count  > 1 ? $tem_cliente_count : ''?>"><?= $producto["cliente"]  ?> </td>
				<?php endif ?>

				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $producto["inventario"] ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= Producto::$medidaList[$producto["tipo_medida"]]  ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>

<h5 style="font-size: 24px"><strong>Totalizadores</strong></h5>

<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
	<thead>
		<tr >
			<th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">N°</th>
			<th align="center" width="40%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">PRODUCTO</th>
			<th align="center" width="20%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CANTIDAD TOTAL SOLICITADA</th>
			<th align="center" width="20%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CANTIDAD TOTAL INVENTARIO</th>
			<th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">UNIDAD DE MEDIDA</th>
		</tr>
	</thead>
	<tbody >
		<?php $count = 0; ?>

		<?php foreach (ViewVenta::getListaCompraProductoPreCapturaGroup(3) as $key => $producto): ?>
			<?php $count++ ?>

			<tr style="border-style:  solid;border-width: 1px;border-spacing: 0px; background: <?=  floatval($producto["total_producto"]) > floatval($producto["inventario"]) ? '#ed5565;':'' ?> ">
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $count ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $producto["producto"]  ?> </td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $producto["total_producto"] ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $producto["inventario"] ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= Producto::$medidaList[$producto["tipo_medida"]]  ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>




