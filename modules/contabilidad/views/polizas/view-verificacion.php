<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\contabilidad\ContabilidadPoliza;
use app\models\contabilidad\ContabilidadCuenta;
use app\models\contabilidad\ContabilidadPolizaVerificacion;
use app\models\contabilidad\ContabilidadTransaccion;
use app\models\contabilidad\ContabilidadPolizaDetail;

/* @var $this yii\web\View */

$this->title = 'VERIFICACION ' .  $model->id;

$this->params['breadcrumbs'][] = 'CONTABILIDAD';
$this->params['breadcrumbs'][] = ['label' => 'POLIZAS', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;

?>

<div class="contabilidad-poliza-view">
	<div class="row">
		<div class="col-sm-12">
			<div class="ibox">
				<div class="ibox-content">
					<?= Html::button("GENERAR REPORTE", ["class" => "btn btn-primary float-right", "style" => "font-size:24px", "id" => "imprimir-reporte" ]) ?>
					<h2 class="text-navy" style="text-decoration: underline;">TRANSACCION : <?= ContabilidadPolizaVerificacion::$transaccionList[$model->transaccion] ?></h2>

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
							<h2><?= $model->contabilidadPolizaVerificacionDetailCount  ?></h2>
							<h5>N° DE POLIZAS</h5>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div class="ibox">
				<div class="ibox-content">
					<h5>POLIZAS: </h5>
					<table class="table">
						<thead>
							<tr>
								<th class="text-center">CUENTA</th>
								<th class="text-center">REFERENCIA</th>
								<th class="text-center">CONCEPTO</th>
								<th class="text-center">DEBE</th>
								<th class="text-center">HABER</th>
							</tr>
						</thead>
						<tbody>
							<?php $totalDebe = $totaHaber = 0; ?>
							<?php foreach ($model->contabilidadPolizaVerificacionDetails as $key => $item_detail): ?>
								<?php foreach (ContabilidadTransaccion::getConfigContable($item_detail->contabilidad_poliza_id) as $key => $item_configuracion): ?>
									<tr>
										<td class="text-center"><p style="font-size:14px"><?= $item_configuracion["cuenta"] ?> [<?= $item_configuracion["cuenta_numero"] ?>]</p></td>
										<td class="text-center"><p style="font-size:14px">#<?= str_pad($item_detail->contabilidadPoliza->id,6,"0",STR_PAD_LEFT) ?></p></td>
										<td class="text-center"><p style="font-size:14px"><?= $item_detail->contabilidadPoliza->tipo == ContabilidadPoliza::TIPO_SISTEMA ?  ( ContabilidadTransaccion::$tipoList[$item_detail->contabilidadPoliza->pertenece] ) : 'POLIZA MANUAL' ?> - <?= $item_detail->contabilidadPoliza->tipo == ContabilidadPoliza::TIPO_SISTEMA ? ContabilidadTransaccion::$motivoList[$item_configuracion["motivo"]] : ''  ?> - <?= date("d/m/Y",$item_configuracion["created_at"])  ?>  <?= $item_detail->contabilidadPoliza->concepto ?> </p></td>

										<td class="text-center"><p style="font-size:16px; font-weight: bold;"><?=  $item_detail->contabilidadPoliza->tipo == ContabilidadPoliza::TIPO_SISTEMA ?  number_format(($item_detail->contabilidadPoliza->total * $item_configuracion["cargo"]) / 100,2) : number_format( $item_configuracion["cargo"],2 ) ?>MXN</p></td>
										<td class="text-center"><p style="font-size:16px; font-weight: bold;"><?= $item_detail->contabilidadPoliza->tipo == ContabilidadPoliza::TIPO_SISTEMA ? number_format( ($item_detail->contabilidadPoliza->total * $item_configuracion["abono"] )  / 100,2) : number_format( $item_configuracion["abono"],2 ) ?>MXN</p></td>
									</tr>
									<?php $totalDebe = $totalDebe +   ( $item_detail->contabilidadPoliza->tipo == ContabilidadPoliza::TIPO_SISTEMA  ? ($item_detail->contabilidadPoliza->total * floatval($item_configuracion["cargo"]) ) / 100 : floatval($item_configuracion["cargo"]) );  ?>
									<?php $totaHaber = $totaHaber + ( $item_detail->contabilidadPoliza->tipo == ContabilidadPoliza::TIPO_SISTEMA  ?  ($item_detail->contabilidadPoliza->total * floatval($item_configuracion["abono"]) )  / 100 : floatval($item_configuracion["abono"]) );  ?>
								<?php endforeach ?>
							<?php endforeach ?>
						</tbody>
						<tfoot>
							<tr>
								<td class="text-right" colspan="3">
									<p  style=" font-size: 18px;font-weight: 700; color: #000">TOTAL</p>
								</td>
								<td class="text-center">
									<p  class="text-primary" style=" font-size: 18px;"><?= number_format($totalDebe,2) ?>MXN</p>
								</td>
								<td class="text-center">
									<p  class="text-primary" style=" font-size: 18px;"><?= number_format($totaHaber,2) ?>MXN</p>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
$('#imprimir-reporte').click(function(event){
    event.preventDefault();
    window.open("<?= Url::to(['imprimir-reporte', 'id' => $model->id ])  ?>",
    'imprimir',
    'width=600,height=500');
});
</script>
