<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\models\esys\EsysDireccionCodigoPostal;
use app\models\Esys;
use app\models\cliente\Cliente;
use app\models\esys\EsysListaDesplegable;

/* @var $this yii\web\View */
/* @var $cliente app\models\cliente\User */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="clientes-cliente-form">

    <?php $form = ActiveForm::begin(['id' => 'form-cliente']) ?>

    <?= $form->field($model, 'titulo_personal_id')->hiddenInput()->label(false) ?>

    <div class="row">
        <!-- Columna izquierda - Información principal -->
        <div class="col-lg-8">
            <!-- Sección de información general -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-user"></i> Información General</h5>
                </div>
                <div class="card-body">
                    <?= $form->errorSummary($model) ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'atraves_de_id')->dropDownList(
                                EsysListaDesplegable::getItems('origen_cliente'),
                                ['prompt' => 'Seleccione cómo nos conoció']
                            ) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'tipo_cliente_id')->dropDownList(
                                EsysListaDesplegable::getItems('tipo_cliente'),
                                ['prompt' => 'Seleccione tipo de cliente']
                            ) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'titulo_personal_id')->dropDownList(
                                EsysListaDesplegable::getItems('titulo_personal'),
                                ['prompt' => 'Título']
                            ) ?>
                        </div>
                        <div class="col-md-8">
                            <?= $form->field($model, 'nombre')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Nombre(s)'
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'apellidos')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Apellidos'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'sexo')->dropDownList(
                                [10 => 'Hombre', 20 => 'Mujer'],
                                ['prompt' => 'Seleccione sexo']
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de contacto -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5><i class="fas fa-phone"></i> Información de Contacto</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'email')->input('email', [
                                'placeholder' => 'correo@ejemplo.com'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'rfc')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'RFC'
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'telefono')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Teléfono fijo'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'telefono_movil')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Teléfono móvil'
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model, 'agente_id')->widget(
                                Select2::classname(),
                                [
                                    'language' => 'es',
                                    'data' => isset($model->agente_id) && $model->agente_id ?
                                        [$model->agente_id => $model->agente_id->nombre . " " . $model->agente_id->apellidos] : [],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 3,
                                        'language' => [
                                            'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                        ],
                                        'ajax' => [
                                            'url' => Url::to(['/admin/user/user-ajax']),
                                            'dataType' => 'json',
                                            'cache' => true,
                                            'processResults' => new JsExpression('function(data, params){ return {results: data} }'),
                                        ],
                                    ],
                                    'options' => [
                                        'placeholder' => 'Asignar agente...',
                                    ],
                                ]
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de información fiscal -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-white">
                    <h5><i class="fas fa-file-invoice-dollar"></i> Información Fiscal</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'regimen_fiscal_id')->dropDownList(
                                \app\models\sat\Regimenfiscal::getArrayRegimenFiscal(),
                                ['prompt' => 'Seleccione régimen fiscal']
                            ) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'uso_cfdi')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Uso CFDI'
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'lista_precios')->dropDownList(
                                Cliente::$tipoListaPrecioList,
                                ['prompt' => 'Seleccione lista de precios']
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna derecha - Información adicional -->
        <div class="col-lg-4">
            <!-- Sección de dirección -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5><i class="fas fa-map-marker-alt"></i> Dirección</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model->dir_obj, 'codigo_search')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Código Postal'
                            ]) ?>
                            <div id="error-codigo-postal" class="alert alert-danger" style="display: none">
                                Código postal inválido, verifique nuevamente o busque la dirección manualmente
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model->dir_obj, 'estado_id')->widget(Select2::classname(), [
                                'language' => 'es',
                                'data' => EsysListaDesplegable::getEstados(),
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                                'options' => [
                                    'placeholder' => 'Seleccione el estado',
                                ],
                                'pluginEvents' => [
                                    "change" => "function(){ onEstadoChange() }",
                                ]
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model->dir_obj, 'municipio_id')->widget(Select2::classname(), [
                                'language' => 'es',
                                'data' => $model->dir_obj->estado_id ?
                                    EsysListaDesplegable::getMunicipios(['estado_id' => $model->dir_obj->estado_id]) : [],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                                'options' => [
                                    'placeholder' => 'Seleccione el municipio'
                                ],
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model->dir_obj, 'codigo_postal_id')->widget(Select2::classname(), [
                                'language' => 'es',
                                'data' => $model->dir_obj->codigo_postal_id ?
                                    EsysDireccionCodigoPostal::getColonia(['codigo_postal' => $model->dir_obj->codigo_search]) : [],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                                'options' => [
                                    'placeholder' => 'Seleccione la colonia'
                                ],
                            ])->label('Colonia') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model->dir_obj, 'direccion')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Calle y número'
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model->dir_obj, 'num_ext')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Número exterior'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model->dir_obj, 'num_int')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Número interior'
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model->dir_obj, 'referencia')->textarea([
                                'rows' => 3,
                                'placeholder' => 'Referencias adicionales'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de crédito -->
            <div class="card mb-4">
                <div class="card-header bg-purple text-white">
                    <h5><i class="fas fa-credit-card"></i> Límite de Crédito</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'monto_credito')->textInput([
                                'type' => 'number',
                                'placeholder' => 'Monto'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'semanas')->textInput([
                                'type' => 'number',
                                'placeholder' => 'Semanas'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de estado -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5><i class="fas fa-info-circle"></i> Estado del Cliente</h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'status')->dropDownList(
                        Cliente::$statusList,
                        ['prompt' => 'Seleccione estado']
                    ) ?>
                </div>
            </div>

            <!-- Sección de notas -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5><i class="fas fa-sticky-note"></i> Notas Adicionales</h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'notas')->textarea([
                        'rows' => 4,
                        'placeholder' => 'Escriba aquí cualquier información adicional...'
                    ])->label(false) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="row m-4">
        <div class="col-md-12 text-center">
            <?= Html::a('Cancelar', ['index'], [
                'class' => 'btn btn-outline-secondary mr-2 btn-zoom',
            ]) ?>
            <?= Html::submitButton($model->isNewRecord ? '<i class="fas fa-plus"></i> Crear cliente' : '<i class="fas fa-save"></i> Guardar cambios', [
                'class' => $model->isNewRecord ? 'btn btn-success btn-zoom' : 'btn btn-primary btn-zoom',
                'id' => 'btnClienteSave'
            ]) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script type="text/javascript">
    var $inputEstado = $('#esysdireccion-estado_id'),
        $inputMunicipio = $('#esysdireccion-municipio_id'),
        $inputCodigoSearch = $('#esysdireccion-codigo_search'),
        $inputColonia = $('#esysdireccion-codigo_postal_id'),
        $error_codigo = $('#error-codigo-postal'),
        $btnClienteSave = $('#btnClienteSave'),
        municipioSelected = null;

    $(document).ready(function() {
        $inputCodigoSearch.change(function() {
            $inputColonia.html('');
            $inputEstado.val(null).trigger("change");

            var codigo_search = $inputCodigoSearch.val();

            $.get('<?= Url::to('@web/municipio/codigo-postal-ajax') ?>', {
                'codigo_postal': codigo_search
            }, function(json) {
                if (json.length > 0) {
                    $error_codigo.hide();
                    $inputEstado.val(json[0].estado_id);
                    $inputEstado.trigger('change');
                    municipioSelected = json[0].municipio_id;

                    $.each(json, function(key, value) {
                        $inputColonia.append("<option value='" + value.id + "'>" + value.colonia + "</option>\n");
                    });
                } else {
                    municipioSelected = null;
                    $error_codigo.show();
                }

                $inputColonia
                    .val(null)
                    .trigger("change");

            }, 'json');
        });
    });

    $inputMunicipio.change(function() {
        if ($inputEstado.val() != 0 && $inputMunicipio.val() != 0 && $inputCodigoSearch.val() != "") {
            $inputColonia.html('');
            $.get('<?= Url::to('@web/municipio/colonia-ajax') ?>', {
                'estado_id': $inputEstado.val(),
                "municipio_id": $inputMunicipio.val()
            }, function(json) {
                if (json.length > 0) {
                    $.each(json, function(key, value) {
                        $inputColonia.append("<option value='" + value.id + "'>" + value.colonia + "</option>\n");
                    });
                } else {
                    municipioSelected = null;
                }

                $inputColonia
                    .val(null)
                    .trigger("change");

            }, 'json');
        }
    });

    /************************************
    / Estados y municipios
    /***********************************/
    function onEstadoChange() {
        var estado_id = $inputEstado.val();
        municipioSelected = estado_id == 0 ? null : municipioSelected;

        $inputMunicipio.html('');

        $.get('<?= Url::to('@web/municipio/municipios-ajax') ?>', {
            'estado_id': estado_id
        }, function(json) {
            $.each(json, function(key, value) {
                $inputMunicipio.append("<option value='" + key + "'>" + value + "</option>\n");
            });

            $inputMunicipio.val(municipioSelected);
            $inputMunicipio.trigger('change');

        }, 'json');
    }
</script>