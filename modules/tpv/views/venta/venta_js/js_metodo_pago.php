<script>
var $template_metodo_pago               = $('.template_metodo_pago'),
    $template_metodo_pago_credito       = $('.template_metodo_pago_credito'),
    $content_metodo_pago                = $(".content_metodo_pago"),
    $content_metodo_pago_credito        = $(".content_metodo_pago_credito"),
    $btnAgregarMetodoPago       =  $('#btnAgregarMetodoPago'),
    $inputApplyCargoExtra       =  $('#credito-apply_cargoextra'),
    $btnAgregarMetodoPagoCredito=  $('#btnAgregarMetodoPagoCredito'),
    metodoPago_arrayCredito     = [],
    $form_metodoPago = {
        $metodoPago : $('#cobroventa-metodo_pago'),
        $cantidad   : $('#cobroventa-cantidad'),
    };

    $form_metodoPagoCredito = {
        $metodoPago : $('#pago-metodo_pago'),
        $cantidad   : $('#pago-cantidad'),
    };


$form_metodoPago.$metodoPago.change(function(){
    $('.alert_warning_pago').hide();
    $('.text-warning-pago').html(null);
    $('.div_tpv_metodo_otro').hide();
    if ($(this).val() == VAR_CREDITO ) {
        $('.div_input_fecha').show();
        if (!ventaArray.cliente_id)
            $('.div_cliente_select').show();
        else
            $('.div_cliente_select').hide();
    }else{
        $('.div_input_fecha').hide();
    }

    if ($(this).val() == VAR_TARJETA_CREDITO || $(this).val() == VAR_DEBITO_CREDITO) {
        $('.alert_warning_pago').show();
        $('.text-warning-pago').html('SE AGREGA UN CARGO EXTRA DEL 2% DEL PAGO A REALIZAR');
    }

    if ($form_metodoPago.$metodoPago.val() == VAR_COBRO_OTRO) {
        $('.div_tpv_metodo_otro').show();
    }

});

$btnAgregarMetodoPago.click(function(){

    if(!$form_metodoPago.$metodoPago.val() || !$form_metodoPago.$cantidad.val()){
        return false;
    }

    if ($form_metodoPago.$metodoPago.val() == VAR_CREDITO ) {
        if ($('#cobroventa-fecha_credito').val() == "" ) {
            $('.alert_forma_pago').show();
            $('.alert_forma_pago').html("Debes ingresar una FECHA A PAGAR, intente nuevamente");
            return false;
        }
    }

    if ($form_metodoPago.$metodoPago.val() == VAR_COBRO_OTRO && !$inputOtroTpvComentario.val()) {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            showMethod: 'slideDown',
            timeOut: 5000
        };
        toastr.error('Verifica tu información, Nota / Comentario son requeridos.');
        return false;
    }


    metodo = {
        "metodo_id"         : metodoPago_array.length + 1,
        "metodo_pago_id"    : $form_metodoPago.$metodoPago.val(),
        "metodo_pago_text"  : $('option:selected', $form_metodoPago.$metodoPago).text(),
        "fecha_credito"     : $('#cobroventa-fecha_credito').val(),
        "cantidad"          : $form_metodoPago.$cantidad.val(),
        "cantidad_pago"     : $form_metodoPago.$cantidad.val(),
        "cargo_extra"       : $form_metodoPago.$metodoPago.val() == VAR_TARJETA_CREDITO || $form_metodoPago.$metodoPago.val() == VAR_DEBITO_CREDITO  ? parseFloat((2 * parseFloat($form_metodoPago.$cantidad.val())) / 100).toFixed(2) : 0,
        "nota_otro"         :  $inputOtroTpvComentario.val(),
        "origen"            : 1,
    };

    metodoPago_array.push(metodo);
    $('#cobroventa-fecha_credito').val(null);
    $form_metodoPago.$cantidad.val(null);
    calcula_cambio_envio();
    render_metodo_template();
});


$inputApplyCargoExtra.change(function(){
    $.each(metodoPago_arrayCredito, function(key_metodo, item_metodo){
        if ($inputApplyCargoExtra.is(':checked')) {
            metodoPago_arrayCredito[key_metodo].cargo_extra = parseFloat((2 * parseFloat(item_metodo.cantidad)) / 100).toFixed(2);
        }else{
            metodoPago_arrayCredito[key_metodo].cargo_extra = 0;
        }
    });
    render_metodo_template_credito();
});


$btnAgregarMetodoPagoCredito.click(function(){

    if(!$form_metodoPagoCredito.$metodoPago.val() || !$form_metodoPagoCredito.$cantidad.val()){
        return false;
    }
    $('.alert_danger_credito').hide();
    $('.text-message-credito').html(null);


    pago_total = 0;
    $.each(metodoPago_arrayCredito, function(key, metodo){
        if (metodo.metodo_id) {
            pago_total = pago_total + parseFloat(metodo.cantidad);
        }
    });

    pago_total = pago_total + parseFloat($form_metodoPagoCredito.$cantidad.val());

    //if ( parseFloat($('#inputCantidadCredito').val()) >= pago_total   ) {

        if ($form_metodoPagoCredito.$metodoPago.val() == VAR_COBRO_OTRO && !$inputOtroComentario.val()) {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 5000
            };
            toastr.error('Verifica tu información, Nota / Comentario son requeridos.');
            return false;
        }

        metodo = {
            "metodo_id"         : metodoPago_arrayCredito.length + 1,
            "metodo_pago_id"    : $form_metodoPagoCredito.$metodoPago.val(),
            "metodo_pago_text"  : $('option:selected', $form_metodoPagoCredito.$metodoPago).text(),
            "cantidad"          : getCantidadMetodoPago($form_metodoPagoCredito.$cantidad.val()),
            "cargo_extra"       : $inputApplyCargoExtra.is(':checked') ? ( $form_metodoPagoCredito.$metodoPago.val() == VAR_TARJETA_CREDITO || $form_metodoPagoCredito.$metodoPago.val() == VAR_DEBITO_CREDITO  ? (2 * parseFloat(getCantidadMetodoPago($form_metodoPagoCredito.$cantidad.val()))) / 100 : 0) : 0,
            "nota_otro"         :  $inputOtroComentario.val(),
            "origen"            : 1,
        };

        metodoPago_arrayCredito.push(metodo);

    /*}else{
        $('.alert_danger_credito').show();
        $('.text-message-credito').html('El monto ingresado debe ser mayor, intente nuevamente');
    }*/


    $form_metodoPagoCredito.$cantidad.val(null);
    //$inputProducto.val(null).change();
    //$inputCantidadRecibe.val(null);
    $inputOtroComentario.val(null);
    //calcula_cambio_envio();
    render_metodo_template_credito();
});



var getCantidadMetodoPago = function(new_abono){
    sumTotalIngresado = 0;
    $.each(metodoPago_arrayCredito, function(key, metodo){
        if (metodo.metodo_id) {
            sumTotalIngresado = sumTotalIngresado + parseFloat(metodo.cantidad);
        }
    });

    totalDisponible = parseFloat($('#inputCantidadCredito').val()) -  parseFloat(sumTotalIngresado).toFixed(2);

    $('.lbl_pago_credito_cambio').html(btf.conta.money( ( new_abono > totalDisponible ? new_abono - totalDisponible : 0 ) ));

    return  (totalDisponible - new_abono) > 0 ? new_abono : totalDisponible ;
}

$form_metodoPagoCredito.$metodoPago.change(function(){
    $('.alert_warning_credito').hide();
    $('.div-control-cargo-extra').hide();
    $('.div_metodo_otro').hide();
    $('.text-warning-credito').html(null);
    if ($form_metodoPagoCredito.$metodoPago.val() == VAR_TARJETA_CREDITO || $form_metodoPagoCredito.$metodoPago.val() == VAR_DEBITO_CREDITO) {
        $('.alert_warning_credito').show();
        $('.div-control-cargo-extra').show();
        $('.text-warning-credito').html('SE AGREGA UN CARGO EXTRA DEL 2% DEL PAGO A REALIZAR');
    }

    if ($form_metodoPagoCredito.$metodoPago.val() == VAR_COBRO_OTRO) {
        $('.div_metodo_otro').show();
    }
});

var render_metodo_template_credito = function(){
    $content_metodo_pago_credito.html("");
    $('.alert_forma_pago').hide();
    $('.alert_forma_pago').html("");
    pago_total = 0;
    $.each(metodoPago_arrayCredito, function(key, metodo){
        if (metodo.metodo_id) {

            metodo.metodo_id = key + 1;

            template_metodo_pago_credito = $template_metodo_pago_credito.html();
            template_metodo_pago_credito = template_metodo_pago_credito.replace("{{metodo_id_credito_}}",metodo.metodo_id);

            $content_metodo_pago_credito.append(template_metodo_pago_credito);

            $tr        =  $("#metodo_id_" + metodo.metodo_id, $content_metodo_pago_credito);

            $("#table_credito_metodo_id",$tr).html(metodo.metodo_pago_text);

            if (metodo.metodo_pago_id == VAR_DEBITO_CREDITO || metodo.metodo_pago_id == VAR_TARJETA_CREDITO)
                $("#table_credito_metodo_cantidad",$tr).html( btf.conta.money( metodo.cantidad) +" + [ CARGO EXTRA - "+ btf.conta.money(metodo.cargo_extra) + "]");
            else if (metodo.metodo_pago_id == VAR_COBRO_OTRO )
                $("#table_credito_metodo_cantidad",$tr).html( btf.conta.money( metodo.cantidad) +" + [ PRODUCTO - "+ metodo.nota_otro +"]");
            else
                $("#table_credito_metodo_cantidad",$tr).html( btf.conta.money(metodo.cantidad) );


            pago_total = pago_total + parseFloat(metodo.cantidad);

        }
    });

    //$('#total_metodo').html( btf.conta.money($('#venta-total').val()) );

    //balance_total = parseFloat( $('#venta-total').val() - pago_total.toFixed(2));

    $('#inputCantidadResiduoCredito').val( btf.conta.money(parseFloat($('#inputCantidadCredito').val() - pago_total)));
    $form_metodoPago.$metodoPago.val(null).change();

    $form_metodoPago.$cantidad.val(null);

}



/*====================================================
*               RENDERIZA TODO LOS METODS DE PAGO
*====================================================*/
var render_metodo_template = function(){
    $content_metodo_pago.html("");
    $('.alert_forma_pago').hide();
    $('.alert_forma_pago').html("");
    pago_total = 0;
    $('#venta-total').val( parseFloat(parseFloat($('#venta-total').val()) -  totalCargoExtra ).toFixed(2));
    totalCargoExtra = 0;
    $.each(metodoPago_array, function(key, metodo){
        if (metodo.metodo_id) {

            metodo.metodo_id = key + 1;

            template_metodo_pago = $template_metodo_pago.html();
            template_metodo_pago = template_metodo_pago.replace("{{metodo_id}}",metodo.metodo_id);

            $content_metodo_pago.append(template_metodo_pago);

            $tr        =  $("#metodo_id_" + metodo.metodo_id, $content_metodo_pago);
            $tr.attr("data-metodo_id",metodo.metodo_id);
            $tr.attr("data-origen",metodo.origen);

            $("#table_metodo_id",$tr).html(metodo.metodo_pago_text);



            if (parseInt(metodo.metodo_pago_id) == VAR_TARJETA_CREDITO || parseInt(metodo.metodo_pago_id) == VAR_DEBITO_CREDITO ){
                $(".div_cargo_extra", $tr).show();
                $("#table_costo_extra",$tr).val(metodo.cargo_extra);
                $("#table_costo_extra",$tr).attr("onchange","refresh_change_cargo_extra(this)");
                totalCargoExtra  =  totalCargoExtra + parseFloat(metodo.cargo_extra);
                $("#table_metodo_cantidad",$tr).html( btf.conta.money(parseFloat(metodo.cantidad)) +  " /  [PAGO + COSTO EXTRA] = " + btf.conta.money(parseFloat(metodo.cantidad) + parseFloat(metodo.cargo_extra)) );
            }else if (metodo.metodo_pago_id == VAR_COBRO_OTRO )
                $("#table_metodo_cantidad",$tr).html( btf.conta.money( metodo.cantidad) +" - [ CONCEPTO - "+ metodo.nota_otro +"]");
            else{
                $("#table_metodo_cantidad",$tr).html(btf.conta.money(parseFloat(metodo.cantidad)));
            }

            if (parseInt(metodo.metodo_pago_id) == VAR_TARJETA_CREDITO || parseInt(metodo.metodo_pago_id) == VAR_DEBITO_CREDITO )
                pago_total = pago_total + parseFloat(metodo.cantidad);// + parseFloat(metodo.cargo_extra);
            else
                pago_total = pago_total + parseFloat(metodo.cantidad);

            if (metodo.origen != 2) {
                $tr.append("<td><button type='button' class='btn btn-warning btn-circle' onclick='refresh_metodo(this)'><i class='fa fa-trash'></i></button></td>");
            }
        }
    });

    $('#venta-total').val( parseFloat(parseFloat($('#venta-total').val()) +  totalCargoExtra).toFixed(2));

    $('#total_metodo').html( btf.conta.money($('#venta-total').val()));

    balance_total = parseFloat( parseFloat($('#venta-total').val()).toFixed(2) - parseFloat(pago_total).toFixed(2) );

    $form_metodoPago.$metodoPago.val(null).change();

    $form_metodoPago.$cantidad.val(null);

    $('#balance_total').html(btf.conta.money(balance_total));
    $('#pago_metodo_total').html(btf.conta.money(pago_total));
    $('.div_input_fecha').hide();
    $inputventaArray.val(JSON.stringify(metodoPago_array));
}


var calcula_cambio_envio = function(){
    pago_total = 0;
    temp_totalCargoExtra = 0;
    $.each(metodoPago_array, function(key, metodo){
        if (metodo.metodo_id){
            pago_total = pago_total + parseFloat(metodo.cantidad);
        }
    });

    new_cambio_metodo = pago_total - parseFloat($('#venta-total').val());


    if (metodoPago_array[0]){
        val_cambio_round = new_cambio_metodo < 0 ?  metodoPago_array[metodoPago_array.length - 1 ].cantidad : (metodoPago_array[metodoPago_array.length - 1 ].cantidad - new_cambio_metodo) < 0 ? 0 : (metodoPago_array[metodoPago_array.length - 1 ].cantidad - new_cambio_metodo);

        //change la cantidad ajustando si el monto ingresado es mayor
        metodoPago_array[metodoPago_array.length - 1 ].cantidad = parseFloat(val_cambio_round).toFixed(2);

        //RECALCULAMOS EL COSTO EXTRA PARA VERIFICAR SI OCURRIO ALGUN CAMBIO
        cargo_extra_calculo =  parseInt(metodoPago_array[metodoPago_array.length - 1 ].metodo_pago_id) == VAR_TARJETA_CREDITO || parseInt(metodoPago_array[metodoPago_array.length - 1 ].metodo_pago_id) == VAR_DEBITO_CREDITO  ? parseFloat((2 * parseFloat(metodoPago_array[metodoPago_array.length - 1 ].cantidad)) / 100).toFixed(2) : 0;

        metodoPago_array[metodoPago_array.length - 1 ].cargo_extra = metodoPago_array[metodoPago_array.length - 1 ].cargo_extra > cargo_extra_calculo ? cargo_extra_calculo : metodoPago_array[metodoPago_array.length - 1 ].cargo_extra;


        /* LE SUMAMOS EL CARGO EXTRA */
        /*if (parseInt(metodoPago_array[metodoPago_array.length - 1 ].metodo_pago_id) == VAR_TARJETA_CREDITO || parseInt(metodoPago_array[metodoPago_array.length - 1 ].metodo_pago_id) == VAR_DEBITO_CREDITO){
           totalCargoExtra  =  parseFloat(metodoPago_array[metodoPago_array.length - 1 ].cargo_extra);
            //console.log(metodoPago_array[key].cargo_extra);
            console.log(totalCargoExtra);
           $('#venta-total').val( parseFloat(parseFloat($('#venta-total').val()) +  totalCargoExtra).toFixed(2));
        }*/

    }

    $.each(metodoPago_array, function(key, metodo){
        if (metodo.metodo_id){
            if (parseInt(metodo.metodo_pago_id) == VAR_TARJETA_CREDITO || parseInt(metodo.metodo_pago_id) == VAR_DEBITO_CREDITO )
                temp_totalCargoExtra  =  temp_totalCargoExtra + parseFloat(metodo.cargo_extra);
        }
    });
    $('#cambio_metodo').html( new_cambio_metodo <= 0 ? 0 :  btf.conta.money(parseFloat(new_cambio_metodo)  ) );
}

var refresh_change_cargo_extra = function(ele){
    $ele_paquete_val = $(ele).closest('tr');

    $ele_paquete_id  = $ele_paquete_val.attr("data-metodo_id");
    $ele_origen_id   = $ele_paquete_val.attr("data-origen");

    $.each(metodoPago_array, function(key, metodo){
        if (metodo) {
            if (metodo.metodo_id == $ele_paquete_id && metodo.origen == $ele_origen_id ) {
                //$('#venta-total').val( parseFloat(parseFloat($('#venta-total').val()) -  totalCargoExtra ).toFixed(2));


                //totalCargoExtra = totalCargoExtra - parseFloat(metodoPago_array[key].cargo_extra);
                metodoPago_array[key].cargo_extra = $(ele).val();

                //totalCargoExtra = totalCargoExtra + parseFloat(metodoPago_array[key].cargo_extra);

            }
        }
    });

    $(ele).closest('tr').remove();
    $inputventaArray.val(JSON.stringify(metodoPago_array));
    calcula_cambio_envio();
    render_metodo_template();
}

var refresh_metodo = function(ele){
    $ele_paquete_val = $(ele).closest('tr');

    $ele_paquete_id  = $ele_paquete_val.attr("data-metodo_id");
    $ele_origen_id   = $ele_paquete_val.attr("data-origen");

    $.each(metodoPago_array, function(key, metodo){
        if (metodo) {
            if (metodo.metodo_id == $ele_paquete_id && metodo.origen == $ele_origen_id ) {
                /*if (metodoPago_array[key].metodo_pago_id == VAR_TARJETA_CREDITO  || metodoPago_array[key].metodo_pago_id == VAR_DEBITO_CREDITO ){
                    $('#venta-total').val( parseFloat(parseFloat($('#venta-total').val()) -  totalCargoExtra ).toFixed(2));
                    totalCargoExtra = 0;
                }*/

                metodoPago_array.splice(key, 1 );
            }
        }
    });

    $(ele).closest('tr').remove();
    $inputventaArray.val(JSON.stringify(metodoPago_array));
    //$inputcobroRembolsoEnvioArray.val(JSON.stringify(metodoPago_array));
    calcula_cambio_envio();
    render_metodo_template();
}
</script>