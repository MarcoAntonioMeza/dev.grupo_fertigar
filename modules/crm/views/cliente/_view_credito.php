<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\credito\Credito;
use app\models\credito\CreditoAbono;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);
$bttUrl       = Url::to(['pagos-detail-json-btt']);
?>
<div class="creditos-credito-view">
    <div class="row">
        <div class="col-md-6" style="height: 550px;">
            <div class="ibox" >
                <div class="ibox-title">
                    <h5 >Información cliente</h5>
                </div>

                <div class="ibox-content text-center">
                    <h1><?= $model->nombreCompleto ?></h1>
                    <div class="m-b-sm">
                            <img alt="image" class="rounded-circle" src="<?= Url::to(['/img/profile-photos/5.png']) ?>">
                    </div>
                    <p class="font-bold">Tel : <?= $model->telefono_movil  ?> / <?= $model->telefono ?></p>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body text-center">
                    <?php $totales = Credito::getTotalesCredito( $model->id , Credito::TIPO_CLIENTE ) ?>
                    <div class="row text-left">
                        <div class="col">
                            <div class=" m-l-md">
                            <span class="h2 font-bold m-t block">$ <?= number_format($totales["total_credito"]) ?></span>
                            <strong class="text-muted m-b block text-center">TOTAL DE CREDITO</strong>
                            </div>
                        </div>
                        <div class="col">
                            <span class="h2 font-bold m-t block">$ <?= number_format($totales["total_por_pagar"],2) ?></span>
                            <strong class="text-muted m-b block text-center">TOTAL A PAGAR</strong>
                        </div>
                        <div class="col">
                            <span class="h2 font-bold m-t block">$ <?= number_format($totales["total_pagado"],2) ?></span>
                            <strong class="text-muted m-b block text-center">TOTAL PAGADO</strong>
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
                            <?= Html::hiddenInput('tipo', Credito::TIPO_CLIENTE ) ?>
                            <?= Html::hiddenInput('item_id', $model->id ) ?>
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
                    <h3>CREDITOS DE CLIENTE</h3>
                </div>
                <div class="ibox-content">
                    <table class="table">
                        <thead>
                            <tr class="text-center">
                                <th>ID CREDITO</th>
                                <th>VENTA</th>
                                <th>MONTO DEL CREDITO</th>
                                <th>MONTO PAGADO</th>
                                <th>MONTO A PAGAR</th>
                                <th>ESTATUS</th>
                                <th>FECHA A PAGAR</th>
                                <th>PAGOS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (Credito::getCredito(  $model->id , Credito::TIPO_CLIENTE ) as $key => $item_credito): ?>
                                <tr class="text-center" style="<?= $item_credito->status == Credito::STATUS_PAGADA ? 'background-color: #457c6a;color: #fff;' : ( $item_credito->status == Credito::STATUS_CANCEL ? 'background-color: #b75252;color: #fff;' : '')?>">
                                    <td>#<?= $item_credito->id ?></td>
                                    <?php if ($item_credito->venta_id): ?>
                                        <td><?= Html::a("#". str_pad($item_credito->venta_id,6,"0",STR_PAD_LEFT), [ "/tpv/venta/view", "id" => $item_credito->venta_id ], ["target" => "_blank", "style" => "font-size: 16px;color: #eed522;font-weight: bold;" ] ) ?>    </td>
                                    <?php else: ?>
                                        <td>
                                            <?php if ($item_credito->trans_token_venta): ?>
                                               <?= Html::a($item_credito->trans_token_venta, null, [ "class" => "text-link","data-target" => "#modal-nota-ventas", "data-toggle" => "modal", 'onclick' => 'onGetVentas(' . $item_credito->id. ')', "style" => "font-size: 16px;color: #eed522;font-weight: bold;"   ] ) ?>
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
                                    <td>
                                        <?= Html::button("<i class= 'fa fa-money'></i>",[ "class" => "btn btn-primary btn-circle ", "data-target" => "#modal-credito", "data-toggle" => "modal", 'onclick' => 'onGetOperacion(' . $item_credito->id. ')' ]) ?>
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
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">OPERACION</th>
                                    <th style="text-align: center;">ABONO</th>
                                    <th style="text-align: center;">FECHA DE PAGO</th>
                                    <th style="text-align: center;">CAJERO / EMPLEADO</th>
                                    <th style="text-align: center;">ESTATUS</th>
                                </tr>
                            </thead>
                            <tbody class="content_search" style="text-align: center;">
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
                                    <th style="text-align: center;">ESTATUS</th>
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
    var $btnShowOperacion   = $('#btnOperacionShow'),
    $contentVenta       = $('.content_venta'),
    $contentPago        = $('.content_pago'),
    $contentHtml        = $('.content_search');

    $(function(){
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
                    "<td><strong class='"+ (item_transaccion.status == 10 ? 'text-primary' : 'text-danger' ) +"'>"+ item_transaccion.status_text + "</strong></td>";
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
                    "<td><strong class='"+ (item_transaccion.status == 10 ? 'text-primary' : 'text-danger' ) +"'>"+ item_transaccion.status_text + "</strong></td>";
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


 var open_ticket = function(token_pay){
    window.open("<?= Url::to(['/creditos/credito/imprimir-credito']) ?>" + "?pay_items=" + token_pay
                    ,'imprimir', 'width=600,height=500');
 }

</script>