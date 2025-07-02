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

$this->title = 'REPORTE DE PROVEEDOR';
$this->params['breadcrumbs'][] = 'Reporte';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['proveedor-saldo-json-btt']);

?>

<div class="reporte-proveedor-saldo">
    <div class="row">
        <div class="col-sm-12">
            <div class="btt-toolbar">
            </div>
        </div>
    </div>
    <div class="pad-all" style="margin-bottom: 40px;">
        <div id="container-proveedor-saldo" style=" height: 355px; margin: 0 auto"></div>
    </div>

    <table class="bootstrap-table"></table>
</div>


<script type="text/javascript">
    var $filters            = $('.btt-toolbar :input'),
        $VAR_PROVEEDOR_ADEUDO_TOP   = null;

    $(document).ready(function(){
            var columns = [
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
                    title: 'PROVEEDOR',
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

            ],
            params = {
                id      : 'ventaClienteSaldo',
                element : '.reporte-proveedor-saldo',
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
        load_info_reporte();

    });

    $filters.change(function(){
        load_info_reporte();
    });

    var load_info_reporte = function()
    {
        $.get("<?= Url::to(['reporte-proveedor-saldo-ajax']) ?>", { filters: $filters.serialize()  }, function($responseReporte){
            if ($responseReporte['code'] == 202) {
                $VAR_PROVEEDOR_ADEUDO_TOP = $responseReporte['reporte'];
                load();
            }
        });

    }


    var load = function(){
        Highcharts.chart('container-proveedor-saldo', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'PROVEEDOR - SALDO'
            },
            subtitle: {
                text: 'PROVEEDOR CON SALDO'
            },
            xAxis: {
                categories: $VAR_PROVEEDOR_ADEUDO_TOP['proveedores'],
                crosshair: true
              },
            yAxis: {
                min: 0,
                title: {
                    text: 'Proveedores'
                }
            },
            tooltip: {
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                  '<td style="padding:0"><b>${point.y}</b></td></tr>',
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
                    name: 'PROVEEDOR',
                    data: $VAR_PROVEEDOR_ADEUDO_TOP['proveedores_adeudo'],//[49.9, 71.5, 106.4, 129.2],
                }
            ]
        });
    }

</script>
