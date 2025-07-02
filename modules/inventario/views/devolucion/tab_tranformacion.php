<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\sucursal\Sucursal;
use kartik\daterange\DateRangePicker;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['tranformacion-json-btt']);
$bttUrlView   = Url::to(['tranformacion-view?id=']);
?>



<div class="inventario-tranformacion-index">
    <div class="btt-toolbar">
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
                </div>
            </div>
        </div>
    </div>
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver tranformacion" class="fa fa-eye"></a>'
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
                    field: 'sucursal',
                    title: 'SUCURSAL QUE RECIBE',
                    sortable: true,
                },
                {
                    field: 'motivo_id',
                    title: 'TIPO DE TRANSFORMACIÓN',
                    align: 'center',
                    formatter: btf.tranformacion.tipo,
                    sortable: true,
                },
                {
                    field: 'producto',
                    title: 'Producto',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'producto_cantidad',
                    title: 'Cantidad',
                    align: 'center',
                    sortable: true,
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
                id      : 'tranformacion',
                element : '.inventario-tranformacion-index',
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