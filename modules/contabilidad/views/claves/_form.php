<?php
use yii\helpers\Html; 
use yii\widgets\ActiveForm;
use app\models\contabilidad\ContabilidadCuenta;
?>


<div class="contabilidad-claves-form">
  <?php $form = ActiveForm::begin(['id' => 'form-claves']) ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-content">
                        <div class="form-group ">
                              <?= Html::submitButton($model->isNewRecord ? 'Guardar' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'style' => 'font-size:18px;']) ?>
                              <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white',  'style' => 'font-size:18px;']) ?>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <?= $model->isNewRecord ?  $form->field($model,  'code')->textInput([ 'maxlength' => true, "autocomplete" => "off", "style" => "font-size:24px; font-weight:700" ]) : ''  ?>

                                <?= $form->field($model, 'nombre')->textInput([ 'maxlength' => true, "autocomplete" => "off", "style" => "font-size:24px; font-weight:700" ])?>

                            </div>
                            <div class="col-lg-6">
                                <?= $form->field($model, 'afectable')->dropDownList(ContabilidadCuenta::$afectableList, ['prompt'=>'--- SELECT ---',  'style' => 'font-size:24px;  height: 100%'] ) ?>

                                <?= $form->field($model, 'status')->dropDownList(ContabilidadCuenta::$statusList, ['prompt'=>'--- SELECT ---',  'style' => 'font-size:24px;  height: 100%'] ) ?>
                            </div>

                        </div>
                </div>
            </div>
        </div>
    </div>
  <?php ActiveForm::end(); ?>
</div>

