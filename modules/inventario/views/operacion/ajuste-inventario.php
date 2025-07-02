<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;

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
                                    <th class="text-center">CANTIDAD EN SISTEMA</th>
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
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_cantidad_sistema", "style" => "font-size:24px; font-weight:bold"]) ?></td>
            </tr>
        </tbody>
    </table>
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
    $.get(VAR_URL_PATH + "inventario/operacion/get-producto-ajustar-inventario", { solicitud_id : VAR_SOLICITUD_ID }, function($responseInventario){
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

            if (item_producto.cantidad != item_producto.cantidad_sistema)
                $tr.css("background","#cbb70e");

            $("#table_count",$tr).html(countItem);
            $("#table_producto",$tr).html("[" + item_producto.producto_id +"] "+ item_producto.producto);
            $("#table_cantidad",$tr).val(item_producto.cantidad);
            $("#table_cantidad",$tr).attr("onchange","refresh_cantidad(this)");
            $("#table_cantidad_sistema",$tr).html(item_producto.cantidad_sistema +" "+ item_producto.unidad_medida);

            //$tr.append("<td><button class='btn btn-danger' onclick='refresh_delete(this, "+ item_producto.item_id +")'><i class='fa fa-trash'></i> </button></td>");
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

/*var refresh_delete = function(elem, item_id){
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
}*/

$btnSaveProducto.click(function(){
    show_loader();
    $.post( VAR_URL_PATH +"inventario/operacion/post-producto-ajuste-inventario", { inventario_array : JSON.stringify($VAR_PRODUCTO_ARRAY), solicitud_id : VAR_SOLICITUD_ID }, function($responseSave){
        if ($responseSave.code == 202) {
            hide_loader();
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.success('SE GUARDO CORRECTAMENTE EL INVENTARIO');
            window.location.href = VAR_URL_PATH + "inventario/operacion/view?id=" + VAR_SOLICITUD_ID;
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