<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Esys;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use yii\widgets\ActiveForm;
use app\models\ruta\Ruta;
use app\models\venta\Venta;
use app\models\reparto\Reparto;
use app\models\sucursal\Sucursal;
use app\models\venta\VentaDetalle;
use app\models\reparto\RepartoDetalle;
use app\models\producto\Producto;
use app\models\inv\InventarioOperacion;

$this->title = "Carga # : " . $model->id;

$this->params['breadcrumbs'][] = ['label' => 'Ruta', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>
<style>
.modal-backdrop{
    z-index: 1040 !important;
}

.modal {
    z-index: 1050 !important;;
}
</style>

<p>
<?= $can['delete'] && $model->status == Reparto::STATUS_PROCESO ?
Html::a('Eliminar', ['delete', 'id' => $model->id], [
    'class' => 'btn btn-danger',
    'data' => [
        'confirm' => '¿Estás seguro de que deseas eliminar esta reparto?',
        'method' => 'post',
    ],
]): '' ?>
</p>


<div class="alert alert-info">
    <strong><?= Reparto::$statusList[$model->status] ?></strong>
</div>

<div class="logistica-reparto-view">
    <div class="row">
        <div class="col-md-9">

            <div class="row">
                <div class="col-sm-6">
                    <div class="ibox">
                        <div class="ibox-content text-center">
                             <h2><?= date("Y-m-d h:i a", $model->created_at) ?></h2>
                             <strong>FECHA [INICIO DE REPARTO]</strong>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="ibox">
                        <div class="ibox-content text-center">
                            <?php if ( $model->cierre_reparto): ?>
                                <h2><?= date("Y-m-d h:i a", $model->cierre_reparto) ?></h2>
                            <?php else: ?>
                                <h2>SIN CIERRE</h2>
                            <?php endif ?>
                            <strong>FECHA [CIRRE DE REPARTO]</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Información de RUTA / SUCURSAL </h5>
                        </div>
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                     [
                                         'attribute' => 'SUCURSAL',
                                         'format'    => 'raw',
                                         'value'     =>  isset($model->sucursal->id) ?  Html::a($model->sucursal->nombre , ['/sucursales/sucursal/view', 'id' => $model->sucursal->id], ['class' => 'text-primary']) : '' ,
                                     ]
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <?php if (Yii::$app->user->id == 7 || Yii::$app->user->can('admin')): ?>
                        <?= Html::a('<i class="fa fa-handshake-o mar-rgt-5px float-left"></i> LIQUIDACION DE REPARTO',null,['onclick' => 'getLiquidacionReparto()','class' => 'btn btn-danger btn-lg btn-block', 'style'=>'padding: 6%;font-size: 24px;box-shadow: 3px 5px 5px black;' ])?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="panel " style="background: #ed5565;color: #ffff;">
                        <div class="pad-all text-center">
                            <strong style="font-size: 24px"><?= Reparto::getTotalProductoLiquidacion($model->id) ?></strong>
                            <p style="font-size: 16px">LIQUIDACION PRODUCTO VENDIDO</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="panel" style="background: #ed5565;color: #ffff;">
                        <div class="pad-all text-center">
                            <strong style="font-size: 24px">$<?= number_format(Reparto::getTotalCobroLiquidacion($model->id)) ?></strong>
                            <p style="font-size: 16px">LIQUIDACION SALDO</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="panel">
                        <div class="pad-all text-center">
                            <strong style="font-size: 24px"><?= Reparto::getCarga($model->id) ?></strong>
                            <p style="font-size: 16px"># CARGA TOTAL</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="panel">
                        <div class="pad-all text-center">
                            <strong style="font-size: 24px"><?= Reparto::getPreventa($model->id) ?></strong>
                            <p style="font-size: 16px"># TOTAL PRECAPTURA</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="panel">
                        <div class="pad-all text-center">
                            <strong style="font-size: 24px"><?= Reparto::getTaraAbierta($model->id) ?></strong>
                            <p style="font-size: 16px"># TOTAL DE TARA ABIERTA</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="panel">
                        <div class="pad-all text-center">
                            <strong style="font-size: 24px"><?= Reparto::getDevolucion($model->id) ?></strong>
                            <p style="font-size: 16px"># DEVOLUCIONES</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="panel">
                        <div class="pad-all text-center">
                            <strong style="font-size: 24px"><?= Reparto::getRecoleccion($model->id) ?></strong>
                            <p style="font-size: 16px"># RECOLECCION</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="panel">
                        <div class="pad-all text-center text-danger">
                            <strong style="font-size: 24px"><?= round(Reparto::getFaltante($model->id),2) ?></strong>
                            <p style="font-size: 16px"># PRODUCTO FALTANTE</p>
                        </div>
                    </div>
                </div>
            </div>


            <div class="tabs-container">
                <ul class="nav nav-tabs" role="tablist">
                    <li>
                        <a class="nav-link active" data-toggle="tab" href="#tab-carga">CARGA DE PRODUCTOS</a>
                    </li>
                    <li>
                        <a class="nav-link" data-toggle="tab" href="#tab-ventas">VENTAS</a>
                    </li>
                    <li>
                        <a class="nav-link" data-toggle="tab" href="#tab-devolucion">DEVOLUCIONES</a>
                    </li>
                    <li>
                        <a class="nav-link" data-toggle="tab" href="#tab-inventario-actual">INVENTARIO ACTUAL [ <?= $model->sucursal->nombre ?> ]</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" id="tab-carga" class="tab-pane active">
                        <div class="ibox">
                            <div class="ibox-content nano" style="height: 500px;padding: 0; overflow: scroll;">
                                <div class="nano-content">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center;">#</th>
                                                <th style="text-align: center;">VENTA #ID</th>
                                                <th style="text-align: center;">PRODUCTO #</th>
                                                <th style="text-align: center;">CLIENTE</th>
                                                <th style="text-align: center;">CANTIDA PREVENTA </th>
                                                <th style="text-align: center;">CANTIDAD CARGADA</th>
                                                <th style="text-align: center;">TOTAL</th>
                                                <th style="text-align: center;">PROCESO</th>
                                                <th style="text-align: center;">METODO DE PAGO</th>
                                            </tr>
                                        </thead>
                                        <tbody  style="text-align: center;">
                                            <?php foreach (Reparto::getPreventaInfoAll($model->id) as $key => $item): ?>
                                                <tr>
                                                    <td><?= ($key + 1) ?></td>
                                                    <td>
                                                    <?php if ($item["status"] == Venta::STATUS_VENTA): ?>
                                                        <?= Html::a("#". str_pad($item["id"],6,"0",STR_PAD_LEFT), [ "/tpv/venta/view", "id" =>$item["id"] ] ) ?>
                                                    <?php else: ?>
                                                        <?= Html::a("#". str_pad($item["id"],6,"0",STR_PAD_LEFT), [ "/tpv/pre-captura/view", "id" =>$item["id"] ] ) ?>
                                                    <?php endif ?>
                                                    </td>
                                                    <td> -- </td>
                                                    <td><?=   $item["cliente"] ? $item["cliente"]  : '***PUBLICO EN GENERAL***'  ?></td>
                                                    <td><?=   $item["productos"]  ?>     </td>
                                                    <td><?=   $item["productos_carga"]  ?>     </td>
                                                    <td><?=   number_format($item["total"],2)  ?>     </td>
                                                    <td class="<?= $item["status"] == Venta::STATUS_VENTA ? 'text-warning font-bold' : 'text-danger' ?>">
                                                        <?php if ($item["status"] == Venta::STATUS_VENTA): ?>
                                                            <?php if ($item["reparto_id"] == $model->id ): ?>
                                                                ** VENDIDO **
                                                            <?php else: ?>
                                                                ***  VENDIDO EN OTRO REPARTO ***
                                                            <?php endif ?>
                                                        <?php endif ?>
                                                        <?php if ($item["status"] == Venta::STATUS_PROCESO): ?>
                                                            ** PROCESO **
                                                        <?php else: ?>
                                                            <?php if (($item["status"] == Venta::STATUS_PRECAPTURA || $item["status"] == Venta::STATUS_PREVENTA) && $model->status == Reparto::STATUS_RUTA ): ?>
                                                                <?= Html::a('CARGAR',[ "add-reparto","venta_id" => $item["id"], "id" => $model->id ], [ "class" => "btn btn-primary"]) ?>
                                                            <?php endif ?>
                                                        <?php endif ?>

                                                         <?php if ($item["status"] == Venta::STATUS_CANCEL): ?>
                                                            CANCELADO
                                                        <?php endif ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($item["status"] == Venta::STATUS_VENTA): ?>
                                                            <?= Html::a("METODO PAGO", false, [ "class" => "", "onclick" => "onGetMetodoPago(". $item["id"] .")" ]) ?>
                                                        <?php endif ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>

                                            <?php foreach ($model->repartoDetalles as $key => $item): ?>

                                                <?php if ($item->tipo != RepartoDetalle::TIPO_PRECAPTURA): ?>
                                                    <?php
                                                if($item->ventaDetalle!=""){
                                                    $ventaDetalle = $item->ventaDetalle;
                                                    $venta = $ventaDetalle->venta;
                                                    if($venta!=""){
                                                        $cliente = $venta->cliente;
                                                        $cliente=$cliente->nombre;
                                                    }else{
                                                        $cliente='N / A';
                                                    }
                                                }else{
                                                    $cliente='N / A';
                                                }
                                                    ?>
                                                    <tr>
                                                        <td><?= ($key + 1) ?></td>
                                                        <td> ** PRODUCTO EXTRA ** </td>
                                                        <td><?=   $item->producto->nombre  ?>  </td>
                                                        <td><?=   $cliente  ?>   </td>
                                                        <td><?=   $item->cantidad  ?>     </td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                <?php endif ?>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="tab-ventas"  role="tabpanel" class="tab-pane">
                        <div class="ibox">
                            <div class="ibox-content nano" style="height: 500px;padding: 0; overflow: scroll;">
                                <div class="nano-content">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center;">#</th>
                                                <th style="text-align: center;">VENTA #ID</th>
                                                <th style="text-align: center;">PRODUCTO #</th>
                                                <th style="text-align: center;">CLIENTE</th>
                                                <th style="text-align: center;">CANTIDAD</th>
                                                <th style="text-align: center;">PRECIO</th>
                                                <th style="text-align: center;">FECHA</th>
                                                <th style="text-align: center;">ESTATUS</th>
                                                <th style="text-align: center;">METODO DE PAGO</th>
                                            </tr>
                                        </thead>
                                        <tbody  style="text-align: center;">
                                            <?php foreach (VentaDetalle::getVentaRuta($model->id) as $key => $item): ?>
                                                <tr>
                                                    <td><?= ($key + 1) ?></td>
                                                    <td><?= Html::a("#". str_pad($item->venta_id,6,"0",STR_PAD_LEFT), [ "/tpv/venta/view", "id" => $item->venta_id ], ["target" => "_blank"] ) ?>    </td>
                                                    <td><?=  $item->producto->nombre  ?>  </td>
                                                    <td><?=   isset($item->venta->cliente_id ) ? $item->venta->cliente->nombreCompleto : 'N/A'  ?></td>
                                                    <td><?=   $item->cantidad  ?>     </td>
                                                    <td>$<?= number_format($item->precio_venta * $item->cantidad,2) ?></td>
                                                    <td><?= date("Y-m-d h:m:s",$item->created_at) ?></td>
                                                    <td class="<?= $item->venta->status == Venta::STATUS_VENTA ? 'text-warning font-bold' : 'text-danger' ?>">
                                                        <?php if ($item->venta->status == Venta::STATUS_VENTA): ?>
                                                            ** VENDIDO **
                                                        <?php endif ?>
                                                        <?php if ($item->venta->status == Venta::STATUS_CANCEL): ?>
                                                            ** CANCELADO **
                                                        <?php endif ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($item->venta->status == Venta::STATUS_VENTA): ?>
                                                            <?= Html::a("METODO PAGO", false, [ "class" => "", "onclick" => "onGetMetodoPago(". $item->venta_id .")" ]) ?>
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
                    <div id="tab-devolucion"  role="tabpanel" class="tab-pane">
                        <div class="ibox">
                            <div class="ibox-content nano" style="height: 500px;padding: 0; overflow: scroll;">
                                <div class="nano-content">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center;">#</th>
                                                <th style="text-align: center;">VENTA #ID</th>
                                                <th style="text-align: center;">PRODUCTO #</th>
                                                <th style="text-align: center;">CLIENTE</th>
                                                <th style="text-align: center;">CANTIDAD</th>
                                                <th style="text-align: center;">PRECIO VENTA [UNITARIO]</th>
                                                <th style="text-align: center;">FECHA</th>
                                            </tr>
                                        </thead>
                                        <tbody  style="text-align: center;">
                                            <?php $count = 0; ?>
                                            <?php foreach (Reparto::getDevolucionVenta($model->id) as $key => $operacionItem): ?>
                                                <?php foreach ($operacionItem->operacionDetalles as $key2 => $detalleItem): ?>
                                                    <?php $count++; ?>
                                                    <tr>
                                                        <td><?= $count ?></td>
                                                        <td><?= Html::a("#". str_pad($detalleItem->ventaDetalle->venta_id ,6,"0",STR_PAD_LEFT), [ "/tpv/venta/view", "id" => $detalleItem->ventaDetalle->venta_id  ], ["target" => "_blank"] ) ?>    </td>
                                                        <td><?=  $detalleItem->ventaDetalle->producto->nombre  ?>  </td>
                                                        <td><?=   isset($detalleItem->ventaDetalle->venta->cliente_id ) ? $detalleItem->ventaDetalle->venta->cliente->nombreCompleto : 'N/A'  ?></td>
                                                        <td><?=   $detalleItem->ventaDetalle->cantidad  ?>     </td>
                                                        <td>$<?= number_format($detalleItem->ventaDetalle->precio_venta,2) ?></td>
                                                        <td><?= date("Y-m-d h:m:s",$detalleItem->operacion->created_at) ?></td>
                                                    </tr>
                                                <?php endforeach ?>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="tab-inventario-actual"  role="tabpanel" class="tab-pane">
                        <div class="ibox">
                            <div class="ibox-content nano" style="height: 500px;padding: 0; overflow: scroll;">
                                <div class="nano-content">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center;">#</th>
                                                <th style="text-align: center;">PRODUCTO #</th>
                                                <th style="text-align: center;">CANTIDAD</th>
                                            </tr>
                                        </thead>
                                        <tbody  style="text-align: center;">
                                            <?php $count = 0; ?>
                                            <?php foreach (InventarioOperacion::getProductoInventario($model->sucursal_id) as $key => $item_producto): ?>
                                                <?php if ($item_producto->cantidad > 0 ): ?>
                                                    <?php $count++; ?>
                                                    <tr>
                                                        <td><?= $count ?></td>
                                                        <td class="text-left"><h4><?= $item_producto->producto->nombre ?></h4></td>
                                                        <td class="text-right"><h3><?= $item_producto->cantidad  ?> [<?= Producto::$medidaList[$item_producto->producto->tipo_medida] ?>]</h3></td>
                                                    </tr>
                                                <?php endif ?>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-md-3">
                <?php /* ?>
                <?php if ($model->status == Reparto::STATUS_PROCESO): ?>
                    <div class="panel">
                        <?= Html::a('ENVIAR A RUTA', ['enviar-ruta-traspaso', 'id' => $model->id ], ['class' => 'btn btn-warning btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas enviar a RUTA?'] ])?>
                    </div>
                <?php endif ?>
                */?>
                <?php /* ?>
                <?php if ($model->status == Reparto::STATUS_RUTA): ?>
                    <div class="panel">
                        <?= Html::a('Terminar / Concluir REPARTO',['set-status-reparto', 'id' => $model->id, 'status' => Reparto::STATUS_TERMINADO ],  ['class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas Terminar el reparto?'] ])?>
                    </div>
                <?php endif ?>
                */?>

                <div class="ibox">
                    <div class="ibox-content text-center text-primary">
                        <div class="row">
                            <div class="col-sm-6">
                                <h2><?= Reparto::getPzVendidas($model->id) ?></h2>
                                <strong>PZ VENDIDAS</strong>
                            </div>
                            <div class="col-sm-6">
                                 <h2>$<?= number_format(Reparto::getTotalVendido($model->id),2) ?></h2>
                                <strong>TOTAL VENDIDO</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel">

                    <?php if (Yii::$app->user->can('ENCARGADO CEDIS') || Yii::$app->user->can('admin')): ?>
                        <?php if ($model->status == Reparto::STATUS_PROCESO): ?>
                            <?= Html::a('<i class="fa fa-truck mar-rgt-5px float-left"></i> ENVIAR REPARTO',["enviar-reparto", "id" => $model->id], ['class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;font-size: 24px;background: #fb9e06;border-color: #fb9e06' ])?>
                        <?php endif ?>

                        <?php if ($model->status == Reparto::STATUS_RUTA): ?>
                            <?= Html::a('<i class="fa  fa-plus-square-o mar-rgt-5px float-left"></i> ABRIR REPARTO',["abrir-reparto", "id" => $model->id],['class' => 'btn btn-warning btn-lg btn-block', 'style'=>'padding: 6%;font-size: 24px;' ])?>
                        <?php endif ?>
                    <?php endif ?>
                    
                    <?php if (Yii::$app->user->id == 7 || Yii::$app->user->can('admin')): ?>
                        <?= Html::a('<i class="fa fa-file-pdf-o mar-rgt-5px float-left"></i> ACUSE',null,['id' => 'reporte_download_acuse','class' => 'btn btn-danger btn-lg btn-block', 'style'=>'padding: 6%;font-size: 24px;' ])?>
                    <?php endif ?>

                    <?php if (Yii::$app->user->id == 7 || Yii::$app->user->can('admin')): ?>
                        <?= Html::a('<i class="fa fa-file-pdf-o mar-rgt-5px float-left"></i> SALDOS',null,['id' => 'reporte_download_saldo','class' => 'btn btn-default btn-lg btn-block', 'style'=>'padding: 6%;font-size: 24px; background: #268627; color: #fff' ])?>
                    <?php endif ?>

                    <?= Html::a('<i class="fa fa-file-pdf-o mar-rgt-5px float-left"></i> INVENTARIO',null,['id' => 'reporte_download_inventario','class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;font-size: 24px;' ])?>

                    <?php if ($model->status == Reparto::STATUS_PROCESO || $model->status == Reparto::STATUS_RUTA): ?>
                        <?= Html::a('<i class="fa fa-plus mar-rgt-5px float-left"></i> PRODUCTO ',null,['id' => 'btn_add_producto','class' => 'btn btn-success btn-lg btn-block', 'style'=>'padding: 6%;font-size: 24px;', 'data-target' => "#modal-add-producto", 'data-toggle' =>"modal" ])?>
                    <?php endif ?>



                    <?php if (Yii::$app->user->id == 7 || Yii::$app->user->can('admin')): ?>
                        <?= Html::a('<i class="fa fa-money mar-rgt-5px float-left"></i> CUENTA',null,['id' => 'reporte_download_cuenta','class' => 'btn btn-lg btn-block', 'style'=>'padding: 6%;font-size: 24px; background: #b29615; color: #fff' ])?>
                    <?php endif ?>
                    
                    <?php if (Yii::$app->user->id == 7 || Yii::$app->user->can('admin')): ?>
                        <?= Html::a('<i class="fa fa-pencil-square-o mar-rgt-5px float-left"></i> PAGARE',null,['id' => 'reporte_download_pagare','class' => 'btn btn-lg btn-block', 'style'=>'padding: 6%;font-size: 24px; background: #4c0ba7; color: #fff' ])?>
                    <?php endif ?>
                    
                    <?php /* ?>
                    <?php if (Yii::$app->user->can('ENCARGADO CEDIS') || Yii::$app->user->can('admin')): ?>
                        <?= $model->status == Reparto::STATUS_TERMINADO ? Html::a('HABILITAR REPARTO', ['habilitar-reparto', "id" => $model->id], ['class' => 'btn btn-warning add  btn-lg btn-block',  'data' => [
                            'confirm' => '¿Estás seguro de que deseas habilitar el reparto ?',
                            'method' => 'post',
                        ], 'style'=>'padding: 6%;font-size: 24px;' ]) : '' ?>
                    <?php endif ?>
                    */?>
                </div>


            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>

<div class="fade modal inmodal " id="modal-add-producto"  role="dialog" aria-labelledby="modal-create-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> AGREGAR PRECAPTURA / PRODUCTO</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <?php $cobro = ActiveForm::begin(['id' => 'form-ajuste-ruta','action' => 'save-ajuste-ruta' ]) ?>

                <?= Html::hiddenInput('Carga[ruta_id]', $model->id) ?>
                <?= Html::hiddenInput('Carga[tipo]', null, ["id" => "inputAjusteTipo"]) ?>
                <?= Html::hiddenInput('Carga[inputArrayItems]', null, ["id" => "inputArrayItems"]) ?>
                <?= Html::hiddenInput('Carga[input_producto_array]', null, [ "id" => "inputCargaProductoArray" ]) ?>

                <div class="alert alert-aviso-ajuste" style="display:none">
                    <strong>Aviso: <strong class="text-message"></strong></strong>
                </div>
                <div class="ibox ">
                    <div class="ibox-content">
                        <?php $mySucursal = Sucursal::getMySucursal(); ?>
                        <strong class="text-danger h2">INVENTARIO A TOMAR EL PRODUCTO :[<?= isset($mySucursal->id) ? $mySucursal->nombre : '** NO TIENE ASIGNADA **'  ?>].</strong>
                        <div class="row" style="margin: 5%;">
                            <div class="col-sm-6">
                                <?= Html::Button("PRODUCTO",[ "class" => "btn btn-success btn-lg btn-block", "id" => "btnAddProducto"]) ?>
                            </div>
                            <div class="col-sm-6">
                                <?= Html::Button("PRECAPTURA",[ "class" => "btn btn-primary btn-lg btn-block", "id" => "btnAddPrecaptura"]) ?>
                            </div>
                        </div>
                        <div class="divContentPrecaptura" style="display: none;">
                            <strong class="text-danger h5">PREVENTAS A CARGAR DE : [<?= isset($mySucursal->id) ? $mySucursal->nombre : '** NO TIENE ASIGNADA **'  ?>].</strong>
                            <div style="overflow-y:none; overflow-x:auto;">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">PREVENTA DE [SUCURSAL]</th>
                                            <th class="text-center">FOLIO DE VENTA</th>
                                            <th class="text-center">CLIENTE</th>
                                            <th class="text-center">VENDEDOR</th>
                                            <th class="text-center">TOTAL</th>
                                            <th class="text-center">FECHA</th>
                                            <th class="text-center">ACCIÓN</th>
                                        </tr>
                                    </thead>
                                    <tbody class="container_precaptura">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="divContentProducto" style="display:none">
                            <h2>TARA ABIERTA</h2>
                            <div class="row">
                                <div class="col-sm-12">
                                    <p><strong>PRODUCTO</strong></p>
                                    <?= Select2::widget([
                                        'id' => 'carga-producto_id',
                                        'name' => 'Carga[producto_id]',
                                        'data' => [],
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                            'minimumInputLength' => 3,
                                            'multiple'    => true,
                                            'language'   => [
                                                'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                            ],
                                            'ajax' => [
                                                'url'      => Url::to(['producto-ajax']),
                                                'dataType' => 'json',
                                                'cache'    => true,
                                                'processResults' => new JsExpression('function(data, params){  return {results: data} }'),
                                            ],

                                        ],
                                        'options' => [
                                            'placeholder' => 'Buscar producto',
                                            'style' => 'border-color: red;border-style: solid;border-width: 1px;'
                                        ],
                                    ]) ?>
                                </div>

                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>PRODUCTO</th>
                                            <th class="text-center">INVENTARIO DISPONIBLE</th>
                                            <th class="text-center">CANTIDAD</th>
                                        </tr>
                                    </thead>
                                    <tbody class="container_productos">

                                    </tbody>
                                </table>

                                <?php /* ?>
                                <div class="col-sm-6">
                                    <p><strong>CANTIDAD</strong></p>
                                    <?= Html::input("number","Carga[cantidad]",false,["class" => "form-control text-center", 'id' => 'carga-cantidad']) ?>
                                </div>*/?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Agregar', ['class' => 'btn btn-primary', "id" => "btnAjusteRuta", 'disabled' => isset($mySucursal->id) ? false : true ]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<div class="fade modal inmodal " id="modal-nota-ventas"   role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">RESUMEN DE PAGO</h4>
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

<div class="fade modal inmodal " id="modal-liquidacion"  role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" style="max-width: 80%;">
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">LIQUIDACION DE REPARTO</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="ibox">
                    <div class="ibox-content">
                        <p style="font-size:14px; font-weight:700; color: #000;">VENTAS POR CONCILIAR</p>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">FOLIO VENTA</th>
                                    <th style="text-align: center;">TIPO DE VENTA</th>
                                    <th style="text-align: center;">CLIENTE</th>
                                    <th style="text-align: center;">PRODUCTOS</th>
                                    <th style="text-align: center;">TOTAL</th>
                                    <th style="text-align: center;">FECHA</th>
                                </tr>
                            </thead>
                            <tbody class="content_liquidacion_venta" style="text-align: center;">
                            </tbody>
                        </table>
                        <p style="font-size:14px; font-weight:700; color: #000;">COBROS POR CONCILIAR</p>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">PERTENECE</th>
                                    <th style="text-align: center;">METODO DE PAGO</th>
                                    <th style="text-align: center;">CANTIDAD</th>
                                    <th style="text-align: center;">FECHA</th>
                                </tr>
                            </thead>
                            <tbody class="content_liquidacion_cobro" style="text-align: center;">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::Button('CARGAR LIQUIDACION DE REPARTO', ['class' => 'btn btn-primary btn-lg', "id" => "btnLiquidacionReparto" ]) ?>
            </div>
        </div>
    </div>
</div>

<div class="display-none">
    <table>
        <tbody class="template_container">
            <tr id = "item_id_{{venta_id}}">
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_count"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_pertenece", "style" => "font-weight:bold; font-size:16px" ]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_venta_id"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_cliente"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_vendedor"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_total"]) ?></td>
                <td class="text-center"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_fecha"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    var $reporte_download_acuse         = $('#reporte_download_acuse'),
        $reporte_download_saldo         = $('#reporte_download_saldo'),
        $reporte_download_pagare         = $('#reporte_download_pagare'),
        $reporte_download_cuenta         = $('#reporte_download_cuenta'),
        $reporte_download_inventario    = $('#reporte_download_inventario'),
        $inputCargaProductoArray        = $('#inputCargaProductoArray'),
        $btnAddPrecaptura               = $('#btnAddPrecaptura'),
        $btnAddProducto                 = $('#btnAddProducto'),
        $template_container             = $('.template_container'),
        $container_precaptura           = $('.container_precaptura'),
        $divContentPrecaptura           = $('.divContentPrecaptura'),
        $divContentProducto             = $('.divContentProducto'),
        $btn_add_producto               = $('#btn_add_producto'),
        $inputProducto                  = $('#carga-producto_id'),
        $inputCantidad                  = $('#carga-cantidad'),
        $inputAjusteTipo                = $('#inputAjusteTipo'),
        $btnAjusteRuta                  = $('#btnAjusteRuta'),
        $containerProductos             = $('.container_productos'),
        $inputArrayItems                = $('#inputArrayItems'),
        $inputProductoSelect            = $('#carga-producto_id'),
        $modalNotaVenta                 = $('#modal-nota-ventas'),
        $modalLiquidacion               = $('#modal-liquidacion'),
        $contentVenta                   = $('.content_venta');
        $btnLiquidacionReparto          = $('#btnLiquidacionReparto');
        containerArray                  = [],
        cargaProductoListArray  = [];
        VAR_TIPO_PRODUCTO               = <?= RepartoDetalle::TIPO_PRODUCTO ?>,
        VAR_TIPO_PRECAPTURA             = <?= RepartoDetalle::TIPO_PRECAPTURA ?> ,
        VAR_SUCURSAL                    = <?= $model->sucursal_id ?>;
        pathUrl                         = "<?= Url::to(['/']) ?>";

        set_reparto_id = <?= $model->id ?>;

$reporte_download_acuse.click(function(event){
    event.preventDefault();
    window.open('<?= Url::to(['imprimir-acuse-pdf']) ?>?reparto_id=' + set_reparto_id,
        'imprimir',
        'width=600,height=600');
});

$reporte_download_saldo.click(function(event){
    event.preventDefault();
    window.open('<?= Url::to(['imprimir-saldo-pdf']) ?>?reparto_id=' + set_reparto_id,
        'imprimir',
        'width=600,height=600');
});

$reporte_download_pagare.click(function(event){
    event.preventDefault();
    window.open('<?= Url::to(['imprimir-pagare-pdf']) ?>?reparto_id=' + set_reparto_id,
        'imprimir',
        'width=600,height=600');
});

$reporte_download_cuenta.click(function(event){
    event.preventDefault();
    window.open('<?= Url::to(['imprimir-cuenta-pdf']) ?>?reparto_id=' + set_reparto_id,
        'imprimir',
        'width=600,height=600');
});

$reporte_download_inventario.click(function(event){
    event.preventDefault();
    window.location = '<?= Url::to('ruta-reporte-inventario') ?>?reparto_id='+set_reparto_id;
});


var getLiquidacionReparto = function(){
    $modalLiquidacion.modal('show');

    load_liquidacion_venta();
    load_liquidacion_cobro();
}

var load_liquidacion_venta = function()
{
    $('.content_liquidacion_venta').html(null);
    contentHtmlVenta = '';
    $.get(pathUrl + "logistica/ruta/get-liquidacion-venta", { reparto_id : set_reparto_id }, function($responseVenta){
        if ($responseVenta.code == 202 ) {
            $.each($responseVenta.temp_venta, function(key, temp_venta){
                contentHtmlVenta += "<tr style='background: "+( temp_venta.is_apply == 20 ? '#594b0b;color:#fff' : '#a4a4a4' )+"'>"+
                    "<td><p>"+  temp_venta.venta_id+"</p></td>"+
                    "<td><p>"+  temp_venta.tipo+"</p></td>"+
                    "<td><p style='font-size:16px;font-weight:700; color:#000'>"+  temp_venta.cliente+"</p></td>"+
                    "<td style='text-align: left;'><ul>"+ temp_venta.productos+"</ul></td>"+
                    "<td><p style='font-size:16px;font-weight:700; color:#000'>"+  btf.conta.money(temp_venta.total) +"</p></td>"+
                    "<td><p style='font-size:16px;font-weight:700; color:#000'>"+  temp_venta.created_at+"</p></td>"+
                "</tr>";
            });

            $('.content_liquidacion_venta').html(contentHtmlVenta);
        }
    },'json');
}


var load_liquidacion_cobro = function()
{
    $('.content_liquidacion_cobro').html(null);
    contentHtmlCobro = '';
    $.get(pathUrl + "logistica/ruta/get-liquidacion-cobro", { reparto_id : set_reparto_id }, function($responseCobro){
        if ($responseCobro.code == 202 ) {
            $.each($responseCobro.temp_cobro, function(key, temp_cobro){
                contentHtmlCobro += "<tr style='background: "+( temp_cobro.is_apply == 20 ? '#594b0b;color:#fff' : '#a4a4a4' )+"'>"+
                    "<td><p>"+  temp_cobro.pertenece+"</p></td>"+
                    "<td><p style='font-size:16px;font-weight:700; color:#000'>"+  temp_cobro.metodo_pago+"</p></td>"+
                    "<td><p style='font-size:16px;font-weight:700; color:#000'>"+  btf.conta.money(temp_cobro.cantidad) +"</p></td>"+
                    "<td><p style='font-size:16px;font-weight:700; color:#000'>"+  temp_cobro.created_at+"</p></td>"+
                "</tr>";
            });

            $('.content_liquidacion_cobro').html(contentHtmlCobro);
        }
    },'json');
    $('.content_liquidacion_cobro').html(contentHtmlCobro);
}

$btnLiquidacionReparto.click(function(){
    show_loader();
    $.post(pathUrl + "logistica/ruta/post-liquidacion-cobro", { reparto_id : set_reparto_id },function($responseLiquidacion){
        if ($responseLiquidacion.code == 202) {
            hide_loader();
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.success('SE CARGO CORRECTAMENTE LA LIQUIDACION DE REPARTO', 'LIQUIDACION');

              location.reload();
        }else{
            hide_loader();
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.error('OCURRIO UN ERROR, INTENTA NUEVAMENTE', 'LIQUIDACION');
        }
    });
});

$btn_add_producto.click(function(){
    $inputAjusteTipo.val(null);
    $divContentPrecaptura.hide();
    $divContentProducto.hide();
    containerArray = [];
    show_loader();
    load_precaptura();
    $inputCantidad.val(null);
    $inputProducto.val(null).change();
    $('.alert-aviso-ajuste').hide();
    $('.text-message').html(null);
    $('.alert-aviso-ajuste').removeClass('alert-danger');
    $('.alert-aviso-ajuste').removeClass('alert-primary');

});

$btnAddPrecaptura.click(function(){
    $inputAjusteTipo.val(VAR_TIPO_PRECAPTURA);
    $divContentPrecaptura.show();
    $divContentProducto.hide();
    containerArray = [];
    show_loader();
    load_precaptura();
    $('.alert-aviso-ajuste').hide();
    $('.text-message').html(null);
    $('.alert-aviso-ajuste').removeClass('alert-danger');
    $('.alert-aviso-ajuste').removeClass('alert-primary');
});

$btnAddProducto.click(function(){
    $inputAjusteTipo.val(VAR_TIPO_PRODUCTO);
    $divContentPrecaptura.hide();
    $divContentProducto.show();
    $('.alert-aviso-ajuste').hide();
    $('.text-message').html(null);
    $('.alert-aviso-ajuste').removeClass('alert-danger');
    $('.alert-aviso-ajuste').removeClass('alert-primary');

});

$inputProductoSelect.change(function(){
    cargaProductoListArray = [];
    render_productos();
    $inputCargaProductoArray.val(null);
    if ($inputProductoSelect.val().length > 0) {
        $.each($inputProductoSelect.val(), function(key, item_producto){
            $.get("<?= Url::to(['get-producto']) ?>", { producto_id : item_producto } , function($response){
                if ($response.code == 202) {
                    cargaProductoListArray.push({
                        "producto_id"       : $response.producto.id,
                        "producto"          : $response.producto.nombre,
                        "inventario_actual" : $response.producto.inventario_actual,
                        "cantidad" : 0,
                    });
                }
                render_productos();
            },'json');
        })
    }
});


var onGetMetodoPago = function( $venta_id ){
    $modalNotaVenta.modal('show');

    $contentVenta.html(null);
    $.get('<?= Url::to(["get-metodo-pago-venta"]) ?>',{ venta_id : $venta_id },function($response){
        if ($response.code == 202 ) {
            $tempHtml = "";
            $.each($response.ventas,function(key, item_venta){
                $tempHtml += "<tr>"+
                    '<td><a href="<?= Url::to(['/tpv/venta/view']) ?>?id='+ item_venta.id +'" target= "_blank">'+item_venta.folio +'</a></td>'+
                    "<td>"+ btf.conta.money(item_venta.total) +"</td>"+
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

var load_precaptura = function(){
    $.get("<?= Url::to(['get-precaptura-sucursal']) ?>",{ sucursal_id : VAR_SUCURSAL }, function($response){
        if ($response.code == 202) {
            $.each($response.precaptura, function(key, prepedido){
                if (prepedido.id) {
                    prepedido_item = {
                        "item_id"            : prepedido.id,
                        "cliente"            : prepedido.cliente,
                        "pertenece"          : prepedido.pertenece,
                        "cliente_id"         : prepedido.cliente_id,
                        "is_abastecimiento"  : prepedido.is_abastecimiento,
                        "tipo"               : 10,
                        "total"              : prepedido.total,
                        "vendedor"           : prepedido.created_by_user,
                        "created_at"         : prepedido.created_at,
                        "check_true"         : 10,
                    };
                }
                containerArray.push(prepedido_item);
                $('.div_submmit_form').show();
            });
            render_template();
        }else{
            show_message($response.message);
        }

        hide_loader();
    },'json');
}


var render_productos = function(){
    $containerProductos.html(null);
    contentHtml = "";

    $.each(cargaProductoListArray, function(key, item_producto){
        contentHtml += "<tr>"+
            "<td><h3>"+ item_producto.producto +"<h3></td>"+
            "<td class='text-center'><h3>"+ item_producto.inventario_actual +"<h3></td>"+
            "<td><input type='number' class='form-control text-center' value='"+ item_producto.cantidad +"' onchange = 'function_change_cantidad(this,"+ item_producto.producto_id+")' style='font-size:16px; font-weight: bold;'></td>"+
        +"</tr>";
    });

    $inputCargaProductoArray.val(JSON.stringify(cargaProductoListArray));

    $containerProductos.html(contentHtml);
}


var function_change_cantidad = function(elem, producto_id){

        cantidad_tem = 0;
        $.each(cargaProductoListArray, function(key, item_producto){
            if (item_producto.producto_id == producto_id)
                cargaProductoListArray[key].cantidad = $(elem).val();
        });

        render_productos();

    }

/*====================================================
*               RENDERIZA LA VISTA
*====================================================*/
var render_template = function()
{
    $container_precaptura.html("");
    total_venta = 0;
    $count_item = 0;
    $carga_item = 0;
    $.each(containerArray, function(key, prepedido){
        if (prepedido.item_id) {
                $count_item = $count_item + 1;
                template_container = $template_container.html();
                template_container = template_container.replace("{{venta_id}}",prepedido.item_id);

                $container_precaptura.append(template_container);


                $tr        =  $("#item_id_" + prepedido.item_id, $container_precaptura);
                $tr.attr("data-item_id",prepedido.item_id);

                if (prepedido.is_abastecimiento == 20)
                    $tr.css({ "background":" #9f1818", "color" : "#fff"});


                $("#table_count",$tr).html($count_item);

                //$("#table_venta_id",$tr).html( "#" + prepedido.item_id);
                $("#table_venta_id",$tr).html("<a href='"+pathUrl+"tpv/pre-captura/view?id="+prepedido.item_id+"'  target='_blank' style='font-size: 16px;'>"+prepedido.item_id+"</a>");
                $("#table_pertenece",$tr).html(prepedido.pertenece);

                $("#table_cliente",$tr).html(prepedido.cliente);
                $("#table_vendedor",$tr).html(prepedido.vendedor);
                $("#table_total",$tr).html(btf.conta.money(prepedido.total));
                $("#table_fecha",$tr).html( btf.time.datetime(prepedido.created_at ));

                if (prepedido.check_true == 10 ){
                    $tr.append("<td class = 'text-center'><input type='checkbox' onclick='refresh_item(this)' checked></td>");
                    $carga_item = $carga_item + 1;
                }

                if(prepedido.check_true == 1)
                    $tr.append("<td class = 'text-center'><input type='checkbox' onclick='refresh_item(this)'></td>");

        }
    });

    $inputArrayItems.val(JSON.stringify(containerArray));
};

var refresh_item = function(elem){

    $ele_paquete        = $(elem).closest('tr');
    $ele_precaptura_id  = $ele_paquete.attr("data-item_id");

    $.each(containerArray, function(key, precaptura){
        if (precaptura.item_id == $ele_precaptura_id ){
            if($(elem).is(":checked"))
                precaptura.check_true = 10;
            else
                precaptura.check_true = 1;
        }
    });

    $inputArrayItems.val(JSON.stringify(containerArray));
    render_template();
}

$btnAjusteRuta.click(function(e){
    e.preventDefault();
    $('.alert-aviso-ajuste').hide();
    $('.text-message').html(null);
    show_loader();
    $('.alert-aviso-ajuste').removeClass('alert-danger');
    $('.alert-aviso-ajuste').removeClass('alert-primary');
    if ($inputAjusteTipo.val()) {
        if ($inputAjusteTipo.val() == VAR_TIPO_PRODUCTO) {

            if (cargaProductoListArray.length  > 0 ) {
                $('#form-ajuste-ruta').submit();
            }else{
                hide_loader();
                $('.alert-aviso-ajuste').show();
                $('.alert-aviso-ajuste').addClass('alert-danger');
                $('.text-message').html("Debes ingresar un producto son requeridos, intenta nuevamente");
            }

        }
        if ($inputAjusteTipo.val() == VAR_TIPO_PRECAPTURA) {
            if (containerArray.length > 0) {
                $('#form-ajuste-ruta').submit();
            }else{
                hide_loader();
                $('.alert-aviso-ajuste').show();
                $('.alert-aviso-ajuste').addClass('alert-danger');
                $('.text-message').html("Debes seleccionar minimo 1 precaptura, intenta nuevamente");
            }
        }
    }else{
        hide_loader();
        $('.alert-aviso-ajuste').show();
        $('.alert-aviso-ajuste').addClass('alert-danger');
        $('.text-message').html("Verifica tu información, intenta nuevamente");
    }
});

var show_loader = function(){
    $('body').append('<div  id="page_loader" style="opacity: .8;z-index: 2040 !important;    position: fixed;top: 0;left: 0;z-index: 1040;width: 100vw;height: 100vh;background-color: #000;"><div class="spiner-example" style="position: fixed;top: 50%;left: 0;z-index: 2050 !important; width: 100%;height: 100%;"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div>');
}

var hide_loader = function(){
    $('#page_loader').remove();
}
</script>
