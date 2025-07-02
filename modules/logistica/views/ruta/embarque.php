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
			<p style="font-style: 9px"><strong>FECHA DE REPARTO: </strong><?= Esys::fecha_en_texto(time(), true)  ?></p>
		</td>

	</tr>
	<tr>
		<td style=" padding: 10px;" align="left" width="40%" >
			<?= Html::img('@web/img/logo.png', ["height"=>"80px"]) ?>
		</td>
		<td colspan="2" align="left" style="#000; padding: 0px; border:0px; padding: 20px;" width="60%">
			<h4 style="font-size: 24px"><strong>Acuse</strong></h4>
			<h5>EMBARQUE: <strong><small style="font-size: 16px">#<?= $sucursal->nombre ?></small></strong></h5>
		</td>
	</tr>
</table>

<hr>

<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
	<thead>
		<tr >
			<th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">N°</th>
			<th align="center" width="60%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">PRODUCTO</th>
			<th align="center" width="15%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CANTIDAD</th>
			<th align="center" width="15%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">UNIDAD DE MEDIDA</th>
		</tr>
	</thead>
	<tbody >
		<?php $count = 0; ?>

		<?php foreach (ViewVenta::getProductoPreCaptura($sucursal->id, $embarque) as $key => $producto): ?>
			<?php $count++ ?>

			<tr style="border-style:  solid;border-width: 1px;border-spacing: 0px;">
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $count ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $producto["producto"]  ?> </td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $producto["total_producto"] ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= Producto::$medidaList[$producto["tipo_medida"]]  ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>

<h5><strong>TARA ABIERTA</strong></h5>
<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
	<thead>
		<tr >
			<th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">N°</th>
			<th align="center" width="60%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">PRODUCTO</th>
			<th align="center" width="15%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CANTIDAD</th>
			<th align="center" width="15%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">UNIDAD DE MEDIDA</th>
		</tr>
	</thead>
	<tbody >
		<?php $count = 0; ?>
		<?php for ($i=0; $i < 5; $i++) {  ?>
			<?php $count++ ?>
			<tr style="border-style:  solid;border-width: 1px;border-spacing: 0px;">
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; padding: 20px;"><?= $count ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; padding: 20px;"></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; padding: 20px;"></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px; padding: 20px;"></td>
			</tr>
		<?php } ?>
	</tbody>
</table>



