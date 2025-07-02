<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);

/* @var $this yii\web\View */

$this->title = 'Perfiles de usarios';
$this->params['breadcrumbs'][] = 'Administración';
$this->params['breadcrumbs'][] = 'Configuraciones';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . " - $this->title - " . date('Y-m-d H.i');
$bttUrl       = Url::to(['perfiles-json-btt']);
$bttUrlView   = Url::to(['view?name=']);
$bttUrlUpdate = Url::to(['update?name=']);
$bttUrlDelete = Url::to(['delete?name=']);
?>


<div class="admin-perfil-index">
    <div class="btt-toolbar">
        <?= $can['create']?
            Html::a('Nuevo perfil de usuario', ['create'], ['class' => 'btn btn-success add']): '' ?>
    </div>
    <table class="bootstrap-table">
        <thead>
            <tr>
                <th data-field="name" data-sortable="true" data-switchable="false"></th>
                <th data-field="" data-sortable="true" data-switchable="false"></th>
            </tr>
        </thead>
    </table>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.name + '" title="Ver perfil del usuario" class="fa fa-eye"></a>',
                (can.update? '<a href="<?= $bttUrlUpdate ?>' + row.name + '" title="Editar perfil de usuario" class="fa fa-pencil"></a>': ''),
                (can.delete? '<a href="<?= $bttUrlDelete ?>' + row.name + '" title="Eliminar perfil de usuario" class="fa fa-trash" data-confirm="Confirma que deseas eliminar el perfil de usuario" data-method="post"></a>': '')
            ].join(''); },
            columns = [
                {
                    field: 'name',
                    title: 'Nombre del perfil',
                    sortable: true,
                    switchable: false,
                },
                {
                    field: 'description',
                    title: 'Descripción',
                    sortable: true,
                    switchable: false,
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
                id      : 'perfil',
                element : '.admin-perfil-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    sortName : 'name',
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                    onDblClickRow : function(row, $element){
                        window.location.href = '<?= $bttUrlView ?>' + row.name;
                    },
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>
