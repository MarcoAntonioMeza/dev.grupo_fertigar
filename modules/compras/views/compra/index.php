<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\sucursal\Sucursal;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Compras';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['compras-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);


?>

<div class="compras-compra-index">
    <div class="btt-toolbar">
        <div class="panel">
           <div class="panel-body">
                <div>
                    <strong class="pad-rgt">Filtrar [FECHA DE PRECOMPRA]:</strong>
                    <div class="DateRangePicker   kv-drp-dropdown">
                        <?= DateRangePicker::widget([
                            'name'           => 'date_range',
                            'presetDropdown' => true,
                            'hideInput'      => true,
                            'useWithAddon'   => true,
                            'convertFormat'  => true,
                            'startAttribute' => 'from_date',
                            'endAttribute' => 'to_date',
                            'startInputOptions' => ['value' => '2019-01-01'],
                            'endInputOptions' => ['value' => '2019-12-31'],
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
                    </div>
                </div>
                <div style="padding: 5px;">
                    <strong class="pad-rgt">Filtrar:</strong>
                    <?=  Html::dropDownList('sucursal_id', null, Sucursal::getAlmacenSucursal(), ['prompt' => 'Bodega / Tienda', 'class' => 'max-width-170px'])  ?>
                </div>
            </div>
        </div>
        <?php /* ?>
        <div class="ibox mar-btm-5px">
            <div class="ibox-content pad-btm-15px">
                <div>
                    <strong class="pad-rgt">Filtrar:</strong>
                    <?=  Html::dropDownList('estado_id', null, EsysListaDesplegable::getEstados(), ['prompt' => 'Tipo estado', 'class' => 'max-width-170px'])  ?>
                </div>
            </div>
        </div>
        */?>

    </div>
    <table class="bootstrap-table"></table>
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
                                <h2 class="text_load lbl_tipo">  </h2><strong style="font-weight: bold;font-size: 24px;" class="text_load text-info lbl_motivo"> </strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 text-center"><strong>FOLIO DE OPERACION </strong><p><a class="text_load text-info  lbl_folio text-link"  target="_blank" style="font-size:24px;text-decoration: underline;"></a></p></div>
                            <div class="col-sm-4 text-center"><strong>RESPONSABLE        </strong><p class="text_load text-info  lbl_responsable"></p></div>
                            <div class="col-sm-4 text-center"><strong>FECHA DE OPERACION </strong><p class="text_load text-info  lbl_fecha_operacion"></p></div>
                        </div>
                        <div class="ibox">
                            <div class="ibox-title">
                                <h3>COMPRA</h3>
                            </div>
                            <div class="ibox-content">
                                <div class="row table-responsive" id="div_salida" >

                                </div>
                            </div>
                        </div>
                        <div class="ibox">
                            <div class="ibox-title">
                                <h3>ENTRADA</h3>
                            </div>
                            <div class="ibox-content">
                            <div class="row " id="div_entrada">

                            </div>
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

    var $reporte_download_tienda         = $('#reporte_download_tienda'),
        $reporte_download_cedis          = $('#reporte_download_cedis');

    $reporte_download_tienda.click(function(event){
        window.open('<?= Url::to(['lista-compra-tienda']) ?>',
            'imprimir',
            'width=600,height=600');
    });

    var $reporte_download_cedis         = $('#reporte_download_cedis');
    $reporte_download_cedis.click(function(event){
        window.open('<?= Url::to(['lista-compra-cedis']) ?>',
            'imprimir',
            'width=600,height=600');
    });

    $(document).ready(function(){
        var can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver compra" class="fa fa-eye"></a>',
                '<a  href="javascript:void(0);"    title="Ver operación" onclick = "load_operacion('+ row.id +')"      class="fa fa-cogs"></a>',,
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
                    field: 'proveedor',
                    title: 'Proveedor',
                    sortable: true,
                },
                {
                    field: 'sucursal',
                    title: 'Sucursal',
                    switchable: false,
                    sortable: true,
                },

                {
                    field: 'tiempo_recorrido',
                    title: 'Tiempo recorrido',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'fecha_salida',
                    title: 'Fecha salida',
                    align: 'center',
                    formatter: btf.time.date,
                    sortable: true,
                }];
                if(!can.hideMonto){
                    columns = $.merge(columns,[{
                        field: 'total',
                        title: 'Total',
                        formatter: btf.conta.money,
                        align: 'right',
                        sortable: true,
                    }]);
                }
                
                columns = $.merge(columns,[{
                    field: 'count_detalle',
                    title: 'TOTAL DE PRODUCTO [COMPRA]',
                    align: 'center',
                    sortable: true,
                    visible: false,
                    sortable: true,
                },
                {
                    field: 'count_entrada_detalle',
                    title: 'TOTAL DE PRODUCTO [ENTRADA]',
                    align: 'center',
                    sortable: true,
                    visible: false,
                    sortable: true,
                },
                {
                    field: 'diferencia',
                    title: 'DIFERENCIA DE PRODUCTOS',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.inv.compra_status,
                },
                {
                    field: 'is_especial',
                    title: '¿ COMPRA ESPECIAL ?',
                    align: 'center',
                    formatter: btf.boolean.sino,
                },
                {
                    field: 'created_at',
                    title: 'Creado',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.date,
                },
                {
                    field: 'created_by',
                    title: 'Creado por',
                    sortable: true,
                    visible: false,
                    formatter: btf.user.created_by,
                },
                {
                    field: 'updated_at',
                    title: 'Modificado',
                    align: 'center',
                    sortable: true,
                    visible: false,
                    formatter: btf.time.date,
                },
                {
                    field: 'updated_by',
                    title: 'Modificado por',
                    sortable: true,
                    visible: false,
                    formatter: btf.user.updated_by,
                },
                {
                    field: 'action',
                    title: 'Acciones',
                    align: 'center',
                    switchable: false,
                    width: '100',
                    class: 'btt-icons',
                    formatter: actions,
                    tableexportDisplay:'none',
                },
            ]);
            params = {
                id      : 'sucursal',
                element : '.compras-compra-index',
                url     : '<?= $bttUrl ?>',
                colorCompra : true,
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                    onDblClickRow : function(row, $element){
                        window.location.href = '<?= $bttUrlView ?>' + row.id;
                    },
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });

    var load_operacion = function($operacion_id)
    {
        $('#modal-show-operacion').modal('show');
        $('.text_load').html(null);
        $.get("<?= Url::to(['view-modal?id='])?>", { id : $operacion_id }, function( $response ){
            console.log($response);
            if ($response.code  == 202 ) {
                $('.lbl_folio').html('# '+$response.compras.id);
                $('.lbl_responsable').html($response.nombre);
                var fecha = new Date($response.compras.created_at);
                var timestampEnMilisegundos = fecha * 1000;
                var fecha = new Date(timestampEnMilisegundos);
                var dia = fecha.getDate();
                var mes = fecha.getMonth() + 1;
                var anio = fecha.getFullYear();
                var horas = fecha.getHours();
                var minutos = fecha.getMinutes();
                var segundos = fecha.getSeconds();
                var fechaFormateada = dia + '/' + mes + '/' + anio + ' ' + horas + ':' + minutos + ':' + segundos;
                $('.lbl_fecha_operacion').html(fechaFormateada);
                var tabla = '<table class="table table-bordered invoice-summary">';
                tabla += '<tr><th>Clave</th><th>Producto</th><th>Cantidad</th><th>Costo x U.</th><th>Total</th></tr>';

                $.each($response.entrada, function(index, dato) {
                    tabla += '<tr>';
                    tabla += '<td>' + dato.id + '</td>';
                    tabla += '<td>' + dato.producto_id + '</td>';
                    tabla += '<td>' + dato.cantidad + '</td>';
                    tabla += '<td>' + dato.costo + '</td>';
                    tabla += '<td>' + dato.total + '</td>';
                    tabla += '</tr>';
                });
                tabla += '</table>';
                $('#div_salida').html(tabla);

                var tabla2 = '<table class="table table-bordered invoice-summary">';
                tabla2 += '<tr><th>Clave</th><th>Producto</th><th>Cantidad</th><th>Costo x U.</th><th>Total</th></tr>';

                $.each($response.compradetalles, function(index, dato) {
                    tabla2 += '<tr>';
                    tabla2 += '<td>' + dato.id + '</td>';
                    tabla2 += '<td>' + dato.producto_id + '</td>';
                    tabla2 += '<td>' + dato.cantidad + '</td>';
                    tabla2 += '<td>' + dato.costo + '</td>';
                    tabla2 += '<td>' + dato.total + '</td>';
                    tabla2 += '</tr>';
                });
                tabla2 += '</table>';
                $('#div_entrada').html(tabla2);

            }

        },'json');

    }
</script>
