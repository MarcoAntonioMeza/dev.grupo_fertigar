<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['devoluciones-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);
?>



<div class="inventario-devolucion-index">
    <div class="btt-toolbar">
        <?= Html::hiddenInput('on_reembolso', true) ?>

    </div>
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver operación" class="fa fa-eye"></a>',
                (can.cancel? '<a href="<?= $bttUrlDelete ?>' + row.id + '" title="Cancelar operación" class="fa fa-trash" data-confirm="Confirma que deseas cancelar la operación" data-method="post"></a>': '')
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
                    title: 'Sucursal / Bodega',
                    sortable: true,
                },
                {
                    field: 'sucursal_tipo',
                    title: 'Origen',
                    align: 'center',
                    formatter: btf.sucursal.tipo,
                    sortable: true,
                },
                {
                    field: 'operacion_cantidad',
                    title: 'Cantidad',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'tipo',
                    title: 'Operación',
                    align: 'center',
                    formatter: btf.inv.tipo,
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
                id      : 'inventario',
                element : '.inventario-devolucion-index',
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