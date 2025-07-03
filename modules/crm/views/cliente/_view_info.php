<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\cliente\Cliente;
use app\models\esys\EsysCambiosLog;
use app\models\esys\EsysDireccion;
?>

<div class="cliente-user-view">
    <div class="row">
        <!-- Columna principal -->
        <div class="col-lg-9">
            <div class="row">
                <!-- Sección izquierda - Información principal -->
                <div class="col-md-7">
                    <!-- Tarjeta de información personal -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5><i class="fas fa-user"></i> Información Personal</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <?= DetailView::widget([
                                        'model' => $model,
                                        'options' => ['class' => 'table table-striped table-bordered detail-view'],
                                        'attributes' => [
                                            'id',
                                            'email:email',
                                            [
                                                'attribute' => 'Título',
                                                'value' => $model->tituloPersonal->singular ?? '',
                                            ],
                                            'nombre',
                                            'apellidos',
                                            [
                                                'attribute' => 'Género',
                                                'value' => $model->sexo ? Cliente::$sexoList[$model->sexo] : '',
                                            ],
                                        ],
                                    ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= DetailView::widget([
                                        'model' => $model,
                                        'options' => ['class' => 'table table-striped table-bordered detail-view'],
                                        'attributes' => [
                                            'telefono',
                                            'telefono_movil',
                                            'rfc',
                                            [
                                                'attribute' => 'Tipo de cliente',
                                                'value' => $model->tipo->singular ?? '',
                                            ],
                                            [
                                                'attribute' => 'Se enteró a través de',
                                                'value' => $model->atravesDe->singular ?? '',
                                            ],
                                            [
                                                'attribute' => 'Lista de precios',
                                                'value' => isset($model->lista_precios) ? Cliente::$tipoListaPrecioList[$model->lista_precios] : '',
                                            ],
                                        ],
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta de información fiscal -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5><i class="fas fa-file-invoice-dollar"></i> Información Fiscal</h5>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-striped table-bordered detail-view'],
                                'attributes' => [
                                    [
                                        'attribute' => 'Régimen fiscal',
                                        'value' => $model->regimenFiscal->nombreCompleto ?? '',
                                    ],
                                    'uso_cfdi',
                                    [
                                        'attribute' => 'Agente asignado',
                                        'format' => 'raw',
                                        'value' => $model->agente ? 
                                            Html::a($model->agente->nombreCompleto, ['/admin/user/view', 'id' => $model->agente->id]) : 
                                            'No asignado',
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>

                   

                    <!-- Tarjeta de notas -->
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h5><i class="fas fa-sticky-note"></i> Notas Adicionales</h5>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-striped table-bordered detail-view'],
                                'attributes' => [
                                    [
                                        'attribute' => 'notas',
                                        'format' => 'ntext',
                                        'value' => $model->notas ?: 'Sin notas adicionales',
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>

                <!-- Sección derecha - Información secundaria -->
                <div class="col-md-5">
                    <!-- Tarjeta de estado -->
                    <div class="card mb-4">
                        <div class="card-header <?= $model->status == Cliente::STATUS_ACTIVE ? 'bg-success' : 'bg-danger' ?> text-white">
                            <h5 class="text-center"><?= Cliente::$statusList[$model->status] ?></h5>
                        </div>
                    </div>

                    <!-- Tarjeta de dirección -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5><i class="fas fa-map-marker-alt"></i> Dirección</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($model->direccion): ?>
                                <?= DetailView::widget([
                                    'model' => $model->direccion,
                                    'options' => ['class' => 'table table-striped table-bordered detail-view'],
                                    'attributes' => [
                                        'direccion',
                                        [
                                            'label' => 'Número exterior',
                                            'value' => $model->direccion->num_ext ?: 'S/N',
                                        ],
                                        [
                                            'label' => 'Número interior',
                                            'value' => $model->direccion->num_int ?: 'N/A',
                                        ],
                                        'referencia',
                                        [
                                            'label' => 'Colonia',
                                            'value' => $model->direccion->esysDireccionCodigoPostal->colonia ?? '',
                                        ],
                                        [
                                            'label' => 'Código Postal',
                                            'value' => $model->direccion->esysDireccionCodigoPostal->codigo_postal ?? '',
                                        ],
                                        [
                                            'label' => 'Municipio',
                                            'value' => $model->direccion->esysDireccionCodigoPostal->municipio->singular ?? '',
                                        ],
                                        [
                                            'label' => 'Estado',
                                            'value' => $model->direccion->esysDireccionCodigoPostal->estado->singular ?? '',
                                        ],
                                    ],
                                ]) ?>
                            <?php else: ?>
                                <div class="alert alert-warning">No se ha registrado dirección</div>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna lateral - Historial -->
        <div class="col-lg-3">
            <!-- Tarjeta de historial de cambios -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5><i class="fas fa-history"></i> Historial de Cambios</h5>
                </div>
                <div class="card-body historial-cambios" style="max-height: 400px; overflow-y: auto;">
                    <?= EsysCambiosLog::getHtmlLog([
                        [new Cliente(), $model->id],
                        [new EsysDireccion(), $model->direccion->id],
                    ], 50, true) ?>
                </div>
                <div class="card-footer text-center">
                    <?= Html::a('Ver historial completo', ['historial-cambios', 'id' => $model->id], [
                        'class' => 'btn btn-sm btn-outline-primary'
                    ]) ?>
                </div>
            </div>

            <!-- Información de creación/actualización -->
            <?= CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>

<style>
    .historial-cambios {
        padding: 10px;
        background: #f8f9fa;
        border-radius: 4px;
    }
    .historial-item {
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }
    .historial-item:last-child {
        border-bottom: none;
    }
</style>