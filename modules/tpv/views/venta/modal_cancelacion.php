<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use app\models\cobro\CobroVenta;
use app\models\venta\Venta;
use app\models\sucursal\Sucursal;

$cobroTotal = 0;
?>

<style>
.modal-backdrop{
    z-index: 1040 !important;
}

.modal {
    z-index: 1050 !important;;
}
</style>

<div class="fade modal inmodal " id="modal-cancelacion"  role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">CANCELACION DE VENTA</h4>
            </div>
            <?php $form = ActiveForm::begin([ "id" => "formCancelVenta", "action" => "cancel-venta"]) ?>
            <?= Html::hiddenInput('Venta[id]', $model->id ) ?>
            <!--Modal body-->
            <div class="modal-body">
                <div class="panel">
                    <div class="panel-body text-center">
                        <h2>VENTA : <strong style="font-weight: bold;">#<?= str_pad($model->id,6,"0",STR_PAD_LEFT) ?></strong> - TOTAL PAGADO : <strong style="font-weight: bold;">$ <?= number_format($cobroTotal,2)  ?></strong> </h2>
                    </div>
                </div>

                <?php if ($model->ruta_sucursal_id ||  $model->reparto_id): ?>
                    <?php if ($model->ruta_sucursal_id ): ?>
                        <div class="alert alert-warning">
                            <h2>El producto regresara a <strong><?= $model->sucursal->nombre ?></strong> </h2>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <h2>El producto regresara a <strong><?= $model->sucursalVende->nombre ?></strong> </h2>
                        </div>
                    <?php endif ?>
                <?php endif ?>


                <?php if ($model->ruta_sucursal_id): ?>

                    <?php foreach ($model->transaccion as $key => $item_tra): ?>
                        <div class="ibox">
                            <div class="ibox-title">
                                <h4> OPERACION - <?= $item_tra->token_pay ?></h4>
                            </div>
                            <div class="ibox-content">
                                <?php foreach (CobroVenta::getVentaRutaOperacion($item_tra->token_pay) as $key => $item_token_pay): ?>
                                    <div class="row">
                                        <div class="col-sm-4 text-center">
                                            <h4><?= CobroVenta::$servicioList[$item_token_pay->metodo_pago] ?></h4>
                                            <p>COBRADO [METODO DE PAGO]</p>
                                        </div>
                                        <div class="col-sm-4 text-center">
                                            <h4><?= number_format($item_token_pay->cantidad,2) ?></h4>
                                            <p>COBRADO</p>
                                        </div>
                                        <div class="col-sm-4 text-center">
                                            <h4>-<?=number_format( $model->total,2) ?></h4>
                                            <p>DEVOLUCIÓN [NOTA DE VENTA #<?= $model->id ?>]</p>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    <?php endforeach ?>
                    <div class="panel">
                        <div class="panel-body">
                            <h2>TOTAL[DEVOLUCIÓN]: <?= number_format($model->total,2)?></h2>
                        </div>
                    </div>
                <?php endif ?>

                <div class="panel">
                    <div class="panel-body">
                        <?= Html::textArea('Venta[nota]',null,["class" => "form-control", "rows" => "6", "style" => "border-color:#ff2f2f", "id"=> "inputNotaCancel" ]) ?>
                    </div>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('CANCELAR VENTA', ['class' => 'btn btn-danger btn-lg', "style" => "font-size:20px", 'id'=> 'btnCancelVenta' ]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<div class="fade modal inmodal " id="modal-cancelacion-multiple" role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg"  style="max-width:75%">
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">CANCELACION DE VENTA - RUTA</h4>
            </div>
            <?= Html::hiddenInput('VentaRutaArray[id]', null,[ "id" => "VentaRutaArrayId"] ) ?>
            <!--Modal body-->
            <div class="modal-body">
                <div class="panel">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6 offset-sm-6">
                                <?= Html::button("<i class='fa fa-edit'></i> MODIFICAR VENTA", ["class" => "btn btn-warning btn-block", "id" => "btnUpdateVenta"]) ?>
                            </div>
                        </div>
                        <h2>NOTAS RELACCIONADAS : <strong style="font-weight: bold;" class="lbl_nota_venta"># </strong>  </h2>
                    </div>
                </div>


                <div class="ibox">
                    <div class="ibox-content">

                        <div class="container-cancelacion-inventario">
                            <h2>TOTAL A CANCELAR : <strong style="font-weight: bold;" class="lbl_total_cancelar text-danger">$ 00.00</strong></h2>
                            <div class="form-group" style="padding-top: 15px">
                                <?= Html::label('INVENTARIO: ','sucursal_invetario') ?>
                                <p class="text-danger">** MOSTRARA LA RUTA SOLO SI SE ENCUENTRA  ABIERTA **</p>
                                <?=  Html::dropDownList('sucursal_invetario', null, Sucursal::getInventarioDisponible($model->id), ["class" => "form-control", "style" => "font-size:24px; font-weight:700;  height: 100%", "id" => "inputSucursalCancelacion"])  ?>
                            </div>
                            <h5>PRODUCTOS A CANCELAR</h5>
                            <div class="table_credito">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <td class="text-center">PRODUCTO</td>
                                            <td class="text-center">CANTIDAD</td>
                                        </tr>
                                    </thead>
                                    <tbody class="container_producto_cancelacion">

                                    </tbody>
                                </table>
                            </div>

                            <h5>COBROS A CANCELAR</h5>
                            <div class="table_credito">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <td class="text-center">METODO DE PAGO</td>
                                            <td class="text-center">CANTIDAD</td>
                                        </tr>
                                    </thead>
                                    <tbody class="container_cobros_cancelacion">

                                    </tbody>
                                </table>
                            </div>

                        </div>

                        <div class="container-edit-preventa" style="display:none">

                            <pre id="print_code" style="display:none">
                            </pre>

                            <p style="color: #000;font-weight: 700;font-size: 19px;">CLIENTE</p>
                            <?= Select2::widget([
                                'id' => 'venta-cliente_id',
                                'name' => 'Venta[cliente_id]',
                                'pluginOptions' => [
                                    'minimumInputLength' => 3,
                                    'language'   => [
                                        'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                    ],
                                    'ajax' => [
                                        'url'      => Url::to(['cliente-ajax']),
                                        'dataType' => 'json',
                                        'cache'    => true,
                                        'processResults' => new JsExpression('function(data, params){ return {results: data} }'),
                                    ],

                                ],
                                'options' => [
                                    'placeholder' => '--- SELECT ---',
                                ],
                            ]) ?>

                            <?php if (Venta::isEditVentaRuta($model->id)): ?>
                                <p style="color: #000;font-weight: 700;font-size: 19px;">PRODUCTOS RELACIONADOS</p>

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <td class="text-center">VENTA</td>
                                            <td class="text-center">PRODUCTO</td>
                                            <td class="text-center">CANTIDAD</td>
                                            <td class="text-center">PRECIO VENTA X U.</td>
                                        </tr>
                                    </thead>
                                    <tbody class="container_producto_venta">

                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="panel">
                                    <div class="panel-panel-body">
                                        <div class="alert alert-danger">
                                            <strong>LA RUTA SE ENCUENTRA CERRADA NO PUEDES EDITAR EL CONTENIDO DE LA VENTA</strong>
                                        </div>
                                    </div>
                                </div>
                            <?php endif ?>


                            <p style="color: #000;font-weight: 700;font-size: 19px;">COBROS RELACIONADOS</p>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <td class="text-center">METODO DE PAGO</td>
                                        <td class="text-center">CANTIDAD</td>
                                        <td class="text-center">ACCION</td>
                                    </tr>
                                </thead>
                                <tbody class="container_cobros_venta">

                                </tbody>
                            </table>

                            <div class="row">
                                <div class="col-sm-6 text-center">
                                    <h2 style="font-weight: 700;" class="lbl_total_nota_venta">$ 00.00</h2>
                                    <p>TOTAL DE VENTA</p>
                                </div>

                                <div class="col-sm-6 text-center">
                                    <h2 style="font-weight: 700;" class="lbl_total_cobro_venta">$ 00.00</h2>
                                    <p>TOTAL DE COBRO</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="panel">
                    <div class="panel-body">
                        <?= Html::textArea('VentaRuta[nota]',null,["class" => "form-control", "rows" => "6", "style" => "border-color:#ff2f2f", "id"=> "inputNotaCancelacion" ]) ?>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <div class="div_btn_edita_venta" style="display:none">
                    <?= Html::Button('EDITAR VENTA - RUTA', ['class' => 'btn btn-warning btn-lg', "style" => "font-size:20px", 'id'=> 'btnEditaVentaRuta' ]) ?>
                </div>
                <div class="div_btn_cancela_venta">
                    <?= Html::Button('CANCELAR VENTA - RUTA', ['class' => 'btn btn-danger btn-lg', "style" => "font-size:20px", 'id'=> 'btnCancelVentaRuta' ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="display-none">
    <table>
        <tbody class="template_cobro">
            <tr id = "item_id_{{cobro_id}}">
                <td ><?=  Html::dropDownList('inputCobro', null, CobroVenta::$servicioList, ['class' => 'form-control', "id"  => "inputSelectMetodoPago","onchange" => 'refresh_metodo_pago(this)'])  ?></td>
                <td ><?= Html::input('number', null,false,["class" => "form-control text-right" , "id"  => "inputMetodoPagoCantidad", "style" => "font-size:14px; font-weight:700;", "onchange" => 'refresh_pago(this)']) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="div_select_hide" style="display: none;">
    <select name="inputSelectProducto" id="inputSelectProducto" onchange="refresh_select_producto(this)" class="form-control"></select>
</div>
<script>
var $btnCancelVenta                 = $('#btnCancelVenta'),
    $contentVenta                   = $('.content_venta'),
    $btnShowCancelVentaMultiple     = $('#btnShowCancelVentaMultiple'),
    $btnEditaVentaRuta              = $('#btnEditaVentaRuta'),
    $containerProductoCancelacion   = $('.container_producto_cancelacion'),
    $containerCobrosCancelacion     = $('.container_cobros_cancelacion'),
    $containerCancelacionInventario = $('.container-cancelacion-inventario'),
    $containerEditVenta             = $('.container-edit-preventa'),
    $btnCancelVentaRuta             = $('#btnCancelVentaRuta'),
    $btnUpdateVenta                 = $('#btnUpdateVenta'),
    $inputSucursalCancelacion       = $('#inputSucursalCancelacion'),
    $containerProductoVenta         = $('.container_producto_venta'),
    $containerCobroVenta            = $('.container_cobros_venta'),
    $inputVentaCliente              = $('#venta-cliente_id'),
    $divSelectProducto              = $('.div_select_hide'),
    $divBtnEditarVenta              = $('.div_btn_edita_venta'),
    $divBtnCancelaVenta             = $('.div_btn_cancela_venta'),
    VAR_IS_EDIT_PREVENTA            = false,
    $VAR_VENTA_DETAIL               = [];
    $VAR_COBRO_DETAIL               = [];
    VAR_INVENTARIO_PRODUCTO         = [];
    $VAR_VENTA_OBJECT               = {
        "venta" : [],
        "cobro" : [],
    };
    VAR_URL_PATH                    = "<?= Url::to(['/']) ?>",
    $inputNotaCancelacion           = $('#inputNotaCancelacion');

$btnCancelVenta.click(function(){
    if (!$('#inputNotaCancel').val()) {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            showMethod: 'slideDown',
            timeOut: 5000
        };
        toastr.error('DEBES INGRESAR UN NOTA DE CANCELACION ANTES DE CONTINUAR', 'CANCELACION DE VENTA');
        return false;
    }
    $btnCancelVenta.attr("disabled",true);
    $('#formCancelVenta').submit();
});


$btnShowCancelVentaMultiple.click(function(){
    $('.lbl_nota_venta').html(false);
    $('.lbl_total_cancelar').html(false);
    $containerCobrosCancelacion.html(false);
    $containerProductoCancelacion.html(false);
    containerProductoHtml = '';
    containerNotaHtml   = '';
    containerCobroHtml  = '';
    totalCancelacion    = 0;
    $.get("<?= Url::tO(['get-notas-multiple']) ?>",{ venta_id : venta_id },function($response){
        if ($response.code == 202) {
            notasArray = [];
            $.each($response.ventas.venta, function(key_venta, item_venta){
                notasArray.push(item_venta.id);
                $.each(item_venta.detail, function(key_detail, item_detail){
                    containerProductoHtml += "<tr>"+
                        "<td><p style='font-size:14px; font-weight:700;'>"+ item_detail.producto +"</p></td>"+
                        "<td><p class='text-right' style='font-size:14px; font-weight:700;'>"+ item_detail.cantidad +"</p></td>"+
                    "</tr>";
                });
            });


            $.each($response.ventas.cobro, function(key_venta, item_cobro){
                containerCobroHtml += "<tr>"+
                    "<td><p style='font-size:14px; font-weight:700;'>"+ item_cobro.metodo_pago_text +"</p></td>"+
                    "<td><p class='text-right' style='font-size:14px; font-weight:700;'>"+ btf.conta.money(item_cobro.cantidad) +"</p></td>"+
                "</tr>";

                totalCancelacion = totalCancelacion + parseFloat(item_cobro.cantidad);
            });

            $.each(notasArray, function(key_nota, item_nota){
                containerNotaHtml += item_nota +"-";
            });


            $containerProductoCancelacion.html(containerProductoHtml);
            $containerCobrosCancelacion.html(containerCobroHtml);
            $('.lbl_nota_venta').html("[" + containerNotaHtml + "]");
            $('.lbl_total_cancelar').html(btf.conta.money(totalCancelacion));
        }

    },'json');
});

var show_loader = function(){
    $('body').append('<div  id="page_loader" style="opacity: .8;z-index: 2040 !important;    position: fixed;top: 0;left: 0;z-index: 1040;width: 100vw;height: 100vh;background-color: #000;"><div class="spiner-example" style="position: fixed;top: 50%;left: 0;z-index: 2050 !important; width: 100%;height: 100%;"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div>');
}

var hide_loader = function(){
    $('#page_loader').remove();
}

$btnCancelVentaRuta.click(function(){
    $('#modal-cancelacion-multiple').modal('hide');
    show_loader();
    $.post("<?= Url::to(['post-cancelacion-venta']) ?>", { venta_id : venta_id, sucursal_id : $inputSucursalCancelacion.val(), nota_cancelacion: $inputNotaCancelacion.val() }, function($response){
        hide_loader();
        if ($response.code == 202 ) {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.success('SE REALIZO CORRECTAMENTE LA CANCELACION', 'CANCELACION DE VENTA');
            hide_loader();
            location.reload();
        }else{

            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.error('OCURRIO UN ERROR, INETNTA NUEVAMENTE', 'CANCELACION DE VENTA');
        }
    });
});


$btnUpdateVenta.click(function(){
    $containerCancelacionInventario.hide();
    $containerEditVenta.show();
    $divBtnEditarVenta.show();
    $divBtnCancelaVenta.hide();
    VAR_IS_EDIT_PREVENTA = true;

    get_venta_detail();

});

var get_venta_detail = function()
{
    $.get(VAR_URL_PATH+"tpv/venta/venta-info", { venta_id : venta_id }, function(responseVenta){
        if (responseVenta.code == 202 ) {
            var newOption       = new Option(responseVenta.venta.info.cliente, responseVenta.venta.info.cliente_id, false, true);
            $inputVentaCliente.append(newOption).trigger('change');
            render_productos();

            productoListFormat      = [];
            $.each(responseVenta.inventario, function(key, item_producto){
                productoListFormat.push({
                    "producto_id"   : item_producto.producto_id,
                    "producto"      : item_producto.producto,
                    "cantidad"      : item_producto.cantidad,
                    "unidad_medida" : item_producto.unidad_medida,
                    "selected"      : 1,
                });
            });

            $.each(responseVenta.venta.info.detail, function(key, detail_producto){
                is_add = true;
                $.each(productoListFormat, function(key_search, item_producto_search){
                    if (item_producto_search.producto_id == detail_producto.producto_id){
                        is_add = false;
                        productoListFormat[key_search].selected = 10;
                    }
                });

                if (is_add) {
                    productoListFormat.push({
                        "producto_id"   : detail_producto.producto_id,
                        "producto"      : detail_producto.producto,
                        "cantidad"      : detail_producto.cantidad,
                        "unidad_medida" : detail_producto.unidad_medida,
                        "selected"      : 10,
                    });
                }
            });


            $VAR_VENTA_DETAIL           = responseVenta.venta.info;
            $VAR_COBRO_DETAIL           = responseVenta.venta.cobro;

            $VAR_VENTA_OBJECT.venta = $VAR_VENTA_DETAIL;


            VAR_INVENTARIO_PRODUCTO     = productoListFormat;
            render_productos();
            render_cobro();
        }
    },'json');
}

var render_productos = function()
{
    $containerProductoVenta.html(false);
    containerProductoHtml   = "";
    totalVenta = 0;
    $.each($VAR_VENTA_DETAIL.detail,function(key, item_detail){
        if (item_detail.venta_id) {
            divSelectProducto   = $divSelectProducto.html();
            $inputSelect        = $("#inputSelectProducto");

            $.each(VAR_INVENTARIO_PRODUCTO,function(key, item_producto){
                $inputSelect.append(new Option(item_producto.producto +" existencia ["+ item_producto.cantidad +" "+ item_producto.unidad_medida +"]", item_producto.producto_id));
            });

            containerProductoHtml += "<tr id='tr_item_"+ item_detail.venta_detail_id +"' data-item_id = ' "+ item_detail.venta_detail_id +"'>"+
                "<td class='text-center'>   <a href='"+ VAR_URL_PATH +"tpv/venta/view?id="+ item_detail.venta_id +"' target='_blank' style='font-size: 14px;font-weight: 700;'>#"+ item_detail.folio +"</a></td>"+
                "<td>"+ $divSelectProducto.html() +"</td>"+
                "<td><input type='number' onchange='refresh_cantidad(this)' class='form-control text-center' style='font-weight:700; font-size:22px; height:100%' value="+ item_detail.cantidad + " /></td>"+
                "<td><input type='number' onchange='refresh_precio(this)' class='form-control text-center' style='font-weight:700; font-size:22px; height:100%' value="+ item_detail.precio_venta + " /></td>"+
            "</tr>";


        }
    });
    $containerProductoVenta.html(containerProductoHtml);
    $('.lbl_total_nota_venta').html(btf.conta.money(funct_total_venta()));
    refresh_producto();
    $VAR_VENTA_OBJECT.venta = $VAR_VENTA_DETAIL;
    $('#print_code').html(JSON.stringify($VAR_VENTA_OBJECT, null, '\t'));
}

var render_cobro = function(){
    $containerCobroVenta.html(false);

    $.each($VAR_COBRO_DETAIL,function(key, item_cobro){
        if (!item_cobro.status) {
            template_cobro = $('.template_cobro').html();
            template_cobro = template_cobro.replace("{{cobro_id}}",item_cobro.id);

            $containerCobroVenta.append(template_cobro);
            $tr        =  $("#item_id_" + item_cobro.id, $containerCobroVenta);
            $tr.attr("data-item_id",item_cobro.id);
            $("#inputSelectMetodoPago",$tr).val(item_cobro.metodo_pago);
            $("#inputMetodoPagoCantidad",$tr).val(item_cobro.cantidad);
            $tr.append("<td class='text-center'><button type='button' class='btn btn-warning btn-circle' onclick='refresh_cobro(this)'><i class='fa fa-trash'></i></button></td>");
        }
    });

    $('.lbl_total_cobro_venta').html(btf.conta.money(funct_total_cobrado()));

    $VAR_VENTA_OBJECT.cobro = $VAR_COBRO_DETAIL;

    $('#print_code').html(JSON.stringify($VAR_VENTA_OBJECT, null, '\t'));
}

var funct_total_cobrado = function()
{
    totalCobro = 0;
    $.each($VAR_COBRO_DETAIL,function(key, item_cobro){
        if (!item_cobro.status)
            totalCobro = totalCobro + parseFloat(item_cobro.cantidad);
    });

    return totalCobro;
}

var funct_total_venta = function()
{
    totalVenta = 0;
    $.each($VAR_VENTA_DETAIL.detail,function(key, item_detail){
        if (item_detail.status == 10 )
            totalVenta = totalVenta + (parseFloat(item_detail.cantidad) * parseFloat(item_detail.precio_venta));
    });

    return totalVenta;
}

var refresh_producto = function()
{
    $.each($VAR_VENTA_DETAIL.detail,function(key, item_detail){
        $inputSelectProducto = $('#inputSelectProducto', '#tr_item_' + item_detail.venta_detail_id);
        $inputSelectProducto.val(item_detail.producto_id);
    });
}

var refresh_cantidad = function(ele)
{
    $ele_tr_val = $(ele).closest('tr');

    $ele_tr_id  = $ele_tr_val.attr("data-item_id");

    $.each($VAR_VENTA_DETAIL.detail, function(key_cantidad, item_registro){
        if (item_registro.venta_detail_id == $ele_tr_id){
            $VAR_VENTA_DETAIL.detail[key_cantidad].cantidad         = $(ele).val();
            $VAR_VENTA_DETAIL.detail[key_cantidad].edit_cantidad    = 10;
        }
    });

    render_productos();
}

var refresh_precio = function(ele)
{
    $ele_tr_val = $(ele).closest('tr');

    $ele_tr_id  = $ele_tr_val.attr("data-item_id");

    $.each($VAR_VENTA_DETAIL.detail, function(key_precio, item_registro){
        if (item_registro.venta_detail_id == $ele_tr_id){
            $VAR_VENTA_DETAIL.detail[key_precio].precio_venta = $(ele).val();
            $VAR_VENTA_DETAIL.detail[key_precio].edit_precio  = 10;
        }
    });

    render_productos();
}


var refresh_cobro = function(ele){
    $ele_tr_val = $(ele).closest('tr');

    $ele_tr_id  = $ele_tr_val.attr("data-item_id");

    $.each($VAR_COBRO_DETAIL, function(key, item_cobro){
        if (item_cobro.id == $ele_tr_id) {
            $VAR_COBRO_DETAIL[key].status = 1;
            $VAR_COBRO_DETAIL[key].edit_cobro  = 10;
        }
    });


    $(ele).closest('tr').remove();
    render_cobro();
}

var refresh_pago = function(ele){
    $ele_tr_val = $(ele).closest('tr');

    $ele_tr_id  = $ele_tr_val.attr("data-item_id");

    $.each($VAR_COBRO_DETAIL, function(key, item_cobro){
        if (item_cobro.id == $ele_tr_id) {
            $VAR_COBRO_DETAIL[key].cantidad     = $(ele).val();
            $VAR_COBRO_DETAIL[key].edit_cobro   = 10;
        }
    });

    $(ele).closest('tr').remove();
    render_cobro();
}

var refresh_metodo_pago = function(ele){
    $ele_tr_val = $(ele).closest('tr');

    $ele_tr_id  = $ele_tr_val.attr("data-item_id");

    $.each($VAR_COBRO_DETAIL, function(key, item_cobro){
        if (item_cobro.id == $ele_tr_id) {
            $VAR_COBRO_DETAIL[key].metodo_pago  = $(ele).val();
            $VAR_COBRO_DETAIL[key].edit_cobro   = 10;
        }
    });

    $(ele).closest('tr').remove();
    render_cobro();
}


var refresh_select_producto = function(ele)
{
    $ele_tr_val = $(ele).closest('tr');

    $ele_tr_id  = $ele_tr_val.attr("data-item_id");

    $.each($VAR_VENTA_DETAIL.detail, function(key_producto, item_registro){
        if (item_registro.venta_detail_id == $ele_tr_id){
            $VAR_VENTA_DETAIL.detail[key_producto].producto_id      = $(ele).val();
            $VAR_VENTA_DETAIL.detail[key_producto].edit_producto    = 10;
        }
    });

    render_productos();
}

$inputVentaCliente.change(function(){
    if ($inputVentaCliente.val()) {
        $VAR_VENTA_DETAIL.cliente_id    = $inputVentaCliente.val();
        $VAR_VENTA_DETAIL.edit_cliente  = 10;
    }else{
        $VAR_VENTA_DETAIL.cliente_id    = null;
        $VAR_VENTA_DETAIL.edit_cliente  = 10;
    }
});


$btnEditaVentaRuta.click(function(){
    show_loader();
    if ( parseFloat(funct_total_cobrado()).toFixed(2) == parseFloat(funct_total_venta()).toFixed(2)) {

        $.post( VAR_URL_PATH + "tpv/venta/update-venta-ruta",{ ventaObject : $VAR_VENTA_OBJECT, nota_cancelacion: $inputNotaCancelacion.val() },function($response){
            $('#modal-cancelacion-multiple').modal('hide');
            hide_loader();
            if ($response.code == 202 ) {

                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                location.reload();
                toastr.success('SE REALIZO CORRECTAMENTE LA OPERACION', 'CANCELACION DE VENTA');
            }
        })
    }else{
        toastr.options = {
            closeButton: true,
            progressBar: true,
            showMethod: 'slideDown',
            timeOut: 5000
        };
        toastr.warning('LA CANTIDAD DE COBRO DEBE SER IGUAL AL TOTAL DE VENTA', 'CANCELACION DE VENTA');
        hide_loader();
    }
});

</script>