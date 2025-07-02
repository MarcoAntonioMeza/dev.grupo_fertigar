<?php
use app\models\Esys;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\esys\EsysCambiosLog;
use app\models\proveedor\Proveedor;

?>

<div class="proveedores-proveedor-view">
    <div class="row">
        <div class="col-lg-7">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Cuenta de proveedor y datos personales</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            "nombre",
                            "email:email",
                            "rfc",
                            "razon_social",
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="text-center">
                <?php if ($model->avatar): ?>
                    <?= Html::img(Url::to(['/avatar/' . $model->avatar]), [ "class" => "rounded-circle", "alt" => "avatar", "style" => 'width:150px; height:150px', 'id' => 'avatar_upload_id'])  ?>
                <?php endif ?>
            </div>

             <div class="ibox">
                <div class="ibox-title">
                    <h5>CONDICIONES CREDITICIAS</h5>
                </div>
                <div class="ibox-content">
                    <div class="row text-center">
                        <div class="col-sm-6">
                            <h2><?= $model->plazo ? $model->plazo : 0 ?> <p>( PLAZO )</p></h2>
                        </div>
                        <div class="col-sm-6">
                            <h2>$<?= number_format($model->monto, 2) ?> <p>( CREDITO AUTORIZADO )</p></h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <p> <strong>*</strong> <?= $model->terminos_condicion  ?></p>
                        </div>
                    </div>
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
                            'descripcion:ntext',
                        ]
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Dirección</h5>
                </div>
                <div class="ibox-content">
                    <?php foreach ($model->direccion as $key => $direccion): ?>
                        <div class="row" style="border-radius: 5%; border-style: solid; border-width: 1px;">
                            <div class="col-sm-4">
                                <h2><strong>Estado: <p style="font-size: 16px;"><strong><?= $direccion->estado->singular ?></strong></p></strong></h2>
                            </div>
                            <div class="col-sm-4">
                                <h2><strong>Municipio: <p style="font-size: 16px;"><strong><?= $direccion->municipio->singular ?></strong></p></strong></h2>
                            </div>
                            <div class="col-sm-4" style="">
                                <h2><strong>Colonia: <p style="font-size: 16px;"><strong><?= $direccion->codigo_postal_id ?  $direccion->esysDireccionCodigoPostal->colonia : 'N/A' ?></strong></p></strong></h2>
                            </div>

                            <div class="col-sm-12">
                                <h5><strong>Direccion: </strong></h5> <p><?= $direccion->direccion ?> </p>
                            </div>
                            <div class="col-sm-4">
                                <h5><strong>N° Interno: </strong><small> #<?= $direccion->num_int  ?> </small></h5>
                            </div>
                            <div class="col-sm-4">
                                <h5><strong>N° Externo: </strong><small> #<?= $direccion->num_ext  ?></small></h5>
                            </div>
                            <div class="col-sm-4">
                                <h5><strong>CP: </strong><small><?= $direccion->codigo_postal_id ? $direccion->esysDireccionCodigoPostal->codigo_postal  : 'N/A' ?></small></h5>
                            </div>
                            <p class="label-danger" style="position: relative;left: 10px; border-radius: 5%; padding: 2px; bottom: 0px"><?= $direccion->tipo  == 1 ? 'PERSONAL' : 'FISCAL' ?></p>
                    </div>
                    <?php endforeach ?>
                </div>
            </div>

            <div class="panel panel-info ">
                <div class="ibox-title">
                        <h5><?= Proveedor::$statusList[$model->status] ?> </h5>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Historial de cambios</h5>
                </div>
                <div class="ibox-content historial-cambios nano" style="overflow-y: scroll; overflow-x: hidden;">
                    <div class="nano-content" >
                        <?= EsysCambiosLog::getHtmlLog([
                            [new Proveedor(), $model->id],
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
