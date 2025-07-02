<?php
use yii\helpers\Html;
use app\models\Esys;
use app\models\inv\Operacion;
use app\models\producto\Producto;
use app\models\tranformacion\TranformacionDevolucion;
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
			<?= Html::img('@web/img/logo.png', ["height"=>"80px"]) ?>
		</td>
		<td colspan="2" align="left" style="#000; padding: 0px; border:0px; padding: 20px;" width="60%">
			<h4 style="font-size: 24px"><strong>REPORTE DE TRANSFORMACION</strong></h4>
		</td>
	</tr>
</table>

<table style=" border-spacing: 0px;" width="100%">
	<tr>
		<td style="padding: 10px" width="25%"><strong>TIPO DE OPERACION </strong></td>
		<td width="25%" style="padding: 10px; border-bottom: 2px; border-style: solid;"><p style="font-size:16px; font-weight: bold;">TRANSFORMACION </p></td>

	</tr>

	<tr >
		<td style="padding: 10px" width="25%" ><strong>EMPLEADO RESPONSABLE : </strong></td>
		<td  width="80%" style="font-size: 12px; padding: 10px;border-bottom: 2px; border-style: solid;"><p style="font-size:16px; "> <?= $model->createdBy->nombreCompleto ?> </p></td>
	</tr>
</table>

<table style=" border-spacing: 0px; padding: 14px;" width="100%">
	<tr>
		<td width="40%" style="text-align:center;">
			<p><strong>
				<h3><?= $model->sucursal_id ? $model->sucursal->nombre : ''?></h3>
			</strong></p>
			<p><strong style="font-size:10px">SUCURSAL</strong></p>
		</td>
	</tr>
</table>

<hr>
<strong style="font-size: 12px;">DETALLE DE TRANSFORMACION</strong>

<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
	<thead>
		<tr>
            <th align="center" width="10%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CLAVE</th>
            <th align="center" width="60%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">PRODUCTO</th>
            <th align="center" width="15%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CANTIDAD</th>
            <th align="center" width="15%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">U.M</th>
		</tr>
	</thead>
	<tbody >
		<?php foreach ($model->tranformacionDevolucionDetalles as $key => $item): ?>
			<tr style="border-style:  solid;border-width: 1px;border-spacing: 0px;">
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item->producto->clave  ?></a></td>
                <td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item->producto->nombre ?></td>
                <td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item->cantidad  ?>        </td>
                <td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= Producto::$medidaList[$item->producto->tipo_medida]  ?> </td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>


<hr>
<strong style="font-size: 12px;">PRODUCTO TRANSFORMADO</strong>

<table style="padding: 5px;margin:0; border-spacing: 0px;  font-size: 12px" width="100%">
	<thead>
		<tr>
            <th align="center" width="70%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">PRODUCTO</th>
            <th align="center" width="30%" style="border-style:  solid;border-width: 1px;border-spacing: 0px; ">CANTIDAD</th>
		</tr>
	</thead>
	<tbody >
		<?php foreach (TranformacionDevolucion::getItemsGroup($model->token, $model->id) as $key => $item_operacion): ?>
			<tr style="border-style:  solid;border-width: 1px;border-spacing: 0px;">
				<td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item_operacion->producto_new ?  $item_operacion->productoNew->nombre : 'MERMA'  ?></a></td>
                <td align="center" style="border-style:  solid;border-width: 1px;border-spacing: 0px;"><?= $item_operacion->producto_new ? $item_operacion->producto_cantidad : '-------' ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>





