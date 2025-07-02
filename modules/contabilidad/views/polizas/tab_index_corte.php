<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;
use app\models\contabilidad\ContabilidadPoliza;

BootstrapTableAsset::register($this);

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['verificacion-polizas-json-btt']);
$bttUrlView   = Url::to(['verificacion-view?id=']);

?>
<div class="ibox">
    <div class="ibox-content">
        <p><?= Html::a('<i class="fa fa-plus"></i> NUEVA VERIFICACION', ['verificacion-corte-poliza'], ['class' => 'btn btn-success add btn-zoom']) ?></p>
        <div class="contabilidad-corte-polizas-index">
            <table class="bootstrap-table"></table>
        </div>
    </div>
</div>

<script type="text/javascript">

        $(document).ready(function(){
        var  $filters = $('.btt-toolbar :input'),
        can     = JSON.parse('<?= json_encode($can) ?>'),
        actions = function(value, row) { return [
            '<a href="<?= $bttUrlView ?>' + row.id + ' title="DETALLES DE LA VERIFICACION" class=" fa fa-eye "></a>',
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
                    field: 'transaccion_text',
                    title: 'TRANSACCION',
                    switchable: false,
                    sortable: true,
                    formatter: btf.color.bold,
                    align: 'center',
                },

                {
                    field: 'total',
                    title: 'TOTAL',
                    switchable: false,
                    sortable: true,
                    align: 'center',
                    formatter: btf.conta.money,
                },

                {
                    field: 'created_at',
                    title: 'FECHA DE VERIFICACION',
                    align: 'center',
                    formatter: btf.time.date,
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'created_by_user',
                    title: 'VERIFICACION GENERADA POR',
                    align: 'center',
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
                id      : 'polizasCorte',
                element : '.contabilidad-corte-polizas-index',
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