<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
//use kartik\date\DatePicker;
use kartik\select2\Select2;
use app\models\sucursal\Sucursal;
/* @var $this yii\web\View */
/* @var $model app\models\sucursales\Sucursal */
/* @var $form yii\widgets\ActiveForm */
?>


<div class="tpv-venta-form">

    <?php $form = ActiveForm::begin() ?>
    <?= $form->field($model->venta_detalle, 'venta_detalle_array')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'total')->hiddenInput()->label(false) ?>
    <div class="row">

        <div class="col-sm-8">
            <div class="ibox" >
                <div class="ibox-content" style="height: 550px">
                    <div class="row">
                        <?= Html::input("text",null,false,[ "class" => "form-control col-sm-9", "style" => "font-size:24px", "id" => "inputProductoAdd"]) ?>
                        <?= Html::button('Agregar', ['class' => 'btn btn-primary btn-lg col-sm-3', "style" => "font-size:20px","id" => "btnProductoAdd"]) ?>
                    </div>
                    <br/>
                    <div class="alert alert-danger alert_danger_message" style="display: none">
                    </div>
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
                            </thead>
                            <tbody class="content_producto" style="text-align: center;">
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr/>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="ibox">
                <div class="ibox-content" style="height: 550px">
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
                    <?php /* ?>
                    <div class="row text-center">
                        <div class="col">
                            <button class="btn btn-danger  dim btn-large-dim" type="button" data-target="#modal-producto" data-toggle="modal"  ><i class="fa fa-search"></i></button>
                            <small class="text-muted m-b block">BUSCAR PRODUCTO</small>
                        </div>
                    </div>
                    */?>
                    <hr>
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
                            ],

                    ]) ?>


                    <?= $form->field($model, 'ruta_sucursal_id')->widget(Select2::classname(),
                    [
                        'language' => 'es',
                        'data' => isset($model->sucursal)  && $model->sucursal ? [$model->sucursal->id => $model->sucursal->nombre ] : Sucursal::getSucursal(),
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                        'options' => [
                            'placeholder' => 'Selecciona la sucursal a ruta...',
                        ],
                    ]) ?>

                    <div class="row text-center">
                        <div class="col">
                            <?= Html::checkbox(
                                "CheckPrecioPublico",
                                true,
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
                                false,
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
                                false,
                                [
                                    "id"    => "check_mayoreo_access",
                                    "class" => "modulo magic-checkbox"
                                ]
                            ) ?>
                            <?= Html::label("P. MAYOREO", "check_mayoreo_access", ["style" => "display:inline"]) ?>

                        </div>
                    </div>


                    <div class="form-group" style="position: absolute;bottom: 10%;">
                        <?= Html::submitButton($model->isNewRecord ? 'GUARDAR' : 'Guardar cambios', ["id" => "btnOperacion",'class' => $model->isNewRecord ? 'btn btn-success btn-lg' : 'btn btn-primary btn-lg', 'style' => 'font-size: 24px' ]) ?>
                        <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white btn-lg' , 'style' => 'font-size: 24px']) ?>
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


<div class="display-none">
    <table>
        <tbody class="template_producto_search">
            <tr id = "producto_search_id_{{producto_search_id}}">
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_search_clave_id"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_search_producto","style" => "text-align:center"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_search_precio"]) ?></td>
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
                                <th style="width: 20%" class="text-center">ACCIONES</th>
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
    var $inputProductoAdd   = $('#inputProductoAdd'),
        $btnProductoAdd     = $('#btnProductoAdd'),
        $inputProductoSearch= $('#inputProductoSearch'),
        $template_producto          = $('.template_producto'),
        $template_producto_search   = $('.template_producto_search'),
        $content_producto   = $(".content_producto"),
        $content_search     = $(".content_search"),
        $inputventaDetalle  = $("#ventadetalle-venta_detalle_array"),
        $inputventaTotal    = $("#venta-total"),
        $btnClearVenta      = $("#btnClearVenta"),
        productoSearch      = [];
        containerArray      = [];

    $(function(){
        $('body').addClass('mini-navbar');
    });

    $inputProductoAdd.keypress(function (e) {
      if (e.which == 13) {
        $(this).trigger("enterKey");
        return false;    //<---- Add this line
      }
    });


    $inputProductoAdd.bind("enterKey",function(e){
        add_producto();
    });

    $btnProductoAdd.click(function(){
        add_producto();
    });
    $btnClearVenta.click(function(){
        containerArray =  [];
        render_template();
    });

    var add_producto = function(){
        $(".alert_danger_message").hide();
        inputSearch = $inputProductoAdd.val().split('*');
        clave       = inputSearch[0];
        cantidad    = inputSearch[1] ? inputSearch[1] : 1;
        $.get("<?= Url::to(['search-producto'])?>",{ clave :  clave },function($response){
            if ($response.code == 202) {
                productoArray = $response.producto;

                productoItem = {
                    "item_id"         : containerArray.length + 1,
                    "producto_id"        : productoArray.id,
                    "producto_nombre"    : productoArray.nombre,
                    "producto_clave"     : productoArray.clave,
                    "mayoreo"              : productoArray.mayoreo,
                    "menudeo"              : productoArray.menudeo,
                    "publico"              : productoArray.publico,
                    "precio_venta"         : 0,
                    "producto_proveedor" : productoArray.proveedor,
                    "producto_unidad"    : productoArray.tipo_medida_text,
                    "cantidad"           : cantidad,
                }

                containerArray.push(productoItem);
                productoArray = [];
                render_template();
                $inputProductoAdd.val(null);

            }
            if ($response.code == 10 ) {
                $(".alert_danger_message").show();
                $(".alert_danger_message").html($response.message);
            }
        });
    };

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


    /*====================================================
    *               RENDERIZA TODO LOS PAQUETE
    *====================================================*/
    var render_template = function()
    {
        $content_producto.html("");
        total_venta = 0;

        $.each(containerArray, function(key, producto){
            if (producto.item_id) {
                template_producto = $template_producto.html();
                template_producto = template_producto.replace("{{producto_id}}",producto.item_id);

                $content_producto.append(template_producto);


                $tr        =  $("#producto_id_" + producto.item_id, $content_producto);
                $tr.attr("data-item_id",producto.item_id);

                if ($("#check_publico_access").is(':checked'))
                    producto.precio_venta = producto.publico ? producto.publico : producto.publico;

                if ($("#check_menudeo_access").is(':checked'))
                    producto.precio_venta = producto.menudeo ? producto.menudeo : producto.publico;

                if ($("#check_mayoreo_access").is(':checked'))
                    producto.precio_venta = producto.mayoreo ? producto.mayoreo : producto.publico;

                $("#table_clave_id",$tr).html(producto.producto_clave);
                $("#table_producto",$tr).html(producto.producto_nombre);
                $("#table_cantidad",$tr).html(producto.cantidad +" ("+ producto.producto_unidad +")");
                $("#table_precio",$tr).html( btf.conta.money(producto.precio_venta * producto.cantidad ) );


                total_venta = producto.precio_venta * producto.cantidad;

                $tr.append("<td><button type='button' class='btn btn-warning btn-circle' onclick='refresh_paquete(this)'><i class='fa fa-trash'></i></button></td>");
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
                containerArray.splice(key, 1 );
            }
        });
        $inputventaDetalle.val(JSON.stringify(containerArray));
        render_template();
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

                $tr.append("<td><button type='button' class='btn btn-success' onclick='select_producto(this)'><i class='fa fa-check-square-o'></i> SELECCIONAR </button></td>");
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
</script>