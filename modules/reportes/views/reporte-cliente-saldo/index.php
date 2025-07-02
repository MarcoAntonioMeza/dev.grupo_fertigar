<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\HighchartsAsset;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;
use app\models\sucursal\Sucursal;
use app\models\producto\ViewProducto;

HighchartsAsset::register($this);
BootstrapTableAsset::register($this);

$this->title = 'REPORTE DE CLIENTE';
$this->params['breadcrumbs'][] = 'Reporte';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['cliente-saldo-json-btt']);
$bttUrlView   = Url::to(['/crm/cliente/view?id=']);
?>

<?= Html::a('VER GRAFICA ', null, ['class' => 'btn btn-primary btn-zoom',  'data-target' => "#modal-show-grafica", 'data-toggle' =>"modal", 'onclick' => "load_info_reporte()", "data-backdrop" => "static", "data-keyboard"=> false  ])?>

<div class="reporte-cliente-saldo">
    <div class="row">
        <div class="col-sm-12">
            <div class="btt-toolbar">
            </div>
        </div>
    </div>
    

    <table class="bootstrap-table"></table>
</div>


<div class="fade modal inmodal " id="modal-show-grafica"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> REPORTE </h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="pad-all" style="margin-bottom: 40px;">
                    <div id="container-cliente-saldo" style=" height: 355px; margin: 0 auto"></div>
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
    var $filters            = $('.btt-toolbar :input'),
        $VAR_CLENTE_ADEUDO_TOP   = null;

    $(document).ready(function(){
            var actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver cliente" class="fa fa-eye"></a>'
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
                    field: 'nombre_completo',
                    title: 'CLIENTE',
                    sortable: true,
                },

                {
                    field: 'por_pagar',
                    title: 'CREDITO [POR PAGAR]',
                    sortable: true,
                    switchable:false,
                    formatter: btf.conta.money,
                    align: 'right',
                },
                {
                    field: 'fecha_ultimo_abono',
                    title: 'FECHA [ULTIMO ABONO]',
                    align: 'center',
                    sortable: true,
                    switchable:false,
                    formatter: btf.time.date,
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
                id      : 'ventaClienteSaldo',
                element : '.reporte-cliente-saldo',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    sortName : 'por_pagar',
                    sortOrder : 'desc',
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
        

    });

    

    var load_info_reporte = function()
    {
        show_loader();
        $.get("<?= Url::to(['reporte-cliente-saldo-ajax']) ?>", { filters: $filters.serialize()  }, function($responseReporte){
            hide_loader();
            if ($responseReporte['code'] == 202) {
                $VAR_CLENTE_ADEUDO_TOP = $responseReporte['reporte'];
                load();
            }
        });

    }


    var load = function(){
        Highcharts.chart('container-cliente-saldo', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'TOP ADEUDOS'
            },
            subtitle: {
                text: 'Cliente con saldo'
            },
            xAxis: {
                categories: $VAR_CLENTE_ADEUDO_TOP['clientes'],
                crosshair: true
              },
            yAxis: {
                min: 0,
                title: {
                    text: 'Cliente'
                }
            },
            tooltip: {
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                  '<td style="padding:0"><b>{point.y:.1f}</b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: [
                {
                    name: 'CLIENTE',
                    data: $VAR_CLENTE_ADEUDO_TOP['clientes_adeudo'],//[49.9, 71.5, 106.4, 129.2],
                }
            ]
        });
    }

</script>
