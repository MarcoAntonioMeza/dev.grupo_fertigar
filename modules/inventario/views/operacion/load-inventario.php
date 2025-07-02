<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use app\models\inv\InventarioOperacion;

$this->title = 'CARGA DE INVENTARIO';
$this->params['breadcrumbs'][] = ['label' => "SOLICITUD DE AJUSTE DE INVENTARIO : #" . $model->id, 'url' => ['view-ajuste', 'id' => $model->id]];

?>

<style>
.modal-backdrop{
    z-index: 1040 !important;
}

.modal {
    z-index: 1050 !important;;
}
</style>

<div class="carga-inventario-create">
    <div class="row">
        <div class="col-sm-4">
            <?php /* ?><?= Html::input("text",null,false,[ "class" => "form-control ", "style" => "font-size:24px", "id" => "inputProductoSearch", "autocomplete" => "off"]) ?>*/?>
            <?= Html::button('GUARDAR INVENTARIO', ['class' => 'btn btn-success btn-lg btn-block btn-loading', "style" => "font-size:20px;","id" => "btnSaveProducto"]) ?>
        </div>
        <div class="col-sm-4">
        </div>
        <div class="col-sm-4">
            <?php if ($model->tipo == InventarioOperacion::TIPO_AJUSTE_COMPLETA): ?>
                <div class="form-group">
                    <?= Html::button('¿ PRODUCTO ENCONTRADO ?', ['class' => 'btn btn-info btn-lg btn-block ', "style" => "font-size:20px;","id" => "btnNewProducto", "data-target" => "#modal-producto", "data-toggle" => "modal",]) ?>
                </div>
            <?php endif ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">PRODUCTO</th>
                                    <th class="text-center">CANTIDAD EN INVENTARIO</th>
                                    <th class="text-center">ACCION</th>
                                </tr>
                            </thead>
                            <tbody class="container_body">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="display-none">
    <table>
        <tbody class="template_tr">
            <tr id = "item_tr_id_{{tr_item_id}}" class="text-center">
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_count"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_producto"]) ?></td>
                <td ><?= Html::input('number', null,false,["class" => "form-control text-center", "style" => "font-size:18px" ,  "step" => '0.001', "id"  => "table_cantidad"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="fade modal inmodal " id="modal-producto"   role="dialog" aria-labelledby="modal-create-label">
    <div class="modal-dialog modal-lg" style="width: 100%; max-width: 70%" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">PRODUCTO</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="ibox">
                            <div class="ibox-content">
                                <strong>PRODUCTO</strong>
                                <?= Select2::widget([
                                    'id' => 'input-producto_id',
                                    'name' => 'input_producto_id',
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
                                            'processResults' => new JsExpression('function(data, params){  return {results: data} }'),
                                        ],

                                    ],
                                    'options' => [
                                        'placeholder' => 'Buscar productos',
                                    ],
                                ]) ?>

                                <strong>CANTIDAD</strong>
                                <?= Html::input('number',null, false, [ "class" => "form-control", "style" => "font-size:24px", "id" => "inputCantidadProducto" ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::button('AGREGAR', ['class' => 'btn btn-primary btn-lg col-sm-3', "style" => "font-size:20px","id" => "btnAgregadoAdd"]) ?>
            </div>
        </div>
    </div>
</div>

<script>

var $btnNewProducto         = $('#btnNewProducto'),
    $btnAgregadoAdd         = $('#btnAgregadoAdd'),
    $inputProductoId        = $('#input-producto_id'),
    $btnSaveProducto        = $('#btnSaveProducto'),
    $inputProductoSearch    = $('#inputProductoSearch'),
    $container_body         = $('.container_body'),
    $inputCantidadProducto  = $('#inputCantidadProducto'),
    $template_tr            = $('.template_tr'),
    $VAR_PRODUCTO_ARRAY     = [],
    VAR_URL_PATH            = "<?= Url::to(['/']) ?>",
    VAR_SOLICITUD_ID        = <?= $model->id ?>;

$(function(){
    load_producto();
})

var load_producto = function(){
    $.get(VAR_URL_PATH + "inventario/operacion/get-producto-inventario", { solicitud_id : VAR_SOLICITUD_ID }, function($responseInventario){
        if ($responseInventario.code == 202) {
            $VAR_PRODUCTO_ARRAY = $responseInventario.inventario;
            render_inventario_producto();
        }
    },'json');
}


var render_inventario_producto = function(){
    $container_body.html(false);
    countItem = 1;
    $.each($VAR_PRODUCTO_ARRAY, function(key, item_producto){
        if (item_producto.item_id && item_producto.status == 10) {
            template_tr = $template_tr.html();
            template_tr = template_tr.replace("{{tr_item_id}}",item_producto.item_id);

            $container_body.append(template_tr);

            $tr        =  $("#item_tr_id_" + item_producto.item_id, $container_body);
            $tr.attr("data-item_id",item_producto.item_id);
            $("#table_count",$tr).html(countItem);
            $("#table_producto",$tr).html("[" + item_producto.producto_id +"] "+ item_producto.producto);
            $("#table_cantidad",$tr).val(item_producto.cantidad);
            $("#table_cantidad",$tr).attr("onchange","refresh_cantidad(this)");

            $tr.append("<td><button class='btn btn-danger' onclick='refresh_delete(this, "+ item_producto.item_id +")'><i class='fa fa-trash'></i> </button></td>");
            countItem++;
        }
    });
}


var refresh_cantidad = function(elem){

    $element    = $(elem);
    $ele_tr     = $(elem).closest('tr');
    $ele_tr_id  = $ele_tr.attr("data-item_id");
    $.each($VAR_PRODUCTO_ARRAY, function(key, item_producto){
        if (item_producto.item_id == $ele_tr_id ) {
            $VAR_PRODUCTO_ARRAY[key].cantidad =  $element.val() ? $element.val() : 0;
        }
    });
    render_inventario_producto();
}

var refresh_delete = function(elem, item_id){
    $element    = $(elem);
    $ele_tr     = $(elem).closest('tr');
    $ele_tr_id  = $ele_tr.attr("data-item_id");
    $.each($VAR_PRODUCTO_ARRAY, function(key, item_producto){
        if (item_producto.item_id == $ele_tr_id ) {
            $VAR_PRODUCTO_ARRAY[key].status =  1;
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.warning('SE ELIMINO ESTE PRODUCTO DEL INVENTARIO: ' + item_producto.producto);

        }
    });
    render_inventario_producto();
}



$btnAgregadoAdd.click(function(){
    if ($inputCantidadProducto.val() && $inputProductoId.val() ) {
        if (valid_producto($inputProductoId.val())) {
            $VAR_PRODUCTO_ARRAY.push({
                "item_id"       : ($VAR_PRODUCTO_ARRAY.length + 1000),
                "sucursal_id"   : null,
                "producto"      : $("#input-producto_id option:selected").text(),
                "producto_id"   : $inputProductoId.val(),
                "cantidad"      : $inputCantidadProducto.val(),
                "status"        : 10
            });
            render_inventario_producto();

            $('#modal-producto').modal('hide');
        }else{
            if (valid_producto_disabled($inputProductoId.val())) {
                $.each($VAR_PRODUCTO_ARRAY, function(key, item_producto){
                    if (parseInt(item_producto.producto_id) == parseInt(producto_id) && parseInt(item_producto.status) == 20 ) {
                        $VAR_PRODUCTO_ARRAY[key].status = 10;
                        $VAR_PRODUCTO_ARRAY[key].cantidad = $inputCantidadProducto.val();
                    }
                });

            }else{
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                toastr.error('ESTE PRODUCTO YA EXISTE EN EL LISTADO AJUSTE DE INVENTARIO');
            }

        }
    }else{
        toastr.options = {
            closeButton: true,
            progressBar: true,
            showMethod: 'slideDown',
            timeOut: 5000
        };
        toastr.error('VERIFICA TU INFORMACIÓN, INTENTA NUEVAMENTE');
    }
});


var valid_producto = function(producto_id){
    is_add = true;
    $.each($VAR_PRODUCTO_ARRAY, function(key, item_producto){
        if (parseInt(item_producto.producto_id) == parseInt(producto_id) && parseInt(item_producto.status) == 10 ) {
            is_add = false;
        }
    });

    return is_add;
}

var valid_producto_disabled = function(producto_id){
    is_add = false;
    $.each($VAR_PRODUCTO_ARRAY, function(key, item_producto){
        if (parseInt(item_producto.producto_id) == parseInt(producto_id) && parseInt(item_producto.status) == 20 ) {
            is_add = true;
        }
    });

    return is_add;
}

$btnSaveProducto.click(function(){
    show_loader();
    $.post( VAR_URL_PATH +"inventario/operacion/post-producto-inventario", { inventario_array : JSON.stringify($VAR_PRODUCTO_ARRAY), solicitud_id : VAR_SOLICITUD_ID }, function($responseSave){
        if ($responseSave.code == 202) {
            hide_loader();
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.success('SE GUARDO CORRECTAMENTE EL INVENTARIO');
            window.location.href = VAR_URL_PATH + "inventario/operacion/view-ajuste?id=" + VAR_SOLICITUD_ID;
        }
    });
});




var show_loader = function(){
    $('body').append('<div  id="page_loader" style="opacity: .8;z-index: 2040 !important;    position: fixed;top: 0;left: 0;z-index: 1040;width: 100vw;height: 100vh;background-color: #000;"><div class="spiner-example" style="position: fixed;top: 50%;left: 0;z-index: 2050 !important; width: 100%;height: 100%;"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div>');
}

var hide_loader = function(){
    $('#page_loader').remove();
}
</script>