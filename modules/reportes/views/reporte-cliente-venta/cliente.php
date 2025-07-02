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

$this->title = 'REPORTE DE VENTA POR CLIENTE';
$this->params['breadcrumbs'][] = 'Reporte';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['venta-cliente-json-btt']);

?>

<div class="reporte-venta-cliente">
    <div class="row">
        <div class="col-sm-12">
            <div class="btt-toolbar">
                <div class="panel">
                   <div class="panel-body">
                        <div>
                            <strong>Filtrar [RANGO DE FECHA]</strong>
                            <div class="DateRangePicker   kv-drp-dropdown">
                                <?= DateRangePicker::widget([
                                    'name'           => 'date_range',
                                    'presetDropdown' => true,
                                    'hideInput'      => true,
                                    'useWithAddon'   => true,
                                    'convertFormat'  => true,
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
                            <?= Html::dropDownList('sucursal_id', null, Sucursal::getItems(), [ 'prompt'=> 'Sucursal']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="pad-all" style="margin-bottom: 40px;">
        <div id="container-venta-producto" style=" height: 355px; margin: 0 auto"></div>
    </div>

    <table class="bootstrap-table"></table>
</div>


<script type="text/javascript">
    var $filters            = $('.btt-toolbar :input'),
        $VAR_PRODUCTO_TOP   = null;

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
                    field: 'nombre_completo',
                    title: 'CLIENTE',
                    sortable: true,
                },

                {
                    field: 'total_ingreso',
                    title: 'TOTAL [INGRESO]',
                    sortable: true,
                    switchable:false,
                    formatter: btf.conta.money,
                    align: 'right',
                },
                {
                    field: 'num_ventas',
                    title: 'N° VENTAS',
                    sortable: true,
                    switchable:false,
                    align: 'right',
                },

            ],
            params = {
                id      : 'ventaCliente',
                element : '.reporte-venta-cliente',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    sortName : 'num_ventas',
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
        $.get("<?= Url::to(['reporte-venta-cliente-ajax']) ?>", { filters: $filters.serialize()  }, function($responseReporte){
            if ($responseReporte['code'] == 202) {
                $VAR_PRODUCTO_TOP = $responseReporte['reporte'];
                load();
            }
        });

    }


    var load = function(){
        Highcharts.chart('container-venta-producto', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'VENTA - CLIENTE'
            },
            subtitle: {
                text: 'Cliente top 10 más compras'
            },
            xAxis: {
                categories: $VAR_PRODUCTO_TOP['clientes'],
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
                    name: 'MAS VENTAS [N° VENTAS]',
                    data: $VAR_PRODUCTO_TOP['vendido'],//[49.9, 71.5, 106.4, 129.2],
                },{
                    name: 'MAS INGRESO [PESOS]',
                    data: $VAR_PRODUCTO_TOP['pesos'],

                }
            ]
        });
    }

</script>
