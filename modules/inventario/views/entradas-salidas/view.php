<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\sucursal\Sucursal;
use app\models\inv\Operacion;
use app\models\producto\Producto;

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title =  "Folio: #" . str_pad($model->id,6,"0",STR_PAD_LEFT);

$this->params['breadcrumbs'][] = ['label' => 'Entradas y Salidas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
?>

<!-- LA CANCELACION SOLO SE REALIZARA CUANDO SE UNA OPERACION DE SALIDA [ABASTECIMIENTO] -->

<?php if ($model->tipo == Operacion::TIPO_SALIDA && $model->motivo == Operacion::SALIDA_TRASPASO ): ?>
    <?php if ( $can['cancel'] && $model->status == Operacion::STATUS_PROCESO): ?>
<p>
        <?= Html::a('Cancelar', ['cancel', 'id' => $model->id], [
            'class' => 'btn btn-danger btn-zoom',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas cancelar esta operación?',
                'method' => 'post',
            ],
        ]) ?>
        <strong class="text-danger alert alert-danger"> * El producto regresara a la [SUCURSAL/BODEGA] de origen</strong>
</p>

    <?php endif ?>
<?php endif ?>


<div class="inv-operacion-view">

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
                            'almacenSucursal.nombre'

                        ],
                    ]) ?>
                </div>
            </div>
            <?php if ($model->operacion_child_id): ?>
                <div class="panel">
                    <div class="panel-body text-center">
                        <div class="row">
                            <div class="col">
                                <div class=" m-l-md">
                                <span class="h5 font-bold m-t block"> <?= $model->operacionChild->almacenSucursal->nombre  ?></span>
                                <small class="text-muted m-b block"><strong>SUCURSAL QUE ABASTECIO</strong></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>
            <?php if ($model->sucursal_recibe_id): ?>
            <div class="panel">
                <div class="panel-body text-center">
                    <div class="row">
                        <div class="col">
                            <div class=" m-l-md">
                                <span class="h5 font-bold m-t block"> <?= $model->almacenSucursal->nombre  ?></span>
                                <small class="text-muted m-b block"><strong>SUCURSAL QUE ABASTECE</strong></small>
                            </div>
                        </div>
                        <div class="col" style="align-self: center;font-size: 48px;">
                             <i class="fa fa-truck"></i> =>
                        </div>
                        <div class="col">
                            <div class=" m-l-md">
                            <span class="h5 font-bold m-t block"> <?= $model->sucursalRecibe->nombre  ?></span>
                            <small class="text-muted m-b block"><strong>SUCURSAL A SURTIR</strong></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif ?>
            <div class="panel">
                <div class="panel-body text-center">
                    <div class="row">
                        <div class="col">
                            <span class="h5 font-bold m-t block"> <?= $model->getTotalUnidades()  ?></span>
                            <small class="text-muted m-b block">CANTIDAD DE PRODUCTO</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h3 >PRODUCTO INGRESADOS</h3>
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
                                <?php foreach (Operacion::getOperacionDetalleGroup($model->id) as $key => $item): ?>
                                    <tr>
                                        <td><a href="<?= Url::to(["/inventario/arqueo-inventario/view", "id" => $item["producto_id"]  ])  ?>"><?= $item["producto_clave"]  ?></a></td>
                                        <td><?= $item["producto"] ?></td>
                                        <td><?= $item["cantidad"]  ?>        </td>
                                        <td><?= Producto::$medidaList[$item["producto_tipo_medida"]]  ?> </td>
                                    </tr>

                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="panel panel-<?= Operacion::$statusAlertList[$model->status] ?>">
                <div class="panel-heading text-center">
                    <h2 style="margin:3%"><?= Operacion::$statusList[$model->status] ?></h2>
                </div>
            </div>
            <?php if ($model->tipo == Operacion::TIPO_ENTRADA): ?>
             <div class="panel">
                <?= Html::a('<i class="fa fa-print fa-button-view float-left"></i> ETIQUETA', false, ['class' => 'btn  btn-lg btn-block btn-success', 'id' => 'imprimir-etiqueta','style'=>'padding: 6%;'])?>
            </div>
            <?php endif ?>
            <div class="panel">
                <?= Html::a('<i class="fa fa-file-pdf-o fa-button-view float-left"></i> REPORTE', false, ['class' => 'btn  btn-lg btn-block btn-danger', 'id' => 'imprimir-reporte','style'=>'padding: 6%;'])?>
            </div>
            <div class="panel panel-success text-center">
                <div class="ibox-title">
                    <h2><?= Operacion::$tipoList[$model->tipo] ?></h2>
                </div>
            </div>
            <div class="panel panel-success text-center">
                <div class="ibox-title">
                    <h2><?= Operacion::$operacionList[$model->motivo] ?></h2>
                </div>
            </div>
             <div class="ibox">
                <div class="ibox-title">
                    <h5>Información extra / Comentarios</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'nota:ntext',
                        ]
                    ]) ?>
                </div>
            </div>

            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>

<script>
$('#imprimir-etiqueta').click(function(event){
    event.preventDefault();
    window.open("<?= Url::to(['imprimir-etiqueta', 'id' => $model->id ])  ?>",
    'imprimir',
    'width=600,height=500');
});

$('#imprimir-reporte').click(function(event){
    event.preventDefault();
    window.open("<?= Url::to(['imprimir-reporte', 'id' => $model->id ])  ?>",
    'imprimir',
    'width=600,height=500');
});
</script>
