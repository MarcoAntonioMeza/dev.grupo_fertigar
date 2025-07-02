<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use app\assets\BootboxAsset;
use app\widgets\CreatedByView;
use app\models\producto\Producto;
use app\models\sucursal\Sucursal;
use app\models\inv\InvProductoSucursal;

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

BootboxAsset::register($this);


$this->title =  "AJUSTE DE INVENTARIO";

$this->params['breadcrumbs'][] = ['label' => 'Inventario', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'ajuste';
?>


<div class="inventario-ajuste-create">
	<?php $form = ActiveForm::begin(['id' => 'form-ajuste-inventario','action' => 'save-inventario-all' ]) ?>
	<h2>Numero de productos : <?= count(Producto::getItems())  ?> </h2>
	<?= $form->field($model, 'producto_array_update')->hiddenInput(["id" => "producto_array_update"])->label(false) ?>
	<div class="form-group">
	    <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white']) ?>
	    <?= Html::submitButton('GUARDAR AJUSTE DE INVENTARIO', ['class' => 'btn btn-primary', "id" => "btnSaveInventarioProductos" ]) ?>
	</div>
	<?php ActiveForm::end(); ?>
	<div class="container-producto" style="height: 550px; overflow-y: scroll; overflow-x: none;">
		<?php $count = 0; ?>
		<?php foreach (Producto::getItems() as $producto_id => $producto): ?>
			<?php $count = $count + 1; ?>
			<div class="row" style="border-style: solid;padding-top: 4%;margin: 1%;">

				<div class="col-md-2 col-sm-3 col-xs-4">
					<div class="success-bg bg-success" style="top: -21px;left: 0;width: 40px;text-align: center;height: 40px;border-radius: 50%;font-size: 16px;border: 3px solid #f1f1f1;position: absolute;text-align: center;">
						<small class=" text-white  text-center"><?= $count ?></small>
					</div>
					<div class="panel">
						<div class="panel-body">
							<h6> <?= $producto ?> </h6>
						</div>
					</div>
				</div>
				<div class="col-md-10 col-sm-9 col-xs-8">
					<div class="row">
						<?php foreach (Sucursal::getItems() as $sucursal_id => $sucursal): ?>
	                        <div class="col">
		                        <div class="form-group text-center">
		                            <?= Html::input('number', 'PRODUCTO['.$count.'][input_inv_cantidad]', isset(InvProductoSucursal::getStockProducto($producto_id,$sucursal_id)->cantidad) ?InvProductoSucursal::getStockProducto($producto_id,$sucursal_id)->cantidad : 0 ,[ 'id' => 'input_inv_cantidad','class' => 'form-control text-center', 'onchange' => 'setInventarioProducto('. $sucursal_id .',' . $producto_id . ',this)']) ?>
		                            <h5><?= $sucursal ?></h5>
		                        </div>
	                        </div>
	                    <?php endforeach ?>
					</div>
				</div>
			</div>
		<?php endforeach ?>
	</div>
</div>


<script>

	var $producto_array_update = $('#producto_array_update'),
		productos_array 	   = [];

	var setInventarioProducto = function($sucursal_id,$producto_id,$elem){

		$item = {
			"sucursal_id" : $sucursal_id,
			"producto_id" : $producto_id,
			"cantidad" 	  : $($elem).val() ? $($elem).val() : 0,
		}

		productos_array.push($item);

		$producto_array_update.val(JSON.stringify(productos_array));
	}

</script>