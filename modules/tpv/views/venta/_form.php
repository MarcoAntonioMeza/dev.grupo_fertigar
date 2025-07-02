<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use app\assets\BootboxAsset;
use app\models\sucursal\Sucursal;
use app\models\apertura\AperturaCaja;
use app\models\esys\EsysSetting;
/* @var $this yii\web\View */
/* @var $model app\models\sucursales\Sucursal */
/* @var $form yii\widgets\ActiveForm */
use app\models\cobro\CobroVenta;
use app\models\Esys;
use app\models\esys\EsysListaDesplegable;

BootboxAsset::register($this);

?>
<style>
.navbar-static-side {
    z-index: 1040;
}
.modal-backdrop{
    z-index: 1040 !important;
}

.modal {
    z-index: 1050 !important;;
}
</style>
<div class="tpv-venta-form">

    <?php $form = ActiveForm::begin([ "id" => "formVenta"]) ?>
    <?= $form->field($model->venta_detalle, 'venta_detalle_array')->hiddenInput()->label(false) ?>
    <?= $form->field($model->cobroVenta, 'cobroVentaArray')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'total')->hiddenInput()->label(false) ?>
    <?= Html::hiddenInput('venta_id', null,["id" => "venta_id"]) ?>
    <?= Html::hiddenInput('venta-cliente_id', null,["id" => "venta-cliente_id"]) ?>

    <?php if ($bloqueo): ?>
        <div class="row">
            <div class="col-md-8">
            </div>
            <div class="col-md-4">
                <div class="alert alert-warning">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    <strong><small>REALIZA UN RETIRO PROXIMAMENTE</small></strong>
                </div>
            </div>
        </div>

    <?php endif ?>
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <div class="ibox" >
                <div class="ibox-content" style="height: 550px">
                    <div class="row">
                        <?= Html::input("text",null,false,[ "class" => "form-control col-sm-9", "style" => "font-size:24px;", "id" => "inputFolioAdd", 'placeholder' => 'Ingresar FOLIO #...']) ?>
                        <?= Html::button('Buscar', ['class' => 'btn btn-primary btn-lg col-sm-3', "style" => "font-size:20px","id" => "btnFolioAdd"]) ?>
                    </div>
                    <br/>
                    <div class="alert alert-danger alert_danger_message" style="display: none">
                    </div>
                    <br>
                    <strong style="display:inline;"><h2 style="display:inline;">Cliente: </h2></strong><h2 class="lbl_cliente_venta" style="display:inline;"></h2>
                    <br>
                    <hr>
                    <div style="height: 100%">
                        <table class="table table-bordered" style="font-size: 9px">
                            <thead>
                                <tr>
                                    <th style="width: 10%" class="text-center">SUCURSAL</th>
                                    <th style="width: 30%" class="text-center">PRODUCTO</th>
                                    <th style="width: 30%" class="text-center">CANTIDAD</th>
                                    <th style="width: 30%" class="text-center">PRECIO COSTO</th>
                                </tr>
                            </thead>
                            <tbody class="content_producto" style="text-align: center;">
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr/>
            </div>
        </div>

        <div class="col-md-4 col-sm-12">
            <div class="div_ImprimirTicket" style="display: none;">
                <div class="panel">
                    <?= Html::Button("IMPRIMIR TICKET DE PREVENTA", ["class" => "btn btn-warning btn-lg btn-block", "style" => "padding: 5%", "id" => "btnImprimirTicket" ]) ?>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-content" style="height: 550px">
                    <div class="content_total text-center">
                        <div class="row">
                            <div class="col">
                                <span class="h1 font-bold m-t block lbl_total_venta"> $ 00.00 </span>
                                <small class="text-muted m-b block">TOTAL</small>
                                <small class="text-muted m-b block"><strong>CAJERO : </strong>  <?= Yii::$app->user->identity->nombre ." ". Yii::$app->user->identity->apellidos ?></small>
                            </div>


                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-4">
                            <button class="btn  <?= AperturaCaja::getAperturaActual() ? 'btn-success': 'btn-dark'   ?>  dim btn-large-dim"  <?= AperturaCaja::getAperturaActual() ? '' : 'disabled';  ?> type="button" id="btnClearVenta" ><i class="fa fa-close"></i></button>
                            <small class="text-muted m-b block">CANCELAR</small>
                        </div>
                        <!-- EL ARQUEO NO DEBE SER VISIBLE PARA EL USUARIO CAJA 1-->
                        <?php if (!Yii::$app->user->can('CAJERA')): ?>
                            <div class="col-4">
                                <button class="btn  <?= AperturaCaja::getAperturaActual() ? 'btn-success': 'btn-dark'   ?>  dim btn-large-dim" <?= AperturaCaja::getAperturaActual() ? '' : 'disabled';  ?> onclick="arqueo_caja_init()"  type="button" data-target="#modal-arqueo" data-toggle="modal"  ><i class="fa fa-exchange"></i></button>
                                <small class="text-muted m-b block">ARQUEO DE CAJA</small>
                            </div>
                        <?php endif ?>
                        <div class="col-4">
                            <button class="btn  <?= AperturaCaja::getAperturaActual() ? 'btn-success': 'btn-dark'   ?>  dim btn-large-dim" <?= AperturaCaja::getAperturaActual() ? '' : 'disabled';  ?> onclick="credito_init()" type="button" data-target="#modal-credito" data-toggle="modal"  ><i class="fa fa-credit-card"></i></button>
                            <small class="text-muted m-b block">CREDITOS</small>
                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-4">
                            <button class="btn  <?= AperturaCaja::getAperturaActual() &&  $can['caja'] ? 'btn-success': 'btn-dark'   ?>  dim btn-large-dim" <?= AperturaCaja::getAperturaActual() &&  $can['caja'] ? '' : 'disabled';  ?>  onclick="cierre_caja_init()" type="button" id="btnClearVenta" ><i class="fa fa-handshake-o"></i></button>
                            <small class="text-muted m-b block">CIERRE DE CAJA</small>
                        </div>
                        <div class="col-4">
                            <button class="btn  <?= AperturaCaja::getAperturaActual() ? 'btn-dark' : 'btn-success' ?>  dim btn-large-dim"  <?=  AperturaCaja::getAperturaActual() ? 'disabled' : '' ?> onclick="apertua_caja_init()" type="button" data-target="#modal-apertura" data-toggle="modal"  ><i class="fa fa-caret-square-o-right"></i></button>
                            <small class="text-muted m-b block">APERTURAR CAJA</small>
                        </div>
                        <div class="col-4">
                            <button class="btn  btn-success  dim btn-large-dim" type="button" data-target="#modal-producto" data-toggle="modal"  ><i class="fa fa-product-hunt"></i></button>
                            <small class="text-muted m-b block">PRODUCTOS</small>
                        </div>
                    </div>

                    <span class="tag label label-default"> [F8] - DEVOLUCIONES</span>

                    <?php if (AperturaCaja::getAperturaActual()): ?>
                        <span class="tag label label-default"> [F9] - RETIRO DE EFECTIVO</span>
                    <?php endif ?>
                    <?php if (AperturaCaja::getAperturaActual()): ?>
                        <span class="tag label label-default"> [F10] - REGISTRO DE GASTOS</span>
                    <?php endif ?>
                    <br>
                    <hr>
                    <?php if (AperturaCaja::getAperturaActual()): ?>
                        <div class="form-group" style="position: absolute;bottom: 5%;">
                            <?= Html::submitButton($model->isNewRecord ? 'GUARDAR' : 'Guardar cambios', ["id" => "btnOperacion",'class' => $model->isNewRecord ? 'btn btn-success btn-lg' : 'btn btn-primary btn-lg', 'style' => 'font-size: 24px' ]) ?>
                            <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white btn-lg' , 'style' => 'font-size: 24px']) ?>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>


<div class="display-none">
    <table>
        <tbody class="template_producto">
            <tr id = "producto_id_{{producto_id}}">
                <td width="10%"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_sucursal","style" => "text-align:center;font-size: 16px;font-weight: bold; color:#ff6f00"]) ?></td>
                <td width="30%"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_producto","style" => "text-align:center;font-size: 16px;font-weight: bold;"]) ?></td>
                <td width="30%"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_cantidad", "style" => "font-size:14px;"]) ?></td>
                <td width="30%"><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_precio", "style" => "text-align:center;font-size: 16px;font-weight: bold;"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>


<div class="display-none">
    <table>
        <tbody class="template_producto_search">
            <tr id = "producto_search_id_{{producto_search_id}}">
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_search_clave_id"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_search_producto","style" => "text-align:center; font-size: 16px;font-weight: bold;"]) ?></td>
                <td ><?= Html::tag('p', null,["class" => "text-main" , "id"  => "table_search_precio"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>


<div class="fade modal inmodal " id="modal-metodo-pago"   role="dialog" aria-labelledby="modal-create-label">
    <div class="modal-dialog modal-lg" style="width: 100%; max-width: 85%" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">METODO DE PAGO</h4>
            </div>
            <div class="modal-body">
                <?php // <h2><strong>Cliente: </strong><p class="lbl_cliente_venta"></p></h2> ?>
                <div class="alert alert-warning alert_forma_pago" style="display: none">

                </div>
                <h3>Metodos de pagos</h3>
                <div class="alert alert-warning alert_warning_pago" style="display:none">
                    <strong class="text-warning-pago"></strong>
                </div>
                <div style="border-style: double;padding: 2%;">
                    <div class="row"  >
                        <div class="col-sm-6">
                            <?= $form->field($model->cobroVenta, 'cantidad')->textInput(['type' => 'number','style' => 'font-size: 24px;font-weight: bold;']) ?>
                        </div>
                        <div class="col-sm-4">
                            <?= $form->field($model->cobroVenta, 'metodo_pago')->dropDownList(CobroVenta::$servicioTpvList,['style' => 'font-size: 24px;font-weight: bold;height: auto;'])->label("&nbsp;") ?>
                        </div>
                        <div class="col-sm-2">
                             <button  type="button"class="btn  btn-primary" id="btnAgregarMetodoPago" style="margin-top: 15px;" >Ingresar pago</button>
                        </div>
                    </div>
                    <div class="div_tpv_metodo_otro" style="padding:15px;display: none;">
                        <strong>NOTA / DESCRIPCIÓN</strong>
                        <?= Html::textArea("comentario",null,[ 'class' => 'form-control', 'rows' => '6', 'style' => 'border-color:#000;box-shadow: 0px 1px 6px #000;', 'id' => 'inputOtroTpvComentario' ]) ?>
                    </div>
                    <div class="row div_input_fecha" style="display: none">
                        <div class="col-sm-6">
                            <?= $form->field($model->cobroVenta, 'fecha_credito')->widget(DatePicker::classname(), [
                                'options' => ['placeholder' => 'FECHA'],
                                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                'language' => 'es',
                                'pickerIcon' => '<i class="fa fa-calendar"></i>',
                                'removeIcon' => '<i class="fa fa-trash"></i>',
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                ]
                            ])->label("FECHA A LIQUIDAR") ?>
                        </div>
                        <div class="col-sm-6 div_cliente_select" style="display: none">
                            <strong> CLIENTE A OTORGAR EL CREDITO: </strong>
                            <?= Select2::widget([
                                'id' => 'temp-cliente_id',
                                'name' => 'temp-cliente_id',
                                'data' => [],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'minimumInputLength' => 3,
                                    'language'   => [
                                        'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                    ],
                                    'ajax' => [
                                        'url'      => Url::to(['/crm/cliente/cliente-ajax']),
                                        'dataType' => 'json',
                                        'cache'    => true,
                                        'processResults' => new JsExpression('function(data, params){  return {results: data} }'),
                                    ],

                                ],
                                'options' => [
                                    'placeholder' => 'Buscar cliente',
                                    'style' => 'border-color: red;border-style: solid;border-width: 1px;'
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12" style="margin-top: 5%;">
                        <div class="table-responsive">
                            <table class="table table-hover table-vcenter" style="background: aliceblue;">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="text-center">Forma de pago</th>
                                        <th colspan="2" class="text-center">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody class="content_metodo_pago" style="text-align: center;">

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>
                                            <div class="widget navy-bg p-lg text-center">
                                                <span class="text-main text-semibold">TOTAL: </span>
                                                <strong id="total_metodo" style="font-size: 24px;">0</strong>
                                            </div>
                                        </td>

                                        <td style="border: none" >
                                            <div class="widget lazur-bg p-lg text-center">
                                                <span class="text-main text-semibold">COBRO: </span>
                                                <strong id= "pago_metodo_total" style="font-size: 24px;">0</strong>
                                            </div>
                                        </td>

                                        <td style="border: none" >
                                            <div class="widget red-bg p-lg text-center">
                                                <span class="text-main text-semibold">DEUDA: </span>
                                                <strong id= "balance_total" style="font-size: 24px;">0</strong>
                                            </div>
                                        </td>

                                        <td style="border: none;" >
                                            <div class="widget yellow-bg p-lg text-center">
                                                <span class="text-main text-semibold">CAMBIO: </span>
                                                <strong id="cambio_metodo" style="font-size: 24px;">0</strong>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::button('TERMINAR', ['class' => 'btn btn-primary btn-lg col-sm-3', "style" => "font-size:20px","id" => "btnTerminarAdd"]) ?>
            </div>
        </div>
    </div>
</div>


<div class="display-none">
    <table>
        <tbody class="template_metodo_pago">
            <tr id = "metodo_id_{{metodo_id}}">
                <td colspan="2"><?= Html::tag('p', "0",["class" => "text-main text-semibold" , "id"  => "table_metodo_id", "style" => "font-size: 24px;font-weight: bold;"]) ?></td>
                <td colspan="2">
                    <div class="row">
                        <div class="col-sm-8">
                            <?= Html::tag('p', "",["class" => "text-main " , "id"  => "table_metodo_cantidad","style" => "text-align:right;font-size: 24px;font-weight: bold;"]) ?>
                        </div>
                        <div class="col-sm-4 div_cargo_extra" style="display:none">
                            <?= Html::input('number', null,false,["class" => "form-control " , "id"  => "table_costo_extra","style" => "text-align:center;font-size: 24px;font-weight: bold;", "step" => "0.01" ]) ?>
                            <small>CARGO EXTRA</small>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>


<div class="display-none">
    <table>
        <tbody class="template_metodo_pago_credito">
            <tr id = "metodo_id_{{metodo_id_credito_}}">
                <td colspan="2"><?= Html::tag('p', "0",["class" => "text-main text-semibold" , "id"  => "table_credito_metodo_id", "style" => "text-align:center;font-size: 16px;font-weight: 500;"]) ?></td>
                <td colspan="2"><?= Html::tag('p', "",["class" => "text-main " , "id"  => "table_credito_metodo_cantidad","style" => "text-align:center;font-size: 16px;font-weight: 500;"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="display-none">
    <table>
        <tbody class="template_cuenta_abierta">
            <tr id = "cuenta_id_{{cuenta_id}}">
                <td><?= Html::tag('p', "0",["class" => "text-center text-semibold" , "id"  => "table_folio"]) ?></td>
                <td><?= Html::tag('p', "",["class" => "text-center " , "id"  => "table_cliente","style" => "text-align:center"]) ?></td>
                <td><?= Html::tag('p', "",["class" => "text-center " , "id"  => "table_total","style" => "text-align:center"]) ?></td>
                <td><?= Html::tag('p', "",["class" => "text-center " , "id"  => "table_creado","style" => "text-align:center"]) ?></td>
                <td><?= Html::tag('p', "",["class" => "text-center " , "id"  => "table_vendedor","style" => "text-align:center"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>



<div class="fade modal inmodal " id="modal-producto"    role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">BUSCAR PRODUCTO</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row panel">
                    <div class="col text-center panel-body">
                            <?= Html::input("text",null,false,[ "class" => "form-control ", "style" => "font-size:24px", "id" => "inputProductoSearch"]) ?>
                    </div>
                </div>
                <div style="height: 550px;overflow: scroll;" class="ibox-content">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 20%" class="text-center">CLAVE</th>
                                <th style="width: 20%" class="text-center">PRODUCTO</th>
                                <th style="width: 20%" class="text-center">PRECIO PUBLICO</th>
                                <th style="width: 20%" class="text-center">EXISTENCIAS</th>
                            </tr>
                        </thead>
                        <tbody class="content_search" style="text-align: center;">
                        </tbody>
                    </table>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<?php if (!AperturaCaja::getAperturaActual()): ?>
    <div class="fade modal inmodal " id="modal-apertura-caja"   role="dialog" aria-labelledby="modal-create-label"  >
        <div class="modal-dialog modal-lg" >
            <div class="modal-content">
                <!--Modal header-->
                 <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">APERTURA DE CAJA</h4>
                </div>
                <?php $apertura = ActiveForm::begin([ "id" => "formAperturaCaja", "action" => "apertura-caja-create"]) ?>
                <!--Modal body-->
                <div class="modal-body">
                    <div class="panel">
                        <div class="panel-body">
                                <small class="float-right"><?= Esys::fecha_en_texto(time())  ?></small>
                                <h2>CAJERO : <?= Yii::$app->user->identity->nombre ." ". Yii::$app->user->identity->apellidos ?> </h2>
                        </div>
                    </div>
                    <div class="row panel">
                        <div class="col text-center panel-body">
                            <h3>TOTAL EN CAJA</h3>
                            <?= Html::input("number","cantidad_apertura",0,[ "class" => "form-control text-center", "style" => "font-size:24px", "id" => "inputCantidadApertura"]) ?>
                        </div>
                    </div>
                </div>

                <!--Modal footer-->
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                    <?= Html::submitButton('APERTURAR', ['class' => 'btn btn-primary btn-lg col-sm-3', "style" => "font-size:20px","id" => "btnAperturaAdd"]) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
<?php endif ?>


<?php if (AperturaCaja::getAperturaActual()): ?>
    <div class="fade modal inmodal " id="modal-cierre-caja"   role="dialog" aria-labelledby="modal-create-label"  >
        <div class="modal-dialog modal-xl" >
            <div class="modal-content">
                <!--Modal header-->
                 <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">CIERRE DE CAJA</h4>
                </div>
                <?php $cerrarCaja = ActiveForm::begin([ "id" => "formCierreCaja"]) ?>
                <?= Html::hiddenInput('inputCuentaAbiertasArray',null, ["id" => "inputCuentaAbiertasArray"]) ?>
                <!--Modal body-->
                <div class="modal-body">
                    <div class="panel">
                        <div class="panel-body">
                                <small class="float-right"><?= Esys::fecha_en_texto(time())  ?></small>
                                <h2>VENDEDOR : <?= Yii::$app->user->identity->nombre ." ". Yii::$app->user->identity->apellidos ?> </h2>
                        </div>
                    </div>

                    <div class="tabs-container">
                        <ul class="nav nav-tabs" role="tablist">
                            <li><a class="nav-link active" data-toggle="tab" href="#tab-cierre-cuenta"> CIERRE DE CUENTA</a></li>
                            <li><a class="nav-link" data-toggle="tab" href="#tab-cuentas-abierta"> CUENTAS ABIERTAS</a></li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" id="tab-cierre-cuenta" class="tab-pane active">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col">
                                            <div class="row">
                                                <div class="col text-left">
                                                    <br>
                                                    <h3>+ MONTO DE APERTURA </h3>
                                                </div>
                                                <div class="col text-right">
                                                    <h2 class="lbl_cierre_monto_apertura"></h2>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col text-left">
                                                    <br>
                                                    <h3>+ VENTAS EN EFECTIVO</h3>
                                                </div>
                                                <div class="col text-right">
                                                    <h2 class="lbl_efectivo"></h2>
                                                </div>
                                                <hr>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col text-left">
                                                    <br>
                                                    <h3> - TOTAL DE RETIROS</h3>
                                                </div>
                                                <div class="col text-right">
                                                    <h2 class="lbl_retiro"></h2>
                                                </div>
                                                <hr>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col text-left">
                                                    <br>
                                                    <h3> - TOTAL DE GASTOS</h3>
                                                </div>
                                                <div class="col text-right">
                                                    <h2 class="lbl_gastos"></h2>
                                                </div>
                                                <hr>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-8 text-right">
                                                    <br>
                                                    <strong><h3>EFECTIVO EN CAJA</h3></strong>
                                                </div>
                                                <div class="col text-right">
                                                    <h2 class="lbl_efectivo_caja"></h2>
                                                </div>
                                                <hr>
                                            </div>
                                        </div>
                                        <div class="col offset-1">
                                            <div class="row">
                                                <div class="col text-left">
                                                    <br>
                                                    <h3>EFECTIVO</h3>
                                                </div>
                                                <div class="col text-right">
                                                    <h2 class="lbl_efectivo"></h2>
                                                </div>
                                            </div>
                                            <div class="row" id="divTransferencia">
                                                <div class="col text-left">
                                                    <br>
                                                    <h3>TRANFERENCIA</h3>
                                                </div>
                                                <div class="col text-right">
                                                    <h2 class="lbl_transferencia"></h2>
                                                </div>
                                            </div>
                                            <div class="row" id="divCheque">
                                                <div class="col text-left">
                                                    <br>
                                                    <h3>CHEQUE</h3>
                                                </div>
                                                <div class="col text-right">
                                                    <h2 class="lbl_cheque"></h2>
                                                </div>
                                            </div>
                                            <div class="row" id="divTarjCredito">
                                                <div class="col text-left">
                                                    <br>
                                                    <h3>TARJETA DE CREDITO</h3>
                                                </div>
                                                <div class="col text-right">
                                                    <h2 class="lbl_tarj_credito"></h2>
                                                </div>
                                            </div>
                                            <div class="row" id="divTarjDebito">
                                                <div class="col text-left">
                                                    <br>
                                                    <h3>TARJETA DE DEBITO</h3>
                                                </div>
                                                <div class="col text-right">
                                                    <h2 class="lbl_tarj_debito"></h2>
                                                </div>
                                            </div>
                                            <div class="row" id="divDeposito">
                                                <div class="col text-left">
                                                    <br>
                                                    <h3>DEPOSITO</h3>
                                                </div>
                                                <div class="col text-right">
                                                    <h2 class="lbl_deposito"></h2>
                                                </div>
                                            </div>
                                            <div class="row" id="divCredito">
                                                <div class="col text-left">
                                                    <br>
                                                    <h3>CREDITO</h3>
                                                </div>
                                                <div class="col text-right">
                                                    <h2 class="lbl_credito"></h2>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-8 text-right">
                                                    <br>
                                                    <strong><h3>VENTA TOTAL</h3></strong>
                                                </div>
                                                <div class="col text-right">
                                                    <h2 class="lbl_venta_total"></h2>
                                                </div>
                                                <hr>
                                            </div>
                                        </div>
                                    </div>
                                    <!--<div class="row">
                                        <div class="col text-center">
                                            <h3>TOTAL DE VENTA</h3>
                                            <h2 class="lbl_cierre_venta"></h2>
                                        </div>
                                        <div class="col text-center panel-body">
                                            <h3>TOTAL DE RETIROS</h3>
                                            <h2 class="lbl_retiro"></h2>
                                        </div>
                                        <div class="col text-center panel-body">
                                            <h3>MONTO DE APERTURA </h3>
                                            <h2 class="lbl_cierre_monto_apertura"></h2>
                                        </div>
                                        <div class="col text-center panel-body">
                                            <h3>TOTAL EN CAJA</h3>
                                            <h2 class="lbl_cierre_total_caja"></h2>
                                        </div>
                                    </div>-->
                                    <div class="row">
                                        <div class="col text-center">
                                            <h3>TOTAL</h3>
                                            <?= Html::input("number","cantidad_cierre",0,[ "class" => "form-control text-center", "style" => "font-size:24px", "id" => "inputCantidadCierre"]) ?>
                                        </div>
                                    </div>
                                    <div class="alert alert-danger alert-danger-cuenta" style="display:none">
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" id="tab-cuentas-abierta" class="tab-pane">
                                <div class="panel-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">FOLIO</th>
                                                <th class="text-center">CLIENTE</th>
                                                <th class="text-center">TOTAL</th>
                                                <th class="text-center">CREADO</th>
                                                <th class="text-center">VENDEDOR</th>
                                                <th class="text-center">CANCELAR</th>
                                                <th class="text-center">OMITIR</th>
                                            </tr>
                                        </thead>
                                        <tbody class="container_cuentas_abierta">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Modal footer-->
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                    <?= Html::submitButton('CERRAR', ['class' => 'btn btn-primary btn-lg col-sm-3', "style" => "font-size:20px","id" => "btnCierreAdd"]) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if (AperturaCaja::getAperturaActual()): ?>
<div class="fade modal inmodal " id="modal-arqueo-caja"   role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">ARQUEO DE CAJA</h4>
            </div>
            <?php $apertura = ActiveForm::begin([ "id" => "formAperturaCaja", "action" => "apertura-caja-create"]) ?>
            <!--Modal body-->
            <div class="modal-body">
                <div class="panel">
                    <div class="panel-body">
                            <small class="text-danger">TIEMPO APERTURADO : <?= Esys::hace_tiempo_en_texto(AperturaCaja::getInfoAperturaActual()->fecha_apertura)  ?></small>
                            <small class="float-right">FECHA DE APERTURA: <?= Esys::fecha_en_texto(AperturaCaja::getInfoAperturaActual()->fecha_apertura)  ?></small>
                            <h2>VENDEDOR : <?= Yii::$app->user->identity->nombre ." ". Yii::$app->user->identity->apellidos ?> - MONTO INICIAL : $ <?= number_format(AperturaCaja::getInfoAperturaActual()->cantidad_caja,2)  ?> </h2>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">TIPO</th>
                                    <th class="text-center">CLIENTE</th>
                                    <th class="text-center">FOLIO</th>
                                    <th class="text-center">TOTAL</th>
                                    <th class="text-center">FECHA</th>
                                </tr>
                            </thead>
                            <tbody class="table_container_arqueo">

                            </tbody>
                            <tfoot class="table_container_total">

                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php endif ?>

<?php if (AperturaCaja::getAperturaActual()): ?>
<div class="fade modal inmodal " id="modal-credito"   role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" style="width: 100%; max-width: 85%" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">CREDITO</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="alert alert-danger alert-credito-error" style="display: none">
                </div>
                <div class="alert alert-success alert-credito-success" style="display: none">
                </div>
                <div class="panel">
                    <div class="panel-body">
                        <?= Select2::widget([
                            'id' => 'cliente-cliente_id',
                            'name' => 'Cliente[nombre]',
                            'data' => [],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'minimumInputLength' => 3,
                                'language'   => [
                                    'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                ],
                                'ajax' => [
                                    'url'      => Url::to(['cliente-ajax']),
                                    'dataType' => 'json',
                                    'cache'    => true,
                                    'processResults' => new JsExpression('function(data, params){  return {results: data} }'),
                                ],

                            ],
                            'options' => [
                                'placeholder' => 'Buscar cliente',
                            ],
                        ]) ?>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <h1>TOTAL DE CREDITO : <strong class="lbl_total_credito">$0.00</strong></h1>
                            </div>
                            <div class="col-sm-6">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h2 class="font-bold text-warning text-right">TOTAL A PAGAR:</h2>
                                    </div>
                                    <div class="col-sm-6">

                                        <?= Html::input("text", false, null, [ "class" => "form-control text-center", "style" => "font-size:24px; font-weight: bold; height:100%;", "disabled" => true, "id" => "inputTotalPago", "autocomplete" => "off" ]) ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="table_credito" style="display: none">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <td class="text-center">ID</td>
                                        <td class="text-center">VENTA</td>
                                        <td class="text-center">DEUDA</td>
                                        <td class="text-center">REGISTRADO POR</td>
                                        <td class="text-center">FECHA OTORGADO</td>
                                        <td class="text-center">FECHA A PAGAR </td>
                                        <td class="text-center">PAGAR</td>
                                        <td class="text-center">CANTIDAD</td>
                                    </tr>
                                </thead>
                                <tbody class="container_table_credito">

                                </tbody>
                            </table>
                            <h3>METODOS DE PAGO</h3>
                            <div class="alert alert-danger alert_danger_credito" style="display:none">
                                <strong class="text-message-credito"></strong>
                            </div>


                            <div class="alert alert-warning alert_warning_credito" style="display:none">
                                <strong class="text-warning-credito"></strong>
                            </div>

                            <div class="row div-control-cargo-extra" style="display: none;">
                                <div class="col-sm-12">
                                    <div class="float-right">
                                        <h5><label for="credito-apply_cargoextra" class="control-label" >¿Aplica cargo extra?</label></h5>
                                        <p style="font-weight: bold;color: #000;">
                                            <label class="switch" >
                                                <input type="checkbox" id="credito-apply_cargoextra" checked>
                                                <span class="slider round"></span>
                                                <!-- <br><br> -->
                                            </label>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div style="border-style: double;padding: 2%;">
                                <div class="row"  >
                                    <div class="col-sm-6">
                                        <?= Html::label('Cantidad','Pago[cantidad]') ?>
                                        <?= Html::input("number",null,false,[ "class" => "form-control", "id" => "pago-cantidad", 'placeholder' => '$ 00.00', 'autocomplete'=> 'off','style' => 'font-size: 24px;font-weight: bold;']) ?>
                                    </div>
                                    <div class="col-sm-4">
                                        <?= Html::label('&nbsp;','Pago[metodo_pago]') ?>
                                        <?= Html::dropDownList('Pago[metodo_pago]', null, [
                                            CobroVenta::COBRO_EFECTIVO      => CobroVenta::$servicioList[CobroVenta::COBRO_EFECTIVO],

                                            CobroVenta::COBRO_TRANFERENCIA  => CobroVenta::$servicioList[CobroVenta::COBRO_TRANFERENCIA],

                                            CobroVenta::COBRO_TARJETA_CREDITO => CobroVenta::$servicioList[CobroVenta::COBRO_TARJETA_CREDITO],

                                            CobroVenta::COBRO_TARJETA_DEBITO => CobroVenta::$servicioList[CobroVenta::COBRO_TARJETA_DEBITO],

                                            CobroVenta::COBRO_CHEQUE  => CobroVenta::$servicioList[CobroVenta::COBRO_CHEQUE],

                                            CobroVenta::COBRO_DEPOSITO  => CobroVenta::$servicioList[CobroVenta::COBRO_DEPOSITO],

                                            CobroVenta::COBRO_OTRO  => CobroVenta::$servicioListAll[CobroVenta::COBRO_OTRO],
                                        ],  ['class' => 'form-control','id'=> 'pago-metodo_pago','style' => 'font-size: 24px;font-weight: bold;height: auto;']) ?>
                                    </div>

                                    <div class="col-sm-2">
                                         <button  type="button"class="btn  btn-primary" id="btnAgregarMetodoPagoCredito" style="margin-top: 15px;" >Ingresar pago</button>
                                    </div>
                                </div>
                                <div class="div_metodo_otro" style="padding:15px;display: none;">
                                    <strong>NOTA / DESCRIPCIÓN</strong>
                                    <?= Html::textArea("comentario",null,[ 'class' => 'form-control', 'rows' => '6', 'style' => 'border-color:#000;box-shadow: 0px 1px 6px #000;', 'id' => 'inputOtroComentario' ]) ?>
                                </div>
                            </div>
                            <table class="table table-hover table-vcenter" style="background: aliceblue;">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="text-center">Forma de pago</th>
                                        <th colspan="2" class="text-center">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody class="content_metodo_pago_credito" style="text-align: center;">

                                </tbody>
                            </table>

                            <div class="row">
                                <div class="col-sm-12 text-center" >
                                    <h2 class="text-warning lbl_pago_credito_cambio">$00.00</h2>
                                    <p><strong>CAMBIO</strong></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col text-center">
                                    <h3>MONTO</h3>
                                    <?= Html::input("number","cantidad_credito",0,[ "class" => "form-control text-center", "style" => "font-size:24px", "id" => "inputCantidadCredito", "disabled" => true]) ?>
                                </div>
                                <div class="col text-center">
                                    <h3>RESIDUO</h3>
                                    <?= Html::input("text","cantidad_residuo_credito",0,[ "class" => "form-control text-center", "style" => "font-size:24px", "id" => "inputCantidadResiduoCredito", "readonly" => true]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('COBRAR', ['class' => 'btn btn-primary btn-lg col-sm-3', "style" => "font-size:20px","id" => "btnCreditoAdd"]) ?>

                <?= Html::submitButton('IMPRIMIR TICKET', ['class' => 'btn btn-warning btn-lg col-sm-3', "style" => "font-size:20px; display:none","id" => "btnCreditoTicketAdd"]) ?>
            </div>
        </div>
    </div>
</div>
<?php endif ?>

<div class="fade modal inmodal " id="modal-devolucion"   role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">DEVOLUCIÓN</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="alert alert-danger alert-danger-devolucion" style="display: none">
                </div>
                <div class="ibox">
                    <div class="ibox-content">
                        <h5>SOLICITA ACCESSO AL GERENTE, PARA PODER CONTINUAR</h5>
                        <?= Html::label('Usuario', 'username') ?>
                        <?= Html::input('text', 'username', null, ['class' => "form-control", "id" => "inputUsername"]) ?>

                        <?= Html::label('Contraseña', 'username') ?>
                        <?= Html:: passwordInput('password', null, ['class' => "form-control", "id" => "inputPassword"]) ?>

                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::Button('ACCEDER', ['class' => 'btn btn-primary btn-lg col-sm-3', "style" => "font-size:20px","id" => "btnDevoluciondd"]) ?>
            </div>
        </div>
    </div>
</div>

<div class="fade modal inmodal " id="modal-retiro"   role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">RETIRO DE EFECTIVO</h4>
            </div>

            <?php $retiro = ActiveForm::begin([ "id" => "formRetiroCaja", "action" => "retiro-efectivo-caja", "method"=>"post"]) ?>

            <!--Modal body-->
            <div class="modal-body">
                <div class="alert alert-danger alert-danger-devolucion" style="display: none">
                </div>
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row">
<!--                            <div class="col-sm-4">-->
<!--                                <h2>$ --><?//= number_format(AperturaCaja::getTotalCaja(),2)  ?><!--</h2>-->
<!--                                <h5>TOTAL EN CAJA</h5>-->
<!--                            </div>-->
<!--                            <div class="col-sm-4">-->
<!--                                <h2 class="lbl_retiro_caja"></h2>-->
<!--                                <h5>TOTAL A RETIRAR</h5>-->
<!--                            </div>-->
<!--                            <div class="col-sm-4">-->
<!--                                <h2 class="lbl_caja_disponible"></h2>-->
<!--                                <h5>TOTAL DISPONIBLE</h5>-->
<!--                            </div>-->
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <?= Html::label('EFECTIVO A RETIRAR', 'efectivo') ?>
                                <?= Html::input('text', 'efectivo', EsysSetting::getCorteCaja(), ['class' => "form-control text-center", "id" => "inputTotalRetiro",  "style" => "font-size: 32px;"]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('RETIRAR EFECTIVO', ['class' => 'btn btn-primary btn-lg col-sm-3', "style" => "font-size:20px","id" => "btnRetiroCajadd"]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<div class="fade modal inmodal " id="modal-gasto"   role="dialog" aria-labelledby="modal-create-label"  >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">REGISTRO DE GASTO</h4>
            </div>

            <?php $retiro = ActiveForm::begin([ "id" => "formGasto", "action" => "registro-gasto-caja"]) ?>

            <!--Modal body-->
            <div class="modal-body">
                <div class="alert alert-danger alert-danger-devolucion" style="display: none">
                </div>
                <div class="ibox">
                    <div class="ibox-content">
<!--                        <div class="row">-->
<!--                            <div class="col-sm-4">-->
<!--                                <h2>$ --><?//= number_format(AperturaCaja::getTotalCaja(),2)  ?><!--</h2>-->
<!--                                <h5>TOTAL EN CAJA</h5>-->
<!--                            </div>-->
<!--                            <div class="col-sm-4">-->
<!--                                <h2 class="lbl_retiro_caja"></h2>-->
<!--                                <h5>MONTO DE GASTO</h5>-->
<!--                            </div>-->
<!--                            <div class="col-sm-4">-->
<!--                                <h2 class="lbl_caja_disponible"></h2>-->
<!--                                <h5>TOTAL DISPONIBLE</h5>-->
<!--                            </div>-->
<!--                        </div>-->
                        <hr>
                        <br>
                        <div class="row">
                            <div class="col-sm-6">
                                <?= Html::label('MONTO DE GASTO', 'efectivo_gasto') ?>
                                <?= Html::input('number', 'efectivo_gasto', EsysSetting::getCorteCaja(), ['class' => "form-control text-center", "id" => "inputTotalGasto",  "style" => "font-size: 32px;"]) ?>
                            </div>
                            <div class="col-sm-6">
                                <?= Html::label('Tipo de gastos', 'gasto') ?>
                                <?= Html::dropDownList('tipo_gasto_id', null, EsysListaDesplegable::getItems('tipo_de_gastos'), ['prompt' => 'Tipo de gastos','class' => "form-control"])  ?>
                            </div>
                        </div>
                        <div class="row">
                            <?= Html::label('Observaciòn', 'observacion') ?>
                            <?= Html::textArea('observacion',"",['class' => "form-control", 'id'=>'observacion', 'rows' => 2, 'readonly' => false]); ?>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('REGISTRAR GASTO', ['class' => 'btn btn-primary btn-lg col-sm-3', "style" => "font-size:20px","id" => "btnGastodd"]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<script>
    var $inputFolioAdd   = $('#inputFolioAdd'),
        $btnFolioAdd     = $('#btnFolioAdd'),

        $template_producto          = $('.template_producto'),
        $template_producto_search   = $('.template_producto_search'),
        $content_producto   = $(".content_producto"),
        $content_search     = $(".content_search"),
        $inputventaDetalle  = $("#ventadetalle-venta_detalle_array"),
        $inputventaArray    = $('#cobroventa-cobroventaarray'),
        $inputventaTotal        = $("#venta-total"),
        $btnTerminarAdd         = $("#btnTerminarAdd"),
        $btnCreditoAdd          = $("#btnCreditoAdd"),
        $btnDevoluciondd        = $("#btnDevoluciondd"),
        $btnRetiroCajadd        = $("#btnRetiroCajadd"),
        $btnClearVenta          = $("#btnClearVenta"),
        $inputSearchCliente     = $("#cliente-cliente_id"),
        $inputTotalRetiro       = $("#inputTotalRetiro"),
        $inputTotalGasto        = $("#inputTotalGasto"),
        $inputTotalPago         = $('#inputTotalPago'),
        //$inputProducto          = $('#producto-select_id'),
        //$inputCantidadRecibe    = $('#input-cantidad-recibe'),
        $inputOtroComentario         = $('#inputOtroComentario'),
        $inputOtroTpvComentario      = $('#inputOtroTpvComentario'),
        $btnAperturaAdd              = $('#btnAperturaAdd'),
        $template_cuenta_abierta     = $(".template_cuenta_abierta"),
        $btnImprimirTicket           = $("#btnImprimirTicket"),
        $divImprimirTicket           = $(".div_ImprimirTicket"),
        $container_cuentas_abierta   = $(".container_cuentas_abierta"),
        productoSearch      = [];
        metodoPago_array    = [];
        containerArray      = [];
        ventaArray          = [];
        creditoArray        = [];
        cuentaArray         = [];
        notaCreditoArray    = [],
        totalCaja           = "<?= AperturaCaja::getTotalCaja() ?>";
        isBloqueo           = '<?= $bloqueo ? 10 : 20  ?>';
        is_apertura_caja    = '<?= AperturaCaja::getAperturaActual() ? 10 : 20 ?>';
        VAR_CREDITO         = '<?= CobroVenta::COBRO_CREDITO ?>';
        VAR_EFECTIVO                = '<?= CobroVenta::COBRO_EFECTIVO ?>';
        VAR_TARJETA_CREDITO         = '<?= CobroVenta::COBRO_TARJETA_CREDITO ?>';
        VAR_DEBITO_CREDITO          = '<?= CobroVenta::COBRO_TARJETA_DEBITO ?>';
        VAR_COBRO_OTRO              = '<?= CobroVenta::COBRO_OTRO ?>';
        permissosCaja               = '<?= $can['caja'] ? 10 : 20  ?>';
        totalCargoExtra             = 0;
        $VAR_TOTAL_CREDITO          = 0;
        PREVENTA_ID                 = null;
        URL_PATH                    = "<?= Url::to(['/']) ?>";

    $(function(){
        $('body').addClass('mini-navbar');
        $inputTotalPago.mask('000,000,000,000,000.00', {reverse: true});
        $inputTotalRetiro.mask('000,000,000,000,000.00', {reverse: true});
        apertua_caja_init();
        if (isBloqueo == 10)
            function_aviso_caja();
    });

    $('#btnRetiroCajadd').click(function (e) {
        e.preventDefault();

        if( parseFloat($inputTotalRetiro.val()) > 0 ){
            show_loader();
            $('#modal-retiro').modal('hide');
            $('#btnRetiroCajadd').attr("disabled",true);
            $.ajax({
                type: $('#formRetiroCaja').attr('method'),
                url: $('#formRetiroCaja').attr('action'),
                data: $('#formRetiroCaja').serialize(),
                success: function ($response) {
                    if ($response.code == 202) {
                        window.open("<?= Url::to(['imprimir-ticket-retiro']) ?>" + "?id=" + $response.data
                        ,'imprimir', 'width=600,height=500');
                        location.reload();
                    }else{
                        toastr.options = {
                            closeButton: true,
                            progressBar: true,
                            showMethod: 'slideDown',
                            timeOut: 5000
                        };
                        toastr.error('VERIFICA TU INFORMACIÓN, INTENTA NUEVAMENTE', 'RETIRO DE EFECTIVO');
                    }
                }
            });
        }else{
            
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };

            toastr.error('DEBES INGRESAR UN MONTO A RETIRAR', 'RETIRO DE EFECTIVO');
        }
    });


    $('#formCierreCaja').submit(function(ev) {
        ev.preventDefault();
        $.ajax({
                type: 'POST',
                url: 'apertura-caja-update',
                data: $(this).serialize(),
                success: function ($response) {
                    window.open("<?= Url::to(['/../web/caja/apertura-y-cierre/imprimir-reporte']) ?>" + "?id=" + $response.data
                    ,'imprimir', 'width=600,height=500');
                    location.reload();
            }
        });

    })

    $btnImprimirTicket.click(function(){
        window.open('<?= Url::to(['imprimir-ticket']) ?>?id=' + PREVENTA_ID,
        'imprimir',
        'width=600,height=600');
    });

    var function_aviso_caja = function(){
        bootbox.confirm({
            message : "DEBES REALIZAR UN RETIRO DE CAJA PROXIMAMENTE, <br/> ¿ DESEAS REALIZAR EL RETIRO?",
             buttons: {
                confirm: {
                    label: 'SI [ RETIRO DE CAJA ]',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'No [ CONTINUAR ]',
                    className: 'btn-default'
                }
            },
            callback: function (result) {
                console.log('This was logged in the callback: ' + result);
            }
        });
    }

    $(document).on("keydown", function(e) {
        if (e.which == 119 ) {

            function_load_devolucion();
        }
        if (e.which == 120 ) {
            if (is_apertura_caja == 10) {
                function_load_retiro();
            }
        }
        if (e.which == 121 ) {
            if (is_apertura_caja == 10) {
                function_load_gasto();
            }
        }
    });

    var function_load_retiro = function(){
        $('#modal-retiro').modal('show');
        // $inputTotalRetiro.change();
    }

    var function_load_gasto = function(){
        $('#modal-gasto').modal('show');
        // $inputTotalGasto.change();
    }

    $btnDevoluciondd.click(function(){
        $('.alert-danger-devolucion').hide();
        $.get("<?= Url::to(['get-devolucion-valid']) ?>", { username : $('#inputUsername').val(), password : $('#inputPassword').val() } , function($response){
            if ( $response.code == 202 ) {
                window.location.href = "<?= Url::to(['/inventario/devolucion/create'])  ?>";
            }else{
                $('.alert-danger-devolucion').show();
                $('.alert-danger-devolucion').html("Ocurrio un error, intenta nuevamente");
            }
        },'json');
    });

    /*$inputProducto.change(function(){
        $('.lbl_tipo_producto').html(null);
        $.get("<?= Url::to(['get-producto']) ?>", { producto_id : $inputProducto.val() } , function($response){
            if ($response.code == 202)
                $('.lbl_tipo_producto').html("INGRESA EN " + $response.producto.tipo_text);

        },'json');
    });*/

    var function_load_devolucion = function(){
        $('#modal-devolucion').modal('show');
    }

    var apertua_caja_init = function(){
        if (is_apertura_caja == 20 && permissosCaja == 10)
            $('#modal-apertura-caja').modal('show');
        else
            $('#modal-apertura-caja').modal('hide');
    };

    var cierre_caja_init = function(){
        $('#modal-cierre-caja').modal('show');
        $('.lbl_cierre_venta').html(null);
        $('.lbl_efectivo').html(null);
        $('.lbl_credito').html(null);
        $('.lbl_retiro').html(null);
        $('.lbl_gastos').html(null);
        $('.lbl_efectivo_caja').html(null);
        $('.lbl_venta_total').html(null);
        $('.lbl_cierre_monto_apertura').html(null);
        $('.lbl_cierre_total_caja').html(null);
        $('#inputCantidadCierre').val(null);
        $('.alert-danger-cuenta').hide();
        cuentaArray = [];
        $('#inputCuentaAbiertasArray').val(null);

        $.get("<?= Url::to(['get-cierre-caja-monto']) ?>",function($response){
            $('.lbl_cierre_monto_apertura').html(btf.conta.money($response.monto_apertura));
            $('.lbl_efectivo').html(btf.conta.money($response.cierre_venta_efectivo));
            $('.lbl_credito').html(btf.conta.money($response.cierre_venta_credito));
            $('.lbl_cierre_venta').html(btf.conta.money($response.totalVenta));
            $('.lbl_retiro').html(btf.conta.money($response.totalRetiro));
            $('.lbl_gastos').html(btf.conta.money($response.totalGasto));
            $('.lbl_efectivo_caja').html(btf.conta.money($response.total_caja));
            $('.lbl_venta_total').html(btf.conta.money($response.totalVenta));
            $('.lbl_cierre_total_caja').html(btf.conta.money($response.total_caja));
            $('#inputCantidadCierre').val(parseFloat($response.total_caja).toFixed(2));

            if($response.totalTransferencia > 0){
                $('.lbl_transferencia').html(btf.conta.money($response.totalTransferencia));
                $('#divTransferencia').show();
            }else{
                $('#divTransferencia').hide();
            }
            if($response.totalCheque > 0){
                $('.lbl_cheque').html(btf.conta.money($response.totalCheque));
                $('#divCheque').show();
            }else{
                $('#divCheque').hide();
            }
            if($response.totalTarjCredito > 0){
                $('.lbl_tarj_credito').html(btf.conta.money($response.totalTarjCredito));
                $('#divTarjCredito').show();
            }else{
                $('#divTarjCredito').hide();
            }
            if($response.totalTarjDebito > 0){
                $('.lbl_tarj_debito').html(btf.conta.money($response.totalTarjDebito));
                $('#divTarjDebito').show();
            }else{
                $('#divTarjDebito').hide();
            }
            if($response.totalDeposito > 0){
                $('.lbl_deposito').html(btf.conta.money($response.totalDeposito));
                $('#divDeposito').show();
            }else{
                $('#divDeposito').hide();
            }
            if($response.totalCredito > 0){
                $('.lbl_credito').html(btf.conta.money($response.totalCredito));
                $('#divCredito').show();
            }else{
                $('#divCredito').hide();
            }

            get_cuentas_abierta();
        });
    }

    var get_cuentas_abierta = function(){

        $.get("<?= Url::to(['get-cuentas-abierta']) ?>", function($response){
            if ($response.code == 202) {
                if ($response.cuenta.length > 0) {
                   $('.alert-danger-cuenta').show();
                   $('.alert-danger-cuenta').html('<strong>* DEBES REVISAR LAS PRE-VENTAS QUE SE ENCUENTRAN ABIERTAS ANTES DE CONTINUAR *</strong>')
                }
                $.each($response.cuenta, function(key, cuenta){
                    cuentaArray.push({
                        "id" : cuenta.id,
                        "folio" : cuenta.folio,
                        "cliente" : cuenta.cliente,
                        "total" : cuenta.total,
                        "creado" : cuenta.creado,
                        "creado_por" : cuenta.creado_por,
                        "accion" : null,
                    });
                });

                $('#inputCuentaAbiertasArray').val(JSON.stringify(cuentaArray));

                render_cuenta();
            }
        });
    }

    var render_cuenta = function(){
        $container_cuentas_abierta.html(null);
        $.each(cuentaArray, function(key, cuenta){
            template_cuenta_abierta = $template_cuenta_abierta.html();
            template_cuenta_abierta = template_cuenta_abierta.replace("{{cuenta_id}}",cuenta.id);

            $container_cuentas_abierta.append(template_cuenta_abierta);


            $tr        =  $("#cuenta_id_" + cuenta.id, $container_cuentas_abierta);
            $tr.attr("data-item_id",cuenta.id);

            $("#table_folio",$tr).html(cuenta.folio);
            $("#table_cliente",$tr).html(cuenta.cliente);
            $("#table_total",$tr).html(btf.conta.money(cuenta.total));
            $("#table_creado",$tr).html( cuenta.creado);
            $("#table_vendedor",$tr).html( cuenta.creado_por);

            if (cuenta.accion == 10)
                $tr.append("<td class='text-center'><input type='checkbox' style=' transform: scale(2);'  checked onchange = 'refresh_cuenta(this,"+ cuenta.id +", 10)'></td>");
            else
                $tr.append("<td class='text-center'><input type='checkbox' style=' transform: scale(2);'  onchange = 'refresh_cuenta(this,"+ cuenta.id +",10)'></td>");


            if (cuenta.accion == 20)
                $tr.append("<td class='text-center'><input type='checkbox' style=' transform: scale(2);'  checked onchange = 'refresh_cuenta(this,"+ cuenta.id +",20)'></td>");
            else
                $tr.append("<td class='text-center'><input type='checkbox'  style=' transform: scale(2);'  onchange = 'refresh_cuenta(this,"+ cuenta.id +",20)'></td>");

        });
    }

    var refresh_cuenta = function(elem, $cuenta_id, $accion)
    {
        $.each(cuentaArray,function(key,cuenta){
            if (cuenta.id == $cuenta_id)
                cuentaArray[key].accion = $(elem).is(':checked') ? $accion : null;
        });
        render_cuenta();
        $('#inputCuentaAbiertasArray').val(JSON.stringify(cuentaArray));
    }

    $('#btnCierreAdd').click(function(e){
        e.preventDefault();
        $is_true = true;

        $.each(cuentaArray,function(key,cuenta){
            if (cuenta.accion == null) {
                $is_true = false;

            }
        });

        if ($is_true) {
            $('#btnCierreAdd').submit();
        }else{
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.error('EXISTEN CUENTAS ABIERTAS, DEBES REVISAR ANTES DE CONTINUAR', 'CIERRE DE CAJA');
        }
    })

    var credito_init = function(){
        $('#modal-credito').modal('show');
        $('.container_table_credito').html(null);
        $('#inputCantidadCredito').val(null);
        $('.table_credito').hide();
        $('.alert_warning_credito').hide();
        $('.text-warning-credito').html(null);
        $inputSearchCliente.val(null).change();
        creditoArray            = [];
        metodoPago_arrayCredito = [];
        render_metodo_template_credito();
    }

    var arqueo_caja_init = function(){
        $('#modal-arqueo-caja').modal('show');
        $('.table_container_arqueo').html(null);
        $.get('<?= Url::to(["get-arqueo-caja"]) ?>',function($response){
            content_html = "";
            totalArqueo  = 0;
            if ($response.code == 202) {
                $.each($response.apertura,function(key,item){

                    if (item.tipo == 30 || item.tipo == 40)
                        totalArqueo = totalArqueo - item.cantidad;
                    else
                        totalArqueo = totalArqueo + item.cantidad;

                    content_html += "<tr class='"+ ( item.tipo == 30 || item.tipo == 40 ? 'text-danger font-bold' : '')  + "'>"+
                        "<td class='text-center' style='font-size:12px; font-weight:700'>"+ item.cliente +"</td>"+
                        "<td class='text-center'>"+ item.tipo_text +"</td>"+
                        "<td class='text-center'>"+ ( item.tipo == 10 ? item.venta_id : item.credito_id  ) +"</td>"+
                        "<td class='text-center'>"+ ( item.tipo == 30 || item.tipo == 40 ? "- " : "" ) + btf.conta.money(item.cantidad)   +"</td>"+
                        "<td class='text-center'>"+ btf.time.datetime(item.created_at) +"</td>"+
                    "</tr>";
                });
            }

            $('.table_container_arqueo').html(content_html);
            $('.table_container_total').html("<tr>"+
                    "<td colspan='3' class='text-right'><strong>TOTAL TPV:</strong> </td>"+
                    "<td>" + btf.conta.money(totalArqueo) + "</td>"+
                "</tr>");
        },'json');
    }

    $inputTotalRetiro.on('keypress change',function(){
        $(".lbl_retiro_caja").html( btf.conta.money($(this).val()) );
        $(".lbl_caja_disponible").html( btf.conta.money( parseFloat(totalCaja) - $(this).val()  ) );

    });

    $inputTotalGasto.on('keypress change',function(){
        $(".lbl_retiro_caja").html( btf.conta.money($(this).val()) );
        $(".lbl_caja_disponible").html( btf.conta.money( parseFloat(totalCaja) - $(this).val()  ) );

    });


    $inputFolioAdd.keypress(function (e) {
      if (e.which == 13) {
        $(this).trigger("enterKey");
        return false;    //<---- Add this line
      }
    });

    $inputFolioAdd.bind("enterKey",function(e){
        containerArray   = [];
        ventaArray       = [];
        metodoPago_array = [];
        render_template();
        render_metodo_template();
        $('#venta-cliente_id').val(null).change();
        //add_folio();
        if ($inputFolioAdd.val()) {
            $content_producto.html('<div class="spiner-example"><div class="sk-spinner sk-spinner-wave"><div class="sk-rect1"></div><div class="sk-rect2"></div><div class="sk-rect3"></div><div class="sk-rect4"></div><div class="sk-rect5"></div></div></div>');
            $inputFolioAdd.attr("disabled",true);
            $btnFolioAdd.attr("disabled",true);
            setTimeout(add_folio, 1000);
        }else{
            $(".alert_danger_message").show();
            $(".alert_danger_message").html("Verifica tu información, intenta nuevamente !");
        }
    });

    $inputSearchCliente.change(function(){
        $('.container_table_credito').html(null);
        $('#inputCantidadCredito').val(null);
        $('.lbl_total_credito').html(false);
        $('.table_credito').hide();
        $('.lbl_pago_credito_cambio').html(false);
        creditoArray            = [];
        notaCreditoArray        = [];
        metodoPago_arrayCredito = [];
        $inputTotalPago.val(null);
        $inputTotalPago.attr("disabled",true);
        render_metodo_template_credito();
        if ($(this).val()) {
            $inputTotalPago.attr("disabled", false);
            $('.table_credito').show();
            content_html = "";
            $.get("<?= Url::to(['get-credito-cliente']) ?>",{ cliente_id : $(this).val() },function($response){
                if ($response.code == 202) {
                    $.each($response.credito, function(key, item_nota){
                        notaCreditoArray.push(item_nota);
                        notaCreditoArray[key].total_pago = 0;
                    })
                    render_notas_credito();
                }
            },'json');
        }
    });


    var render_notas_credito = function(){
        $VAR_TOTAL_CREDITO = 0;
        content_html = "";
        $('.container_table_credito').html(null);
        $.each(notaCreditoArray,function(key, credito){
            content_html += "<tr>"+
                "<td class='text-center'><a href='"+ URL_PATH +"/creditos/credito/view?id=" + credito.id +"' target='_black' style='font-size:24px; font-weight: bold' >"+ credito.id +"</a></td>"+
                "<td class='text-center'><a href='"+ '<?= Url::to(['/tpv/venta/view']) ?>' +"?id="+ credito.venta_id  +"' target='_blank' style='font-size:24px; font-weight: bold'>#"+ credito.venta_id +"</a></td>"+
                "<td class='text-center'>"+ btf.conta.money(credito.monto) +"</td>"+
                "<td class='text-center'><strong>"+  credito.created_by_user +"</strong></td>"+
                "<td class='text-center'>"+ btf.time.datetime(credito.created_at) +"</td>"+
                "<td class='text-center'>"+ btf.time.date(credito.fecha_credito) +"</td>";
                
                if (credito.is_check == 10) {
                    content_html += "<td class='text-center'><input type='checkbox' style='transform: scale(2.0);'  onchange = 'cobro_credito_function(this,"+ credito.id +")' checked></td>";
                }else{
                    content_html += "<td class='text-center'><input type='checkbox'  style='transform: scale(2.0);'  onchange = 'cobro_credito_function(this,"+ credito.id +")'></td>";
                }
               //"<td class='text-center'><input type='checkbox'  onchange = 'cobro_credito_function(this,"+ credito.id +", "+ parseFloat(credito.monto).toFixed(2) +",true)'></td>"+
                content_html += "<td class='text-center'><input type='number' class='form-control text-center' value='"+ credito.total_pago +"'  disabled/></td"+
            "</tr>";

            $VAR_TOTAL_CREDITO = $VAR_TOTAL_CREDITO + parseFloat(parseFloat(credito.monto).toFixed(2));
        });

        $('.lbl_total_credito').html(btf.conta.money($VAR_TOTAL_CREDITO));
        $('.container_table_credito').html(content_html);
    }


    $inputTotalPago.change(function(){
        totalPago = $inputTotalPago.val().replaceAll(',','');

        if ($VAR_TOTAL_CREDITO >= parseFloat(totalPago)){
            function_nota_pago(totalPago);
            metodoPago_arrayCredito = [];
            render_metodo_template_credito();
        }else{
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.error('El TOTAL A PAGAR no debe ser mayor al TOTAL DEL CREDITO');

            $inputTotalPago.val(0);
            function_nota_pago(0);
        }

    });

    var cobro_credito_function = function (elem,credito_id){

        if ($(elem).is(':checked')) {
            VAR_INI_CREDITO = credito_id;
            $.each(notaCreditoArray, function(key, item_credito){
                if (item_credito.id == credito_id) {
                    notaCreditoArray[key].is_check = 10;
                }else{
                    notaCreditoArray[key].is_check = 20;
                }
            });
        }else{
            $.each(notaCreditoArray, function(key, item_credito){
                notaCreditoArray[key].is_check = 20;
            });

            VAR_INI_CREDITO = null;
        }
        $('.container_table_credito').html("<div class='spiner-example'><div class='sk-spinner sk-spinner-double-bounce'><div class='sk-double-bounce1'></div><div class='sk-double-bounce2'></div></div></div>");

        creditoArray = [];
        setTimeout(refresh_dispercion_monto, 1000);
    }

    var refresh_dispercion_monto = function(){

        temNotaCreditoArray = [];
        if (VAR_INI_CREDITO) {
            $.each(notaCreditoArray, function(key,item_credito){
                if (item_credito.id == VAR_INI_CREDITO) {
                    temNotaCreditoArray.push(item_credito);
                }
            });

            $.each(notaCreditoArray, function(key,item_credito){
                if (item_credito.id != VAR_INI_CREDITO) {
                    temNotaCreditoArray.push(item_credito);
                }
            });
        }else{

            while(temNotaCreditoArray.length  < notaCreditoArray.length ){
                $.each(notaCreditoArray, function(key,item_credito){

                    is_sin_anex = true;
                    $.each(temNotaCreditoArray, function(key_anex,item_anex){
                        if (item_anex.id == item_credito.id) {
                            is_sin_anex = false;
                        }
                    });

                    if (is_sin_anex) {

                        is_add = true;
                        $.each(notaCreditoArray, function(key_search,item_search){
                            is_search = true;
                            $.each(temNotaCreditoArray, function(key_anex,item_anex){
                                if (item_anex.id == item_search.id) {
                                    is_search = false;
                                }
                            });

                            if (item_credito.created_at > item_search.created_at && item_search.id != item_credito.id && is_search) {
                                is_add = false;
                            }
                        });

                        if (is_add) {
                            temNotaCreditoArray.push(item_credito);
                        }
                    }
                });
            }
        }

        notaCreditoArray = temNotaCreditoArray;

        totalPago = $inputTotalPago.val().replaceAll(',','');

        if (parseFloat(totalPago) > 0 )
            function_nota_pago(parseFloat(totalPago));
        else
            function_nota_pago(0);
    }

    var refresh_dispercion_monto = function(){

        temNotaCreditoArray = [];
        if (VAR_INI_CREDITO) {
            $.each(notaCreditoArray, function(key,item_credito){
                if (item_credito.id == VAR_INI_CREDITO) {
                    temNotaCreditoArray.push(item_credito);
                }
            });

            $.each(notaCreditoArray, function(key,item_credito){
                if (item_credito.id != VAR_INI_CREDITO) {
                    temNotaCreditoArray.push(item_credito);
                }
            });
        }else{

            while(temNotaCreditoArray.length  < notaCreditoArray.length ){
                $.each(notaCreditoArray, function(key,item_credito){

                    is_sin_anex = true;
                    $.each(temNotaCreditoArray, function(key_anex,item_anex){
                        if (item_anex.id == item_credito.id) {
                            is_sin_anex = false;
                        }
                    });

                    if (is_sin_anex) {

                        is_add = true;
                        $.each(notaCreditoArray, function(key_search,item_search){
                            is_search = true;
                            $.each(temNotaCreditoArray, function(key_anex,item_anex){
                                if (item_anex.id == item_search.id) {
                                    is_search = false;
                                }
                            });

                            if (item_credito.created_at > item_search.created_at && item_search.id != item_credito.id && is_search) {
                                is_add = false;
                            }
                        });

                        if (is_add) {
                            temNotaCreditoArray.push(item_credito);
                        }
                    }
                });
            }
        }

        notaCreditoArray = temNotaCreditoArray;

        totalPago = $inputTotalPago.val().replaceAll(',','');

        if (parseFloat(totalPago) > 0 )
            function_nota_pago(parseFloat(totalPago));
        else
            function_nota_pago(0);
    }

    /*var cobro_credito_function = function (elem,credito_id, monto, is_check = false){

        if (is_check) {
            if ($(elem).is(":checked")) {
                $('#input_credito_' +  credito_id ).attr('disabled',true);
                $('#input_credito_' +  credito_id ).val(monto);

                $.each(creditoArray,function(key, item){
                    if (item) {
                        if (item.credito_id == credito_id)
                            creditoArray.splice(key,1);
                    }
                });

                creditoArray.push({
                    "credito_id" : credito_id,
                    "monto" : monto,
                });
            }else{
                $('#input_credito_' +  credito_id ).attr('disabled',false);
                $('#input_credito_' +  credito_id ).val(0);
                $.each(creditoArray,function(key, item){
                    if (item) {
                        if (item.credito_id == credito_id)
                            creditoArray.splice(key,1);
                    }
                });
            }
        }else{
            monto_input = parseFloat($('#input_credito_' +  credito_id ).val());
            monto_input = monto_input > monto ? monto : monto_input;

            $('#input_credito_' +  credito_id ).val(monto_input);

            is_add      = true;
            $.each(creditoArray,function(key, item){
                if (item) {
                    if (item.credito_id == credito_id){
                        is_add      = false;
                        item.monto  = monto_input;
                    }
                }
            });
            if (is_add) {
                creditoArray.push({
                    "credito_id" : credito_id,
                    "monto" : monto_input,
                });
            }
        }

        render_total_credito();
    }*/

    var function_nota_pago = function(totalPago)
    {
        pago_all  = parseFloat(totalPago);
        $.each(notaCreditoArray,function(key, credito){
            pago_register   = (  credito.monto - pago_all) > 0 ?   pago_all  : credito.monto;
            pago_all        = pago_all - pago_register;
            notaCreditoArray[key].total_pago = parseFloat(pago_register).toFixed(2);

            is_add      = true;
            $.each(creditoArray,function(key, item){
                if (item) {
                    if (item.credito_id == credito.id){
                        is_add      = false;
                        item.monto  = notaCreditoArray[key].total_pago;
                    }
                }
            });
            if (is_add) {
                creditoArray.push({
                    "credito_id" : credito.id,
                    "monto" : notaCreditoArray[key].total_pago,
                });
            }

        });
        render_notas_credito();
        render_total_credito();
    }

    var render_total_credito = function(){
        total = 0;
        $.each(creditoArray,function(key,credito){
            total = total + parseFloat(credito.monto);
        });
        $('#inputCantidadCredito').val(parseFloat(total).toFixed(2));
        render_metodo_template_credito();
    }

    $btnFolioAdd.click(function(){
        $(".alert_danger_message").hide();
        containerArray  = [];
        ventaArray      = [];
        metodoPago_array= [];
        render_template();
        PREVENTA_ID     = null;
        $divImprimirTicket.hide();
        render_metodo_template();
        $('#venta-cliente_id').val(null).change();
        if ($inputFolioAdd.val()) {
            //add_folio();
            $content_producto.html('<div class="spiner-example"><div class="sk-spinner sk-spinner-wave"><div class="sk-rect1"></div><div class="sk-rect2"></div><div class="sk-rect3"></div><div class="sk-rect4"></div><div class="sk-rect5"></div></div></div>');
            $inputFolioAdd.attr("disabled",true);
            $btnFolioAdd.attr("disabled",true);
            setTimeout(add_folio, 1000);
            $divImprimirTicket.show();
        }else{
            $(".alert_danger_message").show();
            $(".alert_danger_message").html("Verifica tu información, intenta nuevamente !");
        }
    });

    $btnClearVenta.click(function(){
        containerArray   =  [];
        ventaArray       =  [];
        metodoPago_array =  [];
        render_template();
        render_metodo_template();
        $('#venta-cliente_id').val(null).change();
    });

    $btnCreditoAdd.on('click',function(event){
        //event.preventDefault();

        show_loader();
        $('.alert-credito-error').html(null);
        $('.alert-credito-success').html(null);
        $('.alert-credito-error').hide();
        $('.alert-credito-success').hide();

        if (creditoArray.length > 0) {

            total = 0;

            total_ingresado = 0;

            $.each(creditoArray,function(key,credito){
                total = total + parseFloat(credito.monto);
            });

            total = parseFloat(parseFloat(total).toFixed(2));

            if (metodoPago_arrayCredito.length == 0) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                toastr.error('Ingresa un metodo de pago para continuar.');
                hide_loader();
                return false;
            }

            $.each(metodoPago_arrayCredito,function(key,credito){
                total_ingresado = total_ingresado + parseFloat(credito.cantidad);
            });

            if ( parseFloat(($('#inputCantidadCredito').val() ? $('#inputCantidadCredito').val() : 0 )) ==  0 ) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                toastr.error('La monto a pagar debe ser MAYOR A 0 !');

                /*$('.alert-credito-error').html('');
                $('.alert-credito-error').show();*/
                hide_loader();
                return false;
            }

            if ( parseFloat(($('#inputCantidadCredito').val() ? $('#inputCantidadCredito').val() : 0 )) < total ) {

                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                toastr.error("El monto a pagar debe ser de : $" + total + ", ingresa el monto correctamente");

                /*$('.alert-credito-error').html();
                $('.alert-credito-error').show();*/
                hide_loader();
                return false;
            }

            if (total > total_ingresado ) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 5000
                };
                toastr.error("El residuo debe ser $0.00, ingresa los pagos correspondientes");
                hide_loader();
                return false;
            }

            $.post("<?= Url::to(['post-credito-create']) ?>",{ listCredito : creditoArray, total : total, metodoPagoArray : metodoPago_arrayCredito  },function($response){
                if ($response.code == 202) {
                    //$btnCreditoAdd.hide();
                    //$('#btnCreditoTicketAdd').show();
                    creditoArray = [];
                    metodoPagoArray = [];
                    $('.alert-credito-success').html("SE REALIZO CORRECTAMENTE EL COBRO");
                    $('.alert-credito-success').show();
                    hide_loader();

                    window.open("<?= Url::to(['imprimir-credito']) ?>" + "?pay_items=" + $response.credito
                    ,'imprimir', 'width=600,height=500');

                    $inputSearchCliente.val(null).trigger('change');

                }else if($response.code == 10 ){
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        showMethod: 'slideDown',
                        timeOut: 5000
                    };
                    toastr.error("Ocurrio un error al procesar el pago, intenta nuevamente");

                    /*$('.alert-credito-error').html();
                    $('.alert-credito-error').show();*/
                    hide_loader();
                }
            });
        }else{

            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.error('Debes seleccionar un credito para generar un PAGO.');
            hide_loader();
        }
    });

    $('#temp-cliente_id').change(function(){
        $('#venta-cliente_id').val(null);
        if ($(this).val())
            $('#venta-cliente_id').val($(this).val());
    });

    $('#btnOperacion').on('click', function(event){
        event.preventDefault();

        if (!$('#venta_id').val()) {
            bootbox.alert("Debes ingresar un Folio, para continuar!");
            return false;
        }

        if (containerArray.length ==  0 ) {
            bootbox.alert("Debes ingresar minimo un producto para continuar !");
            return false;
        }

        $("#modal-metodo-pago").modal('show');
        $form_metodoPago.$metodoPago.val(VAR_EFECTIVO);
    });


    $btnTerminarAdd.on('click', function(event){

        show_loader();
        pago_total = 0;
        is_credito = false;
        $.each(metodoPago_array, function(key, metodo){
            if (metodo.metodo_id) {
                if (parseInt(metodo.metodo_pago_id) == VAR_TARJETA_CREDITO || parseInt(metodo.metodo_pago_id) == VAR_DEBITO_CREDITO )
                    pago_total = pago_total + parseFloat(parseFloat(metodo.cantidad).toFixed(2));// + parseFloat(metodo.cargo_extra);
                else
                    pago_total = pago_total + parseFloat(metodo.cantidad);

               //pago_total = pago_total + parseFloat(metodo.cantidad);
                if (metodo.metodo_pago_id == VAR_CREDITO )
                    is_credito = true;
            }
        });

        $('.alert_forma_pago').hide();
        $('.alert_forma_pago').html(null);



        if (parseFloat(parseFloat(pago_total).toFixed(2)) == parseFloat(parseFloat($inputventaTotal.val()).toFixed(2)) ){

            if (is_credito) {
                if (ventaArray.cliente_id) {
                    $('#formVenta').submit();
                }else{
                    if ($('#venta-cliente_id').val()) {
                        $('#formVenta').submit();
                    }else{
                        $('.alert_forma_pago').show();
                        $('.alert_forma_pago').html("DEBES SELECCIONAR UN CLIENTE PARA GENERAR LA NOTA DE CREDITO Ó CAMBIA EL METODO DE PAGO");
                        hide_loader();
                    }
                }
            }else{
                $('#formVenta').submit();
            }
        }
        else{
            $('.alert_forma_pago').show();
            $('.alert_forma_pago').html("El TOTAL PAGADO debe ser igual al TOTAL DE LA VENTA");
            hide_loader();
        }
    });

    var add_folio = function(){
        $(".alert_danger_message").hide();
        $('.lbl_cliente_venta').html(null);
        var $inputFolioAdd=$('#inputFolioAdd').val();
        if ($inputFolioAdd!="") {

            $.get("<?= Url::to(['get-pre-venta'])?>",{ id :  $inputFolioAdd },function($response){
                console.log($response);
                if ($response.code == 202) {
                    if ($response.errorcode == 50 ) {
                        $(".alert_danger_message").show();
                        $(".alert_danger_message").html("<strong>Error en los datos ingresados, no todos los datos ingresados pertenecen al mismo cliente o al público en general</strong>");
                        render_template();
                    }else {
                        PREVENTA_ID = $inputFolioAdd;
                        $content_producto.html(false);
                        if ($response.venta.status == 10) {
                            $(".alert_danger_message").show();
                            $(".alert_danger_message").html("<strong>La venta ya fue concretada, verifica tu información</strong>");
                        }

                        if ($response.venta.status == 20) {
                            $(".alert_danger_message").show();
                            $(".alert_danger_message").html("<strong>El folio que ingresaste es una Pre-Capturas, verifica tu información</strong>");
                        }

                        if ($response.venta.status == 40) {
                            $(".alert_danger_message").show();
                            $(".alert_danger_message").html("<strong>El folio que ingresaste es una Pre-Capturas que se encuentra en proceso de entrega, verifica tu información</strong>");
                        }

                        console.log( $response.venta.venta_detalle);
                        if ($response.venta.status == 30) {

                            productoArray = $response.venta;
                            ventaArray = $response.venta;

                            $('.lbl_cliente_venta').html($response.venta.cliente ? $response.venta.cliente : 'PUBLICO EN GENERAL');
                            $('#venta_id').val(productoArray.id);

                            $.each(productoArray.venta_detalle, function (key, item) {
                                productoItem = {
                                    "item_id": containerArray.length + 1,
                                    "sucursal_abastece": item.sucursal_abastece,
                                    "producto_nombre": item.producto,
                                    "producto_clave": item.clave,
                                    "producto_unidad": item.producto_unidad,
                                    "cantidad": item.cantidad,
                                    "precio_venta": item.precio_venta,
                                }
                                containerArray.push(productoItem);
                            });

                            productoArray = [];
                            render_template();

                        }

                        $inputFolioAdd.val(null).change();
                    }
                }

                if ($response.code == 10 ) {
                    $(".alert_danger_message").show();
                    $(".alert_danger_message").html($response.message);
                    $content_producto.html(false);
                }

                $('#inputFolioAdd').attr("disabled",false);
                $btnFolioAdd.attr("disabled",false);
            });
        }
    };


    /*====================================================
    *               RENDERIZA TODO LOS PAQUETE
    *====================================================*/
    var render_template = function()
    {
        $content_producto.html("");
        total_venta = 0;

        $.each(containerArray, function(key, producto){
            if (producto.item_id) {
                template_producto = $template_producto.html();
                template_producto = template_producto.replace("{{producto_id}}",producto.item_id);

                $content_producto.append(template_producto);


                $tr        =  $("#producto_id_" + producto.item_id, $content_producto);
                $tr.attr("data-item_id",producto.item_id);

                if ($("#check_publico_access").is(':checked'))
                    producto.precio_venta = producto.publico ? producto.publico : producto.publico;

                if ($("#check_menudeo_access").is(':checked'))
                    producto.precio_venta = producto.menudeo ? producto.menudeo : producto.publico;

                if ($("#check_mayoreo_access").is(':checked'))
                    producto.precio_venta = producto.mayoreo ? producto.mayoreo : producto.publico;

                $("#table_sucursal",$tr).html(producto.sucursal_abastece);
                $("#table_producto",$tr).html(producto.producto_nombre + " ["+ producto.producto_clave +"]");
                $("#table_cantidad",$tr).html(producto.cantidad +" ("+ producto.producto_unidad +")");
                $("#table_precio",$tr).html( btf.conta.money(producto.precio_venta * producto.cantidad ) );


                total_venta = total_venta + (producto.precio_venta * producto.cantidad);

                //$tr.append("<td><button type='button' class='btn btn-warning btn-circle' onclick='refresh_paquete(this)'><i class='fa fa-trash'></i></button></td>");
            }
        });

        $('.lbl_total_venta').html(btf.conta.money(total_venta));
        $inputventaTotal.val(parseFloat(total_venta).toFixed(2));
        $('#total_metodo').html( btf.conta.money(total_venta) );

        $inputventaDetalle.val(JSON.stringify(containerArray));
    };


    var refresh_paquete = function(ele){
        $ele_tr        = $(ele).closest('tr');
        $ele_tr_id  = $ele_tr.attr("data-item_id");

        $.each(containerArray, function(key, paquete){
            if (paquete.item_id == $ele_tr_id ){
                containerArray.splice(key, 1 );
            }
        });
        $inputventaDetalle.val(JSON.stringify(containerArray));
        render_template();
    }



    $("#check_publico_access").change(function(){
        $( "#check_menudeo_access" ).prop( "checked", false );
        $( "#check_mayoreo_access" ).prop( "checked", false );
        render_template();
    });

    $("#check_menudeo_access").change(function(){
        $( "#check_publico_access" ).prop( "checked", false );
        $( "#check_mayoreo_access" ).prop( "checked", false );
        render_template();
    });

    $("#check_mayoreo_access").change(function(){
        $( "#check_publico_access" ).prop( "checked", false );
        $( "#check_menudeo_access" ).prop( "checked", false );
        render_template();
    });

    $btnAperturaAdd.click(function(){
        show_loader();
    });

    var show_loader = function(){
        $('body').append('<div  id="page_loader" style="opacity: .8;z-index: 2040 !important;    position: fixed;top: 0;left: 0;z-index: 1040;width: 100vw;height: 100vh;background-color: #000;"><div class="spiner-example" style="position: fixed;top: 50%;left: 0;z-index: 2050 !important; width: 100%;height: 100%;"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div>');
    }

    var hide_loader = function(){
        $('#page_loader').remove();
    }
</script>

<?= $this->render('venta_js/js_metodo_pago') ?>
<?= $this->render('venta_js/js_producto') ?>