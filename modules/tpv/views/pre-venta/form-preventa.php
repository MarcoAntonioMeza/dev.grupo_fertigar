<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;

$this->title = 'VERIFICACION';
$this->params['breadcrumbs'][] = ['label' => 'PREVENTAS POR VERIFICAR', 'url' => ['index-comanda-autorizacion']];
$this->params['breadcrumbs'][] = ['label' => $this->title ];

?>


<style>
.modal-backdrop{
    z-index: 1040 !important;
}

.modal {
    z-index: 1050 !important;;
}
</style>

<div class="venta-preventa-form">
    <div class="ibox">
        <div class="ibox-content">
            <div class="row">
                <div class="col-sm-8">
                    <h2>CLIENTE: <?= $model->cliente_id ? $model->cliente->nombreCompleto : 'PUBLICO GENERAL' ?></h2>
                </div>
                <div class="col-sm-4 text-center">
                    <p style="font-size:28px; font-weight: 700; text-decoration: underline;">FOLIO: #<?= $model->id ?></p>
                </div>
            </div>
            <p>PRODUCTOS SOLICITADOS:</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>PRODUCTO</th>
                        <th class="text-center">CANTIDAD</th>
                        <th class="text-center">REEMPLAZAR</th>
                        <th class="text-center">QUITAR</th>
                    </tr>
                </thead>
                <tbody class="container_productos">

                </tbody>
            </table>

            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <?= Html::Button('ENVIAR PREVENTA', ['class' => 'btn btn-primary btn-zoom', "style" => "padding:2%", 'id' =>"btSavePreventa"]) ?>
                        <?= Html::a('CANCELAR', ['index-comanda-autorizacion'], ['class' => 'btn btn-white btn-zoom',"style" => "padding:2%"]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="fade modal inmodal " id="modal-reemplazo"   role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">REEMPLAZAR</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="ibox">
                    <div class="ibox-content">
                        <p>PRODUCTO A REEMPLAZAR: </p>
                        <?= Select2::widget([
                            'id' => 'reemplazo-select_id',
                            'name' => 'reemplazo_select_id',
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
                                'placeholder' => '-------------SELECT--------------',
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default btn-zoom" type="button">Close</button>
                <?= Html::Button('REEMPLAZAR PRODUCTO', ['class' => 'btn btn-primary btn-zoom', "style" => "padding:2%", 'id' =>"btnReemplazo"]) ?>
            </div>
        </div>
    </div>
</div>

<script>

var VAR_ITEM_ID         = <?= $model->id ?>;
    $containerProducto  = $('.container_productos'),
    VAR_PRODUCTO_ACTIVO = 10,
    VAR_REEMPLAZO_ID    = null,
    VAR_PRODUCTO_REMOVE = 20,
    preventaDetail      = [];
$(function(){
    load_producto();
});

var load_producto = function(){
    $.get("<?= Url::to(["get-detail-preventa-almacen"]) ?>",{ preventa_id : VAR_ITEM_ID }, function(response){
        if (response.code == 202) {
            preventaDetail = response.detail;
            render_producto();
        }
    });
}

var render_producto = function(){
    $containerProducto.html(false);
    contentHtml ="";

    $.each(preventaDetail, function(key_producto, item_producto){
        if (item_producto.status == VAR_PRODUCTO_ACTIVO) {
            contentHtml += "<tr>"+
                "<td class='text-center' ><p style='text-decoration:underline; font-weight:700'>"+ item_producto.producto + "</p></td>"+
                "<td><input type='number' step='0.01'  onchange='funct_refresh_cantidad("+ item_producto.detail_id +",this)'  class='form-control text-center' value='"+ item_producto.cantidad + "'/></td>"+
                "<td class='text-center'><button class='btn btn-circle btn-success' ONCLICK='funct_reemplazar_producto("+ item_producto.detail_id +")'><i class='fa  fa-exchange'></i></button></td>"+
                "<td class='text-center'><button class='btn btn-circle btn-danger' onclick='funct_remove_producto("+ item_producto.detail_id +")'><i class='fa fa-trash'></i></button></td>"+
            "</tr>";
        }
    });

    $containerProducto.html(contentHtml);
}


var funct_refresh_cantidad = function(detail_id, elem){
    $.each(preventaDetail, function(key_producto, item_producto){
        if (item_producto.detail_id == detail_id) {
            $.get("<?= Url::to(["get-valida-inventario-almacen"]) ?>",{ producto_id : item_producto.producto_id, cantidad : $(elem).val(), sucursal_id : item_producto.sucursal_id }, function(response){
                if (response.code == 202) {
                    if (parseFloat(response.disponible) >=  parseFloat($(elem).val()))
                        preventaDetail[key_producto].cantidad = $(elem).val();
                    else{
                        toastr.options = {
                            closeButton: true,
                            progressBar: true,
                            showMethod: 'slideDown',
                            timeOut: 5000
                        };
                        toastr.warning('INVENTARIO INSUFICIENTE, DISPONIBLE : ' + response.disponible , 'PREVENTA');
                    }

                    render_producto();
                }else{
                     toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        showMethod: 'slideDown',
                        timeOut: 5000
                    };
                    toastr.error('ERROR AL VALIDAR EL INVENTARIO, INTENTA NUEVAMENTE ', 'PREVENTA');
                }
            });
        }
    });
}

var funct_remove_producto = function(detail_id){
    $.each(preventaDetail, function(key_producto, item_producto){
        if (item_producto.detail_id == detail_id) {
            preventaDetail[key_producto].status = VAR_PRODUCTO_REMOVE;
        }
    });
    render_producto();
}

var funct_reemplazar_producto = function(detail_id){
    $('#modal-reemplazo').modal('show');
    VAR_REEMPLAZO_ID = detail_id;
}


$('#btSavePreventa').click(function(){

    $.each(preventaDetail, function(key_producto, item_producto){
        is_stock = false;
        if (item_producto.status == VAR_PRODUCTO_ACTIVO && item_producto.cantidad <= 0 ) {
            is_stock = true;
        }
    });

    if (!is_stock) {
        if (confirm("¿ ESTAS SEGURO QUE DESEAS ENVIAR LA PREVENTA ?")) {
            show_loader();
            $.post("<?= Url::to(["post-autorizar-preventa"]) ?>", { preventaDetailObject : preventaDetail, preventa_id : VAR_ITEM_ID }, function(response){
                if (response.code == 202) {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        showMethod: 'slideDown',
                        timeOut: 5000
                    };
                    toastr.success('SE ENVIO CORRECTAMENTE LA PREVENTA', 'PREVENTA');

                    window.location.href= "<?= Url::to(["index-comanda-autorizacion"])  ?>"
                }else{
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        showMethod: 'slideDown',
                        timeOut: 5000
                    };
                    toastr.error('OCURRIO UN ERROR, INTENTA NUEVAMENTE', 'PREVENTA');
                }
                hide_loader();
            });
        }
    }else{
        toastr.options = {
            closeButton: true,
            progressBar: true,
            showMethod: 'slideDown',
            timeOut: 5000
        };
        toastr.error('VERIFICA TU INFORMACIÓN, TODOS LOS PRODUCTOS DEBEN TENER UNA CANTIDAD', 'PREVENTA');
    }

});


$('#btnReemplazo').click(function(){
     if (confirm("¿ ESTAS SEGURO QUE DESEAS REEMPLAZAR EL PRODUCTO ?")) {

        if ($('#reemplazo-select_id').val()) {
            $.each(preventaDetail, function(key_producto, item_producto){
                if (item_producto.detail_id == VAR_REEMPLAZO_ID) {
                    preventaDetail[key_producto].producto_id    = $('#reemplazo-select_id').val();
                    preventaDetail[key_producto].producto       = $("#reemplazo-select_id option:selected").text();
                    preventaDetail[key_producto].cantidad       = 0;
                    $('#modal-reemplazo').modal('hide');
                }
            });
            render_producto();

        }else{
             toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.error('DEBES SELECCIONAR UN PRODUCTO', 'PREVENTA');
        }
    }
});

var show_loader = function(){
    $('body').append('<div  id="page_loader" style="opacity: .8;z-index: 2040 !important;    position: fixed;top: 0;left: 0;z-index: 1040;width: 100vw;height: 100vh;background-color: #000;"><div class="spiner-example" style="position: fixed;top: 50%;left: 0;z-index: 2050 !important; width: 100%;height: 100%;"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div>');
}

var hide_loader = function(){
    $('#page_loader').remove();
}

</script>