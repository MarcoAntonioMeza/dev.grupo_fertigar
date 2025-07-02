<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\Esys;
use yii\widgets\ActiveForm;
use app\widgets\CreatedByView;
use app\models\sucursal\Sucursal;
use app\models\compra\Compra;
use app\models\producto\Producto;
use app\models\cobro\CobroVenta;


/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title = "FOLIO: #" . $model->id;

$this->params['breadcrumbs'][] = ['label' => 'Compras', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>

<link rel='stylesheet' type='text/css' href='https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.12.0/maps/maps.css'>

<p>
    <?php if ($model->status == Compra::STATUS_PROCESO): ?>
        <?= $can['cancel']?
        Html::a('Cancelar', ['cancel', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas cancelar la compra ?',
                'method' => 'post',
            ],
        ]): '' ?>
    <?php endif ?>
</p>
<div class="compras-compra-view">
    <div class="panel panel-info">
        <div class="ibox-title">
            <h5 ><?= Compra::$statusList[$model->status] ?></h5>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información compra</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'sucursal.nombre',
                            'proveedor.nombre',
                        ],
                    ]) ?>
                </div>
            </div>
            <?php if ($model->is_especial == Compra::COMPRA_ESPECIAL): ?>
                <div class="alert alert-warning" style="background-color:  #ffd44d; color: #fff">
                    <h5 class="text-center">COMPRA ESPECIAL</h5>
                </div>
            <?php endif ?>
            <div class="panel">
                <div class="panel-body">
                    <h3>NOTA / COMENTARIO</h3>
                    <hr>
                    <h5><?= $model->nota ?></h5>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body">
                     <div id='map' style="width: 100%;height: 300px;" class='map'></div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h3 >COMPRA</h3>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-bordered invoice-summary">
                            <thead>
                                <tr class="bg-trans-dark">
                                    <th class="min-col text-center text-uppercase">CLAVE</th>
                                    <th class="min-col text-center text-uppercase">PRODUCTO</th>
                                    <th class="min-col text-center text-uppercase">CANTIDAD</th>

                                    <?php if(!Yii::$app->user->can('ENCARGADO CEDIS SIN MONTOS')): ?>
                                        <th class="min-col text-center text-uppercase">COSTO X U.</th>
                                        <th class="min-col text-center text-uppercase">TOTAL</th>
                                    <?php endif ?>

                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
                                <?php foreach ($model->compraDetalles as $key => $item): ?>
                                    <tr>
                                        <td><a href="<?= Url::to(["/productos/producto/view", "id" => $item->producto->id  ])  ?>"><?= $item->producto->clave  ?></a></td>
                                        <td><?= $item->producto->nombre ?></td>
                                        <td><?= $item->cantidad  ?>        </td>

                                        <?php if(!Yii::$app->user->can('ENCARGADO CEDIS SIN MONTOS')): ?>
                                            <td>$<?= $item->costo  ?>        </td>
                                            <td>$ <?= number_format($item->cantidad * $item->costo,2) ?> </td>
                                        <?php endif ?>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-title">
                    <h3 >ENTRADA</h3>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-bordered invoice-summary">
                            <thead>
                                <tr class="bg-trans-dark">
                                    <th class="min-col text-center text-uppercase">CLAVE</th>
                                    <th class="min-col text-center text-uppercase">PRODUCTO</th>
                                    <th class="min-col text-center text-uppercase">CANTIDAD</th>
                                    <th class="min-col text-center text-uppercase">COSTO X U.</th>

                                    <th class="min-col text-center text-uppercase">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
                                <?php if ($model->entrada): ?>
                                    <?php foreach ($model->entrada->operacionDetalles as $key => $entrada): ?>
                                        <tr>
                                            <td><a href="<?= Url::to(["/productos/producto/view", "id" => $entrada->producto->id  ])  ?>"><?= $entrada->producto->clave  ?></a></td>
                                            <td><?= $entrada->producto->nombre ?></td>
                                            <td><?= $entrada->cantidad  ?>        </td>
                                            <td>$<?= $entrada->costo  ?>        </td>
                                            <td>$ <?= number_format($entrada->cantidad * $entrada->costo,2) ?> </td>
                                        </tr>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <?php if ($model->is_diferencia == Compra::IS_DIFERENCIA_ON): ?>
                <div class="alert alert-warning">
                    <strong>EXISTIO UNA DIFERENCIA Y SE ACTUALIZO EL TOTAL DE LA COMPRA $<?= number_format($model->total,2) ?> a $<?= number_format( $model->total_diferencia ,2)?> </strong>                </div>
            <?php endif ?>
            <div class="panel">
                <div class="panel-body text-center">
                    <div class="row">
                        <div class="col">
                            <h2><?= $model->fecha_salida ? Esys::fecha_en_texto($model->fecha_salida) : ''  ?></h2>
                            <small>FECHA DE SALIDA</small>
                        </div>
                    </div>

                    <?php if(!Yii::$app->user->can('ENCARGADO CEDIS SIN MONTOS')): ?>
                    <div class="row">
                        <div class="col">
                            <div class=" m-l-md">
                            <span class="h5 font-bold m-t block">
                                 <?php if ($model->is_diferencia == Compra::IS_DIFERENCIA_ON): ?>
                                    $ <?= number_format($model->total_diferencia,2)  ?>
                                 <?php else: ?>
                                    $ <?= number_format($model->total,2)  ?>
                                <?php endif ?>
                            </span>
                            <small class="text-muted m-b block">TOTAL</small>
                            </div>
                        </div>
                        <div class="col">
                            <span class="h5 font-bold m-t block"> <?= $model->tiempo_recorrido  ?> Hrs</span>
                            <small class="text-muted m-b block">TIEMPO DE RECORRIDO</small>
                        </div>
                    </div>
                    <?php endif ?>
                </div>
            </div>

            <?php if ($model->is_confirmacion == Compra::IS_CONFIRMACION_OFF && $model->status != Compra::STATUS_CANCEL ): ?>
                <div class="panel">
                    <?= Html::a('CONFIRMACIÓN COMPRA', null, ['class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;', 'data-target' => "#modal-confirmacion-compra", 'data-toggle' =>"modal"  ])?>
                </div>
            <?php else: ?>
                <div class="panel">
                    <div class="panel-body text-center">
                        <?php if ($model->status == Compra::STATUS_CANCEL): ?>
                            <h2 class="text-danger"><strong>COMPRA CANCELADA</strong></h2>
                            <?php else: ?>
                            <h2 class="text-info"><strong>COMPRA APROBADA</strong></h2>
                        <?php endif ?>
                    </div>
                </div>
            <?php endif ?>
            
            <?php if(!Yii::$app->user->can('ENCARGADO CEDIS SIN MONTOS')): ?>
                <div class="ibox">
                    <div class="ibox-title">
                        <h3 >Metodos de pago</h3>
                    </div>
                    <div class="ibox-content">
                        <?php
                            $efectivo       = 0;
                            $efectivo_nota  = "";
                            $cheque         = 0;
                            $cheque_nota    = "";
                            $tarjeta_credito= 0;
                            $tarjeta_credito_nota   = "";
                            $tranferencia           = 0;
                            $tranferencia_nota      = "";
                            $tarjeta_debito         = 0;
                            $tarjeta_debito_nota    = "";
                            $deposito               = 0;
                            $deposito_nota          = "";
                            $credito                = 0;
                            $credito_nota          = "";
                            $fecha_liquidacion      = "";
                        ?>
                        <?php $sumaPago = 0; ?>
                        <?php foreach (CobroVenta::getCompraAll($model->id) as $key => $metodo_pago): ?>
                            <?php $sumaPago = $sumaPago + $metodo_pago->cantidad; ?>
                            <div class="bg-success p-xs b-r-sm">
                                <div class="row text-center">
                                    <div class="col"><?= CobroVenta::$servicioList[$metodo_pago->metodo_pago] ?></div>
                                    <div class="col">$ <?= number_format($metodo_pago->cantidad,2)  ?></div>
                                </div>
                            </div>
                            <?php if ($metodo_pago->metodo_pago == CobroVenta::COBRO_EFECTIVO): ?>
                                <?php $efectivo      = $metodo_pago->cantidad;  ?>
                                <?php $efectivo_nota = $metodo_pago->nota;  ?>
                            <?php endif ?>

                            <?php if ($metodo_pago->metodo_pago == CobroVenta::COBRO_CHEQUE): ?>
                                <?php $cheque = $metodo_pago->cantidad;  ?>
                                <?php $cheque_nota = $metodo_pago->nota;  ?>
                            <?php endif ?>

                            <?php if ($metodo_pago->metodo_pago == CobroVenta::COBRO_TRANFERENCIA): ?>
                                <?php $tranferencia = $metodo_pago->cantidad;  ?>
                                <?php $tranferencia_nota = $metodo_pago->nota;  ?>
                            <?php endif ?>

                            <?php if ($metodo_pago->metodo_pago == CobroVenta::COBRO_TARJETA_CREDITO): ?>
                                <?php $tarjeta_credito = $metodo_pago->cantidad;  ?>
                                <?php $tarjeta_credito_nota = $metodo_pago->nota;  ?>
                            <?php endif ?>

                            <?php if ($metodo_pago->metodo_pago == CobroVenta::COBRO_TARJETA_DEBITO): ?>
                                <?php $tarjeta_debito = $metodo_pago->cantidad;  ?>
                                <?php $tarjeta_debito_nota = $metodo_pago->nota;  ?>
                            <?php endif ?>


                            <?php if ($metodo_pago->metodo_pago == CobroVenta::COBRO_DEPOSITO): ?>
                                <?php $deposito = $metodo_pago->cantidad;  ?>
                                <?php $deposito_nota = $metodo_pago->nota;  ?>
                            <?php endif ?>

                            <?php if ($metodo_pago->metodo_pago == CobroVenta::COBRO_CREDITO): ?>
                                <?php $credito = $metodo_pago->cantidad;  ?>
                                <?php $credito_nota = $metodo_pago->nota;  ?>
                                <?php $fecha_liquidacion = $metodo_pago->fecha_credito;  ?>
                            <?php endif ?>

                        <?php endforeach ?>

                        <?php if ($model->is_diferencia == Compra::IS_DIFERENCIA_ON): ?>
                            <?php if ( round($sumaPago,2) != round($model->total_diferencia,2)): ?>
                                <div class="alert alert-danger">
                                    <strong>*REVISA LAS CANTIDADES INGRESADAS, LA CANTIDAD DEBE SER IGUAL AL TOTAL DE LA COMPRA <?= number_format($model->total_diferencia,2) ?> </strong>
                                </div>
                            <?php endif ?>
                        <?php endif ?>
                    </div>
                </div>
            <?php endif ?>
            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>


<div class="fade modal inmodal " id="modal-confirmacion-compra"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> CONFIRMACIÓN DE COMPRA Y PAGO</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <?php $cobro = ActiveForm::begin(['id' => 'form-cobro','action' => 'save-confirmacion' ]) ?>
                <div class="row">
                    <div class="col-sm-12">
                            <div class="panel">
                                <div class="panel-body text-center">
                                    <div class="row">
                                        <div class="col">
                                            <div class=" m-l-md">
                                            <span class="h5 font-bold m-t block">
                                                 <?php if ($model->is_diferencia == Compra::IS_DIFERENCIA_ON): ?>
                                                    $ <?= number_format($model->total_diferencia,2)  ?>
                                                 <?php else: ?>
                                                    $ <?= number_format($model->total,2)  ?>
                                                <?php endif ?>
                                            </span>
                                            <small class="text-muted m-b block">TOTAL A PAGAR</small>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <span class="h5 font-bold m-t block"> $ 0.00 </span>
                                            <small class="text-muted m-b block">COBRO</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="ibox ">
                                <div class="ibox-content">

                                    <?= Html::hiddenInput('compra_id', $model->id ) ?>

                                    <div class="form-group row ">
                                        <h3 class="col-sm-3"><?= CobroVenta::$servicioList[CobroVenta::COBRO_EFECTIVO] ?></h3>
                                        <?= Html::input('number', 'input_cobro_efectivo',$efectivo,[ 'id' => 'input_cobro_efectivo','class' => 'form-control col-sm-4 text-center']) ?>

                                        <?= Html::input('text', 'input_nota_efectivo',$efectivo_nota,[ 'id' => 'input_nota_efectivo', 'placeholder' => 'Nota / Comentario', 'class' => 'form-control col-sm-4 text-center']) ?>
                                    </div>

                                    <div class="form-group row">
                                        <h3 class="col-sm-3"><?= CobroVenta::$servicioList[CobroVenta::COBRO_CHEQUE] ?></h3>
                                        <?= Html::input('number', 'input_cobro_cheque',$cheque,[ 'id' => 'input_cobro_cheque','class' => 'form-control col-sm-4 text-center']) ?>
                                        <?= Html::input('text', 'input_nota_cheque',$cheque_nota,[ 'id' => 'input_nota_cheque', 'placeholder' => 'Nota / Comentario', 'class' => 'form-control col-sm-4 text-center']) ?>
                                    </div>
                                    <div class="form-group row">
                                        <h3 class="col-sm-3"><?= CobroVenta::$servicioList[CobroVenta::COBRO_TRANFERENCIA] ?></h3>
                                        <?= Html::input('number', 'input_cobro_tranferencia',$tranferencia,[ 'id' => 'input_cobro_tranferencia','class' => 'form-control col-sm-4 text-center']) ?>
                                        <?= Html::input('text', 'input_nota_tranferencia',$tranferencia_nota,[ 'id' => 'input_nota_tranferencia', 'placeholder' => 'Nota / Comentario', 'class' => 'form-control col-sm-4 text-center']) ?>
                                    </div>
                                    <div class="form-group row">
                                        <h3 class="col-sm-3"><?= CobroVenta::$servicioList[CobroVenta::COBRO_TARJETA_CREDITO] ?></h3>

                                        <?= Html::input('number', 'input_cobro_tarjeta_credito',$tarjeta_credito,[ 'id' => 'input_cobro_tarjeta_credito','class' => 'form-control col-sm-4 text-center']) ?>

                                        <?= Html::input('text', 'input_nota_tarjeta_credito',$tarjeta_credito_nota,[ 'id' => 'input_nota_tarjeta_credito', 'placeholder' => 'Nota / Comentario', 'class' => 'form-control col-sm-4 text-center']) ?>
                                    </div>
                                    <div class="form-group row">
                                        <h3 class="col-sm-3"><?= CobroVenta::$servicioList[CobroVenta::COBRO_TARJETA_DEBITO] ?></h3>
                                        <?= Html::input('number', 'input_cobro_tarjeta_debito',$tarjeta_debito,[ 'id' => 'input_cobro_tarjeta_debito','class' => 'form-control col-sm-4 text-center']) ?>

                                        <?= Html::input('text', 'input_nota_tarjeta_debito',$tarjeta_debito_nota,[ 'id' => 'input_nota_tarjeta_debito', 'placeholder' => 'Nota / Comentario', 'class' => 'form-control col-sm-4 text-center']) ?>
                                    </div>

                                    <div class="form-group row">
                                        <h3 class="col-sm-3"><?= CobroVenta::$servicioList[CobroVenta::COBRO_DEPOSITO] ?></h3>
                                        <?= Html::input('number', 'input_cobro_deposito',$deposito,[ 'id' => 'input_cobro_deposito','class' => 'form-control col-sm-4 text-center']) ?>
                                        <?= Html::input('text', 'input_nota_deposito',$deposito_nota,[ 'id' => 'input_nota_deposito', 'placeholder' => 'Nota / Comentario', 'class' => 'form-control col-sm-4 text-center']) ?>
                                    </div>

                                    <div class="form-group row">
                                        <h3 class="col-sm-3"><?= CobroVenta::$servicioList[CobroVenta::COBRO_CREDITO] ?></h3>

                                        <?= Html::input('number', 'input_cobro_credito',$credito,[ 'id' => 'input_cobro_credito','class' => 'form-control col-sm-4 text-center']) ?>

                                        <?= Html::input('text', 'input_nota_credito',$credito_nota,[ 'id' => 'input_nota_credito', 'placeholder' => 'Nota / Comentario', 'class' => 'form-control col-sm-4 text-center']) ?>
                                    </div>

                                </div>
                            </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary' ]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

    <script src='https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.12.0/maps/maps-web.min.js'></script>
    <script type='text/javascript' src='<?= Url::to(['/js/mobile-or-tablet.js']) ?>'></script>
    <script type='text/javascript' src='<?= Url::to(['/js/formatters.js']) ?>'></script>
    <script>
        var lat = "<?= $model->lat ?>",
            lng = "<?= $model->lng ?>";

        var roundLatLng = Formatters.roundLatLng;
        var center = [ parseFloat(lng), parseFloat(lat)];
        var popup = new tt.Popup({
            offset: 35
        });
        var map = tt.map({
            key: 'PZ5GFtRIHkoGGLnnUlNqAligAIfsoBuw',
            container: 'map',
            dragPan: !isMobileOrTablet(),
            center: center,
            zoom: 14
        });
        map.addControl(new tt.FullscreenControl());
        map.addControl(new tt.NavigationControl());

        var marker = new tt.Marker({
            draggable: true
        }).setLngLat(center).addTo(map);

        function onDragEnd() {
            var lngLat = marker.getLngLat();
            lngLat = new tt.LngLat(roundLatLng(lngLat.lng), roundLatLng(lngLat.lat));

            popup.setHTML(lngLat.toString());
            popup.setLngLat(lngLat);
            marker.setPopup(popup);
            marker.togglePopup();
        }

        marker.on('dragend', onDragEnd);



    </script>
</script>