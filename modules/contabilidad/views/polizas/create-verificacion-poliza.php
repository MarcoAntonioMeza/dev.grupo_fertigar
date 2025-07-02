<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\date\DatePicker;
use app\models\contabilidad\ContabilidadPolizaVerificacion;

/* @var $this yii\web\View */

$this->title = 'VERIFICACION DE POLIZAS';

$this->params['breadcrumbs'][] = 'CONTABILIDAD';
$this->params['breadcrumbs'][] = ['label' => 'POLIZAS', 'url' => ['index']];


?>

<div class="ibox">
	<div class="ibox-content">

		<div class="row">
			<div class="col-sm-8">
				<?= Html::dropDownList('tipo_poliza', null, ContabilidadPolizaVerificacion::$transaccionList , ['prompt' => '-- SELECT --', 'class' => 'form-control', 'style' => 'font-size:18px; font-weight:700; height:100%', 'id' => 'inputTipoTransaccion']) ?>
			</div>
			<div class="col-sm-4">
				<?= Html::button("CARGAR POLIZAS", ["class" => "btn btn-success btn-block", "style" => " font-size:18px; padding:15px; font-weight:600" , "id" => "btnGeneratePoliza"]) ?>
			</div>
		</div>
		<div class="row" style="margin-top: 70px;">
	   		<div class="col-sm-3 text-center">
	   			<p style="font-size:16px; font-weight:600">DEBE</p>
	   		</div>
	   		<div class="col-sm-3 text-center">
	   			<p style="font-size:22px; font-weight:600; color:#000" class="lbl-text-totales lbl-poliza-debe">$0.0MXN </p>
	   		</div>
	   		<div class="col-sm-3 text-center">
	   			<p style="font-size:16px; font-weight:600">HABER</p>
	   		</div>
	   		<div class="col-sm-3 text-center">
	   			<p style="font-size:22px; font-weight:600; color:#000" class="lbl-text-totales lbl-poliza-haber">$0.0MXN </p>
	   		</div>
	   	</div>

		<div class="container-control-polizas">
		</div>
		<div class="form-group div_control_form_polizas" style="display: none">
			<?= Html::button("<i class='fa fa-gears' style='font-size:24px; margin-right:15px'></i> VALIDAR POLIZAS", ["class" => "btn btn-success btn-block", "style"=> "padding:15px; font-weight:bold; font-size:14px", "id" => "btnSaveCorteNomina"]) ?>
		</div>
	</div>
</div>

 <?php $this->registerJsFile('@web/js/my_js/poliza-verificacion-script.js'); ?>