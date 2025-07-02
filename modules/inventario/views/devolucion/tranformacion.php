<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use app\models\sucursal\Sucursal;
use app\models\tranformacion\TranformacionDevolucion;

$this->title = 'TRANSFORMACIÓN';
$this->params['breadcrumbs'][] = ['label' => 'Devoluciones', 'url' => ['index']];
?>

<style>
.modal-backdrop{
    z-index: 1040 !important;
}

.modal {
    z-index: 1050 !important;;
}
</style>

<div class="inv-tranformacion-create">

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-content" >

                    <div class="row">
                        <div class="col-sm-6">
                            <?= Html::label('SUCURSAL / RUTA: ','lbl_sucursal') ?>
                            <?=  Html::dropDownList('lbl_sucursal', null, Sucursal::getItems(), ["class" => "form-control", "style" => "font-size:16px; height: 50%", "id" => "inputSucursalSelect"])  ?>

                        </div>
                        <div class="col-sm-3">
                            <?= Html::button('CARGAR INVENTARIO', ['class' => 'btn btn-primary btn-lg btn-block', "style" => "font-size:20px; margin-top: 30px","id" => "btnInventarioSeach"]) ?>
                        </div>

                        <div class="col-sm-3">
                            <?= Html::button('TRANSFORMAR', ['class' => 'btn btn-warning btn-lg btn-block ', "style" => "font-size:20px; margin-top: 30px","id" => "btnTranformacionSeach"]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row content-search" style="display:none">
        <div class="col-sm-6">
            <div class="ibox">
                <div class="ibox-content">
                    <strong>BUSCAR POR PRODUCTO</strong>
                    <?= Html::input("text",null,false,[ "class" => "form-control ", "style" => "font-size:24px", "id" => "inputProductoSearch", "autocomplete" => "off"]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-danger alert_danger_message" style="display: none"></div>

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="text-center">SUCURSAL</th>
                                    <th class="text-center">PRODUCTO</th>
                                    <th class="text-center">CANTIDAD</th>
                                    <th class="text-center">CANTIDAD A TRANSFORMAR</th>
                                    <!-- <th class="text-center">COSTO X KILO</th> -->
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
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_sucursal"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_producto"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_cantidad", "style" => "font-weight: bold;font-size: 16px;"]) ?></td>
                <td ><?= Html::input('number', null,false,["class" => "form-control text-center" ,  "step" => '0.001', "id"  => "table_cantidad_tranformar"]) ?></td>
                <!-- <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_precio_venta_unitario","style" => "font-weight: bold;font-size: 16px;"]) ?></td> -->
            </tr>
        </tbody>
    </table>
</div>

<div class="display-none">
    <table>
        <tbody class="template_tranformacion_tr">
            <tr id = "item_tr_id_{{tr_item_id}}" class="text-center">
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_producto"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_cantidad","style" => "font-weight: bold;font-size: 16px;"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="fade modal inmodal " id="modal-tranformacion"   role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">TRANSFORMACIÓN</h4>
            </div>
            <!--Modal body-->
             <?php $form = ActiveForm::begin([ "id" => "form-transformacion", "action" => "create-transformacion" ]) ?>
             <?= Html::hiddenInput('transformacion_array', null, [ "id" => "inputTransformacionArray" ]) ?>
             <?= Html::hiddenInput('transformacion_producto_array', null, [ "id" => "inputTransformacionProductoArray" ]) ?>
             <?= Html::hiddenInput('sucursal_invetario', null, [ "id" => "inputTransformacionSucursalId" ]) ?>
            <div class="modal-body">
                <div class="alert alert-danger alert-credito-error" style="display: none">
                </div>
                <div class="alert alert-success alert-credito-success" style="display: none">
                </div>
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="form-group">
                            <?= Html::label('TIPO DE TRANSFORMACIÓN','tipo_id') ?>
                            <?= Html::dropDownList('tipo_id', null, TranformacionDevolucion::$transList , ['class' => ' form-control', 'id' => 'tipo_id', 'prompt' => '--select--'  ])  ?>
                        </div>
                        <div class="form-group">
                            <?= Html::label('MOTIVO DE LA TRANFORMACION','nota') ?>
                            <?= Html::textArea('nota', null,['class' => ' form-control', 'id' => 'nota_id'])  ?>
                        </div>
                        <h2 class="text-center">TOTAL DE PRODUCTO <strong class="lbl_cantidad_producto"></strong></h2>
                        <div class="form-group" style="padding: 15px">
                            <div class="content_search_producto" style="display: none">
                                <?= Html::label('BUSCAR PRODUCTOS','tipo_id') ?>
                                <?= Select2::widget([
                                    'id' => 'producto-select_id',
                                    'name' => 'producto_select_id',
                                    'data' => [],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 3,
                                        'language'   => [
                                            'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                        ],
                                        'ajax' => [
                                            'url'      => Url::to(['/tpv/pre-captura/producto-ajax']),
                                            'dataType' => 'json',
                                            'cache'    => true,
                                            'processResults' => new JsExpression('function(data, params){  return {results: data} }'),
                                        ],

                                    ],
                                    'options' => [
                                        'placeholder' => 'Buscar productos',
                                        'multiple' => true,
                                    ],
                                ]) ?>
                                <?php /* ?>
                                <div class="form-group" style="padding-top: 15px">
                                    <?= Html::label('CANTIDAD','cantidad_transformacion') ?>
                                    <?= Html::input('number',"cantidad_transformacion",false, [ "class" => "form-control text-center", "id" => 'cantidad_transformacion', "step"=>"0.001" ]) ?>
                                </div>
                                */?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>PRODUCTO</th>
                                            <th>CANTIDAD</th>
                                        </tr>
                                    </thead>
                                    <tbody class="container_productos">

                                    </tbody>
                                </table>

                                <div class="row">
                                    <div class="col-sm-12 text-center">
                                        <h2 class="lbl_sucursal_inventario">[]</h2>
                                        <strong style="color: #000;">INVENTARIO</strong>
                                    </div>
                                </div>
                                <?php /* ?>
                                <div class="form-group" style="padding-top: 15px">
                                    <?= Html::label('SUCURSAL / RUTA  - PARA INVENTARIO: ','sucursal_invetario') ?>
                                    <?=  Html::dropDownList('sucursal_invetario', null, Sucursal::getItems(), ["class" => "form-control" ])  ?>
                                </div>
                                */?>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Productos a Transformar</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="table_credito">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <td class="text-center">PRODUCTO</td>
                                        <td class="text-center">CANTIDAD A TRANSFORMAR</td>
                                    </tr>
                                </thead>
                                <tbody class="container_resumen_table">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::Button('TRANSFORMAR', ['class' => 'btn btn-primary btn-lg col-sm-3', "id" => "btnTransformacionAdd"]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>


<script>
    /************************************************************/
    /*                          VARIABLES
    /***********************************************************/
    var $container_body     = $('.container_body'),
        $template_tr        = $('.template_tr'),
        $tipo_id            = $('#tipo_id'),
        $btnInventarioSeach = $('#btnInventarioSeach'),
        $btnTransformacionAdd               = $('#btnTransformacionAdd'),
        $inputTransformacionArray           = $('#inputTransformacionArray'),
        $inputTransformacionProductoArray   = $('#inputTransformacionProductoArray'),
        $inputSucursalSelect        = $('#inputSucursalSelect'),
        $btnTranformacionSeach      = $('#btnTranformacionSeach'),
        $container_resumen_table    = $('.container_resumen_table'),
        $template_tranformacion_tr  = $('.template_tranformacion_tr'),
        $content_search_producto    = $('.content_search_producto'),
        $content_search             = $('.content-search'),
        $inputProductoSearch        = $('#inputProductoSearch'),
        $containerProductos         = $('.container_productos'),
        $inputProductoSelect        = $('#producto-select_id'),
        $inputTransformacionSucursalId = $('#inputTransformacionSucursalId'),
        $lblSucursalInventario      = $('.lbl_sucursal_inventario'),
        containerArray              = [];
        tranformacionListArray      = [];
        tranformacionProductoListArray  = [];
        countProducto                   = 0;

    /*==========================================================
    *                       EVENTS
    *===========================================================*/
    $btnInventarioSeach.click(function(){
        $(".alert_danger_message").hide();
        $('.alert_danger_message').html(null);
        $btnInventarioSeach.attr('disabled',true);
        $content_search.hide();
        setInterval(function(){ $btnInventarioSeach.attr('disabled',false); }, 2000);
        containerArray = [];
        tranformacionListArray = [];
        if ($inputSucursalSelect.val()) {
            $inputProductoSearch.val(null);
            load_container($inputSucursalSelect.val());
        }else{
            $(".alert_danger_message").show();
            $(".alert_danger_message").html("Debes seleccionar una sucursal, Intenta nuevamente");
        }
    });

    $inputProductoSelect.change(function(){
        tranformacionProductoListArray = [];
        render_productos();
        if ($inputProductoSelect.val().length > 0) {
            tranformacionProductoListArray.push({
                "producto_id" : null,
                "producto" : "MERMA",
                "cantidad_inicial" : parseFloat(parseFloat(countProducto).toFixed(2)),
                "cantidad_real" : parseFloat(parseFloat(countProducto).toFixed(2)),
                "tipo" : 20,
            });
            $.each($inputProductoSelect.val(), function(key, item_producto){
                $.get("<?= Url::to(['get-producto']) ?>", { producto_id : item_producto } , function($response){
                    if ($response.code == 202) {
                        tranformacionProductoListArray.push({
                            "producto_id" : $response.producto.id,
                            "producto" : $response.producto.nombre,
                            "cantidad" : 0,
                            "tipo" : 10,
                        });
                    }
                    render_productos();
                },'json');
            })
        }
    });

    var render_productos = function(){
        $containerProductos.html(null);
        contentHtml = "";

        $.each(tranformacionProductoListArray, function(key, item_producto){
            if (item_producto.tipo == 20 ) {
                contentHtml += "<tr>"+
                    "<td><h3 class='text-danger'>"+ item_producto.producto +"<h3></td>"+
                    "<td><input type='number' class='form-control text-center' value='"+ item_producto.cantidad_real +"'  style='font-size:16px; font-weight: bold;' disabled></td>"+
                +"</tr>";
            }else{
                contentHtml += "<tr>"+
                    "<td><h3>"+ item_producto.producto +"<h3></td>"+
                    "<td><input type='number' class='form-control text-center' value='"+ item_producto.cantidad +"' onchange = 'function_change_cantidad(this,"+ item_producto.producto_id+")' style='font-size:16px; font-weight: bold;'></td>"+
                +"</tr>";
            }
        });

        $containerProductos.html(contentHtml);
    }

    var function_change_cantidad = function(elem, producto_id){
/*toastr.options = {
    closeButton: true,
    progressBar: true,
    showMethod: 'slideDown',
    timeOut: 5000
};
toastr.error('Verifica tu información, la merma es igual al total de producto a transformar.');*/
        cantidad_tem = 0;
        $.each(tranformacionProductoListArray, function(key, item_producto){
            if (item_producto.producto_id == producto_id) {
                tranformacionProductoListArray[key].cantidad =  getCalculaCantidad((parseFloat($(elem).val()) > 0 ? parseFloat($(elem).val()) : 0),item_producto.producto_id);
            }
            /*if (item_producto.tipo == 10)
                cantidad_tem = cantidad_tem + parseFloat(item_producto.cantidad);*/
        });


        $.each(tranformacionProductoListArray, function(key, item_producto){
            if (item_producto.tipo == 20 ){
                //tranformacionProductoListArray[key].cantidad_real = parseFloat(item_producto.cantidad_inicial) - cantidad_tem;
                tranformacionProductoListArray[key].cantidad_real = getCalculaMerma();
            }
        });

        render_productos();
        $inputTransformacionProductoArray.val(JSON.stringify(tranformacionProductoListArray));
    }


    var getCalculaMerma = function(){
        sumTotal = 0;
        $.each(tranformacionProductoListArray, function(key, item_producto){
            if (item_producto.tipo == 10)
                sumTotal = sumTotal + parseFloat(parseFloat(item_producto.cantidad).toFixed(2));
        });
        return countProducto > sumTotal   ? ( parseFloat(parseFloat(parseFloat(countProducto).toFixed(2)) - parseFloat(parseFloat(sumTotal).toFixed(2))).toFixed(2) ) : 0;
    }

    var getCalculaCantidad = function(cantidad, producto_id){
        sumTotal = 0;
        $.each(tranformacionProductoListArray, function(key, item_producto){
            if (item_producto.tipo == 10 && item_producto.producto_id == producto_id)
                sumTotal = parseFloat(sumTotal) + parseFloat(cantidad);
            else if(item_producto.tipo == 10)
                sumTotal = parseFloat(sumTotal) + parseFloat(item_producto.cantidad);
        });

        return sumTotal  > countProducto ? ( countProducto - sumTotal > 0 ?  countProducto - sumTotal : cantidad - (  sumTotal  - countProducto )) : cantidad;
    }

    var load_container = function(sucursal_id, producto_text = null){
        $.get("<?= Url::to(['get-inventario-sucursal']) ?>", { sucursal_id : sucursal_id, producto : producto_text }, function($response){
            if ($response.code == 202) {
                $.each($response.devoluciones,function(key, devolucionItem){
                    devolucionesObject = {
                        "item_id"            : devolucionItem.producto_id,
                        "inventario_id"      : devolucionItem.id,
                        "producto_id"        : devolucionItem.producto_id,
                        "producto_nombre"    : devolucionItem.producto_nombre,
                        "sucursal_id"        : devolucionItem.sucursal_id,
                        "sucursal"           : devolucionItem.sucursal,
                        "cantidad"           : parseFloat(devolucionItem.cantidad),
                        "cantidad_transformar"  : 0,
                        "costo"                 : parseFloat(devolucionItem.costo),
                    }
                    containerArray.push(devolucionesObject);
                });
                $content_search.show();
                render_template();
            }else{
                $(".alert_danger_message").show();
                $(".alert_danger_message").html("Ocurrio de error, Intenta nuevamente");
            }
        },'json');
    }


    $inputProductoSearch.change(function(e){
        $(".alert_danger_message").hide();
        $('.alert_danger_message').html(null);
        containerArray = [];
        //tranformacionListArray = [];
        //render_template();
        if ($(this).val())
            load_container($inputSucursalSelect.val(),$(this).val());
        else
            load_container($inputSucursalSelect.val());

    });


    $btnTranformacionSeach.click(function(){
        tranformacionProductoListArray = [];
        render_productos();
        $lblSucursalInventario.html(false);
        $inputProductoSelect.val(null).change();
        if ( tranformacionListArray.length > 0 ) {
            $('#modal-tranformacion').modal('show');
            $inputTransformacionSucursalId.val($inputSucursalSelect.val());
            $lblSucursalInventario.html($("#inputSucursalSelect option:selected" ).text());
            render_template_trans();
        }else{
            $(".alert_danger_message").show();
            $(".alert_danger_message").html("Debes seleccionar un producto, Intenta nuevamente");
        }
    });

    $tipo_id.change(function(){
        $content_search_producto.hide();
        if ( parseInt($(this).val()) == 20 ) {
            $content_search_producto.show();
            tranformacionProductoListArray = [];

        }
    });

    $btnTransformacionAdd.click(function(){
        show_loader();
        $btnTransformacionAdd.attr("disabled", true);
        //event.preventDefault();
        if ($tipo_id.val()) {
            if ($inputTransformacionSucursalId.val()) {
                if ($('#nota_id').val().trim()) {
                    if ( parseInt($tipo_id.val()) == 20 ) {
                        if ($("#producto-select_id").val().length > 0) {
                            total_merma = 0;
                            total_producto = 0;
                            $.each(tranformacionProductoListArray, function(key, item_producto){
                                if (item_producto.tipo == 20 ){
                                    total_producto = item_producto.cantidad_inicial;
                                    total_merma = item_producto.cantidad_real;
                                }
                            });

                            if (total_merma != total_producto ) {
                                hide_loader();
                                $("#form-transformacion").submit();
                                setInterval(function(){ $btnTransformacionAdd.attr('disabled',false); }, 2000);
                            }else{
                                toastr.options = {
                                    closeButton: true,
                                    progressBar: true,
                                    showMethod: 'slideDown',
                                    timeOut: 5000
                                };
                                toastr.error('Verifica tu información, la merma es igual al total de producto a transformar.');
                                hide_loader();
                                setInterval(function(){ $btnTransformacionAdd.attr('disabled',false); }, 2000);
                                return false;
                            }
                        }else{
                            alert("VERIFICA TU INFORMACIÓN, DEBES SELECCIONAR UN PRODUCTO - CANTIDAD");
                            hide_loader();
                            setInterval(function(){ $btnTransformacionAdd.attr('disabled',false); }, 2000);
                            return false;
                        }
                    }else{
                        hide_loader();
                        $("#form-transformacion").submit();
                        setInterval(function(){ $btnTransformacionAdd.attr('disabled',false); }, 2000);
                    }
                }else{
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        showMethod: 'slideDown',
                        timeOut: 5000
                    };
                    toastr.error('DEBES INGRESAR UNA NOTA O MOTIVO DE LA TRANFORMACIÓN.');
                    hide_loader();
                    setInterval(function(){ $btnTransformacionAdd.attr('disabled',false); }, 2000);

                }
            }else{

                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                toastr.error('OCURRIO UN ERROR, INTENTA NUEVAMENTE');
                hide_loader();
                setInterval(function(){ $btnTransformacionAdd.attr('disabled',false); }, 2000);

            }

        }else{
            setInterval(function(){ $btnTransformacionAdd.attr('disabled',false); }, 2000);
            hide_loader();

            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.error('DEBES SELECCIONAR EL TIPO DE TRANFORMACIÓN');
        }
    });

    /*==========================================================
    *                       FUNCTIONS
    *===========================================================*/
    var render_template = function()
    {
        $container_body.html("");
        total_venta = 0;

        $.each(containerArray, function(key, devolucionItem){
            if (devolucionItem.item_id) {
                template_tr = $template_tr.html();
                template_tr = template_tr.replace("{{tr_item_id}}",devolucionItem.item_id);

                $container_body.append(template_tr);


                $tr        =  $("#item_tr_id_" + devolucionItem.item_id, $container_body);
                $tr.attr("data-item_id",devolucionItem.item_id);



                $("#table_devolucion_id",$tr).html(devolucionItem.inventario_id);
                $("#table_sucursal",$tr).html(devolucionItem.sucursal);


                $("#table_producto",$tr).html("[" + devolucionItem.producto_id +"] "+ devolucionItem.producto_nombre  );
                $("#table_cantidad",$tr).html(devolucionItem.cantidad);

                if (search_check_item(devolucionItem.producto_id))
                    $("#table_cantidad_tranformar",$tr).val(search_check_cantidad(devolucionItem.producto_id));
                else
                    $("#table_cantidad_tranformar",$tr).val(devolucionItem.cantidad_transformar);

                $("#table_cantidad_tranformar",$tr).attr("onchange","refresh_cantidad_tranformar(this)");

                // $("#table_precio_venta_unitario",$tr).html(btf.conta.money(devolucionItem.costo));
                // $("#table_precio_venta_total",$tr).html( btf.conta.money(devolucionItem.cantidad * devolucionItem.costo ));


                //$("#table_precio",$tr).html( btf.conta.money(devolucionItem.precio_venta * devolucionItem.cantidad ) );


                //total_venta = total_venta + (producto.precio_venta * producto.cantidad);
                if (search_check_item(devolucionItem.producto_id)) {
                    $tr.append("<td><input type='checkbox' style='transform: scale(2);' onclick='refresh_list(this)' checked /></td>");
                }else{
                    $tr.append("<td><input type='checkbox' style='transform: scale(2);' onclick='refresh_list(this)' /></td>");
                }


            }
        });

        //$('.lbl_total_venta').html(btf.conta.money(total_venta));
        //$inputventaTotal.val(total_venta);
        //$('#total_metodo').html( btf.conta.money(total_venta) );

        //$inputventaDetalle.val(JSON.stringify(containerArray));
    };


    var search_check_item = function(producto_id){
        $is_search = false;
        $.each(tranformacionListArray, function(key, item_producto ){
            if (item_producto.producto_id == producto_id) {
                $is_search = true;
            }
        });

        return $is_search
    }

    var search_check_cantidad = function(producto_id){
        $is_cantidad = false;
        $.each(tranformacionListArray, function(key, item_producto ){
            if (item_producto.producto_id == producto_id) {
                $is_cantidad = parseFloat(item_producto.cantidad_transformar);
            }
        });

        return $is_cantidad
    }

    var refresh_cantidad_tranformar = function(elem)
    {
        $element    = $(elem);
        $ele_tr        = $(elem).closest('tr');
        $ele_tr_id  = $ele_tr.attr("data-item_id");
        $.each(containerArray, function(key, devolucionItem){
            if (devolucionItem.item_id == $ele_tr_id ) {
                containerArray[key].cantidad_transformar = devolucionItem.cantidad < $element.val() ? devolucionItem.cantidad : ( parseFloat($element.val()) > 0 ? parseFloat($element.val()) : 0);

                if (search_check_item(devolucionItem.producto_id)) {
                     $.each(tranformacionListArray, function(key_trans, item_producto ){
                        if (item_producto.producto_id == devolucionItem.producto_id) {
                            tranformacionListArray[key_trans].cantidad_transformar = parseFloat(containerArray[key].cantidad_transformar);
                        }
                    });
                }
            }
        });

        render_template();

        $inputTransformacionArray.val(JSON.stringify(tranformacionListArray));

    }

    var render_template_trans = function()
    {
        $container_resumen_table.html("");
        countProducto = 0;
        $.each(tranformacionListArray, function(key, devolucionItem){
            if (devolucionItem.item_id) {
                template_tranformacion_tr = $template_tranformacion_tr.html();
                template_tranformacion_tr = template_tranformacion_tr.replace("{{tr_item_id}}",devolucionItem.item_id);

                $container_resumen_table.append(template_tranformacion_tr);

                $tr        =  $("#item_tr_id_" + devolucionItem.item_id, $container_resumen_table);
                $tr.attr("data-item_id",devolucionItem.item_id);
                $("#table_producto",$tr).html("[" + devolucionItem.producto_id +"] "+ devolucionItem.producto_nombre  );
                $("#table_cantidad",$tr).html(devolucionItem.cantidad_transformar);
                countProducto = countProducto + parseFloat(devolucionItem.cantidad_transformar);
            }
        });

        $('.lbl_cantidad_producto').html(parseFloat(countProducto).toFixed(2));
    };

    var refresh_list = function(elem)
    {
        $element    = $(elem);
        $ele_tr     = $(elem).closest('tr');
        $ele_tr_id  = $ele_tr.attr("data-item_id");


        if ( $element.is(":checked") ) {

            is_add = true;
            $.each(tranformacionListArray, function(key, tranformacionItem){
                if (tranformacionItem.item_id == $ele_tr_id ) {
                    is_add = false;
                }
            });

            if (is_add){
                $elementItem = getElementItem($ele_tr_id);
                if ($elementItem.cantidad_transformar > 0 ){
                    tranformacionListArray.push($elementItem);
                }
                else{
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        showMethod: 'slideDown',
                        timeOut: 5000
                    };
                    toastr.error('La cantidad a transformar debe ser MAYOR A [0.00], ingresa un valor para poder continuar.');
                    $element.prop("checked",false);
                }
            }

        }else{
            $.each(tranformacionListArray, function(key, tranformacionItem){
                if (tranformacionItem) {
                    if (tranformacionItem.item_id == $ele_tr_id ) {
                        tranformacionListArray.splice(key, 1 );
                    }

                     $.each(containerArray, function(key, devolucionItem){
                        if (devolucionItem.item_id == $ele_tr_id ) {
                            containerArray[key].cantidad_transformar = 0;
                        }
                    });
                }
            });
        }
        render_template();

        $inputTransformacionArray.val(JSON.stringify(tranformacionListArray));
    }

    var getElementItem = function(element_id)
    {
        elementItem = [];

        $.each(containerArray, function(key, devolucionItem){
            if (devolucionItem.item_id == element_id) {
                elementItem = devolucionItem;
            }
        });

        return  elementItem;
    }

    var show_loader = function(){
        $('body').append('<div  id="page_loader" style="opacity: .8;z-index: 2040 !important;    position: fixed;top: 0;left: 0;z-index: 1040;width: 100vw;height: 100vh;background-color: #000;"><div class="spiner-example" style="position: fixed;top: 50%;left: 0;z-index: 2050 !important; width: 100%;height: 100%;"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div>');
    }

    var hide_loader = function(){
        $('#page_loader').remove();
    }

</script>