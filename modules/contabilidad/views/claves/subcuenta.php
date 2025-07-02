<?php
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;
use app\models\contabilidad\ContabilidadCuenta;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$this->title = 'Nueva subcuenta ';

$this->params['breadcrumbs'][] = 'contabilidad';
$this->params['breadcrumbs'][] = ['label' => 'CLAVES', 'url' => ['index']];
$this->params['breadcrumbs'][] = $cuenta->nombre;
$this->params['breadcrumbs'][] = 'add subcuenta';
?>

<div class="contabilidad-subcuenta-form">
  	<div class="ibox">
  		<div class="ibox-content">
  			<?php $form = ActiveForm::begin(['id' => 'form-subclaves']) ?>
		      	<div class="form-group ">
		      	<?= Html::submitButton($model->isNewRecord ? 'Guardar' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary','id' => 'btnSubCuentaSave','style' => 'font-size:18px;']) ?>
		          <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white', 'style' => 'font-size:18px;'])?>
		      	</div>
				<div class="col-lg-6">
					<?= $form->field($model, 'nombre')->textInput([ 'maxlength' => true, "autocomplete" => "off", "style" => "font-size:24px; font-weight:700" ])?>

					<?= $form->field($model, 'afectable')->dropDownList(ContabilidadCuenta::$afectableList, ['prompt'=>'--- SELECT ---', 'style' => 'font-size:24px;  height: 100%'] ) ?>

					<?= $form->field($model, 'status')->dropDownList(ContabilidadCuenta::$statusList, ['prompt'=>'--- SELECT ---', 'style' => 'font-size:24px;  height: 100%'] ) ?>
				</div>
		  <?php ActiveForm::end(); ?>
  		</div>
  	</div>
</div>
