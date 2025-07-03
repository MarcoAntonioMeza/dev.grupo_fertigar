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

$this->title =  "Folio: #" . str_pad($model->id, 6, "0", STR_PAD_LEFT);

$this->params['breadcrumbs'][] = ['label' => 'Entradas y Salidas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;

$peso = 0; // Inicializar la variable peso
foreach (Operacion::getOperacionDetalleGroup($model->id) as $item) {
    if (isset($item['producto_peso_aprox'])) {
        $peso += $item['producto_peso_aprox'] * $item['cantidad'];
    }
}
#convierte a toneladas
$peso = $peso / 1000; // Convertir a toneladas si es necesario
?>

<!-- LA CANCELACION SOLO SE REALIZARA CUANDO SE UNA OPERACION DE SALIDA [ABASTECIMIENTO] -->

<?php if ($model->tipo == Operacion::TIPO_SALIDA && $model->motivo == Operacion::SALIDA_TRASPASO): ?>
    <?php if ($can['cancel'] && $model->status == Operacion::STATUS_PROCESO): ?>
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
            <!-- Tarjeta de información general de la operación -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-info-circle"></i> Información de la Operación</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-striped table-bordered detail-view'],
                                'attributes' => [
                                    [
                                        'attribute' => 'Sucursal origen',
                                        'value' => $model->almacenSucursal->nombre ?? 'No especificado',
                                    ],
                                    [
                                        'attribute' => 'Total de productos',
                                        'value' => $model->getTotalUnidades(),
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de transferencia entre sucursales -->
            <?php if ($model->sucursal_recibe_id): ?>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-exchange-alt"></i> Transferencia entre Sucursales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-5">
                                <div class="p-3 border rounded bg-light">
                                    <h4 class="font-bold"><?= $model->almacenSucursal->nombre ?? '' ?></h4>
                                    <small class="text-info">SUCURSAL ORIGEN</small>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-center justify-content-center" style="font-size: 2rem;">
                               <i class="fa fa-truck"></i> =>
                            </div>
                            <div class="col-md-5">
                                <div class="p-3 border rounded bg-light">
                                    <h4 class="font-bold"><?= $model->sucursalRecibe->nombre ?? '' ?></h4>
                                    <small class="text-info">SUCURSAL DESTINO</small>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                peso aprox de la carga: <strong><?= number_format($peso, 2) ?> toneladas</strong>
                            </div>
                            <div class="col-md-4"></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Sección de abastecimiento -->
            <?php if ($model->operacion_child_id): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5><i class="fas fa-truck-loading"></i> Abastecimiento</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="p-3 border rounded bg-light">
                            <h4 class="font-bold"><?= $model->operacionChild->almacenSucursal->nombre ?? '' ?></h4>
                            <small class="text-primary">SUCURSAL QUE ABASTECIÓ</small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tarjeta de productos ingresados -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5><i class="fas fa-boxes"></i> Productos Ingresados</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center">CLAVE</th>
                                    <th class="text-center">PRODUCTO</th>
                                    <th class="text-center">CANTIDAD</th>
                                    <th class="text-center">U.M.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (Operacion::getOperacionDetalleGroup($model->id) as $key => $item): ?>
                                    <tr>
                                        <td class="text-center">
                                            <a href="<?= Url::to(["/inventario/arqueo-inventario/view", "id" => $item["producto_id"]]) ?>"
                                                class="text-primary font-bold">
                                                <?= $item["producto_clave"] ?>
                                            </a>
                                        </td>
                                        <td><?= $item["producto"] ?></td>
                                        <td class="text-center"><?= $item["cantidad"] ?></td>
                                        <td class="text-center"><?= $item["producto_tipo_medida"] ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>



        <div class="col-md-5">

    <!-- Panel de estado -->
    <div class="panel panel-<?= Operacion::$statusAlertList[$model->status] ?>">
        <div class="panel-heading text-center">
            <h2 class="m-3"><?= Operacion::$statusList[$model->status] ?></h2>
        </div>
    </div>

    <!-- Botón de etiqueta (solo si es tipo entrada) -->
    <?php if ($model->tipo == Operacion::TIPO_ENTRADA): ?>
        <div class="panel">
            <?= Html::a('<i class="fa fa-print"></i> ETIQUETA', false, [
                'class' => 'btn btn-success btn-lg btn-block',
                'id' => 'imprimir-etiqueta',
                'style' => 'padding: 6%;',
            ]) ?>
        </div>
    <?php endif; ?>

    <!-- Botón de reporte -->
    <div class="panel">
        <?= Html::a('<i class="fa fa-file-pdf-o"></i> REPORTE', false, [
            'class' => 'btn btn-danger btn-lg btn-block',
            'id' => 'imprimir-reporte',
            'style' => 'padding: 6%;',
        ]) ?>
    </div>

    <!-- Tipo de operación -->
    <div class="panel panel-success text-center">
        <div class="panel-heading">
            <h2><?= Operacion::$tipoList[$model->tipo] ?></h2>
        </div>
    </div>

    <!-- Motivo de operación -->
    <div class="panel panel-success text-center">
        <div class="panel-heading">
            <h2><?= Operacion::$operacionList[$model->motivo] ?></h2>
        </div>
    </div>

    <!-- Información extra / Comentarios -->
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

    <!-- Información de creación / modificación -->
    <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>

</div>

    </div>
</div>

<script>
    $('#imprimir-etiqueta').click(function(event) {
        event.preventDefault();
        window.open("<?= Url::to(['imprimir-etiqueta', 'id' => $model->id])  ?>",
            'imprimir',
            'width=600,height=500');
    });

    $('#imprimir-reporte').click(function(event) {
        event.preventDefault();
        window.open("<?= Url::to(['imprimir-reporte', 'id' => $model->id])  ?>",
            'imprimir',
            'width=600,height=500');
    });
</script>