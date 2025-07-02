<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
//use kartik\date\DatePicker;
use kartik\select2\Select2;
use app\models\user\User;
use app\models\venta\Venta;
use app\assets\BootboxAsset;
use app\models\sucursal\Sucursal;
use app\models\producto\Producto;
use app\models\esys\EsysListaDesplegable;
use app\models\esys\EsysDireccionCodigoPostal;
/* @var $this yii\web\View */
/* @var $model app\models\sucursales\Sucursal */
/* @var $form yii\widgets\ActiveForm */

BootboxAsset::register($this);

?>

<style>
.modal-backdrop{
    z-index: 1040 !important;
}

.modal {
    z-index: 1050 !important;;
}
</style>


<div class="tpv-venta-form">

    <?php $form = ActiveForm::begin() ?>
    <?= $form->field($model->venta_detalle, 'venta_detalle_array')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'total')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
    <?php if ($model->status == Venta::STATUS_PROCESO): ?>
        <div class="alert alert-warning">
            <strong>AVISO</strong>
            <p>La preventa se encuentra <strong>[PROCESO DE ENTREGA]</strong>, el producto agregado o eliminado afectara automaticamente <small  style="font-size:18px"><?= $model->sucursalVende->nombre ?> </small> => <small  style="font-size:18px"><?= $model->sucursal->nombre ?> </small></p>
        </div>
    <?php endif ?>


    <div class="row">
        <div class="col-md-8 col-sm-6 col-xs-12">
            <?= Html::Button("CONSULTA INVENTARIO", ["class" => "btn btn-warning float-right btn-zoom", "data-target"=>"#modal-producto", "data-toggle"=>"modal"]) ?>
            <div class="ibox" >
                <div class="ibox-content" style="height: 650px;overflow-y: auto;overflow-x: hidden;">
                    <div class="row">
                        <div class="col-sm-6">
                            <?= Select2::widget([
                                'id' => 'producto-nombre_id',
                                'name' => 'Producto[nombre]',
                                'data' => [],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'minimumInputLength' => 3,
                                    'language'   => [
                                        'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                    ],
                                    'ajax' => [
                                        'url'      => Url::to(['producto-ajax']),
                                        'dataType' => 'json',
                                        'cache'    => true,
                                        'processResults' => new JsExpression('function(data, params){ productoResult = data ; return {results: data} }'),
                                    ],

                                ],
                                'options' => [
                                    'placeholder' => 'Busca producto',
                                ],
                            ]) ?>
                        </div>
                        <div class="col-sm-2">
                            <?= Html::input("number",null,false,[ "class" => "form-control", "style" => "font-size:24px; text-align: center", "id" => "inputCantidadAdd"]) ?>
                        </div>
                        <div class="col-sm-2">
                            <div class="btn-group">
                            <?= Html::button('KG', ['class' => 'btn btn-default  ', "style" => "font-size:20px","id" => "btnChangeKg"]) ?>
                            <?= Html::button('PZ', ['class' => 'btn btn-default  ', "style" => "font-size:20px","id" => "btnChangePz"]) ?>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <?= Html::button('Agregar', ['class' => 'btn btn-primary btn-lg ', "style" => "font-size:20px","id" => "btnProductoAdd"]) ?>
                        </div>
                    </div>
                    <br/>
                    <div class="alert alert-danger alert_danger_message" style="display: none">
                    </div>
                    <div style="height: 100%">
                        <div class="table-responsive">
                            <table class="table table-bordered" style="font-size: 9px">
                                <thead>
                                    <tr>
                                        <th style="width: 5%" class="text-center">CLAVE</th>
                                        <th style="width: 16%" class="text-center">PRODUCTO</th>
                                        <th style="width: 16%" class="text-center">CANTIDAD</th>
                                        <th style="width: 15%" class="text-center">P. U. PUBLICO</th>
                                        <th style="width: 15%" class="text-center">P. U. MENUDEO</th>
                                        <th style="width: 15%" class="text-center">P. U. MAYOREO</th>
                                        <th style="width: 14%" class="text-center">PRECIO COSTO</th>
                                        <th style="width: 4%" class="text-center">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody class="content_producto" style="text-align: center; ">
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
                <hr/>
            </div>

        </div>

        <div class="col-md-4 col-sm-6 col-xs-12">

            <div class="ibox">
                <div class="ibox-content" >
                    <div class="content_total text-center">
                        <div class="row">
                            <div class="col">
                                <span class="h1 font-bold m-t block lbl_total_venta"> $ 00.00 </span>
                                <small class="text-muted m-b block">TOTAL</small>
                            </div>
                            <div class="col">
                                <button class="btn btn-danger  dim btn-large-dim" type="button" id="btnClearVenta" ><i class="fa fa-trash-o"></i></button>
                                <small class="text-muted m-b block">LIMPIAR</small>
                            </div>
                        </div>
                    </div>
                        <div  style="display:block;">

                                <?php /* ?>
                                <div class="container-checkbox" style="border-style: solid;padding-top: 15px;padding-left: 15px;border-width: 1px; background: #ffb402;color: #fff;font-weight: bold; display: inline-block;     width: 100%;">
                                    <?= $form->field($model,'is_especial')->checkbox() ?>
                                </div>

                                <div class="container-checkbox" style="border-style: solid;padding-top: 15px;padding-left: 15px;border-width: 1px; background: #4c0ba7;color: #fff;font-weight: bold; display: inline-block;    width: 45%;">
                                    <?= $form->field($model,'pay_credito')->checkbox([ 'checked' => $model->pay_credito == Venta::PAY_CREDITO_ON ? true: false ]) ?>
                                </div>
                                */?>

                        </div>

                    <?php /* ?>
                    <div class="row text-center">
                        <div class="col">
                            <button class="btn btn-danger  dim btn-large-dim" type="button" data-target="#modal-producto" data-toggle="modal"  ><i class="fa fa-search"></i></button>
                            <small class="text-muted m-b block">BUSCAR PRODUCTO</small>
                        </div>
                    </div>
                    */?>
                    <hr>
                    <strong>PREVENTA DE [TIENDA / CEDIS]: </strong>
                    <?= $form->field($model, 'sucursal_id')->widget(Select2::classname(),
                    [
                        'language' => 'es',
                        'data' => isset($model->sucursal_id)  && $model->sucursal_id ? [$model->sucursal_id => $model->sucursalVende->nombre ] : Sucursal::getAlmacenSucursal(),
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                        'options' => [
                            'placeholder' => 'Selecciona la sucursal ...',
                            'disabled' => $model->id ? true : false,
                        ],
                    ])->label(false) ?>


                    <div style="margin-top: 15px; margin-bottom: 15px; display: none" class="div_content_check">
                        <?= Html::button('<i class = "fa fa-plus"></i> DIRECCIÓN DE ENTREGA', [ "class" => "btn btn-warning", "data-target" => "#modal-direccion-entrega", "data-toggle" => "modal"]) ?>
                    </div>

                    <?= $form->field($model, 'cliente_id')->widget(Select2::classname(),
                    [
                        'language' => 'es',
                            'data' => isset($model->cliente)  && $model->cliente ? [$model->cliente->id => $model->cliente->nombre ." ". $model->cliente->apellidos] : [],
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
                                'placeholder' => 'Selecciona al cliente...',
                                'disabled' => $model->status == Venta::STATUS_PROCESO ? true : false,
                            ],

                    ]) ?>


                    <?= $form->field($model, 'ruta_sucursal_id')->widget(Select2::classname(),
                    [
                        'language' => 'es',
                        'data' => isset($model->sucursal)  && $model->sucursal ? [$model->sucursal->id => $model->sucursal->nombre ] : Sucursal::getRuta(),
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                        'options' => [
                            'placeholder' => 'Selecciona la sucursal a ruta...',
                            'disabled' => $model->status == Venta::STATUS_PROCESO ? true : false,
                        ],
                    ]) ?>

                    <div class="row text-center" style="font-size: 9px">
                        <div class="col">
                            <?= Html::checkbox(
                                "CheckPrecioPublico",
                                $model->tipo == Venta::TIPO_GENERAL ? true: false,
                                [
                                    "id"    => "check_publico_access",
                                    "class" => "modulo magic-checkbox"
                                ]
                            ) ?>
                            <?= Html::label("P. PUBLICO", "check_publico_access", ["style" => "display:inline"]) ?>
                        </div>
                        <div class="col">
                            <?= Html::checkbox(
                                "CheckPrecioMenudeo",
                                $model->tipo == Venta::TIPO_MENUDEO  ? true: false,
                                [
                                    "id"    => "check_menudeo_access",
                                    "class" => "modulo magic-checkbox"
                                ]
                            ) ?>
                            <?= Html::label("P. MENUDEO", "check_menudeo_access", ["style" => "display:inline"]) ?>
                        </div>
                        <div class="col">
                            <?= Html::checkbox(
                                "CheckPrecioMayoreo",
                                $model->tipo == Venta::TIPO_MAYOREO  ? true: false,
                                [
                                    "id"    => "check_mayoreo_access",
                                    "class" => "modulo magic-checkbox"
                                ]
                            ) ?>
                            <?= Html::label("P. MAYOREO", "check_mayoreo_access", ["style" => "display:inline"]) ?>

                        </div>
                    </div>


                    <div class="form-group" style="">
                        <?= Html::submitButton($model->isNewRecord ? 'GUARDAR' : 'Guardar cambios', ["id" => "btnOperacion",'class' => $model->isNewRecord ? 'btn btn-success btn-lg' : 'btn btn-primary btn-lg', 'style' => 'font-size: 20px; margin-top:10%' ]) ?>
                        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white btn-lg' , 'style' => 'font-size: 20px; margin-top:10%']) ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="fade modal inmodal " id="modal-direccion-entrega"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label"  >
        <div class="modal-dialog modal-lg" >
            <div class="modal-content">
                <!--Modal header-->
                 <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">AGREGAR DIRECCION</h4>
                </div>
                <!--Modal body-->
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="info-reenvio" >
                                <div class="ibox">
                                    <div class="ibox-content">
                                        <div id="error-add-reenvio" class="alert alert-danger" style="display: none">

                                        </div>
                                        <div id="success-add-reenvio" class="alert alert-success" style="display: none">

                                        </div>
                                        <br>

                                        <div class="row">
                                            <div class="col-sm-5">
                                                <?= $form->field($model->dir_obj, 'codigo_search')->textInput(['maxlength' => true, 'autocomplete' => 'off']) ?>
                                            </div>
                                            <div id="error-codigo-postal" class="alert alert-danger" style="display: none">
                                                <div class="help-block"><strong>Codigo postal invalido</strong>, verifique nuevamente ó busque la dirección manualmente</div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <?= Html::label('Estado', 'venta-estado_id', ['class' => 'control-label']) ?>
                                                <?= Select2::widget([
                                                    'id' => 'venta-estado_id',
                                                    'name' => 'EsysDireccion[estado_id]',
                                                    'language' => 'es',
                                                    'value' => isset($model->dir_obj->estado->id) ?  $model->dir_obj->estado->id  : null,
                                                    'data' => EsysListaDesplegable::getEstados(),
                                                    'pluginOptions' => [
                                                        'allowClear' => true,
                                                    ],
                                                    'options' => [
                                                        'placeholder' => 'Selecciona el estado',
                                                    ],
                                                    'pluginEvents' => [
                                                        "change" => "function(){ onEstadoReenvioChange() }",
                                                    ]
                                                ]) ?>

                                                <?= Html::label('Colonia', 'venta-codigo_postal_id', ['class' => 'control-label']) ?>
                                                <?= Select2::widget([
                                                    'id' => 'venta-codigo_postal_id',
                                                    'name' => 'EsysDireccion[codigo_postal_id]',
                                                    'language' => 'es',
                                                    'value' => isset($model->dir_obj->codigo_postal_id) ?  $model->dir_obj->codigo_postal_id  : null,
                                                    'data' => $model->dir_obj->codigo_postal_id ? EsysDireccionCodigoPostal::getColonia(['codigo_postal' => $model->dir_obj->codigo_search]) : [],
                                                    'pluginOptions' => [
                                                        'allowClear' => true,
                                                    ],
                                                    'options' => [
                                                        'placeholder' => 'Selecciona la colonia'
                                                    ],
                                                ]) ?>
                                            </div>
                                            <div class="col-sm-6">
                                                <?= Html::label('Deleg./Mpio.', 'venta-municipio_id', ['class' => 'control-label']) ?>
                                                <?= Select2::widget([
                                                    'id' => 'venta-municipio_id',
                                                    'name' => 'EsysDireccion[municipio_id]',
                                                    'language' => 'es',
                                                    'value' => isset($model->dir_obj->municipio_id) ?  $model->dir_obj->municipio_id  : null,
                                                    'data' => $model->dir_obj->estado_id? EsysListaDesplegable::getMunicipios(['estado_id' => $model->dir_obj->estado_id]): [],
                                                    'pluginOptions' => [
                                                        'allowClear' => true,
                                                    ],
                                                    'options' => [
                                                        'placeholder' => 'Selecciona el municipio'
                                                    ],
                                                ]) ?>
                                                <?= $form->field($model->dir_obj, 'direccion')->textInput(['maxlength' => true]) ?>
                                            </div>
                                        </div>

                                        <br>
                                        <br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Modal footer-->
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                    <?= Html::button('AGREGAR DIRECCION', ['class' =>  'btn btn-lg btn-info', 'data-dismiss' => 'modal']) ?>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>


<div class="display-none">
    <table>
        <tbody class="template_producto">
            <tr id = "producto_id_{{producto_id}}">
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_clave_id"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_producto","style" => "text-align:center;font-size: 16px;font-weight: bold;"]) ?></td>
                <td >
                    <?= Html::input('number', null,false,["class" => "text-center form-control" , "style" => "font-size: 14px;font-weight: bold;", "step" => "0.001","id"  => "table_cantidad"]) ?>
                    <?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_unidad_medida","style" => "text-align:center;font-size: 14px;font-weight: bold;"]) ?>
                    <?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_inventario","style" => "text-align:center;font-size: 10px;font-weight: bold;"]) ?>
                </td>
                <td ><?= Html::input('number', null,false,["class" => "text-center form-control" , "id"  => "table_publico"]) ?></td>
                <td ><?= Html::input('number', null,false,["class" => "text-center form-control" , "id"  => "table_menudeo"]) ?></td>
                <td ><?= Html::input('number', null,false,["class" => "text-center form-control" , "id"  => "table_mayoreo"]) ?></td>

                <?php /* ?>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_publico"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_menudeo"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_mayoreo"]) ?></td>
                */?>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_precio","style" => "text-align:center;font-size: 16px;font-weight: bold;"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>


<div class="display-none">
    <table>
        <tbody class="template_producto_search">
            <tr id = "producto_search_id_{{producto_search_id}}">
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_search_clave_id"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_search_producto","style" => "text-align:center"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_search_precio"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_search_bodega"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_search_tienda"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>


<div class="fade modal inmodal " id="modal-producto"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">BUSCAR PRODUCTO</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row panel">
                    <div class="col text-center panel-body">
                            <?= Html::input("text",null,false,[ "class" => "form-control ", "style" => "font-size:24px", "id" => "inputProductoSearch"]) ?>
                    </div>
                </div>
                <div style="height: 100%" class="ibox-content">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 20%" class="text-center">CLAVE</th>
                                <th style="width: 20%" class="text-center">PRODUCTO</th>
                                <th style="width: 20%" class="text-center">PRECIO PUBLICO</th>
                                <th style="width: 20%" class="text-center">INVENTARIO CEDIS</th>
                                <th style="width: 20%" class="text-center">INVENTARIO TIENDA</th>
                            </tr>
                        </thead>
                        <tbody class="content_search" style="text-align: center;">
                        </tbody>
                    </table>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>


<script>

    /*====================================================
    *                   VARIBLES GLOBALES
    *====================================================*/

    var $inputProductoAdd   = $('#producto-nombre_id'),
        $btnProductoAdd     = $('#btnProductoAdd'),
        $inputCantidadAdd   = $('#inputCantidadAdd'),
        $inputProductoSearch= $('#inputProductoSearch'),
        $template_producto          = $('.template_producto'),
        $template_producto_search   = $('.template_producto_search'),
        $content_producto   = $(".content_producto"),
        $content_search     = $(".content_search"),
        $inputventaDetalle  = $("#ventadetalle-venta_detalle_array"),
        $ventaID            = $('#venta-id'),
        $inputventaTotal    = $("#venta-total"),
        $inputventaCliente  = $("#venta-cliente_id"),
        $inputRutaSucursal  = $("#venta-ruta_sucursal_id"),
        $inputSucursal      = $("#venta-sucursal_id"),
        //$inputVentaCheck    = $('#venta-is_especial'),
        $btnClearVenta      = $("#btnClearVenta"),
        var_is_subproducto  = "<?= Producto::TIPO_SUBPRODUCTO ?>",
        IsVentaCheck        = false,
        $error_codigo       = $('#error-codigo-postal'),
        municipioSelected   = null;
        isConversionKg        = false;
        isConversionPz        = false;
        validExistencia       = <?= $model->status == Venta::STATUS_PROCESO ? 10 : 20 ?>;
        unidadMedidaOriginal  = 0;
        productoSearch      = [],
        pertence_sucursal_id= null;

        $form_direccion_content   = $('.info-reenvio'),
        $form_direccion = {
            $inputEstado       : $('#venta-estado_id',$form_direccion_content),
            $inputMunicipio    : $('#venta-municipio_id',$form_direccion_content),
            $inputColonia      : $('#venta-codigo_postal_id',$form_direccion_content),
            $inputCodigoSearch : $('#esysdireccion-codigo_search',$form_direccion_content),
            $inputDireccion    : $('#esysdireccion-direccion',$form_direccion_content),
        },
        containerArray      = [];

    $(function(){
        $('body').addClass('mini-navbar');
        initDetalleEnvio();
        //$inputVentaCheck.prop( "checked", true ).change();
    });

    /*====================================================
    *                 EVENTENS
    *====================================================*/

    $btnProductoAdd.click(function(){
        $(".alert_danger_message").hide();
        if ($inputCantidadAdd.val() && $inputProductoAdd.val()) {
            add_producto();
        }else{
            $(".alert_danger_message").show();
            $(".alert_danger_message").html("Verifica tu información, intenta nuevamente !");
        }
    });

    $btnClearVenta.click(function(){
        containerArray =  [];
        render_template();
    });

    $('#btnChangeKg').click(function(){
        isConversionKg = !isConversionKg;
        isConversionPz = !isConversionKg;
        render_conversion();
    });

    $('#btnChangePz').click(function(){
        isConversionPz = !isConversionPz;
        isConversionKg = !isConversionPz;
        render_conversion();
    });

    var render_conversion = function(){

        if (isConversionKg){
            $('#btnChangeKg').addClass('btn-danger').removeClass('btn-default');
            $('#btnChangePz').addClass('btn-default').removeClass('btn-danger');
        }
        else
            $('#btnChangeKg').addClass('btn-default').removeClass('btn-danger');



        if (isConversionPz){
            $('#btnChangePz').addClass('btn-danger').removeClass('btn-default');
            $('#btnChangeKg').addClass('btn-default').removeClass('btn-danger');
        }
        else
            $('#btnChangePz').addClass('btn-default').removeClass('btn-danger');
    }

    $inputProductoAdd.change(function(){
        isConversionKg = false;
        isConversionPz = false;
        if ($inputProductoAdd.val()) {
            $.each(productoResult, function(key, item_producto){

                if (item_producto.id == parseInt($inputProductoAdd.val())) {
                    if (item_producto.tipo_medida == 20)
                        isConversionKg = true;
                    else
                        isConversionPz = true;

                }
            })
        }
        render_conversion();
    });

    $('#btnOperacion').on('click', function(event){
        event.preventDefault();

        is_tipo_precio = false;

        if ($("#check_publico_access").is(':checked'))
            is_tipo_precio = true;

        if ($("#check_menudeo_access").is(':checked'))
            is_tipo_precio = true;

        if ($("#check_mayoreo_access").is(':checked'))
            is_tipo_precio = true;



        $('#modal-direccion-entrega').modal('hide');


        if (!$inputventaCliente.val()) {
            bootbox.alert("DEBES SELECCIONAR UN CLIENTE A LA PRECAPTURA, INTENTA NUEVAMENTE !");
            return false;
        }

        if (!is_tipo_precio) {
            bootbox.alert("DEBES INDICAR EL TIPO DE PRECIO A OTORGAR  (PUBLICO, MAYOREO, MENUDEO), INTENA NUEVAMENTE!");
            return false;
        }

        if (!$inputRutaSucursal.val()) {
            bootbox.alert("DEBES SELECCIONAR UNA RUTA, INTENTA NUEVAMENTE !");
            return false;
        }

        if (containerArray.length ==  0 ) {
            bootbox.alert("Debes ingresar minimo un producto para continuar !");
            return false;
        }

        is_valid_check = true;
        message_precio = "";
        $.each(containerArray, function(key, producto){
            if (producto.item_id) {
                if(producto.status == 10){
                    if ($("#check_publico_access").is(':checked')){
                        if (parseFloat(producto.publico) <= 0 || (producto.publico == '' || producto.publico == null ) ){
                            is_valid_check = false; message_precio = "PRECIO PUBLICO";
                        }
                    }

                    if ($("#check_menudeo_access").is(':checked')){
                        console.log();
                        if (parseFloat(producto.menudeo) <= 0 || (producto.menudeo == '' || producto.menudeo == null ) ){
                            is_valid_check = false; message_precio = "PRECIO MENUDEO";
                        }
                    }

                    if ($("#check_mayoreo_access").is(':checked') ){
                        if (parseFloat(producto.mayoreo) <= 0 || (producto.mayoreo == '' || producto.mayoreo == null ) ){
                            is_valid_check = false; message_precio = "PRECIO MAYOREO";
                        }
                    }
                }
            }
        });


        if (!is_valid_check){
            bootbox.alert("Existen productos sin un precio de venta: ["+ message_precio +"], ingresa un precio a otorgar para continuar.!");
            return false;
        }


        bootbox.confirm("¿Estas seguro que deseas finalizar la pre-captura ?", function(result) {
            if (result) {
                $('#btnOperacion').submit();
            }else{

            };
        });
    });

    /*$inputVentaCheck.change(function(){
        if ($(this).is(':checked')){
            IsVentaCheck = true;
            $(".div_content_check").show();
        }
        else{
            alert("SE ELIMINARA LOS PRODUCTOS INGRESADOS, ¿ ESTAS SEGURO QUE DESEAS CONTINUAR?");
            IsVentaCheck = false;
            $(".div_content_check").hide();
            containerArray =  [];
            render_template();
            $.each($form_direccion,function($key,$item){
                $item.val(null).change();
            });
        }
    });*/

    $inputProductoSearch.keypress(function(e){
        productoSearch = [];
        render_template_search();

        if ($(this).val()) {
            $.get("<?= Url::to(['search-producto-nombre'])?>",{ nombre :  $(this).val() },function($response){
                productoSearch = $response.productos;
                render_template_search();
            },'json');
        }
    });

    $("#check_publico_access").change(function(){
        $( "#check_menudeo_access" ).prop( "checked", false );
        $( "#check_mayoreo_access" ).prop( "checked", false );

        render_template();
    });

    $("#check_menudeo_access").change(function(){
        $( "#check_publico_access" ).prop( "checked", false );
        $( "#check_mayoreo_access" ).prop( "checked", false );

        render_template();
    });

    $("#check_mayoreo_access").change(function(){
        $( "#check_publico_access" ).prop( "checked", false );
        $( "#check_menudeo_access" ).prop( "checked", false );

        render_template();
    });

    $form_direccion.$inputCodigoSearch.change(function( event) {
        event.preventDefault();

        $form_direccion.$inputColonia.html('');
        $form_direccion.$inputEstado.val(null).trigger("change");

        var codigo_search = $form_direccion.$inputCodigoSearch.val();
        if (codigo_search) {
            $.get('<?= Url::to('@web/municipio/codigo-postal-ajax') ?>', {'codigo_postal' : codigo_search}, function(jsonCodigoPostal) {
                if(jsonCodigoPostal.length > 0){
                    $error_codigo.hide();
                    $form_direccion.$inputEstado.val(jsonCodigoPostal[0].estado_id); // Select the option with a value of '1'
                    $form_direccion.$inputEstado.trigger('change');


                    $.each(jsonCodigoPostal, function(key, colonia){
                        $form_direccion.$inputColonia.append("<option value='" + colonia.id + "'>" + colonia.colonia + "</option>\n");
                    });


                    municipioSelected = parseInt(jsonCodigoPostal[0].municipio_id);
                }
                else{
                    municipioSelected  = null;
                    $error_codigo.show();
                }
            }, 'json');
        }
    });

    $form_direccion.$inputMunicipio.change(function(){
        if ($form_direccion.$inputEstado.val() != 0 && $form_direccion.$inputMunicipio.val() != 0 && $form_direccion.$inputCodigoSearch.val() ) {

            $form_direccion.$inputColonia.html('');
            if ($form_direccion.$inputMunicipio.val()) {
                $.get('<?= Url::to('@web/municipio/colonia-ajax') ?>', {'estado_id' : $form_direccion.$inputEstado.val(), "municipio_id": $form_direccion.$inputMunicipio.val(), 'codigo_postal' : $form_direccion.$inputColonia.val()}, function(json) {
                      if(json.length > 0){
                        $.each(json, function(key, value){
                            $form_direccion.$inputColonia.append("<option value='" + value.id + "'>" + value.colonia + "</option>\n");
                        });
                      }
                      else
                          municipioSelected  = null;


                }, 'json');
            }
        }
    });

    $inputSucursal.change(function(){
        if (containerArray.length > 0  && pertence_sucursal_id != $inputSucursal.val()) {
            bootbox.confirm("SE BORRAR TODOS LOS PRODUCTOS INGRESADOS, ¿ DESEAS CONTINUAR ?", function(result) {
                if (result) {
                    containerArray = [];
                    render_template();
                    pertence_sucursal_id  = $inputSucursal.val();
                }else{
                    $inputSucursal.val(pertence_sucursal_id).trigger('change');
                };
            });
        }else
            pertence_sucursal_id  = $inputSucursal.val();
    });

    /*====================================================
    *                   FUNCTIONS
    *====================================================*/

    var add_producto = function(){
        $(".alert_danger_message").hide();
        if ($inputProductoAdd.val()) {
            if ($inputSucursal.val()) {
                $.get("<?= Url::to(['search-producto-id'])?>",{ id :  $inputProductoAdd.val() , sucursal_id : $inputSucursal.val() },function($response){
                    if ($response.code == 202) {
                        productoArray = $response.producto;

                        is_add = true;
                        if (validExistencia == 10 ) {
                            if (productoArray.is_subproducto == var_is_subproducto ) {
                                producto_restante  =  productoArray.sub_existencia - ( parseInt($inputCantidadAdd.val()) * productoArray.sub_cantidad_equivalente );
                                if (!IsVentaCheck) {
                                    if (producto_restante < 0 ) {
                                        is_add = false;
                                        $(".alert_danger_message").show();
                                        $(".alert_danger_message").html("La existencia es de " + productoArray.sub_existencia + " [" + productoArray.sub_producto_nombre + "]["+ productoArray.sub_producto_unidad+"], no cumple con las cantidades para ingresarlo, revisar el inventario");
                                    }
                                }
                            }else{
                                producto_restante  =  productoArray.existencia - $inputCantidadAdd.val() ;
                                if (!IsVentaCheck) {
                                    if (producto_restante < 0 ) {
                                        is_add = false;
                                        $(".alert_danger_message").show();
                                        $(".alert_danger_message").html("La existencia es de " + productoArray.existencia + " [" + productoArray.nombre + "]["+ productoArray.tipo_medida_text+"], no cumple con las cantidades para ingresarlo, revisar el inventario");
                                    }
                                }
                            }
                        }


                        if (is_add) {

                            productoItem = {
                                "item_id"            : containerArray.length + 1,
                                "producto_id"        : productoArray.id,
                                "producto_nombre"    : productoArray.nombre,
                                "producto_clave"     : productoArray.clave,
                                "mayoreo"            : productoArray.mayoreo,
                                "menudeo"            : productoArray.menudeo,
                                "publico"            : productoArray.publico,
                                "costo"              : productoArray.costo,
                                "existencia_bodega"  : productoArray.existencia_bodega,
                                "existencia_tienda"  : productoArray.existencia_tienda,
                                "precio_venta"       : 0,
                                "producto_proveedor" : productoArray.proveedor,
                                "producto_unidad"    : productoArray.tipo_medida_text,
                                "producto_unidad_medida"      : productoArray.tipo_medida,
                                "is_conversion"      : ( productoArray.tipo_medida == 10 ? (isConversionPz ? 10 : 1 ) : (isConversionKg ? 10 : 1 )  ),
                                "cantidad"           : $inputCantidadAdd.val(),
                                "status"             : 10,
                                "origen"             : 1,
                            }

                            containerArray.push(productoItem);
                        }

                        productoArray = [];
                        render_template();
                        $inputProductoAdd.val(null).change();
                        $inputCantidadAdd.val(null);

                    }
                    if ($response.code == 10 ) {
                        $(".alert_danger_message").show();
                        $(".alert_danger_message").html($response.message);
                    }
                });
            }else{
                $(".alert_danger_message").show();
                $(".alert_danger_message").html("Debes seleccionar la sucursal a la que pertenece la PRE-VENTA !");
            }
        }
    };

    var initDetalleEnvio = function(){

        containerArray = [];

        if ($ventaID.val()) {
            $.get('<?= Url::to('venta-detalle-ajax') ?>', {'venta_id' : $ventaID.val() }, function(json) {
                $.each(json.rows, function(key, item){
                    if (item.id) {
                        paquete = {
                            "item_id"            : item.id,
                            "producto_id"        : item.producto_id,
                            "producto_nombre"    : item.producto,
                            "producto_clave"     : item.producto_clave,
                            "mayoreo"            : item.mayoreo,
                            "menudeo"            : item.menudeo,
                            "costo"              : item.costo,
                            "publico"            : item.publico,
                            "precio_venta"       : item.precio_venta,
                            "producto_proveedor" : item.producto_proveedor,
                            "producto_unidad"    : item.tipo_medida_text,
                            "cantidad"           : parseInt(item.is_conversion) == 20 ? item.cantidad : item.conversion_cantidad,
                            "is_conversion"      : parseInt(item.is_conversion) == 20 ? 10 : 1,
                            "status"             : 10,
                            "update"             : $ventaID.val() ? 10 : 1,
                            "origen"             : 2
                        };
                    }
                    containerArray.push(paquete);
                });
            render_template();
            }, 'json');

            //if(parseInt(getCheckVenta)  == 10)
                //$inputVentaCheck.prop( "checked", true ).change();
        }
    };

    var render_template = function()
    {
        $content_producto.html("");
        total_venta = 0;

        $.each(containerArray, function(key, producto){
            if (producto.item_id) {
                if(producto.status == 10){
                    template_producto = $template_producto.html();
                    template_producto = template_producto.replace("{{producto_id}}",producto.item_id);

                    $content_producto.append(template_producto);


                    $tr        =  $("#producto_id_" + producto.item_id, $content_producto);
                    $tr.attr("data-item_id",producto.item_id);

                    if (producto.is_conversion == 1)
                        $tr.attr("style","background:#ed5565;border:0px");


                    if ($("#check_publico_access").is(':checked'))
                        producto.precio_venta = producto.publico ? producto.publico : 0; //producto.precio_venta = producto.publico ? producto.publico : producto.publico;

                    if ($("#check_menudeo_access").is(':checked'))
                        producto.precio_venta = producto.menudeo ? producto.menudeo : 0; //producto.precio_venta = producto.menudeo ? producto.menudeo : producto.publico;

                    if ($("#check_mayoreo_access").is(':checked'))
                        producto.precio_venta = producto.mayoreo ? producto.mayoreo : 0; //producto.precio_venta = producto.mayoreo ? producto.mayoreo : producto.publico;

                    $("#table_clave_id",$tr).html(producto.producto_clave);
                    $("#table_producto",$tr).html(producto.producto_nombre);
                    $("#table_publico",$tr).val(producto.publico);
                    $("#table_publico",$tr).attr("onchange","refresh_change(this,'PRECIO_PUBLICO')");
                    $("#table_menudeo",$tr).val(producto.menudeo);
                    $("#table_menudeo",$tr).attr("onchange","refresh_change(this,'PRECIO_MENUDEO')");
                    $("#table_mayoreo",$tr).val(producto.mayoreo);
                    $("#table_mayoreo",$tr).attr("onchange","refresh_change(this,'PRECIO_MAYOREO')");


                    $("#table_cantidad",$tr).val(producto.cantidad);
                    $("#table_cantidad",$tr).attr("onchange","refresh_cantidad(this)");
                    $("#table_unidad_medida",$tr).html(" ("+ ( producto.is_conversion == 1 ? ( producto.producto_unidad == 10 ? 'Kilos' : 'Piezas' )  : producto.producto_unidad ) +")");
                    $("#table_inventario",$tr).html(" CEDIS: "+ producto.existencia_bodega  +"/ TIENDA: " +producto.existencia_tienda);

                    if ( producto.is_conversion == 10 ){
                        $("#table_precio",$tr).html( btf.conta.money(producto.precio_venta * producto.cantidad ) );
                        total_venta = total_venta + (producto.precio_venta * producto.cantidad);
                    }


                    $tr.append("<td><button type='button' class='btn btn-warning btn-circle' onclick='refresh_paquete(this)'><i class='fa fa-trash'></i></button></td>");
                }
            }
        });

        $('.lbl_total_venta').html(btf.conta.money(total_venta));
        $inputventaTotal.val(total_venta);

        $inputventaDetalle.val(JSON.stringify(containerArray));
    };

    var refresh_paquete = function(ele){
        $ele_tr        = $(ele).closest('tr');
        $ele_tr_id  = $ele_tr.attr("data-item_id");

        $.each(containerArray, function(key, paquete){
            if (paquete.item_id == $ele_tr_id ){
                if (paquete.origen ==  1)
                    containerArray.splice(key, 1 );

                if (paquete.origen == 2 )
                    paquete.status = 1;
            }
        });
        $inputventaDetalle.val(JSON.stringify(containerArray));
        render_template();
    }

    var refresh_cantidad = function(ele)
    {
        $ele_input_val    = $(ele);
        $ele_tr         = $(ele).closest('tr');
        $ele_tr_id      = $ele_tr.attr("data-item_id");
        $.each(containerArray, function(key, paquete){
            if (paquete.item_id == $ele_tr_id  ){
                containerArray[key].cantidad =  parseFloat($ele_input_val.val());
            }
        });

        $inputventaDetalle.val(JSON.stringify(containerArray));
        render_template();
    }

    var refresh_change = function(ele,inputChange){
        $ele_input_val    = $(ele);
        $ele_tr         = $(ele).closest('tr');
        $ele_tr_id      = $ele_tr.attr("data-item_id");


        $.each(containerArray, function(key, paquete){
            if (paquete.item_id == $ele_tr_id  ){
                if ( $ele_input_val.val() >= paquete.costo ) {
                    switch(inputChange){
                        case 'PRECIO_PUBLICO':
                            paquete.publico = $ele_input_val.val();
                            update_precio_producto(paquete.producto_id,paquete.publico,10);
                        break;
                        case 'PRECIO_MENUDEO':
                            paquete.menudeo = $ele_input_val.val();
                            update_precio_producto(paquete.producto_id,paquete.menudeo,20);
                        break;
                        case 'PRECIO_MAYOREO':
                            paquete.mayoreo = $ele_input_val.val();
                            update_precio_producto(paquete.producto_id,paquete.mayoreo,30);
                        break;
                    }
                }else{
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        showMethod: 'slideDown',
                        timeOut: 5000
                    };
                    toastr.error('El precio no puede ser menor al costo de adquisición - PRECIO DE ADQUISICION ES: [ $'+ paquete.costo +' ]', 'Aviso - PRODUCTO');
                }
            }
        });

        $inputventaDetalle.val(JSON.stringify(containerArray));
        render_template();
    }

    var update_precio_producto = function(producto_id, precio, tipo){
        $.post("<?= Url::to(['update-precio'])  ?>", { producto_id, precio, tipo },function($response){
            if ($response.code == 202 ) {
                console.log($response.message);
            }
        });
    }

    var render_template_search = function()
    {
        $content_search.html("");


        $.each(productoSearch, function(key, producto){
            if (producto.id) {

                template_producto_search = $template_producto_search.html();
                template_producto_search = template_producto_search.replace("{{producto_search_id}}",producto.id);

                $content_search.append(template_producto_search);


                $tr        =  $("#producto_search_id_" + producto.id, $content_search);
                $tr.attr("data-item_id",producto.id);

                $("#table_search_clave_id",$tr).html(producto.clave);
                $("#table_search_producto",$tr).html(producto.nombre);
                $("#table_search_precio",$tr).html( btf.conta.money(producto.publico ) );
                $("#table_search_bodega",$tr).html( producto.existencia_bodega  );
                $("#table_search_tienda",$tr).html( producto.existencia_tienda  );

            }
        });
    };

    var select_producto = function(ele){
        $('#modal-producto').modal('hide');
        $ele_tr     = $(ele).closest('tr');
        $ele_tr_id  = $ele_tr.attr("data-item_id");
        $.each(productoSearch, function(key, p_search){
            if (p_search.id == $ele_tr_id ) {
                $inputProductoAdd.val(p_search.clave);
            }
        });
    }

    /************************************
    / Estados y municipios
    /***********************************/
    function onEstadoReenvioChange() {
        var estado_id = $form_direccion.$inputEstado.val();
        municipioSelected = estado_id == 0 ? null : municipioSelected;

        $form_direccion.$inputMunicipio.html('');

        if (  estado_id ||  municipioSelected ) {
            $.get('<?= Url::to('@web/municipio/municipios-ajax') ?>', {'estado_id' : estado_id}, function(json) {
                $.each(json, function(key, value){
                    $form_direccion.$inputMunicipio.append("<option value='" + key + "'>" + value + "</option>\n");
                });

                $form_direccion.$inputMunicipio.val(municipioSelected); // Select the option with a value of '1'
                $form_direccion.$inputMunicipio.trigger('change');

            }, 'json');
        }

    }



</script>