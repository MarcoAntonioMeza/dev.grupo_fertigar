<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\producto\Producto;
use app\models\esys\EsysListaDesplegable;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Productos';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['productos-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);

?>

<div class="ibox">
    <div class="ibox-content">
        <p>
            <?= $can['create']?
                Html::a('Nuevo producto', ['create'], ['class' => 'btn btn-success add']): '' ?>
        </p>

        <div class="productos-producto-index">
            <div class="btt-toolbar" style="border-style: solid;border-width: 1px;box-shadow: 2px 2px 5px #8d8d8d;">
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
                            <?= Html::dropDownList('status', null, Producto::$statusList, ['prompt' => 'Estatus', 'class' => '']) ?>
                            <?= Html::dropDownList('categoria_id', null, EsysListaDesplegable::getItems('producto_categoria'), ['prompt' => 'Categoria', 'class' => '']) ?>
                            <?= Html::dropDownList('permisos', null, Producto::$validateList, ['prompt' => 'Autorizacion', 'class' => '']) ?>

                            <?= Html::dropDownList('is_app', null, [
                                Producto::IS_APP_ON => 'CREADOS EN LA APP'
                            ], ['prompt' => 'Generado', 'class' => '']) ?>
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
        var can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver producto" class="fa fa-eye"></a>',
                (can.update? '<a href="<?= $bttUrlUpdate ?>' + row.id + '" title="Editar producto" class="fa fa-pencil"></a>': ''),
                (can.delete? '<a href="<?= $bttUrlDelete ?>' + row.id + '" title="Eliminar producto" class="fa fa-trash" data-confirm="Confirma que deseas eliminar la producto" data-method="post"></a>': '')
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
                    field: 'clave',
                    title: 'Clave',
                    sortable: true,
                },
                {
                    field: 'nombre',
                    title: 'Producto',
                    sortable: true,
                },
                {
                    field: 'categoria',
                    title: 'Categoria',
                    align: 'center',
                    switchable: false,
                    sortable: true,
                },
                
                {
                    field: 'inventariable',
                    title: '¿ Inventariable ?',
                    sortable: true,
                    formatter: btf.producto.inv,
                    align: 'center',
                }];

                if(!can.hideMonto){
                    columns =  $.merge(columns,[{
                        field: 'costo',
                        title: 'Costo',
                        sortable: true,
                        formatter: btf.conta.money,
                        align: 'right',
                    },

                    {
                        field: 'precio_publico',
                        title: 'Precio publico',
                        sortable: true,
                        formatter: btf.conta.money,
                        align: 'right',
                    },

                    {
                        field: 'precio_sub',
                        title: 'Precio subdistribuidor',
                        sortable: true,
                        formatter: btf.conta.money,
                        align: 'right',
                    },

                    {
                        field: 'precio_mayoreo',
                        title: 'Precio mayoreo',
                        sortable: true,
                        formatter: btf.conta.money,
                        align: 'right',
                    }]);
                }

                columns =  $.merge(columns,[{
                        field: 'stock_minimo',
                        title: 'Stock minimo',
                        sortable: true,
                        align :'center',
                    },

                    {
                        field: 'validate',
                        title: 'AUTORIZACION',
                        sortable: true,
                        formatter: btf.producto.validate,
                        align :'center',
                    },

                    {
                        field: 'is_app',
                        title: 'CREADO POR APP',
                        sortable: true,
                        formatter: btf.producto.inv,
                        align :'center',
                    },
                    {
                        field: 'fecha_autorizar',
                        title: 'FECHA LIMITE AUTORIZAR',
                        align: 'center',
                        sortable: true,
                        switchable: false,
                        formatter: btf.time.date,
                    },
                    {
                        field: 'status',
                        title: 'Estatus',
                        align: 'center',
                        formatter: btf.status.opt_o,
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
                ]);

            params = {
                id      : 'producto',
                element : '.productos-producto-index',
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
