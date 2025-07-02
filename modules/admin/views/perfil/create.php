<?php
/* @var $this yii\web\View */
/* @var $model app\models\AuthItem */

$this->title = 'Nuevo perfil de usuario';
$this->params['breadcrumbs'][] = 'Administración';
$this->params['breadcrumbs'][] = 'Configuraciones';
$this->params['breadcrumbs'][] = ['label' => 'Perfiles de usuarios', 'url' => ['index']];
?>


<div class="admin-perfil-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
