<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\apertura\AperturaCaja;
use app\models\apertura\AperturaCajaDetalle;
use app\models\cobro\CobroVenta;
use app\models\venta\Venta;
use app\models\Esys;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title = "#" . $model->id ."- ". $model->user->nombreCompleto;

$this->params['breadcrumbs'][] = ['label' => 'Apertura de caja', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Ver';

$bttExport          = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl             = Url::to(['apertura-cierre-operacion-detail-json-btt']);
$bttUrlOtras        = Url::to(['apertura-cierre-operacion-detail-otras-json-btt']);
$bttUrlVentaView    = Url::to(['/tpv/venta/view?id=']);
$bttUrlCreditoView  = Url::to(['/tpv/venta/view?id=']);

?>

<div class="apertura-caja-view">
    <div class="row">
        <div class="col-md-8">
            <div class="ibox">
                <div class="ibox-content">
                    <h2><?= $model->user->nombreCompleto ?> <small class="float-right"
                            style="font-size: 14px;"><?= Esys::unixTimeToString($model->fecha_apertura,"Y-m-d h:i a") ?>
                            / <?= Esys::unixTimeToString($model->fecha_cierre,"Y-m-d h:i a") ?></small></h2>
                    <div class="text-center">
                        <div class="row">
                            <div class="col">
                                <div class=" m-l-md">
                                    <span class="h5 font-bold m-t block">$
                                        <?= number_format($model->cantidad_caja,2) ?></span>
                                    <small class="text-muted m-b block">APERTURA</small>
                                </div>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?= number_format($model->total,2) ?></span>
                                <small class="text-muted m-b block">CIERRE</small>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?=number_format((( AperturaCaja::getTotalEfectivoTpv($model->id)  + $model->cantidad_caja ) - (AperturaCaja::getTotalRetiroTpv($model->id) + AperturaCaja::getTotalGastoTpv($model->id)))  - $model->total ,2)  ?></span>
                                <small class="text-muted m-b block"  style="font-size:10px">FALTANTE ( (EFECTIVO CAJA + APERTURA)  -  CIERRE )</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="ibox">
                        <div class="ibox-content">
                            <div class="text-center">
                                <div class="row">
                                    <div class="col">
                                        <div class=" m-l-md">
                                            <span class="h5 font-bold m-t block">$
                                                <?= number_format(AperturaCaja::getTotalVentaTpv($model->id),2) ?></span>
                                            <small class="text-muted m-b block">TOTAL DE VENTA <strong class="text-warning"> [EFECTIVO]</strong></small>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalCreditoTpv($model->id),2) ?></span>
                                        <small class="text-muted m-b block">TOTAL ABONADO <strong class="text-warning"> [EFECTIVO]</strong></small>
                                    </div>
                                    <div class="col">
                                        <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalRetiroTpv($model->id),2) ?></span>
                                        <small class="text-muted m-b block">TOTAL RETIRO</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-info">
                <div class="panel-heading text-center">
                    <h3 style="margin:4%"><?= AperturaCaja::$statusList[$model->status] ?></h3>
                </div>
            </div>
            <div class="panel">
                <?= Html::button("TICKET  [OPERACIONES]", ["class" => "btn btn-success btn-block", "style" => "padding:5%", "onclick" => "openTicketOperacion()" ] ) ?>
            </div>
            <div class="panel">
                <?= Html::button("REPORTE [OPERACIONES]", ["class" => "btn btn-danger btn-block", "style" => "padding:5%", "onclick" => "openReporteOperacion()" ] ) ?>
            </div>
            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>


    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="text-center">
                        <div class="row">
                            <div class="col">
                                <div class=" m-l-md">
                                    <span class="h5 font-bold m-t block">
                                        $<?= number_format(( AperturaCaja::getTotalEfectivoTpv($model->id)) - ( AperturaCaja::getTotalRetiroTpv($model->id) +  AperturaCaja::getTotalGastoTpv($model->id))  ,2)  ?>
                                    </span>
                                    <strong class="text-muted m-b block" style="font-size:10px">EFECTIVO CAJA ( T. EFECTIVO - ( RETIRO + GASTO) )</strong>
                                </div>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalTranferenciaTpv($model->id),2) ?></span>
                                <strong class="text-muted m-b block">TRANFERENCIA</strong>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalChequeTpv($model->id),2) ?></span>
                                <strong class="text-muted m-b block">CHEQUE</strong>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalTarjetaCreditoTpv($model->id),2) ?></span>
                                <strong class="text-muted m-b block">TARJETA DE CREDITO</strong>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalTarjetaDebitoTpv($model->id),2) ?></span>
                                <strong class="text-muted m-b block">TARJETA DE DEBITO</strong>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalDepositoTpv($model->id),2) ?></span>
                                <strong class="text-muted m-b block">DEPOSITO</strong>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block">$ <?= number_format(AperturaCaja::getTotalCreditoPayTpv($model->id),2) ?></span>
                                <strong class="text-muted m-b block">CREDITO</strong>
                            </div>
                            <div class="col">
                                <span class="h5 font-bold m-t block text-info">$ <?= number_format(AperturaCaja::getTotalOtrosTpv($model->id),2) ?></span>
                                <strong class=" block text-info">OTRO</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h3>VENTAS / CREDITOS RELACIONADOS</h3>
                </div>
                <div class="ibox-content">
                    <div class="apertura-cierre-caja-detail">
                        <div class="btt-toolbar">
                            <?= Html::hiddenInput("caja_id", $model->id) ?>
                        </div>
                        <table class="bootstrap-table"></table>
                    </div>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-title">
                    <h3>OPERACIONES POR [TRANSFERENCIA,  DEPOSITO, CHEQUE, TARJETA] </h3>
                </div>
                <div class="ibox-content">
                    <div class="apertura-cierre-caja-detail-otras">
                        <div class="btt-toolbar">
                            <?= Html::hiddenInput("caja_id", $model->id) ?>
                        </div>
                        <table class="bootstrap-table"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var actions = function(value, row) { return [
            ( row.tipo == 10 ?  '<a href="<?= $bttUrlVentaView ?>' + row.venta_id + '" title="VER VENTA" class="btn btn-primary btn-block text-center" style="margin: 0;" target="_blank">VENTA</a>' : ''),
            ( row.tipo == 20 ?  '<a href="javascript:void(0)"  onclick="open_ticket(' +"'"+ row.token_pay +"'"+ ')" title="VER CREDITO" class="btn btn-warning btn-block text-center" style="margin: 0;">ABONO</a>' : ''),
            ].join(''); },
            columns = [
                {
                    field: 'count_item',
                    title: '#',
                    align: 'center',
                    width: '60',
                    sortable: true,
                    switchable:false,
                },
                {
                    field: 'tipo_text',
                    title: 'TIPO',
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'venta_id',
                    title: 'VENTA',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'token_pay',
                    title: 'CREDITO',
                    formatter: btf.conta.token_pay,
                    align: 'center',
                    sortable: true,
                },
                 {
                    field: 'cliente',
                    title: 'CLIENTE',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'cantidad',
                    title: 'MONTO',
                    align: 'right',
                    formatter: btf.conta.money,
                    sortable: true,
                },
                {
                    field: 'efectivo',
                    title: 'EFECTIVO',
                    align: 'right',
                    formatter: btf.conta.money_color,
                    sortable: true,
                },
                {
                    field: 'cheque',
                    title: 'CHEQUE',
                    align: 'right',
                    formatter: btf.conta.money_color,
                    sortable: true,
                },
                {
                    field: 'transferencia',
                    title: 'TRANSFERENCIA',
                    align: 'right',
                    formatter: btf.conta.money_color,
                    sortable: true,
                },
                {
                    field: 'tarjeta_credito',
                    title: 'TARJETA CREDITO',
                    align: 'right',
                    formatter: btf.conta.money_color,
                    sortable: true,
                },
                {
                    field: 'tarjeta_debito',
                    title: 'TARJETA DEBITO',
                    align: 'right',
                    formatter: btf.conta.money_color,
                    sortable: true,
                },
                {
                    field: 'deposito',
                    title: 'DEPOSITO',
                    align: 'right',
                    formatter: btf.conta.money_color,
                    sortable: true,
                },
                {
                    field: 'credito',
                    title: 'CREDITO',
                    align: 'right',
                    formatter: btf.conta.money_color,
                    sortable: true,
                },
                {
                    field: 'created_at',
                    title: 'FECHA',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.datetime,
                },
                {
                    field: 'registrado_por',
                    title: 'REGISTRADOR POR',
                    sortable: true,
                    formatter: btf.user.created_by,
                },
                {
                    field: 'action',
                    title: 'ACCIONES',
                    align: 'center',
                    switchable: false,
                    width: '100',
                    class: 'btt-icons',
                    formatter: actions,
                    tableexportDisplay:'none',
                }
            ],
            params = {
                id      : 'aperturaDetail',
                element : '.apertura-cierre-caja-detail',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();




        var actionsOtros = function(value, row) { return [
            ( row.tipo == 10 ?  '<a href="<?= $bttUrlVentaView ?>' + row.venta_id + '" title="VER VENTA" class="btn btn-primary btn-block text-center" style="margin: 0;" target="_blank">VENTA</a>' : ''),
            ( row.tipo == 30 ?  '<a href="javascript:void(0)"  onclick="open_ticket(' +"'"+ row.token_pay +"'"+ ')" title="VER CREDITO" class="btn btn-warning btn-block text-center" style="margin: 0;">ABONO</a>' : ''),
            ].join(''); },
            columns = [
                {
                    field: 'count_item',
                    title: '#',
                    align: 'center',
                    width: '60',
                    sortable: true,
                    switchable:false,
                },
                {
                    field: 'tipo_text',
                    title: 'TIPO',
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'venta_id',
                    title: 'VENTA',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'token_pay',
                    title: 'CREDITO',
                    formatter: btf.conta.token_pay,
                    align: 'center',
                    sortable: true,
                },
                 {
                    field: 'cliente',
                    title: 'CLIENTE',
                    align: 'right',
                    sortable: true,
                },
                {
                    field: 'cantidad',
                    title: 'MONTO',
                    align: 'right',
                    formatter: btf.conta.money,
                    sortable: true,
                },
                {
                    field: 'efectivo',
                    title: 'EFECTIVO',
                    align: 'right',
                    formatter: btf.conta.money_color,
                    sortable: true,
                },
                {
                    field: 'cheque',
                    title: 'CHEQUE',
                    align: 'right',
                    formatter: btf.conta.money_color,
                    sortable: true,
                },
                {
                    field: 'transferencia',
                    title: 'TRANSFERENCIA',
                    align: 'right',
                    formatter: btf.conta.money_color,
                    sortable: true,
                },
                {
                    field: 'tarjeta_credito',
                    title: 'TARJETA CREDITO',
                    align: 'right',
                    formatter: btf.conta.money_color,
                    sortable: true,
                },
                {
                    field: 'tarjeta_debito',
                    title: 'TARJETA DEBITO',
                    align: 'right',
                    formatter: btf.conta.money_color,
                    sortable: true,
                },
                {
                    field: 'deposito',
                    title: 'DEPOSITO',
                    align: 'right',
                    formatter: btf.conta.money_color,
                    sortable: true,
                },
                {
                    field: 'credito',
                    title: 'CREDITO',
                    align: 'right',
                    formatter: btf.conta.money_color,
                    sortable: true,
                },
                {
                    field: 'created_at',
                    title: 'FECHA',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.datetime,
                },
                {
                    field: 'registrado_por',
                    title: 'REGISTRADOR POR',
                    sortable: true,
                    formatter: btf.user.created_by,
                },
                {
                    field: 'action',
                    title: 'ACCIONES',
                    align: 'center',
                    switchable: false,
                    width: '100',
                    class: 'btt-icons',
                    formatter: actionsOtros,
                    tableexportDisplay:'none',
                }
            ],
            paramsOtros = {
                id      : 'aperturaDetailOtras',
                element : '.apertura-cierre-caja-detail-otras',
                url     : '<?= $bttUrlOtras ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilderOtras = new MyBttBuilder(paramsOtros);
        bttBuilderOtras.refresh();
    });


</script>



<script>

    var direccionar = function(token_pay){
        $.get("<?= Url::to(['get-compra-venta']) ?>", { token_pay : token_pay }, function( $response ){
            console.log($response);
            var urlDestino = '<?= Url::to(['/creditos/credito/view']) ?>' + '?id=' + $response;
            // Redireccionar a la otra página
            window.open(urlDestino, '_blank');
        },'json');
    }
var VAR_ITEM = "<?= $model->id ?>";
var openTicketOperacion = function(token_pay){
    window.open("<?= Url::to(['imprimir-recibo']) ?>" + "?id=" + VAR_ITEM
                ,'imprimir', 'width=600,height=500');
}

var openReporteOperacion = function(token_pay){
    window.open("<?= Url::to(['imprimir-reporte']) ?>" + "?id=" + VAR_ITEM
                ,'imprimir', 'width=600,height=500');
}


var open_ticket = function(token_pay){
    window.open("<?= Url::to(['imprimir-credito']) ?>" + "?pay_items=" + token_pay
                ,'imprimir', 'width=600,height=500');
}

</script>