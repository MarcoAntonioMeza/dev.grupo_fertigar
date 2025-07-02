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

<div class="logistica-pedidos-form">

    <div class="alert alert-danger div_alert_pedido_danger" style="display: none">
    </div>
    <div class="ibox">
        <div class="ibox-content">
            <div class="row">
                <div class="col-sm-8">
                    <h2 style="display: inline-block;">LISTA  DE PEDIDOS   : </h2>
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>FILTRA POR RUTA</strong>
                            <?= Html::dropDownList('ruta_sucursal_id', null,Sucursal::getRuta(), ['prompt' => 'SELECCIONA RUTA / SUCURSAL', 'class' => 'form-control m-b', "style" => 'display: inline-block;', 'id' => 'select_pedido_ruta_sucursal_id']) ?>
                        </div>
                        <div class="col-sm-6">
                            <strong>FILTRA POR [TIENDA / CEDIS]</strong>
                            <?= Html::dropDownList('sucursal_id', null,Sucursal::getAlmacenSucursal(), ['prompt' => 'PEDIDOS DE', 'class' => 'form-control m-b', "style" => 'display: inline-block;', 'id' => 'pedido_sucursal_id']) ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-2">
                    <?= Html::a('CARGAR PEDIDOS', null, [
                        'class' => 'btn m-b btn-lg btn-danger btn-block',
                        'id'    => 'btn_carga_pedido',
                        "style" => "margin-top:30px"
                    ]); ?>
                </div>
                <div class="col-sm-2">
                    <?= Html::a('DESCARGAR LISTA DE PEDIDOS', null, [
                        'class' => 'btn m-b btn-lg btn-info btn-block',
                        'id'    => 'btn_pdf_pedido',
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
                                <th class="text-center">FOLIO [VENTA]</th>
                                <th class="text-center">RUTA</th>
                                <th class="text-center">CANTIDAD SOLICITADA</th>
                                <th class="text-center">PRODUCTO</th>
                                <th class="text-center">CLIENTE</th>
                                <th class="text-center">CANTIDAD INVENTARIO</th>
                                <th class="text-center">UNIDAD DE MEDIDA</th>
                            </tr>
                        </thead>
                        <tbody class="container_pedido">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


</div>



<div class="display-none">
    <table>
        <tbody class="template_container_pedido">
            <tr id = "item_id_{{venta_id}}">
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_folio_pedido"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_ruta_pedido", "style" => "font-weight:bold"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_folio_cantidad_solicitada"]) ?></td>

                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_folio_producto"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_folio_cliente"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_folio_cantidad_inventario"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_folio_unidad_medida"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>






<script type="text/javascript">
    var $btn_carga_pedido       = $('#btn_carga_pedido'),
        $template_container_pedido         = $('.template_container_pedido'),
        $select_pedido_ruta_sucursal_id    = $('#select_pedido_ruta_sucursal_id'),
        $pedido_pertenece_id               = $('#pedido_sucursal_id'),
        $container_pedido           = $('.container_pedido'),
        $container_productos        = $('.container_productos'),
        $template_preventa          = $('.template_preventa'),
        $btnPdfPedido             = $('#btn_pdf_pedido'),
        $inputRepartoDetalle        = $('#repartodetalle-reparto_detalle_array'),
        $carga_item                 = 0;
        containerPedidoArray        = [];
        containerVentaArray         = [];


    $btn_carga_pedido.click(function(){

        if ($select_pedido_ruta_sucursal_id.val()) {
            if ($pedido_pertenece_id.val())
                load_pedido($select_pedido_ruta_sucursal_id.val(), $pedido_pertenece_id);
            else
                load_pedido($select_pedido_ruta_sucursal_id.val());
        }else
            show_message("Debes seleccionar una RUTA / SUCURSAL, intenta nuevamente");

    });

    var load_pedido = function($sucursal_id, $pedido_pertenece_id = null){
        $(".div_alert_pedido_danger").hide();

        containerPedidoArray = [];
        containerVentaArray = [];
        $container_pedido.html(null);
        $.get("<?= Url::to(['get-pedido']) ?>",{ sucursal_id : $sucursal_id, pertenece_id : $pedido_pertenece_id ? $pedido_pertenece_id.val() : null }, function($response){
            if ($response.code == 202) {
                $.each($response.pedido, function(key, pedido){
                    if (pedido.folio) {
                        pedido_item = {
                            "item_id"            : containerPedidoArray.length + 1,
                            "folio"              : pedido.folio,
                            "ruta"               : pedido.ruta,
                            "producto"           : pedido.producto,
                            "cliente_id"         : pedido.cliente_id,
                            "cliente"            : pedido.cliente,
                            "clave"              : pedido.clave,
                            "tipo_medida"        : pedido.tipo_medida,
                            "total_producto"     : pedido.total_producto,
                            "inventario"         : pedido.inventario,
                        };
                    }
                    containerPedidoArray.push(pedido_item);
                });
                render_template_pedido();
            }else{
                show_message($response.message);
            }
        },'json');
    }

    /*====================================================
    *               RENDERIZA LA VISTA
    *====================================================*/
    var render_template_pedido = function()
    {
        $container_pedido.html(null);
        total_venta = 0;
        $count_item = 0;
        $carga_item = 0;
        $.each(containerPedidoArray, function(key, pedido){
            if (pedido.item_id) {
                    $count_item = $count_item + 1;
                    template_container_pedido = $template_container_pedido.html();
                    template_container_pedido = template_container_pedido.replace("{{venta_id}}",pedido.item_id);
                    console.log(pedido);
                    $container_pedido.append(template_container_pedido);
                    var cantidadmasunidad=pedido.total_producto+' '+btf.producto.unidad(pedido.tipo_medida);

                    $tr        =  $("#item_id_" + pedido.item_id, $container_pedido);
                    $tr.attr("data-item_id",pedido.item_id);

                    //$("#table_folio_pedido",$tr).html("#"+pedido.folio);
                    $("#table_folio_pedido",$tr).html("<a href='"+pathUrl+"tpv/pre-captura/view?id="+pedido.folio+"'  target='_blank' style='font-size: 16px;'>"+pedido.folio+"</a>");

                    $("#table_folio_cantidad_solicitada",$tr).html(cantidadmasunidad);
                    $("#table_ruta_pedido",$tr).html(pedido.ruta);
                    $("#table_folio_producto",$tr).html(pedido.producto);
                    $("#table_folio_cliente",$tr).html(pedido.cliente);
                    $("#table_folio_cantidad_inventario",$tr).html(pedido.inventario);
                    $("#table_folio_unidad_medida",$tr).html(  btf.producto.unidad(pedido.tipo_medida) );

            }
        });

        //$inputRepartoDetalle.val(JSON.stringify(containerPedidoArray));
    };



    var show_message = function($message){
        $('.div_alert_pedido_danger').html(null);
        $('.div_alert_pedido_danger').html($message);
        $('.div_alert_pedido_danger').show();
    }


    $btnPdfPedido.click(function(){
        if ($select_pedido_ruta_sucursal_id.val() && $pedido_pertenece_id.val()  ) {
            window.open('<?= Url::to(['download-pedido-pdf']) ?>?sucursal_id=' + $pedido_pertenece_id.val() +'&ruta_id='+ $select_pedido_ruta_sucursal_id.val(),
            'imprimir',
            'width=600,height=600');
        }else
            show_message("Debes seleccionar una RUTA / SUCURSAL, intenta nuevamente");

    });



</script>