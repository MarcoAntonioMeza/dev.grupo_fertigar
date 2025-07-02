<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;
use app\models\esys\EsysListaDesplegable;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

//$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['ruta-json-btt']);
$bttUrlView   = Url::to(['view?id=']);

?>

<?php /* ?>
<p>
  <?= $can['create']?
            Html::a('Nueva carga', ['create'], ['class' => 'btn btn-success add']): '' ?>
</p>
*/?>

<div class="logistica-carga-index">
    <div class="btt-toolbar">
    </div>
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver carga" class="fa fa-eye"></a>',
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
                    title: 'Ruta / Sucursal',
                    sortable: true,
                },
                {
                    field: 'encargado',
                    title: 'Encargado',
                    switchable: false,
                    sortable: true,
                },

                {
                    field: 'telefono',
                    title: 'Teléfono',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'telefono_movil',
                    title: 'Teléfono movil',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.status.carga,
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
                id      : 'logistica',
                element : '.logistica-carga-index',
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
