<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;
use app\models\esys\EsysListaDesplegable;
use kartik\daterange\DateRangePicker;
use app\models\proveedor\Proveedor;

BootstrapTableAsset::register($this);


/* @var $this yii\web\View */

$this->title = 'Proveedor';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['proveedores-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);
?>

<div class="ibox">
    <div class="ibox-content">
        <p>
            <?= $can['create'] ?
                Html::a('<i class="fa fa-plus"></i> NUEVO PROVEEDOR', ['create'], ['class' => 'btn btn-success add']): '' ?>
        </p>
        <div class="proveedores-proveedor-index">
            <div class="btt-toolbar" style="border-style: solid;border-width: 1px;box-shadow: 2px 2px 5px #8d8d8d;">
                <div class="ibox ">
                    <div class="ibox-content ">
                        <strong class="pad-rgt">Filtrar:</strong>
                        <?=  Html::dropDownList('status', null, Proveedor::$statusList, [  'class' => 'max-width-170px'])  ?>
                    </div>
                </div>
            </div>
            <table class="bootstrap-table"></table>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function(){


         var  $filters = $('.btt-toolbar :input'),
            can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver proveedor" class="fa fa-eye"></a>',
                (can.update? '<a href="<?= $bttUrlUpdate ?>' + row.id + '" title="Editar proveedor" class="fa fa-pencil"></a>': ''),
                (can.delete? '<a href="<?= $bttUrlDelete ?>' + row.id + '" title="Eliminar proveedor" class="fa fa-trash" data-confirm="Confirma que deseas eliminar el proveedor" data-method="post"></a>': '')
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
                    title: 'Proveedor',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'total_adeudo',
                    title: 'Total de adeudo',
                    formatter: btf.conta.money,
                    align: 'right',
                    sortable: true,
                },
                {
                    field: 'rfc',
                    title: 'RFC',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'tel',
                    title: 'Teléfono',
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
                id      : 'proveedor',
                element : '.proveedores-proveedor-index',
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