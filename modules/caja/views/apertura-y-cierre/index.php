<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\sucursal\Sucursal;
use app\assets\BootstrapTableAsset;
use app\models\esys\EsysListaDesplegable;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Apertura y Cierre';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['apertura-cierre-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);
?>

<div class="ibox">
    <div class="ibox-content">
        <div class="apertura-cierre-caja-index">
            <div class="btt-toolbar">
            </div>
            <table class="bootstrap-table"></table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver" class="fa fa-eye"></a>',
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
                    field: 'vendedor',
                    title: 'Empleado',
                    sortable: true,
                },
                {
                    field: 'fecha_apertura',
                    title: 'Fecha apertura',
                    align: 'center',
                    sortable: true,
                    formatter: btf.time.datetime,
                },
                {
                    field: 'fecha_cierre',
                    title: 'Fecha cierre',
                    align: 'center',
                    sortable: true,
                    formatter: btf.time.datetime,
                },
                 {
                    field: 'cantidad_caja',
                    title: 'Cantidad apertura',
                    align: 'right',
                    formatter: btf.conta.money,
                    sortable: true,
                },
                {
                    field: 'total',
                    title: 'Cantidad cierre',
                    align: 'right',
                    formatter: btf.conta.money,
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
                id      : 'apertura',
                element : '.apertura-cierre-caja-index',
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
