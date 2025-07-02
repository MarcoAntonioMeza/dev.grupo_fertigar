<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\producto\Producto;
use app\models\Esys;

/* @var $this yii\web\View */
/* @var $model app\models\producto\Producto */

$this->title = $model->nombre . " [" . $model->clave . "]";
$this->params['breadcrumbs'][] = ['label' => 'Productos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="productos-producto-view">
    <!-- Alertas de validación -->
    <?php if ($model->validate == Producto::VALIDATE_OFF): ?>
        <?php $fecha_limite = strtotime(date("Y-m-d", $model->created_at) . "+ 7 days") ?>
        <?php if (time() > $fecha_limite): ?>
            <?php
            $date1 = new DateTime(date("Y-m-d", $fecha_limite));
            $date2 = new DateTime(date("Y-m-d", time()));
            $diff = $date1->diff($date2);
            ?>
            <div class="alert alert-danger">
                <strong>SIN VALIDAR</strong> - <strong>FECHA LÍMITE PARA VALIDAR (<?= date("Y-m-d", $fecha_limite) ?>)</strong> / 
                <strong>DÍAS TRANSCURRIDOS <?= $diff->days ?></strong>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <strong>SIN VALIDAR</strong>: EN ESPERA DE VALIDACIÓN POR GERENCIA ADMINISTRATIVA,  
                <strong>FECHA LÍMITE PARA VALIDAR (<?= date("Y-m-d", $fecha_limite) ?>)</strong>
            </div>
        <?php endif ?>
    <?php endif ?>

    <!-- Botones de acción -->
    <div class="row mb-3">
        <div class="col-md-12">
            <?= $can['update'] ? 
                Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) : '' ?>
            
            <?= $can['delete'] ? 
                Html::a('Eliminar', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => '¿Estás seguro de que deseas eliminar este producto?',
                        'method' => 'post',
                    ],
                ]) : '' ?>
        </div>
    </div>

    <div class="row">
        <!-- Columna izquierda -->
        <div class="col-md-8">
            <!-- Tarjeta de información básica -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5>Información Básica</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <?php if ($model->avatar): ?>
                                <?= Html::img(Url::to(["/uploads/".$model->avatar]), [
                                    "class" => "img-thumbnail mb-3", 
                                    "alt" => "Imagen del producto",
                                    "style" => "max-width: 200px;"
                                ]) ?>
                            <?php else: ?>
                                <div class="text-secondary mb-3">Sin imagen</div>
                            <?php endif ?>
                        </div>
                        <div class="col-md-8">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-striped table-bordered detail-view'],
                                'attributes' => [
                                    'id',
                                    'clave',
                                    'nombre',
                                    [
                                        'attribute' => 'tipo',
                                        'value' => Producto::$tipoProductoList[$model->tipo] ?? '',
                                    ],
                                    [
                                        'attribute' => 'pertenece_a',
                                        'value' => Producto::$perteneceList[$model->pertenece_a] ?? '',
                                    ],
                                    [
                                        'attribute' => 'categoria_id',
                                        'value' => $model->categoria->singular ?? '',
                                    ],
                                    [
                                        'attribute' => 'tipo_medida',
                                        'value' => Producto::$medidaList[$model->tipo_medida] ?? '',
                                    ],
                                    [
                                        'attribute' => 'unidad_medida_id',
                                        'value' => $model->unidadMedida->nombre ?? '',
                                    ],
                                    'peso_aprox:decimal',
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de precios y costos -->
            <?php if(!Yii::$app->user->can('ENCARGADO CEDIS SIN MONTOS')): ?>
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5>Información Financiera</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 border-right">
                            <h3 class="text-primary">$<?= number_format($model->costo, 2) ?></h3>
                            <p class="text-secondary">Costo Promedio</p>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-4">
                                    <h4>$<?= number_format($model->precio_publico, 2) ?></h4>
                                    <p class="text-secondary">Público</p>
                                    <small>Comisión: <?= $model->comision_publico ?>%</small>
                                </div>
                                <div class="col-md-4">
                                    <h4>$<?= number_format($model->precio_mayoreo, 2) ?></h4>
                                    <p class="text-secondary">Mayoreo</p>
                                    <small>Comisión: <?= $model->comision_mayoreo ?>%</small>
                                </div>
                                <div class="col-md-4">
                                    <h4>$<?= number_format($model->precio_sub, 2) ?></h4>
                                    <p class="text-secondary">Subdistribuidor</p>
                                    <small>Comisión: <?= $model->comision_sub ?>%</small>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <p><strong>IVA:</strong> <?= $model->iva ?>%</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>IEPS:</strong> <?= $model->ieps ?>%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif ?>

            <!-- Tarjeta de descripción -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Descripción y Detalles</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped table-bordered detail-view'],
                        'attributes' => [
                            'descripcion:ntext',
                            [
                                'attribute' => 'inventariable',
                                'value' => Producto::$invList[$model->inventariable] ?? '',
                            ],
                            [
                                'attribute' => 'stock_minimo',
                                'value' => $model->inventariable == Producto::INV_SI ? $model->stock_minimo : 'N/A',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Columna derecha -->
        <div class="col-md-4">
            <!-- Tarjeta de validación -->
            <?php if (Yii::$app->user->can('admin') || Yii::$app->user->can('ASISTENTE')): ?>
                <?php if ($model->validate == Producto::VALIDATE_OFF): ?>
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <?= Html::a('VALIDAR PRODUCTO', ['validar-producto', 'id' => $model->id], [
                                'class' => 'btn btn-warning btn-lg btn-block',
                                'style' => 'padding: 15px;',
                                'data' => [
                                    'confirm' => '¿Estás seguro de que deseas validar este producto?',
                                    'method' => 'post',
                                ]
                            ]) ?>
                        </div>
                    </div>
                <?php endif ?>
            <?php endif ?>

           

            <!-- Estado del producto -->
            <div class="card mb-4">
                <div class="card-header <?= $model->status == Producto::STATUS_ACTIVE ? 'bg-success' : 'bg-secondary' ?> text-white">
                    <h5>Estado del Producto</h5>
                </div>
                <div class="card-body text-center">
                    <h4><?= Producto::$statusList[$model->status] ?></h4>
                </div>
            </div>

            <!-- Información de subproducto -->
            <?php if ($model->is_subproducto == Producto::TIPO_SUBPRODUCTO): ?>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5>Información de Subproducto</h5>
                    </div>
                    <div class="card-body text-center">
                        <p>Equivalente a <?= $model->sub_cantidad_equivalente ?> unidades de:</p>
                        <h4>
                            <?php if (isset($model->subProducto->id)): ?>
                                <?= Html::a($model->subProducto->nombre, ["view", "id" => $model->subProducto->id]) ?>
                            <?php else: ?>
                                Producto no encontrado
                            <?php endif ?>
                        </h4>
                    </div>
                </div>
            <?php endif ?>

            <!-- Proveedor -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Proveedor</h5>
                </div>
                <div class="card-body">
                    <?php if ($model->proveedor): ?>
                        <h5><?= Html::a($model->proveedor->nombre, ['/crm/proveedor/view', 'id' => $model->proveedor->id]) ?></h5>
                        <p class="text-secondary">ID: <?= $model->proveedor->id ?></p>
                    <?php else: ?>
                        <p class="text-secondary">No asignado</p>
                    <?php endif ?>
                </div>
            </div>

            <!-- Información de creación/actualización -->
            <?= CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>