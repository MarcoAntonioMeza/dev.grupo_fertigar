<?php
use yii\helpers\Html;
use app\models\Esys;
use app\models\producto\Producto;
use app\models\inv\InventarioOperacion;
?>
<table style="padding: 5px;margin:0; border-spacing: 0px" width="100%">
	<tr>
		<td width="20%"></td>
		<td width="80%" align="right">
			<p style="font-style: 9px"><strong>FECHA DE AJUSTE DE INVENTARIO: </strong><?= Esys::fecha_en_texto(time(), true)  ?></p>
		</td>

	</tr>
	<tr>
		<td style=" padding: 10px;" align="left" width="40%" >
			<img src="http://erp.pescadosymariscosarroyo.com/web/img/logo.png" height="80px" alt="MARISCOS ARROYO">
		</td>
		<td colspan="2" align="left" style="#000; padding: 0px; border:0px; padding: 20px;" width="60%">
			<h4 style="font-size: 24px"><strong>AJUSTE DE INVENTARIO</strong></h4>
			<h5>SUCURSAL: <strong><small style="font-size: 16px"># <?= $model->inventarioSucursal->nombre ?></small></strong></h5>
		</td>
	</tr>
</table>



<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
	<thead>
		<tr >
			<th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">#</th>
			<th align="center" width="40%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">PRODUCTO</th>
			<th align="center" width="50%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CANTIDAD [INVENTARIO]</th>
		</tr>
	</thead>
	<tbody >
		<?php $count = 1; ?>
		<?php if ($model->tipo == InventarioOperacion::TIPO_AJUSTE_PARCIAL): ?>
			<?php foreach ($model->inventarioOperacionDetalles as $key => $item_detail): ?>
				<tr style="border-style:  solid;border-width: 1px;border-spacing: 0px;">
					<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $count ?></td>
					<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item_detail->producto->nombre ?></td>
					<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"></td>
				</tr>
				<?php $count = $count + 1; ?>
			<?php endforeach ?>
		<?php else: ?>
			<?php foreach (InventarioOperacion::getProductoInventario($model->inventario_sucursal_id) as $key => $inv): ?>
				<tr style="border-style:  solid;border-width: 1px;border-spacing: 0px;">

					<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $count ?></td>
					<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $inv->producto->nombre ?></td>
					<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"></td>
				</tr>
				<?php $count = $count + 1; ?>
			<?php endforeach ?>
		<?php endif ?>

	</tbody>
</table>
