<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
//use kartik\date\DatePicker;
use kartik\select2\Select2;
use app\models\credito\Credito;
?>

<div class="creditos-credito-form">

    <?php $form = ActiveForm::begin(['id' => 'form-credito','options' => ['enctype' => 'multipart/form-data'] ]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Crear credito' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white']) ?>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información generales</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-6">
                             <?= $form->field($model, 'cliente_id')->widget(Select2::classname(),
                            [
                                'language' => 'es',
                                    'data' => isset($model->cliente_id)  && $model->cliente_id ? [$model->cliente->id => $model->cliente->nombre ." ". $model->cliente->apellidos] : [],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 3,
                                        'language'   => [
                                            'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                        ],
                                        'ajax' => [
                                            'url'      => Url::to(['/crm/cliente/cliente-ajax']),
                                            'dataType' => 'json',
                                            'cache'    => true,
                                            'processResults' => new JsExpression('function(data, params){  return {results: data} }'),
                                        ],
                                    ],
                                    'options' => [
                                        'placeholder' => 'Seleccione al cliente ...',
                                    ],

                            ]) ?>

                            <?= $form->field($model, 'descripcion')->textarea(['rows' => 6]) ?>


                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'monto')->textInput(['type' => 'number']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información extra </h5>
                </div>
                <div class="ibox-content">
                    <?= $form->field($model, 'nota')->textarea(['rows' => 6]) ?>
                </div>
            </div>
        </div>
        <div class="col-lg-5">

        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>

