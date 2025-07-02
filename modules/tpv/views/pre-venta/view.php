<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\venta\Venta;
use app\models\producto\Producto;
use app\models\venta\VentaDetalle;
use app\models\esys\EsysCambiosLog;

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title = "Folio #" . str_pad($model->id,6,"0",STR_PAD_LEFT);

$this->params['breadcrumbs'][] = ['label' => 'Tpv', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Ver';
?>

 <?= $can['cancel'] && ($model->status == Venta::STATUS_PREVENTA || $model->status == Venta::STATUS_PROCESO_VERIFICACION  || $model->status == Venta::STATUS_VERIFICADO ) ?
    Html::a('Cancel', ['cancel', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data' => [
            'confirm' => '¿Estás seguro de que deseas cancelar esta PRE-VENTA?',
            'method' => 'post',
        ],
    ]): '' ?>

<div class="tpv-pre-venta-view">

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

            <div class="ibox">
                <div class="ibox-title">
                    <h5>PRODUCTOS</h5>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-bordered invoice-summary">
                            <thead>
                                <tr class="bg-trans-dark">
                                    <th class="min-col text-center text-uppercase">SUCURSAL</th>
                                    <th class="min-col text-center text-uppercase">CLAVE</th>
                                    <th class="min-col text-center text-uppercase">PRODUCTO</th>
                                    <th class="min-col text-center text-uppercase">CANTINDAD</th>
                                    <th class="min-col text-center text-uppercase">U.M</th>
                                    <th class="min-col text-center text-uppercase">COSTO X KILO</th>
                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
                                <?php foreach ($model->ventaDetalle as $key => $item): ?>
                                    <tr>
                                        <td><?= $item->apply_bodega == VentaDetalle::APPLY_BODEGA_ON ? '<strong>BODEGA</strong>' : 'TIENDA'  ?> </td>
                                        <td><?= $item->producto->clave  ?> </td>
                                        <td><?= $item->producto->nombre  ?></td>
                                        <td><?= $item->cantidad  ?>        </td>
                                        <td><?= Producto::$medidaList[$item->producto->tipo_medida]  ?> </td>
                                        <td><?= $item->precio_venta ? number_format($item->precio_venta,2) : 0 ?> </td>
                                    </tr>

                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-info">
                <div class="ibox-title">
                    <h5 ><?= Venta::$tipoList[$model->tipo] ?></h5>
                </div>
            </div>
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


