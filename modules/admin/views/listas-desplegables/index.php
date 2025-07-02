<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\esys\EsysListaDesplegable;
use app\assets\EsysListasDesplegablesAsset;


EsysListasDesplegablesAsset::register($this);

$this->title = 'Listas desplegables';
$this->params['breadcrumbs'][] = 'Administración';
$this->params['breadcrumbs'][] = 'Configuraciones';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="admin-listas-desplegables-index">
	<div id="listas-desplegables"
	    data-src='<?= Url::to(['.']) ?>'>
		<div class="row">
			<div class="col-sm-9">
				<div class="ibox">
					<div class="ibox-content">
					    <div class="form-horizontal">
					        <div class="form-group">
					            <label class="col-sm-2 control-label" for="modulo_id">Seleccionar módulo</label>
					            <div class="col-sm-4">
					                <?= Html::dropDownList('modulo_id', null, EsysListaDesplegable::getModulos(), ['class' => 'form-control']) ?>
					            </div>
					        </div>
					        <div class="form-group">
					            <label for="lista_id" class="col-sm-2 control-label">Seleccionar lista desplegable</label>
					            <div class="col-sm-4">
					                <select name="lista_id" class='form-control'></select>
					            </div>
					        </div>
					    </div>
					</div>
				</div>
			</div>
		</div>

	    <hr>

	    <div class="row">
	        <div class="col-sm-6 panel lista-items">
	            <div class="panel-heading ibox-title">
	                <h5 class="panel-title">&nbsp;</h5>
	            </div>
	            <div class="panel-body ibox-content"></div>
	        </div>

	        <div class="col-sm-6">

	            <div class="pull-left pad-rgt">
	                <?= $can['create']? Html::button('Agregar elemento', ['class' => 'btn btn-default btn-cfg-listas add']): '' ?>
	                <?= $can['update']? Html::button('Renombrar elemento', ['class' => 'btn btn-default btn-cfg-listas rename']): '' ?>
	                <?= $can['delete']? Html::button('Eliminar elementos', ['class' => 'btn btn-danger btn-cfg-listas del', "data-loading-text" => "Eliminando...", "autocomplete" => "off"]): '' ?>
	                <?= $can['create'] || $can['update']? Html::button('Guardar orden', ['class' => 'btn btn-success btn-cfg-listas saveOrder', "data-loading-text" => "Guardando...", "autocomplete" => "off"]): '' ?>
	            </div>
	            <div>
	                <?php if($can['update']): ?>
	                    <p><i class="fa fa-info-circle"></i> Arrastra los elementos para reposicionarlos</p>
	                <?php endif ?>
	                <?php if($can['update'] || $can['delete']): ?>
	                    <p>Selecciona un elemento para renombrarlo o eliminarlo</p>
	                <?php endif ?>
	            </div>

	        </div>
	    </div>
	    <!-- Modal form -->
	    <div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="titleModal">
	        <div class="modal-dialog" role="document">
	            <div class="modal-content">
	                <div class="modal-header">
	                    <button type="button" class="close" data-dismiss="modal" aria-label="Cancelar"><span aria-hidden="true">&times;</span></button>
	                    <h4 class="modal-title" id="titleModal"></h4>
	                </div>
	                <div class="modal-body">
	                    <div class="form-horizontal">
	                        <!--
	                        <div class="form-group">
	                            <div id="alertModal"></div>
	                        </div>
	                        -->
	                        <div class="form-group singular">
	                            <label class="col-sm-3 control-label" for="input">Nombre</label>
	                            <div class="col-sm-8">
	                                <?= Html::input('text', 'singular', null, ['class' => 'form-control', 'placeholder' => 'Nombre de elemento de lista']) ?>
	                            </div>
	                        </div>

	                        <div class="div_form_envios" style="display: none">
								<div class="form-group">
									<div class="row">
										<div class="col-sm-8 col-sm-offset-2">
											<div class="checkbox">
			                                    <input id="check_aplica_costo" class="magic-checkbox" type="checkbox" >
			                                  	<label class=" control-label" for="check_aplica_costo">¿ Aplicar un costo extra a la categoria ?  </label>
			                                </div>
			                            </div>
									</div>
		                        </div>
		                    </div>


	                        <div class="form_envios" style="display: none">
	                    		<div class="form-group required_min">
		                            <label class="col-sm-3 control-label" for="input">A partir de : </label>
	                            	<div class="col-sm-8">
	                                	<?= Html::input('number', 'required_min', null, ['class' => 'form-control', 'placeholder' => 'Apartit de: ']) ?>
	                                </div>
		                        </div>
	                    		<div class="form-group costo_extra">
		                            <label class="col-sm-3 control-label" for="input">Costo extra</label>
		                            <div class="col-sm-8">
		                            	<div class="input-group mar-btm">
		                                	<?= Html::input('text', 'costo_extra', null, ['class' => 'form-control', 'placeholder' => 'Costo extra']) ?>
		                            		<span class="input-group-addon">USD</span>
		                            	</div>
		                            </div>
		                        </div>
		                        <div class="form-group intervalo">
		                            <label class="col-sm-3 control-label" for="input">Intervalo</label>
		                            <div class="col-sm-8">
		                                <?= Html::input('text', 'intervalo', null, ['class' => 'form-control', 'placeholder' => 'Intervalo']) ?>
		                            </div>
		                        </div>
	                        </div>
	                        <div class="form-group plural">
	                            <label class="col-sm-3 control-label" for="input">En plural</label>
	                            <div class="col-sm-8">
	                                <?= Html::input('text', 'plural', null, ['class' => 'form-control', 'placeholder' => 'Nombre en plural']) ?>
	                            </div>
	                        </div>

	                    </div>
	                </div>
	                <div class="modal-footer">
	                    <?= Html::button('Cancelar', ['class' => 'btn btn-white', 'data-dismiss' => "modal"]) ?>
	                    <?= Html::button(null, ['class' => 'btn btn-primary submit', 'data-loading-text' => "Guardando...", "autocomplete" => "off"]) ?>
	                </div>
	            </div>
	        </div>
	    </div>
	</div>
</div>
<!--<script src="https://tpv.pescadosymariscosarroyo.com/web/assets/a0612554/esysListasDesplegables.jquery.js"></script>-->
<script type="text/javascript">
    $(document).ready(function(){
        $('#listas-desplegables').esysListasDesplegables();
    });
</script>
