<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use app\models\venta\Venta;
use app\assets\BootboxAsset;
use app\widgets\CreatedByView;
use app\models\producto\Producto;
use app\models\sucursal\Sucursal;
use app\models\inv\Operacion;
use app\models\inv\OperacionDetalle;
use app\models\inv\InvProductoSucursal;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\trans\TransProductoInventario;


BootstrapTableAsset::register($this);

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

BootboxAsset::register($this);
$bttUrl       = Url::to(['historial-movimientos-json-btt']);


$this->title =  "#" . trim($model->nombre) ." - [".  $model->clave ."]";

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');

$this->params['breadcrumbs'][] = ['label' => 'Inventario', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';

?>

<div class="productos-producto-view">
     <?php if ($model->is_subproducto == Producto::TIPO_SUBPRODUCTO): ?>
        <div class="alert alert-warning">
            <strong>AVISO </strong> Los SUBPRODUCTOS no maneja inventario, contacta al admistrador para mas información.
        </div>
    <?php endif ?>

    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-6">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5 >Información productos</h5>
                        </div>
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'id',
                                    'nombre',
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5 >Información productos</h5>
                        </div>
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                   
                                    [
                                        'attribute' => 'Unidad de medida',
                                        'format'    => 'raw',
                                        'value'     =>  $model->unidadMedida ? $model->unidadMedida->nombre : 'N/A',
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-md-4">
            <div class="panel panel-info">
                <div class="ibox-title">
                    <h5 ><?= Producto::$statusList[$model->status] ?></h5>
                </div>
            </div>
            <?php if ($model->is_subproducto != Producto::TIPO_SUBPRODUCTO): ?>
                <?php if (Yii::$app->user->can('ajusteInventarioAccess')): ?>
                    <div class="panel">
                        <?= Html::a('AJUSTAR INVENTARIO', ['cancel', 'id' => $model->id], ['class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;', 'data-target' => "#modal-ajuste-inventario", 'data-toggle' =>"modal"  ])?>
                    </div>
                <?php endif ?>
            <?php endif ?>
            <?php /* ?>
            <div class="panel">
                <?= Html::a('AJUSTAR PRECIO', ['cancel', 'id' => $model->id], ['class' => 'btn btn-warning btn-lg btn-block', 'style'=>'padding: 6%;', 'data-target' => "#modal-ajuste-inventario", 'data-toggle' =>"modal"  ])?>
            </div>
            */?>


            <?php if ($model->is_subproducto == Producto::TIPO_SUBPRODUCTO ): ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >SUB PRODUCTO</h5>
                </div>
                <div class="ibox-content ">
                    <div class="row text-center">
                        <div class="col">
                            <span class="h5 font-bold m-t block"><?= $model->sub_cantidad_equivalente   ?> </span>
                            <small class="text-muted m-b block">
                                <?php if (isset($model->subProducto->id)): ?>
                                <a href="<?= Url::to(["view", "id" =>  $model->subProducto->id ])  ?>"><?=  $model->subProducto->nombre   ?></a>
                                <?php endif ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif ?>

        </div>
    </div>

    <?php if (!Yii::$app->user->can('ENCARGADO CEDIS')): ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">
                    <div class="panel-body text-center">
                        <div class="row text-center">
                            <div class="col">
                                <div class=" m-l-md">
                                <span class="h2 font-bold m-t block">$ <?= number_format($model->precio_publico) ?></span>
                                <strong class="text-muted m-b block">publico</strong>
                                </div>
                            </div>
                            <div class="col">
                                <span class="h2 font-bold m-t block">$ <?= number_format($model->precio_mayoreo)  ?></span>
                                <strong class="text-muted m-b block">Mayoreo</strong>
                            </div>
                            <div class="col">
                                <span class="h2 font-bold m-t block">$ <?= number_format($model->precio_menudeo)  ?></span>
                                <strong class="text-muted m-b block">Menudeo</strong>
                            </div>
                            <div class="col">
                                <span class="h2 font-bold m-t block">$ <?= number_format($model->costo) ?></span>
                                <strong class="text-muted m-b ">Costo</strong>
                                <strong class="text-muted m-b block">(Ultima compra)</strong>
                            </div>
                            <div class="col">
                                <span class="h2 font-bold m-t block">$ <?= number_format((count($promedio)==0)? 0 :$promedio[0]['promedio_ultimas_3_ventas'])?></span>
                                <strong class="text-muted m-b block">Costo promedio</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>

    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <?php foreach (Sucursal::getItems() as $key => $item): ?>
                    <div class="col-lg-3">
                        <div class="ibox ">
                            <div class="ibox-title">
                                <h5><?= $item ?></h5>
                            </div>
                            <div class="ibox-content">

                                <div class="row text-center">
                                    <div class="col-sm-12">
                                        <h1 class="no-margins" style="font-weight:bold;"><?= isset(InvProductoSucursal::getStockProducto($model->id,$key)->cantidad) ?InvProductoSucursal::getStockProducto($model->id,$key)->cantidad : 0 ?></h1>
                                        <small><strong>STOCK REAL [<?= isset($model->unidadMedida) ?  $model->unidadMedida->nombre : ' N/A ' ?>]</strong></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>

    <div class="inventario-history-almacen-index">
        <div class="btt-toolbar">
            <?= Html::hiddenInput('producto_id', $model->id) ?>
            <div class="panel mar-btm-5px">
                <div class="panel-body pad-btm-15px">
                    <div>
                       <div class="DateRangePicker   kv-drp-dropdown">
                            <?= DateRangePicker::widget([
                                'name'           => 'date_range',
                                //'presetDropdown' => true,
                                'hideInput'      => true,
                                'useWithAddon'   => true,
                                'convertFormat'  => true,
                                'startAttribute' => 'from_date',
                                'endAttribute' => 'to_date',
                                'startInputOptions' => ['value' => '2019-01-01'],
                                'endInputOptions' => ['value' => '2019-12-31'],
                                'pluginOptions'  => [
                                    'locale' => [
                                        'format'    => 'Y-m-d',
                                        'separator' => ' - ',
                                    ],
                                    'opens' => 'left',
                                    "autoApply" => true,
                                ],
                            ])
                            ?>
                        </div>
                        <br>
                        <strong class="pad-rgt">Filtrar:</strong>
                        <?= Html::dropDownList('sucursal_id', null, Sucursal::getItems(), ['prompt' => 'Sucursal / Ruta', 'class' => '']) ?>
                        <?= Html::dropDownList('tipo', null, TransProductoInventario::$tipoList, ['prompt' => 'TIPO [ENTRADA / SALIDA]', 'class' => '']) ?>
                        <?= Html::dropDownList('operacion', null, TransProductoInventario::$motivoList, ['prompt' => 'OPERACION', 'class' => '']) ?>
                    </div>
                    <div class="mar-top" style="padding: 15px;">
                        <strong class="pad-rgt">Agrupar:</strong>
                        <?= Html::checkbox("agrupar[traspaso]", true, ["id" => "agrupar-mes", "class" => "magic-checkbox"]) ?>
                        <?= Html::label("Por traspaso", "agrupar-mes", ["style" => "display:inline"]) ?>
                    </div>
                </div>
            </div>
        </div>
        <table class="bootstrap-table"></table>
    </div>


</div>


<div class="fade modal inmodal " id="modal-ajuste-inventario"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> AJUSTE DE INVENTARIO</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <?php $cobro = ActiveForm::begin(['id' => 'form-ajuste-inventario','action' => 'save-inventario-producto' ]) ?>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="ibox ">
                            <div class="ibox-content text-center">
                                <?= Html::hiddenInput('producto_id', $model->id ) ?>
                                <h2><?= $model->nombre ." - [". $model->clave ."]" ?></h2>
                                <?php foreach (Sucursal::getItems() as $key => $sucursal): ?>

                                    <?= Html::hiddenInput('PRODUCTO['.$key.'][sucursal_id]', $key ) ?>
                                    <div class="form-group row ">
                                        <h3 class="col-sm-3"><?= $sucursal ?></h3>
                                        <?= Html::input('number', 'PRODUCTO['.$key.'][input_inv_cantidad]', isset(InvProductoSucursal::getStockProducto($model->id,$key)->cantidad) ?InvProductoSucursal::getStockProducto($model->id,$key)->cantidad : 0 ,[ 'id' => 'input_inv_cantidad','class' => 'form-control col-sm-6 text-center']) ?>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary', "id" => "btnSaveInventarioProducto" ]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<div class="fade modal inmodal " id="modal-show-operacion"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> OPERACION </h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">

                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-sm-3">
                                    <?= Html::img('@web/img/logo.png', ["height"=>"80px"]) ?>
                            </div>
                            <div class="col-sm-8">
                                <h2 class="text_load lbl_tipo">  </h2><strong style="font-weight: bold;font-size: 24px;" class="text_load text-info lbl_motivo"> </strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 text-center"><strong>FOLIO DE OPERACION </strong><p><a class="text_load text-info  lbl_folio text-link"  target="_blank" style="font-size:24px;text-decoration: underline;"></a></p></div>
                            <div class="col-sm-4 text-center"><strong>RESPONSABLE        </strong><p class="text_load text-info  lbl_responsable"></p></div>
                            <div class="col-sm-4 text-center"><strong>FECHA DE OPERACION </strong><p class="text_load text-info  lbl_fecha_operacion"></p></div>
                        </div>
                        <div class="row div_salida" style="display:none">
                            <div class="col-sm-4 text-center">
                                <h2 class="text_load text-success lbl_origen_salida"></h2>
                                <strong>SUCURSAL</strong>
                            </div>
                            <div class="col-sm-4 text-center" style="font-size: 48px;"><i class="fa fa-cubes"></i> => </div>
                            <div class="col-sm-4 text-center">
                                <h2 class="text_load text-success lbl_cantidad_salida">0</h2>
                                <strong>CANTIDAD</strong>
                            </div>
                        </div>

                        <div class="row div_entrada" style="display:none">

                            <div class="col-sm-4 text-center">
                                <h2 class="text_load text-success lbl_cantidad_entrada"></h2>
                                <strong>CANTIDAD</strong>
                            </div>
                            <div class="col-sm-4 text-center" style="font-size: 48px;"> => <i class="fa fa-cubes"></i>  </div>
                            <div class="col-sm-4 text-center">
                                <h2 class="text_load text-success lbl_origen_entrada">0</h2>
                                <strong>SUCURSAL</strong>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    var producto_id = <?= $model->id  ?>;
    $('#btnSaveInventarioProducto').on('click', function(event){
        event.preventDefault();
        bootbox.confirm("¿Estas seguro que deseas realizar la operación ?", function(result) {
            if (result) {
                console.log("entrooo");
                $('#form-ajuste-inventario').submit();
            }else{
                $('#modal-ajuste-inventario').modal('hide');
            };
        });
    });


    var load_operacion = function($operacion_id)
    {
        $('#modal-show-operacion').modal('show');
        $('.text_load').html(null);

        $.get("<?= Url::to(['get-operacion-detail']) ?>", { operacion_id : $operacion_id }, function( $response ){
            if ($response.code  == 202 ) {
                if ($response.operacion.motivo == 10){
                    $('.div_entrada').show()
                    $('.div_salida').hide();
                    $('.lbl_motivo').html("<strong class='text-info'>" + $response.operacion.motivo_text + "</strong>");
                    $('.lbl_cantidad_entrada').html("<strong class='text-info'>" + $response.operacion.cantidad + "</strong>");
                    $('.lbl_origen_entrada').html($response.operacion.origen);
                }
                else{
                    $('.div_entrada').hide();
                    $('.div_salida').show();
                    $('.lbl_motivo').html("<strong class='text-danger'>" + $response.operacion.motivo_text + "</strong>");
                    $('.lbl_cantidad_salida').html("<strong class='text-danger'>" + $response.operacion.cantidad + "</strong>");
                    $('.lbl_origen_salida').html($response.operacion.origen);
                }


                $('.lbl_tipo').html($response.operacion.tipo_text);
                $('.lbl_folio').html($response.operacion.folio);
                $('.lbl_folio').attr("href", $response.operacion.url_folio  );
                $('.lbl_responsable').html($response.operacion.responsable);
                $('.lbl_fecha_operacion').html($response.operacion.created_at);



            }

        },'json');

    }

    var link_operacion = function($operacion_id)
    {
        window.open("<?= Url::to(['redirect-operacion-view'])  ?>?operacion_id="+ $operacion_id);
    }

    var show_ticket = function($operacion_id)
    {
        window.open("<?= Url::to(['imprimir-operacion']) ?>?operacion_id=" + $operacion_id,
        'imprimir',
        'width=600,height=900');
    }

</script>

<script type="text/javascript">
    $(document).ready(function(){
        var actions = function(value, row) { return [
                (row.tipo != 50  ? '<a  href="javascript:void(0);"  title="Ver operación" onclick = "link_operacion('+ row.id +')"     class="fa fa-eye"></a>' : '' ),
                '<a  href="javascript:void(0);"    title="Ver operación" onclick = "load_operacion('+ row.id +')"      class="fa fa-cogs"></a>',
                (row.tipo != 50  && row.tipo != 40 && row.tipo != 60 ? '<a  href="javascript:void(0);"   title="Descargar PDF"  onclick = "show_ticket('+ row.id +')"   class="fa fa-file-pdf-o"></a>' : ''),
            ].join(''); },
            columns = [
                {
                    field: 'id',
                    title: 'ID',
                    align: 'center',
                    width: '60',
                    sortable: true,
                    switchable:false,
                },
                /*{
                    field: 'venta_detalle_id',
                    title: 'Operacion [VENTA]',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'operacion_detalle_id',
                    title: 'Operacion [ALMACEN / BODEGA]',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'transformacion_detalle_id',
                    title: 'Operacion [TRANSFORMACIÓN]',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'reparto_detalle_id',
                    title: 'Operacion [RUTA]',
                    align: 'center',
                    sortable: true,
                },*/
                {
                    field: 'origen',
                    title: 'ORIGEN',
                    align: 'center',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'tipo',
                    title: 'MOVIMIENTO',
                    align: 'center',
                    formatter: btf.inv.operacion,
                    sortable: true,
                    sortable: true,
                },
                {
                    field: 'destino',
                    title: 'DESTINO',
                    align: 'center',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'motivo',
                    title: 'TIPO',
                    align: 'center',
                    formatter: btf.inv.tipo_movimiento,
                    sortable: true,
                },
                {
                    field: 'inventario',
                    title: 'ANTES',
                    align: 'right',
                    sortable: true,
                    visible: false,

                },
                {
                    field: 'cantidad',
                    title: 'CANTIDAD',
                    align: 'right',
                    formatter: btf.inv.operacion_cantidad,
                    sortable: true,
                },
                {
                    field: 'inventario_new',
                    title: 'DESPUES',
                    align: 'right',
                    sortable: true,
                    visible: false,

                },
                {
                    field: 'created_at',
                    title: 'Creado',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.datetime,
                },
                {
                    field: 'created_by',
                    title: 'Creado por',
                    sortable: true,
                    formatter: btf.user.created_by,
                },
                {
                    field: 'action',
                    title: 'Acciones',
                    align: 'center',
                    switchable: false,
                    width: '100',
                    class: 'btt-icons',
                    formatter: actions,
                    tableexportDisplay:'none',
                    showExport :'none',
                },
            ],
            params = {
                id      : 'movimiento',
                element : '.inventario-history-almacen-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>