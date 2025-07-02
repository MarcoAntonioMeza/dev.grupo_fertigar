<?php
use app\models\user\User;
use app\models\esys\EsysDireccion;
use app\models\esys\EsysCambiosLog;

/* @var $this yii\web\View */
/* @var $model common\models\ViewUser */

$this->title = $model->nombreCompleto;
$this->params['breadcrumbs'][] = 'Administración';
$this->params['breadcrumbs'][] = ['label' => 'Usuarios internos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Historial de cambios';
?>

<div class="historial-cambios">
    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title">Historial de cambios</h3>
        </div>
        <div class="panel-body historial-cambios">
            <?= EsysCambiosLog::getHtmlLog([
                [new User(), $model->id],
                [new EsysDireccion(), $model->direccion->id],
            ]) ?>
        </div>
    </div>
</div>
