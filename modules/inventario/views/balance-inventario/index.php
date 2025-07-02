<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use app\assets\BootstrapTableAsset;
use app\models\sucursal\Sucursal;
use app\models\esys\EsysListaDesplegable;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'BALANCE INVENTARIO';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['balance-inventario-json-btt']);


?>

<div class="ibox">
    <div class="ibox-content">
        <div class="inventario-arqueo-almacen-index">
            <div class="btt-toolbar" style="border-style: solid;border-width: 1px;box-shadow: 2px 2px 5px #8d8d8d;">
                <div class="panel mar-btm-5px">
                    <div class="panel-body pad-btm-15px">
                        <div>
                            <br>
                            <strong class="pad-rgt">Filtrar:</strong>

                            <?= Html::dropDownList('sucursal_id', null, Sucursal::getItems(), ['prompt' => 'Sucursal', 'class' => '']) ?>
                            <?= Html::dropDownList('existencia', null, [
                                null  => "CATALOGO DE PRODUCTOS [TODOS]",
                                1  => "PRODUCTO CON EXISTENCIA",
                                10 => "PRODUCTO SIN EXISTENCIA",
                                20 => "PRODUCTOS POR TERMINAR (MENOR A 5)",
                                30 => "PRODUCTOS CON EXISTENCIA (MAYOR A 5)",
                            ]) ?>
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
        var columns = [
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
                    title: 'Sucursal',
                    align: 'center',
                    sortable: true,
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
                    title: 'STOCK',
                    align: 'center',
                    formatter: btf.conta.number,
                    sortable: true,
                },
                {
                    field: 'tipo',
                    title: 'Tipo',
                    align: 'center',
                    formatter: btf.producto.tipo,
                    sortable: true,
                    visible:false,
                },
                {
                    field: 'tipo_medida',
                    title: 'Unidad medida',
                    align: 'center',
                    formatter: btf.producto.unidad,
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
            ],
            params = {
                id      : 'inventario',
                element : '.inventario-arqueo-almacen-index',
                autoHeight : false,
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>
