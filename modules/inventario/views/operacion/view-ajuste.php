<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\producto\Producto;
use app\models\inv\InventarioOperacion;
use app\models\inv\InvProductoSucursal;
use app\models\inv\InventarioOperacionDetalle;


$this->title =  "AJUSTE DE INVENTARIO: #" . $model->id;
$this->params['breadcrumbs'][] = $model->id;
?>

<div class="inventario-ajuste-operacion-view">
	 <div class="row">
        <div class="col-sm-9">
            <div class="panel" >
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">SOLICITUD DE AJUSTE DE INVENTARIO</h3>
                    </div>
                </div>
                <div class="row" style="padding:5%">
                    <div class="col-sm-6 text-center">
                        <div class=" m-l-md">
                            <span class="h1 font-bold m-t block"><?= $model->inventarioSucursal->nombre ?></span>
                            <p><strong class="text-muted m-b block text-center"> SUCURSAL</strong></p>
                        </div>
                    </div>
                    <div class="col-sm-6 text-center">
                        <span class="h1 font-bold m-t block btn-link">
                            <?php if ($model->tipo == InventarioOperacion::TIPO_AJUSTE_PARCIAL): ?>
                                <?= count($model->inventarioOperacionDetalles) ?>
                            <?php else: ?>
                                <?= InventarioOperacion::getCountProducto($model->inventario_sucursal_id) ?>
                            <?php endif ?>
                        </span>
                        <p><strong class="text-muted m-b block text-center">N° DE PRODUCTOS</strong></p>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-sm-3 text-center">
                        <p  style="font-size:14PX; color: #000;" class=" font-bold m-t block">CREADO POR :  </p>
                    </div>
                    <div class="col-sm-3 text-center">
                        <span class=" font-bold m-t block"><?= $model->createdBy->nombreCompleto ?> [ <?= date("Y-m-d", $model->created_at) ?>]</span>
                    </div>
                    <div class="col-sm-3 text-center">
                        <p  style="font-size:14PX; color: #000;" class=" font-bold m-t block">MODIFICADO POR :
                    </div>
                    <div class="col-sm-3 text-center">
                        <?php if ($model->updated_by): ?>
                        	<span class=" font-bold m-t block"><?= $model->updatedBy->nombreCompleto ?> [ <?= date("Y-m-d", $model->updated_at) ?>]</span>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
    		<div class="panel">
                <?= Html::Button("<i class='fa fa-file-pdf-o float-left' style='font-size:24px' ></i> REPORTE",[ "class" => "btn btn-danger btn-lg btn-block", "style" => "padding:5%; font-size:24px", "id" => "btnReporteAcuse" ]) ?>
            </div>
            <div class="panel" style="background-color: #cbb70e;color: #fff;">
                <div class="panel-body text-center">
                    <div class="row">
                        <div class="col">
                            <div class=" m-l-md">
                            	<span class="h5 font-bold m-t block"> <?= InventarioOperacion::$tipoList[$model->tipo] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="panel">
                <?php if ($model->status == InventarioOperacion::STATUS_PROCESO || $model->status == InventarioOperacion::STATUS_SOLICITUD): ?>
                    <?= Html::a("<i class='fa fa-cubes float-left' style='font-size:24px' ></i> SUBIR", [ "load-inventario", "id" => $model->id ] ,[ "class" => "btn btn-warning btn-lg btn-block btn-loading", "style" => "padding:5%; font-size:24px" ]) ?>
                    <?php endif ?>
                <?php if ($model->status == InventarioOperacion::STATUS_PROCESO): ?>

                    <?= Html::a("<i class='fa fa-cubes float-left' style='font-size:24px' ></i> ENVIAR INVENTARIO", [ "send-inventario", "id" => $model->id ] ,[ "class" => "btn btn-info btn-lg btn-block btn-loading", "style" => "padding:5%; font-size:24px",'data' => [
                        'confirm' => '¿Estás seguro de que deseas enviar INVENTARIO?',
                        'method' => 'post',
                    ]]) ?>
                <?php endif ?>
            </div>
        </div>
    </div>

    <?php if ($model->tipo == InventarioOperacion::TIPO_AJUSTE_PARCIAL || $model->status == InventarioOperacion::STATUS_PROCESO): ?>
    <div class="ibox">
    	<div class="ibox-content">
    		<table class="table table-bordered">
    			<thead>
    				<tr>
    					<th>#</th>
    					<th>PRODUCTO</th>
    					<th>CANTIDAD [OPERADOR]</th>
    				</tr>
    			</thead>
    			<tbody>
    				<?php $itemCount = 1 ?>
    				<?php foreach ($model->inventarioOperacionDetalles as $key => $item_operacion): ?>
    					<tr style="<?= $item_operacion->tipo  == InventarioOperacionDetalle::TIPO_REMOVE ? 'background: #c7c045;': ''?>">
	    					<td><?= $itemCount ?></td>
                            <?php if ($item_operacion->tipo  == InventarioOperacionDetalle::TIPO_REMOVE): ?>
                                <td><p class="h5"><?= $item_operacion->producto->nombre ?> <strong  style="color: #ef0000;">** ESTE PRODUCTO SE ELIMINARA DE SU INVENTARIO DE <?= $model->inventarioSucursal->nombre ?>**</strong></p></td>
                            <?php else: ?>
                                <td><p class="h5"><?= $item_operacion->producto->nombre ?></p></td>
                            <?php endif ?>

                            <?php if ($model->verificacion == InventarioOperacion::VERIFICACION_ON): ?>
                                <?php if (floatval($item_operacion->cantidad_inventario) != floatval(InvProductoSucursal::getInventarioActual($model->inventario_sucursal_id, $item_operacion->producto_id))): ?>
                                    <td style="background: #cbb70e;"><p class="h5 text-right"><?= $item_operacion->cantidad_inventario ?> <?= Producto::$medidaList[$item_operacion->producto->tipo_medida] ?></p></td>
                                <?php else: ?>
                                    <td><p class="h5 text-right"><?= $item_operacion->cantidad_inventario ?> <?= Producto::$medidaList[$item_operacion->producto->tipo_medida] ?></p></td>
                                <?php endif ?>
                            <?php else: ?>
	    					  <td><p class="h5 text-right"><?= $item_operacion->cantidad_inventario ?> <?= Producto::$medidaList[$item_operacion->producto->tipo_medida] ?></p></td>
                            <?php endif ?>
	    				</tr>
	    			<?php $itemCount = $itemCount + 1 ?>
    				<?php endforeach ?>
    			</tbody>
    		</table>
    	</div>
    </div>
    <?php endif ?>
</div>

<script>
var $btnReporteAcuse        = $('#btnReporteAcuse');
    VAR_AJUSTE_INVENTARIO   = <?= $model->id ?>;

$btnReporteAcuse.click(function(event){
    window.open('<?= Url::to(['imprimir-acuse-pdf']) ?>?ajuste_inventario_id=' + VAR_AJUSTE_INVENTARIO,
        'imprimir',
        'width=600,height=600');
});



</script>