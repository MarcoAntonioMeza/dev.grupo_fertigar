<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;

/* @var $this yii\web\View */
/* @var $model app\models\AuthItem */

$this->title = $model->name;
$this->params['breadcrumbs'][] = 'Administración';
$this->params['breadcrumbs'][] = 'Configuraciones';
$this->params['breadcrumbs'][] = ['label' => 'Perfiles de usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="admin-perfil-view">
    <p>
        <?= $can['update']?
            Html::a('Editar', ['update', 'name' => $model->name], ['class' => 'btn btn-primary']): '' ?>

        <?= $can['delete']?
            Html::a('Eliminar', ['delete', 'name' => $model->name], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]): '' ?>
    </p>
    <div class="row">
        <div class="col-lg-9">
            <?php foreach ($model->items as $key => $groupMain): ?>
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Permisos <?= $groupMain['name'] ?></h5>
                    </div>
                    <div class="ibox-content">
                        <table class="privilegios table table-hover table-vcenter table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center" colspan="2">Modulo</th>
                                    <th class="text-center">Ver</th>
                                    <th class="text-center">Crear</th>
                                    <th class="text-center">Editar</th>
                                    <th class="text-center">Cancelar</th>
                                    <th class="text-center">Eliminar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($groupMain['grupos'] as $key2 => $grupo): $grupo_last = true; ?>
                                    <?php foreach ($grupo['subgrupos'] as $key3 => $subgrupo): ?>
                                        <tr>
                                            <?php if($grupo_last): ?>
                                                <td class="min-width text-center bg-light" rowspan="<?= sizeof($grupo['subgrupos']) ?>">
                                                    <?= $grupo['icon'] ?>
                                                </td>
                                            <?php endif; $grupo_last = false ?>
                                            <td>
                                                <?= Html::checkbox(
                                                    $subgrupo['view']? null: "Perfil[privilegios][{$subgrupo['subgrupo']}]",
                                                    isset($model->privilegios[$subgrupo['subgrupo']]) || isset($model->privilegios[$subgrupo['subgrupo'] . 'View']),
                                                    [
                                                        "id"    => "subgrupo_{$subgrupo['subgrupo']}_access",
                                                        "class" => "modulo magic-checkbox",
                                                        'disabled' => true,
                                                    ]
                                                ) ?>
                                                <?= Html::label(null, "subgrupo_{$subgrupo['subgrupo']}_access", ["style" => "display:inline"]) ?>
                                                <span class="text-main"><?= $subgrupo['description'] ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if($subgrupo['view']): ?>
                                                    <?= Html::checkbox(
                                                        "Perfil[privilegios][{$subgrupo['subgrupo']}View]",
                                                        isset($model->privilegios[$subgrupo['subgrupo'] . 'View']),
                                                        [
                                                            "id"    => "subgrupo_{$subgrupo['subgrupo']}_view",
                                                            "class" => "view magic-checkbox",
                                                            'disabled' => true,
                                                        ]
                                                    ) ?>
                                                    <?= Html::label(null, "subgrupo_{$subgrupo['subgrupo']}_view", ["style" => "display:inline"]) ?>
                                                <?php endif ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if($subgrupo['create']): ?>
                                                    <?= Html::checkbox(
                                                        "Perfil[privilegios][{$subgrupo['subgrupo']}Create]",
                                                        isset($model->privilegios[$subgrupo['subgrupo'] . 'Create']),
                                                        [
                                                            "id"    => "subgrupo_{$subgrupo['subgrupo']}_create",
                                                            "class" => "create magic-checkbox",
                                                            'disabled' => true,
                                                        ]
                                                    ) ?>
                                                    <?= Html::label(null, "subgrupo_{$subgrupo['subgrupo']}_create", ["style" => "display:inline"]) ?>
                                                <?php endif ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if($subgrupo['update']): ?>
                                                    <?= Html::checkbox(
                                                        "Perfil[privilegios][{$subgrupo['subgrupo']}Update]",
                                                        isset($model->privilegios[$subgrupo['subgrupo'] . 'Update']),
                                                        [
                                                            "id"    => "subgrupo_{$subgrupo['subgrupo']}_update",
                                                            "class" => "update magic-checkbox",
                                                            'disabled' => true,
                                                        ]
                                                    ) ?>
                                                    <?= Html::label(null, "subgrupo_{$subgrupo['subgrupo']}_update", ["style" => "display:inline"]) ?>
                                                <?php endif ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if($subgrupo['cancel']): ?>
                                                    <?= Html::checkbox(
                                                        "Perfil[privilegios][{$subgrupo['subgrupo']}Cancel]",
                                                        isset($model->privilegios[$subgrupo['subgrupo'] . 'Cancel']),
                                                        [
                                                            "id"    => "subgrupo_{$subgrupo['subgrupo']}_cancel",
                                                            "class" => "cancel magic-checkbox",
                                                            'disabled' => true,
                                                        ]
                                                    ) ?>
                                                    <?= Html::label(null, "subgrupo_{$subgrupo['subgrupo']}_cancel", ["style" => "display:inline"]) ?>
                                                <?php endif ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if($subgrupo['delete']): ?>
                                                    <?= Html::checkbox(
                                                        "Perfil[privilegios][{$subgrupo['subgrupo']}Delete]",
                                                        isset($model->privilegios[$subgrupo['subgrupo'] . 'Delete']),
                                                        [
                                                            "id"    => "subgrupo_{$subgrupo['subgrupo']}_delete",
                                                            "class" => "delete magic-checkbox",
                                                            'disabled' => true,
                                                        ]
                                                    ) ?>
                                                    <?= Html::label(null, "subgrupo_{$subgrupo['subgrupo']}_delete", ["style" => "display:inline"]) ?>
                                                <?php endif ?>
                                            </td>
                                        </tr>
                                    <?php endforeach  ?>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach ?>
        </div>

        <div class="col-lg-3">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'name',
                    'description:text',
                ],
            ]) ?>

            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>
