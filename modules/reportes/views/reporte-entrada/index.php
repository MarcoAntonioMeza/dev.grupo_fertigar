<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;
use app\models\sucursal\Sucursal;

BootstrapTableAsset::register($this);

/* @var $this yii\web\View */

$this->title = 'Entradas - Nueva Mercancia';
$this->params['breadcrumbs'][] = "Reporte";
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['entradas-json-btt']);
?>

<div class="reporte-entrada-index">
    <div class="btt-toolbar">
        <div class="panel">
           <div class="panel-body">
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
                    <?= Html::dropDownList('sucursal_id', null, Sucursal::getItems(), [ 'prompt'=> 'Sucursal']) ?>
                </div>
            </div>
        </div>
    </div>
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var  $filters = $('.btt-toolbar :input'),
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
                    field: 'sucursal',
                    title: '# SUCURSAL',
                    sortable: true,
                },
                {
                    field: 'compra_id',
                    title: '# COMPRA',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'producto',
                    title: 'Producto',
                    sortable: true,
                },
                {
                    field: 'cantidad',
                    title: 'Cantidad',
                    sortable: true,

                },
                {
                    field: 'costo',
                    title: 'Costo',
                    align : 'right',
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'created_at',
                    title: 'Creado',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.datetime,
                },
                {
                    field: 'created_by',
                    title: 'Creado por',
                    sortable: true,
                    formatter: btf.user.created_by,
                },
            ],
            params = {
                id      : 'entrada',
                element : '.reporte-entrada-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>
