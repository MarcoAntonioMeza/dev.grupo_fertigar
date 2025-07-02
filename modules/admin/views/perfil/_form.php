<?php
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use app\assets\EsysPerfilesAsset;

EsysPerfilesAsset::register($this);

/* @var $this yii\web\View */
/* @var $model app\models\setting\Profile */
?>

<div class="perfiles-form">

    <?php $form = ActiveForm::begin(['layout' => 'horizontal']); ?>

    <div class="form-group">
	    <?= Html::submitButton($model->isNewRecord ? 'Crear perfil' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
		<?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white']) ?>
    </div>

	<div class="row">
		<div class="col-lg-10">
			<div class="ibox">
				<div class="ibox-title">
					<h5>Perfil de usuario</h5>
				</div>
				<div class="ibox-content">
					<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
					<?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
					<?= $form->field($model, 'old_name')->hiddenInput()->label(false) ?>
					<?= $form->field($model, 'privilegios_count')->hiddenInput(['class' => 'privilegios_count'])->label(false) ?>
				</div>
			</div>
		</div>
	</div>

	<?php foreach ($model->items as $key => $groupMain): ?>

		<div class="ibox">
			<div class="ibox-title">
				<h5>Permisos <?= $groupMain['name'] ?></h5>
			</div>
			<div class="ibox-content">
				<table class="privilegios table table-hover table-vcenter table-bordered">
					<thead>
						<tr>
							<th class="text-center" colspan="2" width="40%">
								<?= Html::checkbox(null, false, ["id" => "modulo-all-{$key}", "class" => "modulo magic-checkbox"]) ?>
								<?= Html::label(null, "modulo-all-{$key}", ["style" => "display:inline"]) ?>
								Modulo
							</th>
							<th class="text-center" width="12%">
								<?= Html::checkbox(null, false, ["id" => "view-all-{$key}", "class" => "view magic-checkbox"]) ?>
								<?= Html::label(null, "view-all-{$key}", ["style" => "display:inline"]) ?>
								Ver
							</th>
							<th class="text-center" width="12%">
								<?= Html::checkbox(null, false, ["id" => "create-all-{$key}", "class" => "create magic-checkbox"]) ?>
								<?= Html::label(null, "create-all-{$key}", ["style" => "display:inline"]) ?>
								Crear
							</th>
							<th class="text-center" width="12%">
								<?= Html::checkbox(null, false, ["id" => "update-all-{$key}", "class" => "update magic-checkbox"]) ?>
								<?= Html::label(null, "update-all-{$key}", ["style" => "display:inline"]) ?>
								Editar
							</th>
							<th class="text-center" width="12%">
								<?= Html::checkbox(null, false, ["id" => "cancel-all-{$key}", "class" => "cancel magic-checkbox"]) ?>
								<?= Html::label(null, "cancel-all-{$key}", ["style" => "display:inline"]) ?>
								Cancelar
							</th>
							<th class="text-center" width="12%">
								<?= Html::checkbox(null, false, ["id" => "delete-all-{$key}", "class" => "delete magic-checkbox"]) ?>
								<?= Html::label(null, "delete-all-{$key}", ["style" => "display:inline"]) ?>
								Eliminar
							</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($groupMain['grupos'] as $key2 => $grupo): $grupo_last = true; ?>
							<?php foreach ($grupo['subgrupos'] as $key3 => $subgrupo): ?>
								<tr>
									<?php if($grupo_last): ?>
										<td class="min-width text-center bg-light" rowspan="<?= sizeof($grupo['subgrupos']) ?>">
											<?= $grupo['icon'] ?>
										</td>
									<?php endif; $grupo_last = false ?>
									<td>
										<?= Html::checkbox(
											$subgrupo['view']? null: "Perfil[privilegios][{$subgrupo['subgrupo']}]",
											isset($model->privilegios[$subgrupo['subgrupo']]) || isset($model->privilegios[$subgrupo['subgrupo'] . 'View']),
											[
												"id"    => "subgrupo_{$subgrupo['subgrupo']}_access",
												"class" => "modulo magic-checkbox"
											]
										) ?>
										<?= Html::label(null, "subgrupo_{$subgrupo['subgrupo']}_access", ["style" => "display:inline"]) ?>
										<span class="text-main"><?= $subgrupo['description'] ?></span>
									</td>
									<td class="text-center">
										<?php if($subgrupo['view']): ?>
											<?= Html::checkbox(
												"Perfil[privilegios][{$subgrupo['subgrupo']}View]",
												isset($model->privilegios[$subgrupo['subgrupo'] . 'View']),
												[
													"id"    => "subgrupo_{$subgrupo['subgrupo']}_view",
													"class" => "view magic-checkbox",
												]
											) ?>
											<?= Html::label(null, "subgrupo_{$subgrupo['subgrupo']}_view", ["style" => "display:inline"]) ?>
										<?php endif ?>
									</td>
									<td class="text-center">
										<?php if($subgrupo['create']): ?>
											<?= Html::checkbox(
												"Perfil[privilegios][{$subgrupo['subgrupo']}Create]",
												isset($model->privilegios[$subgrupo['subgrupo'] . 'Create']),
												[
													"id"    => "subgrupo_{$subgrupo['subgrupo']}_create",
													"class" => "create magic-checkbox",
												]
											) ?>
											<?= Html::label(null, "subgrupo_{$subgrupo['subgrupo']}_create", ["style" => "display:inline"]) ?>
										<?php endif ?>
									</td>
									<td class="text-center">
										<?php if($subgrupo['update']): ?>
											<?= Html::checkbox(
												"Perfil[privilegios][{$subgrupo['subgrupo']}Update]",
												isset($model->privilegios[$subgrupo['subgrupo'] . 'Update']),
												[
													"id"    => "subgrupo_{$subgrupo['subgrupo']}_update",
													"class" => "update magic-checkbox",
												]
											) ?>
											<?= Html::label(null, "subgrupo_{$subgrupo['subgrupo']}_update", ["style" => "display:inline"]) ?>
										<?php endif ?>
									</td>
									<td class="text-center">
										<?php if($subgrupo['cancel']): ?>
											<?= Html::checkbox(
												"Perfil[privilegios][{$subgrupo['subgrupo']}Cancel]",
												isset($model->privilegios[$subgrupo['subgrupo'] . 'Cancel']),
												[
													"id"    => "subgrupo_{$subgrupo['subgrupo']}_cancel",
													"class" => "cancel magic-checkbox",
												]
											) ?>
											<?= Html::label(null, "subgrupo_{$subgrupo['subgrupo']}_cancel", ["style" => "display:inline"]) ?>
										<?php endif ?>
									</td>
									<td class="text-center">
										<?php if($subgrupo['delete']): ?>
											<?= Html::checkbox(
												"Perfil[privilegios][{$subgrupo['subgrupo']}Delete]",
												isset($model->privilegios[$subgrupo['subgrupo'] . 'Delete']),
												[
													"id"    => "subgrupo_{$subgrupo['subgrupo']}_delete",
													"class" => "delete magic-checkbox",
												]
											) ?>
											<?= Html::label(null, "subgrupo_{$subgrupo['subgrupo']}_delete", ["style" => "display:inline"]) ?>
										<?php endif ?>
									</td>
								</tr>
							<?php endforeach  ?>
						<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endforeach ?>



    <?php ActiveForm::end(); ?>
</div>


<script type="text/javascript">
    $(document).ready(function(){
    	$('.perfiles-form').esysPerfiles();

    });
</script>
