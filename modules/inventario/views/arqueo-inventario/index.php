<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use app\assets\BootstrapTableAsset;
use app\models\esys\EsysListaDesplegable;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'INVENTARIO DE PRODUCTOS';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['arqueo-inventario-json-btt']);
$bttUrlView   = Url::to(['view?id=']);

?>



<div class="ibox">
    <div class="ibox-content">
        <?php /* ?><?php if (Yii::$app->user->can('ajusteInventarioAccess')): ?>
        <p>
          <?= Html::a('ARQUEO DE INVENTARIO', ['arqueo-inventario-productos'], ['class' => 'btn btn-success add']) ?>
        </p>
        <?php endif ?>
        */?>
        <div class="inventario-arqueo-almacen-index">
            <div class="btt-toolbar" style="border-style: solid;border-width: 1px;box-shadow: 2px 2px 5px #8d8d8d;">
                <div class="panel mar-btm-5px">
                    <div class="panel-body pad-btm-15px">
                        <div>
                            <br>
                            <strong class="pad-rgt">Filtrar:</strong>

                            <?= Html::dropDownList('categoria_id', null, EsysListaDesplegable::getItems('producto_categoria'), ['prompt' => 'Categoria', 'class' => '']) ?>
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
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver operación" class="fa fa-eye"></a>'
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
                    field: 'clave',
                    title: 'Clave',
                    sortable: true,
                },
                {
                    field: 'nombre',
                    title: 'PRODUCTO',
                    sortable: true,
                },
                {
                    field: 'stock_total',
                    title: 'STOCK TOTAL',
                    align: 'center',
                    formatter: btf.conta.number,
                    sortable: true,
                },
                {
                    field: 'categoria',
                    title: 'Categoria',
                    align: 'center',
                    switchable: false,
                    sortable: true,
                },
                
                {
                    field: 'proveedor',
                    title: 'Proveedor',
                    sortable: true,
                    visible:false,
                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.status.opt_o,
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
                id      : 'inventario',
                element : '.inventario-arqueo-almacen-index',
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
