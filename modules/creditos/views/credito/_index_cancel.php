<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\credito\Credito;
use app\models\sucursal\Sucursal;

BootstrapTableAsset::register($this);

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);
$bttUrl       = Url::to(['creditos-json-btt']);
?>
<div class="creditos-cancel-index" >
    <div class="btt-toolbar" >
       <div class="panel mar-btm-5px">
            <div class="panel-body pad-btm-15px">
                <div>
                   <div class="DateRangePicker   kv-drp-dropdown">
                        <strong class="pad-rgt">Filtrar:</strong>
                        <?=  Html::dropDownList('tipo', null, Credito::$tipoList, ['prompt' => 'TIPO DE CREDITO'])  ?>
                        <?=  Html::dropDownList('sucursal_id', null, Sucursal::getRuta(), ['prompt' => 'SUCURSAL / RUTA'])  ?>
                        <?= Html::hiddenInput('cancel_on', true ) ?>
                    </div>
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
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver credito" class="fa fa-eye"></a>'
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
                    title: 'CLIENTE',
                    sortable: true,
                },
                {
                    field: 'proveedor',
                    title: 'Proveedor',
                    sortable: true,
                },
                {
                    field: 'item_count',
                    title: 'CREDITOS CANCELADOS',
                    align : 'center',
                    sortable: true,
                },
                {
                    field: 'tipo',
                    title: 'Tipo',
                    sortable: true,
                    formatter: btf.credito.tipo,
                },
                {
                    field: 'monto',
                    title: 'TOTAL DE CREDITO',
                    align: 'right',
                    switchable: false,
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'monto_pagado',
                    title: 'TOTAL ABONADO [CANCELADO]',
                    align: 'right',
                    switchable: false,
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'deuda',
                    title: 'POR PAGAR',
                    align: 'right',
                    switchable: false,
                    sortable: true,
                    formatter: btf.conta.moneyDanger,
                },
                {
                    field: 'fecha_credito',
                    title: 'FECHA A PAGAR',
                    align: 'center',
                    sortable: true,
                    formatter: btf.time.date,
                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.credito.status,
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
                id      : 'creditoCancel',
                element : '.creditos-cancel-index',
                url     : '<?= $bttUrl ?>',
                pagination: false,
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
