<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
use app\models\sucursal\Sucursal;
use app\models\cobro\CobroVenta;
use app\assets\BootstrapTableAsset;
use app\models\esys\EsysListaDesplegable;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'COBROS, ABONOS, PAGOS Y REEMBOLSOS';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['cobro-abonos-json-btt']);
?>


<div class="ibox">
    <div class="ibox-content">
        <div class="apertura-cierre-caja-index">
            <div class="btt-toolbar" style="border-style: solid;border-width: 1px;box-shadow: 2px 2px 5px #8d8d8d;">
                <div class="panel ">
                    <div class="panel-body">

                            <strong class="pad-rgt">Filtrar [FECHA]:</strong>
                            <div class="DateRangePicker   kv-drp-dropdown">
                                <?= DateRangePicker::widget([
                                    'name'           => 'date_range',
                                    //'presetDropdown' => true,
                                    'hideInput'      => true,
                                    'useWithAddon'   => true,
                                    'convertFormat'  => true,
                                    'startAttribute' => 'from_date',
                                    'endAttribute' => 'to_date',
                                    'pluginOptions'  => [
                                            'timePicker'=>true,
                                            'timePickerIncrement'=>15,
                                        'locale' => [
                                            'format'=>'Y-m-d h:i A',
                                            'separator' => ' - ',
                                        ],
                                        'opens' => 'left',
                                        "autoApply" => true,
                                    ],
                                ])
                                ?>
                            </div>
                            <br>
                            <strong class="pad-rgt">Filtrar:</strong>
                            <?=  Html::dropDownList('metodo_pago', null, CobroVenta::$servicioList, ['prompt' => 'METODO DE PAGO', 'class' => 'max-width-170px form-control m-b'])  ?>
                            <?=  Html::dropDownList('tipo', null, CobroVenta::$tipoList, ['prompt' => 'TIPO DE OPERACION', 'class' => 'max-width-170px form-control m-b'])  ?>
                            <?=  Html::dropDownList('sucursal_id', null, Sucursal::getItems(), ['prompt' => 'SUCURSAL QUE RECIBE', 'class' => 'max-width-170px form-control m-b'])  ?>

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
                    field: 'venta_id',
                    title: 'FOLIO DE VENTA',
                    sortable: true,
                },
                {
                    field: 'compra_id',
                    title: 'FOLIO DE COMPRA',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'trans_token_credito',
                    title: 'TOKEN DE CREDITO',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'tipo',
                    title: 'TIPO DE OPERACION',
                    align: 'center',
                    formatter: btf.cobroabono.tipo,
                    sortable: true,
                },
                {
                    field: 'metodo_pago',
                    title: 'METODO DE PAGO',
                    align: 'right',
                    formatter: btf.cobroabono.metodo_pago,
                    sortable: true,
                },
                {
                    field: 'cantidad',
                    title: 'CANTIDAD',
                    align: 'right',
                    formatter: btf.conta.money,
                    sortable: true,
                },
                {
                    field: 'cargo_extra',
                    title: 'CARGO EXTRA',
                    align: 'right',
                    formatter: btf.conta.money,
                    sortable: true,
                },
                {
                    field: 'sucursal_recibe',
                    title: 'RECIBE',
                    formatter: btf.color.bold,
                    sortable: true,
                },
                {
                    field: 'created_at',
                    title: 'GENERADO',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.datetime,
                },
                {
                    field: 'created_by',
                    title: 'GENERADO POR',
                    sortable: true,
                    visible: false,
                    formatter: btf.user.created_by,
                },
            ],
            params = {
                id      : 'apertura',
                element : '.apertura-cierre-caja-index',
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
