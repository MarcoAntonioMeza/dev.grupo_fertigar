<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\sucursal\Sucursal;
use app\models\inv\Operacion;
use app\models\producto\Producto;

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title =  "Folio: #" . str_pad($model->id,6,"0",STR_PAD_LEFT);

$this->params['breadcrumbs'][] = ['label' => 'Devoluciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
?>



<div class="inv-operacion-view">

    <div class="row">
        <div class="col-md-7">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información operación</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'almacenSucursal.nombre'

                        ],
                    ]) ?>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body text-center">
                    <div class="row">
                        <div class="col">
                            <div class=" m-l-md">
                            <span class="h5 font-bold m-t block"> <?= $model->getTotalOperacion()  ?></span>
                            <small class="text-muted m-b block">COSTO TOTAL  OPERACIÓN</small>
                            </div>
                        </div>
                        <div class="col">
                            <span class="h5 font-bold m-t block"> <?= $model->getTotalUnidades()  ?></span>
                            <small class="text-muted m-b block">UNIDADES</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h3 >PRODUCTO INGRESADOS</h3>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-bordered invoice-summary">
                            <thead>
                                <tr class="bg-trans-dark">
                                    <th class="min-col text-center text-uppercase">CLAVE</th>
                                    <th class="min-col text-center text-uppercase">PRODUCTO</th>
                                    <th class="min-col text-center text-uppercase">CANTIDAD</th>
                                    <th class="min-col text-center text-uppercase">U.M</th>
                                    <th class="min-col text-center text-uppercase">COSTO OPERACION</th>
                                    <th class="min-col text-center text-uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
                                <?php foreach ($model->operacionDetalles as $key => $item): ?>
                                    <tr>
                                        <td><a href="<?= Url::to(["/productos/producto/view", "id" => $item->producto->id  ])  ?>"><?= $item->producto->clave  ?></a></td>
                                        <td><?= $item->producto->nombre ?></td>
                                        <td><?= $item->cantidad  ?>        </td>
                                        <td><?= Producto::$medidaList[$item->producto->tipo_medida]  ?> </td>
                                        <td><?= $item->costo ? number_format($item->costo,2) : 0 ?> </td>
                                        <td></td>
                                    </tr>

                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
             <div class="panel">
                <?= Html::a('<i class="fa fa-print"></i> TICKET', false, ['class' => 'btn  btn-lg btn-block btn-success', 'id' => 'imprimir-etiqueta','style'=>'padding: 6%;'])?>
            </div>
            <div class="panel panel-success text-center">
                <div class="ibox-title">
                    <h2><?= Operacion::$tipoList[$model->tipo] ?></h2>
                </div>
            </div>
            <div class="panel panel-success text-center">
                <div class="ibox-title">
                    <h2><?= Operacion::$operacionList[$model->motivo] ?></h2>
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

            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>

<script>
$('#imprimir-etiqueta').click(function(event){
    event.preventDefault();
    window.open("<?= Url::to(['imprimir-etiqueta', 'id' => $model->id ])  ?>",
    'imprimir',
    'width=600,height=500');
});
</script>
