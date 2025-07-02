<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\sucursal\Sucursal;
use app\assets\BootstrapTableAsset;
use app\models\esys\EsysListaDesplegable;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Sucursales';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['sucursales-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);

?>
<div class="ibox">
    <div class="ibox-content">
        <p>
            <?= $can['create']?
                Html::a('<i class="fa fa-plus"></i> NUEVA SUCURSAL', ['create'], ['class' => 'btn btn-success add']): '' ?>
        </p>

        <div class="sucursales-sucursal-mex-index">
            <table class="bootstrap-table"></table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver sucursal" class="fa fa-eye"></a>',
                (can.update? '<a href="<?= $bttUrlUpdate ?>' + row.id + '" title="Editar sucursal" class="fa fa-pencil"></a>': ''),
                (can.delete? '<a href="<?= $bttUrlDelete ?>' + row.id + '" title="Eliminar sucursal" class="fa fa-trash" data-confirm="Confirma que deseas eliminar la sucursal" data-method="post"></a>': '')
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
                    field: 'nombre',
                    title: 'Nombre sucursal',
                    sortable: true,
                },
                {
                    field: 'encargado',
                    title: 'Encargado',
                    switchable: false,
                    sortable: true,
                },

                {
                    field: 'telefono',
                    title: 'Teléfono',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'telefono_movil',
                    title: 'Teléfono movil',
                    align: 'center',
                    sortable: true,
                },
                 {
                    field: 'estado',
                    title: 'Estado',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'municipio',
                    title: 'Municipio',
                    align: 'center',
                    sortable: true,
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
                id      : 'sucursal',
                element : '.sucursales-sucursal-mex-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    sortName : 'nombre',
                    sortOrder : 'asc',
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
