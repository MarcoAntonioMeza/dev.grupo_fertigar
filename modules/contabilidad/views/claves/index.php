<?php
    use yii\helpers\Url;
    use yii\helpers\Html;
    use app\assets\BootstrapTableAsset;
    use app\models\contabilidad\ContabilidadCuenta;

    BootstrapTableAsset::register($this);

    $this->title = 'Cuentas contables';
    $this->params['breadcrumbs'][] = 'CONTABILIDAD'; 
    $this->params['breadcrumbs'][] = 'CATALOGO';

    $bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
    $bttUrl       = Url::to(['catalogos-conta-json-btt']);
    $bttUrlSubcuenta   = Url::to(['subcuenta?id=']);
    $bttUrlUpdate = Url::to(['update?id=']);
    $bttUrlDelete = Url::to(['delete?id=']);
?>
<div class="ibox">
    <div class="ibox-content">
        <p>
            <?php if (Yii::$app->user->can('admin')): ?>
            <?= $can['create'] ? Html::a('<i class="fa fa-plus"></i> NUEVA CUENTA', ['create'], ['class' => 'btn btn-success add btn-zoom']): '' ?>
            <?php endif ?>
        </p>
        <div class="contabilidad-claves-index">
            <div class="btt-toolbar" style="border-style: solid;border-width: 1px;box-shadow: 2px 2px 5px #8d8d8d;">
                <div class="ibox ">
                    <div class="panel-body pad-btm-15px">
                        <div>
                            <strong class="pad-rgt">Filtrar:</strong>
                            <?=  Html::dropDownList('status', null, ContabilidadCuenta::$statusList, [ 'class' => 'max-width-170px form-control m-b'])  ?>
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
        var  $filters = $('.btt-toolbar :input'),
        can     = JSON.parse('<?= json_encode($can) ?>'),
        actions = function(value, row) { return [
                            '<a href="<?= $bttUrlSubcuenta ?>' + row.id + '"   title="Add subcuenta"    class="fa fa-plus-square">  </a>',
            (can.update? '<a href="<?= $bttUrlUpdate    ?>' + row.id + '"   title="Editar cuenta"    class="fa fa-pencil">       </a>': ''),
            (can.delete? '<a href="<?= $bttUrlDelete    ?>' + row.id + '"   title="Eliminar cuenta"  class="fa fa-trash" data-confirm="Confirma que deseas eliminar la cuenta" data-method="post"></a>': ''),
        ].join(''); },
            columns = [
                {
                    field: 'id',
                    title: 'ID',
                    align: 'center',
                    width: '60',
                    sortable: true,
                    switchable:true,
                    visible: false,
                },
                {
                    field: 'code',
                    title: 'Agrupacion',
                    width: '60',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'nombre',
                    title: 'Nombre',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'afectable',
                    title: 'Afectable',
                    switchable: false,
                    sortable: true,                    
                    align: 'center',
                    formatter: btf.status.opt_check,
                },
                {
                    field: 'status',
                    title: 'Status',                   
                    align: 'center',
                    switchable: false,
                    sortable: true,
                    formatter: btf.status.opt_o,
                },
                {
                    field: 'created_at',
                    title: 'Creado',
                    align: 'center',
                    sortable: true,
                    visible: true,
                    switchable: true,
                    formatter: btf.time.date, 
                },
                {
                    field: 'created_by',
                    title: 'Creado por',
                    sortable: true,
                    visible: false,
                    formatter: btf.user.created_by,
                    switchable: true,
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
                id      : 'catalogos_conta',
                element : '.contabilidad-claves-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    sortName: "code",
                    sortOrder: "asc",
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"}
                }
        };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>