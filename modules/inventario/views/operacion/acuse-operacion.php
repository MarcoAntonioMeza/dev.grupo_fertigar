<?php
use yii\helpers\Html;
use app\models\Esys;
use app\models\producto\Producto;
use app\models\inv\InvProductoSucursal;
use app\models\inv\InventarioOperacion;
?>
<table style="padding: 5px;margin:0; border-spacing: 0px" width="100%">
	<tr>
		<td width="20%"></td>
		<td width="80%" align="right">
			<p style="font-style: 9px"><strong>FECHA DE AJUSTE DE INVENTARIO: </strong><?= Esys::fecha_en_texto($model->updated_at, true)  ?></p>
		</td>

	</tr>
	<tr>
		<td style=" padding: 10px;" align="left" width="40%" >
			<?= Html::img('@web/img/logo.png', ["height"=>"80px"]) ?>
		</td>
		<td colspan="2" align="left" style="#000; padding: 0px; border:0px; padding: 20px;" width="60%">
			<h4 style="font-size: 24px"><strong>AJUSTE DE INVENTARIO [#<?= $model->id ?>]</strong></h4>
			<h5>SUCURSAL: <strong><small style="font-size: 16px"># <?= $model->inventarioSucursal->nombre ?> </small></strong></h5>
		</td>
	</tr>
</table>

<table style=" border-spacing: 0px;" width="100%">

	<?php foreach (InventarioOperacion::cantidadPerdidaOperacionAjuste($model->id) as $key => $item_operacion): ?>
		<tr>
	      	<td>
	      		<td style="padding: 10px" width="30%"><strong>PERDIDA [DIFERENCIA]:</strong></td>
	      	</td>
	      	<td>
	      		<td width="35%" style="font-size: 12px;  padding: 10px; border-bottom: 2px; border-style: solid;   "><p><?= $item_operacion["cantidad"] ?>  <?= Producto::$medidaList[$item_operacion["tipo_medida"]] ?> </p></td>
	      		<td width="35%" style="font-size: 12px;  padding: 10px; border-bottom: 2px; border-style: solid;   "><p><strong>COSTO X PERDIDA: </strong><?= number_format(InventarioOperacion::calculaCostoPerdida($model->id, $item_operacion["tipo_medida"]))  ?></p></td>
	      	</td>
		</tr>
    <?php endforeach ?>
</table>

<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
	<thead>
		<tr >
			<th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">#</th>
			<th align="center" width="40%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">PRODUCTO</th>
			<th align="center" width="50%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CANTIDAD [INVENTARIO]</th>
			<th align="center" width="50%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">DIFERENCIA</th>
		</tr>
	</thead>
	<tbody >
		<?php $count = 1; ?>

		<?php foreach ($model->inventarioOperacionDetalles as $key => $item_detail): ?>
			<tr style="border-style:  solid;border-width: 1px;border-spacing: 0px;">
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $count ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item_detail->producto->nombre ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item_detail->cantidad_inventario ?> <?= Producto::$medidaList[$item_detail->producto->tipo_medida] ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;">

	                <?php  $merma = floatval($item_detail->cantidad_inventario) -   floatval($item_detail->cantidad_old ) ?>
	                <p class="h5 text-right" style="color: <?= $merma >= 0 ? (  $merma > 0 ? "#538f14": "")  : "#ff0707" ?>; font-weight:bold" ><?= $merma ?> <?= Producto::$medidaList[$item_detail->producto->tipo_medida] ?></p>
				</td>
			</tr>
			<?php $count = $count + 1; ?>
		<?php endforeach ?>
	</tbody>
</table>
