<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use app\models\sucursal\Sucursal;
use app\assets\BootboxAsset;

BootboxAsset::register($this);

?>

<div class="logistica-carga-form">

    <?php $form = ActiveForm::begin(['id' => 'form-ruta']) ?>

    <?= $form->field($model, 'sucursal_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model->reparto_detalle, 'reparto_detalle_array')->hiddenInput()->label(false) ?>

    <div class="alert alert-danger div_alert_danger" style="display: none">
    </div>
    <div class="ibox">
        <div class="ibox-content">
            <div class="row">
                <div class="col-sm-8">
                    <h2 style="display: inline-block;">NUEVA CARGA   : </h2>
                    <?= Html::dropDownList('ruta_sucursal_id', null,Sucursal::getSucursal(), ['prompt' => 'SELECCIONA RUTA / SUCURSAL', 'class' => 'form-control m-b', "style" => 'display: inline-block;', 'id' => 'select_ruta_sucursal_id']) ?>
                </div>
                <div class="col-sm-4">
                    <?= Html::a('CARGAR PRECAPTURAS', null, [
                        'class' => 'btn m-b btn-lg btn-danger',
                        'id'    => 'btn_carga_precompras',
                        "style" => "margin-top:30px"
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <?php /* ?>
    <?= Html::a('CARGAR PRODUCTOS EXTRAS', null, [
        'class' => 'btn m-b btn-lg btn-success',
        'id'    => 'btn_add_producto',
        "style" => "margin-top:30px"
    ]); ?>
    */?>

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-content">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">VENTA #ID</th>
                                <th class="text-center">PRODUCTO #ID</th>
                                <th class="text-center">CLIENTE</th>
                                <th class="text-center">VENDEDOR</th>
                                <th class="text-center">PZ</th>
                                <th class="text-center">TOTAL</th>
                                <th class="text-center">FECHA</th>
                                <th class="text-center">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody class="container_precaptura">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group  div_submmit_form" style="display: none">
        <?= Html::submitButton($model->isNewRecord ? 'Crear carga' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'id' => 'btnSaveCarga' ]) ?>
        <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>



<div class="display-none">
    <table>
        <tbody class="template_container">
            <tr id = "item_id_{{venta_id}}">
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_count"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_venta_id"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_producto"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_cliente"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_vendedor"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_pz"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_total"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_fecha"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>



<script type="text/javascript">
    var $btn_carga_precompras       = $('#btn_carga_precompras'),
        $template_container         = $('.template_container'),
        $inputSucursalId            = $('#reparto-sucursal_id'),
        $select_ruta_sucursal_id    = $('#select_ruta_sucursal_id'),
        $container_precaptura       = $('.container_precaptura'),
        $btnSaveCarga               = $('#btnSaveCarga'),
        $inputRepartoDetalle        = $('#repartodetalle-reparto_detalle_array'),
        $carga_item                 = 0;
        containerArray              = [];


    $btn_carga_precompras.click(function(){
        $(".div_alert_danger").hide();
        $inputSucursalId.val(null);
        if ($select_ruta_sucursal_id.val()) {
            load_precaptura($select_ruta_sucursal_id.val());
        }else
            show_message("Debes seleccionar una RUTA / SUCURSAL, intenta nuevamente");

    });

    var load_precaptura = function($sucursal_id){
        $.get("<?= Url::to(['get-precaptura-sucursal']) ?>",{ sucursal_id : $sucursal_id }, function($response){
            if ($response.code == 202) {
                $.each($response.precaptura, function(key, prepedido){
                    if (prepedido.id) {
                        prepedido_item = {
                            "item_id"            : prepedido.id,
                            "producto_id"        : null,
                            "producto"           : null,
                            "cliente"            : prepedido.cliente,
                            "cliente_id"         : prepedido.cliente_id,
                            "cantidad"           : null,
                            "tipo"               : 10,
                            "total"              : prepedido.total,
                            "vendedor"           : prepedido.created_by_user,
                            "created_at"         : prepedido.created_at,
                            "check_true"         : 10,
                        };
                    }
                    containerArray.push(prepedido_item);
                    $('.div_submmit_form').show();
                    $inputSucursalId.val($sucursal_id);
                });
                render_template();
            }else{
                show_message($response.message);
            }
        },'json');
    }

    /*====================================================
    *               RENDERIZA LA VISTA
    *====================================================*/
    var render_template = function()
    {
        $container_precaptura.html("");
        total_venta = 0;
        $count_item = 0;
        $carga_item = 0;
        $.each(containerArray, function(key, prepedido){
            if (prepedido.item_id) {
                    $count_item = $count_item + 1;
                    template_container = $template_container.html();
                    template_container = template_container.replace("{{venta_id}}",prepedido.item_id);

                    $container_precaptura.append(template_container);


                    $tr        =  $("#item_id_" + prepedido.item_id, $container_precaptura);
                    $tr.attr("data-item_id",prepedido.item_id);

                    $("#table_count",$tr).html($count_item);
                    $("#table_venta_id",$tr).html(prepedido.item_id);
                    $("#table_producto",$tr).html(prepedido.producto);
                    $("#table_cliente",$tr).html(prepedido.cliente);
                    $("#table_vendedor",$tr).html(prepedido.vendedor);
                    $("#table_pz",$tr).html(prepedido.pz);
                    $("#table_total",$tr).html(btf.conta.money(prepedido.total));
                    $("#table_fecha",$tr).html( btf.time.datetime(prepedido.created_at ));

                    if (prepedido.check_true == 10 ){
                        $tr.append("<td class = 'text-center'><input type='checkbox' onclick='refresh_item(this)' checked></td>");
                        $carga_item = $carga_item + 1;
                    }

                    if(prepedido.check_true == 1)
                        $tr.append("<td class = 'text-center'><input type='checkbox' onclick='refresh_item(this)'></td>");

            }
        });

        $inputRepartoDetalle.val(JSON.stringify(containerArray));
    };

    var refresh_item = function(elem){

        $ele_paquete        = $(elem).closest('tr');
        $ele_precaptura_id  = $ele_paquete.attr("data-item_id");

        $.each(containerArray, function(key, precaptura){
            if (precaptura.item_id == $ele_precaptura_id ){
                if($(elem).is(":checked"))
                    precaptura.check_true = 10;
                else
                    precaptura.check_true = 1;
            }
        });

        $inputRepartoDetalle.val(JSON.stringify(containerArray));
        render_template();
    }

    var show_message = function($message){
        $('.div_alert_danger').html(null);
        $('.div_alert_danger').html($message);
        $('.div_alert_danger').show();
    }

    $btnSaveCarga.on('click', function(event){
        event.preventDefault();
        if ($carga_item == 0 ) {
            bootbox.alert("Debes ingresar minimo un elemento, intenta nuevamente");
            return false;
        }
        $btnSaveCarga.submit();
    });

</script>