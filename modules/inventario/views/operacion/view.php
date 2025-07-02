<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\producto\Producto;
use app\models\inv\InventarioOperacion;
use app\models\inv\InvProductoSucursal;


$this->title =  "Operacion: #" . $model->id;

$this->params['breadcrumbs'][] = ['label' => 'operaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
?>

<p>
    <?= $model->status == InventarioOperacion::STATUS_SOLICITUD ?
        Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>

    <?= $model->status == InventarioOperacion::STATUS_SOLICITUD ?
        Html::a('Cancel', ['cancel', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas cancelar esta SOLICITUD DE AJUSTE DE INVENTARIO?',
                'method' => 'post',
            ],
        ]): '' ?>
</p>

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
                        <span class="h1 font-bold m-t block btn-link"><?= $model->asignado->nombreCompleto ?></span>
                        <p><strong class="text-muted m-b block text-center">ENCARGADO A REALIZAR CONSULTA DE INVENTARIO</strong></p>
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
            <?php if ($model->status != InventarioOperacion::STATUS_SOLICITUD  && $model->status != InventarioOperacion::STATUS_SOLICITUD): ?>
            <div class="row">
                <?php if ($model->status == InventarioOperacion::STATUS_TERMINADO ): ?>
                <div class="col-sm-6">
                    <div class="widget style1 navy-bg">
                        <div class="row">
                            <div class="col-4">
                                <i class="fa fa-cubes fa-5x"></i>
                            </div>
                            <div class="col-8 text-right">
                                <span> CANTIDAD DE PRODUCTO ENCONTRADO </span>
                                <div class="row">
                                    <?php if (count(InventarioOperacion::cantidadEncontrada($model->id))): ?>
                                        <?php foreach (InventarioOperacion::cantidadEncontrada($model->id) as $key => $item_operacion): ?>
                                          <div class="col-sm-6">
                                              <h2 class="font-bold"><?= number_format($item_operacion["cantidad"],2) ?></h2>
                                              <p><?= Producto::$medidaList[$item_operacion["tipo_medida"]] ?></p>
                                          </div>
                                        <?php endforeach ?>
                                    <?php else: ?>
                                        <div class="col">
                                              <h2>0.00</h2>
                                              <p>---------</p>
                                          </div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="widget red-bg navy-bg">
                        <div class="row">
                            <div class="col-4">
                                <i class="fa fa-cubes fa-5x"></i>
                            </div>
                            <div class="col-8 text-right">
                                <span> CANTIDAD DE PRODUCTO NO ENCONTRADO </span>

                                <div class="row">
                                    <?php if (count(InventarioOperacion::cantidadPerdida($model->id))): ?>
                                        <?php foreach (InventarioOperacion::cantidadPerdida($model->id) as $key => $item_operacion): ?>
                                          <div class="col-sm-6">
                                              <h2 class="font-bold"><?= number_format($item_operacion["cantidad"],2) ?></h2>
                                              <p><?= Producto::$medidaList[$item_operacion["tipo_medida"]] ?></p>
                                          </div>
                                        <?php endforeach ?>
                                    <?php else: ?>
                                        <div class="col">
                                              <h2>0.00</h2>
                                              <p>---------</p>
                                          </div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif ?>
                <div class="col-sm-12">
                    <div class="widget lazur-bg p-xl">
                        <div class="row">
                            <div class="col-4">
                                <i class="fa fa-cubes fa-5x"></i>
                            </div>
                            <div class="col-8 text-right">
                                <strong> TOTAL DE PRODUCTO </strong>
                                <div class="row">
                                    <?php foreach (InventarioOperacion::cantidadTotal($model->id) as $key => $item_operacion): ?>
                                      <div class="col-sm-6">
                                          <h1 class="font-bold"><?=number_format( $item_operacion["cantidad"],2) ?></h1>
                                          <p><?= Producto::$medidaList[$item_operacion["tipo_medida"]] ?></p>
                                      </div>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif ?>
        </div>
        <div class="col-sm-3">
    		<div class="panel panel-warning">
                <div class="panel-body text-center">
                    <div class="row">
                        <div class="col">
                            <div class=" m-l-md">
                            	<span class="h5 font-bold m-t block"> <?= InventarioOperacion::$statusList[$model->status] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
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
            <div class="alert alert-info">
            	<strong>Se le ha notificado al usuario : [ <?= $model->asignado->nombreCompleto ?> ] para la revisión del inventario </strong>
            </div>

            <div class="panel">
                <?php if ($model->status == InventarioOperacion::STATUS_REVISION): ?>
                    <?= Html::a("<i class='fa fa-edit float-left' style='font-size:24px' ></i> AJUSTAR INVENTARIO", [ "ajustar-inventario", "id" => $model->id ] ,[ "class" => "btn btn-warning btn-lg btn-block btn-loading", "style" => "padding:5%; font-size:24px" ]) ?>
                    <?php endif ?>
                <?php if ($model->status == InventarioOperacion::STATUS_REVISION): ?>
                    <?= Html::a("<i class='fa fa-cubes float-left' style='font-size:24px' ></i> CARGAR INVENTARIO", [ "set-inventario-operador", "id" => $model->id ] ,[ "class" => "btn btn-info btn-lg btn-block btn-loading", "style" => "padding:5%; font-size:24px", "id" => "btnLoadInventarioOperador",'data' => [
                        'confirm' => '¿Estás seguro de que deseas carga  INVENTARIO DEL OPERADOR, esta operacion se realizara al inventario de ' .  $model->inventarioSucursal->nombre . '?',
                        'method' => 'post',
                    ]]) ?>
                <?php endif ?>
            </div>

            <?php if ($model->status == InventarioOperacion::STATUS_TERMINADO ): ?>
                <?= Html::Button("<i class='fa fa-file-pdf-o float-left' style='font-size:24px' ></i> REPORTE",[ "class" => "btn btn-danger btn-lg btn-block btn-loading", "style" => "padding:5%; font-size:24px", "id" => "btnReporteAcuse" ]) ?>
            <?php endif ?>
        </div>
    </div>


    <div class="ibox">
    	<div class="ibox-content">
    		<table class="table table-bordered">
    			<thead>
    				<tr >
    					<th>#</th>
    					<th>PRODUCTO</th>
    					<th class="text-center">CANTIDAD [INGRESADO POR <?= $model->asignado->nombreCompleto ?> ]</th>
                        <th class="text-center">INVENTARIO EN OPERACION </th>
                        <th>DIFERENCIA</th>
    					<th class="text-center">INVENTARIO ACTUAL  </th>
    				</tr>
    			</thead>
    			<tbody>
    				<?php $itemCount = 1 ?>
    				<?php foreach ($model->inventarioOperacionDetalles as $key => $item_operacion): ?>
    					<tr>
	    					<td><?= $itemCount ?></td>
	    					<td><p class="h5"><?= Html::a($item_operacion->producto->nombre, ['/inventario/arqueo-inventario/view', 'id'=> $item_operacion->producto_id], [ "class" => "", "target" => "_blank" ]) ?></p></td>

                            <?php if ($model->verificacion == InventarioOperacion::VERIFICACION_ON): ?>
                                    <td><p class="h5 text-right"><?= $item_operacion->cantidad_inventario ?> <?= Producto::$medidaList[$item_operacion->producto->tipo_medida] ?></p></td>
                            <?php else: ?>
                                    <td><p class="h5 text-right"><?= $item_operacion->cantidad_inventario ? $item_operacion->cantidad_inventario : '0' ?> <?= Producto::$medidaList[$item_operacion->producto->tipo_medida] ?></p></td>
                            <?php endif ?>



                            <td><p class="h5 text-right"><?= $item_operacion->cantidad_old ?> <?= Producto::$medidaList[$item_operacion->producto->tipo_medida] ?></p></td>

                            <?php if ($model->status != InventarioOperacion::STATUS_TERMINADO && $model->verificacion == InventarioOperacion::VERIFICACION_ON): ?>
                                <?php  $merma = floatval($item_operacion->cantidad_inventario) -  floatval(InvProductoSucursal::getInventarioActual($model->inventario_sucursal_id, $item_operacion->producto_id)) ?>
                                <td><p class="h5 text-right" style="color: <?= $merma >= 0 ?( $merma > 0  ?  "#538f14" : "" )  : "#ff0707" ?>; font-weight:700" ><?= $merma >= 0 ? ($merma > 0 ? "+"  : "") : "-" ?><?= round($merma,2) ?> <?= Producto::$medidaList[$item_operacion->producto->tipo_medida] ?></p></td>
                            <?php else: ?>
                                <?php if ($model->status == InventarioOperacion::STATUS_TERMINADO): ?>
                                    <?php  $merma = floatval($item_operacion->cantidad_inventario) -  floatval($item_operacion->cantidad_old) ?>
                                    <td><p class="h5 " style="color: <?= $merma >= 0 ?( $merma > 0  ?  "#538f14" : "" )  : "#ff0707" ?>; font-weight:700" ><?= $merma >= 0 ? ($merma > 0 ? "+"  : "") : "" ?> <?= round($merma,2) ?> <?= Producto::$medidaList[$item_operacion->producto->tipo_medida] ?></p></td>
                                <?php else: ?>
                                    <td><p class="h5" style="opacity: 0.4;">0.00</p></td>
                                <?php endif ?>
                            <?php endif ?>

                            <?php if ($model->verificacion == InventarioOperacion::VERIFICACION_ON): ?>
                                    <td style="background: #cbb70e;color: #fff;"><p class="h5 text-right" ><?= floatval(InvProductoSucursal::getInventarioActual($model->inventario_sucursal_id, $item_operacion->producto_id)) ?> <?= Producto::$medidaList[$item_operacion->producto->tipo_medida] ?></p></td>
                            <?php else: ?>
                                    <td><p class="h5" style="opacity: 0.4;">0.00</p></td>
                            <?php endif ?>

	    				</tr>
	    			<?php $itemCount = $itemCount + 1 ?>
    				<?php endforeach ?>
    			</tbody>
    		</table>
    	</div>
    </div>
</div>

<script>

    var $btnLoadInventarioOperador = $('#btnLoadInventarioOperador');

$btnLoadInventarioOperador.click(function(){
    show_loader();
    setInterval(function(){ hide_loader(); }, 2000);
})

var show_loader = function(){
    $('body').append('<div  id="page_loader" style="opacity: .8;z-index: 2040 !important;    position: fixed;top: 0;left: 0;z-index: 1040;width: 100vw;height: 100vh;background-color: #000;"><div class="spiner-example" style="position: fixed;top: 50%;left: 0;z-index: 2050 !important; width: 100%;height: 100%;"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div>');
}


var hide_loader = function(){
    $('#page_loader').remove();
}


var $btnReporteAcuse        = $('#btnReporteAcuse');
    VAR_AJUSTE_INVENTARIO   = <?= $model->id ?>;

$btnReporteAcuse.click(function(event){
    window.open('<?= Url::to(['imprimir-acuse-operacion']) ?>?id=' + VAR_AJUSTE_INVENTARIO,
        'imprimir',
        'width=600,height=600');
});

</script>