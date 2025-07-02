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
<div class="creditos-vigente-index" >
    <div class="btt-toolbar" >
        <div class="row">
            <div class="col-sm-9">
               <div class="panel mar-btm-5px">
                    <div class="panel-body pad-btm-15px">
                        <div>
                           <div class="DateRangePicker   kv-drp-dropdown">
                                <strong>FECHA [PAGAR]</strong>
                                <?= DateRangePicker::widget([
                                    'name'           => 'date_range',
                                    //'presetDropdown' => true,
                                    'hideInput'      => true,
                                    'useWithAddon'   => true,
                                    'convertFormat'  => true,
                                     'useWithAddon'=>true,
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
                                <br>
                                <strong class="pad-rgt">Filtrar:</strong>
                                <?=  Html::dropDownList('status', null, [
                                    Credito::STATUS_ACTIVE      => Credito::$statusList[Credito::STATUS_ACTIVE],
                                    Credito::STATUS_POR_PAGADA  => Credito::$statusList[Credito::STATUS_POR_PAGADA]
                                ], ['prompt' => 'ESTATUS'])  ?>
                                <?=  Html::dropDownList('tipo', null, Credito::$tipoList, ['prompt' => 'TIPO DE CREDITO'])  ?>
                                <?=  Html::dropDownList('sucursal_id', null, Sucursal::getRuta(), ['prompt' => 'SUCURSAL / RUTA'])  ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 ">
                <div class="ibox">
                    <div class="ibox-content text-center" style="width: 250px;height: 150px;padding: 25%;">
                        <h2 class="lbl_total_vigente">$00.00</h2>
                        <small style="font-weight: bold;">CREDITO VIGENTE</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <table class="bootstrap-table table-vigente"></table>
</div>

<script type="text/javascript">
    var $lbl_total_vigente   = $('.lbl_total_vigente'),
        total_vigente = 0;

    var suma_total_vigente = function(){
        total_vigente = 0;
        $.each($('.bootstrap-table .table-vigente').bootstrapTable('getData'), function(key, value) {
            total_vigente = total_vigente + parseFloat(value.deuda);
        });
        load_total_vigencia();
    }

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
                    title: 'CREDITOS VIGENTES',
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
                    title: 'TOTAL DE CREDITO [VIGENTE]',
                    align: 'right',
                    switchable: false,
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'monto_pagado',
                    title: 'TOTAL ABONADO',
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
                id      : 'creditoVigente',
                element : '.creditos-vigente-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                    onLoadSuccess : function(params){
                        suma_total_vigente();
                    },
                    onDblClickRow : function(row, $element){
                        window.location.href = '<?= $bttUrlView ?>' + row.id;
                    },
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });

    var load_total_vigencia = function(){
        $lbl_total_vigente.html(btf.conta.money(total_vigente));
    }
</script>
