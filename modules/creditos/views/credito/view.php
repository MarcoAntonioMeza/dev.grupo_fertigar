<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use app\widgets\CreatedByView;
use app\models\credito\Credito;
use app\models\cobro\CobroVenta;
use app\models\venta\VentaTokenPay;
use kartik\date\DatePicker;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title =  "CREDITO ".Credito::$tipoList[$model->tipo] .": #" . $model->id ;

$this->params['breadcrumbs'][] = ['label' => 'Credito', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Ver';

$cliente_id = $proveedor_id = null;

$bttUrl       = Url::to(['pagos-detail-json-btt']);

?>
<div class="creditos-credito-view">

    <div class="row">
        <div class="col-md-6">
            <div class="ibox">
                <?php if ($model->tipo == Credito::TIPO_CLIENTE ): ?>
                    <div class="ibox-title">
                        <h5 >Información cliente</h5>
                    </div>
                    <?php if ($model->cliente_id):  $cliente_id = $model->cliente_id; ?>
                        <div class="ibox-content text-center">
                            <h1><?= $model->cliente->nombreCompleto ?></h1>
                            <div class="m-b-sm">
                                    <img alt="image" class="rounded-circle" src="<?= Url::to(['/img/profile-photos/5.png']) ?>">
                            </div>
                            <p class="font-bold">Tel : <?= $model->cliente->telefono_movil  ?> / <?= $model->cliente->telefono ?></p>
                        </div>
                    <?php else: $cliente_id = $model->venta->cliente_id; ?>
                         <div class="ibox-content text-center">
                            <h1><?= $model->venta->cliente->nombreCompleto ?></h1>
                            <div class="m-b-sm">
                                    <img alt="image" class="rounded-circle" src="<?= Url::to(['/img/profile-photos/5.png']) ?>">
                            </div>
                            <p class="font-bold">Tel : <?= $model->venta->cliente->telefono_movil  ?> / <?= $model->venta->cliente->telefono ?></p>
                        </div>
                    <?php endif ?>
                <?php else: ?>
                    <div class="ibox-title">
                        <h5 >Información proveedor</h5>
                    </div>
                    <div class="ibox-content">
                        <?php if ($model->proveedor_id): $proveedor_id = $model->proveedor_id; ?>
                            <div class="ibox-content text-center">
                                <h1><?= $model->proveedor->nombre ?></h1>
                                <div class="m-b-sm">
                                    <img alt="image" class="rounded-circle" src="<?= Url::to(['/img/profile-photos/4.png']) ?>">
                                </div>
                                <p class="font-bold">Tel : <?= $model->proveedor->tel  ?></p>
                            </div>
                        <?php else: $proveedor_id = $model->compra->proveedor_id; ?>
                            <div class="ibox-content text-center">
                                <h1><?= $model->compra->proveedor->nombre ?></h1>
                                <div class="m-b-sm">
                                    <img alt="image" class="rounded-circle" src="<?= Url::to(['/img/profile-photos/4.png']) ?>">
                                </div>
                                <p class="font-bold">Tel : <?= $model->compra->proveedor->tel  ?></p>
                            </div>
                        <?php endif ?>
                    </div>
                <?php endif ?>

                <div class="panel">
                    <div class="panel-body">
                        <?php $totales = Credito::getTotalesCredito( ( $model->tipo == Credito::TIPO_CLIENTE ? $cliente_id :  $proveedor_id ) , $model->tipo) ?>
                        <div class="row text-left">
                            <div class="col text-center">
                                <div class=" m-l-md">
                                <span class="h2 font-bold m-t block">$ <?= number_format($totales["total_credito"]) ?></span>
                                <strong class="text-muted m-b block text-center">TOTAL DE CREDITO</strong>
                                </div>
                            </div>
                            <div class="col text-center">
                                <span class="h2 font-bold m-t block">$ <?= number_format($totales["total_por_pagar"],2) ?></span>
                                <strong class="text-muted m-b block text-center">TOTAL A PAGAR</strong>
                            </div>
                            <div class="col text-center">
                                <span class="h2 font-bold m-t block">$ <?= number_format($totales["total_pagado"],2) ?></span>
                                <strong class="text-muted m-b block text-center">TOTAL PAGADO</strong>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <p style="font-size:14px; color: #000;" class=" font-bold m-t block">CREADO POR : <span class=" font-bold m-t"><?= $model->createdBy->nombreCompleto ?> [ <?= date("Y-m-d",$model->created_at) ?> ]</span> </p>
                            </div>
                            <div class="col-sm-6">
                                <?php if ($model->updated_by): ?>
                                    <p style="font-size:14px; color: #000;" class=" font-bold m-t block">MODIFICADO POR : <span class=" font-bold m-t"><?= $model->updatedBy->nombreCompleto ?> [ <?= date("Y-m-d",$model->updated_at) ?> ]</span> </p>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title">
                    <h3>OPERACIONES DE PAGO</h3>
                </div>
                <div class="ibox-content" style="height: 450px;overflow-y: auto;">
                    <div class="pagos-vigente-index" >
                        <div class="btt-toolbar" >
                            <?= Html::hiddenInput('tipo', $model->tipo ) ?>
                            <?= Html::hiddenInput('item_id', ($model->tipo == Credito::TIPO_CLIENTE ? $cliente_id :  $proveedor_id) ) ?>
                        </div>
                        <table class="bootstrap-table"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
             <div class="ibox">
                <div class="ibox-title">
                    <h3>CREDITOS DE [<?= $model->tipo == Credito::TIPO_CLIENTE  ? 'CLIENTE' : 'PROVEEDOR' ?>]</h3>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table" id="credito_tabla">
                            <thead>
                                <tr class="text-center">
                                    <th>ID CREDITO</th>
                                    <?php if ($model->tipo == Credito::TIPO_CLIENTE): ?>
                                        <th>VENTA</th>
                                    <?php else: ?>
                                        <th>COMPRA</th>
                                    <?php endif ?>
                                    <th>MONTO DEL CREDITO</th>
                                    <th>MONTO PAGADO</th>
                                    <th>MONTO A PAGAR</th>
                                    <th>ESTATUS</th>
                                    <th>FECHA A PAGAR</th>
                                    <th>FECHA DE GENERACIÓN</th>
                                    <th>PAGOS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (Credito::getCredito(  ( $model->tipo == Credito::TIPO_CLIENTE ? $cliente_id :  $proveedor_id ) , $model->tipo) as $key => $item_credito): ?>
                                    <tr class="text-center" style="<?= $item_credito->status == Credito::STATUS_PAGADA ? 'background-color: #457c6a;color: #fff;' : ( $item_credito->status == Credito::STATUS_CANCEL ? 'background-color: #b75252;color: #fff;' : '')?>">
                                        <td>#<?= $item_credito->id ?></td>
                                        <?php if ($model->tipo == Credito::TIPO_CLIENTE): ?>
                                            <?php if ($item_credito->venta_id): ?>
                                                <td style="font-size: 16px;color: #eed522;font-weight: bold;"><?= Html::a("#". str_pad($item_credito->venta_id,6,"0",STR_PAD_LEFT), [ "/tpv/venta/view", "id" => $item_credito->venta_id ], ["target" => "_blank",  "style" => "font-size: 16px;color: #eed522;font-weight: bold;" ] ) ?>    </td>
                                            <?php else: ?>
                                                <td>
                                                    <?php if ($item_credito->trans_token_venta): ?>
                                                       <?= Html::a($item_credito->trans_token_venta, null, [ "class" => "text-link","data-target" => "#modal-nota-ventas", "data-toggle" => "modal", 'onclick' => 'onGetVentas(' . $item_credito->id. ')', "style" => "font-size: 16px;color: #eed522;font-weight: bold;"  ] ) ?>
                                                    <?php else: ?>
                                                        CREDITO IMPORTADO [gestionix]
                                                    <?php endif ?>

                                                </td>
                                            <?php endif ?>
                                        <?php else: ?>
                                            <td >
                                                <?php if ($item_credito->compra_id): ?>
                                                    <?= Html::a("#". str_pad($item_credito->compra_id,6,"0",STR_PAD_LEFT), [ "/compras/compra/view", "id" => $item_credito->compra_id ], ["target" => "_blank", "style" => "font-size: 16px;color: #eed522;font-weight: bold;"] ) ?>
                                                <?php else: ?>
                                                    CREDITO IMPORTADO [gestionix]
                                                <?php endif ?>
                                            </td>
                                        <?php endif ?>
                                        <td><strong class="h5">$<?= number_format($item_credito->monto,2) ?></strong></td>
                                        <td><strong class="h5">$<?= number_format($item_credito->monto_pagado,2) ?></strong></td>
                                        <td><strong class="h5">$<?= number_format($item_credito->monto - $item_credito->monto_pagado,2) ?></strong></td>
                                        <td><strong><?= Credito::$statusList[$item_credito->status ] ?></strong></td>
                                        <td><?= $item_credito->fecha_credito ? date("Y-m-d", $item_credito->fecha_credito) : 'N/A' ?></td>
                                        <td><?= date("Y-m-d",$item_credito->created_at) ?></td>
                                        <td>
                                            <?= Html::button("<i class= 'fa fa-money'></i>",[ "class" => "btn btn-primary btn-circle ", "data-target" => "#modal-credito", "data-toggle" => "modal", 'onclick' => 'onGetOperacion(' . $item_credito->id. ')' ]) ?>

                                            <?= $can['cancel'] && $item_credito->status == Credito::STATUS_ACTIVE ?
                                            Html::a('Cancelar', ['cancel', 'id' => $item_credito->id], [
                                                'class' => 'btn btn-danger',
                                                'data' => [
                                                    'confirm' => '¿Estás seguro de que deseas cancelar este credito?',
                                                    'method' => 'post',
                                                ],
                                            ]): '' ?>

                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($model->tipo == Credito::TIPO_PROVEEDOR ): ?>
        <div class="ibox">
            <div class="ibox-content">
                <h3>GASTOS ANEXADOS  <?= Html::button("<i class='fa fa-plus'></i> AGREGAR GASTO", [ " class" => "btn btn-warning", "data-target" => "#modal-nuevo-gasto", "data-toggle" => "modal" ]) ?> </h3>
                <div class="row">
                    <div class="col-sm-12">
                        <table class="table" id="creditos_tabla_2">
                            <thead>
                                <tr class="text-center">
                                    <th>ID CREDITO</th>
                                    <th>CONCEPTO</th>
                                    <th>MONTO DEL CREDITO</th>
                                    <th>MONTO PAGADO</th>
                                    <th>MONTO A PAGAR</th>
                                    <th>ESTATUS</th>
                                    <th>FECHA A PAGAR</th>
                                    <th>FECHA DE GENERACIÓN</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th>PAGOS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (Credito::getCreditoGasto(  ( $model->tipo == Credito::TIPO_CLIENTE ? $cliente_id :  $proveedor_id ) , $model->tipo) as $key => $item_credito): ?>
                                    <tr class="text-center" style="<?= $item_credito->status == Credito::STATUS_PAGADA ? 'background-color: #457c6a;color: #fff;' : ''?>">
                                        <td>#<?= $item_credito->id ?></td>
                                        <td><?= $item_credito->titulo_gasto ?></td>
                                        <td><strong class="h5">$<?= number_format($item_credito->monto,2) ?></strong></td>
                                        <td><strong class="h5">$<?= number_format($item_credito->monto_pagado,2) ?></strong></td>
                                        <td><strong class="h5">$<?= number_format($item_credito->monto - $item_credito->monto_pagado,2) ?></strong></td>
                                        <td><strong><?= Credito::$statusList[$item_credito->status ] ?></strong></td>
                                        <td><?= $item_credito->fecha_credito ? date("Y-m-d", $item_credito->fecha_credito) : 'N/A' ?></td>
                                        <td><?= date("Y-m-d",$item_credito->created_at) ?></td>
                                        <td><?= $item_credito->descripcion ?></td>
                                        <td>
                                            <?= Html::button("<i class= 'fa fa-money'></i>",[ "class" => "btn btn-primary btn-circle ", "data-target" => "#modal-credito", "data-toggle" => "modal", 'onclick' => 'onGetOperacion(' . $item_credito->id. ')' ]) ?>


                                            <?= $can['cancel'] && $item_credito->status == Credito::STATUS_ACTIVE ?
                                            Html::a('Cancelar', ['cancel', 'id' => $item_credito->id], [
                                                'class' => 'btn btn-danger',
                                                'data' => [
                                                    'confirm' => '¿Estás seguro de que deseas cancelar este credito?',
                                                    'method' => 'post',
                                                ],
                                            ]): '' ?>

                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>



    <?php endif ?>
</div>


<div class="fade modal inmodal " id="modal-credito"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">HISTORIAL DE OPERACIONES</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">OPERACION</th>
                                        <th style="text-align: center;">ABONO</th>
                                        <th style="text-align: center;">FECHA DE PAGO</th>
                                        <th style="text-align: center;">CAJERO / EMPLEADO</th>
                                        <th style="text-align: center;">MODIFICADO</th>
                                        <th style="text-align: center;">MODIFICADO POR</th>
                                        <th style="text-align: center;">ESTATUS</th>
                                        <th style="text-align: center;">CANCELAR</th>
                                    </tr>
                                </thead>
                                <tbody class="content_search" style="text-align: center;">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="fade modal inmodal " id="modal-nuevo-gasto"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">NUEVO GASTO - [PROVEEDOR] </h4>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'form-user', 'action' => Url::to(['post-gasto-create']) ]) ?>
            <?= Html::hiddenInput('Credito[proveedor_id]', $proveedor_id ) ?>
            <?= Html::hiddenInput('Credito[credito_id]', $model->id ) ?>
            <!--Modal body-->
            <div class="modal-body">
                <div class="ibox">
                    <div class="ibox-content">
                        <strong>Concepto - Gasto</strong>
                        <?= Html::input("text", 'Credito[titulo_gasto]', false, [ "class" => "form-control", "style" => "font-size: 24px; height: 100%; font-weight: bold;", "autocomplete" => "off"]) ?>

                        <strong>Descripción</strong>
                        <?= Html::textarea('Credito[descripcion]', false, [ "class" => "form-control", "rows" => 6]) ?>

                        <strong>Cantidad</strong>
                        <?= Html::input("text", 'Credito[monto]', false, [ "class" => "form-control", "style" => "font-size: 24px; height: 100%; font-weight: bold;", "id" => "inputNuevoGastoMonto", "autocomplete" => "off" ]) ?>


                        <strong>FECHA A PAGAR</strong>
                        <?= DatePicker::widget([
                            'name' => 'Credito[fecha_credito]',
                            'type' => DatePicker::TYPE_COMPONENT_APPEND,
                            'language' => 'es',
                            'pickerIcon' => '<i class="fa fa-calendar"></i>',
                            'removeIcon' => '<i class="fa fa-trash"></i>',
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',

                            ],
                            'options' => [  "autocomplete" => "off" ],
                        ]); ?>
                    </div>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton( 'Guardar GASTO', ['class' => 'btn btn-success', 'id' => 'btnSaveGasto']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<div class="fade modal inmodal " id="modal-nota-ventas"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">VENTAS RELACIONADAS</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="ibox">
                    <div class="ibox-content">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">FOLIO</th>
                                    <th style="text-align: center;">TOTAL</th>
                                    <th style="text-align: center;">SUCURSAL / RUTA</th>
                                    <th style="text-align: center;">EMPLEADO</th>
                                    <th style="text-align: center;">FECHA</th>
                                </tr>
                            </thead>
                            <tbody class="content_venta" style="text-align: center;">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="fade modal inmodal " id="modal-detail-pago"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">DETALLE DE PAGO</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="ibox">
                    <div class="ibox-content">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">CREDITO</th>
                                    <th style="text-align: center;">MONTO</th>
                                    <th style="text-align: center;">FECHA</th>
                                    <th style="text-align: center;">EMPLEADO</th>
                                    <th style="text-align: center;">MODIFICADO</th>
                                    <th style="text-align: center;">MODIFICADO POR</th>
                                    <th style="text-align: center;">ESTATUS</th>
                                    <th style="text-align: center;">ACCION</th>
                                </tr>
                            </thead>
                            <tbody class="content_pago" style="text-align: center;">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready( function () {
        $('#credito_tabla').DataTable();
        $('#creditos_tabla_2').DataTable();
    });
    $btnShowOperacion   = $('#btnOperacionShow'),
    $contentHtml        = $('.content_search'),
    $contentPago        = $('.content_pago'),
    $contentVenta       = $('.content_venta'),
    $btnSaveGasto       = $('#btnSaveGasto'),
    can_cancel          = <?=  $can['cancel'] ? 10 : 20 ?>;

$(function(){
    $('body').addClass('mini-navbar');
    $('#inputNuevoGastoMonto').mask('000,000,000,000,000.00', {reverse: true});

    columns = [
        {
            field: 'id',
            title: 'ID',
            align: 'center',
            width: '60',
            sortable: true,
            visible:false,
        },
        {
            field: 'trans_token_pay',
            title: 'OPERACION',
            sortable: true,
            formatter: btf.credito.url_link_pago,
        },
        {
            field: 'cantidad_final',
            title: 'CANTIDAD',
            align : 'right',
            sortable: true,
            formatter: btf.credito.title_money,
        },
        {
            field: 'fecha',
            title: 'FECHA DE PAGO',
            sortable: true,
            align: 'center',
        },
        {
            field: 'registrado_por',
            title: 'CAJERO EMPLEADO',
            align: 'center',
            switchable: false,
            sortable: true,
        }        
    ],
    params = {
        id      : 'pagosVigente',
        element : '.pagos-vigente-index',
        url     : '<?= $bttUrl ?>',
        bootstrapTable : {
            columns : columns,
            search: false,
            showRefresh: false,
            showColumns : false,
            showToggle  : false,
            showPaginationSwitch : false,
            showExport       : false,
            pageList    : [ 30, 50, 100, 500, 1000, 10000],
            pageSize    : 30,
        }
    };

    bttBuilder = new MyBttBuilder(params);
    bttBuilder.refresh();

});


var onOperacionPago = function( $opera_token_pay ){
    $('#modal-detail-pago').modal('show');
    $contentPago.html(null);
    $.get('<?= Url::to(["get-history-pago"]) ?>',{ opera_token_pay : $opera_token_pay },function($response){
        if ($response.code == 202 ) {
            $tempHtml = "";
            $.each($response.transaccion,function(key, item_transaccion){
                $tempHtml += "<tr>"+
                    '<td><a href="#" onclick = open_ticket("'+ item_transaccion.token_pay +'") >#'+item_transaccion.credito_id +'</a></td>'+
                    "<td><p style='font-size:14px;font-weight:bold;' class='text-warning'>"+ btf.conta.money(item_transaccion.cantidad) +"</p></td>"+
                    "<td>"+item_transaccion.created_at +"</td>"+
                    "<td>"+item_transaccion.empleado   +"</td>"+
                    "<td>"+item_transaccion.updated_at +"</td>"+
                    "<td>"+item_transaccion.modificado   +"</td>"+
                    "<td><strong class='"+ (item_transaccion.status == 10 ? 'text-primary' : 'text-danger' ) +"'>"+ item_transaccion.status_text + "</strong></td>";
                if (can_cancel == 10 && item_transaccion.status == 10)
                    $tempHtml +="<td><button class='btn btn-danger btn-circle btn-xs' onclick='function_detele_abono("+ item_transaccion.id +","+ item_transaccion.credito_id  +")'><i class='fa fa-trash'></i></button></td>";
                else
                    $tempHtml +="<td></td>";


                $tempHtml += "</tr>";
            });
            $contentPago.html($tempHtml);
        }
    },'json');
};

var onGetOperacion = function( $credito_id ){
    $contentHtml.html(null);
    $.get('<?= Url::to(["get-history-operacion"]) ?>',{ credito_id : $credito_id },function($response){
        if ($response.code == 202 ) {
            $tempHtml = "";
            $.each($response.transaccion,function(key, item_transaccion){
                $tempHtml += "<tr>"+
                    '<td><a href="#" onclick = open_ticket("'+ item_transaccion.token_pay +'") >'+item_transaccion.token_pay +'</a></td>'+
                    "<td>"+ btf.conta.money(item_transaccion.cantidad) +"</td>"+
                    "<td>"+item_transaccion.created_at +"</td>"+
                    "<td>"+item_transaccion.empleado   +"</td>"+
                    "<td>"+item_transaccion.updated_at +"</td>"+
                    "<td>"+item_transaccion.modificado   +"</td>"+
                    "<td><strong class='"+ (item_transaccion.status == 10 ? 'text-primary' : 'text-danger' ) +"'>"+ item_transaccion.status_text + "</strong></td>";
                if (can_cancel == 10 && item_transaccion.status == 10)
                    $tempHtml +="<td><button class='btn btn-danger btn-circle btn-xs' onclick='function_detele_abono("+ item_transaccion.id +","+ $credito_id  +")'><i class='fa fa-trash'></i></button></td>";
                else
                    $tempHtml +="<td></td>";


                $tempHtml += "</tr>";
            });
            $contentHtml.html($tempHtml);
        }
    },'json');
 }

var onGetVentas = function( $credito_id ){
    $contentVenta.html(null);
    $.get('<?= Url::to(["get-token-ventas"]) ?>',{ credito_id : $credito_id },function($response){
        if ($response.code == 202 ) {
            $tempHtml = "";
            $.each($response.ventas,function(key, item_venta){
                $tempHtml += "<tr>"+
                    '<td><a href="<?= Url::to(['/tpv/venta/view']) ?>?id='+ item_venta.id +'" target= "_blank">'+item_venta.folio +'</a></td>'+
                    "<td>"+ btf.conta.money(item_venta.total) +"</td>"+
                    "<td>"+item_venta.sucursal +"</td>"+
                    "<td>"+item_venta.created_at +"</td>"+
                    "<td>"+item_venta.empleado   +"</td>"+
                    "<td></td>";


                $tempHtml += "</tr>";
            });
            $contentVenta.html($tempHtml);
        }
    },'json');
 }

 var function_detele_abono = function(abono_id,credito_id){
    $.post("<?= Url::to(['delete-abono']) ?>",{ abono_id : abono_id },function($response){
        if ($response.code == 202 ) {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.success($response.message, 'CREDITO');
            location.reload();
        }
        onGetOperacion(credito_id);
    });
 }


 var open_ticket = function(token_pay){
    window.open("<?= Url::to(['imprimir-credito']) ?>" + "?pay_items=" + token_pay
                    ,'imprimir', 'width=600,height=500');
 }

 $btnSaveGasto.click(function(event){
    $('#modal-nuevo-gasto').modal('hide');
    show_loader();
 });

 var show_loader = function(){
    $('body').append('<div  id="page_loader" style="opacity: .8;z-index: 2040 !important;    position: fixed;top: 0;left: 0;z-index: 1040;width: 100vw;height: 100vh;background-color: #000;"><div class="spiner-example" style="position: fixed;top: 50%;left: 0;z-index: 2050 !important; width: 100%;height: 100%;"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div>');
}


var hide_loader = function(){
    $('#page_loader').remove();
}

</script>