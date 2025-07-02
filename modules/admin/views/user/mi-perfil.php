<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\user\User */

$this->title = 'Mi perfil';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="mi-perfil">
    <div class="row">
        <div class="col-md-7">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Cuenta de usuario y datos personales</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-7">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'id',
                                    "username",
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
                                    "sexo",
                                    "fecha_nac:date",
                                    "cargo",
                                    "departamento.singular",
                                ],
                            ]) ?>
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    "telefono",
                                    "telefono_movil",
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Permisos</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            "perfil.item_name",
                            "perfilesAsignarString",
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Sucursal</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model->direccion,
                        'attributes' => [
                            'direccion',
                            'num_ext',
                            'num_int',

                        ]
                    ]) ?>
                    <?= DetailView::widget([
                        'model' => $model->direccion,
                        'attributes' => [
                            "estado.singular",
                            "municipio.singular",
                        ]
                    ]) ?>

                </div>
            </div>
            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>
