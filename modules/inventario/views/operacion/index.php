<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use app\assets\BootstrapTableAsset;
use app\models\sucursal\Sucursal;
use app\models\esys\EsysListaDesplegable;
use app\models\inv\InventarioOperacion;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'OPERACION [AJUSTE DE INVENTARIO]';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['ajuste-inventario-json-btt']);
$bttUrlView   = Url::to(['view?id=']);

?>

<div class="ibox">
    <div class="ibox-content">
        <p>
            <?= Html::a('<i class="fa fa-plus"></i> SOLICITUD DE AJUSTE DE INVENTARIO', ['create'], ['class' => 'btn btn-success add']) ?>
        </p>
        <div class="inventario-ajuste-operacion-index">
            <div class="btt-toolbar">
                <div class="panel mar-btm-5px">
                    <div class="panel-body pad-btm-15px">
                        <div>
                            <br>
                            <strong class="pad-rgt">Filtrar:</strong>
                            <?= Html::dropDownList('sucursal_id', null, Sucursal::getAlmacenSucursal(), ['prompt' => 'Sucursal', 'class' => 'max-width-170px']) ?>
                            <?= Html::dropDownList('status', null, InventarioOperacion::$statusList,['prompt' => 'Estatus','class' => 'max-width-170px']) ?>
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
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver operación" class="fa fa-eye"></a>',
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
                    title: 'SUCURSAL',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'asignado',
                    title: 'ASIGNADO',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'tipo',
                    title: 'TIPO DE AJUSTE',
                    sortable: true,
                    formatter: btf.inv.tipo_operacion,
                },
                {
                    field: 'count_producto',
                    title: 'N° PRODUCTOS',
                    sortable: true,
                },
                {
                    field: 'status',
                    title: 'ESTATUS',
                    align: 'center',
                    formatter: btf.inv.solicitud,
                },
                {
                    field: 'created_at',
                    title: 'SOLICITADO',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.date,
                },
                {
                    field: 'created_by',
                    title: 'SOLICITADO POR',
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
                id      : 'operacionAjuste',
                element : '.inventario-ajuste-operacion-index',
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
