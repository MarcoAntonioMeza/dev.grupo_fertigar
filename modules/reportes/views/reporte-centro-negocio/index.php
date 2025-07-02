<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use kartik\date\DatePicker;
use app\models\esys\EsysListaDesplegable;

$this->title = 'REPORTE DE CENTRO DE NEGOCIO';
$this->params['breadcrumbs'][] = 'Reporte';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['centro-negocio-json-btt']);

?>

<div class="reporte-cliente-saldo">
    <div class="ibox">
        <div class="ibox-content">
            <div class="btt-toolbar" >
                <p class="text-warning" style="font-weight: bold;">** SELECCIONA UN PERIODO NO MÁS DE 15 DIAS **</p>
                <div class="row" style="padding:15px">
                    <div class="col-sm-6 col-md-4">
                        <p>FECHA INICIO</p>
                        <?= DatePicker::widget([
                                'name' => 'InputFechaInicio',
                                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                'options' => ['placeholder' => '--- SELECT ---', "autocomplete" => "off", "style" => "font-weight: 700; font-size:24px; color:#000", "id" => "inputFechaInicio" ],
                                'value' =>  date("Y-m-d"),
                                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                'pickerIcon' => '<i class="fa fa-calendar"></i>',
                                'removeIcon' => '<i class="fa fa-trash"></i>',
                                'language' => 'es',
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                ]
                            ]);
                         ?>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <p>FECHA FIN</p>
                         <?= DatePicker::widget([
                                'name' => 'InputFechaFin',
                                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                'options' => ['placeholder' => '--- SELECT ---', "autocomplete" => "off", "style" => "font-weight: 700; font-size:24px; color:#000", "id" => "inputFechaFin" ],
                                'value' =>  date("Y-m-d"),
                                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                'pickerIcon' => '<i class="fa fa-calendar"></i>',
                                'removeIcon' => '<i class="fa fa-trash"></i>',
                                'language' => 'es',
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                ]
                            ]);
                         ?>
                    </div>
                </div>

            </div>
            <table class="table table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>RUTA</th>
                        <th>$ RUTAS (V. PREVENTA / V. TARA ABIERTA)</th>
                        <th>VENTAS A CREDITO</th>
                        <th>VENTAS A CONTADO</th>
                        <th>ABONO A CLIENTE</th>
                        <th>DEVOLUCION</th>
                        <th>ESTATUS</th>
                    </tr>
                </thead>
                <tbody class="container-centro-negocio">

                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
var $containerCentroNegocio         = $('.container-centro-negocio'),
    $filters                        = $('.btt-toolbar :input'),
    $inputFechaInicio               = $('#inputFechaInicio')
    $inputFechaFin                  = $('#inputFechaFin')
    VAR_URL_PATH                    = "<?= Url::to(['/']) ?>";
    $VAR_CENTRO_NEGOCIO_TOP         = null;

$(function(){
    getLoadInformCentroNegocio();
})

var getLoadInformCentroNegocio = function(){
    $containerCentroNegocio.html(false);
    if ($inputFechaFin.val() && $inputFechaInicio) {
        show_loader();
        $.get("<?= Url::to(['reporte-centro-negocio-ajax']) ?>", { filters: $filters.serialize()  }, function($responseReporte){
            if ($responseReporte['code'] == 202) {
                $VAR_CENTRO_NEGOCIO_TOP = $responseReporte['reporte'];
                render_centro_negocio();
                hide_loader();
            }
        });
    }
}

var render_centro_negocio = function()
{
    containerCentroNegocioHtml = '';
    $.each($VAR_CENTRO_NEGOCIO_TOP, function(key, item_centro_negocio){
        containerCentroNegocioHtml += "<tr>";

        if (item_centro_negocio.id)
            containerCentroNegocioHtml += "<td><a href='"+VAR_URL_PATH+"logistica/ruta/view?id="+item_centro_negocio.id +"' target='_black'><p style='font-size:14px; font-weight:bold'>"+ item_centro_negocio.reparto_name +"</p></a></td>";
        else
            containerCentroNegocioHtml += "<td><p style='font-size:14px; font-weight:bold'>"+ item_centro_negocio.reparto_name +"</p></td>";

            containerCentroNegocioHtml +="<td>"+
                "<div class='row'>"+
                    "<div class = 'col-sm-6 text-center'>"+
                        "<p class='h6'>"+ btf.conta.money(item_centro_negocio.valor_preventa) +"</p>"+
                    "</div>"+
                    "<div class = 'col-sm-6 text-center'>"+
                        "<p class='h6'>"+ btf.conta.money(item_centro_negocio.valor_tara_abierta) +"</p>"+
                    "</div>"+
                "</div>"+
            "</td>"+
            "<td class='text-center'><p class='h6'>"+ btf.conta.money(item_centro_negocio.valor_credito) +"</p></td>"+
            "<td class='text-center'><p class='h6'>"+ btf.conta.money(item_centro_negocio.valor_contable) +"</p></td>"+
            "<td class='text-center'><p class='h6'>"+ btf.conta.money(item_centro_negocio.abono_cliente) +"</p></td>"+
            "<td class='text-center'><p class='h6'>"+ btf.conta.money(item_centro_negocio.devoluciones) +"</p></td>"+
            "<td class='text-center'><strong class ='text-"+item_centro_negocio.status_alert+"'>"+item_centro_negocio.status_text+"</strong></td>"+
        "</tr>";
    });

    $containerCentroNegocio.html(containerCentroNegocioHtml);
}

$filters.change(function(){
    getLoadInformCentroNegocio();
});


var show_loader = function(){
    $('body').append('<div  id="page_loader" style="opacity: .8;z-index: 2040 !important;    position: fixed;top: 0;left: 0;z-index: 1040;width: 100vw;height: 100vh;background-color: #000;"><div class="spiner-example" style="position: fixed;top: 50%;left: 0;z-index: 2050 !important; width: 100%;height: 100%;"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div>');
}

var hide_loader = function(){
    $('#page_loader').remove();
}

</script>