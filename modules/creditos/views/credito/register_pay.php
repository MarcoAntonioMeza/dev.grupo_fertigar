<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use app\models\cobro\CobroVenta;
use app\models\sucursal\Sucursal;
use app\models\credito\Credito;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;

BootstrapTableAsset::register($this);
$this->title = 'COBRO DE CREDITO [CLIENTES]';
$this->params['breadcrumbs'][] = $this->title;
$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['get-history-operacion-cliente-global']);
//$bttUrlRuta   = Url::to(['get-saldos-ruta']);
$bttUrlView   = Url::to(['view?id=']);
?>

<div class="creditos-register-pay buscador">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h3>SELECCIONA AL CLIENTE</h3>
                </div>
                <div class="ibox-content">
                    <?= Select2::widget([
                        'id' => 'cliente-cliente_id',
                        'name' => 'cliente-cliente_id',
                        'data' => [],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 3,
                            'language'   => [
                                'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                            ],
                            'ajax' => [
                                'url'      => Url::to(['cliente-ajax']),
                                'dataType' => 'json',
                                'cache'    => true,
                                'processResults' => new JsExpression('function(data, params){  return {results: data} }'),
                            ],

                        ],
                        'options' => [
                            'placeholder' => 'Buscar cliente',
                        ],
                    ]) ?>
                </div>
            </div>

            <div class="tabs-container">
                <ul class="nav nav-tabs" role="tablist">
                    <li>
                        <a class="nav-link active" data-toggle="tab" href="#tab-pago">PAGO</a>
                    </li>
                    <li>
                        <a class="nav-link" data-toggle="tab" href="#tab-historial-pago">HISTORIAL DE ABONOS [ACTIVOS]</a>
                    </li>
                    <li>
                        <a class="nav-link" data-toggle="tab" href="#tab-historial-pago-global" onclick="funct_refreshHistorialAbono()">HISTORIAL DE ABONOS [GLOBAL]</a>
                    </li>
                   <li>
                      <a class="nav-link" data-toggle="tab" href="#tab-ajustes-x-ruta">AJUSTES DE SALDOS POR RUTA</a>
                   </li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" id="tab-pago" class="tab-pane active">
                        <div class="panel">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h1>TOTAL DE CREDITO : <strong class="lbl_total_credito">$0.00</strong></h1>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <h2 class="font-bold text-warning text-right">TOTAL A PAGAR:</h2>
                                            </div>
                                            <div class="col-sm-6">

                                                <?= Html::input("text", false, null, [ "class" => "form-control text-center", "style" => "font-size:24px; font-weight: bold; height:100%;", "disabled" => true, "id" => "inputTotalPago", "autocomplete" => "off" ]) ?>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="table_credito" >
                                    <div class="table-responsive">
                                        <table class="table table-bordered" style="overflow-y:auto;">
                                            <thead>
                                                <tr>
                                                    <td class="text-center">CREDITO [ID]</td>
                                                    <td class="text-center">VENTA</td>
                                                    <td class="text-center">DEUDA</td>
                                                    <td class="text-center">REGISTRADO POR</td>
                                                    <td class="text-center">FECHA OTORGADO</td>
                                                    <td class="text-center">FECHA A PAGAR </td>
                                                    <td class="text-center">PAGAR</td>
                                                    <td class="text-center">CANTIDAD</td>
                                                </tr>
                                            </thead>
                                            <tbody class="container_table_credito">

                                            </tbody>
                                        </table>
                                    </div>

                                    <h3>METODOS DE PAGO</h3>
                                    <div class="alert alert-danger alert_danger_credito" style="display:none">
                                        <strong class="text-message-credito"></strong>
                                    </div>
                                    <div class="alert alert-danger alert-credito-error" style="display: none"></div>
                                    <div class="alert alert-warning alert_warning_credito" style="display:none">
                                        <strong class="text-warning-credito"></strong>
                                    </div>
                                    <div style="border-style: double;padding: 2%;">
                                        <div class="row"  >
                                            <div class="col-sm-4">
                                                <?= Html::label('&nbsp;','Pago[metodo_pago]') ?>
                                                <?= Html::dropDownList('Pago[metodo_pago]', null, [
                                                    CobroVenta::COBRO_EFECTIVO      => CobroVenta::$servicioList[CobroVenta::COBRO_EFECTIVO],

                                                    CobroVenta::COBRO_TRANFERENCIA  => CobroVenta::$servicioList[CobroVenta::COBRO_TRANFERENCIA],

                                                    CobroVenta::COBRO_TARJETA_CREDITO => CobroVenta::$servicioList[CobroVenta::COBRO_TARJETA_CREDITO],

                                                    CobroVenta::COBRO_TARJETA_DEBITO => CobroVenta::$servicioList[CobroVenta::COBRO_TARJETA_DEBITO],

                                                    CobroVenta::COBRO_CHEQUE  => CobroVenta::$servicioList[CobroVenta::COBRO_CHEQUE],

                                                    CobroVenta::COBRO_DEPOSITO  => CobroVenta::$servicioList[CobroVenta::COBRO_DEPOSITO],

                                                    CobroVenta::COBRO_OTRO  => CobroVenta::$servicioListAll[CobroVenta::COBRO_OTRO],

                                                ],  ['class' => 'form-control','id'=> 'pago-metodo_pago']) ?>
                                            </div>
                                            <div class="col-sm-6">
                                                <?= Html::label('Cantidad','Pago[cantidad]') ?>
                                                <?= Html::input("number",null,false,[ "class" => "form-control", "id" => "pago-cantidad", 'placeholder' => '$ 00.00', 'autocomplete'=> 'off']) ?>
                                            </div>
                                            <div class="col-sm-2">
                                                 <button  type="button"class="btn  btn-primary" id="btnAgregarMetodoPagoCredito" style="margin-top: 15px;" >Ingresar pago</button>
                                            </div>
                                        </div>


                                        <div class="div_metodo_otro" style="padding:15px;display: none;">
                                            <strong>NOTA / DESCRIPCIÓN</strong>
                                            <?= Html::textArea("comentario",null,[ 'class' => 'form-control', 'rows' => '6', 'style' => 'border-color:#000;box-shadow: 0px 1px 6px #000;', 'id' => 'inputOtroComentario' ]) ?>
                                        </div>

                                        <table class="table table-hover table-vcenter" style="background: aliceblue;">
                                            <thead>
                                                <tr>
                                                    <th colspan="2" class="text-center">Forma de pago</th>
                                                    <th colspan="2" class="text-center">Cantidad</th>
                                                </tr>
                                            </thead>
                                            <tbody class="content_metodo_pago_credito" style="text-align: center;">

                                            </tbody>
                                        </table>

                                        <div class="row">
                                            <div class="col-sm-12 text-center" >
                                                <h2 class="lbl_pago_credito_cambio" style="font-weight: bold"><b>$00.00</b></h2>
                                                <p><strong>CAMBIO</strong></p>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col text-center">
                                                <h3>MONTO</h3>
                                                <?= Html::input("number","cantidad_credito",0,[ "class" => "form-control text-center", "style" => "font-size:24px", "id" => "inputCantidadCredito", "disabled" => true]) ?>
                                            </div>
                                            <div class="col text-center">
                                                <h3>RESIDUO</h3>
                                                <?= Html::input("text","cantidad_residuo_credito",0,[ "class" => "form-control text-center", "style" => "font-size:24px", "id" => "inputCantidadResiduoCredito", "disabled" => true]) ?>
                                            </div>
                                        </div>

                                        <div class="form-group" style="padding:15px">
                                            <button  id="btnClearPay"  class="btn btn-default" type="button">Cancelar</button>
                                            <?php if (Yii::$app->user->identity->sucursal_id): ?>

                                                <?php if (Credito::validUserAdministrativo()): ?>
                                                    <?= Html::submitButton('COBRAR', ['class' => 'btn btn-primary btn-lg col-sm-3', "style" => "font-size:20px","id" => "btnCreditoAdd"]) ?>
                                                    <div class="alert alert-info">
                                                        <strong>AVISO</strong> Tu usuario es administrativo, los abonos que realices no sera realicioandos a ningun reparto y reporte de cuentas.
                                                    </div>
                                                <?php else: ?>
                                                    <?php if (Credito::validUserRepartidoApertura()): ?>
                                                        <?= Html::submitButton('COBRAR', ['class' => 'btn btn-primary btn-lg col-sm-3', "style" => "font-size:20px","id" => "btnCreditoAdd"]) ?>
                                                    <?php else: ?>
                                                        <div class="alert alert-info">
                                                            <strong>AVISO</strong> No puedes registrar abonos, no tienes un reparto en curso.
                                                        </div>
                                                    <?php endif ?>
                                                <?php endif ?>
                                            <?php endif ?>

                                            <?= Html::submitButton('IMPRIMIR TICKET', ['class' => 'btn btn-warning btn-lg col-sm-3', "style" => "font-size:20px; display:none","id" => "btnCreditoTicketAdd"]) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="tab-historial-pago"  role="tabpanel" class="tab-pane">
                        <div class="panel">
                            <div class="panel-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="text-align: center;">CREDITO #ID</th>
                                            <th style="text-align: center;">OPERACION</th>
                                            <th style="text-align: center;">ABONO</th>
                                            <th style="text-align: center;">FECHA DE PAGO</th>
                                            <th style="text-align: center;">CAJERO / EMPLEADO</th>
                                            <th style="text-align: center;">ESTATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody class="content_search" style="text-align: center;">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="tab-historial-pago-global"  role="tabpanel" class="tab-pane ">



<!--                                    <tbody class="content_search_global" style="text-align: center;">-->
<!--                                    </tbody>-->

                        <div class="bootstrap-table-pago-global">
                            <table class="bootstrap-table"></table>
                        </div>
                    </div>
                    <div id="tab-ajustes-x-ruta"  role="tabpanel" class="tab-pane ">
                        <div class="cotainer-saldos">
                            <div class="ibox">
                                <div class="ibox-title">
                            <strong>FECHA [PAGAR]</strong>
                            <?= DateRangePicker::widget([
                                'name'           => 'date_range',
                                //'presetDropdown' => true,
                                'hideInput'      => true,
                                'useWithAddon'   => true,
                                'convertFormat'  => true,
                                'useWithAddon'=>true,
                                'pluginOptions'  => [
                                    'locale' => [
                                        'format'    => 'Y-m-d',
                                        'separator' => ' - ',
                                    ],
                                    'opens' => 'left',
                                    "autoApply" => true,
                                ],

                            ])
                            ?>
                            <div class="col-sm-4">
                                <?= Html::label('&nbsp;','Pago[metodo_pago]') ?>
                                <?= Html::dropDownList('pago-metodo_pago', null, [
                                    CobroVenta::COBRO_EFECTIVO      => CobroVenta::$servicioTpvList[CobroVenta::COBRO_EFECTIVO],

                                    CobroVenta::COBRO_CHEQUE  => CobroVenta::$servicioTpvList[CobroVenta::COBRO_CHEQUE],

                                    CobroVenta::COBRO_TRANFERENCIA => CobroVenta::$servicioTpvList[CobroVenta::COBRO_TRANFERENCIA],

                                    CobroVenta::COBRO_TARJETA_CREDITO => CobroVenta::$servicioTpvList[CobroVenta::COBRO_TARJETA_CREDITO],

                                    CobroVenta::COBRO_TARJETA_DEBITO  => CobroVenta::$servicioTpvList[CobroVenta::COBRO_TARJETA_DEBITO],

                                    CobroVenta::COBRO_DEPOSITO  => CobroVenta::$servicioTpvList[CobroVenta::COBRO_DEPOSITO],

                                    CobroVenta::COBRO_CREDITO  => CobroVenta::$servicioTpvList[CobroVenta::COBRO_CREDITO],

                                    CobroVenta::COBRO_OTRO  => CobroVenta::$servicioTpvList[CobroVenta::COBRO_OTRO],

                                ],  ['class' => 'form-control','id'=> 'pago-metodo_pago']) ?>
                            </div>
                                </div>
                            </div>
                            <table class="bootstrap-table-saldos"></table>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

<div class="display-none">
    <table>
        <tbody class="template_metodo_pago_credito">
            <tr id = "metodo_id_{{metodo_id_credito_}}">
                <td colspan="2"><?= Html::tag('p', "0",["class" => "text-main text-semibold" , "id"  => "table_credito_metodo_id"]) ?></td>
                <td colspan="2"><?= Html::tag('p', "",["class" => "text-main " , "id"  => "table_credito_metodo_cantidad","style" => "text-align:center"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="fade modal inmodal " id="modal-detail-pago"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">DETALLE DE PAGO</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="ibox">
                    <div class="ibox-content">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">CREDITO</th>
                                    <th style="text-align: center;">MONTO</th>
                                    <th style="text-align: center;">FECHA</th>
                                    <th style="text-align: center;">EMPLEADO</th>
                                    <th style="text-align: center;">MODIFICADO</th>
                                    <th style="text-align: center;">MODIFICADO POR</th>
                                    <th style="text-align: center;">ESTATUS</th>
                                </tr>
                            </thead>
                            <tbody class="content_pago" style="text-align: center;">
                            </tbody>
                        </table>
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

<script>



    var columns = [
            {
                field: 'opera_token_pay',
                title: 'OPERACION',
                sortable: true,
                formatter: btf.historial_bonos.trans_token_pay,

            },
            {
                field: 'cantidad',
                title: 'ABONO',
                align : 'right',
                switchable: false,
                sortable: true,
                formatter: btf.conta.money_underline,
            },
            {
                field: 'fecha',
                title: 'FECHA DE PAGO',
                switchable: false,
                sortable: true,
                align: 'center',
                formatter: btf.time.date,
            },
            {
                field: 'registrado_por',
                title: 'CAJERO / EMPLEADO',
                switchable: false,
                sortable: true,
            }
        ],
        params = {
            id      : 'creditos',
            element : '.creditos-register-pay',
            url     : '<?= $bttUrl ?>',
            bootstrapTable : {
                columns : columns,
                exportOptions : {"fileName":"<?= $bttExport ?>"},
                pageList    : [ 30, 50, 100, 500, 1000, 10000],
                pageSize    : 30,
                //onDblClickRow : function(row, $element){
                //    window.location.href = '<?//= $bttUrlView ?>//' + row.id;
                //},
            }
        };




    //bttBuilder = new MyBttBuilder(params);
    //bttBuilder.refresh();

     /*   var columns = [
                {
                    field: 'cantidad',
                    title: 'ABONO',
                    switchable: false,
                    sortable: true,
                    formatter: btf.abonos_saldos.money,
                },
                {
                    field: 'created_at',
                    title: 'FECHA DE CREACIÓN',
                    switchable: false,
                    sortable: true,
                    formatter: btf.abonos_saldos.datetime,
                },
                {
                    field: 'nombre',
                    title: 'Cliente',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'metodo_pago',
                    title: 'Método de pago',
                    switchable: false,
                    sortable: true,
                    formatter: btf.abonos_saldos.metodo_pago,
                }
            ],
            params = {
                id      : 'creditos',
                element : '.creditos-register-pay',
                url     : '',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                    //onDblClickRow : function(row, $element){
                    //    window.location.href = '<?//= $bttUrlView ?>//' + row.id;
                    //},
                }
            };

        bttBuilder = new MyBttBuilder2(params);
        bttBuilder.refresh();*/





    var $inputSearchCliente = $("#cliente-cliente_id"),
    $content_metodo_pago_credito        = $(".content_metodo_pago_credito"),
    $template_metodo_pago_credito       = $('.template_metodo_pago_credito'),
    $btnAgregarMetodoPagoCredito        = $('#btnAgregarMetodoPagoCredito'),
    $inputOtroComentario                = $('#inputOtroComentario'),
    $inputTotalPago                     = $('#inputTotalPago'),
    //$inputProducto                      = $('#producto-select_id'),
    //$inputCantidadRecibe                = $('#input-cantidad-recibe'),
    $form_metodoPagoCredito = {
        $metodoPago : $('#pago-metodo_pago'),
        $cantidad   : $('#pago-cantidad'),
    },
    $btnCreditoAdd       = $("#btnCreditoAdd"),
    VAR_CREDITO             = '<?= CobroVenta::COBRO_CREDITO ?>';
    VAR_TARJETA_CREDITO     = '<?= CobroVenta::COBRO_TARJETA_CREDITO ?>';
    VAR_DEBITO_CREDITO      = '<?= CobroVenta::COBRO_TARJETA_DEBITO ?>';
    VAR_COBRO_OTRO          = '<?= CobroVenta::COBRO_OTRO ?>';
    $contentHtml            = $('.content_search'),
    $contentHtmlGlobal      = $('.content_search_global'),
    creditoArray            = [],
    notaCreditoArray        = [],
    $VAR_TOTAL_CREDITO      = 0,
    VAR_INI_CREDITO         = null;
    URL_PATH                = "<?= Url::to(['/']) ?>";
    $contentPago            = $('.content_pago');
    APPLY_CARGA             = true;


    $(function(){
        $inputTotalPago.mask('000,000,000,000,000.00', {reverse: true});
        $btnAgregarMetodoPagoCredito.hide();
    });

    var funct_refreshHistorialAbono = function(){



            if (APPLY_CARGA && $inputSearchCliente.val() ) {

                //$('.bootstrap-table-pago-global > .bootstrap-table').html(null);

                bttBuilder  = new MyBttBuilder(params);
                APPLY_CARGA = false;
                console.log("ENTRO AQUI-....");
            }

            if ($inputSearchCliente.val())
                bttBuilder.refresh();

        }

    $inputSearchCliente.change(function(){
        $('.tabs-container a[href="#tab-pago"]').tab('show');
        $('.bootstrap-table-pago-global').html(null);
        $('.bootstrap-table-pago-global').html('<table class="bootstrap-table"></table>');
        APPLY_CARGA = true;
        $btnCreditoAdd.attr('disabled',false);
        $contentHtml.html(null);
        $contentHtmlGlobal.html(null);
        $('.container_table_credito').html(null);
        $('#inputCantidadCredito').val(null);
        $('.lbl_total_credito').html(false);
        $('.lbl_pago_credito_cambio').html(false);
        $('.table_credito').hide();
        $inputTotalPago.val(null);
        $inputTotalPago.attr("disabled",true);
        creditoArray            = [];
        notaCreditoArray        = [];
        metodoPago_arrayCredito = [];
        $btnAgregarMetodoPagoCredito.hide();
        render_metodo_template_credito();


        if ($(this).val()) {
            show_loader();
            APPLY_REFRESH = true;
            $inputTotalPago.attr("disabled", false);
            $('.table_credito').show();
            $.get("<?= Url::to(['get-credito-cliente']) ?>",{ cliente_id : $(this).val() },function($response){
                if ($response.code == 202) {
                    $.each($response.credito, function(key, item_nota){
                        notaCreditoArray.push(item_nota);
                        notaCreditoArray[key].total_pago = 0;
                    })
                    render_notas_credito();

                    onGetOperacion($inputSearchCliente.val());

                }
                hide_loader();
            },'json');
        }
    });

    var render_notas_credito = function(){
        $VAR_TOTAL_CREDITO = 0;
        content_html = "";
        $('.container_table_credito').html(null);
        $.each(notaCreditoArray,function(key, credito){
            content_html += "<tr>"+
                "<td class='text-center'><a href='"+ URL_PATH +"/creditos/credito/view?id=" + credito.id +"' target='_black' style='font-size:24px; font-weight: bold' >"+ credito.id +"</a></td>"+
                "<td class='text-center'><a href='"+ '<?= Url::to(['/tpv/venta/view']) ?>' +"?id="+ credito.venta_id  +"' target='_blank' style='font-size:24px; font-weight: bold'>#"+ credito.venta_id +"</a></td>"+
                "<td class='text-center'>"+ btf.conta.money(credito.monto) +"</td>"+
                "<td class='text-center'><strong>"+  credito.created_by_user +"</strong></td>"+
                "<td class='text-center'>"+ btf.time.datetime(credito.created_at) +"</td>"+
                "<td class='text-center'>"+ btf.time.datetime(credito.fecha_credito) +"</td>";

                if (credito.is_check == 10) {
                    content_html += "<td class='text-center'><input type='checkbox' style='transform: scale(2.0);'  onchange = 'cobro_credito_function(this,"+ credito.id +")' checked></td>";
                }else{
                    content_html += "<td class='text-center'><input type='checkbox'  style='transform: scale(2.0);'  onchange = 'cobro_credito_function(this,"+ credito.id +")'></td>";
                }


                content_html += "<td class='text-center'><input type='number' class='form-control text-center' value='"+ credito.total_pago +"'  disabled/></td"+
            "</tr>";

            $VAR_TOTAL_CREDITO = $VAR_TOTAL_CREDITO + parseFloat(parseFloat(credito.monto).toFixed(2));
        });

        $('.lbl_total_credito').html(btf.conta.money($VAR_TOTAL_CREDITO));
        $('.container_table_credito').html(content_html);

    }


    var onOperacionPago = function( $opera_token_pay ){
        $('#modal-detail-pago').modal('show');
        $contentPago.html(null);
        $.get('<?= Url::to(["get-history-pago"]) ?>',{ opera_token_pay : $opera_token_pay },function($response){
            if ($response.code == 202 ) {
                $tempHtml = "";
                $.each($response.transaccion,function(key, item_transaccion){
                    $tempHtml += "<tr>"+
                        '<td><a href="#" onclick = open_ticket("'+ item_transaccion.token_pay +'") >#'+item_transaccion.credito_id +'</a></td>'+
                        "<td><p style='font-size:14px;font-weight:bold;' class='text-warning'>"+ btf.conta.money(item_transaccion.cantidad) +"</p></td>"+
                        "<td>"+item_transaccion.created_at +"</td>"+
                        "<td>"+item_transaccion.empleado   +"</td>"+
                        "<td>"+item_transaccion.updated_at +"</td>"+
                        "<td>"+item_transaccion.modificado   +"</td>"+
                        "<td><strong class='"+ (item_transaccion.status == 10 ? 'text-primary' : 'text-danger' ) +"'>"+ item_transaccion.status_text + "</strong></td>";
                    $tempHtml += "</tr>";
                });
                $contentPago.html($tempHtml);
            }
        },'json');
    };

    $inputTotalPago.change(function(){
        totalPago = $inputTotalPago.val().replaceAll(',','');

        $btnAgregarMetodoPagoCredito.hide();

        if (parseFloat(totalPago) > 0)
            $btnAgregarMetodoPagoCredito.show();

        if ($VAR_TOTAL_CREDITO >= parseFloat(totalPago)){
            function_nota_pago(totalPago);
        }else{
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.error('El TOTAL A PAGAR no debe ser mayor al TOTAL DEL CREDITO');

            $inputTotalPago.val(0);
            function_nota_pago(0);
        }

        metodoPago_arrayCredito = [];
        render_metodo_template_credito();

    });

    $('#btnClearPay').click(function(){
        $('.container_table_credito').html(null);
        $('.lbl_total_credito').html("$0.00");
        $('#inputCantidadCredito').val(null);
        $inputTotalPago.val(null);
        $('.table_credito').hide();
        creditoArray            = [];
        metodoPago_arrayCredito = [];
        $inputSearchCliente.html(null);
        render_metodo_template_credito()
    });


    var function_nota_pago = function(totalPago)
    {
        creditoArray    = [];
        pago_all        = parseFloat(totalPago);

        $.each(notaCreditoArray,function(key, credito){
            pago_register   = (  credito.monto - pago_all) > 0 ?   pago_all  : credito.monto;
            pago_all        = pago_all - pago_register;
            notaCreditoArray[key].total_pago = parseFloat(pago_register).toFixed(2);

            is_add      = true;
            $.each(creditoArray,function(key, item){
                if (item) {
                    if (item.credito_id == credito.id){
                        is_add      = false;
                        item.monto  = notaCreditoArray[key].total_pago;
                    }
                }
            });
            if (is_add) {
                creditoArray.push({
                    "credito_id" : credito.id,
                    "monto" : notaCreditoArray[key].total_pago,
                });
            }

        });
        render_notas_credito();
        render_total_credito();
    }


    var onGetOperacion = function( $cliente_id ){
        $contentHtml.html(null);


        $.get('<?= Url::to(["get-history-operacion-cliente"]) ?>',{ cliente_id : $cliente_id },function($response){
            if ($response.code == 202 ) {
                $tempHtml = "";
                $.each($response.transaccion,function(key, item_transaccion){
                    $tempHtml += "<tr>"+
                        "<td><a href='"+ URL_PATH +"/creditos/credito/view?id="+item_transaccion.credito_id+"' target='_black' >#"+ item_transaccion.credito_id +"</a></td>"+
                        '<td><a href="javascript:void(0)" onclick = onOperacionPago("'+ item_transaccion.token_pay +'") >'+item_transaccion.token_pay +'</a></td>'+
                        "<td>"+ btf.conta.money(item_transaccion.cantidad) +"</td>"+
                        "<td>"+item_transaccion.created_at +"</td>"+
                        "<td>"+item_transaccion.empleado   +"</td>"+
                        "<td><strong class='"+ (item_transaccion.status == 10 ? 'text-primary' : 'text-danger' ) +"'>"+ item_transaccion.status_text + "</strong></td>"
                    +"</tr>";
                });
                $contentHtml.html($tempHtml);
            }
        },'json');



        //$.get('<?//= Url::to(["get-history-operacion-cliente-global"]) ?>//',{ cliente_id : $cliente_id },function($response){
        //    console.log($response);
        //    if ($response.code == 202 ) {
        //        $tempHtmlGlobal = "";
        //        $.each($response.transaccion,function(key, item_transaccion){
        //            $tempHtmlGlobal += "<tr>"+
        //                '<td><a href="#" onclick = open_ticket("'+ item_transaccion.trans_token_pay +'") >'+item_transaccion.trans_token_pay +'</a></td>'+
        //                "<td style='font-size: 20px;font-weight: 700;color: #af943e;'>"+ btf.conta.money(item_transaccion.cantidad) +"</td>"+
        //                "<td>"+item_transaccion.fecha +"</td>"+
        //                "<td>"+item_transaccion.registrado_por +"</td>"+
        //            +"</tr>";
        //        });
        //        $contentHtmlGlobal.html($tempHtmlGlobal);
        //    }
        //},'json');
     }

    var render_metodo_template_credito = function(){
        $content_metodo_pago_credito.html("");
        $('.alert_forma_pago').hide();
        $('.alert_forma_pago').html("");
        pago_total = 0;


        $.each(metodoPago_arrayCredito, function(key, metodo){
            if (metodo.metodo_id) {

                metodo.metodo_id = key + 1;

                template_metodo_pago_credito = $template_metodo_pago_credito.html();
                template_metodo_pago_credito = template_metodo_pago_credito.replace("{{metodo_id_credito_}}",metodo.metodo_id);

                $content_metodo_pago_credito.append(template_metodo_pago_credito);

                $tr        =  $("#metodo_id_" + metodo.metodo_id, $content_metodo_pago_credito);

                $("#table_credito_metodo_id",$tr).html(metodo.metodo_pago_text);

                if (metodo.metodo_pago_id == VAR_DEBITO_CREDITO || metodo.metodo_pago_id == VAR_TARJETA_CREDITO)
                    $("#table_credito_metodo_cantidad",$tr).html( btf.conta.money( metodo.cantidad) +" + [ CARGO EXTRA - "+ btf.conta.money(metodo.cargo_extra) + "]");
                else if (metodo.metodo_pago_id == VAR_COBRO_OTRO )
                    $("#table_credito_metodo_cantidad",$tr).html( btf.conta.money( metodo.cantidad) +" + [ NOTA - "+ metodo.nota_otro +"]");
                else
                    $("#table_credito_metodo_cantidad",$tr).html( btf.conta.money(metodo.cantidad) );


                pago_total = pago_total + parseFloat(metodo.cantidad);

            }
        });

        //$('#total_metodo').html( btf.conta.money($('#venta-total').val()) );

        //balance_total = parseFloat( $('#venta-total').val() - pago_total.toFixed(2));

        $('#inputCantidadResiduoCredito').val( btf.conta.money(parseFloat($('#inputCantidadCredito').val() - pago_total )));


        $form_metodoPagoCredito.$metodoPago.val(null).change();

        $form_metodoPagoCredito.$cantidad.val(null);

        getCantidadMetodoPago();

    }

    var getCantidadMetodoPago = function(new_abono){
        sumTotalIngresado = 0;
        $.each(metodoPago_arrayCredito, function(key, metodo){
            if (metodo.metodo_id) {
                sumTotalIngresado = sumTotalIngresado + parseFloat(metodo.cantidad);
            }
        });

        totalDisponible = parseFloat(parseFloat(parseFloat($('#inputCantidadCredito').val() ? $('#inputCantidadCredito').val() : 0 ) -  parseFloat(sumTotalIngresado)).toFixed(2)) ;

        $('.lbl_pago_credito_cambio').html(btf.conta.money( ( new_abono > totalDisponible ? new_abono - totalDisponible : 0 ) ));

        return  (totalDisponible - new_abono) > 0 ? new_abono : totalDisponible ;
    }

    var cobro_credito_function = function (elem,credito_id){

        if ($(elem).is(':checked')) {
            VAR_INI_CREDITO = credito_id;
            $.each(notaCreditoArray, function(key, item_credito){
                if (item_credito.id == credito_id) {
                    notaCreditoArray[key].is_check = 10;
                }else{
                    notaCreditoArray[key].is_check = 20;
                }
            });
        }else{
            $.each(notaCreditoArray, function(key, item_credito){
                notaCreditoArray[key].is_check = 20;
            });

            VAR_INI_CREDITO = null;
        }
        $('.container_table_credito').html("<div class='spiner-example'><div class='sk-spinner sk-spinner-double-bounce'><div class='sk-double-bounce1'></div><div class='sk-double-bounce2'></div></div></div>");

        setTimeout(refresh_dispercion_monto, 1000);


    }

    var refresh_dispercion_monto = function(){

        temNotaCreditoArray = [];
        if (VAR_INI_CREDITO) {
            $.each(notaCreditoArray, function(key,item_credito){
                if (item_credito.id == VAR_INI_CREDITO) {
                    temNotaCreditoArray.push(item_credito);
                }
            });

            $.each(notaCreditoArray, function(key,item_credito){
                if (item_credito.id != VAR_INI_CREDITO) {
                    temNotaCreditoArray.push(item_credito);
                }
            });
        }else{

            while(temNotaCreditoArray.length  < notaCreditoArray.length ){
                $.each(notaCreditoArray, function(key,item_credito){

                    is_sin_anex = true;
                    $.each(temNotaCreditoArray, function(key_anex,item_anex){
                        if (item_anex.id == item_credito.id) {
                            is_sin_anex = false;
                        }
                    });

                    if (is_sin_anex) {

                        is_add = true;
                        $.each(notaCreditoArray, function(key_search,item_search){
                            is_search = true;
                            $.each(temNotaCreditoArray, function(key_anex,item_anex){
                                if (item_anex.id == item_search.id) {
                                    is_search = false;
                                }
                            });

                            if (item_credito.created_at > item_search.created_at && item_search.id != item_credito.id && is_search) {
                                is_add = false;
                            }
                        });

                        if (is_add) {
                            temNotaCreditoArray.push(item_credito);
                        }
                    }
                });
            }
        }

        notaCreditoArray = temNotaCreditoArray;

        totalPago = $inputTotalPago.val().replaceAll(',','');

        if (parseFloat(totalPago) > 0 )
            function_nota_pago(parseFloat(totalPago));
        else
            function_nota_pago(0);
    }

    var render_total_credito = function(){
        total = 0;
        $.each(creditoArray,function(key,credito){
            total = total + parseFloat(credito.monto);
        });
        $('#inputCantidadCredito').val(parseFloat(total).toFixed(2));
        render_metodo_template_credito();
    }

    $btnAgregarMetodoPagoCredito.click(function(){

        if(!$form_metodoPagoCredito.$metodoPago.val() || !$form_metodoPagoCredito.$cantidad.val()){
            return false;
        }
        $('.alert_danger_credito').hide();
        $('.text-message-credito').html(null);


        pago_total = 0;
        $.each(metodoPago_arrayCredito, function(key, metodo){
            if (metodo.metodo_id) {
                pago_total = pago_total + parseFloat(metodo.cantidad);
            }
        });

        pago_total = pago_total + parseFloat($form_metodoPagoCredito.$cantidad.val());



        //if ( parseFloat($('#inputCantidadCredito').val()) >= pago_total   ) {

            if ($form_metodoPagoCredito.$metodoPago.val() == VAR_COBRO_OTRO && !$inputOtroComentario.val()) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                toastr.error('Verifica tu información, Nota / Comentario son requeridos.');
                return false;
            }
            metodo = {
                "metodo_id"         : metodoPago_arrayCredito.length + 1,
                "metodo_pago_id"    : $form_metodoPagoCredito.$metodoPago.val(),
                "metodo_pago_text"  : $('option:selected', $form_metodoPagoCredito.$metodoPago).text(),
                "cantidad"          : getCantidadMetodoPago($form_metodoPagoCredito.$cantidad.val()),
                "cargo_extra"       : $form_metodoPagoCredito.$metodoPago.val() == VAR_TARJETA_CREDITO || $form_metodoPagoCredito.$metodoPago.val() == VAR_DEBITO_CREDITO  ? (2 * parseFloat(getCantidadMetodoPago($form_metodoPagoCredito.$cantidad.val()))) / 100 : 0,
                //"producto_id"       :  $form_metodoPagoCredito.$metodoPago.val() == VAR_COBRO_OTRO  ? $inputProducto.val() : null,
                //"producto_text"     :  $form_metodoPagoCredito.$metodoPago.val() == VAR_COBRO_OTRO  ? $('option:selected', $inputProducto).text() : null,
                "nota_otro"         :  $inputOtroComentario.val(),
                "origen"            : 1,
            };

            metodoPago_arrayCredito.push(metodo);



        /*}else{
            $('.alert_danger_credito').show();
            $('.text-message-credito').html('El monto ingresado debe ser menor, intente nuevamente');
        }*/


        $form_metodoPagoCredito.$cantidad.val(null);
        $inputOtroComentario.val(null);
        //$inputCantidadRecibe.val(null);
        //calcula_cambio_envio();
        render_metodo_template_credito();
    });


    $form_metodoPagoCredito.$metodoPago.change(function(){
        $('.alert_warning_credito').hide();
        $('.div_metodo_otro').hide();
        $('.text-warning-credito').html(null);
        if ($form_metodoPagoCredito.$metodoPago.val() == VAR_TARJETA_CREDITO || $form_metodoPagoCredito.$metodoPago.val() == VAR_DEBITO_CREDITO) {
            $('.alert_warning_credito').show();
            $('.text-warning-credito').html('SE AGREGA UN CARGO EXTRA DEL 2% DEL PAGO A REALIZAR');
        }
        if ($form_metodoPagoCredito.$metodoPago.val() == VAR_COBRO_OTRO) {
            $('.div_metodo_otro').show();
        }
    });

    var open_ticket = function(token_pay){
        window.open("<?= Url::to(['imprimir-credito']) ?>" + "?pay_items=" + token_pay
                        ,'imprimir', 'width=600,height=500');
    }

    $btnCreditoAdd.on('click',function(event){
        //event.preventDefault();
        $btnCreditoAdd.attr('disabled',true);
        show_loader();
        $('.alert-credito-error').html(null);
        $('.alert-credito-success').html(null);
        $('.alert-credito-error').hide();
        $('.alert-credito-success').hide();

        if (creditoArray.length > 0) {

            total = 0;

            total_ingresado = 0;

            $.each(creditoArray,function(key,credito){
                total = total + parseFloat(credito.monto);
            });

            total = parseFloat(parseFloat(total).toFixed(2));

            console.log(total);

            if (metodoPago_arrayCredito.length == 0) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                toastr.error('Ingresa un metodo de pago para continuar.');
                $btnCreditoAdd.attr('disabled',false);
                hide_loader();
                return false;
            }


            $.each(metodoPago_arrayCredito,function(key,credito){
                total_ingresado = total_ingresado + parseFloat(credito.cantidad);
            });

            if ( parseFloat(($('#inputCantidadCredito').val() ? $('#inputCantidadCredito').val() : 0 )) ==  0 ) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                toastr.error('La monto a pagar debe ser MAYOR A 0 !');
                $btnCreditoAdd.attr('disabled',false);

                hide_loader();
                return false;
            }

            if ( parseFloat( ( $('#inputCantidadCredito').val() ? $('#inputCantidadCredito').val() : 0) ) < total ) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                toastr.error("El monto a pagar debe ser de : $" + total + ", ingresa el monto correctamente");
                $btnCreditoAdd.attr('disabled',false);
                hide_loader();
                return false;
            }

            if (total > total_ingresado ) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                toastr.error("El residuo debe ser $0.00, ingresa los pagos correspondientes");
                $btnCreditoAdd.attr('disabled',false);
                hide_loader();
                return false;
            }

            $.post("<?= Url::to(['post-credito-create']) ?>",{ listCredito : creditoArray, total : total, metodoPagoArray : metodoPago_arrayCredito  },function($response){
                if ($response.code == 202) {
                    //$btnCreditoAdd.hide();
                    //$('#btnCreditoTicketAdd').show();
                    creditoArray = [];
                    metodoPagoArray = [];
                    $('.alert-credito-success').html("SE REALIZO CORRECTAMENTE EL COBRO");
                    $('.alert-credito-success').show();
                    hide_loader();

                    window.open("<?= Url::to(['imprimir-credito']) ?>" + "?pay_items=" + $response.credito
                    ,'imprimir', 'width=600,height=500');

                    $inputSearchCliente.val(null).trigger('change');

                }else if($response.code == 10 ){
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        showMethod: 'slideDown',
                        timeOut: 5000
                    };
                    toastr.error("Ocurrio un error al procesar el pago, intenta nuevamente");
                    $('.alert-credito-error').show();
                    $('.alert-credito-error').html("<strong>"+ $response.message +"</strong>");
                    $btnCreditoAdd.attr('disabled',false);
                    hide_loader();
                }
            });
        }else{

            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.error('Debes seleccionar un credito para generar un PAGO.');

            $('.alert-credito-error').show();
            $btnCreditoAdd.attr('disabled',false);
            hide_loader();
        }
    });


    var show_loader = function(){
        $('body').append('<div  id="page_loader" style="opacity: .8;z-index: 2040 !important;    position: fixed;top: 0;left: 0;z-index: 1040;width: 100vw;height: 100vh;background-color: #000;"><div class="spiner-example" style="position: fixed;top: 50%;left: 0;z-index: 2050 !important; width: 100%;height: 100%;"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div>');
    }

    var hide_loader = function(){
        $('#page_loader').remove();
    }
</script>