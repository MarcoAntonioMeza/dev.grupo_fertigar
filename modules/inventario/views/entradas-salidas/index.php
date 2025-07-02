<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;
use app\models\esys\EsysListaDesplegable;
use kartik\daterange\DateRangePicker;
use app\models\sucursal\Sucursal;
use app\models\inv\Operacion;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Entradas y Salidas';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['entradas-salidas-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlReporte = Url::to(['imprimir-reporte?id=']);

?>


<div class="ibox">
    <div class="ibox-content">
        <p>
            <?= $can['create']?
                Html::a('<i class="fa fa-plus"></i> Nueva operación', ['create'], ['class' => 'btn btn-success add']): '' ?>
        </p>
        <div class="inventario-entradas-salidas-index">
            <div class="btt-toolbar">
                <?= Html::hiddenInput('off_reembolso', true) ?>
                <div class="panel mar-btm-5px">
                    <div class="panel-body pad-btm-15px">
                        <div>
                           <div class="DateRangePicker   kv-drp-dropdown">
                                <?= DateRangePicker::widget([
                                    'name'           => 'date_range',
                                    //'presetDropdown' => true,
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
                            <br>
                            <strong class="pad-rgt">Filtrar:</strong>
                            <?= Html::dropDownList('sucursal_id', null, Sucursal::getItems(), ['prompt' => 'Sucursal / Ruta', 'class' => '']) ?>
                            <?= Html::dropDownList('tipo', null, [
                               Operacion::TIPO_ENTRADA => Operacion::$tipoList[Operacion::TIPO_ENTRADA],
                               Operacion::TIPO_SALIDA  => Operacion::$tipoList[Operacion::TIPO_SALIDA],
                            ], ['prompt' => 'Operacion', 'class' => '']) ?>
                        </div>
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
                            <div class="col-sm-4 text-center"><strong>FOLIO DE OPERACION </strong><p class="text_load text-info   lbl_folio"></p></div>
                            <div class="col-sm-4 text-center"><strong>RESPONSABLE        </strong><p class="text_load text-info  lbl_responsable"></p></div>
                            <div class="col-sm-4 text-center"><strong>FECHA DE OPERACION </strong><p class="text_load text-info  lbl_fecha_operacion"></p></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 text-center">
                                <h2 class="text_load text-success lbl_origen"></h2>
                                <strong>ORIGEN</strong>
                            </div>
                            <div class="col-sm-4 text-center" style="font-size: 48px;"><i class="fa fa-cubes"></i> => </div>
                            <div class="col-sm-4 text-center">
                                <h2 class="text_load text-success lbl_destino"></h2>
                                <strong>DESTINO</strong>
                            </div>
                        </div>
                        <strong style="color: #000">DETALLE DE LA OPERACIÓN</strong>
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="text-center">CLAVE</th>
                                            <th class="text-center">CONCEPTO</th>
                                            <th class="text-center">CANTIDAD</th>
                                            <th class="text-center">UNIDAD DE MEDIDA</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text_load container_detalle">

                                    </tbody>
                                </table>
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
    $(document).ready(function(){
        var can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver operación" class="fa fa-eye"></a>',
                '<a href="<?= $bttUrlReporte ?>' + row.id + '"  target="_blank" title="Descargar pdf" class="fa fa-file-pdf-o"></a>',
                '<a href="javascript:void(0);" title="Ver operación" onclick = "showModal('+ row.id +')" class="fa fa-cogs"></a>',
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
                    field: 'origen',
                    title: 'ORIGEN',
                    sortable: true,
                    align: 'center',
                    formatter: btf.color.bold,
                },
                {
                    field: 'movimiento',
                    title: 'MOVIMIENTO',
                    sortable: true,
                    align: 'center',
                    formatter: btf.color.bold,
                },
                {
                    field: 'destino',
                    title: 'DESTINO',
                    sortable: true,
                    align: 'center',
                    formatter: btf.color.bold,
                },

                {
                    field: 'operacion_cantidad',
                    title: 'CANTIDAD',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'tipo',
                    title: 'OPERACION',
                    align: 'center',
                    formatter: btf.inv.tipo,
                    sortable: true,
                },
                {
                    field: 'status',
                    title: 'ESTATUS',
                    align: 'center',
                    formatter: btf.inv.status,
                },
                {
                    field: 'created_at',
                    title: 'CREADO',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.date,
                },
                {
                    field: 'created_by',
                    title: 'CREADO POR',
                    sortable: true,
                    visible: false,
                    formatter: btf.user.created_by,
                },
                {
                    field: 'updated_at',
                    title: 'MODIFICADO',
                    align: 'center',
                    sortable: true,
                    visible: false,
                    formatter: btf.time.date,
                },
                {
                    field: 'updated_by',
                    title: 'MODOFICADO POR',
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
            ],
            params = {
                id      : 'inventario',
                element : '.inventario-entradas-salidas-index',
                url     : '<?= $bttUrl ?>',
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

var showModal = function(operacion_id){
    $('#modal-show-operacion').modal('show');
    $('.text_load').html(null);
        $('.container_detalle').html(null);
        $.get("<?= Url::to(['get-operacion-detail']) ?>", { operacion_id : operacion_id }, function( $response ){
            if ($response.code  == 202 ) {
                $('.lbl_tipo').html($response.operacion.tipo_text);
                $('.lbl_motivo').html($response.operacion.motivo_text);
                $('.lbl_folio').html($response.operacion.folio);
                $('.lbl_responsable').html($response.operacion.responsable);
                $('.lbl_fecha_operacion').html($response.operacion.created_at);
                $('.lbl_origen').html($response.operacion.origen);
                $('.lbl_destino').html($response.operacion.destino);

                contentHtml = "";
                $.each($response.operacion.producto_detalle, function(key, item_detail ){



                    contentHtml += "<tr>"+
                        "<td class='text-center '>"+ item_detail.clave +"</td>"+
                        "<td class='text-center '>"+ item_detail.producto +"</td>"+
                        "<td class='text-center '>"+ item_detail.cantidad +"</td>"+
                        "<td class='text-center '>"+ item_detail.producto_unidad +"</td>"+
                    +"</tr>";

                });

                $('.container_detalle').html(contentHtml);

            }

        },'json');
}

</script>
