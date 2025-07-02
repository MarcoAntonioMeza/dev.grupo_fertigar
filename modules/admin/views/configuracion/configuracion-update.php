<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\esys\EsysSetting;
/* @var $this yii\web\View */
/* @var $model app\models\sucursales\Sucursal */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Configuraciones del Sitio';

?>

<div class="configuraciones-configuracion-form">
    <ul class="nav nav-tabs" role="tablist">
        <li>
            <a class="nav-link active" data-toggle="tab" href="#tab-carga-ruta">PAGARE DE CIERRE DE RUTA</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-lista-pedido">NOTIFICACIÓN DE RETIRO DE EFECTIVO</a>
        </li>

    </ul>
    <div class="tab-content">
            <div role="tabpanel" id="tab-carga-ruta" class="tab-pane active">
                <?php $form = ActiveForm::begin(['id' => 'form-configuracion' ]) ?>
                <div class="row">
                    <div class="col-lg-7">
                        <div class="ibox">
                            <div class="ibox-content">
                                <?php foreach ($model->configuracionAll as $key => $item): ?>

                                    <?php if ( $item->clave == EsysSetting::NOMBRE_SITIO ): ?>
                                        <div class="form-group">
                                            <?= Html::label("Nombre sitio", 'esysSetting_list') ?>

                                            <?= Html::input('text', 'esysSetting_list['.$item->clave.']',$item->valor,['class' => 'form-control']) ?>
                                        </div>

                                    <?php endif ?>

                                    <?php if ( $item->clave == EsysSetting::EMAIL_SITIO ): ?>
                                        <div class="form-group">
                                            <?= Html::label("Email del sitio", 'esysSetting_list') ?>

                                            <?= Html::input('text', 'esysSetting_list['.$item->clave.']',$item->valor,['class' => 'form-control']) ?>
                                        </div>
                                    <?php endif ?>


                                    <?php if ( $item->clave == EsysSetting::CORTE_CAJA_MONTO ): ?>
                                        <div class="form-group">
                                            <?= Html::label('MONTO [ CORTE DE CAJA ]', 'esysSetting_list') ?>
                                            <h3 class="lbl_corte_caja"></h3>
                                            <?= Html::input('text', 'esysSetting_list['.$item->clave.']',$item->valor,['class' => 'form-control', 'id' => 'inputCorteCaja']) ?>
                                        </div>
                                    <?php endif ?>

                                    <?php if ( $item->clave == EsysSetting::NOTIFICACION_CIERRE_REPARTO ): ?>
                                        <div class="form-group">
                                            <?= Html::label('PAGARES [ CIERRE DE RUTA ]', 'esysSetting_list') ?>
                                            <?= Html::input('text', 'esysSetting_list['.$item->clave.']',$item->valor,['class' => 'form-control']) ?>
                                        </div>
                                    <?php endif ?>

                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <?= Html::submitButton( 'Guardar cambios', ['class' =>  'btn btn-primary']) ?>
                    <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
            <div id="tab-lista-pedido"  role="tabpanel" class="tab-pane">
                <?php $form = ActiveForm::begin(['id' => 'form-configuracion' ]) ?>
                <div class="row">
                    <div class="col-lg-7">
                        <div class="ibox">
                            <div class="ibox-content">
                                <?php foreach ($model->configuracionAll as $key => $item): ?>
                                    <?php if ( $item->clave == EsysSetting::CORREOS_NOTIFICACION_RETIRO ): ?>
                                        <div class="form-group">
                                            <?= Html::label('CORREOS DE NOTIFICACIÓN', 'esysSetting_list') ?>
                                            <?= Html::input('text', 'esysSetting_list['.$item->clave.']',$item->valor,['class' => 'form-control']) ?>
                                        </div>
                                    <?php endif ?>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <?= Html::submitButton( 'Guardar cambios', ['class' =>  'btn btn-primary']) ?>
                    <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
    </div>
</div>

<script>
    $inputCorteCaja = $('#inputCorteCaja');

    $(function(){
        $inputCorteCaja.change();
    });

    $inputCorteCaja.on('keyup change',function(){
        $('.lbl_corte_caja').html(btf.conta.money($(this).val()));
    });

</script>