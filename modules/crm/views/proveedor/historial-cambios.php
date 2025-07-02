<?php
use app\models\crm\CrmProveedor;
use app\models\esys\EsysCambioLog;
use app\models\esys\EsysCambioLog;

/* @var $this yii\web\View */
/* @var $model app\models\crm\CrmProveedor */

$this->title = 'Historial de cambios: ' . $model->proveedor;
$this->params['breadcrumbs'][] = ['label' => $model->area];
$this->params['breadcrumbs'][] = ['label' => 'CRM'];
$this->params['breadcrumbs'][] = ['label' => 'Proveedores', 'url' => ['index', 'tab' => 'index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="flexzone-proveedor-historial-cambios">
    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title">Historial de cambios</h3>
        </div>
        <div class="panel-body historial-cambios">
            <?= EsysCambioLog::getHtmlLog([
                [CrmProveedor::tableName(), $model->id, (new CrmProveedor())->attributeLabels()],
            ]) ?>
            <?= EsysCambioLog::getHtmlLog([
                [CrmProveedor::tableName(), $model->id, $model->sucursal_id,(new CrmProveedor())->attributeLabels()],
            ]) ?>
        </div>
    </div>
</div>
