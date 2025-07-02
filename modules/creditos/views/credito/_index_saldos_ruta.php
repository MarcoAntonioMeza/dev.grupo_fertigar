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
<div class="creditos-saldos-ruta" >
    <div class="btt-toolbar" >
        <div class="row">
            <div class="col-sm-6">
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
                               <?= Html::hiddenInput('sucursales_on', true ) ?>
                                <?=  Html::dropDownList('sucursal_id', null, Sucursal::getRuta(), ['prompt' => 'SUCURSAL / RUTA'])  ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 ">
                <div class="panel ">
                    <div class="panel-body ">
                    <div style="width: 300px;height: 100px;">
                        <h2 class="lbl_total_saldos">$00.00</h2>
                        <small style="font-weight: bold;">CREDITO VIGENTE</small>
                    </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="panel ">
                    <div class="panel-body ">
                    <div style="width: 300px;height: 100px;">
                        <h2 class="lbl_total_saldos_por_pagar">$00.00</h2>
                        <small style="font-weight: bold;">CREDITO POR PAGAR</small>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <table class="bootstrap-table table-saldos"></table>
</div>

<script type="text/javascript">
    var $lbl_total_saldos   = $('.lbl_total_saldos'),
        total_saldos = 0;
    var $lbl_total_saldos_por_pagar   = $('.lbl_total_saldos_por_pagar'),
        total_saldos = 0;
    var suma_total_saldos = function(){
        total_saldos = 0;
        total_saldos_por_pagar=0;
        $.each($('.bootstrap-table .table-saldos').bootstrapTable('getData'), function(key, value) {
            if(value.status=='40'){
                total_saldos_por_pagar = total_saldos_por_pagar + parseFloat(value.deuda);
            }
            total_saldos = total_saldos + parseFloat(value.deuda);
        });
        load_total_saldos();
        lbl_total_saldos_por_pagar();
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
                id      : 'creditoSaldosRuta',
                element : '.creditos-saldos-ruta',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                    onLoadSuccess : function(params){
                        suma_total_saldos();
                    },
                    onDblClickRow : function(row, $element){
                        window.location.href = '<?= $bttUrlView ?>' + row.id;
                    },
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });

    var load_total_saldos = function(){
        $lbl_total_saldos.html(btf.conta.money(total_saldos));
    }
    var lbl_total_saldos_por_pagar = function(){
        $lbl_total_saldos_por_pagar.html(btf.conta.money(total_saldos_por_pagar));
    }

</script>
