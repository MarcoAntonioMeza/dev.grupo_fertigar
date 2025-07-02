<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\venta\Venta;
use yii\widgets\ActiveForm;
use app\models\venta\VentaDetalle;
use app\models\producto\Producto;
use app\models\esys\EsysCambiosLog;

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title = "Folio #" . str_pad($model->id,6,"0",STR_PAD_LEFT);

$this->params['breadcrumbs'][] = ['label' => 'Tpv', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Ver';
?>

<div class="tpv-pre-captura-view">


    <p>
        <?= $can['update'] && $model->status == Venta::STATUS_PRECAPTURA ?
            Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>


        <?= $can['update'] && $model->status == Venta::STATUS_PROCESO ?
            Html::a('EDITAR PREVENTA  [ PROCESO - ENTREGA]', ['update', 'id' => $model->id], ['class' => 'btn btn-warning btn-lg']): '' ?>

        <?= $can['cancel'] && $model->status == Venta::STATUS_PRECAPTURA ?
            Html::a('Cancel', ['cancel', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '¿Estás seguro de que deseas cancelar esta PRECAPTURA?',
                    'method' => 'post',
                ],
            ]): '' ?>
    </p>


    <div class="alert alert-warning">
        <h5 ><?= Venta::$statusList[$model->status] ?></h5>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información venta</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="alert alert-warning" style="background-color:  <?= $model->is_especial == Venta::VENTA_ESPECIAL  ? '#ffd44d' : '#243747'; ?>; color: #fff">
                <h5 class="text-center"><?= Venta::$ventaList[$model->is_especial] ?></h5>
            </div>

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
                                 'value'     =>  isset($model->cliente->id) ?  Html::a($model->cliente->nombre ." ". $model->cliente->apellidos , ['/crm/cliente/view', 'id' => $model->cliente->id], ['class' => 'text-primary']) : '' ,
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
                            <span class="h5 font-bold m-t block"> <?= number_format($model->total)  ?></span>
                            <small class="text-muted m-b block">TOTAL VENTA</small>
                            </div>
                        </div>
                        <div class="col">
                            <span class="h5 font-bold m-t block"> <?= $model->getTotalUnidades()  ?></span>
                            <small class="text-muted m-b block">UNIDADES</small>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($model->status == Venta::STATUS_CANCEL): ?>
                <div class="ibox">
                    <div class="ibox-content">
                        <h2>NOTA DE CANCELACIÓN</h2>
                        <p class="text-danger"><strong><?= $model->nota_cancelacion ?></strong></p>
                    </div>
                </div>
            <?php endif ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h5>PRODUCTOS</h5>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-bordered invoice-summary">
                            <thead>
                                <tr class="bg-trans-dark">
                                    <th class="min-col text-center text-uppercase">CLAVE</th>
                                    <th class="min-col text-center text-uppercase">PRODUCTO</th>
                                    <th class="min-col text-center text-uppercase">CANTIDAD</th>
                                    <th class="min-col text-center text-uppercase">U.M</th>
                                    <th class="min-col text-center text-uppercase">CANTIDAD CONVERTIR</th>
                                    <th class="min-col text-center text-uppercase">U.M CONVERTIR</th>
                                    <th class="min-col text-center text-uppercase">COSTO X KILO</th>
                                    <th class="min-col text-center text-uppercase">REPARTO</th>
                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
                                <?php foreach ($model->ventaDetalle as $key => $item): ?>
                                    <tr >
                                        <td><?= $item->producto->clave  ?> </td>
                                        <td><?= $item->producto->nombre  ?></td>
                                        <td  style="font-weight:bold;font-size: 16px">
                                            <p><?= $item->cantidad  ?></p>

                                            <?php if ($item->is_conversion == VentaDetalle::CONVERSION_ON ): ?>
                                                <p><?= Html::button("<i class='fa fa-refresh'></i>", [ "class" => "btn btn-warning btn-circle", "data-target"=>"#modal-show-operacion", "data-toggle"=>"modal", "onclick" => "saveConversion(". $item->id .")"  ]) ?></p>
                                            <?php endif ?>
                                        </td>
                                        <td  style="font-weight:bold;font-size: 16px"><?= Producto::$medidaList[$item->producto->tipo_medida]  ?> </td>
                                        <td style="font-weight:bold;font-size: 16px; background:  <?= $item->is_conversion == VentaDetalle::CONVERSION_ON ? '#ed5565':'' ?>"><?= $item->conversion_cantidad  ?>        </td>

                                        <td style="font-weight:bold; font-size: 16px; background:  <?= $item->is_conversion == VentaDetalle::CONVERSION_ON ? '#ed5565':'' ?>">
                                            <?php if ($item->is_conversion == VentaDetalle::CONVERSION_ON): ?>
                                                <?= Producto::$medidaList[$item->producto->tipo_medida == Producto::MEDIDA_PZ ? Producto::MEDIDA_KILO : Producto::MEDIDA_PZ]  ?>
                                            <?php endif ?>
                                         </td>
                                        <td><?= $item->precio_venta ? number_format($item->precio_venta,2) : 0 ?> </td>
                                        <td>
                                            <?php if ($item->repartoAdd): ?>
                                                <?php foreach ($item->repartoAdd as $key => $reparto): ?>
                                                     <li><a href="<?= Url::to([ '/logistica/ruta/view', 'id' => $reparto->reparto_id ]) ?>"><?=  '['. $reparto->reparto_id . ' - '. date("Y-m-d", $reparto->created_at) .']'  ?></a></li>
                                                <?php endforeach ?>
                                            <?php else: ?>
                                                PENDIENTE
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
        <div class="col-md-4">
            <?php if ($model->ruta_sucursal_id): ?>
                <div class="panel panel-warning">
                    <div class="panel-body text-center">
                        <div class="row">
                            <div class="col">
                                <div class=" m-l-md">
                                <span class="h5 font-bold m-t block"> <?= $model->sucursal->nombre ?></span>
                                <small class="text-muted m-b block">RUTA A ENTREGAR</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>
            <div class="panel" style="background-color: #cbb70e;color: #fff;">
                <div class="panel-body text-center ">
                    <div class="row">
                        <div class="col">
                            <div class=" m-l-md">
                            <span class="h5 font-bold m-t block"> <i class="fa fa-cubes"></i> <?= $model->sucursalVende->nombre  ?></span>
                            <small class="  block">PRECAPTURA DE</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-info">
                <div class="ibox-title">
                    <h5 ><?= Venta::$tipoList[$model->tipo] ?></h5>
                </div>
            </div>


            <div class="ibox">
                <?= Html::a('<i class="fa fa-pencil-square-o mar-rgt-5px"></i> PAGARE',null,['id' => 'reporte_download_acuse','class' => 'btn btn-lg btn-block', 'style'=>'padding: 6%;font-size: 24px; background: #4c0ba7; color: #fff' ])?>
            </div>

            <?php if ($model->direccion): ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h5>DIRECCIÓN DE ENTREGA</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model->direccion,
                        'attributes' => [
                            'direccion',
                            'esysDireccionCodigoPostal.colonia',

                        ]
                    ]) ?>
                    <?= DetailView::widget([
                        'model' => $model->direccion,
                        'attributes' => [
                            "estado.singular",
                            "municipio.singular",
                        ]
                    ]) ?>

                    <?= DetailView::widget([
                        'model' => $model->direccion,
                        'attributes' => [
                            'esysDireccionCodigoPostal.codigo_postal',
                        ]
                    ]) ?>
                </div>
            </div>
            <?php endif ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Historial de cambios</h5>
                </div>
                <div class="ibox-content historial-cambios nano">
                    <div class="nano-content">
                        <?= EsysCambiosLog::getHtmlLog([
                            [new Venta(), $model->id],
                        ], 50, true) ?>
                    </div>
                </div>
                <div class="ibox-footer">
                    <?= Html::a('Ver historial completo', ['historial-cambios', 'id' => $model->id], ['class' => 'text-primary']) ?>
                </div>
            </div>

            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>


<div class="fade modal inmodal " id="modal-show-operacion"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> CONVERSION [ PZ => KG ] </h4>
            </div>
            <?php $form = ActiveForm::begin([ 'action' => 'save-conversion-producto']) ?>
            <?= Html::hiddenInput('Conversion[id]', null, [ "id" => "inputConversionId"]) ?>
            <!--Modal body-->
            <div class="modal-body">

                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="row">
                           <?= Html::input("number","Conversion[cantidad]",false,["class"=> "form-control text-center", "style" => "font-size:24px", "step"=>"0.001"]) ?>
                        </div>
                        <div class="row">

                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <?= Html::submitButton('GUARDAR CAMBIOS', ['class' => 'btn btn-primary btn-lg', 'style' => 'font-size: 20px; ' ]) ?>

                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<script>
    var $reporte_download_acuse     = $('#reporte_download_acuse'),
        $inputDetalleId             = $('#inputConversionId'),
    venta_id                        = <?= $model->id ?>;


$reporte_download_acuse.click(function(event){
    event.preventDefault();
    window.open('<?= Url::to(['imprimir-acuse-pdf']) ?>?venta_id=' + venta_id,
        'imprimir',
        'width=600,height=600');
});


var saveConversion = function(detalle_id){
    $inputDetalleId.val(null);
    $inputDetalleId.val(detalle_id);
}
</script>