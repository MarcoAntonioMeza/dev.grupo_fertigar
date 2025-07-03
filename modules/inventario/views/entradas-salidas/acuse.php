<?php
use yii\helpers\Html;
use app\models\Esys;
use app\models\inv\Operacion;
$peso = 0; // Inicializar la variable peso
foreach (Operacion::getOperacionDetalleGroup($model->id) as $item) {
    if (isset($item['producto_peso_aprox'])) {
        $peso += $item['producto_peso_aprox'] * $item['cantidad'];
    }
}
#convierte a toneladas
$peso = $peso / 1000; // Convertir a toneladas si es necesario
?>


<table style="padding: 5px;margin:0; border-spacing: 0px" width="100%">
	<tr>
		<td width="20%"></td>
		<td width="80%" align="right">
			<p style="font-style: 9px"><strong>FECHA DE OPERACION: </strong><?= Esys::fecha_en_texto($model->created_at, true)  ?></p>
		</td>

	</tr>
	<tr>
		<td style=" padding: 10px;" align="left" width="40%" >
			<img src="https://grupofertigar.com/wp-content/uploads/2023/10/GFP-Color-02.png" height="80px" alt="GRUPO FERTIGAR">
		</td>
		<td colspan="2" align="left" style="#000; padding: 0px; border:0px; padding: 20px;" width="60%">
			<h4 style="font-size: 24px"><strong>REPORTE DE OPERACIÓN</strong></h4>
		</td>
	</tr>
</table>

<table style=" border-spacing: 0px;" width="100%">
	<tr>
		<td style="padding: 10px" width="25%"><strong>TIPO DE OPERACION </strong></td>
		<td width="25%" style="padding: 10px; border-bottom: 2px; border-style: solid;"><p style="font-size:16px; font-weight: bold;"><?= Operacion::$tipoList[$model->tipo]  ?> / <?= Operacion::$operacionList[$model->motivo]  ?> </p></td>

		<td style="padding: 10px" width="10%"><strong>ESTATUS </strong></td>
		<td width="25%" style="font-size: 12px;  padding: 10px; border-bottom: 2px; border-style: solid;"><p style="font-size:16px; "><?= Operacion::$statusList[$model->status] ?></p></td>
	</tr>

	<tr >
		<td style="padding: 10px" width="25%" ><strong>EMPLEADO RESPONSABLE : </strong></td>
		<td  width="80%" style="font-size: 12px; padding: 10px;border-bottom: 2px; border-style: solid;"><p style="font-size:16px; "> <?= $model->createdBy->nombreCompleto ?> </p></td>
	</tr>

	<tr >
		<td style="padding: 10px" width="25%" ><strong>COMENTARIO : </strong></td>
		<td  width="80%" style="font-size: 12px; padding: 10px;border-bottom: 2px; border-style: solid;"><p style="font-size:16px; "> <?= $model->nota ?> </p></td>
	</tr>
</table>

<table style=" border-spacing: 0px; padding: 14px;" width="100%">
	<tr>
		<td width="40%" style="text-align:center;">

			<?php if ($model->motivo == Operacion::ENTRADA_MERCANCIA_NUEVA): ?>
				<p><strong>
					<h3>NUEVA MERCANCIA #<?= $model->compra_id ?></h3>
				</strong></p>
				<p><strong style="font-size:10px">ORIGEN</strong></p>
			<?php endif ?>
			<?php if ($model->motivo == Operacion::ENTRADA_TRASPASO_RECOLECCION): ?>
				<p><strong>
					<?php if ($model->reparto_id): ?>
						<h3><?= $model->reparto->sucursal->nombre ?></h3>
					<?php endif ?>
				</strong></p>
				<p><strong style="font-size:10px">RECOLECCION [REPARTO]</strong></p>

			<?php endif ?>
			<?php if ($model->motivo == Operacion::ENTRADA_TRASPASO): ?>
				<p><strong>
					<H3><?= $model->operacionChild->almacenSucursal->nombre ?></H3>
				</strong></p>
				<p><strong style="font-size:10px">ORIGEN</strong></p>
			<?php endif ?>
			<?php if ($model->motivo == Operacion::ENTRADA_RUTA_AJUSTE): ?>
				<p><strong>
					<h3><?=  $model->sucursal_recibe_id ? $model->sucursalRecibe->nombre  : ''?></h3>
				</strong></p>
				<p><strong style="font-size:10px">ORIGEN</strong></p>
			<?php endif ?>


			<?php if ($model->motivo == Operacion::SALIDA_RUTA_AJUSTE): ?>
				<p><strong>
					<h3><?= $model->almacen_sucursal_id ? $model->almacenSucursal->nombre  : '' ?></h3>
				</strong></p>
				<p><strong style="font-size:10px">ORIGEN</strong></p>
			<?php endif ?>

			<?php if ($model->motivo == Operacion::SALIDA_TRASPASO): ?>
				<p><strong>
					<h3><?= $model->almacenSucursal->nombre ?></h3>
				</strong></p>
				<p><strong style="font-size:10px">ORIGEN</strong></p>
			<?php endif ?>

			<?php if ($model->motivo == Operacion::SALIDA_TRASPASO_RECOLECCION): ?>
				<p><strong>
					<?php if ($model->reparto_id): ?>
						<h3><?= $model->reparto->sucursal->nombre ?></h3>
					<?php endif ?>
				</strong></p>
				<p><strong style="font-size:10px">RECOLECCION [REPARTO]</strong></p>
			<?php endif ?>

			<?php if ($model->motivo == Operacion::SALIDA_TRASPASO_UNIDAD): ?>
				<p><strong>
					<h3><?= $model->almacenSucursal->nombre ?></h3>
				</strong></p>
				<p><strong style="font-size:10px">ORIGEN</strong></p>
			<?php endif ?>

			<?php if ($model->motivo == Operacion::ENTRADA_TRASPASO_UNIDAD): ?>
				<p><strong>
					<h3><?= Operacion::searchOrigenTraspaso($model->id) ?></h3>
				</strong></p>
				<p><strong style="font-size:10px">ORIGEN</strong></p>
			<?php endif ?>
		</td>
		<td width="20%" style="text-align: center;"><strong> => <br>
		
	<?= number_format($peso, 2) ?> toneladas </strong></td>
		<td width="40%" style="text-align:center;" >

			<?php if ($model->motivo == Operacion::ENTRADA_MERCANCIA_NUEVA): ?>
				<p><strong>
					<h3><?= $model->almacenSucursal->nombre ?></h3>
				</strong></p>
				<p><strong style="font-size:10px">DESTINO</strong></p>
			<?php endif ?>

			<?php if ($model->motivo == Operacion::SALIDA_TRASPASO): ?>
				<p><strong><?= $model->sucursalRecibe->nombre ?></strong></p>
				<p><strong style="font-size:10px">SUCURSAL A SURTIR</strong></p>
			<?php endif ?>

			<?php if ($model->motivo == Operacion::ENTRADA_TRASPASO): ?>
				<p><strong><?= $model->almacenSucursal->nombre ?></strong></p>
				<p><strong style="font-size:10px">DESTINO</strong></p>
			<?php endif ?>

			<?php if ($model->motivo == Operacion::ENTRADA_RUTA_AJUSTE): ?>
				<p><strong><?= $model->sucursalRecibe->nombre ?></strong></p>
				<p><strong style="font-size:10px">DESTINO</strong></p>
			<?php endif ?>

			<?php if ($model->motivo == Operacion::SALIDA_RUTA_AJUSTE): ?>
				<p><strong>
					<h3><?= $model->sucursal_recibe_id ? $model->sucursalRecibe->nombre  : '' ?></h3>
				</strong></p>
				<p><strong style="font-size:10px"> DESTINO </strong></p>
			<?php endif ?>

			<?php if ($model->motivo == Operacion::ENTRADA_TRASPASO_RECOLECCION): ?>
				<p><strong>
					<h3><?= $model->almacenSucursal->nombre ?></h3>
				</strong></p>
				<p><strong style="font-size:10px">DESTINO</strong></p>
			<?php endif ?>

			<?php if ($model->motivo == Operacion::SALIDA_TRASPASO_RECOLECCION): ?>
				<p><strong>
					<h3><?= $model->operacionChild->almacenSucursal->nombre ?></h3>
				</strong></p>
				<p><strong style="font-size:10px">DESTINO</strong></p>
			<?php endif ?>

			<?php if ($model->motivo == Operacion::SALIDA_TRASPASO_UNIDAD): ?>
				<?php if ($model->operacion_child_id): ?>
					<p><strong>
						<h3><?= $model->operacionChild->almacenSucursal->nombre ?></h3>
					</strong></p>
				<?php endif ?>
				<p><strong style="font-size:10px">DESTINO</strong></p>
			<?php endif ?>

			<?php if ($model->motivo == Operacion::ENTRADA_TRASPASO_UNIDAD): ?>
				<p><strong>
					<h3><?= $model->almacenSucursal->nombre ?></h3>
				</strong></p>
				<p><strong style="font-size:10px">DESTINO</strong></p>
			<?php endif ?>

		</td>
	</tr>
</table>

<hr>
<strong style="font-size: 12px;">DETALLE DE OPERACIÓN</strong>

<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
	<thead>
		<tr >
			<th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CLAVE</th>
			<th align="center" width="60%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">PRODUCTO</th>
			<th align="center" width="15%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CANTIDAD</th>
			<th align="center" width="15%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">UNIDAD DE MEDIDA</th>
		</tr>
	</thead>
	<tbody >
		<?php foreach (Operacion::getOperacionDetalleGroup($model->id)  as $key => $producto): ?>
			<tr style="border-style:  solid;border-width: 1px;border-spacing: 0px;">
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $producto["producto_clave"] ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $producto["producto"]  ?> </td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $producto["cantidad"] ?></td>
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $producto["producto_tipo_medida"]  ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>





