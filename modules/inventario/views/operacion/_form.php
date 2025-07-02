<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use app\models\sucursal\Sucursal;
use app\models\inv\InventarioOperacion;
use app\models\user\User;
?>


<div style="width: 20%;position: absolute;z-index: 1000;right: 0;top: -110px;">
    <h2 style="font-weight: bold; font-size: 28px;"> FOLIO <strong style="color: #007bff;text-decoration: underline;">#N/A</strong></h2>
</div>
<div class="produccion-lista-producto-form">
    <?php $form = ActiveForm::begin(['id' => 'form-orden-fabricacion']) ?>
    <?= Html::hiddenInput('Detail[inputProductoArray]', null, [ "id" => "inputProductoArray"]) ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>INFORMACIÓN DE AJUSTE DE INVENTARIO</h5>
                </div>
                <div class="ibox-content" >
                    <div class="row">
                        <div class="col-sm-8">
                            <?= $form->field($model, 'inventario_sucursal_id')->dropDownList(Sucursal::getAlmacenSucursal(),[ "style" => "font-size: 24px; height: 100%" ])->label(false) ?>
                        </div>
                        <div class="col-sm-4">
                            <?= $form->field($model, 'tipo')->dropDownList(InventarioOperacion::$tipoList,[ "style" => "font-size: 24px; height: 100%" ])->label(false) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <p style="font-style:16px; font-weight: bold; color: #000">ENCARGADO DE REALIZAR CONSULTAR INVENTARIO</p>
                            <?= $form->field($model, 'asignado_id')->widget(Select2::classname(),
                            [
                                'language' => 'es',
                                'data'  =>  User::getItems(),
                                'value' => isset($model->asignado_id)  && $model->asignado_id ? $model->asignado_id : null,
                                'options' => [
                                    'placeholder' => 'Selecciona la ENCARGADO...',
                                    'style' => '    font-size: 24px;height: 100%;font-weight: bold;'
                                ],

                            ])->label(false) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content_ajuste_parcial" style="display:none">

        <div class="ibox">
            <div class="ibox-content" style="padding:35px">
                <div class="row" >
                    <div class="col-sm-6 offset-sm-2">
                        <strong>PRODUCTO :</strong>
                        <?= Select2::widget([
                            'id' => 'ajusteInventario-producto_id',
                            'name' => 'ajusteInventario[producto_id]',
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
                                'placeholder' => 'Buscar producto',
                                'style' => "font-size:26px; height:100%", "step" => "0.01"
                            ],
                        ]) ?>
                    </div>
                    <div class="col-sm-2">
                        <?= Html::Button('<i class="fa fa-plus"></i> Agregar', ['class' => 'btn btn-primary btn-lg btn-block dim btn-xs-dim','id' => 'btnAddProducto', 'style' => "padding:15px"]) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="widget style1 navy-bg">
                    <div class="row">
                        <div class="col-4">
                            <i class="fa fa-cubes fa-5x"></i>
                        </div>
                        <div class="col-8 text-right">
                            <span> Cantidad [PRODUCTOS A SOLICITAR] </span>
                            <h2 class="font-bold lbl_cantidad_producto">0</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="col-sm-12">
                        <div   style="height:550px; overflow-y: auto;" >
                            <table class="table" >
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Producto</th>
                                    <th class="text-center">Accion</th>
                                </tr>
                                </thead>
                                <tbody class="container-list">

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>




    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Crear SOLICITUD DE AJUSTE DE INVENTARIO' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'id' => 'btnSaveSolicitud' ]) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<script>
    var $inputTipo                  = $('#inventariooperacion-tipo'),
        $contentAjusteParcial       = $('.content_ajuste_parcial'),
        $btnAddProducto             = $('#btnAddProducto'),
        $inputDetailProducto        = $('#ajusteInventario-producto_id'),
        $containerList              = $('.container-list'),
        containerListArray          = [];
        $lblCantidadProducto        = $('.lbl_cantidad_producto'),
        $inputProductoArray         = $('#inputProductoArray'),
        /*
        $inputDetailCantidad        = $('#inputDetailCantidad'),
        $inputCotizacionId          = $('#ajusteInventario-cotizacion_id'),

        $lblCantidadOrden           = $('.lbl_cantidad_orden'),



        $btnSaveSolicitud       = $('#btnSaveSolicitud'),

        $btnLoadCotizaciones      = $('#btnLoadCotizaciones'),



        $containerOrdenes            = $('.container-orden'),
        $containerCotizacion         = $('.container-cotizacion'),

        $lblCotizacion               = $('.lbl_cotizacion'),


        cotizacionesArray           = [],
        //cotizacionSelectArray       = [],*/
        VAR_TIPO_PARCIAL            = <?= InventarioOperacion::TIPO_AJUSTE_PARCIAL ?>,
        VAR_SOLICITUD_ID            = '<?= $model->id ?>';




$(function(){
    $('body').addClass('mini-navbar');
    $inputTipo.trigger('change');
    load_init();
});

/**DISBALED AUTOENTER*/

$(document).on("keydown", "input", function(e) {
  if (e.which==13) e.preventDefault();
});

/*****************************************/
//          SOLICITUD - SCRIPT
/*****************************************/

$inputTipo.change(function(){
    $contentAjusteParcial.hide();
    if (parseInt($inputTipo.val()) == VAR_TIPO_PARCIAL)
        $contentAjusteParcial.show();
});


$btnAddProducto.click(function(){
    if ($inputDetailProducto.val()) {
        add_itemContainer();
        $inputDetailProducto.focus();
    }else{
        alertInfo("Verifica tu información, intenta nuevamente !","warning");
    }
});

var add_itemContainer = function(){

    if (function_valid_producto($inputDetailProducto.val())) {
        itemElement = {
            "item_id"                       : containerListArray.length + 1,
            "producto_text"                 : $('#ajusteInventario-producto_id option:selected').text(),
            "producto_id"                   : $inputDetailProducto.val(),
            "update"                        : VAR_SOLICITUD_ID ? 10 : 1,
            "status"                        : 10,
            "origen"                        : 1
        }

        containerListArray.push(itemElement);
        $inputDetailProducto.html(null);
        render_container_producto();
    }else
        alertInfo("Este producto ya se ingreso para su consulta de inventario, intenta con otro producto !","warning");
}

var render_container_producto = function()
{
    $containerList.html("");
    item_count       = 0;
    countProducto    = 0;
    $.each(containerListArray, function(key, element){
        if (element.item_id) {
            if (element.status == 10 ) {
                item_count++;
                template_producto = '<tr class="text-center">'+
                    '<td>'+ item_count +'</td>'+

                    '<td><p style="font-size: 24px;font-weight: bold;">'+ element.producto_text +'</p></td>'+
                    '<td><i class="fa fa-trash btn btn-danger" onclick="refreshItem('+ element.item_id +', '+ element.origen +')"></i></td>'+
                '</tr>';
                countProducto = countProducto + 1;
                $containerList.append(template_producto);

            }
        }
    });
    $lblCantidadProducto.html(countProducto);
    $inputProductoArray.val(JSON.stringify(containerListArray));
};

var function_valid_producto = function(producto_id){
    is_add = true;
    $.each(containerListArray, function(key, element){
        if (element.item_id) {
            if (parseInt(element.producto_id) == producto_id) {
                is_add = false;
            }
        }
    });

    return is_add;
}

var refreshItem = function(element_id, $origen)
{
    $.each(containerListArray, function(key, element){
        if (element) {
            if (element.item_id == element_id  && element.origen == $origen ){
                containerListArray[key].status = 1;
            }
        }
    });
    render_container_producto();
}


var load_init = function(){
    containerListArray = [];
    if (VAR_SOLICITUD_ID) {
        $.get("<?= Url::to(['operacion-detalle-ajax']) ?>",{ solicitud_id : VAR_SOLICITUD_ID },function($response){
            $.each($response, function(key, detail){
                    if (detail.id) {
                        item_operacion = {
                            "item_id"               : detail.id,
                            "producto_text"         : detail.producto,
                            "producto_id"           : detail.producto_id,
                            "update"                : VAR_SOLICITUD_ID ? 10 : 1,
                            "status"                : 10,
                            "origen"                : 2
                        };
                    }
                    containerListArray.push(item_operacion);
                });
            $inputProductoArray.val(JSON.stringify(containerListArray));
            render_container_producto();
        },'json');
    }
}
var alertInfo = function($message, $tipo = 'warning'){
    toastr.options = {
        closeButton: true,
        progressBar: true,
        showMethod: 'slideDown',
        timeOut: 5000
    };

    if ($tipo == 'warning')
        toastr.warning($message);

    if ($tipo == 'danger')
        toastr.error($message);

    if ($tipo == 'success')
        toastr.success($message);
}
</script>