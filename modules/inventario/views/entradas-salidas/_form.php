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


BootboxAsset::register($this);
/* @var $this yii\web\View */
/* @var $model app\models\sucursales\Sucursal */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="inv-entradas-salidas-form">

    <?php $form = ActiveForm::begin() ?>
    <?= $form->field($model->operacion_detalle, 'operacion_detalle_array')->hiddenInput()->label(false) ?>
    <div class="row ">
        <div class="col-sm-3">
            <div class="ibox">
                <div class="ibox-content" style="height: 620px">
                    <?= $form->field($model, 'tipo')->dropDownList(
                        [
                            Operacion::TIPO_ENTRADA => Operacion::$tipoList[Operacion::TIPO_ENTRADA],
                            Operacion::TIPO_SALIDA  => Operacion::$tipoList[Operacion::TIPO_SALIDA]
                        ], ['prompt' => '']) ?>

                    <?= $form->field($model, 'motivo')->dropDownList([], ['prompt' => '']) ?>

                    <?= $form->field($model, 'almacen_sucursal_id')->dropDownList(Sucursal::getItems(), ['prompt' => ''])->label("SUCURSAL - INVENTARIO") ?>

                    <div class="div_sucursal_surtir" style="display: none">
                        <?= $form->field($model, 'sucursal_recibe_id')->dropDownList(Sucursal::getItems(), ['prompt' => ''])->label("SUCURSAL QUE RECIBE") ?>
                    </div>
                    <div class="div_abastecimiento" style="display: none">
                        <div class="alert alert-warning div_alert_abastecimiento" style="display: none">

                        </div>
                        <?= $form->field($model, 'operacion_child_id')->dropDownList([], ['prompt' => ' -- ABASTECIMIENTO DISPONIBLES --'])->label("ABASTECIMIENTO: ") ?>
                    </div>

                    <div class="div_select_compra" style="display:none">
                        <?= $form->field($model, 'compra_id')->dropDownList(Compra::getItems(), ['prompt' => '']) ?>
                    </div>

                    <?= $form->field($model, 'nota')->textarea(['rows' => 6]) ?>


                    <div class="form-group m-2">
                        <?= Html::submitButton($model->isNewRecord ? 'Crear operación' : 'Guardar cambios', ["id" => "btnOperacion",'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                        <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white']) ?>
                    </div>


                </div>
            </div>
        </div>
        <div class="col-sm-9">
            <div class="ibox m-4" >
                <div class="ibox-content" style="height: 350px">
                    <div style="height: 100%">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 20%" class="text-center">CLAVE</th>
                                    <th style="width: 20%" class="text-center">PRODUCTO</th>
                                    <th style="width: 20%" class="text-center">CANTIDAD</th>
                                    <th style="width: 20%" class="text-center">PRECIO COSTO</th>
                                    <th style="width: 20%" class="text-center">ACCIONES</th>
                                </tr>
                            <tbody class="content_producto" style="text-align: center;">
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <?php /* ?><div class="row">
                        <?= Html::input("text",null,false,[ "class" => "form-control col-sm-9", "style" => "font-size:24px"]) ?>
                        <?= Html::button('Agregar', ['class' => 'btn btn-primary btn-lg col-sm-3', "style" => "font-size:24px"]) ?>
                    </div>
                    */?>
                </div>
                <hr>
                <div class="alert alert-danger alert_danger_message" style="display: none">
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-6 col-md-4">
                            <div class="form-group text-center">
                                <strong>BUSCAR PRODUCTO </strong>

                                <div class="input-group m-b">

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
                                                'url'      => Url::to(['/tpv/pre-captura/producto-ajax']),
                                                'dataType' => 'json',
                                                'cache'    => true,
                                                'processResults' => new JsExpression('function(data, params){  return {results: data} }'),
                                            ],

                                        ],
                                        'options' => [
                                            'placeholder' => 'Busca producto',
                                        ],
                                    ]) ?>



                                </div>

                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="form-group text-center">
                                <strong>Producto</strong>
                                <h2> <span class="lbl_producto_name">N/A</span> ( <span class="lbl_unidad_medida">N/A</span> )</h2>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-2">
                            <div class="form-group text-center">
                                <strong>Cantidad</strong>
                                <?= Html::input("number",null,false,[ "id" => "input_cantidad_id", "class" => "form-control text-center", "style" => "font-size:24px"]) ?>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-2">
                            <?= Html::button('Agregar', ['class' => 'btn btn-primary btn-block btn-lg', 'id' => 'btnAgregarProducto']) ?>
                        </div>
                    </div>
                    <div class="row" >
                        <div class="col-md-4">
                            <div class="form-group text-center">
                                <strong>PROVEEDOR</strong>
                                <h2> <span class="lbl_proveedor">N/A</span></h2>
                            </div>
                        </div>
                        <div class="col-md-4 div_costo_producto" style="display: none">
                            <div class="form-group text-center">
                                <strong>COSTO ENTRADA</strong>
                                <?= Html::input("number",null,false,[ "id" => "input_costo_id", "class" => "form-control text-center", "style" => "font-size:24px"]) ?>
                            </div>
                        </div>
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
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_producto","style" => "text-align:center"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_cantidad"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_precio"]) ?></td>

            </tr>
        </tbody>
    </table>
</div>


<script>
    var $select_tipo           = $('#operacion-tipo'),
        $select_motivo         = $('#operacion-motivo'),
        $template_producto     = $('.template_producto'),
        $content_producto      = $(".content_producto"),
        $btnSearchProducto     = $("#btnSearchProducto"),
        $btnOperacion          = $("#btnOperacion"),
        $selectCompra          = $("#operacion-compra_id"),
        $div_select_compra     = $(".div_select_compra"),
        $selectSucursal        = $("#operacion-almacen_sucursal_id"),
        $selectAbastecimiento  = $("#operacion-operacion_child_id"),
        $VAR_MERCANCIA_NUEVA   = "<?= Operacion::ENTRADA_MERCANCIA_NUEVA ?>",
        $VAR_SALIDA_CADUCIDAD  = "<?= Operacion::SALIDA_CADUCIDAD ?>",
        $VAR_SURTIR            = "<?= Operacion::SALIDA_TRASPASO ?>",
        $VAR_ABASTECIMIENTO    = "<?= Operacion::ENTRADA_TRASPASO ?>",
        $inputOperacionDetalle = $("#operaciondetalle-operacion_detalle_array"),
        operacionList   = JSON.parse('<?= json_encode(Operacion::$operacionList) ?>'),
        productoArray   = [],
        containerArray  = [];

    $select_tipo.change(function(){
        $select_motivo.html(null);
        $div_select_compra.hide();
        if ($(this).val() == 10 ){
            newOption1       = new Option(operacionList[10], 10);
            newOption2       = new Option(operacionList[20], 20);
            $select_motivo.append(newOption1);
            $select_motivo.append(newOption2);
        }
        if ($(this).val() == 20 ){
            newOption1       = new Option(operacionList[30], 30);
            newOption2       = new Option(operacionList[40], 40);
            $select_motivo.append(newOption1);
            $select_motivo.append(newOption2);

        }
        $select_motivo.trigger('change');
        containerArray = [];
        render_template();
    });

    $selectCompra.change(function(){
        $(".alert_danger_message").hide();
        $(".alert_danger_message").html(null);
        containerArray = [];
        render_template();
        if ($(this).val()) {
            $.get("<?= Url::to(["get-compra"]) ?>",{ compra_id : $(this).val() },function($response){
                if ($response.code == 202) {
                    $.each($response.compra,function(key,producto){
                        productoItem = {
                            "item_id"           : containerArray.length + 1,
                            "producto_id"        : producto.producto_id,
                            "producto_nombre"    : producto.producto_nombre,
                            "producto_clave"     : producto.producto_clave,
                            "costo"              : producto.costo,
                            "producto_proveedor" : producto.producto_proveedor,
                            "producto_unidad"    : producto.producto_unidad_text,
                            "cantidad"           : producto.cantidad,
                        }

                        containerArray.push(productoItem);
                        productoArray = [];
                        render_template();

                    });
                }else{
                    $(".alert_danger_message").show();
                    $(".alert_danger_message").html($response.message);
                }
            },'json');
        }

    });

    $select_motivo.change(function(){
        $('.div_sucursal_surtir').hide();
        $('.div_abastecimiento').hide();
        if ( $(this).val() == $VAR_MERCANCIA_NUEVA ){
            $('.div_costo_producto').show();
            $div_select_compra.show();
        }
        else{
            $('.div_costo_producto').hide();
            $div_select_compra.hide();
        }
        if( $(this).val() == $VAR_SURTIR){
            $('.div_sucursal_surtir').show();
        }

        if( $(this).val() == $VAR_ABASTECIMIENTO){
            $('.div_abastecimiento').show();
            function_load_abastecimiento();
        }
    });

    $selectSucursal.change(function(){
        if ($select_motivo.val() == $VAR_ABASTECIMIENTO && $(this).val() ) {
            function_load_abastecimiento()

        }
    });

    var function_load_abastecimiento = function(){
        $selectAbastecimiento.html(null);
        $('.div_alert_abastecimiento').hide();
        if ($selectSucursal.val()) {
            $.get("<?= Url::to(['get-abastecimiento-disponible']) ?>",{ sucursal_id : $selectSucursal.val() },function($response){
                if ($response.code == 202) {

                    $.each($response.solicitud, function(key, solicitud){
                        $selectAbastecimiento.append(new Option("[ "+ solicitud.operacion_folio + " ] " + solicitud.sucursal_nombre + " - "+ solicitud.fecha , solicitud.operacion_id));
                    });

                    $selectAbastecimiento.change();
                }
            },'json');
        }else{
            $('.div_alert_abastecimiento').show();
            $('.div_alert_abastecimiento').html("<strong style='font-size: 10px'>ES IMPORTANTE QUE SELECCIONES UNA SUCURSAL </strong> , PARA CONSULTAR LA INFORMACION DE <strong>ABASTECIMIENTOS DISPONIBLES</strong>");
        }
    }

    $selectAbastecimiento.change(function(){
        $(".alert_danger_message").hide();
        $(".alert_danger_message").html(null);
        containerArray = [];
        render_template();
        if ($(this).val()) {
            $.get("<?= Url::to(["get-abastecimiento"]) ?>",{ operacion_id : $(this).val() },function($response){
                if ($response.code == 202) {
                    $.each($response.compra,function(key,producto){
                        productoItem = {
                            "item_id"           : containerArray.length + 1,
                            "producto_id"        : producto.producto_id,
                            "producto_nombre"    : producto.producto_nombre,
                            "producto_clave"     : producto.producto_clave,
                            "costo"              : producto.costo,
                            "producto_proveedor" : producto.producto_proveedor,
                            "producto_unidad"    : producto.producto_unidad_text,
                            "cantidad"           : producto.cantidad,
                        }

                        containerArray.push(productoItem);
                        productoArray = [];
                        render_template();

                    });
                }else{
                    $(".alert_danger_message").show();
                    $(".alert_danger_message").html($response.message);
                }
            },'json');
        }
    });

    $('#producto-nombre_id').change(function(){

        $(".alert_danger_message").hide();
        $.get("<?= Url::to(['search-producto'])?>",{ id : $('#producto-nombre_id').val(), sucursal_id : $selectSucursal.val() },function($response){
            if ($response.code == 202) {
                $(".lbl_producto_name").html($response.producto.nombre);
                $(".lbl_unidad_medida").html($response.producto.tipo_medida_text);
                $(".lbl_proveedor").html($response.producto.proveedor);
                productoArray = $response.producto;
            }
            if ($response.code == 10 ) {
                $(".alert_danger_message").show();
                $(".alert_danger_message").html($response.message);
            }
        });
    });



    $('#input_cantidad_id').keypress(function (e) {
      if (e.which == 13) {
        $(this).trigger("enterKey");
        return false;    //<---- Add this line
      }
    });

    $('#input_costo_id').keypress(function (e) {
      if (e.which == 13) {
        $(this).trigger("enterKey");
        return false;    //<---- Add this line
      }
    });

     $('#input_cantidad_id').change(function(){
        if (productoArray.costo) {
            $('#input_costo_id').val(productoArray.costo * $(this).val());
        }
     });



    $('#btnOperacion').on('click', function(event){
        event.preventDefault();
        if (containerArray.length == 0)
            bootbox.alert("Debes ingresar minimo un producto!");
        else
            $(this).submit();
    });


    $('#btnAgregarProducto').click(function(){

        if ($('#input_cantidad_id').val() &&  $('#producto-nombre_id').val() && productoArray.id ) {

            if ($select_motivo.val() == $VAR_SURTIR || $select_motivo.val() == $VAR_SALIDA_CADUCIDAD  ){
                if ( parseFloat($('#input_cantidad_id').val()) >  parseFloat(productoArray.existencia) ) {
                    $(".alert_danger_message").show();
                    $(".alert_danger_message").html(" * El producto <strong>" +productoArray.nombre + "<strong> no tiene la existencia suficiente ["+ parseFloat(productoArray.existencia) +" - "+ productoArray.tipo_medida_text+"]");


                    $('#input_cantidad_id').val(null);
                    $('#input_costo_id').val(null);
                    $('#producto-nombre_id').val(null);
                    $('#producto-nombre_id').html(null);
                    $(".lbl_producto_name").html(null);
                    $(".lbl_unidad_medida").html(null);
                    $(".lbl_proveedor").html(null);

                    return false;
                }
            }

            productoItem = {
                "item_id"         : containerArray.length + 1,
                "producto_id"        : productoArray.id,
                "producto_nombre"    : productoArray.nombre,
                "producto_clave"     : productoArray.clave,
                "costo"              : $('#input_costo_id').val() ? parseInt($('#input_costo_id').val()) : 0,
                "producto_proveedor" : productoArray.proveedor,
                "producto_unidad"    : productoArray.tipo_medida_text,
                "cantidad"           : $('#input_cantidad_id').val(),
            }

            containerArray.push(productoItem);
            productoArray = [];
            render_template();
            $('#input_cantidad_id').val(null);
            $('#input_costo_id').val(null);
            $('#producto-nombre_id').val(null);
            $('#producto-nombre_id').html(null);
            $(".lbl_producto_name").html(null);
            $(".lbl_unidad_medida").html(null);
            $(".lbl_proveedor").html(null);

        }else{
            $(".alert_danger_message").show();
            $(".alert_danger_message").html(" * Verifica tu información, intenta nuevamente");
        }
    });

    /*====================================================
    *               RENDERIZA TODO LOS PAQUETE
    *====================================================*/
    var render_template = function()
    {
        $content_producto.html("");

        $.each(containerArray, function(key, producto){
            if (producto.item_id) {
                template_producto = $template_producto.html();
                template_producto = template_producto.replace("{{producto_id}}",producto.item_id);

                $content_producto.append(template_producto);

                $tr        =  $("#producto_id_" + producto.item_id, $content_producto);
                $tr.attr("data-item_id",producto.item_id);

                $("#table_clave_id",$tr).html(producto.producto_clave);
                $("#table_producto",$tr).html(producto.producto_nombre);
                $("#table_cantidad",$tr).html(producto.cantidad +" ("+ producto.producto_unidad +")");
                $("#table_precio",$tr).html( btf.conta.money(producto.costo) );
                $tr.append("<td><button type='button' class='btn btn-warning btn-circle' onclick='refresh_paquete(this)'><i class='fa fa-trash'></i></button></td>");
            }
        });

        $inputOperacionDetalle.val(JSON.stringify(containerArray));
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

</script>
