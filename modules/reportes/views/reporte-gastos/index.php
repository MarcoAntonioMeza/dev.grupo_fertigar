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

$this->title = 'REPORTE DE GASTOS';
$this->params['breadcrumbs'][] = 'Reporte';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['gasto-json-btt']);

?>

<div class="reporte-gastos">
    <div class="btt-toolbar">
    <div class="panel">
        <div class="panel-body">


                    <strong>Filtrar [RANGO DE FECHA]</strong>
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

                    <?= Html::label('Tipo de gastos', 'gasto') ?>
                    <?= Html::dropDownList('tipo_gasto_id', null, EsysListaDesplegable::getItems('tipo_de_gastos'), ['prompt' => 'Tipo de gastos','class' => "form-control"])  ?>

        </div>
        </div>
    </div>
    <table class="bootstrap-table"></table>
</div>


<script type="text/javascript">
    var $filters            = $('.btt-toolbar :input'),
        $VAR_CLENTE_ADEUDO_TOP   = null;

    $(document).ready(function(){
            var columns = [
                {
                    field: 'singular',
                    title: 'Tipo de gasto',
                    align: 'center',
                    width: '60',
                    sortable: true,
                    switchable:false,
                },
                {
                    field: 'observacion',
                    title: 'Observación',
                    sortable: true,
                },

                {
                    field: 'created_at',
                    title: 'Fecha de creación',
                    sortable: true,
                    formatter: btf.gastos.datetime,
                },
                {
                    field: 'tipo',
                    title: 'Tipo de gasto',
                    sortable: true,
                    formatter: btf.gastos.tipo,
                },
                    {
                        field: 'status',
                        title: 'Estado',
                        sortable: true,
                        formatter: btf.gastos.status,
                    },
                    {
                        field: 'cantidad',
                        title: 'Cantidad',
                        sortable: true,
                        formatter: btf.gastos.money,
                    },
                    {
                        field: 'username',
                        title: 'Nombre de usuario',
                        sortable: true,
                    },
            ],
            params = {
                id      : 'reporteGastos',
                element : '.reporte-gastos',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    sortOrder : 'desc',
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>
