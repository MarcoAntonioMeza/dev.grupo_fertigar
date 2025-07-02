<?php
use yii\helpers\Html;
use app\models\contabilidad\ContabilidadPoliza;
use app\models\contabilidad\ContabilidadCuenta;
use app\models\contabilidad\ContabilidadTransaccion;
use app\models\contabilidad\ContabilidadPolizaDetail;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$this->title = 'POLIZA #' .  str_pad($model->id,6,"0",STR_PAD_LEFT);

$this->params['breadcrumbs'][] = 'CONTABILIDAD';
$this->params['breadcrumbs'][] = ['label' => 'POLIZAS', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;

?>

<div class="contabilidad-poliza-view">
	<div class="row">
		<div class="col-sm-12">
			<div class="ibox">
				<div class="ibox-content">
					<h5 class="float-right text-primary"><?= ContabilidadPoliza::$tipoList[$model->tipo] ?></h5>
					<h2 class="text-navy" style="text-decoration: underline;">TRANSACCION : <?= $model->tipo == ContabilidadPoliza::TIPO_SISTEMA ? ContabilidadTransaccion::$tipoList[$model->pertenece] : 'POLIZA MANUAL' ?></h2>

					<div class="row">
						<div class="col-sm-4 text-center">
							<h2><?= $model->createdBy->nombreCompleto ?></h2>
							<h5>GENERADO POR:</h5>
						</div>
						<div class="col-sm-4 text-center">
							<h2>$<?= number_format($model->total,2)  ?>MXN</h2>
							<h5>TOTAL POLIZA</h5>
						</div>
						<div class="col-sm-4 text-center">
							<?php if ($model->pertenece == ContabilidadTransaccion::OPERACION_VENTA): ?>
								<?= Html::a("<i class='fa fa-eye'></i> VER OPERACION", ["/tpv/venta/view", "id" => $model->ventaCobro->venta_id ], ["class" => "btn btn-primary", "style" => "padding:15px; font-weight:700; font-size:14px"]) ?>
							<?php endif ?>

							<?php if ($model->pertenece == ContabilidadTransaccion::OPERACION_VENTA_CANCELACION): ?>
								<?= Html::a("<i class='fa fa-eye'></i> VER OPERACION",  ["/tpv/venta/view", "id" => $model->venta_id ] , ["class" => "btn btn-primary", "style" => "padding:15px; font-weight:700; font-size:14px"]) ?>
							<?php endif ?>

							<?php if ($model->pertenece == ContabilidadTransaccion::OPERACION_COMPRA): ?>
								<?= Html::a("<i class='fa fa-eye'></i> VER OPERACION",  ["/compras/compra/view", "id" => $model->compraPago->compra_id ], ["class" => "btn btn-primary", "style" => "padding:15px; font-weight:700; font-size:14px"]) ?>
							<?php endif ?>

							<?php if ($model->pertenece == ContabilidadTransaccion::OPERACION_COMPRA_CANCELACION): ?>
								<?= Html::a("<i class='fa fa-eye'></i> VER OPERACION",  ["/compras/compra/view", "id" => $model->compra_id ], ["class" => "btn btn-primary", "style" => "padding:15px; font-weight:700; font-size:14px"]) ?>
							<?php endif ?>

							<?php if ($model->pertenece == ContabilidadTransaccion::OPERACION_TRASPASO_INVENTARIO_SALIDA): ?>
								<?= Html::a("<i class='fa fa-eye'></i> VER OPERACION", ["/inventario/operacion/view", "id" => $model->almacen_operacion_id ], ["class" => "btn btn-primary", "style" => "padding:15px; font-weight:700; font-size:14px"]) ?>
							<?php endif ?>

							<?php if ($model->pertenece == ContabilidadTransaccion::OPERACION_TRASPASO_INVENTARIO_ENTRADA): ?>
								<?= Html::a("<i class='fa fa-eye'></i> VER OPERACION", ["/inventario/operacion/view", "id" => $model->almacen_operacion_id ], ["class" => "btn btn-primary", "style" => "padding:15px; font-weight:700; font-size:14px"]) ?>
							<?php endif ?>

							<?php if ($model->pertenece == ContabilidadTransaccion::OPERACION_SALARIO_ORDINARIO_ABONO || $model->pertenece == ContabilidadTransaccion::OPERACION_SALARIO_ORDINARIO_RETIRO || $model->pertenece == ContabilidadTransaccion::OPERACION_SALARIO_EXTRAORDINARIO_ABONO || $model->pertenece == ContabilidadTransaccion::OPERACION_SALARIO_EXTRAORDINARIO_RETIRO  ||$model->pertenece == ContabilidadTransaccion::OPERACION_SALARIO_APORTACION_SOCIAL_ABONO ||$model->pertenece == ContabilidadTransaccion::OPERACION_SALARIO_APORTACION_SOCIAL_RETIRO ): ?>
									<h2><?= $model->cajaVentanillaOperacionDetail->cajaVentanillaOperacion->socio->nombreCompleto  ?></h2>
									<h5>SOCIO</h5>
							<?php endif ?>

							<?php if ( $model->pertenece == ContabilidadTransaccion::OPERACION_PAGO_CREDENCIAL || $model->pertenece == ContabilidadTransaccion::OPERACION_PAGO_LIBRETA): ?>
									<h2><?= $model->cajaVentanillaOperacionDetail->cajaVentanillaOperacion->socio->nombreCompleto  ?></h2>
									<h5>SOCIO</h5>
							<?php endif ?>

							<?php if (!$model->pertenece && $model->tipo == ContabilidadPoliza::TIPO_MANUAL ): ?>
								<h2><?= $model->concepto  ?></h2>
								<h5>CONCEPTO</h5>
							<?php endif ?>

							<?php if ($model->pertenece == ContabilidadTransaccion::OPERACION_AYUDA_RETIRO): ?>
								<h2><?= $model->cajaVentanillaOperacionDetail->cajaVentanillaOperacion->socio->nombreCompleto  ?></h2>
								<h5>SOCIO</h5>
							<?php endif ?>

							<?php if ($model->pertenece == ContabilidadTransaccion::OPERACION_AYUDA_ABONO): ?>
								<h2><?= $model->cajaVentanillaOperacionDetail->cajaVentanillaOperacion->socio->nombreCompleto  ?></h2>
								<h5>SOCIO</h5>
							<?php endif ?>

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php if ($model->tipo == ContabilidadPoliza::TIPO_SISTEMA): ?>
		<?php $validConfiguracion =  count(ContabilidadTransaccion::getConfigContable($model->id)) > 0 ? true : false; ?>
		<div class="row">
			<div class="col-sm-12">
				<div class="ibox">
					<div class="ibox-content">
						<h5>CONFIGURACION CONTABLE: </h5>
						<table class="table" style="opacity: <?= $validConfiguracion ? '': '0.5' ?>;">
							<thead>
								<tr>
									<th class="text-center">CUENTA</th>
									<th class="text-center">REFERENCIA</th>
									<th class="text-center">CONCEPTO</th>
									<th class="text-center">DEBE [%]</th>
									<th class="text-center">HABER [%]</th>
								</tr>
							</thead>

							<tbody>
								<?php $totalDebe = $totaHaber = 0; ?>
								<?php foreach (ContabilidadTransaccion::getConfigContable($model->id) as $key => $item_configuracion): ?>
									<tr>
										<td class="text-center"><p style="font-size:14px"><?= $item_configuracion["cuenta"] ?> [<?= $item_configuracion["cuenta_numero"] ?>]</p></td>
										<td class="text-center"><p style="font-size:14px">#<?= str_pad($model->id,6,"0",STR_PAD_LEFT) ?></p></td>
										<td class="text-center"><p style="font-size:14px"><?= ContabilidadTransaccion::$tipoList[$model->pertenece]  ?> - <?= ContabilidadTransaccion::$motivoList[$item_configuracion["motivo"]]  ?> - <?= date("d/m/Y",$item_configuracion["created_at"])  ?> </p></td>
										<td class="text-center"><p style="font-size:16px; font-weight: bold;"><?= $item_configuracion["cargo"] ?>%</p></td>
										<td class="text-center"><p style="font-size:16px; font-weight: bold;"><?= $item_configuracion["abono"] ?>%</p></td>
									</tr>
									<?php $totalDebe = $totalDebe +  $item_configuracion["cargo"]; ?>
									<?php $totaHaber = $totaHaber +  $item_configuracion["abono"]; ?>
								<?php endforeach ?>
							</tbody>
							<tfoot>
								<tr>
									<td class="text-right" colspan="3">
										<p  style=" font-size: 18px;font-weight: 700; color: #000">TOTAL</p>
									</td>
									<td class="text-center">
										<p  class="text-primary" style=" font-size: 18px;"><?= $totalDebe ?>%</p>
									</td>
									<td class="text-center">
										<p  class="text-primary" style=" font-size: 18px;"><?= $totaHaber ?>%</p>
									</td>
								</tr>
							</tfoot>

						</table>

						<div class="div-alert-contable text-center" style="display:  <?= $validConfiguracion ? 'none': 'block' ?>;">
							<p style="display: block;position: absolute;top: 41%;color: #9a2424;font-weight: 700; font-size:24px">REGISTRAR CONFIGURACION CONTABLE <?= Html::a("<i class='fa fa-link'></i> CONFIGURAR", ["/contabilidad/transacciones/index"], ["class" => "btn btn-danger", "style" => "padding:15px; font-weight:700; font-size:14px"]) ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif ?>

	<?php if ($model->tipo == ContabilidadPoliza::TIPO_MANUAL): ?>
		<div class="row">
			<div class="col-sm-12">
				<div class="ibox">
					<div class="ibox-content">
						<h5>CONFIGURACION CONTABLE: </h5>
						<table class="table"  >
							<thead>
								<tr>
									<th class="text-center">CUENTA</th>
									<th class="text-center">REFERENCIA</th>
									<th class="text-center">CONCEPTO</th>
									<th class="text-center">DEBE [%]</th>
									<th class="text-center">HABER [%]</th>
								</tr>
							</thead>

							<tbody>
								<?php $totalDebe = $totaHaber = 0; ?>
								<?php foreach (ContabilidadPolizaDetail::getConfigPolizaManual($model->id) as $key => $item_configuracion): ?>
									<tr>
										<td class="text-center"><p style="font-size:14px"><?= $item_configuracion["cuenta"] ?></p></td>
										<td class="text-center"><p style="font-size:14px">#<?= str_pad($model->id,6,"0",STR_PAD_LEFT) ?></p></td>
										<td class="text-center"><p style="font-size:14px"><?= $model->concepto ?> </p></td>
										<td class="text-center"><p style="font-size:16px; font-weight: bold;">$<?= number_format($item_configuracion["cargo"],2) ?>MXN</p></td>
										<td class="text-center"><p style="font-size:16px; font-weight: bold;">$<?= number_format($item_configuracion["abono"],2) ?>MXN</p></td>
									</tr>
									<?php $totalDebe = $totalDebe +  $item_configuracion["cargo"]; ?>
									<?php $totaHaber = $totaHaber +  $item_configuracion["abono"]; ?>
								<?php endforeach ?>
								<tfoot>
									<tr>
										<td class="text-right" colspan="3">
											<p  style=" font-size: 18px;font-weight: 700; color: #000">TOTAL</p>
										</td>
										<td class="text-center">
											<p  class="text-primary" style=" font-size: 18px;">$<?= number_format($totalDebe,2) ?>MXN</p>
										</td>
										<td class="text-center">
											<p  class="text-primary" style=" font-size: 18px;">$<?= number_format($totaHaber,2) ?>MXN</p>
										</td>
									</tr>
								</tfoot>
							</tbody>
						</table>

					</div>
				</div>
			</div>
		</div>
	<?php endif ?>
</div>