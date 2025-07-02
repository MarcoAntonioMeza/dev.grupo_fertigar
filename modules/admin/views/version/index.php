<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\esys\EsysSetting;
use kartik\date\DatePicker;
use app\models\Esys;
$this->title = 'Control de  versiones';

?>

<div class="panel">
    <div class="panel-body">
        <h3>Registrar una nueva versión</h3>
        <p>Ingresa la información necesaria de los cambios realizados</p>
        <?php $form = ActiveForm::begin(['id' => 'form-reparto' ]) ?>
            <div class="row">
                <div class="col-sm-6">
                    <?= $form->field($model, 'version')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'fecha_registro')->widget(DatePicker::classname(), [
                                'options' => ['placeholder' => 'Fecha de registro'],
                                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                'language' => 'es',
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                ]
                            ]) ?>

                    <?= $form->field($model, 'descripcion')->textarea(['rows' => 6]) ?>
                    <div class="form-group">
                        <?= Html::submitButton('Nueva versión', ['class' => 'btn btn-primary btn-block btn-lg' ]) ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="mar-all">
                        <div class="panel-group accordion" id="demo-acc-info-outline">
                            <?php foreach ($model->items as $key => $item): ?>
                                <div class="panel panel-bordered panel-info">
                                    <!-- Accordion title -->
                                    <div class="panel-heading">
                                        <div class="panel-control">
                                            <strong> <?= Esys::fecha_en_texto($item->fecha_registro)  ?></strong>
                                        </div>
                                        <h4 class="panel-title">
                                            <a data-parent="#demo-acc-info-outline" data-toggle="collapse" href="#demo-acd-info-outline-<?= $key ?>" aria-expanded="false" class="collapsed">V. <?= $item->version  ?></a>
                                        </h4>
                                    </div>

                                    <!-- Accordion content -->
                                    <div class="panel-collapse collapse" id="demo-acd-info-outline-<?= $key ?>" aria-expanded="false" style="height: 0px;">
                                        <div class="panel-body">
                                            <?= $item->descripcion  ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
