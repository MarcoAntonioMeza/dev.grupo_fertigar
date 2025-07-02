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

    <div class="alert alert-danger div_alert_danger" style="display: none">
    </div>
    <div class="ibox">
        <div class="ibox-content">
            <div class="row">
                <div class="col-sm-8">
                    <h2 style="display: inline-block;">LISTA  DE EMBARQUE   : </h2>
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>FILTRA POR RUTA</strong>
                            <?= Html::dropDownList('ruta_sucursal_id', null,Sucursal::getRuta(), ['prompt' => 'SELECCIONA RUTA / SUCURSAL', 'class' => 'form-control m-b', "style" => 'display: inline-block;', 'id' => 'select_ruta_sucursal_id']) ?>
                        </div>
                        <div class="col-sm-6">
                            <strong>FILTRA POR [TIENDA / CEDIS]</strong>
                            <?= Html::dropDownList('sucursal_id', null,Sucursal::getAlmacenSucursal(), ['prompt' => 'PRECAPTURAS DE', 'class' => 'form-control m-b', "style" => 'display: inline-block;', 'id' => 'sucursal_id']) ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-2">
                    <?= Html::a('CARGAR PRECAPTURAS', null, [
                        'class' => 'btn m-b btn-lg btn-danger btn-block',
                        'id'    => 'btn_carga_precompras',
                        "style" => "margin-top:30px"
                    ]); ?>
                </div>
                <div class="col-sm-2">
                    <?= Html::a('DESCARGAR EMBARQUE', null, [
                        'class' => 'btn m-b btn-lg btn-info btn-block',
                        'id'    => 'btn_pdf_embarque',
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
                                <th class="text-center">PERTENECE</th>
                                <th class="text-center">PREVENTA #ID</th>
                                <th class="text-center">CLIENTE</th>
                                <th class="text-center">VENDEDOR</th>
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


</div>



<div class="display-none">
    <table>
        <tbody class="template_container">
            <tr id = "item_id_{{venta_id}}">
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_count"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_pertenece", "style" => "font-weight:bold"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_venta_id"]) ?></td>

                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_cliente"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_vendedor"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_total"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_fecha"]) ?></td>
                <td id="div_btn_seccion" class="text-center"></td>
            </tr>
        </tbody>
    </table>
</div>


<div class="display-none">
    <div class="template_preventa">
        <div id="item_venta_detail_{{venta_detail_id}}" style="width:95%">
            <div class="row">
                <div class="col-sm-6">
                    <p style="color: #000">PRODUCTO : <strong style="font-size: 20px;" id="lbl_producto_text"></strong></p>

                </div>
                <div class="col-sm-3">
                    <p style="color:#000;">TOTAL SOLICITADO: <strong style="font-size: 20px;" id="lbl_solicitado"></strong><p id="lbl_link_nota"></p></p>
                </div>
                <div class="col-sm-3">
                    <p style="color:#000;">TOTAL DISPONIBLE: <strong style="font-size: 20px;" id="lbl_inventario"></strong></p>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-center">PREVENTA</th>
                                <th class="text-center">CLIENTE</th>
                                <th class="text-center">TELEFONO</th>
                                <th class="text-center">PRODUCTO</th>
                                <th class="text-center">CANTIDAD</th>
                                <th class="text-center">VENDEDOR</th>
                                <th class="text-center">QUITAR</th>
                            </tr>
                        </thead>
                        <tbody class="text_load container_detalle_preventas">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="fade modal inmodal " id="modal-show-preventa"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label" >
    <div class="modal-dialog modal-lg" style="max-width:80%">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> AJUSTE DE PREVENTAS </h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">

                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-sm-3">
                                <?= Html::img('@web/img/logo.png', ["height"=>"80px"]) ?>
                            </div>
                        </div>
                        <div class="container_productos table-responsive" style="overflow-x: auto;">

                        </div>
                    </div>
                </div>


            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var $btn_carga_precompras       = $('#btn_carga_precompras'),
        $template_container         = $('.template_container'),
        $inputSucursalId            = $('#reparto-sucursal_id'),
        $select_ruta_sucursal_id    = $('#select_ruta_sucursal_id'),
        $pertenece_id               = $('#sucursal_id'),
        $container_precaptura       = $('.container_precaptura'),
        $container_productos        = $('.container_productos'),
        $btnSaveCarga               = $('#btnSaveCarga'),
        $template_preventa          = $('.template_preventa'),
        $btnPdfEmbarque             = $('#btn_pdf_embarque'),
        $inputRepartoDetalle        = $('#repartodetalle-reparto_detalle_array'),
        $carga_item                 = 0;
        containerArray              = [];
        pathUrl                     = "<?= Url::to(['/']) ?>";
        containerVentaArray         = [];


    $btn_carga_precompras.click(function(){

        if ($select_ruta_sucursal_id.val()) {
            if ($pertenece_id.val())
                load_precaptura($select_ruta_sucursal_id.val(), $pertenece_id);
            else
                load_precaptura($select_ruta_sucursal_id.val());
        }else{
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.warning("DEBES SELECCIONAR UNA RUTA, INTENTA NUEVAMENTE");
        }

    });

    var load_precaptura = function($sucursal_id, $pertenece_id = null){
        $(".div_alert_danger").hide();
        $inputSucursalId.val(null);
        containerArray = [];
        containerVentaArray = [];
        $container_precaptura.html(null);
        $.get("<?= Url::to(['get-precaptura-sucursal']) ?>",{ sucursal_id : $sucursal_id, pertenece_id : $pertenece_id ? $pertenece_id.val() : null }, function($response){
            if ($response.code == 202) {
                $.each($response.precaptura, function(key, prepedido){
                    if (prepedido.id) {
                        prepedido_item = {
                            "item_id"            : prepedido.id,
                            "cliente"            : prepedido.cliente,
                            "pertenece"          : prepedido.pertenece,
                            "cliente_id"         : prepedido.cliente_id,
                            "tipo"               : 10,
                            "total"              : prepedido.total,
                            "vendedor"           : prepedido.created_by_user,
                            "created_at"         : prepedido.created_at,
                            "is_abastecimiento"  : prepedido.is_abastecimiento,
                            "check_true"         : 10,
                        };

                        prepedido_embarque_item = {
                            "item_id"            : prepedido.id,
                            "check_true"         : 10,
                        };
                    }
                    containerArray.push(prepedido_item);
                    containerVentaArray.push(prepedido_embarque_item);
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
        $container_precaptura.html(null);
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
                    if (prepedido.is_abastecimiento == 20)
                        $tr.css({"background":"#a51414","color": '#fff'});

                    $("#table_count",$tr).html($count_item);
                    $("#table_venta_id",$tr).html("<a href='"+pathUrl+"tpv/pre-captura/view?id="+prepedido.item_id+"'  target='_blank' style='font-size: 16px;'>"+prepedido.item_id+"</a>");
                    $("#table_pertenece",$tr).html(prepedido.pertenece);
                    $("#table_producto",$tr).html(prepedido.producto);
                    $("#table_cliente",$tr).html(prepedido.cliente);
                    $("#table_vendedor",$tr).html(prepedido.vendedor);
                    $("#table_total",$tr).html(btf.conta.money(prepedido.total));
                    $("#table_fecha",$tr).html( btf.time.datetime(prepedido.created_at ));

                    if (prepedido.check_true == 10 ){
                        //$tr.append("<td class = 'text-center'></td>");
                        $("#div_btn_seccion",$tr).append("<input type='checkbox' onclick='refresh_item(this)' checked>");
                        $carga_item = $carga_item + 1;
                    }

                    if(prepedido.check_true == 1)
                        $("#div_btn_seccion",$tr).append("<input type='checkbox' onclick='refresh_item(this)'>");

                    //if (prepedido.is_abastecimiento == 20)
                        $("#div_btn_seccion",$tr).append("<button onclick='refresh_edit_preventa(this,"+prepedido.item_id+")' class='btn btn-success btn-circle btn-xs' style='margin: 10px;'><i class='fa fa-pencil'></i> </button>");

            }
        });

        //$inputRepartoDetalle.val(JSON.stringify(containerArray));
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

        $.each(containerVentaArray, function(key, precaptura){
            if (precaptura.item_id == $ele_precaptura_id ){
                if($(elem).is(":checked"))
                    precaptura.check_true = 10;
                else
                    precaptura.check_true = 1;
            }
        });

        //$inputRepartoDetalle.val(JSON.stringify(containerArray));
        render_template();
    }

    var refresh_edit_preventa = function(elem, preventa_id){
        $('#modal-show-preventa').modal('show');
        $container_productos.html(null);
        $.get("<?= Url::to(['get-precapturas-inventario']) ?>", { preventa_id : preventa_id}, function($response){
            if ($response.code == 202) {

                $.each($response.precaptura, function(key, item_detail){
                    if (item_detail.producto_id) {
                        totalSolicitadoCount = 0;
                        contentLinkNotaVenta = '';
                        template_preventa = $template_preventa.html();
                        template_preventa = template_preventa.replace("{{venta_detail_id}}",item_detail.producto_id);
                        $container_productos.append(template_preventa);
                        $div_container =  $("#item_venta_detail_" + item_detail.producto_id, $container_productos);

                        $("#lbl_producto_text",$div_container).html(item_detail.producto);
                        $("#lbl_inventario",$div_container).html(item_detail.inventario);


                        $('.container_detalle_preventas',$div_container).html(null);
                        $contentHtml = '';

                        $.each(item_detail.preventas,function(key,detail){
                            if (detail.id) {
                                $contentHtml += "<tr data-preventa-detail = "+ detail.id +" style='background: "+ (detail.cantidad == 0 ? '#a514145e': (  item_detail.abastecimiento == 20 ? '#d5a61a' : '' ) )+"'>"+
                                    "<td class='text-center'><a href='"+ pathUrl +"/tpv/pre-captura/view?id="+detail.preventa_id+"' target='_blank'>#"+ detail.preventa_id +"</a></td>"+
                                    "<td class='text-center'>"+ detail.cliente +"</td>"+
                                    "<td class='text-center'>"+ detail.telefono+"</td>"+
                                    "<td class='text-center'>"+ detail.producto+"</td>"+
                                    "<td class='text-center'> <input class='form-control text-center input_cantidad_producto' type='number' step='0.001'  value='"+ detail.cantidad+"' /> "+ (detail.cantidad == 0 ? '<strong>El producto requiere una CONVERSION [ ' + detail.conversion_cantidad + ' a unidades por '+ detail.tipo_medida_text +'(s)]</strong>': '' ) + (  detail.abastecimiento == 20 ? '<strong>Existe un problema de abastecimiento</strong>' : '' )+"</td>"+
                                    "<td class='text-center'>"+ detail.vendedor+"</td>"+
                                    "<td class='text-center'><button class='btn btn-danger btn-circle' onclick='refresh_delete(this, "+ detail.id +")'><i class='fa fa-trash'></i></button> <button class='btn btn-primary btn-circle'><i class='fa fa-refresh' onclick='refresh_cantidad(this, "+ detail.id +")'></i></button></td>"+
                                "</tr>";
                            }
                        });


                        contentLinkNotaVenta = '';

                        $.each(item_detail.abastecimiento_ventas,function(key, item_folio){
                            totalSolicitadoCount = totalSolicitadoCount + parseFloat(item_folio.cantidad);
                            contentLinkNotaVenta+="<a href='"+ pathUrl +"/tpv/pre-captura/view?id="+item_folio.folio+"' target='_blank'>#"+item_folio.folio+"</a> - ";

                        });

                        $('.container_detalle_preventas',$div_container).html($contentHtml);

                        $("#lbl_solicitado",$div_container).html(totalSolicitadoCount);
                        $("#lbl_link_nota",$div_container).html(contentLinkNotaVenta);
                    }
                });

            }
        },'json');
    }

    var refresh_cantidad = function( elem, preventa_detail_id ){
        $ele_paquete_val = $(elem).closest('tr');

        $.post("<?= Url::to(['update-preventa']) ?>",{ preventa_detail_id : preventa_detail_id, cantidad: $(".input_cantidad_producto",$ele_paquete_val).val() } , function($response){
            if ($response.code == 202 ) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                toastr.success($response.message);
                load_precaptura($select_ruta_sucursal_id.val(), $pertenece_id);
            }
        })
    }

    var refresh_delete = function( elem, preventa_detail_id ){
        $ele_paquete_val = $(elem).closest('tr').remove();
        $.post("<?= Url::to(['remove-preventa']) ?>",{ preventa_detail_id : preventa_detail_id } , function($response){
            if ($response.code == 202 ) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                toastr.success($response.message);
                load_precaptura($select_ruta_sucursal_id.val(), $pertenece_id);
            }
        })
    }

    var show_message = function($message){
        $('.div_alert_danger').html(null);
        $('.div_alert_danger').html($message);
        $('.div_alert_danger').show();
    }


    $btnPdfEmbarque.click(function(){
        if ($select_ruta_sucursal_id.val()) {
            window.open('<?= Url::to(['download-embarque-pdf']) ?>?embarque=' + JSON.stringify(containerVentaArray) +'&ruta_sucursal_id='+ $select_ruta_sucursal_id.val(),
            'imprimir',
            'width=600,height=600');
        }else
            show_message("Debes seleccionar una RUTA / SUCURSAL, intenta nuevamente");

    });

    $btnSaveCarga.on('click', function(event){
        event.preventDefault();
        if ($carga_item == 0 ) {
            bootbox.alert("Debes ingresar minimo un elemento, intenta nuevamente");
            return false;
        }
        $btnSaveCarga.submit();
    });

</script>