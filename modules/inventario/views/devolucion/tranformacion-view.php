<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\sucursal\Sucursal;
use app\models\inv\Operacion;
use app\models\producto\Producto;
use app\models\tranformacion\TranformacionDevolucion;
/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title =  "Folio: #" . str_pad($model->id,6,"0",STR_PAD_LEFT);

$this->params['breadcrumbs'][] = ['label' => 'Devoluciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
?>



<div class="inv-tranformacion-view">

    <div class="row">
        <div class="col-md-7">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información operación</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'sucursal.nombre'

                        ],
                    ]) ?>
                </div>
            </div>
            <?php foreach (TranformacionDevolucion::getItemsGroup($model->token, $model->id) as $key => $item_operacion): ?>
                <?php if ($item_operacion->motivo_id == TranformacionDevolucion::TRANS_OND_PRODUCTO ): ?>
                    <div class="panel">
                        <div class="panel-body text-center">
                            <div class="row">
                                <div class="col">
                                    <div class=" m-l-md">
                                        <span class="h5 font-bold m-t block"> <?= $item_operacion->productoNew->nombre  ?></span>
                                        <small class="text-muted m-b block"><strong>PRODUCTO ANEXADO</strong></small>
                                    </div>
                                </div>
                                <div class="col" style="align-self: center;font-size: 48px;">
                                     <i class="fa fa-sign-in"></i> =>
                                </div>
                                <div class="col">
                                    <div class=" m-l-md">
                                    <span class="h5 font-bold m-t block"> <?= $item_operacion->producto_cantidad  ?></span>
                                    <small class="text-muted m-b block"><strong>CANTIDAD ANEXADA</strong></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="panel">
                        <div class="panel-body text-center">
                            <div class="row">
                               <div class="col">
                                    <div class=" m-l-md">
                                    <span class="h5 font-bold m-t block"> <?= $item_operacion->producto_cantidad  ?></span>
                                    <h2 class=" block text-danger"><strong>MERMA</strong></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif ?>
            <?php endforeach ?>


            <div class="ibox">
                <div class="ibox-title">
                    <h3 >PRODUCTO TRANSFORMADO</h3>
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
                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
                                <?php foreach ($model->tranformacionDevolucionDetalles as $key => $item): ?>
                                    <tr>
                                        <td><a href="<?= Url::to(["/inventario/arqueo-inventario/view", "id" => $item->producto->id  ])  ?>"><?= $item->producto->clave  ?></a></td>
                                        <td><?= $item->producto->nombre ?></td>
                                        <td><?= $item->cantidad  ?>        </td>
                                        <td><?= Producto::$medidaList[$item->producto->tipo_medida]  ?> </td>

                                    </tr>

                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="panel panel-success text-center">
                <div class="ibox-title">
                    <h2><?= TranformacionDevolucion::$transList[$model->motivo_id] ?></h2>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body">
                    <h3>NOTA / MOTIVO DE TRANSFORMACION</h3>
                    <strong class="text-danger"><?= $model->nota ?></strong>
                </div>
            </div>

            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>


