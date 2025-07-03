<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\venta\Venta;
use app\models\venta\VentaDetalle;
use app\models\producto\Producto;
use app\models\cobro\CobroVenta;


$this->title = "Folio #" . str_pad($model->id,6,"0",STR_PAD_LEFT);

$this->params['breadcrumbs'][] = ['label' => 'Tpv', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Venta';
$cobroTotal = 0;
?>

<div class="tpv-pre-captura-view">

    <div class="alert alert-warning">
        <h5 ><?= Venta::$statusList[$model->status] ?></h5>
    </div>
    <div class="row">
        <div class="col-md-9">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Información de cliente</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                             [
                                 'attribute' => 'CLIENTE',
                                 'format'    => 'raw',
                                 'value'     =>  isset($model->cliente->id) ?  Html::a($model->cliente->nombre ." ". $model->cliente->apellidos , ['/crm/cliente/view', 'id' => $model->cliente->id], ['class' => 'text-primary']) : ' *** PUBLICO EN GENERAL **' ,
                             ]
                        ],
                    ]) ?>
                </div>
            </div>

            <div class="panel">
                <div class="panel-body text-center">
                    <div class="row">
                        <div class="col">
                            <div class=" m-l-md">
                            <span class="h3 font-bold m-t block"> <?= number_format($model->total,2)  ?></span>
                            <small class="h5 text-muted m-b block">TOTAL VENTA</small>
                            </div>
                        </div>
                        <div class="col">
                            <span class="h3 font-bold m-t block"> <?= $model->getTotalUnidades()  ?></span>
                            <small class="h5 text-muted m-b block">UNIDADES</small>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($model->devolucion_transaccion_id): ?>
                <div class="widget style2  red-bg p-lg">
                    <div class="row">
                        <div class="col-4">
                            <i class="fa fa-warning fa-5x"></i>
                        </div>
                        <div class="col-8 text-right">
                            <span> SE GENERO UNA DEVOLUCION DE ESTA VENTA </span>
                            <h2 class="font-bold"><?= Html::a("#".$model->devolucion_transaccion_id, ["/inventario/devolucion/view", "id" => $model->devolucion_transaccion_id], ["target" => "_blank"]) ?></h2>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if ($model->is_tpv_ruta == Venta::IS_TPV_RUTA_ON ): ?>
                <div class="widget style2 lazur-bg">
                    <div class="row">
                        <div class="col-4">
                            <i class="fa fa-truck fa-5x"></i>
                        </div>
                        <div class="col-8 text-right">
                            <span> VENTA REALIZADA EN RUTA </span>
                            <h2 class="font-bold"><?= Html::a($model->sucursalVende->nombre, ["/logistica/ruta/view", "id" => $model->reparto_id], ["target" => "_blank","class" => "btn-link",]) ?> </h2>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if ( $model->reparto_id && $model->is_tpv_ruta == Venta::IS_TPV_RUTA_OFF && $model->status == Venta::STATUS_VENTA ): ?>
                <div class="widget style1 navy-bg">
                    <div class="row">
                        <div class="col-4">
                            <i class="fa fa-truck fa-5x"></i>
                        </div>
                        <div class="col-8 text-right">
                            <span> REPARTO [ <?= $model->reparto_id ?> ] </span>
                            <h2 class="font-bold"><?= $model->reparto->sucursal->nombre ?></h2>
                        </div>
                    </div>
                </div>
            <?php endif ?>


            <?php if ($model->transaccion): ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h3>TRANSACCION DEL PAGO</h3>
                </div>
                <div class="ibox-content">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center;">OPERACION</th>
                                <th style="text-align: center;">FECHA DE PAGO</th>
                                <th style="text-align: center;">CAJERO / EMPLEADO</th>

                            </tr>
                        </thead>
                        <tbody  style="text-align: center;">
                            <tbody>
                                <?php foreach ($model->transaccion as $key => $item_tra): ?>
                                    <tr>
                                        <td class="text-center">
                                            <?= Html::a($item_tra->token_pay, null, [ "class" => "text-link","data-target" => "#modal-nota-ventas", "data-toggle" => "modal", 'onclick' => 'onGetVentas(' . $item_tra->id. ')'  ] ) ?>
                                        <td class="text-center"><?= date("Y-m-d",$item_tra->created_at) ?></td>
                                        <td class="text-center"><?= $item_tra->createdBy->nombreCompleto ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h3 >Cobros realizado</h3>
                </div>
                <div class="ibox-content">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Tipo</th>
                                <th style="text-align: center;">Metodo de pago</th>
                                <th style="text-align: center;">Cobro</th>
                                <th style="text-align: center;">Monto Pago</th>
                            </tr>
                        </thead>
                        <tbody  style="text-align: center;">
                            <tbody>

                                <?php foreach ($model->cobroTpvVenta as $key => $item): ?>
                                    <tr>
                                        <td class="text-center"><?= CobroVenta::$tipoList[$item->tipo] ?></td>
                                        <td class="text-center">
                                            <?= CobroVenta::$servicioTpvList[$item->metodo_pago] ?>
                                            <?php if ($item->metodo_pago == CobroVenta::COBRO_OTRO ): ?>
                                                <p><strong style="font-size: 16px;color: #000;">CONCEPTO [ <?= $item->nota_otro ?> ]</strong></p>
                                            <?php endif ?>
                                        </td>
                                        <td class="text-center"><?= number_format($item->cantidad,2) ?></td>
                                        <td class="text-center"><?= number_format($item->cantidad_pago,2) ?></td>
                                        <?php $cobroTotal =  $cobroTotal + $item->cantidad; ?>

                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td style="font-size: 17px" class="text-right text-main text-semibold " colspan="2">TOTAL</td>
                                    <td style="font-size: 17px"><?= number_format($model->total,2) ?></td>
                                </tr>
                                <tr>
                                    <td style="font-size: 17px" class="text-right text-main text-semibold " colspan="2">PAGADO</td>
                                    <td style="font-size: 17px"><?= number_format($cobroTotal,2) ?></td>
                                </tr>
                            </tfoot>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif ?>

            <div class="ibox">
                <div class="ibox-title">
                    <h3 >Productos relacionados</h3>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-bordered invoice-summary">
                            <thead>
                                <tr class="bg-trans-dark">
                                    <th class="min-col text-center text-uppercase">ABASTECE BODEGA</th>
                                    <th class="min-col text-center text-uppercase">PRODUCTO</th>
                                    <th class="min-col text-center text-uppercase">CANTIDAD</th>
                                    <th class="min-col text-center text-uppercase">U.M</th>
                                    <th class="min-col text-center text-uppercase">COSTO</th>
                                    <th class="min-col text-center text-uppercase">REPARTO</th>
                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
                                <?php foreach ($model->ventaDetalle as $key => $item): ?>
                                    <tr>
                                        <td><?=  $item->apply_bodega == VentaDetalle::APPLY_BODEGA_ON ? '<i class="fa fa-check" style="font-size:18px; color:#87a44d"></i>' : '<i class="fa fa-close" style="font-size:18px; color:#000"></i>' ?></td>
                                        <td><?= Html::a( $item->producto->nombre . "[" . $item->producto->clave . "]" , ["/inventario/arqueo-inventario/view", "id" => $item->producto_id ], ["class" => "", "target" => "_blank"])  ?> </td>
                                        <td><?= $item->cantidad  ?>        </td>
                                        <td><?= $item->producto->unidadMedida->nombre ?? '--'  ?> </td>
                                        <td><?= $item->precio_venta ? number_format($item->precio_venta,2) : 0 ?> </td>
                                        <td>
                                            <?php if ($item->repartoAdd): ?>
                                                <?php foreach ($item->repartoAdd as $key => $reparto): ?>
                                                     <li><a href="<?= Url::to([ '/logistica/ruta/view', 'id' => $reparto->reparto_id ]) ?>"><?=  '['. $reparto->reparto_id . ' - '. date("Y-m-d", $reparto->created_at) .']'  ?></a></li>
                                                <?php endforeach ?>
                                            <?php else: ?>
                                                <?php if ($model->reparto_id && $model->status == Venta::STATUS_VENTA): ?>
                                                    *VENTA EN REPARTO*
                                                <?php else: ?>
                                                    N/A
                                                <?php endif ?>
                                            <?php endif ?>
                                        </td>
                                    </tr>

                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel" style="background-color: #cbb70e;color: #fff;">
                <div class="panel-body text-center ">
                    <div class="row">
                        <div class="col">
                            <div class=" m-l-md">
                                <span class="h5 font-bold m-t block"> <i class="fa fa-cubes"></i> <?= $model->sucursalVende->nombre  ?></span>
                                <small class="  block">VENTA</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($model->ruta_sucursal_id): ?>
                <div class="panel panel-warning">
                    <div class="panel-body text-center">
                        <div class="row">
                            <div class="col">
                                <div class=" m-l-md">
                                <span class="h5 font-bold m-t block"> <?= $model->sucursal->nombre ?></span>
                                <small class="text-muted m-b block">RUTA QUE ENTREGA</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <iframe width="100%" class="panel" height="500px" src="<?= Url::to(['imprimir-ticket', 'id' => $model->id ])  ?>"></iframe>

            <div class="panel">
                <?= Html::a('Imprimir Ticket', false, ['class' => 'btn btn-warning btn-lg btn-block', 'id' => 'imprimir-ticket','style'=>'    padding: 6%;'])?>
            </div>

            <?php if ($model->transaccion): ?>
            <div class="panel">
                <?= Html::a('TICKET DE ENTREGA', false, ['class' => 'btn btn-info btn-lg btn-block', 'id' => 'imprimir-ticket-entrega','style'=>'    padding: 6%;'])?>
            </div>
            <?php endif ?>

            <?php if ($can["cancel"]): ?>
                <?php if (count($model->cobroTpvVenta) > 0 || count($model->transaccion) > 0 ): ?>
                    <?php if ($model->status == Venta::STATUS_VENTA && !Venta::isVentaRuta($model->id)): ?>
                        <div class="panel">
                            <?= Html::button('CANCELAR VENTA',  ['class' => 'btn btn-danger btn-lg btn-block', "data-target" => "#modal-cancelacion", "data-toggle" => "modal" ,'style'=>'    padding: 6%;'])?>
                        </div>
                    <?php elseif($model->status == Venta::STATUS_VENTA && Venta::isVentaRuta($model->id)): ?>
                        <div class="panel">
                            <?= Html::button('CANCELAR VENTA - RUTA',  [ 'id' => 'btnShowCancelVentaMultiple', 'class' => 'btn btn-danger btn-lg btn-block', "data-target" => "#modal-cancelacion-multiple", "data-toggle" => "modal" ,'style'=>'    padding: 6%;'])?>
                        </div>
                    <?php endif ?>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <h2>SIN LIQUIDACION</h2>
                    </div>

                <?php endif ?>
            <?php endif ?>

            <?php if (Venta::isPagoCredito($model->id)): ?>
                <div class="ibox">
                    <?= Html::a('<i class="fa fa-pencil-square-o mar-rgt-5px"></i> PAGARE',null,['id' => 'reporte_download_acuse','class' => 'btn btn-lg btn-block', 'style'=>'padding: 6%;font-size: 24px; background: #4c0ba7; color: #fff' ])?>
                </div>
            <?php endif ?>

            <div class="panel panel-info">
                <div class="ibox-title">
                    <h5 ><?= Venta::$tipoList[$model->tipo] ?></h5>
                </div>
            </div>


            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>




<?= Html::a("Nueva venta",[ '/tpv/venta/pre-venta' ],["class" => "btn", "style" => "position: fixed;
    top: 0;
    right: 5%;
    font-size: 15px;
    z-index: 100000;
    border-radius: 50%;
    width: 100px;
    height: 100px;
    background: #8c45ce;
    color: #ffffff;
    font-weight: 800;
    box-shadow: 3px 5px 5px black;
    padding: 25px;
    border-color: #ab30e0;"]) ?>



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
                        <div class="div_cobro">

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

<script>

var $reporte_download_acuse         = $('#reporte_download_acuse'),
    venta_id                        = <?= $model->id ?>;

$(function(){
    $('body').addClass('mini-navbar');
});



$('#imprimir-ticket').click(function(event){
    event.preventDefault();
    window.open("<?= Url::to(['imprimir-ticket', 'id' => $model->id ])  ?>",
    'imprimir',
    'width=600,height=500');
});

$('#imprimir-ticket-entrega').click(function(event){
    event.preventDefault();
    window.open("<?= Url::to(['imprimir-ticket-entrega', 'id' => $model->id ])  ?>",
    'imprimir',
    'width=600,height=500');
});


$reporte_download_acuse.click(function(event){
    event.preventDefault();
    window.open('<?= Url::to(['imprimir-acuse-pdf']) ?>?venta_id=' + venta_id,
        'imprimir',
        'width=600,height=600');
});

var onGetVentas = function( $operacion_id ){
    $contentVenta.html(null);
    $.get('<?= Url::to(["get-token-ventas"]) ?>',{ operacion_id : $operacion_id },function($response){
        if ($response.code == 202 ) {
            $tempHtml = "";
            $.each($response.ventas,function(key, item_venta){
                $tempHtml += "<tr>"+
                    '<td><a href="<?= Url::to(['/tpv/venta/view']) ?>?id='+ item_venta.id +'" target= "_blank">'+item_venta.folio +'</a></td>'+
                    "<td>"+ btf.conta.money(item_venta.total) +"</td>"+
                    "<td>"+item_venta.sucursal +"</td>"+
                    "<td>"+item_venta.empleado   +"</td>"+
                    "<td>"+item_venta.created_at +"</td>";

                $tempHtml += "</tr>";
            });
            $contentVenta.html($tempHtml);

            $tempcobroHtml = "";
            $.each($response.cobro,function(key, item_cobro){
                $tempcobroHtml += "<h3>PAGOS REGISTRADOS</h3><div class= 'row'>"+
                    "<div class='col-sm-6 text-center'><h2>"+item_cobro.metodo_pago_text +"</h2></div>"+
                    "<div class='col-sm-6 text-center'><h2>"+ btf.conta.money(item_cobro.cantidad) +"</h2></div>"+
                "</div>";
            });
            $('.div_cobro').html($tempcobroHtml);
        }
    },'json');
}

</script>
<?= $this->render('modal_cancelacion',[ "model"   => $model ]) ?>
