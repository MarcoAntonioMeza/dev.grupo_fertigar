<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use app\models\esys\EsysListaDesplegable;
use app\models\producto\Producto;
use app\models\proveedor\Proveedor;
use app\models\sucursal\Sucursal;
?>
<style>
.modal-content {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    margin: auto;
    text-align: center;
}

.modal-header {
    font-size: 18px;
    font-weight: bold;
    color: #444;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

.table-container {
    width: 100%;
    overflow-x: auto;
    margin-top: 15px;
    border-radius: 8px;
    border: 1px solid #ddd;
    background: #fff;
}

.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.table th {
    background: #f4f4f4;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #ddd;
}

.table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    color: #555;
}

.table tr:hover {
    background: #f9f9f9;
}

.total-amount {
    font-size: 30px;
    font-weight: bold;
    color: #222;
    margin-top: 20px;
}

.total-label {
    font-size: 14px;
    color: #777;
    text-transform: uppercase;
    font-weight: 500;
    letter-spacing: 1px;
}

.close-btn {
    margin-top: 15px;
    padding: 8px 15px;
    background: #444;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.close-btn:hover {
    background: #222;
}

</style>
<div class="productos-producto-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <div class="form-group text-right">
        <?= Html::submitButton($model->isNewRecord ? 'Crear producto' : 'Guardar cambios', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <!-- DATOS GENERALES -->
    <div class="ibox">
        <div class="ibox-title"><h5>Datos generales</h5></div>
        <div class="ibox-content">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'clave')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'imageFile')->fileInput(['class' => 'form-control']) ?>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'descripcion')->textarea(['rows' => 4]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'peso_aprox')->textInput(['maxlength' => true, 'type' => 'number', 'step' => 'any']) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'categoria_id')->dropDownList(EsysListaDesplegable::getItems('producto_categoria'), ['prompt' => '']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- CONFIGURACIÓN DEL PRODUCTO -->
    <div class="ibox">
        <div class="ibox-title"><h5>Configuración del producto</h5></div>
        <div class="ibox-content">
            <div class="row">
               
                <div class="col-md-3">
                    <?= $form->field($model, 'status')->dropDownList(Producto::$statusList) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'is_subproducto')->dropDownList(Producto::$tipoProductoList) ?>
                </div>
               

                <div class="col-md-4 div_producto" style="display: none">
                    <?= $form->field($model, 'inventariable')->dropDownList(Producto::$invList, ['prompt' => '']) ?>
                    <div class="div_stock_minimo" style="display: none">
                        <?= $form->field($model, 'stock_minimo')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>

                <div class="col-md-8 div_sub_producto" style="display: none">
                    <div class="row">
                        <div class="col-md-8">
                            <?= $form->field($model, 'sub_producto_id')->widget(Select2::classname(), [
                                'language' => 'es',
                                'data' => isset($model->sub_producto_id) && $model->sub_producto_id ? [$model->subProducto->id => $model->subProducto->nombre] : [],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'minimumInputLength' => 3,
                                    'language' => [
                                        'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                    ],
                                    'ajax' => [
                                        'url' => Url::to(['producto-ajax']),
                                        'dataType' => 'json',
                                        'cache' => true,
                                        'processResults' => new JsExpression('function(data){ return {results: data} }'),
                                    ],
                                ],
                                'options' => [
                                    'placeholder' => 'Busca producto',
                                ],
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'sub_cantidad_equivalente')->textInput(['type' => 'number']) ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'unidad_medida_id')->dropDownList(\app\models\producto\Unidadsat::get_unudades_sat(), ['prompt' => '--SELECCIONE--']) ?>
                </div>
                
            </div>
        </div>
    </div>

    <!-- PRECIOS Y COMISIONES -->
    <div class="ibox">
        <div class="ibox-title"><h5>Precios y comisiones</h5></div>
        <div class="ibox-content">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'costo')->textInput(['maxlength' => true, 'style' => 'font-size : 24px']) ?>
                    <?= $form->field($model, 'precio_publico')->textInput(['maxlength' => true, 'style' => 'font-size : 24px']) ?>
                    <?= $form->field($model, 'comision_publico')->textInput(['maxlength' => true, 'style' => 'font-size : 24px']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'precio_mayoreo')->textInput(['maxlength' => true, 'style' => 'font-size : 24px']) ?>
                    <?= $form->field($model, 'comision_mayoreo')->textInput(['maxlength' => true, 'style' => 'font-size : 24px']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'precio_sub')->textInput(['maxlength' => true, 'style' => 'font-size : 24px']) ?>
                    <?= $form->field($model, 'comision_sub')->textInput(['maxlength' => true, 'style' => 'font-size : 24px']) ?>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>



<div class="fade modal inmodal " id="modal-ultimas-compra"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label" >
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <!--Modal header-->
            <!--Modal body-->
            <div class="modal-body">
                
                <div class="ibox">
                    <div class="ibox-content">
                        <p>Ultimas compras para el calculo del promedio</p>
                        <table class="table table-bordered table-container">
                            <thead>
                                <tr>
                                    <td>FOLIO</td>
                                    <td>FECHA/HORA</td>
                                    <td>DESTINO</td>
                                    <td>COSTO</td>
                                </tr>
                            </thead>
                            <tbody class="divContentCompra">
                                
                            </tbody>
                        </table>

                        <div class="text-center">
                            <p class="total-amount">$0.00</p>
                            <strong class="total-label">PROMEDIO</strong>
                        </div>

                        <div class="divContentCompraAviso">

                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class=" close-btn" type="button">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    var $input_stock_minimo  =  $('#producto-stock_minimo'),
        $select_inventariable=  $('#producto-inventariable'),
        $select_subproducto  =  $('#producto-is_subproducto'),
        $div_stock_minimo    =  $('.div_stock_minimo'),
        $div_sub_producto    =  $('.div_sub_producto'),
        $div_producto        =  $('.div_producto'),
        VAR_ITEM             =  "<?= $model->id ?>";
        $var_inv_on          =  "<?= Producto::INV_SI ?>";
        URL_PATH             =  $('body').data('url-root');
        $var_subproducto     =  "<?= Producto::TIPO_SUBPRODUCTO ?>";

    $(function() {
        $select_inventariable.trigger('change');
        $select_subproducto.trigger('change');
    });

    $select_inventariable.change(function(){
        if ($(this).val() == $var_inv_on )
            $div_stock_minimo.show();
        else
            $input_stock_minimo.val(0);
    });

    $select_subproducto.change(function(){
        if ($(this).val() == $var_subproducto ){
            $div_sub_producto.show();
            $div_producto.hide();
             $('#producto-tipo_medida').attr("disabled",true);
             $('#producto-costo').attr("disabled",true);
             $('#producto-precio_mayoreo').attr("disabled",true);
             $('#producto-precio_menudeo').attr("disabled",true);
        }
        else{
            $div_producto.show();
            $div_sub_producto.hide();
            $('#producto-tipo_medida').attr("disabled",false);
            $('#producto-costo').attr("disabled",false);
            $('#producto-precio_mayoreo').attr("disabled",false);
            $('#producto-precio_menudeo').attr("disabled",false);
        }
    });

    var funct_getPromedioCosto = function (){
        $.get( URL_PATH + "productos/producto/get-promedio-compra",{ productoID :  VAR_ITEM }, function(response){
            if(response.code == 202){
    
                $('.divContentCompra').html(null);
                contentHtml = '';
                
                $.each(response.promedio.historial_venta, function(key, itemHistorial){
                    contentHtml += '<tr>'+
                        '<td><p style="font-size:12px"><a href="'+ URL_PATH +'compras/compra/view?id='+ itemHistorial.compra_id +'" target="_black">'+ itemHistorial.compra_id+'</a></p></td>'+
                        '<td><p style="font-size:12px">'+ itemHistorial.fecha+'</p></td>'+
                        '<td><p style="font-size:12px">'+ itemHistorial.sucursal+'</p></td>'+
                        '<td><p style="font-size:12px">'+ btf.conta.money(itemHistorial.costo) +'</p></td>'+
                    '</tr>';
                });
                
                $('.divContentCompra').html(contentHtml);
                
                $('.total-amount').html(btf.conta.money(response.promedio.valor));
                $('#producto-costo').val(response.promedio.valor.toFixed(2));


                if(response.promedio.valor > ( $('#producto-precio_publico').val() ? $('#producto-precio_publico').val() : 0) ){
                    // $('#producto-precio_publico').val(0);
                    $('.divContentCompraAviso').append('<div class= "alert alert-warning">El precio publico '+ btf.conta.money($('#producto-precio_publico').val()) +' no puede ser menor al costo promedio '+ btf.conta.money(response.promedio.valor) +' </div>')
                }

                if(response.promedio.valor > ( $('#producto-precio_mayoreo').val() ? $('#producto-precio_mayoreo').val() : 0) ){
                    // $('#producto-precio_mayoreo').val(0);
                    $('.divContentCompraAviso').append('<div class= "alert alert-warning">El precio mayoreo '+ btf.conta.money($('#producto-precio_mayoreo').val()) +' no puede ser menor al costo promedio '+ btf.conta.money(response.promedio.valor) +' </div>')
                }

                if(response.promedio.valor > ( $('#producto-precio_menudeo').val() ? $('#producto-precio_menudeo').val() : 0) ){
                    // $('#producto-precio_menudeo').val(0);
                    $('.divContentCompraAviso').append('<div class= "alert alert-warning">El precio publico '+ btf.conta.money($('#producto-precio_menudeo').val()) +' no puede ser menor al costo promedio '+ btf.conta.money(response.promedio.valor) +' </div>')
                }
            }
        },'json');
    }

</script>
