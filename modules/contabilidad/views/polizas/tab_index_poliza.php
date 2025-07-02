<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;
use app\models\contabilidad\ContabilidadPoliza;

BootstrapTableAsset::register($this);

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['polizas-json-btt']);
$bttUrlView   = Url::to(['view?id=']);

?>
<div class="ibox">
    <div class="ibox-content">
        <p><?= Html::a('<i class="fa fa-plus"></i> NUEVA POLIZA', ['new-manual'], ['class' => 'btn btn-success add btn-zoom']) ?></p>
        <div class="contabilidad-polizas-index">
            <div class="btt-toolbar" style="border-style: solid;border-width: 1px;box-shadow: 2px 2px 5px #8d8d8d;">
                <div class="ibox ">
                    <div class="ibox-content ">
                        <strong class="pad-rgt">Filtrar:</strong>
                        <?= Html::dropDownList('status', null, ContabilidadPoliza::$statusList, [ "prompt" => "--- SELECT ---","style" => "font-size:16px"  ]) ?>
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
            '<a href="<?= $bttUrlView ?>' + row.id + '" title="DETALLES DE POLIZA" class=" fa fa-eye "></a>',
        ].join(''); },
            columns = [
                {
                    field: 'id',
                    title: 'ID',
                    align: 'center',
                    width: '60',
                    sortable: false,
                    switchable:false,
                },
                {
                    field: 'transaccion',
                    title: 'TRANSACCION',
                    switchable: false,
                    sortable: true,
                    formatter: btf.color.bold,
                    align: 'center',
                },
                {
                    field: 'referencia',
                    title: 'REFERENCIA',
                    switchable: false,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'total',
                    title: 'TOTAL POLIZA',
                    switchable: false,
                    sortable: true,
                    align: 'center',
                    formatter: btf.conta.money,
                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.cuenta.pol,
                },
                {
                    field: 'created_at',
                    title: 'FECHA DE POLIZA',
                    align: 'center',
                    formatter: btf.time.date,
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'created_by_user',
                    title: 'POLIZA GENERADA POR',
                    width: '150',
                    align: 'left',
                    switchable: false,
                    sortable: true,
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
                id      : 'polizas',
                element : '.contabilidad-polizas-index',
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