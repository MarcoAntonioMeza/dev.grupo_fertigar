<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\sucursal\Sucursal;
use kartik\daterange\DateRangePicker;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['tranformacion-merma-json-btt']);
$bttUrlView   = Url::to(['tranformacion-merma-view?id=']);
?>



<div class="inventario-merma-index">
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
                    field: 'motivo_id',
                    title: 'MOTIVO DE LA MERMA',
                    formatter: btf.tranformacion.motivo,
                    sortable: true,
                },
                {
                    field: 'nota',
                    title: 'NOTA',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'producto_cantidad',
                    title: 'PRODUCTO CANTIDAD',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'nombre',
                    title: 'NOMBRE DE PRODUCTO',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'nombre_sucursal',
                    title: 'NOMBRE DE LA SUCURSAL',
                    align: 'center',
                    sortable: true,
                },

            ],
            params = {
                id      : 'tranformacion',
                element : '.inventario-merma-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                    //onDblClickRow : function(row, $element){
                    //    window.location.href = '<?//= $bttUrlView ?>//' + row.id;
                    //},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>