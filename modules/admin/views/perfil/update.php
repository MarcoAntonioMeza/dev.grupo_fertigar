<?php
/* @var $this yii\web\View */
/* @var $model app\models\AuthItem */

$this->title = $model->name;
$this->params['breadcrumbs'][] = 'Administración';
$this->params['breadcrumbs'][] = 'Configuraciones';
$this->params['breadcrumbs'][] = ['label' => 'Perfiles de usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'name' => $model->name]];
$this->params['breadcrumbs'][] = 'Editar';
?>


<div class="auth-item-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
