<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use app\assets\BootstrapTableAsset;
use app\models\sucursal\Sucursal;
use app\models\esys\EsysListaDesplegable;
use app\models\inv\InventarioOperacion;
use app\models\inv\TraspasoOperacion;
use app\models\trans\TransProductoInventario;

BootstrapTableAsset::register($this);


/* @var $this yii\we        b\View */

$this->title = 'INCIDENCIAS';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['operacion-entrada-incidencia-json-btt']);
?>

<div class="ibox">
    <div class="ibox-content">
        <div class="inventario-entrada-incidencia-index">
            <div class="btt-toolbar">
                <div class="panel mar-btm-5px">
                    <div class="panel-body pad-btm-15px">                
                        <strong class="pad-rgt">Filtrar:</strong>
                        <?= Html::dropDownList('status', null, TraspasoOperacion::$statusList, [ 'class' => '']) ?>
                    </div>
                </div>
            </div>
            <table class="bootstrap-table"></table>
        </div>
    </div>
</div>

<div class="fade modal inmodal " id="modal-show-operacion"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> OPERACION </h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">

                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-sm-3">
                                <?= Html::img('@web/img/logo.png', ["height"=>"80px"]) ?>
                            </div>
                            <div class="col-sm-8">
                                <h2 class="text_load lbl_tipo">ENTRADA </h2><strong style="font-weight: bold;font-size: 24px;" class="text_load text-info lbl_motivo">[ NUEVA MERCANCIA ]</strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 text-center"><strong>FOLIO DE OPERACION </strong><p  style="font-size:24px" class="text_load text-info   lbl_folio"></p></div>
                            <div class="col-sm-4 text-center"><strong>RESPONSABLE        </strong><p  style="font-size:24px" class="text_load text-info  lbl_responsable"></p></div>
                            <div class="col-sm-4 text-center"><strong>FECHA DE OPERACION </strong><p  style="font-size:24px" class="text_load text-info  lbl_fecha_operacion"></p></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 text-center">
                                <h2 class="text_load text-success lbl_origen"></h2>
                                <strong>CANTIDAD EN OPERACION</strong>
                            </div>
                            <div class="col-sm-4 text-center" style="font-size: 48px;"><i class="fa fa-cubes"></i> => </div>
                            <div class="col-sm-4 text-center">
                                <h2 class="text_load text-success lbl_destino"></h2>
                                <strong>CANTIDAD DE INGRESO</strong>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <?= Html::button("AJUSTAR OPERACION", ["class" => "btn btn-primary btn-block", "id" => "btnAjustarOperacion" ]) ?>
                            </div>
                            <div class="col-sm-6">
                            <?= Html::button("OMITIR Y CERRAR", ["class" => "btn btn-info btn-block", "id" => "btnOmitirOperacion"]) ?>
                            </div>
                        </div>


                        <div class="content-operacion-ajuste" style="display:none">
                            <div class="row" style="padding:5%">
                                <div class="col-sm-4">
                                    <?= Html::label('SUCURSAL: ','lbl_sucursal', ["style" => "font-size:12px; color:#000; font-weight:500"] ) ?>
                                    <?= Html::dropDownList('lbl_sucursal', null, Sucursal::getAlmacenSucursal(), ["class" => "form-control", "style" => "font-size:16px; height: 60%", "id" => "inputSucursal"])  ?>
                                </div>
                                <div class="col-sm-4">
                                    <?= Html::label('OPERACION: ','lbl_operacion',["style" => "font-size:12px; color:#000; font-weight:500"]) ?>
                                    <?= Html::dropDownList('lbl_operacion', null, TransProductoInventario::$tipoList, ["class" => "form-control", "style" => "font-size:16px; height: 60%", "id" => "inputTipo"])  ?>  
                                </div>
                                <div class="col-sm-4">
                                    <?= Html::label("CANTIDAD","lbl_folio_venta",["style" => "font-size:12px; color:#000; font-weight:500"]) ?>
                                    <?= Html::input("number",null,false,[ "class" => "form-control text-center", "style" => "font-size:16px;", "id" => "inputCantidad",  'step' => '0.001']) ?>                          
                                </div>
                            </div>    
                            <div class="col-sm-6 offset-sm-6">
                                <?= Html::button("GUARDAR Y CERRAR OPERACION", ["class" => "btn btn-success btn-block btn-lg", "id" => "btnGuardarOperacion" ]) ?>
                            </div>
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
    var VAR_PATH_URL        = $('body').data('url-root');
        OPERACION_ID        = null;
    $(document).ready(function(){
        var actions = function(value, row) { return [
                '<a href="javascript:void(0);" title="Ver operación" onclick = "showModal('+ row.id +')" class="fa fa-sign-out"></a>',
            ].join(''); },
            columns = [
                {
                    field: 'id',
                    title: 'ID',
                    align: 'center',
                    width: '60',
                    sortable: true,
                    switchable:false,
                },
                {
                    field: 'operador',
                    title: 'OPERADOR',
                    align: 'center',
                    sortable: true,
                    formatter: btf.color.bold,
                },
                {
                    field: 'operacion_recibe_id',
                    title: 'FOLIO DE INGRESO',
                    align: 'center',
                    formatter: btf.operacion.url_link_operacion,
                    sortable: true,
                },
                {
                    field: 'producto',
                    title: 'PRODUCTO',
                    sortable: true,                    
                },
                {
                    field: 'cantidad_old',
                    title: 'CANTIDAD OPERACION',
                    align: 'right',
                    sortable: true,
                },
                {
                    field: 'cantidad_new',
                    title: 'CANTIDAD INGRESO',
                    align: 'right',
                    sortable: true,
                },
                {
                    field: 'diferencia',
                    title: 'DIFERENCIA',
                    align: 'right',
                    sortable: true,
                    formatter: btf.color.bold,
                },
                {
                    field: 'status',
                    title: 'ESTATUS',
                    align: 'center',
                    formatter: btf.inv.incidencias,
                },
                {
                    field: 'created_at',
                    title: 'REGISTRO',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.date,
                },
                {
                    field: 'action',
                    title: 'ACCIONES',
                    align: 'center',
                    switchable: false,
                    width: '100',
                    class: 'btt-icons',
                    formatter: actions,
                    tableexportDisplay:'none',
                },
            ],
            params = {
                id      : 'operacionAjuste',
                element : '.inventario-entrada-incidencia-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });

    var url_open_link= function($id){
        window.open( VAR_PATH_URL + "inventario/entradas-salidas/view?id="+ $id);
    } 

    var showModal = function(operacion_id){
        $('#modal-show-operacion').modal('show');
        $('.text_load').html(null);
        $('.container_detalle').html(null);
        $('.content-operacion-ajuste').hide();
        OPERACION_ID = operacion_id;
        $.get("<?= Url::to(['get-operacion-incidencia']) ?>", { operacion_id : operacion_id }, function( $response ){
            if ($response.code  == 202 ) {               
                
                $('.lbl_folio').html($response.operacion.folio_ingreso);
                $('.lbl_responsable').html($response.operacion.operador);
                $('.lbl_fecha_operacion').html($response.operacion.created_at);
                $('.lbl_origen').html($response.operacion.cantidad_old);
                $('.lbl_destino').html($response.operacion.cantidad_new);
                
            }
        },'json');
    }

    $("#btnOmitirOperacion").click(function(){
        if(confirm("¿ ESTAS SEGURO QUE DESEAS OMITIR Y CERRAR LA OPERACION ?")){
            if(OPERACION_ID){
                $.post("<?= Url::to(['post-operacion-omitir']) ?>", { operacion_id : OPERACION_ID }, function( $response ){
                    if ($response.code  == 202 ) {                                    
                        
                        toastr.options = {
                            closeButton: true,
                            progressBar: true,
                            showMethod: 'slideDown',
                            timeOut: 5000
                        };

                        toastr.success('SE REALIZO CORRECTAMENTE LA OPERACION');
                        
                        window.location.reload()

                    }else{
                        toastr.options = {
                            closeButton: true,
                            progressBar: true,
                            showMethod: 'slideDown',
                            timeOut: 5000
                        };

                        toastr.error('VERIFICA TU INFORMACION, INTENTA NUEVAMENTE');
                    }           
                },'json');
            }
        }
    });

    $('#btnAjustarOperacion').click(function(){
        $('.content-operacion-ajuste').show();
    });

    $("#btnGuardarOperacion").click(function(){
        if(confirm("¿ ESTAS SEGURO QUE DESEAS GUARDAR  Y CERRAR LA OPERACION ?")){
            if(OPERACION_ID && $('#inputSucursal').val() && $('#inputTipo').val() && $('#inputCantidad').val()){
                $.post("<?= Url::to(['post-operacion-guardar']) ?>", { operacion_id : OPERACION_ID, sucursal_id : $('#inputSucursal').val(), tipo : $('#inputTipo').val() ,cantidad : $('#inputCantidad').val() }, function( $response ){
                    if ($response.code  == 202 ) {                                    
                        
                        toastr.options = {
                            closeButton: true,
                            progressBar: true,
                            showMethod: 'slideDown',
                            timeOut: 5000
                        };

                        toastr.success('SE REALIZO CORRECTAMENTE LA OPERACION');
                        
                        window.location.reload()

                    }else{
                        toastr.options = {
                            closeButton: true,
                            progressBar: true,
                            showMethod: 'slideDown',
                            timeOut: 5000
                        };

                        toastr.error('VERIFICA TU INFORMACION, INTENTA NUEVAMENTE');
                    }           
                },'json');
            }
        }
    });

</script>
