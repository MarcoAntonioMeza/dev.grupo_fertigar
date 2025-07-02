<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\cliente\Cliente;
use app\models\Esys;
use app\models\esys\EsysCambiosLog;
use app\models\esys\EsysDireccion;


?>

<div class="cliente-user-view">
    <div class="row">
        <div class="col-lg-9">
            <div class="row">
                <div class="col-md-7">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Cuenta de cliente y datos personales</h5>
                        </div>
                        <div class="ibox-content">
                            <div class="row">
                                <div class="col-md-7">
                                    <?= DetailView::widget([
                                        'model' => $model,
                                        'attributes' => [
                                            'id',
                                            "email:email",
                                        ],
                                    ]) ?>
                                    <?= DetailView::widget([
                                        'model' => $model,
                                        'attributes' => [
                                            "tituloPersonal.singular",
                                            "nombre",
                                            "apellidos",
                                        ],
                                    ]) ?>
                                </div>
                                <div class="col-md-5">
                                    <?= DetailView::widget([
                                        'model' => $model,
                                        'attributes' => [
                                          [
                                             'attribute' =>  "Genero",
                                             'format'    => 'raw',
                                             'value'     => $model->sexo ?  Cliente::$sexoList[$model->sexo] : '',
                                         ]
                                        ],
                                    ]) ?>
                                    <?= DetailView::widget([
                                        'model' => $model,
                                        'attributes' => [
                                            "telefono",
                                            "telefono_movil",
                                            [
                                                'attribute' => 'Tipo de cliente',
                                                'format'    => 'raw',
                                                'value'     => isset($model->tipo->singular) ?  $model->tipo->singular : '',
                                            ],
                                        ],
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>CONDICIONES CREDITICIAS</h5>
                        </div>
                        <div class="ibox-content">
                            <div class="row text-center">
                                <div class="col-sm-6">
                                    <h2><?= $model->semanas ? $model->semanas : 0 ?> <p>( SEMANAS )</p></h2>
                                </div>
                                <div class="col-sm-6">
                                    <h2>$<?= number_format($model->monto_credito, 2) ?> <p>( TOTAL DE CREDITO )</p></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ibox">
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                     [
                                         'attribute' => 'Se entero a través de',
                                         'format'    => 'raw',
                                         'value'     =>  isset($model->atravesDe->id) ?  $model->atravesDe->singular : '' ,
                                     ]
                                ],
                            ]) ?>
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
                                    'notas:ntext',
                                ]
                            ]) ?>
                        </div>
                    </div>

                </div>
                <div class="col-md-5">
                    <div class="panel panel-info ">
                        <div class="ibox-title">
                                <h5><?= Cliente::$statusList[$model->status] ?> </h5>
                        </div>
                    </div>

                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Dirección</h5>
                        </div>
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model->direccion,
                                'attributes' => [
                                    'referencia',
                                    'direccion',
                                    'num_ext',
                                    'num_int',
                                    'esysDireccionCodigoPostal.colonia',

                                ]
                            ]) ?>
                            <?= DetailView::widget([
                                'model' => $model->direccion,
                                'attributes' => [
                                    "esysDireccionCodigoPostal.estado.singular",
                                    "esysDireccionCodigoPostal.municipio.singular",
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
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Historial de cambios</h5>
                </div>
                <div class="ibox-content historial-cambios nano">
                    <div class="nano-content">
                        <?= EsysCambiosLog::getHtmlLog([
                            [new Cliente(), $model->id],
                            [new EsysDireccion(), $model->direccion->id],
                        ], 50, true) ?>
                    </div>
                </div>
                <div class="panel-footer">
                    <?= Html::a('Ver historial completo', ['historial-cambios', 'id' => $model->id], ['class' => 'text-primary']) ?>
                </div>
            </div>

            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>

