<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\models\proveedor\Proveedor;
use app\models\esys\EsysListaDesplegable;
use app\models\esys\EsysDireccionCodigoPostal;
use app\models\esys\EsysDireccion;
?>

<style>
.modal-backdrop{
    z-index: 1040 !important;
}

.modal {
    z-index: 1050 !important;;
}
</style>

<div class="proveedores-proveedor-form">
    <?php $form = ActiveForm::begin(['id' => 'form-proveedor', 'options' => ['enctype' => 'multipart/form-data']]) ?>
    <?= $form->field($model, 'dir_obj_array')->hiddenInput()->label(false) ?>
    <div class="form-group ">
        <?= Html::submitButton($model->isNewRecord ? 'Crear proveedor' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white']) ?>
    </div>
    <div class="row">
        <div class="col-lg-7">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Información generales</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-6">

                            <div class="text-center">
                                <?php if ($model->avatar): ?>
                                    <?= Html::img(Url::to(['/avatar/' . $model->avatar]), [ "class" => "rounded-circle", "alt" => "avatar", "style" => 'width:150px; height:150px', 'id' => 'avatar_upload_id'])  ?>
                                <?php else: ?>
                                    <div class="container-avatar">
                                        <?= Html::img(null, [ "class" => "rounded-circle", "alt" => "avatar", "style" => 'width: 150px; height: 150px; border-style: solid; border-radius: 100%; text-align: center; border-width: 1px', 'id' => 'avatar_upload_id' ])  ?>
                                    </div>
                                <?php endif ?>
                            </div>


                            <?= $form->field($model, 'avatar_file')->fileInput(['accept' => 'image/png, image/jpeg, image/jpeg, image/jpeg']) ?>

                            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>


                            <?= $form->field($model, 'tel')->textInput(['maxlength' => true]) ?>

                            <?= $form->field($model, 'telefono_movil')->textInput(['maxlength' => true]) ?>

                        </div>
                        <div class="col-lg-6">

                            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

                            <?= $form->field($model, 'rfc')->textInput(['maxlength' => true]) ?>

                            <?= $form->field($model, 'razon_social')->textInput(['maxlength' => true]) ?>

                            <?= $form->field($model, 'persona_autorizadas')->textarea(['rows' => 3]); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Estatus</h5>
                        </div>
                        <div class="ibox-content">
                            <?= $form->field($model, 'status')->dropDownList(Proveedor::$statusList)->label(false) ?>
                        </div>
                    </div>
                </div>
            </div>
             <div class="ibox">
                <div class="ibox-title">
                    <h5>Información extra / Comentarios</h5>
                </div>
                <div class="ibox-content">

                    <?= $form->field($model, 'descripcion')->textarea(['rows' => 6])->label(false); ?>
                    <?= $form->field($model, 'notas')->textarea(['rows' => 6])->label(false); ?>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Dirección Fiscal / Real</h5>
                </div>
                <div class="ibox-content">
                    <?= Html::button('<i class = "fa fa-plus"></i>NUEVA DIRECCIÓN', [ "class" => "btn btn-warning", "data-target" => "#modal-direccion-entrega", "data-toggle" => "modal"]) ?>


                    <div class="div_content_direccion" style="margin: 10px">

                    </div>
                </div>
            </div>


            <div class="ibox">
                <div class="ibox-title">
                    <h5>CONDICIONES CREDITICIAS</h5>
                </div>
                <div class="ibox-content">

                    <?= $form->field($model, 'monto')->textInput(['type' => 'number']); ?>
                    <?= $form->field($model, 'plazo')->textInput(['type' => 'number']); ?>
                    <?= $form->field($model, 'terminos_condicion')->textarea(['rows' => 6]); ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>


<div class="fade modal inmodal " id="modal-direccion-entrega"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">AGREGAR DIRECCION</h4>
            </div>
            <?php $formDireccion = ActiveForm::begin(['id' => 'form-direccion-proveedor']) ?>
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
                                        <div class="col-sm-6">
                                            <?= $formDireccion->field($model->dir_obj, 'codigo_search')->textInput(['maxlength' => true, 'autocomplete' => 'off']) ?>
                                            <div id="error-codigo-postal" class="alert alert-danger" style="display: none">
                                                <div class="help-block"><strong>Codigo postal invalido</strong>, verifique nuevamente ó busque la dirección manualmente</div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <?= $formDireccion->field($model->dir_obj, 'tipo')->dropDownList(EsysDireccion::$tipoList)->label("TIPO DE DIRECCIÓN") ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <?= Html::label('Estado', 'esysdireccion-estado_id', ['class' => 'control-label']) ?>
                                            <?= Select2::widget([
                                                'id' => 'esysdireccion-estado_id',
                                                'name' => 'EsysDireccion[estado_id]',
                                                'language' => 'es',
                                                'value' => null,
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

                                            <?= Html::label('Colonia', 'esysdireccion-codigo_postal_id', ['class' => 'control-label']) ?>
                                            <?= Select2::widget([
                                                'id' => 'esysdireccion-codigo_postal_id',
                                                'name' => 'EsysDireccion[codigo_postal_id]',
                                                'language' => 'es',
                                                'value' =>  null,
                                                'data' =>  [],
                                                'pluginOptions' => [
                                                    'allowClear' => true,
                                                ],
                                                'options' => [
                                                    'placeholder' => 'Selecciona la colonia'
                                                ],
                                            ]) ?>
                                            <?= $formDireccion->field($model->dir_obj, 'num_ext')->textInput(['maxlength' => true]) ?>
                                        </div>
                                        <div class="col-sm-6">
                                            <?= Html::label('Deleg./Mpio.', 'esysdireccion-municipio_id', ['class' => 'control-label']) ?>
                                            <?= Select2::widget([
                                                'id' => 'esysdireccion-municipio_id',
                                                'name' => 'EsysDireccion[municipio_id]',
                                                'language' => 'es',
                                                'value' =>  null,
                                                'data' =>  [],
                                                'pluginOptions' => [
                                                    'allowClear' => true,
                                                ],
                                                'options' => [
                                                    'placeholder' => 'Selecciona el municipio'
                                                ],
                                            ]) ?>
                                            <?= $formDireccion->field($model->dir_obj, 'direccion')->textInput(['maxlength' => true]) ?>
                                            <?= $formDireccion->field($model->dir_obj, 'num_int')->textInput(['maxlength' => true]) ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <?= $formDireccion->field($model->dir_obj, 'referencia')->textInput(['maxlength' => true]) ?>
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
                <?= Html::button('AGREGAR DIRECCION', ['class' =>  'btn btn-lg btn-info',  "id" =>  'btnDireccionAdd' ]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>


<script>

    var $avatar_file            = $("#proveedor-avatar_file"),
    $error_codigo               = $('#error-codigo-postal'),
    $form_direccion_content     = $('.info-reenvio'),
    $btnDireccionAdd            = $('#btnDireccionAdd'),
    $divContentDireccion        = $('.div_content_direccion'),
    $inputDireccionArray        = $('#proveedor-dir_obj_array'),
    direccionArrayProveedor     = [];
    proveedorId                 = "<?= $model->id  ?>";
    municipioSelected           = null,
    $form_direccion = {
        $tipo              : $('#esysdireccion-tipo',$form_direccion_content),
        $inputEstado       : $('#esysdireccion-estado_id',$form_direccion_content),
        $inputMunicipio    : $('#esysdireccion-municipio_id',$form_direccion_content),
        $inputColonia      : $('#esysdireccion-codigo_postal_id',$form_direccion_content),
        $inputCodigoSearch : $('#esysdireccion-codigo_search',$form_direccion_content),
        $inputDireccion    : $('#esysdireccion-direccion',$form_direccion_content),
        $inputNumeroExt    : $('#esysdireccion-num_ext',$form_direccion_content),
        $inputNumeroInt    : $('#esysdireccion-num_int',$form_direccion_content),
        $inputReferencia   : $('#esysdireccion-referencia',$form_direccion_content),
    };


    $avatar_file.change(function(){
        $file = document.getElementById("proveedor-avatar_file").files;
        var img = document.getElementById('avatar_upload_id');
        var url = window.URL || window.webkitURL;

        if ($file[0]) {
            img.src = url.createObjectURL($file[0]);
        }
    });

    $(function(){
        load_init_direccion()
    });

    var load_init_direccion = function () {
        if (proveedorId) {
            $.get("<?= Url::to(['get-direccion-ajax']) ?>", { proveedor_id : proveedorId }, function( $response ){
                if ($response.code == 202 ) {
                    $.each($response.direccion, function(key, item){
                        direccionArrayProveedor.push({
                            "item_id"           : item.id,
                            "tipo"              : item.tipo,
                            "codigo_postal"     : item.codigo_postal,
                            "estado_id"         : item.estado_id,
                            "estado_text"       : item.estado,
                            "municipio_id"      : item.municipio_id,
                            "municipio_text"    : item.municipio,
                            "colonia_id"        : item.colonia_id,
                            "colonia_text"      : item.colonia,
                            "direccion"         : item.direccion,
                            "num_exterior"      : item.num_ext,
                            "num_interior"      : item.num_int,
                            "referencia"        : item.referencia,
                            "status"            : 10,
                            "update"            : 10,
                        });

                        render_direccion();
                    })
                }
            },'json');
        }
    }
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
        if ($form_direccion.$inputEstado.val() != 0 && $form_direccion.$inputMunicipio.val() != 0 && !$form_direccion.$inputCodigoSearch.val() ) {

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


    $btnDireccionAdd.click(function(){
        if ($form_direccion.$inputEstado.val() && $form_direccion.$inputMunicipio.val() ) {
            direccionArrayProveedor.push({
                "item_id"           : direccionArrayProveedor.length + 1,
                "tipo"              : $form_direccion.$tipo.val(),
                "codigo_postal"     : $form_direccion.$inputCodigoSearch.val(),
                "estado_id"         : $form_direccion.$inputEstado.val(),
                "estado_text"       : $("#esysdireccion-estado_id option:selected").text(),
                "municipio_id"      : $form_direccion.$inputMunicipio.val(),
                "municipio_text"    : $("#esysdireccion-municipio_id option:selected").text(),
                "colonia_id"        : $form_direccion.$inputColonia.val(),
                "colonia_text"      : $("#esysdireccion-codigo_postal_id option:selected").text(),
                "direccion"         : $form_direccion.$inputDireccion.val(),
                "num_exterior"      : $form_direccion.$inputNumeroExt.val(),
                "num_interior"      : $form_direccion.$inputNumeroInt.val(),
                "referencia"    : $form_direccion.$inputReferencia.val(),
                "status"            : 10,
                "update"            : 1,
            });

            render_direccion();
            $('#modal-direccion-entrega').modal('hide');
            $form_direccion.$inputEstado.val(null).change();
            $form_direccion.$inputMunicipio.val(null).change();
            $form_direccion.$inputColonia.val(null).change();
            clear_form($form_direccion);
            $form_direccion.$tipo.val(1).change();
            $inputDireccionArray.val(JSON.stringify(direccionArrayProveedor));
        }
    });


    var render_direccion = function(){
        $divContentDireccion.html(null);
        contentHtml = "";
        $.each(direccionArrayProveedor, function(key, item){
            if (item && item.status == 10 ) {
                contentHtml += '<div class="row" style="border-radius: 5%; border-style: solid; border-width: 1px;margin-top: 15px;">'+
                    '<div class="col-sm-4">'+
                        '<h2><strong>Estado: <p style="font-size: 16px;"><strong>'+ item.estado_text +'</strong></p></strong></h2>'+
                    '</div>'+
                    '<div class="col-sm-4">'+
                        '<h2><strong>Municipio: <p style="font-size: 16px;"><strong>'+ item.municipio_text +'</strong></p></strong></h2>'+
                    '</div>'+
                    '<div class="col-sm-4" style="">'+
                        '<h2><strong>Colonia: <p style="font-size: 16px;"><strong>'+ item.colonia_text +'</strong></p></strong></h2>'+
                    '</div>'+
                    '<div class="col-sm-12">'+
                        '<h5><strong>Direccion: </strong></h5> <p>'+ item.direccion +'</p>'+
                    '</div>'+
                    '<div class="col-sm-4">'+
                        '<h5><strong>N° Interno: </strong><small> '+ item.num_exterior +' </small></h5>'+
                    '</div>'+
                    '<div class="col-sm-4">'+
                        '<h5><strong>N° Externo: </strong><small>'+ item.num_interior +'</small></h5>'+
                    '</div>'+
                    '<div class="col-sm-4">'+
                        '<h5><strong>CP: </strong><small>'+ item.codigo_postal +'</small></h5>'+
                    '</div>'+
                    '<p class="label-danger" style="position: absolute;right: 33px; border-radius: 5%">'+ ( item.tipo == 2 ? 'FISCAL' : 'PERSONAL' ) +'</p>'+
                    '<buttom class="btn btn-danger" style="left: 15px;position: relative;top: -5px;" onclick="function_refresh('+ item.item_id +')"><i class="fa fa-remove"></i></buttom>'+
                '</div>';
            }

        });

        $divContentDireccion.html(contentHtml);
    }

    var function_refresh = function($ele_tr_id){


        $.each(direccionArrayProveedor, function(key, direccion){
            if (direccion) {
                if (parseInt(direccion.item_id) == $ele_tr_id ){

                    if (direccion.update ==  1)
                        direccionArrayProveedor.splice(key, 1 );

                    if (direccion.update == 10 )
                        direccion.status = 1;
                }

            }
        });
        $inputDireccionArray.val(JSON.stringify(direccionArrayProveedor));
        render_direccion();

    }

    var clear_form = function($form){
        $.each($form,function($key,$item){
            $item.val(null);
        });
    };

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
