<?php
use yii\helpers\Url;
?>
<script>
	var $inputProductoSearch= $('#inputProductoSearch');


	$inputProductoSearch.keypress(function(e){
        productoSearch = [];
        console.log("entrooo a buscar");
        render_template_search();
        if ($(this).val()) {
            $.get("<?= Url::to(['search-producto-nombre'])?>",{ nombre :  $(this).val() },function($response){
                productoSearch = $response.productos;
                render_template_search();
            },'json');
        }
    });


    var render_template_search = function()
    {
        $content_search.html("");

        $.each(productoSearch, function(key, producto){
            if (producto.id) {

                template_producto_search = $template_producto_search.html();
                template_producto_search = template_producto_search.replace("{{producto_search_id}}",producto.id);

                $content_search.append(template_producto_search);


                $tr        =  $("#producto_search_id_" + producto.id, $content_search);
                $tr.attr("data-item_id",producto.id);

                $("#table_search_clave_id",$tr).html(producto.clave);
                $("#table_search_producto",$tr).html(producto.nombre);
                $("#table_search_precio",$tr).html( btf.conta.money(producto.publico ) );

                $tr.append("<td>" + producto.existencia + "</td>");
            }
        });
    };

    var select_producto = function(ele){
        $('#modal-producto').modal('hide');
        $ele_tr     = $(ele).closest('tr');
        $ele_tr_id  = $ele_tr.attr("data-item_id");
        $.each(productoSearch, function(key, p_search){
            if (p_search.id == $ele_tr_id ) {
                $inputFolioAdd.val(p_search.clave);
            }
        });
    }


</script>