<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;
use app\models\producto\Producto;

BootstrapTableAsset::register($this);

/* @var $this yii\web\View */

$this->title = 'Producto - App';
$this->params['breadcrumbs'][] = "Reporte";
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['productos-json-btt']);
$bttUrlView   = Url::to(['/productos/producto/view?id=']);
?>

<div class="reporte-producto-index">
    <div class="btt-toolbar">

        <?= Html::hiddenInput('is_app', Producto::IS_APP_ON) ?>

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
                    <?= Html::dropDownList('permisos_reporte', null, [ 10 => "POR VALIDAR", 20 => "POR VALIDAR [ VENCIDOS ]", 30 => "VALIDADOS" ] , [ 'prompt'=> 'TIPO']) ?>
                </div>
            </div>
        </div>
    </div>
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver producto" class="fa fa-eye"></a>',
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
                    field: 'tipo',
                    title: 'Tipo',
                    align: 'center',
                    formatter: btf.producto.tipo,
                    sortable: true,
                    visible:false,
                },
                {
                    field: 'tipo_medida',
                    title: 'Unidad medida',
                    align: 'center',
                    formatter: btf.producto.unidad,
                    sortable: true,
                    visible:false,
                },

                {
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
                    field: 'validate_create_at',
                    title: 'FECHA QUE SE AUTORIZO',
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
            ],
            params = {
                id              : 'producto',
                element         : '.reporte-producto-index',
                colorProducto   : true,
                url             : '<?= $bttUrl ?>',
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
