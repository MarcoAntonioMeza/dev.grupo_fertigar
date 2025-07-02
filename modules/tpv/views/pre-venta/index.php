<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;
use app\models\venta\Venta;
use kartik\daterange\DateRangePicker;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Pre venta APPs';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['ventas-json-btt']);
$bttUrlView   = Url::to(['view?id=']);

?>

<div class="ibox">
    <div class="ibox-content">
        <div class="tpv-preventa-index">
            <div class="btt-toolbar" style="border-style: solid;border-width: 1px;box-shadow: 2px 2px 5px #8d8d8d;">
                <?= Html::hiddenInput('status_preventas_comandera', true) ?>

                <div class="panel mar-btm-5px">
                    <div class="panel-body pad-btm-15px">
                        <div>
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
                            <strong class="pad-rgt">Filtrar:</strong>
                            <?= Html::dropDownList('tipo', null, Venta::$tipoList, ['prompt' => 'Tipo de venta', 'class' => '']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <table class="bootstrap-table"></table>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function(){
        var actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver captura" class="fa fa-eye"></a>'
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
                    field: 'cliente',
                    title: 'Cliente',
                    sortable: true,
                },
                {
                    field: 'tipo',
                    title: 'Tipo',
                    switchable: false,
                    formatter: btf.tpv.tipo,
                    align:'center',
                    sortable: true,
                },

                {
                    field: 'total',
                    title: 'Total',
                    align: 'center',
                    formatter: btf.conta.money,
                    sortable: true,
                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.tpv.status,
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
            ],
            params = {
                id      : 'precaptura',
                element : '.tpv-preventa-index',
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
</script>
