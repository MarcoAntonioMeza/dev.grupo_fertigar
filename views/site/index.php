<?php
/* @var $this yii\web\View */
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;
use app\assets\HighchartsAsset;
use app\models\sucursal\Sucursal;

HighchartsAsset::register($this);

$this->title = '';
$bttUrl       = Url::to(['venta-producto-json-btt']);

?>
<?php if(!isset(Yii::$app->user->identity)): ?>

<div class="site-index">

    <div class="jumbotron">
        <h1><?= Html::encode(Yii::$app->name) ?></h1>
    </div>
</div>

<?php else: ?>

<div class="wrapper wrapper-content">
    <?php if (Yii::$app->user->can('admin')): ?>
    <div class="ibox">
        <div class="ibox-content">
            <h5>PRODUCTOS - MAS VENDIDOS</h5>
            <div class="row">
                <div class="col-sm-12">
                    <div class="btt-toolbar-producto">
                        <div class="panel">
                           <div class="panel-body">
                                <div>
                                    <div  style="display: inline-block;">
                                        <strong>Filtrar [RANGO DE FECHA - SUCURSAL]</strong>
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
                                    </div>
                                    <div  style="display: inline-block;">
                                        <?= Html::dropDownList('sucursal_id', null, Sucursal::getItems(), [ 'prompt'=> 'Sucursal',"class" => "form-control"]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="pad-all" style="margin-bottom: 40px;">
                        <div id="container-venta-producto" style=" height: 355px; margin: 0 auto"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-content">
            <h5>CLIENTES - MAS COMPRAS</h5>
            <div class="row">
                <div class="col-sm-12">
                    <div class="btt-toolbar-cliente">
                        <div class="panel">
                           <div class="panel-body">
                                <div>
                                    <div  style="display: inline-block;">
                                        <strong>Filtrar [RANGO DE FECHA - SUCURSAL]</strong>
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
                                    </div>
                                    <div  style="display: inline-block;">
                                        <?= Html::dropDownList('sucursal_id', null, Sucursal::getItems(), [ 'prompt'=> 'Sucursal', "class" => "form-control" ]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="pad-all" style="margin-bottom: 40px;">
                        <div id="container-venta-cliente" style=" height: 355px; margin: 0 auto"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <?php endif ?>
</div>


    <?php if (Yii::$app->user->can('admin')): ?>
    <script>

        var $filtersProducto    = $('.btt-toolbar-producto :input'),
            $filtersCliente     = $('.btt-toolbar-cliente :input'),
            $VAR_PRODUCTO_TOP   = null;
            $VAR_CLIENTE_TOP    = null;


        $(function(){
            load_info_reporte_producto();
            load_info_reporte_cliente();
        });

        $filtersProducto.change(function(){
            load_info_reporte_producto();
        });

        var load_info_reporte_producto = function()
        {
            $.get("<?= Url::to(['/admin/user/reporte-venta-producto-ajax']) ?>", { filters: $filtersProducto.serialize()  }, function($responseReporte){
                if ($responseReporte['code'] == 202) {
                    $VAR_PRODUCTO_TOP = $responseReporte['reporte'];
                    load_producto();
                }
            });

        }


        var load_producto = function(){
            Highcharts.chart('container-venta-producto', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'VENTA - PRODUCTO'
                },
                subtitle: {
                    text: 'Productos top 10 más vendidos'
                },
                xAxis: {
                    categories: $VAR_PRODUCTO_TOP['productos'],
                    crosshair: true
                  },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Productos'
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
                        name: 'MAS VENDIDO [KILOGRAMOS]',
                        data: $VAR_PRODUCTO_TOP['vendido'],//[49.9, 71.5, 106.4, 129.2],
                    },{
                        name: 'MAS INGRESO [PESOS]',
                        data: $VAR_PRODUCTO_TOP['pesos'],

                    }
                ]
            });
        }


        $filtersCliente.change(function(){
            load_info_reporte_cliente();
        });

        var load_info_reporte_cliente = function()
        {
            $.get("<?= Url::to(['/admin/user/reporte-venta-cliente-ajax']) ?>", { filters: $filtersCliente.serialize()  }, function($responseReporte){
                if ($responseReporte['code'] == 202) {
                    $VAR_CLIENTE_TOP = $responseReporte['reporte'];
                    load_cliente();
                }
            });

        }


        var load_cliente = function(){
            Highcharts.chart('container-venta-cliente', {
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
                    categories: $VAR_CLIENTE_TOP['clientes'],
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
                        data: $VAR_CLIENTE_TOP['vendido'],//[49.9, 71.5, 106.4, 129.2],
                    },{
                        name: 'MAS INGRESO [PESOS]',
                        data: $VAR_CLIENTE_TOP['pesos'],

                    }
                ]
            });
        }
    </script>
    <?php endif ?>
<?php endif ?>
