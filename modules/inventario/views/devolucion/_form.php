<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
//use kartik\date\DatePicker;
use kartik\select2\Select2;
use app\assets\BootboxAsset;
use app\models\inv\Operacion;
use app\models\sucursal\Sucursal;
use app\models\compra\Compra;
use app\models\esys\EsysListaDesplegable;


BootboxAsset::register($this);
/* @var $this yii\web\View */
/* @var $model app\models\sucursales\Sucursal */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="inv-devolucion-form">

    <?php $form = ActiveForm::begin([ "id" => "form-devolucion" ]) ?>
    <?= $form->field($model->operacion_detalle, 'operacion_detalle_array')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'venta_id')->hiddenInput()->label(false) ?>
    <?= Html::hiddenInput('metodo_pago_array', null, [ "id" => "inputMetodoPagoArray" ]) ?>
    <?= Html::hiddenInput('total_pago_reembolso', null, [ "id" => "inputTotalPago" ]) ?>

    <div class="alert alert-danger alert_danger_message" style="display: none"></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-content" >

                    <div class="row">
                        <div class="col-sm-4">
                            <?= $form->field($model, 'almacen_sucursal_id')->dropDownList(Sucursal::getItems(), ['prompt' => '',  "style" => "font-size:24px; height: 100%"])->label("SUCURSAL QUE RECIBE") ?>
                        </div>
                        <div class="col-sm-5">
                            <?= Html::label("INGRESA EL FOLIO","lbl_folio_venta") ?>
                            <?= Html::input("text",null,false,[ "class" => "form-control text-center", "style" => "font-size:24px;", "id" => "inputFolioSearch", 'placeholder' => 'Ingresar FOLIO #...', 'autocomplete' => 'off']) ?>
                        </div>

                        <div class="col-sm-3">
                            <?= Html::button('Buscar', ['class' => 'btn btn-primary btn-lg ', "style" => "font-size:20px; margin: 30px","id" => "btnFolioSeach"]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox" >
                <div class="ibox-content" style="height: 350px">
                    <div style="height: 90%">
                        <table class="table table-bordered" style="font-size: 9px;">
                            <thead>
                                <tr>
                                    <th style="width: 20%" class="text-center">CLAVE</th>
                                    <th style="width: 20%" class="text-center">PRODUCTO</th>
                                    <th style="width: 20%" class="text-center">CANTIDAD</th>
                                    <th style="width: 20%" class="text-center">DEVOLUCION</th>
                                    <th style="width: 20%" class="text-center">PRECIO COSTO</th>
                                    <th style="width: 20%" class="text-center">ACCIONES</th>
                                </tr>
                            <tbody class="content_producto" style="text-align: center;">
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>DEVOLUCIÓN - Nota / Comentario</h5>
                </div>
                <div class="ibox-content" >
                    <?= $form->field($model, 'devolucion_motivo_id')->dropDownList(EsysListaDesplegable::getItems('motivo_devolucion')) ?>

                    <?= $form->field($model, 'nota')->textarea(['rows' => 3])->label(false) ?>

                    <div class="form-group">
                        <?= Html::submitButton($model->isNewRecord ? 'Crear Devolución' : 'Guardar cambios', ["id" => "btnOperacion",'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                        <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white']) ?>
                    </div>
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
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_cantidad"]) ?></td>

                <td><?= Html::input('number',null,false,[  "class" => "form-control text-center", 'id' => 'table_devolucion' ]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_precio", "style" => "text-align:center;font-size: 16px;font-weight: bold;"]) ?></td>

            </tr>
        </tbody>
    </table>
</div>


<div class="fade modal inmodal " id="modal-metodo-pago"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label">
    <div class="modal-dialog modal-lg" style="width: 100%; max-width: 70%" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">INGRESAR PAGO / REEMBOLSO</h4>
            </div>
            <div class="modal-body">
                <?php // <h2><strong>Cliente: </strong><p class="lbl_cliente_venta"></p></h2> ?>
                <div class="alert alert-warning alert_forma_pago" style="display: none">

                </div>
                <h3>INGRESA CANTIDAD</h3>
                <div class="row" style="border-style: double;padding: 2%;">
                    <div class="col-sm-4">
                        <?= Html::label('REEMBOLSO / PAGO','lbl_tipo') ?>
                        <?=  Html::dropDownList('lbl_tipo', null, [ 10 => "REEMBOLSO", 20 => "PAGO"], ["class" => "form-control" , "id" => "inputTipoSelect"])  ?>
                    </div>
                    <div class="col-sm-6">
                        <?= Html::label('CANTIDAD','lbl_cantidad') ?>
                        <?= Html::input('number', null,false,[ "class" => "form-control", 'id' => 'inputCantidad']) ?>
                    </div>
                    <div class="col-sm-2">
                         <button  type="button"class="btn  btn-primary" id="btnAgregarMetodoPago" style="margin-top: 15px;" >Ingresar pago</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12" style="margin-top: 5%;">
                        <table class="table table-hover table-vcenter" style="background: aliceblue;">
                            <thead>
                                <tr>
                                    <th colspan="2" class="text-center">Forma de pago</th>
                                    <th colspan="2" class="text-center">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody class="content_metodo_pago" style="text-align: center;">

                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>
                                        <div class="widget navy-bg p-lg text-center">
                                            <span class="text-main text-semibold">TOTAL: </span>
                                            <strong id="total_metodo">0</strong>
                                        </div>
                                    </td>

                                    <td style="border: none" >
                                        <div class="widget lazur-bg p-lg text-center">
                                            <span class="text-main text-semibold">COBRO: </span>
                                            <strong id= "pago_metodo_total">0</strong>
                                        </div>
                                    </td>

                                    <td style="border: none" >
                                        <div class="widget red-bg p-lg text-center">
                                            <span class="text-main text-semibold">DEUDA: </span>
                                            <strong id= "balance_total">0</strong>
                                        </div>
                                    </td>

                                    <td style="border: none;" >
                                        <div class="widget yellow-bg p-lg text-center">
                                            <span class="text-main text-semibold">CAMBIO: </span>
                                            <strong id="cambio_metodo">0</strong>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::button('TERMINAR', ['class' => 'btn btn-primary btn-lg col-sm-3', "style" => "font-size:20px","id" => "btnTerminarAdd"]) ?>
            </div>
        </div>
    </div>
</div>

<div class="display-none">
    <table>
        <tbody class="template_metodo_pago">
            <tr id = "metodo_id_{{metodo_id}}">
                <td colspan="2"><?= Html::tag('p', "0",["class" => "text-main text-semibold" , "id"  => "table_metodo_id"]) ?></td>
                <td colspan="2"><?= Html::tag('p', "",["class" => "text-main " , "id"  => "table_metodo_cantidad","style" => "text-align:center"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    var $template_producto     = $('.template_producto'),
        $content_producto      = $(".content_producto"),
        $btnOperacion          = $("#btnOperacion"),
        $inputSucursalSelect   = $("#operacion-almacen_sucursal_id"),
        $inputOperacionDetalle = $("#operaciondetalle-operacion_detalle_array"),
        operacionList           = JSON.parse('<?= json_encode(Operacion::$operacionList) ?>'),
        productoArray           = [],
        reembolsoArray          = [],
        $inputFolioSearch       = $('#inputFolioSearch'),
        $btnFolioSeach          = $('#btnFolioSeach'),
        $inputMetodoPagoArray   = $('#inputMetodoPagoArray'),
        $inputVentaId           = $('#operacion-venta_id'),
        $inputTotalPago         = $('#inputTotalPago'),
        containerArray          = [],
        pago_total              = 0;

        /********************************************************
                    METODO DE PAGO / REEMBOLSO
        /********************************************************/
        var $template_metodo_pago   = $('.template_metodo_pago'),
        $content_metodo_pago        = $(".content_metodo_pago"),
        $btnAgregarMetodoPago       = $('#btnAgregarMetodoPago'),
        metodoPago_array            = [],
        $form_metodoPago = {
            $tipo       : $('#inputTipoSelect'),
            $cantidad   : $('#inputCantidad'),
        };

        /********************************************************/

         $(function(){
            $('body').addClass('mini-navbar');
        });


    //**********************************************//
    //              EVENTOS
    //**********************************************//
    $btnFolioSeach.click(function(){
        $(".alert_danger_message").hide();
        $(".alert_danger_message").html(null);
        containerArray = [];
        render_template();
        function_get_venta();


    });

    $inputFolioSearch.keypress(function (e) {
      if (e.which == 13) {
        $(".alert_danger_message").hide();
        $(".alert_danger_message").html(null);
        containerArray = [];
        render_template();
        function_get_venta();
        return false;    //<---- Add this line
      }
    });

    $('#btnOperacion').on('click', function(event){
        event.preventDefault();
        if ($inputSucursalSelect.val()) {
            if (containerArray.length > 0) {
                if (reembolsoArray.length == 0)
                    $("#modal-metodo-pago").modal('show');
                else
                    $(this).submit();
            }else{
                $(".alert_danger_message").show();
                $(".alert_danger_message").html("Debes ingresar una <strong>VENTA</strong>, verifica tu información");
            }
        }else{
            $(".alert_danger_message").show();
            $(".alert_danger_message").html("Debes seleccionar una <strong>SUCURSAL QUE RECIBE</strong>, verifica tu información");
        }
    });

    $('#btnTerminarAdd').on('click', function(event){
        event.preventDefault();
        if (pago_total > 0 ) {
            $('#form-devolucion').submit();
        }else{
            alert("EL TOTAL TIENE QUE SER MAYOR A 0");
        }
    });





    /*====================================================
    *               FUNCTIONS
    *====================================================*/


    var function_get_venta = function(){
        if ($inputFolioSearch.val()) {
            $.get("<?= Url::to(["get-venta"]) ?>",{ venta_id : $inputFolioSearch.val() },function($response){

                if ($response.code == 202) {

                    if ($response.venta.status == 20 || $response.venta.status == 40 ) {
                        $(".alert_danger_message").show();
                        $(".alert_danger_message").html("<strong>No puedes generar una Devolución de una Pre-Captura</strong>");
                    }

                    if ($response.venta.status == 30) {
                        $(".alert_danger_message").show();
                        $(".alert_danger_message").html("<strong>No puedes generar una Devolución de una Pre-Venta</strong>");
                    }

                    if ($response.venta.status == 10) {
                        $inputVentaId.val($response.venta.id);

                        $.each($response.venta.venta_detalle,function(key,detalleItem){
                            productoItem = {
                                "item_id"           : containerArray.length + 1,
                                "producto_id"        : detalleItem.producto_id,
                                "producto_nombre"    : detalleItem.producto,
                                "producto_clave"     : detalleItem.clave,
                                "costo"              : detalleItem.precio_venta,
                                "producto_unidad"    : detalleItem.producto_unidad,
                                "cantidad"           : detalleItem.cantidad,
                                "cantidad_devolucion": 0,
                            }

                            containerArray.push(productoItem);
                            productoArray = [];
                            render_template();

                        });
                    }
                }else{
                    $(".alert_danger_message").show();
                    $(".alert_danger_message").html($response.message);
                }
            },'json');
        }else{
            $(".alert_danger_message").show();
            $(".alert_danger_message").html("Debes ingresar un folio para poder continuar");
        }
    }

    var render_template = function()
    {
        $content_producto.html("");
        sumaDevolucion = 0;
        $.each(containerArray, function(key, producto){
            if (producto.item_id) {
                template_producto = $template_producto.html();
                template_producto = template_producto.replace("{{producto_id}}",producto.item_id);

                $content_producto.append(template_producto);

                $tr        =  $("#producto_id_" + producto.item_id, $content_producto);
                $tr.attr("data-item_id",producto.item_id);

                $("#table_clave_id",$tr).html(producto.producto_clave);
                $("#table_producto",$tr).html(producto.producto_nombre);


                $("#table_devolucion",$tr).val(producto.cantidad_devolucion);
                $("#table_devolucion",$tr).attr("onchange","refresh_change_producto(this)");

                sumaDevolucion = sumaDevolucion + ( parseFloat(producto.cantidad_devolucion) * producto.costo );

                $("#table_cantidad",$tr).html(producto.cantidad +" ("+ producto.producto_unidad +")");
                $("#table_precio",$tr).html( btf.conta.money(producto.costo) );
                $tr.append("<td><button type='button' class='btn btn-warning btn-circle' onclick='refresh_paquete(this)'><i class='fa fa-trash'></i></button></td>");
            }
        });
        pago_total = sumaDevolucion;
        $inputOperacionDetalle.val(JSON.stringify(containerArray));

        $('#total_metodo').html( btf.conta.money(pago_total) );
        $form_metodoPago.$cantidad.val(pago_total);
        $inputTotalPago.val(pago_total);
    };

    var refresh_paquete = function(ele){
        $ele_tr        = $(ele).closest('tr');
        $ele_tr_id  = $ele_tr.attr("data-item_id");

        $.each(containerArray, function(key, paquete){
            if (paquete.item_id == $ele_tr_id ){
                containerArray.splice(key, 1 );
            }
        });
        $inputOperacionDetalle.val(JSON.stringify(containerArray));
        render_template();
    }

    var refresh_change_producto = function(elem){

        $elemento       = $(elem);
        $ele_row        = $(elem).closest('tr');
        $ele_row_id     = $ele_row.attr("data-item_id");


        $.each(containerArray, function(key, productoItem){
            if (productoItem.item_id == $ele_row_id  ){
                if ( parseFloat($elemento.val()) >  parseFloat(productoItem.cantidad) )
                    productoItem.cantidad_devolucion = parseFloat(productoItem.cantidad);
                else
                    productoItem.cantidad_devolucion = parseFloat($elemento.val());

                if (parseFloat($elemento.val()) < 0 ) {
                    productoItem.cantidad_devolucion = 0;
                }
            }
        });

        $inputOperacionDetalle.val(JSON.stringify(containerArray));
        render_template();
    }


    /*====================================================
    *               METODO DE PAGO /  REEMBOLSO
    *====================================================*/



    $btnAgregarMetodoPago.click(function(){

        if(!$form_metodoPago.$tipo.val() || !$form_metodoPago.$cantidad.val()){
            return false;
        }

        metodo = {
            "metodo_id"         : metodoPago_array.length + 1,
            "tipo_id"           : $form_metodoPago.$tipo.val(),
            "tipo_text"         : $('option:selected', $form_metodoPago.$tipo).text(),
            "cantidad"          : parseFloat($form_metodoPago.$cantidad.val()),
        };

        metodoPago_array.push(metodo);
        $form_metodoPago.$cantidad.val(null);
        calcula_cambio_envio();
        render_metodo_template();
    });


    var render_metodo_template = function(){
        $content_metodo_pago.html("");
        $('.alert_forma_pago').hide();
        $('.alert_forma_pago').html("");
        pagado_total = 0;
        $.each(metodoPago_array, function(key, metodo){

            if (metodo.metodo_id) {

                metodo.metodo_id = key + 1;

                template_metodo_pago = $template_metodo_pago.html();
                template_metodo_pago = template_metodo_pago.replace("{{metodo_id}}",metodo.metodo_id);

                $content_metodo_pago.append(template_metodo_pago);

                $tr        =  $("#metodo_id_" + metodo.metodo_id, $content_metodo_pago);
                $tr.attr("data-metodo_id",metodo.metodo_id);
                $tr.attr("data-origen",metodo.origen);

                $("#table_metodo_id",$tr).html(metodo.tipo_text);
                $("#table_metodo_cantidad",$tr).html("$ " +metodo.cantidad);


                pagado_total = pagado_total + parseFloat(metodo.cantidad);

                if (metodo.origen != 2) {
                    $tr.append("<button type='button' class='btn btn-warning btn-circle' onclick='refresh_metodo(this)'><i class='fa fa-trash'></i></button>");
                }
            }
        });


        $('#total_metodo').html( btf.conta.money(total_metodo));

        balance_total = parseFloat( total_metodo - pagado_total.toFixed(2));

        $('#balance_total').html(btf.conta.money(balance_total));
        $('#pago_metodo_total').html(btf.conta.money(pagado_total));

        $inputMetodoPagoArray.val(JSON.stringify(metodoPago_array));
    }


    var calcula_cambio_envio = function(){
        pago_total_new = 0;
        $.each(metodoPago_array, function(key, metodo){
            if (metodo.metodo_id)
                pago_total_new = pago_total_new + parseFloat(metodo.cantidad);
        });

        new_cambio_metodo = pago_total_new - parseFloat(pago_total);


        if (metodoPago_array[0]){
            val_cambio_round = new_cambio_metodo < 0 ?  metodoPago_array[metodoPago_array.length - 1 ].cantidad : (metodoPago_array[metodoPago_array.length - 1 ].cantidad - new_cambio_metodo) < 0 ? 0 : (metodoPago_array[metodoPago_array.length - 1 ].cantidad - new_cambio_metodo);

            metodoPago_array[metodoPago_array.length - 1 ].cantidad = parseFloat(val_cambio_round).toFixed(2);
        }

        $('#cambio_metodo').html( new_cambio_metodo < 0 ? 0 :  btf.conta.money(new_cambio_metodo) );
    }

    var refresh_metodo = function(ele){
        $ele_paquete_val = $(ele).closest('tr');

        $ele_paquete_id  = $ele_paquete_val.attr("data-metodo_id");
        $ele_origen_id   = $ele_paquete_val.attr("data-origen");

        $.each(metodoPago_array, function(key, metodo){
            if (metodo) {
                if (metodo.metodo_id == $ele_paquete_id && metodo.origen == $ele_origen_id ) {
                    metodoPago_array.splice(key, 1 );
                }
            }
        });

        $(ele).closest('tr').remove();
        $inputMetodoPagoArray.val(JSON.stringify(metodoPago_array));
        //$inputcobroRembolsoEnvioArray.val(JSON.stringify(metodoPago_array));
        render_metodo_template();
        calcula_cambio_envio();
    }
</script>
