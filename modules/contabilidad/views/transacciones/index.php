<?php
    use yii\helpers\Url;
    use yii\helpers\Html;
    use app\assets\BootstrapTableAsset;
    use app\models\contabilidad\ContabilidadTransaccion;

    BootstrapTableAsset::register($this);

    $this->title = 'TRANSACCIONES';
    $this->params['breadcrumbs'][] = 'CONTABILIDAD'; 
    $this->params['breadcrumbs'][] = 'TRANSACCIONES';

        $bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
        $bttUrl       = Url::to(['transacciones-conta-json-btt']);
        $bttUrlView   = Url::to(['view?id=']);
        $bttUrlDetails   = Url::to(['detail?id=']);
?>
<div class="ibox">
    <div class="ibox-content">
        <div class="contabilidad-transacciones-index">
            <div class="btt-toolbar" style="border-style: solid;border-width: 1px;box-shadow: 2px 2px 5px #8d8d8d;">
                <div class="ibox "> 
                    <div class="ibox-content ">
                        <strong class="pad-rgt">Filtrar:</strong>
                        <?=  Html::dropDownList('status', false, ContabilidadTransaccion::$statusList, [  "prompt" => "--- SELECT ---" , "style" => "font-size:16px" ])  ?>
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
                row.status ==  <?= ContabilidadTransaccion::STATUS_CONFIGURADO ?> ? '<a href="<?= $bttUrlDetails ?>' + row.id + '" title="Ver Detalles" class="fa fa-eye"></a>' : '',
                row.status ==  <?= ContabilidadTransaccion::STATUS_PROCESO ?> ? '<a href="<?= $bttUrlView ?>' + row.id + '" title="CONFIGURAR OPERACION " class="fa fa-edit"></a>':"",
            ].join(''); },
            columns = [
                {
                    field: 'id',
                    title: 'ID',
                    align: 'center',
                    width: '60',
                    sortable:'true',
                    switchable:false,
                },
                {
                    field: 'transaccion',
                    title: 'Operacion',
                    align: 'center',
                    switchable: false,
                    sortable: false,
                },
                {
                    field: 'motivo',
                    title: 'Descripcion',
                    align: 'center',
                    sortable: false, 
                    formatter: btf.cuenta.motivo,
                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.cuenta.trans_status,
                },
                {
                    field: 'created_at',
                    title: 'Creado',
                    align: 'center',
                    sortable: false,
                    switchable: false,
                    formatter: btf.time.date,
                },
                {
                    field: 'created_by',
                    title: 'Creado por',
                    sortable: false,
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
                id      : 'transacciones_conta',
                element : '.contabilidad-transacciones-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    sortName: "transaccion",
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                    onDblClickRow : function(row, $element){
                        row.status == 20? window.location.href = '<?= $bttUrlDetails ?>' + row.id: "";
                    },
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>