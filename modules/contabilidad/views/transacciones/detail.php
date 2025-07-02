<?php
use app\models\contabilidad\ContabilidadTransaccion;
use yii\helpers\Html;


$this->title = 'Configuracion de la Transaccion';
$this->params['breadcrumbs'][] = 'Contabilidad';
$this->params['breadcrumbs'][] = ['label' => 'TRANSACCIONES', 'url' => ['index']];
$this->params['breadcrumbs'][] = '#'.$des_cuentas['transaccion_cuenta'];
?>


<div class="administracion-caja-view">
    
<?= Html::a('Editar configuracion', ['transacciones/update', 'id' => $model['id']], ['class' => 'btn btn-success', 'style' => 'margin-bottom: 25px']) ?>
    <div class="row">
        <div class="col-md-7">
            <div class="alert alert-success"> 
                <h2 >Asiento contable</h2>
            </div>
            <div class="ibox">
                <div class="ibox-content">
                    <div class="text-center">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">cuenta</th>
                                    <th scope="col">cargo</th>
                                    <th scope="col">abono</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($des_cuentas['asientos'] as $key => $asiento) {?>
                                <tr>
                                    <td><?= $asiento[0]->nombre; ?> [<?= $asiento[0]->code; ?>]</td>
                                    <td><?= $asiento['cargo'];?></td>
                                    <td><?= $asiento['abono'];?></td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>   
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="alert alert-warning">
                <h2><?= ContabilidadTransaccion::$tipoList[$model->tipo] ?> - <?= ContabilidadTransaccion::$motivoList[$model->motivo] ?></h2>
            </div>
                <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>
